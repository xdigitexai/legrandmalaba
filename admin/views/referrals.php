<?php include 'header.php'; ?>
<div class="container-fluid">
<div class="row">
<div class="col-md-2">
<ul class="nav nav-pills nav-stacked p-b">
<li class="active"><a href="/admin/referrals">Referrals</a></li>
<li class=""><a href="/admin/payouts">Payouts</a></li>
</ul>
</div>
<div class="col-md-10">
<ul class="nav nav-tabs">
<li class="pull-right p-b">
<form class="form-inline" action="" method="GET">
<div class="input-group">
<input type="text" name="search" class="form-control" placeholder="Search">
<input type="hidden" name="type" value="referrals">
<span class="input-group-btn">
<button type="submit" class="btn btn-default"><span class="glyphicon glyphicon-search" aria-hidden="true"></span></button>
</span>
</div>
</form>
</li>
</ul><div class="table-responsive">
                            <table class="table order-table">
                                <thead>
                                    <tr>
                                        <th class="p-l">ID</th>
                                        <th>Username</th>
                                        <th>Total visits</th>
                                        <th>Sign Up</th>
                                        <th>Conversion Rate</th>
                                        <th>Total Funds</th>
                                        <th>Earned Commision</th>
                                        
                                        <th>Requested Commision</th>
                                        <th>Total Commision</th>
                                        <th>Status</th>
                                        <!-- <th>Reffered Accounts Username</th> -->
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <form id="changebulkForm" action="<?php echo site_url("admin/payments/online/multi-action") ?>" method="post">
                                    <tbody>
                                        <?php foreach ($referrals as $referral) : ?>
                                            <tr>
                                               <td><?php echo $referral["referral_id"] ?></td> 
                                               <td><?php echo $referral["username"] ?></td>
                                               <td><?php echo $referral["referral_clicks"] ?></td>
                                               <td><?php echo $referral["referral_sign_up"] ?></td>
                                               <td><?php echo ($referral["referral_sign_up"]/$referral["referral_clicks"])*100 ?>%</td>
                                               <td><?php echo $referral["referral_totalFunds_byReffered"] ?></td>
                                               <td><?php echo $referral["referral_earned_commision"] ?></td>   
                                         
                                               <td><?php echo $referral["referral_requested_commision"] ?></td>
                                               <td><?php echo $referral["referral_total_commision"] ?></td>
                                               <td><?php if($referral["referral_status"]==2){echo "Active";}else{echo "Inactive";}  ?></td>
                                               <td class="service-block__action">
                                                <div class="dropdown pull-right">
                                                    <button type="button" class="btn btn-primary btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Action</button>
                                                    <ul class="dropdown-menu">
                                                        <li><a href="#" data-toggle="modal" data-target="#modalDiv" data-action="reffered_users" data-id="<?php echo $referral["referral_code"] ?>">Views Reffered Users</a></li>
                                                        <!-- <li><a >Disable Refferal</a></li> -->
                                                  </ul>
                                                </div>
                                            </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <input type="hidden" name="bulkStatus" id="bulkStatus" value="0">
                                </form>
                            </table></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>




<?php include 'footer.php'; ?>
