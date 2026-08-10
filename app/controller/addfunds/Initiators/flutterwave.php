<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$secretKey = $methodExtras["secretKey"];
$orderId = md5(RAND_STRING(5) . time());
$callbackURL = site_url("payment/" . $methodCallback);

$insert = $conn->prepare(
    "INSERT INTO payments SET
client_id=:client_id,
payment_amount=:amount,
payment_method=:method,
payment_mode=:mode,
payment_create_date=:date,
payment_ip=:ip,
payment_extra=:extra"
);

$insert->execute([
    "client_id" => $user["client_id"],
    "amount" => $paymentAmount,
    "method" => $methodId,
    "mode" => "Automatic",
    "date" => date("Y.m.d H:i:s"),
    "ip" => GetIP(),
    "extra" => $orderId
]);

$paymentAmount = from_to($currencies_array, "USD", "NGN", $paymentAmount);
$paymentAmount = ROUND_AMOUNT($paymentAmount);

$url = "https://api.flutterwave.com/v3/payments";

$postData = [
    'tx_ref' => $orderId,
    'amount' => $paymentAmount,
    'currency' => 'NGN',
    'payment_options' => 'card, ussd, mobilemoneyghana, banktransfer',
    'redirect_url' => $callbackURL,
    'customer' => [
        'email' => $user["email"],
        'name' => $user["name"]
    ],
    'meta' => [
        'price' => $paymentAmount
    ],
    'customizations' => [
        'title' => 'Balance Recharge (' . $user["username"] . ')',
        'description' => ''
    ]
];

$postData = json_encode($postData);

$headers = [
    'Authorization: Bearer ' . $secretKey . '',
    'Content-Type: application/json'
];

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => $headers,
]);

$gatewayResponse = curl_exec($curl);
curl_close($curl);

$gatewayResponse = json_decode($gatewayResponse, 1);

if ($gatewayResponse["status"] == "success") {
    $checkOutURL = $gatewayResponse["data"]["link"];

    $redirectForm .= '<script type="text/javascript">
    window.location.href = "' . $checkOutURL . '";
    </script>';

    $response["success"] = true;
    $response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
    $response["content"] = $redirectForm;

} else {
    errorExit("Something went wrong while initiating your payment.");
}

?>