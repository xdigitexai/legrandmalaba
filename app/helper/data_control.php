<?php




function getCurrencyUnit()
{
    global $conn;
    $lang = $conn->prepare("SELECT site_base_currency FROM settings WHERE id=:id");
    $lang->execute(array("id" => 1));
    $langRow = $lang->fetch(PDO::FETCH_ASSOC);
    return $langRow["site_base_currency"];
}

function CreateApiKey($data)
{
    global $conn;
    $data = md5($data["email"] . $data["username"] . rand(9999, 2324332));
    $row = $conn->prepare("SELECT * FROM clients WHERE apikey=:key ");
    $row->execute(array("key" => $data));
    if ($row->rowCount()) {
        CreateApiKey();
    } else {
        return $data;
    }
}

function createPaymentCode()
{
    global $conn;
    $row = $conn->prepare("SELECT * FROM payments WHERE payment_method!=:method ORDER BY payment_privatecode DESC LIMIT 1 ");
    $row->execute(array("method" => 4));
    $row = $row->fetch(PDO::FETCH_ASSOC);
    return $row["payment_privatecode"];
}

function generate_shopier_form($data)
{
    $api_key = $data->apikey;
    $secret = $data->apisecret;
    $user_registered = date("Y.m.d");
    $time_elapsed = time() - strtotime($user_registered);
    $buyer_account_age = (int) ($time_elapsed / 86400);
    $currency = 0;
    $dataArray = $data;

    $productinfo = $data->item_name;
    $producttype = 1;


    $productinfo = str_replace('"', '', $productinfo);
    $productinfo = str_replace('"', '', $productinfo);
    $current_language = 0;
    $current_lan = 0;
    $modul_version = ('1.0.4');
    srand(time(NULL));
    $random_number = rand(1000000, 9999999);
    $args = array(
        'API_key' => $api_key,
        'website_index' => $data->website_index,
        'platform_order_id' => $data->order_id,
        'product_name' => $productinfo,
        'product_type' => $producttype,
        'buyer_name' => $data->buyer_name,
        'buyer_surname' => $data->buyer_surname,
        'buyer_email' => $data->buyer_email,
        'buyer_account_age' => $buyer_account_age,
        'buyer_id_nr' => 0,
        'buyer_phone' => $data->buyer_phone,
        'billing_address' => $data->billing_address,
        'billing_city' => $data->city,
        'billing_country' => "TR",
        'billing_postcode' => "",
        'shipping_address' => $data->billing_address,
        'shipping_city' => $data->city,
        'shipping_country' => "TR",
        'shipping_postcode' => "",
        'total_order_value' => $data->ucret,
        'currency' => $currency,
        'platform' => 0,
        'is_in_frame' => 1,
        'current_language' => $current_lan,
        'modul_version' => $modul_version,
        'random_nr' => $random_number
    );

    $data = $args["random_nr"] . $args["platform_order_id"] . $args["total_order_value"] . $args["currency"];
    $signature = hash_hmac("SHA256", $data, $secret, true);
    $signature = base64_encode($signature);
    $args['signature'] = $signature;

    $args_array = array();
    foreach ($args as $key => $value) {
        $args_array[] = "<input type='hidden' name='$key' value='$value'/>";
    }
    if (!empty($dataArray->apikey) && !empty($dataArray->apisecret) && !empty($dataArray->website_index)) {
        $_SESSION["data"]["payment_shopier"] = true;

        return '<html> <!doctype html><head> <meta charset="UTF-8"> <meta content="True" name="HandheldFriendly"> <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <meta name="robots" content="noindex, nofollow, noarchive" />
      <meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=0" /> <title lang="tr">Güvenli Ödeme Sayfası</title><body><head>
      <form action="https://www.shopier.com/ShowProduct/api_pay4.php" method="post" id="shopier_payment_form" style="display: none">' . implode('', $args_array) .
            '<script>setInterval(function(){document.getElementById("shopier_payment_form").submit();},2000)</script></form></body></html>';
    }

}


// Add this function somewhere in your code (could be in a functions.php or similar)
function checkFacebookPublic($url) {
    // Check if the URL is a valid Facebook URL
    if (!preg_match('/^(https?:\/\/)?(www\.)?facebook\.com\/.+/i', $url)) {
        return false;
    }
    
    // Initialize cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    // Check for common indicators of private content
    if ($httpCode == 200) {
        if (strpos($response, 'This content isn\'t available right now') !== false ||
            strpos($response, 'content not available') !== false ||
            strpos($response, 'This page isn\'t available') !== false ||
            strpos($response, 'Log Into Facebook') !== false) {
            return false;
        }
        return true;
    }
    
    return false;
}



