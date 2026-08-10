<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "config/auth.php";
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId  = $_SESSION['user_id'];
$message = '';
$error   = '';

/* ===============================
   FETCH MANUAL PAYMENT DETAILS
================================ */
$settings = $db->query("
    SELECT manual_payment_instructions 
    FROM settings 
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

$instructions = trim($settings['manual_payment_instructions'] ?? '');

/* ===============================
   HANDLE DEPOSIT REQUEST
================================ */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $amount    = (float)($_POST['amount'] ?? 0);
    $reference = trim($_POST['reference'] ?? '');

    if ($amount <= 0) {
        $error = "Veuillez entrer un montant valide.";
    } elseif ($reference === '') {
        $error = "Veuillez entrer la référence du paiement.";
    } elseif (!isset($_FILES['screenshot']) || $_FILES['screenshot']['error'] !== 0) {
        $error = "Veuillez télécharger une capture d'écran du paiement.";
    }

    if (!$error) {
        $uploadDir = __DIR__ . "/uploads/deposits/";
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext = strtolower(pathinfo($_FILES['screenshot']['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
            $error = "Format d'image invalide. Utilisez JPG, PNG ou WEBP.";
        } else {
            $fileName = "deposit_{$userId}_" . time() . "." . $ext;
            $filePath = $uploadDir . $fileName;

            if (!move_uploaded_file($_FILES['screenshot']['tmp_name'], $filePath)) {
                $error = "Échec du téléchargement de l'image.";
            } else {
                $stmt = $db->prepare("
                    INSERT INTO deposits 
                    (user_id, amount, method, reference, screenshot, status, created_at) 
                    VALUES (?, ?, 'manual', ?, ?, 'pending', NOW())
                ");
                $stmt->execute([$userId, $amount, $reference, $fileName]);

                $db->prepare("
                    INSERT INTO notifications (user_id, message) 
                    VALUES (0, ?)
                ")->execute([
                    "Demande de dépôt manuel : Utilisateur #{$userId}, Montant : {$amount} $"
                ]);

                $message = "Demande soumise avec succès. En attente de validation.";
            }
        }
    }
}

/* ===============================
   GET CURRENT BALANCE
================================ */
$stmtBal = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmtBal->execute([$userId]);
$userBalance = (float)($stmtBal->fetchColumn() ?? 0);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Ajouter des fonds | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.1);
    --bg: #050507;
    --surface: #111114;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.05);
    --sidebar-width: 280px;
    --error: #f87171;
    --success: #4ade80;
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: var(--bg); color: var(--text); overflow-x: hidden; min-height: 100vh; }

/* SIDEBAR & NAV */
.sidebar { position: fixed; left: calc(-1 * var(--sidebar-width)); top: 0; width: var(--sidebar-width); height: 100%; background: var(--surface); z-index: 10000; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
.sidebar.active { left: 0; }
.sidebar-header { padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--border); }
.sidebar-header img { height: 50px; border-radius: 10px; margin-bottom: 10px; }
.logo-txt { font-weight: 800; color: var(--primary); font-size: 18px; letter-spacing: -1px; }

.sidebar-menu { flex: 1; padding: 25px 0; }
.sidebar-menu a { display: flex; align-items: center; gap: 15px; padding: 15px 30px; color: var(--text-dim); text-decoration: none; font-weight: 600; transition: 0.2s; }
.sidebar-menu a:hover, .sidebar-menu a.active { color: var(--primary); background: var(--primary-glow); border-right: 4px solid var(--primary); }

