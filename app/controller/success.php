<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if(!$_SESSION["funds_added"] == "1"){
header("Location: ".site_url());
}


?>