<?php include 'header.php';?>


<style>
.highlight {
    background-color: yellow;
    font-weight: bold;
    padding: 0 2px;
}
.status-text {
    display: inline-block;
    font-size: 10px;
    font-weight: bold;
    color: #28a745;
    margin-left: 5px;
    vertical-align: middle;
    text-transform: uppercase;
    transform: scale(1); /* Initial state */

    /* Animation for scaling */
    animation: text-pop 1.2s infinite alternate;
}

@keyframes text-pop {
    0% { transform: scale(1); }
    50% { transform: scale(1.1); } /* Slightly larger in the middle of the animation */
    100% { transform: scale(1); }
}
</style>
<div class="container-fluid">
    <ul class="nav nav-tabs nav-tabs__service">
       
        <li class="p-b"><button class="btn btn-default" data-toggle="modal" data-target="#modalDiv" data-action="new_service">Add Service</button></li>
         <li class="p-b"><button class="btn btn-default m-l" data-toggle="modal" data-target="#modalDiv" data-action="new_category">Create Category</button></li>
        <li class="p-b"><button class="btn btn-default m-l" data-toggle="modal" data-target="#modalDiv" data-action="new_subscriptions">Add Subscription</button></li>
        <li class="p-b"><button class="btn btn-default m-l" data-toggle="modal" data-target="#modalDiv" data-action="sync_descriptions_modal"><span class="fas fa-sync-alt"></span> Sync Service Descriptions</button></li>
        <li class="p-b">
    <button onclick="quick_import()" class="btn btn-default m-l" data-toggle="modal" data-target="#modalDiv" data-action="quick_import"><span class="fas fa-plus"></span> Quick Import</button>
</li>
<script>function quick_import(){let t=document.querySelector(".modal-lg");window.innerWidth>=992&&(t.style.width="500px",setTimeout(()=>t.style.width="",6e3))}</script>
        


<li class="m-l p-b">
    <div class="dropdown">
        <button type="button" class="btn btn-default m-l" data-toggle="dropdown">
            Actions <span class="caret"></span>
        </button>
        <ul class="dropdown-menu">
        
<li style="width:100%" class="more-action pull-left"><a class=""  href="<?= site_url('admin/api-services') ?>"> <i class="fas fa-plus-circle"></i> Import Services <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"><a class=""  href="<?= site_url('admin/description') ?>"><i class="fa-solid fa-cloud-download"></i> Generate Service Marketing <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"><a class=""  href="<?= site_url('admin/bulk') ?>"><i class="fa-solid fa-pen-to-square"></i> Bulk Service Editor <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"><a class="custom_method"  data-title="Remove all empty categories?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/delete_all_empty_cat"> <i class="fa-solid fa-trash"></i> Delete empty categories <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"><a class="custom_method"  data-title="Remove all categories?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/delete_all_cat"><i class="fa-solid fa-trash"></i> Delete All categories <span class="status-text">New</span></a></li>
<li style="width:100%;border-top:2px solid #dc3545;margin-top:6px;padding-top:6px" class="more-action pull-left">
  <a class="custom_method"
     data-title="⚠️ HARD RESET — Delete ALL Services & Categories?"
     data-message="This will PERMANENTLY delete all services and categories and reset IDs to 1. Do this BEFORE importing fresh services. Cannot be undone!"
     data-url="admin/services/hard_reset_all"
     style="color:#dc3545;font-weight:bold">
    <i class="fa-solid fa-skull-crossbones"></i> Hard Reset (Delete All + Reset IDs) <span class="status-text" style="color:#dc3545">DANGER</span>
  </a>
</li>
<li style="width:100%" class="more-action pull-left">
    <a class="custom_method"
       data-title="Remove all services?"
       data-message="Are you sure? This action can't be reversed and will empty the services table!"
       data-url="admin/services/delete_all_services"> <i class="fa-solid fa-trash"></i> Delete All Services <span class="status-text">New</span>
    </a>
