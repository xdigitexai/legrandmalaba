<?php
if (!defined('BASEPATH')) {
  die('Direct access to the script is not allowed');
}
if ($admin["access"]["admin_access"] != 1) {
  header("Location:" . site_url("admin"));
  exit();
}
if (!route(2)):
  $route[2] = "general";
endif;

if ($_SESSION["client"]["data"]):
  $data = $_SESSION["client"]["data"];
  foreach ($data as $key => $value) {
    $$key = $value;
  }
  unset($_SESSION["client"]);
endif;


$sellers_count = $conn->prepare("SELECT * FROM service_api");
$sellers_count->execute();
$sellers_count = $sellers_count->rowCount();
$subject = $conn->prepare("SELECT * FROM ticket_subjects");
$subject->execute();
$subject = $subject->rowCount();
$pay_methods_countt = $conn->prepare("SELECT * FROM paymentmethods WHERE methodStatus = :st");
$pay_methods_countt->execute(array("st" => 1));
$pay_methods_countt = $pay_methods_countt->rowCount();

$orders_count = $settings["panel_orders"];

$currencies_count = $conn->prepare("SELECT * FROM currencies");
$currencies_count->execute();
$currencies_count = $currencies_count->rowCount();

$menuList = [
  "General Settings" => "general",
  "API Provider" => "providers",
  "Payment Method" => "paymentMethods",
  "Functions" => "modules",
  "Support Configuration" => "subject",
  "Currency Manager" => "currency-manager",
  "Notification Settings" => "alert",
  "Fake Orders Configuration" => "site_count",
  "Advanced Security" => "ip_allowed",
  "Cpanel Cron Jobs" => "cron_jobs"
  
];

if (!array_search(route(2), $menuList)):
  header("Location:" . site_url("admin/settings"));
elseif (route(2) == "general"):
  $access = $admin["access"]["general_settings"];
  if ($access):
    if ($_POST):
      foreach ($_POST as $key => $value) {
        $$key = htmlspecialchars($value);
      }

      $logo_upload_directory = $_SERVER["DOCUMENT_ROOT"] . '/img/panel';

      if (!is_dir($logo_upload_directory)) {
        mkdir($_SERVER["DOCUMENT_ROOT"] . "/img/panel", 0755, true);
      }


      if ($_FILES["logo"] && ($_FILES["logo"]["type"] == "image/jpeg" || $_FILES["logo"]["type"] == "image/jpg" || $_FILES["logo"]["type"] == "image/png" || $_FILES["logo"]["type"] == "image/gif")):
        $logo_name = $_FILES["logo"]["name"];
        $uzanti = substr($logo_name, -4, 4);
        $logo_newname = "img/panel/" . md5(rand(10, 999)) . ".png";
        $upload_logo = move_uploaded_file($_FILES["logo"]["tmp_name"], $logo_newname);
      elseif ($settings["site_logo"] != ""):
        $logo_newname = $settings["site_logo"];
      else:
        $logo_newname = "";
      endif;
      if ($_FILES["favicon"] && ($_FILES["favicon"]["type"] == "image/jpeg" || $_FILES["favicon"]["type"] == "image/jpg" || $_FILES["favicon"]["type"] == "image/png" || $_FILES["favicon"]["type"] == "image/gif")):
        $favicon_name = $_FILES["favicon"]["name"];
        $uzanti = substr($logo_name, -4, 4);
        $fv_newname = "img/panel/" . sha1(rand(10, 999)) . ".png";
        $upload_logo = move_uploaded_file($_FILES["favicon"]["tmp_name"], $fv_newname);
      elseif ($settings["favicon"] != ""):
        $fv_newname = $settings["favicon"];
      else:
        $fv_newname = "";
      endif;
      if (empty($name)):
        $errorText = "Panel Name cannot be blank";
        $error = 1;
      else:
        $update = $conn->prepare("UPDATE settings SET 
			site_maintenance=:site_maintenance,
			resetpass_page=:resetpass_page,
            skype_feilds=:skype_feilds,
            video_feilds=:video_feilds,
            service_dashboard=:service_dashboard,
            analytics_dashboard=:analytics_dashboard,
            name_fileds=:name_fileds,
			resetpass_sms=:resetpass_sms,
			resetpass_email=:resetpass_email,
			site_name=:name,
			site_userr=:userr,
			site_userrr=:userrr,
			site_video=:video,
			site_marqueen=:marqueen,
			site_marqueens=:marqueens,
			site_logo=:logo,
            email_confirmation=:email_confirmation,
			resend_max=:resend_max, 
			favicon=:fv,
			ticket_system=:ticket_system,
			tickets_per_user=:tickets_per_user, 
			register_page=:registration_page, 
			service_list=:service_list, 
			custom_header=:custom_header, 
			custom_footer=:custom_footer,
			bronz_statu=:bronz_statu,
			silver_statu=:silver_statu,
			gold_statu=:gold_statu,
			bayi_statu=:bayi_statu,
			fundstransfer_fees=:fundstransfer_fees,services_average_time=:avg_time WHERE id=:id ");

        $update->execute(
          array(
            "id" => 1,
            "site_maintenance" => $site_maintenance,
            "resetpass_page" => $resetpass,
            "resetpass_sms" => $resetsms,
            "resetpass_email" => $resetmail,
            "name" => $name,
            "userr" => $userr,
            "marqueen" =>$marqueen,
            "marqueens" =>$marqueens,
            "userrr" => $userrr,
            "video" => $video,
            "logo" => $logo_newname,
            "fv" => $fv_newname,
            "resend_max" => $resend_max,
            "email_confirmation" => $email_confirmation,
            "name_fileds" => $name_fileds,
            "skype_feilds" => $skype_feilds,
            "video_feilds" =>$video_feilds,
            "service_dashboard" =>$service_dashboard,
            "analytics_dashboard" =>$analytics_dashboard,
            "ticket_system" => $ticket_system,
            "tickets_per_user" => $tickets_per_user,
            "registration_page" => $registration_page,
            "service_list" => $service_list,
            "custom_footer" => $custom_footer,
            "custom_header" => $custom_header,
            "bronz_statu" => $bronz_statu,
            "silver_statu" => $silver_statu,
            "gold_statu" => $gold_statu,
            "bayi_statu" => $bayi_statu,
            "fundstransfer_fees" => $fundstransfer_fees,
            "avg_time" => $services_average_time
          )
        );
        $update = $conn->prepare("UPDATE General_options SET currency_format=:format WHERE id=:id ");
        $update->execute(array("format" => $currency_format, "id" => 1));
        $referrer = site_url("admin/settings/general");
        $icon = "success";
        $error = 1;
        $errorText = "Success";

        header("Location:" . site_url("admin/settings/general"));
        echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);

        if ($update):
          header("Location:" . site_url("admin/settings/general"));
          $_SESSION["client"]["data"]["success"] = 1;
          $_SESSION["client"]["data"]["successText"] = "Successful";
        else:
          $errorText = "Failed";
          $error = 1;

        endif;


      endif;
    endif;
    if (route(3) == "delete-logo"):
      $update = $conn->prepare("UPDATE settings SET site_logo=:type WHERE id=:id ");
      $update->execute(array("type" => "", "id" => 1));
      if ($update):
        unlink($settings["site_logo"]);
      endif;
      header("Location:" . site_url("admin/settings/general"));
    elseif (route(3) == "delete-favicon"):
      $update = $conn->prepare("UPDATE settings SET favicon=:type WHERE id=:id ");
      $update->execute(array("type" => "", "id" => 1));
      if ($update):
        unlink($settings["site_favicon"]);
      endif;
      header("Location:" . site_url("admin/settings/general"));
    endif;
  endif;
