<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/database.php";

/* =========================
    ADMIN GUARD
========================= */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = "";
$success = "";

/* =========================
    HANDLE BROADCAST
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $content = trim($_POST['message'] ?? '');
    $target  = $_POST['target'] ?? 'all';

    if ($content === '') {
        $message = "Le message ne peut pas être vide.";
    } else {
        try {
            if ($target === 'all') {
                $users = $db->query("SELECT id FROM users")->fetchAll(PDO::FETCH_COLUMN);
                $stmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                foreach ($users as $uid) {
                    $stmt->execute([$uid, $content]);
                }
                $success = "Diffusion envoyée avec succès à tous les utilisateurs.";
            } else {
                $userId = (int)$target;
                $stmt = $db->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)");
                $stmt->execute([$userId, $content]);
                $success = "Notification privée envoyée à l'utilisateur sélectionné.";
            }
        } catch (Exception $e) {
            $message = "Erreur base de données : " . $e->getMessage();
        }
    }
}

/* =========================
    FETCH USERS FOR SELECT
========================= */
$users = $db->query("SELECT id, username, email FROM users ORDER BY username ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Diffusion | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700;800&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">

<style>
:root {
    --bg: #050507;
    --surface: #111114;
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.1);
    --text-white: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.05);
    --sidebar-width: 280px;
    --success: #4ade80;
    --danger: #f87171;
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: var(--bg); color: var(--text-white); min-height: 100vh; display: flex; overflow-x: hidden; }

/* SIDEBAR (Unified Design) */
.sidebar {
    width: var(--sidebar-width); background: var(--surface); padding: 30px 20px; position: fixed;
    height: 100vh; left: 0; top: 0; border-right: 1px solid var(--border); transition: 0.3s; z-index: 1001;
    display: flex; flex-direction: column;
}
.sidebar-logo { display: flex; align-items: center; gap: 12px; padding: 0 10px 40px; text-decoration: none; }
.logo-circle { width: 45px; height: 45px; background: var(--bg); border-radius: 12px; display: flex; align-items: center; justify-content: center; border: 1px solid var(--border); }
.logo-circle img { width: 30px; }
.logo-text { font-family: 'Space Grotesk', sans-serif; font-size: 20px; font-weight: 700; color: #fff; letter-spacing: -1px; }
.logo-text span { color: var(--primary); }

.sidebar a {
    display: flex; align-items: center; gap: 12px; color: var(--text-dim); text-decoration: none;
    padding: 14px 18px; border-radius: 15px; margin-bottom: 5px; font-size: 14px; font-weight: 600; transition: 0.2s;
}
.sidebar a:hover { color: #fff; background: rgba(255,255,255,0.03); }
.sidebar a.active { background: var(--primary-glow); color: var(--primary); border: 1px solid rgba(255, 170, 0, 0.1); }

/* MAIN AREA */
.main { flex: 1; padding: 40px; margin-left: var(--sidebar-width); transition: 0.3s; width: calc(100% - var(--sidebar-width)); }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; }
.header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 28px; font-weight: 700; letter-spacing: -1px; }

.card { 
    background: var(--surface); padding: 45px; border-radius: 30px; border: 1px solid var(--border); 
    box-shadow: 0 30px 60px rgba(0,0,0,0.4); max-width: 700px; margin: 0 auto;
}

.card h2 { font-family: 'Space Grotesk', sans-serif; font-size: 24px; color: var(--primary); margin-bottom: 12px; display: flex; align-items: center; gap: 15px; text-transform: uppercase; letter-spacing: 1px; }
.card p { color: var(--text-dim); font-size: 15px; margin-bottom: 35px; line-height: 1.6; }

label { display: block; font-size: 11px; color: var(--text-dim); margin-bottom: 10px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; }

select, textarea {
    width: 100%; background: var(--bg); border: 1px solid var(--border); color: white;
    padding: 16px; border-radius: 15px; margin-bottom: 25px; outline: none; transition: 0.3s; font-size: 15px;
}
textarea { height: 180px; resize: none; line-height: 1.5; }
select:focus, textarea:focus { border-color: var(--primary); box-shadow: 0 0 20px var(--primary-glow); }

.btn-send { 
    width: 100%; background: var(--primary); color: #000; border: none; padding: 18px; 
    border-radius: 15px; font-weight: 800; cursor: pointer; transition: 0.3s; display: flex; align-items: center; justify-content: center; gap: 12px;
    text-transform: uppercase; letter-spacing: 1px;
}
.btn-send:hover { transform: translateY(-3px); box-shadow: 0 15px 30px var(--primary-glow); }

/* ALERTS */
.alert { padding: 18px; border-radius: 15px; margin-bottom: 30px; font-size: 14px; font-weight: 700; display: flex; align-items: center; gap: 12px; border: 1px solid transparent; }
.alert-success { background: rgba(74, 222, 128, 0.1); color: var(--success); border-color: rgba(74, 222, 128, 0.2); }
.alert-error { background: rgba(248, 113, 113, 0.1); color: var(--danger); border-color: rgba(248, 113, 113, 0.2); }

@media (max-width: 1024px) {
    .sidebar { transform: translateX(-100%); }
    .main { margin-left: 0; width: 100%; padding: 25px; }
}
</style>
</head>

<body>

<div class="sidebar">
    <a href="dashboard.php" class="sidebar-logo">
        <div class="logo-circle">
            <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Logo">
        </div>
        <div class="logo-text">Legrand</div>
    </a>
    <a href="dashboard.php"><i class="fas fa-layer-group"></i> Dashboard</a>
    <a href="orders.php"><i class="fas fa-shopping-bag"></i> Commandes</a>
    <a href="users.php"><i class="fas fa-user-friends"></i> Utilisateurs</a>
    <a href="deposits.php"><i class="fas fa-credit-card"></i> Dépôts</a>
    <a href="services.php"><i class="fas fa-stream"></i> Services</a>
    <a href="broadcast.php" class="active"><i class="fas fa-bullhorn"></i> Diffusion</a>
    <a href="settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>
</div>

<div class="main">
    <div class="header">
        <h1>Communication Globale</h1>
        <a href="logout.php" style="color:var(--danger); text-decoration:none; font-weight:700; font-size:18px;"><i class="fas fa-power-off"></i></a>
    </div>

    <div class="card">
        <h2><i class="fas fa-paper-plane"></i> Envoyer une annonce</h2>
        <p>Votre message apparaîtra instantanément dans le centre de notifications des utilisateurs ciblés.</p>

        <?php if ($message): ?>
            <div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post">
            <label>Cible de l'audience</label>
            <select name="target">
                <option value="all">📢 Tous les utilisateurs inscrits</option>
                <optgroup label="Utilisateurs individuels">
                    <?php foreach ($users as $u): ?>
                        <option value="<?= $u['id'] ?>">
                            👤 <?= htmlspecialchars($u['username']) ?> (<?= htmlspecialchars($u['email']) ?>)
                        </option>
                    <?php endforeach; ?>
                </optgroup>
            </select>

            <label>Contenu du message</label>
            <textarea name="message" placeholder="Saisissez votre annonce ou alerte urgente ici..." required></textarea>

            <button type="submit" class="btn-send">
                <i class="fas fa-rocket"></i> Pousser la notification
            </button>
        </form>
    </div>
</div>

</body>
</html>