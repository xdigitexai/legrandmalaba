<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$secretKey = $methodExtras["key"];
$orderId = md5(RAND_STRING(5) . time());
$callbackURL = site_url("payment/" . $methodCallback);
$paymentCode = $orderId;

$xendit_api_secret = $secretKey;
if (!empty($xendit_api_secret)) {
    $externalId = "invoice-" . time();
    $success_redirect_url = $callbackURL . '?payment_code=' . $paymentCode;
    $failure_redirect_url = $callbackURL . '?payment_code=' . $paymentCode;
    $params = [
        'external_id' => $externalId,
        'payer_email' => $user['email'],
        'description' => URL . '&nbsp' . "Addfunds In" . '&nbsp' . $user['username'] . '&nbsp' . $externalId,
        'success_redirect_url' => $success_redirect_url,
        'failure_redirect_url' => $failure_redirect_url,
        'amount' => $paymentAmount,
        'invoice_duration' => 86400 // Set invoice expiry time to 24 hours
    ];

    $params = json_encode($params);
    $authHash = base64_encode($xendit_api_secret . ":" . '');
    $curl = curl_init();

    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.xendit.co/v2/invoices/',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_POSTFIELDS => $params,
        CURLOPT_HTTPHEADER => array(
            'Content-Type: application/json',
            'Authorization: Basic ' . $authHash
        ),
    ));

    $responses = curl_exec($curl);
    curl_close($curl);
    $finalResponse = json_decode($responses, true);

    // Log the response for debugging
    file_put_contents('xendit_response.log', print_r($finalResponse, true), FILE_APPEND);

    if (isset($finalResponse['invoice_url'])) {
        $redirectUrl = $finalResponse['invoice_url'];

        // Insert payment details into the database
        $insert = $conn->prepare("INSERT INTO payments SET client_id=:c_id, payment_amount=:amount, payment_privatecode=:code, payment_method=:method, payment_mode=:mode, payment_create_date=:date, payment_ip=:ip, payment_extra=:extra");
        $insert->execute([
            "c_id" => $user['client_id'],
            "amount" => $paymentAmount,
            "code" => $paymentCode,
            "method" => $methodId,
            "mode" => "Automatic",
            "date" => date("Y.m.d H:i:s"),
            "ip" => GetIP(),
            "extra" => $finalResponse['id']
        ]);

        // Redirect to the payment gateway
        $redirectForm = '<script type="text/javascript">
        window.location.href = "' . $redirectUrl . '";
        </script>';

        $response["success"] = true;
        $response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
        $response["content"] = $redirectForm;
    } else {
        errorExit("Something went wrong while initiating your payment.");
    }
} else {
    errorExit("Invalid secret key.");
}
?>