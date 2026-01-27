<?php
/**
 * Script para testar a API e diagnóstico de problemas
 * Acesse: http://localhost/rochas/test_api.php
 */

session_start();

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teste API</title>
    <style>
        body {
            font-family: monospace;
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background: #1e1e1e;
            color: #d4d4d4;
        }
        h1 { color: #4ec9b0; }
        h2 { color: #dcdcaa; margin-top: 30px; }
        .box {
            background: #2d2d2d;
            padding: 15px;
            margin: 10px 0;
            border-radius: 5px;
            border-left: 4px solid #007acc;
        }
        .success { border-color: #4ec9b0; }
        .error { border-color: #f48771; }
        .warning { border-color: #dcdcaa; }
        pre {
            background: #1e1e1e;
            padding: 10px;
            border-radius: 3px;
            overflow-x: auto;
        }
        .btn {
            background: #007acc;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            margin: 5px;
        }
        .btn:hover { background: #005a9e; }
    </style>
</head>
<body>
    <h1>🔧 Diagnóstico da API</h1>

    <!-- TESTE 1: SESSÃO -->
    <h2>1. Verificar Sessão</h2>
    <div class="box <?= isset($_SESSION['usuario_id']) ? 'success' : 'error' ?>">
        <?php if (isset($_SESSION['usuario_id'])): ?>
            ✅ <strong>Sessão OK</strong><br>
            <pre><?php print_r($_SESSION); ?></pre>
        <?php else: ?>
            ❌ <strong>Sem sessão ativa</strong><br>
            <a href="/rochas/fix_session.php" class="btn">Criar Sessão de Teste</a>
        <?php endif; ?>
    </div>

    <!-- TESTE 2: ARQUIVO DE CONEXÃO -->
    <h2>2. Verificar source/Core/Connect.php</h2>
    <div class="box">
        <?php 
        $possible_paths = [
            __DIR__ . '/source/Core/Connect.php' => '/source/Core/Connect.php',
            __DIR__ . '/Source/Core/Connect.php' => '/Source/Core/Connect.php',
            __DIR__ . '/Core/Connect.php' => '/Core/Connect.php',
        ];
        
        $found = false;
        foreach ($possible_paths as $full_path => $display_path) {
            if (file_exists($full_path)) {
                echo "✅ <strong>Arquivo encontrado:</strong> $display_path<br>";
                $found = true;
                break;
            }
        }
        
        if (!$found) {
            echo "❌ <strong>Arquivo source/Core/Connect.php não encontrado</strong><br>";
            echo "Procurado em:<br>";
            foreach ($possible_paths as $full_path => $display_path) {
                echo "• $display_path<br>";
            }
        }
        ?>
    </div>

    <!-- TESTE 3: CONEXÃO COM BANCO -->
    <h2>3. Testar Conexão com Banco</h2>
    <div class="box">
        <?php
        try {
            $possible_paths = [
                __DIR__ . '/source/Core/Connect.php',
                __DIR__ . '/Source/Core/Connect.php',
                __DIR__ . '/Core/Connect.php',
            ];
            
            $db_file = null;
            foreach ($possible_paths as $path) {
                if (file_exists($path)) {
                    $db_file = $path;
                    break;
                }
            }
            
            if ($db_file) {
                require_once $db_file;
                
                // Usar o método getInstance()
                $pdo = \Source\Core\Connect::getInstance();
                
                if ($pdo) {
                    echo "✅ <strong>Conexão OK com MySQL</strong><br>";
                    echo "📝 <strong>Método usado:</strong> \\Source\\Core\\Connect::getInstance()<br>";
                    
                    // Testar query
                    $stmt = $pdo->query("SELECT COUNT(*) as total FROM products");
                    $result = $stmt->fetch();
                    echo "📦 <strong>Total de produtos no banco:</strong> " . $result->total;
                } else {
                    echo '❌ <strong>getInstance() retornou null</strong>';
                }
            } else {
                echo '⚠️ <strong>Arquivo source/Core/Connect.php não encontrado</strong>';
            }
        } catch (Exception $e) {
            echo '❌ <strong>Erro na conexão:</strong><br>';
            echo '<pre>' . htmlspecialchars($e->getMessage()) . '</pre>';
        }
        ?>
    </div>

    <!-- TESTE 4: API PRODUCTS -->
    <h2>4. Testar API de Produtos</h2>
    <div class="box">
        <button class="btn" onclick="testAPI()">🚀 Testar API</button>
        <div id="api-result" style="margin-top: 10px;"></div>
    </div>

    <!-- TESTE 5: ESTRUTURA DE PASTAS -->
    <h2>5. Verificar Estrutura de Pastas</h2>
    <div class="box">
        <?php
        $folders = [
            '/app' => 'Dashboard',
            '/api' => 'APIs',
            '/assets/css/app' => 'CSS',
            '/assets/js' => 'JavaScript',
            '/config' => 'Configuração'
        ];
        
        foreach ($folders as $folder => $name) {
            $exists = is_dir(__DIR__ . $folder);
            echo ($exists ? '✅' : '❌') . " <strong>$name:</strong> " . __DIR__ . $folder . "<br>";
        }
        ?>
    </div>

    <!-- TESTE 6: ARQUIVOS IMPORTANTES -->
    <h2>6. Verificar Arquivos Importantes</h2>
    <div class="box">
        <?php
        $files = [
            '/app/index.php' => 'Dashboard',
            '/api/products.php' => 'API Produtos',
            '/api/orders.php' => 'API Pedidos',
            '/api/categories.php' => 'API Categorias',
            '/login.php' => 'Login',
            '/logout.php' => 'Logout',
            '/assets/css/app/app.css' => 'CSS',
            '/assets/js/app.js' => 'JavaScript'
        ];
        
        foreach ($files as $file => $name) {
            $exists = file_exists(__DIR__ . $file);
            echo ($exists ? '✅' : '❌') . " <strong>$name:</strong> $file<br>";
        }
        ?>
    </div>

    <script>
        async function testAPI() {
            const result = document.getElementById('api-result');
            result.innerHTML = '⏳ Testando...';
            
            try {
                const response = await fetch('/rochas/api/products.php');
                const data = await response.json();
                
                result.innerHTML = `
                    <strong>Status:</strong> ${response.status} ${response.statusText}<br>
                    <strong>Resposta:</strong><br>
                    <pre>${JSON.stringify(data, null, 2)}</pre>
                `;
                
                result.className = response.ok ? 'box success' : 'box error';
            } catch (error) {
                result.innerHTML = `
                    ❌ <strong>Erro:</strong> ${error.message}<br>
                    <em>Provavelmente a API está retornando HTML em vez de JSON</em>
                `;
                result.className = 'box error';
            }
        }
    </script>

    <div style="margin-top: 50px; padding: 20px; background: #2d2d2d; border-radius: 5px;">
        <h3>📋 Próximos Passos:</h3>
        <ol>
            <li>Verifique todos os itens acima</li>
            <li>Se a sessão não estiver OK, clique em "Criar Sessão de Teste"</li>
            <li>Se houver erro na conexão, verifique <code>/config/database.php</code></li>
            <li>Clique em "Testar API" para ver a resposta da API</li>
            <li>Se tudo estiver OK, acesse <a href="/rochas/app/" style="color: #4ec9b0;">/rochas/app/</a></li>
        </ol>
    </div>
</body>
</html>