<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}
ob_start();
$payment_method = $conn->prepare("SELECT * FROM paymentmethods WHERE methodId=:id");
$payment_method->execute(array('id' => 82));
$payment_method = $payment_method->fetch(PDO::FETCH_ASSOC);
$extras = json_decode($payment_method['methodExtras'], true);
$merchantId = isset($extras['merchant_id']) ? $extras['merchant_id'] : '';
$apiKey = isset($extras['api_key']) ? $extras['api_key'] : '';
$store_name = isset($extras['store_name']) ? $extras['store_name'] : $_SERVER['HTTP_HOST'];
$amount_final = isset($_POST['payment_amount']) ? $_POST['payment_amount'] : 0;
if ($amount_final <= 0) {
    ob_clean();
    header('Content-Type: application/json');
    echo json_encode(array("success" => false, "message" => "Please enter a valid amount."));
    exit;
}
$order_id = "ASN" . time() . rand(1000, 9999);
$client_id = isset($user['client_id']) ? $user['client_id'] : (isset($_SESSION['client_id']) ? $_SESSION['client_id'] : 0);
$insert = $conn->prepare("INSERT INTO payments SET client_id=:client_id, payment_amount=:amount, payment_method=82, payment_mode='Automatic', payment_create_date=:date, payment_ip=:ip, payment_extra=:extra");
$insert->execute(array(
    "client_id" => $client_id,
    "amount" => $amount_final,
    "date" => date("Y-m-d H:i:s"),
    "ip" => GetIP(),
    "extra" => $order_id
));
$callback_url = site_url('payment/assanpay?order_id=' . $order_id);
$apiUrl = "https://api.assanpay.com/payment-request/qr/" . $merchantId;
$data = array(
    "amount" => (string)$amount_final,
    "order id" => $order_id,
    "store name" => $store_name,
    "link" => $callback_url,
    "email" => $store_name
);
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
));
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
ob_clean();
header('Content-Type: application/json');
if (isset($result['data']['completeLink'])) {
    $assan_trx = isset($result['data']['transactionId']) ? $result['data']['transactionId'] : '';
    if (!empty($assan_trx)) {
        $new_extra = $order_id . "@@" . $assan_trx;
        $up = $conn->prepare("UPDATE payments SET payment_extra=:new_extra WHERE payment_extra=:old_extra");
        $up->execute(array("new_extra" => $new_extra, "old_extra" => $order_id));
    }
    $payment_url = $result['data']['completeLink'];
    echo json_encode(array(
        "success" => true,
        "message" => "Redirecting to AssanPay...",
        "content" => "<script>window.location.href='{$payment_url}';</script>"
    ));
} else {
    $error_msg = isset($result['message']) ? $result['message'] : "Gateway error. Please try again.";
    echo json_encode(array(
        "success" => false,
        "message" => $error_msg
    ));
}
exit;
?>