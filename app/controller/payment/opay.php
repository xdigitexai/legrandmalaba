<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$merchantId = $methodExtras["merchantId"];
$secretKey = $methodExtras["secretKey"];

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $requestBody = file_get_contents("php://input");
    $requestBody = json_decode($requestBody, 1);
    $orderId = $requestBody["payload"]["reference"];

    if (!$orderId) {
        errorExit("Missing data.");
    }

    $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:orderId");
    $paymentDetails->execute([
        "orderId" => $orderId
    ]);

    if ($paymentDetails->rowCount()) {
        $paymentDetails = $paymentDetails->fetch(PDO::FETCH_ASSOC);
$user = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");

        $user->execute([

            "id" => $paymentDetails["client_id"]
            ]);
            
        $user = $user->fetch(PDO::FETCH_ASSOC);
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

            $url = "https://api.opaycheckout.com/api/v1/international/cashier/status";

            $postData = [
                'country' => 'EG',
                'reference' => $orderId
            ];

            $postData = (string) json_encode($postData, JSON_UNESCAPED_SLASHES);

            $authHeader = hash_hmac('sha512', $postData, $secretKey);

            $headers = [
                'Content-Type:application/json',
                'Authorization:Bearer ' . $authHeader,
                'MerchantId:' . $merchantId
            ];

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
            $response = curl_exec($ch);
            curl_close($ch);

            $response = json_decode($response, 1);

            $paymentStatus = $response["data"]["status"];

            if ($paymentStatus == "SUCCESS") {
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
                errorExit("Payment not completed.");
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