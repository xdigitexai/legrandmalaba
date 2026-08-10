<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
$title .= $languageArray["services.title"];

if( $settings["service_list"] == 1 && !$_SESSION["msmbilisim_userlogin"] ):
  header("Location:".site_url('services'));
endif;


$categoriesRows = $conn->prepare("SELECT * FROM categories WHERE category_type='2' AND category_deleted='0'  ORDER BY categories.category_line ASC ");
$categoriesRows->execute(array());
$categoriesRows = $categoriesRows->fetchAll(PDO::FETCH_ASSOC);


$query = $conn->query("SELECT * FROM settings", PDO::FETCH_ASSOC);
		if ( $query->rowCount() ):
			 foreach( $query as $row ):
				  $siraal = $row['servis_siralama'];
			 endforeach;
		endif;
		
		

$categories = [];
  foreach ( $categoriesRows as $categoryRow ) {
    $search = $conn->prepare("SELECT * FROM clients_category WHERE category_id=:category && client_id=:c_id ");
    $search->execute(array("category"=>$categoryRow["category_id"],"c_id"=>$user["client_id"]));
    if( $categoryRow["category_secret"] == 2 || $search->rowCount() ):
      $rows     = $conn->prepare("SELECT * FROM services WHERE category_id=:id && service_type='2' && service_deleted='0' ORDER BY service_line ".$siraal);
      $rows     ->execute(array("id"=>$categoryRow["category_id"],"type"=>2 , "deleted" => '0'));
      $rows     = $rows->fetchAll(PDO::FETCH_ASSOC);
      $services = [];
        foreach ( $rows as $row ) {

$s["service_price"] = format_amount_string($user["currency_type"],from_to(get_currencies_array("enabled"),$settings["site_base_currency"],$user["currency_type"],service_price($row["service_id"])));
$s["without_login_service_price"] = format_amount_string($settings["site_base_currency"],from_to(get_currencies_array("enabled"),"","",service_price($row["service_id"])));
              $name = $row["service_name"];
          $s["service_id"]    = $row["service_id"];
          $s["service_name"]  = $name;
  
$s["service_description"] = str_replace("\n", "<br />", $row["service_description"]);
$s["time"]  = $row["time"];
          $s["service_min"]   = $row["service_min"];
          $s["service_max"]   = $row["service_max"];
          $search = $conn->prepare("SELECT * FROM clients_service WHERE service_id=:service && client_id=:c_id ");
          $search->execute(array("service"=>$row["service_id"],"c_id"=>$user["client_id"]));
          if( $row["service_secret"] == 2 || $search->rowCount() ):
            array_push($services,$s);
          endif;
        }
      $c["category_name"]          = $categoryRow["category_name"];
      $c["category_icon"]          = $categoryRow["category_icon"];
      $c["category_id"]            = $categoryRow["category_id"];
      $c["services"]               = $services;
      array_push($categories,$c);
    endif;

  }
