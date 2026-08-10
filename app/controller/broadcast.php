<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){

header("Location:".site_url('logout'));

}

if( $settings["email_confirmation"] == 1  && $user["email_type"] == 1  ){
 header("Location:".site_url('confirm_email'));
}


$bid_from_db = $conn->prepare("SELECT broadcast_id FROM clients WHERE client_id=:cid");
$bid_from_db->execute(array("cid" => $user["client_id"]));
$bid_from_db = $bid_from_db->fetch(PDO::FETCH_ASSOC)["broadcast_id"];
$id = $bid_from_db;
if($bid_from_db == 0){
 $id = "00";
}
$next_broadcast = $conn->prepare("SELECT * FROM notifications_popup WHERE id > $id AND expiry_date >= DATE(now()) AND status=:status LIMIT 1");

$next_broadcast->execute(array("status" => 1));
$next_broadcast = $next_broadcast->fetchAll(PDO::FETCH_ASSOC);

if(count($next_broadcast)){
$broadcast_id = $next_broadcast[0]["id"];
$broadcast_title = $next_broadcast[0]["title"];
$broadcast_desc = $next_broadcast[0]["description"];
$broadcast_icon = $next_broadcast[0]["type"];
$action_link = $next_broadcast[0]["action_link"];
$action_text = $next_broadcast[0]["action_text"];
$conn->beginTransaction();
$update = $conn->prepare("UPDATE clients SET broadcast_id=:bid WHERE client_id=:cid");
$update->execute(array(
"bid"=>$broadcast_id,"cid"=>$user["client_id"]
));


$broadcast_output = array(
"id" => $broadcast_id,
"BROADCAST_ICON" => $broadcast_icon,
"BROADCAST_TITLE" => $broadcast_title,
"BROADCAST_DESCRIPTION" => $broadcast_desc
);

} else {

$broadcast_output = array(
"id" => "undefined"
);

}

echo json_encode($broadcast_output,true);
exit();
?>