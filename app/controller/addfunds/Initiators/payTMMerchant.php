<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}
header("Pragma: no-cache");
header("Cache-Control: no-cache");
header("Expires: 0");

require_once("lib/encdec_paytm.php");

$merchantId = $methodExtras["merchantId"];
$payTMOrderId = htmlspecialchars(trim($_POST["payTMOrderId"]));

if (empty($payTMOrderId)) {
    errorExit("The Order ID cannot be empty.");
}

if (
    !countRow([
        'table' => 'payments',
        'where' => [
            'payment_extra' => $payTMOrderId
        ]
    ])
) {
    $requestParamList = [
        "MID" => $merchantId,
        "ORDERID" => $payTMOrderId

    ];

    $responseParamList = getTxnStatusNew($requestParamList);

    
    if ($responseParamList["STATUS"] == "TXN_SUCCESS") {
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
            "extra" => $payTMOrderId
        ]);

        $paymentId = $conn->lastInsertId();
        $paidAmount = floatval($responseParamList["TXNAMOUNT"]);

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
        $response["message"] = "The order ID is verified and the money has been added to your account.";
        $response["content"] = $redirectForm;
    } elseif ($responseParamList["STATUS"] == "TXN_FAILURE") {
        errorExit($responseParamList["RESPMSG"]);
    } else {

        errorExit("Order ID verification failed, please try again later.");
    }
} else {
    errorExit("This Order ID is already used.");
}