function username_check($username)
{
    if (preg_match('/^[a-z\d_]{4,32}$/i', $username)) {
        $validate = true;
    } else {
        $validate = false;
    }
    return $validate;
}

function email_check($email)
{
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $validate = true;
    } else {
        $validate = false;
    }
    return $validate;
}

function userdata_check($where, $data)
{
    global $conn;
    $row = $conn->prepare("SELECT * FROM clients WHERE $where=:data ");
    $row->execute(array("data" => $data));
    if ($row->rowCount()) {
        $validate = true;
    } else {
        $validate = false;
    }
    return $validate;
}
function userlogin_check($username, $pass)
{
    global $conn;
    $row = $conn->prepare("SELECT * FROM clients WHERE username=:username");
    $row->execute(array("username" => $username));
    if ($row->rowCount()) {
        $user = $row->fetch(PDO::FETCH_ASSOC);
        if (password_verify($pass, $user["password"])) {
            return true;
        }
    }
    return false;
}

function serviceSpeed($speed, $price)
{
    $siteLang = strtolower(getCurrencyUnit());
    switch ($speed) {
        case '1':
            return '<span style="color: #f24236;font-weight: 500;">' . priceFormat($price) . ' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i> <span style="font-size:10px;"  class="fa fa-arrow-down"> </span></span>';
            break;
        case '2':
            return '<span style="color: #fe6d86;font-weight: 500;">' . priceFormat($price) . ' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i></i> <span style="font-size:10px;"  class="fa fa-arrow-down"> </span></span>';
            break;
        case '3':
            return '<span style="color: #5696c9;font-weight: 500;">' . priceFormat($price) . ' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i></i> <span style="font-size:10px;" class="fa fa-compress"> </span></span>';
            break;
        case '4':
            return '<span style="color: #0dd887;font-weight: 500;">' . priceFormat($price) . ' <i style="font-size:13px;"  class="fa fa-' . $siteLang . '"></i></i> <span style="font-size:10px;"  class="fa fa-arrow-up"> </span></span>';
            break;
    }
}

function service_price($service)
{
    global $conn, $user;
    $row = $conn->prepare("SELECT * FROM clients_price WHERE service_id=:s_id && client_id=:c_id ");
    $row->execute(array("s_id" => $service, "c_id" => $user["client_id"]));
    if ($row->rowCount()) {
        $row = $row->fetch(PDO::FETCH_ASSOC);
        $price = $row["service_price"];
    } else {
        $row = $conn->prepare("SELECT * FROM services WHERE service_id=:id");
        $row->execute(array("id" => $service));
        $row = $row->fetch(PDO::FETCH_ASSOC);
        $price = $row["service_price"];
    }
    return $price;
}

function client_price($service, $userid)
{
    global $conn, $user;
    $row = $conn->prepare("SELECT * FROM clients_price WHERE service_id=:s_id && client_id=:c_id ");
    $row->execute(array("s_id" => $service, "c_id" => $userid));
    if ($row->rowCount()) {
        $row = $row->fetch(PDO::FETCH_ASSOC);
        $price = $row["service_price"];
    } else {
        $row = $conn->prepare("SELECT * FROM services WHERE service_id=:id");
        $row->execute(array("id" => $service));
        $row = $row->fetch(PDO::FETCH_ASSOC);
        $price = $row["service_price"];
    }
    return $price;
}

function open_bankpayment($user)
{
    global $conn;
    $row = $conn->prepare("SELECT * FROM payments WHERE client_id=:client && payment_status=:status && payment_method=:method ");
    $row->execute(array("client" => $user, "status" => 1, "method" => 4));
    $validate = $row->rowCount();
    return $validate;
}

function open_ticket($user)
{
    global $conn;
    $row = $conn->prepare("SELECT * FROM tickets WHERE client_id=:client && status=:status ");
    $row->execute(array("client" => $user, "status" => "pending"));
    $validate = $row->rowCount();
    return $validate;
}

function new_ticket($user)
{
    global $conn;
    $row = $conn->prepare("SELECT * FROM tickets WHERE client_id=:client && support_new=:new ");
    $row->execute(array("client" => $user, "new" => 2));
    $validate = $row->rowCount();
    return $validate;
}

function countRow($data)
{
    global $conn;
    $where = "";
    if (isset($data["where"]) && $data["where"]):
        $where = "WHERE ";
        foreach ($data["where"] as $key => $value) {
            $where .= " $key=:$key && ";
            $execute[$key] = $value;
        }
        $where = substr($where, 0, -3);
        $row = $conn->prepare("SELECT * FROM " . $data['table'] . " $where ");
    else:
        $execute = array();
        $row = $conn->prepare("SELECT * FROM " . $data['table']);
    endif;

    $row->execute($execute);
    $validate = $row->rowCount();
    return $validate;
}

