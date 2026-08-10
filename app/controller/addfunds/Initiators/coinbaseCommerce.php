<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$APIKey = $methodExtras["APIKey"];
$orderId = md5(RAND_STRING(5) . time());

$endpoint = 'https://api.commerce.coinbase.com/charges';

$body = [
    'redirect_url' => site_url("payment/".$methodCallback),
    'name' => $settings["site_name"],
    'description' => 'Balance recharge ('.$user["username"].')',
    'pricing_type' => 'fixed_price',
    'local_price' => [
        'amount' => number_format($paymentAmount, 2, '.', ''),
        'currency' => $methodCurrency
    ],
    'metadata' => [
        'customer_id' => $user["client_id"],
        'order_id' => $orderId
    ]
];
$bodyJSON = json_encode($body);

$headers = [
    'Content-Type: application/json',
    'X-CC-Api-Key: ' . $APIKey,
    'X-CC-Version: 2018-03-22'
];

$ch = curl_init($endpoint);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $bodyJSON);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$gatewayResponse = curl_exec($ch);
curl_close($ch);
$responseObj = json_decode($gatewayResponse);
echo "<pre>";
print_r($responseObj);
exit;

$chargeCode = $responseObj->data->code;
$checkOutURL = $responseObj->data->hosted_url;

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
    "extra" => $chargeCode
]);

$_SESSION["coinbaseCommerceChargeCode"] = $chargeCode;

$redirectForm .= '<script type="text/javascript">
window.location.href = "' . $checkOutURL . '";
</script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;
