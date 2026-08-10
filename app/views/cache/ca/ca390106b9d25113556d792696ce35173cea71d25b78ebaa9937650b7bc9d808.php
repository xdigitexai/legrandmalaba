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

/* blog.twig */
class __TwigTemplate_dbc56db25b472567b7f8d60e46951ffd2c3d7b80ebff09a5e4736b93761a7c22 extends \Twig\Template
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
        $this->loadTemplate("header.twig", "blog.twig", 1)->display($context);
        // line 2
        echo "
<main id=\"notLogin\">
    
<section id=\"blog_post\">
    
    <div class=\"container\">
        <div class=\"row mb-5\">
            
            ";
        // line 10
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(($context["blogs"] ?? null));
        foreach ($context['_seq'] as $context["_key"] => $context["blog"]) {
            // line 11
            echo "                        <div class=\"col-lg-4 col-md-4 col-sm-6 col-12\">
                <div class=\"blog_wraper\">
                    <div class=\"blog_img\">
                        ";
            // line 14
            if ((($__internal_compile_0 = $context["blog"]) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["image_file"] ?? null) : null)) {
                echo " 
                  <a href=\"/blog/";
                // line 15
                echo (($__internal_compile_1 = $context["blog"]) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["blog_get"] ?? null) : null);
                echo "\">
                                                <img src=\"";
                // line 16
                echo (($__internal_compile_2 = $context["blog"]) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["image_file"] ?? null) : null);
                echo "\" class=\"img-fluid\" alt=\"";
                echo (($__internal_compile_3 = $context["blog"]) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["title"] ?? null) : null);
                echo "\"></a>";
            }
            echo " 
                                            </div>
                    <div class=\"blog_content\">
                        <h4>";
            // line 19
            echo (($__internal_compile_4 = $context["blog"]) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["title"] ?? null) : null);
            echo "</h4>
                        <div class=\"blog_content\">
                            <p><span style=\"color: inherit; font-family: inherit; font-size: 30px;\">";
            // line 21
            echo (($__internal_compile_5 = $context["blog"]) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["content"] ?? null) : null);
            echo "</p>
                        </div>
                    </div>
                    <div class=\"blog_btn_wrap\">
                        <a href=\"/blog/";
            // line 25
            echo (($__internal_compile_6 = $context["blog"]) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["blog_get"] ?? null) : null);
            echo "\" class=\"btn btn-primary\">Read More &gt;&gt;</a>
                    </div>
                </div>
            </div>
             ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['blog'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 29
        echo "           
                    </div>
    </div>

    <div class=\"row\">
        <div class=\"col-md-8 col-md-offset-2\">
                    </div>
    </div>

</section>


";
        // line 41
        $this->loadTemplate("footer.twig", "blog.twig", 41)->display($context);
    }

    public function getTemplateName()
    {
        return "blog.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  113 => 41,  99 => 29,  88 => 25,  81 => 21,  76 => 19,  66 => 16,  62 => 15,  58 => 14,  53 => 11,  49 => 10,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "blog.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/blog.twig");
    }
}
