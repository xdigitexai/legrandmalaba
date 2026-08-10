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

/* terms.twig */
class __TwigTemplate_dde24d937727387be52855a97924b92bf152282d5541326c2f74f688935effff extends \Twig\Template
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
        $this->loadTemplate("header.twig", "terms.twig", 1)->display($context);
        // line 2
        echo "<br>
<br>

 <div class=\"wrapper-content\">
    <div class=\"wrapper-content__header\">
          </div>
    <div class=\"wrapper-content__body\">
      <!-- Main variables *content* -->
      <div id=\"block_93\">
    <div class=\"new_order-block \">
        <div class=\"bg\"></div>
        <div class=\"divider-top\"></div>
        <div class=\"divider-bottom\"></div>
        <div class=\"container\">
\t\t";
        // line 16
        if (($context["contentText"] ?? null)) {
            // line 17
            echo "\t\t<div class=\"card mt-4 mb-2\">
";
            // line 18
            echo ($context["contentText"] ?? null);
            echo "
</div>
";
        }
        // line 21
        echo "           ";
        if (($context["contentText2"] ?? null)) {
            // line 22
            echo "           <div class=\"card mt-4 mb-4\">
";
            // line 23
            echo ($context["contentText2"] ?? null);
            echo "
</div>
";
        }
        // line 26
        echo " </div> </div>
</div> </div>
  </div>
</div><br>
</div>
</div>    </div></div></div></div></div>

";
        // line 33
        $this->loadTemplate("footer.twig", "terms.twig", 33)->display($context);
    }

    public function getTemplateName()
    {
        return "terms.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  87 => 33,  78 => 26,  72 => 23,  69 => 22,  66 => 21,  60 => 18,  57 => 17,  55 => 16,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "terms.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/terms.twig");
    }
}
