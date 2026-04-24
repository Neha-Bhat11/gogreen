<?php
session_start();
if (!isset($_SESSION['otp_user_id'])) {
    header("Location: index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .otp-box {
            background: #ffffff;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
            border-top: 5px solid #2e7d32;
        }
        .brand-title { color: #2e7d32; font-weight: 700; font-size: 28px; }
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
        .otp-input {
            font-size: 24px;
            text-align: center;
            letter-spacing: 8px;
            font-weight: bold;
        }
        #timer { color: #e53935; font-weight: bold; }
    </style>
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="otp-box text-center">

            <div class="brand-title">🌱 GreenLife</div>
            <h5 class="mt-3 mb-1">OTP Verification</h5>
            <p class="text-muted" style="font-size:13px;">
                We sent a 6-digit OTP to your registered email.<br>
                Valid for <span id="timer">5:00</span> minutes.
            </p>

            <?php if (isset($_SESSION['otp_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['otp_error']; unset($_SESSION['otp_error']); ?></div>
            <?php endif; ?>

            <form action="actions/otp_action.php" method="POST">
                <div class="mb-3">
                    <input type="text" name="otp" class="form-control otp-input"
                           placeholder="------" maxlength="6" required>
                </div>
                <button type="submit" class="btn btn-green">Verify OTP</button>
                <div class="mt-3">
                    <a href="index.php" style="color:#2e7d32; font-size:13px;">← Back to Login</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    // 5 minute countdown timer
    let time = 300;
    const timerEl = document.getElementById('timer');
    const interval = setInterval(() => {
        time--;
        const mins = Math.floor(time / 60);
        const secs = time % 60;
        timerEl.textContent = mins + ':' + (secs < 10 ? '0' : '') + secs;
        if (time <= 0) {
            clearInterval(interval);
            timerEl.textContent = 'Expired!';
        }
    }, 1000);
</script>
</body>
</html>