function getRows($data)
{
    global $conn;
    $where = "";
    $order = "";
    $order = "";
    $limit = "";
    $execute[] = "";
    if ($data["where"]):
        $where = "WHERE ";
        foreach ($data["where"] as $key => $value) {
            $where .= " $key=:$key && ";
            $execute[$key] = $value;
        }
        $where = substr($where, 0, -3);
    endif;

    if ($data["order"]):
        $order = "ORDER BY " . $data["order"] . " " . $data["order_type"];
    endif;
    if ($data["limit"]):
        $limit = "LIMIT " . $data["limit"];
    endif;
    $row = $conn->prepare("SELECT * FROM {$data['table']} $where $order $limit ");
    $row->execute($execute);
    if ($row->rowCount()):
        $rows = $row->fetchAll(PDO::FETCH_ASSOC);
    else:
        $rows = [];
    endif;
    return $rows;
}

function getRow($data)
{
    global $conn;
    $where = "WHERE ";
    foreach ($data["where"] as $key => $value) {
        $where .= " $key=:$key && ";
        $execute[$key] = $value;
    }
    $where = substr($where, 0, -3);
    $row = $conn->prepare("SELECT * FROM {$data['table']} $where ");
    $row->execute($execute);
    if ($row->rowCount()):
        $row = $row->fetch(PDO::FETCH_ASSOC);
    else:
        $row = [];
    endif;
    return $row;
}

function statutoTR($status)
{

    switch ($status) {
        case 'pending':
            $statu = "Beklemede";
            break;
        case 'inprogress':
            $statu = "Yükleniyor";
            break;
        case 'completed':
            $statu = "Tamamlandı";
            break;
        case 'partial':
            $statu = "Kısmi tamamlandı";
            break;
        case 'processing':
            $statu = "processing";
            break;
        case 'canceled':
            $statu = "İptal";
            break;
    }

    return $statu;

}

function dripfeedstatutoTR($status)
{

    switch ($status) {
        case 'active':
            $statu = "Aktif";
            break;
        case 'canceled':
            $statu = "İptal";
            break;
        case 'completed':
            $statu = "Tamamlandı";
            break;
    }

    return $statu;

}

function ticketStatu($status)
{

    switch ($status) {
        case 'closed':
            $statu = "Kapalı";
            break;
        case 'answered':
            $statu = "Yanıtlanmış";
            break;
        case 'pending':
            $statu = "Cevap bekliyor";
            break;
    }
    return $statu;


}

function subscriptionstatutoTR($status)
{

    switch ($status) {
        case 'active':
            $statu = "Aktif";
            break;
        case 'canceled':
            $statu = "İptal";
            break;
        case 'completed':
            $statu = "Tamamlanmış";
            break;
        case 'paused':
            $statu = "Durdurulmuş";
            break;
        case 'expired':
            $statu = "Süresi dolmuş";
            break;
        case 'limit':
            $statu = "Gönderimde";
            break;
    }

    return $statu;

}

function serviceTypeGetList($type)
{
    switch ($type) {
        case "Default":
            $service_type = 1;
            break;
        case "Package":
            $service_type = 2;
            break;
        case "Custom Comments":
            $service_type = 3;
            break;
        case "Custom Comments Package":
            $service_type = 4;
            break;
        case "Mentions":
            $service_type = 5;
            break;
        case "Mentions with hashtags":
            $service_type = 6;
            break;
        case "Mentions custom list":
            $service_type = 7;
            break;
        case "Mentions custom list":
            $service_type = "8";
            break;
        case "Mentions user followers":
            $service_type = 9;
            break;
        case "Mentions media likers":
            $service_type = 10;
            break;
        case "Subscriptions":
            $service_type = 11;
            break;

        default:
            $service_type = 1;
            break;
    }
    return $service_type;
}


function array_group_by(array $arr, $key): array
{
    if (!is_string($key) && !is_int($key) && !is_float($key) && !is_callable($key)) {
        trigger_error('array_group_by(): The key should be a string, an integer, a float, or a function', E_USER_ERROR);
    }
    $isFunction = !is_string($key) && is_callable($key);
    $grouped = [];
    foreach ($arr as $value) {
        $groupKey = null;
        if ($isFunction) {
            $groupKey = $key($value);
        } else if (is_object($value)) {
            $groupKey = $value->{$key};
        } else {
            $groupKey = $value[$key];
        }
        $grouped[$groupKey][] = $value;
    }
    if (func_num_args() > 2) {
        $args = func_get_args();
        foreach ($grouped as $groupKey => $value) {
            $params = array_merge([$value], array_slice($args, 2, func_num_args()));
            $grouped[$groupKey] = call_user_func_array('array_group_by', $params);
        }
    }
    return $grouped;
}













