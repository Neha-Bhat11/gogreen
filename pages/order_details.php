<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get all orders for this user
$stmt = $pdo->prepare("SELECT o.*,
                        COUNT(oi.id) as total_items
                        FROM orders o
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        WHERE o.user_id = ?
                        GROUP BY o.id
                        ORDER BY o.created_at DESC");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #1b5e20; padding: 12px 20px; }
        .navbar-brand { color: #fff; font-weight: 700; font-size: 22px; }
        .nav-link { color: #c8e6c9 !important; }
        .nav-link:hover, .nav-link.active { color: #fff !important; font-weight: 600; }
        .section-title {
            color: #1b5e20; font-weight: 700; font-size: 26px;
            border-left: 5px solid #66bb6a; padding-left: 12px;
        }
        .order-card {
            background: white; border-radius: 15px;
            padding: 25px; margin-bottom: 20px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            border-left: 5px solid #2e7d32;
        }
        .order-card:hover { box-shadow: 0 5px 20px rgba(0,128,0,0.15); }
        .status-badge {
            padding: 5px 14px; border-radius: 20px;
            font-size: 13px; font-weight: 600;
        }
        .status-placed     { background: #e3f2fd; color: #1565c0; }
        .status-processing { background: #fff8e1; color: #f57f17; }
        .status-shipped    { background: #e8f5e9; color: #2e7d32; }
        .status-delivered  { background: #1b5e20; color: white; }
        .payment-badge {
            padding: 4px 10px; border-radius: 20px; font-size: 12px;
        }
        .payment-paid   { background: #e8f5e9; color: #2e7d32; }
        .payment-pending{ background: #fff8e1; color: #f57f17; }
        .payment-failed { background: #ffebee; color: #c62828; }
        .order-items-list {
            background: #f9f9f9; border-radius: 10px;
            padding: 15px; margin-top: 15px;
        }
        .item-row {
            display: flex; justify-content: space-between;
            padding: 6px 0; border-bottom: 1px solid #eee;
            font-size: 14px;
        }
        .item-row:last-child { border-bottom: none; }
        .tracking-bar {
            display: flex; justify-content: space-between;
            align-items: center; margin: 15px 0;
            position: relative;
        }
        .tracking-bar::before {
            content: ''; position: absolute; top: 18px;
            left: 8%; width: 84%; height: 3px;
            background: #e0e0e0; z-index: 0;
        }
        .track-step { text-align: center; position: relative; z-index: 1; }
        .track-icon {
            width: 36px; height: 36px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 16px; margin: 0 auto 4px;
        }
        .track-icon.done    { background: #2e7d32; color: white; }
        .track-icon.pending { background: #e0e0e0; color: #999; }
        .track-label { font-size: 10px; color: #777; }
        .btn-review {
            background: #ff6f00; color: white; border: none;
            border-radius: 8px; padding: 6px 14px; font-size: 13px;
            cursor: pointer;
        }
        .btn-review:hover { background: #e65100; }
        footer { background-color: #1b5e20; color: #c8e6c9; padding: 30px 0; margin-top: 60px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php">🌱 GreenLife</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="home.php"><i class="bi bi-house"></i> Home</a></li>
                <li class="nav-item"><a class="nav-link" href="about.php"><i class="bi bi-info-circle"></i> About</a></li>
                <li class="nav-item"><a class="nav-link" href="products.php"><i class="bi bi-grid"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart3"></i> Cart</a></li>
                <li class="nav-item"><a class="nav-link" href="wishlist.php"><i class="bi bi-heart"></i> Wishlist</a></li>
                <li class="nav-item"><a class="nav-link active" href="order_details.php"><i class="bi bi-bag"></i> My Orders</a></li>
                <li class="nav-item">
    <a class="nav-link" href="profile.php">
        <i class="bi bi-person"></i> Profile
    </a>
</li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link" href="../logout.php"
                       style="background:#2e7d32; border-radius:20px; padding:6px 16px;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <h2 class="section-title mb-4">🛍️ My Orders</h2>

    <?php if (empty($orders)): ?>
        <div class="text-center py-5" style="background:white; border-radius:15px;">
            <div style="font-size:80px;">📦</div>
            <h4 style="color:#1b5e20;">No orders yet!</h4>
            <p style="color:#777;">Start shopping to place your first order.</p>
            <a href="products.php" class="btn btn-success mt-2">Browse Products</a>
        </div>
    <?php else: ?>
        <?php foreach ($orders as $order):
            $status_class = 'status-' . $order['order_status'];
            $pay_class    = 'payment-' . $order['payment_status'];

            // Get order items
            $stmt2 = $pdo->prepare("SELECT oi.*, p.name as product_name,
                                    c.type as category_type
                                    FROM order_items oi
                                    JOIN products p ON oi.product_id = p.id
                                    JOIN categories c ON p.category_id = c.id
                                    WHERE oi.order_id = ?");
            $stmt2->execute([$order['id']]);
            $items = $stmt2->fetchAll();

            // Track steps
            $steps   = ['placed', 'processing', 'shipped', 'delivered'];
            $current = array_search($order['order_status'], $steps);
        ?>
        <div class="order-card">
            <!-- Order Header -->
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                <div>
                    <h5 style="color:#1b5e20; font-weight:700; margin:0;">
                        Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
                    </h5>
                    <small style="color:#999;">
                        Placed on <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                    </small>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <span class="status-badge <?= $status_class ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                    <span class="payment-badge <?= $pay_class ?>">
                        💳 <?= ucfirst($order['payment_status']) ?>
                    </span>
                </div>
            </div>

            <!-- Tracking Bar -->
            <div class="tracking-bar mt-3">
                <?php
                $track_icons  = ['📦', '⚙️', '🚚', '🏠'];
                $track_labels = ['Placed', 'Processing', 'Shipped', 'Delivered'];
                foreach ($steps as $i => $step):
                    $done = $i <= $current ? 'done' : 'pending';
                ?>
                <div class="track-step">
                    <div class="track-icon <?= $done ?>"><?= $track_icons[$i] ?></div>
                    <div class="track-label"><?= $track_labels[$i] ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Items -->
            <div class="order-items-list">
                <?php foreach ($items as $item):
                    $icon = '🌱';
                    if ($item['category_type'] == 'flowers') $icon = '🌸';
                    elseif ($item['category_type'] == 'vegetables') $icon = '🥦';
                    elseif ($item['category_type'] == 'fruits') $icon = '🍎';
                ?>
                <div class="item-row">
                    <span><?= $icon ?> <?= htmlspecialchars($item['product_name']) ?>
                        <small style="color:#999;">x<?= $item['quantity'] ?></small>
                    </span>
                    <span style="color:#e65100; font-weight:600;">
                        ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Order Footer -->
            <div class="d-flex justify-content-between align-items-center mt-3 flex-wrap gap-2">
                <div>
                    <?php if ($order['discount'] > 0): ?>
                        <small style="color:#2e7d32;">
                            🎉 Discount: −₹<?= number_format($order['discount'], 2) ?>
                        </small><br>
                    <?php endif; ?>
                    <strong style="font-size:17px;">
                        Total Paid: <span style="color:#e65100;">
                            ₹<?= number_format($order['final_amount'], 2) ?>
                        </span>
                    </strong>
                </div>
                <div class="d-flex gap-2 flex-wrap">
                    <?php if ($order['order_status'] == 'delivered'): ?>
                        <button onclick="openReview(<?= $order['id'] ?>)"
                                class="btn-review">
                            ⭐ Write Review
                        </button>
                    <?php endif; ?>
                    <a href="order_tracking.php?id=<?= $order['id'] ?>"
                       class="btn btn-outline-success btn-sm">
                        📍 Track Order
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Review Modal -->
<div class="modal fade" id="reviewModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background:#1b5e20; color:white;">
                <h5 class="modal-title">⭐ Write a Review</h5>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="reviewProductList"></div>
            </div>
        </div>
    </div>
</div>

<footer>
    <div class="container text-center">
        <h5 style="color:white;">🌱 GreenLife</h5>
        <p style="font-size:12px; opacity:0.7;">© 2026 GreenLife. All rights reserved.</p>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function openReview(orderId) {
    window.location.href = 'order_tracking.php?id=' + orderId + '&review=1';
}
</script>
</body>
</html>