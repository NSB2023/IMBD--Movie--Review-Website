<?php
session_start();
require 'db.php';

/*
  HERO MOVIES (slider) – latest 5 by year
*/
$newsletter_status = $_GET['sub'] ?? '';
$hero_stmt = $pdo->query("SELECT * FROM movies ORDER BY year DESC LIMIT 5");
$hero_movies = $hero_stmt->fetchAll(PDO::FETCH_ASSOC);
$hero = $hero_movies[0] ?? null;

/*
  MOVIE LIST (with optional search)
*/
$movies = [];
if (!empty($_GET['q'])) {
    $q = '%' . $_GET['q'] . '%';
    $stmt = $pdo->prepare(
        "SELECT * FROM movies 
         WHERE title LIKE ? OR genre LIKE ? 
         ORDER BY year DESC"
    );
    $stmt->execute([$q, $q]);
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->query("SELECT * FROM movies ORDER BY year DESC");
    $movies = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

$hero_bg = $hero && $hero['poster_url']
    ? $hero['poster_url']
    : 'images/inception.png'; // fallback
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>IMBD – Movie Reviews</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar">
    <div class="nav-left">
        <div class="logo-icon">🎬</div>
        <div class="brand">IMBD</div>
    </div>

    <div class="nav-links">
        <a href="index.php#home" class="active">Home</a>
        <a href="index.php#movies">Movies</a>
        <a href="index.php#coming">Coming</a>
        <a href="index.php#newsletter">Newsletter</a>
    </div>

    <div class="nav-right">
        <?php if (!empty($_SESSION['user_name'])): ?>
            <span style="font-size:13px;">
                Hi, <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                <?php if (!empty($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <a href="admin_dashboard.php" class="btn-ghost" style="font-size:12px;text-decoration:none;">
                    Dashboard
                </a>
                <a href="admin_newsletter.php" class="btn-ghost" style="font-size:12px;text-decoration:none;">
                    Subscribers
                </a>
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

<main id="home">
    <!-- HERO SECTION -->
    <section class="hero">
        <div class="hero-bg">
            <img id="heroPoster" src="<?php echo htmlspecialchars($hero_bg); ?>" alt="">
        </div>
        <div class="hero-overlay"></div>

        <div class="hero-content">
            <div class="hero-label">FEATURED MOVIE</div>

            <h1 class="hero-title">
                <?php echo $hero ? htmlspecialchars($hero['title']) : 'Welcome to IMBD'; ?>
            </h1>

            <p class="hero-subtitle">
                <?php echo $hero ? htmlspecialchars($hero['description']) : 'Discover, rate, and review your favourite movies.'; ?>
            </p>

            <div class="hero-meta">
                <?php if ($hero): ?>
                    <span><?php echo htmlspecialchars($hero['year']); ?></span>
                    <span><?php echo htmlspecialchars($hero['genre']); ?></span>
                <?php endif; ?>
            </div>

            <div class="hero-actions">
                <?php if ($hero): ?>
                    <a href="booking.php?movie_id=<?php echo $hero['id']; ?>" class="hero-book-link">
                        <button class="btn-primary" type="button">Book Now</button>
                    </a>

                <?php endif; ?>
                <button class="btn-ghost" type="button" id="watchTrailerBtn">Watch Trailer</button>
                <div class="play-circle">▶</div>
            </div>
        </div>
    </section>

    <!-- MOVIES SECTION -->
    <section class="section" id="movies">
        <div class="section-title-row">
            <div>
                <div class="section-title">Opening This Week</div>
                <div class="section-subtitle">
                    Browse movies and read community reviews
                </div>
            </div>
        </div>

        <form method="get" class="search-form">
            <input
                type="text"
                name="q"
                placeholder="Search movies..."
                value="<?php echo isset($_GET['q']) ? htmlspecialchars($_GET['q']) : ''; ?>"
            >
            <button type="submit" class="btn-primary">Search</button>
        </form>

        <div class="movie-grid">
            <?php if (!empty($movies)): ?>
                <?php foreach ($movies as $movie): ?>
                    <a href="movie.php?id=<?php echo $movie['id']; ?>" class="movie-card">
                        <img
                            src="<?php echo htmlspecialchars($movie['poster_url']); ?>"
                            alt="<?php echo htmlspecialchars($movie['title']); ?>"
                        >
                        <div class="movie-card-body">
                            <div class="movie-card-title">
                                <?php echo htmlspecialchars($movie['title']); ?>
                            </div>
                            <div class="movie-card-meta">
                                120 min | <?php echo htmlspecialchars($movie['genre']); ?>
                            </div>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <p>No movies found.</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- COMING SOON -->
    <?php
    $coming = $pdo->query("SELECT * FROM coming_soon ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
    <section class="section" id="coming">
        <div class="section-title-row">
            <div class="section-title">Coming Soon</div>
            <div class="section-subtitle">Movies releasing soon</div>
        </div>

        <div class="movie-grid">
            <?php foreach ($coming as $c): ?>
                <div class="movie-card">
                    <img src="<?php echo htmlspecialchars($c['poster_url']); ?>"
                         alt="<?php echo htmlspecialchars($c['title']); ?>">

                    <div class="movie-card-body">
                        <div class="movie-card-title">
                            <?php echo htmlspecialchars($c['title']); ?>
                        </div>

                        <div class="movie-card-meta">
                            Coming in <?php echo htmlspecialchars($c['release_date']); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- NEWSLETTER -->
    <section class="section" id="newsletter">
        <div class="section-title-row">
            <div class="section-title">Newsletter</div>
            <div class="section-subtitle">Get updates on new releases</div>
        </div>

        <?php if ($newsletter_status === 'ok'): ?>
            <p class="success">You are subscribed to our newsletter 🎉</p>
        <?php elseif ($newsletter_status === 'exists'): ?>
            <p class="error">This email is already subscribed.</p>
        <?php elseif ($newsletter_status === 'invalid'): ?>
            <p class="error">Please enter a valid email address.</p>
        <?php elseif ($newsletter_status === 'fail'): ?>
            <p class="error">Something went wrong. Please try again.</p>
        <?php endif; ?>

        <form method="post" action="subscribe.php" class="newsletter-form">
            <input
                type="email"
                name="email"
                class="newsletter-input"
                placeholder="Enter your email address"
                required
            >
            <button type="submit" class="btn-primary newsletter-btn">
                Subscribe
            </button>
        </form>

        <p class="newsletter-note">
            No spam. Only movie updates, coming soon titles, and special highlights.
        </p>
    </section>

</main>

<footer>
    &copy; <?php echo date('Y'); ?> IMBD – Built with PHP &amp; MySQL.
</footer>

<!-- TRAILER MODAL -->
<div id="trailerModal" class="trailer-modal">
    <div class="trailer-backdrop" id="trailerBackdrop"></div>
    <div class="trailer-content">
        <button class="trailer-close" id="trailerClose">✕</button>
        <div class="trailer-frame-wrapper">
            <iframe id="trailerFrame" 
                    src="" 
                    title="Movie trailer"
                    frameborder="0"
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                    allowfullscreen>
            </iframe>
        </div>
    </div>
</div>

<!-- HERO SLIDER + TRAILER SCRIPT -->
<script>
document.addEventListener('DOMContentLoaded', () => {
    const heroMovies = <?php
        echo json_encode($hero_movies ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
    ?>;

    if (!heroMovies.length) return;

    let current = 0;

    const posterImg  = document.getElementById('heroPoster');
    const titleEl    = document.querySelector('.hero-title');
    const subtitleEl = document.querySelector('.hero-subtitle');
    const metaEl     = document.querySelector('.hero-meta');
    const bookLink   = document.querySelector('.hero-book-link');
    const watchTrailerBtn = document.getElementById('watchTrailerBtn');

    // Trailer modal elements
    const trailerModal   = document.getElementById('trailerModal');
    const trailerFrame   = document.getElementById('trailerFrame');
    const trailerClose   = document.getElementById('trailerClose');
    const trailerBackdrop= document.getElementById('trailerBackdrop');

    // Map movie titles to YouTube trailer URLs (embed form)
    const trailerMap = {
        'Inception': 'https://www.youtube.com/embed/8hP9D6kZseM',
        'Interstellar': 'https://www.youtube.com/embed/zSWdZVtXT7E',
        'The Dark Knight': 'https://www.youtube.com/embed/EXeTwQWrcwY',
        'Dark Knight': 'https://www.youtube.com/embed/EXeTwQWrcwY',
        'Avatar: The Way of Water': 'https://www.youtube.com/embed/d9MyW72ELq0',
        'Dune: Part Two': 'https://www.youtube.com/embed/U2Qp5pL3ovA',
        'Joker': 'https://www.youtube.com/embed/zAGVQLHvwOY',
        'Top Gun: Maverick': 'https://www.youtube.com/embed/qSqVVswa420',
        'Oppenheimer': 'https://www.youtube.com/embed/uYPbbksJxIg',
        'Spider-Man: Far From Home': 'https://www.youtube.com/embed/Nt9L1jCKGnE'
    };

    function getTrailerUrlForMovie(movie) {
        if (!movie || !movie.title) return '';
        const title = movie.title;
        // try exact match
        if (trailerMap[title]) return trailerMap[title];

        // try fuzzy simple contains
        for (const key in trailerMap) {
            if (title.toLowerCase().includes(key.toLowerCase())) {
                return trailerMap[key];
            }
        }
        return '';
    }

    function showSlide(index) {
        const m = heroMovies[index];
        if (!m) return;

        if (posterImg) {
            posterImg.src = m.poster_url || '';
            posterImg.alt = m.title || '';
        }
        if (titleEl) {
            titleEl.textContent = m.title || '';
        }
        if (subtitleEl) {
            subtitleEl.textContent = m.description || '';
        }
        if (metaEl) {
            metaEl.innerHTML = '';
            const spanYear = document.createElement('span');
            spanYear.textContent = m.year || '';
            const spanGenre = document.createElement('span');
            spanGenre.textContent = m.genre || '';
            metaEl.appendChild(spanYear);
            metaEl.appendChild(spanGenre);
        }
        if (bookLink) {
            bookLink.href = 'booking.php?movie_id=' + m.id;
        }


        // Attach current trailer URL to button (via dataset)
        const trailerUrl = getTrailerUrlForMovie(m);
        if (watchTrailerBtn) {
            watchTrailerBtn.dataset.trailer = trailerUrl;
        }
    }

    // Initial slide
    showSlide(current);

    // rotate every 4 seconds
    setInterval(() => {
        current = (current + 1) % heroMovies.length;
        showSlide(current);
    }, 4000);

    // Trailer modal handlers
    function openTrailerModal(url) {
        if (!url) return;
        trailerFrame.src = url + '?autoplay=1';
        trailerModal.classList.add('open');
    }

    function closeTrailerModal() {
        trailerModal.classList.remove('open');
        trailerFrame.src = '';
    }

    if (watchTrailerBtn) {
        watchTrailerBtn.addEventListener('click', () => {
            const currentTrailer = watchTrailerBtn.dataset.trailer || '';
            openTrailerModal(currentTrailer);
        });
    }

    trailerClose.addEventListener('click', closeTrailerModal);
    trailerBackdrop.addEventListener('click', closeTrailerModal);
});
</script>

</body>
</html>
