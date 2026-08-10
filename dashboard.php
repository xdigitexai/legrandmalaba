<?php
declare(strict_types=1);

/* =========================
   ERROR REPORTING (DEV ONLY)
========================= */
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

/* =========================
   LOAD DATABASE
========================= */
require_once __DIR__ . "/config/database.php";

/* =========================
   START SESSION SAFELY
========================= */
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* =========================
   AUTH CHECK
========================= */
if (empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = (int) $_SESSION['user_id'];

/* =========================
   HELPERS
========================= */

/** Cleans messy characters like ///????''' **/
function cleanStr($text) {
    if (!$text) return "";
    return preg_replace('/[?\/\\\\\'\"\}\{\[\]]/', '', $text);
}

/** Smarter Category Logic for "Minutes", "180k", etc. **/
function getSmartCat($serviceName, $platform) {
    $name = cleanStr($serviceName);
    $lower = strtolower($name);
    $plat = ucfirst(strtolower($platform));

    $keywords = ['followers', 'likes', 'minutes', 'views', 'comments', 'members', 'subscribers', 'shares'];
    $found = "";

    foreach ($keywords as $word) {
        if (strpos($lower, $word) !== false) {
            $found = ucfirst($word);
            break;
        }
    }

    if (preg_match('/(\d+k|\d+m|\d+)/i', $name, $matches)) {
        return $plat . " " . $matches[0] . ($found ? " " . $found : "");
    }
    return ($found !== "") ? $plat . " " . $found : $plat . " Services";
}

/* =========================
   FETCH USER BALANCE
========================= */
$userBalance = 0.00;
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ? LIMIT 1");
if ($stmt && $stmt->execute([$userId])) {
    $balance = $stmt->fetchColumn();
    $userBalance = $balance !== false ? (float)$balance : 0.00;
}

/* =========================
   FETCH MARKUP PERCENT
========================= */
$markupPercent = 0.00;
$ruleStmt = $db->query("SELECT markup_percent FROM pricing_rules WHERE id = 1 LIMIT 1");
if ($ruleStmt) {
    $rule = $ruleStmt->fetch(PDO::FETCH_ASSOC);
    if ($rule && isset($rule['markup_percent'])) {
        $markupPercent = (float)$rule['markup_percent'];
    }
}

/* =========================
   FETCH ACTIVE SERVICES
========================= */
$services = [];
$serviceStmt = $db->prepare("
    SELECT service_id as id, service_name as name, 'Social' as platform, service_description as description, '-' as delivery_time, service_price as cost_usd, service_min as min, service_max as max
    FROM services
    WHERE service_type = '2'
    ORDER BY service_id ASC
");
if ($serviceStmt && $serviceStmt->execute()) {
    $services = $serviceStmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Tableau de Bord | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.3);
    --bg: #050507;
    --surface: rgba(255, 255, 255, 0.04);
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.08);
    --sidebar-width: 280px;
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: radial-gradient(circle at top right, #111, #050507); color: var(--text); overflow-x: hidden; min-height: 100vh; }

/* SIDEBAR SHINY */
.sidebar {
    position: fixed; left: calc(-1 * var(--sidebar-width)); top: 0;
    width: var(--sidebar-width); height: 100%;
    background: rgba(17, 17, 20, 0.95); backdrop-filter: blur(15px); z-index: 10000;
    transition: 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    border-right: 1px solid var(--border);
    display: flex; flex-direction: column;
}
.sidebar.active { left: 0; }
.sidebar-header { padding: 40px 20px; text-align: center; border-bottom: 1px solid var(--border); }
.sidebar-header img { height: 50px; border-radius: 12px; filter: drop-shadow(0 0 10px var(--primary-glow)); }
.logo-txt { font-weight: 800; color: var(--primary); font-size: 18px; text-transform: uppercase; letter-spacing: 1px; }

.sidebar-menu { flex: 1; padding: 25px 0; }
.sidebar-menu a {
    display: flex; align-items: center; gap: 15px; padding: 15px 30px;
    color: var(--text-dim); text-decoration: none; font-weight: 600; transition: 0.3s;
}
.sidebar-menu a:hover, .sidebar-menu a.active {
    color: var(--primary); background: rgba(255, 170, 0, 0.05);
    border-right: 4px solid var(--primary);
}

/* TOP NAV SHINY */
.top-nav {
    display: flex; justify-content: space-between; align-items: center;
    padding: 18px 5%; background: rgba(5, 5, 7, 0.7);
    backdrop-filter: blur(20px); position: sticky; top: 0; z-index: 999;
    border-bottom: 1px solid var(--border);
}
.menu-toggle { font-size: 26px; color: var(--primary); cursor: pointer; }

.balance-card {
    background: linear-gradient(135deg, #ffaa00, #ff8800);
    padding: 10px 24px; border-radius: 50px;
    font-weight: 800; font-size: 14px; color: #000;
    box-shadow: 0 0 20px var(--primary-glow);
}

/* PLATFORM GRID SHINY */
.platform-grid {
    display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr));
    gap: 12px; margin: 30px auto; max-width: 900px; padding: 0 15px;
}
.platform {
    background: var(--surface); border-radius: 20px; padding: 20px 10px;
    display: flex; flex-direction: column; align-items: center; gap: 10px;
    cursor: pointer; font-weight: 700; font-size: 13px; color: var(--text-dim);
    border: 1px solid var(--border); transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.platform i { font-size: 26px; color: var(--primary); }
.platform:hover { border-color: var(--primary); transform: translateY(-5px); }
.platform.active { 
    background: linear-gradient(135deg, rgba(255,170,0,0.1), transparent);
    color: var(--primary); border-color: var(--primary);
    box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 10px var(--primary-glow);
}

/* FORM WRAPPER SHINY */
.wrapper {
    background: rgba(17, 17, 20, 0.4); backdrop-filter: blur(25px);
    border-radius: 35px; padding: 40px; border: 1px solid var(--border);
    max-width: 600px; margin: auto; box-shadow: 0 50px 100px rgba(0,0,0,0.6);
}

label { font-size: 11px; font-weight: 800; color: var(--primary); text-transform: uppercase; margin-bottom: 10px; display: block; letter-spacing: 1.5px; }

select, input {
    width: 100%; padding: 18px; border-radius: 16px;
    border: 1px solid var(--border); margin-bottom: 25px;
    background: rgba(0,0,0,0.4); color: #fff; font-size: 15px;
    transition: 0.3s; -webkit-appearance: none;
}
select:focus, input:focus { border-color: var(--primary); box-shadow: 0 0 15px var(--primary-glow); }

.desc-box {
    background: rgba(255,170,0,0.05); padding: 20px; border-radius: 18px;
    display: none; font-size: 14px; color: #cbd5e1;
    border-left: 5px solid var(--primary); margin-bottom: 25px; line-height: 1.6;
}

.summary-card {
    background: rgba(255,255,255,0.02); padding: 25px; border-radius: 20px; margin-bottom: 30px;
    border: 1px solid var(--border);
}
.summary-card div { display: flex; justify-content: space-between; margin-bottom: 8px; font-weight: 600; }
.total-row { border-top: 1px solid var(--border); padding-top: 15px; margin-top: 10px; color: var(--primary); font-size: 26px; font-weight: 800; text-shadow: 0 0 15px var(--primary-glow); }

.btn-submit {
    width: 100%; padding: 22px; border: none; border-radius: 20px;
    background: linear-gradient(90deg, #ffaa00, #ffcc00); color: #000; font-size: 16px;
    font-weight: 800; cursor: pointer; transition: 0.4s; text-transform: uppercase; letter-spacing: 1px;
}
.btn-submit:hover { transform: scale(1.02); box-shadow: 0 15px 40px rgba(255, 170, 0, 0.4); }

.overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 9999; display:none; }
.overlay.active { display:block; }

.whatsapp-float {
    position: fixed; bottom: 30px; right: 30px; width: 65px; height: 65px;
    background-color: #25d366; color: #FFF; border-radius: 50px;
    display: flex; align-items: center; justify-content: center; font-size: 32px;
    box-shadow: 0 15px 35px rgba(37, 211, 102, 0.4); z-index: 10001; transition: 0.3s; text-decoration: none;
}
.whatsapp-float:hover { transform: scale(1.1) rotate(10deg); }
</style>
</head>

<body>

<div class="overlay" id="overlay"></div>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Logo">
        <div class="logo-txt">Legrand</div>
    </div>
    
    <nav class="sidebar-menu">
        <?php 
        $currentFile = basename($_SERVER['PHP_SELF']); 
        $menu = [
            'dashboard.php' => ['rocket', 'Nouvelle Commande'],
            'my_orders.php' => ['history', 'Historique'],
            'add-funds.php' => ['wallet', 'Ajouter fonds'],
            'notifications.php' => ['bell', 'Notifications'],
            'profile.php' => ['user-shield', 'Profil']
        ];
        foreach($menu as $link => $info): ?>
            <a href="<?= $link ?>" class="<?= $currentFile == $link ? 'active' : '' ?>">
                <i class="fas fa-<?= $info[0] ?>"></i> <?= $info[1] ?>
            </a>
        <?php endforeach; ?>
        <a href="logout.php" style="color: #ff4444; margin-top: 30px;"><i class="fas fa-sign-out-alt"></i> Déconnexion</a>
    </nav>
</aside>

<nav class="top-nav">
    <div class="nav-left">
        <i class="fas fa-align-left menu-toggle" id="toggleBtn"></i>
        <div style="font-weight: 800; font-size: 20px; letter-spacing: -0.5px;">Dashboard</div>
    </div>
    <div class="balance-card">
        Solde: <?= number_format($userBalance, 2) ?> $
    </div>
</nav>

<main class="main-content">
    <div class="platform-grid">
    <?php
    $platforms = [
        'all' => ['Tout','fa-layer-group'],
        'instagram' => ['Instagram','fa-instagram'],
        'tiktok' => ['TikTok','fa-tiktok'],
        'youtube' => ['YouTube','fa-youtube'],
        'facebook' => ['Facebook','fa-facebook'],
        'twitter' => ['Twitter','fa-x-twitter'],
        'telegram' => ['Telegram','fa-telegram'],
        'spotify' => ['Spotify','fa-spotify'],
        'website' => ['Site Web','fa-globe']
    ];
    foreach($platforms as $k => $p) {
        $iconType = (in_array($k, ['all', 'website'])) ? 'fa-solid' : 'fa-brands';
        echo "<div class='platform ".($k === 'all' ? 'active' : '')."' data-platform='{$k}'>
                <i class='{$iconType} {$p[1]}'></i><span>{$p[0]}</span>
              </div>";
    }
    ?>
    </div>

    <section class="wrapper">
        <form method="post" action="pay.php">
            <label>Catégorie</label>
            <select id="category" required><option value="">-- Sélectionnez --</option></select>

            <label>Service (Prix / 1000)</label>
            <select name="service_id" id="service" required>
                <option value="">-- Sélectionnez --</option>
                <?php foreach($services as $s): 
                    $priceVal = round($s['cost_usd'] * (1 + ($markupPercent / 100)), 4);
                    $smartCat = getSmartCat($s['name'], $s['platform']);
                ?>
                <option value="<?= $s['id'] ?>"
                 data-platform="<?= strtolower($s['platform']) ?>"
                 data-category="<?= htmlspecialchars($smartCat) ?>"
                 data-price="<?= $priceVal ?>"
                 data-min="<?= $s['min'] ?>"
                 data-max="<?= $s['max'] ?>"
                 data-desc="<?= htmlspecialchars(cleanStr($s['description'])) ?>"
                 data-time="<?= htmlspecialchars($s['delivery_time']) ?>">
                 [ID:<?= $s['id'] ?>] <?= cleanStr($s['name']) ?> - ($<?= number_format($priceVal, 2) ?>)
                </option>
                <?php endforeach; ?>
            </select>

            <div class="desc-box" id="desc"></div>

            <label>Lien / URL Cible</label>
            <input type="url" name="link" placeholder="https://..." required>

            <label>Quantité</label>
            <input type="number" name="quantity" id="qty" value="1000" min="1" required>

            <input type="hidden" name="charge" id="charge" value="0">

            <div class="summary-card">
                <div><span>Prix / 1000 :</span><span>$ <span id="price">0.00</span></span></div>
                <div class="total-row"><span>Charge Totale :</span><span>$ <span id="total">0.00</span></span></div>
            </div>

            <button type="submit" class="btn-submit">Valider la commande</button>
        </form>
    </section>
</main>

<a href="https://wa.me/243814696807" class="whatsapp-float" target="_blank"><i class="fab fa-whatsapp"></i></a>

<script>
const sidebar = document.getElementById('sidebar');
const toggleBtn = document.getElementById('toggleBtn');
const overlay = document.getElementById('overlay');
const platformEls = document.querySelectorAll(".platform");
const category = document.getElementById("category");
const service = document.getElementById("service");
const qty = document.getElementById("qty");
const priceDisp = document.getElementById("price");
const totalDisp = document.getElementById("total");
const descBox = document.getElementById("desc");
const chargeInput = document.getElementById("charge");

toggleBtn.onclick = () => { sidebar.classList.add('active'); overlay.classList.add('active'); };
overlay.onclick = () => { sidebar.classList.remove('active'); overlay.classList.remove('active'); };

let activePlatform = "all";

platformEls.forEach(p => {
    p.onclick = () => {
        platformEls.forEach(x => x.classList.remove("active"));
        p.classList.add("active");
        activePlatform = p.dataset.platform;
        loadCategories();
    };
});

function loadCategories() {
    category.innerHTML = '<option value="">-- Sélectionnez --</option>';
    const cats = new Set();
    [...service.options].forEach(o => {
        if (!o.value) return;
        if (activePlatform === "all" || o.dataset.platform === activePlatform) {
            cats.add(o.dataset.category);
        }
    });
    Array.from(cats).sort().forEach(c => {
        category.innerHTML += `<option value="${c}">${c}</option>`;
    });
    filterServices();
}

function filterServices() {
    const selCat = category.value;
    [...service.options].forEach(o => {
        if (!o.value) return;
        const match = (activePlatform === "all" || o.dataset.platform === activePlatform) && (selCat === "" || o.dataset.category === selCat);
        o.style.display = match ? "block" : "none";
    });
    service.value = "";
    updateCalc();
}

function updateCalc() {
    const o = service.options[service.selectedIndex];
    if (!o || !o.value) {
        priceDisp.textContent = "0.00";
        totalDisp.textContent = "0.00";
        descBox.style.display = "none";
        return;
    }
    
    let q = parseInt(qty.value || 0);
    const min = parseInt(o.dataset.min);
    const max = parseInt(o.dataset.max);
    const unitPrice = parseFloat(o.dataset.price);

    if (q > 0 && q < min) qty.setCustomValidity(`Min: ${min}`);
    else if (q > max) qty.setCustomValidity(`Max: ${max}`);
    else qty.setCustomValidity("");

    const total = ((q / 1000) * unitPrice).toFixed(4);
    priceDisp.textContent = unitPrice.toFixed(2);
    totalDisp.textContent = parseFloat(total).toFixed(2);
    chargeInput.value = total;

    descBox.innerHTML = `<strong>Détails :</strong><br>${o.dataset.desc}<br><br><strong>⚡ Délai :</strong> ${o.dataset.time}`;
    descBox.style.display = "block";
}

category.onchange = filterServices;
service.onchange = updateCalc;
qty.oninput = updateCalc;

loadCategories();
</script>

<!-- Training Popup -->
<div id="trainingPopup" style="display:none;position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.75);backdrop-filter:blur(6px);align-items:center;justify-content:center;">
  <div style="background:#111;border:1px solid rgba(255,170,0,0.35);border-radius:22px;padding:36px 32px;max-width:420px;width:90%;text-align:center;box-shadow:0 20px 60px rgba(0,0,0,0.7);position:relative;">
    <button onclick="closeTrainingPopup()" style="position:absolute;top:14px;right:18px;background:none;border:none;color:#64748b;font-size:22px;cursor:pointer;line-height:1;">&times;</button>
    <div style="font-size:36px;margin-bottom:14px;">&#x1F4B0;</div>
    <h2 style="color:#ffaa00;font-size:18px;font-weight:800;margin-bottom:12px;line-height:1.4;">Vous voulez apprendre &agrave; gagner de l&rsquo;argent en ligne&nbsp;?</h2>
    <p style="color:#94a3b8;font-size:14px;line-height:1.7;margin-bottom:20px;">D&eacute;couvrez les services en ligne, les strat&eacute;gies publicitaires et les conseils pour d&eacute;velopper votre activit&eacute;.</p>
    <a href="https://gbsbusiness.site" target="_blank" rel="noopener"
       style="display:inline-block;background:linear-gradient(135deg,#ffaa00,#ff8800);color:#000;font-weight:800;font-size:14px;padding:14px 28px;border-radius:12px;text-decoration:none;letter-spacing:0.5px;box-shadow:0 6px 20px rgba(255,170,0,0.35);">
      Cliquez ici pour acc&eacute;der &agrave; la formation compl&egrave;te&nbsp;!<br>
      <span style="font-size:13px;font-weight:600;">Gbsbusiness.site</span>
    </a>
    <div style="margin-top:16px;">
      <label style="font-size:11px;color:#475569;cursor:pointer;">
        <input type="checkbox" id="noShowAgainGbs" onchange="handleNoShowGbs()" style="margin-right:5px;">
        Ne plus afficher
      </label>
    </div>
  </div>
</div>
<script>
(function(){
  if (!localStorage.getItem('hideTrainingGbs')) {
    setTimeout(function(){
      var p = document.getElementById('trainingPopup');
      if (p) { p.style.display = 'flex'; }
    }, 1200);
  }
})();
function closeTrainingPopup(){ document.getElementById('trainingPopup').style.display='none'; }
function handleNoShowGbs(){
  if(document.getElementById('noShowAgainGbs').checked){ localStorage.setItem('hideTrainingGbs','1'); }
  else { localStorage.removeItem('hideTrainingGbs'); }
}
</script>

</body>
</html>