<?php
ob_start();

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "config/auth.php";
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    die("Accès non autorisé");
}

$userId = $_SESSION['user_id'];

/* =========================
    VALIDATION DES ENTRÉES
========================= */
$serviceId = (int)($_POST['service_id'] ?? 0);
$link      = trim($_POST['link'] ?? '');
$quantity  = (int)($_POST['quantity'] ?? 0);

if ($serviceId <= 0 || $link === '' || $quantity <= 0) {
    die("Données de commande invalides");
}

/* =========================
    RÉCUPÉRATION SERVICE + FOURNISSEUR
========================= */
$stmt = $db->prepare("
    SELECT 
        s.name,
        s.price_usd,
        s.cost_usd,
        s.provider_service_id,
        p.api_url,
        p.api_key
    FROM services s
    JOIN providers p ON p.id = s.provider_id
    WHERE s.id = ? AND s.active = 1
    LIMIT 1
");
$stmt->execute([$serviceId]);
$service = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$service) {
    die("Service non trouvé ou inactif");
}

// Store the service name in a variable for the orders table
$serviceNameSnapshot = $service['name'];

/* =========================
    CALCUL DU COÛT
========================= */
$pricePer1000 = (float)$service['price_usd'];
$totalCost    = round(($quantity / 1000) * $pricePer1000, 2);

/* =========================
    VÉRIFICATION DU SOLDE
========================= */
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$userId]);
$balance = (float)$stmt->fetchColumn();

if ($balance < $totalCost) {
    $amountNeeded = round($totalCost - $balance, 2);
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <title>Solde Insuffisant | Legrand</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #050507; --card: #111114; --text: #ffffff; --primary: #ffaa00; --border: rgba(255,255,255,0.05); }
        body{font-family:'Outfit',sans-serif; background:var(--bg); color:var(--text); display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;}
        .box{background:var(--card); border-radius:30px; padding:45px; max-width:480px; width:100%; border:1px solid var(--border); box-shadow:0 40px 100px rgba(0,0,0,0.5); text-align:center;}
        .icon-circle{width:80px; height:80px; background:rgba(255,170,0,0.1); color:var(--primary); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:35px; margin:0 auto 25px; border: 1px solid var(--primary);}
        h2{margin:0 0 10px 0; font-size:26px; font-weight:800; letter-spacing: -1px;}
        .summary{background:rgba(255,255,255,0.02); padding:20px; border-radius:20px; margin:25px 0; text-align:left; border: 1px solid var(--border);}
        .row{display:flex; justify-content:space-between; margin-bottom:12px; font-size:14px; font-weight: 500;}
        .row strong{color:var(--primary);}
        .loader-bar { height: 6px; background: rgba(255,255,255,0.05); border-radius: 10px; overflow: hidden; margin-top: 25px; }
        .loader-fill { height: 100%; background: var(--primary); width: 0%; animation: load 4s linear forwards; box-shadow: 0 0 15px var(--primary);}
        @keyframes load { from {width: 0%;} to {width: 100%;} }
    </style>
    </head>
    <body>
    <div class="box">
        <div class="icon-circle"><i class="fas fa-wallet"></i></div>
        <h2>Solde Insuffisant</h2>
        <p style="color:#94a3b8; font-size: 15px;">Vous n'avez pas assez de fonds pour cette commande.</p>
        <div class="summary">
            <div class="row"><span>Coût total :</span><strong><?= number_format($totalCost,2) ?> $</strong></div>
            <div class="row"><span>Votre solde :</span><strong><?= number_format($balance,2) ?> $</strong></div>
            <div class="row" style="border-top:1px solid var(--border); padding-top:12px; margin-top:12px;">
                <span style="font-weight:700;">Manquant :</span><strong style="color:#f87171"><?= number_format($amountNeeded,2) ?> $</strong>
            </div>
        </div>
        <p style="font-size:13px; color:#94a3b8; margin-top: 20px;">Redirection vers la page de dépôt...</p>
        <div class="loader-bar"><div class="loader-fill"></div></div>
    </div>
    <script>setTimeout(() => { window.location.href = "add-funds.php?amountNeeded=<?= $amountNeeded ?>"; }, 4000);</script>
    </body>
    </html>
    <?php
    exit;
}

