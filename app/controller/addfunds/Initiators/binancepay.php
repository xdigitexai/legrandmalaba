<?php 
if (!defined('ADDFUNDS')) { 
    http_response_code(404); 
    die(); 
} 

$apiKey = $methodExtras["api_key"];
$secretKey = $methodExtras["secret_key"];
$merchantId = $methodExtras["merchant_id"];

// Generate unique merchant trade number
$merchantTradeNo = "INV-" . time() . "-" . bin2hex(random_bytes(4));

$params = [
    'merchantId' => $merchantId,
    'merchantTradeNo' => $merchantTradeNo,
    'orderType' => 'DIRECT', // or 'ESCROW'
    'totalFee' => round($paymentAmount, 2),
    'currency' => 'USDT',
    'goods' => [[
        'goodsType' => '01',
        'goodsCategory' => 'Z000',
        'referenceGoodsId' => 'ADD_FUNDS',
        'goodsName' => 'Account Topup - ' . $user['username'],
        'goodsDetail' => 'Balance addition for user ' . $user['email']
    ]],
    'returnUrl' => site_url("payment/" . $methodCallback) . '?payment_code=' . $merchantTradeNo,
    'timestamp' => round(microtime(true) * 1000)
];

// Generate signature
$queryString = http_build_query($params);
$signature = hash_hmac('sha256', $queryString, $secretKey);

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.binance.com/sapi/v1/pay/transactions',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $queryString . '&signature=' . $signature,
    CURLOPT_HTTPHEADER => [
        'X-MBX-APIKEY: ' . $apiKey,
        'Content-Type: application/x-www-form-urlencoded'
    ]
]);

$response = curl_exec($curl);
$httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

$responseData = json_decode($response, true);

// Error handling
if ($httpCode !== 200 || isset($responseData['code'])) {
    errorExit("API Error: " . ($responseData['msg'] ?? 'Unknown error'));
}

if (!isset($responseData['data']['checkoutUrl'])) {
    errorExit("Missing checkout URL in response");
}

// Store payment details
try {
    $conn->beginTransaction();
    $insert = $conn->prepare("INSERT INTO payments SET client_id = :client_id, payment_amount = :amount, payment_privatecode = :code, payment_method = :method, payment_extra = :extra, payment_create_date = NOW()");
    $insert->execute([
        'client_id' => $user['client_id'],
        'amount' => $paymentAmount,
        'code' => $merchantTradeNo,
        'method' => $methodId,
        'extra' => json_encode($responseData['data'])
    ]);
    $conn->commit();
} catch (PDOException $e) {
    $conn->rollBack();
    errorExit("Database error: " . $e->getMessage());
}

// Redirect to Binance checkout
header("Location: " . $responseData['data']['checkoutUrl']);
exit;