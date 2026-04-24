<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/send_mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);

    // Check if email exists
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['fp_error'] = "No account found with this email.";
        header("Location: ../forgot_password.php");
        exit();
    }

    // Generate OTP
    $otp        = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Delete old OTPs
    $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Save OTP
    $stmt = $pdo->prepare("INSERT INTO otp_codes 
                            (user_id, email, mobile, otp, type, expires_at)
                            VALUES (?, ?, ?, ?, 'login', ?)");
    $stmt->execute([$user['id'], $user['email'], $user['mobile'], $otp, $expires_at]);

    // Send OTP email
    sendOTPMail($user['email'], $user['name'], $otp, 'login');

    // Store user id in session
    $_SESSION['reset_user_id'] = $user['id'];
    $_SESSION['fp_success']    = "OTP sent to your email!";

    header("Location: ../reset_password.php");
    exit();
}
?>