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

/* signup.twig */
class __TwigTemplate_8637e7728f290bc4d01030fc44b897b212d7b7b98bebf07562d7775bcb82bc7c extends \Twig\Template
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
        if (((($__internal_compile_0 = ($context["settings"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["register_page"] ?? null) : null) == 2)) {
            echo " 
";
            // line 2
            $this->loadTemplate("header.twig", "signup.twig", 2)->display($context);
            // line 3
            echo "  <main id=\"notLogin\">
    <section id=\"signUp\">
  <div class=\"container\">
    <div class=\"row\">
      <div class=\"col-md-12\">
        <div class=\"sigup_title\">
          <h1>Signup Now</h1>
        </div>
      </div>
    </div>
  </div>
  <div class=\"container mt-3 mb-5\">
    <div class=\"row\">
      <div class=\"col-md-3\"></div>
      <div class=\"col-md-6\">
        <div class=\"card card_v2 mb-5\">
          <div class=\"card-body\">
    ";
            // line 20
            if (($context["errorText"] ?? null)) {
                // line 21
                echo "\t\t\t\t\t\t\t\t<div class=\"alert alert-dismissible alert-danger\">
\t\t\t\t\t\t\t\t  ";
                // line 22
                echo ($context["errorText"] ?? null);
                echo "
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t  ";
            }
            // line 25
            echo "\t\t\t\t\t\t\t  ";
            if (($context["successText"] ?? null)) {
                // line 26
                echo "\t\t\t\t\t\t\t\t<div class=\"alert alert-dismissible alert-success\">
\t\t\t\t\t\t\t\t  ";
                // line 27
                echo ($context["successText"] ?? null);
                echo "
\t\t\t\t\t\t\t\t</div>
\t\t\t\t\t\t\t  ";
            }
            // line 29
            echo "\t\t\t\t
            <form  action=\"\" method=\"post\">
                                          <div class=\"form-group mb-3\">
                <label for=\"username\" class=\"control-label mb-1\">
                  <span class=\"icon\">
                    <i class=\"far fa-user-circle\"></i>
                  </span> 
                  <span class=\"name\">
                    Username
                  </span>
                </label>
                <input type=\"text\" class=\"form-control\" id=\"username\" value=\"\" name=\"username\">
              </div>
              
              <!---start-------- smmpanelbdlab.com  ------------>
              ";
            // line 44
            if (((($__internal_compile_1 = ($context["settings"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["name_fileds"] ?? null) : null) == 1)) {
                echo " 
              <div class=\"form-group mb-3\">
                <label for=\"username\" class=\"control-label mb-1\">
                  <span class=\"icon\">
                    <i class=\"far fa-user\"></i>
                  </span> 
                  <span class=\"name\">
                    Name
                  </span>
                </label>
                <input type=\"text\" class=\"form-control\" id=\"firstname\" name=\"name\" value=\"";
                // line 54
                echo (($__internal_compile_2 = ($context["data"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["name"] ?? null) : null);
                echo "\">
              </div>";
            }
            // line 56
            echo "              <!----------- smmpanelbdlab.com  ------end------>
              
                            <div class=\"form-group mb-3\">
                <label for=\"email\" class=\"control-label mb-1\">
                  <span class=\"icon\">
                    <i class=\"fas fa-envelope\"></i>
                  </span> 
                  <span class=\"name\">
                    Email
                  </span>
                  </label>
                <input type=\"email\" class=\"form-control\" id=\"email\" value=\"\" name=\"email\">
              </div>
              
              <!-----start------ smmpanelbdlab.com  ------------>
              ";
            // line 71
            if (((($__internal_compile_3 = ($context["settings"] ?? null)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["skype_feilds"] ?? null) : null) == 1)) {
                echo " 
               <div class=\"form-group mb-3\">
                <label for=\"skype\" class=\"control-label mb-1\">
                  <span class=\"icon\">
                    <i class=\"far fa-phone\"></i>
                  </span> 
                  <span class=\"name\">
                    Whatsapp
                  </span>
                  </label>
                <input type=\"text\" class=\"form-control\" id=\"skype\" name=\"telephone\" value=\"";
                // line 81
                echo (($__internal_compile_4 = ($context["data"] ?? null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["telephone"] ?? null) : null);
                echo "\">
              </div>";
            }
            // line 83
            echo "              <!----------- smmpanelbdlab.com  -------end----->
              
                            <div class=\"form-group mb-3\">
                <label for=\"password\" class=\"control-label mb-1\">
                  <span class=\"icon\">
                    <i class=\"fas fa-lock\"></i>
                  </span> 
                  <span class=\"name\">
                    Password
                  </span>
                 </label>
                <input type=\"password\" class=\"form-control\" id=\"password\" name=\"password\">
              </div>
              <div class=\"form-group mb-3\">
                <label for=\"confirm\" class=\"control-label mb-1\">
                  <span class=\"icon\">
                    <i class=\"fas fa-lock\"></i>
                  </span> 
                  <span class=\"name\">
                    Confirm password
                  </span>
                  </label>
                <input type=\"password\" class=\"form-control\" id=\"confirm\" name=\"password_again\">
              </div>
                             ";
            // line 107
            if (($context["captcha"] ?? null)) {
                // line 108
                echo "            <div class=\"form-group\">
              <div class=\"g-recaptcha\" data-sitekey=\"";
                // line 109
                echo ($context["captchaKey"] ?? null);
                echo "\"></div>
            </div>
          ";
            }
            // line 112
            echo "                                      <div class=\"checkbox\">
            <label>
              <input type=\"checkbox\" name=\"terms\" value=\"1\"> ";
            // line 114
            echo (($__internal_compile_5 = ($context["lang"] ?? null)) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["signup.accept_terms_text"] ?? null) : null);
            echo "
            </label>
          </div>
                                
              <button type=\"submit\" class=\"btn btn-primary btn-gradient w-100\">Sign up</button>
            </form>
            <p class=\"text-center\">
              Already have an account? <a href=\"/\">Sign in</a>
            </p>
  
  
          </div>
        </div>
      </div>
      <div class=\"col-md-3\"></div>
    </div>
  </div>
</section>
  </main>

 </div>
<div class=\"container\">
<div class=\"row sign-up-center-alignment\" style=\"justify-content: center;margin-top:-5px\">
<div class=\"col-lg-5\">
";
            // line 138
            if (($context["contentText2"] ?? null)) {
                // line 139
                echo "<div class=\"card mt- mb-5\">
";
                // line 140
                echo ($context["contentText2"] ?? null);
                echo "
</div>
";
            }
            // line 143
            echo "</div></div> </div>




";
            // line 148
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable((($__internal_compile_6 = ($context["site"] ?? null)) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["languages"] ?? null) : null));
            foreach ($context['_seq'] as $context["_key"] => $context["lang"]) {
                // line 149
                echo "  ";
                if ((($__internal_compile_7 = $context["lang"]) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7["active"] ?? null) : null)) {
                    // line 150
                    echo "    <script src=\"https://www.google.com/recaptcha/api.js?hl=";
                    echo (($__internal_compile_8 = $context["lang"]) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8["code"] ?? null) : null);
                    echo "\"></script>
  ";
                }
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['lang'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 153
            echo "
";
            // line 154
            $this->loadTemplate("footer.twig", "signup.twig", 154)->display($context);
        }
    }

    public function getTemplateName()
    {
        return "signup.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  261 => 154,  258 => 153,  248 => 150,  245 => 149,  241 => 148,  234 => 143,  228 => 140,  225 => 139,  223 => 138,  196 => 114,  192 => 112,  186 => 109,  183 => 108,  181 => 107,  155 => 83,  150 => 81,  137 => 71,  120 => 56,  115 => 54,  102 => 44,  85 => 29,  79 => 27,  76 => 26,  73 => 25,  67 => 22,  64 => 21,  62 => 20,  43 => 3,  41 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "signup.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/signup.twig");
    }
}
