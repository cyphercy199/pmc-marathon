.<?php
// ... (same connection parameters as above)

// Get username and password from POST
$username = $_POST['username'] ?? '';
$password = $_POST['password'] ?? '';

try {
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $stmt = $pdo->prepare("SELECT password FROM public.users WHERE username = :username");
    $stmt->execute(['username' => $username]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row && password_verify($password, $row['password'])) {
        echo "Login successful!";
        // (Set session, redirect, etc.)
    } else {
        echo "Invalid username or password.";
    }
} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage();
}
?>