</li>
<li style="width:100%" class="more-action pull-left"><a  class="custom_method" data-title="Are you want to fatch icons?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/auto_cat_icon"><i class="fa-solid fa-crown"></i> Black Normal Icons <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"><a class=" custom_method" data-title="Are you want to fatch icons?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/auto_cat_icon_color"><i class="fa-solid fa-image"></i> Colourful Icons <span class="status-text">New</span></a></li>
<li class="more-action pull-left"><a class="custom_method" data-title="Rate sort by low to high?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/rate_low"><i class="fa-solid fa-sort"></i> Rate (low to high) <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"><a class="custom_method" data-title="Rate sort by high to low?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/rate_high"><i class="fa-solid fa-sort"></i> Rate (high to low) <span class="status-text">New</span></a></li>
<li class="more-action pull-left"><a class="custom_method" data-title="Reset to original order?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/reset_order"><i class="fa-solid fa-arrow-down-1-9"></i> Reset to original Service line <span class="status-text">New</span></a></li>
<li style="width:100%" class="more-action pull-left"> <a class=" "  data-toggle="modal" data-target="#modalDiv" data-action="ato_avg_time"><i class="fa-solid fa-clock"></i> Auto Average time <span class="status-text">New</span></a> </li>
<li style="width:100%" class="more-action pull-left"> <a class=" " data-toggle="modal" data-target="#modalDiv" data-action="sync_services"><i class="fa-solid fa-rotate"></i> Sync Services Rate <span class="status-text">New</span></a></li> 
<li style="width:100%" class="more-action pull-left"> <a class="custom_method" data-title="Sync services from provider?" data-message="Are you sure? This action can't be reversed?" data-url="admin/services/sync_button_provider"><i class="fa-solid fa-rotate"></i> Sync Service status <span class="status-text">New</span></a>
  <li style="width:100%" class="more-action pull-left">
    <a href="#" data-toggle="modal" data-target="#modalDiv" data-action="bulk_import_from_provider">
        <i class="fa-solid fa-cloud-download"></i> Bulk Import All Services <span class="status-text">NEW</span>
    </a>
</li>


 </ul>
    </div>
</li> 








               
                        
                        
                        <li class="m-l mln-10"><div class="dropdown">
<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"><?= $to ?><span style="margin-left:8px;"class="caret"></span></button>
<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
<li><a href="<?= site_url("admin/services") ?>?getservices=5">5 Per Page</a></li>
<li><a href="<?= site_url("admin/services") ?>?getservices=10">10 Per Page</a></li>
<li><a href="<?= site_url("admin/services") ?>?getservices=20">20 Per Page</a></li>
<li><a href="<?= site_url("admin/services") ?>?getservices=50">50 Per Page</a></li></ul></div></li>
                        
<li class="pull-right">
<a class="btn btn-primary" href="<?= site_url('admin/api-services') ?>"><i class="fas fa-plus-circle"></i> Import Services</a>
        </li>




<li class="pull-right">
<style>#priceServices {padding: 6px 24px 6px 25px;}</style>
<div class="form-inline">
<label for="service-search-input" class="service-search__icon"></label>
<input class="form-control" placeholder="Search" id="priceServices" type="text" value="">
</div>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>$("#priceServices").on("keyup",function(){var e=$(this).val().toUpperCase();$('[data-id^="service-"]').each(function(){var t=$(this).find(".service-block__id").text(),i=$(this).attr("data-service"),s=$(this).attr("data-category");i.toUpperCase(),i.toUpperCase().indexOf(e)>-1||t.toUpperCase().indexOf(e)>-1?($(this).show(),$(this).attr("id","serviceshow"+s)):($(this).hide(),$(this).attr("id","servicehide"))}),$('[id^="Servicecategory-"]').each(function(){var e=$(this).attr("data-id");0==$("#servicesTableList > tbody > tr#serviceshow"+e).length?$("#"+e).hide():$("#"+e).show()})});</script>
</li>


    </ul>
    <ul></ul>
    <div class="services-table">
        <div class="sticker-head">
            <table class="service-block__header" id="sticker">
                <thead>
<th class="checkAll-th service-block__checker null">
    <div class="checkAll-holder">
        <input type="checkbox" id="checkAll">
        <input type="hidden" id="checkAllText" value="order">
    </div>
    <div class="action-block">
        <ul class="action-list">
            <li><span class="countOrders"></span> Services Selected</li>
            <li>
                <div class="dropdown">
<button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">batch operations<span class="caret"></span></button>
<ul class="dropdown-menu">
    <li>
        <a class="bulkorder" data-type="active">Enable Selected Services</a>
        <a class="bulkorder" data-type="deactive">Disable Selected Services</a>
        <a class="bulkorder" data-type="secret">Make Selected Serivces Secret</a>
        <a class="dcs-pointer assign-category" data-toggle="modal" data-target="#modalDiv" data-action="assign_categoryc" data-id="<?= implode(',', array_column($services, 'service_id')) ?>" >Assign category</a>
        <a class="bulkorder" data-type="desecret">Remove Selected from Secret Serivces</a>
        <a class="bulkorder" data-type="del_price">Delete Selected Services Custom Pricing</a>
        <a class="bulkorder" data-type="del_service">Delete Selected Services</a>
        <a class="bulkorder" data-type="refill-active">Refill Enable Selected Services</a>
        <a class="bulkorder" data-type="refill-inactive">Refill Disable Selected Services</a>
        <a class="bulkorder" data-type="cancel-active">Cancel Enable Selected Services</a>
        <a class="bulkorder" data-type="cancel-inactive">Cancel Disable Selected Services</a>
    </li>
