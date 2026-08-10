<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

if( !route(1) ){
    $route[1] = "login";
}

$title     = $title ?? '';
$pagetitle = $pagetitle ?? '';
if( route(1) == "login" ){
    $title .= $pagetitle;
}elseif( route(1) == "register" ){
    $title .= $languageArray["signup.title"] ?? '';
}

if( ( route(1) == "login" || route(1) == "register") && ($_SESSION["msmbilisim_userlogin"] ?? 0) ){
     header("Location:".site_url());exit();
}
if(route(1) == "neworder" || route(1) == "orders" || route(1) == "tickets" || route(1) == "addfunds" || route(1) == "account" || route(1) == "dripfeeds" || route(1) == "reference" || route(1) == "subscriptions" ) {
    header("Location:".site_url()); exit();
}
// Only log visit if a real client row was found from a cookie session
if(!empty($row["client_id"])) {
    $insert = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
    $insert->execute(array("c_id"=>$row["client_id"],"action"=>"Member logged in.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
    $update = $conn->prepare("UPDATE clients SET login_date=:date, login_ip=:ip WHERE client_id=:c_id ");
    $update->execute(array("c_id"=>$row["client_id"],"date"=>date("Y.m.d H:i:s"),"ip"=>GetIP() ));
}
$google_login_content = '';

$settings["google_login"] = json_decode($settings["google_login"],true)["status"];

if($_SERVER["REQUEST_METHOD"] == "GET" && $settings["google_login"] == "1"){

$google_login_content = '<hr><button type="button" id="login-with-google-btn" class="login-with-google-btn btn btn-secondary" onclick="window.location.href=\'?login-with-google\';"><i class="fab fa-google"></i>&nbsp;&nbsp;Continuer avec Google</button>';


$client_id = $settings["googleclientid"];
$client_secret = $settings["googleclientsecret"];;
$redirect_uri = site_url();
$client = new Google\Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope('email');
$client->addScope('profile');
if(array_key_exists("login-with-google",$_GET)){
$auth_url = $client->createAuthUrl();
header('Location: ' .filter_var($auth_url, FILTER_SANITIZE_URL));
exit();
}
if (isset($_GET['code'])) {
  $token = $client->fetchAccessTokenWithAuthCode(urldecode($_GET['code']));
  $client->setAccessToken($token['access_token']);
  $oauth2 = new Google\Service\Oauth2($client);
  $userinfo = $oauth2->userinfo->get();
  $_SESSION['email'] = $userinfo->email;
  $check_if_username_exists = $conn->prepare("SELECT * FROM clients WHERE email=:email");
  $check_if_username_exists->execute([
   "email" => $userinfo->email
  ]);
  if($check_if_username_exists->rowCount()){
    // SIGN IN 
  $check_if_username_exists = $check_if_username_exists->fetch(PDO::FETCH_ASSOC);
  $currency_hash = $check_if_username_exists["currency_type"];
   $_SESSION["msmbilisim_userlogin"] = 1;
$_SESSION["msmbilisim_userid"] = $check_if_username_exists["client_id"];
$_SESSION["msmbilisim_userpass"] = $check_if_username_exists["password"];
$_SESSION["currency_hash"] = $currency_hash;
setcookie("u_id", $check_if_username_exists["client_id"], strtotime('+1 days'), '/', null, null, true);
setcookie("u_password", $check_if_username_exists["password"], strtotime('+1 days'), '/', null, null, true);
setcookie("u_login", 'ok', strtotime('+1 days'), '/', null, null, true);setcookie("currency_hash",$currency_hash,strtotime('+1 days'),'/',null,null,true);
header("Location: ".site_url(""));
exit();
  } else {
    $email = $userinfo->email; 
    $username = strtolower(preg_replace('/[^a-z0-9]/', '', explode('@', $email)[0]));

    $ref_code = substr(bin2hex(random_bytes(18)), 5, 6);
    $apikey   = md5(openssl_random_pseudo_bytes(16));
    $pass     = openssl_random_pseudo_bytes(16);

    $conn->beginTransaction();
    $insert = $conn->prepare("INSERT INTO clients 
        SET name=:name, username=:username, email=:email, password=:pass, 
            lang=:lang, telephone=:phone, register_date=:date, apikey=:key, 
            ref_code=:ref_code, email_type=:type, balance=:spent, spent=:spent, 
            currency_type=:currency_type");

    $insert->execute([
        "lang"           => "fr",
        "name"           => $userinfo->name,
        "username"       => $username,
        "email"          => $email,
        "pass"           => md5($pass),
        "phone"          => "",
        "date"           => date("Y.m.d H:i:s"),
        "key"            => $apikey,
        "ref_code"       => $ref_code,
        "type"           => 2,
        "spent"          => "0.0000",
        "currency_type"  => get_default_currency()
    ]);
    $conn->commit();

    // Retrieve new user
    $user = $conn->prepare("SELECT * FROM clients WHERE email=:email");
    $user->execute(["email" => $email]);
    $user = $user->fetch(PDO::FETCH_ASSOC);

    // Sessions
    $_SESSION["msmbilisim_userid"] = $user["client_id"];
    $_SESSION["msmbilisim_userpass"] = $user["password"];
    $_SESSION["msmbilisim_userlogin"] = 1;
    $_SESSION["currency_hash"] = $user["currency_type"];

    // Cookies
    setcookie("u_id", $user["client_id"], strtotime('+1 days'), '/', null, null, true);
    setcookie("u_password", $user["password"], strtotime('+1 days'), '/', null, null, true);
    setcookie("u_login", 'ok', strtotime('+1 days'), '/', null, null, true);
    setcookie("currency_hash", $user["currency_type"], strtotime('+1 days'), '/', null, null, true);

    // Redirect
    header("Location: " . site_url(""));
    exit();
}
}



}





if( $route[1] == "login" && $_POST ){

$username       = $_POST["username"];
$mail = "@";
// Test if string contains the word 

$username       = $_POST["username"];
    $username = strip_tags($username);
    $username = htmlspecialchars(strip_tags($username), ENT_QUOTES, 'UTF-8');
    $pass           = $_POST["password"];
    $captcha        = $_POST['g-recaptcha-response'];
    $remember       = $_POST["remember"];
    $googlesecret   = $settings["recaptcha_secret"];
 $captcha_control= file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$googlesecret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
 $captcha_control= json_decode($captcha_control);


if(strpos($username, $mail) == false){

    if( $settings["recaptcha"] == 2 && $captcha_control->success == false && $_SESSION["recaptcha"]  ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.recaptcha"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( !userdata_check("username",$username) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.username"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
}elseif( !user_password_check($username, $pass) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.notmatch"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( countRow(["table"=>"clients","where"=>["username"=>$username,"client_type"=>1]]) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.deactive"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }else{
        $row    = $conn->prepare("SELECT * FROM clients WHERE username=:username && password=:password ");
        $row  -> execute(array("username"=>$username,"password"=>md5($pass) ));
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $access = json_decode($row["access"],true);


$_SESSION["msmbilisim_userlogin"] = 1;
$_SESSION["msmbilisim_userid"] = $row["client_id"];
$_SESSION["msmbilisim_userpass"]       = md5($pass);
$_SESSION["recaptcha"]= false;
$update = $conn->prepare("UPDATE clients SET broadcast_id=:bid WHERE client_id=:cid");
$update->execute(array(
"bid" => 0,
"cid" => $row["client_id"]
));
$currency_hash = get_currency_hash_by_code(get_default_currency());

$_SESSION["currency_hash"] = $currency_hash;

        if( $access["admin_access"] ):
            $_SESSION["msmbilisim_adminlogin"] = 1;
            $_SESSION["login_referrer"] = true;
        endif;
        if( $remember ){
            if($access["admin_access"]):
                setcookie("a_login", 'ok', strtotime('+28 days'), '/', null, null, true);
            endif;
            setcookie("u_id", $row["client_id"], strtotime('+28 days'), '/', null, null, true);
            setcookie("u_password", $row["password"], strtotime('+28 days'), '/', null, null, true);
            setcookie("u_login", 'ok', strtotime('+28 days'), '/', null, null, true);
            setcookie("currency_hash",$currency_hash,strtotime('+28 days'),'/',null,null,true);
        }else{
            setcookie("u_id", $row["client_id"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_password", $row["password"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_login", 'ok', strtotime('+7 days'), '/', null, null, true );
            setcookie("currency_hash",$currency_hash,strtotime('+7 days'),'/',null,null,true);
        }
        
        header('Location:'.site_url(''));
        $insert = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
        $insert->execute(array("c_id"=>$row["client_id"],"action"=>"Member logged in.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
        $update = $conn->prepare("UPDATE clients SET login_date=:date, login_ip=:ip WHERE client_id=:c_id ");
        $update->execute(array("c_id"=>$row["client_id"],"date"=>date("Y.m.d H:i:s"),"ip"=>GetIP() ));
        exit();
    }









} else {


$row    = $conn->prepare("SELECT * FROM clients WHERE email=:username && password=:password ");
        $row  -> execute(array("username"=>$username,"password"=>md5($pass) ));
        $row    = $row->fetch(PDO::FETCH_ASSOC);
$usersname  =  $row["username"];

if( $settings["recaptcha"] == 2 && $captcha_control->success == false && $_SESSION["recaptcha"]  ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.recaptcha"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( !userdata_check("email",$username) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.username"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( !userdata_check("password", md5($pass) ) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.notmatch"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }elseif( countRow(["table"=>"clients","where"=>["username"=>$username,"client_type"=>1]]) ){
        $error      = 1;
        $errorText  = $languageArray["error.signin.deactive"];
        if( $settings["recaptcha"] == 2 ){ $_SESSION["recaptcha"]  = true; }
    }else{
        $row    = $conn->prepare("SELECT * FROM clients WHERE email=:username && password=:password ");
        $row  -> execute(array("username"=>$username,"password"=>md5($pass) ));
        $row    = $row->fetch(PDO::FETCH_ASSOC);
        $access = json_decode($row["access"],true);

     
    $_SESSION["msmbilisim_userlogin"]      = 1;
        $_SESSION["msmbilisim_userid"]         = $row["client_id"];
        $_SESSION["msmbilisim_userpass"]       = md5($pass);
        $_SESSION["recaptcha"]                = false;
        $update = $conn->prepare("UPDATE clients SET broadcast_id=:bid WHERE client_id=:cid");
$update->execute(array(
"bid" => 0,
"cid" => $row["client_id"]
));
$currency_hash = get_currency_hash_by_code(get_default_currency());

$_SESSION["currency_hash"] = $currency_hash;
        if( $access["admin_access"] ):
            $_SESSION["msmbilisim_adminlogin"] = 1;
            $_SESSION["login_referrer"] = true;
        endif;
        if( $remember ){
            if($access["admin_access"]):
                setcookie("a_login", 'ok', strtotime('+7 days'), '/', null, null, true);
            endif;
            setcookie("u_id", $row["client_id"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_password", $row["password"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_login", 'ok', strtotime('+7 days'), '/', null, null, true);
        }else{
            setcookie("u_id", $row["client_id"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_password", $row["password"], strtotime('+7 days'), '/', null, null, true);
            setcookie("u_login", 'ok', strtotime('+7 days'), '/', null, null, true );
            setcookie("currency_hash",$currency_hash,strtotime('+7 days'),'/',null,null,true);
        }
        
        header('Location:'.site_url(''));
        $insert = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
        $insert->execute(array("c_id"=>$row["client_id"],"action"=>"Member  in.","ip"=>GetIP(),"date"=>date("Y-m-d H:i:s") ));
        $update = $conn->prepare("UPDATE clients SET login_date=:date, login_ip=:ip WHERE client_id=:c_id ");
        $update->execute(array("c_id"=>$row["client_id"],"date"=>date("Y.m.d H:i:s"),"ip"=>GetIP() ));
        exit();
    }


    
} 
}
