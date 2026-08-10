<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}


  if( $admin["access"]["decoration"] != 1  ):
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
          


if( $_POST ):
foreach ($_POST as $key => $value) {
$$key = $value;
}
$conn->beginTransaction();
$update = $conn->prepare("UPDATE decoration SET fire_works=:fire_works,
          snowflakes=:snowflakes,
          garlands=:garlands,
          toys=:toys,
          toy_a=:toy_a,
          toy_b=:toy_b,
          toy_c=:toy_c,
          toy_d=:toy_d,
          toy_e=:toy_e,
          toy_f=:toy_f,
          toy_g=:toy_g,
          toy_h=:toy_h,
          toy_i=:toy_i,
          toy_j=:toy_j,
          toy_k=:toy_k,
          toy_l=:toy_l,
          toy_size=:toy_size,
          toy_quantity=:toy_quantity,
          toy_speed=:toy_speed,
          toy_launch=:toy_launch,
          fire_speed=:fire_speed,
          fire_size=:fire_size,
          gar_style=:gar_style,
          gar_shape=:gar_shape,
          snow_speed=:snow_speed,
          snowflakes=:snowflakes,
          snow_fall=:snow_fall
          WHERE id=:id ");
          $update = $update->execute(array("id"=>1,"snow_fall" => $snow_fall,
          "garlands" => $garlands,
          "fire_works" => $fire_works,
          "fire_speed" => $fire_speed,
          "fire_size" => $fire_size,
          "gar_style" => $gar_style,
          "gar_shape" => $gar_shape,
          "snow_speed" => $snow_speed,
          "snowflakes" => $snowflakes,
          "toys" => $toys,
          "toy_a" => $toy_a,
          "toy_b" => $toy_b,
          "toy_c" => $toy_c,
          "toy_d" => $toy_d,
          "toy_e" => $toy_e,
          "toy_f" => $toy_f,
          "toy_g" => $toy_g,
          "toy_h" => $toy_h,
          "toy_i" => $toy_i,
          "toy_j" => $toy_j,
          "toy_k" => $toy_k,
          "toy_l" => $toy_l,
          "toy_size" => $toy_size,
          "toy_quantity" => $toy_quantity,
          "toy_speed" => $toy_speed,
          "toy_launch" => $toy_launch));
          if( $update ):
            $conn->commit();
            header("Location:".site_url("admin/decoration"));
            $_SESSION["client"]["data"]["success"]    = 1;
            $_SESSION["client"]["data"]["successText"]= "Success";
          else:
            $conn->rollBack();
            $error    = 1;
            $errorText= "Failed";
          endif;
        endif;


    require admin_view('decoration');

