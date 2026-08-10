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

/* services.twig */
class __TwigTemplate_ddd7af7ea04efd61f92939e22641ac6137311160b644ffe1fb33b1079d871c82 extends \Twig\Template
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
        $this->loadTemplate("header.twig", "services.twig", 1)->display($context);
        // line 2
        echo "<div class=\"content\">
<main id=\"notLogin\">
<style>.order_id{display:block;background:var(--text-primary);text-align:center;color:#fff;padding:3px 8px;border-radius:10px}.dashboard #notLogin{padding-top:20px}#notLogin{padding-top:100px}</style>
<div id=\"top_services\">
    <div class=\"container-fluid\">
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"card card_v2\">
                    <div class=\"card-body\">
                        <div class=\"row\">
                            <div class=\"col-md-12\">
                          
                                <div class=\"service_button_filter\" id=\"dc-body\">
                                
                                     <button
                      class=\"btn_ser_filter services-list-filter btn_filter btn_all\"
                      onclick=\"javascript:filterService('All')\"
                      data-services-filter=\"\">
                      
                      All
                    </button>

                                    <button class=\"btn_ser_filter btn_filter btn_fb services-list-filter btn_filter btn_fb\"  onclick=\"btnFltr(this)\"
                                  data-services-filter=\"facebook\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-facebook\"></i>
                                        </span>
                                        
                                    </button>

                                    <button class=\"btn_ser_filter btn_tw btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                    data-services-filter=\"twitter\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-twitter\"></i>
                                        </span>
                                        
                                    </button>

                                    <button class=\"btn_ser_filter btn_ig btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                    data-services-filter=\"instagram\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-instagram\"></i>
                                        </span>
                                        
                                    </button>


                                    <button class=\"btn_ser_filter btn_in btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                    data-services-filter=\"linkedin\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-linkedin\"></i>
                                        </span>
                                        
                                    </button>


                                    <button class=\"btn_ser_filter btn_yt btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                   data-services-filter=\"youtube\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-youtube\"></i>
                                        </span>
                                        
                                    </button>


                                    <button class=\"btn_ser_filter btn_sp btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                    data-services-filter=\"spotify\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-spotify\"></i>
                                        </span>
                                        
                                    </button>
                                    <button class=\"btn_ser_filter btn_in btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                    data-services-filter=\"telegram\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-telegram\"></i>
                                        </span>
                                        
                                    </button>
                                    <button class=\"btn_ser_filter btn_tik btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                  data-services-filter=\"tiktok\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-tiktok\"></i>
                                        </span>
                                    </button>

                                    <button class=\"btn_ser_filter btn_dis btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                   data-services-filter=\"discord\">
                                        <span class=\"icon\">
                                            <i class=\"fab fa-discord\"></i>
                                        </span>
                                    </button>

                                    <button class=\"btn_ser_filter btn_traffic btn_filter btn_fb services-list-filter btn_filter\"  onclick=\"btnFltr(this)\"
                                   data-services-filter=\"traffic\">
                                        <span class=\"icon\">
                                            <i class=\"fas fa-globe\"></i>
                                        </span>
                                    </button>

                                </div>
                            </div>
                        </div>
                        <div class=\"row\">
                           <div class=\"col-md-6\">
                                <div class=\"row\">
                                    <div class=\"col-md-6\">
                                        <div class=\"drop_dwon_menu\" id=\"cat_filter\">
                                            <div class=\"text_filter\">
                                            <button class=\"btn mr-2 cat_btn_filter mb-sm-2 \" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\">
                                                <i class=\"fas fa-filter\"></i> <span data-filter-active-category=\"true\">Select Catogery</span>
                                                <i class=\"fas fa-sort-down\"></i>
                                            </button>
                                            
                                            <ul class=\"dropdown-menu\">
                    <li>
            <a class=\"dropdown-item\" href=\"#\" data-filter-category-id=\"All\">All</a>
          \t";
        // line 119
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["serviceCategory"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["category"]) {
            // line 120
            echo "          \t<a class=\"dropdown-item\" href=\"services#\" data-filter-category-id=\"";
            echo (($__internal_compile_0 = $context["category"]) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["category_id"] ?? null) : null);
            echo "\"
          \tdata-filter-category-name=\"";
            // line 121
            echo (($__internal_compile_1 = $context["category"]) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["category_name"] ?? null) : null);
            echo "\">";
            echo (($__internal_compile_2 = $context["category"]) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["category_name"] ?? null) : null);
            echo "</a>";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['category'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 122
        echo "      </ul></div>
                                        </div>
                                    </div>
                                    ";
        // line 125
        if (((($__internal_compile_3 = ($context["user"] ?? null)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["auth"] ?? null) : null) == 0)) {
            // line 126
            echo "<div class=\"col-md-6\">
<ul id=\"currency_changer\">
                                                <li>
                                             <div class=\"dropdown \">
                                                        <button class=\"btn btn-primary dropdown-toggle mb-sm-2\" role=\"button\" data-toggle=\"dropdown\" aria-haspopup=\"true\" aria-expanded=\"false\" id=\"currencyToggler\">
                                                            <span class=\"services-filter__active-currency\">";
            // line 131
            echo ($context["site_base_currency"] ?? null);
            echo "</span>
                                                        </button>
                                                        <ul class=\"dropdown-menu\" id=\"currencies-list\">
                                                                                   ";
            // line 134
            echo ($context["without_login_currencies_item"] ?? null);
            echo "                                         
                                                                </li>
                                                                                                                    </ul>
                                                    </div>
                                                </li>
                                            </ul>
                                                                            </div>
";
        }
        // line 142
        echo "                                    
                                </div>
                           </div>
                            <div class=\"col-md-6\"><div class=\"input-group\">
                                <input type=\"text\" class=\"form-control\" placeholder=\"Search\" id=\"search\" data-search-service=\"#service-table\">
                                <span class=\"input-group-btn\">
                                    <button type=\"button\" class=\"btn btn-default\" data-filter-serch-btn=\"true\">
                                        <i class=\"fa fa-search\" aria-hidden=\"true\"></i>
                                    </button>
                                </span>
                            </div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<section id=\"table\" class=\"services_page\">
    <div class=\"container-fluid\">
        <div class=\"row\">
            <div class=\"col-md-12\">
                <div class=\"responsive-table def_table\">
                       <div class=\"table-responsive\">
                            
                             <table id=\"serv-table\" class=\"app-mtable table results table-mobile-cards\">
                                 
                                <thead>
                                            <th>ID</th>
                                            <th>Service</th>
                                            <th>Rate per 1000</th>
                                            <th>Min order</th>
                                            <th>Max order</th>
                                                                                            <th class=\"nowrap\">Average time
                                                    <span class=\"fa fa-exclamation-circle\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"The average time is based on 10 latest completed orders per 1000 quantity.\"></span>
                                                </th>
                                                                                                                                        <th>Description</th>
                                                                                 <th>Order</th> 
                                        </tr>
                                    </thead>

                                        <tbody>
                                        
                                                ";
        // line 186
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["serviceCategory"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["category"]) {
            // line 187
            echo "                                                                                                            <tr class=\"catetitle\" data-filter-table-category-id=\"";
            echo (($__internal_compile_4 = $context["category"]) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["category_id"] ?? null) : null);
            echo "\">
                                                <td colspan=\"8\">
                                                   <div class=\"catetitle \">
                                                       <div class=\"style-bg-primary-alpha-20 style-text-primary services-category text-center\" style=\"font-size:20px; font-weight:bold;\">
                                                           ";
            // line 191
            echo (($__internal_compile_5 = $context["category"]) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["category_name"] ?? null) : null);
            echo "
                                                       </div>
                                                   </div>
                                                </td>
                                            </tr>
                                           
                                                

                ";
            // line 199
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((($__internal_compile_6 = $context["category"]) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["services"] ?? null) : null));
            foreach ($context['_seq'] as $context["_key"] => $context["service"]) {
                // line 200
                echo "                   <tr data-filter-table-category-id=\"";
                echo (($__internal_compile_7 = $context["category"]) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7["category_id"] ?? null) : null);
                echo "\">
                   <td data-filter-table-service-id=\"";
                // line 201
                echo (($__internal_compile_8 = $context["category"]) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8["category_id"] ?? null) : null);
                echo "\">
              
                    
                    
                            <span id=\"servis_id\" class=\"order_id\">";
                // line 205
                echo (($__internal_compile_9 = $context["service"]) && is_array($__internal_compile_9) || $__internal_compile_9 instanceof ArrayAccess ? ($__internal_compile_9["service_id"] ?? null) : null);
                echo "</span>
                                            </td>
                                            
                                    <td class=\"service-name\" data-filter-table-service-name=\"true\">";
                // line 208
                echo (($__internal_compile_10 = $context["service"]) && is_array($__internal_compile_10) || $__internal_compile_10 instanceof ArrayAccess ? ($__internal_compile_10["service_name"] ?? null) : null);
                echo "
                                    </td>
                                    <td>
                      
