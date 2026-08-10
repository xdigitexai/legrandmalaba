<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}


  if( $admin["access"]["bulkc"] != 1  ):
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

   
    $services       = $conn->prepare("SELECT * FROM categories ") ;
    $services       -> execute(array());
    $services       = $services->fetchAll(PDO::FETCH_ASSOC);
    
    require admin_view('bulkc');


  if( $_POST) :

        
    $services = $_POST["service"];

        foreach ($services as $id => $value):


            $update = $conn->prepare("UPDATE categories SET category_name=:name WHERE category_id=:id ");
            $update->execute(array("name" => $_POST["name-$id"], "id" => $id ));

echo  $_POST["name-$id"] ;
if( $update ):
                header("Location:" . site_url("admin/bulkc"));
                    $_SESSION["client"]["data"]["success"] = 1;
                    $_SESSION["client"]["data"]["successText"] = "Successful";
              else:
                $errorText  = "Failed";
                $error      = 1;
	header("Location:" . site_url("admin/bulkc"));	

	            endif;


endforeach;
        
endif;





$name = "$name-$id";