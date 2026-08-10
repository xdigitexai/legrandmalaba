<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$apiKey  = $methodExtras["apiKey"] ?? '';
$gateway = trim($_POST["xdg_gateway"] ?? "mobile"); // "mobile" or "card"

// Local currency units per $1 USD
$rates = [
    'KES' => 130,   // Kenya        — user-specified
    'UGX' => 3800,  // Uganda       — user-specified
    'CDF' => 2500,  // DR Congo     — user-specified
    'NGN' => 1600,  // Nigeria
    'GHS' => 15,    // Ghana
    'TZS' => 2600,  // Tanzania
    'XAF' => 600,   // Central Africa (Cameroon, Gabon, Chad…)
    'XOF' => 600,   // West Africa  (Senegal, Ivory Coast…)
    'RWF' => 1350,  // Rwanda
    'ZMW' => 27,    // Zambia
    'SLE' => 22,    // Sierra Leone
];

$symbols = [
    'KES' => 'KSh',  'UGX' => 'USh',  'CDF' => 'FC',
    'NGN' => '₦',    'GHS' => 'GH₵',  'TZS' => 'TSh',
    'XAF' => 'FCFA', 'XOF' => 'CFA',  'RWF' => 'Frw',
    'ZMW' => 'K',    'SLE' => 'Le',
];

$logDir = PATH . '/logs';
if (!is_dir($logDir)) {
    @mkdir($logDir, 0755, true);
}

function xdpLog(string $msg): void
{
    global $logDir;
    @file_put_contents(
        "$logDir/xdp.log",
        "[" . date('Y-m-d H:i:s') . "] $msg\n",
        FILE_APPEND | LOCK_EX
    );
}

