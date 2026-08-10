<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

session_start();
require_once "../config/database.php";
require_once "includes/refund_helper.php";

/* =========================
    ADMIN GUARD
========================= */
if (!isset($_SESSION['admin_logged_in'])) {
    header("Location: login.php");
    exit;
}

/* =========================
    HANDLE BULK ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_action'], $_POST['order_ids'])) {
    $orderIds   = $_POST['order_ids'];
    $bulkAction = $_POST['bulk_action'];

    foreach ($orderIds as $id) {
        $id = (int)$id;

        if ($bulkAction === 'resend') {
            $stmt = $db->prepare("SELECT o.*, s.api_service, sa.api_url, sa.api_key FROM orders o LEFT JOIN services s ON s.service_id = o.service_id LEFT JOIN service_api sa ON sa.id = s.service_api WHERE o.order_id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($order && !empty($order['api_url'])) {
                $payload = ['key' => $order['api_key'], 'action' => 'add', 'service' => $order['provider_service_id'], 'link' => $order['link'], 'quantity' => $order['quantity']];
                $ch = curl_init($order['api_url']);
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($payload), CURLOPT_TIMEOUT => 30]);
                $response = curl_exec($ch);
                curl_close($ch);
                $result = json_decode($response, true);
                if (isset($result['order'])) {
                    $db->prepare("UPDATE orders SET api_orderid = ?, order_error = ?, order_status = 'processing', last_check = NULL WHERE order_id = ?")->execute([$result['order'], $response, $id]);
                }
            }
        } elseif ($bulkAction === 'cancel') {
            $stmt = $db->prepare("SELECT o.*, sa.api_url, sa.api_key FROM orders o LEFT JOIN services s ON s.service_id = o.service_id LEFT JOIN service_api sa ON sa.id = s.service_api WHERE o.order_id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($order) {
                if (!empty($order['api_orderid']) && !empty($order['api_url'])) {
                    $payload = ['key' => $order['api_key'], 'action' => 'cancel', 'order' => $order['api_orderid']];
                    $ch = curl_init($order['api_url']);
                    curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($payload), CURLOPT_TIMEOUT => 30]);
                    $response = curl_exec($ch);
                    curl_close($ch);
                    $db->prepare("UPDATE orders SET order_status = 'canceled', order_error = ? WHERE order_id = ?")->execute([$response, $id]);
                } else {
                    $db->prepare("UPDATE orders SET order_status = 'canceled' WHERE order_id = ?")->execute([$id]);
                }
                $stmtRef = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
                $stmtRef->execute([$id]);
                $updatedOrder = $stmtRef->fetch(PDO::FETCH_ASSOC);
                if ($updatedOrder) autoRefund($db, $updatedOrder);
            }
        } else {
            $bulkCompletionSql = ($bulkAction === 'completed') ? ', completion_time = NOW()' : ', completion_time = NULL';
            $db->prepare("UPDATE orders SET order_status = ?{$bulkCompletionSql} WHERE order_id = ?")->execute([$bulkAction, $id]);
            $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
            $stmt->execute([$id]);
            $order = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($order) autoRefund($db, $order);
        }
    }
    header("Location: orders.php");
    exit;
}

/* =========================
    HANDLE SINGLE ACTIONS
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_POST['bulk_action'])) {

    if (isset($_POST['order_id'], $_POST['new_status'])) {
        $orderId   = $_POST['order_id'];
        $newStatus = $_POST['new_status'];
        $completionSql = ($newStatus === 'completed') ? ', completion_time = NOW()' : ', completion_time = NULL';
        $db->prepare("UPDATE orders SET order_status = ?{$completionSql} WHERE order_id = ?")->execute([$newStatus, $orderId]);
        $stmt = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($order) autoRefund($db, $order);
        header("Location: orders.php"); exit;
    }

    if (isset($_POST['action'], $_POST['order_id'])) {
        $action  = $_POST['action'];
        $orderId = (int)$_POST['order_id'];

        $stmt = $db->prepare("
            SELECT o.*, s.api_service, sa.api_url, sa.api_key
            FROM orders o
            LEFT JOIN services s ON s.service_id = o.service_id
            LEFT JOIN service_api sa ON sa.id = s.service_api
            WHERE o.order_id = ? LIMIT 1
        ");
        $stmt->execute([$orderId]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) { header("Location: orders.php"); exit; }

        if ($action === 'cancel') {
            if (!empty($order['api_orderid']) && !empty($order['api_url'])) {
                $payload = ['key' => $order['api_key'], 'action' => 'cancel', 'order' => $order['api_orderid']];
                $ch = curl_init($order['api_url']);
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($payload), CURLOPT_TIMEOUT => 30]);
                $response = curl_exec($ch); curl_close($ch);
                $db->prepare("UPDATE orders SET order_status = 'canceled', order_error = ? WHERE order_id = ?")->execute([$response, $orderId]);
            } else {
                $db->prepare("UPDATE orders SET order_status = 'canceled' WHERE order_id = ?")->execute([$orderId]);
            }
            $stmtRef = $db->prepare("SELECT * FROM orders WHERE order_id = ?");
            $stmtRef->execute([$orderId]);
            $updatedOrder = $stmtRef->fetch(PDO::FETCH_ASSOC);
            if ($updatedOrder) autoRefund($db, $updatedOrder);
            header("Location: orders.php"); exit;
        }

        if ($action === 'resend') {
            if (!empty($order['api_url']) && !empty($order['provider_service_id'])) {
                $payload = ['key' => $order['api_key'], 'action' => 'add', 'service' => $order['provider_service_id'], 'link' => $order['link'], 'quantity' => $order['quantity']];
                $ch = curl_init($order['api_url']);
                curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true, CURLOPT_POSTFIELDS => http_build_query($payload), CURLOPT_TIMEOUT => 30]);
                $response = curl_exec($ch); curl_close($ch);
                $result = json_decode($response, true);
                if (isset($result['order'])) {
                    $db->prepare("UPDATE orders SET api_orderid = ?, order_error = ?, order_status = 'processing', last_check = NULL WHERE order_id = ?")->execute([$result['order'], $response, $orderId]);
                }
            }
            header("Location: orders.php"); exit;
        }
    }
}

/* =========================
    PAGINATION + FILTERING
========================= */
$statusFilter = $_GET['status'] ?? 'all';

