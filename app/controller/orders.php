<?php
if (!defined('BASEPATH')) {
  die('Direct access to the script is not allowed');
}
$title .= $languageArray["orders.title"];
$smmapi = new SMMApi();
if ($_SESSION["msmbilisim_userlogin"] != 1 || $user["client_type"] == 1) {
  Header("Location:".site_url('logout'));
}

if ($settings["email_confirmation"] == 1 && $user["email_type"] == 1) {
  Header("Location:".site_url('confirm_email'));
}


if (route(1) == 'order_complete' && route(2)) {
    $order_id = route(2);
    $completion_time = date("Y-m-d H:i:s"); // Get the current time

    // Fetch the order to check its current status and creation time
    $orderQuery = $conn->prepare("SELECT order_status, completion_time, order_create FROM orders WHERE order_id = :order_id");
    $orderQuery->execute(['order_id' => $order_id]);
    $order = $orderQuery->fetch(PDO::FETCH_ASSOC);

    // Check if the order is already completed
    if ($order['order_status'] == 'completed') {
        // If already completed, use the existing completion time
        $o['completion_time'] = date("Y-m-d H:i:s", strtotime($order['completion_time']));
    } else {
        // Update the order status to completed
        $update = $conn->prepare("UPDATE orders SET order_status = :status, order_error = :error, last_check = :last_check, completion_time = :completion_time WHERE order_id = :order_id");

        // Execute the update with the appropriate parameters
        $update->execute([
            'status' => 'completed',
            'order_id' => $order_id,
            'error' => '-',
            'last_check' => $completion_time,
            'completion_time' => $completion_time
        ]);

        // Set the completion time in the output array
        $o['completion_time'] = $completion_time; // Set the completion time to the current time
    }

    // Calculate how long it took to complete the order
    $order_created_time = strtotime($order['order_create']); // Fetch order creation time
    $completion_time = strtotime($o['completion_time']); // Fetch completion time
    $duration = $completion_time - $order_created_time; // Calculate duration in seconds

    // Format the duration into a human-readable format
    $o['duration'] = format_duration($duration);
}

// Function to format duration into a human-readable format
function format_duration($seconds) {
    $units = [
        'year' => 31536000,
        'month' => 2592000,
        'week' => 604800,
        'day' => 86400,
        'hour' => 3600,
        'minute' => 60,
        'second' => 1,
    ];

    $result = [];
    foreach ($units as $unit => $value) {
        if ($seconds >= $value) {
            $count = floor($seconds / $value);
            $result[] = "$count $unit" . ($count > 1 ? 's' : '');
            $seconds %= $value; // Get the remaining seconds
        }
    }

    return implode(', ', $result);
}
if (route(1) == 'refill' && route(2)) {
  $order_id = route(2);

  $order = $conn->prepare("SELECT * FROM orders INNER JOIN services LEFT JOIN service_api ON services.service_api = service_api.id WHERE services.service_id = orders.service_id
  AND orders.client_id=:c_id AND orders.order_id=:order_id ");
  $order->execute(['c_id' => $user['client_id'], 'order_id' => $order_id]);
  $order = $order->fetch(PDO::FETCH_ASSOC);

  $refill_tasks = $conn->prepare(
    'SELECT * FROM tasks WHERE task_type=:type && order_id=:id'
  );
  $refill_tasks->execute(['id' => $order_id, 'type' => 1]);
  $refill_tasks = $refill_tasks->fetch(PDO::FETCH_ASSOC);

  $now = $order['refill_days'];
  $time = strtotime("$now day", strtotime($order['order_create']));
  $new_time = date('Y.m.d H:i:s', $time);
  $time2 = date('Y.m.d H:i:s');

  if (empty($refill_tasks)) {
    $refill_end_date = date(
      'Y.m.d H:i:s',
      strtotime($order['last_check']) + 84600
    );
  } else {
    $refill_end_date = date(
      'Y.m.d H:i:s',
      strtotime($refill_tasks['task_updated_at']) + 84600
    );
  }

  if (
    $new_time > $time2 &&
    $time2 > $refill_end_date
  ) {
    $refillAllowed = true;
  } else {
    $refillAllowed = false;
  }
//print_r($refillAllowed);exit();
  if (
    !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 1,
        'task_status' => 'inprogress',
        'client_id' => $user['client_id'],
        'order_id' => $order_id,
      ],
    ]) &&
    countRow([
      'table' => 'orders',
      'where' => [
        'order_id' => $order_id,
        'client_id' => $user['client_id'],
      ],
    ]) &&
    $refillAllowed
  ) {
    //check service refill is manual or automatic
    if ($order['api_service'] != 0) {
      // automatic refill will be sent to provider and response added to table

      $get_refill = $smmapi->action(array('key' => $order["api_key"], 'action' => 'refill', 'order' => $order["api_orderid"]), $order["api_url"]);
      $res = $get_refill;
      if (strlen($res->error) > 0) {
        $check_refill_status = 1;
        $status = "rejected";
      } elseif(!$res->refill) {
        $check_refill_status = 2;
        $status = "inprogress";
      } else {
        $check_refill_status = 2;
        $status = "inprogress";
      }




      $jres = json_encode($res);
      $insert = $conn->prepare("INSERT INTO tasks SET client_id=:c_id, order_id=:o_id,
  service_id=:s_id, task_type=:type, task_api=:api , task_response=:res , task_status=:status
   , task_by=:task_by , check_refill_status=:check_refill_status
   ,task_created_at=:task_created_at , task_updated_at=:task_updated_at,refill_orderid=:refill_orderid");
      $insert = $insert->execute([
        'c_id' => $order['client_id'],
        'o_id' => $order['order_id'],
        's_id' => $order['service_id'],
        'type' => 1,
        'api' => $order["id"],
        'res' => $jres,
        'status' => $status,
        'task_by' => 'user',
        'check_refill_status' => $check_refill_status,
        'task_created_at' => date('Y.m.d H:i:s'),
        'task_updated_at' => date('Y.m.d H:i:s'), "refill_orderid" => $res->refill
      ]);


    }
    header("Location: ".site_url("orders"));
  } else {
    $task_nofity = 1;
    $response["refill_res"] = 'Refill not allowed!, try again later!';
    $response["refill_icon"] = 'error';
    header("Location: ".site_url("orders"));
  }

}