// ────────────────────────────────────────────────────────────
//  MOBILE MONEY  (PawaPay STK push — all African networks)
// ────────────────────────────────────────────────────────────
if ($gateway === 'mobile') {
    $currency = strtoupper(preg_replace('/[^A-Z]/', '', $_POST["xdg_currency"] ?? ''));
    $phone    = '+' . preg_replace('/\D/', '', $_POST["xdg_phone"] ?? '');
    $digits   = substr($phone, 1);

    if (!isset($rates[$currency])) {
        errorExit("Unsupported currency. Please choose one from the list.");
    }
    if (!preg_match('/^\+\d{8,15}$/', $phone)) {
        errorExit("Invalid phone format. Example: +254712345678 (with country code).");
    }

    $rate        = $rates[$currency];
    $localAmount = round($paymentAmount * $rate, 2);
    $xdpRef      = 'XDP-' . strtoupper(bin2hex(random_bytes(6)));

    // Insert pending payment (USD amount stored)
    $insert = $conn->prepare("INSERT INTO payments SET
        client_id=:cid, payment_amount=:amt, payment_method=:mid,
        payment_mode='Automatic', payment_create_date=:date,
        payment_ip=:ip, payment_extra=:extra,
        payment_status=1, payment_delivery=1");
    $insert->execute([
        "cid"   => $user["client_id"],
        "amt"   => $paymentAmount,
        "mid"   => $methodId,
        "date"  => date("Y.m.d H:i:s"),
        "ip"    => GetIP(),
        "extra" => $xdpRef,
    ]);
    $pendingId = $conn->lastInsertId();

    $webhookUrl = rtrim(site_url(''), '/') . '/xdigitex_webhook.php';

    $payload = json_encode([
        "amount"      => $localAmount,
        "currency"    => $currency,
        "phone"       => $phone,
        "gateway"     => "pawapay",
        "webhook_url" => $webhookUrl,
        "description" => "Legrand top-up — " . $user["username"],
    ]);

    $ch = curl_init("https://pay.xdigitex.space/api/payments/initiate");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "X-API-Key: " . $apiKey],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($raw, true) ?? [];

    xdpLog("MOBILE_INIT user={$user['client_id']} phone=$phone {$paymentAmount}USD→{$localAmount}{$currency} http=$httpCode ref=" . ($data['reference'] ?? '') . " raw=" . substr($raw, 0, 400));

    if ($httpCode === 200 && !empty($data['reference'])) {
        $txRef = $data['reference'];
        $conn->prepare("UPDATE payments SET payment_extra=:ref, t_id=:tid WHERE payment_id=:id")
             ->execute(["ref" => $txRef, "tid" => $txRef, "id" => $pendingId]);

        if (strtolower($data['pawa_status'] ?? '') === 'rejected') {
            $conn->prepare("UPDATE payments SET payment_status=3, payment_delivery=2 WHERE payment_id=:id")
                 ->execute(["id" => $pendingId]);
            errorExit("Rejected by mobile network. Verify your number and that Mobile Money is enabled on it.");
        }

        $sym   = $symbols[$currency] ?? $currency;
        $lcFmt = $sym . ' ' . number_format($localAmount, 0);

        $response["success"] = true;
        $response["message"] = "Payment prompt sent!";
        $response["content"] = '
<div class="alert alert-success mt-3">
  <strong>&#128242; Check your phone!</strong><br>
  A <strong>' . htmlspecialchars($currency) . '</strong> prompt for
  <strong>' . htmlspecialchars($lcFmt) . '</strong>
  (= $' . number_format($paymentAmount, 2) . ' USD) was sent to
  <strong>' . htmlspecialchars($phone) . '</strong>.<br>
  Enter your PIN — balance updates automatically once confirmed.
</div>
<div class="alert alert-info d-flex align-items-center justify-content-between flex-wrap gap-2">
  <small><strong>Reference:</strong> ' . htmlspecialchars($txRef) . '</small>
  <button class="btn btn-sm btn-outline-info" id="xdg_check_btn"
    data-ref="' . htmlspecialchars($txRef) . '">&#10003; Check Status</button>
</div>
<div id="xdg_status_msg"></div>
<script>
(function(){
  document.getElementById("xdg_check_btn").addEventListener("click",function(){
    var btn=this, ref=btn.getAttribute("data-ref");
    btn.disabled=true; btn.textContent="Checking…";
    fetch(window.location.pathname,{
      method:"POST",
      headers:{"Content-Type":"application/x-www-form-urlencoded"},
      body:"action=xdgCheck&ref="+encodeURIComponent(ref)
    }).then(function(r){return r.json();}).then(function(d){
      var el=document.getElementById("xdg_status_msg");
      if(d.status==="completed"){
        el.innerHTML=\'<div class="alert alert-success">&#10003; Payment confirmed! Refreshing…</div>\';
        setTimeout(function(){location.reload();},1800);
      }else if(d.status==="failed"){
        el.innerHTML=\'<div class="alert alert-danger">&#10007; Payment failed. Please try again.</div>\';
        btn.disabled=false; btn.textContent="Retry Check";
      }else{
        el.innerHTML=\'<div class="alert alert-warning">&#8987; Still pending. Check again in a moment.</div>\';
        btn.disabled=false; btn.textContent="Check Again";
      }
    }).catch(function(){btn.disabled=false; btn.textContent="Check Again";});
  });
})();
</script>';
    } else {
        $conn->prepare("DELETE FROM payments WHERE payment_id=:id")->execute(["id" => $pendingId]);
        $errMsg = $data['message'] ?? $data['error'] ?? "HTTP $httpCode";
        if (is_array($data) && empty($errMsg)) {
            $errMsg = json_encode($data);
        }
        errorExit("Payment failed: " . $errMsg);
    }

// ────────────────────────────────────────────────────────────
//  CARD / M-Pesa Pesapal  (redirect checkout)
// ────────────────────────────────────────────────────────────
} elseif ($gateway === 'card') {
    $email = trim($_POST["xdg_email"] ?? $user["email"] ?? '');
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        errorExit("A valid email address is required for card payment.");
    }

    $xdpRef      = 'XDP-' . strtoupper(bin2hex(random_bytes(6)));
    $callbackUrl = site_url('payment/xdigitex') . '?ref=' . urlencode($xdpRef);
    $webhookUrl  = rtrim(site_url(''), '/') . '/xdigitex_webhook.php';

    $insert = $conn->prepare("INSERT INTO payments SET
        client_id=:cid, payment_amount=:amt, payment_method=:mid,
        payment_mode='Automatic', payment_create_date=:date,
        payment_ip=:ip, payment_extra=:extra,
        payment_status=1, payment_delivery=1");
    $insert->execute([
        "cid"   => $user["client_id"],
        "amt"   => $paymentAmount,
        "mid"   => $methodId,
        "date"  => date("Y.m.d H:i:s"),
        "ip"    => GetIP(),
        "extra" => $xdpRef,
    ]);
    $pendingId = $conn->lastInsertId();

    $nameParts = explode(' ', trim($user["name"] ?? ''), 2);

    $payload = json_encode([
        "amount"       => $paymentAmount,
        "currency"     => "USD",
        "gateway"      => "card",
        "email"        => $email,
        "first_name"   => $nameParts[0] ?? ($user["username"] ?? ''),
        "last_name"    => $nameParts[1] ?? '',
        "redirect_url" => $callbackUrl,
        "webhook_url"  => $webhookUrl,
        "description"  => "Legrand top-up — " . $user["username"],
    ]);

    $ch = curl_init("https://pay.xdigitex.space/api/payments/initiate");
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "X-API-Key: " . $apiKey],
        CURLOPT_TIMEOUT        => 30,
    ]);
    $raw      = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($raw, true) ?? [];

    xdpLog("CARD_INIT user={$user['client_id']} email=$email amount={$paymentAmount}USD http=$httpCode ref=" . ($data['reference'] ?? 'n/a') . " raw=" . substr($raw, 0, 400));

    if ($httpCode === 200 && !empty($data['reference'])) {
        $txRef      = $data['reference'];
        $redirectTo = $data['redirect_url'] ?? $data['checkout_url'] ?? $data['link'] ?? '';

        $conn->prepare("UPDATE payments SET payment_extra=:ref, t_id=:tid WHERE payment_id=:id")
             ->execute(["ref" => $txRef, "tid" => $txRef, "id" => $pendingId]);

        if (empty($redirectTo)) {
            $conn->prepare("DELETE FROM payments WHERE payment_id=:id")->execute(["id" => $pendingId]);
            errorExit("Gateway returned no checkout URL. Please try again.");
        }

        $response["success"] = true;
        $response["message"] = "Redirecting to secure card payment page…";
        $response["content"] = '<script>window.location.href = "' . htmlspecialchars($redirectTo, ENT_QUOTES) . '";</script>';
    } else {
        $conn->prepare("DELETE FROM payments WHERE payment_id=:id")->execute(["id" => $pendingId]);
        $errMsg = $data['message'] ?? $data['error'] ?? "HTTP $httpCode";
        if (is_array($data) && empty($errMsg)) {
            $errMsg = json_encode($data);
        }
        errorExit("Card payment failed: " . $errMsg);
    }

} else {
    errorExit("Invalid payment gateway selected.");
}
