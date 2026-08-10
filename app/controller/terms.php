<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
$title .= $languageArray["terms.title"];

if( $user["client_type"] == 1  ){
  Header("Location:".site_url('logout'));
}