</ul>
                </div>
            </li>
        </ul>
    </div>
</th>
<th class="service-block__id">ID</th>
<th class="service-block__service">Service</th>

<th>Service Type</th>
<th class="service-block__minorder">Refill</th>
<th class="service-block__minorder">Cancel</th>
<th class="service-block__provider">
    <div class="dropdown">
                <button class="btn btn-th btn-default dropdown-toggle" data-active="<?=$_GET["id"]?>" type="button" id="serviceList" data-href="admin/orders/provider" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                  Provider
                  <span class="caret"></span>
                </button>
                <ul class="dropdown-menu" aria-labelledby="dropdownMenu1" id="serviceListContent" style="max-height: 275px; overflow:hidden; overflow-y: scroll">
                </ul>
              </div>
    
    </th>
<th class="service-block__rate">Price</th>
<th class="service-block__minorder">Min</th>
<th class="service-block__minorder">Max</th>
<th class="service-block__visibility">
    
   <div class="dropdown">
<button class="btn btn-default dropdown-toggle" type="button" id="dropdownMenu1" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true"> Status <span style="margin-left:8px;"class="caret"></span></button>
<ul class="dropdown-menu" aria-labelledby="dropdownMenu1">
<li class="<?php if( !$_GET["getstatus"] ): echo "active"; endif; ?>"><a href="<?= site_url("admin/services") ?>">All (<?php echo countRow(["table"=>"services","where"=>["service_deleted"=>0]]) ?>)</a></li>
<li class="<?php if( $_GET["getstatus"] == "2" ): echo "active"; endif; ?>"><a href="<?= site_url("admin/services") ?>?getstatus=2">Enabled (<?php echo countRow(["table"=>"services","where"=>["service_deleted"=>0, "service_type"=>2]]) ?>)</a></li>
<li class="<?php if( $_GET["getstatus"] == "1" ): echo "active"; endif; ?>"><a href="<?= site_url("admin/services") ?>?getstatus=1">Disabled (<?php echo countRow(["table"=>"services","where"=>["service_deleted"=>0, "service_type"=>1]]) ?>)</a></li>
</ul></div>
    
</th>
<th class="service-block__action text-right"><span id="allServices" class="service-block__hide-all fa fa-compress"></span></th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="service-block__body">
        <div class="service-block__body-scroll">
            <div style="width: 100%; height: 0px;"></div>


<form action="<?php echo site_url("admin/services/multi-action") ?>" method="post" id="changebulkForm">
<div style="" class="category-sortable">
<?php $c = 0; foreach ($serviceList as $category => $services): $c++; ?>
<div class="categories" data-id="<?=$services[0]["category_id"] ?>">
    <div class="<?php if ($services[0]["category_type"] == 1): echo 'grey'; endif; ?>  service-block__category ">
        <div class="service-block__category-title" class="categorySortable" data-category="<?=$category ?>" id="category-<?=$c ?>">
            <div class="service-block__drag handle">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
<title>Drag-Handle</title>
<path d="M7 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm6-8c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm0 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2z"></path>
                </svg>
            </div>
<?php if ($services[0]["category_secret"] == 1): echo '<small data-toggle="tooltip" data-placement="top" title="" data-original-title="gizli kategori"><i class="fa fa-lock"></i></small> '; endif; 

if($services[0]["category_type"] == 2){
echo '<span data-post="category_id='.$services[0]["category_id"].'" class="category-visibility category-visible"></span>';
} 
if($services[0]["category_type"] == 1){
echo '<span data-post="category_id='.$services[0]["category_id"].'" class="category-visibility category-invisible"></span>';
} 

$category_icon_array = json_decode($services[0]["category_icon"],true);

$category_icon_type = $category_icon_array["icon_type"];

if($category_icon_type == "image"){

$icon = "<img style=\"margin-right:10px;\" src=\"".$images[$category_icon_array["image_id"]][0]["link"]."\" class=\"img-responsive btn-group-vertical\">";
} elseif($category_icon_type == "icon"){

$icon = "<i style=\"margin-right:10px;font-size:18px;\" class=\"".$category_icon_array["icon_class"]."\" aria-hidden=\"true\"></i>";
} else {
    $icon = "";
    
}


