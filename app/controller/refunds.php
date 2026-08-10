
<?php

if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}
define("ADDFUNDS", TRUE);
$title .= " Refunds ";

// Ensure the user is logged in and has the correct client type
if ($_SESSION["msmbilisim_userlogin"] != 1 || $user["client_type"] == 1) {
    header("Location:" . site_url('logout'));
    exit();
}
if ($settings["email_confirmation"] == 1 && $user["email_type"] == 1) {
    header("Location:" . site_url('confirm_email'));
    exit();
}

// Set the date for 90 days ago
$dateLimit = date('Y-m-d', strtotime('-90 days'));

// Get the logged-in user's ID
$userId = $user['client_id'];

// Query orders with "canceled" or "partial" status for the logged-in user in the last 90 days
$query = "
    SELECT o.order_id, o.client_id, o.service_id, o.order_charge, o.order_status, o.last_check, s.service_price, o.order_remains 
    FROM orders o 
    LEFT JOIN services s ON o.service_id = s.service_id 
    WHERE o.client_id = :userId
    AND o.order_status IN ('canceled', 'partial') 
    AND o.last_check >= :dateLimit
    ORDER BY o.last_check DESC
";
$stmt = $conn->prepare($query);
$stmt->execute([
    'userId' => $userId,
    'dateLimit' => $dateLimit
]);

// Process the data and store in the refunds table if not already present
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Calculate the refund amount based on status
    if ($row['order_status'] == 'canceled' || $row['order_status'] == 'partial') {
        $refundAmount = ($row['order_remains'] / 1000) * $row['service_price'];
    }

    // Check if this order_id already exists in the refunds table
    $checkQuery = "SELECT COUNT(*) FROM refunds WHERE order_id = :order_id";
    $checkStmt = $conn->prepare($checkQuery);
    $checkStmt->execute(['order_id' => $row['order_id']]);
    $exists = $checkStmt->fetchColumn();

    // If the order_id does not exist in refunds, insert it
    if ($exists == 0) {
        $insertQuery = "
            INSERT INTO refunds (order_id, client_id, refund_amount, order_status, date)
            VALUES (:order_id, :client_id, :refund_amount, :order_status, :date)
        ";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->execute([
            'order_id' => $row['order_id'],
            'client_id' => $row['client_id'],
            'refund_amount' => $refundAmount,
            'order_status' => $row['order_status'],
            'date' => $row['last_check']
        ]);
    }
}

// Retrieve data from refunds table for display, only for the logged-in user
$refundsQuery = "SELECT * FROM refunds WHERE client_id = :userId ORDER BY date DESC";
$refundsStmt = $conn->prepare($refundsQuery);
$refundsStmt->execute(['userId' => $userId]);
$refunds = $refundsStmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Refunds Page</title>
    <style>
       /* Style for light theme with dark accents */
html, body {
    margin: 0;
    padding: 0;
    background-color: #ffffff; /* White background */
    color: #333333; /* Dark gray text */
    font-family: Arial, sans-serif;
}

/* General styles */
h1 {
    color: #333333; /* Blue color */
    text-align: center;
    padding: 10px 0;
    font-size: 1.5em;
}

.message {
    text-align: center;
    color: #666666; /* Gray color for the message */
    margin: 10px 0;
}

.button-group {
    text-align: center;
    margin: 20px;
}

/* Buttons */
.button-group button {
    background-color: #f0f0f0; /* Light gray background */
    color: #333333; /* Dark text */
    border: none;
    padding: 10px 20px;
    cursor: pointer;
    margin: 0 5px;
    border-radius: 4px;
    transition: background-color 0.3s;
    font-size: 1em;
}

.button-group button.active {
    background-color: #000; /* Blue for active button */
    color: #ffffff; /* White text for active button */
}

.search-container {
    text-align: center;
    margin: 20px;
}

.search-container input[type="text"] {
    padding: 8px;
    width: 200px;
    border: 1px solid #ccc; /* Light border */
    border-radius: 4px;
    background-color: #ffffff; /* White background */
    color: #333333; /* Dark text */
}

.search-container button {
    padding: 8px 12px;
    background-color: #000; /* Blue background */
    color: #ffffff; /* White text */
    border: none;
    border-radius: 4px;
    cursor: pointer;
}

/* Table styles */
table {
    width: 90%; /* Adjusted to fit better on mobile */
    max-width: 800px; /* Set a maximum width for larger screens */
    margin: 0 auto;
    border-collapse: collapse;
    background-color: #f8f8f8; /* Light background */
    border-radius: 8px;
    overflow: hidden;
}

