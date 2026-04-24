<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

$order_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id  = $_SESSION['user_id'];

if (!$order_id) {
    header("Location: order_details.php");
    exit();
}

// Get order
$stmt = $pdo->prepare("SELECT o.* FROM orders o 
                        WHERE o.id = ? AND o.user_id = ?");
$stmt->execute([$order_id, $user_id]);
$order = $stmt->fetch();

if (!$order) {
    header("Location: order_details.php");
    exit();
}

// Get order items
$stmt = $pdo->prepare("SELECT oi.*, p.name as product_name,
                        c.type as category_type
                        FROM order_items oi
                        JOIN products p ON oi.product_id = p.id
                        JOIN categories c ON p.category_id = c.id
                        WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

// Tracking steps
$steps   = ['placed', 'processing', 'shipped', 'delivered'];
$current = array_search($order['order_status'], $steps);

// Step details
$step_info = [
    ['icon' => '📦', 'label' => 'Order Placed',
     'desc' => 'Your order has been placed successfully and payment confirmed.'],
    ['icon' => '⚙️', 'label' => 'Processing',
     'desc' => 'Your seeds are being carefully packed and prepared for shipment.'],
    ['icon' => '🚚', 'label' => 'Shipped',
     'desc' => 'Your order is on its way! Our delivery partner has picked it up.'],
    ['icon' => '🏠', 'label' => 'Delivered',
     'desc' => 'Your order has been delivered. Happy Planting! 🌱'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Order - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #1b5e20; padding: 12px 20px; }
        .navbar-brand { color: #fff; font-weight: 700; font-size: 22px; }
        .nav-link { color: #c8e6c9 !important; }
        .nav-link:hover { color: #fff !important; font-weight: 600; }
        .section-title {
            color: #1b5e20; font-weight: 700; font-size: 26px;
            border-left: 5px solid #66bb6a; padding-left: 12px;
        }
        .track-box {
            background: white; border-radius: 15px;
            padding: 30px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        /* Vertical tracking timeline */
        .timeline { position: relative; padding-left: 50px; }
        .timeline::before {
            content: ''; position: absolute;
            left: 20px; top: 0; bottom: 0;
            width: 3px; background: #e0e0e0;
        }
        .timeline-step {
            position: relative; margin-bottom: 35px;
        }
        .timeline-step:last-child { margin-bottom: 0; }
        .step-icon {
            position: absolute; left: -38px; top: 0;
            width: 42px; height: 42px; border-radius: 50%;
            display: flex; align-items: center;
            justify-content: center; font-size: 20px;
            border: 3px solid #e0e0e0;
            background: white; z-index: 1;
        }
        .step-icon.done {
            background: #2e7d32; border-color: #2e7d32;
            box-shadow: 0 0 0 4px #c8e6c9;
        }
        .step-icon.current {
            background: #ff6f00; border-color: #ff6f00;
            box-shadow: 0 0 0 4px #ffe0b2;
            animation: pulse 1.5s infinite;
        }
        @keyframes pulse {
            0%   { box-shadow: 0 0 0 4px #ffe0b2; }
            50%  { box-shadow: 0 0 0 8px rgba(255,111,0,0.1); }
            100% { box-shadow: 0 0 0 4px #ffe0b2; }
        }
        .step-content { padding-top: 5px; }
        .step-label {
            font-weight: 700; font-size: 16px; color: #1b5e20;
        }
        .step-label.pending { color: #999; }
        .step-desc { font-size: 13px; color: #777; margin-top: 3px; }
        .step-date { font-size: 12px; color: #aaa; margin-top: 2px; }

        /* Order summary */
        .summary-box {
            background: white; border-radius: 15px;
            padding: 25px;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            position: sticky; top: 20px;
        }
        .item-row {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #f5f5f5;
            font-size: 14px;
        }
        .item-row:last-child { border-bottom: none; }
        .status-badge {
            padding: 5px 14px; border-radius: 20px;
            font-size: 13px; font-weight: 600;
        }
        .status-placed     { background: #e3f2fd; color: #1565c0; }
        .status-processing { background: #fff8e1; color: #f57f17; }
        .status-shipped    { background: #e8f5e9; color: #2e7d32; }
        .status-delivered  { background: #1b5e20; color: white; }

        /* Review form */
        .review-box {
            background: #fffde7; border-radius: 15px;
            padding: 25px; border: 2px solid #ffd54f;
            margin-top: 20px;
        }
        .star-rating { display: flex; gap: 8px; margin: 10px 0; }
        .star-rating span {
            font-size: 30px; cursor: pointer; color: #ddd;
            transition: color 0.2s;
        }
        .star-rating span.active { color: #ffa000; }
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
                <li class="nav-item"><a class="nav-link" href="products.php"><i class="bi bi-grid"></i> Products</a></li>
                <li class="nav-item"><a class="nav-link" href="cart.php"><i class="bi bi-cart3"></i> Cart</a></li>
                <li class="nav-item"><a class="nav-link active" href="order_details.php"><i class="bi bi-bag"></i> My Orders</a></li>
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

    <!-- Back link -->
    <a href="order_details.php" style="color:#2e7d32; text-decoration:none; font-size:14px;">
        ← Back to My Orders
    </a>

    <h2 class="section-title mt-3 mb-4">
        📍 Track Order #<?= str_pad($order['id'], 6, '0', STR_PAD_LEFT) ?>
    </h2>

    <div class="row g-4">

        <!-- TRACKING TIMELINE -->
        <div class="col-lg-7">
            <div class="track-box">

                <!-- Order Info -->
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
                    <div>
                        <div style="color:#777; font-size:13px;">Placed on</div>
                        <div style="font-weight:600;">
                            <?= date('d M Y, h:i A', strtotime($order['created_at'])) ?>
                        </div>
                    </div>
                    <span class="status-badge status-<?= $order['order_status'] ?>">
                        <?= ucfirst($order['order_status']) ?>
                    </span>
                </div>

                <!-- Timeline -->
                <div class="timeline">
                    <?php foreach ($step_info as $i => $step):
                        if ($i < $current) $state = 'done';
                        elseif ($i == $current) $state = 'current';
                        else $state = 'pending';
                    ?>
                    <div class="timeline-step">
                        <div class="step-icon <?= $state ?>">
                            <?= $step['icon'] ?>
                        </div>
                        <div class="step-content">
                            <div class="step-label <?= $state == 'pending' ? 'pending' : '' ?>">
                                <?= $step['label'] ?>
                                <?php if ($state == 'current'): ?>
                                    <span style="background:#ff6f00; color:white;
                                                 font-size:10px; padding:2px 8px;
                                                 border-radius:10px; margin-left:8px;">
                                        CURRENT
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="step-desc"><?= $step['desc'] ?></div>
                            <?php if ($state != 'pending'): ?>
                                <div class="step-date">
                                    <?= $i == 0 ? date('d M Y', strtotime($order['created_at'])) : '' ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Delivery Info -->
                <div class="mt-4 p-3"
                     style="background:#f5f5f5; border-radius:10px; font-size:14px;">
                    <strong>📦 Delivery Address:</strong><br>
                    <?= htmlspecialchars($order['full_name']) ?><br>
                    <?= htmlspecialchars($order['address']) ?>,
                    <?= htmlspecialchars($order['city']) ?> -
                    <?= htmlspecialchars($order['pincode']) ?><br>
                    📞 <?= htmlspecialchars($order['mobile']) ?>
                </div>

                <!-- Helpline -->
                <div class="alert alert-success mt-3" style="font-size:13px;">
                    📞 <strong>Need help?</strong> Call our helpline:
                    <strong>+91 98765 43210</strong><br>
                    ✉️ Email: <strong>support@greenlife.com</strong>
                </div>

            </div>

            <!-- REVIEW SECTION - only if delivered -->
            <?php if ($order['order_status'] == 'delivered'): ?>
            <div class="review-box">
                <h5 style="color:#f57f17; font-weight:700;">⭐ Share Your Experience</h5>
                <p style="color:#777; font-size:13px;">
                    How was your GreenLife experience? Your review helps other plant lovers!
                </p>

                <?php foreach ($items as $item):
                    $icon = '🌱';
                    if ($item['category_type'] == 'flowers') $icon = '🌸';
                    elseif ($item['category_type'] == 'vegetables') $icon = '🥦';
                    elseif ($item['category_type'] == 'fruits') $icon = '🍎';
                ?>
                <div style="background:white; border-radius:10px; padding:15px; margin-bottom:15px;">
                    <strong><?= $icon ?> <?= htmlspecialchars($item['product_name']) ?></strong>

                    <!-- Star Rating -->
                    <div class="star-rating" id="stars-<?= $item['product_id'] ?>">
                        <?php for ($s = 1; $s <= 5; $s++): ?>
                            <span onclick="setRating(<?= $item['product_id'] ?>, <?= $s ?>)"
                                  id="star-<?= $item['product_id'] ?>-<?= $s ?>">★</span>
                        <?php endfor; ?>
                    </div>

                    <textarea class="form-control" id="review-<?= $item['product_id'] ?>"
                              rows="2" placeholder="Write your review..."
                              style="font-size:13px; border-color:#ddd;"></textarea>

                    <button onclick="submitReview(<?= $item['product_id'] ?>, <?= $order_id ?>)"
                            class="btn btn-warning btn-sm mt-2"
                            style="font-weight:600;">
                        Submit Review
                    </button>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="col-lg-5">
            <div class="summary-box">
                <h5 style="color:#1b5e20; font-weight:700;
                           border-bottom:2px solid #e8f5e9; padding-bottom:10px;">
                    🧾 Order Summary
                </h5>

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

                <div class="mt-3">
                    <?php if ($order['discount'] > 0): ?>
                    <div class="d-flex justify-content-between mb-1 text-success">
                        <span>Discount (10%):</span>
                        <strong>−₹<?= number_format($order['discount'], 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <div class="d-flex justify-content-between">
                        <span style="font-weight:700; font-size:16px;">Total Paid:</span>
                        <strong style="font-size:18px; color:#e65100;">
                            ₹<?= number_format($order['final_amount'], 2) ?>
                        </strong>
                    </div>
                </div>

                <hr>
                <div style="font-size:13px; color:#777;">
                    <div><strong>Payment ID:</strong><br>
                        <small><?= htmlspecialchars($order['payment_id']) ?></small>
                    </div>
                    <div class="mt-2"><strong>Expected Delivery:</strong><br>
                        Within 7 working days from order date
                    </div>
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
<script>
var ratings = {};

function setRating(productId, rating) {
    ratings[productId] = rating;
    for (var s = 1; s <= 5; s++) {
        var star = document.getElementById('star-' + productId + '-' + s);
        if (star) {
            star.classList.toggle('active', s <= rating);
        }
    }
}

function submitReview(productId, orderId) {
    var rating = ratings[productId] || 0;
    var review = document.getElementById('review-' + productId).value.trim();

    if (!rating) {
        alert('Please select a star rating!');
        return;
    }
    if (!review) {
        alert('Please write a review!');
        return;
    }

    fetch('../actions/review_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'product_id=' + productId +
              '&order_id='  + orderId +
              '&rating='    + rating +
              '&review_text=' + encodeURIComponent(review)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Review submitted! Thank you!');
            location.reload();
        } else {
            alert(data.message || 'Error submitting review.');
        }
    });
}
</script>
</body>
</html>