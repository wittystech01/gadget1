<?php
if (!isset($settings)) {
    $settings = [];
    try {
        $settings = getSettings($pdo);
    } catch (Exception $e) {}
}
if (!isset($categories)) {
    $categories = [];
    try {
        $stmt = $pdo->query("SELECT id, name, slug FROM categories ORDER BY name ASC LIMIT 8");
        $categories = $stmt->fetchAll();
    } catch (Exception $e) {}
}
?>

<footer class="site-footer mt-5">
    <div class="footer-top">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading"><?= sanitize($settings['site_name'] ?? SITE_NAME) ?></h5>
                    <p class="footer-text"><?= sanitize($settings['site_description'] ?? 'Your one-stop shop for the latest gadgets and electronics. Quality products at the best prices.') ?></p>
                    <div class="social-links mt-3">
                        <?php if (!empty($settings['facebook_url'])): ?>
                        <a href="<?= sanitize($settings['facebook_url']) ?>" target="_blank" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['twitter_url'])): ?>
                        <a href="<?= sanitize($settings['twitter_url']) ?>" target="_blank" class="social-link"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['instagram_url'])): ?>
                        <a href="<?= sanitize($settings['instagram_url']) ?>" target="_blank" class="social-link"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['youtube_url'])): ?>
                        <a href="<?= sanitize($settings['youtube_url']) ?>" target="_blank" class="social-link"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                        <?php if (empty($settings['facebook_url']) && empty($settings['twitter_url'])): ?>
                        <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading">Quick Links</h5>
                    <ul class="footer-links">
                        <li><a href="<?= SITE_URL ?>/index.php"><i class="fas fa-chevron-right me-1"></i>Home</a></li>
                        <li><a href="<?= SITE_URL ?>/shop.php"><i class="fas fa-chevron-right me-1"></i>Shop</a></li>
                        <li><a href="<?= SITE_URL ?>/blog.php"><i class="fas fa-chevron-right me-1"></i>Blog</a></li>
                        <li><a href="<?= SITE_URL ?>/contact.php"><i class="fas fa-chevron-right me-1"></i>Contact</a></li>
                        <li><a href="<?= SITE_URL ?>/video.php"><i class="fas fa-chevron-right me-1"></i>Videos</a></li>
                        <li><a href="<?= SITE_URL ?>/channel.php"><i class="fas fa-chevron-right me-1"></i>Channels</a></li>
                        <li><a href="<?= SITE_URL ?>/cart.php"><i class="fas fa-chevron-right me-1"></i>Cart</a></li>
                        <li><a href="<?= SITE_URL ?>/wishlist.php"><i class="fas fa-chevron-right me-1"></i>Wishlist</a></li>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading">Categories</h5>
                    <ul class="footer-links">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a href="<?= SITE_URL ?>/shop.php?category_id=<?= (int)$cat['id'] ?>">
                                <i class="fas fa-chevron-right me-1"></i><?= sanitize($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <li><a href="<?= SITE_URL ?>/shop.php"><i class="fas fa-chevron-right me-1"></i>All Products</a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="col-lg-3 col-md-6">
                    <h5 class="footer-heading">Contact Us</h5>
                    <ul class="footer-contact-list">
                        <li>
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?= sanitize($settings['contact_address'] ?? '123 Tech Street, Mumbai, India') ?></span>
                        </li>
                        <li>
                            <i class="fas fa-phone"></i>
                            <span><?= sanitize($settings['contact_phone'] ?? '+91 98765 43210') ?></span>
                        </li>
                        <li>
                            <i class="fas fa-envelope"></i>
                            <span><?= sanitize($settings['contact_email'] ?? ADMIN_EMAIL) ?></span>
                        </li>
                    </ul>
                    <div class="mt-3">
                        <h6 class="footer-subheading">Newsletter</h6>
                        <form action="<?= SITE_URL ?>/save_newsletter.php" method="POST" class="newsletter-form">
                            <div class="input-group">
                                <input type="email" name="email" class="form-control form-control-sm" placeholder="Your email address" required>
                                <button class="btn btn-gradient btn-sm" type="submit"><i class="fas fa-paper-plane"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="footer-bottom">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <p class="mb-0">&copy; <?= date('Y') ?> <?= sanitize($settings['site_name'] ?? SITE_NAME) ?>. All Rights Reserved.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="footer-payment">
                        <i class="fab fa-cc-visa"></i>
                        <i class="fab fa-cc-mastercard"></i>
                        <i class="fab fa-cc-paypal"></i>
                        <i class="fas fa-mobile-alt"></i> UPI
                    </span>
                </div>
            </div>
        </div>
    </div>
</footer>

<div id="cookie-consent" class="cookie-consent" style="display:none;">
    <div class="container">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <p class="mb-0"><i class="fas fa-cookie-bite me-2"></i>We use cookies to enhance your experience. By continuing to visit this site you agree to our use of cookies.</p>
            <div class="d-flex gap-2">
                <a href="#" class="btn btn-sm btn-outline-light">Learn More</a>
                <button id="accept-cookies" class="btn btn-sm btn-gradient">Accept</button>
            </div>
        </div>
    </div>
</div>

<div id="loading-spinner" class="loading-spinner" style="display:none;">
    <div class="spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="<?= SITE_URL ?>/assets/js/main.js"></script>
</body>
</html>
