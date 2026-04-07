<?php
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['admin_id']);
}

function sanitize($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

function formatPrice($price) {
    $price = (float)$price;
    if ($price >= 100000) {
        $lakh = floor($price / 100000);
        $remainder = $price - ($lakh * 100000);
        if ($remainder == 0) {
            return '₹' . $lakh . ',00,000';
        }
        return '₹' . number_format($price, 2, '.', ',');
    }
    return '₹' . number_format($price, 2, '.', ',');
}

function getCartCount($pdo) {
    if (isset($_SESSION['user_id'])) {
        try {
            $stmt = $pdo->prepare("SELECT COALESCE(SUM(quantity), 0) as cnt FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $row = $stmt->fetch();
            return (int)$row['cnt'];
        } catch (Exception $e) {
            return 0;
        }
    } else {
        if (!isset($_SESSION['cart'])) return 0;
        $count = 0;
        foreach ($_SESSION['cart'] as $qty) {
            $count += $qty;
        }
        return $count;
    }
}

function getWishlistCount($pdo) {
    if (!isset($_SESSION['user_id'])) return 0;
    try {
        $stmt = $pdo->prepare("SELECT COUNT(*) as cnt FROM wishlist WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $row = $stmt->fetch();
        return (int)$row['cnt'];
    } catch (Exception $e) {
        return 0;
    }
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function flashMessage($type, $message) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

function generateSlug($text) {
    $text = strtolower(trim($text));
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    $text = preg_replace('/[\s-]+/', '-', $text);
    $text = trim($text, '-');
    return $text;
}

function uploadImage($file, $folder) {
    if (!isset($file['tmp_name']) || empty($file['tmp_name'])) {
        return false;
    }
    $allowedMimes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    if (!in_array($mimeType, $allowedMimes)) {
        return false;
    }
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return false;
    }
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid('img_', true) . '.' . strtolower($ext);
    $targetDir = __DIR__ . '/../uploads/' . $folder . '/';
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }
    if (move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        return $filename;
    }
    return false;
}

function getSettings($pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_key, setting_value FROM settings");
        $rows = $stmt->fetchAll();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
        return $settings;
    } catch (Exception $e) {
        return [];
    }
}

function applyDiscount($price, $coupon) {
    if (empty($coupon)) return (float)$price;
    $price = (float)$price;
    if ($coupon['type'] === 'percent') {
        $discount = $price * ((float)$coupon['value'] / 100);
        return max(0, $price - $discount);
    } else {
        return max(0, $price - (float)$coupon['value']);
    }
}
