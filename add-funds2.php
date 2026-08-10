<?php
ini_set('display_errors', 0);
require_once "config/auth.php";
require_once "config/database.php";

$userId = (int)$_SESSION['user_id'];

$client    = $db->prepare("SELECT id, name, email, phone, balance FROM users WHERE id = ? LIMIT 1");
$client->execute([$userId]);
$client    = $client->fetch(PDO::FETCH_ASSOC);

$userBalance = (float)($client['balance'] ?? 0);
$userEmail   = $client['email']   ?? '';
$userName    = $client['name']    ?? '';
$nameParts   = explode(' ', trim($userName), 2);
$userFirst   = $nameParts[0] ?? '';
$userLast    = $nameParts[1] ?? '';
$userPhone   = $client['phone']   ?? '';

$apiKey  = 'pg_L6P7S05sXNANM2rxVL5qdWRbik2OtqSH';
$siteUrl = 'https://cdvrsbooster.site';

// Fetch recent deposit history
$histStmt = $db->prepare(
    "SELECT id, amount, method, status, checkout_id, api_response, reference, created_at
     FROM deposits WHERE user_id = ? ORDER BY id DESC LIMIT 20"
);
$histStmt->execute([$userId]);
$history = $histStmt->fetchAll(PDO::FETCH_ASSOC);

