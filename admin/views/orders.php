<!---------   
=== Theme Author : Mark Ballerda

=== Contact Whatsapp: +639205648851
  -----------> 
<?php include 'header.php'; ?>
<!-- Font Awesome & SweetAlert2 for new icons and alerts -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<style>
/* Merged and improved styles */
.orders-table {
    overflow-y: scroll;
    width: 100%;
}

.table-link {
    max-width: 250px; 
    display: -webkit-box;
    -webkit-line-clamp: 4;  
    -webkit-box-orient: vertical;
    overflow: hidden;
    word-wrap: break-word; 
}

.table-link a {
    margin-right: 5px;
}

.time-ago {
    color: gray;
    font-size: 12px; /* Adjusted for consistency */
    display: flex;
    flex-direction: column; 
    align-items: flex-start; 
    gap: 2px; 
}

.time-ago i {
    color: gray;
    margin-bottom: 3px; 
}

/* Color coding for time */
.year { color: red; }
.month { color: blue; }
.week { color: green; }
.day { color: orange; }
.hour { color: purple; }
.minute { color: brown; }
.second { color: darkcyan; }
.ago { color: black; font-weight: bold; }

.tooltip5 {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.tooltip5:hover .tooltiptext5 {
    visibility: visible;
    opacity: 1;
}

.fas.fa-info-circle:hover {
    color: #0056b3;
}

.label-api {
    background-color: #31708f;
    font-size: 9px;
}

@media (max-width: 768px) {
    .orders-table {
        overflow-y: scroll;
        width: 100%;
    }
    thead th {
        font-size: 12px;
        padding: 8px;
    }
    .btn-th {
        font-size: 12px;
        padding: 5px 10px;
    }
}
</style>
<div class="container-fluid">
    <div class="dropdown">
    <button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
        <?= isset($units['unit']) ? $units['unit'] : 100; ?> Per Page
        <span style="margin-left:8px;" class="caret"></span>
    </button>
    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
        <li><a href="<?= site_url("admin/orders") ?>?units=100">100 Per Page</a></li>
        <li><a href="<?= site_url("admin/orders") ?>?units=200">200 Per Page</a></li>
        <li><a href="<?= site_url("admin/orders") ?>?units=300">300 Per Page</a></li>
        <li><a href="<?= site_url("admin/orders") ?>?units=400">400 Per Page</a></li>
        <li><a href="<?= site_url("admin/orders") ?>?units=500">500 Per Page</a></li>
    </ul>
</div>
<br>
    
    
            <?php if( $success ): ?>
          <div class="alert alert-success "><?php echo $successText; ?></div>
        <?php endif; ?>
                  <?php if( $error ): ?>
          <div class="alert alert-danger "><?php echo $errorText; ?></div>
        <?php endif; ?>
   <ul class="nav nav-tabs p-b">
<li class="<?php if( $status == "all"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders")?>">
        All Orders 
        <span class="badge" style="background: linear-gradient(45deg, #6c757d, #a9a9a9); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php echo countRow(["table"=>"orders"]) ?></span>
    </a>
</li>
<li class="<?php if( $status == "manuel"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1")?>?mode=manuel">
        Manual
        <span class="badge" style="background: linear-gradient(45deg, #007bff, #0056b3); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;">
            <?php echo countRow(["table" => "orders", "where" => ["api_orderid" => 0]]) ?>
        </span>
    </a>
</li> 
<li class="<?php if( $status == "cronpending"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/cronpending")?>">
        Cron Pending 
        <span class="badge" style="background: linear-gradient(45deg, #ff9f43, #ffc107); color: black; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($cronpendingcount): echo $cronpendingcount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "pending"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/pending")?>">
        Pending 
        <span class="badge" style="background: linear-gradient(45deg, #007bff, #4facfe); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($pendingcount): echo $pendingcount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "processing"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/processing")?>">
        Processing 
        <span class="badge" style="background: linear-gradient(45deg, #00c6ff, #0072ff); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($processingcount): echo $processingcount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "inprogress"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/inprogress")?>">
        Inprogress 
        <span class="badge" style="background: linear-gradient(45deg, #28a745, #a3e635); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($inprogresscount): echo $inprogresscount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "completed"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/completed")?>">
        Completed 
        <span class="badge" style="background: linear-gradient(45deg, #20c997, #54f49f); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($completedcount): echo $completedcount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "partial"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/partial")?>">
        Partial 
        <span class="badge" style="background: linear-gradient(45deg, #ffd700, #ffef96); color: black; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($partialcount): echo $partialcount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "canceled"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/canceled")?>">
        Canceled 
        <span class="badge" style="background: linear-gradient(45deg, #ff4d4d, #dc3545); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($canceledcount): echo $canceledcount; endif; ?></span>
    </a>
