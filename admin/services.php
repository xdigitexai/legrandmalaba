<?php
session_start();
require_once "../config/database.php";

/* 🔐 PROTECT ADMIN */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/* =====================
   SINGLE DELETE
===================== */
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    if ($id > 0) {
        $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$id]);
    }
    $qs = $_GET;
    unset($qs['delete']);
    header("Location: services.php" . (!empty($qs) ? ("?" . http_build_query($qs)) : ""));
    exit;
}

/* =====================
   BULK ACTIONS
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['service_ids']) && !empty($_POST['action'])) {
    $ids = array_values(array_filter(array_map('intval', (array)$_POST['service_ids']), fn($v)=>$v>0));
    if ($ids) {
        $in = implode(',', array_fill(0, count($ids), '?'));
        $action = $_POST['action'];

        if ($action === 'activate') {
            $db->prepare("UPDATE services SET active = 1 WHERE id IN ($in)")->execute($ids);
        }
        if ($action === 'deactivate') {
            $db->prepare("UPDATE services SET active = 0 WHERE id IN ($in)")->execute($ids);
        }
        if ($action === 'delete') {
            $db->prepare("DELETE FROM services WHERE id IN ($in)")->execute($ids);
        }
    }
    $qs = $_GET ?? [];
    header("Location: services.php" . (!empty($qs) ? ("?" . http_build_query($qs)) : ""));
    exit;
}

/* =====================
   FETCH DATA
===================== */
$providers = $db->query("SELECT id, name FROM providers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);

$where = [];
$params = [];
$statusFilter   = $_GET['status'] ?? '';
$providerFilter = (int)($_GET['provider_id'] ?? 0);
$platformFilter = trim($_GET['platform'] ?? '');

if ($statusFilter === 'active')   { $where[] = "s.active = 1"; }
if ($statusFilter === 'inactive') { $where[] = "s.active = 0"; }
if ($providerFilter > 0)          { $where[] = "s.provider_id = ?"; $params[] = $providerFilter; }
if ($platformFilter !== '')       { $where[] = "s.platform = ?"; $params[] = $platformFilter; }

$sql = "SELECT s.*, p.name AS provider_name FROM services s LEFT JOIN providers p ON p.id = s.provider_id";
if ($where) { $sql .= " WHERE " . implode(" AND ", $where); }
$sql .= " ORDER BY s.id DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll(PDO::FETCH_ASSOC);

$platforms = ['instagram','facebook','youtube','tiktok','twitter','telegram','spotify','linkedin','whatsapp','website','other'];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>SERVICES | Legrand</title>
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

.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
.header-left { display: flex; align-items: center; gap: 15px; }
.hamburger { font-size: 22px; cursor: pointer; color: var(--primary); }
.header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 28px; }

/* FILTERS & MODULES */
.control-panel { 
    display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; 
}
.module { background: var(--surface); padding: 15px; border-radius: 18px; border: 1px solid var(--border); }
.module-title { font-size: 11px; text-transform: uppercase; color: var(--primary); font-weight: 800; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }

