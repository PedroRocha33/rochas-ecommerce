<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

use Source\Core\Connect;

header('Content-Type: application/json; charset=utf-8');

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if (empty($_ENV['MP_ACCESS_TOKEN'])) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'MP_ACCESS_TOKEN não configurado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON inválido']);
    exit;
}

// Validações
if (empty($data['itens']) || !is_array($data['itens'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Itens obrigatórios']);
    exit;
}

if (!isset($data['total']) || !is_numeric($data['total'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Total inválido']);
    exit;
}

try {
    $pdo = Connect::getInstance();
    $pdo->beginTransaction();

    // Calcular subtotal
    $subtotal = 0;
    foreach ($data['itens'] as $item) {
        $subtotal += floatval($item['preco']) * intval($item['quantidade']);
    }

    $frete = floatval($data['frete'] ?? 0);
    $total = floatval($data['total']);

    // 1️⃣ Criar pedido usando as colunas que EXISTEM no seu banco
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            usuario_id,
            subtotal,
            total,
            valor_frete,
            tipo_frete,
            prazo_frete,
            status
        ) VALUES (?, ?, ?, ?, ?, ?, 'pendente')
    ");
    
    $stmt->execute([
        $_SESSION['usuario_id'] ?? 1,
        $subtotal,
        $total,
        $frete,
        $data['tipo_frete'] ?? null,
        $data['prazo_frete'] ?? null
    ]);

    $pedidoId = $pdo->lastInsertId();

    // 2️⃣ Salvar endereço na tabela order_addresses
    if (!empty($data['endereco']) && !empty($data['cliente'])) {
        $stmt = $pdo->prepare("
            INSERT INTO order_addresses (
                pedido_id,
                nome,
                telefone,
                email,
                cep,
                rua,
                numero,
                complemento,
                bairro,
                cidade,
                estado
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $pedidoId,
            $data['cliente']['nome'],
            $data['cliente']['telefone'],
            $data['cliente']['email'],
            $data['endereco']['cep'],
            $data['endereco']['rua'],
            $data['endereco']['numero'],
            $data['endereco']['complemento'] ?? '',
            $data['endereco']['bairro'],
            $data['endereco']['cidade'],
            $data['endereco']['estado']
        ]);
    }

    // 3️⃣ Salvar itens do pedido usando as colunas que EXISTEM
    foreach ($data['itens'] as $item) {
        $quantidade = intval($item['quantidade']);
        $precoUnitario = floatval($item['preco']);
        $subtotalItem = $quantidade * $precoUnitario;
        
        $stmt = $pdo->prepare("
            INSERT INTO order_items (
                pedido_id,
                produto_id,
                nome_produto,
                quantidade,
                preco_unitario,
                preco
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $pedidoId,
            $item['produto_id'] ?? null,
            $item['nome'],
            $quantidade,
            $precoUnitario,
            $subtotalItem
        ]);
    }

    // 4️⃣ Itens para o Mercado Pago
    $items = [];

    foreach ($data['itens'] as $i) {
        $items[] = [
            'title' => substr($i['nome'], 0, 256),
            'quantity' => (int) $i['quantidade'],
            'unit_price' => (float) $i['preco'],
            'currency_id' => 'BRL'
        ];
    }

    // Adicionar frete como item
    if ($frete > 0) {
        $items[] = [
            'title' => 'Frete',
            'quantity' => 1,
            'unit_price' => $frete,
            'currency_id' => 'BRL'
        ];
    }

    // 5️⃣ Criar preferência Mercado Pago
    $baseUrl = 'http://localhost/rochas';

    $preferenceData = [
        'items' => $items,
        'external_reference' => (string) $pedidoId,
        'payer' => [
            'name' => $data['cliente']['nome'] ?? '',
            'email' => $data['cliente']['email'] ?? '',
            'phone' => [
                'number' => $data['cliente']['telefone'] ?? ''
            ],
            'address' => [
                'zip_code' => $data['endereco']['cep'] ?? '',
                'street_name' => $data['endereco']['rua'] ?? '',
                'street_number' => $data['endereco']['numero'] ?? ''
            ]
        ],
        'back_urls' => [
            'success' => $baseUrl . '/checkout/success.php?pedido_id=' . $pedidoId,
            'failure' => $baseUrl . '/checkout/error.php?pedido_id=' . $pedidoId,
            'pending' => $baseUrl . '/checkout/pending.php?pedido_id=' . $pedidoId
        ],
        'notification_url' => $baseUrl . '/api/mercadopago/webhook.php',
        'statement_descriptor' => 'ROCHAS'
    ];

    // 6️⃣ Enviar para Mercado Pago
    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preferenceData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $_ENV['MP_ACCESS_TOKEN'],
            'Content-Type: application/json'
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode !== 200 && $httpCode !== 201) {
        $err = json_decode($response, true);
        throw new Exception($err['message'] ?? 'Erro ao criar preferência');
    }

    $preference = json_decode($response, true);

    if (empty($preference['init_point'])) {
        throw new Exception('init_point não retornado');
    }

    $pdo->commit();

    echo json_encode([
        'success' => true,
        'pedido_id' => $pedidoId,
        'init_point' => $preference['init_point'],
        'preference_id' => $preference['id']
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }

    error_log("Erro ao criar pedido: " . $e->getMessage());
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}