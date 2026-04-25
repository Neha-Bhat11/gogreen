<?php
$base   = '../';
$active = 'cart';
require_once '../includes/session.php';
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity,
                        p.id as product_id, p.name, p.price, p.image,
                        cat.type as category_type
                        FROM cart c
                        JOIN products p ON c.product_id = p.id
                        JOIN categories cat ON p.category_id = cat.id
                        WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Calculate totals
$total_qty    = 0;
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_qty    += $item['quantity'];
    $total_amount += $item['price'] * $item['quantity'];
}
$discount     = ($total_qty >= 10) ? $total_amount * 0.10 : 0;
$final_amount = $total_amount - $discount;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .cart-table {
            background: white; border-radius: 15px;
            overflow: hidden; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .cart-table th {
            background: #1b5e20; color: white; padding: 15px;
        }
        .cart-table td {
            padding: 15px; vertical-align: middle;
            border-bottom: 1px solid #f0f0f0;
        }
        .product-icon { font-size: 36px; }
        .qty-btn {
            background: #e8f5e9; border: none; border-radius: 5px;
            width: 30px; height: 30px; font-size: 16px;
            color: #1b5e20; cursor: pointer;
        }
        .qty-btn:hover { background: #c8e6c9; }
        .qty-display {
            width: 40px; text-align: center;
            border: 1px solid #ddd; border-radius: 5px; padding: 3px;
        }
        .summary-box {
            background: white; border-radius: 15px;
            padding: 25px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            position: sticky; top: 20px;
        }
        .discount-badge {
            background: #e8f5e9; color: #2e7d32;
            border-radius: 8px; padding: 8px 12px; font-size: 13px;
        }
        .btn-checkout {
            background: #2e7d32; color: white; border: none;
            border-radius: 10px; padding: 12px; font-size: 16px;
            width: 100%; font-weight: 600;
        }
        .btn-checkout:hover { background: #1b5e20; color: white; }
        .btn-remove {
            background: none; border: none;
            color: #e53935; font-size: 18px; cursor: pointer;
        }

        /* Mobile cart */
        @media (max-width: 768px) {
            .cart-table { overflow-x: auto; display: block; }
            .cart-table table { min-width: 480px; }
            .summary-box { position: relative !important; top: 0 !important; }
        }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <h2 class="section-title mb-4">🛒 Your Cart</h2>

    <?php if (empty($cart_items)): ?>
        <div class="text-center py-5"
             style="background:white; border-radius:15px;">
            <div style="font-size:80px;">🛒</div>
            <h4 style="color:#1b5e20;">Your cart is empty!</h4>
            <p style="color:#777;">Add some seeds to get started.</p>
            <a href="products.php" class="btn btn-success mt-2">
                Browse Products
            </a>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <!-- CART ITEMS -->
            <div class="col-lg-8">
             <?php
// Check if first order
$stmt_fo = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$stmt_fo->execute([$user_id]);
$existing_orders = $stmt_fo->fetchColumn();
?>

<?php if ($existing_orders == 0): ?>
    <div class="alert alert-success mb-3">
        🎉 <strong>First Order Special!</strong>
        You get <strong>10% discount</strong>
        automatically on your first order! 🌱
    </div>
<?php elseif ($total_qty >= 10): ?>
    <div class="alert alert-success mb-3">
        🎉 <strong>10% discount applied!</strong>
        You ordered <?= $total_qty ?> packets.
    </div>
<?php else: ?>
    <div class="alert alert-info mb-3">
        💡 Order <strong><?= 10 - $total_qty ?> more</strong>
        seed packets to get 10% discount!
    </div>
<?php endif; ?>   

                <div class="cart-table">
                    <table class="table mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Subtotal</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($cart_items as $item):
                            $icon = '🌱';
                            if ($item['category_type'] == 'flowers')     $icon = '🌸';
                            elseif ($item['category_type'] == 'vegetables') $icon = '🥦';
                            elseif ($item['category_type'] == 'fruits')  $icon = '🍎';
                        ?>
                        <tr id="row-<?= $item['cart_id'] ?>">
                            <td>
                                <span class="product-icon"><?= $icon ?></span>
                                <strong style="color:#1b5e20;">
                                    <?= htmlspecialchars($item['name']) ?>
                                </strong>
                            </td>
                            <td>₹<?= number_format($item['price'], 2) ?></td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <button class="qty-btn"
                                            onclick="updateQty(<?= $item['cart_id'] ?>, -1)">
                                        −
                                    </button>
                                    <input type="text" class="qty-display"
                                           id="qty-<?= $item['cart_id'] ?>"
                                           value="<?= $item['quantity'] ?>" readonly>
                                    <button class="qty-btn"
                                            onclick="updateQty(<?= $item['cart_id'] ?>, 1)">
                                        +
                                    </button>
                                </div>
                            </td>
                            <td id="sub-<?= $item['cart_id'] ?>">
                                ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </td>
                            <td>
                                <button class="btn-remove"
                                        onclick="removeItem(<?= $item['cart_id'] ?>)">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ORDER SUMMARY -->
            <div class="col-lg-4">
                <div class="summary-box">
                    <h5 style="color:#1b5e20; font-weight:700;
                               border-bottom:2px solid #e8f5e9;
                               padding-bottom:10px;">
                        Order Summary
                    </h5>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Total Items:</span>
                        <strong><?= $total_qty ?> packets</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Subtotal:</span>
                        <strong>₹<?= number_format($total_amount, 2) ?></strong>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-2 text-success">
                        <span>Discount (10%):</span>
                        <strong>− ₹<?= number_format($discount, 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between mb-3">
                        <span style="font-size:18px; font-weight:700;">
                            Final Amount:
                        </span>
                        <strong style="font-size:20px; color:#e65100;">
                            ₹<?= number_format($final_amount, 2) ?>
                        </strong>
                    </div>
                    <div class="discount-badge mb-3">
                        🚚 Free delivery across Karnataka in 7 days<br>
                        💰 Order 10+ packets for 10% off
                    </div>
                    <a href="checkout.php" class="btn btn-checkout">
                        Proceed to Checkout →
                    </a>
                    <a href="products.php"
                       class="btn btn-outline-success w-100 mt-2">
                        Continue Shopping
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function updateQty(cartId, change) {
    var qtyEl  = document.getElementById('qty-' + cartId);
    var newQty = parseInt(qtyEl.value) + change;
    if (newQty < 1) newQty = 1;
    fetch('../actions/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=update&cart_id=' + cartId + '&quantity=' + newQty
    })
    .then(res => res.json())
    .then(data => { if (data.success) location.reload(); });
}

function removeItem(cartId) {
    if (!confirm('Remove this item from cart?')) return;
    fetch('../actions/cart_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=remove&cart_id=' + cartId
    })
    .then(res => res.json())
    .then(data => { if (data.success) location.reload(); });
}
</script>
</body>
</html>