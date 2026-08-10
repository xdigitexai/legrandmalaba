<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/database.php";

/* 🔐 ADMIN GUARD */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = "";

/* SAUVEGARDE DES INSTRUCTIONS */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = $_POST['manual_payment_instructions'] ?? '';

    $stmt = $db->prepare("UPDATE settings SET manual_payment_instructions = ? LIMIT 1");
    $stmt->execute([$content]);

    $message = "Instructions de paiement mises à jour.";
}

/* RÉCUPÉRATION DES DONNÉES */
$settings = $db->query("SELECT manual_payment_instructions FROM settings LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$current = $settings['manual_payment_instructions'] ?? '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>PAIEMENTS MANUELS | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root {
    --bg: #050507;
    --surface: #111114;
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.1);
    --success: #4ade80;
    --danger: #f87171;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.05);
    --sidebar-w: 280px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body { background-color: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif; min-height: 100vh; display: flex; overflow-x: hidden; }

/* SIDEBAR TOGGLE */
.sidebar {
    width: var(--sidebar-w); background: var(--surface); border-right: 1px solid var(--border);
    padding: 30px 20px; position: fixed; left: calc(-1 * var(--sidebar-w)); top: 0; bottom: 0; 
    transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1); z-index: 1001;
}
.sidebar.open { transform: translateX(var(--sidebar-w)); }
.sidebar-logo { display: flex; align-items: center; gap: 12px; padding: 0 10px 40px; text-decoration: none; }
.logo-circle { width: 45px; height: 45px; background: var(--bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border); }
.logo-circle img { width: 30px; }
.logo-text { font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -1px; }
.logo-text span { color: var(--primary); }

.sidebar-nav a {
    display: flex; align-items: center; gap: 12px; color: var(--text-dim); text-decoration: none;
    padding: 14px 18px; border-radius: 15px; margin-bottom: 5px; font-size: 14px; font-weight: 600; transition: 0.2s;
}
.sidebar-nav a:hover, .sidebar-nav a.active { color: #fff; background: rgba(255,255,255,0.03); }
.sidebar-nav a.active { background: var(--primary-glow); color: var(--primary); border: 1px solid rgba(255, 170, 0, 0.1); }

/* MAIN AREA */
.main { flex: 1; padding: 40px; transition: margin-left .3s ease; width: 100%; }
@media(min-width: 1025px) { .sidebar { left: 0; } .main { margin-left: var(--sidebar-w); } .hamburger { display: none; } }

.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.header-left { display: flex; align-items: center; gap: 15px; }
.hamburger { font-size: 22px; cursor: pointer; color: var(--primary); }
.header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 28px; font-weight: 700; letter-spacing: -1px; }

/* CARDS & FORMS */
.card { background: var(--surface); padding: 35px; border-radius: 25px; border: 1px solid var(--border); margin-bottom: 30px; }
.card h2 { font-family: 'Space Grotesk', sans-serif; font-size: 18px; color: var(--primary); margin-bottom: 20px; text-transform: uppercase; display: flex; align-items: center; gap: 10px; }

textarea {
    width: 100%; min-height: 300px; background: var(--bg); border: 1px solid var(--border);
    color: #fff; padding: 20px; border-radius: 15px; font-family: 'Consolas', monospace;
    font-size: 14px; line-height: 1.6; outline: none; transition: 0.3s; resize: vertical;
}
textarea:focus { border-color: var(--primary); box-shadow: 0 0 15px var(--primary-glow); }

.btn-save {
    background: var(--primary); color: #000; border: none; padding: 16px 32px;
    border-radius: 12px; font-weight: 800; cursor: pointer; text-transform: uppercase; transition: 0.3s;
    margin-top: 20px; display: inline-flex; align-items: center; gap: 10px;
}
.btn-save:hover { transform: translateY(-3px); box-shadow: 0 10px 20px var(--primary-glow); }

/* PREVIEW BOX */
.preview-box { background: rgba(0,0,0,0.3); border: 1px dashed var(--border); border-radius: 15px; padding: 25px; margin-top: 15px; color: var(--text-dim); }
.preview-box b, .preview-box strong { color: #fff; }

.alert { padding: 18px; border-radius: 12px; margin-bottom: 25px; font-weight: 600; font-size: 14px; background: rgba(74, 222, 128, 0.1); color: var(--success); border: 1px solid rgba(74, 222, 128, 0.1); }
.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); display: none; z-index: 1000; }
.overlay.show { display: block; }
</style>
</head>

<body>

<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <a href="dashboard.php" class="sidebar-logo">
        <div class="logo-circle">
            <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Logo">
        </div>
        <div class="logo-text">Legrand</div>
    </a>
    <div class="sidebar-nav">
        <a href="dashboard.php"><i class="fas fa-layer-group"></i> Dashboard</a>
        <a href="orders.php"><i class="fas fa-shopping-bag"></i> Commandes</a>
        <a href="users.php"><i class="fas fa-user-friends"></i> Utilisateurs</a>
        <a href="deposits.php"><i class="fas fa-credit-card"></i> Dépôts</a>
        <a href="services.php"><i class="fas fa-stream"></i> Services</a>
        <a href="settings.php" class="active"><i class="fas fa-sliders-h"></i> Paramètres</a>
    </div>
</div>

<div class="main">
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>Paiements Manuels</h1>
        </div>
        <a href="logout.php" style="color:var(--danger); text-decoration:none; font-weight:800; font-size:12px; text-transform:uppercase;">Quitter</a>
    </div>

    <?php if($message): ?>
        <div class="alert"><i class="fas fa-check-circle"></i> <?= $message ?></div>
    <?php endif; ?>

    <div class="card">
        <h2><i class="fas fa-code"></i> Éditeur d'instructions</h2>
        <p style="color:var(--text-dim); font-size:13px; margin-bottom:20px;">
            Utilisez du texte simple ou du code <b>HTML/CSS</b> pour personnaliser l'affichage côté client (M-Pesa, Airtel Money, etc.).
        </p>

        <form method="post">
            <textarea name="manual_payment_instructions" placeholder="<h3>M-Pesa</h3><p>Paybill: 123456</p>"><?= htmlspecialchars($current) ?></textarea>
            <button type="submit" class="btn-save">
                <i class="fas fa-save"></i> Enregistrer les détails
            </button>
        </form>
    </div>

    <div class="card">
        <h2><i class="fas fa-eye"></i> Aperçu en direct</h2>
        <div class="preview-box">
            <?= $current ?: '<em>Aucune instruction définie.</em>' ?>
        </div>
    </div>

    <div style="margin-top: 50px; text-align: center; color: var(--text-dim); font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px;">
        Legrand &bull; Système de Paiement v2.4
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>

</body>
</html>