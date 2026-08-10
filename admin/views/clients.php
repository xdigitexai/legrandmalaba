<?php include 'header.php'; ?>
<style>
.container-fluid {
    width: 100%;
    padding: 10px 20px; /* reduced padding */
    background-color: #f8f9fa;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}
.nav-tabs {
    margin-bottom: 10px; /* reduce bottom spacing */
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.nav-tabs .btn-default {
    padding: 6px 12px;
    font-size: 13px;
}
.input-group {
    margin-bottom: 10px; /* tighter spacing */
}

.input-group .form-control {
    padding: 6px 10px;
    height: 36px;
}

.input-group .btn-default {
    padding: 6px 10px;
    height: 36px;
}
.clients-table {
    overflow-x: auto;
    border-radius: 8px;
    border: 1px solid #e0e0e0;
    box-shadow: none; 
    background-color: #ffffff;
    padding: 5px; 
}
.clients-table table {
    width: 100%;
    border-collapse: collapse;
}

.clients-table th, 
.clients-table td {
    padding: 8px 10px;
    text-align: left;
    vertical-align: middle;
}

.clients-table thead th {
    background-color: #f1f3f5;
    font-weight: 600;
    border-bottom: 1px solid #dee2e6;
}

.clients-table tbody tr {
    transition: background 0.2s ease;
}

.clients-table tbody tr:hover {
    background-color: #f8f9fa;
}
.btn-default, .btn-primary {
    padding: 5px 10px;
    font-size: 13px;
}
.pagination li a {
    padding: 5px 8px;
    font-size: 13px;
    margin: 1px;
}
</style>
<div class="container-fluid p-3">
    <ul class="nav nav-tabs">
        <li class="p-b m-r"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="new_user">Add user</button></li>
        <li class="p-b m-r"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="export_user">Backup users</button></li>
        <li class="p-b m-r"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="alert_user">Send Notification</button></li>
        <li class="p-b m-r"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="all_numbers">Contact Information</button></li>
        <li class="p-b m-r"><button class="btn btn-default" type="button" data-toggle="modal" data-target="#modalDiv" data-action="details">Details</button></li>
        <li class="p-b m-r"><button class="custom_method btn btn-default" data-title="Remove all Fake Users?" data-message="Are you sure? This action can't be reversed?" data-url="admin/clients/delete_fake_users">Remove Fake Users</button></li>
    </ul>
    <form class="form-inline" action="" method="get" enctype="multipart/form-data">
        <div class="input-group">
            <input type="text" name="search" class="form-control" value="<?=$search_word?>" placeholder="Search">
            <span class="input-group-btn search-select-wrap">
                <select class="form-control search-select" name="search_type">
                    <option value="username" <?php if( $search_where == "username" ): echo 'selected'; endif; ?>>Username</option>
                    <option value="name" <?php if( $search_where == "name" ): echo 'selected'; endif; ?>>Name</option>
                    <option value="email" <?php if( $search_where == "email" ): echo 'selected'; endif; ?>>Email</option>
                    <option value="telephone" <?php if( $search_where == "telephone" ): echo 'selected'; endif; ?>>Phone No.</option>
                </select>
                <button type="submit" class="btn btn-default"><span class="fa fa-search" aria-hidden="true"></span></button>
            </span>
        </div>
    </form>

    <div class="clients-table">
        <table class="table">
            <thead>
                <tr>
                    <th class="column-id">ID</th>
                    <th width="10%">Username</th>
                    <th>Email</th>
                    <th>Balance</th>
                    <th>Spent</th>
                    <th>Orders</th>
                    <th>Services Discount</th>
                    <th>Special Pricing</th>
                    <th nowrap="">Registered Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($clients as $client ): ?>
                    <tr class="<?php if( $client["client_type"] == 1 ): echo "grey"; endif; ?>">
                        <td><?php echo $client["client_id"] ?></td>
<td>
    <?php echo $client["username"] ?>
    <a href="<?= site_url('admin/clients/view/' . $client['client_id']) ?>" class="signin-link">
        <i class="fa fa-right-to-bracket"></i>
    </a>
</td>

<style>
.signin-link i {
    color: #3b3b7b;
    transition: all 0.3s ease;
}
.signin-link:hover i {
    color: #007bff; /* blue on hover */
    transform: scale(1.2); /* slight zoom effect */
}
</style>
                        <td>
                            <?php echo $client["email"] ?>
                            <?php if ($client["email_type"] == 2):
                                echo '<div class="tooltip5"><i style="color:green;" class="fas fa-check-circle"></i><span class="tooltiptext5">Email Is Verified</span></div>';
                            else:
                                echo '<div class="tooltip5"><i style="color:red;" class="far fa-times-circle"></i><span class="tooltiptext5">Email is Not Verified</span></div>';
                            endif; ?>
                        </td>
                        <td>
                            <div style="width:85px;"><?php echo format_amount_string($settings["site_base_currency"],$client["balance"]); ?></div>
                        </td>
                        <td>
                            <div style="width:85px;"><?php echo format_amount_string($settings["site_base_currency"],$client["spent"]); ?></div>
                        </td>
                        <td><?php echo countRow(["table"=>"orders","where"=>["client_id"=>$client["client_id"]] ]) ?></td>
                        <td>
                            <button type="button" class="btn btn-default btn-xs disabled" style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-id="<?php echo $client["client_id"] ?>" data-action="set_discount_percentage">Discount (<?php echo $client ["discount_percentage"]; ?>%)</button>
                        </td>
                        <td>
                            <button type="button" class="btn btn-default btn-xs"><a class="nav-link" href="<?php echo site_url("admin/special-pricing/".$client["client_id"]."");?>">Special Pricing <span style="margin-left:4px; border-radius:4px;" class="badge badge-info"><?php echo countRow(["table"=>"clients_price","where"=>["client_id"=>$client["client_id"]] ]) ?></span></a></button>
                        </td>
                        <td><?php echo date('jS M Y \a\t g:ia',strtotime($client["register_date"])); ?></td>
                        <td class="td-caret">
                            <div class="dropdown pull-right">
                                <button type="button" class="btn btn-primary btn-xs dropdown-toggle" data-toggle="dropdown">Action</button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dcs-pointer" data-toggle="modal" data-target="#modalDiv" data-action="edit_user" data-id="<?=$client["client_id"]?>">Edit User</a>
                                    </li>
                                    <li>
                                        <a class="dcs-pointer" data-toggle="modal" data-target="#modalDiv" data-action="pass_user" data-id="<?=$client["client_id"]?>">Change Password</a>
                                    </li>
                                    <li>
                                        <a class="dcs-pointer" data-toggle="modal" data-target="#modalDiv" data-action="secret_user" data-id="<?=$client["client_id"]?>">Edit Categories</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo site_url("admin/clients/change_apikey/".$client["client_id"]) ?>">Set New API Key</a>
                                    </li>
                                    <?php if( $client["client_type"] == 1 ): $type = "active"; else: $type = "deactive"; endif; ?>
                                    <li>
                                        <a href="<?php echo site_url("admin/clients/".$type."/".$client["client_id"]) ?>"><?php if( $client["client_type"] == 1 ): echo "Activate"; else: echo "Deactivate"; endif; ?> Account</a>
                                    </li>
                                    <li>
                                        <a href="<?php echo site_url("admin/clients/del_price/".$client["client_id"]) ?>">Reset Special Pricing</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if( $paginationArr["count"] > 1 ): ?>
        <div class="row">
            <div class="col-sm-8">
                <nav>
                    <ul class="pagination">
                        <?php if( $paginationArr["current"] != 1 ): ?>
                            <li class="prev"><a href="<?php echo site_url("admin/clients/1".$search_link) ?>">&laquo;</a></li>
                            <li class="prev"><a href="<?php echo site_url("admin/clients/".$paginationArr["previous"].$search_link) ?>">&lsaquo;</a></li>
                        <?php endif; ?>
                        <?php
                        for ($page=1; $page<=$pageCount ; $page++):
                            if( $page >= ($paginationArr['current']-9) and $page <= ($paginationArr['current']+9) ):
                        ?>
                            <li class="<?php if( $page == $paginationArr["current"] ): echo "active"; endif; ?>"><a href="<?php echo site_url("admin/clients/".$page.$search_link) ?>"><?=$page?></a></li>
                        <?php endif; endfor; ?>
                        <?php if( $paginationArr["current"] != $paginationArr["count"] ): ?>
                            <li class="next"><a href="<?php echo site_url("admin/clients/".$paginationArr["next"].$search_link) ?>" data-page="1">&rsaquo;</a></li>
                            <li class="next"><a href="<?php echo site_url("admin/clients/".$paginationArr["count"].$search_link) ?>" data-page="1">&raquo;</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>
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