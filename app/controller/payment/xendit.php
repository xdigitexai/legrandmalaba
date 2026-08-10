<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$secretKey = $methodExtras["key"];

if (!empty($_GET['payment_code'])) {
    // Fetch payment details from the database
    $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_privatecode=:pmprivatecode');
    $payment->execute(['pmprivatecode' => $_GET['payment_code']]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);

    if (!$payment) {
        errorExit("Payment not found.");
    }

    // Redirect if payment is already completed
    if ($payment['payment_status'] == 3) {
        header("Location: " . site_url("addfunds"));
        exit;
    }

    // Proceed only if payment is pending
    if ($payment['payment_status'] != 1) {
        errorExit("Invalid payment status.");
    }

    $authHash = base64_encode($secretKey . ":");
    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.xendit.co/v2/invoices/' . $payment['payment_extra'],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        CURLOPT_HTTPHEADER => array(
            'Authorization: Basic ' . $authHash
        ),
    ));

    $response = curl_exec($curl);
    if ($response === false) {
        errorExit("Curl error: " . curl_error($curl));
    }
    curl_close($curl);

    $response = json_decode($response, true);
    file_put_contents('payment_log.txt', print_r($response, true), FILE_APPEND);

    // Check for API errors
    if (isset($response['error_code'])) {
        errorExit("API Error: " . $response['message']);
    }

    // Check if the status field is present
    if (!isset($response['status'])) {
        errorExit("Invalid API response: Status field missing.");
    }

    // Handle payment status
    if ($response['status'] == 'PAID' || $response['status'] == 'SETTLED') {
        $transactionReference = $payment['payment_extra'];

        if (empty($transactionReference)) {
            errorExit("Missing transaction reference.");
        }

        $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:transactionReference");
        $paymentDetails->execute(['transactionReference' => $transactionReference]);

        if ($paymentDetails->rowCount()) {
            $paymentDetails = $paymentDetails->fetch(PDO::FETCH_ASSOC);

            // Check for duplicate transaction
            if (!countRow([
                'table' => 'payments',
                'where' => [
                    'client_id' => $user['client_id'],
                    'payment_method' => $methodId,
                    'payment_status' => 3,
                    'payment_delivery' => 2,
                    'payment_extra' => $transactionReference
                ]
            ])) {
                $paidAmount = floatval($paymentDetails["payment_amount"]);

                if ($paymentFee > 0) {
                    $fee = ($paidAmount * ($paymentFee / 100));
                    $paidAmount -= $fee;
                }
                if ($paymentBonusStartAmount != 0 && $paidAmount > $paymentBonusStartAmount) {
                    $bonus = $paidAmount * ($paymentBonus / 100);
                    $paidAmount += $bonus;
                }

                // Update payment status
                $update = $conn->prepare('UPDATE payments SET 
                    client_balance=:balance,
                    payment_status=:status, 
                    payment_delivery=:delivery WHERE payment_id=:id');

                if (!$update->execute([
                    'balance' => $user["balance"],
                    'status' => 3,
                    'delivery' => 2,
                    'id' => $paymentDetails['payment_id']
                ])) {
                    errorExit("Failed to update payment record: " . implode(", ", $update->errorInfo()));
                }

                // Update client balance
                $balance = $conn->prepare('UPDATE clients SET balance=:balance WHERE client_id=:id');
                if (!$balance->execute([
                    "balance" => $user["balance"] + $paidAmount,
                    "id" => $user["client_id"]
                ])) {
                    errorExit("Failed to update client balance: " . implode(", ", $balance->errorInfo()));
                }

                header("Location: " . site_url("addfunds"));
                exit;
            } else {
                errorExit("Fraudulent activity detected.");
            }
        } else {
            errorExit("Order ID not found.");
        }
    } else {
        errorExit("Payment not successful. Status: " . ($response['status'] ?? 'Unknown'));
    }
} else {
    http_response_code(405);
    die();
}