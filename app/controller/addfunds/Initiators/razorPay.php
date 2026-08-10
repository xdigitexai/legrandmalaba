<?php
if (!defined('ADDFUNDS')) {
    http_response_code(404);
    die();
}

$APIPublicKey = $methodExtras["APIPublicKey"];
$APISecretKey = $methodExtras["APISecretKey"];
$redirectURL = site_url("payment/" . $methodCallback);

$orderId = md5(RAND_STRING(5) . time());

$redirectForm .= '
<script type="text/javascript">
$.getScript( "https://checkout.razorpay.com/v1/checkout.js", function( data, textStatus, jqxhr ) {
    var options = {
        "key": "' . $APIPublicKey . '",
        "amount": "' . ($paymentAmount * 100) . '",
        "currency": "' . $methodCurrency . '",
        "name": "' . ($settings["site_name"] ?: site_url()) . '",
        "description": "Balance recharge (' . $user["username"] . ')",
        "image": "' . $settings["favicon"] . '",
        "handler": function (response){
            // Handle the success response
            $.ajax({
                url: "payment_new/'.$methodCallback.'",
                type: "POST",
                data: "paymentId="+response.razorpay_payment_id+"&paymentAmount='.$paymentAmount.'",
                success: function(json){
                    if (json.success == true) {
                        var msgDiv = $("#formSubmitResponseMessage");
                        msgDiv.html(\'<div class="alert alert-success alert-dismissible fade show mt-2" role="alert">\' + json.message + \'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>\');
                        } else {
                        var msgDiv = $("#formSubmitResponseMessage");
                        msgDiv.html(\'<div class="alert alert-danger alert-dismissible fade show mt-2" role="alert">\' + json.message + \'<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">×</span></button></div>\');
                        }
                }
            });
        },
        "prefill": {
            "name": "' . ($user["name"] ?: "Customer") . '",
            "email": "' . ($user["email"] ?: "") . '",
            "contact": "' . ($user["telephone"] ?: "") . '"
        },
        "theme": {
            "color": "' . ($methodExtras["gatewayThemeColour"] ?: "#F37254") . '"
        }
      };
      var rzp = new Razorpay(options);
      rzp.open();
  });
</script>';

$response["success"] = true;
$response["message"] = "Your payment has been initiated and you will now be redirected to the payment gateway.";
$response["content"] = $redirectForm;
