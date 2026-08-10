<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/auth.php";
require_once "../config/database.php";

/* =========================
   ADMIN GUARD
========================= */
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    die("Access denied");
}

/* =========================
   FETCH DEPOSITS
========================= */
$stmt = $db->query("
    SELECT 
        d.payment_id,
        d.payment_amount,
        d.payment_status,
        d.t_id,
        d.payment_create_date,
        u.username,
        u.email
    FROM payments d
    JOIN clients u ON u.client_id = d.client_id
    ORDER BY d.payment_id DESC
");
$deposits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin | Deposit Logs</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
*{box-sizing:border-box;margin:0;padding:0;font-family:Segoe UI,Arial}

body{
    background:#f5f7fb;
    min-height:100vh;
    display:flex;
}

/* =====================
   SIDEBAR
===================== */
.sidebar{
    width:240px;
    background:#203a43;
    color:#fff;
    padding:20px;
    position:fixed;
    top:0;left:0;bottom:0;
    z-index:1001;
}
.sidebar h2{margin-bottom:25px}
.sidebar a{
    display:block;
    color:#fff;
    text-decoration:none;
    padding:12px;
    border-radius:8px;
    margin-bottom:6px;
    font-size:14px;
}
.sidebar a:hover{background:#2c5364}

/* =====================
   OVERLAY
===================== */
.overlay{
    position:fixed;
    inset:0;
    background:rgba(0,0,0,.4);
    z-index:1000;
    display:none;
}

/* =====================
   MAIN
===================== */
.main{
    margin-left:240px;
    padding:25px;
    width:100%;
}

/* =====================
   HEADER
===================== */
.header{
    display:flex;
    align-items:center;
    gap:12px;
    margin-bottom:20px;
}
.menu-btn{
    display:none;
    font-size:22px;
    cursor:pointer;
    background:#203a43;
    color:#fff;
    padding:8px 12px;
    border-radius:8px;
}

/* =====================
   TABLE
===================== */
h2{
    margin-bottom:15px;
    color:#203a43;
}
.table{
    background:#fff;
    border-radius:14px;
    overflow:hidden;
    box-shadow:0 10px 30px rgba(0,0,0,.1);
}
table{
    width:100%;
    border-collapse:collapse;
    font-size:14px;
}
th,td{
    padding:12px 14px;
    border-bottom:1px solid #eee;
}
th{
    background:#203a43;
    color:#fff;
    font-size:13px;
    text-transform:uppercase;
}
tr:hover{background:#f4f7fb}

.status{
    font-weight:700;
    font-size:12px;
    padding:4px 10px;
    border-radius:20px;
    display:inline-block;
}
.completed{background:#dcfce7;color:#166534}
.pending{background:#fef3c7;color:#92400e}
.failed{background:#fee2e2;color:#991b1b}

/* =====================
   MOBILE
===================== */
@media(max-width:768px){
    .sidebar{
        transform:translateX(-100%);
        transition:.3s;
    }
    .sidebar.show{transform:translateX(0)}
    .overlay.show{display:block}
    .main{margin-left:0}
    .menu-btn{display:block}
}
</style>
</head>

<body>

<!-- OVERLAY -->
<div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

<!-- SIDEBAR -->
<div class="sidebar" id="sidebar">
    <h2>Cheap Panel</h2>
    <a href="dashboard.php">Dashboard</a>
    <a href="orders.php">Orders</a>
    <a href="users.php">Users</a>
    <a href="manual_deposit.php">Add Payment</a>
    <a href="deposits.php">Deposit Logs</a>
    <a href="pricing.php">Pricing</a>
    <a href="services.php">Services</a>
    <a href="settings.php">Settings</a>
    <a href="/" target="_blank">View Site</a>
</div>

<!-- MAIN -->
<div class="main">

    <div class="header">
        <div class="menu-btn" onclick="toggleSidebar()">☰</div>
        <h2>💰 Deposit Logs</h2>
    </div>

    <div class="table">
        <table>
            <thead>
            <tr>
                <th>ID</th>
                <th>User</th>
                <th>Email</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Checkout ID</th>
                <th>Date</th>
            </tr>
            </thead>
            <tbody>

            <?php if (!$deposits): ?>
                <tr><td colspan="7">No deposits found</td></tr>
            <?php endif; ?>

            <?php foreach ($deposits as $d): ?>
            <tr>
                <td><?= $d['payment_id'] ?></td>
                <td><?= htmlspecialchars($d['username']) ?></td>
                <td><?= htmlspecialchars($d['email']) ?></td>
                <td><?= number_format($d['payment_amount'],2) ?></td>
                <td>
                    <span class="status <?= $d['payment_status'] ?>">
                        <?= strtoupper($d['payment_status']) ?>
                    </span>
                </td>
                <td><?= htmlspecialchars($d['t_id'] ?? '-') ?></td>
                <td><?= date("d M Y H:i", strtotime($d['payment_create_date'])) ?></td>
            </tr>
            <?php endforeach; ?>

            </tbody>
        </table>
    </div>

</div>

<script>
function toggleSidebar(){
    document.getElementById("sidebar").classList.toggle("show");
    document.getElementById("overlay").classList.toggle("show");
}
</script>

</body>
</html>
