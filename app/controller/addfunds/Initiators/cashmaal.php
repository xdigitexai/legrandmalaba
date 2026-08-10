<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$webId = $methodExtras["webId"];

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

$checkoutURL = "https://www.cashmaal.com/Pay/"; 

$data = [
    "pay_method" => "",
    "amount" => $paymentAmount,
    "currency" => $methodCurrency,
    "succes_url" => site_url("payment/".$methodCallback),
    "cancel_url" => site_url("payment/".$methodCallback),
    "client_email" => $user["email"],
    "web_id" => $webId,
    "order_id" => $orderId,
    "addi_info" => "Balance recharge (".$user["username"].")",
];

$redirectForm .= '<form method="POST" action="'.$checkoutURL.'" name="CashmaalCheckoutForm">';
    foreach($data as $name => $value) {
        $redirectForm .= '<input type="hidden" name="'.$name.'" value="'.$value.'">';
    }
    $redirectForm .= '</form>
    <script type="text/javascript">
    document.CashmaalCheckoutForm.submit();
        </script>';
    
    $response["success"] = true;
    $response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
    $response["content"] = $redirectForm;


?>