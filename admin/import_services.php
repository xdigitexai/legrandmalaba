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

$message = '';
$error   = '';
$pricingMessage = '';

/* ======================
   SAVE PRICING
====================== */
if (isset($_POST['update_pricing'])) {
    $markup = (float) ($_POST['markup_percent'] ?? 0);
    if ($markup < 0) $markup = 0;

    $stmt = $db->prepare("UPDATE pricing_rules SET markup_percent=? WHERE id=1");
    $stmt->execute([$markup]);
    $pricingMessage = "Pricing updated successfully.";
}

/* ======================
   FETCH CURRENT PRICING
====================== */
$rule = $db->query("SELECT markup_percent FROM pricing_rules WHERE id=1")->fetch(PDO::FETCH_ASSOC);
$markup = isset($rule['markup_percent']) ? (float)$rule['markup_percent'] : 0;

/* =========================
   HELPERS
========================= */
function detectPlatform($text){
    $t = strtolower($text);
    foreach (['tiktok','instagram','facebook','youtube','twitter','spotify','linkedin','telegram','whatsapp','website'] as $p) {
        if (strpos($t, $p) !== false) return $p;
    }
    return 'other';
}

function autoCategoryFromName($name){
    $parts = preg_split('/\s+/', trim($name));
    return implode(' ', array_slice($parts, 0, 2));
}

/* =========================
   FETCH ACTIVE PROVIDERS
========================= */
$providers = $db->query("SELECT * FROM providers WHERE active=1")->fetchAll(PDO::FETCH_ASSOC);

/* =========================
   SYNC SERVICES
========================= */
if (isset($_POST['sync_services'])) {

    $provider_id = (int)($_POST['provider_id'] ?? 0);

    if ($provider_id <= 0) {
        $error = "Please select a provider.";
    } else {

        try {

            $stmt = $db->prepare("SELECT * FROM providers WHERE id=?");
            $stmt->execute([$provider_id]);
            $provider = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$provider) {
                throw new Exception("Provider not found.");
            }

            $postData = [
                'key' => $provider['api_key'],
                'action' => 'services'
            ];

            $ch = curl_init($provider['api_url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => http_build_query($postData),
                CURLOPT_TIMEOUT => 60,
                CURLOPT_SSL_VERIFYPEER => false
            ]);

            $response = curl_exec($ch);

            if (curl_errno($ch)) {
                throw new Exception("Connection Error: " . curl_error($ch));
            }

            curl_close($ch);

            $decoded = json_decode($response, true);
            if (!$decoded) {
                throw new Exception("Invalid JSON response.");
            }

            $services = $decoded['data'] ?? $decoded;
            if (!is_array($services)) {
                throw new Exception("Unexpected provider response.");
            }

            $inserted = 0;
            $updated  = 0;

            foreach ($services as $srv) {

                if (!isset($srv['service'], $srv['name'])) continue;

                $name     = trim($srv['name']);
                $category = autoCategoryFromName($name);
                $platform = detectPlatform($name);

                $min  = (int)($srv['min'] ?? 0);
                $max  = (int)($srv['max'] ?? 0);
                $cost = (float)($srv['rate'] ?? 0);

                // ✅ APPLY MARKUP
                $price = round($cost * (1 + ($markup / 100)), 6);

                // ✅ KEEP BOTH SAME
                $price_per_1000 = $price;

                $description   = $srv['description'] ?? '';
                $delivery_time = $srv['delivery_time'] ?? '';

                $check = $db->prepare("SELECT id FROM services WHERE provider_id=? AND provider_service_id=? LIMIT 1");
                $check->execute([$provider_id, $srv['service']]);

                if ($check->fetch()) {

                    $update = $db->prepare("
                        UPDATE services SET
                            name=?,
                            category=?,
                            platform=?,
                            min=?,
                            max=?,
                            cost_usd=?,
                            price_usd=?,
                            price_per_1000=?,
                            description=?,
                            delivery_time=?,
                            updated_at=NOW()
                        WHERE provider_id=? AND provider_service_id=?
                    ");

                    $update->execute([
                        $name,
                        $category,
                        $platform,
                        $min,
                        $max,
                        $cost,
                        $price,
                        $price_per_1000,
                        $description,
                        $delivery_time,
                        $provider_id,
                        $srv['service']
                    ]);

                    $updated++;

                } else {

                    $insert = $db->prepare("
                        INSERT INTO services
                        (name, category, platform, min, max, cost_usd, price_usd, price_per_1000, description, delivery_time, provider_id, provider_service_id, active, created_at)
                        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,1,NOW())
                    ");

                    $insert->execute([
                        $name,
                        $category,
                        $platform,
                        $min,
                        $max,
                        $cost,
                        $price,
                        $price_per_1000,
                        $description,
                        $delivery_time,
                        $provider_id,
                        $srv['service']
                    ]);

                    $inserted++;
                }
            }

            $message = "Sync complete: {$inserted} new, {$updated} updated.";

        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Import & Pricing | Global Media Boost</title>
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

/* CONTENT CARDS */
.grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 25px; }
.card { 
    background: var(--card-dark); padding: 30px; border-radius: 20px; border: 1px solid var(--border); 
    box-shadow: 0 10px 30px rgba(0,0,0,0.2); height: fit-content;
}
.card h2 { font-size: 18px; margin-bottom: 20px; color: var(--primary); display: flex; align-items: center; gap: 10px; }

label { display: block; font-size: 13px; color: var(--text-dim); margin-bottom: 8px; font-weight: 600; }
input, select {
    width: 100%; background: var(--bg-dark); border: 1px solid var(--border); color: white;
    padding: 14px; border-radius: 12px; margin-bottom: 20px; outline: none; transition: 0.3s;
}
input:focus, select:focus { border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-glow); }

.btn-action { 
    width: 100%; background: var(--primary); color: white; border: none; padding: 14px; 
    border-radius: 12px; font-weight: 700; cursor: pointer; transition: 0.3s; 
}
.btn-action:hover { transform: translateY(-2px); opacity: 0.9; }

/* ALERTS */
.alert { padding: 15px; border-radius: 12px; margin-bottom: 20px; font-size: 14px; font-weight: 500; }
.alert-success { background: rgba(16, 185, 129, 0.15); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
.alert-error { background: rgba(239, 68, 68, 0.15); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }

.hint { font-size: 12px; color: var(--text-dim); margin-top: -15px; margin-bottom: 20px; line-height: 1.5; }

.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); display: none; z-index: 1000; }

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
    <a href="/admin/services.php" class="active"><i class="fas fa-list"></i> Services</a>
    <a href="/admin/provider.php"><i class="fas fa-server"></i> Providers</a>
    <a href="/admin/settings.php"><i class="fas fa-cog"></i> Settings</a>
