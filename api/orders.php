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
        if (isset($_GET['id'])) {
            $id = (int)$_GET['id'];
            
            // 1. Buscar dados do pedido (Usa LEFT JOIN para garantir que o pedido apareça mesmo sem user)
            $stmt = $pdo->prepare("
                SELECT 
                    o.id, o.total, o.subtotal, o.valor_frete, o.tipo_frete, o.prazo_frete,
                    o.status, o.created_at, o.usuario_id,
                    u.nome AS cliente_nome, u.email AS cliente_email
                FROM orders o
                LEFT JOIN users u ON u.id = o.usuario_id
                WHERE o.id = ?
            ");
            $stmt->execute([$id]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$pedido) {
                http_response_code(404);
                echo json_encode(['error' => 'Pedido não encontrado']);
                exit;
            }
            
            // 2. Buscar endereço do pedido (Prioridade para a tabela de histórico de endereços)
            $stmt = $pdo->prepare("SELECT * FROM order_addresses WHERE pedido_id = ? LIMIT 1");
            $stmt->execute([$id]);
            $endereco = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Se não tem na order_addresses, tenta buscar o endereço atual do usuário
            if (!$endereco && !empty($pedido['usuario_id'])) {
                $stmt = $pdo->prepare("SELECT * FROM addresses WHERE usuario_id = ? AND principal = 1 LIMIT 1");
                $stmt->execute([$pedido['usuario_id']]);
                $endereco = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            
            $pedido['endereco'] = $endereco ?: null;
            
            // 3. Buscar itens do pedido
            $stmt = $pdo->prepare("
                SELECT 
                    oi.id, oi.quantidade, oi.nome_produto,
                    COALESCE(oi.preco_unitario, oi.preco) AS preco_unitario,
                    p.imagem AS produto_imagem
                FROM order_items oi
                LEFT JOIN products p ON p.id = oi.produto_id
                WHERE oi.pedido_id = ?
            ");
            $stmt->execute([$id]);
            $pedido['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode($pedido);
            
        } else {
            // LISTAR TODOS OS PEDIDOS (Melhoria aplicada aqui)
            $stmt = $pdo->query("
                SELECT 
                    o.id AS pedido_id,
                    o.total,
                    o.status,
                    o.created_at AS criado_em,
                    u.nome AS cliente_nome,
                    u.email AS cliente_email,
                    COUNT(oi.id) AS total_itens
                FROM orders o
                LEFT JOIN users u ON u.id = o.usuario_id -- Alterado de INNER para LEFT
                LEFT JOIN order_items oi ON oi.pedido_id = o.id
                GROUP BY o.id, o.total, o.status, o.created_at, u.nome, u.email
                ORDER BY o.created_at DESC
            ");
            
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        
    } elseif ($method === 'PUT') {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (empty($data['id']) || empty($data['status'])) {
            http_response_code(400);
            echo json_encode(['error' => 'ID e status são obrigatórios']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$data['status'], $data['id']]);

        echo json_encode(['success' => true, 'message' => 'Status atualizado']);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro: ' . $e->getMessage()]);
}