echo '<span class="category-name">'.$icon.$category.'</span>'; ?>
<span style="margin-left:10px;margin-right:10px;font-weight:bold;">|</span>
<div class="dropdown-inline">
                                    <div class="dropdown btn-group">
                                        <button style="padding:3px 10px;" type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Options <span class="caret"></span></button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dcs-pointer" data-toggle="modal" data-target="#modalDiv" data-action="edit_category" data-id="<?= $services[0]["category_id"] ?>">Edit</a></li>
                                            <?php if ($services[0]["category_type"] == 1) : $type = "category-active";
                                            else : $type = "category-deactive";
                                            endif; ?>
                                            <li><a href="<?php echo site_url("admin/services/" . $type . "/" . $services[0]["category_id"]) ?>"><?php if ($services[0]["category_type"] == 1) : echo "Enable";
                                                                                                                                                else : echo "Disable";
                                                                                                                                                endif; ?></a></li>

                                            <li><a class="custom_method" data-title="Delete all services from category?" data-message="Are you sure want to delete all services from <?= $category ?>" data-url="<?= site_url("admin/services/del_category_services/" . $services[0]["category_id"]) ?>" data-action="del_category">Delete Services</a></li>
                                            <li><a  id="selectservices" data-id=" <?= $services[0]["category_id"] ?> " >Select Services</a></li>
                                            <li><a href="<?php echo site_url("admin/services/del_category/".$services[0]["category_id"]) ?>" data-action="del_category">Delete Category</a></li>
                                            <li><a class="custom_method" data-title="Sort this category?" data-message="Are you sure want to sort the category <?= $category ?> by price in ascending order" data-url="<?= site_url("admin/services/sort_by_price_asc/" . $services[0]["category_id"]) ?>" data-action="sort_by_price_asc" data-id=" <?= $services[0]["category_id"] ?> ">Sort by Price (Ascending)</a></li>

                                            <li><a class="custom_method" data-title="Sort this category?" data-message="Are you sure want to sort the category <?= $category ?> by price in descending order" data-url="<?= site_url("admin/services/sort_by_price_desc/" . $services[0]["category_id"]) ?>" data-action="sort_by_price_desc" data-id=" <?= $services[0]["category_id"] ?> ">Sort by Price (Descending)</a></li>
                                        </ul>
                                    </div>
                                </div>
<?php if (!empty($services[0]["service_id"])): ?>

            <div class="service-block__collapse-block">
                <div id="collapedAdd-<?=$c ?>" class="service-block__collapse-button" data-category="category-<?=$c ?>"></div>
            </div>
            <?php endif; ?>
        </div>
        <div class="collapse in">
            <div class="service-block__packages">
                <table id="servicesTableList" class="Servicecategory-<?=$c ?>">
<tbody class="service-sortable">
    <div class="serviceSortable" id="Servicecategory-<?=$c ?>" data-id="category-<?=$c ?>">
        <?php for ($i = 0; $i < count($services); $i++): 
      if($services[$i]["service_deleted"] == 0):
        $api_detail = json_decode($services[$i]["api_detail"], true); ?>
        <tr id="serviceshowcategory-<?=$c ?>" class="ui-state-default <?php if ($services[$i]["service_type"] == 1): echo "grey"; endif; ?>" data-category="category-<?=$c ?>" data-id="service-<?php echo $services[$i]["service_id"] ?>" data-service="<?php echo $services[$i]["service_name"] ?>">
<?php if (!empty($services[0]["service_id"])): ?>
<td class="service-block__checker">
<?php if ($services[$i]["api_servicetype"] == 1): echo '<div class="service-block__danger"></div>'; endif; ?>
<span></span>
<div class="service-block__checkbox">
    <div class="service-block__drag handle">
        <svg>
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                <title>Drag-Handle</title>
                <path d="M7 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm6-8c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm0 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2z"></path>
            </svg>
        </svg>
    </div>
    <input type="checkbox" class="selectOrder" name="service[<?php echo $services[$i]["service_id"] ?>]" value="1" style="border:1px solid #fff">
    
</div>
</td>

<td class="service-block__id"><?php echo $services[$i]["service_id"] ?></td>
<td class="service-block__service"><?php if ($services[$i]["service_secret"] == 1): echo '<small data-toggle="tooltip" data-placement="top" title="" data-original-title="Secret Service"><i class="fa fa-lock"></i></small> '; endif; echo $services[$i]["service_name"]; ?></td>
<td width="10%"><?php echo servicePackageType($services[$i]["service_package"]); ?><?php if ($services[$i]["time"] != "Not enough data"): ?><div class="tooltip5">
&nbsp;<i class="fas fa-clock"></i><span class="tooltiptext5"><?php echo $services[$i]["time"]; ?> </span>
</div>
<?php endif; ?><?php if ($services[$i]["show_refill"] == "true"): echo '<div  class="tooltip5">&nbsp;<i class="fas fa-sync"></i></span><span class="tooltiptext5" >Refill Button Enabled</span></div>'; endif; ?>
<?php if ($services[$i]["cancelbutton"] == 1): echo '<div  class="tooltip5">&nbsp;<i  class="fas fa-ban"></i></span><span class="tooltiptext5" >Cancel Button Enabled</span></div>'; endif; ?>

