<?php
$host = '127.0.0.1';
$port = '2134';
$user = 'postgres';
$pass = 'cikiwir123';
$db = 'postgres';

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$db", $user, $pass);
    $pdo->exec("CREATE DATABASE approval_sistem_test");
    echo "Database created successfully\n";
} catch (PDOException $e) {
    if (strpos($e->getMessage(), 'already exists') !== false) {
        echo "Database already exists\n";
    } else {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
