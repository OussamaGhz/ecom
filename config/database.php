<?php
$host = 'localhost';
$db_name = 'ecommerce';
$username = 'oussama';
$password = '1107';

try {
    $conn = new PDO("mysql:host=$host;dbname=$db_name", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("USE ecommerce");
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
