<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

// Config
$contentFile = 'home_content.txt';
$galleryDir = 'assets/';
$allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$max_upload_size = 5 * 1024 * 1024; // 5MB

// Save hero text
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_text'])) {
    $new_content = trim($_POST['hero_content'] ?? '');
    file_put_contents($contentFile, $new_content);
    $msg = "Homepage content updated!";
}

// Upload image
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_image']) && isset($_FILES['photo'])) {
    $file = $_FILES['photo'];
    if ($file['error'] === UPLOAD_ERR_OK) {
        if (in_array($file['type'], $allowed_types) && $file['size'] <= $max_upload_size) {
            $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
            $safe_name = 'img_' . uniqid() . "." . $ext;
            move_uploaded_file($file['tmp_name'], $galleryDir . $safe_name);
            $msg = "Photo uploaded successfully!";
        } else {
            $err = "Invalid file type or size too large!";
        }
    } else {
        $err = "Upload error!";
    }
}

// Delete image
if (isset($_GET['delete']) && preg_match('/^img_[a-zA-Z0-9]+\.(jpg|jpeg|png|gif|webp)$/', $_GET['delete'])) {
    $file = $galleryDir . $_GET['delete'];
    if (file_exists($file)) {
        unlink($file);
        $msg = "Photo deleted!";
    }
}

// Load hero content
$hero_content = file_exists($contentFile) ? file_get_contents($contentFile) :
"Get ready to lace up and conquer the road! The Philippine Marine Corps invites all running enthusiasts to join the PMC Marathon 2025, whether you're a seasoned athlete or a first-time runner. Join us in a race that celebrates endurance, discipline, and the spirit of camaraderie. Stay tuned for more details and prepare to go the distance! #PMCMarathon2025 #PhilippineMarineCorps";

// List gallery images
$gallery_files = [];
if (is_dir($galleryDir)) {
    $gallery_files = array_values(array_filter(scandir($galleryDir), function($f) use ($galleryDir) {
        return is_file($galleryDir . $f) && preg_match('/^img_[a-zA-Z0-9]+\.(jpg|jpeg|png|gif|webp)$/i', $f);
    }));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Homepage | PMC Marathon 2025</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --red1: #ef4444;
            --red2: #be123c;
            --nav-bg: #fff;
            --nav-link: #be123c;
            --header-gradient: linear-gradient(90deg, var(--red1) 0%, var(--red2) 100%);
            --shadow: 0 2px 16px rgba(239, 68, 68, 0.10);
            --count-bg: #fff1f2;
            --count-color: #be123c;
        }
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; margin: 0; padding: 0; }
        .header-gradient {
            width: 100vw;
            background: var(--header-gradient);
            padding: 2rem 0 1.1rem 0;
            box-shadow: var(--shadow);
            text-align: center;
        }
        .header-gradient h1 {
            color: #fff;
            font-size: 2.2rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: 2px;
        }
        .container { max-width: 850px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 0 18px rgba(0,0,0,0.09); padding: 36px 30px;}
        h2 { color: #be123c; margin-top: 0;}
        textarea { width: 100%; min-height: 140px; font-size: 1.08em; margin-top: 10px; border-radius: 7px; padding: 8px; border: 1px solid #ddd; }
        .form-actions { margin-top: 18px; }
        button, .delete-btn, .back-link, .upload-btn {
            background: #ef4444; color: #fff; border: none; border-radius: 8px; font-weight: 700; cursor: pointer; text-decoration: none; transition: background 0.15s;
        }
        button, .upload-btn { padding: 11px 34px; font-size: 1.1em; }
        .delete-btn { padding: 5px 18px; font-size: 0.95em; margin-left: 7px; background: #b91c1c;}
        .delete-btn:hover, button:hover, .upload-btn:hover { background: #be123c;}
        .back-link { display: inline-block; margin-bottom: 22px; color: #be123c; font-size: 1em; background: none; border: none;}
        .msg { margin-bottom: 14px; color: #22c55e; font-weight: 600; }
        .err { margin-bottom: 14px; color: #b91c1c; font-weight: 600;}
        .gallery-section {margin-top: 2.7em;}
        .gallery-list {
            display: flex; flex-wrap: wrap; gap: 1.2em; justify-content: flex-start; margin-top: 1.5em;
        }
        .gallery-item {
            flex: 0 0 180px; border-radius: 12px; overflow: hidden; background: #f1f5f9; position: relative;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.07);
            text-align: center; padding-bottom: 10px;
        }
        .gallery-item img { width: 100%; border-radius: 12px 12px 0 0; }
        .gallery-item form { display: inline; }
        .upload-form { margin-top: 1.3em; }
        .upload-form input[type="file"] {margin-right: 12px;}
        @media (max-width: 700px) {
            .container { padding: 1.1rem 0.7rem; }
            .gallery-item { flex-basis: 49%; }
            .gallery-list { gap: 0.6em;}
        }
        @media (max-width: 480px) {
            .gallery-item { flex-basis: 100%; }
        }
    </style>
</head>
<body>
    <div class="header-gradient">
        <h1>Edit Homepage - PMC Marathon 2025</h1>
    </div>
    <div class="container">
        <a href="admin_dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <h2>Edit Homepage Hero Content</h2>
        <?php if (!empty($msg)) echo "<div class='msg'>".htmlspecialchars($msg)."</div>"; ?>
        <?php if (!empty($err)) echo "<div class='err'>".htmlspecialchars($err)."</div>"; ?>
        <form method="post">
            <label for="hero_content">Homepage Hero Text:</label>
            <textarea name="hero_content" id="hero_content"><?php echo htmlspecialchars($hero_content); ?></textarea>
            <div class="form-actions">
                <button type="submit" name="save_text">Save Changes</button>
            </div>
        </form>
        <div class="gallery-section">
            <h2>Homepage Gallery Photos</h2>
            <form class="upload-form" method="post" enctype="multipart/form-data">
                <input type="file" name="photo" accept="image/*" required>
                <button type="submit" class="upload-btn" name="upload_image">Upload Photo</button>
            </form>
            <div class="gallery-list">
                <?php foreach ($gallery_files as $img): ?>
                    <div class="gallery-item">
                        <img src="<?php echo htmlspecialchars($galleryDir . $img); ?>" alt="">
                        <form method="get" onsubmit="return confirm('Are you sure you want to delete this photo?');">
                            <input type="hidden" name="delete" value="<?php echo htmlspecialchars($img); ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    </div>
                <?php endforeach; ?>
                <?php if (empty($gallery_files)): ?>
                    <div style="color:#888;font-size:1.01em;">No photos yet.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>