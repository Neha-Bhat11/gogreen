<?php
require_once '../includes/session.php';
require_once '../includes/db.php';

$user_id = $_SESSION['user_id'];

// Update profile
if (isset($_POST['update_profile'])) {
    $name   = trim($_POST['name']);
    $mobile = trim($_POST['mobile']);
    $place  = trim($_POST['place']);

    // Check mobile not taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE mobile = ? AND id != ?");
    $stmt->execute([$mobile, $user_id]);
    if ($stmt->fetch()) {
        $error = "Mobile number already used by another account!";
    } else {
        $stmt = $pdo->prepare("UPDATE users SET name=?, mobile=?, place=? WHERE id=?");
        $stmt->execute([$name, $mobile, $place, $user_id]);
        $_SESSION['user_name'] = $name;
        $success = "Profile updated successfully!";
    }
}

// Change password
if (isset($_POST['change_password'])) {
    $current  = $_POST['current_password'];
    $new      = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    // Get current password
    $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    if (!password_verify($current, $user['password'])) {
        $pass_error = "Current password is incorrect!";
    } elseif ($new !== $confirm) {
        $pass_error = "New passwords do not match!";
    } else {
        $passRegex = '/^(?=.*[A-Z])(?=(?:.*[^a-zA-Z0-9]){2,})(?=.*\d).{8,12}$/';
        if (!preg_match($passRegex, $new)) {
            $pass_error = "Password must be 8-12 chars with 1 capital, 2 special chars and 1 number.";
        } else {
            $hashed = password_hash($new, PASSWORD_BCRYPT);
            $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hashed, $user_id]);
            $pass_success = "Password changed successfully!";
        }
    }
}

// Get user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Get user stats
$total_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
$total_orders->execute([$user_id]);
$total_orders = $total_orders->fetchColumn();