$perPage     = 50;
$currentPage = max(1, (int)($_GET['page'] ?? 1));
$offset      = ($currentPage - 1) * $perPage;

$whereClause = '';
if ($statusFilter !== 'all') {
    $whereClause = " WHERE o.order_status = " . $db->quote($statusFilter);
}

$totalOrders = (int)$db->query("SELECT COUNT(*) FROM orders o" . $whereClause)->fetchColumn();
$totalPages  = max(1, (int)ceil($totalOrders / $perPage));
$currentPage = min($currentPage, $totalPages);

$query = "
    SELECT
        o.order_id, o.order_url, o.order_quantity, o.order_start, o.order_remains, o.order_charge,
        o.order_status, o.order_create, o.completion_time, o.start_time,
        c.username, c.email,
        COALESCE( s.service_name, 'Service Removed') AS service_name
    FROM orders o
    LEFT JOIN clients c ON c.client_id = o.client_id
    LEFT JOIN services s ON s.service_id = o.service_id
    {$whereClause}
    ORDER BY o.order_create DESC
    LIMIT {$perPage} OFFSET {$offset}
";
$orders = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="UTF-8">
<title>Commandes | Legrand</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@500;700&family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
:root {
    --bg: #050507;
    --surface: #111114;
    --surface2: #18181d;
    --primary: #ffaa00;
    --primary-glow: rgba(255, 170, 0, 0.12);
    --success: #4ade80;
    --warning: #fbbf24;
    --danger: #f87171;
    --info: #38bdf8;
    --purple: #a78bfa;
    --text: #ffffff;
    --text-dim: #94a3b8;
    --border: rgba(255, 255, 255, 0.06);
    --sidebar-w: 260px;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Outfit', sans-serif;
    min-height: 100vh;
    overflow-x: hidden;
}

/* ═══════════════════════════════════════
   SIDEBAR
═══════════════════════════════════════ */
.sidebar {
    position: fixed;
    top: 0; left: 0; bottom: 0;
    width: var(--sidebar-w);
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: 28px 16px;
    display: flex;
    flex-direction: column;
    gap: 6px;
    z-index: 1001;
    transform: translateX(-100%);
    transition: transform .28s cubic-bezier(.4,0,.2,1);
    overflow-y: auto;
}
.sidebar.open { transform: translateX(0); }

@media (min-width: 1025px) {
    .sidebar { transform: translateX(0); }
    .main { margin-left: var(--sidebar-w); }
    .hamburger { display: none !important; }
}

