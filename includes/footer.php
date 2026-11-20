<!-- FOOTER -->
<footer class="cb-footer mt-5">
    <div class="container">
        <div class="row g-5">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
                <div class="cb-footer-brand">
                    <span class="brand-icon"><i class="bi bi-camera-reels-fill"></i></span>
                    CineBook
                </div>
                <p class="footer-desc">Your one-stop platform for booking cinema tickets online. Fast, easy, and secure — enjoy the latest movies from home.</p>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6>Quick Links</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= BASE_URL ?>/index.php" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Home</a></li>
                    <li><a href="<?= BASE_URL ?>/movies.php" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Movies</a></li>
                    <li><a href="<?= BASE_URL ?>/movies.php?status=now_showing" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Now Showing</a></li>
                    <li><a href="<?= BASE_URL ?>/movies.php?status=coming_soon" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Coming Soon</a></li>
                </ul>
            </div>

            <!-- Account Links -->
            <div class="col-lg-2 col-md-3 col-6">
                <h6>Account</h6>
                <ul class="list-unstyled mb-0">
                    <li><a href="<?= BASE_URL ?>/login.php" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Sign In</a></li>
                    <li><a href="<?= BASE_URL ?>/register.php" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Register</a></li>
                    <li><a href="<?= BASE_URL ?>/user/my-tickets.php" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>My Tickets</a></li>
                    <li><a href="<?= BASE_URL ?>/user/dashboard.php" class="cb-footer-link"><i class="bi bi-chevron-right" style="font-size:.65rem"></i>Dashboard</a></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="col-lg-4 col-md-6">
                <h6>Contact Us</h6>
                <div class="footer-contact-item">
                    <i class="bi bi-envelope-fill"></i>
                    <span>support@cinebook.com</span>
                </div>
                <div class="footer-contact-item">
                    <i class="bi bi-telephone-fill"></i>
                    <span>+62 800-CINEBOOK</span>
                </div>
                <div class="footer-contact-item">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>Jakarta, Indonesia</span>
                </div>
            </div>
        </div>

        <!-- Bottom Bar -->
        <div class="cb-footer-bottom">
            <span>&copy; <?= date('Y') ?> CineBook. All rights reserved.</span>
            <span style="font-size:.78rem;opacity:.5">Made with <i class="bi bi-heart-fill" style="color:#DC2626"></i> in Indonesia</span>
        </div>
    </div>
</footer>
<!-- END FOOTER -->

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JS -->
<script src="<?= BASE_URL ?>/assets/js/main.js"></script>
</body>
</html>
