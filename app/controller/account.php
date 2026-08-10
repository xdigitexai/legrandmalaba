<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if( $panel["panel_status"] == "suspended" ): include 'app/views/frozen.twig';exit(); endif;
if( $panel["panel_status"] == "frozen" ): include 'app/views/frozen.twig';exit(); endif;
$title .= $languageArray["account.title"];

if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
  header("Location:".site_url('logout'));
}
if(route(1) == "change_currency"){
  
$rate_key = $_POST["rate_key"];
$rate_sym = $_POST["sym"];

$currencies = $conn->prepare("SELECT * FROM currencies WHERE is_enable=1");
$currencies->execute();
$currencies = $currencies->fetchAll(PDO::FETCH_ASSOC);
$currencies = array_group_by($currencies,"currency_code");

if(array_key_exists($rate_key,$currencies) && $settings["site_currency_converter"] == "1"){
 $update = $conn->prepare("UPDATE clients SET currency_type=:currency_type WHERE client_id=:id ");
 $update = $update->execute(array("id"=>$user["client_id"],"currency_type"=>$rate_key));

$resp = array(
"success" => true,
"message" => "Success"
);

setcookie("currency_hash",get_currency_hash_by_code($rate_key),strtotime('+28 days'),'/',null,null,true);
}
else {
header("Location:".site_url(''));
}
 } elseif( route(1) == "newapikey" ){
    $conn->beginTransaction();
    $insert= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
    $insert= $insert->execute(array("c_id"=>$user["client_id"],"action"=>"API Key changed","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
    $apikey = CreateApiKey(["email"=>$user["email"],"username"=>$user["username"]]);
    $update = $conn->prepare("UPDATE clients SET apikey=:key WHERE client_id=:id ");
    $update = $update->execute(array("id"=>$user["client_id"],"key"=>$apikey ));
    if( $update ):
if( $settings["alert_apimail"]  == "2" ){
$htmlContent = "Hello ".$user["username"]. ", 
Your API key has been changed.
If it was done by you, you may ignore this message. If not - change your account password and an API key.";
        $site_name = $settings["site_name"];
        $to = $user["email"]; 
$from = $settings["smtp_user"]; 
$fromName = $settings["site_seo"]; 
$subject = "Api key changed"; 
$headers = "MIME-Version: 1.0" . "\r\n"; 
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
$headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
$headers .= 'Cc: '.$from . "\r\n"; 
$headers .= 'Bcc: '.$from . "\r\n"; 
 if(mail($to, $subject, $htmlContent, $headers)){ 
}else{ 
     } 
} 
      $conn->commit();
      $success    = 1;
      $successText= "API key has been generated: $apikey";

    else:
      $conn->rollBack();
      $error    = 1;
      $errorText= "Api key has not changed, Try again later!";

    endif;
  
}elseif( route(1) == "change_lang" && $_POST ){
    $lang     = $_POST["lang"];
    $update = $conn->prepare("UPDATE clients SET lang=:lang WHERE client_id=:id ");
    $update = $update->execute(array("id"=>$user["client_id"],"lang"=>$lang ));
    header("Location:".site_url('account'));
}elseif( route(1) == "currency" && $_POST ){
$conn->beginTransaction();
    $currency    = $_POST["currency"];
    $update = $conn->prepare("UPDATE clients SET currency=:type WHERE client_id=:id ");
    $update = $update->execute(array("id"=>$user["client_id"],"type"=>$currency ));
    
    header("Location:".site_url('account'));
}elseif (route(1) == "timezone" && $_POST) {
    $timezone = $_POST["timezone"];
    $update = $conn->prepare("UPDATE clients SET timezone = :timezone WHERE client_id = :id");
    $update->execute(array(
        "id" => $user["client_id"],
        "timezone" => $timezone
    ));

    $_SESSION["success"] = "Timezone updated successfully.";
    header("Location:" . site_url('account'));
}elseif( route(0) == "account" && $_POST ){

  $pass     = $_POST["current_password"];
  $new_pass = $_POST["password"];
  $new_again= $_POST["confirm_password"];

  if( !userdata_check('password',md5($pass) ) ){
    $error    = 1;
    $errorText= $languageArray["error.account.password.notmach"];
  }elseif( strlen($new_pass) < 8 ){
    $error    = 1;
    $errorText= $languageArray["error.account.password.length"];
  }elseif( $new_pass != $new_again ){
    $error    = 1;
    $errorText= $languageArray["error.account.passwords.notmach"];
  }else{
    $conn->beginTransaction();
      $insert= $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
      $insert= $insert->execute(array("c_id"=>$user["client_id"],"action"=>"User password has been changed","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
      $update = $conn->prepare("UPDATE clients SET password=:pass WHERE client_id=:id ");
      $update = $update->execute(array("id"=>$user["client_id"],"pass"=>md5($new_pass) ));
        if( $update && $insert ):
          $_SESSION["msmbilisim_userpass"]       = md5($new_pass);
          

          $conn->commit();
          $success    = 1;
          $successText= $languageArray["error.account.password.success"];

        else:
          $conn->rollBack();
          $error    = 1;
          $errorText= $languageArray["error.account.password.fail"];
        endif;
  }

}