select, .btn-action {
    width: 100%; background: var(--bg); border: 1px solid var(--border); color: #fff;
    padding: 12px; border-radius: 10px; font-size: 13px; outline: none; cursor: pointer;
}
.btn-primary { background: var(--primary); color: #000; border: none; font-weight: 800; text-transform: uppercase; }

/* TABLE */
.card-table { background: var(--surface); border-radius: 25px; border: 1px solid var(--border); overflow: hidden; }
table { width: 100%; border-collapse: collapse; font-size: 13px; }
th { text-align: left; color: var(--text-dim); font-size: 11px; text-transform: uppercase; padding: 20px; background: rgba(0,0,0,0.2); letter-spacing: 1px; }
td { padding: 18px 20px; border-bottom: 1px solid var(--border); vertical-align: middle; }

.service-title { font-weight: 700; color: #fff; display: block; }
.service-cat { font-size: 10px; color: var(--primary); text-transform: uppercase; }

.badge { padding: 4px 10px; border-radius: 6px; font-size: 10px; font-weight: 800; }
.badge-active { background: rgba(74, 222, 128, 0.1); color: var(--success); }
.badge-inactive { background: rgba(248, 113, 113, 0.1); color: var(--danger); }

.price { font-family: 'Space Grotesk', sans-serif; font-weight: 700; color: var(--success); }

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
        <a href="services.php" class="active"><i class="fas fa-stream"></i> Services</a>
        <a href="provider.php"><i class="fas fa-server"></i> Fournisseurs</a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>
    </div>
</div>

<div class="main">
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>Gestion des Services</h1>
        </div>
        <a href="logout.php" style="color:var(--danger); text-decoration:none; font-weight:800; font-size:12px;">DÉCONNEXION</a>
    </div>

    <form method="get" class="control-panel">
        <div class="module">
            <div class="module-title"><i class="fas fa-filter"></i> État</div>
            <select name="status" onchange="this.form.submit()">
                <option value="">Tous les statuts</option>
                <option value="active" <?= $statusFilter==='active'?'selected':'' ?>>Actifs uniquement</option>
                <option value="inactive" <?= $statusFilter==='inactive'?'selected':'' ?>>Inactifs uniquement</option>
            </select>
        </div>
        <div class="module">
            <div class="module-title"><i class="fas fa-server"></i> Fournisseur</div>
            <select name="provider_id" onchange="this.form.submit()">
                <option value="">Tous les fournisseurs</option>
                <?php foreach($providers as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= $providerFilter===(int)$p['id']?'selected':'' ?>>
                        <?= htmlspecialchars($p['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="module">
            <div class="module-title"><i class="fas fa-hashtag"></i> Plateforme</div>
            <select name="platform" onchange="this.form.submit()">
                <option value="">Toutes les plateformes</option>
                <?php foreach($platforms as $pl): ?>
                    <option value="<?= htmlspecialchars($pl) ?>" <?= $platformFilter===$pl?'selected':'' ?>><?= ucfirst($pl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <form method="post">
        <div class="module" style="margin-bottom: 25px; display: flex; align-items: center; gap: 15px; border-color: var(--primary-glow);">
            <div style="flex: 1;">
                <div class="module-title" style="margin-bottom: 5px;"><i class="fas fa-bolt"></i> Actions groupées</div>
                <p style="font-size: 11px; color: var(--text-dim);">Sélectionnez des services dans le tableau</p>
            </div>
            <select name="action" style="width: 200px;" required>
                <option value="">Choisir une action...</option>
                <option value="activate">Activer la sélection</option>
                <option value="deactivate">Désactiver la sélection</option>
                <option value="delete">Supprimer définitivement</option>
            </select>
            <button type="submit" class="btn-primary btn-action" style="width: 150px;">Appliquer</button>
        </div>

        <div class="card-table">
            <div style="overflow-x: auto;">
                <table>
                    <thead>
                        <tr>
                            <th style="width: 50px;"><input type="checkbox" onclick="toggleAll(this)"></th>
                            <th style="width: 80px;">ID</th>
                            <th>Service & Catégorie</th>
                            <th>Réseau</th>
                            <th>Min / Max</th>
                            <th>Prix (USD)</th>
                            <th>Statut</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($services as $s): ?>
                        <tr>
                            <td><input type="checkbox" name="service_ids[]" value="<?= (int)$s['id'] ?>"></td>
                            <td style="font-family: monospace; color: var(--primary);">#<?= (int)$s['id'] ?></td>
                            <td>
                                <span class="service-title"><?= htmlspecialchars($s['name']) ?></span>
                                <span class="service-cat"><?= htmlspecialchars($s['category'] ?? 'Général') ?></span>
                            </td>
                            <td style="text-transform: capitalize;">
                                <i class="fab fa-<?= strtolower($s['platform'] ?? 'circle-notch') ?>" style="color: var(--primary); margin-right: 5px;"></i>
                                <?= htmlspecialchars($s['platform'] ?? 'Autre') ?>
                            </td>
                            <td style="color: var(--text-dim);">
                                <b style="color:#fff"><?= number_format((int)($s['min'] ?? 0)) ?></b> / <?= number_format((int)($s['max'] ?? 0)) ?>
                            </td>
                            <td class="price">$<?= number_format((float)($s['cost_usd'] ?? 0), 4) ?></td>
                            <td>
                                <span class="badge <?= !empty($s['active']) ? 'badge-active' : 'badge-inactive' ?>">
                                    <?= !empty($s['active']) ? 'ACTIF' : 'OFF' ?>
                                </span>
                            </td>
                            <td>
                                <a href="services.php?delete=<?= (int)$s['id'] ?>" style="color: var(--danger);" onclick="return confirm('Supprimer ce service ?')">
                                    <i class="fas fa-trash-alt"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if(!$services): ?>
                            <tr><td colspan="8" style="text-align:center; padding:50px; color:var(--text-dim);">Aucun service trouvé.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.getElementById('overlay').classList.toggle('show');
}
function toggleAll(source){
    document.querySelectorAll("input[name='service_ids[]']").forEach(cb => cb.checked = source.checked);
}
</script>
</body>
</html>