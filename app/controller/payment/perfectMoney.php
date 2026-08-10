<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $payeeAccount = $methodExtras["accountNumber"];
    $alternatePassPhrase = $methodExtras["alternatePassPhrase"];

    if (isset($_POST['PAYMENT_BATCH_NUM'])) {
        $orderId = $_POST['PAYMENT_ID'];

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
            $v2_hash = false;
            $alternatePassPhrase = strtoupper(md5($alternatePassPhrase));
            $hashSequence = $_POST['PAYMENT_ID'] . ':' . $_POST['PAYEE_ACCOUNT'] . ':' . $_POST['PAYMENT_AMOUNT'] . ':' . $_POST['PAYMENT_UNITS'] . ':' . $_POST['PAYMENT_BATCH_NUM'] . ':' . $_POST['PAYER_ACCOUNT'] . ':' . $alternatePassPhrase . ':' . $_POST['TIMESTAMPGMT'];

            $createdHash = strtoupper(md5($hashSequence));

            $v2_hash = ($createdHash == $_POST['V2_HASH']) ? true : false;

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

                if ($paymentDetails["payment_amount"] == $_POST['PAYMENT_AMOUNT'] && $v2_hash) {
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
        errorExit("Invalid data.");
    }
} else {
    http_response_code(405);
    die();
}