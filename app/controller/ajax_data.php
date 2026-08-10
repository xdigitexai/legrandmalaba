<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

function getOrderStatusCounts($conn, $service_id) {
    $statuses = ['pending', 'processing', 'inprogress', 'completed', 'partial', 'canceled'];
    $counts = [];

    foreach ($statuses as $status) {
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM orders WHERE service_id = :service_id AND order_status = :status");
        $stmt->execute(['service_id' => $service_id, 'status' => $status]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $counts[$status] = $result['count'];
    }

    // Get total orders
    $stmt = $conn->prepare("SELECT COUNT(*) as total FROM orders WHERE service_id = :service_id");
    $stmt->execute(['service_id' => $service_id]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $counts['total'] = $result['total'];

    return $counts;
}

if($_GET["service_id"] != ""){$_SESSION["data"]["services"] = $_GET["service_id"]; die();}
if (route(1) == "selectize" || route(0) == "selectize"){
$symbol = $currency['symbol'];$currency_value = $currency['value'];
$discount_percent = $user["discount_percentage"]/100;
    $search = $_POST["searchValue"];
    $action = $_POST["action"];
     if($action == "selectize"){
        $s = $_POST["searchValue"]; 
       $sear = strtolower($search);
        $orders = $conn->prepare("SELECT s.* FROM services s INNER JOIN categories c ON s.category_id = c.category_id WHERE (s.service_id LIKE '%".$sear."%' OR LOWER(s.service_name) LIKE '%".$sear."%') AND s.service_type = '2' AND s.service_deleted = '0' AND c.category_type = '2' AND c.category_deleted = '0' ORDER BY s.service_line"); 
$orders->execute(array());
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);
$data = array();
foreach ($orders as $service) {
$price = service_price($service["service_id"]);
$price = ($price - ($price * $discount_percent));
$final_service_price = format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$price));
    $data[] = array("service_id"=>$service["service_id"],"category_id"=>$service["category_id"], "service_name"=>$service["service_name"]." - ". '<b> ' . $final_service_price.'</b>');}
 echo json_encode($data);}
  die(); }
$symbol = $currency['symbol'];
$currency_value = $currency['value'];
$action = htmlspecialchars($_POST["action"]);
$discount_percent = $user["discount_percentage"]/100;
if ($action == "services_list"):
$category = $_POST["category"];
$_SESSION["selected_category_id"] = $category;
$services = $conn->prepare("SELECT * FROM services WHERE category_id=:c_id && service_type='2' && service_deleted='0' ORDER BY service_line ");
$services->execute(array("c_id" => $category));
$services = $services->fetchAll(PDO::FETCH_ASSOC);
if ($services):
$serviceList = "";
else:
$serviceList = "<option value='0'>Service not found in this category</option>";
endif;

$i = 0;
foreach ($services as $service) {
$serviceNameLang = json_decode($service["name_lang"] ?? '[]', 1);
$serviceNameLang = is_array($serviceNameLang) ? $serviceNameLang : [];
$_lang = $user["lang"] ?? '';
if(!($_lang && ($serviceNameLang[$_lang] ?? null))){
    $service_name = $service["service_name"];
} else {
   $service_name = $serviceNameLang[$_lang];
}
$price = service_price($service["service_id"]);
$price = ($price - ($price * $discount_percent));
$final_service_price = format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$price));
$search = $conn->prepare("SELECT * FROM clients_service WHERE service_id=:service && client_id=:c_id ");
$search->execute(array("service" => $service["service_id"], "c_id" => $user["client_id"]));
if ($service["service_secret"] == 2 || $search->rowCount()):
$serviceList.= "<option data-content=\"".htmlentities("<span class=\"badge badge-secondary style-text-primary badge-rounded\">".$service["service_id"]."</span>")." ".$service_name . " - " .$final_service_price." per 1000\" value='" . $service['service_id'] . "' ";
if (isset($_SESSION["data"]["services"])) {$_SESSION["selected_service_id"] = $_SESSION["data"]["services"];}
if (isset($_SESSION["selected_service_id"]) && $_SESSION["selected_service_id"] == $service["service_id"] && $i != 0) :
    $serviceList .= "selected";
