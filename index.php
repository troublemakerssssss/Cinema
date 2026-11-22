<?php
$page_title   = 'Home';
$current_page = 'home';
require_once __DIR__ . '/includes/header.php';

$db = getDB();

// Hero movie: pick the latest now_showing movie
$hero = $db->query("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY created_at DESC LIMIT 1")->fetch();

// Now Showing (up to 20 movies for carousel)
$nowShowing = $db->query("SELECT * FROM movies WHERE status = 'now_showing' ORDER BY created_at DESC LIMIT 20")->fetchAll();

// Coming Soon (up to 20 movies for carousel)
$comingSoon = $db->query("SELECT * FROM movies WHERE status = 'coming_soon' ORDER BY release_date ASC LIMIT 20")->fetchAll();
?>

<!-- ============================================================
     HERO SECTION (Reference 1: Star Cineplex)
     ============================================================ -->
<section class="cb-hero">
    <div class="container">
        <div class="row align-items-center g-5">
            <!-- Left: Text content -->
            <div class="col-lg-6">
                <?php if ($hero): ?>
                    <span class="hero-genre-label">
                        <?= sanitize(strtoupper($hero['genre'])) ?>
                    </span>
                    <h1><?= sanitize($hero['title']) ?></h1>
                    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
                        <span class="cb-badge-rating"><i class="bi bi-star-fill me-1"></i><?= sanitize($hero['rating']) ?></span>
                        <span class="text-muted-cb" style="font-size:.85rem"><i class="bi bi-clock me-1"></i><?= $hero['duration'] ?> min</span>
                    </div>
                    <p class="hero-desc">
                        <?= sanitize(mb_strimwidth($hero['synopsis'] ?? 'An unforgettable cinematic experience awaits you.', 0, 180, '...')) ?>
                    </p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $hero['id'] ?>" class="btn btn-cb-primary btn-lg px-4">
                            Get Tickets
                        </a>
                        <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $hero['id'] ?>" class="btn btn-cb-secondary btn-lg px-4">
                            See Details
                        </a>
                    </div>
                <?php else: ?>
                    <span class="hero-genre-label">WELCOME TO CINEBOOK</span>
                    <h1>Book Your Cinema Tickets Online</h1>
                    <p class="hero-desc">Browse the latest movies, pick your seat, and get your ticket in minutes — all online.</p>
                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-primary btn-lg px-4">
                            Get Tickets
                        </a>
                        <?php if (!isLoggedIn()): ?>
                        <a href="<?= BASE_URL ?>/register.php" class="btn btn-cb-secondary btn-lg px-4">
                            Register
                        </a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Right: Featured movie poster -->
            <?php if ($hero): ?>
            <div class="col-lg-6 d-none d-lg-flex justify-content-end">
                <div class="hero-poster-wrap">
                    <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($hero['poster']) ?>"
                         onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                         alt="<?= sanitize($hero['title']) ?>">
                    <?php if ($hero['release_date']): ?>
                    <div style="position:absolute;bottom:16px;left:16px;background:var(--cb-white);border:1px solid var(--cb-border);border-radius:var(--cb-radius-pill);padding:.4rem .9rem;font-size:.8rem;box-shadow:var(--cb-shadow-sm)">
                        <i class="bi bi-calendar-event me-1" style="color:var(--cb-text)"></i>
                        <span style="font-weight:700;color:var(--cb-text)"><?= date('d M Y', strtotime($hero['release_date'])) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php else: ?>
            <div class="col-lg-6 d-none d-lg-flex justify-content-end">
                <div class="text-center" style="opacity:.2">
                    <i class="bi bi-camera-reels-fill" style="font-size:8rem;color:var(--cb-text)"></i>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FLOATING QUICK BOOKING CARD (Reference 1)
     ============================================================ -->
