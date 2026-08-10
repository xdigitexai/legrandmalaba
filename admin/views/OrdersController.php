<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!defined("ADMIN")) {
    define("ADMIN", true);
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/app/init.php';

if (!isset($_SESSION["msmbilisim_adminlogin"]) || $_SESSION["msmbilisim_adminlogin"] != 1) {
    if (headers_sent($file, $line)) {
        die("⚠️ Headers already sent in $file on line $line");
    }
    header("Location: /login.php");
    exit;
}

include __DIR__ . '/header.php';

$sql = "
    SELECT 
        o.order_id, 
        o.api_orderid, 
        o.order_status, 
        o.order_start, 
        o.order_url, 
        o.order_quantity, 
        o.order_remains,
        s.api_name
    FROM orders o
    LEFT JOIN service_api s ON o.order_api = s.id
    WHERE o.order_status IN ('pending', 'inprogress', 'processing') 
      AND o.order_create >= DATE_SUB(NOW(), INTERVAL 5 DAY)
    ORDER BY o.order_id DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$orders = [
    "pending" => ["panel" => [], "providers" => []],
    "inprogress" => ["panel" => [], "providers" => []],
    "processing" => ["panel" => [], "providers" => []],
];

foreach ($rows as $row) {
    $status = strtolower($row['order_status']);
    $providerName = $row['api_name'] ?: "Unknown Provider";

    if (!isset($orders[$status])) continue;

    // Add to panel orders
    $orders[$status]["panel"][] = $row;

    // Add to provider grouping
    if (!isset($orders[$status]["providers"][$providerName])) {
        $orders[$status]["providers"][$providerName] = [];
    }

    if (!empty($row['api_orderid'])) {
        $orders[$status]["providers"][$providerName][] = $row;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Orders - Last 5 Days</title>
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f5f7fa;
      margin: 0;
      color: #333;
    }
    .container {
      padding: 20px;
      max-width: 1200px;
      margin: auto;
    }
    h2 {
      text-align: center;
      margin: 20px 0;
      font-weight: 600;
    }

    /* Tabs */
    .tabs {
      text-align: center;
      margin-bottom: 20px;
    }
    .btn-tab {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      margin: 5px;
      padding: 10px 120px;
      background: #fff;
      color: #007bff;
      border: 1px solid #ddd;
      border-radius: 10px;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      box-shadow: 0 2px 5px rgba(0,0,0,0.05);
      transition: all 0.2s ease;
    }
    .btn-tab:hover { background: #f1f1f1; }
    .btn-tab.active {
      background: #007bff;
      color: white;
      border-color: #007bff;
    }

    /* Progress bar */
    .progress-card {
      background: #fff;
      border-radius: 12px;
      padding: 15px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      margin-bottom: 20px;
      max-width: auto;
      margin-left: auto;
      margin-right: auto;
    }
    .progress-container {
      width: 100%;
      background: #eee;
      border-radius: 20px;
      height: 18px;
    }
    .progress-bar {
      width: 100%;
      height: 18px;
      background: #007bff;
      border-radius: 20px;
      text-align: center;
      color: white;
      font-size: 12px;
      line-height: 18px;
    }

    /* Search */
    .search-card {
      background: #fff;
      border-radius: 12px;
      padding: 15px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
      text-align: center;
    }
    .search-inner {
      display: inline-flex;
      align-items: center;
      max-width: 500px;
      width: 100%;
    }
    .search-inner input {
      flex: 1;
      padding: 10px;
      border: 1px solid #ddd;
      border-right: none;
      border-radius: 8px 0 0 8px;
      font-size: 14px;
    }
    .btn-search {
      background: #007bff;
      border: none;
      color: white;
      padding: 10px 16px;
      border-radius: 0 8px 8px 0;
      cursor: pointer;
      font-size: 14px;
    }
    .btn-search:hover { background: #0056b3; }

    /* Download Section */
.download-card {
  background: #fff;
  border-radius: 12px;
  padding: 20px;
  margin-bottom: 25px;
  text-align: center;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Base button */
.btn-download {
  border: none;
  color: #fff;
  padding: 10px 90px; /* same size for all buttons */
  border-radius: 8px;
  cursor: pointer;
  font-size: 14px;
  margin: 8px; /* spacing between buttons */
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  transition: background 0.2s ease-in-out;
}

/* Excel - Green */
.btn-excel {
  background: #28a745;
}
.btn-excel:hover {
  background: #1e7e34;
}

/* Word - Blue */
.btn-word {
  background: #0078d4;
}
.btn-word:hover {
  background: #005a9e;
}

/* PDF - Red */
.btn-pdf {
  background: #dc3545;
}
.btn-pdf:hover {
  background: #b52a37;
}

    /* Orders Box */
    .provider-header {
      background: #f9f9f9;
      border: 1px solid #eee;
      border-radius: 12px 12px 0 0;
      padding: 10px 15px;
      font-weight: 600;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    #tab-pending .provider-header {
      background: #fff3cd;
    }
    .box {
      background: #fff;
      border-radius: 0 0 12px 12px;
      margin-bottom: 20px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    .orders-box {
      padding: 10px;
      max-height: 220px;
      overflow-y: auto;
    }
    .order-item {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 8px 10px;
      border-bottom: 1px solid #eee;
      font-size: 14px;
    }
    .order-item:last-child { border-bottom: none; }
    .btn-details {
      background: #007bff;
      color: white;
      border: none;
      padding: 6px 10px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
    }
    .btn-details:hover { background: #0056b3; }

    .btn-copy, .btn-toggle {
      background: #6c757d;
      color: white;
      border: none;
      padding: 6px 10px;
      border-radius: 6px;
      cursor: pointer;
      font-size: 12px;
    }
    .btn-copy:hover { background: #5a6268; }
    .btn-toggle {
      background: #a72dcd;
    }
    .btn-toggle:hover { background: #8a1db2; }

    /* Modal */
    .modal {
      display: none;
      position: fixed;
      z-index: 9999;
      left: 0; top: 0;
      width: 100%; height: 100%;
      background: rgba(0,0,0,0.5);
    }
    .modal-inner {
      background: #fff;
      margin: 8% auto;
      padding: 20px;
      border-radius: 12px;
      width: 90%;
      max-width: 480px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.2);
    }
    .modal-close {
      float: right;
      cursor: pointer;
      background: #dc3545;
      color: #fff;
      border-radius: 6px;
      padding: 4px 10px;
      border: 0;
    }

    .detail-row { margin: 8px 0; font-size: 14px; word-wrap: break-word; }

    /* Footer */
    footer {
      text-align: center;
      padding: 15px;
      background: #fff;
      margin-top: 30px;
      font-size: 13px;
      color: #555;
      border-top: 1px solid #ddd;
    }

    /* Responsive */
    @media (max-width: 600px) {
      .search-inner { flex-direction: column; }
      .search-inner input {
        border-radius: 8px;
        border-right: 1px solid #ddd;
        margin-bottom: 8px;
        width: 100%;
      }
      .btn-search {
        border-radius: 8px;
        width: 100%;
      }
    }
  </style>
</head>
<body>
<div class="container">
  <!-- Back Button -->
  <div style="margin-bottom:15px;">
    <a href="/admin" 
       style="display:inline-block; padding:8px 16px; background:#6c757d; color:white; border-radius:6px; text-decoration:none; font-weight:500;">
       ← Back
    </a>
  </div>

  <h2><i class="fa-solid fa-tasks"></i> Task Manager</h2>

  <!-- Tabs -->
  <div class="tabs">
    <button class="btn-tab active" onclick="showTab('pending', event)">
      <i class="fa-solid fa-clock"></i> Pending Task
    </button>
    <button class="btn-tab" onclick="showTab('processing', event)">
      <i class="fa-solid fa-rotate"></i> Fetch Processing
    </button>
    <button class="btn-tab" onclick="showTab('inprogress', event)">
      <i class="fa-solid fa-spinner"></i> Fetch In Progress
    </button>
  </div>

  <!-- Progress -->
  <div class="progress-card">
    <div class="progress-container">
      <div class="progress-bar">100%</div>
    </div>
  </div>

  <!-- Search -->
  <div class="search-card">
    <div class="search-inner">
      <input type="text" id="searchInput" placeholder="Search Order ID...">
      <button class="btn-search" id="btnSearch"><i class="fa fa-search"></i></button>
    </div>
  </div>

  <!-- Download -->
<div class="download-card">
  <form method="post" action="/download/download_pending_excel.php" style="display:inline-block; margin:5px;">
    <button type="submit" class="btn-download btn-excel">
      <i class="fa-solid fa-file-excel"></i> Download Excel
    </button>
  </form>
  <form method="post" action="/download/download_pending_word.php" style="display:inline-block; margin:5px;">
    <button type="submit" class="btn-download btn-word">
      <i class="fa-solid fa-file-word"></i> Download Word
    </button>
  </form>
  <form method="post" action="/download/download_pending_pdf.php" style="display:inline-block; margin:5px;">
    <button type="submit" class="btn-download btn-pdf">
      <i class="fa-solid fa-file-pdf"></i> Download PDF
    </button>
  </form>
</div>

  <!-- Orders -->
  <?php foreach ($orders as $status => $data): ?>
  <div class="status-tab" id="tab-<?= $status ?>" style="<?= $status==='pending' ? '' : 'display:none;' ?>">
    <!-- Panel Orders -->
    <div class="provider-header">
      <span><i class="fa-solid fa-box"></i> Panel Orders (<?= count($data["panel"]) ?>)</span>
      <?php if (count($data["panel"]) > 0): ?>
        <button class="btn-copy" onclick="copyOrders('<?= $status ?>_panel')"><i class="fa fa-copy"></i> Copy All</button>
      <?php endif; ?>
    </div>
    <div class="box">
      <div class="orders-box" id="<?= $status ?>_panel">
        <?php foreach ($data["panel"] as $row): ?>
          <div class="order-item">
            <span>Order ID: <?= htmlspecialchars($row['order_id']) ?></span>
            <button class="btn-details"
              data-id="<?= $row['order_id'] ?>"
              data-start="<?= $row['order_start'] ?>"
              data-link="<?= htmlspecialchars($row['order_url']) ?>"
              data-qty="<?= $row['order_quantity'] ?>"
              data-target="<?= $row['order_remains'] ?>"
              data-status="<?= $row['order_status'] ?>"
              onclick="showDetails(this)">
              <i class="fa fa-eye"></i> View
            </button>
          </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Provider Orders -->
    <?php foreach ($data["providers"] as $providerName => $rows): ?>
      <div class="provider-header">
        <span>
          <i class="fa-solid fa-plug"></i> Provider: 
          <span class="provider-name" id="provider_<?= md5($providerName) ?>" style="display:none;">
            <?= htmlspecialchars($providerName) ?>
          </span>
        </span>
        <div>
          <button class="btn-toggle" onclick="toggleProvider('<?= md5($providerName) ?>', this)">Show Provider</button>
          <?php if (count($rows) > 0): ?>
            <button class="btn-copy" onclick="copyOrders('<?= $status ?>_<?= md5($providerName) ?>')"><i class="fa fa-copy"></i> Copy All</button>
          <?php endif; ?>
        </div>
      </div>
      <div class="box">
        <div class="orders-box" id="<?= $status ?>_<?= md5($providerName) ?>">
          <?php foreach ($rows as $row): ?>
            <div class="order-item">
              <span>Order ID: <?= htmlspecialchars($row['api_orderid']) ?></span>
              <button class="btn-details"
                data-id="<?= $row['api_orderid'] ?>"
                data-start="<?= $row['order_start'] ?>"
                data-link="<?= htmlspecialchars($row['order_url']) ?>"
                data-qty="<?= $row['order_quantity'] ?>"
                data-target="<?= $row['order_remains'] ?>"
                data-status="<?= $row['order_status'] ?>"
                onclick="showDetails(this)">
                <i class="fa fa-eye"></i> View
              </button>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <?php endforeach; ?>
</div>

<div id="detailsModal" class="modal">
  <div class="modal-inner">
    <button class="modal-close" onclick="closeModal()">Close</button>
    <h3><i class="fa fa-info-circle"></i> Order Details</h3>
    <div class="detail-row"><strong>Order ID:</strong> <span id="m_id">-</span></div>
    <div class="detail-row"><strong>Start Count:</strong> <span id="m_start">-</span></div>
    <div class="detail-row"><strong>Link:</strong> <span id="m_link">-</span></div>
    <div class="detail-row"><strong>Quantity:</strong> <span id="m_qty">-</span></div>
    <div class="detail-row"><strong>Target:</strong> <span id="m_target">-</span></div>
    <div class="detail-row"><strong>Status:</strong> <span id="m_status">-</span></div>
  </div>
</div>

<footer>
  Panel 2025
</footer>

<script>
function showTab(tab, e) {
  document.querySelectorAll('.status-tab').forEach(el => el.style.display = 'none');
  document.getElementById('tab-' + tab).style.display = 'block';
  document.querySelectorAll('.btn-tab').forEach(b => b.classList.remove('active'));
  if (e) e.target.classList.add('active');
}

function filterOrders() {
  const keyword = document.getElementById("searchInput").value.trim().toLowerCase();
  document.querySelectorAll('.order-item').forEach(item => {
    const text = item.innerText.toLowerCase();
    item.style.display = (keyword === "" || text.includes(keyword)) ? "flex" : "none";
  });
}
document.getElementById('btnSearch').addEventListener('click', filterOrders);
document.getElementById('searchInput').addEventListener('keyup', filterOrders);

function showDetails(btn) {
  document.getElementById('m_id').innerText = btn.dataset.id;
  document.getElementById('m_start').innerText = btn.dataset.start;
  document.getElementById('m_link').innerText = btn.dataset.link;
  document.getElementById('m_qty').innerText = btn.dataset.qty;
  document.getElementById('m_target').innerText = btn.dataset.target;
  document.getElementById('m_status').innerText = btn.dataset.status;
  document.getElementById('detailsModal').style.display = 'block';
}
function closeModal() {
  document.getElementById('detailsModal').style.display = 'none';
}

function copyOrders(containerId) {
  const items = document.querySelectorAll('#' + containerId + ' .order-item span');
  let ids = [];
  items.forEach(span => {
    ids.push(span.innerText.replace("Order ID:", "").trim());
  });
  if (ids.length > 0) {
    const formatted = ids.join(", ");
    navigator.clipboard.writeText(formatted).then(() => {
      alert("Copied " + ids.length + " Order IDs!");
    });
  } else {
    alert("No Order IDs found.");
  }
}

function toggleProvider(id, btn) {
    const els = document.querySelectorAll('#provider_' + id);
    els.forEach(el => {
        if (el.style.display === 'none' || el.style.display === '') {
            el.style.display = 'inline';
            btn.innerText = 'Hide Provider';
        } else {
            el.style.display = 'none';
            btn.innerText = 'Show Provider';
        }
    });
}
</script>
</body>
</html>