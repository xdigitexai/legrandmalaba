<?php

session_start();
ob_start();

$config = require __DIR__.'/config.php';

try {
  $port = isset($config["db"]["port"]) ? ";port=".$config["db"]["port"] : "";
  $conn = new PDO("mysql:host=".$config["db"]["host"].$port.";dbname=".$config["db"]["name"].";charset=".$config["db"]["charset"].";", $config["db"]["user"], $config["db"]["pass"] );
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
  die($e->getMessage());
}

function get_currency_hash($code){
global $conn;
$site_curr = $conn->prepare("SELECT currency_hash FROM currencies WHERE currency_code=:code");

$site_curr->execute(array("code"=>$code));

$site_curr = $site_curr->fetch(PDO::FETCH_ASSOC)["currency_hash"];
return $site_curr;
}

if( isset($_COOKIE["u_id"], $_COOKIE["u_login"], $_COOKIE["u_password"]) ):

  $row = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
  $row->execute(array("id"=>$_COOKIE["u_id"] ));
  $row = $row->fetch(PDO::FETCH_ASSOC);

  $password = $row["password"] ?? '';

  if( ($_COOKIE["u_password"] ?? '') == $password ):
    $_SESSION["msmbilisim_userlogin"]      = 1;
    $_SESSION["msmbilisim_userid"]         = $row["client_id"];
    $_SESSION["msmbilisim_userpass"]       = $row["password"];
      if( ($access["admin_access"] ?? false) ):
        $_SESSION["msmbilisim_adminlogin"] = 1;
        $_SESSION["currency_hash"] = get_currency_hash($row["currency_type"]);
        if(!($_COOKIE["currency_hash"] ?? null)){
            $_SESSION["currency_hash"] = get_currency_hash($row["currency_type"]);
            setcookie("currency_hash",get_currency_hash($row["currency_type"]),strtotime('+28 days'),'/',null,null,true);
        }
      endif;
  else:
    unset($_SESSION["msmbilisim_userlogin"]);
    unset($_SESSION["msmbilisim_userid"]);
    unset($_SESSION["msmbilisim_userpass"]);
    unset($_SESSION["msmbilisim_adminlogin"]);
    unset($_SESSION);
    setcookie("u_id", $row["client_id"], time()-(60*60*24*7), '/', null, null, true );
    setcookie("u_password", $row["password"], time()-(60*60*24*7), '/', null, null, true );
    setcookie("u_login", 'ok', time()-(60*60*24*7), '/', null, null, true );
    setcookie("currency_hash",get_currency_hash($row["currency_type"]),time() - (60*60*24*7),'/',null,null,true);
    session_destroy();
  endif;

endif;

if( isset($_COOKIE["a_id"], $_COOKIE["a_login"], $_COOKIE["a_password"]) ):

  $admin      = $conn->prepare("SELECT * FROM admins WHERE admin_id=:id");
  $admin      ->execute(array("id"=>$_COOKIE["a_id"] ));
  $admin      = $admin->fetch(PDO::FETCH_ASSOC);
  $access   = json_decode($admin["access"] ?? '[]', true);
  $password = $admin["password"] ?? '';

  if( ($_COOKIE["a_password"] ?? '') == $password ):
    $_SESSION["msmbilisim_adminslogin"]      = 1;
    $_SESSION["msmbilisim_adminid"]         = $admin["admin_id"];
    $_SESSION["msmbilisim_adminpass"]       = $admin["password"];
      if( ($access["admin_access"] ?? false) ):
        $_SESSION["msmbilisim_adminlogin"] = 1;
      endif;
  else:
    unset($_SESSION["msmbilisim_adminlogin"]);
    unset($_SESSION["msmbilisim_adminid"]);
    unset($_SESSION["msmbilisim_adminpass"]);
    unset($_SESSION["msmbilisim_adminlogin"]);
    unset($_SESSION);
    setcookie("a_id", $admin["admin_id"], time()-(60*60*24*7), '/', null, null, true );
    setcookie("a_password", $admin["password"], time()-(60*60*24*7), '/', null, null, true );
    setcookie("a_login", 'ok', time()-(60*60*24*7), '/', null, null, true );
    session_destroy();
  endif;

endif;


$ordersid = $conn->prepare("SELECT * FROM orders WHERE order_increase=:id ");
$ordersid->execute(array("id" => 1));
 $ordersid  = $ordersid->fetchAll();

$settings = $conn->prepare("SELECT * FROM settings WHERE id=:id");
$settings->execute(array("id"=>1));
$settings = $settings->fetch(PDO::FETCH_ASSOC);

$general = $conn->prepare("SELECT * FROM General_options WHERE id=:id");
$general->execute(array("id"=>1));
$general = $general->fetch(PDO::FETCH_ASSOC);