$total_spent = $pdo->prepare("SELECT SUM(final_amount) FROM orders 
                               WHERE user_id = ? AND payment_status = 'paid'");
$total_spent->execute([$user_id]);
$total_spent = $total_spent->fetchColumn() ?? 0;

$total_reviews = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE user_id = ?");
$total_reviews->execute([$user_id]);
$total_reviews = $total_reviews->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <style>
        body { background-color: #f0f7f0; font-family: 'Segoe UI', sans-serif; }
        .navbar { background-color: #1b5e20; padding: 12px 20px; }
        .navbar-brand { color: #fff; font-weight: 700; font-size: 22px; }
        .nav-link { color: #c8e6c9 !important; }
        .nav-link:hover, .nav-link.active { color: #fff !important; font-weight: 600; }
        .section-title {
            color: #1b5e20; font-weight: 700; font-size: 26px;
            border-left: 5px solid #66bb6a; padding-left: 12px;
        }
        .profile-card {
            background: white; border-radius: 15px;
            padding: 30px; box-shadow: 0 3px 15px rgba(0,0,0,0.08);
        }
        .avatar {
            width: 90px; height: 90px; border-radius: 50%;
            background: linear-gradient(135deg, #1b5e20, #66bb6a);
            display: flex; align-items: center; justify-content: center;
            font-size: 36px; color: white; font-weight: 700;
            margin: 0 auto 15px;
        }
        .stat-box {
            background: #e8f5e9; border-radius: 12px;
            padding: 20px; text-align: center;
        }
        .stat-box h3 { color: #1b5e20; font-weight: 700; margin: 0; }
        .stat-box p { color: #777; font-size: 13px; margin: 0; }
        .form-control:focus, .form-select:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
        .btn-green {
            background: #2e7d32; color: white; border: none;
            border-radius: 8px; padding: 10px 25px; font-weight: 600;
        }
        .btn-green:hover { background: #1b5e20; color: white; }
        .section-heading {
            color: #1b5e20; font-weight: 700;
            border-bottom: 2px solid #e8f5e9;
            padding-bottom: 8px; margin-bottom: 20px;
        }
        footer { background-color: #1b5e20; color: #c8e6c9; padding: 30px 0; margin-top: 60px; }
    </style>
</head>
<body>

<!-- NAVBAR -->
<nav class="navbar navbar-expand-lg">
    <div class="container">
        <a class="navbar-brand" href="home.php">🌱 GreenLife</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
                data-bs-target="#navMenu">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navMenu">
            <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
                <li class="nav-item">
                    <a class="nav-link" href="home.php">
                        <i class="bi bi-house"></i> Home</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="about.php">
                        <i class="bi bi-info-circle"></i> About</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="products.php">
                        <i class="bi bi-grid"></i> Products</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="cart.php">
                        <i class="bi bi-cart3"></i> Cart</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="wishlist.php">
                        <i class="bi bi-heart"></i> Wishlist</a></li>
                <li class="nav-item">
                    <a class="nav-link" href="order_details.php">
                        <i class="bi bi-bag"></i> My Orders</a></li>
                <li class="nav-item">
                    <a class="nav-link active" href="profile.php">
                        <i class="bi bi-person"></i> Profile</a></li>
                <li class="nav-item ms-lg-2">
                    <a class="nav-link" href="../logout.php"
                       style="background:#2e7d32; border-radius:20px; padding:6px 16px;">
                        <i class="bi bi-box-arrow-right"></i> Logout
                    </a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-4 mb-5">
    <h2 class="section-title mb-4">👤 My Profile</h2>

    <div class="row g-4">

        <!-- LEFT - Profile Info -->
        <div class="col-lg-4">
            <div class="profile-card text-center">
                <!-- Avatar with first letter of name -->
                <div class="avatar">
                    <?= strtoupper(substr($user['name'], 0, 1)) ?>
                </div>
                <h4 style="color:#1b5e20; font-weight:700;">
                    <?= htmlspecialchars($user['name']) ?>
                </h4>
                <p style="color:#777; font-size:14px;">
                    <?= htmlspecialchars($user['email']) ?>
                </p>
                <p style="color:#777; font-size:13px;">
                    📍 <?= htmlspecialchars($user['place']) ?>
                </p>
                <p style="color:#999; font-size:12px;">
                    Member since <?= date('M Y', strtotime($user['created_at'])) ?>
                </p>

                <hr>

                <!-- Stats -->
                <div class="row g-2 mt-2">
                    <div class="col-4">
                        <div class="stat-box">
                            <h3><?= $total_orders ?></h3>
                            <p>Orders</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <h3>₹<?= number_format($total_spent, 0) ?></h3>
                            <p>Spent</p>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="stat-box">
                            <h3><?= $total_reviews ?></h3>
                            <p>Reviews</p>
                        </div>
                    </div>
                </div>

                <div class="mt-3 d-grid gap-2">
                    <a href="order_details.php" class="btn btn-outline-success btn-sm">
                        <i class="bi bi-bag"></i> View My Orders
                    </a>
                    <a href="wishlist.php" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-heart"></i> My Wishlist
                    </a>
                </div>
            </div>
        </div>

        <!-- RIGHT - Edit Forms -->
        <div class="col-lg-8">

            <!-- Edit Profile -->
            <div class="profile-card mb-4">
                <h5 class="section-heading">✏️ Edit Profile</h5>

                <?php if (isset($success)): ?>
                    <div class="alert alert-success"><?= $success ?></div>
                <?php endif; ?>
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger"><?= $error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Full Name</label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= htmlspecialchars($user['name']) ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Address</label>
                            <input type="email" class="form-control"
                                   value="<?= htmlspecialchars($user['email']) ?>"
                                   readonly style="background:#f5f5f5;">
                            <small class="text-muted">Email cannot be changed</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Mobile Number</label>
                            <input type="text" name="mobile" class="form-control"
                                   value="<?= htmlspecialchars($user['mobile']) ?>"
                                   maxlength="10" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Place / City</label>
                            <input type="text" name="place" class="form-control"
                                   value="<?= htmlspecialchars($user['place']) ?>" required>
                        </div>
                    </div>
                    <button type="submit" name="update_profile" class="btn btn-green mt-3">
                        <i class="bi bi-check-circle"></i> Update Profile
                    </button>
                </form>
            </div>

            <!-- Change Password -->
            <div class="profile-card">
                <h5 class="section-heading">🔒 Change Password</h5>

                <?php if (isset($pass_success)): ?>
                    <div class="alert alert-success"><?= $pass_success ?></div>
                <?php endif; ?>
                <?php if (isset($pass_error)): ?>
                    <div class="alert alert-danger"><?= $pass_error ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Current Password</label>
                            <input type="password" name="current_password"
                                   class="form-control"
                                   placeholder="Enter current password" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">New Password</label>
                            <input type="password" name="new_password"
                                   class="form-control"
                                   placeholder="Min 8, max 12 chars" required>
                            <small class="text-muted">
                                1 capital, 2 special chars, 1 number (8-12 chars)
                            </small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Confirm New Password</label>
                            <input type="password" name="confirm_password"
                                   class="form-control"
                                   placeholder="Re-enter new password" required>
                        </div>
                    </div>
                    <button type="submit" name="change_password"
                            class="btn btn-green mt-3">
                        <i class="bi bi-lock"></i> Change Password
                    </button>
                </form>
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
</body>
</html>