</td>
<?php if ($services[$i]["show_refill"] == "true"): $type = "refill-deactive"; else : $type = "refill-active"; endif; ?>

<td class="service-block__minorder"> <a href="<?php echo site_url("admin/services/".$type."/".$services[$i]["service_id"]) ?>"> <?php if ($services[$i]["show_refill"] == "false"): echo "Off"; else : echo "On"; endif; ?></a></td>

<?php if ($services[$i]["cancelbutton"] == 2): $type = "cancelbutton-active"; else : $type = "cancelbutton-deactive"; endif; ?>
<td class="service-block__minorder"> <a href="<?php echo site_url("admin/services/".$type."/".$services[$i]["service_id"]) ?>"> <?php if ($services[$i]["cancelbutton"] == "2"): echo "Off"; else : echo "On"; endif; ?></a></td>


<td class="service-block__provider"><?php if ($services[$i]["service_api"] != 0): echo $services[$i]["api_name"]." <span class=\"badge badge-secondary\">".$services[$i]["currency"]."</span><br><span class=\"label label-api\">".$services[$i]["api_service"]."</span>"; else : echo "Manual"; endif; ?></td>

<td class="service-block__rate">
<?php
$api_price = $api_detail["rate"];
?>
<div style="width:100px;<?php if (!$api_detail["rate"]): echo "Empty"; elseif ($services[$i]["service_api"] != 0 && from_to(get_currencies_array("all"), $settings["site_base_currency"], "INR", $services[$i]["service_price"]) > from_to(get_currencies_array("enabled"), $services[$i]["currency"], "INR", $api_price)):
    echo "color: #38E54D;";
    elseif ($services[$i]["service_api"] != 0 && from_to(get_currencies_array("all"), $settings["site_base_currency"], "INR", $services[$i]["service_price"]) < from_to(get_currencies_array("all"), $services[$i]["currency"], "INR", $api_price)):
echo "color: #D2001A;";elseif($services[$i]["service_api"] != 0 && from_to(get_currencies_array("all"), $settings["site_base_currency"], "INR", $services[$i]["service_price"]) == from_to(get_currencies_array("all"), $services[$i]["currency"], "INR", $api_price)):
echo "color: #FFB200;";
endif;?>">
    <?php if ($settings["site_base_currency"] !== $services[$i]["currency"]) {
        echo "≈ ".format_amount_string($settings["site_base_currency"], $services[$i]["service_price"]);
    } elseif ($settings["site_base_currency"] == $services[$i]["currency"]) {
        echo format_amount_string($settings["site_base_currency"], $services[$i]["service_price"]);
    }
    ?>
</div>
<div class="service-block__provider-value">
    <?php if ($services[$i]["service_api"] != 0 && $api_detail["rate"]):
    if ($settings["site_base_currency"] !== $services[$i]["currency"]) {
        echo "≈ ".format_amount_string($settings["site_base_currency"], from_to(get_currencies_array("all"), $services[$i]["currency"], $settings["site_base_currency"], $api_detail["rate"]));
    } elseif ($settings["site_base_currency"] == $services[$i]["currency"]) {

        echo format_amount_string($settings["site_base_currency"], from_to(get_currencies_array("all"), $services[$i]["currency"], $settings["site_base_currency"], $api_detail["rate"]));

    }
    endif; ?>
</div>
</td>
<td class="service-block__minorder">
<div>
    <?php echo $services[$i]["service_min"] ?>
</div>
<?php if ($services[$i]["service_api"] != 0): echo '<div class="service-block__provider-value">'.$api_detail["min"].'</div>'; endif; ?>
</td>
<td class="service-block__minorder">
<div>
    <?php echo $services[$i]["service_max"] ?>
