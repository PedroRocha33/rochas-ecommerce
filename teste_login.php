<?php
/**
 * ARQUIVO DE TESTE - DEBUG DO LOGIN
 * Este arquivo ajuda a identificar onde está o problema
 */
use Source\Core\Connect;

// Ativar exibição de erros
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>🔍 Teste de Login - Debug</h1>";
echo "<hr>";

// 1. Testar autoload do Composer
echo "<h2>1. Testando Autoload do Composer</h2>";
if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require __DIR__ . '/vendor/autoload.php';
    echo "✅ Autoload carregado com sucesso!<br>";
} else {
    echo "❌ ERRO: Arquivo vendor/autoload.php não encontrado!<br>";
    echo "Execute: <code>composer install</code><br>";
    exit;
}

// 2. Testar carregamento do .env
echo "<h2>2. Testando arquivo .env</h2>";
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
    echo "✅ Arquivo .env carregado com sucesso!<br>";
    echo "Database: " . ($_ENV['DB_NAME'] ?? 'NÃO DEFINIDO') . "<br>";
} catch (Exception $e) {
    echo "⚠️ AVISO: Erro ao carregar .env - " . $e->getMessage() . "<br>";
}

// 3. Testar conexão com banco de dados
echo "<h2>3. Testando Conexão com Banco de Dados</h2>";
try {
    
    $pdo = Connect::getInstance();
    echo "✅ Conexão com banco estabelecida!<br>";
} catch (Exception $e) {
    echo "❌ ERRO na conexão: " . $e->getMessage() . "<br>";
    exit;
}

// 4. Testar se a tabela users existe
echo "<h2>4. Verificando Tabela 'users'</h2>";
try {
    $stmt = $pdo->query("DESCRIBE users");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "✅ Tabela 'users' existe!<br>";
    echo "<details><summary>Ver estrutura da tabela</summary><pre>";
    print_r($columns);
    echo "</pre></details>";
} catch (Exception $e) {
    echo "❌ ERRO: Tabela 'users' não existe! - " . $e->getMessage() . "<br>";
    exit;
}

// 5. Testar se existe algum usuário
echo "<h2>5. Verificando Usuários Cadastrados</h2>";
try {
    $stmt = $pdo->query("SELECT id, nome, email, nivel, ativo FROM users");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($usuarios) > 0) {
        echo "✅ Encontrados " . count($usuarios) . " usuário(s):<br>";
        echo "<table border='1' style='border-collapse: collapse; margin-top: 10px;'>";
        echo "<tr><th>ID</th><th>Nome</th><th>Email</th><th>Nível</th><th>Ativo</th></tr>";
        foreach ($usuarios as $user) {
            echo "<tr>";
            echo "<td>{$user['id']}</td>";
            echo "<td>{$user['nome']}</td>";
            echo "<td>{$user['email']}</td>";
            echo "<td>{$user['nivel']}</td>";
            echo "<td>" . ($user['ativo'] ? 'Sim' : 'Não') . "</td>";
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "⚠️ AVISO: Nenhum usuário cadastrado!<br>";
    }
} catch (Exception $e) {
    echo "❌ ERRO ao buscar usuários: " . $e->getMessage() . "<br>";
}

// 6. Testar login com credenciais padrão
echo "<h2>6. Testando Login (admin@site.com)</h2>";
$email_teste = 'lucas@gmail.com';
$senha_teste = '123456';

try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? AND ativo = 1");
    $stmt->execute([$email_teste]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($usuario) {
        echo "✅ Usuário encontrado!<br>";
        echo "Nome: {$usuario['nome']}<br>";
        echo "Email: {$usuario['email']}<br>";
        echo "Nível: {$usuario['nivel']}<br>";
        
        // Testar senha
        if (password_verify($senha_teste, $usuario['senha'])) {
            echo "✅ Senha CORRETA!<br>";
            echo "<br><strong style='color: green;'>🎉 LOGIN FUNCIONARIA!</strong><br>";
            
            // Simular redirecionamento
            echo "<br><h3>Redirecionamento seria para:</h3>";
            if ($usuario['nivel'] >= 2) {
                echo "📍 <strong>/rochas/app/</strong> (Admin/Gerente)<br>";
            } else {
                echo "📍 <strong>/rochas/</strong> (Cliente)<br>";
            }
        } else {
            echo "❌ Senha INCORRETA!<br>";
            echo "<strong>A senha no banco não corresponde a 'admin123'</strong><br>";
            echo "<br>Para corrigir, execute:<br>";
            echo "<code style='background: #f0f0f0; padding: 10px; display: block; margin: 10px 0;'>";
            echo "UPDATE users SET senha = '" . password_hash('admin123', PASSWORD_DEFAULT) . "' WHERE email = 'admin@site.com';";
            echo "</code>";
        }
    } else {
        echo "❌ Usuário não encontrado ou inativo!<br>";
    }
} catch (Exception $e) {
    echo "❌ ERRO ao testar login: " . $e->getMessage() . "<br>";
}

// 7. Testar sessão
echo "<h2>7. Testando Sistema de Sessão</h2>";
session_start();
if (session_status() === PHP_SESSION_ACTIVE) {
    echo "✅ Sessão PHP funcionando!<br>";
    echo "Session ID: " . session_id() . "<br>";
} else {
    echo "❌ ERRO: Sessão não está funcionando!<br>";
}

// 8. Verificar estrutura de pastas
echo "<h2>8. Verificando Estrutura de Pastas</h2>";
$pastas_necessarias = [
    '/rochas/' => __DIR__,
    '/rochas/views/' => __DIR__ . '/app',
    '/rochas/source/' => __DIR__ . '/source',
    '/rochas/vendor/' => __DIR__ . '/vendor',
];

foreach ($pastas_necessarias as $nome => $caminho) {
    if (file_exists($caminho) && is_dir($caminho)) {
        echo "✅ {$nome} existe<br>";
    } else {
        echo "❌ {$nome} NÃO EXISTE! <strong>← PROBLEMA AQUI!</strong><br>";
    }
}

// 9. Verificar se index.php existe em /app/
echo "<h2>9. Verificando Arquivo /app/index.php</h2>";
$app_index = __DIR__ . '/app/index.php';
if (file_exists($app_index)) {
    echo "✅ /rochas/app/index.php existe!<br>";
    echo "Tamanho: " . filesize($app_index) . " bytes<br>";
} else {
    echo "❌ /rochas/app/index.php NÃO EXISTE! <strong>← ESTE É O PROBLEMA!</strong><br>";
    echo "<br><strong>SOLUÇÃO:</strong><br>";
    echo "1. Crie a pasta: /rochas/app/<br>";
    echo "2. Copie o arquivo 'app_index.php' para '/rochas/app/index.php'<br>";
}

echo "<hr>";
echo "<h2>📋 Resumo</h2>";
echo "<p>Se todos os itens acima estiverem com ✅, o login deve funcionar.</p>";
echo "<p>Se houver ❌, corrija os problemas indicados.</p>";

echo "<hr>";
echo "<p><a href='login.php' style='padding: 10px 20px; background: #007bff; color: white; text-decoration: none; border-radius: 5px;'>Voltar para Login</a></p>";
?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    h1 {
        color: #333;
    }
    h2 {
        color: #666;
        margin-top: 30px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
    }
    code {
        background: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: monospace;
    }
    table {
        margin: 10px 0;
    }
    th, td {
        padding: 8px 12px;
        text-align: left;
    }
    th {
        background: #f0f0f0;
    }
</style>