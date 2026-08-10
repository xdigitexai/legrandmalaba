<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
if( $admin["access"]["services"] != 1  ){
    header("Location:".site_url("admin"));
    exit();
}
session_start();
if (route(2) && is_numeric(route(2))) :
    $page = route(2);
else :
    $page = 1;
endif;

$smmapi = new SMMApi();

if (route(2) == "ajax_services_update" && !$_POST) {
    $information = array(
        'type' => 'error',
        'message' => 'No data available, Please try again!',
    );

    $_SESSION["information"] = $information;

    Header("Location:" . site_url('admin/api-services'));
}

if (route(2) == "ajax_services_last" && (!$_SESSION["cat_data"] && !$_SESSION["multiple"])) {
    $information = array(
        'type' => 'error',
        'message' => 'No data available, Please try again!',
    );
    $_SESSION["information"] = $information;

    Header("Location:" . site_url('admin/api-services'));
}

if (!route(2)) :

    if (
        $_SESSION["provider"] || $_SESSION["cat_data"] || $_SESSION["provider_services"]
        || $_SESSION["profit"] || $_SESSION["information"] || $_SESSION["multiple"]
    ) {

        unset($_SESSION["provider"]);
        unset($_SESSION["cat_data"]);
        unset($_SESSION["profit"]);
        unset($_SESSION["information"]);
        unset($_SESSION["provider_services"]);
        unset($_SESSION["multiple"]);
    }

    $providers  = $conn->prepare("SELECT * FROM service_api ORDER BY id ASC");
    $providers->execute(array());
    $providers  = $providers->fetchAll(PDO::FETCH_ASSOC);


elseif (route(2) == "ajax_services_update" && $_POST) :


    $provider_id = $_POST['api_fetch_id'];

    $provider = $conn->prepare('SELECT * FROM service_api WHERE id=:id');
    $provider->execute(['id' => $provider_id]);
    $provider = $provider->fetch(PDO::FETCH_ASSOC);


if ($_POST && (empty($_POST['api_fetch_id']) ||  empty($provider))) :
$information = array(
            "type" => "danger",
            "message" => "Provider must be there to fetch services",
        );
   /* elseif ($_POST && (empty($_POST['profit']) || $_POST['profit'] < 0)) :
        $information = array(
            "type" => "danger",
            "message" => "Profit must be more than 0",
        );*/
    else :
        $information = array();
    endif;

    $_SESSION["information"] = $information;


    if ($information["type"] != "danger") {


        $categoriesData = $conn->prepare('SELECT * FROM categories WHERE category_deleted=:del ORDER BY category_line ');
        $categoriesData->execute(["del" => 0]);
        $categoriesData = $categoriesData->fetchAll(PDO::FETCH_ASSOC);


        if ($provider['api_type'] == 1) :
            $services = $smmapi->action(['key' => $provider['api_key'], 'action' => 'services'], $provider['api_url']);
        endif;

        $servicesCount = count($services);
        // if ($servicesCount > 100) {
        //     $disabled = "disabled";
        // }


        $_SESSION["provider"] = $provider;
        $_SESSION["profit"] = $_POST['profit'];
    } else {
        $services = json_encode(['error' => 'Something went wrong!']);
        Header("Location:" . site_url('admin/api-services'));
    }