</div>

<div class="main">
    <div class="header">
        <div style="display: flex; align-items: center; gap: 15px;">
            <i class="fas fa-bars hamburger" onclick="toggleSidebar()"></i>
            <h1>Import & Automation</h1>
        </div>
        <a href="/admin/logout.php" style="background:#ef4444; color:white; padding:8px 16px; border-radius:20px; text-decoration:none; font-size:12px; font-weight:700;">Logout</a>
    </div>

    <div class="grid">
        <div class="card">
            <h2><i class="fas fa-dollar-sign"></i> Global Pricing Markup</h2>
            <?php if ($pricingMessage): ?>
                <div class="alert alert-success"><?= $pricingMessage ?></div>
            <?php endif; ?>
            <form method="post">
                <label>Markup Percentage (%)</label>
                <input type="number" step="0.01" name="markup_percent" value="<?= $markup ?>" required>
                <div class="hint">
                    This adds a profit margin to all imported services.<br>
                    <strong>Example:</strong> 20 means a $1.00 service will cost users $1.20.
                </div>
                <button type="submit" name="update_pricing" class="btn-action">Save Profit Rules</button>
            </form>
        </div>

        <div class="card">
            <h2><i class="fas fa-sync-alt"></i> API Service Sync</h2>
            <?php if ($message): ?><div class="alert alert-success"><?= $message ?></div><?php endif; ?>
            <?php if ($error): ?><div class="alert alert-error"><?= $error ?></div><?php endif; ?>
            
            <form method="post">
                <label>Active Provider</label>
                <select name="provider_id" required>
                    <option value="">-- Select API Source --</option>
                    <?php foreach ($providers as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <div class="hint">
                    Syncing will fetch all available services from the provider and update costs in your local database.
                </div>
                <button type="submit" name="sync_services" class="btn-action" style="background: #10b981;">Fetch & Sync Now</button>
            </form>
        </div>
    </div>

    <footer style="margin-top: 50px; text-align: center; color: var(--text-dim); font-size: 13px;">
        © <?= date('Y') ?> Global Media Boost • Premium Admin Engine
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