elseif ($i == 0):
$serviceList.= "selected";
endif;
$serviceList.= ">" . htmlspecialchars($service_name . " - " . $final_service_price . " per 1000", ENT_QUOTES, 'UTF-8') . "</option>";
$i++;
endif;
}










    echo json_encode(['services' => $serviceList]);
elseif ($action == "service_detail"):
    $s_id = $_POST["service"];
    $_SESSION["selected_service_id"] = $s_id;
    $service = $conn->prepare("SELECT * FROM services WHERE service_id=:s_id AND service_deleted='0'");
    $service->execute(array('s_id' => $s_id));
    $service = $service->fetch(PDO::FETCH_ASSOC);
    $service["service_price"] = service_price($service["service_id"]);
    $service["service_price"] = ($service["service_price"]  - ($service["service_price"]  * $discount_percent));
    $serviceDetails = "";
    if ($settings['service_dashboard'] == 1) {
    $orderCounts = getOrderStatusCounts($conn, $s_id);

    // Prepare the dashboard HTML
    $dashboardHTML = '<div class="service-dashboard">';
    $dashboardHTML .= '<h4>Service Dashboard</h4>';
    $dashboardHTML .= '<ul>';
    foreach ($orderCounts as $status => $count) {
        $dashboardHTML .= '<li style="color:black;">' . ucfirst($status) . ': ' . $count . '</li>';
    }
    $dashboardHTML .= '</ul>';
    $dashboardHTML .= '</div>';

    // Append the dashboard to service details
    $serviceDetails .= $dashboardHTML;
}


    $multiDescription = json_decode($service["description_lang"] ?? '[]', 1);
    $multiDescription = is_array($multiDescription) ? $multiDescription : [];
    $_lang = $user["lang"] ?? '';
    if($_lang && ($multiDescription[$_lang] ?? null)){
      $description = $multiDescription[$_lang];
    } else {
       $description = $service["service_description"];
    }
    
    if (!empty($description)):
        $description = str_replace("\n", "<br />", $description);
        $serviceDetails.= '<div class="form-group fields" id="description">
              <label for="service_description" class="control-label">Description</label>
              <div class="panel-body border-solid border-rounded" id="service_description">
              ' . $description . '
              </div>
            </div>';
    endif;




 if ($settings['analytics_dashboard'] == 1 && $user['balance'] > 0) {
    // Service Analytics Section
    $orderAnalytics = $conn->prepare("
        SELECT 
            order_id,
            order_quantity,
            order_status,
            order_create,
            completion_time
        FROM orders 
        WHERE service_id = :s_id
        ORDER BY order_create DESC
        LIMIT 6
    ");
    $orderAnalytics->execute(array('s_id' => $s_id));
    
    $analyticsHTML = '
    <div class="form-group fields" id="service_analytics">
        <label class="control-label">Service Analytics</label>
        <div class="scroll-container" id="service_descriptions">';

    if ($orderAnalytics->rowCount() > 0) {
        while ($order = $orderAnalytics->fetch(PDO::FETCH_ASSOC)) {
            $completion_time = '';
            $createdDate = date('Y-m-d H:i:s', strtotime($order['order_create']));
            
            if ($order['order_status'] == 'completed' && !empty($order['completion_time'])) {
                try {
                    $start = new DateTime($order['order_create']);
                    $end = new DateTime($order['completion_time']);
                    $diff = $start->diff($end);

                    // Construct the completion time string
                    $completion_time_parts = [];

                    if ($diff->y > 0) {
                        $completion_time_parts[] = $diff->y . ' year' . ($diff->y > 1 ? 's' : '');
                    }
                    if ($diff->m > 0) {
                        $completion_time_parts[] = $diff->m . ' month' . ($diff->m > 1 ? 's' : '');
                    }
                    if ($diff->d > 0) {
                        $completion_time_parts[] = $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
                    }
                    if ($diff->h > 0) {
                        $completion_time_parts[] = $diff->h . ' hour' . ($diff->h > 1 ? 's' : '');
                    }
                    if ($diff->i > 0) {
                        $completion_time_parts[] = $diff->i . ' minute' . ($diff->i > 1 ? 's' : '');
                    }
                    if ($diff->s > 0) {
                        $completion_time_parts[] = $diff->s . ' second' . ($diff->s > 1 ? 's' : '');
                    }

                    // Join the parts to create the final string
                    $completion_time = 'completed within ' . implode(', ', $completion_time_parts);
                    
                    // If no parts were added, it means the order was completed instantly
                    if (empty($completion_time_parts)) {
                        $completion_time = 'completed instantly';
                    }

                } catch (Exception $e) {
                    $completion_time = "time calculation error";
                }
            }
            
            // Append order details to analyticsHTML
            $analyticsHTML .= '
                <p class="mb-1" style="color:black">
                    Order ID: ' . htmlentities($order['order_id']) . ' of ' . htmlentities($order['order_quantity']) . ' quantity 
                    ' . htmlentities($completion_time) . ' placed on ' . htmlentities($createdDate) . '
                </p>';
        }
    } else {
        $analyticsHTML .= '<p style="color:black;">No order history found for this service</p>';
    }

    $analyticsHTML .= '</div></div>'; // Close the panel body and form group
    $serviceDetails .= $analyticsHTML; // Append to service details
} elseif ($settings['analytics_dashboard'] == 1 && $user['balance'] <= 0) {
    $serviceDetails .= '
    <div class="form-group fields" id="service_analytics">
        <label class="control-label">Service Analytics</label>
        <div class="scroll-container" id="service_descriptions">
            <p style="color:black;">You need to have a positive balance to view service analytics</p>
        </div>
    </div>';
}

// Get service details
$s_id = $_POST["service"];
$service = $conn->prepare("SELECT * FROM services WHERE service_id=:s_id AND service_deleted='0'");
$service->execute(['s_id' => $s_id]);
$service = $service->fetch(PDO::FETCH_ASSOC);

// Calculate discounted price
$service["service_price"] = service_price($service["service_id"]);
$service["service_price"] = ($service["service_price"] - ($service["service_price"] * $discount_percent));

if ($settings["services_average_time"] && !empty($service["time"])) {

    $time = nl2br(htmlspecialchars($service["time"]));
    
    $lastUpdatedDisplay = !empty($service["last_updated"])
        ? date('M j, Y H:i', strtotime($service["last_updated"]))
        : 'Not recorded';

    $serviceDetails .= '
    <div class="form-group fields" id="description">
        <label class="control-label" for="service_description">
            <span>Average Time</span>
            <span class="ml-1 mr-1 fas fa-exclamation-circle" 
                  data-toggle="tooltip"
                  data-placement="top" 
                  title="Calculated from recent orders. Last updated: '.htmlspecialchars($lastUpdatedDisplay).'"></span>
        </label>

        <div class="panel-body border-solid border-rounded" id="service_description">
            '.$time.'
            <div class="text-muted small mt-1">
                Last updated: '.htmlspecialchars($lastUpdatedDisplay).'
            </div>
        </div>
    </div>';
}

      


    if ($service["service_package"] == 1 || $service["service_package"] == 2 || $service["service_package"] == 3 || $service["service_package"] == 4):
        if ($service["want_username"] == 2):
            $link_type = 'Username';
        else:
            $link_type = 'Link';
        endif;
        $serviceDetails.= '<div class="form-group fields" id="order_link">
                <label class="control-label" for="field-orderform-fields-link">' . $link_type . '</label>
                <input class="form-control" name="link" value="' . $_SESSION["data"]["link"] . '" type="text" id="field-orderform-fields-link">
              </div>';
    endif;
    if ($service["service_package"] == 1):
        $serviceDetails.= '<div class="form-group fields" id="order_quantity">
                  <label class="control-label" for="field-orderform-fields-quantity">Quantity</label>
                  <input class="form-control" name="quantity" value="' . $_SESSION["data"]["quantity"] . '" type="text" id="neworder_quantity">
              </div>
              <small class="help-block min-max">Min: ' . $service["service_min"] . ' - Max: ' . $service["service_max"] . '</small>
              ';
    endif;
    if ($service["service_package"] == 11 || $service["service_package"] == 12 || $service["service_package"] == 13 || $service["service_package"] == 14 || $service["service_package"] == 15):
        $serviceDetails.= '<div class="form-group fields" id="order_link">
                <label class="control-label" for="field-orderform-fields-link">Username</label>
                <input class="form-control" name="username" value="' . $_SESSION["data"]["username"] . '" type="text" id="field-orderform-fields-link">
              </div>';
    endif;
    if ($service["service_package"] == 3):
        $serviceDetails.= '<div class="form-group fields" id="order_quantity">
              <label class="control-label" for="field-orderform-fields-quantity">Quantity</label>
              <input class="form-control" name="quantity" value="" type="text" id="neworder_quantity" disabled="">
          </div>
          <small class="help-block min-max">Min: ' . $service["service_min"] . ' - Max: ' . $service["service_max"] . '</small>
          ';
    endif;
    if ($service["service_package"] == 11 || $service["service_package"] == 12 || $service["service_package"] == 13):
        $serviceDetails.= '<div class="form-group fields" id="order_link">
                <label class="control-label" for="field-orderform-fields-link">How many posts limit would you like?</label>
                <input class="form-control" name="posts" value="' . $_SESSION["data"]["posts"] . '" type="text" id="field-orderform-fields-link">
              </div>';
        $serviceDetails.= '<div class="form-group fields" id="order_min">
              <label class="control-label" for="order_count">Quantity</label>
              <div class="row">
                  <div class="col-xs-6">
                      <input type="text" class="form-control" id="order_count" name="min" value="' . $_SESSION["data"]["min"] . '" placeholder="Minimum">
                  </div>
                  <div class="col-xs-6">
                      <input type="text" class="form-control" id="order_count" name="max" value="' . $_SESSION["data"]["max"] . '" placeholder="Maximum">
                  </div>
              </div>
              <small class="help-block min-max">Min: ' . $service["service_min"] . ' - Max: ' . $service["service_max"] . '</small>
          </div>
          <div class="form-group fields" id="order_delay">
              <div class="row">
                  <div class="col-xs-6">
                      <label class="control-label" for="field-orderform-fields-delay">How long order delay do you want?</label>
                      <select class="form-control" name="delay" id="field-orderform-fields-delay">
                          <option value="0" ';
        if ($_SESSION["data"]["delay"] == 0):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>No delay</option>
                          <option value="300" ';
        if ($_SESSION["data"]["delay"] == 300):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>5 minutes</option>
                          <option value="600" ';
        if ($_SESSION["data"]["delay"] == 600):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>10 minutes</option>
                          <option value="900" ';
        if ($_SESSION["data"]["delay"] == 900):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>15 minutes</option>
                          <option value="1800" ';
        if ($_SESSION["data"]["delay"] == 1800):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>30 minutes</option>
                          <option value="3600" ';
        if ($_SESSION["data"]["delay"] == 3600):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>60 minutes</option>
                          <option value="5400" ';
        if ($_SESSION["data"]["delay"] == 5400):
            $serviceDetails.= ' selected';
        endif;
        $serviceDetails.= '>90 minutes</option>
                      </select>
                  </div>
                  <div class="col-xs-6">
                      <label for="field-orderform-fields-expiry">End Date</label>
                      <div class="input-group" id="datetimepicker">
                          <input class="form-control datetime" name="expiry" id="expiryDate" value="' . $_SESSION["data"]["expiry"] . '" type="text" autocomplete="off">
                          <span class="input-group-btn">
                              <button class="btn btn-default clear-datetime" id="clearExpiry" type="button"> <span class="fa fa-trash-o"></span></button>
                          </span>
                      </div>
                  </div>
              </div>
          </div>';
    endif;
    if ($service["service_package"] == 3 || $service["service_package"] == 4):
        $serviceDetails.= '<div class="form-group fields" id="order_comment">
              <label class="control-label">Comments</label>
              <textarea class="form-control counter" name="comments" id="neworder_comment" cols="30" rows="10" data-related="quantity">' . $_SESSION["data"]["comments"] . '</textarea>
          </div>';
    endif;
    if ($service["service_dripfeed"] == 2):
        if ($_SESSION["data"]["check"]):
            $check = "checked";
        endif;
        $serviceDetails.= '<div id="dripfeed">
                <div class="form-group fields" id="order_check">
                    <label class="control-label has-depends " for="dripfeedcheckbox">
                        <input name="check" value="1" type="checkbox" ' . $check . ' id="dripfeedcheckbox">
                        Drip-feed Order
                    </label>
                    <div class="hidden" id="dripfeed-options">
                        <div class="form-group">
                            <label class="control-label" for="dripfeed-runs">How many times should the process be repeated?</label>
                            <input class="form-control" name="runs" value="' . $_SESSION["data"]["runs"] . '" type="text" id="dripfeed-runs">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="dripfeed-interval">How many minutes interval? (60*24 = 1440 for daily shipping)</label>
                            <input class="form-control" name="interval" value="' . $_SESSION["data"]["interval"] . '" type="text" id="dripfeed-interval">
                        </div>

                        <div class="form-group">
                            <label class="control-label" for="dripfeed-totalquantity">Total Quantity to be Sent</label>
                            <input class="form-control" name="total_quantity" value="' . $_SESSION["data"]["total_quantity"] . '" type="text" id="dripfeed-totalquantity" readonly="">
                        </div>
                    </div>
                </div>
            </div>
            ';
    endif;
    $runs = $_POST["runs"];
    if (!$runs):
        $runs = 1;
    endif;
    $dripfeed = $_POST["dripfeed"];
    $quantity = $_POST["quantity"];
    if ($s_id != 0 && $dripfeed == "bos"):
 $price = $quantity * $service["service_price"] / 1000;
 $price = ($price - ($price * $discount_percent));
$data = [
'details' => $serviceDetails,
'price' => format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$price))

];
    elseif ($s_id != 0 && $dripfeed == "var"):
