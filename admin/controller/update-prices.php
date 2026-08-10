<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if ($admin["access"]["update-prices"] != 1):
header("Location:" . site_url("admin"));
exit();
endif;
$currencies_array = get_currencies_array("all");
$providers = $conn->prepare("SELECT * FROM service_api");
$providers->execute();
$providers = $providers->fetchAll(PDO::FETCH_ASSOC);

$providers_option .= "";
for($i = 0;$i < count($providers);$i++){

$providers_option .= "<option value=\"".$providers[$i]["id"]."\">".$providers[$i]["api_name"]."</option>";
}

if($_POST){

$service_type = $_POST["service_type"];
$sellers = $_POST["sellers"];
$profit_percent = $_POST["profit-percent-value"];
$action_type = $_POST["action_type"];


if(empty($service_type)){
 error_exit("Please select service type.");
}
if($service_type == "seller_services" && empty($sellers)){
 error_exit("Please select a seller.");
}
if(empty($profit_percent) || !is_numeric($profit_percent)){
error_exit("The profit percentage you entered is invalid.");
}
if(empty($action_type)){
error_exit("Please select action type.");
}



// TYPE : ALL SERVICES
if($service_type == "all_services"){
$services = $conn->prepare("SELECT service_id,api_detail,service_price,price_profit FROM services");
$services->execute();
$services = $services->fetchAll(PDO::FETCH_ASSOC);

for($i = 0;$i < count($services);$i++){
$service_id =  $services[$i]["service_id"];

$service_api_detail = $services[$i]["api_detail"];
$service_price = $services[$i]["service_price"];
$old_profit_percent = $services[$i]["price_profit"];
if(!empty($service_api_detail)){
$service_api_detail_array = json_decode($service_api_detail,true);
$service_api_price = $service_api_detail_array["rate"];
$seller_currency = $service_api_detail_array["currency"];
$converted_service_api_price = from_to($currencies_array,$seller_currency,$settings["site_base_currency"],$service_api_price);
} else {
$converted_service_api_price = $services[$i]["service_price"];
}

if($action_type == "set_profit"){
$new_profit_percent = $profit_percent;
$new_price = $converted_service_api_price + ($converted_service_api_price * ($new_profit_percent/100));
}
if($action_type == "increase_profit"){
$new_profit_percent = $old_profit_percent + $profit_percent;
$new_price = $converted_service_api_price + ($converted_service_api_price * ($new_profit_percent/100));
}
if($action_type == "decrease_profit"){
$new_profit_percent = $old_profit_percent - $profit_percent;
$new_price = $converted_service_api_price + ($converted_service_api_price * ($new_profit_percent/100));
}

$update_service = $conn->prepare("UPDATE services SET service_price=:service_price,price_profit=:price_profit WHERE service_id=:service_id");
$update_service->execute(array(
  "service_id" => $service_id,
  "service_price" => $new_price,
  "price_profit" => $new_profit_percent
));

} // ALL SERVICES UPDATE LOOP

success_response_exit("All services price updated successfully.");

} // TYPE : ALL SERVICES

// TYPE : MANUAL
if($service_type == "manual_services"){
$services = $conn->prepare("SELECT service_id,service_price,price_profit FROM services WHERE service_api=:service_api");
$services->execute(array(
  "service_api" => 0
));
$services = $services->fetchAll(PDO::FETCH_ASSOC);

for($i = 0;$i < count($services);$i++){
$service_id =  $services[$i]["service_id"];
$service_price = $services[$i]["service_price"];
$old_profit_percent = $services[$i]["price_profit"];
if(empty($old_profit_percent)){
    $old_profit_percent = 0;
}
if($action_type == "set_profit"){
$new_profit_percent = $profit_percent;
$new_price = $service_price + ($service_price * ($new_profit_percent/100));
}
if($action_type == "increase_profit"){
$new_profit_percent = $old_profit_percent + $profit_percent;
$new_price = $service_price + ($service_price * ($new_profit_percent/100)); 
}
if($action_type == "decrease_profit"){
$new_profit_percent = $old_profit_percent - $profit_percent;
$new_price = $service_price + ($service_price * ($new_profit_percent/100)); 
}
$update_service = $conn->prepare("UPDATE services SET service_price=:service_price,price_profit=:price_profit WHERE service_id=:service_id");
$update_service->execute(array(
  "service_id" => $service_id,
  "service_price" => $new_price,
  "price_profit" => $new_profit_percent
));
} // MANUAL SERVICE UPDATE LOOP

success_response_exit("Manual services price updated successfully.");

} // TYPE : MANUAL 

// TYPE : SELLER SERVICES
if($service_type == "seller_services"){

for($i = 0;$i < count($sellers);$i++){
$services = $conn->prepare("SELECT service_id,api_detail,service_price,price_profit FROM services WHERE service_api=:service_api");
$services->execute(array(
  "service_api" => $sellers[$i]
));
$services = $services->fetchAll(PDO::FETCH_ASSOC);

if(count($services)){
for($j = 0;$j < count($services);$j++){

$service_id =  $services[$j]["service_id"];

$service_api_detail = $services[$j]["api_detail"];
$old_profit_percent = $services[$i]["price_profit"];
$service_api_detail_array = json_decode($service_api_detail,true);
$service_api_price = $service_api_detail_array["rate"];
$seller_currency = $service_api_detail_array["currency"];
$converted_service_api_price = from_to($currencies_array,$seller_currency,$settings["site_base_currency"],$service_api_price);
if($action_type == "set_profit"){
$new_profit_percent = $profit_percent;
$new_price = $converted_service_api_price + ($converted_service_api_price * ($new_profit_percent/100));
}
if($action_type == "increase_profit"){
$new_profit_percent = $old_profit_percent + $profit_percent;
$new_price = $converted_service_api_price + ($converted_service_api_price * ($new_profit_percent/100));
}
if($action_type == "decrease_profit"){
$new_profit_percent = $old_profit_percent - $profit_percent;
$new_price = $converted_service_api_price + ($converted_service_api_price * ($new_profit_percent/100));
}
$update_service = $conn->prepare("UPDATE services SET service_price=:service_price,price_profit=:price_profit WHERE service_id=:service_id");
$update_service->execute(array(
  "service_id" => $service_id,
  "service_price" => $new_price,
  "price_profit" => $new_profit_percent
));

} // SELLER SERVICES UPDATE  LOOP 

success_response_exit("Selected sellers price updated successfully.");
}  // SELLER SERVICES FOUND
else {
error_exit("No services found for the seller : ".GET_API_NAME_BY_ID($sellers[$i]).".");
}

} // LOOP FOR SELECTED SELLERS
} // TYPE : SELLER SERVICES

error_exit("The seller you selected doesn not exists.");


} // DATA POSTED


require admin_view("update-prices");