<style>
/* 3D Card Container */
.card {
    background: #fefefe;
    border-radius: 16px;
    padding: 30px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.12), 0 6px 10px rgba(0,0,0,0.08);
    transition: all 0.3s ease;
}

.card:hover {
    box-shadow: 0 12px 40px rgba(0,0,0,0.18), 0 8px 20px rgba(0,0,0,0.12);
}

/* Labels */
.control-label {
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

/* Inputs & Selects 3D style */
.form-control, .form-select {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 12px 15px;
    background: #fdfdfd;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.03), inset -2px -2px 5px rgba(255,255,255,0.8);
    transition: all 0.3s ease;
}

.form-control:focus, .form-select:focus {
    border-color: #007bff;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.05), inset -2px -2px 5px rgba(255,255,255,0.85),
                0 0 10px rgba(0,123,255,0.3);
    outline: none;
}

/* Buttons 3D */
.btn-primary {
    background: linear-gradient(145deg, #4e9cff, #187bff);
    border: none;
    border-radius: 12px;
    color: #fff;
    font-weight: 600;
    padding: 14px 0;
    box-shadow: 0 6px 15px rgba(0,123,255,0.4);
    transition: all 0.3s ease;
}

.btn-primary:hover {
    background: linear-gradient(145deg, #187bff, #4e9cff);
    box-shadow: 0 10px 20px rgba(0,123,255,0.5);
}

/* Alerts / Section Containers */
.alert {
    background: #f6f8fc;
    border-radius: 12px;
    padding: 20px;
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.03), inset -2px -2px 5px rgba(255,255,255,0.9);
    margin-bottom: 20px;
}

/* Image Preview 3D style */
.setting-block__image {
    position: relative;
    display: inline-block;
    margin-left: 10px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 2px 2px 12px rgba(0,0,0,0.1), -2px -2px 12px rgba(255,255,255,0.6);
}

.setting-block__image img {
    border-radius: 12px;
}

/* Tooltip 3D */
.tooltip5 {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.tooltip5 .tooltiptext5 {
    visibility: hidden;
    width: 200px;
    background-color: #333;
    color: #fff;
    text-align: center;
    border-radius: 8px;
    padding: 6px 10px;
    position: absolute;
    z-index: 1;
    bottom: 130%;
    left: 50%;
    transform: translateX(-50%);
    opacity: 0;
    transition: all 0.3s ease;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

.tooltip5:hover .tooltiptext5 {
    visibility: visible;
    opacity: 1;
}

/* Divider */
hr {
    border: 0;
    height: 1px;
    background: #e0e0e0;
    margin: 30px 0;
}

/* Textarea 3D */
textarea.form-control {
    border-radius: 12px;
    border: 1px solid #ddd;
    padding: 12px;
    background: #fdfdfd;
    box-shadow: inset 2px 2px 6px rgba(0,0,0,0.03), inset -2px -2px 6px rgba(255,255,255,0.8);
    transition: all 0.3s ease;
}

textarea.form-control:focus {
    border-color: #007bff;
    box-shadow: inset 2px 2px 6px rgba(0,0,0,0.05), inset -2px -2px 6px rgba(255,255,255,0.85),
                0 0 10px rgba(0,123,255,0.3);
    outline: none;
}
</style>

  <div class="col-md-8">
  <div class="panel panel-default">
    <div class="panel-body">
    
      <form action="" method="post" enctype="multipart/form-data">
 
<div class="alert" >
        <div class="form-group">
        
          <div class="row">
            <div class="col-md-10">
              <label for="preferenceLogo" class="control-label">Website Logo</label>
              <input type="file" name="logo" id="preferenceLogo">
            </div>
            <div class="col-md-2">
              <?php if( $settings["site_logo"] ):  ?>
                <div class="setting-block__image">
                      <img class="img-thumbnail" src="<?=$settings["site_logo"]?>">
                    <div class="setting-block__image-remove">
                      <a href="" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/settings/general/delete-logo")?>"><span class="fa fa-remove"></span></a>
                    </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="form-group">
          <div class="row">
            <div class="col-md-11">
              <label for="preferenceFavicon" class="control-label">Website Favicon</label>
              <input type="file" name="favicon" id="preferenceFavicon">
            </div>
            <div class="col-md-1">
              <?php if( $settings["favicon"] ):  ?>
                <div class="setting-block__image">
                    <img class="img-thumbnail" src="<?=$settings["favicon"]?>">
                    <div class="setting-block__image-remove">
                      <a href="" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/settings/general/delete-favicon")?>"><span class="fa fa-remove"></span></a>
                    </div>
                </div>
              <?php endif; ?>
            </div>
          </div>
        </div>
          <hr>
      
        <div class="form-group">
    <label class="control-label">Maintenance Configuration</label>
    <select class="form-control" name="site_maintenance">
        <option value="2" <?= $settings["site_maintenance"] == 2 ? "selected" : ""; ?>>Inactive</option>
        <option value="1" <?= $settings["site_maintenance"] == 1 ? "selected" : ""; ?>>Active</option>
    </select>
</div>

<hr>

<div class="form-group">
    <label class="control-label">Website Name</label>
    <input type="text" class="form-control" name="name" value="<?= $settings["site_name"]; ?>" placeholder="Enter panel name">
</div>

<div class="form-group">
    <label class="control-label">Website Owner</label>
    <input type="text" class="form-control" name="userr" value="<?= $settings["site_userr"]; ?>" placeholder="Owner name">
</div>

<div class="form-group">
    <label class="control-label">Youtube Video Tutorial URL</label>
    <input type="text" class="form-control" name="video" value="<?= $settings["site_video"]; ?>" placeholder="Video URL">
</div>

<div class="form-group">
    <label class="control-label">Website Developer</label>
    <input type="text" class="form-control" name="userrr" value="<?= $settings["site_userrr"]; ?>" placeholder="Developer username">
</div>

<div class="form-group">
    <label class="control-label">Website Marquee External</label>
    <input type="text" class="form-control" name="marqueen" value="<?= $settings["site_marqueen"]; ?>" placeholder="Marquee text">
</div>

<div class="form-group">
    <label class="control-label">Website Marquee internal</label>
    <input type="text" class="form-control" name="marqueens" value="<?= $settings["site_marqueens"]; ?>" placeholder="Internal marquee text">
</div>

</div>
<?php 
/*
<hr>

<div style="background-color:rgba(227,18,76);color:#fff;" class="alert">
<div class="form-group">
<div class="input-group">
<label for="" class="control-label">INR RATE FOR 1 USD</label>
<input type="text" name="dolar" id="inr_rate" class="form-control" value="">
<span class="input-group-btn">
<button style="margin-bottom:-25px;" class="btn btn-default" id="update_inr_rate" type="button"><i class="fa fa-refresh" aria-hidden="true"></i></button>
   </span>
   </div>
<div style="background-color:rgba(255,255,255,0.2);margin-top:4px;" class="alert help block">
<small><i>The INR exchange rate is updated automatically. You can also update / edit manually.</i></small>
</div></div>


<div class="form-group">
<label class="control-label">Rates Rounding   <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">When Sync and import</span></div> 
            </label>
          <select class="form-control" name="currency_format">
            <option value="0" <?= $general["currency_format"] == 0 ? "selected" : null; ?> >Ones (1)</option>
            <option value="2" <?= $general["currency_format"] == 2 ? "selected" : null; ?>>Hundreds (1.12)</option>
<option value="3" <?= $general["currency_format"] == 3 ? "selected" : null; ?> >Thousands (1.111)</option>
            <option value="4" <?= $general["currency_format"] == 4 ? "selected" : null; ?>>Ten Thousands (1.1111)</option>

          </select> 
          </div></div>*/?>
	<hr>
<div  class="alert">
        <div class="row">	
          <div class="form-group col-md-4">
            <?php 
            if($settings["resetpass_page"] == "2"){
                $respass_active = "selected";
            }else{
                $respass_passive = "selected";
            } ?>  
            <label class="control-label">Reset Password</label>
            <select class="form-control" name="resetpass">
              <option value="2" <?= $respass_active ?> >Enabled</option>
              <option value="1" <?= $respass_passive ?>>Disabled</option>
            </select>
          </div>

          <div class="form-group col-md-4">
            <?php 
            if($settings["resetpass_sms"] == "2"){
                $ressms_active = "selected";
            }else{
                $ressms_passive = "selected";
            } ?>  
            <label class="control-label">Reset Using SMS</label>
            <select class="form-control" name="resetsms">
              <option value="2" <?= $ressms_active ?> >Enabled</option>
              <option value="1" <?= $ressms_passive ?>>Disabled</option>
            </select>
          </div>
          <div class="form-group col-md-4">
            <?php 
            if($settings["resetpass_email"] == "2"){
                $resemail_active = "selected";
            }else{
                $resemail_passive = "selected";
            } ?>
            <label class="control-label">Reset Using Email</label>
            <select class="form-control" name="resetmail">
              <option value="2" <?= $resemail_active ?> >Enabled</option>
              <option value="1" <?= $resemail_passive ?>>Disabled</option>
            </select>
          </div>
        </div></div>
        <hr>
<div class="alert" >
        <div class="form-group">
            <?php 
            if($settings["ticket_system"] == "1"){
                $ticket_active = "selected";
            }else{
                $ticket_passive = "selected";
            } ?>
          <label class="control-label">Ticket system</label>
          <select class="form-control" name="ticket_system">
            <option value="1" <?= $ticket_active ?> >Enabled</option>
            <option value="2" <?= $ticket_passive ?>>Disabled</option>
          </select>
        </div>
<div class="form-group">
          <label class="control-label">Max Pending Tickets per user</label>
          <select class="form-control" name="tickets_per_user">
            <option value="1" <?= $settings["tickets_per_user"] == 1 ? "selected" : null; ?> >1</option>
            <option value="2" <?= $settings["tickets_per_user"] == 2 ? "selected" : null; ?>>2</option>
<option value="3" <?= $settings["tickets_per_user"] == 3 ? "selected" : null; ?>>3</option>
<option value="4" <?= $settings["tickets_per_user"] == 4 ? "selected" : null; ?> >4</option>
            <option value="5" <?= $settings["tickets_per_user"] == 5 ? "selected" : null; ?>>5</option>
<option value="6" <?= $settings["tickets_per_user"] == 6 ? "selected" : null; ?>>6</option>
<option value="7" <?= $settings["tickets_per_user"] == 7 ? "selected" : null; ?> >7</option>
            <option value="8" <?= $settings["tickets_per_user"] == 8 ? "selected" : null; ?>>8</option>
<option value="9" <?= $settings["tickets_per_user"] == 9 ? "selected" : null; ?>>9</option>
<option value="10" <?= $settings["tickets_per_user"] == 10 ? "selected" : null; ?> >10</option>
            <option value="9999999999" <?= $settings["tickets_per_user"] == 9999999999 ? "selected" : null; ?>>Unlimited</option>

          </select>
        </div></div>

<hr>
<div class="alert" >
        <div class="form-group">
            <?php 
            if($settings["register_page"] == "2"){
                $reg_active = "selected";
            }else{
                $reg_passive = "selected";
            } ?>
            <div class="form-group field-editgeneralform-skype_field required">
<label class="control-label" for="editgeneralform-skype_field">Analytics Dashboard <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Analytics service on the New Order</span></div></label>
          <select class="form-control" name="analytics_dashboard">
            <option value="1" <?= $settings["analytics_dashboard"] == 1 ? "selected" : null; ?> >Enabled</option>
            <option value="2" <?= $settings["analytics_dashboard"] == 2 ? "selected" : null; ?>>Disabled</option>
          </select>
          </div>
            <div class="form-group field-editgeneralform-skype_field required">
<label class="control-label" for="editgeneralform-skype_field">Service Dashboard <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Service on the New Order</span></div></label>
          <select class="form-control" name="service_dashboard">
            <option value="1" <?= $settings["service_dashboard"] == 1 ? "selected" : null; ?> >Enabled</option>
            <option value="2" <?= $settings["service_dashboard"] == 2 ? "selected" : null; ?>>Disabled</option>
          </select>
          </div>
            <div class="form-group field-editgeneralform-skype_field required">
<label class="control-label" for="editgeneralform-skype_field">Video <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Video on the New Order</span></div></label>
          <select class="form-control" name="video_feilds">
            <option value="1" <?= $settings["video_feilds"] == 1 ? "selected" : null; ?> >Enabled</option>
            <option value="2" <?= $settings["video_feilds"] == 2 ? "selected" : null; ?>>Disabled</option>
          </select>
          </div>
<div class="form-group field-editgeneralform-skype_field required" >
          <label class="control-label" for="editgeneralform-registration_page">Signup page <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Allows Users to register</span></div></label>

          <select class="form-control"  name="registration_page">
            <option value="2" <?= $reg_active ?> >Enabled</option>
            <option value="1" <?= $reg_passive ?>>Disabled</option>
          </select>
        </div></div>
<div class="form-group field-editgeneralform-skype_field required">
<label class="control-label" for="editgeneralform-skype_field">Name fields <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Name field on the Signup page</span></div></label>
          <select class="form-control" name="name_fileds">
            <option value="1" <?= $settings["name_fileds"] == 1 ? "selected" : null; ?> >Enabled</option>
            <option value="2" <?= $settings["name_fileds"] == 2 ? "selected" : null; ?>>Disabled</option>
          </select>
          </div>
<div class="form-group field-editgeneralform-skype_field required">
<label class="control-label" for="editgeneralform-skype_field">Skype fields <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Skype field on the Signup page</span></div></label>
          <select class="form-control" name="skype_feilds">
            <option value="1" <?= $settings["skype_feilds"] == 1 ? "selected" : null; ?> >Enabled</option>
            <option value="2" <?= $settings["skype_feilds"] == 2 ? "selected" : null; ?>>Disabled</option>
          </select>
          </div>
<div class="form-group field-editgeneralform-skype_field required">
<label class="control-label" for="editgeneralform-skype_field">Email Confirmation <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">(Enables mandatory email confirmation for the user after signing up)</span></div> </label>
          <select class="form-control" name="email_confirmation">
            <option value="1" <?= $settings["email_confirmation"] == 1 ? "selected" : null; ?> >Enabled</option>
            <option value="2" <?= $settings["email_confirmation"] == 2 ? "selected" : null; ?>>Disabled</option>
          </select>
          </div>
            <div class="form-group ">
                <label class="control-label">Transfer funds percentage <span class="fa fa-percent" data-toggle="tooltip" data-placement="top"></span></label>
                <input type="number" value="<?= $settings["fundstransfer_fees"]; ?>" class="form-control" name="fundstransfer_fees">
            </div> 
<div class="form-group">
          <label for="" class="control-label">Resend link max<h6>(Recommended 2)</h6></label>
          <input type="text" class="form-control" name="resend_max" value="<?=$settings["resend_max"]?>">
        </div>
        <div class="form-group">
            <?php 
            if($settings["service_list"] == "2"){
                $servlist_active = "selected";
            }else {
                $servlist_passive = "selected";
            } ?>
          <label class="control-label">Service List</label>
          <select class="form-control" name="service_list">
            <option value="2" <?= $servlist_active ?> >Active for everyone</option>
            <option value="1" <?= $servlist_passive ?>>Active for only users</option>
          </select>
        </div>
                <div class="form-group">

            <?php 

            if($settings["services_average_time"] == "1"){
                $avg_time_active = "selected";
            }else {
                $avg_time_passive = "selected";
            } ?>
          <label class="control-label">Average time</label>
          <select class="form-control" name="services_average_time">
            <option value="1" <?= $avg_time_active ?> >Enabled</option>
            <option value="0" <?= $avg_time_passive ?>>Disabled</option>
          </select>
        </div>
</div>
                  <hr>
<div class="alert" >
        <div class="form-group">
          <label class="control-label">Header codes</label>
          <textarea class="form-control" rows="7" name="custom_header" placeholder='<style type="text/css">...</style>'><?=$settings["custom_header"]?></textarea>
        </div>
        <div class="form-group">
          <label>Footer codes</label>
          <textarea class="form-control" rows="7" name="custom_footer" placeholder='<script>...</script>'><?=$settings["custom_footer"]?></textarea>
        </div></div>
		<hr>
                    
        <button type="submit" class="btn btn-primary w-100">Update Settings</button>
      </form>
    </div>
  </div>
</div>
<div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
 <div class="modal-dialog modal-dialog-center" role="document">
   <div class="modal-content">
     <div class="modal-body text-center">
       <h4>Are you sure?</h4>
       <div align="center">
         <a class="btn btn-primary" href="" id="confirmYes">Yes</a>
         <button type="button" class="btn btn-default" data-dismiss="modal">NO</button>
       </div>
     </div>
   </div>
 </div>
