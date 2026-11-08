<?php
declare(strict_types=1);

$host = '127.0.0.1';
$dbname = 'back2you_db';
$username = 'root';
$password = ''; // Default XAMPP password. Set this if your MySQL root has a password.

try {
    $pdo = new PDO(
        "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ]
    );
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Check config/database.php and make sure MySQL is running.');
}
