<?php
session_start();
require_once "../config/database.php";

/* 🔐 ADMIN GUARD */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $header = $_POST['header_code'] ?? '';
    $footer = $_POST['footer_code'] ?? '';

    $stmt = $db->prepare("UPDATE settings SET header_code=?, footer_code=? WHERE id=1");
    $stmt->execute([$header, $footer]);

    $message = "SYSTEM: Appearance scripts updated successfully.";
}

$settings = $db->query("SELECT header_code, footer_code FROM settings WHERE id=1")->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Media Boost | Appearance</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Outfit:wght@400;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

<style>
:root {
    --bg: #05070a;
    --surface: #0f1117;
    --primary: #00f2ff;
    --secondary: #7000ff;
    --success: #2ed573;
    --warning: #ffa502;
    --danger: #ff4757;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.08);
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

/* ================= SIDEBAR (SYNCED) ================= */
.sidebar {
    width: 280px;
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: 30px 20px;
    position: fixed;
    left: 0; top: 0; bottom: 0;
    transition: transform .3s ease;
    z-index: 1001;
    display: flex;
    flex-direction: column;
}

.sidebar-logo {
    display: flex; align-items: center; gap: 12px;
    padding: 0 10px 40px; text-decoration: none;
}

.logo-circle {
    width: 45px; height: 45px;
    background: rgba(255,255,255,0.03);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 0 15px rgba(0, 242, 255, 0.3);
    animation: pulse-glow 3s infinite;
    overflow: hidden;
    border: 1px solid var(--primary);
}
.logo-circle img { width: 100%; height: 100%; object-fit: cover; }

.logo-text {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 22px; font-weight: 700; color: #fff; letter-spacing: -1px;
}
.logo-text span { color: var(--primary); }

@keyframes pulse-glow {
    0% { box-shadow: 0 0 10px rgba(0, 242, 255, 0.2); }
    50% { box-shadow: 0 0 20px rgba(112, 0, 255, 0.4); }
    100% { box-shadow: 0 0 10px rgba(0, 242, 255, 0.2); }
}

.sidebar a {
    display: flex; align-items: center; gap: 12px;
    color: var(--text-dim); text-decoration: none;
    padding: 12px 15px; border-radius: 12px;
    margin-bottom: 5px; font-size: 14px; font-weight: 500; transition: 0.3s;
}
.sidebar a i { width: 20px; text-align: center; font-size: 16px; }
.sidebar a:hover, .sidebar a.active {
    background: rgba(0, 242, 255, 0.05);
    color: var(--primary);
}

/* ================= MAIN CONTENT ================= */
.main {
    flex: 1;
    padding: 40px;
    margin-left: 280px;
    transition: margin-left .3s ease;
}

.header {
    display: flex; justify-content: space-between;
    align-items: center; margin-bottom: 40px;
}
.header h1 { font-family: 'Space Grotesk', sans-serif; font-size: 28px; letter-spacing: -1px; }

/* ================= APPEARANCE CARDS ================= */
.editor-panel {
    background: var(--surface);
    border-radius: 20px;
    border: 1px solid var(--border);
    padding: 30px;
    margin-bottom: 30px;
}

.editor-label {
    display: flex; align-items: center; gap: 10px;
    font-family: 'Space Grotesk'; font-size: 14px;
    color: var(--primary); margin-bottom: 15px;
    text-transform: uppercase; letter-spacing: 1px;
}

textarea {
    width: 100%;
    height: 250px;
    background: #05070a;
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 20px;
    color: #00f2ff;
    font-family: 'Consolas', monospace;
    font-size: 13px;
    line-height: 1.6;
    outline: none;
    resize: vertical;
    transition: 0.3s;
}
textarea:focus { border-color: var(--primary); box-shadow: 0 0 15px rgba(0, 242, 255, 0.1); }

.btn-save {
    background: var(--primary);
    color: #000;
    border: none;
    padding: 15px 40px;
    border-radius: 12px;
    font-family: 'Space Grotesk';
    font-weight: 700;
    font-size: 15px;
    cursor: pointer;
    transition: 0.3s;
    display: flex; align-items: center; gap: 10px;
}
.btn-save:hover { box-shadow: 0 0 25px rgba(0, 242, 255, 0.4); transform: translateY(-2px); }

.alert {
    background: rgba(46, 213, 115, 0.1);
    border: 1px solid var(--success);
    color: var(--success);
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 25px;
    font-size: 14px;
}

/* ================= RESPONSIVE ================= */
.hamburger { display: none; font-size: 24px; cursor: pointer; color: var(--primary); }
.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.7); display: none; z-index: 1000; backdrop-filter: blur(4px); }

@media(max-width: 1024px){
    .sidebar { transform: translateX(-100%); }
    .sidebar.open { transform: translateX(0); }
    .main { margin-left: 0; padding: 20px; }
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
            <img src="https://image2url.com/r2/default/images/1771368838602-bf6543ef-276c-4269-9811-83228fc931c0.jpeg" alt="Logo">
        </div>
        <div class="logo-text">MEDIA<span>BOOST</span></div>
    </a>

    <a href="/admin/dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
    <a href="/admin/orders.php"><i class="fas fa-shopping-cart"></i> Orders</a>
    <a href="/admin/users.php"><i class="fas fa-users"></i> Users</a>
    <a href="/admin/deposits.php"><i class="fas fa-wallet"></i> Deposits</a>
    <a href="/admin/services.php"><i class="fas fa-list"></i> Services</a>
    <a href="/admin/provider.php"><i class="fas fa-server"></i> Providers</a>
    <a href="/admin/appearance.php" class="active"><i class="fas fa-paint-brush"></i> Appearance</a>
    <a href="/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
    
    <div style="margin-top: auto; padding-top: 20px; border-top: 1px solid var(--border);">
        <a href="/" target="_blank"><i class="fas fa-external-link-alt"></i> View Site</a>
    </div>
</div>

<div class="main">

    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <span class="hamburger" onclick="toggleSidebar()"><i class="fas fa-bars"></i></span>
            <h1>Appearance </h1>
        </div>
        <div style="font-family: monospace; font-size: 12px; color: var(--primary);"></div>
    </div>

    <?php if (!empty($message)): ?>
        <div class="alert"><i class="fas fa-check-circle"></i> <?= $message ?></div>
    <?php endif; ?>

    <form method="post">
        <div class="editor-panel">
            <label class="editor-label"><i class="fas fa-code"></i> Header </label>
            <p style="color: var(--text-dim); font-size: 12px; margin-bottom: 15px;">Scripts and styles placed inside the &lt;head&gt; tag.</p>
            <textarea name="header_code" placeholder="/* CSS or JS here */"><?= htmlspecialchars($settings['header_code']) ?></textarea>
        </div>

        <div class="editor-panel">
            <label class="editor-label"><i class="fas fa-terminal"></i> Footer </label>
            <p style="color: var(--text-dim); font-size: 12px; margin-bottom: 15px;">Scripts placed before the closing &lt;/body&gt; tag (Trackers, Live Chat, etc).</p>
            <textarea name="footer_code" placeholder="<script> // code </script>"><?= htmlspecialchars($settings['footer_code']) ?></textarea>
        </div>

        <button type="submit" class="btn-save">
            <i class="fas fa-save"></i> DEPLOY CHANGES
        </button>
    </form>

    <div style="margin-top: 40px; color: var(--text-dim); font-size: 12px; text-align: center;">
        &copy; <?= date('Y') ?>  • V2.0.4
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