<div class="container mb-5">
    <div class="cb-floating-booking-card">
        <form method="GET" action="<?= BASE_URL ?>/movie-detail.php" class="row g-3 align-items-end">
            <div class="col-12">
                <h5 class="mb-0"><i class="bi bi-ticket-perforated me-2"></i>Book Tickets</h5>
            </div>
            <div class="col-md-7">
                <label class="cb-label">Select Movie</label>
                <select name="id" class="form-select cb-form-control" required>
                    <option value="" disabled selected>Choose a movie...</option>
                    <?php foreach ($nowShowing as $m): ?>
                        <option value="<?= $m['id'] ?>"><?= sanitize($m['title']) ?> (<?= $m['duration'] ?> min)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="cb-label">Status</label>
                <div class="cb-form-control text-muted-cb d-flex align-items-center" style="background:var(--cb-section)">
                    <i class="bi bi-camera-reels me-2 text-dark"></i>Now Showing
                </div>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-cb-primary w-100 py-2">
                    Book Now
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ============================================================
     FEATURES BAR
     ============================================================ -->
<section class="cb-features-bar">
    <div class="container">
        <div class="row g-4">
            <div class="col-6 col-md-3">
                <div class="cb-feature-item">
                    <div class="cb-feature-icon"><i class="bi bi-ticket-perforated-fill"></i></div>
                    <div>
                        <div class="feature-title">Instant E-Ticket</div>
                        <div class="feature-desc">Get tickets instantly</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cb-feature-item">
                    <div class="cb-feature-icon"><i class="bi bi-cursor-fill"></i></div>
                    <div>
                        <div class="feature-title">Seat Selection</div>
                        <div class="feature-desc">Choose your seats</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cb-feature-item">
                    <div class="cb-feature-icon"><i class="bi bi-shield-fill-check"></i></div>
                    <div>
                        <div class="feature-title">Secure Booking</div>
                        <div class="feature-desc">Safe &amp; reliable</div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="cb-feature-item">
                    <div class="cb-feature-icon"><i class="bi bi-phone-fill"></i></div>
                    <div>
                        <div class="feature-title">Mobile Friendly</div>
                        <div class="feature-desc">Book on any device</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     NOW SHOWING — CAROUSEL
     ============================================================ -->
