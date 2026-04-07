<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

$cartCount = getCartCount($pdo);
$wishlistCount = getWishlistCount($pdo);

$categories = [];
try {
    $stmt = $pdo->query("SELECT id, name, slug, icon FROM categories ORDER BY name ASC");
    $categories = $stmt->fetchAll();
} catch (Exception $e) {
    $categories = [];
}

try {
    $settings = getSettings($pdo);
} catch (Exception $e) {
    $settings = [];
}

$siteName = $settings['site_name'] ?? SITE_NAME;
$logoFile = $settings['logo_file'] ?? '';
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($siteName) ?> - Best Gadgets Online</title>
    <meta name="description" content="<?= sanitize($settings['site_description'] ?? 'Your one-stop shop for gadgets') ?>">
    <link rel="manifest" href="<?= SITE_URL ?>/manifest.json">
    <meta name="theme-color" content="#00d4ff">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="<?= SITE_URL ?>/assets/css/style.css">
</head>
<body>

<?php if ($flash): ?>
<div id="flash-message" class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show position-fixed top-0 start-50 translate-middle-x mt-3" style="z-index:9999;min-width:300px;" role="alert">
    <?= sanitize($flash['message']) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
<?php endif; ?>

<div id="toast-container" class="position-fixed bottom-0 end-0 p-3" style="z-index:11000;"></div>

<nav class="navbar navbar-expand-lg navbar-dark sticky-top" id="mainNavbar">
    <div class="container">
        <a class="navbar-brand brand-logo" href="<?= SITE_URL ?>/index.php">
            <?php if ($logoFile && file_exists(__DIR__ . '/../uploads/logos/' . $logoFile)): ?>
                <img src="<?= SITE_URL ?>/uploads/logos/<?= sanitize($logoFile) ?>" alt="<?= sanitize($siteName) ?>" height="40">
            <?php else: ?>
                <span class="brand-text"><?= sanitize($siteName) ?></span>
            <?php endif; ?>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarMain">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarMain">
            <form class="d-flex mx-auto search-form" action="<?= SITE_URL ?>/shop.php" method="GET">
                <div class="position-relative search-wrapper">
                    <input class="form-control search-input" type="search" name="search" id="search-input" placeholder="Search gadgets..." autocomplete="off">
                    <button class="btn btn-search" type="submit"><i class="fas fa-search"></i></button>
                    <div id="search-dropdown" class="search-dropdown"></div>
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center gap-1">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-th-large"></i> <span class="d-none d-lg-inline">Categories</span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-categories">
                        <?php foreach ($categories as $cat): ?>
                        <li>
                            <a class="dropdown-item" href="<?= SITE_URL ?>/shop.php?category_id=<?= (int)$cat['id'] ?>">
                                <i class="<?= sanitize($cat['icon'] ?? 'fas fa-tag') ?>"></i>
                                <?= sanitize($cat['name']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                        <?php if (empty($categories)): ?>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/shop.php">All Products</a></li>
                        <?php endif; ?>
                    </ul>
                </li>

                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?= SITE_URL ?>/wishlist.php">
                        <i class="fas fa-heart"></i>
                        <?php if ($wishlistCount > 0): ?>
                        <span class="badge badge-counter wishlist-badge"><?= $wishlistCount ?></span>
                        <?php else: ?>
                        <span class="badge badge-counter wishlist-badge" style="display:none;">0</span>
                        <?php endif; ?>
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link position-relative" href="<?= SITE_URL ?>/cart.php">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="badge badge-counter cart-badge"><?= $cartCount > 0 ? $cartCount : '' ?></span>
                    </a>
                </li>

                <?php if (isLoggedIn()): ?>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle"></i>
                        <span class="d-none d-lg-inline"><?= sanitize($_SESSION['user_name'] ?? 'Account') ?></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end">
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/orders.php"><i class="fas fa-box me-2"></i>My Orders</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/profile.php"><i class="fas fa-user me-2"></i>Profile</a></li>
                        <li><a class="dropdown-item" href="<?= SITE_URL ?>/wishlist.php"><i class="fas fa-heart me-2"></i>Wishlist</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="<?= SITE_URL ?>/logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </li>
                <?php else: ?>
                <li class="nav-item">
                    <a class="nav-link" href="<?= SITE_URL ?>/login.php"><i class="fas fa-sign-in-alt"></i> <span class="d-none d-lg-inline">Login</span></a>
                </li>
                <li class="nav-item">
                    <a class="btn btn-gradient btn-sm ms-1" href="<?= SITE_URL ?>/register.php">Register</a>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
