<?php
// api/orders.php
header('Content-Type: application/json');
session_start();

// Verificar autenticação
if (!isset($_SESSION['usuario_id']) || $_SESSION['nivel'] < 2) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
use Source\Core\Connect;
$pdo = Connect::getInstance();



$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($method === 'GET') {
        // Listar pedidos com informações do usuário
        $stmt = $pdo->query("
            SELECT o.*, u.nome as cliente_nome, u.email as cliente_email,
                   COUNT(oi.id) as total_itens
            FROM orders o
            LEFT JOIN users u ON o.usuario_id = u.id
            LEFT JOIN order_items oi ON o.id = oi.pedido_id
            GROUP BY o.id
            ORDER BY o.criado_em DESC
        ");
        
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($pedidos);
        
    } elseif ($method === 'PUT') {
        // Atualizar status do pedido
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id']) || empty($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID e status são obrigatórios']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Status atualizado com sucesso'
        ]);
    } else {
        http_response_code(405);
        echo json_encode(['error' => 'Método não permitido']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}