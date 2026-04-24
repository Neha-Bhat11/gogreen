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
    <title>Login - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .login-box {
            background: #ffffff;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
            border-top: 5px solid #2e7d32;
        }
        .brand-title { color: #2e7d32; font-weight: 700; font-size: 28px; }
        .brand-sub { color: #66bb6a; font-size: 14px; }
        .btn-green {
            background-color: #2e7d32; color: white;
            border: none; width: 100%; padding: 10px;
            border-radius: 8px; font-size: 16px;
        }
        .btn-green:hover { background-color: #1b5e20; color: white; }
        .form-control:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
        .captcha-box {
            background: #e8f5e9;
            border: 2px dashed #2e7d32;
            border-radius: 8px;
            padding: 12px 15px;
            font-size: 26px;
            font-weight: bold;
            letter-spacing: 8px;
            color: #1b5e20;
            text-align: center;
            font-family: 'Courier New', monospace;
            user-select: none;
            text-shadow: 1px 1px 2px rgba(0,0,0,0.2);
            min-height: 55px;
            line-height: 1.5;
        }
        a { color: #2e7d32; }
    </style>
</head>
<body>

<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="login-box">

            <!-- Logo -->
            <div class="text-center mb-4">
                <div class="brand-title">🌱 GreenLife</div>
                <div class="brand-sub">Plant a seed, grow a life</div>
                <h5 class="mt-3" style="color:#333;">Welcome Back!</h5>
            </div>

            <!-- Messages -->
            <?php if (isset($_SESSION['login_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['login_error']; unset($_SESSION['login_error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['reg_success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['reg_success']; unset($_SESSION['reg_success']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['login_success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['login_success']; unset($_SESSION['login_success']); ?></div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="actions/login_action.php" method="POST" id="loginForm">

                <!-- Email or Mobile -->
                <div class="mb-3">
                    <label class="form-label">Email or Mobile Number</label>
                    <input type="text" name="login_id" id="login_id" class="form-control"
                           placeholder="Enter email or mobile number" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control"
                           placeholder="Enter your password" required>
                </div>

                <!-- CAPTCHA -->
                <div class="mb-3">
                    <label class="form-label">Enter the CAPTCHA below</label>
                    <div class="captcha-box mb-2" id="captchaDisplay">Loading...</div>
                    <div class="d-flex gap-2">
                        <input type="text" name="captcha_input" id="captcha_input"
                               class="form-control" placeholder="Type captcha here" required>
                        <button type="button" class="btn btn-outline-success"
                                onclick="generateCaptcha()">🔄</button>
                    </div>
                    <input type="hidden" name="captcha_code" id="captcha_code">
                </div>

                <button type="submit" class="btn btn-green mt-2">Login</button>

                <div class="text-center mt-3">
                    <small>Don't have an account? <a href="register.php">Register here</a></small><br>
                    <small><a href="forgot_password.php">Forgot Password?</a></small>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    function generateCaptcha() {
        var chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz23456789';
        var captcha = '';
        for (var i = 0; i < 6; i++) {
            captcha += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        document.getElementById('captchaDisplay').textContent = captcha;
        document.getElementById('captcha_code').value = captcha;
        document.getElementById('captcha_input').value = '';
    }

    generateCaptcha();

    document.getElementById('loginForm').addEventListener('submit', function(e) {
        var entered = document.getElementById('captcha_input').value.trim();
        var actual  = document.getElementById('captcha_code').value.trim();
        if (entered !== actual) {
            e.preventDefault();
            alert('Incorrect CAPTCHA! Please try again.');
            generateCaptcha();
        }
    });
</script>
</body>
</html>