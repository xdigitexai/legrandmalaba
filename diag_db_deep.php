<?php
chdir('/home/tipmrnhl/panel.mkboost.site');
$config = include('app/config.php');
$conn = new PDO(
    'mysql:host='.$config['db']['host'].';dbname='.$config['db']['name'].';charset='.$config['db']['charset'],
    $config['db']['user'],
    $config['db']['pass']
);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

echo "--- Categories Type Check ---\n";
$r1 = $conn->query("SELECT category_type, COUNT(*) as cnt FROM categories GROUP BY category_type");
foreach($r1 as $row) echo "Type: ".$row['category_type']." Count: ".$row['cnt']."\n";

echo "\n--- Services Type Check ---\n";
$r2 = $conn->query("SELECT service_type, COUNT(*) as cnt FROM services GROUP BY service_type");
foreach($r2 as $row) echo "Type: ".$row['service_type']." Count: ".$row['cnt']."\n";

echo "\n--- Sample Category (ID=1) ---\n";
$r3 = $conn->query("SELECT * FROM categories WHERE category_id=1");
$cat = $r3->fetch(PDO::FETCH_ASSOC);
if($cat) foreach($cat as $k=>$v) echo "$k: $v\n";

echo "\n--- Sample Service (Category=1) ---\n";
$r4 = $conn->query("SELECT * FROM services WHERE category_id=1 LIMIT 1");
$svc = $r4->fetch(PDO::FETCH_ASSOC);
if($svc) foreach($svc as $k=>$v) echo "$k: $v\n";

echo "\n--- Checking for 'No service found' message in ajax_data.php ---\n";
$category = 1;
$services = $conn->prepare("SELECT * FROM services WHERE category_id=:c_id && service_type='2' && service_deleted='0' ORDER BY service_line ");
$services->execute(array("c_id" => $category));
$services_rows = $services->fetchAll(PDO::FETCH_ASSOC);
echo "Query for category 1 returned ".count($services_rows)." rows.\n";

if(count($services_rows) > 0) {
    $svc = $services_rows[0];
    echo "First service secret: ".$svc['service_secret']."\n";
    // Check the filter condition
    $user_client_id = 0; // Mock
    $search = $conn->prepare("SELECT * FROM clients_service WHERE service_id=:service && client_id=:c_id ");
    $search->execute(array("service" => $svc["service_id"], "c_id" => $user_client_id));
    if ($svc["service_secret"] == 2 || $search->rowCount()) {
        echo "Filter condition: PASSED\n";
    } else {
        echo "Filter condition: FAILED\n";
    }
}