// KES rate
$kesRow  = $db->query("SELECT currency_rate FROM currencies WHERE currency_code='KES' LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$kesRate = (float)($kesRow['currency_rate'] ?? 130);

$defaultRates = [
    'KES'=>1,'UGX'=>1,'CDF'=>1,'XAF'=>1,'XOF'=>1,
    'RWF'=>1,'ZMW'=>1,'SLE'=>1,'TZS'=>1,'GHS'=>1,'NGN'=>1,
];

$currencyByCode = [
    '254' => 'KES',
];

function detectCurrency(string $phone, array $cmap): array {
    $d = preg_replace('/\D/', '', $phone);
    foreach ([3, 2] as $l) {
        $p = substr($d, 0, $l);
        if (isset($cmap[$p])) return [$p, $cmap[$p]];
    }
    return ['', ''];
}

function getIP(): string {
    return $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

// Check pending card payment in session
$sessionCardRef     = '';
$sessionCardPending = false;
if (!empty($_SESSION['xdp_card_ref'])) {
    $sessionCardRef = $_SESSION['xdp_card_ref'];
    $chk = $db->prepare("SELECT status FROM deposits WHERE checkout_id = ? AND user_id = ? LIMIT 1");
    $chk->execute([$sessionCardRef, $userId]);
    $depStatus = $chk->fetchColumn();
    if ($depStatus === 'completed' || $depStatus === 'failed' || $depStatus === false) {
        unset($_SESSION['xdp_card_ref']);
        $sessionCardRef = '';
    } else {
        $sessionCardPending = true;
    }
}

// Check pending mobile payment in session
$sessionMobileRef     = '';
$sessionMobilePending = false;
if (!empty($_SESSION['xdp_mobile_ref'])) {
    $sessionMobileRef = $_SESSION['xdp_mobile_ref'];
    $chkM = $db->prepare("SELECT status FROM deposits WHERE checkout_id = ? AND user_id = ? LIMIT 1");
    $chkM->execute([$sessionMobileRef, $userId]);
    $mStatus = $chkM->fetchColumn();
    if ($mStatus === 'completed' || $mStatus === 'failed' || $mStatus === false) {
        unset($_SESSION['xdp_mobile_ref']);
        $sessionMobileRef = '';
    } else {
        $sessionMobilePending = true;
        $activeTabForce = 'mobile';
    }
}

$error      = '';
$success    = '';
$pendingRef = '';
$activeTab  = $_GET['tab'] ?? 'mobile';

// Flash messages from redirects
$payParam = $_GET['pay'] ?? '';
if ($payParam === 'completed') {
    $amount = htmlspecialchars($_GET['amount'] ?? '');
    $success = '✅ Payment confirmed! Your balance has been credited' . ($amount ? " with \$$amount USD" : '') . '.';
} elseif ($payParam === 'failed') {
    $activeTab = 'card';
    $error = '❌ Payment was not completed. Please try again.';
} elseif ($payParam === 'processing') {
    $activeTab = 'card';
    $success = '⏳ Your payment is being processed. Your balance will be updated shortly.';
}

// ══════════════════════════════════════
//  MOBILE MONEY (PawaPay STK Push)
// ══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mobile_submit'])) {
    $activeTab = 'mobile';
    $rawPhone  = trim($_POST['phone'] ?? '');
    $amount    = (float)($_POST['amount'] ?? 0);
    $phone     = '+' . preg_replace('/\D/', '', $rawPhone);
    $digits    = substr($phone, 1);

    [$dialCode, $currency] = detectCurrency($phone, $currencyByCode);
    $minAmount = (int)($defaultRates[$currency] ?? 1);

    if (!$dialCode || !$currency) {
        $error = "Mobile Money is available for Kenya (+254) only. Use Card for other countries.";
    } elseif (strlen($digits) < 8) {
        $error = "Phone number too short. Include country code, e.g. +254712345678.";
    } elseif ($amount < $minAmount) {
        $error = "Minimum amount: 4 USD.";
    } else {
        $xdpRef  = 'XDP-' . strtoupper(bin2hex(random_bytes(6)));
        $initNote = 'PawaPay mobile|' . number_format($amount, 2, '.', '') . ' ' . $currency;

        $db->prepare(
            "INSERT INTO deposits (user_id, amount, method, phone, status, reference, api_response, created_at)
             VALUES (?, ?, 'mobile', ?, 'pending', ?, ?, NOW())"
        )->execute([$userId, $amount, $phone, $xdpRef, $initNote]);

        $depInsertId = $db->lastInsertId();

        $payload = json_encode([
            'amount'      => $amount,
            'currency'    => $currency,
            'phone'       => $phone,
            'gateway'     => 'pawapay',
            'webhook_url' => "$siteUrl/xdp-webhook.php",
            'description' => 'Cdvrsbooster - Ajout de solde',
        ]);

        $ch = curl_init('https://pay.xdigitex.space/api/payments/initiate');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "X-API-Key: $apiKey"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($resp, true);

        if ($httpCode === 200 && !empty($data['reference'])) {
            $txRef = $data['reference'];
            $db->prepare("UPDATE deposits SET checkout_id=? WHERE id=?")->execute([$txRef, $depInsertId]);
            $pendingRef = $txRef;

            if (strtolower($data['pawa_status'] ?? '') === 'rejected') {
                $db->prepare("UPDATE deposits SET status='failed' WHERE id=?")->execute([$depInsertId]);
                $error      = 'Payment rejected by mobile network. Check your phone number and ensure mobile money is active.';
                $pendingRef = '';
            } else {
                $_SESSION['xdp_mobile_ref'] = $txRef;
                $sessionMobilePending       = true;
                $sessionMobileRef           = $txRef;
            }
        } else {
            $db->prepare("DELETE FROM deposits WHERE id=?")->execute([$depInsertId]);
            $errMsg = $data['message'] ?? $data['error'] ?? '';
            if (!$errMsg && is_array($data)) $errMsg = json_encode($data);
            $error = $errMsg ?: "Payment failed (HTTP $httpCode). Please try again.";
        }
    }
}

// ══════════════════════════════════════
//  CARD / MPESA PESAPAL (redirect)
// ══════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_submit'])) {
    $activeTab     = 'card';
    $cardAmountUsd = (float)($_POST['card_amount'] ?? 0);
    $cardEmail     = trim($_POST['card_email'] ?? '');
    $cardFirst     = trim($_POST['card_first'] ?? '');
    $cardLast      = trim($_POST['card_last'] ?? 'User');
    $cardPhone     = trim($_POST['card_phone'] ?? '');
    $cardAmountKes = round($cardAmountUsd * $kesRate, 2);

    if ($cardAmountUsd < 4) {
        $error = "Minimum amount: 4 USD.";
    } elseif (!filter_var($cardEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "Valid email is required for card payment.";
    } elseif (!$cardFirst) {
        $error = "First name is required for card payment.";
    } else {
        $xdpRef = 'XDP-' . strtoupper(bin2hex(random_bytes(6)));

        $db->prepare(
            "INSERT INTO deposits (user_id, amount, method, phone, status, reference, api_response, created_at)
             VALUES (?, ?, 'card', ?, 'pending', ?, 'Card/Pesapal', NOW())"
        )->execute([$userId, $cardAmountKes, $cardPhone ?: '', $xdpRef]);

        $depInsertId = $db->lastInsertId();

        $payload = json_encode([
            'amount'       => $cardAmountKes,
            'currency'     => 'KES',
            'gateway'      => 'card',
            'email'        => $cardEmail,
            'phone'        => $cardPhone ? '+' . preg_replace('/\D/', '', $cardPhone) : '+254000000000',
            'first_name'   => $cardFirst,
            'last_name'    => $cardLast ?: 'User',
            'description'  => 'Cdvrsbooster - Recharge portefeuille',
            'webhook_url'  => "$siteUrl/xdp-webhook.php",
            'callback_url' => "$siteUrl/xdp-card-callback.php",
        ]);

        $ch = curl_init('https://pay.xdigitex.space/api/payments/initiate');
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_HTTPHEADER     => ["Content-Type: application/json", "X-API-Key: $apiKey"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 30,
        ]);
        $resp     = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        $data = json_decode($resp, true);

        if ($httpCode === 200 && !empty($data['reference']) && !empty($data['redirect_url'])) {
            $txRef = $data['reference'];
            $db->prepare("UPDATE deposits SET checkout_id=? WHERE id=?")->execute([$txRef, $depInsertId]);
            $_SESSION['xdp_card_ref'] = $txRef;
            header("Location: " . $data['redirect_url']);
            exit;
        } else {
            $db->prepare("DELETE FROM deposits WHERE id=?")->execute([$depInsertId]);
            $errMsg = $data['message'] ?? $data['error'] ?? '';
            if (!$errMsg && is_array($data)) $errMsg = json_encode($data);
            $error = $errMsg ?: "Card payment failed (HTTP $httpCode). Please try again.";
        }
    }
}

