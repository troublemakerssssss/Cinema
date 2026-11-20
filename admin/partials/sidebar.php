<?php
$current_admin_page = $current_admin_page ?? '';
$menu = [
    ['url' => BASE_URL . '/admin/index.php',     'icon' => 'bi-speedometer2',           'label' => 'Dashboard',  'key' => 'dashboard'],
    ['url' => BASE_URL . '/admin/movies.php',     'icon' => 'bi-camera-reels',           'label' => 'Movies',     'key' => 'movies'],
    ['url' => BASE_URL . '/admin/showtimes.php',  'icon' => 'bi-calendar-week',          'label' => 'Showtimes',  'key' => 'showtimes'],
    ['url' => BASE_URL . '/admin/bookings.php',   'icon' => 'bi-ticket-perforated',      'label' => 'Bookings',   'key' => 'bookings'],
    ['url' => BASE_URL . '/admin/users.php',      'icon' => 'bi-people',                 'label' => 'Users',      'key' => 'users'],
];
?>
<nav class="cb-sidebar d-none d-lg-flex flex-column">
    <div class="sidebar-label">Main Menu</div>
    <ul class="nav flex-column gap-1">
        <?php foreach ($menu as $item): ?>
        <li class="nav-item">
            <a href="<?= $item['url'] ?>"
               class="nav-link <?= $current_admin_page === $item['key'] ? 'active' : '' ?>">
                <i class="bi <?= $item['icon'] ?>"></i><?= $item['label'] ?>
            </a>
        </li>
        <?php endforeach; ?>
    </ul>
</nav>
