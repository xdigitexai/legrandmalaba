<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$productionAPIToken = $methodExtras["productionAPIToken"];
$productionAPISecretKey = $methodExtras["productionAPISecretKey"];
$orderId = md5(RAND_STRING(5) . time());
$callbackURL = site_url("payment/" . $methodCallback);

$postData = [
    "token" => $productionAPIToken,
    "orderId" => $orderId,
    "txnAmount" => $paymentAmount,
    "txnNote" => "Balance Recharge (" . $user["username"] . ")",
    "customerName" => $user["name"] ?: "UpiApi User",
    "customerEmail" => $user["email"] ?: $user["username"] . rand(1000, 5000) . "@gmail.com",
    "customerMobile" => str_replace("+", "", $user["telephone"]) ?: rand(60000, 99999) . rand(10000, 99999),
    "callbackUrl" => $callbackURL
];

$postData = json_encode($postData);


$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://upiapi.in/order/create',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => $postData,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json'
    ]
]);

$gatewayResponse = curl_exec($curl);
curl_close($curl);
$gatewayResponse = json_decode($gatewayResponse, true);
$checkOutURL = $gatewayResponse["result"]["payment_url"];

if ($gatewayResponse["status"] == true) {

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
window.location.href = "' . $checkOutURL . '";
</script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;

} else {
    errorExit("Something went wrong while initiating your payment.");
}

?>