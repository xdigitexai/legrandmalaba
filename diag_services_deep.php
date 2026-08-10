<?php
require_once "app/init.php";
$category_id = 1; // Let's check category 1
$services = $conn->prepare("SELECT * FROM services WHERE category_id=:c_id");
$services->execute(array("c_id"=>$category_id));
$services = $services->fetchAll(PDO::FETCH_ASSOC);
echo "Services for category $category_id:\n";
print_r($services);

$categories = $conn->prepare("SELECT * FROM categories");
$categories->execute();
$categories = $categories->fetchAll(PDO::FETCH_ASSOC);
echo "\nAll Categories:\n";
print_r($categories);
?>