elseif (route(2) == "currency-manager"):


  $access = $admin["access"]["currency-manager"];
  if ($access):
    if (route(3)):
      $chosen_curr = route(3);

      $update = $conn->prepare("UPDATE settings SET site_base_currency=:curr WHERE id=:id");
      $update->execute(
        array(
          "curr" => $chosen_curr,
          "id" => 1
        )
      );

      $check_if_table_exists = $conn->prepare("DESCRIBE currencies");
      if ($check_if_table_exists->execute()) {
        $delete_table = $conn->prepare("DROP TABLE currencies");
        $delete_table->execute();
      }
      $create_table = $conn->prepare("CREATE TABLE currencies (
  id int(100) NOT NULL,
  currency_name varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  currency_code varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  currency_symbol varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  symbol_position varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT 'left',
  currency_rate double NOT NULL,
  currency_inverse_rate double NOT NULL,
  is_enable tinyint(1) NOT NULL DEFAULT 0,
  currency_hash text COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
      $create_table->execute();

      $add_primary_key = $conn->prepare("ALTER TABLE currencies ADD PRIMARY KEY (id)");
      $add_primary_key->execute();


      if (!file_exists($_SERVER["DOCUMENT_ROOT"] . "/currencies.json")) {

        $json_content = HTTP_REQUEST("https://dukesmm.com/currencies.json", "", array(""), "GET", 0);

        file_put_contents($_SERVER["DOCUMENT_ROOT"] . "/currencies.json", $json_content);

        $curr_code_array = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/currencies.json"), true);
      } else {
        $curr_code_array = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/currencies.json"), true);
      }



      $insert = $conn->prepare("INSERT INTO currencies (id,currency_name,currency_code,currency_symbol,currency_rate,currency_inverse_rate,is_enable,currency_hash) VALUES
(1, '" . $curr_code_array[$chosen_curr]["name"] . "', '" . $chosen_curr . "', '" . $curr_code_array[$chosen_curr]["symbol"] . "', 1, 1, 1, '" . sha1(md5(RAND_STRING(10))) . "')");

      $insert->execute();

      $add_autoincrement = $conn->prepare("ALTER TABLE currencies
  MODIFY id int(100) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2");
      $add_autoincrement->execute();

      $url = "http://www.floatrates.com/daily/" . strtolower($chosen_curr) . ".json";

      $a = HTTP_REQUEST($url, "", array(""), "GET", 0);

      $b = json_decode($a, true);


      $db_codes = array("USD", "EUR", "INR", "TRY", "RUB", "BRL", "KRW", "SAR", "CNY", "VND", "KWD", "EGP", "PKR", "NGN");

      foreach ($db_codes as $code) {

        if ($chosen_curr !== $code) {

          $cur_name = $b[strtolower($code)]["name"];

          $cur_symbol = $curr_code_array[$code]["symbol"];
          $cur_code = $code;
          $cur_rate = $b[strtolower($code)]["rate"];
          $cur_inv_rate = $b[strtolower($code)]["inverseRate"];
          $cur_hash = sha1(md5(RAND_STRING(10)));

          $insert = $conn->prepare("INSERT INTO currencies SET currency_name=:name,currency_code=:code,currency_symbol=:symbol,currency_rate=:rate,currency_inverse_rate=:inv_rate,is_enable=:enable,currency_hash=:hash");
          $insert->execute(
            array(
              "name" => $cur_name,
              "code" => $cur_code,
              "symbol" => $cur_symbol,
              "rate" => $cur_rate,
              "inv_rate" => $cur_inv_rate,
              "enable" => 1,
              "hash" => $cur_hash
            )
          );
        }
      }


      header("Location: " . site_url("admin/settings/currency-manager"));
    endif; // (route3) == a currency chosen
    if ($_POST): // some data is been posted
      $action = $_POST["action"];
      if ($action == "currency-values-save-changes"):
        $cur_id = $_POST["id"];
        $cur_symbol = $_POST["symbol"];
        $cur_rate = $_POST["cur_rate"];
        $cur_inv_rate = $_POST["inv_rate"];
        $sym_pos = $_POST["sym_pos"];
        $enable = $_POST["enable"];


        $update = $conn->prepare("UPDATE currencies SET currency_symbol=:symbol,currency_rate=:rate,currency_inverse_rate=:inv_rate,is_enable=:enable,symbol_position=:sym_pos WHERE id=:id");
        $update->execute(
          array(
            "id" => $cur_id,
            "symbol" => $cur_symbol,
            "rate" => $cur_rate,
            "inv_rate" => $cur_inv_rate,
            "enable" => $enable,
            "sym_pos" => $sym_pos
          )
        );
      endif; // currency-values-save-changes
      if ($action == "activate_deactivate_curr_conv"):
        if ($settings["site_currency_converter"] == "1") {
          $set = "0";
        } else {
          $set = "1";
        }

        $update = $conn->prepare("UPDATE settings SET site_currency_converter=:set WHERE id=:id");
        $update->execute(
          array(
            "set" => $set,
            "id" => 1
          )
        );
      endif; //activate_deactivate_curr_conv
      if ($action == "rate_update_switch"):
        if ($settings["site_update_rates_automatically"] == "1") {
          $set = "0";
        } else {
          $set = "1";
        }

        $update = $conn->prepare("UPDATE settings SET site_update_rates_automatically=:set WHERE id=:id");
        $update->execute(
          array(
            "set" => $set,
            "id" => 1
          )
        );
      endif; //rate_update_switch
      if ($action == "update_rates"):
        $currency_codes = $conn->prepare("SELECT currency_code FROM currencies WHERE currency_code!=:code");
        $currency_codes->execute(["code" => $settings["site_base_currency"]]);
        $currency_codes = $currency_codes->fetchAll(PDO::FETCH_ASSOC);

        $url = "http://www.floatrates.com/daily/" . strtolower($settings["site_base_currency"]) . ".json";
        $a = HTTP_REQUEST($url, "", array(""), "GET", 0);

        $floatrates_array = json_decode($a, true);
        for ($i = 0; $i < count($currency_codes); $i++) {

          $currency_code = $currency_codes[$i]["currency_code"];
          $lower_case_currency_code = strtolower($currency_code);

          $currency_rate = $floatrates_array[$lower_case_currency_code]["rate"];
          $inverse_rate = $floatrates_array[$lower_case_currency_code]["inverseRate"];


          $update_db = $conn->prepare("UPDATE currencies SET currency_rate=:rate,currency_inverse_rate=:inverse_rate WHERE currency_code=:code");

          $update_db->execute(
            array(

              "rate" => $currency_rate,
              "inverse_rate" => $inverse_rate,
              "code" => $currency_code
            )
          );
        }
        $settings_update = $conn->prepare("UPDATE settings SET last_updated_currency_rates=:time WHERE id=:id");

        $settings_update->execute(
          array(

            "time" => date('Y-m-d H:i:s'),
            "id" => 1
          )
        );
      endif; //update rates
      if ($action == "site-add-currency"):

        $url = "http://www.floatrates.com/daily/" . strtolower($settings["site_base_currency"]) . ".json";

        $a = HTTP_REQUEST($url, "", array(""), "GET", 0);

        $b = json_decode($a, true);

        $db_codes = $_POST["selected-currencies"];
        $curr_code_array = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/currencies.json"), true);
        foreach ($db_codes as $code) {

          if ($settings["site_base_currency"] !== $code) {

            $cur_name = $b[strtolower($code)]["name"];

            $cur_symbol = $curr_code_array[$code]["symbol"];
            $cur_code = $code;
            $cur_rate = $b[strtolower($code)]["rate"];
            $cur_inv_rate = $b[strtolower($code)]["inverseRate"];
            $cur_hash = sha1(md5(RAND_STRING(10)));

            $insert = $conn->prepare("INSERT INTO currencies SET currency_name=:name,currency_code=:code,currency_symbol=:symbol,currency_rate=:rate,currency_inverse_rate=:inv_rate,is_enable=:enable,currency_hash=:hash");
            $insert->execute(
              array(
                "name" => $cur_name,
                "code" => $cur_code,
                "symbol" => $cur_symbol,
                "rate" => $cur_rate,
                "inv_rate" => $cur_inv_rate,
                "enable" => 1,
                "hash" => $cur_hash
              )
            );
          }
        }
        header("Location: " . site_url("admin/settings/currency-manager"));
      endif; // add currencies
      if ($action == "delete-currency"):
        $currency_id = $_POST["currency_id"];
        $delete = $conn->prepare("DELETE FROM currencies WHERE id=:id");
        $delete->execute(["id" => $currency_id]);
      endif;
      exit();
    endif; // POST
  endif; //  admin access
elseif (route(2) == "modules"):
  $access = $admin["access"]["modules"];
  $access = 1;
  if ($access):
    if (isset($_GET["action"])) {
      if ($_GET["action"] == "buy_addon" && $_GET["addon"] == "google_login") {

        $txn_id = $_SESSION["txn_id"];
        if (!$_SESSION["txn_id"]) {
          $txn_id = RAND_STRING(10) . time();
          $_SESSION["txn_id"] = $txn_id;
        }

        $upi = $_SESSION["upi"];

        if (!$_SESSION["upi"]) {
          $upi = "upi://pay?pa=paytmqr2810050501011gb0p47l4lnl@paytm&pn=Paytm%20Merchant&am=200.00&mam=0&tn=Google%20Login%20Addon&tr=$txn_id";
          $_SESSION["upi"] = $upi;
        }

        $google_chart_api_url = "https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=" . urlencode($upi) . "&choe=UTF-8";
        echo '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <base href="' . site_url() . '">
    <title>SMM CLOUD | Buy Addon </title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css"/>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
</head>
<body>
<div class="container mt-2 col-md-8">
<div class="card">
<div class="card-header">
<h4>Google Login Addon</h4></div>
<div class="card-body">
<div align="center">
<h6>Scan the Below QR Code Using a UPI App.</h6>
<img src="' . $google_chart_api_url . '" />
</div>
</div>
</div>
</div>
<script>
$(document).ready(function(){
var txn_id = "' . $txn_id . '";
var addon = "google_login";
  setInterval(function()
{ 
 $.ajax({
type:"GET",
url:"admin/settings/modules?action=verify_transaction&transaction_id="+txn_id+"&addon="+addon,
success:function(data)
{
 json = JSON.parse(data);
 if(json.status == "success"){
  window.location.href = "admin/settings/modules";
 }
 }
});
}, 2000);
});
</script>
</body>
</html>';
        exit();
      }

      if ($_GET["action"] == "verify_transaction" && $_GET["addon"] == "google_login") {

        $txn_id = $_GET["transaction_id"];
        $JsonData = array(
          "MID" => "UnTxqr907474jyjy13789906",
          "ORDERID" => $txn_id
        );
        $url = "https://securegw.paytm.in/order/status?JsonData=" . json_encode($JsonData);

        $resp = HTTP_REQUEST($url, "", array(""), "GET", 0);
        $resp = json_decode($resp, true);

        if ($resp["STATUS"] == "TXN_SUCCESS") {
          unset($_SESSION["upi"]);
          unset($_SESSION["txn_id"]);
          $msg = $_SERVER["HTTP_HOST"] . " purchased the google login addon.";
          $send = mail("cheworozviero12@gmail.com", "Addon Purchase : " . $_SERVER["HTTP_HOST"] . "", $msg);
          $google_login = json_decode($settings["google_login"], true);
          $json["purchased"] = "1";
          $json["status"] = "1";
          $update = $conn->prepare("UPDATE settings SET google_login=:login");
          $update->execute([
            "login" => json_encode($json)
          ]);
          $output["status"] = "success";
          $output["message"] = "Addon purchased successfully.";
          echo json_encode($output);
          exit();
        } else {
          $output["status"] = "fail";
          $output["message"] = "";
          echo json_encode($output);
          exit();
        }
      }

      if ($_GET["action"] == "toggle_addon" && $_GET["addon"] == "google_login") {
        $google_login = json_decode($settings["google_login"], true);
        $json["purchased"] = $google_login["purchased"];
        if ($google_login["status"] == "1") {
          $json["status"] = "0";
        } else {
          $json["status"] = "1";
        }
        $update = $conn->prepare("UPDATE settings SET google_login=:login");
        $update->execute([
          "login" => json_encode($json)
        ]);
      }
    }
    if ($_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      $conn->beginTransaction();
      $update = $conn->prepare("UPDATE settings SET referral_commision=:referral_commision,
childpanel_selling=:selling,
childpanel_price=:price,
promotion=:promotion,
freebalance=:freebalance,
freeamount=:freeamount,
googleclientid=:googleclientid,
openai_api_key=:openai_api_key,
googleclientsecret=:googleclientsecret,
fb_page_id=:fb_page_id,
fb_access_token=:fb_access_token,
tgbottoken=:tgbottoken,
tgbottokenuser=:tgbottokenuser,
tgchatiduser=:tgchatiduser,
tgchatid=:tgchatid,
referral_payout=:referral_payout,
referral_status=:referral_status WHERE id=:id ");
      $update = $update->execute(
        array(
          "id" => 1,
          "referral_commision" => $commision,
          "referral_payout" => $minimum,
          "promotion" => $promotion,
          "selling" => $selling,
          "freeamount" => $freeamount,
          "freebalance" => $freebalance,
          "price" => $price,
          "googleclientid" => $googleclientid,
          "openai_api_key" => $openai_api_key,
          "googleclientsecret" => $googleclientsecret,
          "fb_page_id" => $fb_page_id,
          "fb_access_token" => $fb_access_token,

          "tgbottoken" => $tgbottoken,
          "tgbottokenuser" => $tgbottokenuser,
          "tgchatiduser" => $tgchatiduser,
          "tgchatid" => $tgchatid,
          "referral_status" => $affiliates_status
        )
      );

      $update = $conn->prepare("UPDATE General_options SET updates_show=:updates_show,coupon_status=:coupon_status,massorder=:massorder WHERE id=:id ");
      $update = $update->execute(array("id" => 1, "updates_show" => $updates_show, "coupon_status" => $coupon_status, "massorder" => $massorder));

      //update menu updates
      $update = $conn->prepare("UPDATE menus SET type=:updates_show WHERE slug=:id ");
      $update = $update->execute(array("id" => "/updates", "updates_show" => $updates_show));


      //update menu Affiliates
      $update = $conn->prepare("UPDATE menus SET type=:updates_show WHERE slug=:id ");
      $update = $update->execute(array("id" => "/refer", "updates_show" => $affiliates_status));


      //update menu Child
      $update = $conn->prepare("UPDATE menus SET type=:updates_show WHERE slug=:id ");
      $update = $update->execute(array("id" => "/child-panels", "updates_show" => $selling));

      //update menu Promotion
      $update = $conn->prepare("UPDATE menus SET type=:updates_show WHERE slug=:id ");
      $update = $update->execute(array("id" => "/earn", "updates_show" => $promotion));

      //update menu Mass order
      $update = $conn->prepare("UPDATE menus SET type=:updates_show WHERE slug=:id ");
      $update = $update->execute(array("id" => "/massorder", "updates_show" => $massorder));


      if ($update):
        $conn->commit();
        header("Location:" . site_url("admin/settings/modules"));
        $_SESSION["client"]["data"]["success"] = 1;
        $_SESSION["client"]["data"]["successText"] = "Success";
      else:
        $conn->rollBack();
        $error = 1;
        $errorText = "Failed";
      endif;
    endif;
  endif;
elseif (route(2) == "paymentMethods"):
  require("settings/paymentMethods.php");
  require admin_view("new-header");
  require admin_view("settings/paymentMethods");
  require admin_view("new-footer");
  exit;
elseif (route(2) == "site_count"):

  $access = $admin["access"]["site_count"];
  if ($access):

    if (count($conn->query("SHOW COLUMNS FROM settings LIKE 'panel_orders_pattern'")->fetchAll())) {
    } else {
      $create_column = $conn->prepare("ALTER TABLE settings ADD panel_orders_pattern varchar(255) NOT NULL DEFAULT '{\"panel_orders_prefix\":\"\",\"panel_orders_suffix\":\"\"}' AFTER panel_orders");
      $create_column->execute();
    }

    if (route(3) == "service_enable_disable"):
      if ($settings["fake_order_service_enabled"] == 0):
        $update = $conn->prepare("UPDATE settings SET fake_order_service_enabled=:a WHERE id=:id");
        $update->execute(
          array(
            "a" => 1,
            "id" => 1
          )
        );
      else:
        $update = $conn->prepare("UPDATE settings SET fake_order_service_enabled=:a WHERE id=:id");
        $update->execute(
          array(
            "a" => 0,
            "id" => 1
          )
        );
      endif;
      echo "<script> window.location.href = 'admin/settings/site_count';</script>";
    endif;

    if ($_POST):
      $fake_order_min = $_POST["min_count"];
      $fake_order_max = $_POST["max_count"];

      if (!empty($fake_order_min) && !empty($fake_order_max)):
        $update = $conn->prepare("UPDATE settings SET fake_order_min=:min,fake_order_max=:max WHERE id=:id");
        $update->execute(
          array(
            "min" => $fake_order_min,
            "max" => $fake_order_max,
            "id" => 1
          )
        );
        echo "<script> window.location.href = 'admin/settings/site_count';</script>";
      else:
        $update = $conn->prepare("UPDATE settings SET fake_order_min=:min,fake_order_max=:max WHERE id=:id");
        $update->execute(
          array(
            "min" => rand(1, 11),
            "max" => rand(12, 21),
            "id" => 1
          )
        );
        echo "<script> window.location.href = 'admin/settings/site_count';</script>";
      endif;
      if (route(3) == "total_orders_pattern"):
        $prefix = $_POST["total_orders_prefix"];
        $suffix = $_POST["total_orders_suffix"];

        $array = array(
          "panel_orders_prefix" => $prefix,
          "panel_orders_suffix" => $suffix
        );

        $update = $conn->prepare("UPDATE settings SET panel_orders_pattern=:pattern WHERE id=1");
        $update->execute(
          array(
            "pattern" => json_encode($array, true)
          )
        );

        exit();
      endif;

    endif;

  endif;
elseif (route(2) == "payment-bonuses"):
  $access = $admin["access"]["payments_bonus"];
  if ($access):
    if (route(3) == "new" && $_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      if (empty($method_type)):
        $error = 1;
        $errorText = "Method boş olamaz";
        $icon = "error";
      elseif (empty($amount)):
        $error = 1;
        $errorText = "Bonus tutarı boş olamaz";
        $icon = "error";
      elseif (empty($from)):
        $error = 1;
        $errorText = "İtibaren olamaz";
        $icon = "error";
      else:
        $conn->beginTransaction();
        $insert = $conn->prepare("INSERT INTO payments_bonus SET bonus_method=:method, bonus_from=:from, bonus_amount=:amount, bonus_type=:type ");
        $insert = $insert->execute(array("method" => $method_type, "from" => $from, "amount" => $amount, "type" => 2));
        if ($insert):
          $conn->commit();
          $referrer = site_url("admin/settings/payment-bonuses");
          $error = 1;
          $errorText = "Success";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "Failed";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "edit" && $_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      $id = route(4);
      if (empty($method_type)):
        $error = 1;
        $errorText = "Method boş olamaz";
        $icon = "error";
      elseif (empty($amount)):
        $error = 1;
        $errorText = "Bonus tutarı boş olamaz";
        $icon = "error";
      elseif (empty($from)):
        $error = 1;
        $errorText = "İtibaren olamaz";
        $icon = "error";
      else:
        $conn->beginTransaction();
        $update = $conn->prepare("UPDATE payments_bonus SET bonus_method=:method, bonus_from=:from, bonus_amount=:amount WHERE bonus_id=:id ");
        $update = $update->execute(array("method" => $method_type, "from" => $from, "amount" => $amount, "id" => $id));
        if ($update):
          $conn->commit();
          $referrer = site_url("admin/settings/payment-bonuses");
          $error = 1;
          $errorText = "Success";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "Failed";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "delete"):
      $id = route(4);
      if (!countRow(["table" => "payments_bonus", "where" => ["bonus_id" => $id]])):
        $error = 1;
        $icon = "error";
        $errorText = "Lütfen geçerli ödeme bonusu seçin";
      else:
        $delete = $conn->prepare("DELETE FROM payments_bonus WHERE bonus_id=:id ");
        $delete->execute(array("id" => $id));
        if ($delete):
          $error = 1;
          $icon = "success";
          $errorText = "Success";
          $referrer = site_url("admin/settings/payment-bonuses");
        else:
          $error = 1;
          $icon = "error";
          $errorText = "Failed";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 0]);
      exit();
    elseif (!route(3)):
      $bonusList = $conn->prepare("SELECT * FROM payments_bonus INNER JOIN payment_methods WHERE payment_methods.id = payments_bonus.bonus_method ORDER BY payment_methods.id DESC ");
      $bonusList->execute(array());
      $bonusList = $bonusList->fetchAll(PDO::FETCH_ASSOC);
    else:
      header("Location:" . site_url("admin/settings/payment-bonuses"));
    endif;
  endif;

elseif (route(2) == "providers"):
  $access = $admin["access"]["providers"];
  if ($access):
    if (route(3) == "capture-description" && $_POST):


      $api_id = $_POST["api-id"];
      $services = $_POST["services"];
      $language = $_POST["language"];
      $services_page_url = $_POST["service_page_url"];


      $panel_services = $conn->prepare("SELECT service_id,api_service FROM services WHERE service_api=:api");
      $panel_services->execute(
        array(
          "api" => $api_id
        )
      );
      $panel_services = $panel_services->fetchAll(PDO::FETCH_ASSOC);
      $panel_services = array_group_by($panel_services, "service_id");

      $response = HTTP_REQUEST($services_page_url, "", array(""), "GET", 0);
      $response = str_replace("<br />","",$response);

      // PERFECT PANEL 
      $panel_type_1_regex = '!service-description-id-[0-9]+-(.*?)"(\s+|\s|)>([\s\S]*?)<\/div>!';

      // SUPER RENTAL
      $panel_type_2_regex = '!<tr\s+class="servicetable"[^>]+>\s+<td>(.*?)<\/td>[\s\S]*?(?=pdesc)pdesc="([\s\S]*?(?="))!';

      // AIRSMM.COM TYPE PANEL
      $panel_type_3_regex = '!data-filter-table-service-id="(.*?)">[\s\S]*?(?:service-description">)([\s\S]*?(?=<\/td>))!';

      //  smmxboost.com TYPE PANEL
      $panel_type_4_regex = '!id="sDet(.*?)"[\s\S]*?(?:<p>)([\s\S]*?(?=<\/p>))!';

      // VINASMM.COM TYPE PANEL
      $panel_type_5_regex = '!aria-labelledby="serNo(.*?)Label[\s\S]*?(?:class="modal-body">)([\s\S]*?)<\/div>!';

      // RENTAL PANEL
      $panel_type_6_regex = '!id="open_details_(.*?)"[\s\S]*?(?:class="modal-body">)([\s\S]*?)<\/div>!';

      // SECSERS.COM TYPE PANEL
      $panel_type_7_regex = '!id="exampleModal(.*?)"[\s\S]*?(?:class="modal-body">)([\s\S]*?)<\/div>!';

      if (preg_match($panel_type_1_regex, $response)):
        preg_match_all($panel_type_1_regex, $response, $match);
        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[3];

        $array_of_service_ids_and_descriptions = array();
        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }


        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = $array_of_service_ids_and_descriptions[$api_service_id];
          $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          if($multiDesc){
          $multiDesc = json_decode($multiDesc,1);
          }
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor; 
        for ($j = 0; $j < count($services); $j++) {
    $service_id = $services[$j];
    $api_service_id = $panel_services[$service_id][0]["api_service"];
    $service_description = $array_of_service_ids_and_descriptions[$api_service_id];
    $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
    $multiDesc->execute([
        "id" => $service_id
    ]);
    $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
    if($multiDesc){
        $multiDesc = json_decode($multiDesc, true);
        $new_description = $multiDesc[$language]; // Extract the value associated with the "en" key
        if ($new_description !== null) {
            $multiDesc = $new_description; // Assign the new description
        }
    }
    
    $update = $conn->prepare("UPDATE services SET service_description=:description_lang WHERE service_id=:service_id");
    $update->execute(
        array(
            "service_id" => $service_id,
            "description_lang" => $multiDesc
        )
    );
}
// here ends description fetch for perfect panel 
      elseif (preg_match($panel_type_2_regex, $response)):
        preg_match_all($panel_type_2_regex, $response, $match);

        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[2];

        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          $array_of_service_ids_and_descriptions = array();
          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }

        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = htmlspecialchars_decode($array_of_service_ids_and_descriptions[$api_service_id]);

         $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          if($multiDesc){

          $multiDesc = json_decode($multiDesc,1);

          }
        
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor;

      elseif (preg_match($panel_type_3_regex, $response)):

        preg_match_all($panel_type_3_regex, $response, $match);

        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[2];

        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          $array_of_service_ids_and_descriptions = array();
          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }


        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = $array_of_service_ids_and_descriptions[$api_service_id];

          $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          
          if($multiDesc){
          $multiDesc = json_decode($multiDesc,1);
          }
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor;

      elseif (preg_match($panel_type_4_regex, $response)):
        preg_match_all($panel_type_4_regex, $response, $match);
        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[2];
        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          $array_of_service_ids_and_descriptions = array();
          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }

        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = $array_of_service_ids_and_descriptions[$api_service_id];

         $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          if($multiDesc){
          $multiDesc = json_decode($multiDesc,1);
          }
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor;

      elseif (preg_match($panel_type_5_regex, $response)):

        preg_match_all($panel_type_5_regex, $response, $match);
        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[2];
        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          $array_of_service_ids_and_descriptions = array();
          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }

        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = $array_of_service_ids_and_descriptions[$api_service_id];

          $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          if($multiDesc){
          $multiDesc = json_decode($multiDesc,1);
          }
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor;

      elseif (preg_match($panel_type_6_regex, $response)):
        preg_match_all($panel_type_6_regex, $response, $match);
        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[2];
        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          $array_of_service_ids_and_descriptions = array();
          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }

        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = $array_of_service_ids_and_descriptions[$api_service_id];

          $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          if($multiDesc){
          $multiDesc = json_decode($multiDesc,1);
          }
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor;

      elseif (preg_match($panel_type_7_regex, $response)):

        preg_match_all($panel_type_7_regex, $response, $match);
        $array_of_service_ids = $match[1];
        $array_of_service_descriptions = $match[2];
        if (count($array_of_service_ids) == count($array_of_service_descriptions)) {

          $array_of_service_ids_and_descriptions = array();
          for ($i = 0; $i < count($array_of_service_ids); $i++) {
            $array_of_service_ids_and_descriptions[$array_of_service_ids[$i]] = $array_of_service_descriptions[$i];
          }
        }

        for ($j = 0; $j < count($services); $j++):

          $service_id = $services[$j];
          $api_service_id = $panel_services[$service_id][0]["api_service"];
          $service_description = $array_of_service_ids_and_descriptions[$api_service_id];

          $multiDesc = $conn->prepare("SELECT description_lang FROM services WHERE service_id=:id");
          $multiDesc->execute([
             "id" => $service_id
              ]);
          $multiDesc = $multiDesc->fetch(PDO::FETCH_ASSOC)["description_lang"];
          if($multiDesc){
          $multiDesc = json_decode($multiDesc,1);
          }
          $multiDesc[$language] = trim($service_description);
          $multiDesc = json_encode($multiDesc);
          $update = $conn->prepare("UPDATE services SET description_lang=:description_lang WHERE service_id=:service_id");
          $update->execute(
            array(
              "service_id" => $service_id,
              "description_lang" => $multiDesc
            )
          );
        endfor;

      endif;


      header("Location:" . site_url("admin/settings/providers"));
    elseif (route(3) == "new" && $_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      $api_login_credentials["username"] = htmlspecialchars($credential_username);
      $api_login_credentials["password"] = $credential_password;

      $smmapi = new SMMApi();
      $order = $smmapi->action(array('action' => ''), $url);

      $error = $order->error;
      $cur = $smmapi->action(array('key' => $apikey, 'action' => 'balance'), $url);

      if (empty($url)):
        $error = 1;
        $errorText = "Api url cannot be blank";
        $icon = "error";
      elseif (empty($error)):
        $error = 1;
        $errorText = "Wrong url or Api not supporting";
        $icon = "error";
      else:
        $order = explode("/", $url);
        $name = $order[2];
        $conn->beginTransaction();
        $insert = $conn->prepare("INSERT INTO service_api SET api_name=:name, api_alert=:api_alert, status=:status, api_key=:key, api_url=:url, api_limit=:limit, currency=:currency, api_type=:type,api_sync=:sync,api_login_credentials=:credentials");
        $insert = $insert->execute(
          array(
            "name" => $name,
            "key" => $apikey,
            "url" => $url,
            "status" => 1,
            "limit" => 0,
            "currency" => $cur->currency,
            "type" => 1,
            "api_alert" => 1,
            "sync" => $api_sync,
            "credentials" => json_encode($api_login_credentials)
          )
        );

        if ($insert):
          $conn->commit();
          $referrer = site_url("admin/settings/providers");
          $error = 1;
          $errorText = "Success";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "Failed";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "edit" && $_POST):
    foreach ($_POST as $key => $value) {
        $$key = $value;
    }
    $id = route(4);
    $api_login_credentials["username"] = htmlspecialchars($credential_username);
    $api_login_credentials["password"] = $credential_password;

    if (empty($apikey)):
        $error = 1;
        $errorText = "API Key cannot be empty";
        $icon = "error";
    else:
        $theme = $conn->prepare("SELECT * FROM service_api WHERE id=:name");
        $theme->execute(array("name" => $id));
        $theme = $theme->fetch(PDO::FETCH_ASSOC);

        $status = "1";
        if ($theme["status"] == 2):
            $status = "2";
        endif;
        if ($theme["status"] == 2):
            $api_url = $theme["api_url"];

            $smmapi = new SMMApi();

            $order = $smmapi->action(array('action' => 'balance', 'key' => $apikey), $api_url);

            $balance = $order->error;

            if (!empty($balance)):
                $status = "2";
            else:
                $status = "1";
            endif;
        endif;

        $conn->beginTransaction();
        if (strpos($apikey, "*") !== false) {
            $update = $conn->prepare("UPDATE service_api SET api_name=:name, status=:status, api_sync=:sync WHERE id=:id");
            $update = $update->execute(array(
                "name" => $name,
                "id" => $id, 
                "status" => $status, 
                "sync" => $api_sync
            ));
        } else {
            $update = $conn->prepare("UPDATE service_api SET api_name=:name, api_key=:key, status=:status, api_sync=:sync, api_login_credentials=:credentials WHERE id=:id");
            $update = $update->execute(
                array(
                    "name" => $name,
                    "key" => $apikey,
                    "id" => $id,
                    "status" => $status,
                    "sync" => $api_sync,
                    "credentials" => json_encode($api_login_credentials)
                )
            );
        }

        if (strpos($api_login_credentials["credential_username"], "*") !== false) {
        } else {
            $update2 = $conn->prepare("UPDATE service_api SET api_login_credentials=:credentials WHERE id=:id");
            $update2->execute(["credentials" => json_encode($api_login_credentials), "id" => $id]);
        }

        if ($update):
            $conn->commit();
            $referrer = site_url("admin/settings/providers");
            $error = 1;
            $errorText = "Success";
            $icon = "success";
        else:
            $conn->rollBack();
            $error = 1;
            $errorText = "Failed";
            $icon = "error";
        endif;
    endif;
    echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
    exit();
    elseif (!route(3)):
      $providersList = $conn->prepare("SELECT * FROM service_api ");
      $providersList->execute(array());
      $providersList = $providersList->fetchAll(PDO::FETCH_ASSOC);

    elseif (route(3) == "delete"):
      if ($panel["panel_type"] != "Child"):
        $id = route(4);
        if (!countRow(["table" => "service_api", "where" => ["id" => $id]])):
          $error = 1;
          $icon = "error";
          $errorText = "Lütfen geçerli ödeme bonusu seçin";
        else:
          $delete = $conn->prepare("DELETE FROM service_api WHERE id=:id ");
          $delete->execute(array("id" => $id));
          if ($delete):
            $error = 1;
            $errorText = "Success";
            $icon = "success";
            header("Location:" . site_url("admin/settings/providers"));
          else:
            $conn->rollBack();
            $error = 1;
            $errorText = "Failed";
            $icon = "error";
          endif;
        endif;
      else:
        header("Location:" . site_url("admin/settings/providers"));
      endif;
    else:
      header("Location:" . site_url("admin/settings/providers"));
    endif;
  endif;
  if (route(5)):
    header("Location:" . site_url("admin/settings/providers"));
  endif;

