<?php include 'header.php'; ?>
<div class="container-fluid">
<div class="row">
<div class="col-md-2">
<ul class="nav nav-pills nav-stacked p-b">
<li class=""><a href="/admin/referrals">Referrals</a></li>
<li class="active"><a href="/admin/payouts">Payouts</a></li>
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
                                        <th class="p-l">#</th>
                                        <th> Code</th>
                                        <th> Username </th>
                                        <th>Amount Requested</th>
                                        <th>Payout Status</th>
                                        <th>Payout Created At</th>
                                        <th>Payout Updated At</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <form id="changebulkForm" action="<?php echo site_url("admin/payouts") ?>" method="post">
                                    <tbody>
                                        <?php foreach ($referral_payouts as $referral_payout) : ?>
                                            <tr>
                                                <td><?php echo $referral_payout["r_p_id"] ?></td>
                                                <td><?php echo $referral_payout["r_p_code"] ?></td>
                                                <td><?php echo $referral_payout["username"] ?></td>
                                                <td><?php echo $referral_payout["r_p_amount_requested"] ?></td>
                                                <td><?php if ($referral_payout["r_p_status"] == 0) {
                                                        echo "Pending";
                                                    } elseif (
                                                        $referral_payout["r_p_status"] == 1
                                                    ) {
                                                        echo "Disapproved ";
                                                    }elseif (
                                                        $referral_payout["r_p_status"] == 2
                                                    ){
                                                        echo "Approved ";
                                                    } else {
                                                        echo "Rejected ";
                                                    }
                                                       
                                                      ?></td>
                                                <td><?php echo $referral_payout["r_p_requested_at"] ?></td>
                                                <td><?php echo $referral_payout["r_p_updated_at"] ?></td>


                                                <td class="service-block__action">
                                                    <div class="dropdown pull-right">
                                                        <button type="button" class="btn btn-primary btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Action</button>
                                                        <ul class="dropdown-menu">

                                                            <?php if ($referral_payout["r_p_status"] == 0) : ?>
                                                              
                                                                    <li><a href="<?= site_url("admin/payouts?approve=" . $referral_payout["r_p_id"]) ?>">Approve</a></li>
                                                                    <li><a href="<?= site_url("admin/payouts?disapprove=" . $referral_payout["r_p_id"]) ?>">Disapprove</a></li>
                                                                    <li><a href="<?= site_url("admin/payouts?reject=" . $referral_payout["r_p_id"]) ?>">Reject</a></li>

                                                             
                                                            <?php else : ?>

                                                                <li><a href="javascript:void(0)">No options to use</a></li>
                                                            <?php endif; ?>

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

