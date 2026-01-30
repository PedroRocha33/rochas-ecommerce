<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "1. Teste iniciado<br>";
use Source\Core\Connect;
// Teste 1: Autoload
try {
    require __DIR__ . '/../../vendor/autoload.php';
    echo "2. Autoload OK<br>";
} catch (Exception $e) {
    die("ERRO Autoload: " . $e->getMessage());
}

// Teste 2: Dotenv
try {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
    $dotenv->load();
    echo "3. Dotenv OK<br>";
} catch (Exception $e) {
    die("ERRO Dotenv: " . $e->getMessage());
}

// Teste 3: Token
if (!isset($_ENV['MP_ACCESS_TOKEN']) || empty($_ENV['MP_ACCESS_TOKEN'])) {
    die("ERRO: MP_ACCESS_TOKEN não encontrado no .env");
}
echo "4. Token encontrado: " . substr($_ENV['MP_ACCESS_TOKEN'], 0, 20) . "...<br>";

// Teste 4: Conexão com banco
try {
    
    $pdo = Connect::getInstance();
    echo "5. Conexão com banco OK<br>";
} catch (Exception $e) {
    echo "5. ⚠️ Aviso banco: " . $e->getMessage() . "<br>";
}

// Teste 5: URLs de retorno - ✅ CORRIGIDO PARA _checkout
echo "6. Verificando se as páginas de retorno existem...<br>";

$baseUrl = 'http://localhost/rochas';
$pages = [
    'success' => '/_checkout/success.php',
    'error' => '/_checkout/error.php', 
    'pending' => '/_checkout/pending.php'
];

$allPagesExist = true;
foreach ($pages as $name => $path) {
    $fullUrl = $baseUrl . $path;
    $ch = curl_init($fullUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_NOBODY, true);
    curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        echo "   ✅ $name: <a href='$fullUrl' target='_blank'>$fullUrl</a><br>";
    } else {
        echo "   ❌ $name: $fullUrl (HTTP $httpCode - página não encontrada)<br>";
        $allPagesExist = false;
    }
}

if (!$allPagesExist) {
    echo "<br><strong style='color: red;'>⚠️ ATENÇÃO: Algumas páginas não existem!</strong><br>";
    echo "Certifique-se de criar os arquivos na pasta <code>/rochas/_checkout/</code><br><br>";
}

// Teste 6: Criar preferência no MP
$pedidoId = time();

$testData = [
    'items' => [
        [
            'title' => 'Produto Teste',
            'quantity' => 1,
            'unit_price' => 100.00,
            'currency_id' => 'BRL'
        ]
    ],
    'external_reference' => (string) $pedidoId,
    'back_urls' => [
        'success' => $baseUrl . '/_checkout/success.php?pedido_id=' . $pedidoId,
        'failure' => $baseUrl . '/_checkout/error.php?pedido_id=' . $pedidoId,
        'pending' => $baseUrl . '/_checkout/pending.php?pedido_id=' . $pedidoId
    ],
    'auto_return' => 'approved',
    'statement_descriptor' => 'ROCHAS'
];

echo "<br>7. Enviando para API do Mercado Pago...<br>";
echo "<details><summary>Ver dados enviados</summary><pre>" . json_encode($testData, JSON_PRETTY_PRINT) . "</pre></details>";

$ch = curl_init('https://api.mercadopago.com/checkout/preferences');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($testData),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $_ENV['MP_ACCESS_TOKEN'],
        'Content-Type: application/json',
        'X-Idempotency-Key: test_' . uniqid()
    ],
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "8. Resposta HTTP: <strong style='font-size: 20px;'>$httpCode</strong><br><br>";

if ($curlError) {
    die("<h2 style='color: red;'>❌ ERRO cURL: $curlError</h2>");
}

if ($httpCode === 201 || $httpCode === 200) {
    $preference = json_decode($response, true);
    echo "<h2 style='color: green;'>✅ SUCESSO! Preferência criada!</h2>";
    echo "<div style='background: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<strong>Init Point:</strong><br>";
    echo "<a href='" . $preference['init_point'] . "' target='_blank' style='font-size: 16px;'>" . $preference['init_point'] . "</a><br><br>";
    echo "<strong>Preference ID:</strong> " . $preference['id'] . "<br>";
    echo "</div>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; border-left: 4px solid #ffc107;'>";
    echo "<strong>🎉 Tudo funcionando!</strong><br>";
    echo "O checkout está pronto para uso. Teste fazendo uma compra real.";
    echo "</div>";
    
    echo "<br><details><summary>Ver resposta completa da API</summary>";
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px; overflow: auto;'>" . json_encode($preference, JSON_PRETTY_PRINT) . "</pre>";
    echo "</details>";
    
} else {
    echo "<h2 style='color: red;'>❌ ERRO na API do Mercado Pago</h2>";
    $errorDetail = json_decode($response, true);
    
    echo "<div style='background: #ffebee; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<strong>HTTP Code:</strong> $httpCode<br>";
    
    if (isset($errorDetail['message'])) {
        echo "<strong>Mensagem:</strong> " . $errorDetail['message'] . "<br>";
    }
    
    if (isset($errorDetail['cause'])) {
        echo "<br><strong>Causa:</strong><br>";
        echo "<pre>" . print_r($errorDetail['cause'], true) . "</pre>";
    }
    echo "</div>";
    
    echo "<details><summary>Ver resposta completa</summary>";
    echo "<pre style='background: #fff3f3; padding: 15px; border-radius: 5px;'>" . print_r($errorDetail, true) . "</pre>";
    echo "</details>";
    
    echo "<br><div style='background: #e3f2fd; padding: 15px; border-radius: 8px;'>";
    echo "<h3>🔧 Possíveis soluções:</h3>";
    echo "<ul>";
    
    if ($httpCode === 400) {
        echo "<li>Verifique se as páginas em /_checkout/ existem e estão acessíveis</li>";
        echo "<li>Teste acessando: <a href='$baseUrl/_checkout/success.php' target='_blank'>$baseUrl/_checkout/success.php</a></li>";
        echo "<li>As URLs devem retornar HTTP 200 (não 404)</li>";
    } elseif ($httpCode === 401) {
        echo "<li>Token do MP inválido/expirado</li>";
        echo "<li>Gere novo token em: <a href='https://www.mercadopago.com.br/developers/panel' target='_blank'>Painel do MP</a></li>";
    } else {
        echo "<li>Erro inesperado. Verifique os logs do PHP</li>";
    }
    
    echo "</ul></div>";
}
?>

<style>
    body { 
        font-family: Arial, sans-serif; 
        padding: 20px; 
        max-width: 900px; 
        margin: 0 auto;
        background: #f5f5f5;
    }
    details { 
        margin: 10px 0; 
        cursor: pointer; 
    }
    summary { 
        padding: 10px; 
        background: #e0e0e0; 
        border-radius: 5px; 
    }
    summary:hover { 
        background: #d0d0d0; 
    }
    code {
        background: #f5f5f5;
        padding: 2px 6px;
        border-radius: 3px;
        font-family: 'Courier New', monospace;
    }
</style>