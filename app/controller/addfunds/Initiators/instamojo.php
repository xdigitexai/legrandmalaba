<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$APIKey = $methodExtras["APIKey"];
$authToken = $methodExtras["authToken"];



$url = "https://www.instamojo.com/api/1.1/payment-requests/";

$payload = [
    'purpose' => "Balance recharge (" . $user["username"] . ")",
    'amount' => number_format($paymentAmount, 2, '.', ''),
    'phone' => ($user["telephone"] ?: "90000000000"),
    'buyer_name' => ($user["name"] ?: "Instamojo User"),
    'redirect_url' => site_url("payment/" . $methodCallback),
    'send_email' => true,
    'webhook' => site_url("payment/" . $methodCallback),
    'send_sms' => false,
    'email' => $user["email"],
    'allow_repeated_payments' => false
];

$headers = [
    "X-Api-Key" => $APIKey,
    "X-Auth-Token" => $authToken
];


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, FALSE);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($payload));
$gatewayResponse = curl_exec($ch);
curl_close($ch);

$gatewayResponse = json_decode($gatewayResponse,true);

$status = $gatewayResponse["success"];
$paymentRequestId = $gatewayResponse["payment_request"]["id"];
$checkOutURL = $gatewayResponse["payment_request"]["longurl"];
if($status){
    
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
    "extra" => $paymentRequestId
]);


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
