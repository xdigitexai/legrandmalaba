<?php include 'header.php'; ?>
<div class="container">
  <div class="row">
    <div class="col-md-8 col-md-offset-2">
		 <div class="panel panel-default">
          <div class="panel-body">
      <form action="admin/account" method="post" enctype="multipart/form-data">
     
          <div class="form-group">
            <label for="charge" class="control-label">Current Password</label>
            <input type="password" class="form-control" value="" name="current_password">
          </div>

          <div class="form-group">
            <label for="charge" class="control-label">New Password</label>
            <input type="password" class="form-control" value="" name="password">
          </div>

          <div class="form-group">
            <label for="charge" class="control-label">Password again</label>
            <input type="password" class="form-control" value="" name="confirm_password">
          </div>
          <hr>
          <button type="submit" class="btn btn-primary w-100">Change Password</button>
        </form>
      </div>

      </div>
</div>

    </div>
  </div>
</div>

<?php include 'footer.php'; ?>