function instagramProfilecheck($array)
{
    $type = $array["type"];
    if ($type == "username"):
        $profile = "https://www.instagram.com/" . $array["url"];
        $search_type = "profile";
    else:
        $profile = $array["url"];
        $check = explode("instagram.com/", $profile);
        if (substr($check[1], 0, 2) == "p/"):
            $search_type = "photo";
        else:
            $search_type = "profile";
        endif;
    endif;

    $html = file_get_contents($profile);
    $arr = explode('window._sharedData = ', $html);
    $arr = explode(';</script>', $arr[1]);
    $obj = json_decode($arr[0], true);

    if ($search_type == "profile"):
        $user = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
        $private = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["is_private"];
    else:
        $user = $obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["owner"];
        $private = $obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["owner"]["is_private"];
        if (!$user):
            $user = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
            $private = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["is_private"];
        endif;
    endif;

    if ($array["return"] == "private"):
        return $private;
    endif;
}

function instagramCount($array)
{
    $type = $array["type"];
    if ($type == "username"):
        $profile = "https://www.instagram.com/" . $array["url"];
        $search_type = "profile";
    else:
        $profile = $array["url"];
        $check = explode("instagram.com/", $profile);
        if (substr($check[1], 0, 2) == "p/"):
            $search_type = "photo";
        else:
            $search_type = "profile";
        endif;
    endif;

    $html = file_get_contents($profile);
    $arr = explode('window._sharedData = ', $html);
    $arr = explode(';</script>', $arr[1]);
    $obj = json_decode($arr[0], true);

    if ($array["search"] == "instagram_follower"):
        $user = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"];
        $count = $obj["entry_data"]["ProfilePage"][0]["graphql"]["user"]["edge_followed_by"]["count"];
    else:

        $user = $obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["edge_media_preview_like"]["count"];
        $count = $obj["entry_data"]["PostPage"][0]["graphql"]["shortcode_media"]["edge_media_preview_like"]["count"];

    endif;
    if (!$count):
        return 0;
    else:
        return $count;
    endif;
}

function force_download($file)
{
    if ((isset($file)) && (file_exists($file))) {
        header("Content-length: " . filesize($file));
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $file . '"');
        readfile("$file");
    } else {
        echo "No file selected";
    }
}


