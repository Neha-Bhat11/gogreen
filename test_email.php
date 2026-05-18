<?php
require_once 'includes/send_mail.php';

echo "<h2>Testing Email...</h2>";

$result = sendOTPMail('kpneha77@gmail.com', 'Test User', '123456', 'login');

if ($result) {
    echo "<p style='color:green'>✅ Email sent successfully!</p>";
} else {
    echo "<p style='color:red'>❌ Email failed!</p>";
}
?>