<section id="now-showing" class="cb-carousel-section py-5">
    <div class="container">
        <!-- Section Header -->
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
            <div class="d-flex align-items-center gap-4">
                <div class="cb-movie-tabs mb-0 pb-0 border-0">
                    <a href="#now-showing" class="cb-movie-tab-btn active">Now Showing</a>
                    <a href="#coming-soon" class="cb-movie-tab-btn">Coming Soon</a>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>/movies.php?status=now_showing" class="btn btn-cb-secondary btn-sm d-flex align-items-center gap-1">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
                <button class="cb-carousel-btn cb-carousel-prev" data-target="carousel-now-showing" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="cb-carousel-btn cb-carousel-next" data-target="carousel-now-showing" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <?php if (empty($nowShowing)): ?>
            <div class="cb-empty">
                <div class="empty-icon"><i class="bi bi-camera-reels"></i></div>
                <h5>No Movies Currently Showing</h5>
                <p>Check back soon for new movies.</p>
            </div>
        <?php else: ?>
        <div class="cb-carousel-track-wrapper">
            <div class="cb-carousel-track" id="carousel-now-showing">
                <?php foreach ($nowShowing as $movie): ?>
                <div class="cb-carousel-item">
                    <div class="cb-movie-card">
                        <div class="poster-wrap position-relative">
                            <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                 alt="<?= sanitize($movie['title']) ?>"
                                 loading="lazy"
                                 width="300" height="450">
                            <div class="ribbon ribbon-showing">Showing</div>
                            <div class="poster-overlay">
                                <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                                   class="btn btn-cb-primary btn-sm w-100">
                                    <i class="bi bi-ticket-perforated me-1"></i>Book Now
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title" title="<?= sanitize($movie['title']) ?>"><?= sanitize($movie['title']) ?></h6>
                            <div class="card-meta mb-2">
                                <span class="cb-badge-genre"><?= sanitize(explode(',', $movie['genre'])[0]) ?></span>
                                <?php if ($movie['rating']): ?>
                                <span class="cb-badge-rating"><i class="bi bi-star-fill" style="font-size:.6rem"></i><?= sanitize($movie['rating']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-size:.78rem;color:var(--cb-text-muted)">
                                    <i class="bi bi-clock me-1"></i><?= $movie['duration'] ?> min
                                </span>
                                <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= $movie['id'] ?>"
                                   class="btn btn-cb-primary btn-sm py-1 px-2" style="font-size:.75rem">
                                    Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- ============================================================
     COMING SOON — CAROUSEL
     ============================================================ -->
<?php if (!empty($comingSoon)): ?>
<section id="coming-soon" class="cb-carousel-section py-5" style="background:var(--cb-white);border-top:1px solid var(--cb-border)">
    <div class="container">
        <!-- Section Header -->
        <div class="d-flex align-items-end justify-content-between mb-4 flex-wrap gap-3">
            <div>
                <div class="cb-section-label"><i class="bi bi-calendar-event"></i> Coming Soon</div>
                <h2 class="cb-section-title">Upcoming Movies</h2>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="<?= BASE_URL ?>/movies.php?status=coming_soon" class="btn btn-cb-secondary btn-sm d-flex align-items-center gap-1">
                    View All <i class="bi bi-arrow-right"></i>
                </a>
                <button class="cb-carousel-btn cb-carousel-prev" data-target="carousel-coming-soon" aria-label="Previous">
                    <i class="bi bi-chevron-left"></i>
                </button>
                <button class="cb-carousel-btn cb-carousel-next" data-target="carousel-coming-soon" aria-label="Next">
                    <i class="bi bi-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="cb-carousel-track-wrapper">
            <div class="cb-carousel-track" id="carousel-coming-soon">
                <?php foreach ($comingSoon as $movie): ?>
                <div class="cb-carousel-item">
                    <div class="cb-movie-card">
                        <div class="poster-wrap position-relative">
                            <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                                 onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                                 alt="<?= sanitize($movie['title']) ?>"
                                 loading="lazy"
                                 width="300" height="450">
                            <div class="ribbon ribbon-soon">Coming Soon</div>
                            <div class="poster-overlay">
                                <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= (int)$movie['id'] ?>"
                                   class="btn btn-cb-secondary btn-sm w-100">
                                    <i class="bi bi-info-circle me-1"></i>See Details
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <h6 class="card-title" title="<?= sanitize($movie['title']) ?>"><?= sanitize($movie['title']) ?></h6>
                            <div class="card-meta mb-2">
                                <span class="cb-badge-genre"><?= sanitize(explode(',', $movie['genre'])[0]) ?></span>
                            </div>
                            <div class="d-flex align-items-center justify-content-between">
                                <span style="font-size:.78rem;color:var(--cb-muted)">
                                    <i class="bi bi-calendar me-1" style="color:var(--cb-text)"></i>
                                    <?= $movie['release_date'] ? date('d M Y', strtotime($movie['release_date'])) : 'TBA' ?>
                                </span>
                                <a href="<?= BASE_URL ?>/movie-detail.php?id=<?= (int)$movie['id'] ?>"
                                   class="btn btn-cb-primary btn-sm py-1 px-2" style="font-size:.75rem">
                                    See Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- ============================================================
     CTA SECTION
     ============================================================ -->
<?php if (!isLoggedIn()): ?>
<section class="py-5" style="background:var(--cb-section);border-top:1px solid var(--cb-border)">
    <div class="container">
        <div class="row justify-content-center text-center">
            <div class="col-lg-6">
                <div class="cb-section-label justify-content-center"><i class="bi bi-ticket-perforated"></i> Easy Booking</div>
                <h2 class="cb-section-title mb-2">Ready to Watch?</h2>
                <p class="cb-section-subtitle mb-4">Create your free account and start booking movie tickets in minutes.</p>
                <div class="d-flex justify-content-center gap-3 flex-wrap">
                    <a href="<?= BASE_URL ?>/register.php" class="btn btn-cb-primary btn-lg px-5">
                        <i class="bi bi-person-plus me-2"></i>Create Free Account
                    </a>
                    <a href="<?= BASE_URL ?>/movies.php" class="btn btn-cb-secondary btn-lg px-4">
                        <i class="bi bi-camera-reels me-2"></i>Browse Movies
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
