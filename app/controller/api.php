<?php
if(!defined('BASEPATH')) {
die('Direct access to the script is not allowed');
}
$title .= $languageArray["api.title"];

$API_VERSION_ROUTE = route(1);
$OUTPUT = array();
if($_SERVER["REQUEST_METHOD"] == "POST"){
if($API_VERSION_ROUTE == "v2"){
$SMM_API = new SMMApi();
$action = htmlspecialchars($_POST["action"]);
$key = htmlspecialchars($_POST["key"]);
if(empty($key)){
    $key = htmlspecialchars($_POST["api_token"]);
}
// #ERROR 1 : USER NOT POSTED WHAT TO DO.
if (empty($action)){
APIErrorExit("Incorrect request.");
}
// #ERROR 1 : END
// #ERROR 2 : USER NOT POSTED API KEY
if (empty($key)){
APIErrorExit("Incorrect request.");
}
// #ERROR 2 : END


$ORDER_ID = htmlspecialchars($_POST["order"]);
$REFILL_ID  = htmlspecialchars($_POST["refill"]);
$SERVICE_ID = htmlspecialchars($_POST["service"]);
$QUANTITY = htmlspecialchars($_POST["quantity"]);
$LINK = htmlspecialchars($_POST["link"]);
$USERNAME = htmlspecialchars($_POST["username"]);
$POSTS = htmlspecialchars($_POST["posts"]);
$DELAY = htmlspecialchars($_POST["delay"]);
$MINIMUM = htmlspecialchars($_POST["min"]);
$MAXIMUM = htmlspecialchars($_POST["max"]);
$COMMENTS = $_POST["comments"];
$RUNS = htmlspecialchars($_POST["runs"]);
$INTERVAL = htmlspecialchars($_POST["interval"]);
$EXPIRY = date("Y.m.d", strtotime($_POST["expiry"]));


$API_CLIENT = $conn->prepare("SELECT * FROM clients WHERE apikey=:key");
$API_CLIENT->execute(array("key" => $key));

if($API_CLIENT->rowCount()){
 $API_CLIENT = $API_CLIENT->fetch(PDO::FETCH_ASSOC);
} else {
 // #ERROR 3 : API KEY NOT FOUND IN DB
APIErrorExit("Invalid API Key.");
// #ERROR 3 : END
}

if($API_CLIENT["client_type"] == 1){
 // #ERROR 4 : The account is inactive.
APIErrorExit("The account is inactive.");
// #ERROR 4 : END
}

$DISCOUNT_PERCENTAGE = $API_CLIENT["discount_percentage"] / 100;

$SITE_BASE_CURRENCY = $settings['site_base_currency'];

// ACTION : BALANCE
if($action == "balance"){
$OUTPUT["balance"] = $API_CLIENT["balance"];
$OUTPUT["currency"] = $SITE_BASE_CURRENCY;
$OUTPUT = json_encode($OUTPUT,1);
header('Content-Type: application/json; charset=utf-8');
echo $OUTPUT;
exit();

}  // END ACTION : BALANCE
// ACTION : STATUS
elseif($action == "status"){
$ORDER = $conn->prepare("SELECT * FROM orders WHERE order_id=:order_id && client_id=:client_id");
$ORDER->execute(array(
    "client_id" => $API_CLIENT["client_id"],
    "order_id" => $ORDER_ID
));
if($ORDER->rowCount()){
$ORDER = $ORDER->fetch(PDO::FETCH_ASSOC);

if ($ORDER["subscriptions_type"] == 2){
$OUTPUT["status"] = ucwords($ORDER["subscriptions_status"]);
$OUTPUT["posts"] = $ORDER["subscriptions_posts"];

} elseif ($ORDER["dripfeed"] != 1){
$OUTPUT["status"] = ucwords($ORDER["subscriptions_status"]);
$OUTPUT["runs"] = $ORDER["dripfeed_runs"];

} else {
$OUTPUT["charge"] = $ORDER["order_charge"];
$OUTPUT["start_count"] = $ORDER["order_start"];
$OUTPUT["status"] = ucfirst($ORDER["order_status"]);
$OUTPUT["remains"] = $ORDER["order_remains"];
$OUTPUT["currency"] = $SITE_BASE_CURRENCY;
}

$OUTPUT = json_encode($OUTPUT,1);
header('Content-Type: application/json; charset=utf-8');
echo $OUTPUT;
exit();

} else {
 // #ERROR 5 : Order not found.
APIErrorExit("No order was placed with this ORDER ID.");
// #ERROR 5 : END
}

} // END ACTION : STATUS
// ACTION : ORDER REFILL 
elseif($action == "refill"){
APIErrorExit("Something went wrong.");
} // END ACTION : ORDER REFILL 
// ACTION : REFILL STATUS
elseif($action == "refill_status"){
APIErrorExit("Something went wrong.");
} // END ACTION : REFILL STATUS
// ACTION : SERVICES
elseif($action == "services"){

$CATEGORIES = $conn->prepare("SELECT * FROM categories WHERE category_type=:type AND category_secret=:category_secret AND category_deleted=:deleted ORDER BY category_line ASC");
$CATEGORIES->execute(array(
  "type" => 2,
  "category_secret" => 2,
  "deleted" => '0'
));
if($CATEGORIES->rowCount()){
$CATEGORIES = $CATEGORIES->fetchAll(PDO::FETCH_ASSOC);
$CATEGORIES = array_group_by($CATEGORIES,"category_id");
} else {
 // #ERROR 6 : No service categories found
APIErrorExit("No service categories found.");
// #ERROR 6 : END
}
$SERVICES = $conn->prepare("SELECT * FROM services WHERE service_type=:service_type AND service_secret=:service_secret AND service_deleted=:deleted ORDER BY service_line ASC");
$SERVICES->execute(array(
  "service_type" => 2,
  "service_secret" => 2,
  "deleted" => '0'
));
if($SERVICES->rowCount()){
$SERVICES = $SERVICES->fetchAll(PDO::FETCH_ASSOC);
} else {
   // #ERROR 7 : No services found.
APIErrorExit("No services found.");
// #ERROR 7 : END
}

for($i = 0;$i < count($SERVICES);$i++){
$SERVICE_NAME = $SERVICES[$i]["service_name"];
$SERVICE_ID = $SERVICES[$i]["service_id"];
$SERVICE_TYPE = servicePackage($SERVICES[$i]["service_package"]);
$SERVICE_CATEGORY = $SERVICES[$i]["category_id"];
$SERVICE_CATEGORY_NAME = $CATEGORIES[$SERVICE_CATEGORY][0]["category_name"];
$SERVICE_DESCRIPTION = $SERVICES[$i]["service_description"];
$SERVICE_RATE = $SERVICES[$i]["service_price"];
$SERVICE_RATE = ($SERVICE_RATE - ($SERVICE_RATE * $DISCOUNT_PERCENTAGE));
$SERVICE_RATE = APIRoundAmount($SERVICE_RATE);
$SERVICE_MIN = $SERVICES[$i]["service_min"];
$SERVICE_MAX = $SERVICES[$i]["service_max"];
$OUTPUT[] = array(
    "service" => $SERVICE_ID,
    "name" => $SERVICE_NAME,
    "type" => $SERVICE_TYPE,
    "category" => $SERVICE_CATEGORY_NAME,
    "rate" => $SERVICE_RATE,
    "min" => $SERVICE_MIN,
    "max" => $SERVICE_MAX,
    "desc" => $SERVICE_DESCRIPTION
);
}

$OUTPUT = json_encode($OUTPUT,1);
header('Content-Type: application/json; charset=utf-8');
print_r($OUTPUT);
exit();

} // END ACTION : SERVICES
// ACTION : ADD ORDER
elseif($action == "add"){
if(empty($SERVICE_ID)){
// #ERROR 8 : Incorrect Service ID.
APIErrorExit("Incorrect Service ID.");
// #ERROR 8 : END
}
$API_CLIENT_BALANCE = $API_CLIENT["balance"];

$SERVICE_DETAIL = $conn->prepare("SELECT * FROM services INNER JOIN categories ON categories.category_id=services.category_id LEFT JOIN service_api ON service_api.id=services.service_api WHERE services.service_id=:id");
$SERVICE_DETAIL->execute(array(
  "id" => $SERVICE_ID
));
if($SERVICE_DETAIL->rowCount()){
$SERVICE_DETAIL = $SERVICE_DETAIL->fetch(PDO::FETCH_ASSOC);
} else {
// #ERROR 9 : Package or Service not found.
APIErrorExit("Package or service not found.");
// #ERROR 9 : END
}

if($SERVICE_DETAIL["service_type"] == 2 && $SERVICE_DETAIL["service_secret"] == 2 && $SERVICE_DETAIL["category_type"] == 2 && $SERVICE_DETAIL["category_secret"] == 2){


$SERVICE_PRICE = ($SERVICE_DETAIL["service_price"] - ($SERVICE_DETAIL["service_price"] * $DISCOUNT_PERCENTAGE));

$SERVICE_PRICE = APIServicePrice($SERVICE_DETAIL["service_id"],$SERVICE_PRICE,$API_CLIENT["client_id"]);
$PER_ITEM_PRICE = $SERVICE_PRICE / 1000;
$SERVICE_PACKAGE = $SERVICE_DETAIL["service_package"];
if($SERVICE_PACKAGE == 1 || $SERVICE_PACKAGE == 2 || $SERVICE_PACKAGE == 3 || $SERVICE_PACKAGE == 4){



if(empty($LINK)){
// #ERROR 11 : Link field is required.
APIErrorExit("Link field is required.");
// #ERROR 11 : END
}

if($SERVICE_PACKAGE == 1 && empty($QUANTITY) || !is_numeric($QUANTITY)){
// #ERROR 12 : Quanity no specified.
APIErrorExit("Order quantity field is required.");
// #ERROR 12 : END
}

if($SERVICE_PACKAGE == 1 && $QUANTITY > $SERVICE_DETAIL["service_max"]){
// #ERROR 13 : Quanity max error.
APIErrorExit("The quantity specified must be less than the maximum quantity.");
// #ERROR 13 : END
}
if($SERVICE_PACKAGE == 1 && $QUANTITY <  $SERVICE_DETAIL["service_min"]){
// #ERROR 13 : Quanity min error.
APIErrorExit("The quantity specified must be greater than the minimum quantity.");
// #ERROR 13 : END
}

$SERVICE_PRICE = $PER_ITEM_PRICE * $QUANTITY;


if($SERVICE_PACKAGE == 3 || $SERVICE_PACKAGE == 4){
$QUANTITY = count(explode("\n",$COMMENTS));
$ORDER_EXTRAS = json_encode(["comments"=>$COMMENTS]);
$SERVICE_PRICE = $PER_ITEM_PRICE * $QUANTITY;
}


$ORDER_ALREADY_EXISTS = $conn->prepare("SELECT * FROM orders WHERE order_url LIKE :url && ( order_status=:status1 || order_status=:status2 || order_status=:status3 ) && dripfeed=:dripfeed && subscriptions_type=:subscriptions_type ");
$ORDER_ALREADY_EXISTS->execute(array("url" => '%' . $LINK . '%', "status1" => "pending", "status2" => "inprogress", "status3" => "processing", "dripfeed" => 1, "subscriptions_type" => 1));
$ORDER_ALREADY_EXISTS = $ORDER_ALREADY_EXISTS->rowCount();



if($SERVICE_DETAIL["instagram_second"] == 1 && $ORDER_ALREADY_EXISTS){
// #ERROR 14 : Order already processing error.
APIErrorExit("You cannot start a new order with the same link that is already in progress.");
// #ERROR 14 : END
} 

if($SERVICE_PRICE > $API_CLIENT["balance"]){
// #ERROR 15 : Quanity min error.
APIErrorExit("You don't have sufficient balance to place this order.");
// #ERROR 15 : END
}
// MANUAL ORDER
if($SERVICE_DETAIL["service_api"] == 0){
 $conn->beginTransaction();
 $insert_maunal_order = $conn->prepare("INSERT INTO orders SET 
     order_start=:count,
     order_profit=:profit,
     order_error=:error,
     client_id=:c_id,
     service_id=:s_id,
     order_quantity=:quantity,
     order_charge=:price,
     order_url=:url,
     order_create=:create,
     order_extras=:extra,
     last_check=:last");

$insert_maunal_order = $insert_maunal_order->execute(array(
    "count"=> "0",
    "c_id"=> $API_CLIENT["client_id"],
    "error"=> "-",
    "s_id"=> $SERVICE_DETAIL["service_id"],
    "quantity"=> $QUANTITY,
    "price"=> $SERVICE_PRICE,
    "profit"=> $SERVICE_PRICE,
    "url"=> $LINK,
    "create"=> date("Y.m.d H:i:s"),
    "last"=> date("Y.m.d H:i:s"),
    "extra"=> $extras));

if( $insert_maunal_order ){
$ORDER_ID = intval($conn->lastInsertId());
} 

if( $insert_maunal_order ){ 
$update_user = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
$update_user = $update_user->execute(array(
   "balance"=>$API_CLIENT["balance"] - $SERVICE_PRICE,
   "spent"=>$API_CLIENT["spent"] + $SERVICE_PRICE,
   "id"=>$API_CLIENT["client_id"]
 ));
$insert_order_log = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
$insert_order_log = $insert_order_log->execute(array(
  "c_id"=>$user["client_id"],
  "action"=>"New Manual Order #".$ORDER_ID." has been placed by ".$API_CLIENT["username"].".",
  "ip"=>GetIP(),
  "date"=>date("Y-m-d H:i:s")
));
}

if ( $insert_maunal_order && $update_user && $insert_order_log){
$conn->commit();
if( $settings["alert_newmanuelservice"] == 2 ){
$msg = "Order #".$ORDER_ID.". New manual order received.
View all manual orders in admin panel: ".site_url()."admin/orders/1/all?mode=manuel ";     
$send = mail($settings['admin_mail'],"New manual orders",$msg);
}
$OUTPUT["order"] = $ORDER_ID;
$OUTPUT = json_encode($OUTPUT,1);
header('Content-Type: application/json; charset=utf-8');
echo $OUTPUT;
exit();
} else {
  $conn->rollBack();
  // #ERROR 16 : Something went wrong.
APIErrorExit("Something went wrong while placing a manual order");
// #ERROR 16 : END
}


} // MANUAL ORDER END 
// API ORDER 
else {

$subscriptions_status = "active";
$subscriptions = "1";
$username = NULL;
$subscription_posts = 0;
$subscription_delay = 0;
$subscription_min = 0;
$subscription_max = 0;
$subscription_expiry = "1970-01-01";
$dripfeed_id = 0;
$currencycharge = NULL;
$conn->beginTransaction();
// API TYPE 1 
if( $SERVICE_DETAIL["api_type"] == 1 ){

// PACKAGE 1 (API TYPE 1)
if($SERVICE_PACKAGE == 1){
$API_ORDER = $SMM_API->action(array(
   'key' => $SERVICE_DETAIL["api_key"],
   'action' =>'add',
   'service'=> $SERVICE_DETAIL["api_service"],
   'link'=> $LINK,
   'quantity'=> $QUANTITY
   ),
   $SERVICE_DETAIL["api_url"]
);

if( @!$API_ORDER->order){
$ORDER_ERROR = json_encode($API_ORDER);
$API_ORDER_ID = 0;
} else {
$ORDER_ERROR = "-";
$API_ORDER_ID = @$API_ORDER->order;
}
} // PACKAGE 1 (API TYPE 1)
// PACKAGE 2 (API TYPE 1)
if($SERVICE_PACKAGE == 2){
$API_ORDER = $SMM_API->action(array(
   'key' => $SERVICE_DETAIL["api_key"],
   'action' =>'add',
   'service'=> $SERVICE_DETAIL["api_service"],
   'link'=> $LINK
   ),
   $SERVICE_DETAIL["api_url"]
);

if( @!$API_ORDER->order){
$ORDER_ERROR = json_encode($API_ORDER);
$API_ORDER_ID = 0;
} else {
$ORDER_ERROR = "-";
$API_ORDER_ID = @$API_ORDER->order;
} 

} // END PACKAGE 2 (API TYPE 1)
// PACKAGE 3,4 (API TYPE 1)
if($SERVICE_PACKAGE == 3 || $SERVICE_PACKAGE == 4){


$API_ORDER = $SMM_API->action(array(
   'key' => $SERVICE_DETAIL["api_key"],
   'action' =>'add',
   'service'=> $SERVICE_DETAIL["api_service"],
   'link'=> $LINK,
   'comments'=>$COMMENTS
 ),
$SERVICE_DETAIL["api_url"]
);

if(@!$API_ORDER->order){
$ORDER_ERROR = json_encode($API_ORDER);
$API_ORDER_ID = 0;
} else {
$ORDER_ERROR = "-";
$API_ORDER_ID = @$API_ORDER->order;
}


} // END PACKAGE 3,4 (API TYPE 1)


$ORDER_STATUS = $SMM_API->action(array(
  'key' =>$SERVICE_DETAIL["api_key"],
  'action' =>'status',
  'order'=> $API_ORDER_ID
),
$SERVICE_DETAIL["api_url"]
);
if(empty($ORDER_EXTRAS)){
    $ORDER_EXTRAS = "[]";
}
if(empty($ORDER_STATUS->start_count)){
    $ORDER_STATUS->start_count = 0;
}

$API_BALANCE = $SMM_API->action(array(
  'key' => $SERVICE_DETAIL["api_key"],
  'action' =>'balance'
),
$SERVICE_DETAIL["api_url"]
);

$API_ORDER_CHARGE  = $ORDER_STATUS->charge;
if(!$API_ORDER_CHARGE){
  $API_ORDER_CHARGE = 0;
}
$API_CURRENCY  = $SERVICE_DETAIL["currency"];
$ORDER_PROFIT = from_to(get_currencies_array("enabled"),$API_CURRENCY,$settings["site_base_currency"],$SERVICE_PRICE - $API_ORDER_CHARGE);
$API_BALANCE = $API_BALANCE->balance;

$INSERT_API_ORDER = $conn->prepare("INSERT INTO orders SET 
    order_start=:count,
    order_error=:error,
    order_detail=:detail,
    client_id=:c_id,
    api_orderid=:order_id,
    service_id=:s_id,
    order_quantity=:quantity,
    order_charge=:price,
    order_url=:url,
    order_create=:create,
    order_extras=:extra,
    last_check=:last_check,
    order_api=:api,
    api_serviceid=:api_serviceid,
    subscriptions_status=:s_status,
    subscriptions_type=:subscriptions,
    subscriptions_username=:username,
    subscriptions_posts=:posts,
    subscriptions_delay=:delay,
    subscriptions_min=:min,
    subscriptions_max=:max,
    subscriptions_expiry=:expiry,
    dripfeed_id=:dripfeed_id,
    api_charge=:api_charge,
    api_currencycharge=:api_currencycharge,
    order_profit=:profit,
    order_increase=:order_increase
");

$INSERT_API_ORDER = $INSERT_API_ORDER->execute(array(
  "count"=> $ORDER_STATUS->start_count,
  "c_id"=>$API_CLIENT["client_id"],
  "detail"=>json_encode($API_ORDER),
  "error"=>$ORDER_ERROR,
  "s_id"=>$SERVICE_DETAIL["service_id"],
  "quantity"=>$QUANTITY,
  "price"=> $SERVICE_PRICE,
  "url"=> $LINK,
  "create"=>date("Y.m.d H:i:s"),
  "extra"=> $ORDER_EXTRAS,
  "order_id"=> $API_ORDER_ID,
  "last_check"=>date("Y.m.d H:i:s"),
  "api"=>$SERVICE_DETAIL["id"],
  "api_serviceid"=>$SERVICE_DETAIL["api_service"],
  "s_status"=>$subscriptions_status,
  "subscriptions"=>$subscriptions,
  "username"=>$username,
  'posts'=>$subscription_posts,
  "delay"=>$subscription_delay,
  "min"=>$subscription_min,
  "max"=>$subscription_max,
  "expiry"=>$subscription_expiry,
  "dripfeed_id"=>$dripfeed_id,
  "profit"=>$ORDER_PROFIT,
  "api_charge"=>$API_ORDER_CHARGE,
  "api_currencycharge"=>$currencycharge,
  "order_increase" => 0
));

if( $INSERT_API_ORDER ){
 $ORDER_ID = intval($conn->lastInsertId());
}

if ($settings["alert_orderfail"] == 2) {
if ($ORDER_ERROR != "-") {
$errorMessage = json_decode($ORDER_ERROR, true);
$msg = "Order Got Failed Order ID : ".$ORDER_ID."
Order Error : ".$errorMessage["error"]." 
View Fail orders in admin panel :
". site_url(). "admin/orders/1/failed"; 
$send = mail($settings["admin_mail"],"Failed Orders Information",$msg);
}
}

if($INSERT_API_ORDER){
$update_user = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
$update_user = $update_user->execute(array(
    "balance"=> $API_CLIENT["balance"] - $SERVICE_PRICE,
    "spent"=>$API_CLIENT["spent"] + $SERVICE_PRICE ,
    "id"=>$API_CLIENT["client_id"]
));
$insert_order_log = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
$insert_order_log = $insert_order_log->execute(array(
  "c_id"=>$API_CLIENT["client_id"],
  "action"=>"New API Order #".$ORDER_ID." has been placed by ".$API_CLIENT["username"].".",
  "ip"=>GetIP(),
  "date"=>date("Y-m-d H:i:s")
));

}

if( $settings["alert_apibalance"] == 2 && $SERVICE_DETAIL["api_limit"] > $API_BALANCE && $SERVICE_DETAIL["api_alert"] == 2 ){

$msg = "Provider balance is lesser than limit! API DOMAIN : ". $SERVICE_DETAIL["api_name"].". Available balance :".$API_BALANCE;
$send = mail($settings['admin_mail'],"Provider balance notification",$msg);
}
if($INSERT_API_ORDER && $update_user && $insert_order_log){
$OUTPUT["order"] = $ORDER_ID;
$OUTPUT = json_encode($OUTPUT,1);
header('Content-Type: application/json; charset=utf-8');
echo $OUTPUT;
exit();
} else {
  $conn->rollBack();
  // #ERROR 16 : Something went wrong.
APIErrorExit("Something went wrong while placing a manual order");
// #ERROR 16 : END
}


} // API TYPE 1 END





} // API ORDER END


exit();

} else {
// #ERROR 10 : Package or Service currently not supported.
APIErrorExit("Package or Service currently not supported.");
// #ERROR 10 : END
}

} else {
// #ERROR 10 : Package or Service is disabled.
APIErrorExit("Package or Service is disabled.");
// #ERROR 10 : END
}



} // END ACTION : ADD ORDER

} // API VERSION : V2
  else {
  APIErrorExit("API Version not supported or is invalid.");
}

} // DATA WAS POSTED


?>