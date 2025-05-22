<?php
$dsn = 'mysql:host=localhost;dbname=lb_pdo_goods;charset=utf8';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Помилка підключення: " . $e->getMessage());
}
?>
