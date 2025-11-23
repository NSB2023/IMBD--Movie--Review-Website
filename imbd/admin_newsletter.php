<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->query("SELECT * FROM subscribers ORDER BY created_at DESC");
$subs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Newsletter Subscribers – IMBD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper" style="align-items:flex-start;padding-top:80px;">
    <div class="auth-card" style="width:600px;max-width:95%;">
        <h2>Newsletter Subscribers</h2>
        <p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">
            All emails collected from the newsletter form.
        </p>
        <p style="font-size:12px;margin-bottom:8px;">
            <a href="index.php" style="color:#ff3b3b;">← Back to site</a>
        </p>

        <?php if (!$subs): ?>
            <p style="font-size:13px;color:#9ca3af;">No subscribers yet.</p>
        <?php else: ?>
            <div style="max-height:260px;overflow:auto;font-size:13px;">
                <?php foreach ($subs as $s): ?>
                    <div style="padding:6px 0;border-bottom:1px solid #111827;">
                        <strong><?php echo htmlspecialchars($s['email']); ?></strong>
                        <span style="color:#9ca3af;font-size:12px;">
                            &nbsp;• Joined: <?php echo htmlspecialchars($s['created_at']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
