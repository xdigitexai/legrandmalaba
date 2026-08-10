<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

$secretKey = $methodExtras["key"] ?? null; // Ensure secret key is defined

if (!empty($_GET['payment_code'])) {
    // Fetch payment details from the database
    $payment = $conn->prepare('SELECT * FROM payments INNER JOIN clients ON clients.client_id=payments.client_id WHERE payments.payment_privatecode=:pmprivatecode');
    $payment->execute(['pmprivatecode' => $_GET['payment_code']]);
    $payment = $payment->fetch(PDO::FETCH_ASSOC);

    if ($payment && $payment['payment_status'] == 1) {
        if (is_null($secretKey)) {
            errorExit("Secret key is not defined.");
        }

        $authHash = base64_encode($secretKey . ":");
        $curl = curl_init();
        
        // PayMongo Checkout Session endpoint
        curl_setopt_array($curl, [
            CURLOPT_URL => 'https://api.paymongo.com/v1/checkout_sessions/' . $payment['payment_extra'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
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

       $response = json_decode($response, true);
file_put_contents('paymongo_log.txt', "API Response: " . print_r($response, true), FILE_APPEND);

// Check if payment status is present
if (isset($response['data']['attributes']['payment_status'])) {
    $paymentStatus = $response['data']['attributes']['payment_status'];
    file_put_contents('paymongo_log.txt', "Payment Status: " . $paymentStatus . "\n", FILE_APPEND);
    
    if ($paymentStatus === 'paid') {
        // Proceed with payment processing
    } else {
        errorExit("Payment not completed. Status: " . htmlspecialchars($paymentStatus));
    }
} else {
    errorExit("Payment status not found in response.");
}

        // Check PayMongo payment status
        if (isset($response['data']['attributes']['payment_status']) && 
            $response['data']['attributes']['payment_status'] === 'paid') {
            
            $transactionReference = $payment['payment_extra'];

            if (empty($transactionReference)) {
                errorExit("Missing transaction reference.");
            }

            // Verify payment details
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

                    // Calculate fees and bonuses
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
                    errorExit("Duplicate transaction detected.");
                }
            } else {
                errorExit("Transaction reference not found.");
            }
        } else {
            $status = $response['data']['attributes']['payment_status'] ?? 'unpaid';
            errorExit("Payment not completed. Status: " . htmlspecialchars($status));
        }
    } else {
        errorExit("Invalid payment status or payment not found.");
    }
} else {
    http_response_code(405);
    die();
}
?>