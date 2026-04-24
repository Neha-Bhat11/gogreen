<?php
session_start();
if (isset($_SESSION['user_id'])) {
    header("Location: pages/home.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .box {
            background: #ffffff; border-radius: 15px; padding: 35px;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
            border-top: 5px solid #2e7d32;
        }
        .brand-title { color: #2e7d32; font-weight: 700; font-size: 28px; }
        .brand-sub { color: #66bb6a; font-size: 14px; }
        .btn-green {
            background-color: #2e7d32; color: white; border: none;
            width: 100%; padding: 10px; border-radius: 8px; font-size: 16px;
        }
        .btn-green:hover { background-color: #1b5e20; color: white; }
        .form-control:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
        a { color: #2e7d32; }
    </style>
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-md-5 col-lg-4">
        <div class="box">
            <div class="text-center mb-4">
                <div class="brand-title">🌱 GreenLife</div>
                <div class="brand-sub">Plant a seed, grow a life</div>
                <h5 class="mt-3" style="color:#333;">Forgot Password</h5>
                <p style="color:#777; font-size:13px;">
                    Enter your registered email — we'll send an OTP to reset your password.
                </p>
            </div>

            <?php if (isset($_SESSION['fp_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['fp_error']; unset($_SESSION['fp_error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['fp_success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['fp_success']; unset($_SESSION['fp_success']); ?></div>
            <?php endif; ?>

            <form action="actions/forgot_action.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Registered Email Address</label>
                    <input type="email" name="email" class="form-control"
                           placeholder="Enter your email" required>
                </div>
                <button type="submit" class="btn btn-green">Send OTP</button>
                <div class="text-center mt-3">
                    <small><a href="index.php">← Back to Login</a></small>
                </div>
            </form>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>