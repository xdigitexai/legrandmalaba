<?php require 'header.php'; ?>

<div class="container-fluid">
    <div class="col-md-12">


        <ul class="nav nav-tabs">


<li class="pull-left">
<?php if (route(2) == "admins") : ?>
<div class="row">
<button  class="btn btn-primary w2-100" type="button" data-toggle="modal" data-target="#modalDiv" data-action="add_admin">Add Admin</button>
</div>
<?php endif; ?>


</li>
</ul>

<?php if (route(2) == "admins") : ?>

<div class="row">
<center>
    <h3><strong>Super Admin</strong></h3>
</center>
<div  style="overflow-x:scroll;border:1px solid #dbdbdb" class="">

<table  class="table">
    <thead>
        <tr>

<th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Status</th>


<th nowrap="">Created at</th>
<th nowrap="">Last Login</th>

            <th></th>
        </tr>
    </thead>



    <tbody>
        <?php foreach ($admins as $mainadmin): ?>

           

                <tr>

<td><?php echo $mainadmin["admin_name"] ?></td>
<td><?php echo $mainadmin["admin_email"] ?></td>
<td><?php echo $mainadmin["username"] ?></td>
<td><?php echo $mainadmin["client_type"] == 2 ? "Active"  : "Inactive" ?></td>

<td><?php echo $mainadmin["register_date"] ?></td>
<td><?php echo $mainadmin["login_date"] ?></td>


<td class="td-caret">
    <div class="dropdown pull-right">
        <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown" aria-expanded="true">
          Actions <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">

            <li><a href="#" data-toggle="modal" data-target="#modalDiv" data-action="edit_admin" data-id="<?php echo $mainadmin["admin_id"] ?>">Edit Account</a></li>


        </ul>
    </div>
</td>
                </tr>
        
   <?php    endforeach; ?>



    </tbody>
</table>
                </div>
<center>
    <h3><strong>Staff</strong></h3>
</center>
<div style="overflow-x:scroll;border:1px solid #dbdbdb">



<table class="table">
    <thead>
        <tr>

            <th>Name</th>
            <th>Email</th>
            <th>Username</th>
            <th>Status</th>

            <th nowrap="">Created at</th>
            <th nowrap="">Last Login</th>

            <th></th>
        </tr>
    </thead>



    <tbody>

        <?php foreach ($staffsData as $staff ) : ?>



                <tr>

<td><?php echo $staff["admin_name"] ?></td>
<td><?php echo $staff["admin_email"] ?></td>
<td><?php echo $staff["username"] ?></td>
<td><?php echo $staff["client_type"] == 2 ? "Active"  : "Inactive" ?></td>

<td><?php echo $staff["register_date"] ?></td>
<td><?php echo $staff["login_date"] ?></td>




                <td class="td-caret">
<div class="dropdown pull-right">
    <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown"
    data-boundary="window"
    aria-haspopup="true"
    aria-expanded="false">
      Actions <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">

        <li><a href="#" data-toggle="modal" data-target="#modalDiv" data-action="edit_admin" data-id="<?php echo $staff["admin_id"] ?>">Edit Account</a></li>
        <li><a href="#" data-toggle="modal" data-target="#modalDiv" data-action="edit_admin_password" data-id="<?php echo $staff["admin_id"] ?>">Change Password</a></li>
<li><a href="/admin/manager/admins/delete_staff/<?php echo $staff["admin_id"]?>">Delete Staff Member</a></li>
    </ul>
</div>
                </td>
            </tr>
    <?php    endforeach; ?>



    </tbody>
</table>
                </div>
            </div>

        <?php endif; ?>
        
    </div>
</div>
<div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
    <div class="modal-dialog modal-dialog-center" role="document">
        <div class="modal-content">
            <div class="modal-body text-center">
                <h4>Do you confirm the process?</h4>
                <div align="center">
<a class="btn btn-primary" href="" id="confirmYes">Confirm</a>
<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>


<?php require 'footer.php'; ?>