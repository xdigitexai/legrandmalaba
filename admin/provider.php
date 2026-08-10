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

/* =========================
   BALANCE FETCHER
========================= */
function fetchBalance($apiUrl, $apiKey)
{
    if (!$apiUrl || !$apiKey) return 'N/A';
    $postData = ['key' => $apiKey, 'action' => 'balance'];
    $ch = curl_init($apiUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($postData),
        CURLOPT_TIMEOUT => 15,
        CURLOPT_SSL_VERIFYPEER => false
    ]);
    $response = curl_exec($ch);
    if (curl_errno($ch)) { curl_close($ch); return 'Conn. Error'; }
    curl_close($ch);
    $json = json_decode($response, true);
    if (!$json) return 'Invalid JSON';
    if (isset($json['balance'])) return $json['balance'];
    if (isset($json['data']['balance'])) return $json['data']['balance'];
    return 'Error';
}

/* =========================
   ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_provider'])) {
        $stmt = $db->prepare("INSERT INTO providers (name, api_url, api_key, active) VALUES (?, ?, ?, 1)");
        $stmt->execute([trim($_POST['name']), trim($_POST['api_url']), trim($_POST['api_key'])]);
        header("Location: provider.php"); exit;
    }
    if (isset($_POST['update_provider'])) {
        $id = (int)$_POST['id'];
        $active = isset($_POST['active']) ? 1 : 0;
        $stmt = $db->prepare("UPDATE providers SET name=?, api_url=?, api_key=?, active=? WHERE id=?");
        $stmt->execute([trim($_POST['name']), trim($_POST['api_url']), trim($_POST['api_key']), $active, $id]);
        header("Location: provider.php"); exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM services WHERE provider_id=?")->execute([$id]);
    $db->prepare("DELETE FROM providers WHERE id=?")->execute([$id]);
    header("Location: provider.php"); exit;
}

$providers = $db->query("SELECT p.*, (SELECT COUNT(*) FROM services s WHERE s.provider_id=p.id) AS total_services FROM providers p ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>FOURNISSEURS | Legrand</title>
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

/* SIDEBAR ESCA MOTABLE */
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

/* CONTENT BOXES */
.grid { display: grid; grid-template-columns: 1fr 380px; gap: 30px; }
@media (max-width: 1200px) { .grid { grid-template-columns: 1fr; } }

.card { background: var(--surface); border-radius: 25px; border: 1px solid var(--border); overflow: hidden; }
.card-header { padding: 25px; border-bottom: 1px solid var(--border); }
.card-header h3 { font-family: 'Space Grotesk', sans-serif; font-size: 18px; color: var(--primary); text-transform: uppercase; }

/* TABLES */
table { width: 100%; border-collapse: collapse; }
th { text-align: left; color: var(--text-dim); font-size: 11px; text-transform: uppercase; padding: 20px; background: rgba(0,0,0,0.2); letter-spacing: 1px; }
td { padding: 20px; border-bottom: 1px solid var(--border); font-size: 14px; }

/* FORMS */
.form-body { padding: 25px; }
label { display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 8px; font-weight: 700; text-transform: uppercase; }
input { 
    width: 100%; background: var(--bg); border: 1px solid var(--border); color: white; 
    padding: 14px; border-radius: 12px; margin-bottom: 20px; outline: none; transition: 0.3s;
}
input:focus { border-color: var(--primary); box-shadow: 0 0 10px var(--primary-glow); }

