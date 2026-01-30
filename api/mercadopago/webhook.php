<?php
require __DIR__ . '/../../vendor/autoload.php';

use Source\Core\Connect;

// ✅ Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['data']['id'])) {
    http_response_code(400);
    exit;
}

$paymentId = $data['data']['id'];

$ch = curl_init("https://api.mercadopago.com/v1/payments/$paymentId");
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . $_ENV['MP_ACCESS_TOKEN']
    ],
    // ✅ DESABILITAR SSL EM DESENVOLVIMENTO
    // ⚠️ REMOVER EM PRODUÇÃO!
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode !== 200) {
    error_log("Webhook MP Error: HTTP $httpCode - $response");
    http_response_code(500);
    exit;
}

$response = json_decode($response, true);

$pedidoId = $response['external_reference'];
$status = $response['status'];

try {
    $pdo = Connect::getInstance();
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $pedidoId]);
    
    http_response_code(200);
    echo json_encode(['success' => true]);
    
} catch (Exception $e) {
    error_log("Webhook DB Error: " . $e->getMessage());
    http_response_code(500);
}