$decoration = $conn->prepare("SELECT * FROM decoration WHERE id=:id");
$decoration->execute(array("id"=>1));
$decoration = $decoration->fetch(PDO::FETCH_ASSOC);

$panel = $conn->prepare("SELECT * FROM panel_info WHERE panel_id=:id");
$panel->execute(array("id"=>1));
$panel = $panel->fetch(PDO::FETCH_ASSOC);
define('THEME', $settings["site_theme"]);

$loader   = new Twig_Loader_Filesystem(__DIR__.'/views/'.THEME);
$twig     = new Twig_Environment($loader, ['autoescape' => false, 'cache' => PATH.'/app/views/cache']);

function is_user_currency_enable($currency_code){
global $conn;
$is_enable = $conn->prepare("SELECT is_enable FROM currencies WHERE currency_code=:code");
$is_enable->execute(array(
"code" => $currency_code
));
$is_enable = $is_enable->fetch(PDO::FETCH_ASSOC)["is_enable"];
return $is_enable;
}
$row = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
  $row->execute(array("id"=>($_COOKIE["u_id"] ?? 0)));
  $row = $row->fetch(PDO::FETCH_ASSOC);
if($row && is_user_currency_enable($row["currency_type"]) == "0"){
  $update = $conn->prepare("UPDATE clients SET currency_type=:currency_type WHERE client_id=:id ");
  $update = $update->execute(array("id"=>$row["client_id"],"currency_type"=>$settings["site_base_currency"]));
}

if($row && ($settings["site_currency_converter"] ?? '') == "0"){
  if(($row["currency_type"] ?? '') !== ($settings["site_base_currency"] ?? '')){
    $update = $conn->prepare("UPDATE clients SET currency_type=:currency_type WHERE client_id=:id ");
    $update = $update->execute(array("id"=>$row["client_id"],"currency_type"=>$settings["site_base_currency"]));
  }
}

$user = $conn->prepare("SELECT * FROM clients WHERE client_id=:id");
$user->execute(array("id"=>($_SESSION["msmbilisim_userid"] ?? 0)));
$user = $user->fetch(PDO::FETCH_ASSOC);
$user = is_array($user) ? $user : [];
$user['auth'] = $_SESSION["msmbilisim_userlogin"] ?? 0;
if($user["auth"] != 1):
  $user = $conn->prepare("SELECT * FROM clients WHERE passwordreset_token=:id");
  $user->execute(array("id"=> ($route[1] ?? '')));
  $user = $user->fetch(PDO::FETCH_ASSOC);
  $user = is_array($user) ? $user : [];
  $user['auth'] = 0;
endif;


$admin = $conn->prepare("SELECT * FROM admins WHERE admin_id=:id");
$admin->execute(array("id"=>($_SESSION["msmbilisim_adminid"] ?? 0)));
$admin = $admin->fetch(PDO::FETCH_ASSOC);
$admin = is_array($admin) ? $admin : [];
$admin['auth']   = $_SESSION["msmbilisim_adminslogin"] ?? 0;
$admin["access"] = json_decode($admin["access"] ?? '[]', true);





$currencies = $conn->prepare("SELECT * FROM currencies WHERE is_enable=1");
$currencies->execute();
$currencies = $currencies->fetchAll(PDO::FETCH_ASSOC);
$currencies_item = "";
for($i = 0;$i < count($currencies);$i++){
$x = $currencies[$i];
$currency_code = $x["currency_code"];
$currency_sym = $x["currency_symbol"];
if($currency_code == ($user["currency_type"] ?? '')){
    $currencies_item .= '';
}else {
$currencies_item .= '<li class="balance-dropdown__item">
<a href="javascript:void(0)" class="currencies-item balance-dropdown__link" data-rate-key="'.$currency_code.'" data-rate-symbol="'.$currency_sym.'">'.$currency_code.' '.$currency_sym.'</a></li>';
}

}

$offline_currencies = $conn->prepare("SELECT * FROM currencies WHERE is_enable=1 AND id!=1");
$offline_currencies->execute ();
$offline_currencies = $offline_currencies->fetchAll(PDO::FETCH_ASSOC);
$offline_currencies_item = "";
$offline_currencies_item_with_li = "";
for($i = 0;$i < count($offline_currencies);$i++){
$x = $offline_currencies[$i];
$currency_code = $x["currency_code"];
$currency_sym = $x["currency_symbol"];
$offline_currencies_item .= '<a class="dropdown-item" href="service/'.$currency_code.'">'.$currency_code.' '.$currency_sym.'</a>';
$offline_currencies_item_with_li .=  '<li><a class="dropdown-item" href="service/'.$currency_code.'">'.$currency_code.' '.$currency_sym.'</a></li>';
}


foreach ( glob(__DIR__.'/helper/*.php') as $helper ) {
  require $helper;
}

foreach ( glob(__DIR__.'/classes/*.php') as $class ) {
  require $class;
}

$currencies_array = get_currencies_array("all");

