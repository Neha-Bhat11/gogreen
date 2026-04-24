<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
require_once '../includes/db.php';

// Stats
$total_users    = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
$total_orders   = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_products = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$total_revenue  = $pdo->query("SELECT SUM(final_amount) FROM orders WHERE payment_status='paid'")->fetchColumn();

// Recent orders
$recent_orders = $pdo->query("SELECT o.*, u.name as user_name 
                               FROM orders o JOIN users u ON o.user_id = u.id
                               ORDER BY o.created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            background: #1b5e20; min-height: 100vh;
            padding: 20px 0; position: fixed;
            width: 240px; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand {
            color: white; font-size: 20px; font-weight: 700;
            padding: 15px 20px; border-bottom: 1px solid #2e7d32;
            margin-bottom: 10px;
        }
        .sidebar-link {
            display: block; color: #c8e6c9; padding: 12px 20px;
            text-decoration: none; font-size: 14px; transition: all 0.2s;
        }
        .sidebar-link:hover, .sidebar-link.active {
            background: #2e7d32; color: white; padding-left: 28px;
        }
        .sidebar-link i { margin-right: 8px; }
        .main-content { margin-left: 240px; padding: 25px; }
        .top-bar {
            background: white; border-radius: 12px; padding: 15px 20px;
            margin-bottom: 25px; display: flex;
            justify-content: space-between; align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .stat-card {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #2e7d32;
        }
        .stat-card.orange { border-left-color: #ff6f00; }
        .stat-card.blue   { border-left-color: #1565c0; }
        .stat-card.purple { border-left-color: #6a1b9a; }
        .stat-icon { font-size: 40px; margin-bottom: 10px; }
        .stat-value { font-size: 32px; font-weight: 700; color: #1b5e20; }
        .stat-label { color: #777; font-size: 14px; }
        .table-box {
            background: white; border-radius: 15px; padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .status-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 12px; font-weight: 600;
        }
        .status-placed     { background: #e3f2fd; color: #1565c0; }
        .status-processing { background: #fff8e1; color: #f57f17; }
        .status-shipped    { background: #e8f5e9; color: #2e7d32; }
        .status-delivered  { background: #1b5e20; color: white; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<div class="sidebar">
    <div class="sidebar-brand">🌱 GreenLife Admin</div>
    <a href="dashboard.php" class="sidebar-link active">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
    <a href="manage_orders.php" class="sidebar-link">
        <i class="bi bi-bag"></i> Manage Orders
    </a>
    <a href="manage_products.php" class="sidebar-link">
        <i class="bi bi-grid"></i> Manage Products
    </a>
    <a href="manage_users.php" class="sidebar-link">
        <i class="bi bi-people"></i> Manage Users
    </a>
    <a href="manage_reviews.php" class="sidebar-link">
        <i class="bi bi-star"></i> Manage Reviews
    </a>
    <a href="../index.php" class="sidebar-link" style="margin-top:20px;">
        <i class="bi bi-globe"></i> View Website
    </a>
    <a href="admin_logout.php" class="sidebar-link" style="color:#ef9a9a;">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<!-- MAIN CONTENT -->
<div class="main-content">

    <!-- Top Bar -->
    <div class="top-bar">
        <h5 style="margin:0; color:#1b5e20; font-weight:700;">
            📊 Dashboard
        </h5>
        <span style="color:#777; font-size:14px;">
            Welcome, <strong><?= $_SESSION['admin_name'] ?></strong> |
            <?= date('d M Y') ?>
        </span>
    </div>

    <!-- Stats -->
    <div class="row g-4 mb-4">
        <div class="col-md-3 col-6">
            <div class="stat-card">
                <div class="stat-icon">👥</div>
                <div class="stat-value"><?= $total_users ?></div>
                <div class="stat-label">Total Users</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card orange">
                <div class="stat-icon">📦</div>
                <div class="stat-value"><?= $total_orders ?></div>
                <div class="stat-label">Total Orders</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card blue">
                <div class="stat-icon">🌱</div>
                <div class="stat-value"><?= $total_products ?></div>
                <div class="stat-label">Total Products</div>
            </div>
        </div>
        <div class="col-md-3 col-6">
            <div class="stat-card purple">
                <div class="stat-icon">💰</div>
                <div class="stat-value">₹<?= number_format($total_revenue ?? 0, 0) ?></div>
                <div class="stat-label">Total Revenue</div>
            </div>
        </div>
    </div>

    <!-- Recent Orders -->
    <div class="table-box">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 style="color:#1b5e20; font-weight:700; margin:0;">
                🕐 Recent Orders
            </h5>
            <a href="manage_orders.php" class="btn btn-success btn-sm">View All</a>
        </div>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background:#e8f5e9;">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_orders as $order): ?>
                    <tr>
                        <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                        <td><?= htmlspecialchars($order['user_name']) ?></td>
                        <td style="color:#e65100; font-weight:600;">
                            ₹<?= number_format($order['final_amount'], 2) ?>
                        </td>
                        <td>
                            <span class="status-badge status-<?= $order['order_status'] ?>">
                                <?= ucfirst($order['order_status']) ?>
                            </span>
                        </td>
                        <td style="font-size:13px; color:#777;">
                            <?= date('d M Y', strtotime($order['created_at'])) ?>
                        </td>
                        <td>
                            <a href="manage_orders.php?id=<?= $order['id'] ?>"
                               class="btn btn-outline-success btn-sm">Update</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>