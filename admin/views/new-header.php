<!doctype html>
<html>

<head>
  <base href="<?php echo site_url(); ?>">
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css"/>
  <link rel="stylesheet" href="https://cdn.quilljs.com/1.3.6/quill.snow.css">
  <link href="<?php echo site_url("public/admin/css/tom-select.bootstrap5.min.css"); ?>" rel="stylesheet">
  <link rel="stylesheet" href="public/admin/iziToast.min.css">
  <link rel="stylesheet" href="<?php echo site_url("public/admin/css/fancyselect.css"); ?>">
  <link rel="stylesheet" href="public/admin/css/rwd-table.min.css">
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.1/jquery.min.js"></script>
  <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.13.0/css/all.css">
  <script>$(document).on("click",".category-visible",function(){var a=$(this);$.ajax({url:"admin/ajax_data",data:"action=category_disable&"+$(this).data("post"),type:"POST",success:function(t){var t=JSON.parse(t);a.replaceWith(t.content)}})}),$(document).on("click",".category-invisible",function(){var a=$(this);$.ajax({url:"admin/ajax_data",data:"action=category_enable&"+$(this).data("post"),type:"POST",success:function(t){var t=JSON.parse(t);a.replaceWith(t.content)}})});</script>
  <style>
  .category-visibility.category-visible{padding:10px;}.category-visibility{width:22px;height:22px;border-radius:50%;display:inline-block;margin-right:10px;cursor:pointer}.category-visibility.category-visible{border-radius:50%;background-color:#29bf12;box-shadow:2px 2px 2px rgba(41,191,18,.4);cursor:pointer}.category-visibility.category-invisible{border-radius:50%;background-color:#ef233c;box-shadow:2px 2px 2px rgba(239,35,60,.4);cursor:pointer}*{margin:0;padding:0;box-sizing:border-box;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,"Helvetica Neue",Arial,"Noto Sans",sans-serif,"Apple Color Emoji","Segoe UI Emoji","Segoe UI Symbol","Noto Color Emoji";font-size:1rem;line-height:1.5}a,button,div,input,select,textarea{-webkit-tap-highlight-color:transparent}header{width:100%}.ql-toolbar.ql-snow{border-top-right-radius:.375rem;border-top-left-radius:.375rem}.ql-toolbar.ql-snow+.ql-container.ql-snow{border-bottom-right-radius:.375rem;border-bottom-left-radius:.375rem}@media (min-width:991px){.payment-card{margin-left:8px;margin-right:8px}.page-content{display:flex;flex-direction:row;flex-wrap:wrap;justify-content:center}}.mobile-nav-close-btn{position:fixed;z-index:5;top:15px;left:18px;cursor:pointer;-webkit-transition:left .5s cubic-bezier(.6, .05, .28, .91);transition:left .5s cubic-bezier(.6, .05, .28, .91)}.mobile-nav-close-btn div{width:34px;height:3px;margin-bottom:7px;background-color:#333;-webkit-transition:-webkit-transform .5s cubic-bezier(.6, .05, .28, .91),opacity .5s,box-shadow 250ms,background-color .5s;transition:transform .5s cubic-bezier(.6, .05, .28, .91),opacity .5s,box-shadow 250ms,background-color .5s}.mobile-nav-close-btn.active{left:210px}.mobile-nav-close-btn.active div{background-color:#fff;color:#fff}.mobile-nav-close-btn.active .bar-top{-webkit-transform:translateY(10px) rotate(-135deg);-ms-transform:translateY(10px) rotate(-135deg);transform:translateY(10px) rotate(-135deg)}.mobile-nav-close-btn.active .bar-middle{-webkit-transform:scale(0);-ms-transform:scale(0);transform:scale(0)}.mobile-nav-close-btn.active .bar-bottom{-webkit-transform:translateY(-10px) rotate(-45deg);-ms-transform:translateY(-10px) rotate(-45deg);transform:translateY(-10px) rotate(-45deg)}.mobile-navbar{position:fixed;z-index:4;top:0;left:-260px;width:260px;opacity:0;padding:20px 0;height:100%;background-color:#11101d;color:#fff;-webkit-transition:350ms cubic-bezier(.6, .05, .28, .91);transition:350ms cubic-bezier(.6, .05, .28, .91)}.mobile-navbar.active{left:0;opacity:1}.mobile-navbar .logo-details{margin-left:.5rem;margin-top:-.6rem}.mobile-navbar .logo-details .logo_name{font-size:1.5rem;font-weight:800;font-family:Arial,Helvetica,sans-serif;background:padding-box text #fff;transform:skew(-8deg,0deg);-webkit-text-fill-color:transparent;-webkit-background-clip:text;color:#11101d;text-transform:uppercase;margin-left:.7rem;margin-bottom:-10px;text-shadow:0 0 15px #bd8e13c3}.mobile-navbar .nav-list{margin-top:1rem;height:100%;padding:1rem;overflow-y:scroll}.mobile-navbar li{position:relative;list-style:none;margin-bottom:.3rem}.mobile-navbar .search-nav-input{font-size:15px;color:#fff;font-weight:400;outline:0;height:50px;border:none;border-radius:12px;transition:.5s;background:#1d1b31;padding:0 20px 0 50px;margin-bottom:1rem;width:100%}.mobile-navbar .search-nav-input-icon{position:absolute;top:50%;left:10px;transform:translateY(-50%);font-size:18px;background:#1d1b31;color:#fff}.mobile-navbar li:not(.search-box-li){padding:.6rem .4rem;border:none;border-radius:8px;transition:background-color .3s;cursor:pointer}.mobile-navbar li.open-dropdown:not(.search-box-li),.mobile-navbar li:hover:not(.search-box-li){background-color:#1d1b31;color:#fff}.mobile-navbar li a .arrow{float:right;margin-top:6px;margin-right:2px;transition:transform .2s}.mobile-navbar li .content{display:none}.mobile-navbar li .content a{padding:.4rem .2rem .4rem 1rem;margin-bottom:.2rem;border:none;border-radius:8px;transition:background-color .3s;cursor:pointer}.mobile-navbar li .content a:first-child{margin-top:.3rem}.mobile-navbar li .content a.active,.mobile-navbar li .content a:hover{background-color:#fff;color:#11101d}.mobile-navbar li.open-dropdown a .arrow{transform:rotate(90deg)}.form-control.search-nav-input:focus{box-shadow:0 0 0 .1rem rgba(0,188,228,.8)}.fsb-select{border:none;border-radius:4px;width:100%;box-shadow:none}.fsb-button{border:1px solid #ced4da;border-radius:.375rem;transition:box-shadow .2s}.fsb-button[aria-expanded=true]{border:1px solid #fff;box-shadow:0 0 0 .1rem #0d6dfd}.fsb-option:hover{background-color:rgba(222,234,254)}.blue-background-class,.fsb-option[aria-selected=true],.ts-dropdown .active{background-color:#0d6efd;color:#fff}.fsb-list{margin-top:5px;border-radius:.375rem;border:none;width:100%;box-shadow:0 2px 15px 0 rgb(0 0 0 / 20%)}.increase-decrease-form,.margin-top-container{margin-top:4rem}.ts-control{border-radius:4px;background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23000' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3E%3C/svg%3E");background-position:right .75rem center;background-repeat:no-repeat;background-size:16px 12px}.focus .ts-control{border:1px solid #fff;border-radius:4px;box-shadow:0 0 0 .1rem #0d6dfd;background-image:url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3E%3Cpath fill='none' stroke='%23000' stroke-linecap='round' stroke-linejoin='round' stroke-width='3' d='m2 5 6 6 6-6'/%3E%3C/svg%3E")!important}.ts-wrapper.multi .ts-control>div{margin:0 2px 2px 0;padding:.5px 0 .5px 2px;font-size:12px;display:flex;min-width:0;background-color:#e6e6e6;border-radius:2px}.ts-wrapper.plugin-remove_button:not(.rtl) .item .remove{border-left:0;margin-left:0;font-weight:700;font-size:15px}.ts-dropdown,.ts-dropdown.form-control,.ts-dropdown.form-select{border:.5px solid rgba(0,0,0,.175);border-radius:4px;box-shadow:0 4px 8px rgba(0,0,0,.175)}.ts-dropdown [data-selectable].option{padding:7px}.ts-dropdown [data-selectable].option:hover{background-color:rgba(222,234,254);color:#000}.spinner{pointer-events:none;-webkit-animation:1.5s linear infinite rotate;animation:1.5s linear infinite rotate;width:3.2rem;height:3.2rem;transition:opacity .3s linear .15s;margin-right:10px}.spinner.large{width:2.5rem;height:2.5rem}.spinner.medium{width:1.5rem;height:1.5rem}.spinner.small{width:1rem;height:1rem}.spinner .path{stroke:#0d6efd;stroke-linecap:round;-webkit-animation:1.5s ease-in-out infinite dash;animation:1.5s ease-in-out infinite dash}@-webkit-keyframes rotate{100%{transform:rotate(360deg)}}@keyframes rotate{100%{transform:rotate(360deg)}}@-webkit-keyframes dash{0%{stroke-dasharray:1,150;stroke-dashoffset:0}50%{stroke-dasharray:90,150;stroke-dashoffset:-35}100%{stroke-dasharray:90,150;stroke-dashoffset:-124}}@keyframes dash{0%{stroke-dasharray:1,150;stroke-dashoffset:0}50%{stroke-dasharray:90,150;stroke-dashoffset:-35}100%{stroke-dasharray:90,150;stroke-dashoffset:-124}}.plugin-clear_button .clear-button{position:absolute;right:calc(2.5rem);top:50%;transform:translateY(-50%);transition:opacity .5s;font-size:20px!important;margin-right:15px!important}.ts-control::after{content:'';display:block;position:absolute;top:50%;right:2.5em;height:1.25em;width:1px;background-color:#6b7177;-webkit-transform:translateY(-50%);transform:translateY(-50%)}.category-list-div{margin-bottom:20px;height:80vh;overflow-y:auto;width:100%}.category-sort-handle{margin-right:0;font-size:25px;font-weight:700;cursor:move;display:flex;align-items:center;flex-direction:row-reverse;cursor:-webkit-grabbing}.category-sort-handle span{margin-left:8px}.list-group-item{display:flex;flex-direction:row;justify-content:flex-start;align-items:center}.btn-label,.fa-stack,.payment-card{display:inline-block}.list-group-item .green-circle,.list-group-item .red-circle{margin-right:10px;width:22px}.list-group{margin:10px}.blue-background-class{opacity:.8}.ts-wrapper:not(.multiple) .ts-control .item{text-overflow:ellipsis;white-space:nowrap;width:calc(100% - 3rem);overflow:hidden}.custom-modal-footer{border-top:1px solid #ced4da;width:calc(100% + 2rem);margin-left:-1rem;padding-top:1rem;padding-left:1rem}@media (max-width :992px){.special_pricing_service_name{width:300px;white-space:normal}table thead tr th{font-size:15px}}.fa-stack{height:2em;line-height:2em;position:relative;vertical-align:middle;width:2.5em}.actions-icon{width:30px;height:30px}.btn-labeled{padding-top:0;padding-bottom:0}.btn-label{position:relative;left:-12px;padding:6px 12px;background:rgba(0,0,0,.15);border-radius:.375rem 0 0 .375rem}.badge{padding:.25em .4em;font-size:75%;font-weight:700;border-radius:.25rem}.badge-info{color:#fff;background-color:#17a2b8}.page-content{margin-bottom:50px;overflow-x:auto;overflow-y:auto;max-height:calc(100vh - 1.5rem - 48px - 2em)}.payment-card{margin-top:10px;margin-bottom:10px;padding:10px 5px;width:310px;border-radius:10px;box-shadow:rgba(50,50,93,.25) 0 6px 12px -2px,rgba(0,0,0,.3) 0 3px 7px -3px}@media only screen and (max-width:768px){.payment-card{display:block;width:100%}}.green-circle,.red-circle{width:22px;height:22px;border-radius:50%;cursor:pointer}.payment-card .method-logo{text-align:center;margin-left:20px}.payment-card .method_name{text-align:center;font-size:20px}.payment-card .vertical-line{width:100%;height:1px;background-color:grey;margin:10px 0}.payment-card .method-status{margin-top:-2px;margin-left:5px}.payment-card .method_min_max{text-align:center;font-weight:700}.payment-card .method_min_max .min{color:green}.payment-card .method_min_max .max{color:red}.green-circle{background-color:#29bf12;box-shadow:2px 2px 2px rgba(41,191,18,.4)}.red-circle{background-color:#ef233c;box-shadow:2px 2px 2px rgba(239,35,60,.4)}.payment-card .actions{text-align:center}.method-sort-handle{float:right;margin-top:-30px;margin-right:5px;font-size:25px}</style>
</head>
<body>
  <header>
    <div class="mobile-nav-close-btn">
      <div class="bar-top"></div>
      <div class="bar-middle"></div>
      <div class="bar-bottom"></div>
    </div>
    <div class="mobile-navbar">
      <div class="logo-details">
        <div class="logo_name"><img style="margin-top:-3px;" class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/flas.svg"); ?>">Super Rental </div>
      </div>
      <ul class="nav-list">
        <li class="search-box-li">
          <img class="search-nav-input-icon" height="30" width="30"
            src="<?php echo site_url("img/admin/search.png"); ?>">
          <input class="form-control search-nav-input" type="text" placeholder="Search...">
        </li>

        <li>
          <a class="nav-link" href="<?php echo site_url("admin"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/dashboard.svg"); ?>">
            <span class="links_name">Dashboard</span>
          </a>
        </li>
        <li class="dropdown <?php
        if (route(1) == "clients" || route(1) == "special-pricing" || route(1) == "fund-add-history") {
          echo "open-dropdown";
        }
        ?>">
          <a class="nav-link" href="javascript:void(0)">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/users.svg"); ?>">
            <span class="links_name">Users</span><i class="bi bi-chevron-right arrow"></i>
          </a>
          <div class="content">
            <a class="nav-link <?php if (route(1) == "clients") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/clients"); ?>"><img class="img-responsive nav-li-href-logo"
                height="30" width="30" src="<?php echo site_url("img/admin/users.svg"); ?>"> Manage Users</a>
      
          <a class="nav-link <?php if (route(1) == "fund-add-history") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/fund-add-history"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/payments.svg"); ?>">
            <span class="links_name">Fund Add History</span>
          </a>
      
            <a class="nav-link <?php if (route(1) == "special-pricing") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/special-pricing"); ?>"><img class="img-responsive nav-li-href-logo"
                height="30" width="30" src="<?php echo site_url("img/admin/special-pricing.svg"); ?>"> Special
              Pricing</a>
          </div>
        </li>
        <li class="dropdown <?php
        if (route(1) == "services" || route(1) == "update-prices" || route(1) == "bulk" || route(1) == "synced-logs" || route(1) == "category-sort") {
          echo "open-dropdown";
        }
        ?>">
          <a class="nav-link" href="javascript:void(0)">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/services.png"); ?>">
            <span class="links_name">Services</span><i class="bi bi-chevron-right arrow"></i>
          </a>
          <div class="content">
            <a class="nav-link  <?php if (route(1) == "services") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/services"); ?>"><img class="img-responsive nav-li-href-logo"
                height="30" width="30" src="<?php echo site_url("img/admin/services.png"); ?>"> Manage Services</a>
            <a class="nav-link  <?php if (route(1) == "category-sort") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/category-sort"); ?>"><img class="img-responsive nav-li-href-logo"
                height="30" width="30" src="<?php echo site_url("img/admin/category-sort.svg"); ?>"> Category Sort</a>
            <a class="nav-link  <?php if (route(1) == "update-prices") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/update-prices"); ?>"><img class="img-responsive nav-li-href-logo"
                height="30" width="30" src="<?php echo site_url("img/admin/update-prices.svg"); ?>"> Update Prices</a>
            <a class="nav-link  <?php if (route(1) == "bulk") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/bulk"); ?>"><img class="img-responsive nav-li-href-logo" height="30"
                width="30" src="<?php echo site_url("img/admin/bulk-services-edit.png"); ?>"> Bulk Services Editor</a>
            <a class="nav-link  <?php if (route(1) == "synced_logs") {
              echo "active";
            } ?>" href="<?php echo site_url("admin/synced_logs"); ?>"><img class="img-responsive nav-li-href-logo"
                height="30" width="30" src="<?php echo site_url("img/admin/synced-logs.svg"); ?>"> Seller Sync Logs</a>
          </div>
        </li>
        <li class="dropdown">
          <a class="nav-link" href="javascript:void(0)">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/orders.svg"); ?>">
            <span class="links_name">Orders</span><i class="bi bi-chevron-right arrow"></i>
          </a>
          <div class="content">
            <a class="nav-link" href="<?php echo site_url("admin/orders"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/orders.svg"); ?>"> Manage Orders</a>
            <a style="font-size:14px;" class="nav-link" href="<?php echo site_url("admin/tasks"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/cancel-refill-tasks.svg"); ?>"> Cancel and Refill Tasks</a>
          </div>
        </li>


        <li>
          <a class="nav-link" href="<?php echo site_url("admin/tickets"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/tickets.svg"); ?>">
            <span class="links_name">Support</span>
          </a>
        </li>

        <li class="dropdown">
          <a class="nav-link" href="javascript:void(0)">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/additionals.svg"); ?>">
            <span class="links_name">Additionals</span><i class="bi bi-chevron-right arrow"></i>
          </a>
          <div class="content">
            <a class="nav-link" href="<?php echo site_url("admin/referrals"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/referrals.svg"); ?>"> Affiliates</a>
            <a class="nav-link" href="<?php echo site_url("admin/broadcasts"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/broadcasts.svg"); ?>"> Broadcasts</a>
            <a class="nav-link" href="<?php echo site_url("admin/logs"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/logs.png"); ?>"> System Logs</a>

            <a class="nav-link" href="<?php echo site_url("admin/reports"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/reports.png"); ?>"> Statistics</a>
            <a class="nav-link" href="<?php echo site_url("admin/kuponlar"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/coupons.svg"); ?>"> Vouchers</a>
            <a class="nav-link" href="<?php echo site_url("admin/child-panels"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/child-panels.svg"); ?>"> Child Panels</a>
            <a class="nav-link" href="<?php echo site_url("admin/updates"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="<?php echo site_url("img/admin/payment-notifications.svg"); ?>"> Updates</a>
          </div>
        </li>
        
        <li>
          <a class="nav-link" href="<?php echo site_url("admin/appearance"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/appearance.svg"); ?>"><span class="links_name"> Appearance</span>
          </a>
        </li>

        <li class="dropdown <?php
        if (route(1) == "settings") {
          echo "open-dropdown";
        }
        ?>">
          <a class="nav-link" href="javascript:void(0)">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/settings.png"); ?>">
            <span class="links_name"> Settings</span><i class="bi bi-chevron-right arrow"></i>
          </a>
          <div class="content">
            <a class="nav-link" href="<?php echo site_url("admin/settings/general"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/general.svg"> General</a>
            <a class="nav-link" href="<?php echo site_url("admin/settings/providers"); ?>"><img
                class="img-responsive nav-li-href-logo" height="35" width="35"
                src="img/admin/sellers.svg"> Sellers</a>
            <a class="nav-link <?php if(route(2) == "paymentMethods"){echo "active";}?>" href="<?php echo site_url("admin/settings/paymentMethods"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/payment-methods.svg"> Payment Methods</a>
            <a class="nav-link" href="<?php echo site_url("admin/settings/modules"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/modules.svg"> Modules</a>
            <a class="nav-link" href="<?php echo site_url("admin/settings/subject"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/support-settings.svg"> Support</a>
            <a class="nav-link" href="<?php echo site_url("admin/settings/currency-manager"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/Site-currency-settings.svg"> Site Currency Manager</a>
            <a class="nav-link" href="<?php echo site_url("admin/settings/alert"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/notification-settings.svg"> Notifications</a>
            <a class="nav-link" href="<?php echo site_url("admin/settings/site_count"); ?>"><img
                class="img-responsive nav-li-href-logo" height="30" width="30"
                src="img/admin/fake-order.svg"> Fake orders</a>
          </div>
        </li>


        <li>
          <a class="nav-link" href="<?php echo site_url("admin/manager"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/manager.svg"); ?>"><span class="links_name"> Manager</span>
          </a>
        </li>


        <li>
          <a class="nav-link" href="<?php echo site_url("admin/account"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/admin.png"); ?>"><span class="links_name"> Admin Account</span>
          </a>
        </li>

        <li>
          <a class="nav-link" href="<?php echo site_url("admin/logout"); ?>">
            <img class="img-responsive nav-li-href-logo" height="30" width="30"
              src="<?php echo site_url("img/admin/logout.svg"); ?>"><span class="links_name"> Logout</span>
          </a>
        </li>

      </ul>
    </div>
  </header>