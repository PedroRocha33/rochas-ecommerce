<?php
header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    http_response_code(400);
    echo json_encode(['error' => 'JSON inválido']);
    exit;
}

if (empty($input['cep']) || empty($input['produtos'])) {
    http_response_code(400);
    echo json_encode(['error' => 'CEP ou produtos ausentes']);
    exit;
}

$payload = [
    'from' => ['postal_code' => '95840000'],
    'to' => ['postal_code' => $input['cep']],
    'products' => array_map(function ($p) {
        return [
            'id' => uniqid('prod_'),
            'width'  => (int)$p['largura'],
            'height' => (int)$p['altura'],
            'length' => (int)$p['comprimento'],
            'weight' => (float)$p['peso'],
            'quantity' => (int)$p['quantidade'],
            'insurance_value' => 10
        ];
    }, $input['produtos'])
];

$ch = curl_init('https://www.melhorenvio.com.br/api/v2/me/shipment/calculate');

curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
        'Content-Type: application/json',
        'Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiNmQ2M2M1MDgxNjIyZGQyMmUxZWMzMWQ1NzM1NjFhY2EwOTU1ZWY0ZTdhMzEyMzlmMWY3ZTA1Nzk2NDAwMmNkN2U4YWMyOGU2NjFlNmIwNTgiLCJpYXQiOjE3Njg1MjU3NTUuNDUyMjg4LCJuYmYiOjE3Njg1MjU3NTUuNDUyMjg5LCJleHAiOjE4MDAwNjE3NTUuNDQxNTgxLCJzdWIiOiJhMGQ4Mzc0Ni05MWExLTQ5MWUtYmE0NC03YmRiN2U5YTU0MWQiLCJzY29wZXMiOlsic2hpcHBpbmctY2FsY3VsYXRlIl19.USB_gK3sebGKJFxtLhSb1gKNrt2WVACzpRkSkD-1WBPLV52Wwbiug9uoDmhtncXOGGsd9cV5sT9EFrvzA7HMaMMPI_bub03briksVBzyPuuMgqo6SWRKjMnW9YvLwbQh-UHoFjyZ-lJ7o-AdCvDYCin8-dedebgzH2Y-khX-IugwruF8TB22UvqYYRvc9RNa9hYZjy4OSG3rJ66hKeQM16WCT1m8x4Tgh5AQBMHL4k74pfm1eHDD8vRI8gnTBZAgvLVtMqFRmqpXsJQVHBOWplWP-JPHc54hMWrpcDCKYCsbkNglC4qeIhM98HgomntF32H2QeCwD_1_QiehTU3oDuWpMlrpBhRDiIH5pQW0UCkjUztQi1mOiJ_kpvXwgQPPw7lSdSfpGCApGmxvu7AdYJrjaefs5yBQe7UaZObaCfEAb3tM1gghwCmhKtLMKG0c20JPXDFwe2sp0tmAu_CiBpIw_FUWzJ9c3B64uHg3xixUKVfuPDqHExjuCx6vKbrKw3W6FIrDsLyRyt45JaUQcTOpmhWSacMj5U7QqOHK_d_44JTW6m_NwP3phOSJaPqHyqnuz-JTRyctw1S15CQDoLZGArs3oSLZ1fzM3BEMdIYVj4SPzn5DmGzv4xvpqdrr-0nJPdcMfD4evQpLXAShvN5MsIqOoDV1YWYNmHEdMLk'
    ],
    CURLOPT_SSL_VERIFYPEER => false
]);

$response = curl_exec($ch);

if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
    exit;
}

curl_close($ch);

echo $response;
