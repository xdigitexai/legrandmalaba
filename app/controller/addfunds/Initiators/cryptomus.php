<?php
if (!defined('ADDFUNDS')) { http_response_code(404); die(); }

function get_cryptomus_url($merchantId, $apiKey, $data) {
    $payload = [
        'amount'   => (string)number_format($data['amount'], 2, '.', ''),
        'currency' => 'USD',
        'order_id' => $data['order_id'],
        'url_return' => site_url('addfunds'),
        'url_callback' => site_url('payment/cryptomus')
    ];

    $sign = md5(base64_encode(json_encode($payload)) . $apiKey);

    $ch = curl_init('https://api.cryptomus.com/v1/payment');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'merchant: ' . $merchantId,
            'sign: ' . $sign,
            'Content-Type: application/json'
        ]
    ]);

    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);
    return $result['result']['url'] ?? false;
}

// Swichpay logic ke mutabiq keys uthana
$merchant = $methodExtras['merchant_id'];
$api_key  = $methodExtras['api_key'];

if (empty($merchant) || empty($api_key)) {
    errorExit('❌ Cryptomus: Settings missing in Admin Panel (Merchant ID or API Key)');
}

$basket_id = "CRP" . time();
$paymentAmount1 = $paymentAmount;
$paymentAmount  = from_to($currencies_array, $methodCurrency, $settings["site_base_currency"], $paymentAmount);

$data_to_send = ["amount" => $paymentAmount1, "order_id" => $basket_id];
$checkout_url = get_cryptomus_url($merchant, $api_key, $data_to_send);

if ($checkout_url) {
    $insert = $conn->prepare("INSERT INTO payments SET client_id=:client_id, client_balance=:balance, payment_amount=:amount, payment_method=:method, payment_mode=:mode, payment_create_date=NOW(), payment_ip=:ip, payment_extra=:extra, t_id=:t_id");
    $insert->execute([
        "client_id" => $user["client_id"], "balance" => $user['balance'], "amount" => $paymentAmount,
        "method" => $methodId, "mode" => "Automatic", "ip" => GetIP(), "extra" => $basket_id, "t_id" => $basket_id
    ]);

    $response["success"] = true;
    $response["message"] = "Redirecting to Cryptomus...Please wait";
    $response["content"] = '<script type="text/javascript">window.location = "'.$checkout_url.'";</script>';
} else {
    errorExit('❌ Cryptomus: Link generate nahi ho saki. Check your Merchant ID.');
}