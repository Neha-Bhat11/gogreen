<?php
session_start();
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Please login first.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$action  = $_POST['action'] ?? '';

// SAVE ORDER DETAILS TO SESSION
if ($action == 'save_order_session') {
    $_SESSION['pending_order'] = [
        'full_name'   => trim($_POST['full_name']),
        'email'       => trim($_POST['email']),
        'mobile'      => trim($_POST['mobile']),
        'alt_mobile'  => trim($_POST['alt_mobile']),
        'address'     => trim($_POST['address']),
        'city'        => trim($_POST['city']),
        'pincode'     => trim($_POST['pincode']),
    ];

    // Calculate totals
    $stmt = $pdo->prepare("SELECT c.quantity, p.price
                            FROM cart c
                            JOIN products p ON c.product_id = p.id
                            WHERE c.user_id = ?");
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();

    $total_qty    = 0;
    $total_amount = 0;
    foreach ($items as $item) {
        $total_qty    += $item['quantity'];
        $total_amount += $item['price'] * $item['quantity'];
    }
    $discount     = ($total_qty >= 10) ? $total_amount * 0.10 : 0;
    $final_amount = $total_amount - $discount;

    $_SESSION['pending_order']['total_amount'] = $total_amount;
    $_SESSION['pending_order']['discount']     = $discount;
    $_SESSION['pending_order']['final_amount'] = $final_amount;

    echo json_encode(['success' => true]);
    exit();
}

// PLACE ORDER AFTER PAYMENT
if ($action == 'place_order') {
    if (!isset($_SESSION['pending_order'])) {
        echo json_encode(['success' => false, 'message' => 'Order session expired.']);
        exit();
    }

    $order          = $_SESSION['pending_order'];
    $payment_method = $_POST['payment_method'] ?? 'card';
    $payment_id     = 'DUMMY_' . strtoupper(uniqid());

    try {
        // Insert order
        $stmt = $pdo->prepare("INSERT INTO orders 
                                (user_id, total_amount, discount, final_amount,
                                 full_name, address, city, pincode,
                                 mobile, alt_mobile, email,
                                 payment_id, payment_status, order_status)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'placed')");
        $stmt->execute([
            $user_id,
            $order['total_amount'],
            $order['discount'],
            $order['final_amount'],
            $order['full_name'],
            $order['address'],
            $order['city'],
            $order['pincode'],
            $order['mobile'],
            $order['alt_mobile'],
            $order['email'],
            $payment_id
        ]);

        $order_id = $pdo->lastInsertId();

        // Insert order items
        $stmt = $pdo->prepare("SELECT c.quantity, p.id as product_id, p.price
                                FROM cart c
                                JOIN products p ON c.product_id = p.id
                                WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items 
                                    (order_id, product_id, quantity, price)
                                    VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);

            // Reduce stock
            $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        // Notify admin by email
require_once '../includes/send_mail.php';
notifyAdminNewOrder($order_id, $order, $cart_items);

        // Clear pending order session
        unset($_SESSION['pending_order']);

        echo json_encode(['success' => true, 'order_id' => $order_id]);

    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'Order failed: ' . $e->getMessage()]);
    }
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>