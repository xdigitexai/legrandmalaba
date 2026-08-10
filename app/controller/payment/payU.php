<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $orderId = htmlspecialchars(trim($_POST["txnid"] ?: ""));

    if (empty($orderId)) {
        errorExit("Missing data.");
    }

    $merchantKey = $methodExtras["merchantKey"];
    $merchantSalt = $methodExtras["merchantSalt"];

    $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:orderId");
    $paymentDetails->execute([
        "orderId" => $orderId
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
                    'payment_extra' => $orderId
                ]
            ])
        ) {
            $hash = hash("sha512",$merchantKey."|"."verify_payment"."|".$orderId."|".$merchantSalt);

            $api_url = 'https://info.payu.in/merchant/postservice.php?form=2';

            $data = [
                'key' => $merchantKey,
                'command' => 'verify_payment',
                'hash' => $hash,
                'var1' => $orderId
            ];

            $ch = curl_init($api_url);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $gatewayResponse = curl_exec($ch);
            curl_close($ch);
            $gatewayResponse = json_decode($gatewayResponse,true);
           // print_r($gatewayResponse);exit;
            $transactionDetails = $gatewayResponse['transaction_details'][$orderId];

            if($transactionDetails["status"] == "success" && $transactionDetails["unmappedstatus"] == "captured"){
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
                errorExit("Payment verification failed.");
            }
        } else {
            errorExit("Order ID is already used.");
        }
    } else {
        errorExit("Order ID not found.");
    }
} else {
    http_response_code(405);
    die();
}

?>