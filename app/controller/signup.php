<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if (!route(1)) {
  $route[1] = "signup";
}



$title .= $languageArray["signup.title"];


if ((route(1) == "login" || route(1) == "register") && $_SESSION["msmbilisim_userlogin"]) {
  Header("Location:" . site_url());
}
if (route(1) == "neworder" || route(1) == "orders" || route(1) == "tickets" || route(1) == "addfunds" || route(1) == "account" || route(1) == "dripfeeds" || route(1) == "reference" || route(1) == "subscriptions") {
  Header("Location:" . site_url());
  exit();
}
if ($_SESSION["msmbilisim_userlogin"] == 1  || $user["client_type"] == 1 || $settings["register_page"] == 1) {
  Header("Location:" . site_url());
} elseif ($route[1] == "signup" && $_POST) {

  $ip = $_SERVER['REMOTE_ADDR'] ?? '';

  $check = $conn->prepare("SELECT COUNT(*) FROM signup_attempts WHERE ip = ? AND attempt_time > (NOW() - INTERVAL 1 DAY) AND reason = 'form_submit'");
  $check->execute([$ip]);
  $attempts_today = (int) $check->fetchColumn();

  if ($attempts_today >= 1) {

    http_response_code(429);
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Signup Limit Reached</title>
  <meta name="description" content="You've reached the daily signup limit. Please try again in 24 hours." />
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
  :root {
    --bg: #f5f8ff;
    --card: #ffffff;
    --text: #1c2a4a;
    --muted: #5a6b8c;
    --brand: #007bff;
    --brand-600: #1a8cff;
    --brand-700: #0066cc;
    --stroke: #d8e1f0;
    --radius-xl: 20px;
    --shadow-1: 0 10px 30px rgba(0,0,0,.08), inset 0 1px 0 rgba(255,255,255,.02);
  }

  * { box-sizing: border-box; }
  html, body { height: 100%; width: 100%; margin: 0; padding: 0; }

  body {
    font-family: 'Inter', system-ui, sans-serif;
    color: var(--text);
    background: linear-gradient(180deg, #e6f0ff 0%, #f5f8ff 100%);

    /* Ensure vertical spacing ABOVE & BELOW even when centered */
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    min-height: 100dvh;                 /* viewport height with mobile-safe units */
    padding-block: clamp(64px, 12vh, 140px); /* top & bottom spacing */
    padding-inline: 20px;               /* left & right */
  }

  .card {
    display: grid;
    gap: 20px;
    max-width: 480px;
    width: 100%;
    background: var(--card);
    border: 1px solid var(--stroke);
    border-radius: var(--radius-xl);
    padding: 36px 28px;
    box-shadow: var(--shadow-1);
    text-align: center;
    margin: 0 auto;                      /* center horizontally; no extra vertical margin needed */
  }

  h1 {
    font-size: clamp(22px, 4vw, 28px);
    line-height: 1.2;
    margin: 0;
    letter-spacing: .1px;
    color: var(--brand);
  }

  p {
    color: var(--muted);
    line-height: 1.6;
    font-size: clamp(14px, 1.5vw, 15px);
  }

  .actions {
    display: flex;
    flex-direction: column;
    gap: 12px;
    margin-top: 10px;
  }

  .btn {
    appearance: none;
    border: none;
    cursor: pointer;
    font-weight: 600;
    padding: 12px 18px;
    border-radius: 12px;
    transition: transform .06s ease, box-shadow .2s ease, background .2s ease;
    font-size: clamp(14px, 1.5vw, 15px);
  }

  .btn-primary {
    background: linear-gradient(180deg, var(--brand-600), var(--brand-700));
    color: #ffffff;
    box-shadow: 0 10px 24px rgba(0,102,204,.22);
  }

  .btn-primary:hover {
    transform: translateY(-1px);
    box-shadow: 0 16px 28px rgba(0,102,204,.28);
  }

  .btn-ghost {
    background: transparent;
    color: var(--brand-700);
    border: 1px solid var(--brand-700);
  }

  .btn-ghost:hover {
    background: rgba(0,102,204,0.05);
  }

  /* Optional tweaks per breakpoint */
  @media (max-width: 768px) {
    .card { padding: 24px 20px; gap: 16px; }
    h1 { font-size: 22px; }
  }

  @media (min-width: 1024px) {
    .card { max-width: 520px; padding: 40px; }
  }
</style>
</head>
<body>
  <main>
    <section class="card" aria-live="polite" aria-atomic="true">
      <h1>Signup Limit Reached</h1>
      <p>You’ve already signed up today.<br>Please try again after <strong>24 hours</strong>.</p>
      <p>We value our users and aim to maintain a fair and secure registration process. This temporary restriction helps prevent automated signups and ensures the best experience for genuine users. If you believe this message is a mistake, please reach out to our support team for further assistance.</p>
      <p>In the meantime, you can explore our platform, learn more about our services, or prepare the information you’ll need when you try again. Thank you for your patience and understanding.</p>
      <div class="actions">
        <button class="btn btn-primary" onclick="location.href='/'">Go Back Home</button>
        <button class="btn btn-ghost" onclick="location.href='/help'">Visit Help Center</button>
        <button class="btn btn-ghost" onclick="location.href='mailto:support@example.com'">Contact Support</button>
      </div>
    </section>
  </main>
</body>
</html>
    <?php
    exit;
}

  try {
      $stmt = $conn->prepare("INSERT INTO signup_attempts (ip, reason) VALUES (?, ?)");
      $stmt->execute([$ip, 'form_submit']);
  } catch (Exception $e) {

  }

  foreach ($_POST as $key => $value) {
      $_SESSION["data"][$key] = $value;
  }

  $name = $_POST["name"];
  $name = strip_tags($name);
  $name = htmlspecialchars(strip_tags($name), ENT_QUOTES, 'UTF-8');
  $email = $_POST["email"];
  $email = strip_tags($email);
  $email = filter_var($email, FILTER_SANITIZE_EMAIL);
  $username       = $_POST["username"];
  $username = strip_tags($username);
  $username = htmlspecialchars(strip_tags($username), ENT_QUOTES, 'UTF-8');
  $phone          = $_POST["telephone"];
  $phone = strip_tags($phone);
  $phone = filter_var($phone, FILTER_VALIDATE_INT);
  $pass           = $_POST["password"];






  $pass_again     = $_POST["password_again"];
  $terms          = $_POST["terms"];
  $captcha        = $_POST['g-recaptcha-response'];
  $googlesecret   = $settings["recaptcha_secret"];
  $captcha_control = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$googlesecret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
  $captcha_control = json_decode($captcha_control);

  $ref_code =  substr(bin2hex(random_bytes(18)), 5, 6);

  if ($captcha && $settings["recaptcha"] == 2 && $captcha_control->success == false) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.recaptcha"];
  } elseif (!username_check($username)) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.usename"];
  } elseif (userdata_check("username", $username)) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.username.used"];
}elseif( $settings["name_feilds"] == 1 && empty($name) ){
    $error      = 1;
    $errorText  = $languageArray["error.signup.name"];
  } elseif (!email_check($email)) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.email"];
  } elseif (userdata_check("email", $email)) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.email.used"];

}elseif( $settings["skype_feilds"] == 1 && empty($phone)) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.telephone"];
}elseif( $settings["skype_feilds"] == 1 && userdata_check("telephone", $phone)) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.telephone.used"];
  } elseif (strlen($pass) < 8) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.password"];
  } elseif ($pass != $pass_again) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.password.notmatch"];
  } elseif (!$terms) {
    $error      = 1;
    $errorText  = $languageArray["error.signup.terms"];
  } else {
    $apikey = CreateApiKey($_POST);
    $conn->beginTransaction();
    $insert = $conn->prepare("INSERT INTO clients SET name=:name, username=:username, email=:email, password=:pass, lang=:lang, telephone=:phone, register_date=:date, apikey=:key , ref_code=:ref_code, email_type=:type, balance=:spent, spent=:spent,currency_type=:currency_type");
    $insert = $insert->execute(array("lang" => $selectedLang, "name" => $name, "username" => $username, "email" => $email, "pass" => md5($pass), "phone" => $phone, "date" => date("Y.m.d H:i:s"), 'key' => $apikey, "ref_code" => $ref_code, "type"=> 2, "spent"=> "0.0000000","currency_type"=>get_default_currency()));
    if ($insert) : $client_id = $conn->lastInsertId();



    endif;



    $insert2 = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
    $insert2 = $insert2->execute(array("c_id" => $client_id, "action" => "
    User registered.", "ip" => GetIP(), "date" => date("Y-m-d H:i:s")));
    if ($insert && $insert2) :
      $conn->commit();
      unset($_SESSION["data"]);
      $success    = 1;
      $successText = $languageArray["error.signup.success"];

      if ($_COOKIE['ref']) {
        $ref_by = $_COOKIE['ref'];
        $insert12 = $conn->prepare("UPDATE clients SET ref_by=:ref_by WHERE client_id=:c_id");
        $insert12->execute(array("c_id" => $client_id, "ref_by" => $ref_by));






        if (countRow(['table' => 'referral', 'where' => ['referral_code' => $ref_by]])) {

          $select = $conn->prepare("SELECT * FROM referral WHERE referral_code=:referral_code");
          $select->execute(array("referral_code" => $ref_by));
          $select  = $select->fetch(PDO::FETCH_ASSOC);



          //update signup value
          $update = $conn->prepare("UPDATE referral SET referral_sign_up=:referral_sign_up WHERE referral_code=:referral_code");
          $update = $update->execute(array("referral_code" => $ref_by, "referral_sign_up" => $select["referral_sign_up"] + 1));
        } else {
          //insert

          $clients  = $conn->prepare("SELECT * FROM clients WHERE ref_code=:ref_code ");
          $clients->execute(array("ref_code" => $ref_by));
          $clients  = $clients->fetch(PDO::FETCH_ASSOC);


          $insert = $conn->prepare("INSERT INTO referral SET referral_code=:referral_code");
          $insert->execute(array("referral_code" => $ref_by));

          $update = $conn->prepare("UPDATE referral SET referral_client_id=:referral_client_id , referral_sign_up=:referral_sign_up WHERE referral_code=:referral_code");
          $update = $update->execute(array("referral_client_id" => $clients["client_id"],  "referral_code" => $ref_by, "referral_sign_up" => 1));
        }
      }


if ($settings["alert_welcomemail"] == 2) {
$site_name = $settings["site_name"];
$htmlContent = "Hello, 
Thank you for signing up on $site_name
Your Username is : $username
Use it to sign in to " . site_url() . "  "  ;
        $to = "$email"; 
$from = $settings["smtp_user"]; 
$fromName = $settings["site_seo"]; 
$subject = "Welcome"; 
$headers = "MIME-Version: 1.0" . "\r\n"; 
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
$headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
$headers .= 'Cc: '.$from . "\r\n"; 
$headers .= 'Bcc: '.$from . "\r\n"; 
 if(mail($to, $subject, $htmlContent, $headers)){ 
}else{ 
     } 
        
      }

$insert = $conn->prepare("INSERT INTO referral SET referral_code=:referral_code , referral_client_id=:referral_client_id");
         $insert->execute(array("referral_code" => $ref_code , 
      "referral_client_id" => $client_id));

if ($settings["freebalance"] == 2) {
$update111 = $conn->prepare("UPDATE clients SET balance=:balance WHERE client_id=:id ");
        $update111 = $update111->execute(array(
            "id" => $client_id,
            "balance" => $settings["freeamount"] + $user["balance"]
        ));
$freebalance = ($settings["freeamount"] + $user["balance"]) 
        ;
$insert = $conn->prepare("INSERT INTO payments SET client_id=:client_id , client_balance=:client_balance , 
            payment_amount=:payment_amount , payment_method=:payment_method ,
            payment_status=:payment_status , payment_delivery=:payment_delivery , payment_note=:payment_note,
            payment_create_date=:payment_create_date ,
             payment_update_date=:payment_update_date, 	payment_ip=:payment_ip , 
             payment_extra=:payment_extra ");
        $insert = $insert->execute(array(
            "client_id" => $client_id,
            "client_balance" => 0.00,
            "payment_amount" =>  $freebalance, "payment_method" => 30,
            "payment_status" => 3, "payment_delivery" => 2, "payment_note" => "Free Balance added for New user of : $freebalance  ",
             "payment_create_date" => date("Y-m-d H:i:s"),
            "payment_update_date" => date("Y-m-d H:i:s"), "payment_ip" => GetIP(),
            "payment_extra" => "Free balance Added of  : $freebalance "
        ));

$insert2 = $conn->prepare("INSERT INTO client_report SET client_id=:c_id, action=:action, report_ip=:ip, report_date=:date ");
            
    $insert2 = $insert2->execute(array("c_id" => $client_id, "action" => 
    "Free Balance Added of  : $freebalance " , "ip" => GetIP(), "date" => date("Y-m-d H:i:s")));
} 
if ($settings["email_confirmation"] == 1) {
$update122 = $conn->prepare("UPDATE clients SET email_type=:type WHERE client_id=:id ");
        $update122 = $update122->execute(array(
            "id" => $client_id,
            "type" => 1 ));
        
$htmlContent = "Please confirm email address for your account. Click the link below to confirm your email: ". site_url()."confirm_email/activate/$apikey" ;

        $to = "$email"; 
$from = $settings["smtp_user"]; 
$fromName = $settings["smtp_user"]; 
$subject = "Email Confirmation"; 
$headers = "MIME-Version: 1.0" . "\r\n"; 
$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n"; 
$headers .= 'From: '.$fromName.'<'.$from.'>' . "\r\n"; 
$headers .= 'Cc: '.$from . "\r\n"; 
$headers .= 'Bcc: '.$from . "\r\n"; 
 if(mail($to, $subject, $htmlContent, $headers)){ 
}else{ 
     } 
}

      //Auto Login
      $row    = $conn->prepare("SELECT * FROM clients WHERE username=:username && password=:password ");
      $row->execute(array("username" => $username, "password" => md5($pass) ));
      $row    = $row->fetch(PDO::FETCH_ASSOC);
      $access = json_decode($row["access"], true);


      $_SESSION["otp_login"] = true;
      if ($settings["otp_login"] == 2) {

        loginOtp($row);
      } elseif ($settings["otp_login"] == 1) {

        $admin_access =  $access["admin_access"];

        if ($admin_access == 1) {


          loginOtp($row);
        } else {
          $_SESSION["otp_login"] = true;
        }
      } else {
        $_SESSION["msmbilisim_userlogin"]      = 1;
      }


      $_SESSION["msmbilisim_userid"]         = $row["client_id"];
      $_SESSION["msmbilisim_userpass"]       = md5($pass);
      $_SESSION["recaptcha"]                = false;
      if ($access["admin_access"]) :
        $_SESSION["msmbilisim_adminlogin"] = 1;
$currency_hash = get_currency_hash_by_code(get_default_currency());
$_SESSION["currency_hash"] = $currency_hash;
      endif;
      if ($remember) {
        if ($access["admin_access"]) :
          setcookie("a_login", 'ok', strtotime('+28 days'), '/', null, null, true);
        endif;
        setcookie("u_id", $row["client_id"], strtotime('+28 days'), '/', null, null, true);
        setcookie("u_password", $row["password"], strtotime('+28 days'), '/', null, null, true);    
        setcookie("u_login", 'ok', strtotime('+28 days'), '/', null, null, true);
        setcookie("currency_hash",$currency_hash,strtotime('+28 days'),'/',null,null,true);
      } else {
        setcookie("u_id", $row["client_id"], strtotime('+7 days'), '/', null, null, true);
        setcookie("u_password", $row["password"], strtotime('+7 days'), '/', null, null, true);    
        setcookie("u_login", 'ok', strtotime('+7 days'), '/', null, null, true);
        setcookie("currency_hash",$currency_hash,strtotime('+7 days'),'/',null,null,true);
      }






      header('Location:' . site_url(''));

    //Auto Login









    else :
      $conn->rollBack();
      $error      = 1;
      $errorText  = $languageArray["error.signup.fail"];
    endif;
  }
}

