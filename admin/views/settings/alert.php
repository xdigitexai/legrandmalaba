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

<label class="control-label">  Users Notifications</label>
<hr>
<div class="row">
          <div class="col-md-6 form-group">
            <label class="control-label">Welcome <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Sent to new users when their account is created.</span></div>  </label>
            <select class="form-control" name="welcomemail">
              <option value="2" <?= $settings["alert_welcomemail"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_welcomemail"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>

          <div class="col-md-6 form-group">
            <label class="control-label">API key changed <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Sent to users when their API key is changed</span></div> </label>
            <select class="form-control" name="apimail">
              <option value="2" <?= $settings["alert_apimail"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_apimail"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>

          <div class="col-md-6 form-group">
            <label class="control-label">New message <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Sent to users when they receive a new message.</span></div> </label>
            <select class="form-control" name="newmessage">
              <option value="2" <?= $settings["alert_newmessage"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_newmessage"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>
</div>
<hr>
<label class="control-label">  Admin Notifications</label> 
   <hr>
     <div class="row">
          <div class="col-md-6 form-group">
            <label class="control-label">API balance notification</label>
            <select class="form-control" name="alert_apibalance">
              <option value="2" <?= $settings["alert_apibalance"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_apibalance"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>

          <div class="col-md-6 form-group">
            <label class="control-label">New support ticket notification</label>
            <select class="form-control" name="alert_newticket">
              <option value="2" <?= $settings["alert_newticket"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_newticket"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>

          <div class="col-md-6 form-group">
            <label class="control-label">New manual orders <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Periodically sent to staff if new manual orders received.</span></div>  </label>
            <select class="form-control" name="alert_newmanuelservice">
              <option value="2" <?= $settings["alert_newmanuelservice"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_newmanuelservice"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>
          <div class="col-md-6 form-group">
            <label class="control-label">Fail orders <div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Periodically sent to staff if some orders got Fail status.</span></div> </label>
            <select class="form-control" name="orderfail">
              <option value="2" <?= $settings["alert_orderfail"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_orderfail"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>
          <div class="col-md-12 form-group">
            <label class="control-label">Service provider changed information</label>
            <select class="form-control" name="serviceapialert">
              <option value="2" <?= $settings["alert_serviceapialert"] == 2 ? "selected" : null; ?> >Enabled</option>
              <option value="1" <?= $settings["alert_serviceapialert"] == 1 ? "selected" : null; ?>>Disabled</option>
            </select>
          </div>
		 <div class="col-md-12 form-group">
            <label class="control-label">SMTP Email</label>
            <input type="text" class="form-control" name="admin_mail" value="<?=$settings["admin_mail"]?>">
			 
          </div>

      
        </div>
      <div class="row">
          <div class="form-group col-md-6">
            <label class="control-label">Email</label>
            <input type="text" class="form-control" name="smtp_user" value="<?=$settings["smtp_user"]?>">
          </div>
          <div class="form-group col-md-6">
            <label class="control-label">Email password</label>
            <input type="text" class="form-control" name="smtp_pass" value="<?=$settings["smtp_pass"]?>">
          </div>
          <div class="form-group col-md-6">
            <label class="control-label">SMTP Server</label>
            <input type="text" class="form-control" name="smtp_server" value="<?=$settings["smtp_server"]?>">
          </div>
          <div class="form-group col-md-3">
            <label class="control-label">SMTP Port</label>
            <input type="text" class="form-control" name="smtp_port" value="<?=$settings["smtp_port"]?>">
          </div>
          <div class="col-md-3 form-group">
            <label class="control-label">SMTP Protocol</label>
            <select class="form-control" name="smtp_protocol">
              <option value="0" <?= $settings["smtp_protocol"] == 0 ? "selected" : null; ?> >None</option>
              <option value="tls" <?= $settings["smtp_protocol"] == "tls" ? "selected" : null; ?>>TLS</option>
              <option value="ssl" <?= $settings["smtp_protocol"] == "ssl" ? "selected" : null; ?>>SSL</option>
            </select>
          </div>
        </div>
        <hr>
 <button type="submit" class="btn btn-primary w-100">Update Settings</button>
      
    
            </table>
        </div>
    </div>

</form>
</div>
  </div>
</div>