";
                // line 212
                if (((($__internal_compile_11 = ($context["user"] ?? null)) && is_array($__internal_compile_11) || $__internal_compile_11 instanceof ArrayAccess ? ($__internal_compile_11["auth"] ?? null) : null) == "1")) {
                    echo " 
";
                    // line 213
                    echo (($__internal_compile_12 = $context["service"]) && is_array($__internal_compile_12) || $__internal_compile_12 instanceof ArrayAccess ? ($__internal_compile_12["service_price"] ?? null) : null);
                    echo "

";
                } else {
                    // line 216
                    echo (($__internal_compile_13 = $context["service"]) && is_array($__internal_compile_13) || $__internal_compile_13 instanceof ArrayAccess ? ($__internal_compile_13["without_login_service_price"] ?? null) : null);
                    echo "
";
                }
                // line 218
                echo "                    </td>
                                    <td>";
                // line 219
                echo (($__internal_compile_14 = $context["service"]) && is_array($__internal_compile_14) || $__internal_compile_14 instanceof ArrayAccess ? ($__internal_compile_14["service_min"] ?? null) : null);
                echo "</td>
                                    <td>";
                // line 220
                echo (($__internal_compile_15 = $context["service"]) && is_array($__internal_compile_15) || $__internal_compile_15 instanceof ArrayAccess ? ($__internal_compile_15["service_max"] ?? null) : null);
                echo "</td>
                                                                        <td class=\"avarage_time_Services\">";
                // line 221
                echo (($__internal_compile_16 = $context["service"]) && is_array($__internal_compile_16) || $__internal_compile_16 instanceof ArrayAccess ? ($__internal_compile_16["time"] ?? null) : null);
                echo "</td>
                                                                                                        
                                                                                        <td>
                     <!-- Modal -->
                        <div class=\"modal fade\" id=\"servis";
                // line 225
                echo (($__internal_compile_17 = $context["service"]) && is_array($__internal_compile_17) || $__internal_compile_17 instanceof ArrayAccess ? ($__internal_compile_17["service_id"] ?? null) : null);
                echo "\" tabindex=\"-1\" role=\"dialog\" aria-labelledby=\"exampleModalLabel\" aria-hidden=\"true\">
                          <div class=\"modal-dialog modal-dialog-centered\" role=\"document\">
                            <div class=\"modal-content\">
                              <div class=\"modal-header\">
                                  <center><div class=\"serv_id_wrap\" bis_skin_checked=\"1\">
                       <span id=\"servId\">#";
                // line 230
                echo (($__internal_compile_18 = $context["service"]) && is_array($__internal_compile_18) || $__internal_compile_18 instanceof ArrayAccess ? ($__internal_compile_18["service_id"] ?? null) : null);
                echo "</span>
                    </div></center>
                    <div class=\"servName\" bis_skin_checked=\"1\"> <span id=\"exampleModalLabel\">
                                                ";
                // line 233
                echo (($__internal_compile_19 = $context["service"]) && is_array($__internal_compile_19) || $__internal_compile_19 instanceof ArrayAccess ? ($__internal_compile_19["service_name"] ?? null) : null);
                echo "
                                            </span></div>
                                
                                <button type=\"button\" class=\"serv_modal_close\" data-dismiss=\"modal\" aria-label=\"Close\"><i class=\"fas fa-times\"></i></button>
                                
                              </div>
                              <div class=\"modal-body text-left\">
                                <p class=\"detail-data details-split-959\"> ";
                // line 240
                echo (($__internal_compile_20 = $context["service"]) && is_array($__internal_compile_20) || $__internal_compile_20 instanceof ArrayAccess ? ($__internal_compile_20["service_description"] ?? null) : null);
                echo "</p>
                              </div>
                              <div class=\"modal-footer\">
                                <button type=\"button\" class=\"btn btn-actions\" data-dismiss=\"modal\">Close</button>
                              </div>
                            </div>
                          </div>
