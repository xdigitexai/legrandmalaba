<?php include 'header.php'; ?>

<div class="container-fluid">
  <div class="">    
   <div  class="table-responsive">
  
    <table  class="table " id="dt">
<thead>
    <tr>
<th class="p-l">ID</th>
<th>User</th>
<th>Domain</th>
<th>Created At</th>
<th>Status</th>
<th>Actions</th>
</tr>
</thead>
    <tbody>
<?php foreach($payments as $payment): ?>
<tr>
    <td class="p-l"><?php echo $payment["id"] ?></td>
    <td><?php echo $payment["username"] ?></td>
    <td><?php echo $payment["domain"] ?></td>
    <td><?php echo $payment["created_on"]; ?></td>
    <td><?php echo $payment["child_panel_status"]; ?></td>
    <td>

<div class="dropdown pull-right">
<button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Actions <span class="caret"></span></button>
<ul class="dropdown-menu">
<li>
<a href="<?php echo site_url("admin/child-panels/".$payment["id"]."/activate");?>">Status To Active</a>
<a href="<?php echo site_url("admin/child-panels/".$payment["id"]."/suspend");?>">Status to Suspended</a>
</li>

</ul>
</div>
</td>
</tr>
<?php endforeach; ?>
    </tbody>
    </table>
</div>
    </div>
</div>


<?php include 'footer.php'; ?>