</div>
<?php if ($services[$i]["service_api"] != 0): echo '<div class="service-block__provider-value">'.$api_detail["max"].'</div>'; endif; ?>
</td>
<td class="service-block__visibility"><?php if ($services[$i]["service_type"] == 1): echo "Disabled"; else : echo "Enabled"; endif; ?> <?php if ($services[$i]["api_servicetype"] == 1): echo '<span class="text-danger" title="Service provider removed service"><span class="fa fa-exclamation-circle"></span></span>'; endif; ?> </td>
<td class="service-block__action">
<div class="dropdown pull-right">
    <button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Options <span class="caret"></span></button>
    <ul class="dropdown-menu">
    <li><a style="cursor:pointer;" onclick="sendToTelegram(<?=$services[$i]['service_id']?>)">
            <i class="fab fa-telegram"></i> Send to Telegram
        </a></li>
       <li>
    <a style="cursor:pointer;" onclick="sendToFacebook(<?=$services[$i]['service_id']?>)">
        <i class="fab fa-facebook"></i> Post to Facebook
    </a>
</li>
        <li><a style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-action="edit_service" data-id="<?=$services[$i]["service_id"] ?>">Edit Service</a></li>
        <li><a style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-action="edit_service_name" data-id="<?=$services[$i]["service_id"] ?>">Edit Service Name</a></li>
        <li><a style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-action="edit_description" data-id="<?=$services[$i]["service_id"] ?>">Edit Description</a></li>
        <li><a style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-action="edit_time" data-id="<?=$services[$i]["service_id"] ?>">Edit Average Time</a></li>
        <?php if ($services[$i]["service_type"] == 1): $type = "service-active"; else : $type = "service-deactive"; endif; ?>
        <li><a href="<?php echo site_url("admin/services/".$type."/".$services[$i]["service_id"]) ?>">Service <?php if ($services[$i]["service_type"] == 1): echo "Activate"; else : echo "Deactivate"; endif; ?></a></li>

        <?php if ($services[$i]["show_refill"] == "true"): $type = "refill-deactive"; else : $type = "refill-active"; endif; ?>
        <li><a href="<?php echo site_url("admin/services/".$type."/".$services[$i]["service_id"]) ?>">Refill <?php if ($services[$i]["show_refill"] == "true"): echo "Deactivate"; else : echo "Activate"; endif; ?></a></li>

        <?php if ($services[$i]["cancelbutton"] == 2): $type = "cancelbutton-active"; else : $type = "cancelbutton-deactive"; endif; ?>
        <li><a href="<?php echo site_url("admin/services/".$type."/".$services[$i]["service_id"]) ?>">Cancel Button <?php if ($services[$i]["cancelbutton"] == 1): echo "Deactivate"; else : echo "Activate"; endif; ?></a></li>


        <li><a href="<?php echo site_url("admin/services/delete/".$services[$i]["service_id"]) ?>">Delete Service</a></li>
    </ul>
</div>
</td>
<?php endif; ?>
        </tr>
        <?php 
        endif;
        endfor; ?>
    </div>
</tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>

<?php
$services = $conn->prepare("SELECT * FROM services LEFT JOIN service_api ON service_api.id = services.service_api WHERE services.category_id=:c_id ORDER BY services.service_line ASC ");
$services -> execute(array("c_id" => 0));
$services = $services->fetchAll(PDO::FETCH_ASSOC);
if ($services):
?>
<div class="service-block__category ">
    <div class="service-block__category-title" class="categorySortable" data-category="notcategory" id="category-0">
        Uncategorized
        <div class="service-block__collapse-block">
            <div id="collapedAdd-0" class="service-block__collapse-button" data-category="category-0"></div>
        </div>
    </div>
    <div class="collapse in">
        <div class="service-block__packages">
            <table id="servicesTableList" class="Servicecategory-0">
                <tbody class="service-sortable">
<div class="serviceSortable" id="Servicecategory-0" data-id="category-0">
    <?php foreach ($services as $service): $api_detail = json_decode($service["api_detail"], true); ?>
    <tr id="serviceshowcategory-0" class="ui-state-default <?php if ($service["service_type"] == 1): echo "grey"; endif; ?>" data-category="category-0" data-id="service-<?php echo $service["service_id"] ?>" data-service="<?php echo mb_convert_encoding($service["service_name"], "UTF-8", "UTF-8") ?>">
        <td class="service-block__checker">
<!-- <div class="service-block__danger"></div> //Servis diğer sitede pasifse burayı aktif et-->
<span></span>
<div class="service-block__checkbox">
<div class="service-block__drag handle">
    <svg>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
            <title>Drag-Handle</title>
            <path d="M7 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm6-8c1.104 0 2-.896 2-2s-.896-2-2-2-2 .896-2 2 .896 2 2 2zm0 2c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2zm0 6c-1.104 0-2 .896-2 2s.896 2 2 2 2-.896 2-2-.896-2-2-2z"></path>
        </svg>
    </svg>