\t\t\t\t\t\t  
                        </div><a href=\"javascript:void(0)\" class=\"btn btn-primary btn_serv btn_detail \" data-toggle=\"modal\" data-target=\"#servis";
                // line 248
                echo (($__internal_compile_21 = $context["service"]) && is_array($__internal_compile_21) || $__internal_compile_21 instanceof ArrayAccess ? ($__internal_compile_21["service_id"] ?? null) : null);
                echo "\">
                         Details
                      </a>
                   
                        </td>         
                                         <td>
                                                <a href=\"";
                // line 254
                echo (($__internal_compile_22 = ($context["site"] ?? null)) && is_array($__internal_compile_22) || $__internal_compile_22 instanceof ArrayAccess ? ($__internal_compile_22["url"] ?? null) : null);
                echo "/?category_id=";
                echo (($__internal_compile_23 = $context["category"]) && is_array($__internal_compile_23) || $__internal_compile_23 instanceof ArrayAccess ? ($__internal_compile_23["category_id"] ?? null) : null);
                echo "&service_id=";
                echo (($__internal_compile_24 = $context["service"]) && is_array($__internal_compile_24) || $__internal_compile_24 instanceof ArrayAccess ? ($__internal_compile_24["service_id"] ?? null) : null);
                echo "\" class=\"btn btn-primary btn_serv\">
                                                    <i class=\"fas fa-shopping-cart\"></i> 
                                                </a>
                                            </td>                                                       
                                                                                                

                                                                    </tr>
                                           
                  
                  
                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['service'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 265
            echo "              ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['category'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 266
        echo "
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div></div>
</div>
</div></div></div>


";
        // line 277
        if (($context["contentText2"] ?? null)) {
            // line 278
            echo ($context["contentText2"] ?? null);
            echo "
";
        }
        // line 280
        echo "
<script>
function btnFltr(t){var e=t.getAttribute(\"data-services-filter\");\$(\"#search\").val(e),\$(\"#search\").attr(\"data-filter-value\",e),\$(\"#search\").trigger(\"keyup\")}\$(document).ready(function(){\$(\"#search\").on(\"keyup\",function(){var t=\$(this).val().toLowerCase();\$(\"#serv-table tbody tr\").each(function(){var e=\$(this).find(\"#servis_id\").text().toLowerCase(),a=\$(this).find(\".service-name\").text().toLowerCase(),i=\$(this).attr(\"data-filter-table-category-id\"),r=\$('.catetitle[data-filter-table-category-id=\"'+i+'\"]');(e+a).includes(t)?(\$(this).show(),r.show()):(\$(this).hide(),\$('#serv-table tbody tr[data-filter-table-category-id=\"'+i+'\"]:visible').length>0||r.hide())})})});
</script>
";
        // line 284
        $this->loadTemplate("footer.twig", "services.twig", 284)->display($context);
        // line 285
        echo "
<script src=\"https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js\"></script>
  <script type=\"text/javascript\" src=\"/public/global/ch3915babussofa4.js\">
      </script>
  <script type=\"text/javascript\" src=\"/public/global/cgtptn05b64bwcs4.js\">
      </script>
  <script type=\"text/javascript\" src=\"/public/global/xcz59lmywkfdgsp4.js\">
      </script>
  <script type=\"text/javascript\" src=\"/public/global/wnzsoolloslhfumj.js\">
      </script>
      <script type=\"text/javascript\" src=\"/public/global/qri1ac173hshc2hdjc.js\">
</script>
  <script type=\"text/javascript\" >
     window.modules.layouts = {\"auth\":0};   </script>
  <script type=\"text/javascript\" >
     window.modules.signin = [];   </script>
  <script type=\"text/javascript\" >
      </script>
    <script src=\"https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.27.6/js/jquery.tablesorter.js\"></script>

";
    }

    public function getTemplateName()
    {
        return "services.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  437 => 285,  435 => 284,  429 => 280,  424 => 278,  422 => 277,  409 => 266,  403 => 265,  382 => 254,  373 => 248,  362 => 240,  352 => 233,  346 => 230,  338 => 225,  331 => 221,  327 => 220,  323 => 219,  320 => 218,  315 => 216,  309 => 213,  305 => 212,  298 => 208,  292 => 205,  285 => 201,  280 => 200,  276 => 199,  265 => 191,  257 => 187,  253 => 186,  207 => 142,  196 => 134,  190 => 131,  183 => 126,  181 => 125,  176 => 122,  167 => 121,  162 => 120,  158 => 119,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "services.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/services.twig");
    }
}
