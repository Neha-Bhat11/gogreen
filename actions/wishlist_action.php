<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? $_GET['action'] ?? '';

// ADD TO WISHLIST
if ($action == 'add') {
    $product_id = (int)$_POST['product_id'];

    // Check if already in wishlist
    $stmt = $pdo->prepare("SELECT id FROM wishlist WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->fetch()) {
        echo json_encode(['success' => false, 'message' => 'Already in wishlist!']);
        exit();
    }

    $stmt = $pdo->prepare("INSERT INTO wishlist (user_id, product_id) VALUES (?, ?)");
    $stmt->execute([$user_id, $product_id]);
    echo json_encode(['success' => true]);
    exit();
}

// REMOVE FROM WISHLIST
if ($action == 'remove') {
    $wishlist_id = (int)$_POST['wishlist_id'];
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    $stmt->execute([$wishlist_id, $user_id]);
    echo json_encode(['success' => true]);
    exit();
}

// MOVE TO CART
if ($action == 'move_to_cart') {
    $wishlist_id = (int)$_POST['wishlist_id'];
    $product_id  = (int)$_POST['product_id'];

    // Add to cart
    $stmt = $pdo->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);
    if ($stmt->fetch()) {
        $stmt = $pdo->prepare("UPDATE cart SET quantity = quantity + 1 
                                WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, 1)");
        $stmt->execute([$user_id, $product_id]);
    }

    // Remove from wishlist
    $stmt = $pdo->prepare("DELETE FROM wishlist WHERE id = ? AND user_id = ?");
    $stmt->execute([$wishlist_id, $user_id]);

    echo json_encode(['success' => true]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>