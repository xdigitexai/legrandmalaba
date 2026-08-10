<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$php_input = file_get_contents("php://input");

if ($_SERVER["REQUEST_METHOD"] == "GET" ||  $_SERVER["REQUEST_METHOD"] == "POST") {
    if (!$_SESSION["coinbaseCommerceChargeCode"]) {
        errorExit("Missing data.");
    }
    $APIKey = $methodExtras["APIKey"];
    $chargeCode = $_SESSION["coinbaseCommerceChargeCode"];

    $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:chargeCode");
    $paymentDetails->execute([
        "chargeCode" => $chargeCode
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
                    'payment_extra' => $chargeCode
                ]
            ])
        ) {
            $endpoint = "https://api.commerce.coinbase.com/charges/" . $chargeCode;

            $headers = [
                'Content-Type: application/json',
                'X-CC-Api-Key: ' . $APIKey,
                'X-CC-Version: 2018-03-22'
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $endpoint);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $gatewayResponse = curl_exec($ch);
            curl_close($ch);
            $responseObj = json_decode($gatewayResponse);

            $paymentStatus = $responseObj->data->status;


            if ($paymentStatus == "COMPLETED") {

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
                payment_delivery=:delivery,
                t_id=:tid WHERE payment_id=:id');
                $update->execute([
                    'balance' => $user["balance"],
                    'status' => 3,
                    'delivery' => 2,
                    't_id' => $php_input,
                    'id' => $paymentDetails['payment_id']
                ]);

                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id');
                $balance->execute([
                    "balance" => $user["balance"] + $paidAmount,
                    "id" => $user["client_id"]
                ]);
                header("Location: " . site_url("addfunds"));
                
            } else {
                errorExit("Payment failed.");
            }
        } else {
            errorExit("Charge code already used.");
        }
    } else {
        errorExit("Charge code not found.");
    }
} else {
    http_response_code(405);
    die();
}
?>