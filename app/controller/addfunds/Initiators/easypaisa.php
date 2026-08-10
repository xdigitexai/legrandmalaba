<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$email = $methodExtras["email"];
$password = $methodExtras["password"];
$senderEmail = $methodExtras["senderEmail"];
$emailSubject = $methodExtras["emailSubject"];

$EasypaisaTransactionId = htmlspecialchars(trim($_POST["EasypaisaTransactionId"]));

if (empty($EasypaisaTransactionId)) {
    errorExit("The Transaction ID cannot be empty.");
}

if (
    !countRow([
        'table' => 'payments',
        'where' => [
            'payment_extra' => $EasypaisaTransactionId,
            "payment_status" => 3,
            "payment_delivery" => 2
        ]
    ])
) {

    $hostname = '{imap.gmail.com:993/imap/ssl/novalidate-cert}';

    $inbox = imap_open($hostname, $email, $password) or die('Cannot connect to Gmail: ' . imap_last_error());
    $emails = imap_search($inbox, 'TEXT "' . $EasypaisaTransactionId . '"', SE_FREE, "UTF-8");

    $transaction = array();
    foreach ($emails as $email_id) {
        $header = imap_headerinfo($inbox, $email_id);
        $sender = $header->from[0]->mailbox . "@" . $header->from[0]->host;
        $subject = $header->subject;
        $body = imap_fetchbody($inbox, $email_id, 1);
        preg_match("/Rs\s([+-]?([0-9]*[.])?[0-9]+)/m",$body,$amount);
        $transaction["subject"] = $subject;
        $transaction["sender"] = $sender;
        $transaction["amount"] = $amount[1];
        $transaction["tid"] = $EasypaisaTransactionId;
    }
    imap_close($inbox);
    
    if($transaction["tid"] != $EasypaisaTransactionId){
        errorExit("This Transaction ID was not found, please try again later.");
    }
    if(floatval($transaction["amount"]) != floatval($paymentAmount)){
        errorExit("The amount you entered seems to be invalid.");
    }

    if($emailSubject != $transaction["subject"]){
        errorExit("Transaction ID verification failed.");
    }

    if($senderEmail != $transaction["sender"]){
        errorExit("Transaction ID verification failed.");
    }
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
        "extra" => $EasypaisaTransactionId
    ]);

    $paymentId = $conn->lastInsertId();
    
    $paidAmount = floatval($paymentAmount);
        if($paymentFee > 0){
          $fee = ($paidAmount * ($paymentFee / 100));
          $paidAmount = $paidAmount - $fee;
        }
        if($paymentBonusStartAmount != 0 && $paidAmount > $paymentBonusStartAmount){
            $bonus = $paidAmount * ($paymentBonus / 100);
            $paidAmount = $paidAmount + $bonus;
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

        $redirectForm .= '<script type="text/javascript">window.location.rel1oad();</script>';

        $response["success"] = true;
        $response["message"] = "The transaction ID is verified and the money has been added to your account.";
        $response["content"] = $redirectForm;

} else {
    errorExit("This Transaction ID is already used.");
}
?>