function dayPayments($day, $ay, $year, $extra = null)
{
    global $conn;
    if (count($extra["methods"])):
        $where = "&& ( ";
        foreach ($extra["methods"] as $method):
            $where .= "payment_method='$method' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    $first = $year . "-" . $ay . "-" . $day . " 00:00:00";
    $last = $year . "-" . $ay . "-" . $day . " 23:59:59";
    $row = $conn->query("SELECT SUM(payment_amount) FROM payments WHERE payment_delivery='2' && payment_status='3' && payment_create_date<='$last' && payment_create_date>='$first' $where  ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(payment_amount)'];
    return number_format($charge, 2, ".", ",");
}

function monthPayments($ay, $year, $extra = null)
{
    global $conn;
    if (count($extra["methods"])):
        $where = "&& ( ";
        foreach ($extra["methods"] as $method):
            $where .= "payment_method='$method' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    $first = $year . "-" . $ay . "-1 00:00:00";
    $last = $year . "-" . $ay . "-31 23:59:59";
    $row = $conn->query("SELECT SUM(payment_amount) FROM payments WHERE payment_delivery='2' && payment_status='3' && payment_create_date<='$last' && payment_create_date>='$first' $where ")->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(payment_amount)'];
    return number_format($charge, 2, ".", ",");
}

function dayCharge($day, $ay, $year, $extra = null)
{
    global $conn;
    if (count($extra["status"])):
        $where = "&& ( ";
        if (in_array("cron", $extra["status"])):
            $where .= "order_detail='cronpending' || ";
        endif;
        if (in_array("fail", $extra["status"])):
            $where .= "order_error!='-' || ";
        endif;
        foreach ($extra["status"] as $statu):
            if ($statu != "cron" || $statu != "fail"):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    if (count($_POST["services"])):
        $where .= "&& ( ";
        foreach ($extra["services"] as $service):
            $where .= " service_id='$service' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    endif;
    $first = $year . "-" . $ay . "-" . $day . " 00:00:00";
    $last = $year . "-" . $ay . "-" . $day . " 23:59:59";
    $row = $conn->prepare("SELECT SUM(order_charge) FROM orders WHERE order_create<='$last' && order_create>='$first' && dripfeed='1' && subscriptions_type='1' $where");
    $row->execute();
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(order_charge)'];
    return number_format($charge, 2, ".", ",");
}

function monthCharge($month, $year, $extra = null)
{
    global $conn;
    if (count($extra["status"])):
        $where = "&& ( ";
        if (in_array("cron", $extra["status"])):
            $where .= "order_detail='cronpending' || ";
        endif;
        if (in_array("fail", $extra["status"])):
            $where .= "order_error!='-' || ";
        endif;
        foreach ($extra["status"] as $statu):
            if ($statu != "cron" || $statu != "fail"):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ")";
    else:
        $where = "";
    endif;
    if (count($_POST["services"])):
        $where .= "&& ( ";
        foreach ($extra["services"] as $service):
            $where .= " service_id='$service' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    endif;
    $first = $year . "-" . $month . "-1 00:00:00";
    $last = $year . "-" . $month . "-31 23:59:59";
    $row = $conn->prepare("SELECT SUM(order_charge) FROM orders WHERE order_create<='$last' && order_create>='$first'  && dripfeed='1' && subscriptions_type='1' $where");
    $row->execute();
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $charge = $row['SUM(order_charge)'];
    return number_format($charge, 2, ".", ",");
}

function monthChargeNet($month, $year, $extra = null)
{
    global $conn;
    if (count($extra["status"])):
        $where = "&& ( ";
        if (in_array("cron", $extra["status"])):
            $where .= "order_detail='cronpending' || ";
        endif;
        if (in_array("fail", $extra["status"])):
            $where .= "order_error!='-' || ";
        endif;
        foreach ($extra["status"] as $statu):
            if ($statu != "cron" || $statu != "fail"):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ")";
    else:
        $where = "";
    endif;
    if (count($_POST["services"])):
        $where .= "&& ( ";
        foreach ($extra["services"] as $service):
            $where .= " service_id='$service' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    endif;
    $first = $year . "-" . $month . "-1 00:00:00";
    $last = $year . "-" . $month . "-31 23:59:59";
    $row = $conn->prepare("SELECT SUM(order_profit) FROM orders WHERE order_create<='$last' && order_create>='$first' && dripfeed='1' && subscriptions_type='1' && order_api!='0' $where");
    $row->execute();
    $row = $row->fetch(PDO::FETCH_ASSOC);
    $row2 = $conn->prepare("SELECT SUM(order_charge) FROM orders WHERE order_create<='$last' && order_create>='$first' && dripfeed='1' && subscriptions_type='1'  $where");
    $row2->execute();
    $row2 = $row2->fetch(PDO::FETCH_ASSOC);
    $charge = $row2['SUM(order_charge)'] - $row['SUM(order_profit)'];
    return number_format($charge, 2, ".", ",");
}

function dayOrders($day, $month, $year, $extra = null)
{
    global $conn;
    if (count($extra["status"])):
        $where = "&& ( ";
        if (in_array("cron", $extra["status"])):
            $where .= "order_detail='cronpending' || ";
        endif;
        if (in_array("fail", $extra["status"])):
            $where .= "order_error!='-' || ";
        endif;
        foreach ($extra["status"] as $statu):
            if ($statu != "cron" || $statu != "fail"):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    else:
        $where = "";
    endif;
    if (count($_POST["services"])):
        $where .= "&& ( ";
        foreach ($extra["services"] as $service):
            $where .= " service_id='$service' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    endif;
    $first = $year . "-" . $month . "-" . $day . " 00:00:00";
    $last = $year . "-" . $month . "-" . $day . " 23:59:59";
    return $row = $conn->query("SELECT order_id FROM orders WHERE order_create<='$last' && order_create>='$first' $where ")->rowCount();
}

function monthOrders($month, $year, $extra = null)
{
    global $conn;
    if (count($extra["status"])):
        $where = "&& ( ";
        if (in_array("cron", $extra["status"])):
            $where .= "order_detail='cronpending' || ";
        endif;
        if (in_array("fail", $extra["status"])):
            $where .= "order_error!='-' || ";
        endif;
        foreach ($extra["status"] as $statu):
            if ($statu != "cron" || $statu != "fail"):
                $where .= "order_status='$statu' || ";
            endif;
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ")";
    else:
        $where = "";
    endif;
    if (count($_POST["services"])):
        $where .= "&& ( ";
        foreach ($extra["services"] as $service):
            $where .= " service_id='$service' || ";
        endforeach;
        $where = substr($where, 0, -3);
        $where .= ") ";
    endif;
    $first = $year . "-" . $month . "-1 00:00:00";
    $last = $year . "-" . $month . "-31 23:59:59";
    return $row = $conn->query("SELECT order_id FROM orders WHERE order_create<='$last' && order_create>='$first' $where ")->rowCount();
}

function priceFormat($price)
{
    $priceExplode = explode(".", $price);
    if ($priceExplode[1]) {
        if (strlen($priceExplode[1]) == 1) {
            return $price . "0";
        } else {
            return $price;
        }
    } else {
        return $price . "";
    }
}

function icon($images, $category_id, $category_name)
{
    global $conn;
    // $category_name = Normalizer::normalize($category_name, Normalizer::FORM_KC);

    //$category_name = strtolower($category_name);
    $icon = "";


    $category_icon = $conn->prepare("SELECT category_icon FROM categories WHERE category_id=:cid");
    $category_icon->execute(
        array(
            "cid" => $category_id
        )
    );
    $category_icon_row = $category_icon->fetch(PDO::FETCH_ASSOC);
    $category_icon = is_array($category_icon_row) ? json_decode($category_icon_row["category_icon"] ?? '{}', true) : [];
    $category_icon = is_array($category_icon) ? $category_icon : [];

    $category_icon_type = $category_icon["icon_type"] ?? '';

    if ($category_icon_type == "image") {

        $icon = "<img src=\"" . $images[$category_icon["image_id"]][0]["link"] . "\" class=\"img-responsive btn-group-vertical\">";
    } elseif ($category_icon_type == "icon") {

        $icon = "<i style=\"font-size:20px;\" class=\"" . $category_icon["icon_class"] . "\" aria-hidden=\"true\"></i>";
    } else {
        $icon = "";

    }

    return htmlentities($icon);
}

function placeRefill($order_array)
{
    $smmapi = new SMMApi();
    $get_refill = $smmapi->action(array('key' => $order["api_key"], 'action' => 'refill', 'order' => $order["api_orderid"]), $order["api_url"]);
    return $get_refill;
}

function get_inr_rate()
{

}

function update_inr_rate()
{

}
function HTTP_REQUEST($url, $data, $headers, $method, $resp_headers)
{

    $curl = curl_init($url);

    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HEADER, $resp_headers);
    $resp = curl_exec($curl);
    return $resp;
}

