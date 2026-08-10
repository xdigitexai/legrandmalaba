<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$merchantId = $methodExtras["merchantId"];
$publicKey = $methodExtras["publicKey"];
// $secretKey = $methodExtras["secretKey"];
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

$paymentAmount = from_to($currencies_array, "USD", "EGP", $paymentAmount);
$paymentAmount = ROUND_AMOUNT($paymentAmount);
$paymentAmount = $paymentAmount * 100;

$postData = [
    'country' => 'EG',
    'reference' => $orderId,
    'amount' => [
        "total" => $paymentAmount,
        "currency" => "EGP",
    ],
    'returnUrl' => $callbackURL,
    'callbackUrl' => $callbackURL,
    'cancelUrl' => $callbackURL,
    'expireAt' => 30,
    'productList' => [
        [
            "productId" => uniqid(),
            "name" => "Balance Recharge",
            "description" => "Balance Recharge (" . $user["username"] . ")",
            "price" => $paymentAmount,
            "quantity" => 1
        ]
    ]
];

$postData = (string) json_encode($postData, JSON_UNESCAPED_SLASHES);

$headers = [
    'Content-Type: application/json', 
    'Authorization:Bearer ' . $publicKey, 
    'MerchantId:' . $merchantId
];


$url = "https://api.opaycheckout.com/api/v1/international/cashier/create";

$ch = curl_init();
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$gatewayResponse = curl_exec($ch);
curl_close($ch);

$gatewayResponse = json_decode($gatewayResponse,true);

if($gatewayResponse["message"] == "SUCCESSFUL"){
    $checkOutURL = $gatewayResponse["data"]["cashierUrl"];

    $redirectForm .= '<script type="text/javascript">
    window.location.href = "' . $checkOutURL . '";
    </script>';
    
    $response["success"] = true;
    $response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
    $response["content"] = $redirectForm;
} else {
    print_r($gatewayResponse);
    errorExit("Something went wrong while initiating your payment.");
}
