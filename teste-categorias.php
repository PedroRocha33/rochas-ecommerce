<?php
// ============================================
// TESTE DE CATEGORIAS - DESCUBRA O PROBLEMA
// ============================================

session_start();

// ✅ MÚLTIPLOS CAMINHOS POSSÍVEIS PARA O AUTOLOAD
$possiblePaths = [
    __DIR__ . '/../../vendor/autoload.php',
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/vendor/autoload.php',
    dirname(__DIR__, 2) . '/vendor/autoload.php',
    dirname(__DIR__) . '/vendor/autoload.php',
];

$autoloadFound = false;
foreach ($possiblePaths as $path) {
    if (file_exists($path)) {
        require $path;
        $autoloadFound = true;
        break;
    }
}

if (!$autoloadFound) {
    die("❌ ERRO: Não foi possível encontrar o arquivo vendor/autoload.php<br><br>Caminhos testados:<br>" . implode('<br>', $possiblePaths));
}

use Source\Core\Connect;

echo "<h1>🔍 Teste de Categorias</h1>";
echo "<style>
    body { font-family: Arial; padding: 20px; background: #f5f5f5; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    .warning { color: orange; font-weight: bold; }
    .info { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 5px; }
    table { border-collapse: collapse; width: 100%; background: white; margin: 10px 0; }
    th, td { border: 1px solid #ddd; padding: 12px; text-align: left; }
    th { background: #4CAF50; color: white; }
    pre { background: #f5f5f5; padding: 10px; border-radius: 5px; overflow-x: auto; }
    .code-block { background: #263238; color: #aed581; padding: 15px; border-radius: 5px; margin: 10px 0; }
</style>";

try {
    $pdo = Connect::getInstance();
    echo "<p class='success'>✅ Conexão com banco OK!</p>";
    
    // ========================================
    // TESTE 1: Ver estrutura da tabela
    // ========================================
    echo "<h2>📋 TESTE 1: Estrutura da tabela 'categories'</h2>";
    
    $stmt = $pdo->query("SHOW COLUMNS FROM categories");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table>";
    echo "<tr><th>Campo</th><th>Tipo</th><th>Permite NULL</th><th>Valor Padrão</th></tr>";
    foreach ($columns as $col) {
        echo "<tr>";
        echo "<td><strong>" . $col['Field'] . "</strong></td>";
        echo "<td>" . $col['Type'] . "</td>";
        echo "<td>" . $col['Null'] . "</td>";
        echo "<td>" . ($col['Default'] ?? 'NULL') . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // ========================================
    // TESTE 2: Buscar TODAS as categorias (sem filtro)
    // ========================================
    echo "<h2>📦 TESTE 2: TODAS as categorias (sem filtro)</h2>";
    
    $stmtAll = $pdo->query("SELECT * FROM categories");
    $allCategories = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<div class='info'>";
    echo "<strong>Total de categorias no banco:</strong> " . count($allCategories);
    echo "</div>";
    
    if (count($allCategories) > 0) {
        echo "<table>";
        $firstRow = true;
        foreach ($allCategories as $cat) {
            if ($firstRow) {
                echo "<tr>";
                foreach (array_keys($cat) as $key) {
                    echo "<th>" . htmlspecialchars($key) . "</th>";
                }
                echo "</tr>";
                $firstRow = false;
            }
            
            echo "<tr>";
            foreach ($cat as $value) {
                $displayValue = $value ?? 'NULL';
                // Destacar valores importantes
                if ($value === '1' || $value === 1) {
                    $displayValue = "<span class='success'>✓ " . $value . "</span>";
                } elseif ($value === '0' || $value === 0) {
                    $displayValue = "<span class='error'>✗ " . $value . "</span>";
                }
                echo "<td>" . $displayValue . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p class='error'>❌ Nenhuma categoria encontrada!</p>";
    }
    
    // ========================================
    // TESTE 3: Detectar campo de status
    // ========================================
    echo "<h2>🔍 TESTE 3: Detectar campo de status</h2>";
    
    $statusFields = ['ativo', 'ativa', 'active', 'status', 'enabled', 'visivel'];
    $detectedField = null;
    
    foreach ($columns as $col) {
        if (in_array(strtolower($col['Field']), $statusFields)) {
            $detectedField = $col['Field'];
            echo "<p class='success'>✅ Campo de status detectado: <strong>{$detectedField}</strong></p>";
            break;
        }
    }
    
    if (!$detectedField) {
        echo "<p class='warning'>⚠️ Nenhum campo de status detectado automaticamente</p>";
    }
    
    // ========================================
    // TESTE 4: Testar queries com diferentes campos
    // ========================================
    echo "<h2>🧪 TESTE 4: Testando queries</h2>";
    
    $queries = [
        'ativo = 1' => "SELECT * FROM categories WHERE ativo = 1",
        'ativa = 1' => "SELECT * FROM categories WHERE ativa = 1",
        'active = 1' => "SELECT * FROM categories WHERE active = 1",
        "status = 'ativo'" => "SELECT * FROM categories WHERE status = 'ativo'",
        'sem filtro' => "SELECT * FROM categories"
    ];
    
    $workingQuery = null;
    $workingCount = 0;
    
    foreach ($queries as $label => $query) {
        try {
            $testStmt = $pdo->query($query);
            $results = $testStmt->fetchAll(PDO::FETCH_ASSOC);
            $count = count($results);
            
            if ($count > 0) {
                echo "<p class='success'>✅ <strong>{$label}</strong>: {$count} categoria(s) encontrada(s)</p>";
                if ($count > $workingCount) {
                    $workingQuery = $query;
                    $workingCount = $count;
                }
            } else {
                echo "<p class='warning'>⚠️ <strong>{$label}</strong>: Nenhuma categoria encontrada</p>";
            }
            
        } catch (Exception $e) {
            echo "<p class='error'>❌ <strong>{$label}</strong>: ERRO - " . $e->getMessage() . "</p>";
        }
    }
    
    // ========================================
    // SOLUÇÃO FINAL
    // ========================================
    echo "<h2>💡 SOLUÇÃO RECOMENDADA</h2>";
    echo "<div class='info'>";
    
    if ($workingQuery) {
        echo "<h3>✅ Use esta query no seu index.php:</h3>";
        echo "<div class='code-block'>";
        echo "\$stmtCategorias = \$pdo->query(\"<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;SELECT id, nome, slug<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;FROM categories<br>";
        
        // Extrair a parte WHERE da query que funcionou
        if (strpos($workingQuery, 'WHERE') !== false) {
            $wherePart = substr($workingQuery, strpos($workingQuery, 'WHERE'));
            echo "&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars($wherePart) . "<br>";
        }
        
        echo "&nbsp;&nbsp;&nbsp;&nbsp;ORDER BY nome ASC<br>";
        echo "\");<br>";
        echo "</div>";
        
        echo "<p><strong>Categorias que serão exibidas:</strong> {$workingCount}</p>";
        
    } else {
        echo "<p class='error'>❌ Nenhuma query funcionou!</p>";
        echo "<p>Verifique:</p>";
        echo "<ul>";
        echo "<li>Se há categorias cadastradas no banco</li>";
        echo "<li>Se a tabela 'categories' existe</li>";
        echo "<li>Se o campo 'slug' existe (caso contrário, remova-o da query)</li>";
        echo "</ul>";
    }
    
    echo "</div>";
    
    // ========================================
    // CÓDIGO COMPLETO PARA COPIAR
    // ========================================
    if ($workingQuery && $workingCount > 0) {
        echo "<h2>📝 CÓDIGO COMPLETO PARA SEU index.php</h2>";
        echo "<div class='code-block'>";
        echo "// Buscar categorias<br>";
        echo "\$stmtCategorias = \$pdo->query(\"<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;SELECT id, nome, slug<br>";
        echo "&nbsp;&nbsp;&nbsp;&nbsp;FROM categories<br>";
        
        if (strpos($workingQuery, 'WHERE') !== false) {
            $wherePart = substr($workingQuery, strpos($workingQuery, 'WHERE'));
            echo "&nbsp;&nbsp;&nbsp;&nbsp;" . htmlspecialchars($wherePart) . "<br>";
        }
        
        echo "&nbsp;&nbsp;&nbsp;&nbsp;ORDER BY nome ASC<br>";
        echo "\");<br><br>";
        echo "\$categorias = \$stmtCategorias->fetchAll(PDO::FETCH_OBJ);<br>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<p class='error'>❌ ERRO FATAL: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<hr>";
echo "<p><a href='index.php' style='background: #4CAF50; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>← Voltar para a loja</a></p>";
?>