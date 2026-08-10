<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Official Rental</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css" integrity="sha512-SzlrxWUlpfuzQ+pcUCosxcglQRNAq/DZjVsC0lE40xsADsfeQoEypE+enwcOiGjk/bSuGGKHEyjSoQ1zVisanQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-w76AqPfDkMBDXo30jS1Sgez6pr3x5MlQ1ZAGC+nuZB+EYdgRZgiwxhTBTkF7CXvN" crossorigin="anonymous"></script>
<style>
html,body {height:100%;width:100%;margin:0;padding:0;}
* {box-sizing: border-box;}
form {width:100%;padding:20px;}
a {color: #039;}
a:hover {color: #03F;}
@media(min-width:991px){form {margin-top: -40px;margin-bottom: 10px;width:450px;border:1px solid #bebebe;border-radius:12px;}}
</style>
</head>
<body>
<!-- vh-100 here-->
    <div style="padding-top:50px;" class="d-flex align-items-center justify-content-center">

<form action="<?=site_url("admin/activate-google-2fa")?>" method="POST">
<h5 align="center">Add an extra layer of security to the admin page.</h5>
<h6 align="center">Scan the below QR Code or Enter the below Code in <a target="_blank" href="https://play.google.com/store/apps/details?id=com.google.android.apps.authenticator2"> Google Authenticator</a> App.</h6>
<img class="rounded mx-auto d-block mb-2" style="height:250px;width:250px;" src="data:image/png;base64,<?=$encoded_qr_data?>" alt="Google Authenticator Setup QR Code">
<p align="center" class="h6">Code : <strong class=" text-success"><?=$GoogleTFA_admin->google2fa_secret ?></strong> <span style="margin-left:10px;" onclick="copyToClipboard('<?=$GoogleTFA_admin->google2fa_secret ?>')"><i class="fas fa-copy"></i></span></p>
<div class="error"></div>
<div class="form-control-sm mb-3">
<label class="form-label" for="2FA_Code">6-Digit Code from Google Authenticator App</label>
<input type="hidden" id="secret_key" name="secret_key" value="<?=$GoogleTFA_admin->google2fa_secret?>">
<div class="mb-3">
  <input id="2FA_Code" type="number" class="form-control" name="2FA_Code" placeholder="Enter Code" autocomplete="off">
  </div>
<div class="d-grid gap-2">
 <button type="submit" class="btn btn-primary">Enable Two-Step Verification</button>
<a class="btn btn-warning" href="<?=site_url("admin")?>">Skip for now</a>
</div>
</form>
    </div>

<script>
 function copyToClipboard(text) {
    if (window.clipboardData && window.clipboardData.setData) {
        // Internet Explorer-specific code path to prevent textarea being shown while dialog is visible.
        return window.clipboardData.setData("Text", text);

    }
    else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
        var textarea = document.createElement("textarea");
        textarea.textContent = text;
        textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in Microsoft Edge.
        document.body.appendChild(textarea);
        textarea.select();
        try {
            return document.execCommand("copy");  // Security exception may be thrown by some browsers.
        }
        catch (ex) {
            console.warn("Copy to clipboard failed.", ex);
            return prompt("Copy to clipboard: Ctrl+C, Enter", text);
        }
        finally {
            document.body.removeChild(textarea);
        }
    }
}

$(document).ready(function(){

$("form").submit(function(e){
    e.preventDefault();
 var secret_key = $("#secret_key").val();
 var _2fa_code = $("#2FA_Code").val();
 var error = $(".error");
 $.ajax({
  url: "<?=site_url("admin/activate-google-2fa")?>",
  data:"secret_key="+secret_key+"&2FA_Code="+_2fa_code,
  type:"POST",
  success:function(response){
   var response = JSON.parse(response);
   
   
   if(response.success == false){
     error.html('<div class="alert alert-danger alert-dismissible fade show" role="alert"> <strong>'+response.message+'</strong><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
   }
   if(response.success == true){
     error.html('<div class="alert alert-success alert-dismissible fade show" role="alert"> <strong>'+response.message+'</strong><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
     window.location.href = "/admin";
   }
  }
 });
});

});


</script>
  </body>
</html>


