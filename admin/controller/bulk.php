<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

  if( $admin["access"]["bulk"] != 1  ):
    header("Location:".site_url("admin"));
    exit();
  endif;

  if( $_SESSION["client"]["data"] ):
    $data = $_SESSION["client"]["data"];
    foreach ($data as $key => $value) {
      $$key = $value;
    }
    unset($_SESSION["client"]);
  endif;

 if($_SERVER["REQUEST_METHOD"] == "GET"){
    



if(route(2) == "getData"){
    $services       = $conn->prepare("SELECT service_id,service_name,service_min,service_max,service_price,service_description FROM services WHERE service_deleted=:deleted AND category_id=:cid") ;

    $services       -> execute(array("deleted" => '0',"cid" => $_GET["categoryId"]));

    $services       = $services->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($services);exit;
} else {
    require admin_view('bulk');
}

}
  if( $_POST) :

        
    $services = $_POST["service"];

        foreach ($services as $id => $value):


            $update = $conn->prepare("UPDATE services SET service_name=:name, service_min=:min, service_max=:max, service_price=:price , service_description=:description WHERE service_id=:id ");
            $update->execute(array("description" => $_POST["desc-$id"], "price" => $_POST["price-$id"], "max" =>$_POST["max-$id"], "min" => $_POST["min-$id"] , "name" => $_POST["name-$id"], "id" => $id ));

echo  $_POST["name-$id"] ;
if( $update ):
                header("Location:" . site_url("admin/bulk"));
                    $_SESSION["client"]["data"]["success"] = 1;
                    $_SESSION["client"]["data"]["successText"] = "Successful";
              else:
                $errorText  = "Failed";
                $error      = 1;
	header("Location:" . site_url("admin/bulk"));	

	            endif;


endforeach;
        
endif;