.top-nav { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background: rgba(5, 5, 7, 0.8); backdrop-filter: blur(15px); position: sticky; top: 0; z-index: 999; border-bottom: 1px solid var(--border); }
.nav-left { display: flex; align-items: center; gap: 20px; }
.menu-toggle { font-size: 24px; color: var(--primary); cursor: pointer; }
.balance-card { background: var(--primary); padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 14px; color: #000; box-shadow: 0 10px 20px rgba(255, 170, 0, 0.2); }

/* CONTENT */
.main-content { padding: 40px 20px; max-width: 650px; margin: auto; }
.box { background: var(--surface); border-radius: 30px; padding: 40px; border: 1px solid var(--border); box-shadow: 0 40px 100px rgba(0,0,0,0.4); }

h2 { margin-bottom: 8px; font-size: 26px; font-weight: 800; text-align: center; letter-spacing: -1px; }
p.subtitle { text-align: center; color: var(--text-dim); margin-bottom: 35px; font-size: 14px; font-weight: 500; }

.instructions { background: rgba(255, 170, 0, 0.03); padding: 25px; border-radius: 20px; font-size: 14px; margin-bottom: 30px; border: 1px dashed var(--primary); color: var(--text); line-height: 1.7; }
.instructions strong { color: var(--primary); text-transform: uppercase; font-size: 12px; letter-spacing: 1px; }

label { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-top: 25px; display: block; margin-bottom: 10px; letter-spacing: 1px; }
input { width: 100%; padding: 16px; border-radius: 14px; border: 1px solid var(--border); background: #0d0d10; color: #fff; outline: none; transition: 0.3s; font-size: 15px; }
input:focus { border-color: var(--primary); box-shadow: 0 0 15px var(--primary-glow); }

/* FILE INPUT */
input[type="file"] { padding: 12px; background: rgba(255,255,255,0.02); cursor: pointer; font-size: 13px; }

button { width: 100%; padding: 18px; border: none; border-radius: 18px; background: var(--primary); color: #000; font-size: 16px; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 35px; text-transform: uppercase; }
button:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 170, 0, 0.3); }

.success { background: rgba(74, 222, 128, 0.1); color: var(--success); padding: 18px; border-radius: 15px; margin-bottom: 25px; border: 1px solid rgba(74, 222, 128, 0.2); text-align: center; font-size: 14px; font-weight: 700; }
.error { background: rgba(248, 113, 113, 0.1); color: var(--error); padding: 18px; border-radius: 15px; margin-bottom: 25px; border: 1px solid rgba(248, 113, 113, 0.2); text-align: center; font-size: 14px; font-weight: 700; }

.note { font-size: 12px; color: var(--text-dim); margin-top: 25px; text-align: center; line-height: 1.6; background: rgba(255,255,255,0.03); padding: 15px; border-radius: 12px; }
.overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 9999; display:none; backdrop-filter: blur(5px); }
.overlay.active { display:block; }
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
        <a href="logout.php" style="color: #f87171; margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>

<div class="top-nav">
    <div class="nav-left">
        <i class="fas fa-align-left menu-toggle" id="toggleBtn"></i>
        <div style="font-weight: 800; font-size: 20px; letter-spacing: -1px;">Dépôt Manuel</div>
    </div>
    <div class="balance-card">
        Solde: <?= number_format($userBalance, 2) ?> $
    </div>
</div>

<div class="main-content">

    <div class="box">
        <h2>Recharger votre compte</h2>
        <p class="subtitle">Suivez les instructions et envoyez votre preuve de paiement</p>

        <?php if ($message): ?>
            <div class="success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="error"><i class="fas fa-exclamation-triangle"></i> <?= $error ?></div>
        <?php endif; ?>

        <div class="instructions">
            <strong>Instructions de paiement :</strong><br>
            <div style="margin-top:10px;">
                <?= $instructions ?: '<em>Aucune instruction définie. Veuillez contacter le support.</em>' ?>
            </div>
        </div>

        <form method="post" enctype="multipart/form-data">
            <label>Montant déposé (USD)</label>
            <div style="position:relative;">
                <span style="position:absolute; left:16px; top:16px; color:var(--primary); font-weight:800; opacity: 0.8;">$</span>
                <input type="number" name="amount" step="0.01" required style="padding-left:35px;" placeholder="0.00">
            </div>

            <label>Référence du paiement (ID Transaction)</label>
            <input type="text" name="reference" placeholder="Ex: M-Pesa Ref ou N° de transaction" required>

            <label>Capture d'écran / Reçu</label>
            <input type="file" name="screenshot" accept="image/*" required>

            <button type="submit">Envoyer la demande de dépôt</button>
        </form>

        <div class="note">
            <i class="fas fa-clock" style="color: var(--primary);"></i> <strong>Note :</strong> Les dépôts sont traités manuellement sous <strong>1 à 12 heures</strong>. Assurez-vous que la capture d'écran est bien lisible.
        </div>
    </div>

</div>

<script>
const toggleBtn = document.getElementById('toggleBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

toggleBtn.onclick = () => { 
    sidebar.classList.add('active'); 
    overlay.classList.add('active'); 
};
overlay.onclick = () => { 
    sidebar.classList.remove('active'); 
    overlay.classList.remove('active'); 
};
</script>

</body>
</html>