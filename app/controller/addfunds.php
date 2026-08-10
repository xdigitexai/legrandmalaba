<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}
define("ADDFUNDS", TRUE);
$title = ($title ?? '') . " Add Funds";

if ($_SESSION["msmbilisim_userlogin"] != 1 || $user["client_type"] == 1) {
    header("Location:" . site_url('logout'));
}
if ($settings["email_confirmation"] == 1 && $user["email_type"] == 1) {
    header("Location:" . site_url('confirm_email'));
}

// Redirect GET requests to the standalone add-funds page
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    header("Location: /xdp-addfunds.php");
    exit;
}

if (false && $_SERVER["REQUEST_METHOD"] == "GET") {
    $paymentMethods = $conn->prepare("SELECT * FROM paymentmethods WHERE methodStatus=:status ORDER BY methodPosition ASC");
    $paymentMethods->execute(["status" => 1]);

    $methodsList = array();

    if ($paymentMethods->rowCount()) {
        $paymentMethods = $paymentMethods->fetchAll(PDO::FETCH_ASSOC);
        for ($i = 0; $i < count($paymentMethods); $i++) {
            $methodsList[] = [
                "id" => $paymentMethods[$i]["methodId"],
                "name" => $paymentMethods[$i]["methodVisibleName"],
                "instructions" => trim(htmlspecialchars_decode($paymentMethods[$i]["methodInstructions"])),
                "fee" => $paymentMethods[$i]["methodFee"]
            ];
            $paymentMethodsJSON = json_encode(array_group_by($methodsList, "id"), 1);
        }
    } else {
        $methodsList[] = [
            "id" => 0,
            "name" => "No payment gateway activated"
        ];
    }



    $methodNames = $conn->prepare("SELECT methodId,methodVisibleName FROM paymentmethods");
    $methodNames->execute();
    $methodNames = $methodNames->fetchAll(PDO::FETCH_ASSOC);
    $methodNames = array_group_by($methodNames, "methodId");


    $transactions = $conn->prepare("SELECT payment_id,payment_create_date,payment_method,payment_amount FROM payments WHERE payment_status=:status && payment_delivery=:delivery && client_id=:id ORDER BY payment_id DESC");
    $transactions->execute([
        "status" => 3,
        "delivery" => 2,
        "id" => $user["client_id"]
    ]);
    $transactions = $transactions->fetchAll(PDO::FETCH_ASSOC);

    $paymentHistory = [];
    for ($i = 0; $i < count($transactions); $i++) {
        $paymentHistory[] = [
            "id" => $transactions[$i]["payment_id"],
            "date" => $transactions[$i]["payment_create_date"],
            "name" => $methodNames[$transactions[$i]["payment_method"]][0]["methodVisibleName"],
            "amount" => format_amount_string($user["currency_type"], from_to($currencies_array, $settings["site_base_currency"], $user["currency_type"], $transactions[$i]["payment_amount"]))
        ];
    }
}
// ── xdigitex AJAX status check ───────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_POST["action"] ?? '') == "xdgCheck") {
    $ref = trim($_POST["ref"] ?? '');
    $result = ["status" => "error"];
    if ($ref && isset($user["client_id"])) {
        // 1. Check local DB first
        $stmt = $conn->prepare(
            "SELECT payment_status FROM payments
             WHERE payment_extra = :ref AND client_id = :cid LIMIT 1"
        );
        $stmt->execute(["ref" => $ref, "cid" => $user["client_id"]]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            if ($row["payment_status"] == 3) {
                $result = ["status" => "completed"];
            } else {
                // 2. Poll xdigitex live
                $methodRow = $conn->prepare("SELECT methodExtras FROM paymentmethods WHERE methodId=200 LIMIT 1");
                $methodRow->execute();
                $me = json_decode($methodRow->fetchColumn() ?: '{}', true);
                $ak = $me["apiKey"] ?? '';
                if ($ak) {
                    $ch = curl_init("https://pay.xdigitex.space/api/payments/" . urlencode($ref) . "/status");
                    curl_setopt_array($ch, [
                        CURLOPT_RETURNTRANSFER => true,
                        CURLOPT_HTTPHEADER     => ["X-API-Key: $ak"],
                        CURLOPT_TIMEOUT        => 10
                    ]);
                    $gwRaw  = curl_exec($ch);
                    curl_close($ch);
                    $gwData = json_decode($gwRaw, true) ?? [];
                    $result = ["status" => $gwData["status"] ?? "pending"];
                } else {
                    $result = ["status" => "pending"];
                }
            }
        } else {
            $result = ["status" => "not_found"];
        }
    }
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($result);
    exit;
}
// ─────────────────────────────────────────────────────────────────────
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["action"] == "getForm") {
    $formData = $formData ?? "";
    $selectedMethod = $_POST["selectedMethod"];
    include("addfunds/getForm.php");
    $response = [];
    $response["success"] = true;
    $response["content"] = $formData;
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($response, true);
    exit;
}
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $methodId = intval($_POST["payment_type"] ?: 0);

    $method = $conn->prepare("SELECT * FROM paymentmethods WHERE methodId=:id AND methodStatus=:status");
    $method->execute([
        "id" => $methodId,
        "status" => 1
    ]);
    if ($method->rowCount()) {
        $method = $method->fetch(PDO::FETCH_ASSOC);
        $methodId = $method["methodId"];
        $methodMin = number_format($method["methodMin"], 2, '.', '');
        $methodMax = number_format($method["methodMax"], 2, '.', '');
        $methodCurrency = $method["methodCurrency"];
        $methodCurrencySymbol = $currencies_array[$methodCurrency][0]["currency_symbol"] ?: $methodCurrency;
        $methodCallback = $method["methodCallback"];
        $methodExtras = json_decode($method["methodExtras"], 1);
        $paymentFee = $method["methodFee"];
        $paymentBonus = $method["methodBonusPercentage"];
        $paymentBonusStartAmount = $method["methodBonusStartAmount"];

        $paymentAmount = floatval($_POST["payment_amount"] ?: 0);
        if ($paymentFee > 0) {
            $fee = ($paymentAmount * ($paymentFee / 100));
            $paymentAmount += $fee;
        }
        $response = [];

        if ($paymentAmount < $methodMin) {
            errorExit("Minimum amount : $methodCurrencySymbol $methodMin");
        }
        if ($paymentAmount > $methodMax) {
            errorExit("Maximum amount : $methodCurrencySymbol $methodMax");
        }
        if ($method["methodId"] == 1) {
            require("addfunds/Initiators/payTMCheckout.php");
        }
        if ($method["methodId"] == 2) {
            require("addfunds/Initiators/payTMMerchant.php");
        }
        if ($method["methodId"] == 3) {
            require("addfunds/Initiators/perfectMoney.php");
        }
        if ($method["methodId"] == 4) {
            require("addfunds/Initiators/coinbaseCommerce.php");
        }
        if ($method["methodId"] == 5) {
            require("addfunds/Initiators/kashier.php");
        }
        if ($method["methodId"] == 6) {
            require("addfunds/Initiators/razorPay.php");
        }
        if ($method["methodId"] == 7) {
            require("addfunds/Initiators/phonepe.php");
        }
        if ($method["methodId"] == 8) {
            require("addfunds/Initiators/easypaisa.php");
        }
        if ($method["methodId"] == 9) {
            require("addfunds/Initiators/jazzcash.php");
        }
        if ($method["methodId"] == 10) {
            require("addfunds/Initiators/instamojo.php");
        }
        if ($method["methodId"] == 11) {
            require("addfunds/Initiators/cashmaal.php");
        }
        if ($method["methodId"] == 12) {
            require("addfunds/Initiators/alipay.php");
        }
        if ($method["methodId"] == 13) {
            require("addfunds/Initiators/payU.php");
        }
        if ($method["methodId"] == 14) {
            require("addfunds/Initiators/upiapi.php");
        }
        if ($method["methodId"] == 15) {
            require("addfunds/Initiators/opay.php");
        }
        if ($method["methodId"] == 16) {
            require("addfunds/Initiators/flutterwave.php");
        }
        if ($method["methodId"] == 17) {
            require("addfunds/Initiators/stripe.php");
        }
         if ($method["methodId"] == 33) {
            require("addfunds/Initiators/easypaisa-to-naypay.php");
        }
        if ($method["methodId"] == 34) {
            require("addfunds/Initiators/jazzcash-to-naypay.php");
        }
        if ($method["methodId"] == 35) {
            require("addfunds/Initiators/cryptomus.php");
        }
        if ($method["methodId"] == 18) {
            require("addfunds/Initiators/payeer.php");
        }
        
            if ($method["methodId"] == 110) {
            require("addfunds/Initiators/xendit.php");
        }
         if ($method["methodId"] == 82) {
            require("addfunds/Initiators/assanpay.php");
        }
         if ($method["methodId"] == 111) {
            require("addfunds/Initiators/paymongo.php");
        }
        if ($method["methodId"] == 112) {
            require("addfunds/Initiators/binance.php");
        }
        if ($method["methodId"] == 113) {
            require("addfunds/Initiators/binance2.php");
        }
        if ($method["methodId"] == 114) {
            require("addfunds/Initiators/binancepay.php");
        }
        if ($method["methodId"] == 200) {
            require("addfunds/Initiators/xdigitex.php");
        }
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode($response, true);
        exit;
    } else {
        errorExit("Select a valid payment method.");
    }
}
?>