<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$secretKey = $methodExtras["secretKey"];

if ($_SERVER["REQUEST_METHOD"] == "GET") {

    if ($_GET["status"] == "successful") {

        $transactionId = htmlspecialchars($_GET["transaction_id"]);
        $transactionReference = htmlspecialchars($_GET["tx_ref"]);

        if (empty($transactionId) || empty($transactionReference)) {
            errorExit("Missing data.");
        }

        $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:transactionReference");
        $paymentDetails->execute([
            "transactionReference" => $transactionReference
        ]);

        if ($paymentDetails->rowCount()) {
            $paymentDetails = $paymentDetails->fetch(PDO::FETCH_ASSOC);

            if (
                !countRow([
                    'table' => 'payments',
                    'where' => [
                        'client_id' => $user['client_id'],
                        'payment_method' => $methodId,
                        'payment_status' => 3,
                        'payment_delivery' => 2,
                        'payment_extra' => $transactionReference
                    ]
                ])
            ) {
                $url = "https://api.flutterwave.com/v3/transactions/{$transactionId}/verify";

                $headers = [
                    "Content-Type: application/json",
                    "Authorization: Bearer " . $secretKey . "",
                ];

                $curl = curl_init();
                curl_setopt_array(
                    $curl,
                    [
                        CURLOPT_URL => $url,
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_ENCODING => "",
                        CURLOPT_MAXREDIRS => 10,
                        CURLOPT_TIMEOUT => 0,
                        CURLOPT_FOLLOWLOCATION => true,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                        CURLOPT_CUSTOMREQUEST => "GET",
                        CURLOPT_HTTPHEADER => $headers,
                    ]
                );
                $response = curl_exec($curl);
                curl_close($curl);

                $response = json_decode($response, 1);

                $gatewayPaidAmount = $response["data"]["charged_amount"];
                $gatewayAmountToBePaid = $response["data"]["meta"]["price"];

                if ($gatewayPaidAmount >= $gatewayAmountToBePaid) {

                    $paidAmount = floatval($paymentDetails["payment_amount"]);

                    if ($paymentFee > 0) {
                        $fee = ($paidAmount * ($paymentFee / 100));
                        $paidAmount -= $fee;
                    }
                    if ($paymentBonusStartAmount != 0 && $paidAmount > $paymentBonusStartAmount) {
                        $bonus = $paidAmount * ($paymentBonus / 100);
                        $paidAmount += $bonus;
                    }

                    $paidAmount = from_to($currencies_array, $methodCurrency, $settings["site_base_currency"], $paidAmount);

                    $update = $conn->prepare('UPDATE payments SET 
                    client_balance=:balance,
                    payment_status=:status, 
                    payment_delivery=:delivery WHERE payment_id=:id');

                    $update->execute([
                        'balance' => $user["balance"],
                        'status' => 3,
                        'delivery' => 2,
                        'id' => $paymentDetails['payment_id']
                    ]);

                    $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id');
                    $balance->execute([
                        "balance" => $user["balance"] + $paidAmount,
                        "id" => $user["client_id"]
                    ]);

                    header("Location: " . site_url("addfunds"));

                } else {
                    errorExit("Fraudulent activity detected.");
                }

            } else {
                errorExit("Order ID is already used.");
            }

        } else {
            errorExit("Order ID not found.");
        }

    } else {
        errorExit("Payment not successful.");
    }

} else {
    http_response_code(405);
    die();
}

?>