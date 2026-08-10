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
<h2>Fake Orders</h2><a href="<?php echo site_url("admin/settings/site_count/service_enable_disable");?>" style="float:right;margin-top:-45px;" class="btn btn-primary btn-sm"><?php if($settings["fake_order_service_enabled"] == 0){ echo "Enable Service";} else { echo  "Disable Service";}?></a>
<center>
<p><i>Use this settings to accelerate panel orders.</i></p></center>
<form class="form  <?php if($settings["fake_order_service_enabled"] == 0){ echo "disabledDiv"; }?>" action="" method="post">
<div class="form-group">
<label class="">Minimum number of fake orders</label>
<input class="form-control" type="number" name="min_count" value="<?php if(is_numeric($settings["fake_order_min"])){
echo $settings["fake_order_min"];}?>">
</div>
<div class="form-group">
<label class="">Maximum number of fake orders</label>
<input class="form-control" type="number" name="max_count" value="<?php if(is_numeric($settings["fake_order_max"])){
echo $settings["fake_order_max"];}?>">
</div>
<div class="alert alert-info">Leave empty to choose randomly.</div>
<div class="form-group">
<button class="btn btn-primary w-100" type="submit">Update Settings</button>
</div>
</form>
<div class="alert alert-info">
Note : When enabled, orders are incremented every 5 minutes.</div>
<hr><hr>
<div class="form-group">
<label class="">NEXT ORDER ID</label>
<input class="form-control" type="number" id="next_order_id_value" value="<?=$settings["panel_orders"] + 1?>">
<small class="text-muted">
Must be greater than <?=$settings["panel_orders"]?>.
</small>
</div>
<div class="form-group">
<button type="button" id="next_order_id_value_btn" class="btn btn-primary w-100">Submit</button>
</div>
<div class="alert alert-info">

Note : The above setting will create a fake order with the entered order ID. The next order ID will start from that entered order ID.<br>Example, ORDER ID : 2000<br>NEXT ORDER ID : 2001</div>


<hr><hr>

<label class="">Total Orders Pattern</label>

<p style="font-weight:bold;"><span>Total Orders Prefix</span>
<span style="float:right;" class="">Total Orders Suffix</span></p>
<div class="form-group">
<div class="input-group">
<?php 
$sff = json_decode($settings["panel_orders_pattern"],true);
$prefix = $sff["panel_orders_prefix"];
$suffix = $sff["panel_orders_suffix"];

?>
<input type="number" class="form-control" id="total_orders_prefix" value="<?=$prefix?>" placeholder="10">
<span class="input-group-addon"><?=$settings["panel_orders"]?></span>

<input type="number" class="form-control" id="total_orders_suffix" value="<?=$suffix?>" placeholder="10">
</div></div>
<div class="form-group">
<button type="button" id="set_total_orders_pattern" class="btn btn-primary w-100">Submit</button>
</div>


<div class="alert alert-info">
Note : Order ID won't be affected.</div>



</div>


</div>

</div></div>