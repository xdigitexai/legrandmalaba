<?php
session_start();
require_once "../config/database.php";

/* 🔐 ADMIN GUARD */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/* =========================
   HANDLE USER ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_balance'])) {
        $db->prepare("UPDATE users SET balance = balance + ? WHERE id = ?")->execute([(float)$_POST['amount'], (int)$_POST['user_id']]);
    }
    if (isset($_POST['deduct_balance'])) {
        $db->prepare("UPDATE users SET balance = balance - ? WHERE id = ?")->execute([(float)$_POST['amount'], (int)$_POST['user_id']]);
    }
    if (isset($_POST['toggle_suspend'])) {
        $db->prepare("UPDATE users SET status = IF(status='active','suspended','active'), suspended_at = IF(status='active',NOW(),NULL) WHERE id = ?")->execute([(int)$_POST['user_id']]);
    }
    header("Location: users.php" . (isset($_GET['id']) ? "?id=".$_GET['id'] : ""));
    exit;
}

$viewUserId = (int)($_GET['id'] ?? 0);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>UTILISATEURS | Legrand</title>
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

.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
.header-left { display: flex; align-items: center; gap: 15px; }
.hamburger { font-size: 22px; cursor: pointer; color: var(--primary); }
.header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 28px; }

/* USER CARDS & TABLES */
.card-glass { 
    background: var(--surface); border-radius: 25px; border: 1px solid var(--border); 
    overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
}
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { background: rgba(0,0,0,0.2); padding: 20px; text-align: left; color: var(--text-dim); font-size: 11px; text-transform: uppercase; letter-spacing: 1px; }
td { padding: 18px 20px; border-bottom: 1px solid var(--border); }

.badge { padding: 5px 10px; border-radius: 8px; font-size: 10px; font-weight: 800; text-transform: uppercase; }
.bg-active { background: rgba(74, 222, 128, 0.1); color: var(--success); }
.bg-suspended { background: rgba(248, 113, 113, 0.1); color: var(--danger); }

