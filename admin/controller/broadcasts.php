<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}

 if( $admin["access"]["broadcast"] != 1  ):
    header("Location:".site_url("admin"));
    exit();
  endif;

  if( !route(2) ):
    $page   = 1;
  elseif( is_numeric(route(2)) ):
    $page   = route(2);
  elseif( !is_numeric(route(2)) ):
    $action = route(2);
  endif;

  if( empty($action) ):
      
    $notifications        = $conn->prepare("SELECT * FROM notifications_popup ");
    $notifications        -> execute(array());
    $notifications        = $notifications->fetchAll(PDO::FETCH_ASSOC);
    
    $kupon_kullananlar        = $conn->prepare("SELECT * FROM kupon_kullananlar ");
    $kupon_kullananlar        -> execute(array());
    $kupon_kullananlar        = $kupon_kullananlar->fetchAll(PDO::FETCH_ASSOC);
    
    require admin_view('broadcasts');
	
	
	
	
    elseif( $action == "edit" ):
	
      if( $_POST ):
            $nId = $_POST['id'];
            $title = $_POST['title'];

$type = $_POST["broadcast_type"];
              $description = str_replace("\n", "<br />", $_POST['description']);
              $action_link = $_POST['action_link'];
              $action_text = $_POST['action_text'];
              $expiry_date = $_POST['expiry_date'];
              $isAllUser = $_POST['isAllUser'];
              $status = $_POST['status'];
              
              if(date("Y-m-d H:i:s") < $expiry_date){
                    $insert = $conn->prepare("UPDATE notifications_popup SET title=:title,type=:type,description=:description,action_link=:action_link,action_text=:action_text,expiry_date=:expiry_date,isAllUser=:isAllUser,status=:status  WHERE id=:id ");
               $insert = $insert-> execute(array("id"=>$nId,"title"=>$title,"type"=>$type,"description"=>$description,"action_link"=>$action_link,"action_text"=>$action_text,"expiry_date"=>$expiry_date,"isAllUser"=>$isAllUser,"status"=>$status));
                if( $insert ):
          
                  header("Location:".site_url("admin/broadcasts"));
                else:
          
                  header("Location:".site_url("admin/broadcasts"));
                endif;
              }else {
                  echo '<script>alert("Error! Expiry Date should be more than current date");</script>';
                  
              }
              

		 
			
	else:
	    $link = $_SERVER['REQUEST_URI'];
        $link_array = explode('/',$link);
        $nId = end($link_array);
        $pages        = $conn->prepare("SELECT * FROM pages ");
        $pages        -> execute(array());
        $pages        = $pages->fetchAll(PDO::FETCH_ASSOC);
        
        $notifications        = $conn->prepare("SELECT * FROM notifications_popup WHERE id= $nId LIMIT 1");
        $notifications        -> execute(array());
        $notifData        = $notifications->fetchAll(PDO::FETCH_ASSOC)[0];   
        
	    require admin_view('editbroadcasts');
	  
	endif;
	
	elseif( $action == "delete" ):
	
	if( $_POST ):
	 $notification_id =  $_POST['notification_id'];

$delete = $conn->prepare("DELETE FROM notifications_popup WHERE id=:id");
$delete->execute(array("id"=>$notification_id));

if( $delete ):
			
header("Location: ".site_url("admin/broadcasts"));
else:
header("Location: ".site_url("admin/broadcasts"));
endif;
			
			
	  
	endif;
	
	elseif( $action == "create" ):
	    
	    $pages        = $conn->prepare("SELECT * FROM pages ");
        $pages        -> execute(array());
        $pages        = $pages->fetchAll(PDO::FETCH_ASSOC);
	  require admin_view('createbroadcasts');

	
  elseif( $action == "new" ):
            
          $title = @$_POST['title'];
          $type = @$_POST["broadcast_type"];
          $description = @$_POST['description'];
          $action_link = @$_POST['action_link'];
          $action_text  = @$_POST['action_text'];
          $expiry_date = @$_POST['expiry_date'];
          $status = @$_POST["status"];
          $isAllUser = @$_POST['isAllUser'];
          
if(date("Y-m-d H:i:s") < $expiry_date){
$insert = $conn->prepare("INSERT INTO notifications_popup SET title=:title,type=:type,description=:desc,action_link=:link,action_text=:text,expiry_date=:expiry,status=:status,isAllUser=:isAllUser");
$insert->execute(
array("title"=>$title,
"type"=>$type,
"desc"=>$description,
"link"=>$action_link,
"text"=>$action_text,
"expiry"=>$expiry_date,
"status"=>$status,
"isAllUser"=>$isAllUser));

header("Location:../../admin/broadcasts");
}else {
echo '<script>alert("Error! Expiry Date should be more than current date");</script>';
}
  endif;
?>