</div>
<input type="checkbox" class="selectOrder" name="service[<?php echo $service["service_id"] ?>]" value="1" style="border:1px solid #fff">
</div>
        </td>
        <td class="service-block__id"><?php echo $service["service_id"] ?></td>
        <td class="service-block__service"><?php if (mb_convert_encoding($service["service_secret"], "UTF-8", "UTF-8") == 1): echo '<small data-toggle="tooltip" data-placement="top" title="" data-original-title="Secret service"><i class="fa fa-lock"></i></small> '; endif; echo mb_convert_encoding($service["service_name"], "UTF-8", "UTF-8"); ?></td>
        <td class="service-block__type" nowrap=""><?php echo servicePackageType($service["service_package"]); ?></td>
        <td class="service-block__provider"><?php if ($service["service_api"] != 0): echo $service["api_name"]; else : echo "Manual"; endif; ?></td>
        <td class="service-block__rate">
<?php
if ($api_detail["currency"] == "USD"):
$api_price = $api_detail["rate"];
endif;
?>
<div style="<?php if ($service["service_api"] != 0 && $service["service_price"] > $api_price): echo "color: green"; elseif ($service["service_api"] != 0 && $service["service_price"] < $api_price): echo "color: red"; endif ?>">
<?php echo $service["service_price"] ?>
</div>
<?php if ($service["service_api"] != 0): echo '<div class="service-block__provider-value"><i class="fa fa-'.strtolower($api_detail["currency"]).'"></i> '.priceFormat($api_detail["rate"]).'</div>'; endif; ?>
        </td>
        <td class="service-block__minorder">
<div>
<?php echo $service["service_min"] ?>
</div>
<?php if ($service["service_api"] != 0): echo '<div class="service-block__provider-value">'.$api_detail["min"].'</div>'; endif; ?>
        </td>
        <td class="service-block__minorder">
<div>
<?php echo $service["service_max"] ?>
</div>
<?php if ($service["service_api"] != 0): echo '<div class="service-block__provider-value">'.$api_detail["max"].'</div>'; endif; ?>
        </td>
        <td class="service-block__visibility"><?php if ($service["service_type"] == 1): echo "Deactive"; else : echo "Active"; endif; ?> </td>
        <td class="service-block__action">
<div class="dropdown pull-right">
<button type="button" class="btn btn-default btn-xs dropdown-toggle btn-xs-caret" data-toggle="dropdown">Options <span class="caret"></span></button>
<ul class="dropdown-menu">
    <li><a style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-action="edit_service" data-id="<?=$service["service_id"] ?>">Edit Service</a></li>
    <li><a style="cursor:pointer;" data-toggle="modal" data-target="#modalDiv" data-action="edit_description" data-id="<?=$service["service_id"] ?>">Edit Description</a></li>
    <?php if ($service["service_type"] == 1): $type = "service-active"; else : $type = "service-deactive"; endif; ?>
    <li><a href="<?php echo site_url("admin/services/".$type."/".$service["service_id"]) ?>">Service <?php if ($service["service_type"] == 1): echo "Enable"; else : echo "Disable"; endif; ?></a></li>
    <li><a href="<?php echo site_url("admin/services/del_price/".$service["service_id"]) ?>">Delete Price</a></li>
    <li><a href="<?php echo site_url("admin/services/delete/".$services[$i]["service_id"]) ?>">Delete Service</a></li>
</ul>
</div>
        </td>
    </tr>
    <?php endforeach; ?>
</div>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php endif; ?>
</div>

<input type="hidden" name="bulkStatus" id="bulkStatus" value="-1">
            </form>
        </div>
    </div>
    
</div>



<?php if( $paginationArr["count"] > 1 ): ?>
     <div class="row">
        <div class="col-sm-8">
  <nav>
 <ul class="pagination">
 <?php if( $paginationArr["current"] != 1 ): ?>
  <li class="prev"><a href="<?php echo site_url("admin/services/1".$search_link) ?>">&laquo;</a></li>
  <li class="prev"><a href="<?php echo site_url("admin/services/".$paginationArr["previous"].$search_link) ?>">&lsaquo;</a></li>
  <?php
      endif;
      for ($page=1; $page<=$pageCount; $page++):
        if( $page >= ($paginationArr['current']-9) and $page <= ($paginationArr['current']+9) ):
  ?>
  <li class="<?php if( $page == $paginationArr["current"] ): echo "active"; endif; ?> "><a href="<?php echo site_url("admin/services/".$page.$search_link) ?>"><?=$page?></a></li>
  <?php endif; endfor;
        if( $paginationArr["current"] != $paginationArr["count"] ):
  ?>
  <li class="next"><a href="<?php echo site_url("admin/services/".$paginationArr["next"].$search_link) ?>" data-page="1">&rsaquo;</a></li>
  <li class="next"><a href="<?php echo site_url("admin/services/".$paginationArr["count"].$search_link) ?>" data-page="1">&raquo;</a></li> 
  <?php endif; ?>
 </ul>
  </nav>
        </div>
     </div>
   <?php endif; ?>









    <div class="modal modal-center fade" id="confirmChange" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" data-backdrop="static">
        <div class="modal-dialog modal-dialog-center" role="document">
            <div class="modal-content">
                <div class="modal-body text-center">