.btn-action { background: var(--primary); color: #000; padding: 8px 15px; border-radius: 10px; text-decoration: none; font-size: 12px; font-weight: 800; transition: 0.3s; }
.btn-action:hover { box-shadow: 0 0 15px var(--primary-glow); transform: translateY(-2px); }

.btn-logout { border: 1px solid var(--danger); color: var(--danger); padding: 10px 20px; border-radius: 12px; text-decoration: none; font-size: 12px; font-weight: 800; transition: 0.3s; }
.btn-logout:hover { background: var(--danger); color: #fff; }

.input-dark { background: var(--bg); border: 1px solid var(--border); color: #fff; padding: 12px; border-radius: 12px; outline: none; width: 100%; }
.btn-submit { background: var(--primary); border: none; color: #000; padding: 12px; border-radius: 12px; cursor: pointer; font-weight: 800; text-transform: uppercase; width: 100%; transition: 0.3s; }

.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(4px); display: none; z-index: 1000; }
.overlay.show { display: block; }

.user-stat-card { background: var(--surface); padding: 30px; border-radius: 25px; border: 1px solid var(--border); }
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
        <a href="users.php" class="active"><i class="fas fa-user-friends"></i> Utilisateurs</a>
        <a href="deposits.php"><i class="fas fa-credit-card"></i> Dépôts</a>
        <a href="services.php"><i class="fas fa-stream"></i> Services</a>
        <a href="provider.php"><i class="fas fa-server"></i> Fournisseurs</a>
        <a href="appearance.php"><i class="fas fa-paint-brush"></i> Apparence</a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>
    </div>
</div>

<div class="main">
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>Gestion Utilisateurs</h1>
        </div>
        <a href="logout.php" class="btn-logout"><i class="fas fa-power-off"></i> DÉCONNEXION</a>
    </div>

    <?php if ($viewUserId > 0): 
        $stmt = $db->prepare("SELECT * FROM users WHERE id=?");
        $stmt->execute([$viewUserId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
    ?>
        <div style="margin-bottom: 25px;">
            <a href="users.php" style="color: var(--primary); text-decoration: none; font-size: 13px; font-weight: 700;"><i class="fas fa-chevron-left"></i> RETOUR À LA LISTE</a>
        </div>
        
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 25px;">
            <div class="user-stat-card">
                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                    <div>
                        <span style="color: var(--primary); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px;">Profil Utilisateur</span>
                        <h2 style="font-family: 'Space Grotesk', sans-serif; font-size: 24px; margin-top: 5px;"><?= htmlspecialchars($user['username']) ?></h2>
                    </div>
                    <span class="badge bg-<?= $user['status'] ?>"><?= $user['status'] ?></span>
                </div>
                <p style="color: var(--text-dim); margin-bottom: 20px; font-size: 14px;"><i class="fas fa-envelope"></i> <?= htmlspecialchars($user['email']) ?></p>
                
                <div style="background: var(--bg); padding: 20px; border-radius: 15px; border: 1px solid var(--border);">
                    <span style="font-size: 12px; color: var(--text-dim);">SOLDE ACTUEL</span>
                    <div style="font-family: 'Space Grotesk', sans-serif; font-size: 32px; font-weight: 700; color: var(--success);">$<?= number_format($user['balance'], 2) ?></div>
                </div>
            </div>

            <div class="user-stat-card">
                <h3 style="font-family: 'Space Grotesk', sans-serif; font-size: 18px; margin-bottom: 20px;"><i class="fas fa-wallet" style="color: var(--primary);"></i> Ajuster le solde</h3>
                <form method="POST" style="display: flex; flex-direction: column; gap: 15px;">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <input type="number" name="amount" step="0.01" class="input-dark" placeholder="Montant (USD)" required>
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="add_balance" class="btn-submit" style="background: var(--success);">AJOUTER</button>
                        <button type="submit" name="deduct_balance" class="btn-submit" style="background: var(--danger);">RETIRER</button>
                    </div>
                </form>
                
                <form method="POST" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid var(--border);">
                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                    <button type="submit" name="toggle_suspend" class="btn-submit" style="background: transparent; border: 1px solid var(--primary); color: var(--primary);">
                        <?= $user['status'] == 'active' ? 'SUSPENDRE LE COMPTE' : 'RÉACTIVER LE COMPTE' ?>
                    </button>
                </form>
            </div>
        </div>

    <?php else: 
        $users = $db->query("SELECT u.*, (SELECT COUNT(*) FROM orders WHERE user_id=u.id) as orders_count FROM users u ORDER BY u.id DESC")->fetchAll(PDO::FETCH_ASSOC);
    ?>
        <div class="card-glass">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Utilisateur</th>
                            <th>Solde</th>
                            <th>Commandes</th>
                            <th>Statut</th>
                            <th style="text-align: right;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $u): ?>
                        <tr>
                            <td style="font-family: monospace; color: var(--primary);">#<?= $u['id'] ?></td>
                            <td>
                                <div style="font-weight: 700; color: #fff;"><?= htmlspecialchars($u['username']) ?></div>
                                <div style="font-size: 11px; color: var(--text-dim)"><?= htmlspecialchars($u['email']) ?></div>
                            </td>
                            <td style="color: var(--success); font-weight: 800; font-family: 'Space Grotesk', sans-serif;">$<?= number_format($u['balance'], 2) ?></td>
                            <td><span style="background: rgba(255,255,255,0.05); padding: 3px 8px; border-radius: 5px;"><?= $u['orders_count'] ?></span></td>
                            <td><span class="badge bg-<?= $u['status'] ?>"><?= $u['status'] ?></span></td>
                            <td style="text-align: right;"><a href="users.php?id=<?= $u['id'] ?>" class="btn-action">GÉRER</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <footer style="margin-top: 50px; text-align: center; color: var(--text-dim); font-size: 12px; letter-spacing: 1px;">
        © <?= date('Y') ?> Legrand • ADMINISTRATION ENGINE
    </footer>
</div>

<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
</script>

</body>
</html>