<?php
require_once '../includes/session.php';

// Must come from checkout
if (!isset($_SESSION['pending_order'])) {
    header("Location: cart.php");
    exit();
}

$order = $_SESSION['pending_order'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #1b5e20; padding: 12px 20px; }
        .navbar-brand { color: #fff; font-weight: 700; font-size: 22px; }
        .nav-link { color: #c8e6c9 !important; }
        .nav-link:hover { color: #fff !important; }
        .payment-box {
            background: white; border-radius: 15px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
            border-top: 5px solid #2e7d32;
        }
        .section-heading {
            color: #1b5e20; font-weight: 700;
            border-bottom: 2px solid #e8f5e9;
            padding-bottom: 8px; margin-bottom: 20px;
        }
        .amount-display {
            background: linear-gradient(135deg, #1b5e20, #388e3c);
            color: white; border-radius: 12px;
            padding: 20px; text-align: center; margin-bottom: 25px;
        }
        .amount-display h2 { font-size: 38px; font-weight: 700; margin: 0; }
        .amount-display p { margin: 0; opacity: 0.85; font-size: 14px; }
        .payment-tab {
            border: 2px solid #e0e0e0; border-radius: 10px;
            padding: 15px; cursor: pointer; margin-bottom: 10px;
            transition: all 0.2s;
        }
        .payment-tab:hover { border-color: #2e7d32; background: #f9fffe; }
        .payment-tab.active { border-color: #2e7d32; background: #e8f5e9; }
        .payment-tab input[type="radio"] { margin-right: 10px; }
        .form-control:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
        .card-input {
            font-family: 'Courier New', monospace;
            font-size: 18px; letter-spacing: 2px;
        }
        .btn-pay {
            background: #2e7d32; color: white; border: none;
            border-radius: 10px; padding: 14px;
            font-size: 18px; width: 100%; font-weight: 700;
        }
        .btn-pay:hover { background: #1b5e20; color: white; }
        .secure-badge {
            text-align: center; color: #777;
            font-size: 12px; margin-top: 10px;
        }
        .upi-box {
            background: #f5f5f5; border-radius: 10px;
            padding: 20px; text-align: center;
        }
        /* Loading overlay */
        .loading-overlay {
            display: none;
            position: fixed; top: 0; left: 0;
            width: 100%; height: 100%;
            background: rgba(0,0,0,0.6);
            z-index: 9999;
            align-items: center; justify-content: center;
            flex-direction: column; color: white;
        }
        .spinner {
            width: 60px; height: 60px;
            border: 6px solid rgba(255,255,255,0.3);
            border-top-color: #66bb6a;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-bottom: 20px;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        footer { background-color: #1b5e20; color: #c8e6c9; padding: 30px 0; margin-top: 60px; }
    </style>
</head>
<body>

<!-- Loading Overlay -->
<div class="loading-overlay" id="loadingOverlay">
    <div class="spinner"></div>
    <h4>Processing Payment...</h4>
    <p style="opacity:0.8;">Please do not close this window</p>
</div>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php">🌱 GreenLife</a>
        <ul class="navbar-nav ms-auto">
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </li>
        </ul>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="payment-box">

                <!-- Amount Display -->
                <div class="amount-display">
                    <p>Total Amount to Pay</p>
                    <h2>₹<?= number_format($order['final_amount'], 2) ?></h2>
                    <p>Order for <?= htmlspecialchars($order['full_name']) ?></p>
                </div>

                <h5 class="section-heading">💳 Choose Payment Method</h5>

                <!-- Payment Tabs -->
                <div id="paymentTabs">
                    <div class="payment-tab active" onclick="selectTab('card', this)">
                        <input type="radio" name="pay_method" value="card" checked>
                        💳 <strong>Credit / Debit Card</strong>
                    </div>
                    <div class="payment-tab" onclick="selectTab('upi', this)">
                        <input type="radio" name="pay_method" value="upi">
                        📱 <strong>UPI Payment</strong>
                    </div>
                    <div class="payment-tab" onclick="selectTab('netbanking', this)">
                        <input type="radio" name="pay_method" value="netbanking">
                        🏦 <strong>Net Banking</strong>
                    </div>
                </div>

                <!-- CARD FORM -->
                <div id="cardForm" class="mt-4">
                    <div class="mb-3">
                        <label class="form-label">Card Number</label>
                        <input type="text" class="form-control card-input"
                               id="card_number" placeholder="1234 5678 9012 3456"
                               maxlength="19" oninput="formatCard(this)">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Cardholder Name</label>
                        <input type="text" class="form-control"
                               id="card_name" placeholder="Name on card">
                    </div>
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label">Expiry Date</label>
                            <input type="text" class="form-control card-input"
                                   id="card_expiry" placeholder="MM/YY"
                                   maxlength="5" oninput="formatExpiry(this)">
                        </div>
                        <div class="col-6">
                            <label class="form-label">CVV</label>
                            <input type="password" class="form-control card-input"
                                   id="card_cvv" placeholder="***" maxlength="3">
                        </div>
                    </div>
                </div>

                <!-- UPI FORM -->
                <div id="upiForm" class="mt-4" style="display:none;">
                    <div class="upi-box">
                        <div style="font-size:40px;">📱</div>
                        <p style="color:#555; margin-top:10px;">Enter your UPI ID</p>
                        <input type="text" class="form-control text-center"
                               id="upi_id" placeholder="yourname@upi"
                               style="max-width:280px; margin:0 auto;">
                        <small style="color:#999; display:block; margin-top:8px;">
                            e.g. name@okaxis, name@paytm, name@ybl
                        </small>
                    </div>
                </div>

                <!-- NET BANKING FORM -->
                <div id="netbankingForm" class="mt-4" style="display:none;">
                    <div class="mb-3">
                        <label class="form-label">Select Your Bank</label>
                        <select class="form-select" id="bank_select">
                            <option value="">-- Select Bank --</option>
                            <option>State Bank of India</option>
                            <option>HDFC Bank</option>
                            <option>ICICI Bank</option>
                            <option>Axis Bank</option>
                            <option>Kotak Mahindra Bank</option>
                            <option>Bank of Baroda</option>
                            <option>Canara Bank</option>
                            <option>Punjab National Bank</option>
                            <option>Union Bank of India</option>
                            <option>Other Bank</option>
                        </select>
                    </div>
                    <div class="alert alert-info" style="font-size:13px;">
                        ℹ️ You will be redirected to your bank's secure page to complete payment.
                    </div>
                </div>

                <!-- Pay Button -->
                <button onclick="processPayment()" class="btn-pay mt-4">
                    🔒 Pay ₹<?= number_format($order['final_amount'], 2) ?> Now
                </button>

                <div class="secure-badge">
                    🔒 100% Secure Payment &nbsp;|&nbsp; SSL Encrypted &nbsp;|&nbsp; 🌱 GreenLife
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
function selectTab(type, el) {
    // Remove active from all tabs
    document.querySelectorAll('.payment-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');

    // Hide all forms
    document.getElementById('cardForm').style.display       = 'none';
    document.getElementById('upiForm').style.display        = 'none';
    document.getElementById('netbankingForm').style.display = 'none';

    // Show selected
    if (type === 'card')       document.getElementById('cardForm').style.display       = 'block';
    if (type === 'upi')        document.getElementById('upiForm').style.display        = 'block';
    if (type === 'netbanking') document.getElementById('netbankingForm').style.display = 'block';
}

function formatCard(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 16);
    input.value = val.replace(/(.{4})/g, '$1 ').trim();
}

function formatExpiry(input) {
    let val = input.value.replace(/\D/g, '').substring(0, 4);
    if (val.length >= 2) val = val.substring(0, 2) + '/' + val.substring(2);
    input.value = val;
}

function processPayment() {
    // Get selected method
    const activeTab = document.querySelector('.payment-tab.active input');
    const method = activeTab ? activeTab.value : 'card';

    // Basic validation
    if (method === 'card') {
        const num  = document.getElementById('card_number').value.replace(/\s/g, '');
        const name = document.getElementById('card_name').value.trim();
        const exp  = document.getElementById('card_expiry').value.trim();
        const cvv  = document.getElementById('card_cvv').value.trim();
        if (num.length < 16 || !name || exp.length < 5 || cvv.length < 3) {
            alert('Please fill all card details correctly!');
            return;
        }
    }
    if (method === 'upi') {
        const upi = document.getElementById('upi_id').value.trim();
        if (!upi.includes('@')) {
            alert('Please enter a valid UPI ID!');
            return;
        }
    }
    if (method === 'netbanking') {
        const bank = document.getElementById('bank_select').value;
        if (!bank) {
            alert('Please select your bank!');
            return;
        }
    }

    // Show loading
    const overlay = document.getElementById('loadingOverlay');
    overlay.style.display = 'flex';

    // Simulate payment processing (3 seconds)
    setTimeout(() => {
        // Submit to order_action.php
        fetch('../actions/order_action.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'action=place_order&payment_method=' + method
        })
        .then(res => res.json())
        .then(data => {
            overlay.style.display = 'none';
            if (data.success) {
                window.location.href = 'order_success.php?id=' + data.order_id;
            } else {
                alert(data.message || 'Payment failed. Please try again.');
            }
        })
        .catch(() => {
            overlay.style.display = 'none';
            alert('Something went wrong. Please try again.');
        });
    }, 3000);
}
</script>
</body>
</html>