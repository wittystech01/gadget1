<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$banners = [];
try {
    $stmt = $pdo->query("SELECT * FROM banners WHERE is_active = 1 ORDER BY id ASC LIMIT 5");
    $banners = $stmt->fetchAll();
} catch (Exception $e) {}

$featuredProducts = [];
try {
    $stmt = $pdo->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.featured = 1 AND p.stock > 0 ORDER BY p.created_at DESC LIMIT 8");
    $featuredProducts = $stmt->fetchAll();
} catch (Exception $e) {}

$featuredCategories = [];
try {
    $stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count FROM categories c LEFT JOIN products p ON p.category_id = c.id GROUP BY c.id ORDER BY product_count DESC LIMIT 6");
    $featuredCategories = $stmt->fetchAll();
} catch (Exception $e) {}

$latestPosts = [];
try {
    $stmt = $pdo->query("SELECT id, title, content, image, created_at FROM blog_posts ORDER BY created_at DESC LIMIT 3");
    $latestPosts = $stmt->fetchAll();
} catch (Exception $e) {}
?>

<section class="hero-section">
    <?php if (!empty($banners)): ?>
    <div id="heroCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">
        <div class="carousel-indicators">
            <?php foreach ($banners as $i => $banner): ?>
            <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" <?= $i === 0 ? 'class="active"' : '' ?>></button>
            <?php endforeach; ?>
        </div>
        <div class="carousel-inner">
            <?php foreach ($banners as $i => $banner): ?>
            <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
                <?php if ($banner['image'] && file_exists('uploads/banners/' . $banner['image'])): ?>
                <img src="<?= SITE_URL ?>/uploads/banners/<?= sanitize($banner['image']) ?>" class="d-block w-100 carousel-img" alt="<?= sanitize($banner['title']) ?>">
                <?php else: ?>
                <div class="carousel-placeholder d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <h1 class="display-4 fw-bold text-gradient"><?= sanitize($banner['title'] ?? 'Latest Gadgets') ?></h1>
                        <p class="lead">Discover Amazing Tech</p>
                        <a href="<?= sanitize($banner['link'] ?? SITE_URL . '/shop.php') ?>" class="btn btn-gradient btn-lg mt-3">Shop Now</a>
                    </div>
                </div>
                <?php endif; ?>
                <div class="carousel-caption">
                    <h2><?= sanitize($banner['title'] ?? '') ?></h2>
                    <?php if (!empty($banner['link'])): ?>
                    <a href="<?= sanitize($banner['link']) ?>" class="btn btn-gradient">Shop Now</a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon"></span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon"></span>
        </button>
    </div>
    <?php else: ?>
    <div class="hero-fallback d-flex align-items-center justify-content-center">
        <div class="text-center">
            <h1 class="display-3 fw-bold text-gradient">Welcome to <?= sanitize(SITE_NAME) ?></h1>
            <p class="lead text-muted mt-3">Discover the best gadgets and electronics at unbeatable prices.</p>
            <div class="mt-4 d-flex gap-3 justify-content-center">
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-gradient btn-lg">Shop Now</a>
                <a href="<?= SITE_URL ?>/video.php" class="btn btn-outline-primary btn-lg">Watch Reviews</a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</section>

<section class="features-strip py-3">
    <div class="container">
        <div class="row g-3">
            <div class="col-6 col-md-3">
                <div class="feature-item text-center">
                    <i class="fas fa-shipping-fast feature-icon"></i>
                    <span>Free Delivery</span>
                    <small>On orders over ₹500</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="feature-item text-center">
                    <i class="fas fa-undo feature-icon"></i>
                    <span>Easy Returns</span>
                    <small>30-day return policy</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="feature-item text-center">
                    <i class="fas fa-shield-alt feature-icon"></i>
                    <span>Secure Payment</span>
                    <small>100% secure checkout</small>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="feature-item text-center">
                    <i class="fas fa-headset feature-icon"></i>
                    <span>24/7 Support</span>
                    <small>Dedicated support</small>
                </div>
            </div>
        </div>
    </div>
</section>

<?php if (!empty($featuredCategories)): ?>
<section class="categories-section py-5">
    <div class="container">
        <div class="section-header text-center mb-4">
            <h2 class="section-title">Shop by <span class="text-gradient">Category</span></h2>
            <p class="section-subtitle text-muted">Find exactly what you're looking for</p>
        </div>
        <div class="row g-3">
            <?php foreach ($featuredCategories as $cat): ?>
            <div class="col-6 col-md-4 col-lg-2">
                <a href="<?= SITE_URL ?>/shop.php?category_id=<?= (int)$cat['id'] ?>" class="category-card text-center text-decoration-none d-block">
                    <div class="category-icon">
                        <i class="<?= sanitize($cat['icon'] ?? 'fas fa-tag') ?>"></i>
                    </div>
                    <div class="category-name"><?= sanitize($cat['name']) ?></div>
                    <div class="category-count"><?= (int)$cat['product_count'] ?> items</div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($featuredProducts)): ?>
