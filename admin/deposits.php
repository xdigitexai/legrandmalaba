<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
require_once __DIR__ . "/../config/database.php";

if (!isset($_SESSION['admin_logged_in']) && !isset($_SESSION['msmbilisim_adminlogin'])) {
    header("Location: /admin/login.php");
    exit;
}

$message = '';

// Handle manual payment approval
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['payment_id'])) {
    $pid    = (int)$_POST['payment_id'];
    $action = $_POST['action'];
    if ($action === 'approve') {
        // Get payment info
        $pay = $db->prepare("SELECT * FROM payments WHERE payment_id = ?");
        $pay->execute([$pid]);
        $pay = $pay->fetch();
        if ($pay && $pay['payment_status'] == 1) {
            // Approve: set status=3, credit client balance
            $db->prepare("UPDATE payments SET payment_status=3 WHERE payment_id=?")->execute([$pid]);
            $db->prepare("UPDATE clients SET balance = balance + ? WHERE client_id=?")->execute([$pay['payment_amount'], $pay['client_id']]);
            $message = "Payment #{$pid} approved and balance credited.";
        }
    } elseif ($action === 'reject') {
        $db->prepare("UPDATE payments SET payment_status=2 WHERE payment_id=?")->execute([$pid]);
        $message = "Payment #{$pid} rejected.";
    }
    header("Location: deposits.php?msg=" . urlencode($message));
    exit;
}

// Fetch pending payments
$payments = $db->query("
    SELECT p.*, c.username, c.email
    FROM payments p
    LEFT JOIN clients c ON c.client_id = p.client_id
    WHERE p.payment_status = 1
    ORDER BY p.payment_id DESC
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin | Dépôts en attente</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<style>
body{font-family:Arial,sans-serif;background:#f5f7fa;margin:0;padding:20px;color:#333}
.container{max-width:1200px;margin:auto}
h2{text-align:center;margin:20px 0}
.msg{padding:12px;background:#d4edda;border:1px solid #c3e6cb;border-radius:8px;margin-bottom:16px;color:#155724}
table{width:100%;border-collapse:collapse;background:#fff;border-radius:10px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,.05)}
th{background:#343a40;color:#fff;padding:12px 10px;font-size:13px;text-align:left}
td{padding:10px;font-size:13px;border-bottom:1px solid #f0f0f0}
tr:hover td{background:#f9f9f9}
.btn{padding:6px 12px;border:none;border-radius:6px;cursor:pointer;font-size:12px;color:#fff}
.btn-approve{background:#28a745}.btn-reject{background:#dc3545}
.back{display:inline-block;padding:8px 16px;background:#6c757d;color:#fff;border-radius:6px;text-decoration:none;margin-bottom:16px}
</style>
</head>
<body>
<div class="container">
<a class="back" href="/admin">&larr; Admin</a>
<h2><i class="fa-solid fa-clock"></i> Dépôts en attente</h2>
<?php if (!empty($_GET['msg'])): ?>
<div class="msg"><?= htmlspecialchars($_GET['msg']) ?></div>
<?php endif; ?>
<table>
<thead><tr>
  <th>#ID</th><th>Client</th><th>Email</th><th>Méthode</th>
  <th>Montant</th><th>Date</th><th>Note</th><th>Actions</th>
</tr></thead>
<tbody>
<?php if (empty($payments)): ?>
<tr><td colspan="8" style="text-align:center;padding:20px">Aucun dépôt en attente</td></tr>
<?php else: foreach ($payments as $p): ?>
<tr>
  <td><?= $p['payment_id'] ?></td>
  <td><?= htmlspecialchars($p['username'] ?? '-') ?></td>
  <td><?= htmlspecialchars($p['email'] ?? '-') ?></td>
  <td><?= htmlspecialchars($p['payment_method'] ?? '-') ?></td>
  <td><?= number_format($p['payment_amount'], 2) ?></td>
  <td><?= htmlspecialchars($p['payment_create_date'] ?? '-') ?></td>
  <td><?= htmlspecialchars($p['payment_note'] ?? '-') ?></td>
  <td>
    <form method="post" style="display:inline">
      <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
      <input type="hidden" name="action" value="approve">
      <button class="btn btn-approve" onclick="return confirm('Approuver ce dépôt?')"><i class="fa fa-check"></i> Approuver</button>
    </form>
    <form method="post" style="display:inline">
      <input type="hidden" name="payment_id" value="<?= $p['payment_id'] ?>">
      <input type="hidden" name="action" value="reject">
      <button class="btn btn-reject" onclick="return confirm('Rejeter ce dépôt?')"><i class="fa fa-times"></i> Rejeter</button>
    </form>
  </td>
</tr>
<?php endforeach; endif; ?>
</tbody>
</table>
</div>
</body>
</html>
