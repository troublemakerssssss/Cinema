<!-- FOOTER -->
<footer class="cb-footer mt-5 py-4">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-4">
                <h5 class="fw-bold text-white mb-3">
                    <i class="bi bi-camera-reels-fill me-2 text-warning"></i>CineBook
                </h5>
                <p class="text-secondary small">Your one-stop platform for booking cinema tickets online. Fast, easy, and secure.</p>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-semibold mb-3">Quick Links</h6>
                <ul class="list-unstyled small">
                    <li><a href="<?= BASE_URL ?>/index.php" class="cb-footer-link">Home</a></li>
                    <li><a href="<?= BASE_URL ?>/movies.php" class="cb-footer-link">Movies</a></li>
                    <li><a href="<?= BASE_URL ?>/login.php" class="cb-footer-link">Login</a></li>
                    <li><a href="<?= BASE_URL ?>/register.php" class="cb-footer-link">Register</a></li>
                </ul>
            </div>
            <div class="col-md-4">
                <h6 class="text-white fw-semibold mb-3">Contact</h6>
                <ul class="list-unstyled small text-secondary">
                    <li><i class="bi bi-envelope me-2"></i>support@cinebook.com</li>
                    <li><i class="bi bi-telephone me-2"></i>+62 800-CINEBOOK</li>
                    <li><i class="bi bi-geo-alt me-2"></i>Jakarta, Indonesia</li>
                </ul>
            </div>
        </div>
        <hr class="border-secondary mt-4">
        <p class="text-center text-secondary small mb-0">&copy; <?= date('Y') ?> CineBook. All rights reserved.</p>
    </div>
</footer>
<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
