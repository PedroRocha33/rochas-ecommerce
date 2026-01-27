<?php
// api/categories.php
header('Content-Type: application/json');
session_start();

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
use Source\Core\Connect;
$pdo = Connect::getInstance();



try {
    $stmt = $pdo->query("SELECT * FROM categories WHERE ativa = 1 ORDER BY nome ASC");
    $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($categorias);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
}