if (route(1) == 'cancel' && route(2)) {
  $order_id = route(2);

  if (
    !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 2,
        'task_status' => 'rejected',
        'client_id' => $user['client_id'],
        'order_id' => $order_id,
      ],
    ]) &&
    !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 2,
        'task_status' => 'completed',
        'client_id' => $user['client_id'],
        'order_id' => $order_id,
      ],
    ]) &&
    !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 2,
        'task_status' => 'failed',
        'client_id' => $user['client_id'],
        'order_id' => $order_id,
      ],
    ]) &&
    !countRow([

      'table' => 'tasks',

      'where' => [
        'task_type' => 2,
        'task_status' => 'inprogress',
        'client_id' => $user['client_id'],
        'order_id' => $order_id,
      ],
    ]) &&
    countRow([
      'table' => 'orders',
      'where' => [
        'order_id' => $order_id,
        'client_id' => $user['client_id'],
      ],
    ])
  ) {
    $order = $conn->prepare("SELECT * FROM orders INNER JOIN services LEFT JOIN service_api ON services.service_api = service_api.id WHERE services.service_id = orders.service_id
AND orders.client_id=:c_id AND orders.order_id=:order_id ");
    $order->execute([
      'c_id' => $user['client_id'],
      'order_id' => $order_id,
    ]);
    $order = $order->fetch(PDO::FETCH_ASSOC);

    //send api req to cancel , if api doesnt accepts req , add it as a manual request
    $smmapi = new SMMApi();

    $get_cancel = $smmapi->action(
      [
        'key' => $order['api_key'],
        'action' => 'cancel',
        'order' => $order['api_orderid'],
      ],
      $order['api_url']
    );
    $res = json_encode($get_cancel, true);

    $insert = $conn->prepare("INSERT INTO tasks SET client_id=:c_id, order_id=:o_id,
  service_id=:s_id, task_type=:type, task_api=:api , task_response=:res , task_status=:status , task_by=:task_by,task_created_at=:date,task_updated_at=:task_updated_at,check_refill_status=:check_refill_status");
    $insert->execute([
      'c_id' => $order['client_id'],
      'o_id' => $order['order_id'],
      's_id' => $order['service_id'],
      'type' => 2,
      'api' => $order["id"],
      'res' => $res,
      "check_refill_status" => 2,
      'status' => 'inprogress',
      'task_by' => 'user', "date" => date('Y.m.d H:i:s'),
      "task_updated_at" => date('Y.m.d H:i:s')
    ]);

    /*
$task_nofity = 1;
$response["refill_res"] =
"Cancellation request added, we'll try our best to cancel it.";
$response["refill_icon"] = 'success';*/
  }







}

$status_list = [
  'all',
  'pending',
  'inprogress',
  'completed',
  'partial',
  'processing',
  'canceled',
];
$search_statu = route(1);
if (!route(1)) :
$route[1] = 'all';
endif;

if (!in_array($search_statu, $status_list)) :
$route[1] = 'all';
endif;

if (route(2)) :
$page = route(2);
else :
$page = 1;
endif;
if (route(1) != 'all') :
$search = "&& order_status='" . route(1) . "'";
else :
$search = '';
endif;
if (!empty(urldecode(strip_tags($_GET['search'])))) :
$search .=
" && ( order_url LIKE '%" .
urldecode(strip_tags($_GET['search'])) .
"%' || order_id LIKE '%" .
urldecode(strip_tags($_GET['search'])) .
"%' ) ";
endif;
if (!empty(strip_tags($_GET['subscription']))) :
$search .=
" && ( subscriptions_id LIKE '%" . strip_tags($_GET['subscription']) . "%'  ) ";
endif;
if (!empty(strip_tags($_GET['dripfeed']))) :
$search .= " && ( dripfeed_id LIKE '%" . strip_tags($_GET['dripfeed']) . "%'  ) ";
endif;

$c_id = $user['client_id'];
$to = 25;
$count = $conn->query(
  "SELECT * FROM orders WHERE client_id='$c_id' && dripfeed='1' && subscriptions_type='1' $search "
);
if (empty($count)) {
  $count = 0;
} else {
  $count = $count->rowCount();
}

$pageCount = ceil($count / $to);
if ($page > $pageCount) :
$page = 1;
endif;
$where = $page * $to - $to;
$paginationArr = [
  'count' => $pageCount,
  'current' => $page,
  'next' => $page + 1,
  'previous' => $page - 1,
];

$orders = $conn->prepare(
  "SELECT * FROM orders INNER JOIN services ON services.service_id = orders.service_id && orders.dripfeed=:dripfeed && orders.subscriptions_type=:subs && orders.client_id=:c_id $search ORDER BY orders.order_id DESC LIMIT $where,$to "
);
$orders->execute(['c_id' => $user['client_id'], 'dripfeed' => 1, 'subs' => 1]);
$orders = $orders->fetchAll(PDO::FETCH_ASSOC);
$ordersList = [];

foreach ($orders as $order) {
  // 94256

  // refill options 1- off , 2 - on , 3 - locked , 4 - refilling

  $service_refill = $order['is_refill'];
  $show_refill = $order['show_refill'];
  $order_status = $order['order_status'];
  $service_refill_days = $order['refill_days'];
  $order_created = $order['order_create'];
  $order_updated = $order['last_check'];
  $refill_end_date = date(
    'Y-m-d H:i:s',
    strtotime("$service_refill_days day", strtotime($order_updated))
  );
  $todaysDate = date('Y-m-d H:i:s');
  $orderCompleted_1Day = date(
    'Y-m-d H:i:s',
    strtotime('1 day', strtotime($order_updated))
  );

  // order has a refill service and order is completed and more than 24 hours of completion and order is under refill period then show refill
  if (
    $service_refill == 1 &&
    $order_status == 'completed'
  ) {
    $refillLog = countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 1,
        'client_id' => $user['client_id'],
        'order_id' => $order['order_id'],
        'service_id' => $order['service_id'],
      ],
    ]);

    //check whether a refill exists or its an fresh order with no refills previously

    if ($refillLog == 0) {
      // fresh order , refill logs doesnt exists.
      //check whether the order has been completed before 24 hours or not
      if ($todaysDate >= $orderCompleted_1Day) {
        $o['refillButton'] = 2;
      } else {
        //remaining time to get the refill
        $diff =
        strtotime($orderCompleted_1Day) - strtotime($todaysDate); //in seconds
        $diff = gmdate('H:i:s', $diff);
        $diff = explode(':', $diff); //in hours
        $hours = ltrim($diff[0], '0');
        $minutes = ltrim($diff[1], '0');
        if (empty($minutes)) {
          $o['refillTimeLeft'] = "$hours hours";
        } elseif (empty($hours)) {
          $o['refillTimeLeft'] = "$minutes minutes";
        } else {
          $o['refillTimeLeft'] = "$hours hours $minutes minutes";
        }

        $o['refillButton'] = 3;
      }
    } else {
      // already few or more refill has been taken place for it...next ;;

      $refill_tasks = $conn->prepare(
        'SELECT * FROM tasks WHERE task_type=:type && order_id=:id ORDER BY task_id DESC LIMIT 1'
      );
      $refill_tasks->execute(['id' => $order['order_id'], 'type' => 1]);
      $refill_tasks = $refill_tasks->fetch(PDO::FETCH_ASSOC);

      $last_refill_time = $refill_tasks['task_updated_at'];
      $last_refill_time_1Day = date(
        'Y-m-d H:i:s',
        strtotime('1 day', strtotime($last_refill_time))
      );

      //we ll check whether the last refill is placed 24 hours ago or not , if yes then true

      if ($todaysDate > $last_refill_time_1Day) {
        $o['refillButton'] = 2;
      } elseif (
        $refill_tasks['task_status'] == 'pending' ||
        $refill_tasks['task_status'] == 'inprogress'
      ) {
        $o['refillButton'] = 4;
      } else {
        $diff =
        strtotime($last_refill_time_1Day) - strtotime($todaysDate); //in seconds
        $diff = gmdate('H:i:s', $diff);
        $diff = explode(':', $diff); //in hours
        $hours = ltrim($diff[0], '0');
        $minutes = ltrim($diff[1], '0');
        if (empty($minutes)) {
          $o['refillTimeLeft'] = "$hours hours";
        } elseif (empty($hours)) {
          $o['refillTimeLeft'] = "$minutes minutes";
        } else {
          $o['refillTimeLeft'] = "$hours hours $minutes minutes";
        }

        $o['refillButton'] = 3;
      }
    }
  } else {
    //order is not eligible for refill
    $o['refillButton'] = 1;
  }

  if (
    $order['cancelbutton'] == 1 &&
    ($order['order_status'] == 'pending' ||
      $order['order_status'] == 'processing' ||
      $order['order_status'] == 'inprogress') &&
    !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 2,
        'task_status' => 'pending',
        'client_id' => $user['client_id'],
        'order_id' => $order['order_id'],
      ],
    ]) && !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 2,
        'task_status' => 'inprogress',
        'client_id' => $user['client_id'],
        'order_id' => $order['order_id'],
      ],
    ]) &&
    !countRow([
      'table' => 'tasks',
      'where' => [
        'task_type' => 2,
        'task_status' => 'rejected',
        'client_id' => $user['client_id'],
        'order_id' => $order['order_id'],
      ],
    ])
  ) {
    $o['cancelButton'] = true;
  } else {
    $o['cancelButton'] = false;
  }
 // Other existing code...

    // Ensure completion time is set correctly
    if ($order['order_status'] == 'completed') {
        // Assuming 'completion_time' is a field in the orders table
        $o['completion_time'] = !empty($order['completion_time']) ? date("Y-m-d H:i:s", strtotime($order['completion_time'])) : null;
    } else {
        $o['completion_time'] = null; // Or set to a default value if needed
    }
  // Calculate how long it took to complete the order
    $order_created_time = strtotime($order['order_create']); // Fetch order creation time
    $completion_time = strtotime($o['completion_time']); // Fetch completion time
    $duration = $completion_time - $order_created_time; // Calculate duration in seconds

    // Format the duration into a human-readable format
    $o['duration'] = format_duration($duration);
  
  $o['id'] = $order['order_id'];
  $o['date'] = date('Y-m-d H:i:s', strtotime($order['order_create']));
  $o['link'] = $order['order_url'];
  $o['charge'] = format_amount_string($user["currency_type"], from_to(get_currencies_array("enabled"), $settings["site_base_currency"], $user["currency_type"], $order['order_charge']));
  $o['start_count'] = $order['order_start'];
  $o['quantity'] = $order['order_quantity'];
  $o['service'] = $order['service_name'];
  $o["show_refill"] = $show_refill;
  $o['service_api'] = $order['service_api'];
  $o['cancel'] = $order['cancelbutton'];
  $o['status'] = $languageArray['orders.status.' . $order['order_status']];
  if (
    $order['order_status'] == 'completed' &&
    substr($order['order_remains'], 0, 1) == '-'
  ) :
  $o['remains'] = '+' . substr($order['order_remains'], 1);
  else :
  $o['remains'] = $order['order_remains'];
  endif;
  array_push($ordersList, $o);


}


