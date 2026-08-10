<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$email = $methodExtras["email"];
$password = $methodExtras["password"];
$PhonePeTransactionId = htmlspecialchars(trim($_POST["PhonePeTransactionId"]));

if (empty($PhonePeTransactionId)) {
    errorExit("The Transaction ID cannot be empty.");
}


if (
    !countRow([
        'table' => 'payments',
        'where' => [
            'payment_extra' => $PhonePeTransactionId,
            "payment_status" => 3,
            "payment_delivery" => 2
        ]
    ])
) {

    $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}';

    $inbox = imap_open($hostname, $email, $password) or die('Cannot connect to Gmail: ' . imap_last_error());
    $emails = imap_search($inbox, 'TEXT "' . $PhonePeTransactionId . '"', SE_FREE, "UTF-8");

    $transaction = array();
    foreach ($emails as $email_id) {
        $header = imap_headerinfo($inbox, $email_id);
        $sender = $header->from[0]->mailbox . "@" . $header->from[0]->host;
        $subject = $header->subject;
        $body = imap_fetchbody($inbox, $email_id, 1);
    
        $amount_expression = '/Received\sfrom[^₹]+₹\s+(.*?)\s+Txn./m';
        $transactionid_expression = '/Txn\.\s+ID\s+:\s+(.*?)\s+Txn/m';
        $status = '/Txn\.\s+status\s+:\s+(.*?)\s+Credited/m';
        $UTR_expression = '/Bank\s+Ref\.\s+No\.\s+:\s+(.*?)\s+Message/m';
        preg_match($amount_expression, $body, $amount);
        preg_match($transactionid_expression, $body, $transaction_id);
        preg_match($status, $body, $txn_status);
        preg_match($UTR_expression, $body, $utr);
        $PAYMENT_AMOUNT = $amount[1];
        $PAYMENT_TXN_ID = $transaction_id[1];
        $PAYMENT_TXN__STATUS = $txn_status[1];
        $PAYMENT_UTR = $utr[1];
        $transaction["sender"] = $sender;
        $transaction["amount"] = $PAYMENT_AMOUNT;
        $transaction["tid"] = $PAYMENT_TXN_ID;
        $transaction["status"] = $PAYMENT_TXN__STATUS;
        $transaction["utr"] = $PAYMENT_UTR;
        imap_close($inbox);
    }
    
    if(empty($transaction)){
        errorExit("Transaction ID not found, please try again later.");
    }
    if(floatval($transaction["amount"]) != floatval($paymentAmount)){
        errorExit("Amount is invalid.");
    }
    if ($transaction["tid"] == $PhonePeTransactionId && floatval($transaction["amount"]) == floatval($paymentAmount) && $transaction["sender"] == "noreply@phonepe.com" && $transaction["status"] == "Successful") {
        $insert = $conn->prepare(
            "INSERT INTO payments SET
        client_id=:client_id,
        payment_amount=:amount,
        payment_method=:method,
        payment_mode=:mode,
        payment_create_date=:date,
        payment_ip=:ip,
        payment_extra=:extra"
        );
        $insert->execute([
            "client_id" => $user["client_id"],
            "amount" => $paymentAmount,
            "method" => $methodId,
            "mode" => "Automatic",
            "date" => date("Y.m.d H:i:s"),
            "ip" => GetIP(),
            "extra" => $PhonePeTransactionId
        ]);

        $paymentId = $conn->lastInsertId();

        $paidAmount = floatval($paymentAmount);
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
        payment_amount=:payment_amount, 
        payment_status=:status, 
        payment_delivery=:delivery WHERE payment_id=:id');
        $update->execute(
            [
                'balance' => $user['balance'],
                "payment_amount" => $paidAmount,
                'status' => 3,
                'delivery' => 2,
                'id' => $paymentId
            ]
        );
        $updateBalance = $conn->prepare("UPDATE clients SET balance=:balance WHERE client_id=:id");
        $updateBalance->execute([
            "balance" => $user["balance"] + $paidAmount,
            "id" => $user["client_id"]
        ]);

        $redirectForm .= '<script type="text/javascript">window.location.reload();</script>';

        $response["success"] = true;
        $response["message"] = "The transaction ID is verified and the money has been added to your account.";
        $response["content"] = $redirectForm;
    } else {
        errorExit("Transaction ID verification failed, please try again later.");
    }

} else {
    errorExit("This Transaction ID is already used.");
}
