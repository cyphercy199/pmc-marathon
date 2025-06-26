<?php
// Database connection parameters
$host = 'localhost';
$db   = 'postgres';
$user = 'postgres';
$pass = '05152578Ac!@#$';
$dsn = "pgsql:host=$host;dbname=$db;";

try {
    // Create a PDO connection and export as $pdo
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (PDOException $e) {
    // Handle connection error
    die("Database connection failed: " . $e->getMessage());
}
?>