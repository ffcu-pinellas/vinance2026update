<?php
// Set error reporting for debugging
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Define the secret key for security
$secretKey = "uXN1gBq5iLc4XbS2PJQcR7wLMu7d5zH4eU2ukAvPVASgRK2yb43XSrrGhbyeqUC0xnyTH7ELErz4EvciVXgg8NyUzBz13k0uX2H6";

// Check if the script is being run directly with the correct key
if (isset($_GET['key']) && $_GET['key'] === $secretKey) {
    // Define the base path to your Laravel application
    // Adjust this path according to your actual directory structure
    define('LARAVEL_BASE_PATH', __DIR__ . '/../../');
    
    // Include the autoloader
    require LARAVEL_BASE_PATH . 'vendor/autoload.php';
    
    // Bootstrap Laravel
    $app = require_once LARAVEL_BASE_PATH . 'bootstrap/app.php';
    
    // Get the HTTP kernel instance
    $kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
    
    // Create a request to process
    $request = Illuminate\Http\Request::create('/update-rewards-internal', 'GET');
    
    // Handle the request
    $response = $kernel->handle($request);
    
    // Output the response
    echo $response->getContent();
    
    // Terminate the request
    $kernel->terminate($request, $response);
} else {
    // If accessed without the correct key or directly from a browser
    header('HTTP/1.0 403 Forbidden');
    echo 'Access denied';
}