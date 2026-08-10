<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$email       = $methodExtras["email"];
$password    = $methodExtras["password"];
$senderEmail = "service@nayapay.com";
$userTxLast11 = trim($_POST["JazzcashTransactionId"]);

if (!preg_match('/^\d{11}$/', $userTxLast12)) {
    errorExit("Enter valid  12 digits of Transaction ID.");
}

if (countRow([
    'table' => 'payments',
    'where' => [
        'payment_extra'    => $userTxLast12,
        'payment_status'   => 3,
        'payment_delivery' => 2
    ]
])) {
    errorExit("This Transaction ID is already used.");
}

$inbox = imap_open('{imap.gmail.com:993/imap/ssl/novalidate-cert}', $email, $password);
if (!$inbox) {
    errorExit("Email connection failed.");
}

$emails = imap_search($inbox, 'ALL');

$transaction = [];

if ($emails) {
    rsort($emails); 

    foreach ($emails as $email_id) {

        $header = imap_headerinfo($inbox, $email_id);
        $sender = strtolower(
            $header->from[0]->mailbox . '@' . $header->from[0]->host
        );

        if ($sender !== $senderEmail) {
            continue;
        }


        $body = imap_fetchbody($inbox, $email_id, "1.1");
        if (empty($body)) $body = imap_fetchbody($inbox, $email_id, "1");
        if (empty($body)) $body = imap_fetchbody($inbox, $email_id, "2");

        if (empty($body)) continue;


        $decoded = quoted_printable_decode($body);
        $text    = strtolower(strip_tags($decoded));
        $text    = preg_replace('/\s+/', ' ', $text);



        if (strpos($text, $userTxLast12) === false) {
            continue;
        }



        $amount = 0;

 
        $patterns = [
            '/amount received\s*rs\.?\s*([0-9]+)/i',
            '/total amount\s*rs\.?\s*([0-9]+)/i',
            '/rs\.?\s*([0-9]+)\b/i',
            '/pkr\s*([0-9]+)/i'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $text, $match)) {
                $amount = floatval($match[1]);
                break;
            }
        }

        if ($amount <= 0) continue;

        $transaction = [
            "amount" => $amount,
            "sender" => $sender
        ];
        break;
    }
}

imap_close($inbox);



if (empty($transaction)) {
    errorExit("Transaction not found or email not readable.");
}

if (floatval($transaction["amount"]) != floatval($paymentAmount)) {
    errorExit("Amount mismatch. Email shows Rs. {$transaction['amount']}.");
}



$insert = $conn->prepare("
    INSERT INTO payments SET
        client_id=:client_id,
        payment_amount=:amount,
        payment_method=:method,
        payment_mode='Automatic',
        payment_create_date=:date,
        payment_ip=:ip,
        payment_extra=:extra
");

$insert->execute([
    "client_id" => $user["client_id"],
    "amount"    => $paymentAmount,
    "method"    => $methodId,
    "date"      => date("Y-m-d H:i:s"),
    "ip"        => GetIP(),
    "extra"     => $userTxLast12
]);

$paymentId = $conn->lastInsertId();


$paidAmount = floatval($paymentAmount);

$update = $conn->prepare("
    UPDATE payments SET
        client_balance=:balance,
        payment_amount=:payment_amount,
        payment_status=3,
        payment_delivery=2
    WHERE payment_id=:id
");

$update->execute([
    "balance"         => $user["balance"],
    "payment_amount" => $paidAmount,
    "id"              => $paymentId
]);


$conn->prepare("
    UPDATE clients SET balance=balance+:amount WHERE client_id=:id
")->execute([
    "amount" => $paidAmount,
    "id"     => $user["client_id"]
]);

$response["success"] = true;
$response["message"] = "Payment verified successfully and balance added.....
Please refresh the page.";