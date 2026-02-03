<?php
require __DIR__ . '/../../vendor/autoload.php';

use MercadoPago\SDK;
use MercadoPago\Preference;
use MercadoPago\Item;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

if (!isset($_ENV['MP_ACCESS_TOKEN']) || empty($_ENV['MP_ACCESS_TOKEN'])) {
    http_response_code(500);
    echo json_encode(['error' => 'MP_ACCESS_TOKEN não configurado']);
    exit;
}

SDK::setAccessToken($_ENV['MP_ACCESS_TOKEN']);

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

    // ✅ Adicionar produtos
    foreach ($data['itens'] as $p) {
        $item = new Item();
        $item->title = $p['nome'];
        $item->quantity = $p['quantidade'];
        $item->unit_price = (float)$p['preco'];
        $items[] = $item;
    }

    // ✅ ADICIONAR FRETE COMO ITEM (igual ao create.php)
    if (!empty($data['frete']) && $data['frete'] > 0) {
        $freteItem = new Item();
        $freteItem->title = 'Frete';
        $freteItem->quantity = 1;
        $freteItem->unit_price = (float)$data['frete'];
        $items[] = $freteItem;
    }

    $preference->items = $items;
    $preference->external_reference = (string) $data['pedido_id'];
    $preference->back_urls = [
        "success" => "http://localhost/rochas/_checkout/success",
        "failure" => "http://localhost/rochas/_checkout/failure",
        "pending" => "http://localhost/rochas/_checkout/pending"
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