<?php
session_start();
require 'db.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

// Handle adding a new movie
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title       = trim($_POST['title'] ?? '');
    $year        = (int) ($_POST['year'] ?? 0);
    $genre       = trim($_POST['genre'] ?? '');
    $poster_url  = trim($_POST['poster_url'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if ($title === '' || $year === 0 || $genre === '') {
        $error = 'Title, year and genre are required.';
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO movies (title, year, genre, poster_url, description) 
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([$title, $year, $genre, $poster_url, $description]);
        $success = 'Movie added successfully.';
    }
}

// Fetch movies
$movies = $pdo->query("SELECT * FROM movies ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard – IMBD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="auth-wrapper" style="align-items:flex-start;padding-top:80px;">
    <div class="auth-card" style="width:650px;max-width:90%;">
        <h2>Admin Dashboard</h2>
        <p style="margin-bottom:10px;">Add movies and manage your catalog.</p>
        <p style="font-size:12px;margin-bottom:14px;">
            Logged in as <?php echo htmlspecialchars($_SESSION['user_name']); ?> 
            | <a href="index.php">Back to site</a>
        </p>

        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form method="post" style="margin-bottom:18px;">
            <label>Title
                <input type="text" name="title" required>
            </label>
            <label>Year
                <input type="number" name="year" required>
            </label>
            <label>Genre
                <input type="text" name="genre" placeholder="Action, Adventure" required>
            </label>
            <label>Poster URL
                <input type="text" name="poster_url" placeholder="https://...">
            </label>
            <label>Description
                <input type="text" name="description">
            </label>
            <button type="submit" class="btn-primary">Add Movie</button>
        </form>

        <h3 style="font-size:14px;margin-bottom:8px;">Existing Movies</h3>
        <div style="max-height:220px;overflow:auto;font-size:12px;">
            <?php foreach ($movies as $m): ?>
                <div style="padding:6px 0;border-bottom:1px solid #111827;">
                    <strong>#<?php echo $m['id']; ?> <?php echo htmlspecialchars($m['title']); ?></strong>
                    (<?php echo htmlspecialchars($m['year']); ?>) – 
                    <?php echo htmlspecialchars($m['genre']); ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
</html>