<section class="products-section py-5">
    <div class="container">
        <div class="section-header d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="section-title mb-1">Featured <span class="text-gradient">Products</span></h2>
                <p class="text-muted mb-0">Handpicked products just for you</p>
            </div>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-outline-primary">View All <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-3">
            <?php foreach ($featuredProducts as $product): ?>
            <div class="col-6 col-md-4 col-lg-3">
                <div class="product-card">
                    <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                    <span class="badge-sale">SALE</span>
                    <?php elseif ($product['featured']): ?>
                    <span class="badge-hot">HOT</span>
                    <?php endif; ?>
                    <div class="product-img-wrap">
                        <a href="<?= SITE_URL ?>/product.php?id=<?= (int)$product['id'] ?>">
                            <?php
                            $imgPath = 'uploads/products/' . $product['image'];
                            $imgSrc = ($product['image'] && file_exists($imgPath))
                                ? SITE_URL . '/' . $imgPath
                                : 'https://via.placeholder.com/300x300/1a1a2e/00d4ff?text=' . urlencode($product['name']);
                            ?>
                            <img src="<?= $imgSrc ?>" alt="<?= sanitize($product['name']) ?>" class="product-img">
                        </a>
                        <div class="product-actions">
                            <button class="btn-icon" onclick="toggleWishlist(<?= (int)$product['id'] ?>, this)" title="Add to Wishlist">
                                <i class="far fa-heart"></i>
                            </button>
                        </div>
                    </div>
                    <div class="product-info">
                        <div class="product-category"><?= sanitize($product['category_name'] ?? '') ?></div>
                        <h6 class="product-name">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= (int)$product['id'] ?>"><?= sanitize($product['name']) ?></a>
                        </h6>
                        <div class="product-price">
                            <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                            <span class="price-sale"><?= formatPrice($product['sale_price']) ?></span>
                            <span class="price-original"><?= formatPrice($product['price']) ?></span>
                            <?php else: ?>
                            <span class="price-current"><?= formatPrice($product['price']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button class="btn btn-gradient btn-sm w-100 mt-2" onclick="addToCart(<?= (int)$product['id'] ?>)">
                            <i class="fas fa-cart-plus me-1"></i>Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="promo-section py-5">
    <div class="container">
        <div class="promo-banner text-center p-5">
            <h2 class="fw-bold mb-3">Get <span class="text-gradient">10% OFF</span> Your First Order</h2>
            <p class="text-muted mb-4">Use coupon code <strong class="text-primary">SAVE10</strong> at checkout</p>
            <a href="<?= SITE_URL ?>/shop.php" class="btn btn-gradient btn-lg">Shop Now</a>
        </div>
    </div>
</section>

<?php if (!empty($latestPosts)): ?>
<section class="blog-section py-5">
    <div class="container">
        <div class="section-header d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="section-title mb-1">Latest <span class="text-gradient">Articles</span></h2>
                <p class="text-muted mb-0">Stay updated with tech news</p>
            </div>
            <a href="<?= SITE_URL ?>/blog.php" class="btn btn-outline-primary">All Articles <i class="fas fa-arrow-right ms-1"></i></a>
        </div>
        <div class="row g-3">
            <?php foreach ($latestPosts as $post): ?>
            <div class="col-md-4">
                <div class="blog-card">
                    <?php if ($post['image'] && file_exists('uploads/' . $post['image'])): ?>
                    <img src="<?= SITE_URL ?>/uploads/<?= sanitize($post['image']) ?>" alt="<?= sanitize($post['title']) ?>" class="blog-img">
                    <?php else: ?>
                    <div class="blog-img-placeholder"><i class="fas fa-newspaper"></i></div>
                    <?php endif; ?>
                    <div class="blog-body">
                        <span class="blog-date"><i class="fas fa-calendar me-1"></i><?= date('M d, Y', strtotime($post['created_at'])) ?></span>
                        <h5 class="blog-title"><?= sanitize($post['title']) ?></h5>
                        <p class="blog-excerpt"><?= sanitize(substr(strip_tags($post['content']), 0, 120)) ?>...</p>
                        <a href="<?= SITE_URL ?>/blog.php?id=<?= (int)$post['id'] ?>" class="btn btn-sm btn-outline-primary">Read More</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="newsletter-section py-5">
    <div class="container">
        <div class="newsletter-box text-center p-5">
            <h3 class="fw-bold mb-2">Subscribe to Our Newsletter</h3>
            <p class="text-muted mb-4">Get the latest deals, product launches, and tech news in your inbox.</p>
            <form action="<?= SITE_URL ?>/save_newsletter.php" method="POST" class="row justify-content-center">
                <div class="col-md-6">
                    <div class="input-group">
                        <input type="email" name="email" class="form-control" placeholder="Enter your email address" required>
                        <button class="btn btn-gradient" type="submit">Subscribe <i class="fas fa-paper-plane ms-1"></i></button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
