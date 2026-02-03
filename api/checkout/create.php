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

// 🔍 LER E LOGAR DADOS RECEBIDOS
$rawInput = file_get_contents('php://input');
error_log("=== DADOS RECEBIDOS ===");
error_log($rawInput);

$data = json_decode($rawInput, true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'JSON inválido']);
    exit;
}

// 🔍 LOGAR FRETE RECEBIDO
error_log("Frete recebido: " . ($data['frete'] ?? 'NÃO ENVIADO'));
error_log("Tipo do frete: " . gettype($data['frete'] ?? null));

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

    // 2️⃣ Salvar itens do pedido
    foreach ($data['itens'] as $item) {
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

    // 3️⃣ Itens para o Mercado Pago
    $items = [];

    foreach ($data['itens'] as $i) {
        $items[] = [
            'title' => substr($i['nome'], 0, 256),
            'quantity' => (int) $i['quantidade'],
            'unit_price' => (float) $i['preco'],
            'currency_id' => 'BRL'
        ];
    }

    // ➕ FRETE como item - COM VALIDAÇÃO MELHORADA
    $freteValor = floatval($data['frete'] ?? 0);
    
    error_log("Frete convertido para float: " . $freteValor);
    
    if ($freteValor > 0) {
        $items[] = [
            'title' => 'Frete',
            'quantity' => 1,
            'unit_price' => $freteValor,
            'currency_id' => 'BRL'
        ];
        error_log("✅ Frete adicionado aos itens: R$ " . $freteValor);
    } else {
        error_log("⚠️ Frete não adicionado (valor: " . $freteValor . ")");
    }

    // 🔍 LOGAR ITENS FINAIS
    error_log("=== ITENS PARA MERCADO PAGO ===");
    error_log(json_encode($items, JSON_PRETTY_PRINT));

    // 4️⃣ Preferência Mercado Pago
    $baseUrl = 'http://localhost/rochas';

    $preferenceData = [
        'items' => $items,
        'external_reference' => (string) $pedidoId,
        'back_urls' => [
            'success' => $baseUrl . '/_checkout/success.php?pedido_id=' . $pedidoId,
            'failure' => $baseUrl . '/_checkout/error.php?pedido_id=' . $pedidoId,
            'pending' => $baseUrl . '/_checkout/pending.php?pedido_id=' . $pedidoId
        ],
        'statement_descriptor' => 'ROCHAS'
    ];

    // 5️⃣ Criar preferência via API
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

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}