.sidebar-logo {
    display: flex; align-items: center; gap: 12px;
    padding: 4px 10px 28px;
    text-decoration: none;
}
.logo-icon {
    width: 42px; height: 42px;
    background: var(--primary-glow);
    border: 1px solid rgba(255,170,0,.2);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.logo-icon img { width: 28px; }
.logo-text {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 17px; font-weight: 700;
    color: #fff; letter-spacing: -0.5px;
}
.logo-text span { color: var(--primary); }

.nav-label {
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 1.5px;
    color: var(--text-dim);
    text-transform: uppercase;
    padding: 14px 12px 6px;
    opacity: 0.5;
}

.sidebar a {
    display: flex; align-items: center; gap: 11px;
    color: var(--text-dim);
    text-decoration: none;
    padding: 11px 14px;
    border-radius: 12px;
    font-size: 14px; font-weight: 600;
    transition: .18s;
}
.sidebar a i { width: 18px; text-align: center; font-size: 14px; }
.sidebar a:hover { color: #fff; background: rgba(255,255,255,.04); }
.sidebar a.active {
    color: var(--primary);
    background: var(--primary-glow);
    border: 1px solid rgba(255,170,0,.15);
}

.sidebar-footer {
    margin-top: auto;
    padding-top: 20px;
    border-top: 1px solid var(--border);
}
.sidebar-footer a {
    color: var(--danger) !important;
}

/* ═══════════════════════════════════════
   OVERLAY
═══════════════════════════════════════ */
.overlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(0,0,0,.75);
    backdrop-filter: blur(3px);
    z-index: 1000;
}
.overlay.show { display: block; }

/* ═══════════════════════════════════════
   MAIN
═══════════════════════════════════════ */
.main {
    min-height: 100vh;
    padding: 0;
    transition: margin-left .28s ease;
}

/* Topbar */
.topbar {
    position: sticky; top: 0; z-index: 100;
    display: flex; align-items: center; justify-content: space-between;
    background: rgba(5,5,7,.85);
    backdrop-filter: blur(12px);
    border-bottom: 1px solid var(--border);
    padding: 0 20px;
    height: 60px;
    gap: 12px;
}
.topbar-left { display: flex; align-items: center; gap: 14px; }
.hamburger {
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--primary);
    width: 38px; height: 38px;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; font-size: 16px;
    flex-shrink: 0;
}
.page-title {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 17px; font-weight: 700;
    white-space: nowrap;
}
.logout-btn {
    display: flex; align-items: center; gap: 6px;
    background: rgba(248,113,113,.08);
    border: 1px solid rgba(248,113,113,.2);
    color: var(--danger);
    text-decoration: none;
    padding: 7px 14px;
    border-radius: 10px;
    font-size: 12px; font-weight: 800;
    white-space: nowrap;
}

.content { padding: 20px; }

/* ═══════════════════════════════════════
   STAT CHIPS (total / per-status counts)
═══════════════════════════════════════ */
.stat-strip {
    display: flex; gap: 10px;
    overflow-x: auto;
    padding-bottom: 4px;
    margin-bottom: 16px;
    scrollbar-width: none;
}
.stat-strip::-webkit-scrollbar { display: none; }
.stat-chip {
    display: flex; align-items: center; gap: 8px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 12px;
    padding: 10px 16px;
    white-space: nowrap;
    flex-shrink: 0;
    text-decoration: none;
    transition: .18s;
}
.stat-chip:hover { border-color: rgba(255,255,255,.15); }
.stat-chip.active { border-color: var(--primary); background: var(--primary-glow); }
.stat-chip .chip-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    flex-shrink: 0;
}
.stat-chip .chip-label {
    font-size: 12px; font-weight: 700;
    color: var(--text-dim);
}
.stat-chip.active .chip-label { color: var(--primary); }
.stat-chip .chip-count {
    font-family: 'Space Grotesk', sans-serif;
    font-size: 14px; font-weight: 700;
    color: #fff;
}

/* ═══════════════════════════════════════
   TOOLBAR (bulk + search row)
═══════════════════════════════════════ */
.toolbar {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 10px;
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 16px;
    padding: 12px 16px;
    margin-bottom: 16px;
}
.toolbar-label { font-size: 12px; color: var(--text-dim); font-weight: 600; white-space: nowrap; }

select.bulk-select {
    background: var(--surface2);
    border: 1px solid var(--border);
    color: #fff;
    padding: 8px 12px;
    border-radius: 10px;
    font-size: 12px;
    font-family: 'Outfit', sans-serif;
    cursor: pointer;
    min-width: 150px;
}
.btn-execute {
    background: var(--primary);
    color: #000;
    border: none;
    padding: 8px 18px;
    border-radius: 10px;
    font-size: 12px; font-weight: 800;
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
    white-space: nowrap;
}

/* ═══════════════════════════════════════
   TABLE
═══════════════════════════════════════ */
.table-wrapper { position: relative; }

.table-card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: 20px;
    overflow: hidden;
}

.table-scroll { overflow-x: auto; }

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13px;
    min-width: 900px;
}
th {
    background: rgba(255,255,255,.02);
    padding: 14px 16px;
    text-align: left;
    font-size: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
    color: var(--text-dim);
    border-bottom: 1px solid var(--border);
    white-space: nowrap;
}
td {
    padding: 14px 16px;
    border-bottom: 1px solid var(--border);
    vertical-align: middle;
}
tr:last-child td { border-bottom: none; }
tr:hover td { background: rgba(255,255,255,.015); }

.check-custom {
    width: 16px; height: 16px;
    cursor: pointer;
    accent-color: var(--primary);
}

.oid { font-family: monospace; color: var(--primary); font-weight: 700; }