elseif (route(2) == "bank-accounts"):
  $access = $admin["access"]["bank_accounts"];
  if ($access):
    if (route(3) == "new" && $_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      if (empty($bank_name)):
        $error = 1;
        $errorText = "Banka adı boş olamaz";
        $icon = "error";
      elseif (empty($bank_alici)):
        $error = 1;
        $errorText = "Alıcı boş olamaz";
        $icon = "error";
      elseif (empty($bank_sube)):
        $error = 1;
        $errorText = "Şube no boş olamaz";
        $icon = "error";
      elseif (empty($bank_hesap)):
        $error = 1;
        $errorText = "Hesap no boş olamaz";
        $icon = "error";
      elseif (empty($bank_iban)):
        $error = 1;
        $errorText = "IBAN boş olamaz";
        $icon = "error";
      else:
        $conn->beginTransaction();
        $insert = $conn->prepare("INSERT INTO bank_accounts SET bank_name=:name, bank_sube=:sube, bank_hesap=:hesap, bank_iban=:iban, bank_alici=:alici ");
        $insert = $insert->execute(
          array(
            "name" => $bank_name,
            "sube" => $bank_sube,
            "hesap" => $bank_hesap,
            "iban" => $bank_iban,
            "alici" => $bank_alici
          )
        );
        if ($insert):
          $conn->commit();
          $referrer = site_url("admin/settings/bank-accounts");
          $error = 1;
          $errorText = "İşlem başarılı";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "İşlem başarısız";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "edit"):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      $id = route(4);
      if (empty($bank_name)):
        $error = 1;
        $errorText = "Banka adı boş olamaz";
        $icon = "error";
      elseif (empty($bank_alici)):
        $error = 1;
        $errorText = "Alıcı boş olamaz";
        $icon = "error";
      elseif (empty($bank_sube)):
        $error = 1;
        $errorText = "Şube no boş olamaz";
        $icon = "error";
      elseif (empty($bank_hesap)):
        $error = 1;
        $errorText = "Hesap no boş olamaz";
        $icon = "error";
      elseif (empty($bank_iban)):
        $error = 1;
        $errorText = "IBAN boş olamaz";
        $icon = "error";
      else:
        $conn->beginTransaction();
        $update = $conn->prepare("UPDATE bank_accounts SET bank_name=:name, bank_sube=:sube, bank_hesap=:hesap, bank_iban=:iban, bank_alici=:alici WHERE id=:id ");
        $update = $update->execute(
          array(
            "name" => $bank_name,
            "sube" => $bank_sube,
            "hesap" => $bank_hesap,
            "iban" => $bank_iban,
            "alici" => $bank_alici,
            "id" => $id
          )
        );
        if ($update):
          $conn->commit();
          $referrer = site_url("admin/settings/bank-accounts");
          $error = 1;
          $errorText = "İşlem başarılı";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "İşlem başarısız";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "delete"):
      $id = route(4);
      if (!countRow(["table" => "bank_accounts", "where" => ["id" => $id]])):
        $error = 1;
        $icon = "error";
        $errorText = "Lütfen geçerli ödeme bonusu seçin";
      else:
        $delete = $conn->prepare("DELETE FROM bank_accounts WHERE id=:id ");
        $delete->execute(
          array(
            "id" => $id
          )
        );
        if ($delete):
          $error = 1;
          $icon = "success";
          $errorText = "İşlem başarılı";
          $referrer = site_url("admin/settings/bank-accounts");
        else:
          $error = 1;
          $icon = "error";
          $errorText = "İşlem başarısız";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 0]);
      exit();
    elseif (!route(3)):
      $bankList = $conn->prepare("SELECT * FROM bank_accounts ");
      $bankList->execute(array());
      $bankList = $bankList->fetchAll(PDO::FETCH_ASSOC);
    else:
      header("Location:" . site_url("admin/settings/bank-accounts"));
    endif;
  endif;
  if (route(5)):
    header("Location:" . site_url("admin/settings/bank-accounts"));
  endif;
