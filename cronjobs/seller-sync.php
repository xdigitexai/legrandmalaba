<?php
ini_set('max_execution_time', '600');
define("BASEPATH",TRUE);
require $_SERVER["DOCUMENT_ROOT"]."/vendor/autoload.php";
require $_SERVER["DOCUMENT_ROOT"]."/app/init.php";

$SMM_API  = new SMMApi();


$SELLERS = $conn->prepare("SELECT id,api_name,api_url,api_key,currency FROM service_api WHERE api_sync=:sync");
$SELLERS->execute([
    "sync" => 1
]);
$SELLERS = $SELLERS->fetchAll(PDO::FETCH_ASSOC);
$SELLERS = replaceKeys("id","api_id",$SELLERS);
$SELLERS = replaceKeys("currency","api_currency",$SELLERS);

for($i = 0;$i < count($SELLERS);$i++){
$SERVICES_OF_SELLER = $conn->prepare("SELECT * FROM services WHERE service_api=:apitype AND service_sync=:sync");
$SERVICES_OF_SELLER->execute(array(
      "apitype" => $SELLERS[$i]["api_id"],
      "sync" => 1
));
$SERVICES_OF_SELLER = $SERVICES_OF_SELLER->fetchAll(PDO::FETCH_ASSOC);

if(count($SERVICES_OF_SELLER)){
sleep(rand(5,30));
$API_SERVICES = $SMM_API->action(
array(
'key'=> $SELLERS[$i]["api_key"],'action'=>'services'),
$SELLERS[$i]["api_url"]
);


if(is_array($API_SERVICES)){

$API_SERVICES = json_encode($API_SERVICES,true);

$API_SERVICES = array_group_by(json_decode($API_SERVICES,true),"service");

for($j = 0;$j < count($SERVICES_OF_SELLER);$j++){

$PANEL_SERVICE_ID = $SERVICES_OF_SELLER[$j]["service_id"];
$PANEL_API_SERVICE_ID = $SERVICES_OF_SELLER[$j]["api_service"];
$API_DETAIL_JSON = $SERVICES_OF_SELLER[$j]["api_detail"];
$API_DETAIL_ARRAY = json_decode($API_DETAIL_JSON,1);
$PANEL_SERVICE_PRICE = $SERVICES_OF_SELLER[$j]["service_price"];
$PANEL_SERVICE_MIN_QUANTITY = $SERVICES_OF_SELLER[$j]["service_min"];
$PANEL_SERVICE_MAX_QUANTITY = $SERVICES_OF_SELLER[$j]["service_max"];
$PANEL_PRICE_SERVICE_PROFIT = $SERVICES_OF_SELLER[$j]["price_profit"];
$PANEL_API_SERVICE_TYPE = $SERVICES_OF_SELLER[$j]["api_servicetype"];

$PANEL_SERVICE_SECRET = $SERVICES_OF_SELLER[$j]["service_secret"];

if(!empty($API_SERVICES[$PANEL_API_SERVICE_ID])){

$API_SERVICE_PRICE = $API_SERVICES[$PANEL_API_SERVICE_ID][0]["rate"];
$API_SERVICE_MIN_QUANTITY = $API_SERVICES[$PANEL_API_SERVICE_ID][0]["min"];
$API_SERVICE_MAX_QUANTITY = $API_SERVICES[$PANEL_API_SERVICE_ID][0]["max"];

if($API_DETAIL_ARRAY["rate"] != $API_SERVICE_PRICE){
// API SERVICE PRICE CHANGED 

$API_DETAIL_ARRAY["rate"] = $API_SERVICE_PRICE;
$API_DETAIL_UPDATED_JSON = json_encode($API_DETAIL_ARRAY,1);
$update_api_detail = $conn->prepare("UPDATE services SET api_detail=:detail WHERE service_id=:service_id");
$update_api_detail->execute(array( 
    "service_id" => $PANEL_SERVICE_ID,
    "detail" => $API_DETAIL_UPDATED_JSON
)); 
// CONVERTING AND UPDATING PANEL PRICE
$NEW_BASE_CURRENCY_CONVERTED_PANEL_PRICE = from_to(get_currencies_array("all"),$SELLERS[$i]["api_currency"],$settings["site_base_currency"],$API_SERVICE_PRICE);

$NEW_PANEL_SERVICE_PRICE = $NEW_BASE_CURRENCY_CONVERTED_PANEL_PRICE + (($PANEL_PRICE_SERVICE_PROFIT/100) * $NEW_BASE_CURRENCY_CONVERTED_PANEL_PRICE);

$update_panel_service_price = $conn->prepare("UPDATE services SET service_price=:price WHERE service_id=:service_id");
$update_panel_service_price->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"price" => $NEW_PANEL_SERVICE_PRICE
));

// INSERT PRICE CHANGE LOGS


$insert_rate_changed_log = $conn->prepare("INSERT INTO sync_logs SET service_id=:service_id, api_id=:api_id, action=:action,  description=:description, date=:date");

$insert_rate_changed_log = $insert_rate_changed_log->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"api_id"=> $SELLERS[$i]["api_id"],
"action"=>"SERVICE PRICE UPDATED",
"description"=> "Service rate changed from <b>".format_amount_string($settings["site_base_currency"],$PANEL_SERVICE_PRICE)."</b> to <b>".format_amount_string($settings["site_base_currency"],$NEW_PANEL_SERVICE_PRICE)."</b>",
"date"=>date("Y-m-d H:i:s")
));


} // API SERVICE PRICE CHANGED


// SERVICE MINIMUM QUANTITY CHANGED
if($API_DETAIL_ARRAY["min"] != $API_SERVICE_MIN_QUANTITY){

$API_DETAIL_ARRAY["min"] = $API_SERVICE_MIN_QUANTITY;

$API_DETAIL_UPDATED_JSON = json_encode($API_DETAIL_ARRAY,1);
$update_api_detail = $conn->prepare("UPDATE services SET api_detail=:detail WHERE service_id=:service_id");
$update_api_detail->execute(array( 
    "service_id" => $PANEL_SERVICE_ID,
    "detail" => $API_DETAIL_UPDATED_JSON
)); 


$update_service_min_quantity = $conn->prepare("UPDATE services SET service_min=:min WHERE service_id=:service_id");
$update_service_min_quantity->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"min" => $API_SERVICE_MIN_QUANTITY
));



