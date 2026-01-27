<?php
// api/products.php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

session_start();
header('Content-Type: application/json');

// Verificar autenticação
if (!isset($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado. Sessão não encontrada.']);
    exit;
}

if (!isset($_SESSION['nivel']) || $_SESSION['nivel'] < 2) {
    http_response_code(403);
    echo json_encode(['error' => 'Acesso negado. Nível insuficiente.']);
    exit;
}

require_once dirname(__DIR__) . '/vendor/autoload.php';
use Source\Core\Connect;

// Usar o método getInstance() da classe Connect
try {
    $pdo = Connect::getInstance();
    
    if (!$pdo) {
        http_response_code(500);
        echo json_encode(['error' => 'Erro ao obter conexão com banco']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro na conexão: ' . $e->getMessage()]);
    exit;
}

// Detectar método real (suporta _method para simular PUT via POST)
$method = $_POST['_method'] ?? $_SERVER['REQUEST_METHOD'];

// Log para debug
error_log("Método detectado: " . $method);
error_log("POST data: " . print_r($_POST, true));
error_log("FILES data: " . print_r($_FILES, true));

// ===============================
// FUNÇÃO AUXILIAR: UPLOAD DE IMAGEM
// ===============================
function uploadImagem($file) {
    if (empty($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    
    // Validar tipo de arquivo
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        throw new Exception('Tipo de arquivo inválido. Use JPG, PNG, WEBP ou GIF');
    }
    
    // Validar tamanho (máx 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        throw new Exception('Arquivo muito grande. Tamanho máximo: 5MB');
    }
    
    // Criar diretório se não existir
    $uploadDir = dirname(__DIR__) . '/storage/images';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    
    // Gerar nome único
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $nomeImagem = uniqid('prod_') . '.' . $ext;
    $destino = $uploadDir . '/' . $nomeImagem;
    
    // Mover arquivo
    if (!move_uploaded_file($file['tmp_name'], $destino)) {
        throw new Exception('Erro ao salvar imagem no servidor');
    }
    
    return $nomeImagem;
}

// ===============================
// FUNÇÃO AUXILIAR: DELETAR IMAGEM
// ===============================
function deleteImagem($nomeImagem) {
    if (empty($nomeImagem)) {
        return;
    }
    
    $caminhoImagem = dirname(__DIR__) . '/storage/images/' . $nomeImagem;
    if (file_exists($caminhoImagem)) {
        unlink($caminhoImagem);
    }
}

try {
    switch ($method) {
        case 'GET':
            if (isset($_GET['id'])) {
                // Buscar produto específico
                $stmt = $pdo->prepare("
                    SELECT p.*, c.nome as categoria_nome 
                    FROM products p 
                    LEFT JOIN categories c ON p.categoria_id = c.id 
                    WHERE p.id = ?
                ");
                $stmt->execute([$_GET['id']]);
                $produto = $stmt->fetch();
                
                if ($produto) {
                    // Converter objeto para array
                    echo json_encode((array)$produto);
                } else {
                    http_response_code(404);
                    echo json_encode(['error' => 'Produto não encontrado']);
                }
            } else {
                // Listar todos os produtos
                $stmt = $pdo->query("
                    SELECT p.*, c.nome as categoria_nome 
                    FROM products p 
                    LEFT JOIN categories c ON p.categoria_id = c.id 
                    ORDER BY p.id DESC
                ");
                $produtos = $stmt->fetchAll();
                
                // Converter objetos para array
                $result = array_map(function($item) {
                    return (array)$item;
                }, $produtos);
                
                echo json_encode($result);
            }
            break;

        case 'POST':
            // POST com FormData - dados vêm de $_POST e $_FILES
            $data = $_POST;
            
            // Validação
            if (empty($data['nome']) || empty($data['preco'])) {
                http_response_code(400);
                echo json_encode(['error' => 'Nome e preço são obrigatórios']);
                exit;
            }

            // Criar slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nome'])));

            // Upload de imagem (se enviada)
            $imagemPath = null;
            if (!empty($_FILES['imagem'])) {
                try {
                    $imagemPath = uploadImagem($_FILES['imagem']);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }
            }

            $stmt = $pdo->prepare("
                INSERT INTO products (nome, slug, descricao, preco, estoque, categoria_id, imagem, weight, width, height, length, ativo)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            
            $stmt->execute([
                $data['nome'],
                $slug,
                $data['descricao'] ?? '',
                $data['preco'],
                $data['estoque'] ?? 0,
                !empty($data['categoria_id']) ? $data['categoria_id'] : null,
                $imagemPath,
                !empty($data['weight']) ? $data['weight'] : null,
                !empty($data['width']) ? $data['width'] : null,
                !empty($data['height']) ? $data['height'] : null,
                !empty($data['length']) ? $data['length'] : null,
                $data['ativo'] ?? 1
            ]);

            echo json_encode([
                'success' => true,
                'id' => $pdo->lastInsertId(),
                'imagem' => $imagemPath,
                'message' => 'Produto criado com sucesso'
            ]);
            break;

        case 'PUT':
            // PUT sempre vem via POST com _method=PUT quando há arquivo
            $data = $_POST;
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID é obrigatório']);
                exit;
            }

            // Buscar produto atual para pegar imagem antiga
            $stmtSelect = $pdo->prepare("SELECT imagem FROM products WHERE id = ?");
            $stmtSelect->execute([$data['id']]);
            $produtoAtual = $stmtSelect->fetch();
            
            if (!$produtoAtual) {
                http_response_code(404);
                echo json_encode(['error' => 'Produto não encontrado']);
                exit;
            }

            $imagemPath = $produtoAtual->imagem;

            // Upload de nova imagem (se enviada)
            if (!empty($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
                try {
                    // Deletar imagem antiga
                    if ($imagemPath) {
                        deleteImagem($imagemPath);
                    }
                    
                    // Upload da nova imagem
                    $imagemPath = uploadImagem($_FILES['imagem']);
                } catch (Exception $e) {
                    http_response_code(400);
                    echo json_encode(['error' => $e->getMessage()]);
                    exit;
                }
            }

            // Criar slug
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $data['nome'])));

            $stmt = $pdo->prepare("
                UPDATE products 
                SET nome = ?, slug = ?, descricao = ?, preco = ?, estoque = ?, categoria_id = ?, imagem = ?, weight = ?, width = ?, height = ?, length = ?, ativo = ?
                WHERE id = ?
            ");
            
            $stmt->execute([
                $data['nome'],
                $slug,
                $data['descricao'] ?? '',
                $data['preco'],
                $data['estoque'] ?? 0,
                !empty($data['categoria_id']) ? $data['categoria_id'] : null,
                $imagemPath,
                !empty($data['weight']) ? $data['weight'] : null,
                !empty($data['width']) ? $data['width'] : null,
                !empty($data['height']) ? $data['height'] : null,
                !empty($data['length']) ? $data['length'] : null,
                $data['ativo'] ?? 1,
                $data['id']
            ]);

            echo json_encode([
                'success' => true,
                'imagem' => $imagemPath,
                'message' => 'Produto atualizado com sucesso'
            ]);
            break;

        case 'DELETE':
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (empty($data['id'])) {
                http_response_code(400);
                echo json_encode(['error' => 'ID é obrigatório']);
                exit;
            }

            // Buscar imagem antes de deletar para remover do disco
            $stmtSelect = $pdo->prepare("SELECT imagem FROM products WHERE id = ?");
            $stmtSelect->execute([$data['id']]);
            $produto = $stmtSelect->fetch();
            
            if ($produto && $produto->imagem) {
                deleteImagem($produto->imagem);
            }

            $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
            $stmt->execute([$data['id']]);

            echo json_encode([
                'success' => true,
                'message' => 'Produto removido com sucesso'
            ]);
            break;

        default:
            http_response_code(405);
            echo json_encode(['error' => 'Método não permitido']);
            break;
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Erro no servidor: ' . $e->getMessage()]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}