elseif (route(2) == "ajax_services_add" && $_POST) :
 
    parse_str(json_decode($_POST["form_data"]), $postData);


    $checkBoxes = $postData['checkbox'];
    $category_ids = $postData['category_ids'];
    $category_name = $postData['category_name'];
    $old_category_name = $postData['old_category_name'];
    $status = $postData['status'];

    $i = 0;
    $nextData = array();
  //  print_r($_POST);exit;
    foreach ($checkBoxes as $check) {

        $cat_id = $category_ids[$check];

        if ($cat_id == 0) {

            $query = $conn->prepare("SELECT * FROM categories WHERE category_name=:name");
            $query->execute(array("name" => $category_name[$check]));
            $query = $query->fetch(PDO::FETCH_ASSOC);

        //    if (!empty($query)) {
                //already exists a category
                $category_ids[$check] = $query["category_id"];
                $cat_id =  $query["category_id"];
    //      } else {


                // get last service line
                $categoryLine     = $conn->prepare("SELECT category_line FROM categories
            ORDER BY category_line DESC LIMIT 1");
                $categoryLine->execute(array());
                $categoryLine     = $categoryLine->fetch(PDO::FETCH_ASSOC);


                $categoryLine  = empty($categoryLine["category_line"]) ? '0' : $categoryLine["category_line"];
                
              $language   = $conn->prepare("SELECT * FROM languages WHERE default_language=:default");

        $language->execute(array("default"=>1));

        $language   = $language->fetch(PDO::FETCH_ASSOC);
                
$MultiCatName = [
                    
$language["language_code"] =>  $category_name[$check]
                    ];
                    
                    $MultiCatName = json_encode($MultiCatName);

                //make a new category    
                $insert = $conn->prepare("INSERT INTO categories SET category_name=:category_name,category_name_lang=:category_name_lang, 
            category_line=:category_line, category_type=2,  category_secret=2");
                $insert = $insert->execute(array(
                    "category_name" => $category_name[$check],
                    "category_name_lang" => $MultiCatName,
                    "category_line" => $categoryLine + 1
                ));
                //insert in data
                $cat_id = $conn->lastInsertId();
                $i++;
 //           }
        } else {

            //use the existing category to insert services
        }

        $item = array(
            "id" => $check,
            "category_id" => $cat_id,
            "api_category_name" => $old_category_name[$check]
        );
        array_push($nextData, $item);
    }

    $information = array(
        'type' => 'success',
        'message' => $i . ' Categories inserted succesfully',
    );

    $_SESSION["cat_data"] = $nextData;
    $_SESSION["information"] = $information;

    Header("Location:" . site_url('admin/api-services/ajax_services_last'));

elseif (route(2) == "ajax_services_last") :

    // unset($_SESSION['multiple']);
    // p('done');



    if ($_SESSION['multiple']['status'] != 1) {
        $cat_data = $_SESSION["cat_data"];
        $profit = $_SESSION["profit"];


        $provider = $_SESSION["provider"];

        $countCatToAdd = count($cat_data);
        $dividingNumber = 5;
        if ($countCatToAdd > $dividingNumber) :
            $multiple = 1;
            $totalPages = ceil($countCatToAdd / $dividingNumber);
            $page = 1;
            $new_cat_divided_data =  array_chunk($cat_data, $dividingNumber);
            $multipleQuantity = array(
                'status' => $multiple,
                'totalPages' => $totalPages,
                'currentPage' => $page,
                'data' => $new_cat_divided_data,
            );
            $pageMessage = "Page " . $multipleQuantity['currentPage'] . " Out of " . $multipleQuantity['totalPages'] . " , Please wait the page to load all data properly!";
            $new_cat_divided_data = $new_cat_divided_data[$multipleQuantity['currentPage'] - 1];
            $multipleQuantity['currentPage']++;
            $_SESSION['multiple'] = $multipleQuantity;
        // unset($_SESSION['cat_data']);
        else :
            $new_cat_divided_data = $cat_data;
            $pageMessage = "";
        endif;
    } else {
        $profit = $_SESSION["profit"];
        $provider = $_SESSION["provider"];
        $multipleQuantity = $_SESSION['multiple'];

        if ($multipleQuantity['currentPage'] <= $multipleQuantity['totalPages']) :
            $new_cat_divided_data = $multipleQuantity['data'][$multipleQuantity['currentPage'] - 1];
            $pageMessage = "Page " . $multipleQuantity['currentPage'] . " Out of " . $multipleQuantity['totalPages'] . " , Please wait the page to load all data properly!";
            $multipleQuantity['currentPage']++;
            $multipleQuantity['currentPage'] > $multipleQuantity['totalPages'] ?  $multipleQuantity['status'] = 0 :  $multipleQuantity['status'] = 1;
            $_SESSION['multiple'] = $multipleQuantity;
        endif;
    }


    if (empty($_SESSION['provider_services'])) :
        if ($provider['api_type'] == 1) :
            $services = $smmapi->action(['key' => $provider['api_key'], 'action' => 'services'], $provider['api_url']);
            $_SESSION['provider_services'] = $services;
        endif;
    else :
        $services =  $_SESSION['provider_services'];
    endif;

    $getServicesByCategory = array();

    $servicesCount = 0;
    foreach ($new_cat_divided_data as $category) {



        $c_id = $category["category_id"];
        $servicesArray = array();

        $api_category_name = $category["api_category_name"];

        foreach ($services as $service) {

            if (urlencode($service->category) == urlencode($api_category_name)) {
                $servicesCount++;
                array_push($servicesArray, $service);
            }
        }

        $getServicesByCategory[$c_id] = $servicesArray;
    }
    // p(($getServicesByCategory));

    $allCategories = $conn->prepare('SELECT * FROM categories WHERE category_deleted=:del ORDER BY category_line ');
    $allCategories->execute(["del" => 0]);
    $allCategories = $allCategories->fetchAll(PDO::FETCH_ASSOC);

    $allServices = $conn->prepare('SELECT * FROM services ORDER BY service_line');
    $allServices->execute([]);
    $allServices = $allServices->fetchAll(PDO::FETCH_ASSOC);



elseif (route(2) == "ajax_services_addNow") :



    $decodedString = json_decode($_POST["form_data"], true);
    $newArray = array();
    $loopCounter = 0;
    foreach ($decodedString as $key) {
        if ($loopCounter >= 3) {
            $keyName = $key['name'];
            $keyName = explode('[', $keyName);
            $keyIndex = explode(']', $keyName[1]);
            $keyName = $keyName[0];
            $keyIndex = $keyIndex[0];
            $newArray[$keyName][$keyIndex] = $key['value'];
        }
        $loopCounter++;
    }
    $postData  = $newArray;
    $postData['service_profit_percentage'] = ($decodedString[0]['value']);
    $postData['import'] = ($decodedString[1]['value']);
    $postData['status'] = ($decodedString[2]['value']);



    foreach ($postData as $key => $value) {
        $$key = $value;
    }



    $i = 0;



    foreach ($checkbox as $cKey => $Cvalue) {


        //load  each and every service here

        $cat_id = $category_ids_array[$cKey];
        $service_id = $our_services_ids_array[$cKey];
        $service_name = mb_convert_encoding($service_name_array[$cKey], 'UTF-8', 'UTF-8');
        $service_api_id = $api_service_id_array[$cKey];
        $service_price = $prices_array[$cKey];
        $service_api_price = $service_api_prices[$cKey];
$service_min = $min_array[$cKey];
$service_max = $max_array[$cKey];
$service_desc = $description_array[$cKey];
$service_type = $service_type_array[$cKey];
$service_refill = empty($service_refill_array[$cKey]) ? 0 : $service_refill_array[$cKey];
$provider_id = $service_provider_array[$cKey];
$package = serviceTypeGetList($service_type);

$providerData     = $conn->prepare("SELECT * FROM service_api WHERE id=:id");
        $providerData->execute(array("id" => $provider_id));
$providerData     = $providerData->fetch(PDO::FETCH_ASSOC);



        $service_profit_percentage = $postData["service_profit_percentage"];

        $detail = array(
            "min" => $service_min,
            "max" => $service_max,
            "rate" => $service_api_price,
            "currency" => $providerData["currency"],
        );




        if ($service_id == 0) {

            // get last service line
            $serviceLine     = $conn->prepare("SELECT service_line FROM services
             ORDER BY service_line DESC LIMIT 1");
            $serviceLine->execute(array());
            $serviceLine     = $serviceLine->fetch(PDO::FETCH_ASSOC);


            $serviceLine  = empty($serviceLine["service_line"]) ? '0' : $serviceLine["service_line"];


        $multiName = json_encode(['en' => $service_name_array[$cKey]]);

$multiDesc =  json_encode(['en' => $service_desc]);

if($service_refill == 1):
$service_refill = "true";
else:
$service_refill = "false";
endif;

            //insert a new service with data

            $insert = $conn->prepare("INSERT INTO services SET service_api=:api,
                 api_service=:api_service, api_detail=:detail, category_id=:category,
                  service_line=:line, service_type=:type, service_package=:package, 
                  service_name=:name, service_description=:desc , service_price=:price, 
                  service_min=:min, service_max=:max , show_refill=:refill , price_profit=:price_profit, 
name_lang=:multiName, 
description_lang=:multi");
            $insert = $insert->execute(array(
                "api" => $provider_id, "api_service" => $service_api_id,
                "detail" => json_encode($detail), "category" => $cat_id, "line" => $serviceLine + 1, "type" => 2,
                "package" => $package, "name" => $service_name, "desc" => $service_desc,
                "price" => $service_price, "min" => $service_min, "max" => $service_max,
                "refill" => $service_refill, "price_profit" => $service_profit_percentage,"multiName"=>$multiName,"multi"=>$multiDesc      ));


            
        } else {

            //update existing service by service_id

            $update = $conn->prepare("UPDATE services SET service_api=:api,
            api_service=:api_service, api_detail=:detail, category_id=:category,
             service_type=:type, service_package=:package, 
             service_name=:name, service_description=:desc , service_price=:price, 
             service_min=:min, service_max=:max , show_refill=:refill , price_profit=:price_profit , 
name_lang=:multiName, 
description_lang=:multi
             WHERE service_id=:service_id");
            $update = $update->execute(array(
                "api" => $provider_id, "api_service" => $service_api_id,
                "detail" => json_encode($detail), "category" => $cat_id, "type" => 2,
                "package" => $package, "name" => $service_name, "desc" => $service_desc,
                "price" => $service_price, "min" => $service_min, "max" => $service_max,
                "refill" => $service_refill, "price_profit" => $service_profit_percentage, "service_id" => $service_id,"multiName"=>$multiName,"multi"=>$multiDesc
            ));

            if ($update) :
                insertAdminLog("Service Updated Through API", "service id => " . $service_id);
            endif;
        }
        $i++;
    }

    if (!$_SESSION["multiple"]["status"]) :
        unset($_SESSION["provider"]);
        unset($_SESSION["cat_data"]);
        unset($_SESSION["profit"]);
        unset($_SESSION["information"]);
        unset($_SESSION["provider_services"]);
        unset($_SESSION["multiple"]);
        $postData = array();
        header("Location:" . site_url("admin/services"));
    else :
        $_POST = array();
        $postData = array();
        header("Location:" . site_url("admin/api-services/ajax_services_last"));
    endif;






endif;



require admin_view('api-services');