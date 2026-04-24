<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp              = trim($_POST['otp']);
    $new_password     = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    $user_id          = $_SESSION['reset_user_id'];

    if (empty($user_id)) {
        header("Location: ../forgot_password.php");
        exit();
    }

    // Password match check
    if ($new_password !== $confirm_password) {
        $_SESSION['rp_error'] = "Passwords do not match!";
        header("Location: ../reset_password.php");
        exit();
    }

    // Password strength check
    $passRegex = '/^(?=.*[A-Z])(?=(?:.*[^a-zA-Z0-9]){2,})(?=.*\d).{8,12}$/';
    if (!preg_match($passRegex, $new_password)) {
        $_SESSION['rp_error'] = "Password must be 8-12 chars with 1 capital, 2 special chars and 1 number.";
        header("Location: ../reset_password.php");
        exit();
    }

    // Verify OTP
    $stmt = $pdo->prepare("SELECT * FROM otp_codes 
                            WHERE user_id = ? 
                            AND CAST(otp AS CHAR) = CAST(? AS CHAR)
                            AND is_used = 0
                            ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id, $otp]);
    $record = $stmt->fetch();

    if (!$record) {
        $_SESSION['rp_error'] = "Invalid OTP. Please try again.";
        header("Location: ../reset_password.php");
        exit();
    }

    // Mark OTP used
    $stmt = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
    $stmt->execute([$record['id']]);

    // Update password
    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
    $stmt   = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->execute([$hashed, $user_id]);

    // Clear session
    unset($_SESSION['reset_user_id']);

    $_SESSION['login_success'] = "✅ Password reset successful! Please login with your new password.";
    header("Location: ../index.php");
    exit();
}
?>