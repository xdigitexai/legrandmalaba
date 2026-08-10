<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if ($admin["access"]["update-prices"] != 1):
header("Location:" . site_url("admin"));
exit();
endif;

$images = $conn->prepare("SELECT * FROM files");
$images->execute();
$images = $images->fetchAll(PDO::FETCH_ASSOC);
$images = array_group_by($images,"id");

$categories = $conn->prepare("SELECT * FROM categories WHERE category_deleted = :cat_del ORDER BY category_line ASC");
$categories->execute(["cat_del" => 0]);
$categories = $categories->fetchAll(PDO::FETCH_ASSOC);
$list .= "";
for($i = 0;$i < count($categories);$i++){
    
if ($categories[$i]["category_type"] == 2) {$std = '<span data-post="category_id=' . $categories[$i]["category_id"] . '" class="category-visibility category-visible"></span>';} 
elseif ($categories[$i]["category_type"] == 1) {$std = '<span data-post="category_id=' . $categories[$i]["category_id"] . '" class="category-visibility category-invisible"></span>';}

$category_icon_array = json_decode($categories[$i]["category_icon"], true);
$category_icon_type = $category_icon_array["icon_type"];
if ($category_icon_type == "image") {$icon = "<img style=\"margin-right:10px;\" src=\"" . $images[$category_icon_array["image_id"]][0]["link"] . "\" class=\"img-responsive btn-group-vertical\">";}
elseif ($category_icon_type == "icon") {$icon = "<i style=\"margin-right:10px;font-size:18px;\" class=\"" . $category_icon_array["icon_class"] . "\" aria-hidden=\"true\"></i>";} 
else {$icon = "";}
    
 $list .= "<li data-category-id=\"" . $categories[$i]["category_id"] . "\" class=\"list-group-item\">
    <span class=\"category-sort-handle\">=</span>" .$std .$icon . $categories[$i]["category_name"] . "<span style=\"margin-left:10px;margin-right:10px;font-weight:bold;\">|</span>
    <a class=\"text-danger\" href=\"" . site_url("admin/services/del_category/" . $categories[$i]["category_id"]) . "\" data-action=\"del_category\"><i class=\"fas fa-trash\"></i></a>
</li>";

}


if( $action == "del_category" ):
   foreach ($services as $id => $value):
      $delete = $conn->prepare("DELETE FROM categories WHERE category_id=:id ");
      $delete->execute(array("id"=>$id));
    endforeach;
header("Location:".site_url("admin/services"));
 endif;

  
if($_POST){
$action = $_POST["action"];
if($action == "sort_category"){
$category_list_array = json_decode(base64_decode($_POST["category_list"]), true);
array_unshift($category_list_array,"");
unset($category_list_array[0]);
foreach($category_list_array as $index => $category_id){
  $update_position = $conn->prepare("UPDATE categories SET category_line=:category_line WHERE category_id=:category_id");
  $update_position->execute(array(
   "category_line" => $index,
   "category_id" => $category_id
  ));
}
exit();
}
}

require admin_view("category-sort");
?>


