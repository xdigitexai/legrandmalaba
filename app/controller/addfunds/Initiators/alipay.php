<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$partnerId = $methodExtras["partnerId"];
$privateKey = $methodExtras["privateKey"];

require_once("lib/alipay.php");

$orderId = md5(RAND_STRING(5) . time());
$description = "Balance recharge (" . $user["username"] . ")";
$uuid = uuid();

$return_url = site_url("payment/" . $methodCallback);
$notify_url = site_url("payment/" . $methodCallback);
$alipay = new Alipay($partnerId,$privateKey);

$checkoutURL = $alipay->createPayment($orderId, $paymentAmount, $methodCurrency, $description, $return_url, $notify_url);


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




$redirectForm .= '<script type="text/javascript">
    window.location.href = "' . $checkoutURL . '";
        </script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;

?>