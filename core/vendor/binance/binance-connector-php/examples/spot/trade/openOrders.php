<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

$key = 'ubnfbtmSyPzkOOatBIVrR44h8rp8v8UgJFcLpWr7Gc1zo7KjImEjNmL5bm0nzPc2';
$secret = 'quZmVHWxmiBuS1bXqmg0H9tVP8gblEaAirOwVIjIG6IYYAvMqNWfEQ4B3fuck3FI';

$client = new \Binance\Spot([
    'key'  => $key,
    'secret'  => $secret
]);

$response = $client->openOrders(
    [
        'symbol' => 'BNBUSDT',
        'recvWindow' => 5000
    ]
);

echo json_encode($response);
