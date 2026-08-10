<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

  unset($_SESSION["msmbilisim_adminid"]);
  unset($_SESSION["msmbilisim_adminpass"]);
  unset($_SESSION["msmbilisim_userlogin"]);
  setcookie("a_id", $admin["admin_id"], time()-(60*60*24*7), '/', null, null, true );
  setcookie("a_password", $admin["password"], time()-(60*60*24*7), '/', null, null, true );
  setcookie("a_login", 'ok', time()-(60*60*24*7), '/', null, null, true );
  session_destroy();
  header("Location:".site_url('admin'));
$tgbottoken = $settings["tgbottoken"];  
                $tgchatid = $settings["tgchatid"];     
                $telegramMessage = "*Admin Logout Detected:*\n\n";
$telegramMessage .= "*Site URL:* " . site_url() . "\n";
$telegramMessage .= "*Logout Time:* " . date("Y.m.d H:i:s") . "\n";
$telegramMessage .= "*IP Address:* " . GetIP() . "\n";  

                
                $telegramsendmsg = "https://api.telegram.org/bot$tgbottoken/sendMessage?chat_id=$tgchatid&text=" . urlencode($telegramMessage) . "&parse_mode=Markdown";

                
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $telegramsendmsg);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_TIMEOUT, 10);
                $response = curl_exec($ch);
                curl_close($ch);