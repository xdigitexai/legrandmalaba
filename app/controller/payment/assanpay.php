<?php
if (!defined('BASEPATH')) {
    die();
}
$order_id = isset($_GET['order_id']) ? $_GET['order_id'] : '';
if (empty($order_id)) {
    header("Location: " . site_url('addfunds'));
    exit;
}
$chk = $conn->prepare("SELECT * FROM payments WHERE payment_extra LIKE :extra AND payment_status=1");
$chk->execute(array('extra' => $order_id . '%'));
if ($chk->rowCount() == 0) {
    header("Location: " . site_url('addfunds'));
    exit;
}
$payment = $chk->fetch(PDO::FETCH_ASSOC);
$parts = explode('@@', $payment['payment_extra']);
$assan_trx = isset($parts[1]) ? $parts[1] : $order_id;
$method = $conn->prepare("SELECT * FROM paymentmethods WHERE methodId = 82");
$method->execute();
$methodConf = $method->fetch(PDO::FETCH_ASSOC);
$extras = json_decode($methodConf['methodExtras'], true);
$merchantId = isset($extras['merchant_id']) ? $extras['merchant_id'] : '';
$apiKey = isset($extras['api_key']) ? $extras['api_key'] : '';
$apiUrl = "https://api.assanpay.com/payment/all-inquiry/" . $merchantId . "?transactionId=" . $assan_trx;
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $apiKey
));
$response = curl_exec($ch);
curl_close($ch);
$result = json_decode($response, true);
$api_status = '';
if (isset($result['data']['transactionStaus'])) {
    $api_status = strtolower($result['data']['transactionStaus']);
} elseif (isset($result['data']['transactionStatus'])) {
    $api_status = strtolower($result['data']['transactionStatus']);
} elseif (isset($result['data']['status'])) {
    $api_status = strtolower($result['data']['status']);
} elseif (isset($result['status']) && $result['status'] == 'true' && isset($result['data']) && !empty($result['data'])) {
    $api_status = 'success';
}
if (in_array($api_status, array('success', 'completed', 'paid', 'approved', 'true'))) {
    $amount = $payment['payment_amount'];
    $client_id = $payment['client_id'];
    $update = $conn->prepare("UPDATE payments SET payment_status=3, payment_delivery=2 WHERE payment_id=:id");
    $update->execute(array("id" => $payment['payment_id']));
    $updateUser = $conn->prepare("UPDATE clients SET balance = balance + :amount WHERE client_id=:id");
    $updateUser->execute(array("amount" => $amount, "id" => $client_id));
    header("Location: " . site_url('addfunds'));
    exit;
} else {
    header("Location: " . site_url('addfunds'));
    exit;
}
?>