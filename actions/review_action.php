<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit();
}

$user_id    = $_SESSION['user_id'];
$product_id = (int)$_POST['product_id'];
$order_id   = (int)$_POST['order_id'];
$rating     = (int)$_POST['rating'];
$review_text= trim($_POST['review_text']);

if (!$product_id || !$order_id || !$rating || !$review_text) {
    echo json_encode(['success' => false, 'message' => 'All fields required.']);
    exit();
}

if ($rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid rating.']);
    exit();
}

// Check already reviewed
$stmt = $pdo->prepare("SELECT id FROM reviews 
                        WHERE user_id = ? AND product_id = ? AND order_id = ?");
$stmt->execute([$user_id, $product_id, $order_id]);
if ($stmt->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You already reviewed this product!']);
    exit();
}

// Insert review
$stmt = $pdo->prepare("INSERT INTO reviews 
                        (user_id, product_id, order_id, rating, review_text)
                        VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$user_id, $product_id, $order_id, $rating, $review_text]);

echo json_encode(['success' => true]);
?>