$price = $runs * $quantity * $service["service_price"] / 1000;
$price = ($price - ($price * $discount_percent));
$data = [
'details' => $serviceDetails,
'price' => format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$price))
];
    elseif ($s_id != 0 && !isset($dripfeed)):
$price = $service["service_price"];
$price = ($price - ($price * $discount_percent));
$data = [
'details' => $serviceDetails, 
'price' => format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$price))

];
    else:
        $data = ['empty' => 1];
    endif;
    if ($service["service_package"] == 11 || $service["service_package"] == 12 || $service["service_package"] == 13):
        $data["sub"] = 1;
    endif;
    echo json_encode($data);
    unset($_SESSION["data"]);
elseif ($action == "service_price"):
    $service = $_POST["service"];
    $quantity = $_POST["quantity"];
    $comments = $_POST["comments"];
    $dripfeed = $_POST["dripfeed"];
    $runs = $_POST["runs"];
    if (!$runs):
        $runs = 1;
    endif;
$price = service_price($service) / 1000 ;
$price = ($price - ($price * $discount_percent));

if ($comments):
$quantity = count(explode("\n", $comments));
endif;

if($quantity == 0) {
$totalPrice = get_currency_symbol_by_code($user["currency_type"])." ".service_price($service)*0;

} elseif ($dripfeed == "var") {
$totalPrice = $price * $quantity * $runs;
$totalPrice = format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$totalPrice));
$totalPrice.= '';
    } else {
$totalPrice = $price * $quantity;;
$totalPrice = format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],$totalPrice));
$totalPrice.= '';
    }
    echo json_encode(['price' => $totalPrice, 'commentsCount' => $quantity, 'totalQuantity' => $runs * $quantity]);


endif;


