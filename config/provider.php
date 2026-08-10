<?php

define("PROVIDER_API_URL", "https://example-smm-panel.com/api/v2");
define("PROVIDER_API_KEY", "YOUR_API_KEY_HERE");

function sendToProvider($data){
    return [
        "success" => true,
        "order_id" => "TEST123456"
    ];
}