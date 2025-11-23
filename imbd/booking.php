<?php
session_start();
require 'db.php';

$is_logged_in = isset($_SESSION['user_id']);
$user_name    = $is_logged_in ? ($_SESSION['user_name'] ?? '') : '';
$user_id      = $is_logged_in ? ($_SESSION['user_id'] ?? null) : null;

// must be logged in to book
if (!$is_logged_in) {
    header('Location: login.php');
    exit;
}

// validate movie_id
if (!isset($_GET['movie_id']) || !ctype_digit($_GET['movie_id'])) {
    die("Invalid movie ID");
}
$movie_id = (int) $_GET['movie_id'];

// fetch the movie
$stmt = $pdo->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$movie_id]);
$movie = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$movie) {
    die("Movie not found.");
}

$success = '';
$error   = '';

// handle booking form submit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $show_date = $_POST['show_date'] ?? '';
    $show_time = $_POST['show_time'] ?? '';
    $seats     = (int) ($_POST['seats'] ?? 0);

    if ($show_date === '' || $show_time === '' || $seats < 1) {
        $error = "Please choose a date, time, and at least 1 seat.";
    } else {
        $stmt = $pdo->prepare(
            "INSERT INTO bookings (user_id, user_name, movie_id, movie_title, show_date, show_time, seats)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $user_id,
            $user_name ?: 'Guest',
            $movie_id,
            $movie['title'],
            $show_date,
            $show_time,
            $seats
        ]);

        $success = "Booking confirmed for {$movie['title']} on {$show_date} at {$show_time} ({$seats} seat(s)).";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Book <?php echo htmlspecialchars($movie['title']); ?> – IMBD</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

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
        <span style="font-size:13px;">
            Hi, <?php echo htmlspecialchars($user_name); ?>
        </span>
        <a href="logout.php">
            <button class="btn-primary" type="button">Logout</button>
        </a>
    </div>
</nav>

<main style="margin-top:80px;">
    <section class="section">
        <div class="booking-layout">
            <div class="booking-movie">
                <img src="<?php echo htmlspecialchars($movie['poster_url']); ?>"
                     alt="<?php echo htmlspecialchars($movie['title']); ?>">
                <h1 class="movie-title-page"><?php echo htmlspecialchars($movie['title']); ?></h1>
                <p class="meta-line">
                    <?php echo htmlspecialchars($movie['year']); ?> •
                    <?php echo htmlspecialchars($movie['genre']); ?>
                </p>
                <p class="movie-description">
                    <?php echo nl2br(htmlspecialchars($movie['description'])); ?>
                </p>
            </div>

            <div class="booking-form-card">
                <h2>Book Tickets</h2>
                <p style="font-size:12px;color:#9ca3af;margin-bottom:10px;">
                    Choose your show date, time and number of seats.
                </p>

                <?php if ($success): ?>
                    <p class="success"><?php echo htmlspecialchars($success); ?></p>
                <?php endif; ?>

                <?php if ($error): ?>
                    <p class="error"><?php echo htmlspecialchars($error); ?></p>
                <?php endif; ?>

                <form method="post" class="booking-form">
                    <label>
                        Show date
                        <input type="date" name="show_date" required>
                    </label>

                    <label>
                        Show time
                        <select name="show_time" required>
                            <option value="">Select time</option>
                            <option value="11:00 AM">11:00 AM</option>
                            <option value="2:00 PM">2:00 PM</option>
                            <option value="6:00 PM">6:00 PM</option>
                            <option value="9:00 PM">9:00 PM</option>
                        </select>
                    </label>

                    <label>
                        Seats
                        <input type="number" name="seats" min="1" max="10" value="1" required>
                    </label>

                    <button type="submit" class="btn-primary booking-submit">
                        Confirm Booking
                    </button>
                </form>

                <p style="font-size:12px;color:#9ca3af;margin-top:10px;">
                    This is a demo booking. No real payment is processed.
                </p>
            </div>
        </div>
    </section>
</main>

<footer>
    &copy; <?php echo date('Y'); ?> IMBD – Movie Bookings.
</footer>

</body>
</html>
