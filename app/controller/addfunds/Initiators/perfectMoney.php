<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$payeeAccount = $methodExtras["accountNumber"];
$payeeName = $user["name"] ?: "User";
$orderId = md5(RAND_STRING(5) . time());
$paymentURL = site_url("payment/" . $methodCallback);
$paymentURLMethod = 'POST';
$noPaymentURL = site_url("payment/" . $methodCallback);
$noPaymentURLMethod = 'POST';
$suggestedMemo = "Balance recharge (".$user["username"].")";

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


$data = [
    'PAYEE_ACCOUNT' => $payeeAccount,
    'PAYEE_NAME' => $payeeName,
    'PAYMENT_ID' => $orderId,
    'PAYMENT_AMOUNT' => $paymentAmount,
    'PAYMENT_UNITS' => $methodCurrency,
    'PAYMENT_URL' => $paymentURL,
    'PAYMENT_URL_METHOD' => $paymentURLMethod,
    'NOPAYMENT_URL' => $noPaymentURL,
    'NOPAYMENT_URL_METHOD' => $noPaymentURLMethod,
    'ORDER_NUM' => $orderId,
    'BAGGAGE_FIELDS' => 'IDENT',
    'SUGGESTED_MEMO' => $suggestedMemo,
];

$redirectForm .= '<form method="POST" action="https://perfectmoney.is/api/step1.asp" name="perfectMoneyCheckoutForm">';
foreach ($data as $name => $value) {
    $redirectForm .= '<input type="hidden" name="' . $name . '" value="' . $value . '">';
}
$redirectForm .= '</form>
<script type="text/javascript">
document.perfectMoneyCheckoutForm.submit();
</script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;
?>
