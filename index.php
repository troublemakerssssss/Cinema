<?php
$page_title   = 'Home';
$current_page = 'home';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Now Showing (up to 8 movies)
$nowShowing = $db->query("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY created_at DESC LIMIT 8")->fetchAll();

// Coming Soon
$comingSoon = $db->query("SELECT * FROM movies WHERE status = 'coming_soon' ORDER BY release_date ASC LIMIT 4")->fetchAll();
?>
<!-- Hero -->
<section class="cb-hero text-center">
    <div class="container position-relative">
        <span class="badge bg-warning text-dark mb-3 fw-semibold px-3 py-2 rounded-pill">
            <i class="bi bi-stars me-1"></i>Your Cinema Experience
        </span>
        <h1 class="mb-3">Book Your Favourite<br><span class="text-warning">Movie Tickets</span></h1>
        <p class="mb-4 mx-auto" style="max-width:520px">
            Browse the latest movies, pick your seat, and get your ticket in minutes — all online.
        </p>
        <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-warning btn-lg px-5 me-2">
            <i class="bi bi-camera-reels me-2"></i>Browse Movies
        </a>
        <?php if (!isLoggedIn()): ?>
        <a href="<?= BASE_URL ?>/register.php" class="btn btn-outline-light btn-lg px-4">
            <i class="bi bi-person-plus me-2"></i>Get Started
        </a>
        <?php endif; ?>
    </div>
</section>

<!-- Features Bar -->
<section class="py-4 bg-cb-mid border-bottom border-cb">
    <div class="container">
        <div class="row g-3 text-center">
            <div class="col-6 col-md-3">
                <i class="bi bi-ticket-perforated-fill fs-3 text-warning mb-2 d-block"></i>
                <small class="text-muted-cb">Instant E-Ticket</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-cursor-fill fs-3 text-warning mb-2 d-block"></i>
                <small class="text-muted-cb">Seat Selection</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-shield-lock-fill fs-3 text-warning mb-2 d-block"></i>
                <small class="text-muted-cb">Secure Booking</small>
            </div>
            <div class="col-6 col-md-3">
                <i class="bi bi-phone-fill fs-3 text-warning mb-2 d-block"></i>
                <small class="text-muted-cb">Mobile Friendly</small>
            </div>
        </div>
    </div>
</section>

<!-- Now Showing -->
<section class="py-5">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h2 class="cb-section-title mb-0">Now Showing</h2>
            <a href="<?= BASE_URL ?>/movies.php" class="btn btn-sm btn-outline-primary">View All <i class="bi bi-arrow-right"></i></a>
        </div>
        <?php if (empty($nowShowing)): ?>
            <p class="text-muted-cb">No movies currently showing.</p>
        <?php else: ?>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php foreach ($nowShowing as $movie): ?>
            <div class="col">
                <div class="cb-movie-card">
                    <div class="poster-wrap position-relative">
                        <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                             alt="<?= sanitize($movie['title']) ?>" loading="lazy">
                        <div class="ribbon">Now Showing</div>
                        <div class="poster-overlay">
                            <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                               class="btn btn-cb-warning btn-sm w-100">
                                <i class="bi bi-play-fill me-1"></i>Book Now
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="card-title mb-0"><?= sanitize($movie['title']) ?></h6>
                            <span class="cb-badge-rating ms-1"><?= $movie['rating'] ?></span>
                        </div>
                        <span class="cb-badge-genre"><?= sanitize($movie['genre']) ?></span>
                        <p class="card-text mt-1"><i class="bi bi-clock me-1"></i><?= $movie['duration'] ?> min</p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Coming Soon -->
<?php if (!empty($comingSoon)): ?>
<section class="py-5 bg-cb-mid">
    <div class="container">
        <h2 class="cb-section-title mb-4">Coming Soon</h2>
        <div class="row row-cols-2 row-cols-md-4 g-3">
            <?php foreach ($comingSoon as $movie): ?>
            <div class="col">
                <div class="cb-movie-card" style="cursor:default">
                    <div class="poster-wrap position-relative">
                        <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                             onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                             alt="<?= sanitize($movie['title']) ?>" loading="lazy">
                        <div class="ribbon" style="background:#6c757d">Coming Soon</div>
                    </div>
                    <div class="card-body">
                        <h6 class="card-title"><?= sanitize($movie['title']) ?></h6>
                        <span class="cb-badge-genre"><?= sanitize($movie['genre']) ?></span>
                        <p class="card-text mt-1 small"><i class="bi bi-calendar me-1"></i>
                            <?= $movie['release_date'] ? date('d M Y', strtotime($movie['release_date'])) : 'TBA' ?>
                        </p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