function RAND_STRING($length)
{
    $characters = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function get_currencies_array($all = "enabled", $grouped_by = "currency_code")
{
    global $conn;
    if ($all == "enabled") {
        $sql = " WHERE is_enable=1";
    } elseif ($all == "all") {
        $sql = "";
    }
    $currencies = $conn->prepare("SELECT * FROM currencies$sql");
    $currencies->execute();
    $currencies = $currencies->fetchAll(PDO::FETCH_ASSOC);
    $currency_grouped = array_group_by($currencies, $grouped_by);
    return $currency_grouped;
}




function from_to($currencies_array, $from, $to, $amount)
{
    if (empty($from)) {
        $from = getCurrencyUnit();
    }
    if (empty($to)) {
        $to = getCurrencyUnit();
    }
    $amount = floatval($amount);

    $base_currency = strtolower($settings["site_base_currency"] ?? '');
    if (count($currencies_array) && strtolower($from) != strtolower($to) && strtolower($from) != $base_currency && strtolower($to) != $base_currency) {
        $inverse = $currencies_array[$from][0]["currency_inverse_rate"] ?? 1;
        // amount to usd
        $amount_to_base_currency = $amount * $inverse;
        $rate_to = $currencies_array[$to][0]["currency_rate"] ?? 1;
        $c = $amount_to_base_currency * $rate_to;
        return $c;
    } else if (count($currencies_array) && strtolower($from) == strtolower($to)) {
        return $amount;
    } else if (count($currencies_array) && strtolower($from) != strtolower($to) && strtolower($from) == $base_currency && strtolower($to) != $base_currency) {
        $rate_to = $currencies_array[$to][0]["currency_rate"];
        $c = $amount * $rate_to;
        return $c;
    } else if (count($currencies_array) && strtolower($from) != strtolower($to) && strtolower($from) != $base_currency && strtolower($to) == $base_currency) {
        $inverse = $currencies_array[$from][0]["currency_inverse_rate"];

        $amount_to_base_currency = $amount * $inverse;
        return $amount_to_base_currency;
    }
}

function currency_array_group_by_code()
{
    global $conn;
    $currencies = $conn->prepare("SELECT * FROM currencies");
    $currencies->execute();
    $currencies = $currencies->fetchAll(PDO::FETCH_ASSOC);

    $currencies = array_group_by($currencies, "currency_code");
    return $currencies;
}

function get_default_currency()
{
    global $conn;
    $site_curr = $conn->prepare("SELECT site_base_currency FROM settings WHERE id=:id");
    $site_curr->execute(array("id" => 1));
    $site_curr = $site_curr->fetch(PDO::FETCH_ASSOC)["site_base_currency"];
    return $site_curr;
}

function get_currency_hash_by_code($code)
{
    global $conn;
    $site_curr = $conn->prepare("SELECT currency_hash FROM currencies WHERE currency_code=:code");

    $site_curr->execute(array("code" => $code));

    $site_curr = $site_curr->fetch(PDO::FETCH_ASSOC)["currency_hash"];
    return $site_curr;
}

function get_currency_code_by_id($id)
{
    global $conn;
    $curr_code = $conn->prepare("SELECT currency_code FROM currencies WHERE id=:id");
    $curr_code->execute(array("id" => $id));
    $curr_code = $curr_code->fetch(PDO::FETCH_ASSOC)["currency_code"];
    return $curr_code;
}


function get_currency_symbol_by_code($code)
{
    global $conn;
    $curr_sym = $conn->prepare("SELECT currency_symbol FROM currencies WHERE currency_code=:currency_code");
    $curr_sym->execute(array("currency_code" => $code));
    $curr_sym = $curr_sym->fetch(PDO::FETCH_ASSOC);
    return is_array($curr_sym) ? ($curr_sym["currency_symbol"] ?? '') : '';
}
function get_symbol_position_by_code($code)
{
    global $conn;
    $sym_pos = $conn->prepare("SELECT symbol_position FROM currencies WHERE currency_code=:currency_code");
    $sym_pos->execute(array("currency_code" => $code));
    $sym_pos = $sym_pos->fetch(PDO::FETCH_ASSOC);
    return is_array($sym_pos) ? ($sym_pos["symbol_position"] ?? 'left') : 'left';
}

function ROUND_AMOUNT($amount, $precision = 2)
{
    $amount = floatval($amount);
    if ($amount < 1) {
        $amount = round(rtrim(sprintf('%f', floatval($amount)), '0'), 4);
    } elseif ($amount > 0) {
        $amount = number_format(round($amount, $precision), 2);
        //$amount = round($amount,3);
    }
    return $amount;
}

function APIRoundAmount($amount, $precision = 2)
{
    $amount = floatval($amount);
    if ($amount < 1) {
        $amount = round(rtrim(sprintf('%f', floatval($amount)), '0'), 4);
    } elseif ($amount > 0) {
        $amount = number_format(round($amount, $precision), 4);
        //$amount = round($amount,3);
    }
    return $amount;
}




function format_amount_string($currency_code, $amount)
{
    $site_base_currency = get_default_currency();
    $currency_sym = get_currency_symbol_by_code($currency_code);
    $symbol_position = get_symbol_position_by_code($currency_code);
    $amount = ROUND_AMOUNT($amount);
    $formatted_string = $currency_sym . " " . $amount;
    if ($site_base_currency == $currency_code) {
        if ($symbol_position == "left") {
            $formatted_string = "" . $currency_sym . " " . $amount . "";
        } elseif ($symbol_position == "right") {
            $formatted_string = "" . $amount . " " . $currency_sym . "";
        }
    } elseif ($site_base_currency !== $currency_code) {
        if ($symbol_position == "left") {
            $formatted_string = "≈ " . $currency_sym . " " . $amount . "";
        } elseif ($symbol_position == "right") {
            $formatted_string = "≈ " . $amount . " " . $currency_sym . "";
        }
    }
    return $formatted_string;
}

function replaceKeys($oldKey, $newKey, array $input)
{

    $return = array();

    foreach ($input as $key => $value) {
        if ($key === $oldKey)
            $key = $newKey;

        if (is_array($value))
            $value = replaceKeys($oldKey, $newKey, $value);

        $return[$key] = $value;
    }
    return $return;
}

function GET_API_NAME_BY_ID($API_ID)
{
    global $conn;
    $API_NAME = $conn->prepare("SELECT api_name FROM service_api WHERE id=:id");
    $API_NAME->execute(
        array(
            "id" => $API_ID
        )
    );
    $API_NAME = $API_NAME->fetch(PDO::FETCH_ASSOC)["api_name"];
    return $API_NAME;
}



function GET_SERVICE_NAME_BY_ID($service_id)
{
    global $conn;
    $service_name = $conn->prepare("SELECT service_name FROM services WHERE service_id=:service_id");
    $service_name->execute(
        array(
            "service_id" => $service_id
        )
    );
    $service_name = $service_name->fetch(PDO::FETCH_ASSOC)["service_name"];
    return $service_name;

}

function GET_IMAGE_URL_BY_ID($image_id)
{
    global $conn;
    $image = $conn->prepare("SELECT link FROM files WHERE id=:id");
    $image->execute(
        array(
            "id" => $image_id
        )
    );
    $image = $image->fetch(PDO::FETCH_ASSOC)["link"];
    return $image;
}


function error_exit($text)
{
    $output = array(
        "success" => false,
        "message" => $text
    );
    echo json_encode($output, true);
    exit();
}

function errorExit($text)
{
    $output = array(
        "success" => false,
        "message" => $text
    );
    header("Content-Type: application/json; charset=utf-8");
    echo json_encode($output, true);
    exit();
}
function APIErrorExit($text)
{
    $output = array(
        "success" => false,
        "error" => $text
    );
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($output, true);
    exit();
}

function success_response_exit($text)
{
    $output = array(
        "success" => true,
        "message" => $text
    );
    echo json_encode($output, true);
    exit();
}

function obfuscate_provider_key($input)
{

    $length = strlen($input);
    
    if ($length <= 8) {
        return $input;
    }
    
    $firstFour = substr($input, 0, 4);
    $lastFour = substr($input, -4);
    $masked = str_repeat("*", $length - 8);
    
    return $firstFour . $masked . $lastFour;
}


function ServicePrice($service_id = 0, $service_price = null)
{
    global $conn, $user;
    $special_price = $conn->prepare("SELECT service_price FROM clients_price WHERE service_id=:service_id && client_id=:client_id ");
    $special_price->execute(
        array(
            "service_id" => $service_id,
            "client_id" => $user["client_id"]
        )
    );
    if ($special_price->rowCount()) {
        $special_price = $special_price->fetch(PDO::FETCH_ASSOC);
        $service_price = $special_price["service_price"];
    }
    return $service_price;
}
function APIServicePrice($service_id, $service_price, $user_id)
{
    global $conn;
    $special_price = $conn->prepare("SELECT service_price FROM clients_price WHERE service_id=:service_id && client_id=:client_id ");
    $special_price->execute(
        array(
            "service_id" => $service_id,
            "client_id" => $user_id
        )
    );
    if ($special_price->rowCount()) {
        $special_price = $special_price->fetch(PDO::FETCH_ASSOC);
        $service_price = $special_price["service_price"];
    }
    return $service_price;
}

function servicePackage($type)
{
    switch ($type) {
        case 1:
            $service_type = "Default";
            break;
        case 2:
            $service_type = "Package";
            break;
        case 3:
            $service_type = "Custom Comments";
            break;
        case 4:
            $service_type = "Custom Comments Package";
            break;
        default:
            $service_type = "Subscriptions";
            break;
    }
    return $service_type;
}


function decrypt($ciphertext, $key)
{

    $ciphertext = base64_decode($ciphertext);
    $iv = substr($ciphertext, 0, openssl_cipher_iv_length('aes-256-cbc'));
    $hmac = substr($ciphertext, openssl_cipher_iv_length('aes-256-cbc'), 32);
    $ciphertext = substr($ciphertext, openssl_cipher_iv_length('aes-256-cbc') + 32);
    if (hash_hmac('sha256', $iv . $ciphertext, $key, true) !== $hmac) {
        return false;
    }
    return openssl_decrypt($ciphertext, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
}

function generateKashierOrderHash($mid, $amount, $orderId, $secret, $currency = "EGP")
{

    $path = "/?payment=" . $mid . "." . $orderId . "." . $amount . "." . $currency;

    return hash_hmac('sha256', $path, $secret, false);
}

function get_domain($url)
{
    $pieces = parse_url($url);
    $domain = isset($pieces['host']) ? $pieces['host'] : $pieces['path'];
    if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs)) {
        return $regs['domain'];
    }
    return false;
}


function generateUsername($string)
{
    $pattern = " ";
    $firstPart = strstr(strtolower($string), $pattern, true);
    $secondPart = strstr(strtolower($string), $pattern, false);
    $nrRand = rand(0, 100);
    $username = trim($firstPart) . trim($secondPart) . trim($nrRand);
    return $username;
}

function replaceText($string,$text){
    return str_replace("[text]",$text,$string);
}

function uuid()
{
    $data = openssl_random_pseudo_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0010
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}