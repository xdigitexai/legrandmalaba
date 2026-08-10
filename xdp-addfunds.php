<?php
/**
 * xdp-addfunds.php — Legrand Panel
 * Standalone add-funds page. Mobile Money STK push + Card/Pesapal redirect.
 * Modeled on cdvrsbooster.site/add-funds.php structure.
 */
ini_set('display_errors', 0);

$config = require __DIR__ . '/app/config.php';

try {
    $port = isset($config["db"]["port"]) ? ";port=" . $config["db"]["port"] : "";
    $conn = new PDO(
        "mysql:host=" . $config["db"]["host"] . $port
            . ";dbname=" . $config["db"]["name"]
            . ";charset=" . ($config["db"]["charset"] ?? "utf8mb4"),
        $config["db"]["user"],
        $config["db"]["pass"]
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("DB connection error");
}

session_start();

/* ── Auth check ── */
$loggedIn = false;
if (!empty($_SESSION["msmbilisim_userlogin"]) && $_SESSION["msmbilisim_userlogin"] == 1) {
    $loggedIn = true;
}
// Also support cookie-based auth like init.php
if (!$loggedIn && !empty($_COOKIE["u_id"]) && !empty($_COOKIE["u_login"]) && !empty($_COOKIE["u_password"])) {
    $rowCk = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
    $rowCk->execute(["id" => $_COOKIE["u_id"]]);
    $rowCk = $rowCk->fetch(PDO::FETCH_ASSOC);
    if ($rowCk && $_COOKIE["u_password"] === $rowCk["password"]) {
        $_SESSION["msmbilisim_userlogin"] = 1;
        $_SESSION["msmbilisim_userid"]    = $rowCk["client_id"];
        $loggedIn = true;
    }
}
if (!$loggedIn) {
    header("Location: /");
    exit;
}

$clientId = (int)($_SESSION["msmbilisim_userid"] ?? 0);

$clientStmt = $conn->prepare("SELECT client_id, name, username, email, balance FROM clients WHERE client_id=? LIMIT 1");
$clientStmt->execute([$clientId]);
$client = $clientStmt->fetch(PDO::FETCH_ASSOC);
if (!$client) {
    header("Location: /logout");
    exit;
}

/* ── Get xdigitex API key from paymentmethods table ── */
$pmStmt = $conn->prepare("SELECT methodExtras FROM paymentmethods WHERE methodId=200 AND methodStatus='1' LIMIT 1");
$pmStmt->execute();
$pmRow = $pmStmt->fetch(PDO::FETCH_ASSOC);
$extras = json_decode($pmRow["methodExtras"] ?? '{}', true);
$apiKey = trim($extras["apiKey"] ?? '');

/* ── Site info ── */
$siteStmt = $conn->query("SELECT site_name FROM settings WHERE id=1 LIMIT 1");
$siteName = $siteStmt->fetchColumn() ?: 'Legrand';

$proto   = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$siteUrl = $proto . '://' . $_SERVER['HTTP_HOST'];

$userBalance = (float)($client['balance'] ?? 0);
$userEmail   = $client['email'] ?? '';
$userName    = $client['username'] ?? $client['name'] ?? '';

/* ── Currency rates ── */
$defaultRates = [
    'KES' => 130, 'UGX' => 3800, 'CDF' => 2400,
    'XAF' => 600, 'XOF' => 600,  'RWF' => 1350,
    'ZMW' => 27,  'SLE' => 22,   'TZS' => 2600,
    'GHS' => 15,  'NGN' => 1600,
];

/* ── Recent payment history ── */
$histStmt = $conn->prepare(
    "SELECT payment_id, payment_create_date, payment_amount, payment_method, payment_extra, t_id, payment_status
     FROM payments WHERE client_id=? ORDER BY payment_id DESC LIMIT 20"
);
$histStmt->execute([$clientId]);
$history = $histStmt->fetchAll(PDO::FETCH_ASSOC);

/* ── Check pending mobile payment ── */
$sessionMobileRef     = '';
$sessionMobilePending = false;
if (!empty($_SESSION['xdp_mobile_ref'])) {
    $sessionMobileRef = $_SESSION['xdp_mobile_ref'];
    $chkM = $conn->prepare("SELECT payment_status FROM payments WHERE t_id=? AND client_id=? LIMIT 1");
    $chkM->execute([$sessionMobileRef, $clientId]);
    $mStatus = $chkM->fetchColumn();
    if ($mStatus == 3 || $mStatus === false) {
        unset($_SESSION['xdp_mobile_ref']);
        $sessionMobileRef = '';
    } else {
        $sessionMobilePending = true;
    }
}

/* ── Check pending card payment ── */
$sessionCardRef     = '';
$sessionCardPending = false;
if (!empty($_SESSION['xdp_card_ref'])) {
    $sessionCardRef = $_SESSION['xdp_card_ref'];
    $chkC = $conn->prepare("SELECT payment_status FROM payments WHERE t_id=? AND client_id=? LIMIT 1");
    $chkC->execute([$sessionCardRef, $clientId]);
    $cStatus = $chkC->fetchColumn();
    if ($cStatus == 3 || $cStatus === false) {
        unset($_SESSION['xdp_card_ref']);
        $sessionCardRef = '';
    } else {
        $sessionCardPending = true;
    }
}

$error     = '';
$success   = '';
$activeTab = $_GET['tab'] ?? 'mobile';

$payParam = $_GET['pay'] ?? '';
if ($payParam === 'completed') {
    $amount  = htmlspecialchars($_GET['amount'] ?? '');
    $success = '✅ Paiement confirmé ! Votre solde a été crédité' . ($amount ? " de \$$amount USD" : '') . '.';
    $activeTab = 'mobile';
} elseif ($payParam === 'failed') {
    $activeTab = 'card';
    $error = '❌ Le paiement n\'a pas été complété. Veuillez réessayer.';
} elseif ($payParam === 'processing') {
    $activeTab = 'card';
    $success = '⏳ Votre paiement est en cours de traitement. Votre solde sera mis à jour sous peu.';
}

/* ═══════════════════════════════════════════
   MOBILE MONEY — STK Push
═══════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mobile_submit'])) {
    $activeTab = 'mobile';
    $rawPhone  = trim($_POST['phone'] ?? '');
    $amount    = (float)($_POST['amount'] ?? 0);
    $phone     = '+' . preg_replace('/\D/', '', $rawPhone);
    $digits    = substr($phone, 1);

    $currencyByCode = [
        '254' => 'KES', '256' => 'UGX', '243' => 'CDF',
        '255' => 'TZS', '250' => 'RWF', '237' => 'XAF',
        '233' => 'GHS', '234' => 'NGN', '260' => 'ZMW',
        '232' => 'SLE', '221' => 'XOF',
    ];

    $detectedCurrency = '';
    foreach ([3, 2] as $l) {
        $p = substr($digits, 0, $l);
        if (isset($currencyByCode[$p])) { $detectedCurrency = $currencyByCode[$p]; break; }
    }

    $minLocal = (int)ceil(($defaultRates[$detectedCurrency] ?? 1) * 5);

    if (!$detectedCurrency) {
        $error = "Country not recognized. Examples: +254 (Kenya), +256 (Uganda), +243 (Congo DRC).";
    } elseif (strlen($digits) < 8) {
        $error = "Phone number too short. Include country code. e.g. +254712345678.";
    } elseif ($amount < $minLocal) {
        $error = "Minimum amount: " . number_format($minLocal, 0, '.', ',') . " $detectedCurrency.";
    } elseif (empty($apiKey)) {
        $error = "Payment gateway unavailable. Please try again later.";
    } else {
        $xdpRef = 'XDP-' . strtoupper(bin2hex(random_bytes(6)));

        $ins = $conn->prepare(
            "INSERT INTO payments SET client_id=:cid, payment_amount=:amt, payment_method=:meth,
             payment_mode=:mode, payment_create_date=:dt, payment_ip=:ip,
             payment_extra=:extra, payment_status=0, payment_delivery=2, t_id=:tid"
        );
        $ins->execute([
            'cid'   => $clientId,
            'amt'   => number_format($amount / ($defaultRates[$detectedCurrency] ?? 130), 4, '.', ''),
            'meth'  => 200,
            'mode'  => 'Automatic',
            'dt'    => date('Y.m.d H:i:s'),
            'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
            'extra' => $xdpRef,
            'tid'   => $xdpRef,
        ]);
        $depId = $conn->lastInsertId();

        $payload = json_encode([
            'amount'      => $amount,
            'currency'    => $detectedCurrency,
            'phone'       => $phone,
            'gateway'     => 'pawapay',
            'reference'   => $xdpRef,
            'webhook_url' => "$siteUrl/xdigitex_webhook.php",
            'description' => "$siteName - Ajout de solde",
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
            $conn->prepare("UPDATE payments SET t_id=? WHERE payment_id=?")->execute([$txRef, $depId]);
            if (strtolower($data['pawa_status'] ?? '') === 'rejected') {
                $conn->prepare("UPDATE payments SET payment_status=2 WHERE payment_id=?")->execute([$depId]);
                $error = 'Paiement rejeté par le réseau mobile. Vérifiez votre numéro et que le mobile money est actif.';
            } else {
                $_SESSION['xdp_mobile_ref'] = $txRef;
                $sessionMobilePending       = true;
                $sessionMobileRef           = $txRef;
            }
        } else {
            $conn->prepare("DELETE FROM payments WHERE payment_id=?")->execute([$depId]);
            $errMsg = $data['message'] ?? $data['error'] ?? '';
            $error  = $errMsg ?: "Paiement échoué (HTTP $httpCode). Réessayez.";
        }
    }
}

/* ═══════════════════════════════════════════
   CARD / M-PESA ONLINE — Redirect
═══════════════════════════════════════════ */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['card_submit'])) {
    $activeTab     = 'card';
    $cardAmountUsd = (float)($_POST['card_amount'] ?? 0);
    $cardEmail     = trim($_POST['card_email'] ?? '');
    $cardFirst     = trim($_POST['card_first'] ?? '');
    $cardLast      = trim($_POST['card_last'] ?? 'User');
    $cardPhone     = trim($_POST['card_phone'] ?? '');
    $cardAmountKes = round($cardAmountUsd * ($defaultRates['KES'] ?? 130), 2);

    if ($cardAmountUsd < 1) {
        $error = "Minimum amount: 5 USD.";
    } elseif (!filter_var($cardEmail, FILTER_VALIDATE_EMAIL)) {
        $error = "A valid email is required for card payment.";
    } elseif (!$cardFirst) {
        $error = "First name is required for card payment.";
    } elseif (empty($apiKey)) {
        $error = "Payment gateway unavailable. Please try again later.";
    } else {
        $xdpRef = 'XDP-' . strtoupper(bin2hex(random_bytes(6)));

        $ins = $conn->prepare(
            "INSERT INTO payments SET client_id=:cid, payment_amount=:amt, payment_method=:meth,
             payment_mode=:mode, payment_create_date=:dt, payment_ip=:ip,
             payment_extra=:extra, payment_status=0, payment_delivery=2, t_id=:tid"
        );
        $ins->execute([
            'cid'   => $clientId,
            'amt'   => number_format($cardAmountUsd, 4, '.', ''),
            'meth'  => 200,
            'mode'  => 'Automatic',
            'dt'    => date('Y.m.d H:i:s'),
            'ip'    => $_SERVER['REMOTE_ADDR'] ?? '',
            'extra' => $xdpRef,
            'tid'   => $xdpRef,
        ]);
        $depId = $conn->lastInsertId();

        $payload = json_encode([
            'amount'       => $cardAmountKes,
            'currency'     => 'KES',
            'gateway'      => 'card',
            'email'        => $cardEmail,
            'phone'        => $cardPhone ? '+' . preg_replace('/\D/', '', $cardPhone) : '+254000000000',
            'first_name'   => $cardFirst,
            'last_name'    => $cardLast ?: 'User',
            'reference'    => $xdpRef,
            'description'  => "$siteName - Recharge portefeuille",
            'webhook_url'  => "$siteUrl/xdigitex_webhook.php",
            'callback_url' => "$siteUrl/xdp-addfunds?pay=completed&amount=" . urlencode($cardAmountUsd),
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
            $conn->prepare("UPDATE payments SET t_id=? WHERE payment_id=?")->execute([$txRef, $depId]);
            $_SESSION['xdp_card_ref'] = $txRef;
            header("Location: " . $data['redirect_url']);
            exit;
        } else {
            $conn->prepare("DELETE FROM payments WHERE payment_id=?")->execute([$depId]);
            $errMsg = $data['message'] ?? $data['error'] ?? '';
            $error  = $errMsg ?: "Paiement par carte échoué (HTTP $httpCode). Réessayez.";
        }
    }
}

if ($sessionCardPending) $activeTab = 'card';
$ratesForJs = $defaultRates;
$kesRate    = $defaultRates['KES'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ajouter des fonds | <?= htmlspecialchars($siteName) ?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
:root{
  --primary:#00e5a0;--primary-glow:rgba(0,229,160,0.1);
  --bg:#050507;--surface:#111114;--text:#ffffff;--text-dim:#94a3b8;
  --border:rgba(255,255,255,0.07);--sidebar-width:270px;
  --error:#f87171;--success:#4ade80;--pri:#00c47a;--green:#10b981;
  --muted:#94a3b8;--bg2:#111318;--bg3:#181c28;--bd:#252a3a;
}
*{box-sizing:border-box;margin:0;padding:0;font-family:'Outfit',sans-serif;}
body{background:var(--bg);color:var(--text);overflow-x:hidden;min-height:100vh;}

/* SIDEBAR */
.sidebar{position:fixed;left:calc(-1*var(--sidebar-width));top:0;width:var(--sidebar-width);height:100%;
  background:var(--surface);z-index:10000;transition:.4s cubic-bezier(.4,0,.2,1);
  border-right:1px solid var(--border);display:flex;flex-direction:column;}
.sidebar.active{left:0;}
.sidebar-header{padding:32px 20px;text-align:center;border-bottom:1px solid var(--border);}
.logo-txt{font-weight:800;color:var(--primary);font-size:20px;letter-spacing:-1px;}
.sidebar-menu{flex:1;padding:20px 0;overflow-y:auto;}
.sidebar-menu a{display:flex;align-items:center;gap:14px;padding:13px 28px;color:var(--text-dim);
  text-decoration:none;font-weight:600;transition:.2s;font-size:14px;}
.sidebar-menu a:hover,.sidebar-menu a.active{color:var(--primary);background:var(--primary-glow);
  border-right:3px solid var(--primary);}

/* TOP NAV */
.top-nav{display:flex;justify-content:space-between;align-items:center;
  padding:14px 5%;background:rgba(5,5,7,.9);backdrop-filter:blur(15px);
  position:sticky;top:0;z-index:999;border-bottom:1px solid var(--border);}
.nav-left{display:flex;align-items:center;gap:16px;}
.menu-toggle{font-size:22px;color:var(--primary);cursor:pointer;background:none;border:none;}
.site-logo{font-weight:800;font-size:18px;color:var(--primary);}
.balance-card{background:linear-gradient(135deg,var(--pri),#00a86b);padding:8px 18px;
  border-radius:50px;font-weight:800;font-size:13px;color:#fff;
  box-shadow:0 8px 20px rgba(0,196,122,.25);}

/* MAIN */
.main-content{padding:28px 20px;max-width:560px;margin:auto;}
.page-title{font-size:22px;font-weight:800;margin-bottom:20px;}

/* OVERLAY */
.overlay{position:fixed;top:0;left:0;width:100%;height:100%;
  background:rgba(0,0,0,.75);z-index:9999;display:none;backdrop-filter:blur(5px);}
.overlay.active{display:block;}

/* TABS */
.tabs{display:flex;gap:6px;margin-bottom:20px;background:var(--bg2);
  padding:5px;border-radius:12px;border:1px solid var(--bd);}
.tb{flex:1;padding:11px;background:transparent;color:var(--muted);border:none;
  border-radius:8px;cursor:pointer;font-size:13px;font-weight:700;transition:.15s;
  font-family:'Outfit',sans-serif;}
.tb.active{background:var(--bg3);color:var(--text);box-shadow:0 1px 4px rgba(0,0,0,.5);}
.tc{display:none;}.tc.active{display:block;}

/* CARDS */
.card-box{background:var(--bg2);border:1px solid var(--bd);border-radius:16px;padding:22px;margin-bottom:14px;}
.fg{margin-bottom:14px;}
.fl{display:block;font-size:11px;font-weight:700;color:var(--muted);margin-bottom:6px;
  text-transform:uppercase;letter-spacing:.6px;}
.fi{width:100%;background:var(--bg3);border:1px solid var(--bd);border-radius:10px;
  padding:13px 16px;color:var(--text);font-size:15px;font-weight:600;outline:none;
  transition:.15s;font-family:'Outfit',sans-serif;-webkit-appearance:none;}
.fi::placeholder{font-weight:400;color:var(--muted);font-size:14px;}
.fi:focus{border-color:var(--pri);box-shadow:0 0 0 3px rgba(0,196,122,.15);}
.fi-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.amt-wrap{position:relative;}
.amt-inp{padding-left:64px!important;font-size:17px!important;font-weight:800!important;}
.amt-sym{position:absolute;left:14px;top:50%;transform:translateY(-50%);font-size:12px;
  font-weight:700;color:var(--muted);pointer-events:none;}

/* Country detected box */
.country-detected{display:none;background:rgba(0,196,122,.07);border:1px solid rgba(0,196,122,.25);
  border-radius:9px;padding:10px 14px;font-size:13px;font-weight:600;color:var(--primary);
  margin-top:6px;line-height:1.5;}
.min-note{display:none;font-size:11px;color:var(--muted);margin-top:5px;font-weight:600;}
.usd-det{display:none;background:rgba(0,196,122,.06);border:1px solid rgba(0,196,122,.2);
  border-radius:8px;padding:7px 12px;font-size:12px;font-weight:700;color:#4ade80;margin-top:6px;}
.codes-hint{font-size:11px;color:var(--muted);margin-top:5px;line-height:1.6;}

/* FEE BOX */
.fee-box{display:none;background:var(--bg3);border:1px solid var(--bd);border-radius:10px;
  padding:12px 14px;margin-top:6px;}
.fee-box.visible{display:block;}
.fee-row{display:flex;justify-content:space-between;font-size:13px;color:var(--muted);padding:3px 0;}
.fee-row.total{color:var(--text);font-weight:800;border-top:1px solid var(--bd);margin-top:6px;padding-top:8px;}

/* BUTTON */
.btn{display:flex;align-items:center;justify-content:center;gap:9px;width:100%;padding:15px;
  background:linear-gradient(135deg,var(--pri),#00a86b);border:none;border-radius:12px;
  color:#fff;font-size:15px;font-weight:800;cursor:pointer;transition:.15s;
  font-family:'Outfit',sans-serif;margin-top:8px;
  box-shadow:0 8px 24px rgba(0,196,122,.3);}
.btn:hover{transform:translateY(-1px);box-shadow:0 12px 28px rgba(0,196,122,.4);}

/* ALERTS */
.alert{border-radius:12px;padding:13px 16px;font-weight:600;font-size:14px;margin-bottom:18px;
  display:flex;align-items:start;gap:8px;}
.alert-err{background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);color:var(--error);}
.alert-ok{background:rgba(16,185,129,.08);border:1px solid rgba(16,185,129,.25);color:var(--green);}

/* PENDING */
.pending-card{background:var(--bg2);border:1px solid var(--bd);border-radius:16px;
  padding:36px 24px;text-align:center;}
.spin{width:52px;height:52px;border:3px solid var(--bd);border-top-color:var(--pri);
  border-radius:50%;animation:sp 1s linear infinite;margin:0 auto 20px;}
@keyframes sp{to{transform:rotate(360deg);}}
.pending-ref{font-size:11px;color:var(--muted);margin-top:8px;word-break:break-all;}

/* HISTORY */
.hist-wrap{background:var(--bg2);border:1px solid var(--bd);border-radius:16px;
  padding:18px;margin-top:20px;}
.hist-head{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.hist-head h3{font-size:15px;font-weight:800;}
.hist-count{background:var(--bg3);border:1px solid var(--bd);border-radius:50px;
  padding:2px 10px;font-size:11px;font-weight:700;color:var(--muted);}
.dep-item{display:flex;justify-content:space-between;align-items:center;padding:11px 0;
  border-bottom:1px solid var(--bd);}
.dep-item:last-child{border-bottom:none;}
.dep-left{display:flex;align-items:center;gap:12px;}
.dep-icon{width:36px;height:36px;border-radius:10px;display:flex;align-items:center;
  justify-content:center;font-size:14px;}
.dep-icon.pending{background:rgba(251,191,36,.1);color:#fbbf24;}
.dep-icon.completed{background:rgba(74,222,128,.1);color:var(--green);}
.dep-icon.failed{background:rgba(248,113,113,.1);color:var(--error);}
.dep-info{display:flex;flex-direction:column;gap:2px;}
.dep-info strong{font-size:13px;font-weight:700;}
.dep-info span{font-size:11px;color:var(--muted);}
.dep-right{text-align:right;}
.dep-amt{font-size:14px;font-weight:800;}
.dep-amt.completed{color:var(--green);}
.dep-amt.failed{color:var(--error);}
.dep-amt.pending{color:#fbbf24;}
.st-pill{display:inline-block;padding:2px 8px;border-radius:50px;font-size:10px;font-weight:700;}
.st-pill.completed{background:rgba(74,222,128,.1);color:var(--green);}
.st-pill.failed{background:rgba(248,113,113,.1);color:var(--error);}
.st-pill.pending{background:rgba(251,191,36,.1);color:#fbbf24;}
.hist-empty{text-align:center;padding:30px;color:var(--muted);}
.hist-empty i{font-size:28px;margin-bottom:10px;display:block;}

/* API KEY WARNING */
.api-warn{background:rgba(251,191,36,.08);border:1px solid rgba(251,191,36,.3);
  border-radius:10px;padding:12px 16px;font-size:13px;color:#fbbf24;margin-bottom:16px;font-weight:600;}

@media(max-width:480px){
  .main-content{padding:20px 14px;}
  .fi-row{grid-template-columns:1fr;}
}
</style>
</head>
<body>

<!-- Sidebar overlay -->
<div class="overlay" id="overlay"></div>

<!-- Sidebar -->
<div class="sidebar" id="sidebar">
  <div class="sidebar-header">
    <div class="logo-txt"><?= htmlspecialchars($siteName) ?></div>
    <div style="font-size:12px;color:var(--muted);margin-top:4px;">Bienvenue, <?= htmlspecialchars($userName) ?></div>
  </div>
  <nav class="sidebar-menu">
    <a href="/neworder"><i class="fas fa-plus-circle fa-fw"></i> Nouvelle commande</a>
    <a href="/orders"><i class="fas fa-list fa-fw"></i> Mes commandes</a>
    <a href="/xdp-addfunds" class="active"><i class="fas fa-wallet fa-fw"></i> Ajouter des fonds</a>
    <a href="/account"><i class="fas fa-user fa-fw"></i> Mon compte</a>
    <a href="/tickets"><i class="fas fa-headset fa-fw"></i> Support</a>
    <a href="/logout"><i class="fas fa-sign-out-alt fa-fw"></i> Déconnexion</a>
  </nav>
</div>

<!-- Top nav -->
<nav class="top-nav">
  <div class="nav-left">
    <button class="menu-toggle" id="toggleBtn" aria-label="Menu"><i class="fas fa-bars"></i></button>
    <span class="site-logo"><?= htmlspecialchars($siteName) ?></span>
  </div>
  <div class="balance-card">$<?= number_format($userBalance, 2) ?> USD</div>
</nav>

<!-- Main -->
<div class="main-content">
  <h2 class="page-title">💳 Ajouter des fonds</h2>

  <?php if ($error): ?>
  <div class="alert alert-err"><i class="fas fa-exclamation-circle"></i><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>
  <?php if ($success): ?>
  <div class="alert alert-ok"><i class="fas fa-check-circle"></i><?= htmlspecialchars($success) ?></div>
  <?php endif; ?>

  <?php if (empty($apiKey)): ?>
  <div class="api-warn"><i class="fas fa-exclamation-triangle"></i> Payment system is temporarily unavailable. Please contact support.</div>
  <?php endif; ?>

  <!-- TABS -->
  <div class="tabs">
    <button class="tb <?= $activeTab==='mobile'?'active':'' ?>" id="btn-mobile" onclick="switchTab('mobile')">
      📱 Mobile Money
    </button>
    <button class="tb <?= $activeTab==='card'?'active':'' ?>" id="btn-card" onclick="switchTab('card')">
      💳 Card / Visa / M-Pesa Online
    </button>
  </div>

  <!-- MOBILE MONEY TAB -->
  <div class="tc <?= $activeTab==='mobile'?'active':'' ?>" id="tab-mobile">
    <?php if ($sessionMobilePending): ?>
    <div class="pending-card">
      <div class="spin"></div>
      <h3 style="margin-bottom:10px;">Invitation de paiement envoyée !</h3>
      <p style="color:var(--muted);font-size:14px;">Vérifiez votre téléphone et confirmez le paiement Mobile Money.</p>
      <p class="pending-ref">Réf: <?= htmlspecialchars($sessionMobileRef) ?></p>
      <button class="btn" style="margin-top:20px;" onclick="location.reload()"><i class="fas fa-sync"></i> Actualiser le statut</button>
      <button class="btn" style="margin-top:8px;background:var(--bg3);box-shadow:none;color:var(--muted);"
        onclick="<?php unset($_SESSION['xdp_mobile_ref']); ?>location.href='/xdp-addfunds'">Nouveau paiement</button>
    </div>
    <?php else: ?>
    <form method="post" class="card-box">
      <div class="fg">
        <label class="fl">Numéro de téléphone <span style="color:var(--muted)">(avec indicatif pays)</span></label>
        <input class="fi" type="tel" name="phone" id="mphone"
          placeholder="+254712345678" autocomplete="tel" required>
        <div class="codes-hint">KE +254 · UG +256 · CD +243 · TZ +255 · RW +250 ·<br>GH +233 · NG +234 · ZM +260 · CM +237 · SN +221</div>
        <div class="country-detected" id="m-country"></div>
        <div class="min-note" id="m-min-note"></div>
      </div>

      <div class="fg">
        <label class="fl">Montant <span id="m-cur-label" style="color:var(--primary)">(devise locale)</span></label>
        <div class="amt-wrap">
          <span class="amt-sym" id="m-sym">KES</span>
          <input class="fi amt-inp" type="number" name="amount" id="mamount"
            step="1" min="1" placeholder="1" required>
        </div>
        <div class="usd-det" id="m-usd-det"></div>
      </div>

      <button type="submit" name="mobile_submit" class="btn">
        <i class="fas fa-mobile-alt"></i> Envoyer l'invitation de paiement
      </button>
    </form>
    <?php endif; ?>
  </div>

  <!-- CARD TAB -->
  <div class="tc <?= $activeTab==='card'?'active':'' ?>" id="tab-card">
    <?php if ($sessionCardPending): ?>
    <div class="pending-card">
      <div class="spin"></div>
      <h3 style="margin-bottom:10px;">Redirection en cours…</h3>
      <p style="color:var(--muted);font-size:14px;">Complétez le paiement sur la page sécurisée.</p>
      <button class="btn" style="margin-top:20px;" onclick="location.reload()"><i class="fas fa-sync"></i> Actualiser</button>
    </div>
    <?php else: ?>
    <form method="post" class="card-box">
      <div class="fg">
        <label class="fl">Montant (USD, min $5.00)</label>
        <div class="amt-wrap">
          <span class="amt-sym">$</span>
          <input class="fi amt-inp" type="number" name="card_amount" id="camount"
            step="0.01" min="5" placeholder="0.00" required>
        </div>
        <div class="fee-box" id="fee-box">
          <div class="fee-row"><span>USD</span><span id="fee-usd">-</span></div>
          <div class="fee-row"><span>Taux KES (~<?= number_format($kesRate,0) ?>)</span><span id="fee-kes">-</span></div>
          <div class="fee-row total"><span>Total à payer</span><span id="fee-total">-</span></div>
        </div>
      </div>
      <div class="fi-row">
        <div class="fg">
          <label class="fl">Prénom</label>
          <input class="fi" type="text" name="card_first" placeholder="Jean"
            value="<?= htmlspecialchars(explode(' ', $userName)[0]) ?>" required>
        </div>
        <div class="fg">
          <label class="fl">Nom</label>
          <input class="fi" type="text" name="card_last" placeholder="Dupont">
        </div>
      </div>
      <div class="fg">
        <label class="fl">Email</label>
        <input class="fi" type="email" name="card_email" placeholder="vous@exemple.com"
          value="<?= htmlspecialchars($userEmail) ?>" required>
      </div>
      <div class="fg">
        <label class="fl">Téléphone (optionnel)</label>
        <input class="fi" type="tel" name="card_phone" placeholder="+254712345678">
      </div>
      <button type="submit" name="card_submit" class="btn">
        <i class="fas fa-credit-card"></i> Payer via Pesapal / Card
      </button>
    </form>
    <?php endif; ?>
  </div>

  <!-- HISTORY -->
  <?php if (!empty($history)): ?>
  <div class="hist-wrap">
    <div class="hist-head"><h3>Historique des dépôts</h3><span class="hist-count"><?= count($history) ?></span></div>
    <?php foreach ($history as $dep):
      $stCode  = (int)($dep['payment_status'] ?? 0);
      $stName  = $stCode === 3 ? 'completed' : ($stCode === 2 ? 'failed' : 'pending');
      $stLabel = $stCode === 3 ? 'Complété' : ($stCode === 2 ? 'Échoué' : 'En attente');
      $icon    = $stCode === 3 ? 'fa-check' : ($stCode === 2 ? 'fa-times' : 'fa-clock');
      $ref     = $dep['t_id'] ?: ($dep['payment_extra'] ?: '-');
      $dtStr   = date('d/m/Y H:i', strtotime($dep['payment_create_date']));
    ?>
    <div class="dep-item">
      <div class="dep-left">
        <div class="dep-icon <?= $stName ?>"><i class="fas <?= $icon ?>"></i></div>
        <div class="dep-info">
          <strong>Mobile Money</strong>
          <span>Réf: <?= htmlspecialchars($ref) ?></span>
          <span><?= $dtStr ?></span>
        </div>
      </div>
      <div class="dep-right">
        <div class="dep-amt <?= $stName ?>">$<?= number_format((float)$dep['payment_amount'],4) ?></div>
        <span class="st-pill <?= $stName ?>"><?= $stLabel ?></span>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="hist-wrap"><div class="hist-empty"><i class="fas fa-inbox"></i><p>Aucun dépôt pour l'instant.</p></div></div>
  <?php endif; ?>
</div>

<script>
const ratesForJs = <?= json_encode($ratesForJs) ?>;
const kesRate    = <?= $kesRate ?>;

document.getElementById('toggleBtn').onclick = function(){
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('overlay').classList.toggle('active');
};
document.getElementById('overlay').onclick = function(){
    document.getElementById('sidebar').classList.remove('active');
    document.getElementById('overlay').classList.remove('active');
};

function switchTab(t){
    document.querySelectorAll('.tb').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tc').forEach(c=>c.classList.remove('active'));
    document.getElementById('btn-'+t).classList.add('active');
    document.getElementById('tab-'+t).classList.add('active');
}

/* ── Phone auto-detect ── */
const countryInfo = {
    '254':{flag:'🇰🇪',name:'Kenya',       currency:'KES'},
    '256':{flag:'🇺🇬',name:'Uganda',      currency:'UGX'},
    '243':{flag:'🇨🇩',name:'Congo (RDC)', currency:'CDF'},
    '255':{flag:'🇹🇿',name:'Tanzanie',    currency:'TZS'},
    '250':{flag:'🇷🇼',name:'Rwanda',      currency:'RWF'},
    '237':{flag:'🇨🇲',name:'Cameroun',    currency:'XAF'},
    '233':{flag:'🇬🇭',name:'Ghana',       currency:'GHS'},
    '234':{flag:'🇳🇬',name:'Nigeria',     currency:'NGN'},
    '260':{flag:'🇿🇲',name:'Zambie',      currency:'ZMW'},
    '232':{flag:'🇸🇱',name:'Sierra Leone',currency:'SLE'},
    '221':{flag:'🇸🇳',name:'Sénégal',     currency:'XOF'},
};

function detectCur(phone){
    const d = phone.replace(/\D/g,'');
    for(const len of [3,2]){
        const p = d.substring(0,len);
        if(countryInfo[p]) return [p,countryInfo[p]];
    }
    return ['',null];
}

const mphone   = document.getElementById('mphone');
const msym     = document.getElementById('m-sym');
const mamount  = document.getElementById('mamount');
const mdet     = document.getElementById('m-usd-det');
const mCountry = document.getElementById('m-country');
const mCurLbl  = document.getElementById('m-cur-label');
const mMinNote = document.getElementById('m-min-note');

function onPhoneInput(){
    const [code,info] = detectCur(mphone ? mphone.value : '');
    if(info){
        msym.textContent = info.currency;
        if(mCurLbl) mCurLbl.textContent = info.currency;
        const rate = ratesForJs[info.currency]||1;
        if(mCountry){
            const minVal = Math.ceil((ratesForJs[info.currency]||1) * 5);
        const minStr = 'Min: '+minVal.toLocaleString()+' '+info.currency+' (≈ 5 USD)';
            mCountry.innerHTML = info.flag+' <strong>'+info.name+'</strong> &nbsp;·&nbsp; '+info.currency
                +' &nbsp;·&nbsp; 1 USD ≈ '+rate.toLocaleString()+' '+info.currency
                +'<br><span style="font-size:11px;opacity:.8">'+minStr+'</span>';
            mCountry.style.display='block';
        }
        if(mamount){ mamount.min=minVal; mamount.placeholder=minVal.toLocaleString(); }
        if(mMinNote){
            mMinNote.textContent = 'Dépôt minimum: '+minVal.toLocaleString()+' '+info.currency+' (= 5 USD)';
            mMinNote.style.display='block';
        }
    } else {
        msym.textContent='KES';
        if(mCurLbl) mCurLbl.textContent='devise locale';
        if(mCountry) mCountry.style.display='none';
        if(mMinNote) mMinNote.style.display='none';
    }
    updateMUsd();
}

function updateMUsd(){
    if(!mamount||!mdet) return;
    const amt = parseFloat(mamount.value)||0;
    const [,info] = detectCur(mphone ? mphone.value : '');
    const cur  = info ? info.currency : '';
    const rate = ratesForJs[cur]||130;
    if(amt>0 && cur){
        mdet.style.display='block';
        mdet.textContent = '≈ $'+(amt/rate).toFixed(4)+' USD sera crédité sur votre solde';
    } else {
        mdet.style.display='none';
    }
}

if(mphone){
    mphone.addEventListener('input',onPhoneInput);
    if(mamount) mamount.addEventListener('input',updateMUsd);
    if(mphone.value) onPhoneInput();
}

/* ── Card amount preview ── */
const camount = document.getElementById('camount');
if(camount){
    camount.addEventListener('input',function(){
        const usd = parseFloat(this.value)||0;
        const box = document.getElementById('fee-box');
        if(usd>=5){
            const kes = Math.round(usd*kesRate);
            document.getElementById('fee-usd').textContent  = '$'+usd.toFixed(2);
            document.getElementById('fee-kes').textContent  = kes.toLocaleString()+' KES';
            document.getElementById('fee-total').textContent= kes.toLocaleString()+' KES';
            box.classList.add('visible');
        } else {
            box.classList.remove('visible');
        }
    });
}
(function() {    var ref = "<?= $sessionMobileRef ?: $sessionCardRef ?>";    if (!ref) return;    var iv, n = 0, max = 100;    function poll() {      fetch("xdp-check.php?ref=" + encodeURIComponent(ref))        .then(r => r.json())        .then(d => {          n++;          if (d.status === "completed") {            clearInterval(iv);            window.location.href = "xdp-addfunds.php?pay=completed&amount=" + encodeURIComponent(d.amount || "");          } else if (d.status === "failed") {            clearInterval(iv);            window.location.href = "xdp-addfunds.php?pay=failed";          } else if (n >= max) {            clearInterval(iv);          }        }).catch(() => {});    }    iv = setInterval(poll, 5000); poll();  })();
</script>
</body>
</html>
