<?php
require 'db_connection.php';

$errors = [];
$success = false;
$show_qr_modal = false;

// Handle receipt upload
$receipt_uploaded = false;
$receipt_path = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['register'])) {
        $fullname = trim($_POST['fullname'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $contact  = trim($_POST['contact'] ?? '');
        $email    = trim($_POST['email'] ?? '');

        if (!$fullname) $errors[] = "Full name is required.";
        if (!$username) $errors[] = "Username is required.";
        if (!$password) $errors[] = "Password is required.";
        if (!$category) $errors[] = "Category is required.";
        if (!$contact)  $errors[] = "Contact number is required.";
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT id FROM public.participants WHERE username = :username");
            $stmt->execute(['username' => $username]);
            if ($stmt->fetch()) {
                $errors[] = "Username is already taken.";
            } else {
                $stmt = $pdo->prepare("INSERT INTO public.participants (fullname, username, password, category, contact, email, payment_status, registration_date)
                                       VALUES (:fullname, :username, :password, :category, :contact, :email, 'unpaid', NOW())");
                $stmt->execute([
                    'fullname' => $fullname,
                    'username' => $username,
                    'password' => $password, // Hash in production!
                    'category' => $category,
                    'contact'  => $contact,
                    'email'    => $email,
                ]);
                $_SESSION['registered_username'] = $username; // for associating receipt
                $success = true;
                $show_qr_modal = true;
            }
        }
    }

    // Receipt upload after registration
    if (isset($_POST['upload_receipt']) && isset($_SESSION['registered_username'])) {
        $username = $_SESSION['registered_username'];
        $upload_dir = 'receipts/';
        if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

        if (isset($_FILES['receipt']) && $_FILES['receipt']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];
            $max_size = 5 * 1024 * 1024;
            $file_type = mime_content_type($_FILES['receipt']['tmp_name']);
            $file_size = $_FILES['receipt']['size'];
            $ext = strtolower(pathinfo($_FILES['receipt']['name'], PATHINFO_EXTENSION));

            if (in_array($file_type, $allowed_types) && $file_size <= $max_size) {
                $filename = $username . '_' . time() . '.' . $ext;
                $receipt_path = $upload_dir . $filename;
                if (move_uploaded_file($_FILES['receipt']['tmp_name'], $receipt_path)) {
                    $receipt_uploaded = true;
                    // Optionally update participant record with receipt path
                    $stmt = $pdo->prepare("UPDATE public.participants SET receipt = :receipt WHERE username = :username");
                    $stmt->execute(['receipt' => $receipt_path, 'username' => $username]);
                } else {
                    $errors[] = "Failed to upload receipt.";
                }
            } else {
                $errors[] = "Receipt must be an image or PDF (max 5MB).";
            }
        } else {
            $errors[] = "Please select a receipt file to upload.";
        }
        $show_qr_modal = true; // keep QR modal open after receipt upload
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PMC Marathon 2025 – Participant Registration</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; }
        .container { max-width: 430px; margin: 60px auto; background: #fff; border-radius: 12px; box-shadow: 0 0 18px rgba(0,0,0,0.11); padding: 38px 26px 26px 26px; }
        h2 { color: #1e293b; margin-bottom: 30px; text-align: center; }
        .error { color: #b91c1c; background: #fee2e2; padding: 1em 1em; border-radius:8px; margin-bottom: 1em; }
        .success { color: #15803d; background: #dcfce7; padding: 1em 1em; border-radius:8px; margin-bottom: 1em; }
        label { font-weight: 500; display: block; margin-bottom: 4px; }
        input, select { width: 100%; padding: 9px; margin-bottom: 18px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: 1em; }
        button { width: 100%; padding: 11px 0; background: #ef4444; border: none; color: #fff; border-radius: 5px; font-weight: bold; font-size: 1.1em; cursor: pointer; }
        button:hover { background:rgb(209, 9, 59); }
        .center { text-align: center; }
        /* Modal Styles */
        .modal-bg {
            position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
            background: rgba(30, 41, 59, 0.50); display: flex; align-items: center; justify-content: center; z-index: 1000;
        }
        .modal {
            background: #fff; border-radius: 12px; box-shadow: 0 0 18px rgba(0,0,0,0.21); padding: 34px 30px 24px 30px; max-width: 340px; width:95%; text-align: center; position:relative;
        }
        .modal h3 { color: #be123c; margin-top:0; }
        .modal img { max-width: 200px; margin: 18px auto 12px auto; display: block; }
        .modal form { margin-top: 1em; }
        .modal-close { position: absolute; top: 18px; right: 32px; background: none; border: none; font-size: 2em; color: #be123c; cursor: pointer; }
        .receipt-status { margin: 1em auto; color: #15803d; }
        .receipt-preview { margin: 0.7em auto; }
    </style>
    <script>
        function closeModal() {
            document.getElementById('qrModalBg').style.display = 'none';
        }
    </script>
</head>
<body>
    <div class="container">
        <h2>PMC Marathon 2025 Participant Registration</h2>
        <?php if ($success && !$show_qr_modal): ?>
            <div class="success">Registration successful! Please scan the QR code below to process your payment.</div>
        <?php elseif ($errors): ?>
            <div class="error">
                <?php foreach ($errors as $err) echo htmlspecialchars($err) . "<br>"; ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="post" action="" enctype="multipart/form-data">
            <label for="fullname">Full Name</label>
            <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($_POST['fullname'] ?? '') ?>" required>

            <label for="username">Username</label>
            <input type="text" name="username" id="username" value="<?php echo htmlspecialchars($_POST['username'] ?? '') ?>" required>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <label for="category">Category</label>
            <select name="category" id="category" required>
                <option value="">-- Select --</option>
                <option value="5km" <?php if(($_POST['category'] ?? '') === '5km') echo 'selected'; ?>>5 km Run</option>
                <option value="10km" <?php if(($_POST['category'] ?? '') === '10km') echo 'selected'; ?>>10 km Run</option>
                <option value="25km" <?php if(($_POST['category'] ?? '') === '25km') echo 'selected'; ?>>25 km Run</option>
                <option value="50km" <?php if(($_POST['category'] ?? '') === '50km') echo 'selected'; ?>>50 km Ultra Marathon</option>
            </select>

            <label for="contact">Contact Number</label>
            <input type="text" name="contact" id="contact" value="<?php echo htmlspecialchars($_POST['contact'] ?? '') ?>" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($_POST['email'] ?? '') ?>" required>

            <button type="submit" name="register">Register</button>
        </form>
        <div class="center" style="margin-top:1em;">
            Already registered? Contact the admin for payment.
        </div>
        <?php endif; ?>

        <?php if ($show_qr_modal): ?>
            <div class="modal-bg" id="qrModalBg">
                <div class="modal">
                    <h3>Scan to Pay</h3>
                    <img src="qr.jpeg" alt="Scan QR to Pay" style="max-width:200px;">
                    <div style="margin-bottom:1em;">Please scan the QR code above to process payment.<br>
                    After payment, attach your receipt below and contact the admin for confirmation.</div>
                    <?php if ($receipt_uploaded && $receipt_path): ?>
                        <div class="receipt-status">Receipt uploaded successfully!</div>
                        <div class="receipt-preview">
                            <?php if (preg_match('/\.(jpg|jpeg|png|gif)$/i', $receipt_path)): ?>
                                <img src="<?php echo htmlspecialchars($receipt_path); ?>" alt="Receipt" style="max-width:150px;">
                            <?php else: ?>
                                <a href="<?php echo htmlspecialchars($receipt_path); ?>" target="_blank">View uploaded receipt</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <form method="post" enctype="multipart/form-data">
                        <input type="file" name="receipt" accept="image/*,application/pdf" required>
                        <button type="submit" name="upload_receipt" style="margin-top:8px;">Attach Receipt</button>
                    </form>
                    <button onclick="closeModal()" style="margin-top:12px;">Close</button>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>