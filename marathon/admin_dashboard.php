<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Only allow logged-in admins from the users table
if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit;
}

require 'db_connection.php';

// Fetch participant summary and filtered participant list
$filter = $_GET['filter'] ?? 'all';
$where = '';
$params = [];

switch ($filter) {
    case 'paid':
        $where = "WHERE payment_status = 'paid'";
        break;
    case 'unpaid':
        $where = "WHERE payment_status = 'unpaid'";
        break;
    default:
        $where = '';
}

try {
    // Participant summary counts
    $summary = $pdo->query("SELECT 
        COUNT(*) AS total,
        COUNT(*) FILTER (WHERE payment_status = 'paid') AS paid,
        COUNT(*) FILTER (WHERE payment_status = 'unpaid') AS unpaid
        FROM public.participants
    ")->fetch(PDO::FETCH_ASSOC);

    // List of participants, filtered
    $stmt = $pdo->prepare("SELECT fullname, username, category, payment_status, registration_date FROM public.participants $where ORDER BY registration_date DESC, fullname ASC");
    $stmt->execute($params);
    $participants = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div style='color:#b91c1c;background:#fee2e2;padding:1em;border-radius:8px;'>Database error: " . htmlspecialchars($e->getMessage()) . "</div>";
    exit;
}

function format_category($cat) {
    switch ($cat) {
        case '5km': return "5 km Run";
        case '10km': return "10 km Run";
        case '25km': return "25 km Run";
        case '50km': return "50 km Ultra Marathon";
        default: return htmlspecialchars($cat);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>PMC Marathon 2025 – Admin Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #f8fafc; margin: 0; padding: 0; }
        .header-bar { width: 100vw; position: fixed; top: 0; left: 0; background: linear-gradient(90deg, #ef4444 0%, #be123c 100%); padding: 1.2rem 0; box-shadow: 0 2px 16px rgba(239, 68, 68, 0.12); z-index: 10;}
        .header-title { color: #fff; font-weight: 800; font-size: 2rem; letter-spacing: 1px; margin-left: 2.2rem; }
        .container { max-width: 1100px; margin: 120px auto 0 auto; background: #fff; border-radius: 12px; box-shadow: 0 0 18px rgba(0,0,0,0.11); padding: 38px 26px 26px 26px;}
        h2 { color: #1e293b; margin-bottom: 30px; }
        .summary { margin-bottom: 1.7em; background: #f3f4f6; border-radius: 8px; padding: 0.8em 1em; display: flex; gap: 2em;}
        .summary span { font-weight: 600; color: #be123c;}
        .filters { margin-bottom: 18px;}
        .filters a { color: #fff; background: #ef4444; padding: 7px 15px; border-radius: 5px; text-decoration: none; margin-right: 7px; font-weight: 500;}
        .filters a.active, .filters a:hover { background: #be123c; }
        .no-participants { color: #ef4444; background: #fee2e2; padding: 1.2em 1em; border-radius: 8px; margin: 2em 0;}
        .table-responsive { width: 100%; overflow-x: auto;}
        table { border-collapse: collapse; width: 100%; background: #f9fafb; margin-top: 0.5em;}
        th, td { border: 1px solid #e5e7eb; padding: 12px 9px; text-align: left;}
        th { background: #2563eb; color: #fff; font-weight: 600;}
        tr:nth-child(even) { background: #f1f5f9;}
        tr:hover { background: #e0e7ef;}
        .category-tag { display: inline-block; padding: 4px 14px; border-radius: 14px; background: #e0e7ef; color: #1e293b; font-size: 0.95em; font-weight: 500;}
        .paid { color: #22c55e; font-weight: bold;}
        .unpaid { color: #b91c1c; font-weight: bold;}
        .admin-actions {
            margin-bottom: 1em;
            text-align: right;
        }
        .edit-home-btn {
            display: inline-block;
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 0.7em 2em;
            border-radius: 7px;
            font-weight: 700;
            font-size: 1.1em;
            text-decoration: none;
            margin-bottom: 18px;
            cursor: pointer;
            transition: background 0.15s;
        }
        .edit-home-btn:hover { background: #be123c; }
        .logout { margin-top: 18px; text-align: right;}
        .logout a { display: inline-block; padding: 9px 22px; background: #ef4444; color: #fff; border-radius: 5px; text-decoration: none; font-weight: bold; transition: background 0.2s;}
        .logout a:hover { background: #be123c; }
        @media (max-width: 1100px) { .container { padding: 12px 3vw 20px 3vw; } .header-title { font-size: 1.3rem; margin-left: 1rem; } table, th, td { font-size: 0.97em; } }
        @media (max-width: 800px) { .container { margin: 90px auto 0 auto; } }
    </style>
</head>
<body>
    <div class="header-bar">
        <span class="header-title">PMC Marathon 2025 &mdash; Admin Dashboard</span>
    </div>
    <div class="container">
        <div class="admin-actions">
            <a href="edit_home.php" class="edit-home-btn">Edit Homepage</a>
        </div>
        <h2>Registered Participants</h2>
        <div class="summary">
            <div>Total: <span><?php echo (int)$summary['total']; ?></span></div>
            <div>Paid: <span><?php echo (int)$summary['paid']; ?></span></div>
            <div>Unpaid: <span><?php echo (int)$summary['unpaid']; ?></span></div>
        </div>
        <div class="filters">
            <a href="?filter=all" class="<?php if($filter=='all') echo 'active'; ?>">All</a>
            <a href="?filter=paid" class="<?php if($filter=='paid') echo 'active'; ?>">Paid Only</a>
            <a href="?filter=unpaid" class="<?php if($filter=='unpaid') echo 'active'; ?>">Unpaid Only</a>
        </div>
        <?php if (empty($participants)): ?>
            <div class="no-participants">No participants found for this filter.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Full Name</th>
                            <th>Username</th>
                            <th>Category</th>
                            <th>Payment Status</th>
                            <th>Registration Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count=1; foreach ($participants as $p): ?>
                        <tr>
                            <td><?php echo $count++; ?></td>
                            <td><?php echo htmlspecialchars($p['fullname']); ?></td>
                            <td><?php echo htmlspecialchars($p['username']); ?></td>
                            <td><span class="category-tag"><?php echo format_category($p['category']); ?></span></td>
                            <td class="<?php echo $p['payment_status'] === 'paid' ? 'paid' : 'unpaid'; ?>">
                                <?php echo ucfirst($p['payment_status']); ?>
                            </td>
                            <td><?php echo isset($p['registration_date']) ? htmlspecialchars(date('Y-m-d H:i', strtotime($p['registration_date']))) : '-'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <div class="logout">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</body>
</html>