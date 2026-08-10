<?php include 'header.php'; ?>
<div class="container-fluid">
        <?php if( $success ): ?>
          <div class="alert alert-success "><?php echo $successText; ?></div>
        <?php endif; ?>
           <?php if( $error ): ?>
          <div class="alert alert-danger "><?php echo $errorText; ?></div>
        <?php endif; ?>
    
  <ul class="nav nav-tabs p-b">      
      <li class="pull-right custom-search">
         <form class="form-inline" action="<?=site_url("admin/tasks")?>" method="get">
            <div class="input-group">
               <input type="text" name="search" class="form-control" value="<?=$search_word?>" placeholder="Task ara...">
               <span class="input-group-btn search-select-wrap">
                  <select class="form-control search-select" name="search_type">
                     <option value="order_id" <?php if( $search_where == "order_id" ): echo 'selected'; endif; ?> >Order ID</option>
                  </select>
                  <button type="submit" class="btn btn-default"><span class="fa fa-search" aria-hidden="true"></span></button>
               </span>
            </div>
         </form>
      </li>
   </ul>
   <div class="row row-xs">


<div class="col"><div class="card dwd-100 ">
<div  class="card-body pd-20  dof-inherit">
<div class="container-fluid pd-t-20 pd-b-20 ">
<div class="table-responsive">
   <table class="table order-table ">
      <thead>
         <tr>
         <th>Task ID</th>
          <th>Order ID</th>
          <th>User</th>
          <th>Service</th>
          <th>Link</th>
          <th>Beginning</th>
          <th>Quantity</th>
          <th>Request</th>
          <th>Task Status</th>
          <th>Task Created At</th>
<th>Task Updated At</th>
          <th class="dropdown-th"></th>
         </tr>
      </thead>
      <form id="changebulkForm" action="<?php echo site_url("admin/tasks/multi-action") ?>" method="post">
        <tbody>
          <?php foreach( $orders as $order ): ?>
              <tr>
                 <td class="p-l"><?=$order["task_id"]?>
                 <div class="label label-api"><?php if($order["refill_orderid"]){ echo $order["refill_orderid"]; } ?></div></td>
                 <td><?php echo $order["order_id"] ?>
                 <div class="label label-api"><?php if($order["api_orderid"]){ echo $order["api_orderid"]; } ?></div></td>
                 <td><?php echo $order["username"]; ?></td>
<td><div class="hideextra" style="width:300px">
<?php echo $order["service_name"]; ?></div></td>
                 <td><?php echo $order["order_url"]; ?></td>
                 <td><?php echo $order["order_start"]; ?></td>
                 <td><?php echo $order["order_quantity"]; ?></td>
                 <td><?php if($order["task_type"] == 1): echo "<span class='badge badge-success'>REFILL</span>"; elseif($order["task_type"] == 2): echo "<span class='badge badge-danger'>CANCEL</span>";endif; ?></td>
                 <td><?php if($order["task_status"] == "failed"): echo "Failed"; elseif($order["task_status"] == "inprogress"): echo "In Progress"; elseif($order["task_status"] == "rejected"): echo "Rejected";elseif($order["task_status"] == "completed"): echo "Completed"; elseif($order["task_status"] == "canceled"): echo "Canceled";
                 endif; ?></td>
                 <td><?php echo $order["task_created_at"] ?></td>
                 <td><?php echo $order["task_updated_at"] ?></td>
                 <td class="service-block__action">
                     <div class="dropdown pull-right">
                     <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown" <?php if( $order["task_status"] == "completed" ):  echo "disabled"; elseif( $order["task_status"] == "canceled" ):  echo "disabled"; endif; ?>>Transactions <span class="caret"></span></button>
                       <ul class="dropdown-menu">
<?php if($order["task_type"] == 2 ){ ?>
<li><a href="<?=site_url("admin/tasks/cancel_order/".$order["task_id"])?>">Cancel & Refund</a></li>
<li>
<a href="#" class="check_seller_last_response" data-toggle="modal" data-action="task_id=<?=$order["task_id"]?>" data-target="#flipFlop">Seller Last Response</a></li>
<?php }elseif($order["task_type"] == 1){ ?>

<?php if($order["check_refill_status"] != 1){  ?>


<li><a href="<?=site_url("admin/tasks/update_refill_status/".$order["refill_orderid"])?>">Update Refill Status</a></li>

<?php } ?>
<li><a href="#" class="check_seller_last_response" data-toggle="modal" data-action="task_id=<?=$order["task_id"]?>" data-target="#flipFlop">Seller Last Response</a></li>


<?php } ?>
</ul>
</div>               
</td>
             </tr>
            <?php endforeach; ?>
        </tbody>
        <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
      </form>
   </table></div>
  </div></div></div></div></div>
   <?php if( $paginationArr["count"] > 1 ): ?>
     <div class="row">
        <div class="col-sm-8">
           <nav>
              <ul class="pagination">
                <?php if( $paginationArr["current"] != 1 ): ?>
                 <li class="prev"><a href="<?php echo site_url("admin/tasks/1/".$status.$search_link) ?>">&laquo;</a></li>
                 <li class="prev"><a href="<?php echo site_url("admin/tasks/".$paginationArr["previous"]."/".$status.$search_link) ?>">&lsaquo;</a></li>
                 <?php
                     endif;
                     for ($page=1; $page<=$pageCount; $page++):
                       if( $page >= ($paginationArr['current']-9) and $page <= ($paginationArr['current']+9) ):
                 ?>
                 <li class="<?php if( $page == $paginationArr["current"] ): echo "active"; endif; ?> "><a href="<?php echo site_url("admin/tasks/".$page."/".$status.$search_link) ?>"><?=$page?></a></li>
                 <?php endif; endfor;
                       if( $paginationArr["current"] != $paginationArr["count"] ):
                 ?>
                 <li class="next"><a href="<?php echo site_url("admin/tasks/".$paginationArr["next"]."/".$status.$search_link) ?>" data-page="1">&rsaquo;</a></li>
                 <li class="next"><a href="<?php echo site_url("admin/tasks/".$paginationArr["count"]."/".$status.$search_link) ?>" data-page="1">&raquo;</a></li>
                 <?php endif; ?>
              </ul>
           </nav>
        </div>
     </div>
   <?php endif; ?>
</div>
<div class="modal fade" id="flipFlop" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
<div class="modal-dialog" role="document">
<div class="modal-content">
<div class="modal-header">
<button type="button" class="close" data-dismiss="modal" aria-label="Close">
<span aria-hidden="true">&times;</span>
</button>
<h4 class="modal-title" id="modalLabel"></h4>
</div>
<div class="modal-body">
</div>
<div class="modal-footer">
<button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
</div>
</div>
</div>
</div>


<?php include 'footer.php'; ?>
