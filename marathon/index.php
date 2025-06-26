<?php
session_start();
if (isset($_SESSION['username']) && $_SESSION['username'] === 'admin') {
    header('Location: admin_dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Login - PMC Marathon 2025</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --primary: #2563eb;
            --primary-dark: #1e40af;
            --error: #ef4444;
            --bg: #f8fafc;
            --card-bg: #fff;
            --border: #e5e7eb;
            --red-gradient: linear-gradient(90deg, #ef4444 0%, #be123c 100%);
        }
        body { 
            background: var(--bg); 
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            margin: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
        }
        .header-bar {
            width: 100vw;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 10;
        }
        .header-gradient {
            width: 100%;
            background: var(--red-gradient);
            padding: 1.2rem 0;
            box-shadow: 0 2px 16px rgba(239, 68, 68, 0.12);
            display: flex;
            align-items: center;
            justify-content: flex-start;
        }
        .header-title {
            color: #fff;
            font-family: 'Inter', Arial, Helvetica, sans-serif;
            font-weight: 800;
            font-size: 2.3rem;
            letter-spacing: 1px;
            margin-left: 2.2rem;
            margin-top: 1px;
            margin-bottom: 1px;
        }

        .login-container {
            width: 100%;
            max-width: 350px;
            padding: 2.5rem 2rem 2rem 2rem;
            background: var(--card-bg);
            border-radius: 16px;
            box-shadow: 0 4px 32px rgba(0,0,0,0.10);
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            border: 1px solid var(--border);
            margin-top: 100px; /* Space below the fixed header */
        }
        .login-container h2 {
            margin: 0 0 0.5rem 0;
            font-weight: 700;
            letter-spacing: -1px;
            color: var(--primary-dark);
            text-align: center;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        /* Add more space between form groups */
        .form-group:not(:last-child) {
            margin-bottom: 1.2rem;
        }
        input[type="text"], input[type="password"] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid var(--border);
            border-radius: 6px;
            font-size: 1rem;
            background: #f1f5f9;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        input[type="text"]:focus, input[type="password"]:focus {
            border: 1.5px solid var(--primary);
            outline: none;
            background: #fff;
        }
        .login-actions {
            margin-top: 2rem; /* More space before the button */
            display: flex;
            justify-content: center;
            width: 100%;
        }
        input[type="submit"] {
            background: var(--primary);
            color: #fff;
            border: none;
            padding: 0.75rem 0;
            width: 80%;
            max-width: 200px;
            border-radius: 6px;
            font-weight: 600;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
            cursor: pointer;
            transition: background 0.2s;
            display: block;
            margin: 0 auto;
        }
        input[type="submit"]:hover, input[type="submit"]:focus {
            background: var(--primary-dark);
        }
        .error {
            background: #fee2e2;
            color: var(--error);
            border: 1px solid #fca5a5;
            border-radius: 5px;
            padding: 0.6em 1em;
            font-size: 0.98em;
            text-align: center;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        @media (max-width: 600px) {
            .header-title {
                font-size: 1.2rem;
                margin-left: 1rem;
            }
            .login-container {
                padding: 1.5rem 0.75rem;
                max-width: 95vw;
                margin-top: 70px;
            }
            .header-gradient {
                padding: 0.8rem 0;
            }
            input[type="submit"] {
                width: 100%;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <div class="header-bar">
        <div class="header-gradient">
            <span class="header-title">PMC Marathon 2025 Admin</span>
        </div>
    </div>
    <div class="login-container">
        <h2>Admin Sign In</h2>
        <?php
        if (isset($_GET['error'])) {
            $lastUser = isset($_GET['user']) ? htmlspecialchars($_GET['user']) : '';
            echo '<div class="error">Invalid username or password!';
            if ($lastUser) echo "<br>Attempted Username: <b>$lastUser</b>";
            echo '</div>';
        }
        ?>
        <form action="process_login.php" method="post" autocomplete="off">
            <div class="form-group">
                <input type="text" name="username" placeholder="Admin Username" required autofocus 
                       value="<?php echo isset($_GET['user']) ? htmlspecialchars($_GET['user']) : ''; ?>">
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Password" required>
            </div>
            <div class="login-actions">
                <input type="submit" value="Login">
            </div>
        </form>
    </div>
</body>
</html>