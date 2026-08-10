<?php
session_start();

/* Si déjà connecté, redirection vers le tableau de bord */
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

// Récupération des erreurs (ex: mot de passe ne correspond pas ou utilisateur existe déjà)
$error = $_GET['error'] ?? null;
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Inscription | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.15);
    --bg: #050507;
    --surface: #111114;
    --text: #f8fafc;
    --text-dim: #94a3b8;
    --accent-gradient: linear-gradient(135deg, #ffaa00 0%, #ff6600 100%);
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }

body {
    background: var(--bg);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    background: radial-gradient(circle at center, rgba(255, 170, 0, 0.05), transparent 100%);
}

.wrapper {
    width: 100%;
    max-width: 480px;
    background: var(--surface);
    border-radius: 30px;
    box-shadow: 0 40px 100px rgba(0,0,0,0.8);
    padding: 40px;
    border: 1px solid rgba(255,255,255,0.03);
}

.brand {
    text-align: center;
    margin-bottom: 30px;
}

.brand img {
    height: 65px;
    width: auto;
    border-radius: 12px;
    margin-bottom: 15px;
    box-shadow: 0 10px 20px rgba(0,0,0,0.3);
}

.brand h1 {
    font-size: 26px;
    font-weight: 800;
    letter-spacing: -1px;
    background: var(--accent-gradient);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}

.brand p {
    font-size: 14px;
    color: var(--text-dim);
    margin-top: 5px;
    font-weight: 500;
}

/* ALERTES */
.alert {
    padding: 14px 18px;
    border-radius: 15px;
    font-size: 14px;
    margin-bottom: 25px;
    display: flex;
    align-items: center;
    gap: 12px;
    background: rgba(239, 68, 68, 0.08);
    color: #f87171;
    border: 1px solid rgba(239, 68, 68, 0.15);
    animation: shake 0.5s ease;
}

@keyframes shake {
    0%, 100% { transform: translateX(0); }
    25% { transform: translateX(-5px); }
    75% { transform: translateX(5px); }
}

label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 10px;
    display: block;
    color: var(--primary);
}

.input-group {
    position: relative;
    margin-bottom: 18px;
}

.input-group i {
    position: absolute;
    left: 18px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-dim);
    font-size: 16px;
    transition: 0.3s;
}

input {
    width: 100%;
    padding: 15px 15px 15px 52px;
    border-radius: 15px;
    border: 1px solid rgba(255,255,255,0.05);
    background: #0d0d10;
    color: #fff;
    font-size: 15px;
    transition: 0.3s;
    outline: none;
}

input:focus {
    border-color: var(--primary);
    background: #121217;
    box-shadow: 0 0 20px var(--primary-glow);
}

input:focus + i {
    color: var(--primary);
}

button {
    width: 100%;
    padding: 18px;
    border: none;
    border-radius: 16px;
    background: var(--accent-gradient);
    color: #000;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
    transition: 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    margin-top: 15px;
    text-transform: uppercase;
}

button:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(255, 170, 0, 0.3);
}

.login-link {
    margin-top: 30px;
    text-align: center;
    font-size: 14px;
    color: var(--text-dim);
}

.login-link a {
    color: var(--primary);
    font-weight: 700;
    text-decoration: none;
    margin-left: 5px;
}

.login-link a:hover { text-decoration: underline; }
</style>
</head>

<body>

<div class="wrapper">

    <div class="brand">
        <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Legrand Logo">
        <h1>Legrand</h1>
        <p>Commencez votre ascension dès maintenant</p>
    </div>

    <?php if ($error == 'exists'): ?>
        <div class="alert">
            <i class="fas fa-exclamation-triangle"></i> Pseudo ou Email déjà utilisé.
        </div>
    <?php elseif ($error == 'mismatch'): ?>
        <div class="alert">
            <i class="fas fa-lock"></i> Les mots de passe ne correspondent pas.
        </div>
    <?php endif; ?>

    <form method="post" action="signup_process.php">
        <label>Nom d'utilisateur</label>
        <div class="input-group">
            <i class="fas fa-user-plus"></i>
            <input type="text" name="username" placeholder="Choisissez un pseudo" required>
        </div>

        <label>Adresse Email</label>
        <div class="input-group">
            <i class="fas fa-envelope"></i>
            <input type="email" name="email" placeholder="votre@email.com" required>
        </div>

        <label>Mot de passe</label>
        <div class="input-group">
            <i class="fas fa-shield-alt"></i>
            <input type="password" name="password" placeholder="Mot de passe robuste" required>
        </div>

        <label>Confirmer le mot de passe</label>
        <div class="input-group">
            <i class="fas fa-check-double"></i>
            <input type="password" name="password_confirm" placeholder="Répétez le mot de passe" required>
        </div>

        <button type="submit">Créer mon compte gratuit</button>
    </form>

    <div class="login-link">
        Déjà membre ? <a href="login.php">Connectez-vous ici</a>
    </div>

</div>

</body>
</html>