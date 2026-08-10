<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

if ($_SERVER["REQUEST_METHOD"] == "GET") {
    if ($_GET["action"] == "getData") {
        $clients = $conn->prepare("SELECT client_id, username FROM clients");
        $clients->execute();
        $clients = $clients->fetchAll(PDO::FETCH_ASSOC);
        $clients = array_group_by($clients, "client_id");

        $payments = $conn->prepare("SELECT payment_id, client_id, client_balance, payment_amount, payment_method, payment_status, payment_delivery, payment_note, payment_mode, payment_extra, payment_create_date FROM payments ORDER BY payment_id DESC");
        $payments->execute();
        $payments = $payments->fetchAll(PDO::FETCH_ASSOC);

        $methods = $conn->prepare("SELECT methodId, methodVisibleName FROM paymentmethods");
        $methods->execute();
        $methods = $methods->fetchAll(PDO::FETCH_ASSOC);
        $methods = array_group_by($methods, "methodId");

        $PAYMENTS = [];
        for ($i = 0; $i < count($payments); $i++) {
            if ($payments[$i]["payment_status"] == 1 && $payments[$i]["payment_delivery"] == 1) {
                $paymentStatus = '<span class="badge bg-warning text-dark">Pending</span>';
            } elseif ($payments[$i]["payment_status"] == 3 && $payments[$i]["payment_delivery"] == 2) {
                $paymentStatus = '<span class="badge bg-success">Completed</span>';
            } elseif ($payments[$i]["payment_status"] == 2 && $payments[$i]["payment_delivery"] == 2) {
                $paymentStatus = '<span class="badge bg-danger">Failed</span>';
            } else {
                $paymentStatus = "Pending";
            }

            $PAYMENTS[] = [
                "id" => $payments[$i]["payment_id"],
                "cid" => $payments[$i]["client_id"],
                "username" => $clients[$payments[$i]["client_id"]][0]["username"],
                "method" => $methods[$payments[$i]["payment_method"]][0]["methodVisibleName"],
                "user_balance" => number_format($payments[$i]["client_balance"], 2, '.', ''),
                "amount" => number_format($payments[$i]["payment_amount"], 2, '.', ''),
                "status" => $paymentStatus,
                "mode" => $payments[$i]["payment_mode"],
                "extra" => $payments[$i]["payment_extra"],
                "created_at" => date("m-d-Y h:i A", strtotime($payments[$i]["payment_create_date"]))
            ];
        }

        header("Content-Type: application/json");
        echo json_encode($PAYMENTS);
        exit;
    }

    if ($_GET["action"] == "add_remove_balance") {
        $methods = $conn->prepare("SELECT methodId, methodVisibleName FROM paymentmethods");
        $methods->execute();
        $methods = $methods->fetchAll(PDO::FETCH_ASSOC);

        $select = "";
        for ($i = 0; $i < count($methods); $i++) {
            $select .= '<option value="' . $methods[$i]["methodId"] . '">' . $methods[$i]["methodVisibleName"] . '</option>';
        }

        $form = '<form method="POST" action="admin/fund-add-history/manage-funds">';
        $form .= '<div class="form-group mb-3"><label class="form-label">Username</label>
<input type="text"  name="username" class="form-control" required/></div>';
        $form .= '<div class="form-group mb-3"><label class="form-label">Amount</label>
<input type="number"  name="amount" class="form-control" step="0.01" required /></div>';
        $form .= '<div class="form-group mb-3"><label class="form-label">Method</label><select class="form-select" name="method">' . $select . '</select></div>';
        $form .= '<div class="form-group mb-3"><label class="form-label">Action</label><select class="form-select" name="action"><option value="add">Add</option><option value="deduct">Deduct</option></select></div>';
        $form .= '<div class="form-group mb-3"><label class="form-label">Order ID</label><input type="text"  name="orderId" class="form-control" /></div>';
        $form .= '<div class="custom-modal-footer"><button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>&nbsp;&nbsp;<button type="submit" data-loading-text="Updating..." class="btn btn-primary">Apply</button></div></form>';

        $response = [
            "success" => true,
            "content" => $form
        ];

        header("Content-Type: application/json");
        echo json_encode($response);
        exit;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Fetch settings and client details
    $settings = $conn->prepare("SELECT site_name, smtp_user, site_seo, alert_welcomemail, referral_commision FROM settings LIMIT 1");
    $settings->execute();
    $settings = $settings->fetch(PDO::FETCH_ASSOC);

    $client = $conn->prepare("SELECT client_id, balance, ref_by, email FROM clients WHERE username=:username");
    $client->execute([
        "username" => htmlspecialchars(trim($_POST["username"]))
    ]);
    $client = $client->fetch(PDO::FETCH_ASSOC);

    if ($client && $settings) {
        $username = htmlspecialchars(trim($_POST["username"]));
        $amount = floatval($_POST["amount"]);
        $methodId = intval($_POST["method"]);
        $action = $_POST["action"];
        $orderId = htmlspecialchars($_POST["orderId"]);
        $email = $client['email'];
        $site_name = $settings["site_name"];

        if ($action == "add") {
            $insert = $conn->prepare("INSERT INTO payments SET client_id=:cid, client_balance=:balance, payment_amount=:amount, payment_method=:method, payment_status=:status, payment_delivery=:delivery, payment_mode=:mode, payment_create_date=:date, payment_ip=:ip, payment_extra=:extra, admin=:admin");
            $insert->execute([
                "cid" => $client["client_id"],
                "balance" => $client["balance"],
                "amount" => +$amount,
                "method" => $methodId,
                "status" => 3,
                "delivery" => 2,
                "mode" => "Manual",
                "date" => date("Y-m-d H:i:s"),
                "ip" => GetIP(),
                "extra" => $orderId,
                "admin" => $admin["admin_id"]
            ]);

            $update = $conn->prepare("UPDATE clients SET balance=:balance WHERE client_id=:id");
            $update->execute([
                "id" => $client["client_id"],
                "balance" => $client["balance"] + $amount
            ]);

            // Update referral commission
            if ($client['ref_by']) {
                $referral_commision = floatval($settings['referral_commision']);
                $commision = $amount * $referral_commision / 100;

                $update_referral = $conn->prepare("UPDATE referral SET referral_total_commision = referral_total_commision + :commision WHERE referral_code = :referral_id");
                $update_referral->execute([
                    "commision" => $commision,
                    "referral_id" => $client['ref_by']
                ]);
            }

            if ($settings["alert_welcomemail"] == 2) {
    $htmlContent = "
    <!DOCTYPE html>
    <html lang='en'>

    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Thank You for Adding Funds</title>
        <style>
            body {
                font-family: 'Helvetica Neue', Arial, sans-serif;
                background-color: #f5f7fa;
                margin: 0;
                padding: 0;
            }

            .email-container {
                width: 100%;
                max-width: 600px;
                background-color: #ffffff;
                padding: 20px;
                border-radius: 8px;
                margin: 40px auto;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
                border: 1px solid #e0e0e0;
            }

            .header {
                background-color: #007bff;
                padding: 20px;
                border-radius: 8px 8px 0 0;
                text-align: center;
                color: #ffffff;
            }

            .header h1 {
                margin: 0;
                font-size: 24px;
            }

            .content {
                padding: 30px;
                line-height: 1.6;
                color: #333333;
            }

            .content p {
                margin: 0 0 20px;
                font-size: 16px;
            }

            .content .details {
                background-color: #f1f1f1;
                padding: 15px;
                border-radius: 5px;
                margin-bottom: 20px;
            }

            .content .details p {
                margin: 5px 0;
            }

            .content a {
                color: #007bff;
                text-decoration: none;
            }

            .footer {
                margin-top: 20px;
                text-align: center;
                color: #777777;
                font-size: 13px;
                border-top: 1px solid #e0e0e0;
                padding-top: 15px;
            }
        </style>
    </head>

    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>Thank You for Adding Funds</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>$username</strong>,</p>
                <p>We're pleased to inform you that your funds have been successfully added to your account.</p>
                <div class='details'>
                    <p><strong>Amount:</strong> ₱$amount</p>
                    <p><strong>Method:</strong> Gcash/Maya</p>
                </div>
                <p>Thank you for choosing us! We appreciate your continued support.</p>
                <p>You can sign in to your account to check your balance and manage your funds:</p>
                <p><a href='" . site_url() . "'>$site_name</a></p>
            </div>
            <div class='footer'>
                <p>&copy; 2024 $site_name. All rights reserved.</p>
            </div>
        </div>
    </body>

    </html>
                ";

                $to = $email; 
                $from = $settings["smtp_user"]; 
                $fromName = $settings["site_seo"]; 
                $subject = "$site_name : Payment Added!"; 
                $headers = "MIME-Version: 1.0" . "\r\n"; 
                $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
                $headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
                $headers .= 'Cc: '.$from . "\r\n"; 
                $headers .= 'Bcc: '.$from . "\r\n"; 
                
                // Attempt to send the email
                if(!mail($to, $subject, $htmlContent, $headers)) {
                    error_response_exit("Failed to send the email notification.");
                }
            }

            success_response_exit("Record added, amount added to balance, and commission updated for the referrer.");
        }

        if ($action == "deduct") {
            $insert = $conn->prepare("INSERT INTO payments SET client_id=:cid, client_balance=:balance, payment_amount=:amount, payment_method=:method, payment_status=:status, payment_delivery=:delivery, payment_mode=:mode, payment_create_date=:date, payment_ip=:ip, payment_extra=:extra");
            $insert->execute([
                "cid" => $client["client_id"],
                "balance" => $client["balance"],
                "amount" => -$amount,
                "method" => $methodId,
                "status" => 3,
                "delivery" => 2,
                "mode" => "Manual",
                "date" => date("Y-m-d H:i:s"),
                "ip" => GetIP(),
                "extra" => $orderId
            ]);

            $update = $conn->prepare("UPDATE clients SET balance=:balance WHERE client_id=:id");
            $update->execute([
                "id" => $client["client_id"],
                "balance" => $client["balance"] - $amount
            ]);

            success_response_exit("Record added and amount deducted from balance.");
        }
    } else {
        error_response_exit("Client or settings not found.");
    }
}

require admin_view("fund-add-history");
?>
