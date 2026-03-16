<?php
/**
 * /wedding/php/db.php
 * PDO database connection for the wedding site.
 * Update credentials below before deployment.
 */

define('DB_HOST', 'localhost');
define('DB_NAME', 'u565780247_wedding');
define('DB_USER', 'u565780247_groom');
define('DB_PASS', 'iLOVEmyWIFE10-09-2027');
define('DB_CHAR', 'utf8mb4');

$dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHAR;

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    // Don't expose DB details to the user
    error_log("DB Connection failed: " . $e->getMessage());
    http_response_code(500);
    die("A database error occurred. Please try again later.");
}
