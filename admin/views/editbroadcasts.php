<?php include 'header.php'; ?>

          <div class="container container-md"> <div class="row"><div class="col-md-12">
  <div class="panel panel-default">
    <div class="panel-body">
  <center> <h3>Edit Broadcast</h3></center>

          <hr>
<form class="form" action="<?php echo site_url("admin/broadcasts/edit"); ?> " method="POST" >
<div class="form-group">
<label class="form-group__service-name">Title</label>
<input type="hidden" name="id" value="<?= $notifData['id']; ?>">
                <input type="text" class="form-control" name="title" value="<?= $notifData['title']; ?>" required>
              </div>
<div class="form-group">

<label class="form-group__service-name">Type</label>
<select class="form-control" name="broadcast_type">
<option value="info" <?php if($notifData['type']== "info"){ echo 'selected'; }?>>Info</option>
<option value="success" <?php if($notifData['type']== "success"){ echo 'selected'; }?>>Success</option>
<option value="error" <?php if($notifData['type']== "error"){ echo 'selected'; }?>>Error</option>
<option value="warning" <?php if($notifData['type']== "warning"){ echo 'selected'; }?>>Warning</option>
</select>
</div>
               <div class="form-group">
                        <label class="form-group__service-name">Action Link</label>
                        <input type="text" class="form-control" name="action_link" value="<?= $notifData['action_link']; ?>">
                </div>
                <div class="form-group">
                        <label class="form-group__service-name">Button Text (Optional)</label>
                        <input type="text" class="form-control" name="action_text" value="<?= $notifData['action_text']; ?>">
                </div>
                <div class="form-group">
                        <label class="form-group__service-name">Description</label>
         <textarea class="form-control" id="summernote" rows="5" name="description" placeholder="" required> <?= $notifData['description']; ?> </textarea>

                </div>

<div class="form-group">
                        <label class="form-group__service-name">Select Users</label>
                        <input type="radio" class="" name="isAllUser" value="0" <?php if ($notifData['isAllUser']==0) { echo 'checked'; } ?>> All Users</br>
                        <input type="radio" class="" name="isAllUser" value="1" <?php if ($notifData['isAllUser']==1) { echo 'checked'; } ?>> Logged-In User
                </div>
                <div class="form-group">
                        <label class="form-group__service-name">Expiry Date</label>
                        <input type="date" class="form-control" name="expiry_date" value="<?= $notifData['expiry_date']; ?>">
                </div>
                <div class="form-group">
                        <label class="form-group__service-name">Status</label>
                        <select class="form-control" name="status">
                                <option value="0" >Selected : <?php if($notifData['status']==0){echo "Inactive";}else{echo "Active";}  ?></option>
                            <option value="0" <?php if($notifData['status']==0){ echo 'selected'; } ?>>Inactive</option>
                            <option value="1" <?php if($notifData['status']==1){ echo 'selected'; } ?>>Active</option>
                        </select>    
                </div>
              <button type="submit" class="btn btn-primary">Update</button>  
         <a href="<?=site_url("admin/broadcasts")?>" class="btn btn-default">
         <span class="export-title">Go Back</span>
         </a>
        </form>
    
  


<?php include 'footer.php'; ?>
<script>
$('input#isAllPage').change(function() {
if ($('input#isAllPage').prop('checked')) {    
   $('div#allPages').hide();
}else{
    $('div#allPages').show();
}

});
</script>