<?php
/**
 * VERIFICADOR DE ROTAS - Debug
 * Este arquivo ajuda a entender como o sistema de rotas está configurado
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require __DIR__ . '/../../vendor/autoload.php';

echo "<h1>🔍 Verificador de Rotas</h1>";
echo "<hr>";

// Carregar .env se existir
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
    $dotenv->load();
} catch (Exception $e) {
    echo "⚠️ .env não carregado<br>";
}

echo "<h2>1. Verificando Router</h2>";

// Verificar se existe arquivo de rotas
$possible_route_files = [
    __DIR__ . '/../../index.php',
    __DIR__ . '/../../routes.php',
    __DIR__ . '/../../source/Boot.php',
    __DIR__ . '/../../source/Routes.php',
];

echo "<h3>Arquivos de rota possíveis:</h3>";
foreach ($possible_route_files as $file) {
    if (file_exists($file)) {
        echo "✅ Encontrado: " . basename($file) . "<br>";
        echo "<details><summary>Ver conteúdo</summary>";
        echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
        echo htmlspecialchars(file_get_contents($file));
        echo "</pre></details><br>";
    } else {
        echo "❌ Não encontrado: " . basename($file) . "<br>";
    }
}

echo "<h2>2. Informações da Sessão</h2>";
session_start();
if (isset($_SESSION['usuario_id'])) {
    echo "✅ Usuário logado:<br>";
    echo "<ul>";
    echo "<li>ID: " . $_SESSION['usuario_id'] . "</li>";
    echo "<li>Nome: " . $_SESSION['usuario_nome'] . "</li>";
    echo "<li>Email: " . $_SESSION['usuario_email'] . "</li>";
    echo "<li>Nível: " . $_SESSION['nivel'] . "</li>";
    echo "</ul>";
} else {
    echo "❌ Nenhum usuário logado na sessão<br>";
}

echo "<h2>3. Verificando Estrutura de Pastas /views/</h2>";
$views_dir = __DIR__ . '/..';
if (is_dir($views_dir)) {
    echo "✅ Pasta /views/ existe<br>";
    echo "<h3>Subpastas encontradas:</h3>";
    $dirs = scandir($views_dir);
    echo "<ul>";
    foreach ($dirs as $dir) {
        if ($dir != '.' && $dir != '..' && is_dir($views_dir . '/' . $dir)) {
            echo "<li><strong>$dir/</strong>";
            
            // Listar arquivos principais dentro da subpasta
            $subfiles = scandir($views_dir . '/' . $dir);
            $php_files = array_filter($subfiles, function($f) use ($views_dir, $dir) {
                return pathinfo($f, PATHINFO_EXTENSION) === 'php' && is_file($views_dir . '/' . $dir . '/' . $f);
            });
            
            if (!empty($php_files)) {
                echo "<ul>";
                foreach ($php_files as $file) {
                    echo "<li>$file</li>";
                }
                echo "</ul>";
            }
            echo "</li>";
        }
    }
    echo "</ul>";
}

echo "<h2>4. Possíveis URLs de Acesso</h2>";
echo "<p>Com base na estrutura, estas são as possíveis URLs:</p>";
echo "<ul>";
echo "<li><a href='/rochas/views/app/index.php' target='_blank'>/rochas/views/app/index.php</a></li>";
echo "<li><a href='/rochas/views/app/' target='_blank'>/rochas/views/app/</a></li>";
echo "<li><a href='/rochas/app' target='_blank'>/rochas/app</a> (via Router)</li>";
echo "<li><a href='/rochas/admin' target='_blank'>/rochas/admin</a> (via Router)</li>";
echo "<li><a href='/rochas/' target='_blank'>/rochas/</a> (Raiz)</li>";
echo "</ul>";

echo "<h2>5. Verificar arquivo index.php da raiz</h2>";
$root_index = __DIR__ . '/../../index.php';
if (file_exists($root_index)) {
    echo "✅ /rochas/index.php existe<br>";
    echo "<details><summary>Ver conteúdo (primeiras 50 linhas)</summary>";
    echo "<pre style='background: #f0f0f0; padding: 10px; overflow-x: auto;'>";
    $lines = file($root_index);
    echo htmlspecialchars(implode('', array_slice($lines, 0, 50)));
    echo "</pre></details>";
} else {
    echo "❌ /rochas/index.php não existe<br>";
}

echo "<h2>6. Verificar .htaccess</h2>";
$htaccess = __DIR__ . '/../../.htaccess';
if (file_exists($htaccess)) {
    echo "✅ /.htaccess existe<br>";
    echo "<details><summary>Ver conteúdo</summary>";
    echo "<pre style='background: #f0f0f0; padding: 10px;'>";
    echo htmlspecialchars(file_get_contents($htaccess));
    echo "</pre></details>";
} else {
    echo "❌ /.htaccess não existe<br>";
    echo "<p>Isso pode ser o problema! O Router precisa de um .htaccess</p>";
}

echo "<hr>";
echo "<h2>💡 Diagnóstico</h2>";
echo "<p>Com base nas informações acima, o problema pode ser:</p>";
echo "<ol>";
echo "<li>Rota '/app' não está configurada no Router</li>";
echo "<li>Arquivo .htaccess está faltando ou mal configurado</li>";
echo "<li>O redirecionamento está indo para uma rota que não existe</li>";
echo "</ol>";

?>

<style>
    body {
        font-family: Arial, sans-serif;
        margin: 20px;
        line-height: 1.6;
    }
    h1 { color: #333; }
    h2 { 
        color: #666; 
        margin-top: 30px;
        border-bottom: 2px solid #ddd;
        padding-bottom: 10px;
    }
    details {
        margin: 10px 0;
        padding: 10px;
        background: #f9f9f9;
        border-left: 3px solid #007bff;
    }
    summary {
        cursor: pointer;
        font-weight: bold;
        color: #007bff;
    }
    pre {
        font-size: 12px;
        max-height: 400px;
        overflow-y: auto;
    }
</style>
