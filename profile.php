<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

require_once "config/auth.php";
require_once "config/database.php";

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* =========================
   RÉCUPÉRATION UTILISATEUR
========================= */
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("Utilisateur non trouvé");
}

$userBalance = $user['balance'] ?? 0.00;
$columns = array_keys($user);

/* =========================
   DÉTECTION CHAMP TÉLÉPHONE
========================= */
$phoneField = null;
foreach (['phone','phone_number','mobile','msisdn','contact','tel'] as $f) {
    if (in_array($f, $columns)) {
        $phoneField = $f;
        break;
    }
}

/* =========================
   AUTO-DÉTECTION URL API
========================= */
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host   = $_SERVER['HTTP_HOST'];
$autoApiUrl = $scheme . '://' . $host . '/api/v2';

$message = "";
$msgType = "info";

/* =========================
   LOGIQUE API & PROFIL
========================= */
if (in_array('api_key', $columns) && empty($user['api_key'])) {
    $apiKey = bin2hex(random_bytes(32));
    $stmt = $db->prepare("UPDATE users SET api_key=?, api_url=?, api_enabled=1 WHERE id=?");
    $stmt->execute([$apiKey, $autoApiUrl, $userId]);
}

if (isset($_POST['regen_api']) && in_array('api_key', $columns)) {
    $apiKey = bin2hex(random_bytes(32));
    $stmt = $db->prepare("UPDATE users SET api_key=?, api_url=? WHERE id=?");
    $stmt->execute([$apiKey, $autoApiUrl, $userId]);
    $message = "Clé API régénérée avec succès.";
}

if (isset($_POST['toggle_api']) && in_array('api_enabled', $columns)) {
    $new = $user['api_enabled'] ? 0 : 1;
    $stmt = $db->prepare("UPDATE users SET api_enabled=? WHERE id=?");
    $stmt->execute([$new, $userId]);
    $message = $new ? "API activée." : "API désactivée.";
}

if (isset($_POST['update_profile'])) {
    $updates = []; $params  = [];
    if (in_array('email', $columns)) {
        $email = trim($_POST['email']);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
            $message = "Adresse email invalide."; 
            $msgType = "error";
        } else { 
            $updates[] = "email=?"; $params[] = $email; 
        }
    }
    if ($phoneField) {
        $phone = preg_replace('/\s+/', '', $_POST['phone']);
        if (strpos($phone, '0') === 0) { $phone = '254' . substr($phone, 1); }
        if (!preg_match('/^2547\d{8}$/', $phone)) { 
            $message = "Format de numéro invalide (Kenya 254)."; 
            $msgType = "error";
        } else { 
            $updates[] = "{$phoneField}=?"; $params[] = $phone; 
        }
    }
    if (!$message && $updates) {
        $params[] = $userId;
        $sql = "UPDATE users SET " . implode(', ', $updates) . " WHERE id=?";
        $db->prepare($sql)->execute($params);
        $message = "Profil mis à jour avec succès.";
        $msgType = "success";
    }
}

if (isset($_POST['change_password'])) {
    if (!password_verify($_POST['current_password'], $user['password'])) {
        $message = "Le mot de passe actuel est incorrect.";
        $msgType = "error";
    } elseif (strlen($_POST['new_password']) < 6) {
        $message = "Le mot de passe doit faire au moins 6 caractères.";
        $msgType = "error";
    } else {
        $hash = password_hash($_POST['new_password'], PASSWORD_DEFAULT);
        $db->prepare("UPDATE users SET password=? WHERE id=?")->execute([$hash, $userId]);
        $message = "Mot de passe mis à jour avec succès.";
        $msgType = "success";
    }
}

// Rafraîchir les données
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Mon Profil | Legrand</title>
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

