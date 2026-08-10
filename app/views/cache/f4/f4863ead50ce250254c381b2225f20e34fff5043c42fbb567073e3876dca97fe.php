<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* header.twig */
class __TwigTemplate_875b442eb5787aab6275663df33d3f004020bbe7cfcd03c6156498676a7449e4 extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<!DOCTYPE html>
";
        // line 2
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((($__internal_compile_0 = ($context["site"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["languages"] ?? null) : null));
        foreach ($context['_seq'] as $context["_key"] => $context["lang"]) {
            // line 3
            if ((($__internal_compile_1 = $context["lang"]) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["active"] ?? null) : null)) {
                // line 4
                echo "<html lang=\"";
                echo (($__internal_compile_2 = $context["lang"]) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["code"] ?? null) : null);
                echo "\">
";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['lang'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 7
        echo "<head>
  <base href=\"";
        // line 8
        echo (($__internal_compile_3 = ($context["site"] ?? null)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["url"] ?? null) : null);
        echo "\">

  
  <title>";
        // line 11
        if (($context["pagetitle"] ?? null)) {
            echo "  ";
            echo ($context["pagetitle"] ?? null);
            echo "  ";
        } else {
            echo " ";
            echo ($context["title"] ?? null);
            echo " ";
        }
        echo " </title>
  <meta property=\"og:title\" content=\"";
        // line 12
        if (($context["pagetitle"] ?? null)) {
            echo "  ";
            echo ($context["pagetitle"] ?? null);
            echo "  ";
        } else {
            echo " ";
            echo ($context["title"] ?? null);
            echo " ";
        }
        echo "\" />
  <meta charset=\"utf-8\">
  <meta http-equiv=\"X-UA-Compatible\" content=\"IE=edge\">
  <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">
  <link rel=\"canonical\" href=\"";
        // line 16
        echo (($__internal_compile_4 = ($context["site"] ?? null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["url"] ?? null) : null);
        echo "\">
  <link rel=\"sitemap\" type=\"application/xml\" title=\"Sitemap\" href=\"/sitemap.xml\">
  <meta property=\"og:locale\" content=\"fr_CD\">
  <meta property=\"og:type\" content=\"website\">
  <meta name=\"keywords\" content=\"";
        // line 20
        if (($context["pagekeywords"] ?? null)) {
            echo " ";
            echo ($context["pagekeywords"] ?? null);
            echo " ";
        } else {
            echo " ";
            echo (($__internal_compile_5 = ($context["site"] ?? null)) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["keywords"] ?? null) : null);
            echo "  ";
        }
        echo "\">
  <meta name=\"description\" content=\"";
        // line 21
        if (($context["pagedescription"] ?? null)) {
            echo " ";
            echo ($context["pagedescription"] ?? null);
            echo " ";
        } else {
            echo " ";
            echo (($__internal_compile_6 = ($context["site"] ?? null)) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["description"] ?? null) : null);
            echo "  ";
        }
        echo "\">
  <meta property=\"og:description\" content=\"";
        // line 22
        if (($context["pagedescription"] ?? null)) {
            echo " ";
            echo ($context["pagedescription"] ?? null);
            echo " ";
        } else {
            echo " ";
            echo (($__internal_compile_7 = ($context["site"] ?? null)) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7["description"] ?? null) : null);
            echo "  ";
        }
        echo "\" />
  <meta property=\"og:image\" content=\"";
        // line 23
        echo (($__internal_compile_8 = ($context["site"] ?? null)) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8["logo"] ?? null) : null);
        echo "\" />
  <meta property=\"og:site_name\" content=\"Legrand\" />
  <meta property=\"og:url\" content=\"";
        // line 25
        echo (($__internal_compile_9 = ($context["site"] ?? null)) && is_array($__internal_compile_9) || $__internal_compile_9 instanceof ArrayAccess ? ($__internal_compile_9["url"] ?? null) : null);
        echo "\" />
  <meta name=\"twitter:card\" content=\"summary_large_image\" />
  <meta name=\"twitter:title\" content=\"";
        // line 27
        if (($context["pagetitle"] ?? null)) {
            echo " ";
            echo ($context["pagetitle"] ?? null);
            echo " ";
        } else {
            echo " ";
            echo ($context["title"] ?? null);
            echo " ";
        }
        echo "\" />
  <meta name=\"twitter:description\" content=\"";
        // line 28
        if (($context["pagedescription"] ?? null)) {
            echo " ";
            echo ($context["pagedescription"] ?? null);
            echo " ";
        } else {
            echo " ";
            echo (($__internal_compile_10 = ($context["site"] ?? null)) && is_array($__internal_compile_10) || $__internal_compile_10 instanceof ArrayAccess ? ($__internal_compile_10["description"] ?? null) : null);
            echo " ";
        }
        echo "\" />
  <meta name=\"twitter:image\" content=\"";
        // line 29
        echo (($__internal_compile_11 = ($context["site"] ?? null)) && is_array($__internal_compile_11) || $__internal_compile_11 instanceof ArrayAccess ? ($__internal_compile_11["logo"] ?? null) : null);
        echo "\" />
  <meta name=\"robots\" content=\"index, follow\" />
  <meta name=\"author\" content=\"Legrand\" />
  <meta name=\"geo.region\" content=\"CD\" />
  <meta name=\"geo.placename\" content=\"République Démocratique du Congo\" />
  <meta name=\"language\" content=\"French\" />
  <meta name=\"revisit-after\" content=\"7 days\" />
  ";
        // line 36
        if ((($__internal_compile_12 = ($context["site"] ?? null)) && is_array($__internal_compile_12) || $__internal_compile_12 instanceof ArrayAccess ? ($__internal_compile_12["favicon"] ?? null) : null)) {
            // line 37
            echo "    <link rel=\"shortcut icon\" type=\"image/ico\" href=\"";
            echo (($__internal_compile_13 = ($context["site"] ?? null)) && is_array($__internal_compile_13) || $__internal_compile_13 instanceof ArrayAccess ? ($__internal_compile_13["favicon"] ?? null) : null);
            echo "\" />
  ";
        }
        // line 39
        echo "
        
  <script src=\"https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js\"></script>
  <link rel=\"stylesheet\" href=\"https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css\" />
  
  <link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css\" />

    <link rel=\"stylesheet\" type=\"text/css\" href=\"/app/views/GreenSMM/css/xn3xyxzgfy8kn8r3.css\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"/app/views/GreenSMM/css/6p0wdo6s3yzt84tk.css\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datetimepicker/4.17.47/css/bootstrap-datetimepicker.min.css\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\" crossorigin=\"anonymous\">
    <link rel=\"stylesheet\" type=\"text/css\" href=\"/app/views/GreenSMM/css/tp2jssyocan4ndm1.css\">
  
  <!--  Some CDN Link From Old Theme -->
  <link rel=\"stylesheet\"
    href=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-select/1.13.2/css/bootstrap-select.min.css\">
  <script src=\"https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.2/umd/popper.min.js\"></script>

  <link rel=\"stylesheet\" type=\"text/css\" href=\"https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css\" />
  <link rel=\"stylesheet\" type=\"text/css\"
    href=\"https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css\" />
    <script src=\"https://code.jquery.com/jquery-3.5.1.min.js\"></script>
    <!--  /// Some CDN Link From Old Theme -->
  <script src=\"https://unpkg.com/@lottiefiles/lottie-player@latest/dist/lottie-player.js\"></script>
