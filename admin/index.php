<?php
session_start();
if (isset($_SESSION['admin_id'])) {
    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body { background-color: #1b5e20; font-family: 'Segoe UI', sans-serif; }
        .login-box {
            background: white; border-radius: 15px;
            padding: 40px; box-shadow: 0 10px 40px rgba(0,0,0,0.3);
        }
        .brand-title { color: #1b5e20; font-weight: 700; font-size: 28px; }
        .btn-admin {
            background: #1b5e20; color: white; border: none;
            width: 100%; padding: 12px; border-radius: 8px; font-size: 16px;
        }
        .btn-admin:hover { background: #2e7d32; color: white; }
        .form-control:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
    </style>
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
    <div class="col-md-5 col-lg-4">
        <div class="login-box">
            <div class="text-center mb-4">
                <div class="brand-title">🌱 GreenLife</div>
                <h5 class="mt-2" style="color:#555;">Admin Panel</h5>
            </div>

            <?php if (isset($_SESSION['admin_error'])): ?>
                <div class="alert alert-danger">
                    <?= $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
                </div>
            <?php endif; ?>

            <form action="admin_login_action.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control"
                           placeholder="Enter admin username" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control"
                           placeholder="Enter password" required>
                </div>
                <button type="submit" class="btn btn-admin">Login to Admin</button>
            </form>

            <div class="text-center mt-3">
                <a href="../index.php" style="color:#2e7d32; font-size:13px;">
                    ← Back to Website
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>