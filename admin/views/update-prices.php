<?php include 'new-header.php'; ?>
<div class="container">
<div class="row">
<div class="col-md-6 col-12 offset-md-3 increase-decrease-form ">
<div class="card">
<div class="card-header">Update Prices</div>
<form action="admin/update-prices" method="POST">

<div class="card-body">

<div class="form-group">
<div class="mb-3">
<label for="special_pricing_service_type" class="form-label">Services Type</label>
<select name="service_type" id="special_pricing_service_type">
<option value="all_services">All Services</option>
<option value="seller_services">Seller Services</option>
<option value="manual_services">Manual Services</option>
</select>
</div></div>

<div style="display:none;" id="special-pricing-seller-select-div" class="form-group">
<div class="mb-3" >
 <label for="select-seller" class="form-label">Sellers</label>
<select class="fsb-ignore multiple" name="sellers[]" id="select-seller" multiple autocomplete="off" data-placeholder="Select a seller...">
<?=$providers_option;?>
</select></div>
</div>


<div class="form-group">
<label class="form-label">Profit Percentage</label>
<div class="input-group mb-3">
  <input type="number" name="profit-percent-value" id="profit-percent-value" class="form-control" placeholder="10" aria-label="Profit Percentage">
  <span class="input-group-text"><i class="bi bi-percent"></i></span>
</div></div>

<div class="form-group" id="action_type_div">
</div>


</div>
<div class="card-footer">
 <button type="submit" data-loading-text="Saving..." class="btn btn-primary">Save changes</button>
</div>
</form>
</div>
</div>

</div>
</div>
<?php include 'new-footer.php'; ?>


