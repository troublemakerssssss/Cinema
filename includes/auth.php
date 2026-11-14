<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isLoggedIn() || !isAdmin()) {
        header('Location: ' . BASE_URL . '/login.php');
        exit;
    }
}

function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function generateBookingCode(): string {
    return 'CIN-' . strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, 8));
}

function formatPrice(float $price): string {
    return 'Rp ' . number_format($price, 0, ',', '.');
}

function formatDate(string $date): string {
    return date('l, d F Y', strtotime($date));
}

function formatTime(string $time): string {
    return date('H:i', strtotime($time));
}
