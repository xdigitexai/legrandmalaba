<?php 
if (!defined('PAYMENT')) { 
    http_response_code(404); 
    die(); 
} 

$secretKey = $methodExtras["secret_key"]; // Corrected key name
$apiKey = $methodExtras["api_key"];
$merchantId = $methodExtras["merchant_id"];

if (!empty($_GET['payment_code'])) {
    // Fetch payment details
    $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_privatecode=:pmprivatecode');
    $payment->execute(['pmprivatecode' => $_GET['payment_code']]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);

    if (!$payment) errorExit("Payment not found.");
    if ($payment['payment_status'] == 3) header("Location: " . site_url("addfunds"));
    if ($payment['payment_status'] != 1) errorExit("Invalid payment status.");

    // Binance Pay transaction query parameters
    $params = [
        'merchantId' => $merchantId,
        'merchantTradeNo' => $payment['payment_privatecode'],
        'timestamp' => round(microtime(true) * 1000)
    ];

    // Generate signature
    $query = http_build_query($params);
    $signature = hash_hmac('sha256', $query, $secretKey);

    // Call Binance Pay API to verify transaction
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.binance.com/sapi/v1/pay/transactions/trade?' . $query . '&signature=' . $signature,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'X-MBX-APIKEY: ' . $apiKey
        ]
    ]);

    $response = curl_exec($curl);
    if(curl_errno($curl)) errorExit("Curl error: " . curl_error($curl));
    curl_close($curl);

    $responseData = json_decode($response, true);
    if(isset($responseData['code'])) errorExit("API Error: {$responseData['msg']}");

    // Check if transaction is successful
    if ($responseData['status'] === 'SUCCESS' && $responseData['currency'] === 'USDT' && (float)$responseData['totalFee'] === (float)$payment['payment_amount']) {
        // Update payment status
        $conn->beginTransaction();
        try {
            $update = $conn->prepare('UPDATE payments SET payment_status = 3, payment_delivery = 2 WHERE payment_id = ?');
            $update->execute([$payment['payment_id']]);

            $newBalance = $user['balance'] + $payment['payment_amount'];
            $balanceUpdate = $conn->prepare('UPDATE clients SET balance = ? WHERE client_id = ?');
            $balanceUpdate->execute([$newBalance, $user['client_id']]);

            $conn->commit();
            header("Location: " . site_url("addfunds"));
            exit;
        } catch (PDOException $e) {
            $conn->rollBack();
            errorExit("Database error: " . $e->getMessage());
        }
    } else {
        errorExit("Payment verification failed. Transaction not confirmed.");
    }
} else {
    http_response_code(400);
    die('Invalid request');
}
?>