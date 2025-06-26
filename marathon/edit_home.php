<?php
session_start();
$is_admin = isset($_SESSION['username']);
// Load homepage hero content
$hero_content = file_exists('home_content.txt')
    ? file_get_contents('home_content.txt')
    : "Get ready to lace up and conquer the road! The Philippine Marine Corps invites all running enthusiasts to join the PMC Marathon 2025, whether you're a seasoned athlete or a first-time runner. Join us in a race that celebrates endurance, discipline, and the spirit of camaraderie. Stay tuned for more details and prepare to go the distance! #PMCMarathon2025 #PhilippineMarineCorps";
// Load gallery images from assets/ folder
$galleryDir = 'assets/';
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
    <title>Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        :root {
            --red1: #ef4444;
            --red2: #be123c;
            --nav-bg: #fff;
            --nav-link: #be123c;
            --nav-link-hover: #ef4444;
            --header-gradient: linear-gradient(90deg, var(--red1) 0%, var(--red2) 100%);
            --shadow: 0 2px 16px rgba(239, 68, 68, 0.10);
            --count-bg: #fff1f2;
            --count-color: #be123c;
        }
        body {
            margin: 0;
            font-family: 'Inter', Arial, sans-serif;
            background: #f8fafc;
            color: #1e293b;
        }
        .header-gradient {
            width: 100vw;
            background: var(--header-gradient);
            padding: 2rem 0 1.1rem 0;
            box-shadow: var(--shadow);
            text-align: center;
        }
        .header-gradient h1 {
            color: #fff;
            font-size: 2.7rem;
            font-weight: 900;
            margin: 0;
            letter-spacing: 2px;
        }
        .navbar {
            width: 100%;
            background: var(--nav-bg);
            box-shadow: 0 2px 16px rgba(239, 68, 68, 0.06);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 1.6rem;
            padding: 0.6rem 0;
            position: sticky;
            top: 0;
            z-index: 100;
        }
        .navbar a {
            color: var(--nav-link);
            text-decoration: none;
            font-weight: 700;
            letter-spacing: 0.5px;
            font-size: 1.11rem;
            padding: 0.4em 1em;
            border-radius: 6px;
            transition: background .18s, color .18s;
        }
        .navbar a:hover, .navbar a.active {
            background: var(--red1);
            color: #fff;
        }
        .hero {
            max-width: 900px;
            margin: 2.5rem auto 1.5rem auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 2px 24px rgba(239, 68, 68, 0.07);
            padding: 2.2rem 2rem 2.5rem 2rem;
            text-align: center;
        }
        .hero h2 {
            font-size: 2.1rem;
            font-weight: 800;
            color: var(--red2);
            margin-bottom: 0.7em;
        }
        .hero-desc {
            font-size: 1.13rem;
            margin-bottom: 1.8em;
            color: #334155;
        }
        .countdown {
            display: flex;
            justify-content: center;
            gap: 2.2em;
            margin-bottom: 2em;
        }
        .count-box {
            background: var(--count-bg);
            color: var(--count-color);
            border-radius: 14px;
            padding: 1.2em 1.6em;
            box-shadow: 0 1px 8px rgba(239, 68, 68, 0.06);
            display: flex;
            flex-direction: column;
            align-items: center;
            font-weight: 800;
            font-size: 2.1em;
            min-width: 70px;
        }
        .count-label {
            font-size: 0.65em;
            font-weight: 700;
            color: #be123c;
            letter-spacing: 1px;
            margin-top: 0.5em;
        }
        .gallery-section {
            margin: 2.5rem auto;
            max-width: 960px;
            background: #fff;
            border-radius: 14px;
            padding: 1.75rem 2.5vw;
            box-shadow: 0 2px 16px rgba(239, 68, 68, 0.07);
        }
        .gallery-section h3 {
            color: var(--red2);
            margin-bottom: 1rem;
        }
        .gallery-list {
            display: flex;
            flex-wrap: wrap;
            gap: 1.2em;
            justify-content: center;
        }
        .gallery-item {
            flex: 0 0 220px;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(239, 68, 68, 0.07);
            background: #f1f5f9;
            position: relative;
        }
        .gallery-item img, .gallery-item video {
            width: 100%;
            display: block;
            border-radius: 12px;
        }
        .edit-bar {
            margin: 0.7rem 0 1.2rem 0;
            text-align: right;
        }
        .edit-bar a {
            display: inline-block;
            color: #fff;
            background: var(--red2);
            padding: 0.45em 1.2em;
            border-radius: 8px;
            font-weight: 700;
            font-size: 1.01rem;
            text-decoration: none;
            transition: background .18s;
        }
        .edit-bar a:hover {
            background: var(--red1);
        }
        .admin-edit-section {
            margin-top: 1.2rem;
            text-align: right;
        }
        .edit-home-btn {
            display: inline-block;
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 0.6em 1.5em;
            border-radius: 7px;
            font-weight: 700;
            font-size: 1.06em;
            text-decoration: none;
            margin-bottom: 12px;
            cursor: pointer;
            margin-left: 1em;
            transition: background 0.15s;
        }
        .edit-home-btn:hover { background: #be123c; }
        @media (max-width: 600px) {
            .header-gradient h1 { font-size: 1.3rem; }
            .hero { padding: 1.2rem 0.7rem; }
            .gallery-section { padding: 1.1rem 0.4rem; }
            .gallery-item { flex-basis: 100%; }
            .countdown { gap: 1em;}
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function(){
            const targetDate = new Date("2025-11-17T06:00:00");
            function updateCountdown() {
                const now = new Date();
                let diff = Math.max(0, targetDate - now);
                let days = Math.floor(diff / (1000 * 60 * 60 * 24));
                let hours = Math.floor(diff / (1000 * 60 * 60) % 24);
                let minutes = Math.floor(diff / (1000 * 60) % 60);
                let seconds = Math.floor(diff / 1000 % 60);
                document.getElementById('count-days').textContent = String(days).padStart(2,'0');
                document.getElementById('count-hours').textContent = String(hours).padStart(2,'0');
                document.getElementById('count-mins').textContent = String(minutes).padStart(2,'0');
                document.getElementById('count-secs').textContent = String(seconds).padStart(2,'0');
            }
            setInterval(updateCountdown, 1000);
            updateCountdown();
        });
    </script>
</head>
<body>
    <div class="header-gradient">
        <h1>Welcome to PMC Marathon 2025</h1>
    </div>
    <nav class="navbar">
        <a href="home.php" class="active">Home</a>
        <a href="participants_register.php">Register</a>
        <a href="gallery.php">Gallery</a>
        <a href="results.php">Results</a>
        <a href="contact.php">Contact</a>
        <?php if($is_admin): ?>
            <a href="admin_dashboard.php">Admin Dashboard</a>
            <a href="logout.php">Logout</a>
        <?php else: ?>
            <a href="index.php">Admin Login</a>
        <?php endif; ?>
    </nav>
    <?php if($is_admin): ?>
        <div class="admin-edit-section">
            <a href="edit_homepage.php" class="edit-home-btn">Edit Homepage Content</a>
        </div>
    <?php endif; ?>
    <div class="hero">
        <h2>LOOK II</h2>
        <div class="hero-desc">
            <?php echo nl2br(htmlspecialchars($hero_content)); ?>
        </div>
        <div class="countdown">
            <div class="count-box">
                <span id="count-days">150</span>
                <div class="count-label">Days</div>
            </div>
            <div class="count-box">
                <span id="count-hours">00</span>
                <div class="count-label">Hours</div>
            </div>
            <div class="count-box">
                <span id="count-mins">00</span>
                <div class="count-label">Minutes</div>
            </div>
            <div class="count-box">
                <span id="count-secs">00</span>
                <div class="count-label">Seconds</div>
            </div>
        </div>
    </div>
    <div class="gallery-section">
        <h3>Event Photos</h3>
        <?php if($is_admin): ?>
        <div class="edit-bar">
            <a href="edit_homepage.php">Edit Gallery</a>
        </div>
        <?php endif; ?>
        <div class="gallery-list">
            <?php foreach ($gallery_files as $img): ?>
                <div class="gallery-item"><img src="<?php echo htmlspecialchars($galleryDir . $img); ?>" alt="PMC Marathon 2025"></div>
            <?php endforeach; ?>
            <?php if (empty($gallery_files)): ?>
                <div style="color:#888;">No photos yet.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>