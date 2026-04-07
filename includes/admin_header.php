<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';

if (!isAdmin()) {
    header('Location: ' . SITE_URL . '/admin_login.php');
    exit;
}

$currentPage = basename($_SERVER['PHP_SELF']);
$adminUsername = sanitize($_SESSION['admin_username'] ?? 'Admin');
$flash = getFlashMessage();

function isActivePage($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body { background: #0f0f1a; color: #e0e0e0; min-height: 100vh; }
        .admin-sidebar {
            position: fixed; top: 0; left: 0; width: 250px; height: 100vh;
            background: #0a0a14; border-right: 1px solid rgba(0,212,255,0.15);
            overflow-y: auto; z-index: 1000; transition: transform 0.3s;
        }
        .admin-sidebar .sidebar-brand {
            padding: 1.2rem 1.5rem; background: linear-gradient(135deg, #00d4ff, #7c3aed);
            font-weight: 700; font-size: 1.1rem; color: #fff; text-decoration: none; display: block;
        }
        .admin-sidebar .nav-link {
            color: #a0aec0; padding: 0.65rem 1.5rem; display: flex; align-items: center; gap: 0.75rem;
            transition: all 0.2s; border-left: 3px solid transparent; font-size: 0.9rem;
        }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active {
            color: #00d4ff; background: rgba(0,212,255,0.08); border-left-color: #00d4ff;
        }
        .admin-sidebar .nav-link i { width: 18px; text-align: center; font-size: 0.95rem; }
        .admin-sidebar .sidebar-section {
            padding: 0.5rem 1.5rem 0.25rem; font-size: 0.7rem; text-transform: uppercase;
            letter-spacing: 0.1em; color: #4a5568; margin-top: 0.5rem;
        }
        .admin-content { margin-left: 250px; min-height: 100vh; padding: 0; }
        .admin-topbar {
            background: #0a0a14; border-bottom: 1px solid rgba(0,212,255,0.1);
            padding: 0.75rem 1.5rem; display: flex; align-items: center; justify-content: space-between;
            position: sticky; top: 0; z-index: 999;
        }
        .admin-main { padding: 1.5rem; }
        .admin-card { background: #1a1a2e; border: 1px solid rgba(0,212,255,0.1); border-radius: 12px; padding: 1.5rem; margin-bottom: 1.5rem; }
        .stat-card { background: linear-gradient(135deg, #1a1a2e, #16213e); border: 1px solid rgba(0,212,255,0.15); border-radius: 12px; padding: 1.5rem; text-align: center; }
        .stat-card .stat-number { font-size: 2rem; font-weight: 700; color: #00d4ff; }
        .stat-card .stat-label { color: #718096; font-size: 0.85rem; }
        .stat-card .stat-icon { font-size: 2.5rem; opacity: 0.3; margin-bottom: 0.5rem; }
        table { color: #e0e0e0 !important; }
        .table { --bs-table-bg: transparent; --bs-table-border-color: rgba(0,212,255,0.1); }
        .table thead th { color: #00d4ff; border-color: rgba(0,212,255,0.2); font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; }
        .table td, .table th { border-color: rgba(255,255,255,0.05); vertical-align: middle; }
        .form-control, .form-select { background: #0f0f1a; border-color: rgba(0,212,255,0.2); color: #e0e0e0; }
        .form-control:focus, .form-select:focus { background: #0f0f1a; border-color: #00d4ff; color: #e0e0e0; box-shadow: 0 0 0 0.2rem rgba(0,212,255,0.15); }
        .form-label { color: #a0aec0; font-size: 0.875rem; }
        .btn-primary { background: #00d4ff; border-color: #00d4ff; color: #000; }
        .btn-primary:hover { background: #00b8e0; border-color: #00b8e0; }
        .badge { font-size: 0.75rem; }
        @media (max-width: 768px) {
            .admin-sidebar { transform: translateX(-100%); }
            .admin-sidebar.show { transform: translateX(0); }
            .admin-content { margin-left: 0; }
        }
    </style>
</head>
<body>

<div class="admin-sidebar" id="adminSidebar">
    <a href="<?= SITE_URL ?>/admin_dashboard.php" class="sidebar-brand">
        <i class="fas fa-bolt me-2"></i><?= SITE_NAME ?> Admin
    </a>

    <div class="sidebar-section">Main</div>
    <a href="<?= SITE_URL ?>/admin_dashboard.php" class="nav-link <?= isActivePage('admin_dashboard.php') ?>">
        <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <div class="sidebar-section">Catalog</div>
    <a href="<?= SITE_URL ?>/admin_products.php" class="nav-link <?= isActivePage('admin_products.php') ?>">
        <i class="fas fa-box"></i> Products
    </a>
    <a href="<?= SITE_URL ?>/admin_categories.php" class="nav-link <?= isActivePage('admin_categories.php') ?>">
        <i class="fas fa-tags"></i> Categories
    </a>

    <div class="sidebar-section">Sales</div>
    <a href="<?= SITE_URL ?>/admin_orders.php" class="nav-link <?= isActivePage('admin_orders.php') ?>">
        <i class="fas fa-shopping-bag"></i> Orders
    </a>
    <a href="<?= SITE_URL ?>/admin_coupons.php" class="nav-link <?= isActivePage('admin_coupons.php') ?>">
        <i class="fas fa-ticket-alt"></i> Coupons
    </a>

    <div class="sidebar-section">Users</div>
    <a href="<?= SITE_URL ?>/admin_users.php" class="nav-link <?= isActivePage('admin_users.php') ?>">
        <i class="fas fa-users"></i> Users
    </a>

    <div class="sidebar-section">Content</div>
    <a href="<?= SITE_URL ?>/admin_videos.php" class="nav-link <?= isActivePage('admin_videos.php') ?>">
        <i class="fab fa-youtube"></i> Videos
    </a>
    <a href="<?= SITE_URL ?>/admin_channels.php" class="nav-link <?= isActivePage('admin_channels.php') ?>">
        <i class="fas fa-broadcast-tower"></i> Channels
    </a>
    <a href="<?= SITE_URL ?>/admin_banner.php" class="nav-link <?= isActivePage('admin_banner.php') ?>">
        <i class="fas fa-images"></i> Banners
    </a>

    <div class="sidebar-section">Config</div>
    <a href="<?= SITE_URL ?>/admin_settings.php" class="nav-link <?= isActivePage('admin_settings.php') ?>">
        <i class="fas fa-cog"></i> Settings
    </a>
    <a href="<?= SITE_URL ?>/admin_logo.php" class="nav-link <?= isActivePage('admin_logo.php') ?>">
        <i class="fas fa-image"></i> Logo
    </a>
    <a href="<?= SITE_URL ?>/admin_payment.php" class="nav-link <?= isActivePage('admin_payment.php') ?>">
        <i class="fas fa-credit-card"></i> Payment
    </a>

    <div class="sidebar-section">Account</div>
    <a href="<?= SITE_URL ?>/admin_logout.php" class="nav-link text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>

<div class="admin-content">
    <div class="admin-topbar">
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-outline-secondary d-md-none" onclick="document.getElementById('adminSidebar').classList.toggle('show')">
                <i class="fas fa-bars"></i>
            </button>
            <h6 class="mb-0 text-muted"><?= ucfirst(str_replace(['admin_', '.php', '_'], ['', '', ' '], $currentPage)) ?></h6>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="<?= SITE_URL ?>/index.php" class="btn btn-sm btn-outline-info" target="_blank">
                <i class="fas fa-external-link-alt me-1"></i>View Site
            </a>
            <span class="text-muted small"><i class="fas fa-user-shield me-1"></i><?= $adminUsername ?></span>
        </div>
    </div>

    <?php if ($flash): ?>
    <div class="admin-main pb-0">
        <div class="alert alert-<?= $flash['type'] === 'error' ? 'danger' : $flash['type'] ?> alert-dismissible fade show">
            <?= sanitize($flash['message']) ?>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    </div>
    <?php endif; ?>

    <div class="admin-main">
