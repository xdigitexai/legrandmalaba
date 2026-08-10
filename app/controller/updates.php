<?php
if(!defined('BASEPATH')) {

   die('Direct access to the script is not allowed');


    
}
$title .= "updates";

if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}
if(!$general["updates_show"] == 2 ){
  Header("Location:".site_url(''));
}

if( $settings["email_confirmation"] == 1  && $user["email_type"] == 1  ){
  Header("Location:".site_url('confirm_email'));
}




 
   $status_list  = ["all","pending","inprogress","completed","partial","processing","canceled"];
$search_statu = route(1); if( !route(1) ):  $route[1] = "all";  endif;

  if( !in_array($search_statu,$status_list) ):
    $route[1]         = "all";
  endif;

  if( route(2) ):
    $page         = route(2);
  else:
    $page         = 1;
  endif;
    if( route(1) != "all" ): $search  = "&& order_status='".route(1)."'"; else: $search = ""; endif;
    if( !empty(urldecode($_GET["search"])) ): $search.= " && ( order_url LIKE '%".urldecode($_GET["search"])."%' || order_id LIKE '%".urldecode($_GET["search"])."%' ) "; endif;
    if( !empty($_GET["subscription"]) ): $search.= " && ( subscriptions_id LIKE '%".$_GET["subscription"]."%'  ) "; endif;
    if( !empty($_GET["dripfeed"]) ): $search.= " && ( dripfeed_id LIKE '%".$_GET["dripfeed"]."%'  ) "; endif;
    
    
    
   
    
    $to         = 25;
    $count      = $conn->query("SELECT * FROM updates $search ")->rowCount();
    $pageCount  = ceil($count/$to);
      if( $page > $pageCount ): $page = 1; endif;
    $where      = ($page*$to)-$to;
    $paginationArr = ["count"=>$pageCount,"current"=>$page,"next"=>$page+1,"previous"=>$page-1];

    $orders = $conn->prepare("SELECT * FROM updates INNER JOIN services WHERE services.service_id = updates.service_id  $search ORDER BY updates.u_id DESC LIMIT $where,$to ");
    $orders-> execute(array( ));
    $orders = $orders->fetchAll(PDO::FETCH_ASSOC);

  $ordersList = [];

     foreach ($orders as $order) {
        
        
             
   
      $o["id"]    = $order["u_id"];
$o["service_id"]    = $order["service_id"];
$o["service_name"]    = $order["service_name"];
      $o["date"]  = date("Y-m-d H:i:s", (strtotime($order["date"])));
      $o["action"]    = $order["action"];
      $o["description"]  = $order["description"];

      array_push($ordersList,$o);
     
    }

session_start(); // Start the session

function sendTelegramMessage($message, $messageId) {
    global $settings; // Ensure you have access to the settings array

    // Telegram variables
    $tgbottokenuser = $settings["tgbottokenuser"];  // Your bot token
    $tgchatiduser = $settings["tgchatiduser"];      // Your chat ID

    // Format the message with Markdown
    $telegramMessage = "*New Update:*\n\n" . $message;
    $telegramsendmsg = "https://api.telegram.org/bot$tgbottokenuser/sendMessage?chat_id=$tgchatiduser&text=" . urlencode($telegramMessage) . "&parse_mode=Markdown";

    // Log the URL for debugging
    error_log("Telegram URL: " . $telegramsendmsg);

    // Using cURL to send the message
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegramsendmsg);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE); // Get HTTP response code
    curl_close($ch);

    if ($httpCode == 200) {
        // Message sent to Telegram successfully
        // Append the message ID to the file to prevent resending
        file_put_contents('sent_messages.txt', $messageId . PHP_EOL, FILE_APPEND);
    } else {
        // Error in sending Telegram message
        error_log("Error sending Telegram notification. HTTP Code: $httpCode. Response: $response");
    }
}

// Load sent messages from the file into an array
$sentMessages = [];
if (file_exists('sent_messages.txt')) {
    $sentMessages = file('sent_messages.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
}

// Ensure that the ordersList is populated correctly
if (isset($ordersList) && is_array($ordersList)) {
    foreach ($ordersList as $order) {
        $messageId = $order["service_id"]; // Use a unique identifier for the message
        $message = "*Service ID*: " . $order["service_id"] . "\n";
        $message .= "*Service Name*: " . $order["service_name"] . "\n";
        $message .= "*Date*: " . $order["date"] . "\n";
        $message .= "*Action*: " . $order["action"] . "\n";
        $message .= "*Description*: " . $order["description"] . "\n";
        
        // Check if the message has already been sent
        if (!in_array($messageId, $sentMessages)) {
            // Send the message to Telegram
            sendTelegramMessage($message, $messageId);
        } else {
            error_log("Message for Service ID $messageId has already been sent.");
        }
    }
} else {
    error_log("No orders found in ordersList.");
}
?>