if ($sessionCardPending) $activeTab = 'card';

$ratesForJs = $defaultRates;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajouter des fonds | Legrand</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
    --primary: #ffaa00;
    --primary-glow: rgba(255,170,0,0.1);
    --bg: #050507;
    --surface: #111114;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255,255,255,0.05);
    --sidebar-width: 280px;
    --error: #f87171;
    --success: #4ade80;
    --pri: #6366f1;
    --green: #10b981;
    --muted: #94a3b8;
    --bg2: #131625;
    --bg3: #1a1e35;
    --bd: #2a2f4a;
}
* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: var(--bg); color: var(--text); overflow-x: hidden; min-height: 100vh; }

.sidebar { position: fixed; left: calc(-1 * var(--sidebar-width)); top: 0; width: var(--sidebar-width); height: 100%; background: var(--surface); z-index: 10000; transition: 0.4s cubic-bezier(0.4,0,0.2,1); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
.sidebar.active { left: 0; }
.sidebar-header { padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--border); }
.sidebar-header img { height: 50px; border-radius: 10px; margin-bottom: 10px; }
.logo-txt { font-weight: 800; color: var(--primary); font-size: 18px; letter-spacing: -1px; }
.sidebar-menu { flex: 1; padding: 25px 0; }
.sidebar-menu a { display: flex; align-items: center; gap: 15px; padding: 15px 30px; color: var(--text-dim); text-decoration: none; font-weight: 600; transition: 0.2s; }
.sidebar-menu a:hover, .sidebar-menu a.active { color: var(--primary); background: var(--primary-glow); border-right: 4px solid var(--primary); }

.top-nav { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background: rgba(5,5,7,0.8); backdrop-filter: blur(15px); position: sticky; top: 0; z-index: 999; border-bottom: 1px solid var(--border); }
.nav-left { display: flex; align-items: center; gap: 20px; }
.menu-toggle { font-size: 24px; color: var(--primary); cursor: pointer; }
.balance-card { background: var(--primary); padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 14px; color: #000; box-shadow: 0 10px 20px rgba(255,170,0,0.2); }

.main-content { padding: 30px 20px; max-width: 560px; margin: auto; }
.overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 9999; display:none; backdrop-filter: blur(5px); }
.overlay.active { display:block; }

