<?php
require __DIR__ . '/../../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

// ✅ Carregar variáveis de ambiente
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if (!isset($_ENV['MP_ACCESS_TOKEN']) || empty($_ENV['MP_ACCESS_TOKEN'])) {
    http_response_code(500);
    echo json_encode(['error' => 'MP_ACCESS_TOKEN não configurado']);
    exit;
}

SDK::setAccessToken($_ENV['MP_ACCESS_TOKEN']);

// ✅ CONFIGURAR CURL PARA IGNORAR SSL EM DESENVOLVIMENTO
MercadoPago\SDK::setHttpClient(new \MercadoPago\Http\CurlClient([
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false
]));

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Dados inválidos']);
    exit;
}

try {
    $preference = new Preference();

    $items = [];

    foreach ($data['itens'] as $p) {
        $item = new Item();
        $item->title = $p['nome'];
        $item->quantity = $p['quantidade'];
        $item->unit_price = (float)$p['preco'];
        $items[] = $item;
    }

    $preference->items = $items;
    $preference->external_reference = (string) $data['pedido_id'];
    $preference->back_urls = [
        "success" => "http://localhost/rochas/checkout/success",
        "failure" => "http://localhost/rochas/checkout/failure",
        "pending" => "http://localhost/rochas/checkout/pending"
    ];
    $preference->auto_return = "approved";
    $preference->notification_url = "http://localhost/rochas/api/mercadopago/webhook.php";

    $preference->save();

    echo json_encode([
        "success" => true,
        "init_point" => $preference->init_point
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage()
    ]);
}