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
    SOLDE UTILISATEUR
========================= */
$stmtBal = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmtBal->execute([$userId]);
$userBal = $stmtBal->fetch(PDO::FETCH_ASSOC);
$userBalance = $userBal['balance'] ?? 0.00;

/* =========================
    HISTORIQUE DES COMMANDES
========================= */
// Pulled from o.service_name first to ensure data persistence
$stmt = $db->prepare("
    SELECT 
        o.order_id as id,
        COALESCE(s.service_name, 'Service supprimé') AS service,
        o.order_url as link,
        o.order_quantity as quantity,
        o.order_start as start_count,
        o.order_charge as price,
        o.order_status as status,
        o.order_create as created_at
    FROM orders o
    LEFT JOIN services s ON s.service_id = o.service_id
    WHERE o.client_id = ?
    ORDER BY o.order_create DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Historique | Legrand</title>
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
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: var(--bg); color: var(--text); overflow-x: hidden; min-height: 100vh; }

/* SIDEBAR */
.sidebar { position: fixed; left: calc(-1 * var(--sidebar-width)); top: 0; width: var(--sidebar-width); height: 100%; background: var(--surface); z-index: 10000; transition: 0.4s cubic-bezier(0.4, 0, 0.2, 1); border-right: 1px solid var(--border); display: flex; flex-direction: column; }
.sidebar.active { left: 0; }
.sidebar-header { padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--border); }
.sidebar-header img { height: 50px; border-radius: 10px; margin-bottom: 10px; }
.logo-txt { font-weight: 800; color: var(--primary); font-size: 18px; letter-spacing: -1px; }

.sidebar-menu { flex: 1; padding: 25px 0; }
.sidebar-menu a { display: flex; align-items: center; gap: 15px; padding: 15px 30px; color: var(--text-dim); text-decoration: none; font-weight: 600; transition: 0.2s; }
.sidebar-menu a:hover, .sidebar-menu a.active { color: var(--primary); background: var(--primary-glow); border-right: 4px solid var(--primary); }

