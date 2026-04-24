<?php
session_start();
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $otp_entered = trim($_POST['otp']);
    $user_id = $_SESSION['otp_user_id'];

    if (empty($user_id)) {
        header("Location: ../index.php");
        exit();
    }

    // Get latest unused OTP for this user - removed time check
    $stmt = $pdo->prepare("SELECT * FROM otp_codes 
                            WHERE user_id = ? 
                            AND CAST(otp AS CHAR) = CAST(? AS CHAR)
                            AND is_used = 0
                            ORDER BY id DESC LIMIT 1");
    $stmt->execute([$user_id, $otp_entered]);
    $record = $stmt->fetch();

    if (!$record) {
        $_SESSION['otp_error'] = "Invalid OTP. Please try again.";
        header("Location: ../otp_verify.php");
        exit();
    }

    // Check expiry using PHP time instead of MySQL NOW()
    $expires_at = strtotime($record['expires_at']);
    $current_time = time();

    if ($current_time > $expires_at + 330) { // +330 = 5.5hrs for IST offset
        $_SESSION['otp_error'] = "OTP has expired. Please login again.";
        header("Location: ../index.php");
        exit();
    }

    // Mark OTP as used
    $stmt = $pdo->prepare("UPDATE otp_codes SET is_used = 1 WHERE id = ?");
    $stmt->execute([$record['id']]);

    // Get user details
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();

    // Set session
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    unset($_SESSION['otp_user_id']);

    header("Location: ../pages/home.php");
    exit();
}
?>