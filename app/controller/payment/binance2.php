<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

// Configuration from method extras
$methodExtras = json_decode($method['extras'], true);
$gmailUsername = $methodExtras['gmail_user'] ?? getenv('GMAIL_USER');
$gmailPassword = $methodExtras['gmail_pass'] ?? getenv('GMAIL_PASS');
$binanceWallet = $methodExtras['wallet_address'] ?? '';
$paymentExpiryHours = 2; // Payment validity duration

if (!empty($_GET['payment_code'])) {
    try {
        // Validate payment code
        $payment = $conn->prepare('SELECT p.*, c.email, c.balance 
            FROM payments p
            JOIN clients c ON p.client_id = c.client_id
            WHERE p.payment_privatecode = :code
            AND p.payment_method = :method');
        $payment->execute([
            'code' => $_GET['payment_code'],
            'method' => $methodId
        ]);
        
        if ($payment->rowCount() === 0) {
            throw new Exception("Invalid payment code");
        }

        $paymentData = $payment->fetch(PDO::FETCH_ASSOC);
        $paymentExtra = json_decode($paymentData['payment_extra'], true);

        // Check payment status
        if ($paymentData['payment_status'] == 3) {
            header("Location: " . site_url("addfunds"));
            exit;
        }

        // Verify payment expiration
        $paymentDate = new DateTime($paymentData['payment_create_date']);
        $now = new DateTime();
        if ($paymentDate->diff($now)->h >= $paymentExpiryHours) {
            $conn->prepare('UPDATE payments SET payment_status = 4 WHERE payment_id = :id')
                ->execute(['id' => $paymentData['payment_id']]);
            throw new Exception("Payment session expired");
        }

        // IMAP connection with error handling
        $imap = @imap_open("{imap.gmail.com:993/imap/ssl}INBOX", $gmailUsername, $gmailPassword);
        if (!$imap) {
            throw new Exception("Email connection failed: " . imap_last_error());
        }

        // Search criteria
        $sinceDate = date('d-M-Y', strtotime($paymentData['payment_create_date']));
        $search = 'FROM "' . $binanceEmail . '" SINCE "' . $sinceDate . '" UNSEEN';
        $emails = imap_search($imap, $search);

        $paymentVerified = false;
        $expectedMemo = $paymentExtra['memo'] ?? '';
        $expectedAmount = (float)$paymentData['payment_amount'];

        if ($emails) {
            foreach ($emails as $emailId) {
                $body = quoted_printable_decode(imap_fetchbody($imap, $emailId, 1));
                
                // Enhanced verification pattern
                $pattern = '/Amount:\s+([\d\.]+)\s+USDT.*?Memo:\s+([^\s]+).*?Wallet:\s+([^\s]+)/is';
                if (preg_match($pattern, $body, $matches)) {
                    $receivedAmount = (float)$matches[1];
                    $receivedMemo = trim($matches[2]);
                    $receivedWallet = trim($matches[3]);

                    // Verify all components
                    if ($receivedAmount >= $expectedAmount &&
                        hash_equals($expectedMemo, $receivedMemo) &&
                        hash_equals($binanceWallet, $receivedWallet)) {
                        
                        $paymentVerified = true;
                        imap_setflag_full($imap, $emailId, "\\Seen");
                        break;
                    }
                }
            }
        }

        imap_close($imap);

        if (!$paymentVerified) {
            throw new Exception("Payment verification failed. Check amount, memo, and wallet address.");
        }

        // Calculate final amount with transaction
        $finalAmount = $receivedAmount;
        if ($paymentFee > 0) {
            $finalAmount -= ($finalAmount * ($paymentFee / 100));
        }
        if ($paymentBonus > 0 && $finalAmount >= $paymentBonusStartAmount) {
            $finalAmount += ($finalAmount * ($paymentBonus / 100));
        }

        // Database transaction
        $conn->beginTransaction();
        
        try {
            // Update payment
            $updatePayment = $conn->prepare('UPDATE payments SET 
                payment_status = 3, 
                payment_delivery = 2, 
                payment_update_date = NOW() 
                WHERE payment_id = :id');
            $updatePayment->execute(['id' => $paymentData['payment_id']]);

            // Update balance
            $updateBalance = $conn->prepare('UPDATE clients SET 
                balance = balance + :amount 
                WHERE client_id = :id');
            $updateBalance->execute([
                'amount' => $finalAmount,
                'id' => $paymentData['client_id']
            ]);

            $conn->commit();

            // Send confirmation
            sendPaymentConfirmationEmail(
                $paymentData['email'],
                $receivedAmount,
                $finalAmount,
                $paymentData['payment_id']
            );

            header("Location: " . site_url("addfunds"));
            exit;

        } catch (PDOException $e) {
            $conn->rollBack();
            throw new Exception("Database error: " . $e->getMessage());
        }

    } catch (Exception $e) {
        errorExit($e->getMessage(), $paymentData['payment_id'] ?? null);
    }
} else {
    http_response_code(400);
    die('Invalid request');
}

function sendPaymentConfirmationEmail($to, $originalAmount, $finalAmount, $paymentId) {
    $subject = "Payment Confirmation #$paymentId - " . site_url("");
    $cleanAmount = number_format($finalAmount, 2);
    $cleanOriginal = number_format($originalAmount, 6);
    
    $message = <<<HTML
    <div style="font-family: Arial, sans-serif;">
        <h2>Payment Confirmation #$paymentId</h2>
        <p>We've successfully processed your payment:</p>
        <table>
            <tr><td>Received Amount:</td><td><strong>$cleanOriginal USDT</strong></td></tr>
            <tr><td>Credited Amount:</td><td><strong>$$cleanAmount USD</strong></td></tr>
            <tr><td>Transaction Date:</td><td><strong>{date('Y-m-d H:i')}</strong></td></tr>
        </table>
        <p>Your new balance is available immediately.</p>
    </div>
    HTML;

    $headers = [
        'MIME-Version: 1.0',
        'Content-type: text/html; charset=utf-8',
        'From: Payments <noreply@' . $_SERVER['HTTP_HOST'] . '>',
        'X-Mailer: PHP/' . phpversion()
    ];

    mail($to, $subject, $message, implode("\r\n", $headers));
}

function errorExit($message, $paymentId = null) {
    $logMessage = date('[Y-m-d H:i:s] ') . ($paymentId ? "[$paymentId] " : "") . $message;
    file_put_contents('payment_errors.log', $logMessage . PHP_EOL, FILE_APPEND);
    die($message);
}
?>