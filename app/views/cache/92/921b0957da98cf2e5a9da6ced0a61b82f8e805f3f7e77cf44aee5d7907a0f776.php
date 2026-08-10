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

/* resetpassword.twig */
class __TwigTemplate_ee4a4376fc0c80b5c764059183ed5a66fcae752577475180de7383f01dec7a3c extends \Twig\Template
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
        $this->loadTemplate("header.twig", "resetpassword.twig", 1)->display($context);
        // line 2
        echo "          <main id=\"notLogin\">
    <div id=\"resetPassword\" class=\"mt-5\">
  <div class=\"container mt-5\">
    <div class=\"row\">
        <div class=\"col-md-6 col-sm-12\">
          <div class=\"highlight-lside\">
            <div class=\"hlight-box\">
               <div class=\"row align-items-center\">
                  <div class=\"col-md-3\">
                     <i class=\"fal fa-phone-laptop icon\"></i>
                  </div>
                  <div class=\"col-md-9\">
                     <div>
                        <h4 class=\"hlight-first\">Device Compatibility</h4>
                        <p class=\"hlight-second\">Our site is fully compatible with all desktop and mobile devices you are using.</p>
                     </div>
                  </div>
               </div>
            </div>
            <div class=\"hlight-box\">
               <div class=\"row align-items-center\">
                  <div class=\"col-md-3\">
                     <i class=\"fal fa-credit-card icon\"></i>
                  </div>
                  <div class=\"col-md-9\">
                     <div>
                        <h4 class=\"hlight-first\">Secure Payment</h4>
                        <p class=\"hlight-second\">Thanks to the secure payment activation on our site, you can pay by credit card or money order.</p>
                     </div>
                  </div>
               </div>
            </div>
            <div class=\"hlight-box\">
               <div class=\"row align-items-center\">
                  <div class=\"col-md-3\">
                     <i class=\"fal fa-smile icon\"></i>
                  </div>
                  <div class=\"col-md-9\">
                     <div>
                        <h4 class=\"hlight-first\">Quality Service</h4>
                        <p class=\"hlight-second\">We are always working to increase the satisfaction rate by providing cheap and quality services.</p>
                     </div>
                  </div>
               </div>
            </div>
         </div>
        </div>
        <div class=\"col-md-6 col-sm-12\">
            <div class=\"reset_Card\">
                <div class=\"card card_v2\">
                    <div class=\"card-body p-4\">
                      <div id=\"def_side_title\" class=\"mb-5\">
                          <div class=\"icon\">
                            <i class=\"fas fa-lock\"></i>
                          </div>
                          <div class=\"name\">
                            Reset your password
                          </div>
                      </div>
                      
                        
                        <form  method=\"post\" action=\"\">
                                                        

                            <div class=\"form-group mb-3\">
                              <label for=\"email\" class=\"control-label mb-2\">
                                <span class=\"icon\">
                                  <i class=\"fas fa-envelope\"></i>
                                </span> 
                                <span class=\"name\">
                                  E-mail
                                </span>
                              </label>
                              <input type=\"text\" class=\"form-control\" name=\"user\" value=\"";
        // line 75
        echo (($__internal_compile_0 = ($context["data"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["user"] ?? null) : null);
        echo "\" placeholder=\"johnsmith28@gmail.com\">
                            </div>
                                                           ";
        // line 77
        if (($context["captcha"] ?? null)) {
            // line 78
            echo "            <div class=\"form-group\">
              <div class=\"g-recaptcha\" data-sitekey=\"";
            // line 79
            echo ($context["captchaKey"] ?? null);
            echo "\"></div>
            </div>
          ";
        }
        // line 82
        echo "
            <button type=\"submit\" class=\"btn btn-block btn-primary w-100\">";
        // line 83
        echo (($__internal_compile_1 = ($context["lang"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["resetpassword.button"] ?? null) : null);
        echo "</button>
                          </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>


 
  ";
        // line 95
        $this->loadTemplate("footer.twig", "resetpassword.twig", 95)->display($context);
    }

    public function getTemplateName()
    {
        return "resetpassword.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  148 => 95,  133 => 83,  130 => 82,  124 => 79,  121 => 78,  119 => 77,  114 => 75,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "resetpassword.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/resetpassword.twig");
    }
}