$insert_min_changed_log = $conn->prepare("INSERT INTO sync_logs SET service_id=:service_id, api_id=:api_id, action=:action,  description=:description, date=:date");

$insert_min_changed_log = $insert_min_changed_log->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"api_id" => $SELLERS[$i]["api_id"],
"action"=>"SERVICE MINIMUM QUANTITY UPDATED",
"description" => "Service minimum quantity changed from <b>".$PANEL_SERVICE_MIN_QUANTITY."</b> to <b>".$API_SERVICE_MIN_QUANTITY."</b>",
"date"=>date("Y-m-d H:i:s")
));


} // SERVICE MIN QUANTITY CHANGED 



if($API_DETAIL_ARRAY["max"] != $API_SERVICE_MAX_QUANTITY){

$API_DETAIL_ARRAY["max"] = $API_SERVICE_MAX_QUANTITY;

$API_DETAIL_UPDATED_JSON = json_encode($API_DETAIL_ARRAY,1);
$update_api_detail = $conn->prepare("UPDATE services SET api_detail=:detail WHERE service_id=:service_id");
$update_api_detail->execute(array( 
    "service_id" => $PANEL_SERVICE_ID,
    "detail" => $API_DETAIL_UPDATED_JSON
)); 


$update_service_max_quantity = $conn->prepare("UPDATE services SET service_max=:max WHERE service_id=:service_id");
$update_service_max_quantity->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"max" => $API_SERVICE_MAX_QUANTITY
));



$insert_max_changed_log = $conn->prepare("INSERT INTO sync_logs SET service_id=:service_id, api_id=:api_id, action=:action,  description=:description, date=:date");

$insert_max_changed_log = $insert_max_changed_log->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"api_id" => $SELLERS[$i]["api_id"],
"action"=>"SERVICE MAXIMUM QUANTITY UPDATED",
"description" => "Service maximum quantity changed from <b>".$PANEL_SERVICE_MAX_QUANTITY."</b> to <b>".$API_SERVICE_MAX_QUANTITY."</b>",
"date"=>date("Y-m-d H:i:s")
));


} // SERVICE MAX QUANTITY CHANGED 


// API SERVICE WAS MARK DISABLED ENABLE IT 
// PANEL SERVICE WILL STILL BE DISABLED (if manually disabled)
if($PANEL_API_SERVICE_TYPE == 1){



$update_api_service_type = $conn->prepare("UPDATE services SET api_servicetype=:service_type WHERE service_id=:service_id");

$update_api_service_type->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"service_type" => 2
));

if($PANEL_SERVICE_SECRET == 2){
$update_service_type = $conn->prepare("UPDATE services SET service_type=:service_type WHERE service_id=:service_id");

$update_service_type->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"service_type" => 2
));
}

$insert_service_type_changed_log = $conn->prepare("INSERT INTO sync_logs SET service_id=:service_id, api_id=:api_id, action=:action,  description=:description, date=:date");

$insert_service_type_changed_log = $insert_service_type_changed_log->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"api_id" => $SELLERS[$i]["api_id"],
"action"=>"SERVICE ADDED BY THE SELLER",
"description" => "Service marked <b>ACTIVE</b>",
"date"=>date("Y-m-d H:i:s")
));


} // API SERVICE WAS MARKED DISABLE



} // PACKAGE EXIST IN API RESPONSE 

else {

// MARK SERVICE INACTIVE IN PANEL

// IF SERVICE IS DISABLED FROM API
if($PANEL_API_SERVICE_TYPE == 2){
$update_service_type = $conn->prepare("UPDATE services SET api_servicetype=:api_servicetype,service_type=:service_type WHERE service_id=:service_id");

$update_service_type->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"api_servicetype" => 1,
"service_type" => 1
));

$insert_service_type_changed_log = $conn->prepare("INSERT INTO sync_logs SET service_id=:service_id, api_id=:api_id, action=:action,  description=:description, date=:date");

$insert_service_type_changed_log = $insert_service_type_changed_log->execute(array(
"service_id" => $PANEL_SERVICE_ID,
"api_id" => $SELLERS[$i]["api_id"],
"action"=>"SERVICE REMOVED BY THE SELLER",
"description" => "Service marked <b>INACTIVE</b>",
"date"=>date("Y-m-d H:i:s")
));
} // CONDITION FOR SERVICE DISABLED




} // PACKAGE DOESN'T EXIST IN API RESPONSE 



}// loop for updating services

} // CHECK IF RESPONSE IS AN ARRAY

}// CONDITION FOR IF SELLER SERVICES PRESENT

} // LOOP FOR SELLER
?>