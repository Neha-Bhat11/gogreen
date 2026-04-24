<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$order_id) {
    header("Location: home.php");
    exit();
}

// Get order details
$stmt = $pdo->prepare("SELECT o.*, 
                        COUNT(oi.id) as total_items
                        FROM orders o
                        LEFT JOIN order_items oi ON o.id = oi.order_id
                        WHERE o.id = ? AND o.user_id = ?
                        GROUP BY o.id");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Successful - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #1b5e20; padding: 12px 20px; }
        .navbar-brand { color: #fff; font-weight: 700; font-size: 22px; }
        .nav-link { color: #c8e6c9 !important; }
        .nav-link:hover { color: #fff !important; }
        .success-box {
            background: white; border-radius: 20px;
            padding: 40px; text-align: center;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
        }
        .success-icon {
            width: 100px; height: 100px;
            background: #e8f5e9; border-radius: 50%;
            display: flex; align-items: center;
            justify-content: center; font-size: 50px;
            margin: 0 auto 20px;
            animation: pop 0.5s ease;
        }
        @keyframes pop {
            0%   { transform: scale(0); }
            70%  { transform: scale(1.2); }
            100% { transform: scale(1); }
        }
        .order-detail-row {
            display: flex; justify-content: space-between;
            padding: 10px 0; border-bottom: 1px solid #f5f5f5;
            font-size: 15px;
        }
        .tracking-bar {
            display: flex; justify-content: space-between;
            align-items: center; margin: 20px 0;
            position: relative;
        }
        .tracking-bar::before {
            content: '';
            position: absolute; top: 20px; left: 10%;
            width: 80%; height: 3px; background: #c8e6c9; z-index: 0;
        }
        .track-step {
            text-align: center; position: relative; z-index: 1;
        }
        .track-icon {
            width: 40px; height: 40px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; margin: 0 auto 5px;
        }
        .track-icon.done { background: #2e7d32; color: white; }
        .track-icon.pending { background: #e0e0e0; color: #999; }
        .track-label { font-size: 11px; color: #777; }
        .btn-green {
            background: #2e7d32; color: white; border: none;
            border-radius: 10px; padding: 12px 25px;
            font-size: 15px; font-weight: 600; text-decoration: none;
            display: inline-block;
        }
        .btn-green:hover { background: #1b5e20; color: white; }
        footer { background-color: #1b5e20; color: #c8e6c9; padding: 30px 0; margin-top: 60px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php">🌱 GreenLife</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item"><a class="nav-link" href="home.php"><i class="bi bi-house"></i> Home</a></li>
            <li class="nav-item"><a class="nav-link" href="order_details.php"><i class="bi bi-bag"></i> My Orders</a></li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php"
                   style="background:#2e7d32; border-radius:20px; padding:6px 16px;">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="success-box">

                <!-- Success Icon -->
                <div class="success-icon">✅</div>
                <h2 style="color:#2e7d32; font-weight:700;">Order Placed Successfully!</h2>
                <p style="color:#777;">
                    Thank you, <strong><?= htmlspecialchars($order['full_name']) ?></strong>!
                    Your seeds are on their way 🌱
                </p>

                <!-- Order ID -->
                <div class="alert alert-success" style="font-size:14px;">
                    📦 Order ID: <strong>#<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?></strong>
                    &nbsp;|&nbsp;
                    Payment ID: <strong><?= htmlspecialchars($order['payment_id']) ?></strong>
                </div>

                <!-- Order Details -->
                <div style="text-align:left; background:#f9f9f9; border-radius:12px; padding:20px; margin:20px 0;">
                    <h6 style="color:#1b5e20; font-weight:700; margin-bottom:15px;">📋 Order Details</h6>
                    <div class="order-detail-row">
                        <span>Items Ordered:</span>
                        <strong><?= $order['total_items'] ?> products</strong>
                    </div>
                    <div class="order-detail-row">
                        <span>Total Amount:</span>
                        <strong>₹<?= number_format($order['total_amount'], 2) ?></strong>
                    </div>
                    <?php if ($order['discount'] > 0): ?>
                    <div class="order-detail-row text-success">
                        <span>Discount:</span>
                        <strong>− ₹<?= number_format($order['discount'], 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="order-detail-row">
                        <span style="font-weight:700;">Amount Paid:</span>
                        <strong style="color:#e65100; font-size:18px;">
                            ₹<?= number_format($order['final_amount'], 2) ?>
                        </strong>
                    </div>
                    <div class="order-detail-row">
                        <span>Delivery Address:</span>
                        <strong style="text-align:right; max-width:60%;">
                            <?= htmlspecialchars($order['address']) ?>,
                            <?= htmlspecialchars($order['city']) ?> -
                            <?= htmlspecialchars($order['pincode']) ?>
                        </strong>
                    </div>
                    <div class="order-detail-row">
                        <span>Expected Delivery:</span>
                        <strong style="color:#2e7d32;">
                            Within 7 working days
                        </strong>
                    </div>
                </div>

                <!-- Order Tracking Bar -->
                <h6 style="color:#1b5e20; font-weight:700; text-align:left;">
                    📍 Order Status
                </h6>
                <div class="tracking-bar">
                    <div class="track-step">
                        <div class="track-icon done">📦</div>
                        <div class="track-label">Placed</div>
                    </div>
                    <div class="track-step">
                        <div class="track-icon pending">⚙️</div>
                        <div class="track-label">Processing</div>
                    </div>
                    <div class="track-step">
                        <div class="track-icon pending">🚚</div>
                        <div class="track-label">Shipped</div>
                    </div>
                    <div class="track-step">
                        <div class="track-icon pending">🏠</div>
                        <div class="track-label">Delivered</div>
                    </div>
                </div>

                <!-- Important Notes -->
                <div class="alert alert-warning" style="font-size:13px; text-align:left;">
                    ⚠️ <strong>Important:</strong><br>
                    • No return policy on seeds<br>
                    • Delivery only within Karnataka<br>
                    • For queries call: <strong>+91 98765 43210</strong>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-3 justify-content-center flex-wrap mt-3">
                    <a href="order_details.php" class="btn-green">
                        <i class="bi bi-bag"></i> View My Orders
                    </a>
                    <a href="products.php"
                       style="background:white; color:#2e7d32; border:2px solid #2e7d32;
                              border-radius:10px; padding:12px 25px; font-size:15px;
                              font-weight:600; text-decoration:none;">
                        🌱 Continue Shopping
                    </a>
                </div>

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
</body>
</html>