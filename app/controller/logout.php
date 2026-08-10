<?php
if(!defined("BASEPATH")) {
   die("Direct access to the script is not allowed");
}
  setcookie("u_id", "", time()-3600, "/");
  setcookie("u_password", "", time()-3600, "/");
  setcookie("u_login", "", time()-3600, "/");
  setcookie("currency_hash", "", time()-3600, "/");
  
  session_destroy();
  header("Location: ".site_url(""));
  exit;
?>
