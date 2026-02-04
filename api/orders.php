<?php

session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../vendor/autoload.php';

use Source\Core\Connect;

header('Content-Type: application/json; charset=utf-8');

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

try {
    $pdo = Connect::getInstance();

    // ========== LISTAR PEDIDOS ==========
    if ($method === 'GET' && !isset($_GET['id'])) {
        $stmt = $pdo->query("
            SELECT 
                o.id as pedido_id,
                o.subtotal,
                o.valor_frete,
                o.total,
                o.status,
                o.criado_em,
                oa.nome as cliente_nome,
                oa.email as cliente_email,
                COUNT(oi.id) as total_itens
            FROM orders o
            LEFT JOIN order_addresses oa ON oa.pedido_id = o.id
            LEFT JOIN order_items oi ON oi.pedido_id = o.id
            GROUP BY o.id
            ORDER BY o.criado_em DESC
        ");

        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pedidos);
        exit;
    }

    // ========== VER DETALHES DO PEDIDO ==========
    if ($method === 'GET' && isset($_GET['id'])) {
        $pedidoId = (int) $_GET['id'];

        // Buscar dados do pedido
        $stmt = $pdo->prepare("
            SELECT 
                o.*,
                oa.nome as cliente_nome,
                oa.email as cliente_email,
                oa.telefone as cliente_telefone,
                oa.cep,
                oa.rua,
                oa.numero,
                oa.complemento,
                oa.bairro,
                oa.cidade,
                oa.estado
            FROM orders o
            LEFT JOIN order_addresses oa ON oa.pedido_id = o.id
            WHERE o.id = ?
        ");
        
        $stmt->execute([$pedidoId]);
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pedido) {
            http_response_code(404);
            echo json_encode(['error' => 'Pedido não encontrado']);
            exit;
        }

        // Buscar itens do pedido
        $stmt = $pdo->prepare("
            SELECT 
                oi.*,
                oi.nome_produto as produto_nome,
                p.imagem as produto_imagem
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.produto_id
            WHERE oi.pedido_id = ?
        ");
        
        $stmt->execute([$pedidoId]);
        $pedido['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Montar objeto endereco separado
        $pedido['endereco'] = [
            'nome' => $pedido['cliente_nome'] ?? null,
            'telefone' => $pedido['cliente_telefone'] ?? null,
            'cep' => $pedido['cep'] ?? null,
            'rua' => $pedido['rua'] ?? null,
            'numero' => $pedido['numero'] ?? null,
            'complemento' => $pedido['complemento'] ?? null,
            'bairro' => $pedido['bairro'] ?? null,
            'cidade' => $pedido['cidade'] ?? null,
            'estado' => $pedido['estado'] ?? null
        ];

        echo json_encode($pedido);
        exit;
    }

    // ========== ATUALIZAR STATUS ==========
    if ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);

        if (!isset($data['id']) || !isset($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID e status obrigatórios']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso'
        ]);
        exit;
    }

    // ========== MÉTODO NÃO PERMITIDO ==========
    http_response_code(405);
    echo json_encode(['error' => 'Método não permitido']);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}