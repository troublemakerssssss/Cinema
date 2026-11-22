<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM movies WHERE id = ?");
$stmt->execute([$id]);
$movie = $stmt->fetch();

if (!$movie) {
    header('Location: ' . BASE_URL . '/movies.php');
    exit;
}

// Get showtimes for the next 7 days grouped by date
$stmt2 = $db->prepare("
    SELECT * FROM showtimes
    WHERE movie_id = ? AND show_date >= CURDATE() AND show_date <= DATE_ADD(CURDATE(), INTERVAL 6 DAY)
    ORDER BY show_date ASC, show_time ASC
");
$stmt2->execute([$id]);
$allShowtimes = $stmt2->fetchAll();

// Group by date
$showtimesByDate = [];
foreach ($allShowtimes as $st) {
    $showtimesByDate[$st['show_date']][] = $st;
}

$page_title   = sanitize($movie['title']);
$current_page = 'movies';
require_once __DIR__ . '/includes/header.php';
?>

<!-- Breadcrumb -->
<div style="background:var(--cb-white);border-bottom:1px solid var(--cb-border)">
    <div class="container py-3">
        <nav style="font-size:.82rem;" aria-label="breadcrumb">
            <ol class="breadcrumb mb-0" style="--bs-breadcrumb-divider: '›';">
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/index.php" style="color:var(--cb-text-muted)">Home</a></li>
                <li class="breadcrumb-item"><a href="<?= BASE_URL ?>/movies.php" style="color:var(--cb-text-muted)">Movies</a></li>
                <li class="breadcrumb-item active" style="color:var(--cb-text)"><?= sanitize($movie['title']) ?></li>
            </ol>
        </nav>
    </div>
</div>

<div class="container py-5">
    <div class="row g-5">
        <!-- ========================
             LEFT: POSTER
             ======================== -->
        <div class="col-md-3 text-center text-md-start">
            <div style="position:sticky;top:84px">
                <img src="<?= BASE_URL ?>/assets/images/<?= sanitize($movie['poster']) ?>"
                     onerror="this.src='<?= BASE_URL ?>/assets/images/no-poster.jpg'"
                     alt="<?= sanitize($movie['title']) ?>"
                     class="poster-detail img-fluid">

                <?php if ($movie['status'] === 'now_showing'): ?>
                <div class="mt-3 d-none d-md-block">
                    <a href="#showtimes" class="btn btn-cb-primary w-100">
                        <i class="bi bi-ticket-perforated me-2"></i>Book Tickets
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ========================
             RIGHT: INFO
             ======================== -->
        <div class="col-md-9">
            <!-- Status & Rating Row -->
            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                <?php if ($movie['status'] === 'now_showing'): ?>
                    <span class="ribbon-showing" style="font-size:.75rem;font-weight:700;padding:.3rem .85rem;border-radius:var(--cb-radius-pill);background:var(--cb-button);color:#fff">Now Showing</span>
                <?php elseif ($movie['status'] === 'coming_soon'): ?>
                    <span style="font-size:.75rem;font-weight:700;padding:.3rem .85rem;border-radius:var(--cb-radius-pill);background:var(--cb-charcoal);color:#fff">Coming Soon</span>
                <?php endif; ?>
                <span class="cb-badge-rating"><i class="bi bi-star-fill me-1" style="font-size:.7rem"></i><?= sanitize($movie['rating']) ?></span>
                <?php foreach (explode(',', $movie['genre']) as $g): ?>
                    <span class="cb-badge-genre"><?= sanitize(trim($g)) ?></span>
                <?php endforeach; ?>
            </div>

            <!-- Title -->
            <h1 class="cb-detail-title">
                <?= sanitize($movie['title']) ?>
            </h1>

            <!-- Action buttons (Reference 2: Star Wars) -->
            <div class="d-flex align-items-center gap-3 mb-4 flex-wrap">
                <button type="button" class="btn btn-cb-accent btn-sm px-4" onclick="alert('Trailer preview coming soon!')">
                    <i class="bi bi-play-fill me-1"></i>Watch trailer
                </button>
                <button type="button" class="btn btn-cb-secondary btn-sm px-3" onclick="this.classList.toggle('active')">
                    <i class="bi bi-heart me-1"></i>Favourite
                </button>
            </div>

            <!-- Info Grid -->
            <div class="row g-2 mb-4">
                <div class="col-6 col-md-3">
                    <div class="cb-info-card">
                        <div class="info-icon"><i class="bi bi-clock"></i></div>
                        <div class="info-value"><?= $movie['duration'] ?> min</div>
                        <div class="info-label">Duration</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="cb-info-card">
                        <div class="info-icon"><i class="bi bi-calendar-event"></i></div>
                        <div class="info-value" style="font-size:.85rem">
                            <?= $movie['release_date'] ? date('d M Y', strtotime($movie['release_date'])) : 'TBA' ?>
                        </div>
                        <div class="info-label">Release Date</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="cb-info-card">
                        <div class="info-icon"><i class="bi bi-star-fill" style="color:var(--cb-rating)"></i></div>
                        <div class="info-value"><?= sanitize($movie['rating']) ?></div>
                        <div class="info-label">Age Rating</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="cb-info-card">
                        <div class="info-icon"><i class="bi bi-film"></i></div>
                        <div class="info-value" style="font-size:.85rem"><?= sanitize(explode(',', $movie['genre'])[0]) ?></div>
                        <div class="info-label">Genre</div>
                    </div>
                </div>
            </div>

            <!-- Synopsis -->
            <div class="mb-4">
                <h5 class="fw-bold mb-2" style="font-size:1.05rem;color:var(--cb-text)">
                    <i class="bi bi-file-text me-2" style="color:var(--cb-text)"></i>Synopsis
                </h5>
                <p style="line-height:1.8;color:var(--cb-text-secondary);font-size:.925rem">
                    <?= nl2br(sanitize($movie['synopsis'] ?? 'No synopsis available.')) ?>
                </p>
            </div>

            <!-- Mobile Book Button -->
            <?php if ($movie['status'] === 'now_showing'): ?>
            <div class="d-md-none mb-4">
                <a href="#showtimes" class="btn btn-cb-primary w-100">
                    <i class="bi bi-ticket-perforated me-2"></i>View Showtimes
                </a>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================
         SHOWTIMES SECTION (Reference 2: Get Showtimes block)
         ============================================================ -->
    <?php if ($movie['status'] === 'now_showing'): ?>
    <div class="cb-showtimes-box" id="showtimes">
        <div class="cb-showtimes-header-dark">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar-week" style="font-size:1.15rem"></i>
                <h5 class="mb-0">Get Showtimes</h5>
            </div>
            <span style="font-size:.8rem;opacity:.8">Select date and hall</span>
        </div>
        <div class="p-4">
            <?php if (empty($showtimesByDate)): ?>
                <div class="cb-empty" style="padding:2rem 1rem">
                    <div class="empty-icon"><i class="bi bi-calendar-x"></i></div>
                    <h5>No Showtimes Available</h5>
                    <p>No showtimes scheduled for the next 7 days.</p>
                </div>
            <?php else: ?>
                <!-- Date Tabs -->
                <ul class="nav cb-tabs nav-tabs mb-4" id="dateTabs" data-movie-id="<?= $id ?>" role="tablist">
                    <?php $i = 0; foreach ($showtimesByDate as $date => $times): ?>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link <?= $i === 0 ? 'active' : '' ?>"
                                data-date="<?= $date ?>"
                                type="button">
                            <span style="display:block;font-size:.7rem;font-weight:700;text-transform:uppercase;letter-spacing:.04em"><?= date('D', strtotime($date)) ?></span>
                            <span style="display:block;font-size:1rem;font-weight:800"><?= date('d', strtotime($date)) ?></span>
                            <span style="display:block;font-size:.7rem"><?= date('M', strtotime($date)) ?></span>
                        </button>
                    </li>
                    <?php $i++; endforeach; ?>
                </ul>

                <!-- Showtime Container (AJAX) -->
                <div id="showtime-container"></div>

                <?php if (!isLoggedIn()): ?>
                <div class="mt-4 p-3 d-flex align-items-center gap-2" style="background:var(--cb-section);border-radius:var(--cb-radius-sm);border:1px solid var(--cb-border)">
                    <i class="bi bi-info-circle text-dark"></i>
                    <span style="font-size:.875rem;color:var(--cb-text)">
                        <a href="<?= BASE_URL ?>/login.php" class="fw-semibold text-decoration-underline" style="color:var(--cb-text)">Sign in</a> to book tickets for this movie.
                    </span>
                </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
