<?php
if (!defined("ADDFUNDS")) {
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


\Stripe\Stripe::setApiKey($secretKey);

$paymentAmount = $paymentAmount * 100;

$checkoutSession = \Stripe\Checkout\Session::create([
    "payment_method_types" => ["card"],
    "line_items" => [
        [
            "price_data" => [
                "currency" => "usd",
                "unit_amount" => $paymentAmount,
                "product_data" => [
                    "name" => $settings["site_name"],
                    "description" => "Balance Recharge (" . $user["username"] . ")",
                ],
            ],
            "quantity" => 1,
        ]
    ],
    "mode" => "payment",
    "success_url" => $callbackURL . "?session_id={CHECKOUT_SESSION_ID}",
    "cancel_url" => site_url(""),
    "customer_email" => $user["email"],
    "client_reference_id" => $orderId
]);

$checkOutURL = $checkoutSession->url;

$redirectForm .= '<script type="text/javascript">
    window.location.href = "' . $checkOutURL . '";
    </script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;

?>