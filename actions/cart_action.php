<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action  = $_GET['action'] ?? $_POST['action'] ?? '';

// GET CART COUNT
if ($action == 'count') {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $row = $stmt->fetch();
    echo json_encode(['count' => (int)($row['total'] ?? 0)]);
    exit();
}

// ADD TO CART
if ($action == 'add') {
    $product_id = (int)$_POST['product_id'];

    // Check if already in cart
    $stmt = $pdo->prepare("SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    $existing = $stmt->fetch();

    if ($existing) {
        // Increase quantity
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 WHERE id = ?");
        $stmt->execute([$existing['id']]);
    } else {
        // Add new
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }
    echo json_encode(['success' => true]);
    exit();
}

// UPDATE QUANTITY
if ($action == 'update') {
    $cart_id  = (int)$_POST['cart_id'];
    $quantity = (int)$_POST['quantity'];
    if ($quantity < 1) $quantity = 1;
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
    $stmt->execute([$quantity, $cart_id, $user_id]);
    echo json_encode(['success' => true]);
    exit();
}

// REMOVE FROM CART
if ($action == 'remove') {
    $cart_id = (int)$_POST['cart_id'];
    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
    $stmt->execute([$cart_id, $user_id]);
    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>