/* =========================
    SAUVEGARDE DE LA COMMANDE
========================= */
try {
    $db->beginTransaction();

    $stmt = $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
    $stmt->execute([$totalCost, $userId]);

    // Added 'service_name' to the INSERT to save the name snapshot
    $stmt = $db->prepare("
        INSERT INTO orders 
        (user_id, service_id, service_name, link, quantity, price, status, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, 'paid', NOW())
    ");
    $stmt->execute([$userId, $serviceId, $serviceNameSnapshot, $link, $quantity, $totalCost]);

    $orderId = $db->lastInsertId();

    $db->commit();
} catch (Exception $e) {
    $db->rollBack();
    die("Échec de la commande : " . $e->getMessage());
}

/* =========================
    ENVOI AU FOURNISSEUR (API)
========================= */
$payload = [
    'key'      => $service['api_key'],
    'action'   => 'add',
    'service'  => $service['provider_service_id'],
    'link'     => $link,
    'quantity' => $quantity
];

$ch = curl_init($service['api_url']);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => http_build_query($payload),
    CURLOPT_TIMEOUT        => 40,
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);

// VÉRIFICATION DE L'ERREUR DE COMMANDE ACTIVE
if (isset($data['error']) && strpos($data['error'], 'active order with this link') !== false) {
    $db->beginTransaction();
    $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([$totalCost, $userId]);
    $db->prepare("DELETE FROM orders WHERE id = ?")->execute([$orderId]);
    $db->commit();
    ?>
    <!DOCTYPE html>
    <html lang="fr">
    <head>
    <meta charset="UTF-8">
    <title>Commande en cours | Legrand</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;700;800&display=swap" rel="stylesheet">
    <style>
        :root { --bg: #050507; --card: #111114; --text: #ffffff; --warning: #ffaa00; --border: rgba(255,255,255,0.05); }
        body{font-family:'Outfit',sans-serif; background:var(--bg); color:var(--text); display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;}
        .box{background:var(--card); border-radius:30px; padding:45px; max-width:480px; width:100%; border:1px solid var(--border); text-align:center;}
        .icon-circle{width:80px; height:80px; background:rgba(255,170,0,0.1); color:var(--warning); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:35px; margin:0 auto 25px; border: 1px solid var(--warning);}
        h2{margin:0 0 15px 0; font-size:24px; font-weight:800;}
        .btn{display:inline-block; width:100%; padding:15px; background:rgba(255,255,255,0.05); color:#fff; text-decoration:none; border-radius:12px; font-weight:700; border: 1px solid var(--border); margin-top: 20px;}
    </style>
    </head>
    <body>
    <div class="box">
        <div class="icon-circle"><i class="fas fa-clock"></i></div>
        <h2>Commande déjà active</h2>
        <p style="color:#94a3b8; font-size: 15px;">Vous avez une commande active en cours de traitement, veuillez patienter, réessayer plus tard ou utiliser un lien différent.</p>
        <a href="dashboard.php" class="btn">Retour au tableau de bord</a>
    </div>
    </body>
    </html>
    <?php
    exit;
}

if (isset($data['order'])) {
    $providerOrderId = $data['order'];
    $stmt = $db->prepare("
        UPDATE orders 
        SET status = 'processing', 
            provider_order_id = ?, 
            provider_response = ? 
        WHERE id = ?
    ");
    $stmt->execute([$providerOrderId, $response, $orderId]);
} else {
    $logDir = __DIR__ . "/logs";
    if (!is_dir($logDir)) { 
        mkdir($logDir, 0755, true); 
    }
    $logMessage = "[" . date("Y-m-d H:i:s") . "] ----------------------------------\n";
    $logMessage .= "ORDER ID: #$orderId\n";
    $logMessage .= "ENDPOINT: " . $service['api_url'] . "\n";
    $logMessage .= "API RESPONSE: " . ($response ?: "EMPTY_RESPONSE/CURL_ERROR") . "\n";
    $logMessage .= "PAYLOAD SENT: " . json_encode($payload) . "\n";
    $logMessage .= "-------------------------------------------------------------\n\n";
    file_put_contents($logDir . "/provider_errors.log", $logMessage, FILE_APPEND);
}

/* =========================
    RÉCUPÉRATION DONNÉES FINALES
========================= */
// Pulling service_name from orders table directly
$stmt = $db->prepare("
    SELECT o.id, o.quantity, o.price, o.status, 
           o.service_name, u.balance
    FROM orders o
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ? AND o.user_id = ?
");
$stmt->execute([$orderId, $userId]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commande Reçue | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&swap" rel="stylesheet">
<style>
    :root { --bg: #050507; --card: #111114; --text: #ffffff; --primary: #ffaa00; --success: #4ade80; --border: rgba(255,255,255,0.05); }
    body{font-family:'Outfit',sans-serif; background:var(--bg); color:var(--text); display:flex; align-items:center; justify-content:center; min-height:100vh; margin:0; padding:20px;}
    .box{background:var(--card); border-radius:30px; padding:45px; max-width:550px; width:100%; border:1px solid var(--border); box-shadow:0 40px 100px rgba(0,0,0,0.5); text-align:center; animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);}
    @keyframes slideUp { from {opacity:0; transform:translateY(30px);} to {opacity:1; transform:translateY(0);} }
    .check-icon{width:80px; height:80px; background:rgba(74,222,128,0.1); color:var(--success); border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:35px; margin:0 auto 25px; border: 2px solid rgba(74,222,128,0.2); animation: scaleIn 0.5s 0.2s both;}
    @keyframes scaleIn { from {transform:scale(0);} to {transform:scale(1);} }
    h2{margin:0 0 8px 0; font-size:28px; font-weight:800; letter-spacing: -1px;}
    p.subtitle{color:#94a3b8; font-size:15px; margin-bottom:35px; font-weight: 500;}
    .receipt{background:rgba(255,255,255,0.02); border-radius:20px; overflow:hidden; border:1px solid var(--border); margin-bottom:35px;}
    .receipt-row{display:flex; justify-content:space-between; padding:15px 25px; border-bottom:1px solid var(--border); font-size:14px; font-weight: 500;}
    .receipt-row:last-child{border-bottom:none;}
    .label{color:#94a3b8;}
    .value{font-weight:700; text-align:right; max-width:60%;}
    .btn{display:inline-block; width:100%; padding:18px; background:var(--primary); color:#000; text-decoration:none; border-radius:15px; font-weight:800; transition:0.3s; text-transform: uppercase; letter-spacing: 0.5px;}
    .btn:hover{transform:translateY(-3px); box-shadow: 0 15px 30px rgba(255,170,0,0.3);}
</style>
</head>
<body>
<div class="box">
    <div class="check-icon"><i class="fas fa-rocket"></i></div>
    <h2>Commande Expédiée</h2>
    <p class="subtitle">Votre commande #<?= (int)$order['id'] ?> est maintenant en cours de traitement.</p>
    <div class="receipt">
        <div class="receipt-row">
            <span class="label">Service</span>
            <span class="value"><?= htmlspecialchars($order['service_name']) ?></span>
        </div>
        <div class="receipt-row">
            <span class="label">Quantité</span>
            <span class="value"><?= number_format($order['quantity']) ?></span>
        </div>
        <div class="receipt-row">
            <span class="label">Coût Total</span>
            <span class="value" style="color:var(--primary)"><?= number_format($order['price'],2) ?> $</span>
        </div>
        <div class="receipt-row">
            <span class="label">Solde Restant</span>
            <span class="value"><?= number_format($order['balance'],2) ?> $</span>
        </div>
    </div>
    <a href="my_orders.php" class="btn">Suivre ma commande</a>
</div>
</body>
</html>