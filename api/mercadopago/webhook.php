<?php

require __DIR__ . '/../../vendor/autoload.php';

use Source\Core\Connect;

// Log de entrada
$logFile = __DIR__ . '/webhook.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Webhook recebido\n", FILE_APPEND);

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

// Pegar dados do Mercado Pago
$input = file_get_contents('php://input');
file_put_contents($logFile, "Dados: " . $input . "\n", FILE_APPEND);

$data = json_decode($input, true);

if (!$data || !isset($data['type'])) {
    http_response_code(400);
    exit;
}

// Apenas processar notificações de pagamento
if ($data['type'] !== 'payment') {
    http_response_code(200);
    exit;
}

try {
    $paymentId = $data['data']['id'] ?? null;
    
    if (!$paymentId) {
        throw new Exception('Payment ID não encontrado');
    }

    // Buscar dados do pagamento na API do Mercado Pago
    $ch = curl_init("https://api.mercadopago.com/v1/payments/{$paymentId}");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $_ENV['MP_ACCESS_TOKEN']
        ],
        CURLOPT_SSL_VERIFYPEER => false
    ]);

    $response = curl_exec($ch);
    $payment = json_decode($response, true);
    curl_close($ch);

    if (!$payment || !isset($payment['external_reference'])) {
        throw new Exception('Pagamento não encontrado');
    }

    $pedidoId = $payment['external_reference'];
    $status = $payment['status'];

    // Mapear status do Mercado Pago para status do pedido
    $statusMap = [
        'approved' => 'pago',
        'pending' => 'pendente',
        'in_process' => 'processando',
        'rejected' => 'cancelado',
        'cancelled' => 'cancelado',
        'refunded' => 'cancelado'
    ];

    $novoStatus = $statusMap[$status] ?? 'pendente';

    // Atualizar pedido no banco
    $pdo = Connect::getInstance();
    
    // Como seu banco não tem as colunas payment_id e payment_status,
    // vamos apenas atualizar o status
    $stmt = $pdo->prepare("
        UPDATE orders 
        SET status = ?
        WHERE id = ?
    ");
    
    $stmt->execute([
        $novoStatus,
        $pedidoId
    ]);

    file_put_contents($logFile, 
        "Pedido #{$pedidoId} atualizado: {$novoStatus} (MP Status: {$status})\n", 
        FILE_APPEND
    );

    // Opcionalmente, salvar na tabela payments
    $stmt = $pdo->prepare("
        INSERT INTO payments (pedido_id, metodo, status, referencia, valor)
        VALUES (?, 'mercadopago', ?, ?, ?)
        ON DUPLICATE KEY UPDATE status = ?, valor = ?
    ");
    
    $stmt->execute([
        $pedidoId,
        $status,
        $paymentId,
        $payment['transaction_amount'] ?? 0,
        $status,
        $payment['transaction_amount'] ?? 0
    ]);

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    file_put_contents($logFile, "Erro: " . $e->getMessage() . "\n", FILE_APPEND);
    http_response_code(500);
}