<?php
if (!defined("ADDFUNDS")) {
    http_response_code(404);
    die();
}

$shopId = $methodExtras["shopId"];
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




$hashSequence = [
    $shopId,
    $orderId,
    number_format($paymentAmount, 2, '.', ''),
    $methodCurrency,
    base64_encode("Balance Recharge (".$user["username"].")"),
    $secretKey

];

$signature = strtoupper(hash('sha256', implode(':', $hashSequence)));

$data = [
    "m_shop" => $shopId,
    "m_orderid" => $orderId,
    "m_amount" => number_format($paymentAmount, 2, '.', ''),
    "m_curr" => $methodCurrency,
    "m_desc" => base64_encode("Balance Recharge (".$user["username"].")"),
    "m_sign" => $signature
];

$url = "https://payeer.com/merchant/?".http_build_query($data);

$redirectForm .= '<script type="text/javascript">
    window.location.href = "' . $url . '";
    </script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;

?>