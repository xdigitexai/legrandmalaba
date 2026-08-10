<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    require_once("lib/encdec_paytm.php");
    $merchantId = $methodExtras["merchantId"];
    $merchantKey = $methodExtras["merchantKey"];
    $paytmChecksum = "";
    $paramList = [];
    $isValidChecksum = "FALSE";
    $paramList = $_POST;
    $orderId = $_POST["ORDERID"];
    $paymentStatus = $_POST["STATUS"];


    $paytmChecksum = isset($_POST["CHECKSUMHASH"]) ? $_POST["CHECKSUMHASH"] : "";
    if (empty($orderId) && empty($paytmChecksum)) {
        errorExit("Invalid Data.");
    }

    $isValidChecksum = verifychecksum_e($paramList, $merchantKey, $paytmChecksum);

    if ($isValidChecksum == "TRUE" && $paymentStatus == "TXN_SUCCESS") {
        $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:order_id");
        $paymentDetails->execute([
            "payment_extra" => $orderId
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
                
                errorExit("Order ID is already used.");
            }
        } else {

            errorExit("Order ID not found.");
        }
    } else {
        errorExit("Checksum mismatched.");
    }
} else {
    http_response_code(405);
    die();
}
