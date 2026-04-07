<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';
include 'includes/header.php';

$categoryId = isset($_GET['category_id']) ? (int)$_GET['category_id'] : 0;
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
$maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;
$perPage = 12;
$offset = ($page - 1) * $perPage;

$where = ['p.stock >= 0'];
$params = [];

if ($categoryId > 0) {
    $where[] = 'p.category_id = ?';
    $params[] = $categoryId;
}
if ($search !== '') {
    $where[] = '(p.name LIKE ? OR p.description LIKE ?)';
    $params[] = '%' . $search . '%';
    $params[] = '%' . $search . '%';
}
if ($minPrice > 0) {
    $where[] = 'COALESCE(p.sale_price, p.price) >= ?';
    $params[] = $minPrice;
}
if ($maxPrice > 0) {
    $where[] = 'COALESCE(p.sale_price, p.price) <= ?';
    $params[] = $maxPrice;
}

$whereSQL = 'WHERE ' . implode(' AND ', $where);

$orderSQL = match($sort) {
    'price_asc' => 'ORDER BY COALESCE(p.sale_price, p.price) ASC',
    'price_desc' => 'ORDER BY COALESCE(p.sale_price, p.price) DESC',
    'name_asc' => 'ORDER BY p.name ASC',
    default => 'ORDER BY p.created_at DESC'
};

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereSQL");
$countStmt->execute($params);
$totalProducts = (int)$countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $perPage);

$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id $whereSQL $orderSQL LIMIT $perPage OFFSET $offset");
$stmt->execute($params);
$products = $stmt->fetchAll();

$currentCategory = null;
if ($categoryId > 0) {
    $cs = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
    $cs->execute([$categoryId]);
    $currentCategory = $cs->fetch();
}
?>

