<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
$title .= "News";


if( $_SESSION["msmbilisim_userlogin"] != 1  || $user["client_type"] == 1  ){
  header("Location:".site_url('logout'));
}


if( $settings["email_confirmation"] == 1  && $user["email_type"] == 1  ){
  header("Location:".site_url('confirm_email'));
}

          $newsList = $conn->prepare("SELECT * FROM news ");
          $newsList->execute(array());
          $newsList = $newsList->fetchAll(PDO::FETCH_ASSOC);



