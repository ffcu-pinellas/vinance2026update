<?php
namespace App\Services;

use Ratchet\Client\WebSocket;
use React\EventLoop\LoopInterface;

class TradeWebSocket
{
    private static $instance;
    private $connection;
    private $prices = [];
    private $loop;

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->initWebSocket();
    }

    private function initWebSocket()
    {
        $this->loop = \React\EventLoop\Factory::create();
        
        \Ratchet\Client\connect('wss://ws-api.kucoin.com/endpoint', [], [], $this->loop)->then(
            function(WebSocket $conn) {
                $this->connection = $conn;
                
                // Subscribe to all tickers
                $conn->send(json_encode([
                    'type' => 'subscribe',
                    'topic' => '/market/ticker:all',
                    'response' => true
                ]));
                
                $conn->on('message', function($msg) {
                    $data = json_decode($msg, true);
                    if (isset($data['topic']) && str_contains($data['topic'], '/market/ticker:')) {
                        $symbol = str_replace('/market/ticker:', '', $data['topic']);
                        $this->prices[$symbol] = $data['data']['price'];
                    }
                });
                
                $conn->on('close', function() {
                    $this->reconnect();
                });
            },
            function($e) {
                // Failed connection
            }
        );
        
        // Start the loop in background
        $this->startBackgroundLoop();
    }

    private function startBackgroundLoop()
    {
        ignore_user_abort(true);
        set_time_limit(0);
        
        if (function_exists('fastcgi_finish_request')) {
            session_write_close();
            fastcgi_finish_request();
        }
        
        $this->loop->run();
    }

    private function reconnect()
    {
        sleep(5);
        $this->initWebSocket();
    }

    public function getPrice($symbol)
    {
        $cleanSymbol = strtolower(str_replace(['-', '_'], '', $symbol));
        return $this->prices[$cleanSymbol] ?? null;
    }
}