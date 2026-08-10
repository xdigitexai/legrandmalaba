<?php
$config = require __DIR__ . '/app/config.php';

try {
    $conn = new PDO("mysql:host=".$config["db"]["host"].";dbname=".$config["db"]["name"].";charset=".$config["db"]["charset"].";", $config["db"]["user"], $config["db"]["pass"]);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("DB Connection failed");
}

$method = $conn->prepare("SELECT * FROM paymentmethods WHERE methodId = 82");
$method->execute();

if($method->rowCount() == 0) {
    die("AssanPay method not found.");
}

$methodConf = $method->fetch(PDO::FETCH_ASSOC);
$extras = json_decode($methodConf['methodExtras'], true);
$merchantId = isset($extras['merchant_id']) ? $extras['merchant_id'] : '';
$apiKey = isset($extras['api_key']) ? $extras['api_key'] : '';

$stmt = $conn->prepare("SELECT * FROM payments WHERE payment_method = 82 AND payment_status = 1");
$stmt->execute();
$payments = $stmt->fetchAll(PDO::FETCH_ASSOC);

$checked = 0;
$updated = 0;

foreach ($payments as $payment) {
    $checked++;
    $parts = explode('@@', $payment['payment_extra']);
    $assan_trx = isset($parts[1]) ? $parts[1] : $payment['payment_extra'];
    
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
        
        $updated++;
    }
}

echo "Cron executed. Checked: $checked | Updated: $updated";
?>