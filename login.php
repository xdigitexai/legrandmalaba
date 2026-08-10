<?php
session_start();
$error = $_GET['error'] ?? '';
$success = $_GET['success'] ?? '';
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Connexion — Legrand</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:'Inter',sans-serif;min-height:100vh;display:flex;align-items:center;justify-content:center;background:#0f0f1a;position:relative;overflow:hidden}
.bg-grid{position:fixed;top:0;left:0;width:100%;height:100%;background-image:radial-gradient(rgba(99,102,241,0.08) 1px,transparent 1px);background-size:40px 40px;z-index:0}
.bg-glow{position:fixed;top:-30%;right:-20%;width:600px;height:600px;background:radial-gradient(circle,rgba(99,102,241,0.15),transparent 70%);border-radius:50%;z-index:0;animation:float 8s ease-in-out infinite}
.bg-glow2{position:fixed;bottom:-20%;left:-10%;width:500px;height:500px;background:radial-gradient(circle,rgba(168,85,247,0.12),transparent 70%);border-radius:50%;z-index:0;animation:float 10s ease-in-out infinite reverse}
@keyframes float{0%,100%{transform:translateY(0)}50%{transform:translateY(-40px)}}
.login-container{position:relative;z-index:1;width:100%;max-width:440px;padding:20px}
.login-card{background:rgba(255,255,255,0.04);backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);border:1px solid rgba(255,255,255,0.08);border-radius:24px;padding:48px 40px;box-shadow:0 25px 60px rgba(0,0,0,0.5)}
.logo{text-align:center;margin-bottom:36px}
.logo-icon{width:56px;height:56px;background:linear-gradient(135deg,#6366f1,#a855f7);border-radius:16px;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:24px;color:#fff;box-shadow:0 8px 24px rgba(99,102,241,0.3)}
.logo h1{font-size:22px;font-weight:700;color:#fff;letter-spacing:-0.5px}
.logo p{font-size:13px;color:rgba(255,255,255,0.5);margin-top:4px}
.alert{padding:14px 18px;border-radius:12px;font-size:14px;margin-bottom:24px;display:flex;align-items:center;gap:10px;animation:slideIn 0.3s ease}
@keyframes slideIn{from{opacity:0;transform:translateY(-10px)}to{opacity:1;transform:translateY(0)}}
.alert-error{background:rgba(239,68,68,0.12);border:1px solid rgba(239,68,68,0.25);color:#fca5a5}
.alert-success{background:rgba(34,197,94,0.12);border:1px solid rgba(34,197,94,0.25);color:#86efac}
.form-group{margin-bottom:20px}
.form-group label{display:block;font-size:13px;font-weight:600;color:rgba(255,255,255,0.7);margin-bottom:8px;letter-spacing:0.3px;text-transform:uppercase}
.input-wrap{position:relative}
.input-wrap .icon{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.3);font-size:18px;pointer-events:none}
.input-wrap input{width:100%;padding:14px 16px 14px 48px;background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);border-radius:12px;color:#fff;font-size:15px;font-family:'Inter',sans-serif;transition:all 0.2s;outline:none}
.input-wrap input:focus{border-color:#6366f1;background:rgba(99,102,241,0.08);box-shadow:0 0 0 3px rgba(99,102,241,0.15)}
.input-wrap input::placeholder{color:rgba(255,255,255,0.25)}
.btn-login{width:100%;padding:16px;background:linear-gradient(135deg,#6366f1,#a855f7);border:none;border-radius:12px;color:#fff;font-size:16px;font-weight:600;font-family:'Inter',sans-serif;cursor:pointer;transition:all 0.3s;margin-top:8px}
.btn-login:hover{transform:translateY(-2px);box-shadow:0 12px 32px rgba(99,102,241,0.35)}
.btn-login:active{transform:translateY(0)}
.signup-footer{text-align:center;margin-top:24px;font-size:14px;color:rgba(255,255,255,0.4)}
.signup-footer a{color:#a855f7;font-weight:600;text-decoration:none;transition:color 0.2s}
.signup-footer a:hover{color:#c084fc}
.footer{text-align:center;margin-top:20px;font-size:13px;color:rgba(255,255,255,0.3)}
@media(max-width:480px){.login-card{padding:32px 24px}.logo h1{font-size:20px}}
</style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-glow"></div>
<div class="bg-glow2"></div>
<div class="login-container">
<div class="login-card">
<div class="logo">
<div class="logo-icon">
<svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
</div>
<h1>Legrand</h1>
<p>Espace Membre</p>
</div>
<?php if ($error === 'invalid'): ?>
<div class="alert alert-error">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
Identifiants incorrects.
</div>
<?php elseif ($error === 'empty'): ?>
<div class="alert alert-error">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
Veuillez remplir tous les champs.
</div>
<?php endif; ?>
<?php if ($success === 'registered'): ?>
<div class="alert alert-success">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
Inscription réussie ! Connectez-vous.
</div>
<?php endif; ?>
<form method="post" action="login_process.php">
<div class="form-group">
<label for="username">Nom d'utilisateur</label>
<div class="input-wrap">
<span class="icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
</span>
<input type="text" name="username" id="username" placeholder="Votre pseudo" required autocomplete="username" autofocus>
</div>
</div>
<div class="form-group">
<label for="password">Mot de passe</label>
<div class="input-wrap">
<span class="icon">
<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
</span>
<input type="password" name="password" id="password" placeholder="••••••••" required autocomplete="current-password">
</div>
</div>
<button type="submit" class="btn-login">Se connecter</button>
</form>
<div class="signup-footer">
Pas encore membre ? <a href="signup.php">Créer un compte</a>
</div>
<div class="footer">
&copy; 2026 Legrand
</div>
</div>
</div>
</body>
</html>