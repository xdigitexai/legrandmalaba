<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "config/auth.php";
require_once "config/database.php";

/* =========================
   AUTH GUARD
========================= */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];
$notifications = [];
$error = "";

/* =========================
   RÉCUPÉRATION DES NOTIFICATIONS
========================= */
try {
    // On récupère les notifications avant de les marquer comme lues pour garder le style "non lu" à l'affichage
    $stmt = $db->prepare("
        SELECT id, message, is_read, created_at
        FROM notifications
        WHERE user_id = ?
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $stmt->execute([$userId]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Mark all as read (pour la prochaine visite)
    $db->prepare("
        UPDATE notifications 
        SET is_read = 1 
        WHERE user_id = ? AND is_read = 0
    ")->execute([$userId]);

} catch (Exception $e) {
    $error = "Impossible de charger les notifications.";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Notifications | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">

<style>
:root {
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.1);
    --bg: #050507;
    --surface: #111114;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.05);
}

* { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Outfit', sans-serif; }
body { background: var(--bg); color: var(--text); min-height: 100vh; padding: 20px; }

.wrapper {
    max-width: 700px;
    margin: 40px auto;
    background: var(--surface);
    border-radius: 30px;
    padding: 35px;
    border: 1px solid var(--border);
    box-shadow: 0 40px 100px rgba(0,0,0,0.4);
}

.back-link {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    color: var(--primary);
    font-weight: 700;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 25px;
    transition: 0.3s;
}
.back-link:hover { transform: translateX(-5px); opacity: 0.8; }

h1 {
    font-size: 28px;
    font-weight: 800;
    margin-bottom: 30px;
    display: flex;
    align-items: center;
    gap: 15px;
    letter-spacing: -1px;
}

.alert-error {
    background: rgba(248, 113, 113, 0.1);
    color: #f87171;
    padding: 15px;
    border-radius: 15px;
    border: 1px solid rgba(248, 113, 113, 0.2);
    font-size: 14px;
    margin-bottom: 20px;
}

.notif-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.notif-item {
    padding: 20px;
    background: rgba(255, 255, 255, 0.02);
    border: 1px solid var(--border);
    border-radius: 20px;
    transition: 0.3s;
    position: relative;
    overflow: hidden;
}

.notif-item:hover {
    border-color: var(--primary-glow);
    background: rgba(255, 255, 255, 0.04);
}

.notif-item.unread {
    border-left: 4px solid var(--primary);
    background: var(--primary-glow);
}

.notif-item.unread::after {
    content: '';
    position: absolute;
    top: 20px;
    right: 20px;
    width: 8px;
    height: 8px;
    background: var(--primary);
    border-radius: 50%;
    box-shadow: 0 0 10px var(--primary);
}

.notif-msg {
    font-size: 15px;
    line-height: 1.5;
    color: #e2e8f0;
    margin-bottom: 10px;
    padding-right: 20px;
}

.notif-time {
    font-size: 12px;
    color: var(--text-dim);
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 6px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
}
.empty-state i {
    font-size: 50px;
    color: var(--primary);
    opacity: 0.2;
    margin-bottom: 20px;
}
.empty-state p {
    color: var(--text-dim);
    font-weight: 500;
}
</style>
</head>

<body>

<div class="wrapper">

    <a href="dashboard.php" class="back-link">
        <i class="fas fa-arrow-left"></i> Retour au tableau de bord
    </a>

    <h1><i class="fas fa-bell" style="color: var(--primary);"></i> Notifications</h1>

    <?php if ($error): ?>
        <div class="alert-error">
            <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
        </div>

    <?php elseif (empty($notifications)): ?>
        <div class="empty-state">
            <i class="fas fa- inbox"></i>
            <p>Vous n'avez aucune notification pour le moment.</p>
        </div>

    <?php else: ?>
        <div class="notif-list">
            <?php foreach ($notifications as $n): ?>
                <div class="notif-item <?= $n['is_read'] ? '' : 'unread' ?>">
                    <div class="notif-msg">
                        <?= htmlspecialchars($n['message']) ?>
                    </div>
                    <div class="notif-time">
                        <i class="far fa-clock"></i>
                        <?= date("d M Y à H:i", strtotime($n['created_at'])) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

</div>

</body>
</html>