$timezones  = [
    
  ["label"=>"(UTC -12:00) Baker/Howland Island","timezone"=>"-54000"],
  ["label"=>"(UTC -11:00) Niue","timezone"=>"-50400"],
  ["label"=>"(UTC -10:00) Hawaii-Aleutian Standard Time, Cook Islands, Tahiti","timezone"=>"-46800"],
  ["label"=>"(UTC -9:30) Marquesas Islands","timezone"=>"-45000"],
  ["label"=>"(UTC -9:00) Alaska Standard Time, Gambier Islands","timezone"=>"-43200"],
  ["label"=>"(UTC -8:00) Pacific Standard Time, Clipperton Island","timezone"=>"-39600"],
  ["label"=>"(UTC -7:00) Mountain Standard Time","timezone"=>"-36000"],
  ["label"=>"(UTC -6:00) Central Standard Time","timezone"=>"-32400"],
  ["label"=>"(UTC -5:00) Eastern Standard Time, Western Caribbean Standard Time","timezone"=>"-28800"],
  ["label"=>"(UTC -4:30) Venezuelan Standard Time","timezone"=>"-27000"],
  ["label"=>"(UTC -4:00) Atlantic Standard Time, Eastern Caribbean Standard Time","timezone"=>"-25200"],
  ["label"=>"(UTC -3:30) Newfoundland Standard Time","timezone"=>"-23400"],
  ["label"=>"(UTC -3:00) Argentina, Brazil, French Guiana, Uruguay","timezone"=>"-21600"],
  ["label"=>"(UTC -2:00) South Georgia/South Sandwich Islands","timezone"=>"-18000"],
  ["label"=>"(UTC -1:00) Azores, Cape Verde Islands","timezone"=>"-14400"],
  ["label"=>"(UTC) Greenwich Mean Time, Western European Time","timezone"=>"-10800"],
  ["label"=>"(UTC +1:00) Central European Time, West Africa Time","timezone"=>"-7200"],
  ["label"=>"(UTC +2:00) Central Africa Time, Eastern European Time, Kaliningrad Time","timezone"=>"-3600"],
  ["label"=>"(UTC +3:00) Moscow Time, East Africa Time, Arabia Standard Time","timezone"=>"0"],
  ["label"=>"(UTC +3:30) Iran Standard Time","timezone"=>"1800"],
  ["label"=>"(UTC +4:00) Azerbaijan Standard Time, Samara Time","timezone"=>"3600"],
  ["label"=>"(UTC +4:30) Afghanistan","timezone"=>"5400"],
  ["label"=>"(UTC +5:00) Pakistan Standard Time, Yekaterinburg Time","timezone"=>"7200"],
  ["label"=>"(UTC +5:30) Indian Standard Time, Sri Lanka Time","timezone"=>"9000"],
  ["label"=>"(UTC +5:45) Nepal Time","timezone"=>"9900"],
  ["label"=>"(UTC +6:00) Bangladesh Standard Time, Bhutan Time, Omsk Time","timezone"=>"10800"],
  ["label"=>"(UTC +6:30) Cocos Islands, Myanmar","timezone"=>"12600"],
  ["label"=>"(UTC +7:00) Krasnoyarsk Time, Cambodia, Laos, Thailand, Vietnam","timezone"=>"14400"],
  ["label"=>"(UTC +8:00) Philippine Standard Time (Asia/Manila)","timezone"=>"18000"],
  ["label"=>"(UTC +8:00) Australian Western Standard Time, Beijing Time, Irkutsk Time","timezone"=>"18000"],
  ["label"=>"(UTC +8:45) Australian Central Western Standard Time","timezone"=>"20700"],
  ["label"=>"(UTC +9:00) Japan Standard Time, Korea Standard Time, Yakutsk Time","timezone"=>"21600"],
  ["label"=>"(UTC +9:30) Australian Central Standard Time","timezone"=>"23400"],
  ["label"=>"(UTC +10:00) Australian Eastern Standard Time, Vladivostok Time","timezone"=>"25200"],
  ["label"=>"(UTC +10:30) Lord Howe Island","timezone"=>"27000"],
  ["label"=>"(UTC +11:00) Srednekolymsk Time, Solomon Islands, Vanuatu","timezone"=>"28800"],
  ["label"=>"(UTC +11:30) Norfolk Island","timezone"=>"30600"],
  ["label"=>"(UTC +12:00) Fiji, Gilbert Islands, Kamchatka Time, New Zealand Standard Time","timezone"=>"32400"],
  ["label"=>"(UTC +12:45) Chatham Islands Standard Time","timezone"=>"35100"],
  ["label"=>"(UTC +13:00) Samoa Time Zone, Phoenix Islands Time, Tonga","timezone"=>"36000"],
  ["label"=>"(UTC +14:00) Line Islands","timezone"=>"39600"]
];
