<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$merchantKey = $methodExtras["merchantKey"];
$merchantSalt = $methodExtras["merchantSalt"];
$callbackURL = site_url("payment/" . $methodCallback);
$orderId = md5(RAND_STRING(5) . time());

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

$paymentAmount = number_format($paymentAmount, 2, '.', '');
$clientName = trim($user["name"]) ?: "User";
$productInfo = "Balance Recharge (" . $user["username"] . ")";

$hash = hash('sha512', $merchantKey . '|' . $orderId . '|' . $paymentAmount . '|' . urlencode($productInfo) . '|' . $clientName . '|' . $user["email"] . '|||||||||||' . $merchantSalt);


$url = "https://secure.payu.in/_payment";

$data = [
    "key" => $merchantKey,
    "txnid" => $orderId,
    "amount" => $paymentAmount,
    "firstname" => $clientName,
    "email" => $user["email"],
    "phone" => $user["telephone"],
    "productinfo" => urlencode($productInfo),
    "surl" => $callbackURL,
    "furl" => $callbackURL,
    "hash" => $hash
];

$redirectForm .= '<form method="POST" action="' . $url . '" name="PayUCheckoutForm">';
foreach ($data as $name => $value) {
    $redirectForm .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
}
$redirectForm .= '</form>
    <script type="text/javascript">
    document.PayUCheckoutForm.submit();
    </script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;

?>