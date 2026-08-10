<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}
require_once("lib/encdec_paytm.php");

$merchantId = $methodExtras["merchantId"];
$merchantKey = $methodExtras["merchantKey"];

$orderId = md5(RAND_STRING(5).time());

$paramList = [];
$paramList["MID"] = $merchantId;
$paramList["ORDER_ID"] = $orderId;
$paramList["CUST_ID"] = $user['client_id'];
$paramList["EMAIL"] = $user['email'] ?: "user@user.com";
$paramList["INDUSTRY_TYPE_ID"] = "Retail";
$paramList["CHANNEL_ID"] = "WEB";
$paramList["TXN_AMOUNT"] = number_format($paymentAmount, 2, '.', '');
$paramList["WEBSITE"] = "DEFAULT";
$paramList["CALLBACK_URL"] = site_url("payment/".$methodCallback);
$checkSum = getChecksumFromArray($paramList, $merchantKey);

$insert = $conn->prepare("INSERT INTO payments SET
          client_id=:client_id, payment_amount=:amount, payment_method=:method,
          payment_mode=:mode, payment_create_date=:date,
          payment_ip=:ip,
          payment_extra=:extra"
          );
$insert->execute([
     "client_id" => $user["client_id"],
     "amount" => $paymentAmount,
     "method" => $method["methodId"],
     "mode" => "Automatic",
     "date" => date("Y.m.d H:i:s"),
     "ip" => GetIP(),
     "extra" => $orderId
]);

$redirectForm .= '<form method="POST" action="https://securegw.paytm.in/theia/processTransaction" name="paytmCheckoutForm">';
foreach($paramList as $name => $value) {
    $redirectForm .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
}
$redirectForm .= '<input type="hidden" name="CHECKSUMHASH" value="'.$checkSum.'">
    <script type="text/javascript">
document.paytmCheckoutForm.submit();
    </script>
</form>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;
?>