/* Tabs */
.tabs { display:flex; gap:6px; margin-bottom:20px; background:var(--bg2); padding:5px; border-radius:12px; border:1px solid var(--bd); }
.tb { flex:1; padding:10px; background:transparent; color:var(--muted); border:none; border-radius:8px; cursor:pointer; font-size:13px; font-weight:600; transition:.15s; font-family:'Outfit',sans-serif; }
.tb.active { background:var(--bg3); color:var(--text); box-shadow:0 1px 3px rgba(0,0,0,.4); }
.tc { display:none; }
.tc.active { display:block; }
.card-box { background:var(--bg2); border:1px solid var(--bd); border-radius:16px; padding:24px; margin-bottom:16px; }
.fg { margin-bottom:16px; }
.fl { display:block; font-size:11px; font-weight:600; color:var(--muted); margin-bottom:6px; text-transform:uppercase; letter-spacing:.5px; }
.fi { width:100%; background:var(--bg3); border:1px solid var(--bd); border-radius:10px; padding:13px 16px; color:var(--text); font-size:15px; font-weight:600; outline:none; transition:.15s; font-family:'Outfit',sans-serif; -webkit-appearance:none; }
.fi::placeholder { font-weight:400; color:var(--muted); font-size:14px; }
.fi:focus { border-color:var(--pri); box-shadow:0 0 0 3px rgba(99,102,241,.12); }
.fi-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.amt-wrap { position:relative; }
.amt-inp { padding-left:58px!important; font-size:18px!important; font-weight:800!important; }
.amt-sym { position:absolute; left:14px; top:50%; transform:translateY(-50%); font-size:12px; font-weight:700; color:var(--muted); }
.det { display:none; background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.2); border-radius:8px; padding:6px 12px; font-size:12px; font-weight:700; color:#a5b4fc; margin-top:6px; }
.min-hint { font-size:11px; color:var(--muted); margin-top:5px; display:flex; align-items:center; gap:5px; }
.min-hint.show { color:#f59e0b; }
.btn { display:flex; align-items:center; justify-content:center; gap:8px; width:100%; padding:15px; background:var(--pri); border:none; border-radius:12px; color:#fff; font-size:15px; font-weight:700; cursor:pointer; transition:.15s; font-family:'Outfit',sans-serif; margin-top:8px; }
.btn:hover { background:#5254cc; transform:translateY(-1px); }
.btn:disabled { opacity:.5; cursor:not-allowed; transform:none; }
.alert { border-radius:12px; padding:13px 16px; font-weight:600; font-size:14px; margin-bottom:18px; display:flex; align-items:start; gap:8px; }
.alert-err { background:rgba(239,68,68,.08); border:1px solid rgba(239,68,68,.25); color:var(--error); }
.alert-ok { background:rgba(16,185,129,.08); border:1px solid rgba(16,185,129,.25); color:var(--green); }
.alert-info { background:rgba(99,102,241,.08); border:1px solid rgba(99,102,241,.25); color:#a5b4fc; }
.pending-card { background:var(--bg2); border:1px solid var(--bd); border-radius:16px; padding:40px 28px; text-align:center; }
.spin { width:56px; height:56px; border:3px solid var(--bd); border-top-color:var(--pri); border-radius:50%; animation:sp 1s linear infinite; margin:0 auto 24px; }
@keyframes sp { to { transform:rotate(360deg); } }
.flow { display:flex; flex-direction:column; gap:12px; margin:24px 0; text-align:left; }
.flow-step { display:flex; align-items:center; gap:14px; padding:14px 16px; background:var(--bg3); border-radius:12px; border:1px solid var(--bd); }
.flow-step.done { border-color:rgba(16,185,129,.3); background:rgba(16,185,129,.05); }
.flow-step.active { border-color:rgba(99,102,241,.3); background:rgba(99,102,241,.05); }
.flow-icon { width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:14px; border:2px solid var(--bd); }
.flow-step.done .flow-icon { border-color:var(--green); color:var(--green); }
.flow-step.active .flow-icon { border-color:var(--pri); color:var(--pri); }
.flow-text strong { font-size:13px; display:block; }
.flow-text span { font-size:11px; color:var(--muted); }
.card-logos { display:flex; gap:10px; align-items:center; margin-bottom:20px; flex-wrap:wrap; }
.card-logo-badge { background:var(--bg3); border:1px solid var(--bd); border-radius:10px; padding:7px 14px; font-size:11px; font-weight:800; display:flex; align-items:center; gap:6px; }
.fee-box { background:var(--bg3); border:1px solid var(--bd); border-radius:12px; padding:14px 16px; margin-top:-8px; margin-bottom:16px; display:none; }
.fee-box.visible { display:block; }
.fee-row { display:flex; justify-content:space-between; align-items:center; font-size:13px; padding:3px 0; }
.fee-row .lbl { color:var(--muted); }
.fee-row .val { font-weight:700; }
.fee-row.total { border-top:1px solid var(--bd); margin-top:8px; padding-top:10px; font-weight:800; font-size:14px; }
.fee-row.total .val { color:var(--green); }
.networks { display:grid; grid-template-columns:repeat(4,1fr); gap:8px; margin-bottom:20px; }
.nc { background:var(--bg3); border:1px solid var(--bd); border-radius:10px; padding:10px 6px; text-align:center; font-size:10px; color:var(--muted); font-weight:600; }
.nc i { display:block; font-size:20px; margin-bottom:4px; color:var(--pri); }
.back-link { display:inline-flex; align-items:center; gap:6px; color:var(--muted); text-decoration:none; font-size:13px; margin-top:20px; }
.back-link:hover { color:var(--text); }

/* History */
.hist-wrap { margin-top:28px; }
.hist-head { display:flex; justify-content:space-between; align-items:center; margin-bottom:14px; }
.hist-head h3 { font-size:15px; font-weight:800; }
.hist-count { background:var(--bg3); border:1px solid var(--bd); border-radius:8px; padding:3px 10px; font-size:11px; font-weight:700; color:var(--muted); }
.dep-item { display:flex; justify-content:space-between; align-items:center; background:var(--bg2); border:1px solid var(--bd); border-radius:14px; padding:14px 16px; margin-bottom:10px; transition:.15s; }
.dep-item:hover { border-color:rgba(99,102,241,.3); background:var(--bg3); }
.dep-left { display:flex; align-items:center; gap:12px; }
.dep-icon { width:38px; height:38px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0; }
.dep-icon.completed { background:rgba(16,185,129,.1); border:1px solid rgba(16,185,129,.25); color:var(--green); }
.dep-icon.pending { background:rgba(245,158,11,.1); border:1px solid rgba(245,158,11,.25); color:#f59e0b; }
.dep-icon.failed { background:rgba(239,68,68,.1); border:1px solid rgba(239,68,68,.25); color:var(--error); }
.dep-info strong { display:block; font-size:13px; font-weight:700; margin-bottom:2px; }
.dep-info span { font-size:11px; color:var(--muted); }
.dep-right { text-align:right; flex-shrink:0; }
.dep-amt { font-size:15px; font-weight:800; margin-bottom:3px; }
.dep-amt.completed { color:var(--green); }
.dep-amt.pending { color:#f59e0b; }
.dep-amt.failed { color:var(--error); }
.st-pill { display:inline-block; padding:2px 9px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase; letter-spacing:.4px; }
.st-pill.completed { background:rgba(16,185,129,.1); color:var(--green); }
.st-pill.pending { background:rgba(245,158,11,.1); color:#f59e0b; }
.st-pill.failed { background:rgba(239,68,68,.1); color:var(--error); }
.dep-date { display:block; font-size:10px; color:var(--muted); margin-top:3px; }
.hist-empty { text-align:center; padding:36px 0; color:var(--muted); }
.hist-empty i { font-size:32px; opacity:.3; display:block; margin-bottom:12px; }

@media(max-width:480px) { .fi-row { grid-template-columns:1fr; } .networks { grid-template-columns:repeat(3,1fr); } }
</style>
</head>
<body>

<div class="overlay" id="overlay"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Logo">
        <div class="logo-txt">Legrand</div>
    </div>
    <div class="sidebar-menu">
        <a href="dashboard.php"><i class="fas fa-rocket"></i> Nouvelle Commande</a>
        <a href="my_orders.php"><i class="fas fa-history"></i> Historique</a>
        <a href="add-funds.php" class="active"><i class="fas fa-wallet"></i> Ajouter des fonds</a>
        <a href="services.php"><i class="fas fa-list-ul"></i> Services</a>
        <a href="api.php"><i class="fas fa-code"></i> API</a>
        <a href="profile.php"><i class="fas fa-user-shield"></i> Profil</a>
        <a href="logout.php" style="color:#f87171; margin-top:50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>

<div class="top-nav">
    <div class="nav-left">
        <i class="fas fa-align-left menu-toggle" id="toggleBtn"></i>
        <div style="font-weight:800; font-size:20px; letter-spacing:-1px;">Ajouter des fonds</div>
    </div>
    <div class="balance-card">
        Solde: <?= number_format($userBalance, 2) ?> $
    </div>
</div>

<div class="main-content">

<?php if ($success): ?>
<div class="alert alert-ok"><i class="fas fa-check-circle" style="margin-top:2px;flex-shrink:0"></i> <?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<div class="tabs">
    <button class="tb <?= $activeTab === 'mobile' ? 'active' : '' ?>" id="btn-mobile" onclick="switchTab('mobile')">
        <i class="fas fa-mobile-alt"></i> Mobile Money
    </button>
    <button class="tb <?= $activeTab === 'card' ? 'active' : '' ?>" id="btn-card" onclick="switchTab('card')">
        <i class="fas fa-credit-card"></i> Carte / Pesapal
    </button>
</div>

<!-- ═══ MOBILE MONEY TAB ═══ -->
<div class="tc <?= $activeTab === 'mobile' ? 'active' : '' ?>" id="tab-mobile">
  <div class="card-box">
    <div class="networks">
      <div class="nc"><i class="fas fa-mobile-alt" style="color:#4caf50"></i>M-Pesa</div>
      <div class="nc"><i class="fas fa-mobile-alt" style="color:#e91e63"></i>Airtel</div>
      <div class="nc"><i class="fas fa-mobile-alt" style="color:#ff9800"></i>MTN</div>
      <div class="nc"><i class="fas fa-mobile-alt" style="color:#2196f3"></i>Moov &amp; more</div>
    </div>

    <?php if ($error && $activeTab === 'mobile'): ?>
    <div class="alert alert-err"><i class="fas fa-exclamation-circle" style="margin-top:2px;flex-shrink:0"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($sessionMobilePending): ?>
    <div class="pending-card">
      <div class="spin" id="mspin"></div>
      <div style="font-size:20px;font-weight:800;margin-bottom:8px" id="mtitle">Attente du PIN…</div>
      <div style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:4px">Vérifiez votre téléphone et entrez votre PIN mobile money pour finaliser le paiement.</div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:8px">Ref: <code><?= htmlspecialchars($sessionMobileRef) ?></code></div>
      <div class="flow">
        <div class="flow-step done">
          <div class="flow-icon"><i class="fas fa-check"></i></div>
          <div class="flow-text"><strong>Demande envoyée</strong><span>STK push initié avec succès</span></div>
        </div>
        <div class="flow-step active" id="mfs2">
          <div class="flow-icon"><i class="fas fa-mobile-screen-button"></i></div>
          <div class="flow-text"><strong>Attente du PIN</strong><span>Entrez votre PIN mobile money</span></div>
        </div>
        <div class="flow-step" id="mfs3">
          <div class="flow-icon"><i class="fas fa-coins"></i></div>
          <div class="flow-text"><strong>Solde crédité</strong><span>Votre solde USD sera mis à jour</span></div>
        </div>
      </div>
      <button onclick="cancelMobile()" style="margin-top:8px;background:transparent;border:1px solid var(--bd);color:var(--muted);padding:8px 18px;border-radius:8px;cursor:pointer;font-size:13px;">Annuler / Réessayer</button>
    </div>
    <script>
    (function(){
      var ref=<?= json_encode($sessionMobileRef) ?>, n=0, max=50, iv;
      function poll(){
        fetch('<?= $siteUrl ?>/xdp-check.php?ref='+encodeURIComponent(ref))
          .then(function(r){return r.json();})
          .then(function(d){
            n++;
            if(d.status==='completed'){
              clearInterval(iv);
              document.getElementById('mfs2').className='flow-step done';
              document.getElementById('mfs3').className='flow-step done';
              document.getElementById('mspin').style.display='none';
              document.getElementById('mtitle').textContent='Paiement confirmé!';
              setTimeout(function(){
                window.location.href='<?= $siteUrl ?>/add-funds.php?pay=completed&amount='+encodeURIComponent(d.amount||'');
              }, 1500);
            } else if(d.status==='failed'){
              clearInterval(iv);
              document.getElementById('mspin').style.display='none';
              document.getElementById('mtitle').textContent='Paiement échoué';
              document.getElementById('mfs2').className='flow-step';
              document.getElementById('mfs2').innerHTML='<div class="flow-icon" style="border-color:var(--error);color:var(--error)"><i class="fas fa-times"></i></div><div class="flow-text"><strong>Paiement refusé</strong><span>Veuillez réessayer</span></div>';
            } else if(n>=max){
              clearInterval(iv);
              document.getElementById('mspin').style.display='none';
              document.getElementById('mtitle').textContent='Délai dépassé';
            }
          }).catch(function(){});
      }
      iv = setInterval(poll, 5000);
      poll();
      window.cancelMobile = function(){
        clearInterval(iv);
        fetch('<?= $siteUrl ?>/xdp-check.php?ref='+encodeURIComponent(ref)+'&cancel=1');
        window.location.href='<?= $siteUrl ?>/add-funds.php';
      };
    })();
    </script>
    <?php else: ?>
    <form method="post">
      <input type="hidden" name="mobile_submit" value="1">
      <div class="fg">
        <label class="fl">Numéro de téléphone (avec indicatif)</label>
        <input class="fi" type="tel" name="phone" placeholder="+254712345678" value="<?= htmlspecialchars($userPhone) ?>" id="mphone" required>
        <div class="min-hint" id="m-hint"><i class="fas fa-info-circle"></i> Kenya (+254) uniquement via Mobile Money</div>
      </div>
      <div class="fg">
        <label class="fl">Montant (devise locale)</label>
        <div class="amt-wrap">
          <span class="amt-sym" id="m-sym">KES</span>
          <input class="fi amt-inp" style="padding-left:68px" type="number" name="amount" id="mamount" step="1" min="1" placeholder="0" required>
        </div>
        <div class="det" id="m-usd-det"></div>
      </div>
      <button type="submit" class="btn"><i class="fas fa-mobile-alt"></i> Envoyer la demande STK Push</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- ═══ CARD TAB ═══ -->
<div class="tc <?= $activeTab === 'card' ? 'active' : '' ?>" id="tab-card">
  <div class="card-box">
    <div class="card-logos">
      <div class="card-logo-badge"><i class="fas fa-credit-card" style="color:#6366f1"></i> Visa / Mastercard</div>
      <div class="card-logo-badge"><i class="fas fa-mobile-alt" style="color:#4caf50"></i> M-Pesa</div>
      <div class="card-logo-badge"><i class="fas fa-mobile-alt" style="color:#e91e63"></i> Airtel</div>
    </div>

    <?php if ($error && $activeTab === 'card'): ?>
    <div class="alert alert-err"><i class="fas fa-exclamation-circle" style="margin-top:2px;flex-shrink:0"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($sessionCardPending): ?>
    <div class="pending-card">
      <div class="spin" id="cspin"></div>
      <div style="font-size:20px;font-weight:800;margin-bottom:8px" id="ctitle">Vérification du paiement…</div>
      <div style="font-size:13px;color:var(--muted);line-height:1.6;margin-bottom:4px">Veuillez patienter pendant que nous vérifions votre paiement par carte.</div>
      <div style="font-size:12px;color:var(--muted);margin-bottom:8px">Ref: <code><?= htmlspecialchars($sessionCardRef) ?></code></div>
      <div class="flow">
        <div class="flow-step done">
          <div class="flow-icon"><i class="fas fa-check"></i></div>
          <div class="flow-text"><strong>Redirecté vers Pesapal</strong><span>Paiement initié</span></div>
        </div>
        <div class="flow-step active" id="cfs2">
          <div class="flow-icon"><i class="fas fa-clock"></i></div>
          <div class="flow-text"><strong>Vérification en cours</strong><span>Confirmation du paiement</span></div>
        </div>
        <div class="flow-step" id="cfs3">
          <div class="flow-icon"><i class="fas fa-coins"></i></div>
          <div class="flow-text"><strong>Solde crédité</strong><span>Votre solde USD sera mis à jour</span></div>
        </div>
      </div>
      <a href="<?= $siteUrl ?>/add-funds.php?tab=card" class="back-link"><i class="fas fa-arrow-left"></i> Retour / Réessayer</a>
    </div>
    <script>
    (function(){
      var ref=<?= json_encode($sessionCardRef) ?>, n=0, max=24, iv;
      function poll(){
        fetch('<?= $siteUrl ?>/xdp-check.php?ref='+encodeURIComponent(ref))
          .then(function(r){return r.json();})
          .then(function(d){
            n++;
            if(d.status==='completed'){
              clearInterval(iv);
              document.getElementById('cfs2').className='flow-step done';
              document.getElementById('cfs3').className='flow-step done';
              document.getElementById('cspin').style.display='none';
              document.getElementById('ctitle').textContent='Paiement confirmé!';
              setTimeout(function(){
                window.location.href='<?= $siteUrl ?>/add-funds.php?pay=completed&amount='+encodeURIComponent(d.amount||'');
              }, 1500);
            } else if(d.status==='failed'){
              clearInterval(iv);
              document.getElementById('cspin').style.display='none';
              document.getElementById('ctitle').textContent='Paiement échoué';
            } else if(n>=max){
              clearInterval(iv);
              document.getElementById('cspin').style.display='none';
              document.getElementById('ctitle').textContent='Délai dépassé';
            }
          }).catch(function(){});
      }
      iv = setInterval(poll, 5000);
      poll();
    })();
    </script>
    <?php else: ?>
    <form method="post">
      <input type="hidden" name="card_submit" value="1">
      <div class="fg">
        <label class="fl">Montant (USD)</label>
        <div class="amt-wrap">
          <span class="amt-sym">$</span>
          <input class="fi amt-inp" type="number" name="card_amount" id="camount" step="0.01" min="1" placeholder="0.00" required>
        </div>
      </div>
      <div class="fee-box" id="fee-box">
        <div class="fee-row"><span class="lbl">Montant USD</span><span class="val" id="fee-usd">-</span></div>
        <div class="fee-row"><span class="lbl">Taux KES (~<?= number_format($kesRate, 0) ?>)</span><span class="val" id="fee-kes">-</span></div>
        <div class="fee-row total"><span class="lbl">Total à payer</span><span class="val" id="fee-total">-</span></div>
      </div>
      <div class="fi-row">
        <div class="fg">
          <label class="fl">Prénom</label>
          <input class="fi" type="text" name="card_first" placeholder="Jean" value="<?= htmlspecialchars($userFirst) ?>" required>
        </div>
        <div class="fg">
          <label class="fl">Nom</label>
          <input class="fi" type="text" name="card_last" placeholder="Dupont" value="<?= htmlspecialchars($userLast) ?>">
        </div>
      </div>
      <div class="fg">
        <label class="fl">Email</label>
        <input class="fi" type="email" name="card_email" placeholder="vous@exemple.com" value="<?= htmlspecialchars($userEmail) ?>" required>
      </div>
      <div class="fg">
        <label class="fl">Téléphone (optionnel)</label>
        <input class="fi" type="tel" name="card_phone" placeholder="+254712345678" value="<?= htmlspecialchars($userPhone) ?>">
      </div>
      <button type="submit" class="btn"><i class="fas fa-credit-card"></i> Payer via Pesapal</button>
    </form>
    <?php endif; ?>
  </div>
</div>

<!-- ═══ DEPOSIT HISTORY ═══ -->
<?php if (!empty($history)): ?>
<div class="hist-wrap">
  <div class="hist-head">
    <h3>Historique des dépôts</h3>
    <span class="hist-count"><?= count($history) ?></span>
  </div>
  <?php foreach ($history as $dep):
    $st = $dep['status'];
    $icon = $st === 'completed' ? 'fa-check' : ($st === 'failed' ? 'fa-times' : 'fa-clock');
    $method = strtoupper($dep['method'] ?? 'auto');
    $note = $dep['api_response'] ?? '';
    $localInfo = '';
    if (str_contains($note, '|')) {
        $localInfo = trim(explode('|', $note, 2)[1] ?? '');
    }
    $dateStr = date('d/m/Y H:i', strtotime($dep['created_at']));
  ?>
  <div class="dep-item">
    <div class="dep-left">
      <div class="dep-icon <?= $st ?>"><i class="fas <?= $icon ?>"></i></div>
      <div class="dep-info">
        <strong><?= htmlspecialchars($method) ?></strong>
        <span><?= $localInfo ? htmlspecialchars($localInfo) . ' · ' : '' ?>Ref: <?= htmlspecialchars($dep['checkout_id'] ?: $dep['reference'] ?: '-') ?></span>
        <span class="dep-date"><?= $dateStr ?></span>
      </div>
    </div>
    <div class="dep-right">
      <div class="dep-amt <?= $st ?>">$<?= number_format((float)$dep['amount'], 2) ?></div>
      <span class="st-pill <?= $st ?>"><?= $st === 'completed' ? 'Complété' : ($st === 'failed' ? 'Échoué' : 'En attente') ?></span>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php else: ?>
<div class="hist-wrap">
  <div class="hist-empty"><i class="fas fa-inbox"></i>Aucun dépôt pour l'instant</div>
</div>
<?php endif; ?>

</div><!-- /main-content -->

<script>
const ratesForJs = <?= json_encode($ratesForJs) ?>;
const kesRate = <?= $kesRate ?>;

function switchTab(t) {
    document.querySelectorAll('.tb').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tc').forEach(c => c.classList.remove('active'));
    document.getElementById('btn-' + t).classList.add('active');
    document.getElementById('tab-' + t).classList.add('active');
}

// Mobile: detect currency from phone
const mphone = document.getElementById('mphone');
const msym   = document.getElementById('m-sym');
const mhint  = document.getElementById('m-hint');
const mamount = document.getElementById('mamount');
const mdet   = document.getElementById('m-usd-det');

const currencyMap = {'254':'KES'};

function detectCur(phone) {
    const d = phone.replace(/\D/g, '');
    for (const [code, cur] of Object.entries(currencyMap)) {
        if (d.startsWith(code)) return [code, cur];
    }
    return ['', ''];
}

if (mphone) {
    mphone.addEventListener('input', function() {
        const [code, cur] = detectCur(this.value);
        if (cur) { msym.textContent = cur; }
        updateMUsd();
    });
    mamount && mamount.addEventListener('input', updateMUsd);
}

function updateMUsd() {
    if (!mamount || !mdet) return;
    const amt = parseFloat(mamount.value) || 0;
    const [, cur] = detectCur(mphone ? mphone.value : '');
    const rate = ratesForJs[cur] || 130;
    if (amt > 0 && cur) {
        const usd = (amt / rate).toFixed(2);
        mdet.style.display = 'block';
        mdet.textContent = '≈ $' + usd + ' USD sera crédité';
    } else {
        mdet.style.display = 'none';
    }
}

// Card: fee box
const camount = document.getElementById('camount');
const feeBox  = document.getElementById('fee-box');
if (camount) {
    camount.addEventListener('input', function() {
        const usd = parseFloat(this.value) || 0;
        if (usd >= 1) {
            const kes = (usd * kesRate).toFixed(2);
            document.getElementById('fee-usd').textContent = '$' + usd.toFixed(2);
            document.getElementById('fee-kes').textContent = 'KES ' + parseFloat(kes).toLocaleString();
            document.getElementById('fee-total').textContent = 'KES ' + parseFloat(kes).toLocaleString();
            feeBox.classList.add('visible');
        } else {
            feeBox.classList.remove('visible');
        }
    });
}

// Sidebar
const toggleBtn = document.getElementById('toggleBtn');
const sidebar   = document.getElementById('sidebar');
const overlay   = document.getElementById('overlay');
if (toggleBtn) {
    toggleBtn.onclick = () => { sidebar.classList.add('active'); overlay.classList.add('active'); };
    overlay.onclick   = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); };
}
</script>

</body>
</html>
