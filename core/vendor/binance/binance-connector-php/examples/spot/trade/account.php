<?php

require_once __DIR__ . '/../../../vendor/autoload.php';

# Hmac Authentication
$key = "ubnfbtmSyPzkOOatBIVrR44h8rp8v8UgJFcLpWr7Gc1zo7KjImEjNmL5bm0nzPc2";
$secret = "quZmVHWxmiBuS1bXqmg0H9tVP8gblEaAirOwVIjIG6IYYAvMqNWfEQ4B3fuck3FI";
$client = new \Binance\Spot([
    'key'  => $key,
    'secret'  => $secret,
    'baseURL' => 'https://testnet.binance.vision'
]);


# RSA Key(Unencrypted) Authentication
$key = ''; # api key is also required
$privateKey = 'file:///path/to/rsa/private/key.pem';

$client = new \Binance\Spot([
    'key'  => $key,
    'privateKey'  => $privateKey, # pass the key file directly
    'baseURL' => 'https://testnet.binance.vision'
]);


# RSA key(Encrypted) Authentication
$key = '';
$encryptedPrivateKey = 'file:///path/to/rsa/private/key.pem';
$privateKey = openssl_pkey_get_private($encryptedPrivateKey, 'password');

$client = new \Binance\Spot([
    'key'  => $key,
    'privateKey'  => $privateKey,
    'baseURL' => 'https://testnet.binance.vision'
]);

$response = $client->account();

echo json_encode($response);
