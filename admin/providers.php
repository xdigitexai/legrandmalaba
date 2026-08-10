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
   BALANCE FETCHER (CURL VERSION)
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
   ACTIONS (ADD/UPDATE/DELETE)
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_provider'])) {
        $stmt = $db->prepare("INSERT INTO providers (name, api_url, api_key, active) VALUES (?, ?, ?, 1)");
        $stmt->execute([trim($_POST['name']), trim($_POST['api_url']), trim($_POST['api_key'])]);
        header("Location: providers.php"); exit;
    }
    if (isset($_POST['update_provider'])) {
        $id = (int)$_POST['id'];
        $active = isset($_POST['active']) ? 1 : 0;
        $stmt = $db->prepare("UPDATE providers SET name=?, api_url=?, api_key=?, active=? WHERE id=?");
        $stmt->execute([trim($_POST['name']), trim($_POST['api_url']), trim($_POST['api_key']), $active, $id]);
        if ($active === 0) { $db->prepare("UPDATE services SET active=0 WHERE provider_id=?")->execute([$id]); }
        header("Location: providers.php"); exit;
    }
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $db->prepare("DELETE FROM services WHERE provider_id=?")->execute([$id]);
    $db->prepare("DELETE FROM providers WHERE id=?")->execute([$id]);
    header("Location: providers.php"); exit;
}

$providers = $db->query("SELECT p.*, (SELECT COUNT(*) FROM services s WHERE s.provider_id=p.id) AS total_services FROM providers p ORDER BY p.id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Providers | Global Media Boost</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
:root {
    --bg-dark: #0f172a;
    --card-dark: #1e293b;
    --primary: #4f46e5;
    --primary-glow: rgba(79, 70, 229, 0.2);
    --text-white: #f8fafc;
    --text-dim: #94a3b8;
    --border: #334155;
    --sidebar-width: 260px;
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: var(--bg-dark); color: var(--text-white); min-height: 100vh; display: flex; overflow-x: hidden; }

/* SIDEBAR */
.sidebar {
    width: var(--sidebar-width); background: var(--card-dark); padding: 25px 15px; position: fixed;
    height: 100vh; left: 0; top: 0; border-right: 1px solid var(--border); transition: 0.3s; z-index: 1001;
}
.sidebar-brand { text-align: center; margin-bottom: 30px; padding-bottom: 20px; border-bottom: 1px solid var(--border); }
.sidebar-brand img { height: 45px; border-radius: 8px; margin-bottom: 10px; }
.sidebar-brand h2 { font-size: 18px; color: var(--primary); font-weight: 800; letter-spacing: 1px; }

.sidebar a {
    display: flex; align-items: center; gap: 12px; color: var(--text-dim); text-decoration: none;
    padding: 12px 15px; border-radius: 12px; margin-bottom: 5px; font-size: 14px; font-weight: 500; transition: 0.2s;
}
.sidebar a:hover, .sidebar a.active { background: var(--primary-glow); color: var(--text-white); }
.sidebar a.active { border-left: 4px solid var(--primary); }

/* MAIN AREA */
.main { flex: 1; padding: 30px; margin-left: var(--sidebar-width); transition: 0.3s; }
.header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 35px; }
.header h1 { font-size: 24px; font-weight: 700; }
.hamburger { display: none; font-size: 24px; cursor: pointer; color: var(--text-white); }

/* CONTENT BOXES */
.grid { display: grid; grid-template-columns: 1fr 380px; gap: 25px; align-items: start; }
.card { 
    background: var(--card-dark); border-radius: 20px; border: 1px solid var(--border); 
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); overflow: hidden;
}
.card-header { padding: 20px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
.card-header h3 { font-size: 16px; color: var(--primary); }

/* TABLES */
table { width: 100%; border-collapse: collapse; }
th { text-align: left; color: var(--text-dim); font-size: 11px; text-transform: uppercase; padding: 15px; background: rgba(0,0,0,0.1); }
td { padding: 15px; border-bottom: 1px solid var(--border); font-size: 13px; }
tr:hover { background: rgba(255,255,255,0.02); }

/* FORMS */
.form-group { padding: 25px; }
label { display: block; font-size: 12px; color: var(--text-dim); margin-bottom: 8px; font-weight: 600; }
input { 
    width: 100%; background: var(--bg-dark); border: 1px solid var(--border); color: white; 
    padding: 12px; border-radius: 10px; margin-bottom: 15px; outline: none; transition: 0.3s;
}
input:focus { border-color: var(--primary); }

.btn-primary { background: var(--primary); color: white; border: none; padding: 12px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; width: 100%; }
.btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text-dim); padding: 5px 10px; border-radius: 6px; text-decoration: none; font-size: 11px; margin-right: 5px; }
.btn-outline:hover { color: var(--text-white); border-color: var(--text-white); }

