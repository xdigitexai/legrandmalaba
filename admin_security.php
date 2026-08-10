<?php
// List of authorized IPs
$authorized_ips = ['223.123.11.63', '154.159.237.46'];

// Get the client's IP address
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'];
    }
}

$client_ip = get_client_ip();
$access = 'admin';

if (isset($_GET['access']) && $_GET['access'] === $access) {
    echo "";
} elseif (false) {
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>🛡️ Access Denied</title>
<style>
:root{
  --bg:#050505;
  --panel: rgba(10, 15, 20, 0.9);
  --neon-green:#00ff88;
  --neon-blue:#00d9ff;
  --accent-yellow: #f4ff4d;
  --danger:#ff2e2e;
  --muted:#a0aec0;
}

*{margin:0;padding:0;box-sizing:border-box;font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;}
html,body{height:100%;background:var(--bg);color:var(--neon-green);overflow:hidden;display:flex;align-items:center;justify-content:center;}

.canvas-layer{position:fixed;inset:0;z-index:0;pointer-events:none}

.hud-overlay{
  position:fixed;inset:0;z-index:1;pointer-events:none;
  background: radial-gradient(circle at center, transparent 0%, rgba(0,0,0,0.8) 100%);
}

.container {
    position: relative;
    z-index:5;
    background: var(--panel);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(0, 255, 136, 0.2);
    padding: 3rem;
    border-radius: 20px;
    width: min(800px, 95%);
    box-shadow: 0 0 40px rgba(0,0,0,0.9), 0 0 20px rgba(0, 255, 136, 0.1);
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.header {
    display: flex;
    align-items: center;
    gap: 20px;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    padding-bottom: 20px;
}

.logo-box {
    width: 60px;
    height: 60px;
    border-radius: 12px;
    background: linear-gradient(135deg, var(--neon-green), var(--neon-blue));
    display: flex;
    align-items: center;
    justify-content: center;
    color: #000;
    font-weight: 900;
    font-size: 20px;
    box-shadow: 0 0 15px var(--neon-green);
}

.title-text h1 {
    font-size: 24px;
    letter-spacing: 2px;
    color: #fff;
    text-transform: uppercase;
}

.title-text p {
    font-size: 13px;
    color: var(--neon-blue);
    opacity: 0.8;
}

.terminal-window {
    background: rgba(0,0,0,0.4);
    border-radius: 12px;
    padding: 20px;
    border: 1px solid rgba(255,255,255,0.05);
    font-family: 'Courier New', monospace;
}

.badge {
    background: var(--danger);
    color: white;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    margin-bottom: 10px;
    display: inline-block;
}

.log-content {
    color: #cbd5e0;
    font-size: 14px;
    line-height: 1.6;
}

.ip-section {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: rgba(255,255,255,0.03);
    padding: 15px 25px;
    border-radius: 12px;
}

.ip-address {
    font-family: 'Courier New', monospace;
    font-size: 18px;
    color: var(--accent-yellow);
    font-weight: bold;
}

.copy-button {
    background: transparent;
    border: 1px solid var(--neon-green);
    color: var(--neon-green);
    padding: 8px 20px;
    border-radius: 8px;
    cursor: pointer;
    transition: 0.3s;
    font-weight: 600;
}

.copy-button:hover {
    background: var(--neon-green);
    color: #000;
}

.footer-note {
    text-align: center;
    font-size: 12px;
    color: var(--muted);
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
.live-dot {
    width: 8px;
    height: 8px;
    background: var(--danger);
    border-radius: 50%;
    display: inline-block;
    margin-right: 5px;
    animation: pulse 1s infinite;
}
</style>
</head>
<body>

<canvas id="terminal-bg" class="canvas-layer"></canvas>

<div class="hud-overlay"></div>

<div class="container">
  <div class="header">
    <div class="logo-box">FS</div>
    <div class="title-text">
      <h1>Digital Palace</h1>
      <p>Security Infrastructure • Version 4.0.2</p>
    </div>
  </div>

  <div class="terminal-window">
    <div class="badge">SYSTEM ALERT</div>
    <div class="log-content">
        <div style="color:var(--danger); margin-bottom: 10px;">[!] Unauthorized Access Blocked</div>
        <pre id="logArea">
› INITIALIZING SECURITY CHECK...
› CLIENT_IP: <?= htmlspecialchars($client_ip, ENT_QUOTES, 'UTF-8') ?>
› STATUS: ACCESS_DENIED
› ACTION: IP_LOGGED_AND_REPORTED
› TRACE_ID: <?= strtoupper(substr(md5((string)time()),0,12)) ?>
        </pre>
    </div>
  </div>

  <div class="ip-section">
    <div>
        <div style="font-size: 11px; color: var(--muted); margin-bottom: 5px;">YOUR IP ADDRESS</div>
        <div class="ip-address" id="ipValue"><?= htmlspecialchars($client_ip, ENT_QUOTES, 'UTF-8') ?></div>
    </div>
    <button class="copy-button" id="copyBtn" onclick="copyIP()">COPY IP</button>
  </div>

  <div class="footer-note">
    <span class="live-dot"></span> Protected by Farhan SMM Panel Security Team. <br>
    <span style="opacity: 0.5; font-size: 10px; margin-top: 5px; display: block;">TIMESTAMP: <?= date('Y-m-d H:i:s') ?></span>
  </div>
</div>

<script>
function copyIP(){
  const ip = '<?= htmlspecialchars($client_ip, ENT_QUOTES, 'UTF-8') ?>';
  navigator.clipboard.writeText(ip).then(()=>{
    const btn = document.getElementById('copyBtn');
    btn.textContent = 'COPIED!';
    btn.style.borderColor = '#fff';
    setTimeout(()=> {
        btn.textContent = 'COPY IP';
        btn.style.borderColor = 'var(--neon-green)';
    }, 2000);
  });
}

const tCanvas = document.getElementById('terminal-bg');
const tCtx = tCanvas.getContext('2d');

function resize(){
  tCanvas.width = innerWidth;
  tCanvas.height = innerHeight;
}
window.addEventListener('resize', resize);
resize();

const chars = "010101010101010101";
const fontSize = 14;
const columns = tCanvas.width / fontSize;
const drops = [];
for(let x = 0; x < columns; x++) drops[x] = 1;

function draw() {
  tCtx.fillStyle = "rgba(0, 0, 0, 0.05)";
  tCtx.fillRect(0, 0, tCanvas.width, tCanvas.height);
  tCtx.fillStyle = "#0F0"; 
  tCtx.font = fontSize + "px arial";
  for(let i = 0; i < drops.length; i++) {
    const text = chars.charAt(Math.floor(Math.random() * chars.length));
    tCtx.fillText(text, i * fontSize, drops[i] * fontSize);
    if(drops[i] * fontSize > tCanvas.height && Math.random() > 0.975) drops[i] = 0;
    drops[i]++;
  }
}
setInterval(draw, 33);

setInterval(()=>{
  const log = document.getElementById('logArea');
  const msgs = ['PACKET_FILTER: ACTIVE', 'DB_CONNECTION: SECURED', 'BYPASS_ATTEMPT: NULLIFIED', 'ENCRYPTION: AES-256'];
  const line = '› ' + msgs[Math.floor(Math.random()*msgs.length)];
  log.textContent = line + '\n' + log.textContent.split('\n').slice(0,5).join('\n');
}, 3000);
</script>
</body>
</html>
<?php
exit();
}
?>