<div class="container py-4">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="<?= SITE_URL ?>/index.php">Home</a></li>
            <li class="breadcrumb-item <?= !$currentCategory ? 'active' : '' ?>"><a href="<?= SITE_URL ?>/shop.php">Shop</a></li>
            <?php if ($currentCategory): ?>
            <li class="breadcrumb-item active"><?= sanitize($currentCategory['name']) ?></li>
            <?php endif; ?>
        </ol>
    </nav>

    <div class="row g-4">
        <div class="col-lg-3">
            <div class="sidebar-card">
                <h6 class="sidebar-heading"><i class="fas fa-tags me-2"></i>Categories</h6>
                <ul class="sidebar-list">
                    <li>
                        <a href="<?= SITE_URL ?>/shop.php<?= $search ? '?search=' . urlencode($search) : '' ?>" class="<?= !$categoryId ? 'active' : '' ?>">
                            All Products
                        </a>
                    </li>
                    <?php foreach ($categories as $cat): ?>
                    <li>
                        <a href="<?= SITE_URL ?>/shop.php?category_id=<?= (int)$cat['id'] ?><?= $search ? '&search=' . urlencode($search) : '' ?>" class="<?= $categoryId == $cat['id'] ? 'active' : '' ?>">
                            <i class="<?= sanitize($cat['icon'] ?? 'fas fa-tag') ?> me-1"></i>
                            <?= sanitize($cat['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="sidebar-card mt-3">
                <h6 class="sidebar-heading"><i class="fas fa-filter me-2"></i>Filter by Price</h6>
                <form action="<?= SITE_URL ?>/shop.php" method="GET">
                    <?php if ($categoryId): ?><input type="hidden" name="category_id" value="<?= $categoryId ?>"> <?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= sanitize($search) ?>"> <?php endif; ?>
                    <input type="hidden" name="sort" value="<?= sanitize($sort) ?>">
                    <div class="mb-2">
                        <label class="form-label small">Min Price (₹)</label>
                        <input type="number" name="min_price" id="min-price" class="form-control form-control-sm" value="<?= $minPrice > 0 ? $minPrice : '' ?>" min="0" placeholder="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small">Max Price (₹)</label>
                        <input type="number" name="max_price" id="max-price" class="form-control form-control-sm" value="<?= $maxPrice > 0 ? $maxPrice : '' ?>" min="0" placeholder="Any">
                    </div>
                    <button type="submit" class="btn btn-gradient btn-sm w-100">Apply Filter</button>
                    <?php if ($minPrice > 0 || $maxPrice > 0): ?>
                    <a href="<?= SITE_URL ?>/shop.php<?= $categoryId ? '?category_id=' . $categoryId : '' ?>" class="btn btn-outline-secondary btn-sm w-100 mt-1">Clear Filter</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>

        <div class="col-lg-9">
            <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
                <div>
                    <h5 class="mb-0">
                        <?php if ($search): ?>
                            Search: "<span class="text-gradient"><?= sanitize($search) ?></span>"
                        <?php elseif ($currentCategory): ?>
                            <?= sanitize($currentCategory['name']) ?>
                        <?php else: ?>
                            All Products
                        <?php endif; ?>
                    </h5>
                    <small class="text-muted"><?= $totalProducts ?> products found</small>
                </div>
                <form action="<?= SITE_URL ?>/shop.php" method="GET" class="d-flex gap-2 align-items-center">
                    <?php if ($categoryId): ?><input type="hidden" name="category_id" value="<?= $categoryId ?>"> <?php endif; ?>
                    <?php if ($search): ?><input type="hidden" name="search" value="<?= sanitize($search) ?>"> <?php endif; ?>
                    <?php if ($minPrice): ?><input type="hidden" name="min_price" value="<?= $minPrice ?>"> <?php endif; ?>
                    <?php if ($maxPrice): ?><input type="hidden" name="max_price" value="<?= $maxPrice ?>"> <?php endif; ?>
                    <label class="text-muted small mb-0">Sort:</label>
                    <select name="sort" class="form-select form-select-sm sort-select" onchange="this.form.submit()">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="name_asc" <?= $sort === 'name_asc' ? 'selected' : '' ?>>Name A-Z</option>
                    </select>
                </form>
            </div>

            <?php if (empty($products)): ?>
            <div class="empty-state text-center py-5">
                <i class="fas fa-search fa-3x text-muted mb-3"></i>
                <h4>No products found</h4>
                <p class="text-muted">Try adjusting your search or filters</p>
                <a href="<?= SITE_URL ?>/shop.php" class="btn btn-gradient">Browse All Products</a>
            </div>
            <?php else: ?>
            <div class="row g-3">
                <?php foreach ($products as $product): ?>
                <div class="col-6 col-md-4">
                    <div class="product-card">
                        <?php if ($product['sale_price'] && $product['sale_price'] < $product['price']): ?>
                        <span class="badge-sale">SALE</span>
                        <?php elseif ($product['featured']): ?>
                        <span class="badge-hot">HOT</span>
                        <?php endif; ?>
                        <?php if ($product['stock'] == 0): ?>
                        <span class="badge-oos">OUT OF STOCK</span>
                        <?php endif; ?>
                        <div class="product-img-wrap">
                            <a href="<?= SITE_URL ?>/product.php?id=<?= (int)$product['id'] ?>">
                                <?php
                                $imgPath = 'uploads/products/' . $product['image'];
                                $imgSrc = ($product['image'] && file_exists($imgPath))
                                    ? SITE_URL . '/' . $imgPath
                                    : 'https://via.placeholder.com/300x300/1a1a2e/00d4ff?text=' . urlencode(substr($product['name'], 0, 10));
                                ?>
                                <img src="<?= $imgSrc ?>" alt="<?= sanitize($product['name']) ?>" class="product-img" loading="lazy">
                            </a>
                            <div class="product-actions">
                                <button class="btn-icon wishlist-btn" onclick="toggleWishlist(<?= (int)$product['id'] ?>, this)" title="Wishlist">
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
                            <?php if ($product['stock'] > 0): ?>
                            <button class="btn btn-gradient btn-sm w-100 mt-2" onclick="addToCart(<?= (int)$product['id'] ?>)">
                                <i class="fas fa-cart-plus me-1"></i>Add to Cart
                            </button>
                            <?php else: ?>
                            <button class="btn btn-secondary btn-sm w-100 mt-2" disabled>Out of Stock</button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($totalPages > 1): ?>
            <nav class="mt-4">
                <ul class="pagination justify-content-center">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