</li>
<li class="<?php if( $status == "fail"): echo "active"; endif; ?>">
    <a href="<?=site_url("admin/orders/1/fail")?>">
        Fail 
        <span class="badge" style="background: linear-gradient(45deg, #6c757d, #343a40); color: white; border-radius: 12px; padding: 5px 10px; font-size: 12px;"><?php if($failCount): echo $failCount; endif; ?></span>
    </a>
</li> 
    <li class="pull-right custom-search">
    <form class="form-inline search-form" action="<?=site_url("admin/orders")?>" method="get">
        <div class="input-group">
            <input type="text" name="search" class="form-control search-input" value="<?=$search_word?>" placeholder="Search" required>
            <span class="input-group-btn search-select-wrap">
                <select class="form-control search-select" name="search_type">
                    <option value="order_id" <?php if( $search_where == "order_id" ): echo 'selected'; endif; ?>>Order ID</option>
                    <option value="order_url" <?php if( $search_where == "order_url" ): echo 'selected'; endif; ?>>Order URL</option>
                    <option value="username" <?php if( $search_where == "username" ): echo 'selected'; endif; ?>>Username</option>
                    <option value="service_id" <?php if( $search_where == "service_id" ): echo 'selected'; endif; ?>>Service ID</option>
                    <option value="api_orderid" <?php if( $search_where == "api_orderid" ): echo 'selected'; endif; ?>>Api Order ID</option>
                    <option value="api_serviceid" <?php if( $search_where == "api_serviceid" ): echo 'selected'; endif; ?>>Api Service ID</option>
                    <option value="multi_id" <?php if( $search_where == "multi_id" ): echo 'selected'; endif; ?>>Multiple ID</option>
                </select>
                <button type="submit" class="btn btn-primary search-button">
                    <span class="fa fa-search" aria-hidden="true"></span>
                </button>
            </span>
        </div>
    </form>
