<?php include 'header.php'; ?>

 <div class="container container-md"> <div class="row"><div class="col-md-12">
  <div class="panel panel-default">
    <div class="panel-body">
  <center> <h3>Create Broadcast</h3></center>

<hr>
<form class="form" action="<?php echo site_url("admin/broadcasts/new"); ?> " method="POST" >


<div class="form-group">
<label class="form-group__service-name">Title</label>
<input type="text" class="form-control" name="title" value="" required>
</div>


<div class="form-group">

<label class="form-group__service-name">Type</label>
<select class="form-control" name="broadcast_type">
<option value="info" selected>Info</option>
<option value="success">Success</option>
<option value="error">Error</option>
<option value="warning">Warning</option>
</select>
</div>


<div class="form-group">
<label class="form-group__service-name">Button Link (Optional)</label>
<input type="text" class="form-control" name="action_link" value="">
</div>

<div class="form-group">
<label class="form-group__service-name">Button Text (Optional)</label>
<input type="text" class="form-control" name="action_text" value="">
</div>

<div class="form-group">
<label class="form-group__service-name">Description</label>
<textarea class="form-control" id="summernote" rows="5" name="description" placeholder=""></textarea>

</div>


<div class="form-group">
<label class="form-group__service-name">Select Users</label>
<input type="radio" class="" name="isAllUser" value="0"> All Users</br>
<input type="radio" class="" name="isAllUser" value="1"> Logged-In User
</div>

<div class="form-group">
<label class="form-group__service-name">Expiry Date</label>
<input type="date" class="form-control" name="expiry_date" value="">
</div>
<div class="form-group">

<label class="form-group__service-name">Status</label>
<select class="form-control" name="status">
<option value="1" selected>Active</option>
<option value="0">Inactive</option>
</select>
</div>

<button type="submit" class="btn btn-primary">Submit</button>  <a href="<?=site_url("admin/broadcasts")?>" class="btn btn-default">
<span class="export-title">Go Back</span></a>
</form>
</div>
</div>

</div>
<?php include 'footer.php'; ?>