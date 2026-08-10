<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

if (
    !in_array($_SERVER['REMOTE_ADDR'], [
        '185.71.65.92',
        '185.71.65.189',
        '149.202.17.210'
    ])
) {
    errorExit("Something went wrong.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $shopId = $methodExtras["shopId"];
    $secretKey = $methodExtras["secretKey"];
    $orderId = htmlspecialchars($_POST["m_orderid"]);
    $receivedSignature = $_POST['m_sign'];
    $paymentStatus = $_POST['m_status'];

    if (empty($orderId)) {
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

            $hashSequence = [
                $_POST['m_operation_id'],
                $_POST['m_operation_ps'],
                $_POST['m_operation_date'],
                $_POST['m_operation_pay_date'],
                $_POST['m_shop'],
                $_POST['m_orderid'],
                $_POST['m_amount'],
                $_POST['m_curr'],
                $_POST['m_desc'],
                $_POST['m_status'],
                $secretKey
            ];

            $generatedSignatureHash = strtoupper(hash('sha256', implode(':', $hashSequence)));

            if ($receivedSignature == $generatedSignatureHash && $paymentStatus == "success") {
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