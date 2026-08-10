<?php
$id = route(1);
$_SESSION['cur'] = htmlspecialchars($id);
header("Location:".site_url("service/$id"));

?>