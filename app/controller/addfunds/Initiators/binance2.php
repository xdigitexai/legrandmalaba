<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

/* Binance Payment with Gmail Verification */
$gmailUser  = getenv('GMAIL_USER'); // Use environment variables for sensitive data
$gmailPass = getenv('GMAIL_PASS');
$binanceEmail = 'noreply@binance.com';

$paymentCode = md5(uniqid() . time());
$callbackURL = site_url("payment/" . $methodCallback);
$binanceWallet = $methodExtras["wallet_address"]; // Merchant Binance wallet address
$cryptoCurrency = 'USDT'; // Supported cryptocurrency

// Generate unique payment ID and amount
$paymentAmountCrypto = $paymentAmount; // Assuming 1:1 conversion for example
$memo = "PAY-" . time() . "-" . substr(md5($user['email']), 0, 6);

// Insert payment record first
$insert = $conn->prepare("INSERT INTO payments SET 
    client_id=:c_id, 
    payment_amount=:amount,
    payment_privatecode=:code,
    payment_method=:method,
    payment_mode=:mode,
    payment_create_date=:date,
    payment_ip=:ip,
    payment_extra=:extra");

$insert->execute([
    "c_id" => $user['client_id'],
    "amount" => $paymentAmount,
    "code" => $paymentCode,
    "method" => $methodId,
    "mode" => "Binance Pay",
    "date" => date("Y.m.d H:i:s"),
    "ip" => GetIP(),
    "extra" => json_encode([
        'wallet' => $binanceWallet,
        'memo' => $memo,
        'crypto' => $cryptoCurrency,
        'amount_crypto' => $paymentAmountCrypto
    ])
]);

// Create payment verification URL
$verificationURL = $callbackURL . '?payment_code=' . $paymentCode;

// HTML template for Binance payment instructions
$paymentInstructions = '
<div class="binance-payment-box">
    <h3>Binance Payment Instructions</h3>
    <p>Send <strong>'.$paymentAmountCrypto.' '.$cryptoCurrency.'</strong> to:</p>
    <div class="wallet-address">
        <input type="text" value="'.$binanceWallet.'" id="binanceWallet" readonly>
        <button onclick="copyWalletAddress()">Copy Address</button>
    </div>
    <p class="memo">MEMO: <strong>'.$memo.'</strong></p>
    <p>⚠️ You MUST include the MEMO in your transaction!</p>
    
    <div class="verification-section">
        <p>After sending payment, click below to verify:</p>
        <a href="'.$verificationURL.'" class="verify-button">Verify Payment Now</a>
        <p class="auto-check">Auto-checking payment status... <span id="countdown">300</span>s</p>
    </div>
</div>

<script>
let seconds = 300;
const countdownElement = document.getElementById("countdown");

function updateCountdown() {
    seconds--;
    countdownElement.textContent = seconds;
    if(seconds <= 0) {
        window.location.reload();
    }
}

// Auto-refresh every 30 seconds
setInterval(updateCountdown, 1000);
setTimeout(() => { window.location.href = "'.$verificationURL.'"; }, 300000);

function copyWalletAddress() {
    const copyText = document.getElementById("binanceWallet");
    copyText.select();
    document.execCommand("copy");
    alert("Wallet address copied to clipboard!");
}
</script>
';

// Store verification data in session
$_SESSION['binance_payment_'.$paymentCode] = [
    'wallet' => $binanceWallet,
    'amount' => $paymentAmountCrypto,
    'memo' => $memo,
    'expiry' => time() + 3600 // 1 hour expiry
];

$response["success"] = true;
$response["message"] = "Binance payment instructions generated";
$response["content"] = $paymentInstructions;
?>