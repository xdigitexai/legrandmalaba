<?php
session_start();

/* =========================
    ADMIN AUTH GUARD
========================= */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: /admin/login.php");
    exit;
}

/* =========================
    DATABASE
========================= */
require_once __DIR__ . "/../config/database.php";

/* =========================
    DASHBOARD STATS
========================= */
$totalOrders = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$paidOrders  = $db->query("SELECT COUNT(*) FROM orders WHERE status='paid'")->fetchColumn();
$pendingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn();
$processingOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status='processing'")->fetchColumn();
$failedOrders = $db->query("SELECT COUNT(*) FROM orders WHERE status IN ('failed','cancelled')")->fetchColumn();

$totalRevenue = $db->query("
    SELECT IFNULL(SUM(price),0)
    FROM orders
    WHERE status IN ('paid','processing','completed')
")->fetchColumn();

$totalServices = $db->query("SELECT COUNT(*) FROM services")->fetchColumn();
$activeServices = $db->query("SELECT COUNT(*) FROM services WHERE active=1")->fetchColumn();
$inactiveServices = $totalServices - $activeServices;

$totalProviders = $db->query("SELECT COUNT(*) FROM providers")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>ADMIN | Legrand</title>
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
    --warning: #fbbf24;
    --danger: #f87171;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.05);
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background-color: var(--bg);
    color: var(--text);
    font-family: 'Outfit', sans-serif;
    min-height: 100vh;
    display: flex;
    overflow-x: hidden;
}

/* ================= SIDEBAR ================= */
.sidebar {
    width: 280px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: 30px 20px;
    position: fixed;
    left: 0; top: 0; bottom: 0;
    transition: transform .3s cubic-bezier(0.4, 0, 0.2, 1);
    z-index: 1001;
    display: flex;
    flex-direction: column;
}

.sidebar-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 0 10px 40px;
    text-decoration: none;
}

.logo-circle {
    width: 45px;
    height: 45px;
    background: var(--bg);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid var(--border);
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}

.logo-circle img { width: 30px; height: auto; }

.logo-text {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 20px;
    font-weight: 700;
    color: #fff;
    letter-spacing: -1px;
}
.logo-text span { color: var(--primary); }

.sidebar-nav { flex: 1; }

.sidebar a {
    display: flex;
    align-items: center;
    gap: 12px;
    color: var(--text-dim);
    text-decoration: none;
    padding: 14px 18px;
    border-radius: 15px;
    margin-bottom: 5px;
    font-size: 14px;
    font-weight: 600;
    transition: 0.2s;
}

.sidebar a i { width: 20px; text-align: center; font-size: 16px; opacity: 0.7; }

.sidebar a:hover {
    color: #fff;
    background: rgba(255,255,255,0.03);
}

.sidebar a.active {
    background: var(--primary-glow);
    color: var(--primary);
    border: 1px solid rgba(255, 170, 0, 0.1);
}

.sidebar a.active i { opacity: 1; }

/* ================= MAIN CONTENT ================= */
.main {
    flex: 1;
    padding: 40px;
    margin-left: 280px;
    transition: margin-left .3s ease;
}

.header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
}

.header h1 {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -1px;
}

.logout-btn {
    background: rgba(248, 113, 113, 0.1);
    color: var(--danger);
    text-decoration: none;
    padding: 10px 20px;
    border-radius: 12px;
    font-size: 13px;
    font-weight: 700;
    border: 1px solid rgba(248, 113, 113, 0.2);
    transition: 0.3s;
}
.logout-btn:hover { background: var(--danger); color: #000; }

/* ================= STAT CARDS ================= */
.cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
    gap: 25px;
}

.card {
    background: var(--surface);
    padding: 30px;
    border-radius: 25px;
    border: 1px solid var(--border);
    transition: transform 0.3s;
}

.card:hover {
    transform: translateY(-5px);
    border-color: rgba(255, 170, 0, 0.2);
}

.card-icon {
    width: 45px;
    height: 45px;
    border-radius: 12px;
    background: rgba(255,255,255,0.03);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 20px;
    color: var(--primary);
    font-size: 18px;
}

.card h3 {
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    color: var(--text-dim);
    margin-bottom: 8px;
    font-weight: 800;
}