/* TOP NAV */
.top-nav { display: flex; justify-content: space-between; align-items: center; padding: 15px 5%; background: rgba(5, 5, 7, 0.8); backdrop-filter: blur(15px); position: sticky; top: 0; z-index: 999; border-bottom: 1px solid var(--border); }
.nav-left { display: flex; align-items: center; gap: 20px; }
.menu-toggle { font-size: 24px; color: var(--primary); cursor: pointer; }
.balance-card { background: var(--primary); padding: 10px 20px; border-radius: 50px; font-weight: 800; font-size: 14px; color: #000; box-shadow: 0 10px 20px rgba(255, 170, 0, 0.2); }

/* CONTENT & TABLE */
.main-content { padding: 40px 20px; max-width: 1200px; margin: auto; }
.box { background: var(--surface); border-radius: 30px; padding: 30px; border: 1px solid var(--border); box-shadow: 0 40px 100px rgba(0,0,0,0.4); overflow-x: auto; }
h2 { margin-bottom: 30px; font-size: 24px; font-weight: 800; display: flex; align-items: center; gap: 12px; letter-spacing: -1px; }

table { width: 100%; border-collapse: collapse; font-size: 14px; min-width: 900px; }
th { background: rgba(255,255,255,0.02); padding: 18px 15px; text-align: left; font-weight: 800; color: var(--primary); text-transform: uppercase; font-size: 11px; letter-spacing: 1px; border-bottom: 1px solid var(--border); }
td { padding: 20px 15px; border-bottom: 1px solid var(--border); vertical-align: middle; color: var(--text-dim); }

/* STATUS BADGES */
.status { font-weight: 800; font-size: 10px; padding: 5px 12px; border-radius: 50px; display: inline-block; text-transform: uppercase; letter-spacing: 0.5px; }
.processing { background: rgba(37, 99, 235, 0.1); color: #60a5fa; border: 1px solid rgba(37, 99, 235, 0.2); }
.completed { background: rgba(22, 163, 74, 0.1); color: #4ade80; border: 1px solid rgba(22, 163, 74, 0.2); }
.pending { background: rgba(202, 138, 4, 0.1); color: #fbbf24; border: 1px solid rgba(202, 138, 4, 0.2); }
.failed { background: rgba(220, 38, 38, 0.1); color: #f87171; border: 1px solid rgba(220, 38, 38, 0.2); }
.cancelled { background: rgba(255, 255, 255, 0.05); color: #94a3b8; border: 1px solid rgba(255, 255, 255, 0.1); }

.link-cell { max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.link { color: var(--primary); text-decoration: none; font-weight: 600; font-size: 13px; }
.link:hover { text-decoration: underline; }

.overlay { position: fixed; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.7); z-index: 9999; display:none; backdrop-filter: blur(5px); }
.overlay.active { display:block; }

.order-id { font-family: monospace; color: var(--text); font-weight: 700; background: rgba(255,255,255,0.05); padding: 4px 8px; border-radius: 6px; }
.badge-unit { font-family: monospace; background: rgba(255,255,255,0.03); padding: 4px 8px; border-radius: 6px; color: var(--text); border: 1px solid var(--border); }

@media (max-width: 768px) { .box { padding: 20px; border-radius: 20px; } }
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
        <a href="my_orders.php" class="active"><i class="fas fa-history"></i> Historique</a>
        <a href="add-funds.php"><i class="fas fa-wallet"></i> Ajouter des fonds</a>
        <a href="services.php"><i class="fas fa-list-ul"></i> Services</a>
        <a href="api.php"><i class="fas fa-code"></i> API</a>
        <a href="profile.php"><i class="fas fa-user-shield"></i> Profil</a>
        <a href="logout.php" style="color: #f87171; margin-top: 50px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </div>
</div>

<div class="top-nav">
    <div class="nav-left">
        <i class="fas fa-align-left menu-toggle" id="toggleBtn"></i>
        <div style="font-weight: 800; font-size: 20px; letter-spacing: -1px;">Historique</div>
    </div>
    <div class="balance-card">
        Solde: <?= number_format($userBalance, 2) ?> $
    </div>
</div>

<div class="main-content">
    <div class="box">
        <h2><i class="fas fa-receipt" style="color: var(--primary);"></i> Mes Commandes</h2>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Service</th>
                    <th>Lien</th>
                    <th>Quantité</th>
                    <th>Début</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!$orders): ?>
                <tr>
                    <td colspan="8" style="text-align:center; padding: 60px; color: var(--text-dim); font-size: 16px;">
                        <i class="fas fa-inbox" style="display:block; font-size: 40px; margin-bottom: 15px; opacity: 0.2;"></i>
                        Aucune commande pour le moment.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($orders as $o): ?>
                    <tr>
                        <td><span class="order-id">#<?= (int)$o['id'] ?></span></td>
                        <td style="max-width: 280px; font-size: 13px; line-height: 1.5; color: var(--text); font-weight: 600;">
                            <?= htmlspecialchars($o['service']) ?>
                        </td>
                        <td class="link-cell">
                            <a href="<?= htmlspecialchars($o['link']) ?>" target="_blank" class="link">
                                <i class="fas fa-external-link-alt" style="font-size: 10px;"></i> Voir le lien
                            </a>
                        </td>
                        <td style="font-weight: 700; color: var(--text);"><?= number_format($o['quantity']) ?></td>
                        <td><span class="badge-unit"><?= number_format($o['start_count'] ?? 0) ?></span></td>
                        <td style="font-weight: 800; color: var(--primary);"><?= number_format($o['price'], 2) ?> $</td>
                        <td>
                            <span class="status <?= strtolower($o['status']) ?>">
                                <?= ucfirst($o['status']) ?>
                            </span>
                        </td>
                        <td style="color: var(--text-dim); font-size: 12px; font-weight: 500;">
                            <i class="far fa-calendar-alt"></i> <?= date("d M Y", strtotime($o['created_at'])) ?><br>
                            <span style="opacity: 0.5;"><i class="far fa-clock"></i> <?= date("H:i", strtotime($o['created_at'])) ?></span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
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