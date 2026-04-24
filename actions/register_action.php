<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/send_mail.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name             = trim($_POST['name']);
    $email            = trim($_POST['email']);
    $mobile           = trim($_POST['mobile']);
    $place            = trim($_POST['place']);
    $password         = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    if (empty($name) || empty($email) || empty($mobile) || empty($place) || empty($password)) {
        $_SESSION['reg_error'] = "All fields are required.";
        header("Location: ../register.php");
        exit();
    }

    // Password match
    if ($password !== $confirm_password) {
        $_SESSION['reg_error'] = "Passwords do not match.";
        header("Location: ../register.php");
        exit();
    }

    // Password strength
    $passRegex = '/^(?=.*[A-Z])(?=(?:.*[^a-zA-Z0-9]){2,})(?=.*\d).{8,12}$/';
    if (!preg_match($passRegex, $password)) {
        $_SESSION['reg_error'] = "Password must be 8-12 chars with 1 capital, 2 special chars and 1 number.";
        header("Location: ../register.php");
        exit();
    }

    // Check if email or mobile already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR mobile = ?");
    $stmt->execute([$email, $mobile]);
    if ($stmt->rowCount() > 0) {
        $_SESSION['reg_error'] = "Email or Mobile number already registered.";
        header("Location: ../register.php");
        exit();
    }

    // Hash password
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    // Insert user
    $stmt = $pdo->prepare("INSERT INTO users (name, email, mobile, place, password, is_verified) 
                            VALUES (?, ?, ?, ?, ?, 1)");
    $stmt->execute([$name, $email, $mobile, $place, $hashed_password]);

    // Send welcome email
    sendOTPMail($email, $name, null, 'register');

    $_SESSION['reg_success'] = "Registration successful! Welcome to GreenLife 🌱 Please login.";
    header("Location: ../register.php");
    exit();
}
?>