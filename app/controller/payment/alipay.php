<?php
if (!defined('PAYMENT')) {
    http_response_code(404);
    die();
}

require_once("lib/alipay.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $partnerId = $methodExtras["partnerId"];
    $privateKey = $methodExtras["privateKey"];
    $tid = $_POST['out_trade_no'];
    $tno = $_POST['trade_no'];
    $total_amount = $_POST['total_fee'];

    $paymentDetails = $conn->prepare("SELECT * FROM payments WHERE payment_extra=:tid");
    $paymentDetails->execute([
        "tid" => $tid
    ]);

    if ($paymentDetails->rowCount()) {
        $paymentDetails = $paymentDetails->fetch(PDO::FETCH_ASSOC);

        if (
            !countRow([
                'table' => 'payments',
                'where' => [
                    'client_id' => $user['client_id'],
                    'payment_method' => $methodId,
                    'payment_status' => 3,
                    'payment_delivery' => 2,
                    'payment_extra' => $tid
                ]
            ])
        ) {
            $alipay = new Alipay($partnerId, $privateKey);

            try {
                if ($alipay->verifyPayment($_POST) === false)
                {
                    errorExit("Unable to verify payment.");
                    return false;
                }
            } catch (Exception $e) { 
                echo $e->getMessage();
                return false;
            } catch (AlipayException $e) {
                echo $e->getMessage();
                return false;
            }

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
    http_response_code(405);
    die();
}