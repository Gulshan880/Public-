<?php
include 'config.php';
require_once 'razorpay-php/Razorpay.php';

use Razorpay\Api\Api;

$key_id = "rzp_live_xxxxxxxxxxxx";
$key_secret = "xxxxxxxxxxxxxxxxxxxx";

$api = new Api($key_id, $key_secret);

$success = true;
$error = "Payment Failed!";

if (empty($_POST['razorpay_payment_id']) === false) {
    try {
        $attributes = array(
            'razorpay_order_id' => $_SESSION['razorpay_order_id'],
            'razorpay_payment_id' => $_POST['razorpay_payment_id'],
            'razorpay_signature' => $_POST['razorpay_signature']
        );
        
        $api->utility->verifyPaymentSignature($attributes);
    } catch(SignatureVerificationError $e) {
        $success = false;
        $error = 'Razorpay Error : ' . $e->getMessage();
    }
}

if ($success === true) {
    $amount = $_SESSION['investment_amount'];
    $start_date = date('Y-m-d H:i:s');
    $end_date = date('Y-m-d H:i:s', strtotime('+28 days'));
    
    $sql = "INSERT INTO investments (user_id, amount, status, start_date, end_date, return_amount) VALUES (?, ?, 'active', ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['user_id'], $amount, $start_date, $end_date, $amount * 2]);
    
    $success_msg = "Investment of ₹{$amount} successful!";
    header("Location: dashboard.php?success=" . urlencode($success_msg));
    exit();
} else {
    header("Location: invest.php?error=" . urlencode($error));
    exit();
}
?>