.card p {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 34px;
    font-weight: 700;
}

/* Accents */
.card.revenue p { color: var(--success); }
.card.revenue .card-icon { color: var(--success); background: rgba(74, 222, 128, 0.1); }
.card.pending p { color: var(--warning); }
.card.failed p { color: var(--danger); }

/* ================= RESPONSIVE ================= */
.hamburger { display: none; font-size: 24px; cursor: pointer; color: var(--primary); }
.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); display: none; z-index: 1000; backdrop-filter: blur(8px); }

@media(max-width: 1024px){
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .main { margin-left: 0; padding: 25px; }
    .hamburger { display: block; }
    .overlay.show { display: block; }
}
</style>
</head>

<body>

<div class="overlay" onclick="toggleSidebar()"></div>

<div class="sidebar" id="sidebar">
    <a href="/admin/dashboard.php" class="sidebar-logo">
        <div class="logo-circle">
            <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Logo">
        </div>
        <div class="logo-text">Legrand</div>
    </a>

    <div class="sidebar-nav">
        <a href="/admin/dashboard.php" class="active"><i class="fas fa-layer-group"></i> Dashboard</a>
        <a href="/admin/orders.php"><i class="fas fa-shopping-bag"></i> Commandes</a>
        <a href="/admin/users.php"><i class="fas fa-user-friends"></i> Utilisateurs</a>
        <a href="/admin/deposits.php"><i class="fas fa-credit-card"></i> Dépôts</a>
        <a href="/admin/services.php"><i class="fas fa-stream"></i> Services</a>
        <a href="/admin/provider.php"><i class="fas fa-database"></i> Fournisseurs</a>
        <a href="/admin/import_services.php"><i class="fas fa-cloud-download-alt"></i> Importer</a>
        <a href="/admin/appearance.php"><i class="fas fa-magic"></i> Apparence</a>
        <a href="/admin/broadcast.php"><i class="fas fa-bullhorn"></i> Broadcast</a>
        <a href="/admin/settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>
    </div>
    
    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);">
        <a href="/" target="_blank"><i class="fas fa-external-link-alt"></i> Voir le site</a>
    </div>
</div>

<div class="main">

    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <span class="hamburger" onclick="toggleSidebar()"><i class="fas fa-align-left"></i></span>
            <h1>Vue d'ensemble</h1>
        </div>
        <a href="/admin/logout.php" class="logout-btn"><i class="fas fa-power-off"></i> Déconnexion</a>
    </div>

    <div class="cards">
        <div class="card revenue">
            <div class="card-icon"><i class="fas fa-chart-line"></i></div>
            <h3>Revenu Total</h3>
            <p><?= number_format($totalRevenue, 2) ?> $</p>
        </div>
        
        <div class="card">
            <div class="card-icon"><i class="fas fa-shopping-cart"></i></div>
            <h3>Commandes Totales</h3>
            <p><?= number_format($totalOrders) ?></p>
        </div>
        
        <div class="card pending">
            <div class="card-icon"><i class="fas fa-clock"></i></div>
            <h3>En attente</h3>
            <p><?= number_format($pendingOrders) ?></p>
        </div>
        
        <div class="card failed">
            <div class="card-icon"><i class="fas fa-times-circle"></i></div>
            <h3>Échouées</h3>
            <p><?= number_format($failedOrders) ?></p>
        </div>
        
        <div class="card">
            <div class="card-icon"><i class="fas fa-check-double"></i></div>
            <h3>Services Actifs</h3>
            <p><?= number_format($activeServices) ?></p>
        </div>
        
        <div class="card">
            <div class="card-icon"><i class="fas fa-plug"></i></div>
            <h3>Fournisseurs</h3>
            <p><?= number_format($totalProviders) ?></p>
        </div>
    </div>

    <div style="margin-top: 60px; color: var(--text-dim); font-size: 11px; text-align: center; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; opacity: 0.5;">
        Legrand &copy; <?= date('Y') ?> &bull; SYSTEM VERSION 2.0.4
    </div>
</div>

<script>
function toggleSidebar(){
    document.getElementById('sidebar').classList.toggle('open');
    document.querySelector('.overlay').classList.toggle('show');
}
</script>

</body>
</html>