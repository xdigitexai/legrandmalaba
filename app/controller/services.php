<?php
if(!defined('BASEPATH')) {
   die('Direct access to the script is not allowed');
}
ini_set('memory_limit', '512M');
ini_set('max_execution_time', 120);
$title .= $languageArray["services.title"];

if( $settings["service_list"] == 2 && !$_SESSION["msmbilisim_userlogin"] ):
  header("Location:".site_url('services'));
  exit();
endif;

$categoriesRows = $conn->prepare("SELECT * FROM categories WHERE category_type='2' AND category_deleted='0' ORDER BY categories.category_line ASC");
$categoriesRows->execute(array());
$categoriesRows = $categoriesRows->fetchAll(PDO::FETCH_ASSOC);

$siraal = 'asc';
$q_siraal = $conn->query("SELECT servis_siralama FROM settings WHERE id=1", PDO::FETCH_ASSOC);
foreach($q_siraal as $r) { $siraal = $r['servis_siralama']; }

// Pre-fetch ALL services in one query
$allServicesStmt = $conn->query("SELECT * FROM services WHERE service_type='2' AND service_deleted='0' ORDER BY service_line ".$siraal, PDO::FETCH_ASSOC);
$servicesByCategory = [];
foreach($allServicesStmt as $svc) {
    $servicesByCategory[$svc["category_id"]][] = $svc;
}

// Pre-fetch client-specific access only if logged in
$clientCategoryIds = [];
$clientServiceIds  = [];
$clientPrices      = [];
if( isset($user["client_id"]) && $user["client_id"] ) {
    $cc = $conn->prepare("SELECT category_id FROM clients_category WHERE client_id=:c_id");
    $cc->execute(["c_id"=>$user["client_id"]]);
    foreach($cc as $r) $clientCategoryIds[$r["category_id"]] = true;

    $cs = $conn->prepare("SELECT service_id FROM clients_service WHERE client_id=:c_id");
    $cs->execute(["c_id"=>$user["client_id"]]);
    foreach($cs as $r) $clientServiceIds[$r["service_id"]] = true;

    // Pre-fetch custom client prices in ONE query
    $cp = $conn->prepare("SELECT service_id, service_price FROM clients_price WHERE client_id=:c_id");
    $cp->execute(["c_id"=>$user["client_id"]]);
    foreach($cp as $r) $clientPrices[$r["service_id"]] = $r["service_price"];
}

// Pre-fetch ALL currency data in ONE query instead of per-service DB calls
$_allCurrencies = $conn->query("SELECT * FROM currencies", PDO::FETCH_ASSOC)->fetchAll();
$_currencyByCode = [];
foreach($_allCurrencies as $_c) {
    $_currencyByCode[strtolower($_c['currency_code'])] = $_c;
}
$_baseCurrency = strtolower($settings['site_base_currency'] ?? 'usd');
$_userCurrency = strtolower($user['currency_type'] ?? $_baseCurrency);
$_baseSym    = $_currencyByCode[$_baseCurrency]['currency_symbol']    ?? '$';
$_basePos    = $_currencyByCode[$_baseCurrency]['symbol_position']    ?? 'left';
$_userSym    = $_currencyByCode[$_userCurrency]['currency_symbol']    ?? $_baseSym;
$_userPos    = $_currencyByCode[$_userCurrency]['symbol_position']    ?? $_basePos;
$_userRate   = floatval($_currencyByCode[$_userCurrency]['currency_rate'] ?? 1);
$_baseInv    = floatval($_currencyByCode[$_baseCurrency]['currency_inverse_rate'] ?? 1);

// Inline price formatter — zero DB queries
$_fmt = function($price, $sym, $pos, $isBase, $baseCurr, $userCurr) {
    $price = floatval($price);
    $price = ($price < 1)
        ? round(rtrim(sprintf('%f', $price), '0'), 4)
        : number_format(round($price, 2), 2);
    $prefix = ($isBase || strtolower($baseCurr) === strtolower($userCurr)) ? '' : '≈ ';
    return ($pos === 'right') ? $prefix.$price.' '.$sym : $prefix.$sym.' '.$price;
};

$categories = [];
foreach ( $categoriesRows as $categoryRow ) {
    $catId = $categoryRow["category_id"];
    if( $categoryRow["category_secret"] == 2 || isset($clientCategoryIds[$catId]) ):
      $rows = isset($servicesByCategory[$catId]) ? $servicesByCategory[$catId] : [];
      $services = [];
      foreach ( $rows as $row ) {
          $svcId = $row["service_id"];
          if( $row["service_secret"] == 2 || isset($clientServiceIds[$svcId]) ):
              // Get price — custom client price or default service price
              $_rawPrice = isset($clientPrices[$svcId]) ? floatval($clientPrices[$svcId]) : floatval($row["service_price"]);

              // Convert to user currency if different from base
              if($_userCurrency !== $_baseCurrency) {
                  $_userPrice = $_rawPrice * $_baseInv * $_userRate;
              } else {
                  $_userPrice = $_rawPrice;
              }

              $s["service_price"]             = $_fmt($_userPrice, $_userSym, $_userPos, false, $_baseCurrency, $_userCurrency);
              $s["without_login_service_price"]= $_fmt($_rawPrice, $_baseSym, $_basePos, true,  $_baseCurrency, $_baseCurrency);
              $s["service_id"]          = $row["service_id"];
              $s["service_name"]        = $row["service_name"];
              $s["service_description"] = str_replace("\n", "<br />", $row["service_description"]);
              $s["time"]                = $row["time"];
              $s["service_min"]         = $row["service_min"];
              $s["service_max"]         = $row["service_max"];
              array_push($services, $s);
          endif;
      }
      $c["category_name"] = $categoryRow["category_name"];
      $c["category_icon"] = $categoryRow["category_icon"];
      $c["category_id"]   = $categoryRow["category_id"];
      $c["services"]      = $services;
      array_push($categories, $c);
    endif;
}
