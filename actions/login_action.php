<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/send_mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $login_id = trim($_POST['login_id']);
    $password = $_POST['password'];

    if (empty($login_id) || empty($password)) {
        $_SESSION['login_error'] = "All fields are required.";
        header("Location: ../index.php");
        exit();
    }

    // Find user by email or mobile
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR mobile = ?");
    $stmt->execute([$login_id, $login_id]);
    $user = $stmt->fetch();

    if (!$user) {
        $_SESSION['login_error'] = "No account found with this email or mobile.";
        header("Location: ../index.php");
        exit();
    }

    // Check password
    if (!password_verify($password, $user['password'])) {
        $_SESSION['login_error'] = "Incorrect password. Please try again.";
        header("Location: ../index.php");
        exit();
    }

    // Generate OTP
    $otp = rand(100000, 999999);
    $expires_at = date('Y-m-d H:i:s', strtotime('+5 minutes'));

    // Delete old OTPs for this user
    $stmt = $pdo->prepare("DELETE FROM otp_codes WHERE user_id = ?");
    $stmt->execute([$user['id']]);

    // Save new OTP
    $stmt = $pdo->prepare("INSERT INTO otp_codes (user_id, email, mobile, otp, type, expires_at) 
                            VALUES (?, ?, ?, ?, 'login', ?)");
    $stmt->execute([$user['id'], $user['email'], $user['mobile'], $otp, $expires_at]);

    // Send OTP email
    sendOTPMail($user['email'], $user['name'], $otp, 'login');

    // Store user_id temporarily for OTP verification
    $_SESSION['otp_user_id'] = $user['id'];
    $_SESSION['login_success'] = "OTP sent to your registered email. Valid for 5 minutes.";

    header("Location: ../otp_verify.php");
    exit();
}
?>