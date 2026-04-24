<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/phpmailer/src/Exception.php';
require_once __DIR__ . '/../vendor/phpmailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/phpmailer/src/SMTP.php';

function sendOTPMail($toEmail, $toName, $otp, $type = 'login') {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'kpneha77@gmail.com'; // ← change this
        $mail->Password   = 'hjbm cfwc fzuw cpyx';    // ← change this
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('kpneha77@gmail.com', 'GreenLife');
        $mail->addAddress($toEmail, $toName);
        $mail->isHTML(true);

        if ($type == 'register') {
            $mail->Subject = 'Welcome to GreenLife - Registration Successful';
            $mail->Body    = "<h2>Welcome, $toName!</h2>
                              <p>You have successfully registered on <b>GreenLife</b>.</p>
                              <p>Start exploring our plant seeds collection today! 🌱</p>";
        } else {
            $mail->Subject = 'GreenLife - Your OTP Code';
            $mail->Body    = "<h2>Hello, $toName!</h2>
                              <p>Your OTP for login is: <b style='font-size:24px;color:green;'>$otp</b></p>
                              <p>This OTP is valid for <b>5 minutes</b>.</p>
                              <p>Do not share this OTP with anyone.</p>";
        }

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

function notifyAdminNewOrder($order_id, $order, $items) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'your_email@gmail.com'; // ← your gmail
        $mail->Password   = 'your_app_password';    // ← your app password
        $mail->SMTPSecure = 'tls';
        $mail->Port       = 587;

        $mail->setFrom('your_email@gmail.com', 'GreenLife');
        $mail->addAddress('your_email@gmail.com'); // ← admin email
        $mail->isHTML(true);
        $mail->Subject = '🌱 New Order #' . str_pad($order_id, 6, '0', STR_PAD_LEFT);

        $items_html = '';
        foreach ($items as $item) {
            $items_html .= "<tr>
                <td>{$item['product_id']}</td>
                <td>x{$item['quantity']}</td>
                <td>₹{$item['price']}</td>
            </tr>";
        }

        $mail->Body = "
        <h2 style='color:#1b5e20;'>🌱 New Order Received!</h2>
        <p><b>Order ID:</b> #" . str_pad($order_id, 6, '0', STR_PAD_LEFT) . "</p>
        <p><b>Customer:</b> {$order['full_name']}</p>
        <p><b>Mobile:</b> {$order['mobile']}</p>
        <p><b>Address:</b> {$order['address']}, {$order['city']} - {$order['pincode']}</p>
        <p><b>Amount Paid:</b> ₹{$order['final_amount']}</p>
        <p><b>Payment:</b> PAID ✅</p>
        <p><a href='http://localhost:8085/gogreen/admin/manage_orders.php'
              style='background:#1b5e20; color:white; padding:10px 20px;
                     border-radius:8px; text-decoration:none;'>
           View Order in Admin Panel
        </a></p>";

        $mail->send();
    } catch (Exception $e) {
        // Silent fail - don't block order
    }
}
?>