/* LAYOUT */
.main-content { padding: 40px 20px; max-width: 1000px; margin: auto; }
.grid { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
.box { background: var(--surface); border-radius: 25px; padding: 35px; border: 1px solid var(--border); box-shadow: 0 40px 100px rgba(0,0,0,0.4); margin-bottom: 30px; }

h3 { margin-bottom: 25px; font-size: 20px; font-weight: 800; display: flex; align-items: center; gap: 12px; color: var(--text); letter-spacing: -0.5px; }
label { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-top: 20px; display: block; margin-bottom: 8px; letter-spacing: 1px; }

input { width: 100%; padding: 14px; border-radius: 12px; border: 1px solid var(--border); background: #0d0d10; color: #fff; outline: none; font-size: 15px; transition: 0.3s; }
input:focus { border-color: var(--primary); box-shadow: 0 0 15px var(--primary-glow); }
input.readonly { background: rgba(255,255,255,0.03); color: var(--text-dim); font-family: monospace; cursor: not-allowed; }

button { padding: 16px; border: none; border-radius: 14px; background: var(--primary); color: #000; font-weight: 800; cursor: pointer; transition: 0.3s; margin-top: 25px; width: 100%; text-transform: uppercase; letter-spacing: 0.5px; }
button:hover { transform: translateY(-3px); box-shadow: 0 15px 30px rgba(255, 170, 0, 0.3); }
button.secondary { background: rgba(255,255,255,0.05); color: #fff; border: 1px solid var(--border); width: auto; font-size: 12px; margin-top: 0; padding: 12px 20px; }
button.secondary:hover { background: rgba(255,255,255,0.1); border-color: var(--primary); }

.status-badge { padding: 6px 14px; border-radius: 50px; font-size: 10px; font-weight: 800; letter-spacing: 1px; }
.status-enabled { background: rgba(74, 222, 128, 0.1); color: var(--success); border: 1px solid rgba(74, 222, 128, 0.2); }
.status-disabled { background: rgba(248, 113, 113, 0.1); color: var(--error); border: 1px solid rgba(248, 113, 113, 0.2); }

.msg { padding: 20px; border-radius: 18px; font-size: 14px; margin-bottom: 30px; text-align: center; font-weight: 600; border: 1px solid transparent; }
.msg.success { background: rgba(74, 222, 128, 0.1); color: var(--success); border-color: rgba(74, 222, 128, 0.2); }
.msg.error { background: rgba(248, 113, 113, 0.1); color: var(--error); border-color: rgba(248, 113, 113, 0.2); }
.msg.info { background: rgba(255, 170, 0, 0.05); color: var(--primary); border-color: var(--primary-glow); }

.overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 9999; display:none; backdrop-filter: blur(5px); }
.overlay.active { display:block; }

@media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }
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
        <a href="add-funds.php"><i class="fas fa-wallet"></i> Ajouter des fonds</a>
        <a href="services.php"><i class="fas fa-list-ul"></i> Services</a>
        <a href="api.php"><i class="fas fa-code"></i> API</a>
        <a href="profile.php" class="active"><i class="fas fa-user-shield"></i> Profil</a>
        <a href="logout.php" style="color: #f87171; margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>

<div class="top-nav">
    <div class="nav-left">
        <i class="fas fa-align-left menu-toggle" id="toggleBtn"></i>
        <div style="font-weight: 800; font-size: 20px; letter-spacing: -1px;">Paramètres du compte</div>
    </div>
    <div class="balance-card">
        Solde: <?= number_format($userBalance, 2) ?> $
    </div>
</div>

<div class="main-content">

    <?php if ($message): ?>
        <div class="msg <?= $msgType ?>"><i class="fas fa-bell"></i> <?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="grid">
        <div class="box">
            <h3><i class="fas fa-id-card" style="color:var(--primary)"></i> Informations de base</h3>
            <form method="post">
                <input type="hidden" name="update_profile">
                
                <label>Nom d'utilisateur</label>
                <input value="<?= htmlspecialchars($user['username']) ?>" readonly class="readonly">

                <label>Adresse Email</label>
                <input name="email" value="<?= htmlspecialchars($user['email']) ?>" type="email" required>

                <?php if ($phoneField): ?>
                <label>Numéro </label>
               <input name="phone" 
       value="<?= htmlspecialchars($user[$phoneField] ?? '') ?>" 
       placeholder="ex: 24300000000">

                <?php endif; ?>

                <button type="submit">Enregistrer les modifications</button>
            </form>
        </div>

        <div class="box">
            <h3><i class="fas fa-lock" style="color:var(--primary)"></i> Sécurité</h3>
            <form method="post">
                <input type="hidden" name="change_password">

                <label>Mot de passe actuel</label>
                <input type="password" name="current_password" required placeholder="••••••••">

                <label>Nouveau mot de passe</label>
                <input type="password" name="new_password" required placeholder="Min. 6 caractères">

                <button type="submit" style="background:rgba(255,255,255,0.05); color:#fff; border: 1px solid var(--border);">Mettre à jour le mot de passe</button>
            </form>
        </div>
    </div>

    <div class="box">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:25px;">
            <h3><i class="fas fa-terminal" style="color:var(--primary)"></i> Accès API Développeur</h3>
            <span class="status-badge <?= $user['api_enabled'] ? 'status-enabled' : 'status-disabled' ?>">
                <?= $user['api_enabled'] ? 'ACTIF' : 'DÉSACTIVÉ' ?>
            </span>
        </div>

        <label>Votre Clé API Personnelle</label>
        <div style="display:flex; gap:10px;">
            <input class="readonly" value="<?= htmlspecialchars($user['api_key']) ?>" readonly style="letter-spacing: 2px;">
        </div>

        <label>Endpoint (URL API)</label>
        <input class="readonly" value="<?= htmlspecialchars($user['api_url'] ?? $autoApiUrl) ?>" readonly>

        <div style="margin-top:30px; display:flex; gap:15px; flex-wrap:wrap;">
            <form method="post" style="flex:1">
                <button name="regen_api" class="secondary" style="width:100%"><i class="fas fa-sync-alt"></i> Régénérer la clé</button>
            </form>
            <form method="post" style="flex:1">
                <button name="toggle_api" class="secondary" style="width:100%">
                    <i class="fas <?= $user['api_enabled'] ? 'fa-toggle-on' : 'fa-toggle-off' ?>"></i> 
                    <?= $user['api_enabled'] ? 'Désactiver l\'accès' : 'Activer l\'accès' ?>
                </button>
            </form>
        </div>
    </div>
</div>

<script>
const toggleBtn = document.getElementById('toggleBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

toggleBtn.onclick = () => { sidebar.classList.add('active'); overlay.classList.add('active'); };
overlay.onclick = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); };
</script>

</body>
</html>