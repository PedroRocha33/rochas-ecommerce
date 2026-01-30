<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

use Source\Core\Connect;

// ✅ Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if (!isset($_ENV['MP_ACCESS_TOKEN']) || empty($_ENV['MP_ACCESS_TOKEN'])) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'MP_ACCESS_TOKEN não configurado']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['success' => false, 'error' => 'Dados inválidos ou JSON mal formatado']);
    exit;
}

// Validações
if (!isset($data['itens']) || !is_array($data['itens']) || empty($data['itens'])) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['success' => false, 'error' => 'Campo "itens" é obrigatório']);
    exit;
}

if (!isset($data['total']) || !is_numeric($data['total'])) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode(['success' => false, 'error' => 'Campo "total" é obrigatório']);
    exit;
}

try {
    $pdo = Connect::getInstance();
    $pdo->beginTransaction();

    // 1️⃣ Criar pedido
    $stmt = $pdo->prepare("
        INSERT INTO orders (usuario_id, total, status)
        VALUES (?, ?, 'pendente')
    ");
    $stmt->execute([
        $_SESSION['usuario_id'] ?? 1,
        $data['total']
    ]);

    $pedidoId = $pdo->lastInsertId();

    // 2️⃣ Criar itens do pedido
    foreach ($data['itens'] as $item) {
        if (!isset($item['produto_id']) || !isset($item['quantidade']) || !isset($item['preco'])) {
            throw new Exception('Item com dados incompletos');
        }

        $stmt = $pdo->prepare("
            INSERT INTO order_items (pedido_id, produto_id, quantidade, preco)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $pedidoId,
            $item['produto_id'],
            $item['quantidade'],
            $item['preco']
        ]);
    }

    // 3️⃣ Criar itens para Mercado Pago
    $items = [];
    foreach ($data['itens'] as $i) {
        if (!isset($i['nome']) || empty($i['nome'])) {
            throw new Exception('Campo "nome" é obrigatório');
        }
        
        if (!isset($i['quantidade']) || $i['quantidade'] < 1) {
            throw new Exception('Quantidade deve ser maior que 0');
        }
        
        if (!isset($i['preco']) || $i['preco'] <= 0) {
            throw new Exception('Preço deve ser maior que 0');
        }

        $items[] = [
            'title' => substr($i['nome'], 0, 256),
            'quantity' => (int) $i['quantidade'],
            'unit_price' => (float) $i['preco'],
            'currency_id' => 'BRL'
        ];
    }

    if (empty($items)) {
        throw new Exception('Nenhum item válido');
    }

    // 4️⃣ Criar preferência com URLs COMPLETAS
    // ✅ CORREÇÃO: URLs devem ser absolutas e válidas
    $baseUrl = 'http://localhost/rochas';
    
    $preferenceData = [
        'items' => $items,
        'external_reference' => (string) $pedidoId,
        'back_urls' => [
            'success' => $baseUrl . '/_checkout/success.php?pedido_id=' . $pedidoId,
            'failure' => $baseUrl . '/_checkout/error.php?pedido_id=' . $pedidoId,
            'pending' => $baseUrl . '/_checkout/pending.php?pedido_id=' . $pedidoId
        ],
        // 'auto_return' => 'approved', // ✅ Agora vai funcionar pois back_urls.success está definida
        'statement_descriptor' => 'ROCHAS'
    ];

    // ✅ Remover notification_url para testes locais (não vai funcionar no localhost)
    // $preferenceData['notification_url'] = $baseUrl . '/api/mercadopago/webhook.php';

    error_log("Criando preferência MP: " . json_encode($preferenceData, JSON_PRETTY_PRINT));

    // 5️⃣ Fazer requisição
    $ch = curl_init('https://api.mercadopago.com/checkout/preferences');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($preferenceData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $_ENV['MP_ACCESS_TOKEN'],
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . uniqid('checkout_', true)
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    error_log("MP Response Code: $httpCode");
    error_log("MP Response: $response");

    if ($curlError) {
        throw new Exception("Erro cURL: $curlError");
    }

    if ($httpCode !== 201 && $httpCode !== 200) {
        $errorDetail = json_decode($response, true);
        $errorMsg = isset($errorDetail['message']) ? $errorDetail['message'] : 'Erro desconhecido';
        
        error_log("Erro MP: " . print_r($errorDetail, true));
        throw new Exception("Erro ao criar preferência (HTTP $httpCode): $errorMsg");
    }

    $preference = json_decode($response, true);

    if (!isset($preference['init_point'])) {
        throw new Exception('Resposta inválida da API');
    }

    $pdo->commit();

    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => true,
        'pedido_id' => $pedidoId,
        'init_point' => $preference['init_point'],
        'preference_id' => $preference['id'] ?? null
    ]);

} catch (Exception $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    error_log("Erro: " . $e->getMessage());
    
    http_response_code(500);
    header('Content-Type: application/json; charset=utf-8');

    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}