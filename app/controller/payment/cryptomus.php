<?php
$methodId = 35; // Cryptomus ID
$method = $conn->prepare("SELECT * FROM paymentmethods WHERE methodId = :id");
$method->execute(["id" => $methodId]);
$method = $method->fetch();
$methodExtras = json_decode($method['methodExtras'], true);

$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['order_id']) && ($data['status'] === 'paid' || $data['status'] === 'paid_over')) {
    $sign = $data['sign'];
    unset($data['sign']);
    $expectedSign = md5(base64_encode(json_encode($data)) . $methodExtras['api_key']);

    if ($sign === $expectedSign) {
        $transactionId = $data['order_id'];
        $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE t_id = ? AND payment_status = 1");
        $paymentDetails->execute([$transactionId]);
        $paymentDetails = $paymentDetails->fetch();

        if ($paymentDetails) {
            $user = $conn->prepare("SELECT * FROM clients WHERE client_id = ?");
            $user->execute([$paymentDetails['client_id']]);
            $userData = $user->fetch();

            $conn->prepare("UPDATE payments SET payment_status=3, payment_delivery=2, client_balance=? WHERE t_id=?")
                 ->execute([$userData['balance'] + $paymentDetails['payment_amount'], $transactionId]);

            $conn->prepare("UPDATE clients SET balance = balance + ? WHERE client_id = ?")
                 ->execute([$paymentDetails['payment_amount'], $userData['client_id']]);
            echo "OK";
        }
    }
}