<style>
   .label-id {

    border: 1px solid #ddd;

    background: 0 0;
    min-width: 30px;
    display: inline-block;
    padding: .2em .6em .3em;
    font-size: 75%;
    font-weight: 700;
    line-height: 1;
    text-align: center;
    white-space: nowrap;
    vertical-align: 1px;
    border-radius: .25em;
}

</style>
<div class="">
            <?php if($panel["panel_type"] != "Child" ): ?>

<div class="settings-header__table">

            <div class="col-md-5">
	<div class="settings-header__table">
		<button type="button" class="btn btn-default m-b" data-toggle="modal" data-target="#modalDiv" data-action="new_provider">Add new Provider</button>
	</div>

<?php endif; ?>

<table class="table providers_list" id="service-table">
    <thead>
    <tr>
<th>ID</th>
<th>Provider</th>
<th>Balance</th>
<th>Actions</th>

</tr>
</thead>
<tbody>

<?php foreach ($providersList as $provider) : ?>


<tr      data-provider-id="<?php echo $provider["id"]; ?>" id="<?php echo $provider["api_name"]; ?>"   class="list_item ">
<td><?php echo $provider["id"]; ?> </td>
	<td class="<?php if( $provider["status"] == 2 ): echo "grey "; endif; ?>" data-label="Service" class="table-service" data-filter-table-service-name="true" class="name"><div style="word-break: break-all;"><?php echo $provider["api_name"]; ?></div></td>

	<td>
<div style="width:70px;">
<?php



		$api_id = $provider["id"];
		$api_url = $provider["api_url"];

		$api_key = $provider["api_key"];

 if( $provider["status"] == "1" ): 
$smmapi   = new SMMApi();
$veri = $smmapi->action(array('key' =>$api_key,'action' =>'balance'),$api_url);

if($veri->currency == $settings["site_base_currency"]){
echo format_amount_string($settings["site_base_currency"],from_to(get_currencies_array("all"),$veri->currency,$settings["site_base_currency"],$veri->balance))."<br>";
}
if($veri->currency !== $settings["site_base_currency"]){

echo "≈ ".format_amount_string($settings["site_base_currency"],from_to(get_currencies_array("all"),$veri->currency,$settings["site_base_currency"],$veri->balance))."<br>";

}

echo "<div class='service-block__provider-value'>".str_replace("≈ ","",format_amount_string($veri->currency,$veri->balance))."</div>";



 if(!empty($veri->error)) : 
$update = $conn->prepare("UPDATE service_api SET status=:status WHERE id=:id ");
$update->execute(array("id"=>$api_id,"status"=> 2 ));

endif; 


else:

echo '<div class="tooltip5">  <span class="fas fa-info-circle"></span><span class="tooltiptext5">Balance info not available for that provider</span></div>'  ;
 
 endif; 
?>
</div>
</td>
<td>
<div style="width:60px;font-size:17px;">
<span style="color:#3A86FF;" data-toggle="modal" data-target="#modalDiv" data-action="edit_provider" data-id="<?= $provider["id"]?>"><i class="fas fa-edit"></i></span>

<?php if($panel["panel_type"] != "Child" ): ?>
<a style="color:#D90429;display:inline-block;" href="#" data-toggle="modal" data-target="#confirmChange" data-href="<?=site_url("admin/settings/providers/delete/".$provider["id"])?>">
<i class="fas fa-trash"></i>
</a>
<?php endif; ?>

<span data-toggle="modal" data-target="#modalDiv" data-action="capture_description" data-id="<?= $provider["id"]?>" style="display:inline-block;color:#FCA311;">
    <i class="fa fa-download" aria-hidden="true"></i>
</span>
</div>
</td>


	<input type="hidden" name="privder_changes" value="privder_changes">
<?php endforeach; ?>
</tbody>
		</table>
	</div>
</div>
</div>




<?php

function kontrol($api_url, $api_key)
{

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $api_url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);


	$_post = 	array(
		'key' => $api_key,
		'action' => 'balance',
	);
	if (is_array($_post)) {
		foreach ($_post as $name => $value) {
$_post[] = $name . '=' . urlencode($value);
		}
	}

	if (is_array($_post)) {
		curl_setopt($ch, CURLOPT_POSTFIELDS, join('&', $_post));
	}


	$result = curl_exec($ch);
	return $result;
	curl_close($ch);
}


?>

<div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
   <div class="modal-dialog modal-dialog-center" role="document">
      <div class="modal-content">
         <div class="modal-body text-center">
            <h4>Are you sure you want to Delete?</h4>
            <div align="center">
               <a class="btn btn-primary" href="" id="confirmYes">Yes</a>
               <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
            </div>
         </div>
      </div>
   </div>
</div>