<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$secretKey = $methodExtras["key"];
$orderId = md5(RAND_STRING(5) . time());
$callbackURL = site_url("payment/" . $methodCallback);
$paymentCode = $orderId;

$paymongo_api_secret = $secretKey;
if (!empty($paymongo_api_secret)) {
    $externalId = "invoice-" . time();
    $success_redirect_url = $callbackURL . '?payment_code=' . $paymentCode;
    $failure_redirect_url = $callbackURL . '?payment_code=' . $paymentCode;

    $params = [
        'data' => [
            'attributes' => [
                'line_items' => [
                    [
                        'amount' => $paymentAmount * 100, // Convert to cents
                        'currency' => 'PHP',
                        'name' => 'Addfunds',
                        'quantity' => 1
                    ]
                ],
                'payment_method_types' => ['card', 'gcash', 'grab_pay'],
                'success_url' => $success_redirect_url,
                'cancel_url' => $failure_redirect_url,
                'description' => 'Addfunds for ' . $user['username'],
                'metadata' => [
                    'payment_code' => $paymentCode,
                    'external_id' => $externalId
                ]
            ]
        ]
    ];

    $authHash = base64_encode($paymongo_api_secret . ':');
    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL => 'https://api.paymongo.com/v1/checkout_sessions',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => json_encode($params),
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Basic ' . $authHash
        ],
        CURLOPT_SSL_VERIFYHOST => 0,
        CURLOPT_SSL_VERIFYPEER => 0,
    ]);

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        errorExit("cURL Error: " . $err);
    }

    $finalResponse = json_decode($response, true);

    if (isset($finalResponse['data']['attributes']['checkout_url'])) {
        $redirectUrl = $finalResponse['data']['attributes']['checkout_url'];
        $sessionId = $finalResponse['data']['id'];

        // Insert payment record
        $insert = $conn->prepare("INSERT INTO payments SET client_id=:c_id, payment_amount=:amount, payment_privatecode=:code, payment_method=:method, payment_mode=:mode, payment_create_date=:date, payment_ip=:ip, payment_extra=:extra");
        $insert->execute([
            "c_id" => $user['client_id'],
            "amount" => $paymentAmount,
            "code" => $paymentCode,
            "method" => $methodId,
            "mode" => "Automatic",
            "date" => date("Y.m.d H:i:s"),
            "ip" => GetIP(),
            "extra" => $sessionId
        ]);

        $redirectForm = '<script type="text/javascript">
            window.location.href = "' . $redirectUrl . '";
        </script>';

        $response = [
            "success" => true,
            "message" => "Your payment has been initiated. Redirecting to payment gateway...",
            "content" => $redirectForm
        ];
    } else {
        errorExit("Failed to create PayMongo session: " . ($finalResponse['errors'][0]['detail'] ?? 'Unknown error'));
    }
} else {
    errorExit("Invalid PayMongo secret key configuration.");
}
?>