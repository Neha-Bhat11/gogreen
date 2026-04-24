<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit();
}
require_once '../includes/db.php';

// Delete review
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM reviews WHERE id = ?")->execute([$id]);
    header("Location: manage_reviews.php?success=1");
    exit();
}

$reviews = $pdo->query("SELECT r.*, u.name as user_name,
                         p.name as product_name
                         FROM reviews r
                         JOIN users u ON r.user_id = u.id
                         JOIN products p ON r.product_id = p.id
                         ORDER BY r.created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Reviews - GreenLife Admin</title>
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
            margin-bottom: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .table-box {
            background: white; border-radius: 15px;
            padding: 25px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .star { color: #ffa000; }
    </style>
</head>
<body>
<div class="sidebar">
    <div class="sidebar-brand">🌱 GreenLife Admin</div>
    <a href="dashboard.php" class="sidebar-link"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="manage_orders.php" class="sidebar-link"><i class="bi bi-bag"></i> Manage Orders</a>
    <a href="manage_products.php" class="sidebar-link"><i class="bi bi-grid"></i> Manage Products</a>
    <a href="manage_users.php" class="sidebar-link"><i class="bi bi-people"></i> Manage Users</a>
    <a href="manage_reviews.php" class="sidebar-link active"><i class="bi bi-star"></i> Manage Reviews</a>
    <a href="../index.php" class="sidebar-link"><i class="bi bi-globe"></i> View Website</a>
    <a href="admin_logout.php" class="sidebar-link" style="color:#ef9a9a;">
        <i class="bi bi-box-arrow-right"></i> Logout
    </a>
</div>
<div class="main-content">
    <div class="top-bar">
        <h5 style="margin:0; color:#1b5e20; font-weight:700;">⭐ Manage Reviews</h5>
        <span style="color:#777;"><?= count($reviews) ?> reviews</span>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">✅ Review deleted!</div>
    <?php endif; ?>

    <div class="table-box">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead style="background:#e8f5e9;">
                    <tr>
                        <th>#</th>
                        <th>User</th>
                        <th>Product</th>
                        <th>Rating</th>
                        <th>Review</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (empty($reviews)): ?>
                    <tr>
                        <td colspan="7" class="text-center" style="color:#777; padding:30px;">
                            No reviews yet
                        </td>
                    </tr>
                <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                <tr>
                    <td><?= $r['id'] ?></td>
                    <td><strong><?= htmlspecialchars($r['user_name']) ?></strong></td>
                    <td><?= htmlspecialchars($r['product_name']) ?></td>
                    <td>
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <span class="star"><?= $i <= $r['rating'] ? '★' : '☆' ?></span>
                        <?php endfor; ?>
                    </td>
                    <td style="max-width:200px; font-size:13px;">
                        <?= htmlspecialchars($r['review_text']) ?>
                    </td>
                    <td style="font-size:12px; color:#777;">
                        <?= date('d M Y', strtotime($r['created_at'])) ?>
                    </td>
                    <td>
                        <a href="manage_reviews.php?delete=<?= $r['id'] ?>"
                           class="btn btn-outline-danger btn-sm"
                           onclick="return confirm('Delete this review?')">
                            <i class="bi bi-trash"></i>
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>