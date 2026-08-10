<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
$title .= "Child Panels";
if ($settings["childpanel_selling"] == 1){
header("Location:" . site_url());
}
if( $settings["email_confirmation"] == 1  && $user["email_type"] == 1  ){
  Header("Location:".site_url('confirm_email'));
}
if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}

$client_id =$user['client_id']; 

$panel_logs = $conn->prepare("SELECT * FROM childpanels INNER JOIN clients ON clients.client_id=childpanels.client_id WHERE childpanels.client_id=:client_id ORDER BY childpanels.id DESC");
$panel_logs->execute(array(
  "client_id" => $client_id
));
$panel_logs = $panel_logs->fetchAll(PDO::FETCH_ASSOC);

$currenciesArray = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"]."/currencies.json"),1);
$choose_currencies .= "";
foreach($currenciesArray as $code => $value){
$choose_currencies .= '<option value="'.$code.'">'.$value["name"].' ('.$code.')</option>';
}

$childpanel_nameservers = json_decode($settings["child_panel_nameservers"],true);

if( $_POST ):
    
if($_POST["renew"]){
    $now = new DateTime();
    $renewal_date = $now->format('Y-m-d');
    
$renew_id = htmlspecialchars($_POST["renew_id"]);
$childorders = $conn->prepare("SELECT * FROM childpanels WHERE id=:id");
$childorders->execute(array("id" => $renew_id));
$childorders = $childorders->fetch(PDO::FETCH_ASSOC);
$childorders = $childorders;

if($user['balance'] < $childorders['charge']){
  $conn->beginTransaction();
  $update = $conn->prepare("UPDATE childpanels SET status=:status WHERE id=:id");
  $update = $update->execute(array("id"=>$childorders['id'],"status"=>"terminated"));
  $conn->commit();
  $error    = 1;
  $errorText= $languageArray["error.neworder.balance.notenough"];
} else {
  $date = new DateTime();
  $date->modify('+1 month');
  $renewal_date = $date->format('Y-m-d');
  
  $price = $childorders['charge'];
  
  $conn->beginTransaction();
  $insert = $conn->prepare("UPDATE childpanels SET renewal_date=:renewal_date, status=:status WHERE id=:id");
  $insert = $insert->execute(array("renewal_date"=>$renewal_date,"status"=>"active","id"=>$_POST["renew_id"]));
    
  $update = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
  $update = $update-> execute(array("balance"=>$user["balance"]-$price,"spent"=>$user["spent"]+$price,"id"=>$user["client_id"]));
  
 $insert2 = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
 $insert2->execute(array("c_id"=>$user["client_id"],"action"=>"Child Panel Renewed with id : ".$_POST["renew_id"].".","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
    if ( $insert && $update && $insert2 ):
      $conn->commit();
$order_data = ['success'=>2,'id'=>$_POST["renew_id"],"service"=>"Child Panel","link"=>$childorders["domain"],"quantity"=>"1","price"=>$price,"balance"=>$user["balance"] ];
      $_SESSION["data"]["services"]   = "Child Panel";
      $_SESSION["data"]["categories"] = "Child Panels";
      $_SESSION["data"]["childpanel"] = $order_data;
      header("Location:".site_url("child-panels/".$_POST["renew_id"]));
    else:
      $conn->rollBack();
      header("Location:".site_url("child-panels"));
    endif;
      
  }
      
   }else{

  foreach ($_POST as $key => $value) {
    $_SESSION["data"][$key]  = $value;
  }
  
  
  $ip       = GetIP();
  $domain  = htmlspecialchars(strip_tags($_POST["panel_domain"]), ENT_QUOTES, 'UTF-8');
  $domain   = strip_tags(htmlspecialchars($domain));
  $currency = strip_tags(htmlspecialchars($_POST["panel_currency"]));
  $username  = htmlspecialchars(strip_tags($_POST["admin_username"]), ENT_QUOTES, 'UTF-8');
  $username = strip_tags(htmlspecialchars($username));
  $password  = htmlspecialchars(strip_tags($_POST["admin_password"]), ENT_QUOTES, 'UTF-8');
  $password = strip_tags($password);
  $re_password      = strip_tags($_POST["admin_confirm_password"]);
  $price = $settings["childpanel_price"];
  
  $date = new DateTime();
  $date->modify('+1 month');
  $renewal_date = $date->format('Y-m-d');
    
    if(empty($domain)):
      $error    = 1;
      $errorText= "Please enter a valid domain name";
    elseif( empty($currency)):
      $error    = 1;
      $errorText= "Please choose a valid currency";
    elseif( empty($username)):
      $error    = 1;
      $errorText= "Enter a valid username";
    elseif( empty($password) ):
      $error    = 1;
      $errorText= "Enter a valid Password";
    elseif( $password != $re_password ):
      $error    = 1;
      $errorText= "Passwords do not match";  
    elseif( ( $price > $user["balance"] ) && $user["balance_type"] == 2 ):
      $error    = 1;
      $errorText= $languageArray["error.neworder.balance.notenough"];
    elseif( ( $user["balance"] - $price < "-".$user["debit_limit"] ) && $user["balance_type"] == 1 ):
      $error    = 1;
      $errorText= $languageArray["error.neworder.balance.notenough"];  
    else:
  $conn->beginTransaction();

$insert = $conn->prepare("INSERT INTO childpanels SET 
     client_id=:c_id,
     domain=:domain,
     child_panel_currency=:currency,
     child_panel_username=:username,
     child_panel_password=:password,
     charged_amount=:charged_amount,
     renewal_date=:renewal_date, 
     created_on=:created_on,
     child_panel_uqid=:uqid");

 $insert-> execute(array(
   "c_id"=>$user["client_id"],
   "domain"=>$domain,
   "currency"=>$currency,
   "username"=>$username,
   "password"=>$password,
   "charged_amount"=>$price,
   "renewal_date"=>$renewal_date,
   "created_on" => date("Y.m.d H:i:s"),
   "uqid"=>md5(openssl_random_pseudo_bytes(16))
)); 

if( $insert ):
  $last_id = $conn->lastInsertId();
endif;

  $update = $conn->prepare("UPDATE clients SET balance=:balance, spent=:spent WHERE client_id=:id");
  $update->execute(array("balance"=>$user["balance"]-$price,"spent"=>$user["spent"]+$price,"id"=>$user["client_id"]));
  $insert2= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
 
 $insert2->execute(array("c_id"=>$user["client_id"],"action"=>"#".$last_id." New Child Panel Order.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
    if ( $insert && $update && $insert2 ):

      $conn->commit();
      unset($_SESSION["data"]);
      $user = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
      $user->execute(array("id"=>$_SESSION["msmbilisim_userid"] ));
      $user = $user->fetch(PDO::FETCH_ASSOC);
      $user['auth']   = $_SESSION["msmbilisim_userlogin"];
$order_data = [
     'success'=>1,
     'id'=>$last_id,
     "link"=>$domain,
     "quantity"=>"1",
     "price"=> format_amount_string($user["currency_type"],from_to($currencies_array,$settings["site_base_currency"],$user["currency_type"],$price)),
     "balance"=>format_amount_string($user["currency_type"],from_to($currencies_array,$settings["site_base_currency"],$user["currency_type"],$user["balance"]))
];
      $_SESSION["data"]["services"]   = "Child Panel";
      $_SESSION["data"]["categories"] = "Child Panels";
      $_SESSION["data"]["childpanel"] = $order_data;
header("Location:".site_url("child-panels/".$last_id));
else:
$conn->rollBack();
$error    = 1;
$errorText= "Child Panel order failed";
    endif;
     
 endif;
 

  }  
endif;

?>