<h4>Are you sure you want to update the status?</h4>
<div align="center">
    <a class="btn btn-primary" href="" id="confirmYes">Yes</a>
    <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
</div>
                </div>
            </div>
        </div>
    </div>


    <?php include 'footer.php'; ?>
<script>
$(document).ready(function() {
    $('.assign-category').click(function() {
        var selectedCheckboxes = $('input.selectOrder:checked');
        var selectedServiceIds = [];
        selectedCheckboxes.each(function() {
            var name = $(this).attr('name');
            var value = $(this).val();
            var serviceId = name.match(/\[(\d+)\]/)[1]; 
            selectedServiceIds.push(serviceId);
        });
        console.log(selectedServiceIds);
        $.ajax({
            url: 'ajax_data.php',
            type: 'POST',
            data: { service_ids: selectedServiceIds },
            success: function(response) {
            },
            error: function(xhr, status, error) {
            }
        });
    });
});
</script>

    <script>
    $("#selectservices").click(function () {
    $('.selectOrder').not(this).prop('checked', true);
   var count = $('.selectOrder').filter(':checked').length;
   $('.countOrders').html(count);
   if( count > 0 ){
     $('.checkAll-th').addClass("show-action-menu");
   }else{
     $('.checkAll-th').removeClass("show-action-menu");
   }
 });
    </script>



<script>
$("#priceServices").on("keyup", function() {
    var searchTerm = $(this).val().toUpperCase();
    
    // Remove any existing highlights first
    $('.service-block__service').each(function() {
        var text = $(this).text();
        $(this).html(text.replace(/<span class="highlight">([^<]*)<\/span>/gi, '$1'));
    });
    
    $('[data-id^="service-"]').each(function() {
        var serviceRow = $(this);
        var serviceId = serviceRow.find(".service-block__id").text();
        var serviceName = serviceRow.attr("data-service");
        var categoryId = serviceRow.attr("data-category");
        
        // Highlight matching text
        if (searchTerm.length > 0) {
            var serviceNameElement = serviceRow.find(".service-block__service");
            var originalText = serviceNameElement.text();
            
            if (originalText.toUpperCase().indexOf(searchTerm) {
                var highlightedText = originalText.replace(
                    new RegExp(searchTerm, "gi"),
                    match => `<span class="highlight">${match}</span>`
                );
                serviceNameElement.html(highlightedText);
            }
        }
        
        if (serviceName.toUpperCase().indexOf(searchTerm) > -1 || 
            serviceId.toUpperCase().indexOf(searchTerm) > -1) {
            serviceRow.show();
            serviceRow.attr("id", "serviceshow" + categoryId);
        } else {
            serviceRow.hide();
            serviceRow.attr("id", "servicehide");
        }
    });
    
    $('[id^="Servicecategory-"]').each(function() {
        var categoryId = $(this).attr("data-id");
        if ($("#servicesTableList > tbody > tr#serviceshow" + categoryId).length == 0) {
            $("#" + categoryId).hide();
        } else {
            $("#" + categoryId).show();
        }
    });
});
</script>



<!-- Add this script after including footer -->
<script>
function sendToTelegram(serviceId) {
    Swal.fire({
        title: 'Sending to Telegram...',
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading()
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
    });

    $.post("<?= site_url('admin/services/send_telegram/') ?>" + serviceId, function(response) {
        Swal.fire({
            icon: response.icon,
            title: response.title,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }).fail(function() {
        Swal.fire('Error', 'Failed to send', 'error');
    });
}
</script>

<script>
function sendToFacebook(serviceId) {
    Swal.fire({
        title: 'Posting to Facebook...',
        timerProgressBar: true,
        didOpen: () => {
            Swal.showLoading()
        },
        allowOutsideClick: false,
        allowEscapeKey: false,
        allowEnterKey: false
    });

    $.post("<?= site_url('admin/services/send_facebook/') ?>" + serviceId, function(response) {
        Swal.fire({
            icon: response.icon,
            title: response.title,
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000
        });
    }).fail(function() {
        Swal.fire('Error', 'Failed to post to Facebook', 'error');
    });
}
</script>