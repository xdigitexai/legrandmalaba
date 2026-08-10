<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['msmbilisim_adminlogin'])) {
    header("Location: /admin/login.php"); exit;
}

$message = $error = '';

$clients = $db->query("SELECT client_id, username, email, balance FROM clients ORDER BY username ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)($_POST['user_id'] ?? 0);
    $amount  = (float)($_POST['amount'] ?? 0);
    $note    = trim($_POST['note'] ?? 'Dépôt manuel Admin');
    $method  = trim($_POST['method'] ?? 'manual');

    if ($user_id <= 0 || $amount <= 0) {
        $error = "Veuillez sélectionner un utilisateur et entrer un montant valide.";
    } else {
        try {
            // Insert approved payment
            $db->prepare("INSERT INTO payments (client_id, payment_amount, payment_method, payment_status, payment_note, payment_create_date, payment_update_date)
                          VALUES (?, ?, ?, 3, ?, NOW(), NOW())")
               ->execute([$user_id, $amount, $method, $note]);

            // Credit client balance
            $db->prepare("UPDATE clients SET balance = balance + ? WHERE client_id = ?")->execute([$amount, $user_id]);

            $message = "Dépôt de " . number_format($amount, 2) . " ajouté avec succès.";
        } catch (Exception $e) {
            $error = "Erreur: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Dépôt Manuel</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Arial,sans-serif;background:#f5f7fa;margin:0;padding:20px;color:#333}
.container{max-width:600px;margin:auto}
.card{background:#fff;border-radius:12px;padding:30px;box-shadow:0 2px 8px rgba(0,0,0,.08)}
h2{text-align:center;margin-bottom:24px}
.form-group{margin-bottom:16px}
label{display:block;font-weight:600;margin-bottom:6px;font-size:13px}
select,input{width:100%;padding:10px;border:1px solid #ddd;border-radius:8px;font-size:14px;box-sizing:border-box}
.btn-submit{width:100%;padding:12px;background:#28a745;color:#fff;border:none;border-radius:8px;font-size:15px;cursor:pointer;font-weight:600;margin-top:8px}
.btn-submit:hover{background:#218838}
.msg{padding:12px;border-radius:8px;margin-bottom:16px}
.msg-ok{background:#d4edda;color:#155724;border:1px solid #c3e6cb}
.msg-err{background:#f8d7da;color:#721c24;border:1px solid #f5c6cb}
.back{display:inline-block;padding:8px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none;margin-bottom:16px}
</style>
</head>
<body>
<div class="container">
<a class="back" href="/admin">&larr; Admin</a>
<div class="card">
<h2><i class="fa-solid fa-plus-circle"></i> Dépôt Manuel</h2>
<?php if ($message): ?><div class="msg msg-ok"><?= htmlspecialchars($message) ?></div><?php endif; ?>
<?php if ($error):   ?><div class="msg msg-err"><?= htmlspecialchars($error) ?></div><?php endif; ?>
<form method="post">
<div class="form-group">
  <label>Utilisateur</label>
  <select name="user_id" required>
    <option value="">-- Sélectionner --</option>
    <?php foreach ($clients as $c): ?>
    <option value="<?= $c['client_id'] ?>"><?= htmlspecialchars($c['username']) ?> (<?= htmlspecialchars($c['email']) ?>) — Solde: <?= number_format($c['balance'],2) ?></option>
    <?php endforeach; ?>
  </select>
</div>
<div class="form-group">
  <label>Montant</label>
  <input type="number" name="amount" min="0.01" step="0.01" placeholder="0.00" required>
</div>
<div class="form-group">
  <label>Méthode de paiement</label>
  <input type="text" name="method" value="manual" placeholder="manual, bank, etc.">
</div>
<div class="form-group">
  <label>Note (optionnel)</label>
  <input type="text" name="note" placeholder="Dépôt manuel Admin">
</div>
<button type="submit" class="btn-submit"><i class="fa fa-save"></i> Ajouter le dépôt</button>
</form>
</div>
</div>
</body>
</html>
