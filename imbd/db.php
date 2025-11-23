<?php
$host = 'localhost';
$port = '3306';     // MAMP MySQL default port
$dbname = 'imbd_db';
$username = 'root'; // default for MAMP
$password = 'root'; // default for MAMP

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>
