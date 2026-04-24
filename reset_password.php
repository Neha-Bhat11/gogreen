<?php
session_start();
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot_password.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .box {
            background: #ffffff; border-radius: 15px; padding: 35px;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
            border-top: 5px solid #2e7d32;
        }
        .brand-title { color: #2e7d32; font-weight: 700; font-size: 28px; }
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
        .otp-input { font-size: 22px; text-align: center; letter-spacing: 6px; font-weight: bold; }
        #timer { color: #e53935; font-weight: bold; }
    </style>
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-md-5 col-lg-4">
        <div class="box">
            <div class="text-center mb-4">
                <div class="brand-title">🌱 GreenLife</div>
                <h5 class="mt-3">Reset Password</h5>
                <p style="color:#777; font-size:13px;">
                    OTP sent to your email. Valid for
                    <span id="timer">5:00</span> minutes.
                </p>
            </div>

            <?php if (isset($_SESSION['rp_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['rp_error']; unset($_SESSION['rp_error']); ?></div>
            <?php endif; ?>

            <form action="actions/reset_action.php" method="POST">

                <!-- OTP -->
                <div class="mb-3">
                    <label class="form-label">Enter OTP</label>
                    <input type="text" name="otp" class="form-control otp-input"
                           placeholder="------" maxlength="6" required>
                </div>

                <!-- New Password -->
                <div class="mb-3">
                    <label class="form-label">New Password</label>
                    <input type="password" name="new_password" id="new_password"
                           class="form-control" placeholder="Min 8, max 12 characters" required>
                    <small class="text-muted">1 capital, 2 special characters, 1 number (8-12 chars)</small>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label class="form-label">Confirm New Password</label>
                    <input type="password" name="confirm_password" id="confirm_password"
                           class="form-control" placeholder="Re-enter new password" required>
                    <span id="passError" style="color:red; font-size:12px;"></span>
                </div>

                <button type="submit" class="btn btn-green">Reset Password</button>
                <div class="text-center mt-3">
                    <small><a href="forgot_password.php">← Resend OTP</a></small>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    let time = 300;
    const timerEl = document.getElementById('timer');
    const interval = setInterval(() => {
        time--;
        const mins = Math.floor(time / 60);
        const secs = time % 60;
        timerEl.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        if (time <= 0) { clearInterval(interval); timerEl.textContent = 'Expired!'; }
    }, 1000);

    document.querySelector('form').addEventListener('submit', function(e) {
        const p1 = document.getElementById('new_password').value;
        const p2 = document.getElementById('confirm_password').value;
        if (p1 !== p2) {
            e.preventDefault();
            document.getElementById('passError').textContent = 'Passwords do not match!';
        }
    });
</script>
</body>
</html>