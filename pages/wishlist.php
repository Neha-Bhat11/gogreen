<?php
$base   = '../';
$active = 'wishlist';
require_once '../includes/session.php';
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT w.id as wishlist_id,
                        p.id as product_id,
                        p.name, p.price, p.image,
                        c.type as category_type,
                        c.name as category_name
                        FROM wishlist w
                        LEFT JOIN products p ON w.product_id = p.id
                        LEFT JOIN categories c ON p.category_id = c.id
                        WHERE w.user_id = ?
                        ORDER BY w.added_at DESC");
$stmt->execute([$user_id]);
$wishlist_items = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wishlist - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .wish-card {
            background: white; border-radius: 15px; overflow: hidden;
            box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            transition: transform 0.2s; height: 100%;
        }
        .wish-card:hover { transform: translateY(-5px); }
        .wish-img {
            width: 100%; height: 160px;
            background: linear-gradient(135deg, #e8f5e9, #c8e6c9);
            display: flex; align-items: center;
            justify-content: center; font-size: 55px;
        }
        .wish-body { padding: 15px; }
        .product-cat {
            background: #e8f5e9; color: #2e7d32; font-size: 11px;
            padding: 3px 8px; border-radius: 20px;
            display: inline-block; margin-bottom: 8px;
        }
        .btn-move {
            background: #2e7d32; color: white; border: none;
            border-radius: 8px; padding: 7px 12px; font-size: 13px;
            width: 100%; margin-top: 8px; cursor: pointer;
        }
        .btn-move:hover { background: #1b5e20; }
        .btn-remove-wish {
            background: white; color: #e53935;
            border: 1px solid #e53935; border-radius: 8px;
            padding: 7px 12px; font-size: 13px;
            width: 100%; margin-top: 5px; cursor: pointer;
        }
        .btn-remove-wish:hover { background: #ffebee; }

        @media (max-width: 576px) {
            .wish-img { height: 130px; font-size: 45px; }
        }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <h2 class="section-title mb-4">💚 My Wishlist</h2>

    <?php if (empty($wishlist_items)): ?>
        <div class="text-center py-5"
             style="background:white; border-radius:15px;">
            <div style="font-size:80px;">💚</div>
            <h4 style="color:#1b5e20;">Your wishlist is empty!</h4>
            <p style="color:#777;">Save products you love for later.</p>
            <a href="products.php" class="btn btn-success mt-2">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <p style="color:#777; font-size:14px;" class="mb-3">
            You have <strong><?= count($wishlist_items) ?></strong> saved item(s)
        </p>
        <div class="row g-4">
            <?php foreach ($wishlist_items as $item):
                $icon = '🌱';
                if ($item['category_type'] == 'flowers')     $icon = '🌸';
                elseif ($item['category_type'] == 'vegetables') $icon = '🥦';
                elseif ($item['category_type'] == 'fruits')  $icon = '🍎';
            ?>
            <div class="col-md-3 col-6"
                 id="wish-<?= $item['wishlist_id'] ?>">
                <div class="wish-card">
                    <a href="product_detail.php?id=<?= $item['product_id'] ?>"
                       style="text-decoration:none;">
                        <div class="wish-img"><?= $icon ?></div>
                    </a>
                    <div class="wish-body">
                        <span class="product-cat">
                            <?= htmlspecialchars($item['category_name']) ?>
                        </span>
                        <div style="color:#1b5e20; font-weight:600; font-size:15px;">
                            <?= htmlspecialchars($item['name']) ?>
                        </div>
                        <div style="color:#e65100; font-weight:700;
                                    font-size:18px; margin-top:5px;">
                            ₹<?= number_format($item['price'], 2) ?>
                        </div>
                        <button onclick="moveToCart(<?= $item['wishlist_id'] ?>,
                                         <?= $item['product_id'] ?>)"
                                class="btn-move">
                            <i class="bi bi-cart-plus"></i> Move to Cart
                        </button>
                        <button onclick="removeWishlist(<?= $item['wishlist_id'] ?>)"
                                class="btn-remove-wish">
                            <i class="bi bi-trash"></i> Remove
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function moveToCart(wishlistId, productId) {
    fetch('../actions/wishlist_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=move_to_cart&wishlist_id=' + wishlistId +
              '&product_id=' + productId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert('✅ Moved to cart!');
            location.reload();
        } else {
            alert(data.message || 'Error!');
        }
    });
}

function removeWishlist(wishlistId) {
    if (!confirm('Remove from wishlist?')) return;
    fetch('../actions/wishlist_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=remove&wishlist_id=' + wishlistId
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            document.getElementById('wish-' + wishlistId).remove();
        }
    });
}
</script>
</body>
</html>