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
    <title>Register - GreenLife</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            background-color: #f0f7f0;
            font-family: 'Segoe UI', sans-serif;
        }
        .register-box {
            background: #ffffff;
            border-radius: 15px;
            padding: 35px;
            box-shadow: 0 4px 20px rgba(0,128,0,0.15);
            border-top: 5px solid #2e7d32;
        }
        .brand-title {
            color: #2e7d32;
            font-weight: 700;
            font-size: 28px;
        }
        .brand-sub {
            color: #66bb6a;
            font-size: 14px;
        }
        .btn-green {
            background-color: #2e7d32;
            color: white;
            border: none;
            width: 100%;
            padding: 10px;
            border-radius: 8px;
            font-size: 16px;
        }
        .btn-green:hover {
            background-color: #1b5e20;
            color: white;
        }
        .form-control:focus {
            border-color: #66bb6a;
            box-shadow: 0 0 0 0.2rem rgba(102,187,106,0.25);
        }
        .error { color: red; font-size: 12px; }
        .success { color: green; font-size: 13px; }
        a { color: #2e7d32; }
    </style>
</head>
<body>
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
    <div class="col-md-6 col-lg-5">
        <div class="register-box">

            <!-- Logo / Title -->
            <div class="text-center mb-4">
                <div class="brand-title">🌱 GreenLife</div>
                <div class="brand-sub">Plant a seed, grow a life</div>
                <h5 class="mt-3" style="color:#333;">Create Your Account</h5>
            </div>

            <!-- Error / Success Messages -->
            <?php if (isset($_SESSION['reg_error'])): ?>
                <div class="alert alert-danger"><?= $_SESSION['reg_error']; unset($_SESSION['reg_error']); ?></div>
            <?php endif; ?>
            <?php if (isset($_SESSION['reg_success'])): ?>
                <div class="alert alert-success"><?= $_SESSION['reg_success']; unset($_SESSION['reg_success']); ?></div>
            <?php endif; ?>

            <!-- Register Form -->
            <form action="actions/register_action.php" method="POST" id="registerForm">

                <!-- Name -->
                <div class="mb-3">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="name" id="name" class="form-control" 
                           placeholder="Enter your full name" required>
                    <span class="error" id="nameError"></span>
                </div>

                <!-- Email -->
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" id="email" class="form-control" 
                           placeholder="Enter your email" required>
                    <span class="error" id="emailError"></span>
                </div>

                <!-- Mobile -->
                <div class="mb-3">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" name="mobile" id="mobile" class="form-control" 
                           placeholder="Enter 10-digit mobile number" maxlength="10" required>
                    <span class="error" id="mobileError"></span>
                </div>

                <!-- Place -->
                <div class="mb-3">
                    <label class="form-label">Place</label>
                    <input type="text" name="place" id="place" class="form-control" 
                           placeholder="Enter your city/town" required>
                </div>

                <!-- Password -->
                <div class="mb-3">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="password" class="form-control" 
                           placeholder="Min 8, max 12 characters" required>
                    <span class="error" id="passwordError"></span>
                    <small class="text-muted">Must have 1 capital, 2 special characters, 1 number (8-12 chars)</small>
                </div>

                <!-- Confirm Password -->
                <div class="mb-3">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" 
                           class="form-control" placeholder="Re-enter your password" required>
                    <span class="error" id="confirmError"></span>
                </div>

                <button type="submit" class="btn btn-green mt-2">Register</button>

                <div class="text-center mt-3">
                    <small>Already have an account? <a href="index.php">Login here</a></small>
                </div>

            </form>
        </div>
    </div>
</div>

<script src="assets/bootstrap/js/bootstrap.min.js"></script>
<script>
document.getElementById('registerForm').addEventListener('submit', function(e) {
    let valid = true;

    // Name validation
    const name = document.getElementById('name').value.trim();
    if (name.length < 2) {
        document.getElementById('nameError').textContent = 'Please enter a valid name.';
        valid = false;
    } else {
        document.getElementById('nameError').textContent = '';
    }

    // Mobile validation
    const mobile = document.getElementById('mobile').value.trim();
    if (!/^\d{10}$/.test(mobile)) {
        document.getElementById('mobileError').textContent = 'Enter a valid 10-digit mobile number.';
        valid = false;
    } else {
        document.getElementById('mobileError').textContent = '';
    }

    // Password validation
    const password = document.getElementById('password').value;
    const passRegex = /^(?=.*[A-Z])(?=(?:.*[^a-zA-Z0-9]){2,})(?=.*\d).{8,12}$/;
    if (!passRegex.test(password)) {
        document.getElementById('passwordError').textContent = 
            'Password must be 8-12 chars with 1 capital, 2 special characters and 1 number.';
        valid = false;
    } else {
        document.getElementById('passwordError').textContent = '';
    }

    // Confirm password
    const confirm = document.getElementById('confirm_password').value;
    if (password !== confirm) {
        document.getElementById('confirmError').textContent = 'Passwords do not match.';
        valid = false;
    } else {
        document.getElementById('confirmError').textContent = '';
    }

    if (!valid) e.preventDefault();
});
</script>
</body>
</html>