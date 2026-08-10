<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/database.php";

/* 🔐 Admin guard */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/* ======================
   CHECK IF LOGO COLUMN EXISTS
====================== */
$columns = $db->query("
    SELECT COLUMN_NAME
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_NAME = 'settings'
")->fetchAll(PDO::FETCH_COLUMN);

$hasLogo = in_array('logo', $columns);
$message = $error = "";

/* ======================
   HANDLE LOGO UPLOAD
====================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['logo']) && $hasLogo) {
    $allowed = ['image/png','image/jpeg','image/jpg','image/svg+xml'];
    $file = $_FILES['logo'];

    if ($file['error'] === 0 && in_array($file['type'], $allowed)) {
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $newName = 'site-logo.' . $ext;

        $uploadDir = __DIR__ . '/../uploads/logo/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

        $path = $uploadDir . $newName;
        move_uploaded_file($file['tmp_name'], $path);

        $logoPath = 'uploads/logo/' . $newName;
        $stmt = $db->prepare("UPDATE settings SET logo = ? LIMIT 1");
        $stmt->execute([$logoPath]);

        $message = "Identité visuelle mise à jour avec succès.";
    } else {
        $error = "Type de fichier invalide. Utilisez PNG, JPG ou SVG.";
    }
}

/* ======================
   FETCH SETTINGS
====================== */
$logo = ($hasLogo) ? $db->query("SELECT logo FROM settings LIMIT 1")->fetchColumn() : '';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>APPARENCE | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root {
    --bg: #050507;
    --surface: #111114;
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.15);
    --success: #4ade80;
    --danger: #f87171;
    --warning: #fbbf24;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.05);
    --sidebar-w: 280px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
body { background-color: var(--bg); color: var(--text); font-family: 'Outfit', sans-serif; min-height: 100vh; display: flex; overflow-x: hidden; }

/* SIDEBAR */
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
.header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 28px; }

/* BRANDING CARD */
.card { 
    background: var(--surface); padding: 40px; border-radius: 30px; border: 1px solid var(--border); 
    max-width: 600px; margin: 0 auto; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
}
.card h2 { font-family: 'Space Grotesk', sans-serif; font-size: 20px; color: var(--primary); margin-bottom: 30px; display: flex; align-items: center; gap: 12px; text-transform: uppercase; }

/* UPLOAD UI */
.logo-preview-box {
    background: var(--bg); border: 2px dashed var(--border); border-radius: 20px;
    padding: 40px; text-align: center; margin-bottom: 30px; position: relative;
}
.logo-preview-box img { max-height: 100px; filter: drop-shadow(0 0 15px var(--primary-glow)); }
.logo-placeholder { color: var(--text-dim); font-size: 14px; }

label { font-size: 12px; color: var(--text-dim); font-weight: 800; display: block; margin-bottom: 12px; text-transform: uppercase; letter-spacing: 1px; }

input[type=file] {
    width: 100%; background: var(--bg); color: var(--text-dim);
    padding: 15px; border-radius: 15px; border: 1px solid var(--border); margin-bottom: 30px; font-size: 13px;
    cursor: pointer;
}

.btn-update {
    width: 100%; background: var(--primary); color: #000; border: none; padding: 18px;
    border-radius: 15px; font-weight: 800; cursor: pointer; transition: 0.3s; 
    text-transform: uppercase; letter-spacing: 1px;
}
.btn-update:hover { transform: translateY(-3px); box-shadow: 0 10px 20px var(--primary-glow); }

/* ALERTS */
.alert { padding: 18px; border-radius: 15px; margin-bottom: 25px; font-size: 14px; display: flex; align-items: center; gap: 12px; font-weight: 600; }
.alert-success { background: rgba(74, 222, 128, 0.1); color: var(--success); border: 1px solid rgba(74, 222, 128, 0.2); }
.alert-danger { background: rgba(248, 113, 113, 0.1); color: var(--danger); border: 1px solid rgba(248, 113, 113, 0.2); }
.alert-notice { background: rgba(251, 191, 36, 0.1); color: var(--warning); border: 1px solid rgba(251, 191, 36, 0.2); }

.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); display: none; z-index: 1000; }
.overlay.show { display: block; }

@media (max-width: 600px) { .card { padding: 25px; } }
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
        <a href="provider.php"><i class="fas fa-server"></i> Fournisseurs</a>
        <a href="appearance.php" class="active"><i class="fas fa-paint-brush"></i> Apparence</a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>
    </div>
</div>

<div class="main">
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>Identité Visuelle</h1>
        </div>
        <a href="logout.php" style="color:var(--danger); text-decoration:none; font-weight:800; font-size:12px;"><i class="fas fa-power-off"></i> DECONNEXION</a>
    </div>

    <div class="card">
        <h2><i class="fas fa-palette"></i> Configuration du Logo</h2>

        <?php if (!$hasLogo): ?>
            <div class="alert alert-notice">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Action requise : La colonne 'logo' est absente de la table 'settings'.</span>
            </div>
        <?php endif; ?>

        <?php if ($message): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $message ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div>
        <?php endif; ?>

        <label>Logo Actuel du Site</label>
        <div class="logo-preview-box">
            <?php if ($logo): ?>
                <img src="/<?= htmlspecialchars($logo) ?>" alt="Logo Site">
            <?php else: ?>
                <div class="logo-placeholder">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 40px; margin-bottom: 15px; display: block; color: var(--primary);"></i>
                    Aucun logo configuré.
                </div>
            <?php endif; ?>
        </div>

        <?php if ($hasLogo): ?>
        <form method="post" enctype="multipart/form-data">
            <label>Télécharger un nouveau fichier (PNG, JPG, SVG)</label>
            <input type="file" name="logo" accept=".png,.jpg,.jpeg,.svg" required>
            <button type="submit" class="btn-update">Mettre à jour le branding</button>
        </form>
        <?php endif; ?>
    </div>
</div>

<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>
</body>
</html>