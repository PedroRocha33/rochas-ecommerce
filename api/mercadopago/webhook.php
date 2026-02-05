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

    file_put_contents($logFile, "Pedido: {$pedidoId} | Status MP: {$status}\n", FILE_APPEND);

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

    // Conectar ao banco
    $pdo = Connect::getInstance();
    $pdo->beginTransaction();

    // ============================================
    // ✅ ATUALIZAR ESTOQUE (APENAS SE APROVADO)
    // ============================================
    
    if ($status === 'approved') {
        file_put_contents($logFile, "=== PAGAMENTO APROVADO - ATUALIZANDO ESTOQUE ===\n", FILE_APPEND);
        
        // Buscar os itens do pedido
        $stmtOrder = $pdo->prepare("
            SELECT items_json 
            FROM orders 
            WHERE id = ?
        ");
        $stmtOrder->execute([$pedidoId]);
        $order = $stmtOrder->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            throw new Exception("Pedido #{$pedidoId} não encontrado no banco");
        }
        
        // Decodificar os itens
        $items = json_decode($order['items_json'], true);
        
        if (!$items || !is_array($items)) {
            throw new Exception("Itens do pedido inválidos");
        }
        
        file_put_contents($logFile, "Total de itens: " . count($items) . "\n", FILE_APPEND);
        
        // Atualizar estoque de cada produto
        foreach ($items as $item) {
            $productId = $item['id'] ?? $item['product_id'] ?? null;
            $quantity = $item['quantity'] ?? $item['quantidade'] ?? 0;
            
            if (!$productId || !$quantity) {
                file_put_contents($logFile, "AVISO: Item sem ID ou quantidade válida\n", FILE_APPEND);
                continue;
            }
            
            // Buscar produto atual
            $stmtProduct = $pdo->prepare("
                SELECT id, nome, estoque 
                FROM products 
                WHERE id = ?
            ");
            $stmtProduct->execute([$productId]);
            $product = $stmtProduct->fetch(PDO::FETCH_ASSOC);
            
            if (!$product) {
                file_put_contents($logFile, "AVISO: Produto ID {$productId} não encontrado\n", FILE_APPEND);
                continue;
            }
            
            $estoqueAtual = (int) $product['estoque'];
            $novoEstoque = $estoqueAtual - $quantity;
            
            // Não permitir estoque negativo
            if ($novoEstoque < 0) {
                file_put_contents($logFile, 
                    "AVISO: Estoque insuficiente para '{$product['nome']}' (Atual: {$estoqueAtual}, Vendido: {$quantity})\n", 
                    FILE_APPEND
                );
                $novoEstoque = 0;
            }
            
            // Atualizar estoque
            $stmtUpdate = $pdo->prepare("
                UPDATE products 
                SET estoque = ? 
                WHERE id = ?
            ");
            $stmtUpdate->execute([$novoEstoque, $productId]);
            
            file_put_contents($logFile, 
                "✅ Produto: {$product['nome']} | Estoque: {$estoqueAtual} → {$novoEstoque}\n", 
                FILE_APPEND
            );
        }
        
        file_put_contents($logFile, "=== ESTOQUE ATUALIZADO COM SUCESSO ===\n", FILE_APPEND);
    }

    // ============================================
    // ATUALIZAR STATUS DO PEDIDO
    // ============================================
    
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

    // ============================================
    // SALVAR PAGAMENTO
    // ============================================
    
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

    // Commit das transações
    $pdo->commit();

    http_response_code(200);
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback em caso de erro
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    
    file_put_contents($logFile, "ERRO: " . $e->getMessage() . "\n", FILE_APPEND);
    file_put_contents($logFile, "Stack trace: " . $e->getTraceAsString() . "\n", FILE_APPEND);
    
    http_response_code(500);
}