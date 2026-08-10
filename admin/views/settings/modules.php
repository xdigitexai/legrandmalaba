<?php
if (route(3) == "ip") {
    $ipType = ($_POST['ip_type'] == 1 || $_POST['ip_type'] == 2) ? $_POST['ip_type'] : null;
    $ip = filter_var($_POST['ip'], FILTER_SANITIZE_STRING);
    if (!empty($ip) && $ip != obfuscate_provider_key($admin["ip"])) {
        $update = $conn->prepare("UPDATE admins SET ip_type = :ipType, ip = :ip");
        $update->execute(array("ipType" => $ipType, "ip" => $ip));
    } else {echo "Please provide a valid IPv6 address.";}}
$adminQuery = $conn->prepare("SELECT * FROM admins");
$adminQuery->execute();
$admin = $adminQuery->fetch(PDO::FETCH_ASSOC);
$inputValue = (isset($_POST['ip']) && route(3) == "ip") ? htmlspecialchars($_POST['ip']) : obfuscate_provider_key($admin["ip"]);
?>

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
       <div class="form-group">AI Configuration</label>
          <input type="text" class="form-control" name="openai_api_key" value="<?=$settings["openai_api_key"]?>">
        </div>
        <hr>

<?php
$google_login = json_decode($settings["google_login"], true);
if (!$google_login) {
  $google_login = ["purchased" => "0", "status" => "0"];
}
?>

