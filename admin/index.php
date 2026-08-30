<?php
session_start();
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: /?page=login');
    exit;
}
$page = $_GET['page'] ?? 'dashboard';
$allowed = ['dashboard', 'products', 'orders', 'users', 'topups', 'coupons', 'reviews', 'logs'];
if (!in_array($page, $allowed)) $page = 'dashboard';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard — CYGNUSXSTORE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { background: #06070c; color: #e2e8f0; font-family: 'Kanit', sans-serif; }
        .admin-sidebar { background: #08090e; min-height: 100vh; border-right: 1px solid #facc1525; }
        .admin-sidebar .brand { font-size: 1.4rem; font-weight: 800; color: #facc15; }
        .admin-sidebar .nav-link { color: #94a3b8; border-radius: 8px; padding: 10px 16px; margin: 2px 0; }
        .admin-sidebar .nav-link:hover, .admin-sidebar .nav-link.active { background: #facc1518; color: #facc15; }
        .admin-sidebar .nav-link i { width: 24px; }
        .admin-card { background: #0a0b10; border: 1px solid #facc1520; border-radius: 12px; padding: 20px; }
        .stat-value { font-size: 2rem; font-weight: 800; color: #facc15; }
        .stat-label { color: #94a3b8; font-size: 0.9rem; }
        .table-dark-custom { background: #0a0b10; color: #e2e8f0; }
        .table-dark-custom th { border-bottom: 1px solid #facc1530; }
        .badge-status { padding: 6px 14px; border-radius: 20px; font-size: 0.75rem; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar p-3">
            <div class="brand mb-4"><i class="fa-solid fa-crown me-2" style="color:#facc15;"></i> CYGNUSX ADMIN</div>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="?page=dashboard" class="nav-link <?= $page === 'dashboard' ? 'active' : '' ?>"><i class="fa-solid fa-chart-pie"></i> Dashboard</a></li>
                <li class="nav-item"><a href="?page=products" class="nav-link <?= $page === 'products' ? 'active' : '' ?>"><i class="fa-solid fa-box"></i> สินค้า</a></li>
                <li class="nav-item"><a href="?page=orders" class="nav-link <?= $page === 'orders' ? 'active' : '' ?>"><i class="fa-solid fa-cart-shopping"></i> คำสั่งซื้อ</a></li>
                <li class="nav-item"><a href="?page=users" class="nav-link <?= $page === 'users' ? 'active' : '' ?>"><i class="fa-solid fa-users"></i> ผู้ใช้</a></li>
                <li class="nav-item"><a href="?page=topups" class="nav-link <?= $page === 'topups' ? 'active' : '' ?>"><i class="fa-solid fa-wallet"></i> เติมเงิน</a></li>
                <li class="nav-item"><a href="?page=coupons" class="nav-link <?= $page === 'coupons' ? 'active' : '' ?>"><i class="fa-solid fa-ticket"></i> คูปอง</a></li>
                <li class="nav-item"><a href="?page=reviews" class="nav-link <?= $page === 'reviews' ? 'active' : '' ?>"><i class="fa-solid fa-star"></i> รีวิว</a></li>
                <li class="nav-item"><a href="?page=logs" class="nav-link <?= $page === 'logs' ? 'active' : '' ?>"><i class="fa-solid fa-history"></i> Logs</a></li>
                <li class="nav-item mt-3"><a href="/?page=logout" class="nav-link text-danger"><i class="fa-solid fa-sign-out-alt"></i> ออกจากระบบ</a></li>
            </ul>
        </nav>
        <main class="col-md-9 col-lg-10 p-4">
            <?php include __DIR__ . "/pages/{$page}.php"; ?>
        </main>
    </div>
</div>
<script src="/assets/js/cygnusx.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
