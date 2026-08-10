<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if( $admin["access"]["admin_access"] != 1  ){
    header("Location:".site_url("admin"));
    exit();
}
if(route(1) == "special-pricing"){
$action = route(2);
if($action == "data"){
$special_prices = $conn->prepare("SELECT * FROM clients_price");
$special_prices->execute();
$special_prices = $special_prices->fetchAll(PDO::FETCH_ASSOC);
$special_prices = json_encode($special_prices,true);
echo $special_prices;
exit();
}
if($action == "delete"){
$special_price_id = route(3);
$special_prices = $conn->prepare("DELETE FROM clients_price WHERE id=:id");
$special_prices->execute(array(
   "id" => $special_price_id
));
success_response_exit("Special Price deleted successfully.");
}
if($action == "delete-all"){
$special_prices = $conn->prepare("DELETE FROM clients_price");
$special_prices->execute();
success_response_exit("Deleted all special prices.");
}


if($_POST){

if($action == "create-new"){

foreach($_POST as $key => $value){
    $$key = htmlspecialchars($value);
}
if(empty($special_price_user) || !is_numeric($special_price_user)){
error_exit("Please select a user.");
}
if(empty($special_price_service) || !is_numeric($special_price_service)){
error_exit("Please select a service.");
}
if(empty($special_price_for_service)){
error_exit("Please set a special price.");
}

$insert = $conn->prepare("INSERT INTO clients_price SET client_id=:client_id, service_price=:service_price, service_id=:service_id");
$insert->execute(array(
    "service_id"=>$special_price_service,
    "client_id"=>$special_price_user,
    "service_price"=>$special_price_for_service));

success_response_exit("Special price created successfully.");
} // CREATE NEW SPECIAL PRICE


if($action == "edit"){
$special_price_id = route(3);
foreach($_POST as $key => $value){
    $$key = $value;
}
if(empty($special_price_user) || !is_numeric($special_price_user)){
error_exit("Please select a user.");
}
if(empty($special_price_service) || !is_numeric($special_price_service)){
error_exit("Please select a service.");
}
if(empty($special_price_for_service)){
error_exit("Please set a special price.");
}
$insert = $conn->prepare("UPDATE clients_price SET client_id=:client_id, service_price=:service_price, service_id=:service_id WHERE id=:id");
$insert->execute(array(
    "id" => $special_price_id,
    "service_id"=>$special_price_service,
    "client_id"=>$special_price_user,
    "service_price"=>$special_price_for_service));
success_response_exit("Special price updated successfully.");
}
} // DATA POSTED

require admin_view("special-pricing");

}


?>