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

// =============================================
// SAVE ORDER DETAILS TO SESSION
// =============================================
if ($action == 'save_order_session') {
    $_SESSION['pending_order'] = [
        'full_name'  => trim($_POST['full_name']),
        'email'      => trim($_POST['email']),
        'mobile'     => trim($_POST['mobile']),
        'alt_mobile' => trim($_POST['alt_mobile']),
        'address'    => trim($_POST['address']),
        'city'       => trim($_POST['city']),
        'pincode'    => trim($_POST['pincode']),
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

    // Check if this is user's first order
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $order_count    = $stmt->fetchColumn();
    $is_first_order = ($order_count == 0);

    // Calculate discount
    $discount        = 0;
    $discount_reason = '';

    if ($is_first_order) {
        $discount        = $total_amount * 0.10;
        $discount_reason = 'first_order';
    } elseif ($total_qty >= 10) {
        $discount        = $total_amount * 0.10;
        $discount_reason = 'bulk_order';
    }

    $final_amount = $total_amount - $discount;

    $_SESSION['pending_order']['total_amount']    = $total_amount;
    $_SESSION['pending_order']['discount']        = $discount;
    $_SESSION['pending_order']['final_amount']    = $final_amount;
    $_SESSION['pending_order']['is_first_order']  = $is_first_order;
    $_SESSION['pending_order']['discount_reason'] = $discount_reason;

    echo json_encode([
        'success'        => true,
        'is_first_order' => $is_first_order,
        'discount'       => $discount
    ]);
    exit();
}

// =============================================
// PLACE ORDER AFTER PAYMENT
// =============================================
if ($action == 'place_order') {
    if (!isset($_SESSION['pending_order'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Order session expired.'
        ]);
        exit();
    }

    $order          = $_SESSION['pending_order'];
    $payment_method = $_POST['payment_method'] ?? 'card';
    $payment_id     = 'DUMMY_' . strtoupper(uniqid());

    try {
        // Insert order into orders table
        $stmt = $pdo->prepare("INSERT INTO orders
                                (user_id, total_amount, discount, final_amount,
                                 full_name, address, city, pincode,
                                 mobile, alt_mobile, email,
                                 payment_id, payment_status, order_status)
                                VALUES
                                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'paid', 'placed')");
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

        // Get cart items
        $stmt = $pdo->prepare("SELECT c.quantity, p.id as product_id, p.price
                                FROM cart c
                                JOIN products p ON c.product_id = p.id
                                WHERE c.user_id = ?");
        $stmt->execute([$user_id]);
        $cart_items = $stmt->fetchAll();

        // Insert order items and reduce stock
        foreach ($cart_items as $item) {
            $stmt = $pdo->prepare("INSERT INTO order_items
                                    (order_id, product_id, quantity, price)
                                    VALUES (?, ?, ?, ?)");
            $stmt->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price']
            ]);

            // Reduce product stock
            $stmt = $pdo->prepare("UPDATE products
                                    SET stock = stock - ?
                                    WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }

        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);

        // Clear pending order session
        unset($_SESSION['pending_order']);

        // =============================================
        // SEND EMAILS - silently, never block payment
        // =============================================

        // 1. Notify admin about new order
        try {
            require_once '../includes/send_mail.php';
            notifyAdminNewOrder($order_id, $order, $cart_items);
        } catch (Throwable $e) {
            error_log("Admin email error: " . $e->getMessage());
        }

        // 2. Send order confirmation + invoice to customer
        try {
            require_once '../includes/send_order_mail.php';

            // Get full order data
            $stmt = $pdo->prepare("
                SELECT o.*, u.name as user_name, u.email as user_email
                FROM orders o
                JOIN users u ON o.user_id = u.id
                WHERE o.id = ?
            ");
            $stmt->execute([$order_id]);
            $orderData = $stmt->fetch();

            // Get order items with product and category names
            $stmt = $pdo->prepare("
                SELECT oi.*,
                       p.name as product_name,
                       c.name as cat_name
                FROM order_items oi
                JOIN products p ON oi.product_id = p.id
                JOIN categories c ON p.category_id = c.id
                WHERE oi.order_id = ?
            ");
            $stmt->execute([$order_id]);
            $orderItems = $stmt->fetchAll();

            // User data for email
            $userData = [
                'name'  => $orderData['user_name'],
                'email' => $orderData['user_email']
            ];

            // Send confirmation email with invoice PDF
            sendOrderConfirmationEmail($orderData, $orderItems, $userData);

        } catch (Throwable $e) {
            // Email failed silently - order is already saved!
            error_log("Customer email error: " . $e->getMessage());
        }

        // Return success with order_id
        echo json_encode(['success' => true, 'order_id' => $order_id]);

    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Order failed: ' . $e->getMessage()
        ]);
    }
    exit();
}

// =============================================
// CANCEL ORDER
// =============================================
if ($action == 'cancel_order') {
    $order_id = (int)$_POST['order_id'];

    // Check order belongs to this user
    $stmt = $pdo->prepare("SELECT * FROM orders
                            WHERE id = ? AND user_id = ?");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();

    if (!$order) {
        echo json_encode([
            'success' => false,
            'message' => 'Order not found!'
        ]);
        exit();
    }

    // Only allow cancel if Placed or Processing
    if (!in_array($order['order_status'], ['placed', 'processing'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Order cannot be cancelled after it has been shipped!'
        ]);
        exit();
    }

    // Restore stock for each product
    $stmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $items = $stmt->fetchAll();

    foreach ($items as $item) {
        $stmt = $pdo->prepare("UPDATE products
                                SET stock = stock + ?
                                WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }

    // Update order status to cancelled
    $stmt = $pdo->prepare("UPDATE orders
                            SET order_status = 'cancelled'
                            WHERE id = ?");
    $stmt->execute([$order_id]);

    echo json_encode([
        'success' => true,
        'message' => 'Order cancelled successfully!'
    ]);
    exit();
}

echo json_encode(['success' => false, 'message' => 'Invalid action.']);
?>