<?php
require_once "config/auth.php";
require_once "config/database.php";

$orderId = (int)($_GET['order_id'] ?? 0);

if (!$orderId) {
    header("Location: dashboard.php");
    exit;
}

/* ===============================
   FETCH ORDER DETAILS
================================ */
$stmt = $db->prepare("
    SELECT 
        o.order_ref,
        o.quantity,
        o.price,
        o.status,
        o.link,
        s.name AS service_name,
        u.balance
    FROM orders o
    JOIN services s ON s.id = o.service_id
    JOIN users u ON u.id = o.user_id
    WHERE o.id = ? AND o.user_id = ?
    LIMIT 1
");
$stmt->execute([$orderId, $_SESSION['user_id']]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    header("Location: dashboard.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Order Received | Cheap Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body{
    font-family:Arial, Helvetica, sans-serif;
    background:#f5f7fb;
    padding:20px;
}
.box{
    background:#d1fae5;
    border:1px solid #86efac;
    border-radius:12px;
    padding:20px;
    max-width:720px;
    margin:40px auto;
}
h2{
    color:#065f46;
    margin-bottom:12px;
}
p{
    font-size:14px;
    margin:6px 0;
}
strong{
    color:#064e3b;
}
.notice{
    margin-top:15px;
    font-size:13px;
    color:#065f46;
}
.countdown{
    margin-top:10px;
    font-size:12px;
    color:#047857;
}
a{
    color:#065f46;
    font-weight:bold;
    text-decoration:none;
}
</style>
</head>

<body>

<div class="box">
    <h2>✅ Your order received</h2>

    <p><strong>Order ID:</strong> <?= htmlspecialchars($order['order_ref']) ?></p>
    <p><strong>Service:</strong> <?= htmlspecialchars($order['service_name']) ?></p>
    <p><strong>Link:</strong> <?= htmlspecialchars($order['link']) ?></p>
    <p><strong>Quantity:</strong> <?= number_format($order['quantity']) ?></p>
    <p><strong>Charge:</strong> <?= number_format($order['price'], 2) ?> KES</p>
    <p><strong>Balance:</strong> <?= number_format($order['balance'], 2) ?> KES</p>
    <p><strong>Status:</strong> <?= ucfirst($order['status']) ?></p>

    <div class="notice">
        Your order has been sent for processing.  
        Please do not submit the same order again.
    </div>

    <div class="countdown">
        You will be redirected to your dashboard in <span id="sec">5</span> seconds…
    </div>

    <br>
    <a href="dashboard.php">Go to Dashboard now</a>
</div>

<script>
let seconds = 5;
const el = document.getElementById("sec");

const timer = setInterval(() => {
    seconds--;
    el.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(timer);
        window.location.href = "dashboard.php";
    }
}, 1000);
</script>

</body>
</html>