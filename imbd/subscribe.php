<?php
session_start();
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#newsletter');
    exit;
}

$email = trim($_POST['email'] ?? '');

if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: index.php#newsletter&sub=invalid');
    exit;
}

try {
    // Check if already subscribed
    $stmt = $pdo->prepare("SELECT id FROM subscribers WHERE email = ?");
    $stmt->execute([$email]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        header('Location: index.php#newsletter&sub=exists');
        exit;
    }

    // Insert new subscriber
    $stmt = $pdo->prepare("INSERT INTO subscribers (email) VALUES (?)");
    $stmt->execute([$email]);

    header('Location: index.php#newsletter&sub=ok');
    exit;

} catch (Exception $e) {
    // You could log the error message here if needed
    header('Location: index.php#newsletter&sub=fail');
    exit;
}