thead {
    background-color: #333333;
}

th, td {
    padding: 12px;
    text-align: left;
    font-size: 1em;
}

th {
    color: #ffffff; /* White text for headers */
    font-weight: bold;
    text-transform: uppercase;
}

tbody tr {
    border-bottom: 1px solid #008000; /* Light border */
}

tbody tr:nth-child(even) {
    background-color: #f2f2f2; /* Light gray for even rows */
}

tbody tr:hover {
    background-color: #e0e0e0; /* Slightly darker gray on hover */
}

.highlight {
    color: #007BFF; /* Blue for highlighted order ID */
}

/* Responsive adjustments */
@media (max-width: 768px) {
    h1 {
        font-size: 1.2em;
    }

    .button-group button {
        padding: 8px 16px;
        font-size: 0.9em;
    }

    .search-container input[type="text"] {
        width: 60%; /* Full width on smaller screens */
        max-width: 300px;
    }

    table {
        width: %; /* Full width on mobile */
        font-size: 0.9em; /* Slightly smaller font for better fit */
    }

    th, td {
        padding: 8px; /* Reduced padding for compact layout */
    }
}

    </style>
</head>
<body>

    <h1>Refunds                         <button onclick="history.back()" 
        aria-label="Go back" 
        title="Go back"
        class="cute-back-btn">
    ⬅ Back
</button></h1>
   
<style>
.cute-back-btn {
   position: fixed;
  top: 20px;
  right: 20px;
  background-color: #ffb6c1;
  color: white;
  border: none;
  padding: 10px 18px;
  font-size: 16px;
  font-weight: bold;
  border-radius: 20px;
  cursor: pointer;
  box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
  transition: all 0.3s ease;
  
}

.cute-back-btn:hover {
  background-color: #ff69b4; /* hot pink */
  transform: scale(1.05);
}

.cute-back-btn:active {
  transform: scale(0.95);
}
</style>
    <div class="message">Refund details are available for the past 90 days.</div> <!-- Added message here -->

    <!-- Button Section -->
    <div class="button-group">
        <button class="active">All</button>
        <button>Canceled</button>
        <button>Partial</button>
    </div>

    <!-- Search Section -->
    <div class="search-container">
        <input type="text" placeholder="Search" />
        <button>🔍</button>
    </div>

    <!-- Refunds Table -->
    <table>
        <thead>
            <tr>
                <th>Order ID</th>
                <th>Refunded Amount</th>
                <th>Order Status</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($refunds as $refund): ?>
                <tr>
                    <td class="highlight">
                        <a href="<?php echo site_url('orders?search=') . urlencode($refund['order_id']); ?>" style="color: #007BFF;">
                            <?php echo htmlspecialchars($refund['order_id']); ?>
                        </a>
                    </td>
                    <td>+<?php echo htmlspecialchars(number_format($refund['refund_amount'], 2)); ?></td>
                    <td><?php echo htmlspecialchars(ucfirst($refund['order_status'])); ?></td>
                    <td><?php echo htmlspecialchars($refund['date']); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const buttons = document.querySelectorAll('.button-group button');
            const rows = document.querySelectorAll('tbody tr');

            // Button filter functionality
            buttons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove "active" class from all buttons and add to clicked button
                    buttons.forEach(btn => btn.classList.remove('active'));
                    button.classList.add('active');

                    // Get filter type
                    const filter = button.textContent.toLowerCase();

                    // Show or hide rows based on the filter
                    rows.forEach(row => {
                        const status = row.querySelector('td:nth-child(3)').textContent.toLowerCase();
                        row.style.display = (filter === 'all' || filter === status) ? '' : 'none';
                    });
                });
            });

            // Search functionality
            document.querySelector('.search-container button').addEventListener('click', function() {
                const searchValue = document.querySelector('.search-container input').value.toLowerCase();
                rows.forEach(row => {
                    const orderId = row.querySelector('td').textContent.toLowerCase();
                    row.style.display = orderId.includes(searchValue) ? '' : 'none';
                });
            });

            // Search as you type
            document.querySelector('.search-container input').addEventListener('keyup', function() {
                const searchValue = this.value.toLowerCase();
                rows.forEach(row => {
                    const orderId = row.querySelector('td').textContent.toLowerCase();
                    row.style.display = orderId.includes(searchValue) ? '' : 'none';
                });
            });
        });
    </script>
</body>
</html>