<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
require_once '../includes/db.php';

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status   = trim($_POST['order_status']);

    $allowed = ['placed', 'processing', 'shipped', 'delivered'];
    if (in_array($status, $allowed)) {
        $stmt = $pdo->prepare("UPDATE orders SET order_status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
    }
    header("Location: manage_orders.php?success=1");
    exit();
}

// Get all orders
$orders = $pdo->query("SELECT o.*, u.name as user_name, u.email as user_email
                        FROM orders o
                        JOIN users u ON o.user_id = u.id
                        ORDER BY o.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Orders - GreenLife Admin</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .sidebar {
            background: #1b5e20; min-height: 100vh; padding: 20px 0;
            position: fixed; width: 240px; top: 0; left: 0; z-index: 100;
        }
        .sidebar-brand {
            color: white; font-size: 20px; font-weight: 700;
            padding: 15px 20px; border-bottom: 1px solid #2e7d32; margin-bottom: 10px;
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
        .table-box {
            background: white; border-radius: 15px;
            padding: 25px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
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
    <a href="dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="manage_orders.php" class="sidebar-link active"><i class="bi bi-bag"></i> Manage Orders</a>
    <a href="manage_products.php" class="sidebar-link"><i class="bi bi-grid"></i> Manage Products</a>
    <a href="manage_users.php" class="sidebar-link"><i class="bi bi-people"></i> Manage Users</a>
    <a href="manage_reviews.php" class="sidebar-link"><i class="bi bi-star"></i> Manage Reviews</a>
    <a href="../index.php" class="sidebar-link"><i class="bi bi-globe"></i> View Website</a>
    <a href="admin_logout.php" class="sidebar-link" style="color:#ef9a9a;">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>

<div class="main-content">
    <div class="top-bar">
        <h5 style="margin:0; color:#1b5e20; font-weight:700;">📦 Manage Orders</h5>
        <span style="color:#777; font-size:14px;"><?= count($orders) ?> total orders</span>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Order status updated successfully!</div>
    <?php endif; ?>

    <div class="table-box">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background:#e8f5e9;">
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Payment</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($orders as $order):
                    // Get item count
                    $stmt = $pdo->prepare("SELECT SUM(quantity) FROM order_items WHERE order_id = ?");
                    $stmt->execute([$order['id']]);
                    $item_count = $stmt->fetchColumn();
                ?>
                <tr>
                    <td><strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong></td>
                    <td>
                        <strong><?= htmlspecialchars($order['user_name']) ?></strong><br>
                        <small style="color:#999;"><?= htmlspecialchars($order['mobile']) ?></small>
                    </td>
                    <td><?= $item_count ?> packets</td>
                    <td style="color:#e65100; font-weight:600;">
                        ₹<?= number_format($order['final_amount'], 2) ?>
                    </td>
                    <td>
                        <span class="badge <?= $order['payment_status']=='paid' ? 'bg-success' : 'bg-warning' ?>">
                            <?= ucfirst($order['payment_status']) ?>
                        </span>
                    </td>
                    <td>
                        <span class="status-badge status-<?= $order['order_status'] ?>">
                            <?= ucfirst($order['order_status']) ?>
                        </span>
                    </td>
                    <td style="font-size:12px; color:#777;">
                        <?= date('d M Y', strtotime($order['created_at'])) ?>
                    </td>
                    <td>
                        <form method="POST" class="d-flex gap-1">
                            <input type="hidden" name="order_id" value="<?= $order['id'] ?>">
                            <select name="order_status" class="form-select form-select-sm"
                                    style="width:130px;">
                                <option value="placed"     <?= $order['order_status']=='placed'     ? 'selected':'' ?>>Placed</option>
                                <option value="processing" <?= $order['order_status']=='processing' ? 'selected':'' ?>>Processing</option>
                                <option value="shipped"    <?= $order['order_status']=='shipped'    ? 'selected':'' ?>>Shipped</option>
                                <option value="delivered"  <?= $order['order_status']=='delivered'  ? 'selected':'' ?>>Delivered</option>
                            </select>
                            <button type="submit" name="update_status"
                                    class="btn btn-success btn-sm">✓</button>
                        </form>
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