<?php
$settings = $db->query("SELECT site_url FROM settings LIMIT 1")->fetch();
$siteUrl = $settings['site_url'] ?? '#';
?>

<div class="sidebar">
    <h2>Cheap Panel</h2>

    <a href="dashboard.php">📊 Dashboard</a>
    <a href="import_services.php">⬇ Import Services</a>
    <a href="orders.php"> Orders</a>
    <a href="order_action.php"> Manage Orders</a>
    <a href="pricing.php"> Update Prices</a>
    <a href="provider_add.php"> Add Provider</a>
    <a href="provider_list.php"> Manage Providers</a>
    <a href="services.php"> Services</a>
    <a href="appearance.php"> Header & Footer</a>

    <hr style="border:1px solid #2c5364;margin:15px 0">

    <a href="<?= htmlspecialchars($siteUrl) ?>" target="_blank">🌍 Visit Site</a>
    <a href="logout.php" style="background:#c0392b"> Logout</a>
</div>