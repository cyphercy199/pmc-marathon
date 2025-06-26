<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require 'db_connection.php';
session_start();

$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

if (!$username || !$password) {
    header("Location: index.php?error=1&user=" . urlencode($username));
    exit;
}

// Check admin credentials in users table
$stmt = $pdo->prepare("SELECT * FROM public.users WHERE username = :username AND password = :password LIMIT 1");
$stmt->execute(['username' => $username, 'password' => $password]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    $_SESSION['username'] = $user['username'];
    header("Location: admin_dashboard.php");
    exit;
} else {
    header("Location: index.php?error=1&user=" . urlencode($username));
    exit;
}
?>