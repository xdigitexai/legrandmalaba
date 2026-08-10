<?php

if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

$admin = $conn->prepare("SELECT * FROM admins");
$admin->execute();
$admin = $admin->fetch(PDO::FETCH_ASSOC);
if ($admin["ip_type"] == 2 && $_SERVER['REMOTE_ADDR'] !== $admin["ip"]) {
    http_response_code(404);
    include('404.php');
    exit();
}


 if( $_POST ){
  $username       = htmlspecialchars($_POST["username"]);
  $pass           = $_POST["password"];
  $captcha        = htmlspecialchars($_POST['g-recaptcha-response']);
  $remember       = htmlspecialchars($_POST["remember"]);
 $two_factor_code = htmlspecialchars($_POST["two_factor_code"]);
  $googlesecret   = $settings["recaptcha_secret"];
  $captcha_control= file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$googlesecret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
  $captcha_control= json_decode($captcha_control);


  if( $settings["recaptcha"] == 2 && $captcha_control->success == false && $_SESSION["recaptcha"]  ){
    $error      = 1;
    $errorText  = "Please verify that you are not a robot.";
      if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
  }elseif( countRow(["table"=>"admins","where"=>["username"=>$username,"client_type"=>1]]) ){
    $error      = 1;
    $errorText  = "Your account is Suspended.";
      if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
  }else{
    $admin    = $conn->prepare("SELECT * FROM admins WHERE username=:username && password=:password ");
    $admin  -> execute(array("username"=>$username,"password"=>$pass));
    $admin    = $admin->fetch(PDO::FETCH_ASSOC);
    $access = json_decode($admin["access"],true);

if( $access["admin_access"] ){
 if( $admin["two_factor"] == 1){

$google2fa = new \PragmaRX\Google2FA\Google2FA();

$is_valid = $google2fa->verifyKey($admin["two_factor_secret_key"], $two_factor_code);
}

 if($admin["two_factor"] == 1 && $is_valid == true){
 $_SESSION["msmbilisim_adminlogin"]= 1;

$_SESSION["msmbilisim_adminid"]         = $admin["admin_id"];
            $_SESSION["msmbilisim_adminpass"]       = $pass ;
            $_SESSION["recaptcha"]                = false;
setcookie("a_login", 'ok', time()+(60*60*24*7), '/', null, null, true );
              
              setcookie("a_id", $admin["admin_id"], time()+(60*60*24*7), '/', null, null, true );
              setcookie("a_password", $admin["password"], time()+(60*60*24*7), '/', null, null, true );
              setcookie("a_login", 'ok', time()+(60*60*24*7), '/', null, null, true );
         header('Location: '.site_url('admin'));
         exit();
}  elseif($admin["two_factor"] == 1 && $is_valid == false) {
  $error = 1;

$errorText  = "Invalid Code.";
} else {
$_SESSION["msmbilisim_adminlogin"]= 1;
$_SESSION["msmbilisim_adminid"] = $admin["admin_id"];
$_SESSION["msmbilisim_adminpass"] = $pass ;

$_SESSION["recaptcha"] = false;
setcookie("a_login", 'ok', time()+(60*60*24*7), '/', null, null, true );
setcookie("a_id", $admin["admin_id"], time()+(60*60*24*7), '/', null, null, true );
setcookie("a_password", $admin["password"], time()+(60*60*24*7), '/', null, null, true );
setcookie("a_login", 'ok', time()+(60*60*24*7), '/', null, null, true );
header('Location: '.site_url('admin'));
}
$update = $conn->prepare("UPDATE admins SET login_date=:date, login_ip=:ip WHERE admin_id=:c_id ");
$update->execute(array("c_id"=>$admin["admin_id"],"date"=>date("Y.m.d H:i:s"),"ip"=>GetIP() ));

 } else {
$error = 1;
$errorText  = "Could not find administrator account registered with this information.";
 }
    
      
  }
 }


if( $access["admin_access"]  && $_SESSION["msmbilisim_adminlogin"]  ):
        
        exit();
else:
        require admin_view('login');
endif;