<style>
    #block_95 .card{
    background: #fff !important;
    box-shadow: 0 5px 5px rgba(7, 7, 7, 10%) !important;
    margin-bottom: 15px !important;
    border-radius: 15px !important;
    border: 1px solid transparent !important;
    margin-top:100px !important;
    min-width:100% !important;
    padding:10px !important;
}
#main_container .content_area{
    display: block !important ;
}
#block_95 textarea{
    height:150px;
}
</style>
</head>
\t";
        // line 82
        if ((($__internal_compile_14 = ($context["user"] ?? null)) && is_array($__internal_compile_14) || $__internal_compile_14 instanceof ArrayAccess ? ($__internal_compile_14["auth"] ?? null) : null)) {
            // line 83
            echo "\t
          <body class=\"dashboard\" id=\"body\" >
    <script>
    if (localStorage.getItem('techSMMCurrentMode')) {
      const bodyFire2 = document.getElementById('body');
      bodyFire2.classList.add('nightmode');
    }
  </script>
  
  <style>
      img{
     width:100%;
 }
  </style>
  <main id=\"main_container\">
    <div class=\"sidebar\">
      <div class=\"sidebar_top\">
        <button class=\"close_btn_phone\" onclick=\"toggleSidebar()\">
          <i class=\"fas fa-times\"></i>
        </button>
        <div class=\"logo\">
          <a href=\"/\">
            <img src=\"";
            // line 105
            echo (($__internal_compile_15 = ($context["site"] ?? null)) && is_array($__internal_compile_15) || $__internal_compile_15 instanceof ArrayAccess ? ($__internal_compile_15["logo"] ?? null) : null);
            echo "\" class=\"logo_img img-fluid\" alt=\"Legrand - Plateforme SMM RDC\" title=\"Legrand\">
          </a>
        </div>
        <div class=\"user_data\">
          <div class=\"user_badges\" data-bs-toggle=\"modal\" data-bs-target=\"#GGstaticBackdrop\">
            <h4>
                            NEW
                          </h4>
          </div>
          <div class=\"total_data\">
            <div class=\"user_wrap\">
              <div class=\"v2_avatar\">
                <img src=\"/app/views/GreenSMM/css/img/avatar.png\" class=\"img-fluid\" alt=\"Tech SMM Avatar\" id=\"profile1\">
              </div>
              <div class=\"v2_user_info\">
                <h5>";
            // line 120
            echo (($__internal_compile_16 = ($context["user"] ?? null)) && is_array($__internal_compile_16) || $__internal_compile_16 instanceof ArrayAccess ? ($__internal_compile_16["username"] ?? null) : null);
            echo "</h5>
              </div>
            </div>
            <div class=\"user_balance balance-dropdown__name balance-dropdown__toggle\" data-toggle=\"dropdown\" aria-expanded=\"false\">
              <span class=\"balance\">";
            // line 124
            echo (($__internal_compile_17 = ($context["user"] ?? null)) && is_array($__internal_compile_17) || $__internal_compile_17 instanceof ArrayAccess ? ($__internal_compile_17["balance"] ?? null) : null);
            echo "
              ";
            // line 125
            if ((($context["site_currency_converter"] ?? null) == "1")) {
                // line 126
                echo "              <style>
                  .balance-dropdown__container{
                      top: -140px !important;
    left: 0px !important;
    padding: 10px;
    align-items:center;
    max-height:120px;
    width:90%;
    overflow-y:scroll;
                  }
              </style>
            <div class=\"balance-dropdown-container component_balance_dropdown\">
    <div class=\"balance-dropdown\">
        <ul class=\"balance-dropdown__container dropdown-menu\" id=\"currencies-list\">
            <li class=\"balance-dropdown__item\">
";
                // line 141
                echo ($context["currencies_dropdown"] ?? null);
                echo "
        </ul>
    </div>
</div>
     ";
            } else {
                // line 146
                echo "\t";
            }
            echo "   
              
              
              
              </span>
            </div>
          </div>
        </div>

      </div>
      <div class=\"sidebar_bottom\">
        <div class=\"sidebar_menu\">
                              <a ";
            // line 158
            if ((($context["active_menu"] ?? null) == "neworder")) {
                echo " href=\"/\" class=\"menu_item active\" ";
            }
            echo " class=\"menu_item\" href=\"/\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-shopping-basket\"></i>
            </div>
                        <div class=\"menu_name\">
              New order
            </div>
          </a>
             <a ";
            // line 166
            if ((($context["active_menu"] ?? null) == "completed_orders")) {
                echo " href=\"/analytics\" class=\"menu_item active\" ";
            }
            echo " class=\"menu_item\" href=\"/analytics\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-box\"></i>
            </div>
                        <div class=\"menu_name\">
              Commandes terminées
            </div>
          </a>
          
                              <a ";
            // line 175
            if ((($context["active_menu"] ?? null) == "massorder")) {
                echo " href=\"/massorder\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/massorder\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-boxes\"></i>
            </div>
                        <div class=\"menu_name\">
              Commande groupée
            </div>
          </a>
          
                               <a ";
            // line 184
            if ((($context["active_menu"] ?? null) == "refill")) {
                echo " href=\"/refill\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/refill\">
                        <div class=\"menu_icon\">
              <i class=\"fad fa-redo\"></i>
            </div>
                        <div class=\"menu_name\">
              Recharge
            </div>
          </a>
          
                               <a ";
            // line 193
            if ((($context["active_menu"] ?? null) == "refunds")) {
                echo " href=\"/refunds\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/refunds\">
                        <div class=\"menu_icon\">
              <i class=\"fad fa-ban\"></i>
            </div>
                        <div class=\"menu_name\">
              Remboursements
            </div>
          </a>
          
                    
                               <a ";
            // line 203
            if ((($context["active_menu"] ?? null) == "orders")) {
                echo " href=\"/orders\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/orders\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-truck\"></i>
            </div>
                        <div class=\"menu_name\">
              Historique des commandes
            </div>
          </a>
          
                               <a ";
            // line 212
            if ((($context["active_menu"] ?? null) == "link-fixer")) {
                echo " href=\"/link-fixer\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/link-fixer.php\">
                        <div class=\"menu_icon\">
              <i class=\"fa fa-link\"></i>
            </div>
                        <div class=\"menu_name\">
             Fixateur de liens
            </div>
          </a>
          
                    
                    
                              <a ";
            // line 223
            if ((($context["active_menu"] ?? null) == "services")) {
                echo " href=\"/services\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/services\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-list\"></i>
            </div>
                        <div class=\"menu_name\">
              Services
            </div>
          </a>
          
                    
                              <a ";
            // line 233
            if ((($context["active_menu"] ?? null) == "addfunds")) {
                echo " href=\"/addfunds\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/addfunds\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-wallet\"></i>
            </div>
                        <div class=\"menu_name\">
              Ajouter des fonds
            </div>
          </a>
          
                              <a ";
            // line 242
            if ((($context["active_menu"] ?? null) == "ai-support")) {
                echo " href=\"https://t.me/smmveiorg_bot\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"https://t.me/smmveiorg_bot\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-robot\"></i>
            </div>
                        <div class=\"menu_name\">
              Support IA
            </div>
          </a>
          
                              <a ";
            // line 251
            if ((($context["active_menu"] ?? null) == "tickets")) {
                echo " href=\"/tickets\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/tickets\">
                        <div class=\"menu_icon\">
              <i class=\"fas fa-ticket-alt\"></i>
            </div>
                        <div class=\"menu_name\">
              Support Tickets <span class=\"badge\" style=\"background-color: #ffb500\">";
            // line 256
            echo ($context["ticketCount"] ?? null);
            echo "</span>
            </div>
          </a>
          
                    
                    
                    
                    
                    
          
          <!-- Show More Btn Codes Start -->
          <div id=\"show_more_wrap\">
            <button type=\"button\" class=\"\" id=\"showMore\">
              <div class=\"menu_name\">
                Show More
              </div>
              <div class=\"menu_icon\">
                <i class=\"fas fa-angle-down\"></i>
              </div>
            </button>

          </div>

          <div id=\"more_menu\">
                                                                                                                                                                                                                                                            <a ";
            // line 280
            if ((($context["active_menu"] ?? null) == "child-panels")) {
                echo " href=\"/\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/child-panels\">
                            <div class=\"menu_icon\">
                <i class=\"fas fa-server\"></i>
              </div>
                            <div class=\"menu_name\">
                Child panel
              </div>
            </a>


                                                <a ";
            // line 290
            if ((($context["active_menu"] ?? null) == "refer")) {
                echo " href=\"/refer\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/refer\">
                            <div class=\"menu_icon\">
                <i class=\"fas fa-user-tag\"></i>
              </div>
                            <div class=\"menu_name\">
                Affiliates
              </div>
            </a>
                                                 <a ";
            // line 298
            if ((($context["active_menu"] ?? null) == "updates")) {
                echo " href=\"/updates\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/updates\">
                            <div class=\"menu_icon\">
                <i class=\"fas fa-bullhorn\"></i>
              </div>
                            <div class=\"menu_name\">
                Updates
              </div>
            </a>
                                                <a ";
            // line 306
            if ((($context["active_menu"] ?? null) == "api")) {
                echo " href=\"/api\" class=\"menu_item  active\" ";
            }
            echo " class=\"menu_item\" href=\"/api\">
                            <div class=\"menu_icon\">
                <i class=\"fas fa-file-code\"></i>
              </div>
                            <div class=\"menu_name\">
                API
              </div>
            </a>
                                  </div>
          <!-- Show More Btn Codes End -->
        </div>
      </div>
    </div>
            <div class=\"content_area\">
      <div class=\"top_header\">
        <div class=\"top_head_wrap\">
          <div class=\"item\">
            <button class=\"sidebar_menu_icon\" onclick=\"toggleSidebar()\">
              <i class=\"fas fa-bars\"></i>
            </button>
            <div class=\"logo_off_nav\">
              <a href=\"/\">
               \t";
            // line 328
            if ((($__internal_compile_18 = ($context["site"] ?? null)) && is_array($__internal_compile_18) || $__internal_compile_18 instanceof ArrayAccess ? ($__internal_compile_18["logo"] ?? null) : null)) {
                // line 329
                echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<img style=\"max-width:200px;\" src=\"";
                echo (($__internal_compile_19 = ($context["site"] ?? null)) && is_array($__internal_compile_19) || $__internal_compile_19 instanceof ArrayAccess ? ($__internal_compile_19["logo"] ?? null) : null);
                echo "\" alt=\"Legrand\" title=\"Legrand\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
            } else {
                // line 331
                echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"text-transform: \">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 24px\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"letter-spacing: 1.0px\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<strong style=\"font-weight: bold\">";
                // line 335
                echo (($__internal_compile_20 = ($context["site"] ?? null)) && is_array($__internal_compile_20) || $__internal_compile_20 instanceof ArrayAccess ? ($__internal_compile_20["site_name"] ?? null) : null);
                echo "</strong>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
            }
            // line 341
            echo "              </a>
            </div>
          </div>
          <div class=\"item\">

          </div>
          <div class=\"item user_settings\">

            
            <a href=\"javascript:void(0)\" class=\"day_night_btn\" onclick=\"toggleThemeMode()\">
              <span class=\"active_circle\"></span>
              <span class=\"night_mode\"> <i class=\"fas fa-moon\"></i> </span>
              <span class=\"day_mode\"><i class=\"fas fa-sun\"></i></span>
            </a>
            
\t\t\t\t\t\t\t\t\t\t\t\t\t
            <div class=\"dropdown\">
              <button class=\"btn btn-secondary dropdown-toggle btn_profiles\" type=\"button\" id=\"dropdownMenuButton1\"
                data-bs-toggle=\"dropdown\" aria-expanded=\"false\">
                <span class=\"user_top_avatar\">
                 <img src=\"/app/views/GreenSMM/css/img/avatar.png\" width=\"40px\" class=\"img-fluid\" alt=\"\" id=\"profile2\">
                </span>
              </button>
              <div class=\"dropdown-menu settings_drop\" aria-labelledby=\"dropdownMenuButton1\">
                <div class=\"user_menu_wraper\">
                  <div class=\"user__menu\">
                    <a class=\"user_menu__item\" href=\"/account\">
                      <span class=\"user_menu_icon\"><i class=\"fas fa-cog\"></i></span>
                      <span class=\"user_menu_text\">Settings</span>
                    </a>

                    
                    <a class=\"user_menu__item\" href=\"/logout\">
                      <span class=\"user_menu_icon\"> <i class=\"fas fa-sign-out\"></i></span>
                      <span class=\"user_menu_text\">Déconnexion</span>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    
";
        } else {
            // line 386
            echo "<body class=\"noAuth\" >
      <nav class=\"navbar navbar-expand-lg navbar-light bg-light navbar-static-top\" id=\"navbar\">
      <div class=\"container\">
        <a class=\"navbar-brand\" href=\"/\">
         \t";
            // line 390
            if ((($__internal_compile_21 = ($context["site"] ?? null)) && is_array($__internal_compile_21) || $__internal_compile_21 instanceof ArrayAccess ? ($__internal_compile_21["logo"] ?? null) : null)) {
                // line 391
                echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<img style=\"max-width:200px;\" src=\"";
                echo (($__internal_compile_22 = ($context["site"] ?? null)) && is_array($__internal_compile_22) || $__internal_compile_22 instanceof ArrayAccess ? ($__internal_compile_22["logo"] ?? null) : null);
                echo "\" alt=\"Legrand\" title=\"Legrand\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
            } else {
                // line 393
                echo "\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"text-transform: \">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"font-size: 24px\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span style=\"letter-spacing: 1.0px\">
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t<strong style=\"font-weight: bold\">";
                // line 397
                echo (($__internal_compile_23 = ($context["site"] ?? null)) && is_array($__internal_compile_23) || $__internal_compile_23 instanceof ArrayAccess ? ($__internal_compile_23["site_name"] ?? null) : null);
                echo "</strong>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t</span>
\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t\t";
            }
            // line 403
            echo "        </a>
        <button class=\"navbar-toggler nav__icons\" type=\"button\" onclick=\"navToggleMob()\">
         
        </button>
        <div class=\"collapse navbar-collapse\" id=\"navbarSupportedContent\">
          <ul class=\"navbar-nav ms-auto mb-2 mb-lg-0\">
                                    <li class=\"nav-item d-flex align-items-center\">
              <a ";
            // line 410
            if ((($context["active_menu"] ?? null) == "login")) {
                echo " href=\"/\" class=\"nav-link active\" ";
            }
            echo " class=\"nav-link\" href=\"/\"> 
                                <i class=\"navbar-icon fas fa-address-book\"></i>
                                Se connecter
              </a>
            </li>
                                                                                    <li class=\"nav-item d-flex align-items-center\">
              <a ";
            // line 416
            if ((($context["active_menu"] ?? null) == "services")) {
                echo " href=\"/services\" class=\"nav-link active\" ";
            }
            echo " class=\"nav-link \" href=\"/services\">
                                <i class=\"navbar-icon fas fa-server\"></i>
                                Services
              </a>
            </li>
                                                <li class=\"nav-item d-flex align-items-center\">
              <a ";
            // line 422
            if ((($context["active_menu"] ?? null) == "api")) {
                echo " href=\"/api\" class=\"nav-link active\" ";
            }
            echo " class=\"nav-link \" href=\"/api\">
                                <i class=\"navbar-icon fas fa-wifi\"></i>
                                API
              </a>
            </li>
                                                <li class=\"nav-item d-flex align-items-center\">
              <a ";
            // line 428
            if ((($context["active_menu"] ?? null) == "blog")) {
                echo " href=\"/blog\" class=\" nav-link active\" ";
            }
            echo " class=\"nav-link \" href=\"/blog\">
                                <i class=\"navbar-icon fas fa-address-card\"></i>
                                Blog
              </a>
            </li>
                                                
                                    <li class=\"nav-item btn__sp__nav\">
                                        <a ";
            // line 435
            if ((($context["active_menu"] ?? null) == "signup")) {
                echo " href=\"/signup\" class=\"btn btn-primary btn-gradient  active\" ";
            }
            echo " class=\" btn btn-primary btn-gradient\" href=\"/signup\">
              <span class=\"sp__icon\">
                  <i class=\"fas fa-user\"></i>
                </span>
                <span class=\"btn__txt\">
                  Sign Up
                </span>
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <div id=\"navMob\">
      <button id=\"cls\" onclick=\"navToggleMob()\"><i class=\"fas fa-times\"></i></button>
      <div class=\"nav_content\">
        <div class=\"menu\">
          <ul class=\"menu_mobs\">
                        <li> <a ";
            // line 453
            if ((($context["active_menu"] ?? null) == "login")) {
                echo " href=\"/\" class=\"  active\" ";
            }
            echo " class=\"\" href=\"/\"> 
                                <i class=\"navbar-icon fas fa-address-book\"></i>
                                Se connecter
              </a></li>
                        <li><a ";
            // line 457
            if ((($context["active_menu"] ?? null) == "signup")) {
                echo " href=\"/signup\" class=\"  active\" ";
            }
            echo " class=\" \" href=\"/signup\">
                                <i class=\"navbar-icon far fa-address-book\"></i>
                                Sign up
              </a></li>
                        <li><a ";
            // line 461
            if ((($context["active_menu"] ?? null) == "services")) {
                echo " href=\"/services\" class=\"  active\" ";
            }
            echo " class=\" \" href=\"/services\">
                                <i class=\"navbar-icon fas fa-server\"></i>
                                Services
              </a></li>
                        <li><a ";
            // line 465
            if ((($context["active_menu"] ?? null) == "api")) {
                echo " href=\"/api\" class=\"  active\" ";
            }
            echo " class=\" \" href=\"/api\">
                                <i class=\"navbar-icon fas fa-wifi\"></i>
                                API
              </a></li>
                        <li><a ";
            // line 469
            if ((($context["active_menu"] ?? null) == "blog")) {
                echo " href=\"/blog\" class=\"  active\" ";
            }
            echo " class=\" \" href=\"/blog\">
                                <i class=\"navbar-icon fas fa-address-card\"></i>
                                Blog
              </a></li>
                        
                      </ul>
        </div>
        <div class=\"btn\">
          <a href=\"/\">Se connecter</a>
          <a href=\"/signup\">Sign up <span class=\"btn_icon\"><i class=\"fas fa-arrow-right\"></i></span></a>
        </div>
      </div>
    </div>

";
        }
    }

    public function getTemplateName()
    {
        return "header.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  790 => 469,  781 => 465,  772 => 461,  763 => 457,  754 => 453,  731 => 435,  719 => 428,  708 => 422,  697 => 416,  686 => 410,  677 => 403,  668 => 397,  662 => 393,  656 => 391,  654 => 390,  648 => 386,  601 => 341,  592 => 335,  586 => 331,  580 => 329,  578 => 328,  551 => 306,  538 => 298,  525 => 290,  510 => 280,  483 => 256,  473 => 251,  459 => 242,  445 => 233,  430 => 223,  414 => 212,  400 => 203,  385 => 193,  371 => 184,  357 => 175,  343 => 166,  330 => 158,  314 => 146,  306 => 141,  289 => 126,  287 => 125,  283 => 124,  276 => 120,  258 => 105,  234 => 83,  232 => 82,  187 => 39,  181 => 37,  179 => 36,  169 => 29,  157 => 28,  145 => 27,  140 => 25,  135 => 23,  123 => 22,  111 => 21,  99 => 20,  92 => 16,  77 => 12,  65 => 11,  59 => 8,  56 => 7,  46 => 4,  44 => 3,  40 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "header.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/header.twig");
    }
}
