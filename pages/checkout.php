<?php
$base   = '../';
$active = 'cart';
require_once '../includes/session.php';
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Get cart items
$stmt = $pdo->prepare("SELECT c.id as cart_id, c.quantity,
                        p.id as product_id, p.name, p.price,
                        cat.type as category_type
                        FROM cart c
                        JOIN products p ON c.product_id = p.id
                        JOIN categories cat ON p.category_id = cat.id
                        WHERE c.user_id = ?");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// Redirect if cart empty
if (empty($cart_items)) {
    header("Location: cart.php");
    exit();
}

// Calculate totals
$total_qty    = 0;
$total_amount = 0;
foreach ($cart_items as $item) {
    $total_qty    += $item['quantity'];
    $total_amount += $item['price'] * $item['quantity'];
}
$discount     = ($total_qty >= 10) ? $total_amount * 0.10 : 0;
$final_amount = $total_amount - $discount;

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .checkout-box {
            background: white; border-radius: 15px;
            padding: 30px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .summary-box {
            background: white; border-radius: 15px;
            padding: 25px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
            position: sticky; top: 20px;
        }
        .section-heading {
            color: #1b5e20; font-weight: 700;
            border-bottom: 2px solid #e8f5e9;
            padding-bottom: 8px; margin-bottom: 20px;
        }
        .order-item {
            display: flex; justify-content: space-between;
            padding: 8px 0; border-bottom: 1px solid #f5f5f5;
            font-size: 14px;
        }
        .btn-place-order {
            background: #2e7d32; color: white; border: none;
            border-radius: 10px; padding: 14px; font-size: 17px;
            width: 100%; font-weight: 700;
        }
        .btn-place-order:hover { background: #1b5e20; color: white; }
        .policy-note {
            background: #fff8e1; border-left: 4px solid #ffa000;
            border-radius: 8px; padding: 12px 15px; font-size: 13px;
        }

        @media (max-width: 768px) {
            .checkout-box { padding: 20px; }
            .summary-box { position: relative !important; top: 0 !important; }
        }
    </style>
</head>
<body>

<?php require_once '../includes/header.php'; ?>

<div class="container mt-4 mb-5">
    <h2 class="section-title mb-4">🛒 Checkout</h2>

    <div class="row g-4">

        <!-- DELIVERY FORM -->
        <div class="col-lg-7">
            <div class="checkout-box">
                <h5 class="section-heading">📦 Delivery Details</h5>

                <div class="row g-3" id="deliveryForm">

                    <div class="col-12">
                        <label class="form-label">Full Name *</label>
                        <input type="text" class="form-control" id="full_name"
                               value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Email Address *</label>
                        <input type="email" class="form-control" id="email"
                               value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Mobile Number *</label>
                        <input type="text" class="form-control" id="mobile"
                               value="<?= htmlspecialchars($user['mobile']) ?>"
                               maxlength="10" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Alternative Mobile</label>
                        <input type="text" class="form-control" id="alt_mobile"
                               placeholder="Optional" maxlength="10">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">City / Town *</label>
                        <input type="text" class="form-control" id="city"
                               value="<?= htmlspecialchars($user['place']) ?>" required>
                    </div>

                    <div class="col-12">
                        <label class="form-label">Full Address *</label>
                        <textarea class="form-control" id="address" rows="3"
                                  placeholder="House no, Street, Area..."
                                  required></textarea>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Pincode *</label>
                        <input type="text" class="form-control" id="pincode"
                               placeholder="6-digit pincode" maxlength="6" required>
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">State</label>
                        <input type="text" class="form-control"
                               value="Karnataka" readonly
                               style="background:#f5f5f5;">
                    </div>
                </div>

                <!-- Policy Notes -->
                <div class="policy-note mt-4">
                    ⚠️ <strong>Please Note:</strong><br>
                    • Delivery only within <strong>Karnataka</strong><br>
                    • Delivery within <strong>7 working days</strong><br>
                    • <strong>No return policy</strong> on seeds<br>
                    • <strong>Online payment only</strong>
                </div>

                <!-- Payment Method -->
                <div class="mt-4">
                    <h5 class="section-heading">💳 Payment Method</h5>
                    <div class="form-check p-3"
                         style="background:#e8f5e9; border-radius:10px;
                                border:2px solid #2e7d32;">
                        <input class="form-check-input" type="radio"
                               name="payment" id="online" checked>
                        <label class="form-check-label" for="online">
                            <strong>💳 Online Payment</strong><br>
                            <small style="color:#777;">
                                UPI, Credit/Debit Card, Net Banking
                            </small>
                        </label>
                    </div>
                </div>

                <button type="button" onclick="placeOrder()"
                        class="btn-place-order mt-4">
                    Proceed to Payment →
                </button>
            </div>
        </div>

        <!-- ORDER SUMMARY -->
        <div class="col-lg-5">
            <div class="summary-box">
                <h5 style="color:#1b5e20; font-weight:700;
                           border-bottom:2px solid #e8f5e9;
                           padding-bottom:10px;">
                    🧾 Order Summary
                </h5>

                <?php foreach ($cart_items as $item):
                    $icon = '🌱';
                    if ($item['category_type'] == 'flowers')     $icon = '🌸';
                    elseif ($item['category_type'] == 'vegetables') $icon = '🥦';
                    elseif ($item['category_type'] == 'fruits')  $icon = '🍎';
                ?>
                <div class="order-item">
                    <span>
                        <?= $icon ?> <?= htmlspecialchars($item['name']) ?>
                        <small style="color:#999;">x<?= $item['quantity'] ?></small>
                    </span>
                    <span>
                        ₹<?= number_format($item['price'] * $item['quantity'], 2) ?>
                    </span>
                </div>
                <?php endforeach; ?>

                <div class="mt-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Total Items:</span>
                        <strong><?= $total_qty ?> packets</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Subtotal:</span>
                        <strong>₹<?= number_format($total_amount, 2) ?></strong>
                    </div>
                    <?php if ($discount > 0): ?>
                    <div class="d-flex justify-content-between mb-1 text-success">
                        <span>Discount (10%):</span>
                        <strong>− ₹<?= number_format($discount, 2) ?></strong>
                    </div>
                    <?php endif; ?>
                    <hr>
                    <div class="d-flex justify-content-between">
                        <span style="font-size:18px; font-weight:700;">Total:</span>
                        <strong style="font-size:22px; color:#e65100;">
                            ₹<?= number_format($final_amount, 2) ?>
                        </strong>
                    </div>
                </div>

                <div class="mt-3 p-2"
                     style="background:#e8f5e9; border-radius:8px;
                            font-size:13px; color:#2e7d32;">
                    🚚 Free delivery across Karnataka<br>
                    📦 Estimated delivery: 7 working days
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function placeOrder() {
    const full_name  = document.getElementById('full_name').value.trim();
    const email      = document.getElementById('email').value.trim();
    const mobile     = document.getElementById('mobile').value.trim();
    const address    = document.getElementById('address').value.trim();
    const city       = document.getElementById('city').value.trim();
    const pincode    = document.getElementById('pincode').value.trim();
    const alt_mobile = document.getElementById('alt_mobile').value.trim();

    if (!full_name || !email || !mobile || !address || !city || !pincode) {
        alert('Please fill all required fields!');
        return;
    }
    if (!/^\d{10}$/.test(mobile)) {
        alert('Enter valid 10-digit mobile number!');
        return;
    }
    if (!/^\d{6}$/.test(pincode)) {
        alert('Enter valid 6-digit pincode!');
        return;
    }

    fetch('../actions/order_action.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'action=save_order_session' +
              '&full_name='  + encodeURIComponent(full_name) +
              '&email='      + encodeURIComponent(email) +
              '&mobile='     + encodeURIComponent(mobile) +
              '&alt_mobile=' + encodeURIComponent(alt_mobile) +
              '&address='    + encodeURIComponent(address) +
              '&city='       + encodeURIComponent(city) +
              '&pincode='    + encodeURIComponent(pincode)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'payment.php';
        } else {
            alert('Error! Please try again.');
        }
    });
}
</script>
</body>
</html>