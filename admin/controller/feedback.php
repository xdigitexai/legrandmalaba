<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$dailyLimit = 25;

if(!isset($_SESSION['feedback_submissions'])) {
    $_SESSION['feedback_submissions'] = 0;
}

$remainingSubmissions = $dailyLimit - $_SESSION['feedback_submissions'];
$remainingSubmissions = max(0, $remainingSubmissions);

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if ($_SESSION['feedback_submissions'] >= $dailyLimit) {
        $errorMessage = "You have reached the daily limit for feedback submissions.";
    } else {
        $title = htmlspecialchars($_POST["title"]);
        $type = htmlspecialchars($_POST["type"]);
        $messageContent = htmlspecialchars($_POST["message"]);

        // Optimize: fetch only what you need
        $tme = $conn->prepare("SELECT username FROM admins LIMIT 1");
        $tme->execute();
        $the = $tme->fetch(PDO::FETCH_ASSOC);
        $username = $the ? $the["username"] : "Unknown";

        $panel = $_SERVER['HTTP_HOST'];

        // Get SMTP settings from the panel's configuration
        $stmt = $conn->prepare("SELECT * FROM settings WHERE id=1");
        $stmt->execute();
        $settings = $stmt->fetch(PDO::FETCH_ASSOC);

        // Create HTML email content
        $htmlMessage = "
<!DOCTYPE html>
<html>
<head>
  <meta charset='UTF-8'>
  <meta name='viewport' content='width=device-width, initial-scale=1.0'>
  <style>
    body { background:#f0f2f5; margin:0; padding:30px; font-family: 'Segoe UI', sans-serif; }
    .email-box { 
      max-width:650px; 
      margin:auto; 
      background:#fff; 
      border-radius:12px; 
      box-shadow:0 4px 12px rgba(0,0,0,0.12); 
      overflow:hidden; 
      border:1px solid #e5e5e5;
    }
    .header { 
      background: linear-gradient(135deg, #337ab7, #23527c); 
      color:#fff; 
      padding:25px; 
      text-align:center; 
    }
    .header h1 { margin:0; font-size:22px; }
    .content { padding:30px; color:#333; }
    .feedback-item { margin-bottom:20px; }
    .label { font-weight:bold; color:#337ab7; display:block; margin-bottom:6px; font-size:14px; }
    .value { font-size:15px; color:#333; }
    .message-box { background:#f9fbff; border:1px solid #d9e6f2; padding:15px; border-radius:6px; font-size:14px; color:#333; }
    .footer { 
      background:#fafafa; 
      padding:15px; 
      text-align:center; 
      font-size:12px; 
      color:#666; 
      border-top:1px solid #eee; 
    }
  </style>
</head>
<body>
  <div class='email-box'>
    <div class='header'>
      <h1>New Feedback Received</h1>
    </div>
    <div class='content'>
      <div class='feedback-item'>
        <span class='label'>Panel URL:</span>
        <span class='value'>{$panel}</span>
      </div>
      <div class='feedback-item'>
        <span class='label'>Admin Username:</span>
        <span class='value'>{$username}</span>
      </div>
      <div class='feedback-item'>
        <span class='label'>Title:</span>
        <span class='value'>{$title}</span>
      </div>
      <div class='feedback-item'>
        <span class='label'>Feedback Type:</span>
        <span class='value'>{$type}</span>
      </div>
      <div class='feedback-item'>
        <span class='label'>Message:</span>
        <div class='message-box'>{$messageContent}</div>
      </div>
    </div>
    <div class='footer'>
      This email was sent from <strong>{$panel}</strong>. Please do not reply directly.
    </div>
  </div>
</body>
</html>";

        try {
            $mail = new PHPMailer(true);
            
            // Server settings
$mail->isSMTP();
$mail->Host       = 'localhost';
$mail->SMTPAuth   = true;
$mail->Username   = 'noreply@panel.mkboost.site';
$mail->Password   = '@Markk083022';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
$mail->Port       = 465;
            $mail->Timeout    = 10; 

            $mail->SMTPOptions = [
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                ]
            ];
            
            // Recipients
            $mail->setFrom('noreply@panel.mkboost.site', 'Panel Support');
            $mail->addAddress('noreply@panel.mkboost.site');
            
            // Content
            $mail->isHTML(true);
            $mail->Subject = "Feedback from $panel";
            $mail->Body    = $htmlMessage;
            $mail->AltBody = strip_tags(str_replace(['<br>', '</div>'], "\n", $messageContent));
            
            // Release session lock before sending (faster page response)
            session_write_close();

            $mail->send();
            $errorMessage = "Feedback successfully sent to the owner.";
            $_SESSION['feedback_submissions']++;
            $remainingSubmissions = $dailyLimit - $_SESSION['feedback_submissions'];
            $remainingSubmissions = max(0, $remainingSubmissions);
        } catch (Exception $e) {
            $errorMessage = "Failed to send feedback. Error: " . $mail->ErrorInfo;
        }
    }
}

require admin_view('feedback');
?>