elseif (route(2) == "alert"):
  $access = $admin["access"]["alert_settings"];
  if ($access):
    if ($_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      $conn->beginTransaction();
      $update = $conn->prepare("UPDATE settings SET alert_apibalance=:alert_apibalance, alert_serviceapialert=:alert_serviceapialert, admin_mail=:mail,smtp_user=:smtp_user,smtp_pass=:smtp_pass,smtp_server=:smtp_server,smtp_port=:smtp_port,smtp_protocol=:smtp_protocol, alert_newticket=:alert_newticket, alert_newmanuelservice=:alert_newmanuelservice,alert_newmessage=:newmessage, alert_welcomemail=:welcomemail, alert_apimail=:apimail, alert_orderfail=:orderfail WHERE id=:id ");
      $update = $update->execute(array("id" => 1, "alert_apibalance" => $alert_apibalance, "alert_serviceapialert" => $serviceapialert, "mail" => $admin_mail, "smtp_user" => $smtp_user, "smtp_pass" => $smtp_pass, "smtp_server" => $smtp_server, "smtp_port" => $smtp_port, "smtp_protocol" => $smtp_protocol, "newmessage" => $newmessage, "alert_newticket" => $alert_newticket, "alert_newmanuelservice" => $alert_newmanuelservice, "welcomemail" => $welcomemail, "apimail" => $apimail, "orderfail" => $orderfail));
      if ($update):
        $conn->commit();
        header("Location:" . site_url("admin/settings/alert"));
        $_SESSION["client"]["data"]["success"] = 1;
        $_SESSION["client"]["data"]["successText"] = "Success";
      else:
        $conn->rollBack();
        $error = 1;
        $errorText = "Failed";
      endif;
    endif;
  endif;
  if (route(3)):
    header("Location:" . site_url("admin/settings/alert"));
  endif;


elseif (route(2) == "currency"):
  $access = $admin["access"]["currency"];
  if ($access):
    $currencies = $conn->prepare("SELECT * FROM currency WHERE nouse=:code");
    $currencies->execute(array("code" => "2"));
    $currencies = $currencies->fetchAll(PDO::FETCH_ASSOC);



    if (route(3) == "add" && $_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      if (empty($name)):
        $error = 1;
        $errorText = "Currency name cannot be empty";
        $icon = "error";
      elseif (empty($symbol)):
        $error = 1;
        $errorText = "Currency symbol cannot be empty";
        $icon = "error";
      elseif (empty($value)):
        $error = 1;
        $errorText = "Currency exchange rate cannot be empty";
        $icon = "error";
      else:
        $conn->beginTransaction();
        $insert = $conn->prepare("INSERT INTO currency SET name=:name, value=:value, symbol=:symbol  ");
        $insert = $insert->execute(array("name" => $name, "value" => $value, "symbol" => $symbol));
        if ($insert):
          $conn->commit();
          $referrer = site_url("admin/settings/currency");
          $error = 1;
          $errorText = "Success";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "Failed";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "edit" && $_POST):
      foreach ($_POST as $key => $value) {
        $$key = $value;
      }
      $id = route(4);
      if (empty($name)):
        $error = 1;
        $errorText = "Currency name cannot be empty";
        $icon = "error";
      elseif (empty($symbol)):
        $error = 1;
        $errorText = "Currency symbol cannot be empty";
        $icon = "error";
      elseif (empty($value)):
        $error = 1;
        $errorText = "Currency exchange rate cannot be empty";
        $icon = "error";
      else:
        $conn->beginTransaction();
        $update = $conn->prepare("UPDATE currency SET name=:name, status=:status, value=:value, symbol=:symbol WHERE id=:id ");
        $update = $update->execute(array("name" => $name, "value" => $currencyvalue, "status" => $status, "symbol" => $symbol, "id" => $id));
        if ($update):
          $conn->commit();
          $referrer = site_url("admin/settings/currency");
          $error = 1;
          $errorText = "Success";
          $icon = "success";
        else:
          $conn->rollBack();
          $error = 1;
          $errorText = "Failed";
          $icon = "error";
        endif;
      endif;
      echo json_encode(["t" => "error", "m" => $errorText, "s" => $icon, "r" => $referrer, "time" => 1]);
      exit();
    elseif (route(3) == "delete"):
      $id = route(4);
      if ($id == 1):
        $error = 1;
        $icon = "error";
        $errorText = "Failed";
      else:
        $delete = $conn->prepare("DELETE FROM currency WHERE id=:id ");
        $delete->execute(array("id" => $id));
        if ($delete):
          $error = 1;
          $icon = "success";
          $errorText = "Success";
          $referrer = site_url("admin/settings/currency");
        else:
          $error = 1;
          $icon = "error";
          $errorText = "Failed";
        endif;
      endif;
    endif;
  endif;




elseif (route(2) == "subject"):

  $access = $admin["access"]["subject"];
  if ($access):

    if (route(3) == "edit"):
      if ($_POST):
        $id = route(4);
        foreach ($_POST as $key => $value) {
          $$key = $value;
        }

        if (empty($subject)):
          $error = 1;
          $errorText = "Please write a title.";
          $icon = "error";
        else:
          $update = $conn->prepare("UPDATE ticket_subjects SET subject=:subject, content=:content, auto_reply=:auto_reply WHERE subject_id=:id ");
          $update->execute(
            array(
              "id" => $id,
              "subject" => $subject,
              "content" => $content,
              "auto_reply" => $auto_reply
            )
          );
          if ($update):
            $success = 1;
            $successText = "Transaction successful";
          else:
            $error = 1;
            $errorText = "Operation failed";
          endif;
        endif;
      endif;
      $post = $conn->prepare("SELECT * FROM ticket_subjects WHERE subject_id=:id");
      $post->execute(
        array(
          "id" => route(4)
        )
      );
      $post = $post->fetch(PDO::FETCH_ASSOC);
      if (!$post):
        header("Location:" . site_url("admin/settings/subject"));
      endif;

    elseif (!route(3)):

      if ($_POST):

        foreach ($_POST as $key => $value) {
          $$key = $value;
        }

        if (empty($subject)):
          $error = 1;
          $errorText = "Please write a title.";
          $icon = "error";
        else:

          $insert = $conn->prepare("INSERT INTO ticket_subjects SET subject=:subject, content=:content, auto_reply=:auto_reply");

          $insert = $insert->execute(
            array(
              "subject" => $subject,
              "content" => $content,
              "auto_reply" => $auto_reply
            )
          );

          if ($insert):
            $success = 1;
            $successText = "Transaction successful";
            $referrer = site_url("admin/settings/subject");
          else:
            $error = 1;
            $errorText = "Operation failed";
          endif;
        endif;
      endif;

      $subjectList = $conn->prepare("SELECT * FROM ticket_subjects ORDER BY subject_id DESC ");
      $subjectList->execute(array());
      $subjectList = $subjectList->fetchAll(PDO::FETCH_ASSOC);

    elseif (route(3) == "delete"):
      $id = route(4);
      if (!countRow(["table" => "ticket_subjects", "where" => ["subject_id" => $id]])):
        $error = 1;
        $icon = "error";
        $errorText = "Please select valid payout bonus";
      else:
        $delete = $conn->prepare("DELETE FROM ticket_subjects WHERE subject_id=:id ");
        $delete->execute(
          array(
            "id" => $id
          )
        );

        if ($delete):
          $error = 1;
          $icon = "success";
          $errorText = "Transaction successful";
          $referrer = site_url("admin/settings/subject");
        else:
          $error = 1;
          $icon = "error";
          $errorText = "Operation failed";
        endif;
      endif;
      header("Location:" . site_url("admin/settings/subject"));
      exit();
    else:
      header("Location:" . site_url("admin/settings/subject"));
    endif;
  endif;
  if (route(5)):
    header("Location:" . site_url("admin/settings/subject"));
  endif;

endif;

require admin_view('settings');