.badge { font-size: 10px; font-weight: 800; padding: 4px 8px; border-radius: 5px; }
.active-bg { background: rgba(16, 185, 129, 0.2); color: #10b981; }
.disabled-bg { background: rgba(239, 68, 68, 0.2); color: #ef4444; }

.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; z-index: 1000; }

@media (max-width: 1200px) { .grid { grid-template-columns: 1fr; } }
@media (max-width: 1024px) {
    .sidebar { left: calc(-1 * var(--sidebar-width)); }
    .sidebar.open { left: 0; }
    .main { margin-left: 0; }
    .hamburger { display: block; }
    .overlay.show { display: block; }
}
</style>
</head>

<body>
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="https://image2url.com/r2/default/images/1771368838602-bf6543ef-276c-4269-9811-83228fc931c0.jpeg" alt="Logo">
        <h2>ADMIN PANEL</h2>
    </div>
    <a href="/admin/dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
    <a href="/admin/orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="/admin/users.php"><i class="fas fa-users"></i> Users</a>
    <a href="/admin/deposits.php"><i class="fas fa-wallet"></i> Deposits</a>
    <a href="/admin/services.php"><i class="fas fa-list"></i> Services</a>
    <a href="/admin/provider.php" class="active"><i class="fas fa-server"></i> Providers</a>
    <a href="/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
</div>

<div class="main">
    <div class="header">
        <div style="display:flex; align-items:center; gap:15px;">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>API Providers</h1>
        </div>
        <a href="/admin/logout.php" style="color:#ef4444; text-decoration:none; font-weight:700; font-size:13px;"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>

    <div class="grid">
        <div class="card">
            <div class="card-header">
                <h3>Connected API Sources</h3>
            </div>
            <div style="overflow-x:auto;">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Status</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($providers as $p): ?>
                        <tr>
                            <td>
                                <div style="font-weight:700;"><?= htmlspecialchars($p['name']) ?></div>
                                <div style="font-size:10px; color:var(--text-dim);"><?= $p['total_services'] ?> Services</div>
                            </td>
                            <td>
                                <span class="badge <?= $p['active']?'active-bg':'disabled-bg' ?>">
                                    <?= $p['active']?'ACTIVE':'OFF' ?>
                                </span>
                            </td>
                            <td style="color:#10b981; font-weight:700;">
                                <?= $p['active'] ? '$'.number_format((float)fetchBalance($p['api_url'],$p['api_key']), 2) : '—' ?>
                            </td>
                            <td>
                                <a href="?edit=<?= $p['id'] ?>" class="btn-outline">Edit</a>
                                <a href="?delete=<?= $p['id'] ?>" class="btn-outline" style="color:#ef4444;" onclick="return confirm('Delete provider?')">Delete</a>
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
                <div class="card-header"><h3><i class="fas fa-edit"></i> Edit Provider</h3></div>
                <form method="post" class="form-group">
                    <input type="hidden" name="id" value="<?= $prov['id'] ?>">
                    <label>Display Name</label>
                    <input name="name" value="<?= htmlspecialchars($prov['name']) ?>" required>
                    <label>API Endpoint URL</label>
                    <input name="api_url" value="<?= htmlspecialchars($prov['api_url']) ?>" required>
                    <label>API Key</label>
                    <input name="api_key" value="<?= htmlspecialchars($prov['api_key']) ?>" required>
                    <label style="display:flex; align-items:center; gap:10px; cursor:pointer;">
                        <input type="checkbox" name="active" style="width:auto; margin:0;" <?= $prov['active']?'checked':'' ?>> 
                        System Active
                    </label>
                    <button type="submit" name="update_provider" class="btn-primary" style="margin-top:15px;">Save Changes</button>
                    <div style="text-align:center; margin-top:15px;"><a href="providers.php" style="color:var(--text-dim); font-size:12px;">Cancel Edit</a></div>
                </form>
            <?php else: ?>
                <div class="card-header"><h3><i class="fas fa-plus-circle"></i> Add New Provider</h3></div>
                <form method="post" class="form-group">
                    <label>Provider Name (e.g., SMM Panel X)</label>
                    <input name="name" placeholder="Name" required>
                    <label>API URL</label>
                    <input name="api_url" placeholder="https://provider.com/api/v2" required>
                    <label>Your API Key</label>
                    <input name="api_key" placeholder="Paste key here" required>
                    <button type="submit" name="add_provider" class="btn-primary">Connect Provider</button>
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