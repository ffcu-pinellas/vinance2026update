<?php
$host = "127.0.0.1:3306"; // Keep this unless Hostinger specifies a different host
$dbname = "u664663598_vinance";
$username = "u664663598_vinance009";
$password = "Messenger@009"; // Manually enter your password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
?>