.user-name { font-weight: 700; color: #fff; font-size: 13px; }
.user-email { font-size: 11px; color: var(--text-dim); }

.svc-name {
    font-weight: 600; color: #fff;
    max-width: 180px;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    display: block;
    font-size: 12px;
}
.link-anchor {
    color: var(--text-dim); font-size: 11px;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
    margin-top: 2px;
}
.link-anchor:hover { color: var(--primary); }

.badge {
    font-family: monospace;
    background: rgba(255,255,255,.05);
    padding: 3px 7px;
    border-radius: 6px;
    font-size: 12px;
    color: var(--text-dim);
}
.badge.remains { color: var(--warning); }

.price-val { color: var(--success); font-weight: 700; font-size: 13px; }
.profit-val { font-size: 11px; color: var(--primary); }

/* Status pills */
.pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px;
    border-radius: 7px;
    font-size: 10px; font-weight: 800;
    text-transform: uppercase;
    border: 1px solid transparent;
    white-space: nowrap;
}
.pill.processing { background: rgba(56,189,248,.1);  color: var(--info);    border-color: rgba(56,189,248,.2); }
.pill.completed  { background: rgba(74,222,128,.1);  color: var(--success); border-color: rgba(74,222,128,.2); }
.pill.failed     { background: rgba(248,113,113,.1); color: var(--danger);  border-color: rgba(248,113,113,.2); }
.pill.cancelled  { background: rgba(148,163,184,.1); color: #94a3b8;        border-color: rgba(148,163,184,.2); }
.pill.partial    { background: rgba(251,191,36,.1);  color: var(--warning); border-color: rgba(251,191,36,.2); }
.pill.refunded   { background: rgba(167,139,250,.1); color: var(--purple);  border-color: rgba(167,139,250,.2); }
.pill.paid       { background: rgba(255,170,0,.1);   color: var(--primary); border-color: rgba(255,170,0,.2); }

/* Action cell */
.action-cell { display: flex; flex-direction: column; gap: 6px; min-width: 180px; }
.action-row-top { display: flex; gap: 5px; align-items: center; }

select.status-sel {
    background: var(--bg);
    border: 1px solid var(--border);
    color: #fff;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 11px;
    font-family: 'Outfit', sans-serif;
    flex: 1;
    cursor: pointer;
}
.btn-update {
    background: var(--primary);
    color: #000;
    border: none;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 11px; font-weight: 800;
    cursor: pointer;
    font-family: 'Outfit', sans-serif;
    white-space: nowrap;
}
.action-row-btns { display: flex; gap: 5px; }
.btn-resend, .btn-cancel {
    flex: 1;
    display: inline-flex; align-items: center; justify-content: center; gap: 4px;
    padding: 6px 8px;
    border-radius: 8px;
    font-size: 10px; font-weight: 800;
    cursor: pointer;
    border: 1px solid;
    font-family: 'Outfit', sans-serif;
    white-space: nowrap;
}
.btn-resend {
    background: rgba(56,189,248,.08);
    color: var(--info);
    border-color: rgba(56,189,248,.2);
}
.btn-resend:hover { background: rgba(56,189,248,.18); }
.btn-cancel {
    background: rgba(248,113,113,.08);
    color: var(--danger);
    border-color: rgba(248,113,113,.2);
}
.btn-cancel:hover { background: rgba(248,113,113,.18); }

/* ═══════════════════════════════════════
   PAGINATION
═══════════════════════════════════════ */
.pager {
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
    padding: 14px 18px;
    border-top: 1px solid var(--border);
}
.pager-info { font-size: 12px; color: var(--text-dim); }
.pager-info strong { color: var(--primary); }
.pager-controls { display: flex; align-items: center; gap: 5px; flex-wrap: wrap; }
.pager-btn {
    background: var(--surface2);
    border: 1px solid var(--border);
    color: var(--text-dim);
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 12px; font-family: 'Outfit', sans-serif;
    text-decoration: none;
    display: inline-flex; align-items: center; gap: 4px;
    transition: .15s;
    cursor: pointer;
}
.pager-btn:hover { border-color: var(--primary); color: var(--primary); }
.pager-btn.active { background: var(--primary-glow); border-color: var(--primary); color: var(--primary); font-weight: 700; }
.pager-btn.disabled { opacity: .3; pointer-events: none; }
.pager-ellipsis { color: var(--text-dim); font-size: 12px; padding: 0 2px; }

/* ═══════════════════════════════════════
   LOADING OVERLAY
═══════════════════════════════════════ */
#loadingOverlay {
    display: none;
    position: fixed; inset: 0;
    background: rgba(5,5,7,.6);
    backdrop-filter: blur(3px);
    z-index: 9000;
    align-items: center;
    justify-content: center;
}
#loadingOverlay.show { display: flex; }
.spinner {
    width: 40px; height: 40px;
    border: 3px solid var(--border);
    border-top-color: var(--primary);
    border-radius: 50%;
    animation: spin .7s linear infinite;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ═══════════════════════════════════════
   MOBILE CARD VIEW  (< 768px)
   Each order becomes a card instead of a row
═══════════════════════════════════════ */
@media (max-width: 767px) {
    .content { padding: 12px; }

    /* Hide desktop table */
    .table-scroll { display: none; }

    /* Show card list */
    .cards-list { display: flex; flex-direction: column; gap: 12px; padding: 12px; }

    .order-card {
        background: var(--surface2);
        border: 1px solid var(--border);
        border-radius: 16px;
        padding: 14px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    .card-top {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 8px;
    }
    .card-id { font-family: monospace; color: var(--primary); font-weight: 800; font-size: 14px; }
    .card-date { font-size: 11px; color: var(--text-dim); text-align: right; }

    .card-service {
        font-weight: 700; color: #fff; font-size: 13px;
        line-height: 1.3;
    }
    .card-user {
        font-size: 12px; color: var(--text-dim);
    }

    .card-link {
        color: var(--primary); font-size: 11px;
        text-decoration: none;
        display: inline-flex; align-items: center; gap: 4px;
        word-break: break-all;
    }

    .card-stats {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 6px;
    }
    .card-stat {
        background: rgba(255,255,255,.03);
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 8px 10px;
        text-align: center;
    }
    .card-stat-label { font-size: 9px; color: var(--text-dim); text-transform: uppercase; letter-spacing: .5px; }
    .card-stat-val { font-size: 13px; font-weight: 700; color: #fff; margin-top: 2px; font-family: 'Space Grotesk', sans-serif; }

    .card-finance {
        display: flex; align-items: center; justify-content: space-between;
    }

    .card-actions { display: flex; flex-direction: column; gap: 8px; }
    .card-action-top { display: flex; gap: 6px; }
    .card-action-btns { display: flex; gap: 6px; }

    select.status-sel { font-size: 12px; padding: 8px 10px; }
    .btn-update { padding: 8px 14px; font-size: 12px; }
    .btn-resend, .btn-cancel { padding: 8px 10px; font-size: 11px; flex: 1; }
}

@media (min-width: 768px) {
    /* Hide cards on desktop */
    .cards-list { display: none; }
}

/* ═══════════════════════════════════════
   ROW ANIMATION
═══════════════════════════════════════ */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(5px); }
    to   { opacity: 1; transform: translateY(0); }
}
tbody tr, .order-card {
    animation: fadeIn .2s ease both;
}
<?php for ($i = 1; $i <= 50; $i++): ?>
tbody tr:nth-child(<?= $i ?>), .cards-list .order-card:nth-child(<?= $i ?>) {
    animation-delay: <?= ($i - 1) * 0.01 ?>s;
}
<?php endfor; ?>

/* Empty state */
.empty-cell { text-align: center; padding: 60px 20px !important; color: var(--text-dim); }
.empty-cell i { font-size: 32px; opacity: .3; display: block; margin-bottom: 12px; }
</style>
</head>
<body>

<div id="loadingOverlay"><div class="spinner"></div></div>
<div class="overlay" id="overlay"></div>

<!-- ═══ SIDEBAR ═══ -->
<div class="sidebar" id="sidebar">
    <a href="dashboard.php" class="sidebar-logo">
        <div class="logo-icon">
            <img src="https://image2url.com/r2/default/images/1771452346611-65d20324-492d-4bd4-bdba-0e196cb84b0c.png" alt="Logo">
        </div>
        <div class="logo-text">Legrand</div>
    </a>

    <div class="nav-label">Menu</div>
    <a href="dashboard.php"><i class="fas fa-layer-group"></i> Dashboard</a>
    <a href="orders.php" class="active"><i class="fas fa-shopping-bag"></i> Commandes</a>
    <a href="users.php"><i class="fas fa-user-friends"></i> Utilisateurs</a>
    <a href="deposits.php"><i class="fas fa-credit-card"></i> Dépôts</a>
    <a href="services.php"><i class="fas fa-stream"></i> Services</a>
    <a href="provider.php"><i class="fas fa-database"></i> Fournisseurs</a>
    <a href="settings.php"><i class="fas fa-sliders-h"></i> Paramètres</a>

    <div class="sidebar-footer">
        <a href="logout.php"><i class="fas fa-power-off"></i> Déconnexion</a>
    </div>
</div>

<!-- ═══ MAIN ═══ -->
<div class="main" id="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-left">
            <div class="hamburger" id="hamBtn"><i class="fas fa-bars"></i></div>
            <span class="page-title"><i class="fas fa-shopping-bag" style="color:var(--primary); margin-right:8px;"></i>Commandes</span>
        </div>
        <a href="logout.php" class="logout-btn"><i class="fas fa-power-off"></i> Quitter</a>
    </div>

    <div class="content">

        <!-- ── Stat chips / filter tabs ── -->
        <?php
        $statuses = [
            'all'        => ['label' => 'Tout',       'color' => '#94a3b8'],
            'processing' => ['label' => 'Processing',  'color' => '#38bdf8'],
            'completed'  => ['label' => 'Completed',   'color' => '#4ade80'],
            'partial'    => ['label' => 'Partial',     'color' => '#fbbf24'],
            'canceled'  => ['label' => 'Cancelled',   'color' => '#94a3b8'],
            'refunded'   => ['label' => 'Refunded',    'color' => '#a78bfa'],
            'failed'     => ['label' => 'Failed',      'color' => '#f87171'],
        ];
        // Get counts per status
        $countRows = $db->query("SELECT order_status AS status, COUNT(*) AS cnt FROM orders GROUP BY order_status")->fetchAll(PDO::FETCH_ASSOC);
        $counts = ['all' => $totalOrders];
        foreach ($countRows as $cr) $counts[$cr['status']] = (int)$cr['cnt'];
        ?>
        <div class="stat-strip">
            <?php foreach ($statuses as $key => $info):
                $cnt = $counts[$key] ?? 0;
                $isActive = $statusFilter === $key;
            ?>
            <a href="?status=<?= $key ?>" class="stat-chip <?= $isActive ? 'active' : '' ?>">
                <span class="chip-dot" style="background:<?= $info['color'] ?>;"></span>
                <span class="chip-label"><?= $info['label'] ?></span>
                <span class="chip-count"><?= number_format($key === 'all' ? ($counts['all'] ?? 0) : ($counts[$key] ?? 0)) ?></span>
            </a>
            <?php endforeach; ?>
        </div>

        <!-- ── Bulk action toolbar ── -->
        <form id="bulkForm" method="POST">
            <div class="toolbar">
                <span class="toolbar-label">Action groupée :</span>
                <select name="bulk_action" class="bulk-select" required>
                    <option value="">-- Sélectionner --</option>
                    <option value="resend">Renvoyer</option>
                    <option value="cancel">Annuler</option>
                    <option value="completed">Marquer Terminé</option>
                    <option value="processing">Marquer En cours</option>
                    <option value="partial">Marquer Partiel</option>
                    <option value="refunded">Marquer Remboursé</option>
                    <option value="failed">Marquer Échoué</option>
                </select>
                <button type="submit" class="btn-execute"><i class="fas fa-bolt"></i> Exécuter</button>
            </div>

            <!-- ── Desktop table ── -->
            <div class="table-wrapper">
                <div class="table-card">
                    <div class="table-scroll">
                        <table>
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll" class="check-custom"></th>
                                    <th>ID</th>
                                    <th>Client</th>
                                    <th>Service / Lien</th>
                                    <th>Qté</th>
                                    <th>Début</th>
                                    <th>Reste</th>
                                    <th>Prix / Profit</th>
                                    <th>Statut</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php if (!empty($orders)): foreach ($orders as $o):
                                $st = strtolower($o['order_status'] ?? 'paid');
                                $pillClass = in_array($st, ['processing','completed','failed','canceled','partial','refunded','paid']) ? $st : 'paid';
                            ?>
                            <tr>
                                <td><input type="checkbox" name="order_ids[]" value="<?= (int)$o['order_id'] ?>" class="check-custom order-check"></td>
                                <td class="oid">#<?= (int)$o['order_id'] ?></td>
                                <td>
                                    <div class="user-name"><?= htmlspecialchars($o['username'] ?? 'User') ?></div>
                                    <div class="user-email"><?= htmlspecialchars($o['email'] ?? '') ?></div>
                                </td>
                                <td>
                                    <span class="svc-name" title="<?= htmlspecialchars($o['service_name']) ?>"><?= htmlspecialchars($o['service_name']) ?></span>
                                    <a href="<?= htmlspecialchars($o['order_url']) ?>" target="_blank" class="link-anchor"><i class="fas fa-external-link-alt"></i> Voir lien</a>
                                </td>
                                <td style="font-weight:700;"><?= number_format($o['order_quantity']) ?></td>
                                <td><span class="badge"><?= number_format($o['order_start'] ?? 0) ?></span></td>
                                <td><span class="badge remains"><?= number_format($o['order_remains'] ?? 0) ?></span></td>
                                <td>
                                    <div class="price-val">$<?= number_format($o['order_charge'], 2) ?></div>
                                    <div class="profit-val">+$<?= number_format((float)($o['profit'] ?? 0), 2) ?></div>
                                </td>
                                <td>
                                    <span class="pill <?= $pillClass ?>"><?= strtoupper($o['order_status'] ?: 'PAID') ?></span>
                                    <?php if ($o['order_status'] === 'completed' || $o['order_status'] === 'partial'): ?>
                                    <div style="font-size:10px;color:var(--text-dim);margin-top:5px;white-space:nowrap;">
                                        <i class="fas fa-check-circle" style="color:var(--success);margin-right:3px;"></i>
                                        <?= !empty($o['completion_time']) ? date('d M Y, H:i', strtotime($o['completion_time'])) : '<span style="color:#475569;">Not recorded</span>' ?>
                                    </div>
                                    <?php elseif ($o['order_status'] === 'processing'): ?>
                                    <div style="font-size:10px;color:var(--text-dim);margin-top:5px;white-space:nowrap;">
                                        <i class="fas fa-clock" style="color:var(--info);margin-right:3px;"></i>
                                        In progress
                                    </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-cell">
                                        <div class="action-row-top">
                                            <form method="post" style="display:contents;">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                                <select name="new_status" class="status-sel">
                                                    <option value="processing" <?= $o['order_status']=='processing'?'selected':'' ?>>Processing</option>
                                                    <option value="completed"  <?= $o['order_status']=='completed' ?'selected':'' ?>>Completed</option>
                                                    <option value="partial"    <?= $o['order_status']=='partial'   ?'selected':'' ?>>Partial</option>
                                                    <option value="refunded"   <?= $o['order_status']=='refunded'  ?'selected':'' ?>>Refunded</option>
                                                    <option value="failed"     <?= $o['order_status']=='failed'    ?'selected':'' ?>>Failed</option>
                                                    <option value="cancelled"  <?= $o['order_status']=='canceled' ?'selected':'' ?>>Cancelled</option>
                                                </select>
                                                <button type="submit" class="btn-update">OK</button>
                                            </form>
                                        </div>
                                        <div class="action-row-btns">
                                            <form method="post" style="flex:1;">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                                <input type="hidden" name="action" value="resend">
                                                <button type="submit" class="btn-resend"><i class="fas fa-sync-alt"></i> Resend</button>
                                            </form>
                                            <form method="post" style="flex:1;">
                                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                                <input type="hidden" name="action" value="cancel">
                                                <button type="submit" class="btn-cancel"><i class="fas fa-times"></i> Cancel</button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="10" class="empty-cell"><i class="fas fa-inbox"></i>Aucune commande trouvée.</td></tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="pager">
                        <div class="pager-info">
                            Affichage <strong><?= number_format($offset + 1) ?></strong>–<strong><?= number_format(min($offset + $perPage, $totalOrders)) ?></strong>
                            sur <strong><?= number_format($totalOrders) ?></strong>
                        </div>
                        <div class="pager-controls">
                            <?php
                            $base = '?status=' . urlencode($statusFilter) . '&page=';
                            echo '<a href="' . ($currentPage > 1 ? $base.($currentPage-1) : '#') . '" class="pager-btn' . ($currentPage<=1?' disabled':'') . '"><i class="fas fa-chevron-left"></i></a>';
                            $win = 2; $pages = [];
                            for ($p = 1; $p <= $totalPages; $p++) {
                                if ($p===1 || $p===$totalPages || ($p>=$currentPage-$win && $p<=$currentPage+$win)) $pages[] = $p;
                            }
                            $prev = null;
                            foreach ($pages as $p) {
                                if ($prev !== null && $p - $prev > 1) echo '<span class="pager-ellipsis">…</span>';
                                echo '<a href="'.$base.$p.'" class="pager-btn'.($p===$currentPage?' active':'').'">'.$p.'</a>';
                                $prev = $p;
                            }
                            echo '<a href="' . ($currentPage < $totalPages ? $base.($currentPage+1) : '#') . '" class="pager-btn' . ($currentPage>=$totalPages?' disabled':'') . '"><i class="fas fa-chevron-right"></i></a>';
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ── Mobile cards ── -->
            <div class="cards-list">
                <?php if (!empty($orders)): foreach ($orders as $o):
                    $st = strtolower($o['order_status'] ?? 'paid');
                    $pillClass = in_array($st, ['processing','completed','failed','canceled','partial','refunded','paid']) ? $st : 'paid';
                ?>
                <div class="order-card">
                    <div class="card-top">
                        <div>
                            <div class="card-id">#<?= (int)$o['order_id'] ?></div>
                            <div class="card-user"><?= htmlspecialchars($o['username'] ?? 'User') ?></div>
                        </div>
                        <div style="text-align:right;">
                            <span class="pill <?= $pillClass ?>"><?= strtoupper($o['order_status'] ?: 'PAID') ?></span>
                            <div class="card-date" style="margin-top:4px;"><?= date('d M Y', strtotime($o['order_create'])) ?></div>
                            <?php if ($o['order_status'] === 'completed' && !empty($o['completion_time'])): ?>
                            <div style="font-size:10px;color:var(--success);margin-top:2px;"><i class="fas fa-check-circle"></i> <?= date('d M, H:i', strtotime($o['completion_time'])) ?></div>
                            <?php elseif ($o['order_status'] === 'completed'): ?>
                            <div style="font-size:10px;color:var(--text-dim);margin-top:2px;"><i class="fas fa-check-circle"></i> Not recorded</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card-service"><?= htmlspecialchars($o['service_name']) ?></div>
                    <a href="<?= htmlspecialchars($o['order_url']) ?>" target="_blank" class="card-link"><i class="fas fa-external-link-alt"></i> <?= htmlspecialchars($o['order_url']) ?></a>

                    <div class="card-stats">
                        <div class="card-stat">
                            <div class="card-stat-label">Quantité</div>
                            <div class="card-stat-val"><?= number_format($o['order_quantity']) ?></div>
                        </div>
                        <div class="card-stat">
                            <div class="card-stat-label">Début</div>
                            <div class="card-stat-val"><?= number_format($o['order_start'] ?? 0) ?></div>
                        </div>
                        <div class="card-stat">
                            <div class="card-stat-label">Reste</div>
                            <div class="card-stat-val" style="color:var(--warning);"><?= number_format($o['order_remains'] ?? 0) ?></div>
                        </div>
                    </div>

                    <div class="card-finance">
                        <span class="price-val" style="font-size:15px;">$<?= number_format($o['order_charge'], 2) ?></span>
                        <span class="profit-val">Profit: +$<?= number_format((float)($o['profit'] ?? 0), 2) ?></span>
                    </div>

                    <div class="card-actions">
                        <div class="card-action-top">
                            <form method="post" style="display:contents;">
                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                <select name="new_status" class="status-sel" style="flex:1;">
                                    <option value="processing" <?= $o['order_status']=='processing'?'selected':'' ?>>Processing</option>
                                    <option value="completed"  <?= $o['order_status']=='completed' ?'selected':'' ?>>Completed</option>
                                    <option value="partial"    <?= $o['order_status']=='partial'   ?'selected':'' ?>>Partial</option>
                                    <option value="refunded"   <?= $o['order_status']=='refunded'  ?'selected':'' ?>>Refunded</option>
                                    <option value="failed"     <?= $o['order_status']=='failed'    ?'selected':'' ?>>Failed</option>
                                    <option value="cancelled"  <?= $o['order_status']=='canceled' ?'selected':'' ?>>Cancelled</option>
                                </select>
                                <button type="submit" class="btn-update">Mettre à jour</button>
                            </form>
                        </div>
                        <div class="card-action-btns">
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                <input type="hidden" name="action" value="resend">
                                <button type="submit" class="btn-resend"><i class="fas fa-sync-alt"></i> Resend</button>
                            </form>
                            <form method="post" style="flex:1;">
                                <input type="hidden" name="order_id" value="<?= (int)$o['order_id'] ?>">
                                <input type="hidden" name="action" value="cancel">
                                <button type="submit" class="btn-cancel"><i class="fas fa-times"></i> Cancel</button>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; else: ?>
                <div style="text-align:center; padding:50px 20px; color:var(--text-dim);">
                    <i class="fas fa-inbox" style="font-size:32px; opacity:.3; display:block; margin-bottom:12px;"></i>
                    Aucune commande trouvée.
                </div>
                <?php endif; ?>

                <!-- Mobile pagination -->
                <div class="pager" style="background:var(--surface); border-radius:14px; border:1px solid var(--border);">
                    <div class="pager-info">
                        <strong><?= number_format($offset + 1) ?></strong>–<strong><?= number_format(min($offset + $perPage, $totalOrders)) ?></strong>
                        / <strong><?= number_format($totalOrders) ?></strong>
                    </div>
                    <div class="pager-controls">
                        <?php
                        echo '<a href="' . ($currentPage > 1 ? $base.($currentPage-1) : '#') . '" class="pager-btn' . ($currentPage<=1?' disabled':'') . '"><i class="fas fa-chevron-left"></i> Préc</a>';
                        echo '<span style="font-size:12px; color:var(--text-dim);">Page '.$currentPage.' / '.$totalPages.'</span>';
                        echo '<a href="' . ($currentPage < $totalPages ? $base.($currentPage+1) : '#') . '" class="pager-btn' . ($currentPage>=$totalPages?' disabled':'') . '">Suiv <i class="fas fa-chevron-right"></i></a>';
                        ?>
                    </div>
                </div>
            </div>

        </form><!-- end bulkForm -->

    </div><!-- .content -->
</div><!-- .main -->

<script>
/* ── Sidebar ── */
const hamBtn  = document.getElementById('hamBtn');
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');

function toggleSidebar() {
    sidebar.classList.toggle('open');
    overlay.classList.toggle('show');
}
hamBtn.addEventListener('click', toggleSidebar);
overlay.addEventListener('click', toggleSidebar);

/* ── Select All ── */
const selectAll = document.getElementById('selectAll');
if (selectAll) {
    selectAll.addEventListener('change', function () {
        document.querySelectorAll('.order-check').forEach(cb => cb.checked = this.checked);
    });
}

/* ── Bulk form validation ── */
document.getElementById('bulkForm').addEventListener('submit', function (e) {
    const action = this.querySelector('[name="bulk_action"]').value;
    if (!action) return; // not a bulk submit
    const checked = document.querySelectorAll('.order-check:checked').length;
    if (checked === 0) {
        e.preventDefault();
        alert('Veuillez sélectionner au moins une commande.');
    }
});

/* ── Pagination loading overlay ── */
const loadingOverlay = document.getElementById('loadingOverlay');
document.addEventListener('click', function (e) {
    const btn = e.target.closest('a.pager-btn');
    if (!btn || btn.classList.contains('disabled') || btn.classList.contains('active')) return;
    loadingOverlay.classList.add('show');
});
window.addEventListener('pageshow', function (e) {
    if (e.persisted) loadingOverlay.classList.remove('show');
});
</script>

</body>
</html>