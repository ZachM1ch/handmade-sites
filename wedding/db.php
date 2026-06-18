<?php
// Wedding database connection via PDO
// Update credentials to match your server configuration

define('DB_HOST', 'localhost');
define('DB_NAME', 'u565780247_wedding');
define('DB_USER', 'u565780247_groom');
define('DB_PASS', 'groomOn10092027!!');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER,
        DB_PASS,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}