<?php if ($google_login["purchased"] != "1") { ?>
<!-- LOCKED VIEW -->
<div id="google_login_card" data-addon="google_login" 
  class="google-lock-card">

  <div class="google-lock-content">
    <img src="/images/icons/google.png" 
         alt="Google Login" class="google-lock-logo">
    <div class="google-lock-text">
      <h5>Google Login Addon</h5>
      <p>Enable users to log in seamlessly with their Google accounts.</p>
      <span class="google-lock-price">₱ 800 • Limited Offer</span>
    </div>
  </div>

  <button 
    type="button" 
    class="google-unlock-btn"
    onclick="unlock_google_login()" 
    data-action="buy_addon" 
    data-addon="google_login">
    <i class="fas fa-unlock-alt mr-2"></i> Unlock Now
  </button>
</div>

<script>
function unlock_google_login(){
  let modal = $("#modalDiv");
  let modalContent = $("#modalDiv .modal-content");

  $.ajax({
    url: "admin/ajax_data/",
    method: "POST",
    dataType: "json",
    data: { action: "buy_addon", addon: "google_login" },
    beforeSend: function(){
      modalContent.html('<div class="p-4 text-center"><i class="fa fa-spinner fa-spin fa-2x"></i><p class="mt-3"></p></div>');
      modal.modal("show");
    },
    success: function(response){
      if(response.content){
        modalContent.html(response.content);
      } else {
        modalContent.html('<div class="p-4 text-danger text-center">Error loading addon.</div>');
      }
    },
    error: function(){
      modalContent.html('<div class="p-4 text-danger text-center">Connection error.</div>');
    }
  });
}

// When unlocked, refresh card to toggle version
$(document).on("addonUnlocked", function(){
  location.reload();
});
</script>

<style>
.google-lock-card {
  position: relative;
  border: 1px solid #e5e5e5;
  border-radius: 14px;
  padding: 25px;
  background: #fff;
  display: flex;
  align-items: center;
  justify-content: space-between;
  box-shadow: 0 6px 18px rgba(0,0,0,0.08);
  overflow: hidden;
  transition: all 0.3s ease;
  margin-bottom: 25px;
}

.google-lock-card:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.google-lock-content {
  display: flex;
  align-items: center;
  gap: 15px;
}

.google-lock-logo {
  width: 55px;
  height: 55px;
  border-radius: 12px;
  background: #fff;
  box-shadow: 0 0 8px rgba(66,133,244,0.2);
  padding: 8px;
}

.google-lock-text h5 {
  font-size: 17px;
  font-weight: 700;
  color: #202124;
  margin-bottom: 4px;
}

.google-lock-text p {
  margin: 0;
  font-size: 14px;
  color: #5f6368;
}

.google-lock-price {
  display: block;
  margin-top: 6px;
  font-weight: 600;
  color: #34a853;
}

.google-unlock-btn {
  background: linear-gradient(90deg, #34a853, #1e8e3e);
  color: #fff;
  border: none;
  border-radius: 10px;
  padding: 10px 22px;
  font-weight: 600;
  font-size: 14px;
  transition: all 0.2s ease;
  box-shadow: 0 4px 12px rgba(52,168,83,0.3);
}

.google-unlock-btn:hover {
  background: linear-gradient(90deg, #2e9440, #107c33);
  transform: translateY(-1px);
  box-shadow: 0 6px 16px rgba(52,168,83,0.4);
}
</style>

<?php } else { ?>
<!-- PURCHASED VIEW -->
<div class="settings-emails__block-body">
  <table class="table">
    <thead><tr><th>Name</th><th>Actions</th></tr></thead>
    <tbody>
      <tr>
        <td>
          <div class="font-weight-bold">Google Login</div>
          <div class="text-muted">Allow users to log in using Google.</div>
        </td>
        <td>
          <label class="switch">
            <input 
              type="checkbox" 
              class="switch-input addon" 
              data-addon="google_login"
              <?= $google_login["status"] == "1" ? "checked" : "";?>>
            <span class="switch-label" data-on="On" data-off="Off"></span>
            <span class="switch-handle"></span>
          </label>
        </td>
      </tr>
    </tbody>
  </table>
</div>

<script>
// Toggle ON/OFF handler
$(document).on("change", ".addon", function() {
  let addon  = $(this).data("addon");
  let status = $(this).is(":checked") ? 1 : 0;

  $.post("admin/ajax_data/", {
      action: "toggle_addon_status",
      addon: addon,
      status: status
  }, function(res){
      console.log(res);
  }, "json");
});
</script>
<?php } ?>

        <?php
$googleData = json_decode($settings["google_login"], true) ?: ["purchased" => "0", "status" => "0"];
$isLocked   = ($googleData["purchased"] == "0");
?>

<style>
.google-settings-wrapper {
  position: relative;
  border: 1px solid #e5e5e5;
  border-radius: 12px;
  padding: 25px;
  background: #fff;
  overflow: hidden;
  box-shadow: 0 4px 18px rgba(0,0,0,0.06);
}

/* Overlay */
.google-locked-overlay {
  position: absolute;
  inset: 0;
  background: rgba(255, 255, 255, 0.85);
  backdrop-filter: blur(4px);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 10;
  border-radius: 12px;
}

/* Lock Card */
.lock-card {
  text-align: center;
  background: rgba(255, 255, 255, 0.9);
  padding: 25px 30px;
  border-radius: 12px;
  box-shadow: 0 6px 20px rgba(0,0,0,0.12);
  animation: fadeIn 0.4s ease;
}

.lock-card i {
  font-size: 50px;
  color: #dc3545;
  margin-bottom: 15px;
  text-shadow: 0 0 8px rgba(220,53,69,0.4);
}

.lock-card h5 {
  font-weight: 600;
  color: #333;
  margin-bottom: 10px;
}

.lock-card p {
  color: #666;
  font-size: 14px;
  margin-bottom: 18px;
}

.lock-card button {
  background: linear-gradient(90deg, #dc3545, #ff5b5b);
  color: white;
  font-weight: 600;
  border: none;
  border-radius: 8px;
  padding: 10px 22px;
  transition: 0.2s;
}

.lock-card button:hover {
  background: linear-gradient(90deg, #c82333, #ff3d3d);
  transform: translateY(-1px);
}

@keyframes fadeIn {
  from {opacity: 0; transform: translateY(10px);}
  to {opacity: 1; transform: translateY(0);}
}
</style>

<div class="google-settings-wrapper">
  <?php if ($isLocked): ?>
    <div class="google-locked-overlay">
      <div class="lock-card">
        <i class="fas fa-lock"></i>
        <h5>Google Login Addon Locked</h5>
        <p>This feature requires activation. Unlock to enable Google authentication for your users.</p>
      </div>
    </div>
  <?php endif; ?>

  <div class="form-group">
    <label class="control-label">Google Client ID</label>
    <input type="text" class="form-control" name="googleclientid"
           value="<?=htmlspecialchars($settings["googleclientid"])?>"
           <?= $isLocked ? 'disabled' : '' ?>>
  </div>

  <div class="form-group">
    <label class="control-label">Google Client Secret</label>
    <input type="text" class="form-control" name="googleclientsecret"
           value="<?=htmlspecialchars($settings["googleclientsecret"])?>"
           <?= $isLocked ? 'disabled' : '' ?>>
  </div>
</div>
<hr>

        <div class="form-group">
          <label for="" class="control-label">Facebook Bot Token</label>
          <input type="text" class="form-control" name="fb_access_token" value="<?=$settings["fb_access_token"]?>">
        </div>
        <div class="form-group">  
          <label for="" class="control-label">Facebook Page ID</label>
          <input type="text" class="form-control" name="fb_page_id" value="<?=$settings["fb_page_id"]?>">
        </div>
        <hr>
  
   <div class="form-group">
          <label for="" class="control-label">Telegram Bot Token</label>
          <input type="text" class="form-control" name="tgbottokenuser" value="<?=$settings["tgbottokenuser"]?>">
        </div>
        <div class="form-group">  
          <label for="" class="control-label">Telegram Chat ID</label>
          <input type="text" class="form-control" name="tgchatiduser" value="<?=$settings["tgchatiduser"]?>">
        </div>
        <hr>
  
   <div class="form-group">
          <label for="" class="control-label">Telegram Bot Token</label>
          <input type="text" class="form-control" name="tgbottoken" value="<?=$settings["tgbottoken"]?>">
        </div>
        <div class="form-group">  
          <label for="" class="control-label">Telegram Chat ID</label>
          <input type="text" class="form-control" name="tgchatid" value="<?=$settings["tgchatid"]?>">
        </div>
        <hr>
  
  
<div class="form-group">
          <label for="" class="control-label">Affiliate System</label>
          <select class="form-control" name="affiliates_status">
         
          <option value="1"  <?= $settings["referral_status"] == 1 ? "selected" : null; ?>>Disabled</option>
          <option value="2" <?= $settings["referral_status"] == 2 ? "selected" : null; ?>>Enabled</option>
          
          </select>
        </div>
  <div class="form-group">
          <label for="" class="control-label">Commission rate, %</label>
          <input type="number" class="form-control" name="commision" value="<?=$settings["referral_commision"]?>">
        </div>
        <div class="form-group">  
          <label for="" class="control-label">Minimum payout</label>
          <input type="number" class="form-control" name="minimum" value="<?=$settings["referral_payout"]?>">
        </div>
        
<hr>
<div class="childpanels-settings">
<div class="form-group">
          <label for="" class="control-label">Child Panel Selling</label>
          <select class="form-control" name="selling">
         
<option value="1"  <?= $settings[""] == 1 ? "selected" : null; ?>>Disabled</option>
          <option value="2" <?= $settings["childpanel_selling"] == 2 ? "selected" : null; ?>>Enabled</option>
          
          </select>
        </div>
        

<div class="form-group">
<label for="" class="control-label">Child Panel Price</label>
<input type="text" class="form-control" name="price" value="<?=$settings["childpanel_price"]?>">
</div> 
<div style="padding:4px; background-color:lightgrey;border:1px solid #000; border-radius:4px;width:max-content;">
<small>Base Child Panel Price : 500</small></div>

</div>

<hr>


<div class="form-group">
          <label for="" class="control-label">Free Balance</label>
          <select class="form-control" name="freebalance">
         
                    <option value="1"  <?= $settings["freebalance"] == 1 ? "selected" : null; ?>>Disabled</option>
          <option value="2" <?= $settings["freebalance"] == 2 ? "selected" : null; ?>>Enabled</option>
          
          </select>
        </div>
<div class="form-group">
          <label for="" class="control-label">Free Amount</label>
          <input type="text" class="form-control" name="freeamount" value="<?=$settings["freeamount"]?>">
        </div> 
<hr>
<div class="form-group">
          <label for="" class="control-label">Video Promotion</label>
          <select class="form-control" name="promotion">
         
                    <option value="1"  <?= $settings["promotion"] == 1 ? "selected" : null; ?>>Disabled</option>
          <option value="2" <?= $settings["promotion"] == 2 ? "selected" : null; ?>>Enabled</option>
          
          </select>
        </div>

<div class="form-group">
          <label for="" class="control-label">Updates Logs</label>
          <select class="form-control" name="updates_show">
         
                    <option value="1"  <?= $general["updates_show"] == 1 ? "selected" : null; ?>>Disabled</option>
          <option value="2" <?= $general["updates_show"] == 2 ? "selected" : null; ?>>Enabled</option>
          
          </select>
        </div>


<div class="form-group">
          <label for="" class="control-label">Mass Order</label>
          <select class="form-control" name="massorder">
         
                    <option value="1"  <?= $general["massorder"] == 1 ? "selected" : null; ?>>Disabled</option>
          <option value="2" <?= $general["massorder"] == 2 ? "selected" : null; ?>>Enabled</option>
          
          </select>
        </div>


<hr>
        <center><button type="submit" class="btn btn-primary w-100">Save Changes</button></center>
      </form>
      
    </div>
  </div>
</div>