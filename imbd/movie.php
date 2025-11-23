<?php
session_start();
require 'db.php';

// Check movie id
if (!isset($_GET['id']) || !ctype_digit($_GET['id'])) {
    die("Invalid movie ID");
}
$movie_id = (int) $_GET['id'];

$is_logged_in = isset($_SESSION['user_id']);
$user_name_session = $is_logged_in ? ($_SESSION['user_name'] ?? '') : '';
$user_role = $is_logged_in ? ($_SESSION['role'] ?? 'user') : 'user';

$error = '';

// ------------------------
// Handle POST actions
// ------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // 1) Deleting a review
    if (isset($_POST['delete_review_id'])) {

        if (!$is_logged_in) {
            header('Location: login.php');
            exit;
        }

        $review_id = (int) $_POST['delete_review_id'];

        // Check that the review belongs to this user OR user is admin
        $stmt = $pdo->prepare(
            "SELECT id, user_name FROM reviews 
             WHERE id = ? AND movie_id = ?"
        );
        $stmt->execute([$review_id, $movie_id]);
        $review = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($review && ($review['user_name'] === $user_name_session || $user_role === 'admin')) {
            $del = $pdo->prepare("DELETE FROM reviews WHERE id = ?");
            $del->execute([$review_id]);
        }

        // Redirect back to avoid resubmission
        header("Location: movie.php?id=" . $movie_id);
        exit;
    }

    // 2) Adding a new review
    if (!$is_logged_in) {
        header('Location: login.php');
        exit;
    }

    $rating  = (int) ($_POST['rating'] ?? 0);
    $comment = trim($_POST['comment'] ?? '');

    if ($rating >= 1 && $rating <= 10 && $comment !== '') {
        $stmt = $pdo->prepare(
            "INSERT INTO reviews (movie_id, user_name, rating, comment) 
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([$movie_id, $user_name_session, $rating, $comment]);

        header("Location: movie.php?id=" . $movie_id);
        exit;
    } else {
        $error = "Please provide a rating (1–10) and a comment.";
    }
}

// Fetch movie
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    die("Movie not found.");
}

// Fetch reviews
$stmt = $pdo->prepare(
    "SELECT * FROM reviews 
     WHERE movie_id = ? 
     ORDER BY created_at DESC"
);
$stmt->execute([$movie_id]);
$reviews = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Average rating
$stmt = $pdo->prepare(
    "SELECT AVG(rating) AS avg_rating, COUNT(*) AS cnt 
     FROM reviews WHERE movie_id = ?"
);
$stmt->execute([$movie_id]);
$stats = $stmt->fetch(PDO::FETCH_ASSOC);

$avg_rating   = $stats['avg_rating'] ? number_format($stats['avg_rating'], 1) : 'No ratings yet';
$review_count = (int) $stats['cnt'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($movie['title']); ?> – IMBD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- reuse same navbar style as home -->
<nav class="navbar">
    <div class="nav-left">
        <div class="logo-icon">🎬</div>
        <div class="brand"><a href="index.php" style="text-decoration:none;color:inherit;">IMBD</a></div>
    </div>

    <div class="nav-links">
        <a href="index.php#home">Home</a>
        <a href="index.php#movies" class="active">Movies</a>
    </div>

    <div class="nav-right">
        <?php if ($is_logged_in): ?>
            <span style="font-size:13px;">
                Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    (Admin)
                <?php endif; ?>
            </span>
            <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-ghost" style="font-size:12px;text-decoration:none;">
                    Dashboard
                </a>
            <?php endif; ?>
            <a href="logout.php">
                <button class="btn-primary" type="button">Logout</button>
            </a>
        <?php else: ?>
            <a href="login.php">
                <button class="btn-primary" type="button">Sign In</button>
            </a>
        <?php endif; ?>
    </div>
</nav>

<main style="margin-top:80px;">
    <!-- Movie details -->
    <section class="section">
        <div class="movie-detail">
            <div class="movie-detail-poster">
                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>"
                     alt="<?php echo htmlspecialchars($movie['title']); ?>">
            </div>
            <div class="movie-info">
                <h1 class="movie-title-page"><?php echo htmlspecialchars($movie['title']); ?></h1>
                <p class="meta-line">
                    <?php echo htmlspecialchars($movie['year']); ?> • 
                    <?php echo htmlspecialchars($movie['genre']); ?>
                </p>
                <p class="movie-description">
                    <?php echo nl2br(htmlspecialchars($movie['description'])); ?>
                </p>
                <p class="rating-summary">
                    ⭐ Average Rating: <?php echo $avg_rating; ?>
                    <?php if ($review_count): ?>
                        (<?php echo $review_count; ?> reviews)
                    <?php endif; ?>
                </p>
            </div>
        </div>
    </section>

    <!-- Reviews list -->
    <section class="section reviews-section">
        <div class="section-title-row">
            <div class="section-title">Reviews</div>
        </div>

        <?php if ($reviews): ?>
        <?php foreach ($reviews as $rev): ?>
            <div class="review-card">
                <div class="review-header">
                    <div>
                        <strong><?php echo htmlspecialchars($rev['user_name']); ?></strong>
                        <span>• Rating: <?php echo (int)$rev['rating']; ?>/10</span>
                        <span class="date">
                            <?php echo htmlspecialchars($rev['created_at']); ?>
                        </span>
                    </div>

                    <?php if ($is_logged_in && ($rev['user_name'] === $user_name_session || $user_role === 'admin')): ?>
                        <form method="post" class="delete-review-form"
                            onsubmit="return confirm('Delete this review?');">
                            <input type="hidden" name="delete_review_id" value="<?php echo $rev['id']; ?>">
                            <button type="submit" class="delete-btn">Delete</button>
                        </form>
                    <?php endif; ?>
                </div>

                <p><?php echo nl2br(htmlspecialchars($rev['comment'])); ?></p>
            </div>
        <?php endforeach; ?>

        <?php else: ?>
            <p style="font-size:13px;color:#9ca3af;">No reviews yet. Be the first to review!</p>
        <?php endif; ?>
    </section>

    <!-- Add review (only if logged in) -->
    <section class="section review-form-section">
        <div class="section-title-row">
            <div class="section-title">Add Your Review</div>
        </div>

        <?php if ($error): ?>
            <p class="error"><?php echo htmlspecialchars($error); ?></p>
        <?php endif; ?>

        <?php if ($is_logged_in): ?>

            <p style="font-size:13px;color:#9ca3af;margin-bottom:14px;">
                Reviewing as <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong>
            </p>

            <form action="" method="post" class="review-form">

                <label for="rating">Rating (1–10)</label>
                <input type="number" id="rating" name="rating" min="1" max="10" required>

                <label for="comment">Comment</label>
                <textarea id="comment" name="comment" placeholder="Share your thoughts about this movie..." required></textarea>

                <button type="submit" class="btn-primary">Submit Review</button>
            </form>

        <?php else: ?>
            <p style="font-size:13px;color:#9ca3af;">
                You must be <a href="login.php" style="color:#ff3b3b;">signed in</a> to leave a review.
            </p>
        <?php endif; ?>
    </section>

</main>

<footer>
    &copy; <?php echo date('Y'); ?> IMBD – Movie Reviews.
</footer>

</body>
</html>