</li>
                                
                                
<div class="orders-table">
<table class="table" id="dt">
        <thead>
          <tr>
            <th class="checkAll-th">
              <div class="checkAll-holder">
                <input type="checkbox" id="checkAll">
                <input type="hidden" id="checkAllText" value="order">
              </div>
              <div class="action-block">
                <ul class="action-list">
                  <li><span class="countOrders"></span> Orders Selected</li>
                  <li>
                    <div class="dropdown">
                      <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown"> Bulk Actions <span class="caret"></span></button>
                      <ul class="dropdown-menu">
                        <li>
								  <?php if( $status  ==  "fail" ): ?>
                          <a class="bulkorder" data-type="resend">Resend</a>
                          <?php endif; ?>
                          <a class="bulkorder" data-type="pending">Pending</a>
                          <a class="bulkorder" data-type="processing">Processing</a>
                          <a class="bulkorder" data-type="inprogress">In Progress</a>
                          <a class="bulkorder" data-type="completed">Completed</a>
                          <a class="bulkorder" data-type="canceled">Cancel & Refund</a>
                          
                        </li>
                      </ul>
                    </div>
                  </li>
                </ul>
              </div>
            </th>
            <th class="p-l">ID</th>
            <th>User</th>
            <th>Charge</th>
            <th>Profit</th>
            <th class="service-block__service">Link</th>
            <th>Start</th>
            <th>Quantity</th>
            <th class="dropdown-th">Service</th>
            <th>Remains</th>
            <th width="5%" class="dropdown-th">Status</th>
            <th width="10%">Created at</th> 
            <th width="10%">Completion Time</th> 
            <th width="5%" class="dropdown-th">Mode</th>
            <th></th>
          </tr>
        </thead>
      <form id="changebulkForm" action="<?php echo site_url("admin/orders/multi-action") ?>" method="post">
        <tbody>
          <?php foreach( $orders as $order ): ?>
            <tr>
                <td><input type="checkbox" class="selectOrder"  name="order[<?php echo $order["order_id"] ?>]" value="1" style="border:1px solid #fff"></td>
                <td class="p-l">
                <?php echo $order["order_id"] ?>
                <?php if( $order["api_orderid"] != 0 ): echo '<div class="label label-api">'.$order["api_orderid"].'</div>'; endif; ?>
              </td>
                <td><?php echo $order["username"]; if( $order["order_where"] == "api" ): echo ' <span class="label label-api">API</span>'; endif; ?> </td>
                <td class="service-block__minorder">
                  <div style="width:85px;">
                    <?php echo format_amount_string($settings["site_base_currency"],$order["order_charge"]); ?>
                    <?php if( $order["service_api"] != 0 ): echo '<div class="service-block__provider-value">'.format_amount_string($settings["site_base_currency"],$order["api_charge"]).'</div>'; endif; ?>
                  </div>
                </td>
                <td>
                  <div style="width:85px;">
                    <span style="color: green;">
                      <?php echo format_amount_string($settings["site_base_currency"], $order["order_profit"]); ?>
                    </span>
                  </div>
                </td>

                <td data-label="Link" class="table-link">
                    <?php 
                        echo '<a href="'.$order["order_url"].'" target="_blank">'.$order["order_url"].'</a>'; 
                        if (!empty($order["order_extras"]) && $order["order_extras"] != "[]") {
                            echo ' <a href="#" class="btn btn-default btn-xs" data-toggle="modal" data-target="#modalDiv" data-action="order_comment" data-id="'.$order["order_id"].'">Comments</a>'; 
                        }
                    ?>
                    <a href="#" class="copy-link" data-link="<?php echo htmlspecialchars($order["order_url"], ENT_QUOTES); ?>">
                        <i class="fas fa-copy"></i>
                    </a>
                </td>

                <td><?php echo $order["order_start"]; ?></td>
                <td><?php echo $order["order_quantity"]; ?></td>
                
                <td width="30%">
                    <span class="label-id">ID-<?php echo $order["service_id"]; ?></span>
                    <?php 
                        if (!empty($order["service_name"])) {
                            echo htmlspecialchars($order["service_name"]);
                        } else {
                            echo '<span style="color: #c7254e; font-style: italic;">(Service Deleted)</span>';
                        }
                    ?>
                    <a style="color: #555;" target="_blank" href="/admin/services?search=<?php echo $order["service_id"]; ?>"> <i class="fa fa-search" aria-hidden="true"></i></a>
                    <?php 
                    $api_name = GET_API_NAME_BY_ID($order["order_api"]);
                    if ($api_name):
                    ?>
                        <div class="tooltip5">
                            <span class="fas fa-info-circle"></span>
                            <span class="tooltiptext5"><?php echo $api_name; ?></span>
                        </div>
                    <?php endif; ?>
                </td>
                <td><?php if( $order["order_status"] == "completed" && substr($order["order_remains"], 0,1) == "-" ): echo "+".substr($order["order_remains"], 1);  else: echo $order["order_remains"]; endif; ?></td>
                <td>
                  <div class="dropdown">
                    <button class="btn btn-th btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                      <?php echo orderStatu($order["order_status"], $order["order_error"], $order["order_detail"]); ?>
                      <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
                      <?php if( in_array($order["order_status"], ["pending", "completed", "processing", "partial", "fail"]) ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_cancel/".$order["order_id"])?>">Cancel & Refund</a></li>
                      <?php endif; ?>
                      
                      <?php if( in_array($order["order_status"], ["pending", "inprogress", "processing"]) ): ?>              
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_complete/".$order["order_id"])?>">Complete</a></li>
                      <?php endif; ?>              

                      <?php if( in_array($order["order_status"], ["pending", "processing"]) ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_inprogress/".$order["order_id"])?>">In Progress</a></li>
                      <?php endif; ?>    
                      
                      <?php if( in_array($order["order_status"], ["pending", "processing"]) ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_processing/".$order["order_id"])?>">Processing</a></li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </td>

                <td width="10%" style="white-space: nowrap;">
                    <i class="fa-solid fa-calendar-days"></i> <?php 
                    $order_create = $order["order_create"];
                    $formatted_date = date("F j, Y, g:i a", strtotime($order_create)); 
                    echo $formatted_date; 
                    ?>
                    <br>
                    <div class="time-ago">
                        <i class="fas fa-stopwatch"></i>
                        <span>
                        <?php 
                        $current_time = new DateTime();
                        $order_time = new DateTime($order_create);
                        $interval = $current_time->diff($order_time);

                        $time_parts = [];

                        if ($interval->y > 0) { $time_parts[] = "<span class='year'>" . $interval->y . " year" . ($interval->y > 1 ? "s" : "") . "</span>"; }
                        if ($interval->m > 0) { $time_parts[] = "<span class='month'>" . $interval->m . " month" . ($interval->m > 1 ? "s" : "") . "</span>"; }
                        if ($interval->d >= 7) { $weeks = floor($interval->d / 7); $time_parts[] = "<span class='week'>" . $weeks . " week" . ($weeks > 1 ? "s" : "") . "</span>"; }
                        elseif ($interval->d > 0) { $time_parts[] = "<span class='day'>" . $interval->d . " day" . ($interval->d > 1 ? "s" : "") . "</span>"; }
                        if ($interval->h > 0) { $time_parts[] = "<span class='hour'>" . $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . "</span>"; }
                        if ($interval->i > 0) { $time_parts[] = "<span class='minute'>" . $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . "</span>"; }
                        if (empty($time_parts) && $interval->s > 0) { $time_parts[] = "<span class='second'>" . $interval->s . " second" . ($interval->s > 1 ? "s" : "") . "</span>"; }
                        
                        echo implode(", ", array_slice($time_parts, 0, 2)); // Show top 2 time parts
                        ?>
                        <span class="ago"> ago</span>
                        </span>
                    </div>
                </td>
                
                <td class="completion-time" data-completion-time="<?php echo htmlspecialchars($order['completion_time']); ?>">
    <?php 
    if ($order['order_status'] == 'completed' && !empty($order['completion_time'])) {
        // Display the actual completion time
        echo htmlspecialchars($order['completion_time']); 
        echo '<br>'; // Line break for better formatting
        
        // Ensure you have the start time available
        if (!empty($order['order_create'])) {
            // Call hoursTaken with both start and completion times
            echo hoursTaken($order['order_create'], $order['completion_time']); // Display time taken
        } else {
            echo 'Start time not available';
        }
    } else {
        echo 'Not completed';
    }
    ?>
</td>
    
                <td width="5%"><?php if( $order["api_service"] == 0 ): echo "Manual"; else: echo "Automatic"; endif; ?></td>
                <td class="service-block__action">
                  <div class="dropdown pull-right">
                    <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Options <span class="caret"></span></button>
                    <ul class="dropdown-menu">
                      <?php if( $order["order_error"] != "-" && $order["service_api"] != 0 ): ?>
                        <li><a href="#"  data-toggle="modal" data-target="#modalDiv" data-action="order_errors" data-id="<?php echo $order["order_id"] ?>">Order Errors</a></li>
                        <li><a href="<?=site_url("admin/orders/order_resend/".$order["order_id"])?>">Forward Order</a></li>
                      <?php endif; ?>
                      <?php if( $order["order_error"] == "-" && $order["service_api"] != 0 ): ?>
                        <li><a href="#"  data-toggle="modal" data-target="#modalDiv" data-action="order_details" data-id="<?php echo $order["order_id"] ?>">Order Details</a></li>
                      <?php endif; ?>
                      <?php if( $order["service_api"] == 0 || $order["order_error"] != "-"  ): ?>
                        <li><a href="#"  data-toggle="modal" data-target="#modalDiv" data-action="order_orderurl" data-id="<?php echo $order["order_id"] ?>">Set Order URL</a></li>
                      <?php endif; ?>
                        <li><a href="#"  data-toggle="modal" data-target="#modalDiv" data-action="order_startcount" data-id="<?php echo $order["order_id"] ?>">Set Start Count</a></li>
                      
                      <?php if( $order["order_status"] != "partial"): ?>
                        <li><a href="#"  data-toggle="modal" data-target="#modalDiv" data-action="order_partial" data-id="<?php echo $order["order_id"] ?>">Set Partial</a></li>
                      <?php endif; ?>
                      <hr>
                      <?php if( in_array($order["order_status"], ["pending", "completed", "processing", "partial", "fail"]) ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_cancel/".$order["order_id"])?>">Cancel & Refund</a></li>
                      <?php endif; ?>
                      
                      <?php if( in_array($order["order_status"], ["pending", "inprogress", "processing"]) ): ?>              
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_complete/".$order["order_id"])?>">Complete</a></li>
                      <?php endif; ?>              

                      <?php if( in_array($order["order_status"], ["pending", "processing"]) ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_inprogress/".$order["order_id"])?>">In Progress</a></li>
                      <?php endif; ?>  
                      
                      <?php if( in_array($order["order_status"], ["pending", "processing"]) ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_processing/".$order["order_id"])?>">Processing</a></li>
                      <?php endif; ?>

                      <?php if( $order["refill"]  ==  "1" || $order["refill"]  ==  "2" || $order["status_order"]  ==  "Completed" ): ?>
                        <li><a href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/orders/order_refill_activate/".$order["order_id"])?>">Activate Refill Button</a></li>
                      <?php endif; ?>
                    </ul>
                  </div>
                </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
        <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
      </form>
    </table>
</div>
    <?php if( $paginationArr["count"] > 1 ): ?>
      <div class="row">
        <div class="col-sm-8">
          <nav>
            <ul class="pagination">
              <?php if( $paginationArr["current"] != 1 ): ?>
                <li class="prev"><a href="<?php echo site_url("admin/orders/1/".$status.$search_link) ?>">&laquo;</a></li>
                <li class="prev"><a href="<?php echo site_url("admin/orders/".$paginationArr["previous"]."/".$status.$search_link) ?>">&lsaquo;</a></li>
                <?php
                  endif;
                  for ($page=1; $page<=$pageCount; $page++):
                    if( $page >= ($paginationArr['current']-9) and $page <= ($paginationArr['current']+9) ):
              ?>
              <li class="<?php if( $page == $paginationArr["current"] ): echo "active"; endif; ?> "><a href="<?php echo site_url("admin/orders/".$page."/".$status.$search_link) ?>"><?=$page?></a></li>
              <?php endif; endfor;
                    if( $paginationArr["current"] != $paginationArr["count"] ):
              ?>
              <li class="next"><a href="<?php echo site_url("admin/orders/".$paginationArr["next"]."/".$status.$search_link) ?>" data-page="1">&rsaquo;</a></li>
              <li class="next"><a href="<?php echo site_url("admin/orders/".$paginationArr["count"]."/".$status.$search_link) ?>" data-page="1">&raquo;</a></li>
              <?php endif; ?>
            </ul>
          </nav>
        </div>
        <div class="col-sm-4 pagination-counters">
          <?php echo $count; ?> from within the order <?php echo $where+1 ?>'den <?php if( $where+$to > $count ): echo $count; else: echo $where+$to; endif; ?>'up to
          </div>
      </div>
    <?php endif; ?>
</div>
<div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
   <div class="modal-dialog modal-dialog-center" role="document">
      <div class="modal-content">
         <div class="modal-body text-center">
            <h4>Are you sure you want to update the status?</h4>
            <div align="center">
               <a class="btn btn-primary" href="" id="confirmYes">Yes</a>
               <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
         </div>
      </div>
   </div>
</div>

<?php include 'footer.php'; ?>

<script>
    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll(".copy-link").forEach(function (button) {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                var link = this.getAttribute("data-link");

                var tempInput = document.createElement("input");
                tempInput.value = link;
                document.body.appendChild(tempInput);
                tempInput.select();
                document.execCommand("copy");
                document.body.removeChild(tempInput);

                Swal.fire({
                    icon: "success",
                    title: "Link Copied!",
                    text: "The link has been copied to your clipboard.",
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        });
    });
</script>