.btn-primary { background: var(--primary); color: #000; border: none; padding: 15px; border-radius: 12px; font-weight: 800; cursor: pointer; width: 100%; text-transform: uppercase; }
.btn-action { background: rgba(255,255,255,0.05); border: 1px solid var(--border); color: #fff; padding: 8px 12px; border-radius: 8px; text-decoration: none; font-size: 11px; font-weight: 700; transition: 0.2s; }
.btn-action:hover { background: var(--primary); color: #000; }

.status-badge { font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 6px; }
.active-status { background: rgba(74, 222, 128, 0.1); color: var(--success); }
.off-status { background: rgba(248, 113, 113, 0.1); color: var(--danger); }

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
        <a href="provider.php" class="active"><i class="fas fa-server"></i> Fournisseurs</a>
        <a href="settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>
    </div>
</div>

<div class="main">
    <div class="header">
        <div class="header-left">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>Sources API</h1>
        </div>
        <a href="logout.php" style="color:var(--danger); text-decoration:none; font-weight:800; font-size:12px;"><i class="fas fa-power-off"></i> DÉCONNEXION</a>
    </div>

    <div class="grid">
        <div class="card">
            <div class="card-header">
                <h3>Fournisseurs Connectés</h3>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Fournisseur</th>
                            <th>Statut</th>
                            <th>Solde API</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($providers as $p): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700; color:#fff;"><?= htmlspecialchars($p['name']) ?></div>
                                <div style="font-size:11px; color:var(--text-dim);"><?= $p['total_services'] ?> Services importés</div>
                            </td>
                            <td>
                                <span class="status-badge <?= $p['active']?'active-status':'off-status' ?>">
                                    <?= $p['active']?'ACTIF':'INACTIF' ?>
                                </span>
                            </td>
                            <td style="color:var(--success); font-family:'Space Grotesk'; font-weight:700;">
                                <?= $p['active'] ? '$'.number_format((float)fetchBalance($p['api_url'],$p['api_key']), 2) : '—' ?>
                            </td>
                            <td>
                                <a href="?edit=<?= $p['id'] ?>" class="btn-action">Éditer</a>
                                <a href="?delete=<?= $p['id'] ?>" class="btn-action" style="color:var(--danger);" onclick="return confirm('Supprimer ce fournisseur ?')">Suppr.</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <?php if (isset($_GET['edit'])): 
                $stmt = $db->prepare("SELECT * FROM providers WHERE id=?");
                $stmt->execute([(int)$_GET['edit']]);
                $prov = $stmt->fetch(PDO::FETCH_ASSOC);
            ?>
                <div class="card-header"><h3><i class="fas fa-edit"></i> Modifier</h3></div>
                <form method="post" class="form-body">
                    <input type="hidden" name="id" value="<?= $prov['id'] ?>">
                    <label>Nom du site</label>
                    <input name="name" value="<?= htmlspecialchars($prov['name']) ?>" required>
                    <label>URL de l'API</label>
                    <input name="api_url" value="<?= htmlspecialchars($prov['api_url']) ?>" required>
                    <label>Clé API (Key)</label>
                    <input name="api_key" value="<?= htmlspecialchars($prov['api_key']) ?>" required>
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer; color:var(--text-dim);">
                        <input type="checkbox" name="active" style="width:auto; margin:0;" <?= $prov['active']?'checked':'' ?>> 
                        Fournisseur Actif
                    </label>
                    <button type="submit" name="update_provider" class="btn-primary" style="margin-top:20px;">Sauvegarder</button>
                    <div style="text-align:center; margin-top:15px;"><a href="provider.php" style="color:var(--text-dim); font-size:12px; text-decoration:none;">Annuler</a></div>
                </form>
            <?php else: ?>
                <div class="card-header"><h3><i class="fas fa-plus-circle"></i> Nouveau</h3></div>
                <form method="post" class="form-body">
                    <label>Nom (ex: SMM Panel)</label>
                    <input name="name" placeholder="Nom du fournisseur" required>
                    <label>Lien API</label>
                    <input name="api_url" placeholder="https://site.com/api/v2" required>
                    <label>Clé API</label>
                    <input name="api_key" placeholder="Votre clé secrète" required>
                    <button type="submit" name="add_provider" class="btn-primary">Connecter</button>
                </form>
            <?php endif; ?>
        </div>
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