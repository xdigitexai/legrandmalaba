<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "config/auth.php";
require_once "config/database.php";

/* 🔐 Protect wallet */
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$userId = $_SESSION['user_id'];

/* ======================
   FETCH USER BALANCE
====================== */
$stmt = $db->prepare("SELECT balance FROM users WHERE id = ?");
$stmt->execute([$userId]);
$userBalance = (float)$stmt->fetchColumn();

/* ======================
   FETCH NOTIFICATIONS
====================== */
$stmt = $db->prepare("
    SELECT COUNT(*) 
    FROM notifications 
    WHERE user_id = ? AND is_read = 0
");
$stmt->execute([$userId]);
$unreadCount = (int)$stmt->fetchColumn();

/* ======================
   FETCH WALLET TRANSACTIONS
====================== */
$stmt = $db->prepare("
    SELECT type, amount, balance_after, reference, created_at
    FROM wallet_transactions
    WHERE user_id = ?
    ORDER BY id DESC
    LIMIT 20
");
$stmt->execute([$userId]);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);

/* ======================
   SETTINGS (HEADER / FOOTER)
====================== */
$settings = $db->query("
    SELECT header_code, footer_code
    FROM settings
    LIMIT 1
")->fetch(PDO::FETCH_ASSOC);

/* ======================
   USD ONLY (HARDCODED)
====================== */
$currencySymbol = '$';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Wallet | Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Segoe UI,Tahoma}
body{background:#f4f6fb;min-height:100vh;padding:20px}

/* TOP ACTIONS */
.top-actions{
    position:fixed;top:14px;right:14px;z-index:9999;
    display:flex;gap:12px
}
.top-actions a{
    background:#fff;padding:8px 12px;border-radius:30px;
    display:flex;align-items:center;gap:6px;
    text-decoration:none;font-size:13px;
    color:#203a43;
    box-shadow:0 4px 12px rgba(0,0,0,.12)
}
.notify{position:relative}
.notify .badge{
    position:absolute;top:-6px;right:-6px;
    background:#e74c3c;color:#fff;
    font-size:10px;padding:2px 6px;border-radius:50%
}

/* CARD */
.card{
    background:#fff;border-radius:18px;
    padding:25px;max-width:520px;
    margin:90px auto 0;
    box-shadow:0 15px 40px rgba(0,0,0,.08)
}

.balance{
    background:linear-gradient(135deg,#203a43,#2c5364);
    color:#fff;padding:25px;border-radius:16px;
    text-align:center;margin-bottom:25px
}
.balance h3{font-size:14px;opacity:.9}
.balance p{font-size:32px;font-weight:700;margin-top:8px}

.btn{
    width:100%;padding:15px;border:none;
    border-radius:14px;font-size:16px;
    font-weight:600;cursor:pointer
}
.btn-fund{background:#22c55e;color:#fff;margin-bottom:18px}

.section h4{
    font-size:16px;margin-bottom:12px;color:#203a43
}

.tx{
    display:flex;justify-content:space-between;
    padding:12px;border-bottom:1px solid #eee;
    font-size:13px
}
.tx:last-child{border-bottom:none}

.tx .left{max-width:65%}
.tx .type{font-weight:600}
.tx .ref{font-size:11px;color:#777}

.positive{color:#166534;font-weight:600}
.negative{color:#991b1b;font-weight:600}

.empty{
    text-align:center;font-size:13px;color:#777;padding:15px
}
</style>
</head>

<body>

<!-- TOP ACTIONS -->
<div class="top-actions">
    <a href="wallet.php">
        <i class="fas fa-wallet"></i>
        <?= $currencySymbol ?> <?= number_format($userBalance,2) ?>
    </a>

    <a href="my_orders.php">
        <i class="fas fa-list"></i> My Orders
    </a>

    <a href="notifications.php" class="notify">
        <i class="fas fa-bell"></i>
        <?php if($unreadCount): ?>
            <span class="badge"><?= $unreadCount ?></span>
        <?php endif; ?>
    </a>

    <a href="profile.php">
        <i class="fas fa-user-circle"></i>
    </a>
</div>

<?php if(!empty($settings['header_code'])) echo $settings['header_code']; ?>

<div class="card">

    <div class="balance">
        <h3>Available Balance</h3>
        <p><?= $currencySymbol ?> <?= number_format($userBalance,2) ?></p>
    </div>

    <button class="btn btn-fund" onclick="location.href='add-funds.php'">
        Add Funds
    </button>

    <div class="section">
        <h4>Wallet History</h4>

        <?php if (empty($transactions)): ?>
            <div class="empty">No wallet transactions yet</div>
        <?php else: ?>
            <?php foreach ($transactions as $tx): ?>
                <div class="tx">
                    <div class="left">
                        <div class="type">
                            <?= ucfirst($tx['type']) ?>
                        </div>
                        <div class="ref">
                            <?= htmlspecialchars($tx['reference']) ?>
                            • <?= date("d M Y H:i", strtotime($tx['created_at'])) ?>
                        </div>
                    </div>

                    <div class="<?= $tx['amount'] >= 0 ? 'positive' : 'negative' ?>">
                        <?= $currencySymbol ?>
                        <?= number_format($tx['amount'],2) ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>

<?php if(!empty($settings['footer_code'])) echo $settings['footer_code']; ?>

</body>
</html>