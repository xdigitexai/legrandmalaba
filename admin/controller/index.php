<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
@ini_set('memory_limit', '256M');
if( $admin["access"]["admin_access"] != 1  ){

    header("Location:".site_url("admin"));

    exit();
}
  
  $clients = $conn->prepare("SELECT * FROM clients ORDER BY client_id DESC LIMIT 500");
  $clients->execute(array());
  $clients = $clients->fetchAll(PDO::FETCH_ASSOC);







$failCount      = $conn->prepare("SELECT * FROM orders WHERE orders.dripfeed='1' && orders.subscriptions_type='1' && order_error!=:error ");
  $failCount     -> execute(array("error"=>"-"));
  $failCount      = $failCount->rowCount();
$todayCount      = $conn->prepare("SELECT * FROM orders WHERE last_check=:error ");
  $todayCount     -> execute(array("error"=> date("Y-m-d") ));
  $todayCount     = $todayCount->rowCount();

   $services       = $conn->prepare("SELECT services.service_id, services.service_name, services.service_price, services.service_deleted, categories.category_id, categories.category_name, categories.category_line, services.service_line FROM services RIGHT JOIN categories ON categories.category_id = services.category_id LEFT JOIN service_api ON service_api.id = services.service_api ORDER BY categories.category_line,services.service_line ASC ");
  $services       -> execute(array());
  $services       = $services->fetchAll(PDO::FETCH_ASSOC);
  $serviceList    = array_group_by($services, 'category_name');
  if(!isset($yearList)) $yearList = [];
  if( count($yearList) == 0 ): $yearList[0] = date("Y"); endif;
  if( $_GET["year"] ):
    $year = $_GET["year"];
  else:
    $year = date("Y");
  endif;
  
  
  

require admin_view('index');

