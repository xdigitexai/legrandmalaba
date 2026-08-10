<?php 
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$MID = $methodExtras["MID"];
$APIKey = $methodExtras["APIKey"];
$mode = $methodExtras["mode"];
$orderId = md5(RAND_STRING(5) . time());


$callbackURL = urlencode(site_url("payment/".$methodCallback));

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

$paymentAmount = from_to($currencies_array,"USD","EGP",$paymentAmount);
$paymentAmount = ROUND_AMOUNT($paymentAmount);

$hashSequence = "/?payment=" . $MID . "." . $orderId . "." .$paymentAmount. "." . "EGP";
$hash =  hash_hmac('sha256', $hashSequence, $APIKey, false);

$checkOutURL = "https://checkout.kashier.io?merchantId=$MID&orderId=$orderId&mode=$mode&amount=$paymentAmount&currency=EGP&hash=$hash&merchantRedirect=$callbackURL&display=en&allowedMethods=card,wallet,bank_installments&type=external";

$redirectForm .= '<script type="text/javascript">
window.location.href = "' . $checkOutURL . '";
</script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;

?>