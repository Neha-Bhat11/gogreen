<?php
$base   = '../';
$active = 'products';
require_once '../includes/session.php';
require_once '../includes/db.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$product_id) {
    header("Location: products.php");
    exit();
}

// Get product
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name, c.type as category_type,
                        c.season, c.location
                        FROM products p
                        JOIN categories c ON p.category_id = c.id
                        WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header("Location: products.php");
    exit();
}

// Get reviews
$stmt = $pdo->prepare("SELECT r.*, u.name as user_name
                        FROM reviews r
                        JOIN users u ON r.user_id = u.id
                        WHERE r.product_id = ?
                        ORDER BY r.created_at DESC");
$stmt->execute([$product_id]);
$reviews = $stmt->fetchAll();

// Average rating
$avg_rating = 0;
if (count($reviews) > 0) {
    $avg_rating = array_sum(array_column($reviews, 'rating')) / count($reviews);
}

$icon = '🌱';
if ($product['category_type'] == 'flowers')    $icon = '🌸';
elseif ($product['category_type'] == 'vegetables') $icon = '🥦';
elseif ($product['category_type'] == 'fruits') $icon = '🍎';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .product-img-box {
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            border-radius: 20px;
            display: flex; align-items: center;
            justify-content: center;
            font-size: 120px; height: 300px;
        }
        @media (max-width:576px) {
            .product-img-box { height: 200px; font-size: 80px; }
        }
        .detail-box {
            background: white; border-radius: 20px;
            padding: 30px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .badge-cat {
            background: #e8f5e9; color: #2e7d32;
            padding: 5px 12px; border-radius: 20px;
            font-size: 13px; font-weight: 500;
        }
        .price-tag { font-size: 32px; font-weight: 700; color: #e65100; }
        .btn-cart {
            background: #2e7d32; color: white; border: none;
            border-radius: 10px; padding: 12px 25px;
            font-size: 16px; font-weight: 600;
        }
        .btn-cart:hover { background: #1b5e20; color: white; }
        .btn-wish {
            background: white; color: #e53935;
            border: 2px solid #e53935; border-radius: 10px;
            padding: 12px 25px; font-size: 16px; font-weight: 600;
        }
        .btn-wish:hover { background: #ffebee; }
        .info-badge {
            background: #f1f8e9; border: 1px solid #c5e1a5;
            border-radius: 8px; padding: 8px 14px;
            font-size: 13px; color: #33691e;
            display: inline-block; margin: 3px;
        }
        .review-card {
            background: white; border-radius: 12px;
            padding: 20px; margin-bottom: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.06);
        }
        .star { color: #ffa000; font-size: 18px; }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4 mb-5">

    <!-- BREADCRUMB -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item">
                <a href="products.php" style="color:#2e7d32;">Products</a>
            </li>
            <li class="breadcrumb-item active">
                <?= htmlspecialchars($product['name']) ?>
            </li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- IMAGE -->
        <div class="col-md-5">
            <div class="product-img-box">
                <?php
                $imgPath = '../assets/images/products/' . $product['image'];
                if ($product['image'] && file_exists($imgPath)) {
                    echo '<img src="'.$imgPath.'" style="width:100%;
                          height:300px; object-fit:cover; border-radius:20px;">';
                } else {
                    echo $icon;
                }
                ?>
            </div>
        </div>

        <!-- DETAILS -->
        <div class="col-md-7">
            <div class="detail-box">
                <span class="badge-cat">
                    <?= htmlspecialchars($product['category_name']) ?>
                </span>
                <h2 style="color:#1b5e20; font-weight:700; margin-top:12px;">
                    <?= htmlspecialchars($product['name']) ?>
                </h2>

                <!-- Rating -->
                <?php if (count($reviews) > 0): ?>
                <div class="mb-2">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <span class="star">
                            <?= $i <= round($avg_rating) ? '★' : '☆' ?>
                        </span>
                    <?php endfor; ?>
                    <small style="color:#777;">
                        (<?= count($reviews) ?> reviews)
                    </small>
                </div>
                <?php endif; ?>

                <div class="price-tag mb-3">
                    ₹<?= number_format($product['price'], 2) ?>
                </div>

                <p style="color:#555; line-height:1.8;">
                    <?= htmlspecialchars($product['description']) ?>
                </p>

                <!-- Info Badges -->
                <div class="mb-3">
                    <span class="info-badge">
                        📦 Stock: <?= $product['stock'] ?> packets
                    </span>
                    <span class="info-badge">🚚 Delivery in 7 days</span>
                    <span class="info-badge">🌍 Karnataka only</span>
                    <span class="info-badge">❌ No return policy</span>
                </div>

                <!-- Buttons -->
                <div class="d-flex gap-3 flex-wrap">
                    <button onclick="addToCart(<?= $product['id'] ?>)"
                            class="btn btn-cart">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <button onclick="addToWishlist(<?= $product['id'] ?>)"
                            class="btn btn-wish">
                        <i class="bi bi-heart"></i> Wishlist
                    </button>
                </div>

                <div class="alert alert-success mt-3" style="font-size:13px;">
                    💰 <strong>Order 10+ seed packets</strong>
                    to get 10% discount on total!
                </div>
            </div>
        </div>
    </div>

    <!-- REVIEWS SECTION -->
    <div class="mt-5">
        <h3 class="section-title mb-4">⭐ Customer Reviews</h3>

        <?php if (empty($reviews)): ?>
            <div class="text-center py-4"
                 style="background:white; border-radius:15px;">
                <div style="font-size:50px;">⭐</div>
                <p style="color:#777;">
                    No reviews yet. Be the first to review after purchase!
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($reviews as $review): ?>
            <div class="review-card">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong style="color:#1b5e20;">
                            <?= htmlspecialchars($review['user_name']) ?>
                        </strong>
                        <div>
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <span class="star">
                                    <?= $i <= $review['rating'] ? '★' : '☆' ?>
                                </span>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <small style="color:#999;">
                        <?= date('d M Y', strtotime($review['created_at'])) ?>
                    </small>
                </div>
                <p style="color:#555; margin-top:10px; margin-bottom:0;">
                    <?= htmlspecialchars($review['review_text']) ?>
                </p>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function addToCart(productId) {
    fetch('../actions/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Added to cart!');
        } else {
            alert(data.message || 'Could not add to cart.');
        }
    });
}

function addToWishlist(productId) {
    fetch('../actions/wishlist_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=add&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('💚 Added to wishlist!');
        } else {
            alert(data.message || 'Could not add to wishlist.');
        }
    });
}
</script>
</body>
</html>