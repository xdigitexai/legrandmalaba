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

/* login.twig */
class __TwigTemplate_73365615288f756fe4cd2781150887941f6283cf8080eabc9a8161f057e3b14e extends \Twig\Template
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
        $this->loadTemplate("header.twig", "login.twig", 1)->display($context);
        // line 2
        echo "
<main id=\"notConnexion\">
<section id=\"hero-login\" style=\"
  background: linear-gradient(135deg, #e0f2ff 0%, #f7eaff 50%, #ffffff 100%);
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Poppins', sans-serif;
  padding-top: 80px; /* extra space to prevent header overlap */
  padding-bottom: 50px;
\">
  <div class=\"container\" style=\"max-width:1200px; margin:auto;\">
    <div class=\"row\" style=\"display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap;\">

      <!-- LEFT CONTENT -->
      <div class=\"col-left\" style=\"flex:1 1 520px; max-width:520px; padding:15px;\">
        <div class=\"v2_banner_content\">

          <p style=\"font-size:15px; margin-bottom:5px;\">
            <span style=\"color:#ffc107; font-weight:600;\">
              <i class=\"fas fa-crown\" style=\"margin-right:5px; color:#ffc107;\"></i>Legrand
            </span>
            <span style=\"color:#5cb85c; font-weight:500;\"> Le N°1 en RDC</span>
          </p>

          <h1 style=\"font-size:45px; font-weight:800; line-height:1.2; color:#111; margin-bottom:10px;\">
            Le <span style=\"color:#ff8c00;\">Leader</span> du Boost des <span style=\"color:#00c853;\">Réseaux Sociaux</span> en RDC
          </h1>

          <p style=\"font-size:16px; color:#444; margin-bottom:30px;\">
            Boostez votre visibilité sur Instagram, TikTok, Facebook, YouTube et Telegram. Livraison rapide, prix abordables et services fiables partout en République Démocratique du Congo.
          </p>

          <!-- LOGIN FORM -->
          <form method=\"post\" action=\"/\" style=\"width:100%;\">
            <!-- Username -->
            <div style=\"position:relative; margin-bottom:20px;\">
              <span style=\"position:absolute; left:0; top:0; bottom:0; width:55px; background:linear-gradient(135deg,#ff8c00,#00c853); border-radius:10px 0 0 10px; display:flex; align-items:center; justify-content:center; color:white;\">
                <i class=\"fas fa-user\"></i>
              </span>
              <input type=\"text\" name=\"username\" placeholder=\"Nom d'utilisateur\" style=\"width:100%; padding:15px 15px 15px 70px; border:none; border-radius:10px; background:#fff; font-size:15px; outline:none; box-shadow:0 2px 5px rgba(0,0,0,0.05);\">
            </div>

            <!-- Password -->
<div style=\"position:relative; margin-bottom:10px;\">
  <span style=\"position:absolute; left:0; top:0; bottom:0; width:55px; background:linear-gradient(135deg,#ff8c00,#00c853); border-radius:10px 0 0 10px; display:flex; align-items:center; justify-content:center; color:white;\">
    <i class=\"fas fa-lock\"></i>
  </span>
  <input type=\"password\" id=\"password\" name=\"password\" placeholder=\"Mot de passe\" style=\"width:100%; padding:15px 45px 15px 70px; border:none; border-radius:10px; background:#fff; font-size:15px; outline:none; box-shadow:0 2px 5px rgba(0,0,0,0.05);\">
  <span id=\"togglePassword\" style=\"position:absolute; right:15px; top:50%; transform:translateY(-50%); color:#888; cursor:pointer;\">
    <i class=\"fas fa-eye-slash\"></i>
  </span>
</div>
<!-- CAPTCHA -->
  ";
        // line 57
        if (($context["captcha"] ?? null)) {
            // line 58
            echo "  <div class=\"form-group\" style=\"margin-bottom:20px;\">
    <div class=\"g-recaptcha\" data-sitekey=\"";
            // line 59
            echo ($context["captchaKey"] ?? null);
            echo "\"></div>
  </div>
  ";
        }
        // line 62
        echo "          
            <!-- Remember + Forgot -->
            <div style=\"display:flex; justify-content:space-between; align-items:center; margin:10px 0 20px 0;\">
              <label style=\"display:flex; align-items:center; font-size:14px; color:#333;\">
                <input type=\"checkbox\" style=\"margin-right:6px;\"> Se souvenir
              </label>
              <a href=\"/resetpassword\" style=\"font-size:14px; color:#007bff; text-decoration:none;\">Mot de passe oublié ?</a>
            </div>

<!-- Buttons -->
<div style=\"display:flex; flex-direction:column; gap:10px; align-items:stretch;\">
  <!-- Normal Sign in -->
  <button type=\"submit\" style=\"width:100%; padding:12px; background:linear-gradient(90deg,#ff8c00,#00c853); border:none; color:white; font-weight:600; border-radius:10px; font-size:16px; cursor:pointer; transition:0.3s;\">
    Se connecter
  </button>

  <!-- Google Sign in -->
  <div style=\"width:100%; padding:12px; border-radius:10px; font-size:16px; font-weight:600; text-align:center; cursor:pointer;\">
    <center>";
        // line 80
        echo ($context["google_login_content"] ?? null);
        echo "</center>
  </div>
</div>

            <!-- Sign up -->
            <p style=\"text-align:center; font-size:14px; color:#333; margin-top:20px;\">Pas encore de compte ?
              <a href=\"/signup\" style=\"color:#007bff; text-decoration:none;\">S'inscrire</a>
            </p>
          </form>

          <!-- Social Icons -->
          <div style=\"display:flex; gap:12px; margin-top:25px; flex-wrap:wrap; align-items:center;\">
            <a href=\"#\" style=\"width:40px;height:40px;border-radius:50%;background:#1877F2;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;\" title=\"Facebook\"><i class=\"fab fa-facebook-f\" style=\"font-size:18px;\"></i></a>
            <a href=\"#\" style=\"width:40px;height:40px;border-radius:50%;background:#FF0000;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;\" title=\"YouTube\"><i class=\"fab fa-youtube\" style=\"font-size:18px;\"></i></a>
            <a href=\"#\" style=\"width:40px;height:40px;border-radius:50%;background:#000;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;\" title=\"X / Twitter\"><i class=\"fab fa-x-twitter\" style=\"font-size:18px;\"></i></a>
            <a href=\"#\" style=\"width:40px;height:40px;border-radius:50%;background:linear-gradient(135deg,#E1306C,#833AB4,#F77737);display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;\" title=\"Instagram\"><i class=\"fab fa-instagram\" style=\"font-size:18px;\"></i></a>
            <a href=\"#\" style=\"width:40px;height:40px;border-radius:50%;background:#000;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;\" title=\"TikTok\"><i class=\"fab fa-tiktok\" style=\"font-size:18px;\"></i></a>
            <a href=\"#\" style=\"width:40px;height:40px;border-radius:50%;background:#0088CC;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;\" title=\"Telegram\"><i class=\"fab fa-telegram-plane\" style=\"font-size:18px;\"></i></a>
          </div>
        </div>
      </div>

      <!-- RIGHT IMAGE -->
      <div class=\"col-right\" style=\"
        flex: 1 1 500px; 
        text-align: right; 
        padding: 15px;
        display: flex; 
        justify-content: flex-end; 
        align-items: center;
      \">
        <img 
          src=\"https://i.postimg.cc/K8BV64zC/e4a7a2f5cc79df774388dbd0bf2c12a8-1.png\" 
          alt=\"Legrand République Démocratique du Congo\" 
          style=\"width: 100%; max-width: 480px; height: auto;\"
        >
      </div>

    </div>
  </div>
</section>

<!-- Font Awesome -->
<script src=\"https://kit.fontawesome.com/a076d05399.js\" crossorigin=\"anonymous\"></script>

<script>
document.getElementById(\"togglePassword\").addEventListener(\"click\", function() {
  const passwordInput = document.getElementById(\"password\");
  const icon = this.querySelector(\"i\");
  
  if (passwordInput.type === \"password\") {
    passwordInput.type = \"text\";
    icon.classList.remove(\"fa-eye-slash\");
    icon.classList.add(\"fa-eye\");
  } else {
    passwordInput.type = \"password\";
    icon.classList.remove(\"fa-eye\");
    icon.classList.add(\"fa-eye-slash\");
  }
});
</script>

<!-- ✅ Mobile Fix -->
<style>
@media (max-width: 768px) {
  #hero-login {
    flex-direction: column;
    padding-top: 30px !important; /* adds extra space under navbar */
    text-align: center;
  }
  #hero-login h1 {
    font-size: 32px !important;
  }
  #hero-login .col-right {
    margin-top: 30px;
  }
  header, .navbar, .header {
  padding: 8px 25px !important; /* Reduced height */
  height: auto !important;
  transition: all 0.3s ease;
}

/* Adjust logo size */
header img, .navbar img, .header img {
  max-height: 45px !important;
}

/* Align items properly */
header, .navbar, .header {
  display: flex;
  align-items: center;
  justify-content: space-between;
}
}
</style>
</main>

<!-- Counter Section -->
<section id=\"CounterSections\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-3 col-md-6 col-12\">
                <div class=\"counter_item__wrap\">
                    <div class=\"count__icon\">
                        <div style=\"width:60px;height:60px;border-radius:16px;background:linear-gradient(135deg,#E1306C,#833AB4);display:flex;align-items:center;justify-content:center;\"><i class=\"fab fa-instagram\" style=\"font-size:28px;color:#fff;\"></i></div>
                    </div>
                    <div class=\"count__content\">
                        <h4>259597</h4>
                        <p>
                            Total Orders
                        </p>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-3 col-md-6 col-12\">
                <div class=\"counter_item__wrap\">
                    <div class=\"count__icon\">
                        <div style=\"width:60px;height:60px;border-radius:16px;background:#000;display:flex;align-items:center;justify-content:center;\"><i class=\"fab fa-tiktok\" style=\"font-size:28px;color:#fff;\"></i></div>
                    </div>
                    <div class=\"count__content\">
                        <h4>\$0.001/1K</h4>
                        <p>
                            Starting Price
                        </p>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-3 col-md-6 col-12\">
                <div class=\"counter_item__wrap\">
                    <div class=\"count__icon\">
                        <div style=\"width:60px;height:60px;border-radius:16px;background:#1877F2;display:flex;align-items:center;justify-content:center;\"><i class=\"fab fa-facebook-f\" style=\"font-size:28px;color:#fff;\"></i></div>
                    </div>
                    <div class=\"count__content\">
                        <h4>24/7</h4>
                        <p>
                            Le plus rapide Support
                        </p>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-3 col-md-6 col-12\">
                <div class=\"counter_item__wrap\">
                    <div class=\"count__icon\">
                        <div style=\"width:60px;height:60px;border-radius:16px;background:#FF0000;display:flex;align-items:center;justify-content:center;\"><i class=\"fab fa-youtube\" style=\"font-size:28px;color:#fff;\"></i></div>
                    </div>
                    <div class=\"count__content\">
                        <h4>2091+</h4>
                        <p>
                            Happy Clients
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Top SMM Services -->
<section id=\"top__smm\">
    <div class=\"container\">
        <div class=\"row d-flex align-items-center\">
            <div class=\"col-lg-6 col-md-6 col-12\">
                <h2 class=\"section__title\">Services SMM <br> Que Nous Proposons</h2>
                <p class=\"mb-3\">
                    At ";
        // line 244
        echo (($__internal_compile_0 = ($context["site"] ?? null)) && is_array($__internal_compile_0) || $__internal_compile_0 instanceof ArrayAccess ? ($__internal_compile_0["site_name"] ?? null) : null);
        echo ", we pride ourselves on delivering top-tier Social Media Marketing (SMM) services designed
                    to elevate your online presence and drive unparalleled engagement. With our comprehensive suite of
                    solutions, we empower businesses of all sizes to harness the full potential of social media
                    platforms.
                </p>
                <a href=\"/services\" class=\"btn btn-primary btn-gradient mb-md-0 mb-4\">See All Our Services</a>
            </div>
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"top__smm__img text-end\" bis_skin_checked=\"1\" style=\"display:grid;grid-template-columns:repeat(3,1fr);gap:16px;padding:20px;\">
                    <div style=\"background:linear-gradient(135deg,#E1306C,#833AB4);border-radius:20px;padding:24px;text-align:center;color:#fff;\">
                        <i class=\"fab fa-instagram\" style=\"font-size:36px;display:block;margin-bottom:8px;\"></i>
                        <span style=\"font-size:13px;font-weight:600;\">Instagram</span>
                    </div>
                    <div style=\"background:#000;border-radius:20px;padding:24px;text-align:center;color:#fff;\">
                        <i class=\"fab fa-tiktok\" style=\"font-size:36px;display:block;margin-bottom:8px;\"></i>
                        <span style=\"font-size:13px;font-weight:600;\">TikTok</span>
                    </div>
                    <div style=\"background:#1877F2;border-radius:20px;padding:24px;text-align:center;color:#fff;\">
                        <i class=\"fab fa-facebook-f\" style=\"font-size:36px;display:block;margin-bottom:8px;\"></i>
                        <span style=\"font-size:13px;font-weight:600;\">Facebook</span>
                    </div>
                    <div style=\"background:#FF0000;border-radius:20px;padding:24px;text-align:center;color:#fff;\">
                        <i class=\"fab fa-youtube\" style=\"font-size:36px;display:block;margin-bottom:8px;\"></i>
                        <span style=\"font-size:13px;font-weight:600;\">YouTube</span>
                    </div>
                    <div style=\"background:#0088CC;border-radius:20px;padding:24px;text-align:center;color:#fff;\">
                        <i class=\"fab fa-telegram-plane\" style=\"font-size:36px;display:block;margin-bottom:8px;\"></i>
                        <span style=\"font-size:13px;font-weight:600;\">Telegram</span>
                    </div>
                    <div style=\"background:#1DB954;border-radius:20px;padding:24px;text-align:center;color:#fff;\">
                        <i class=\"fab fa-spotify\" style=\"font-size:36px;display:block;margin-bottom:8px;\"></i>
                        <span style=\"font-size:13px;font-weight:600;\">Spotify</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Le moins cher Le plus rapide -->
<section id=\"cheapest\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 col-md-12 col-12\">
                <h2 class=\"section__title text-center mb-5\">
                    Le moins cher & Le plus rapide Services SMM pour tous les réseaux sociaux.
                </h2>
            </div>
        </div>
        <div class=\"row d-flex align-items-center cheap_content_wrapers\">
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"cheapest_img text-start\">
                    <div style=\"background:linear-gradient(135deg,#f0f9ff,#e8f5e9);border-radius:24px;padding:40px;text-align:center;\">
                        <div style=\"display:flex;justify-content:center;gap:20px;flex-wrap:wrap;margin-bottom:20px;\">
                            <div style=\"background:#fff;border-radius:16px;padding:20px;box-shadow:0 4px 15px rgba(0,0,0,0.08);min-width:100px;\">
                                <i class=\"fas fa-bolt\" style=\"font-size:32px;color:#ff8c00;display:block;margin-bottom:8px;\"></i>
                                <span style=\"font-size:13px;font-weight:600;color:#333;\">Livraison<br>Rapide</span>
                            </div>
                            <div style=\"background:#fff;border-radius:16px;padding:20px;box-shadow:0 4px 15px rgba(0,0,0,0.08);min-width:100px;\">
                                <i class=\"fas fa-tag\" style=\"font-size:32px;color:#00c853;display:block;margin-bottom:8px;\"></i>
                                <span style=\"font-size:13px;font-weight:600;color:#333;\">Prix<br>Abordables</span>
                            </div>
                            <div style=\"background:#fff;border-radius:16px;padding:20px;box-shadow:0 4px 15px rgba(0,0,0,0.08);min-width:100px;\">
                                <i class=\"fas fa-shield-alt\" style=\"font-size:32px;color:#007bff;display:block;margin-bottom:8px;\"></i>
                                <span style=\"font-size:13px;font-weight:600;color:#333;\">Service<br>Fiable</span>
                            </div>
                        </div>
                        <p style=\"font-size:22px;font-weight:800;color:#333;margin:0;\">à partir de <span style=\"color:#ff8c00;\">\$0.001</span></p>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"cheapest__content__wrap\">
                    <p>
                        At ";
        // line 318
        echo (($__internal_compile_1 = ($context["site"] ?? null)) && is_array($__internal_compile_1) || $__internal_compile_1 instanceof ArrayAccess ? ($__internal_compile_1["site_name"] ?? null) : null);
        echo ", we specialize in providing the most cost-effective and expeditious Social Media
                        Marketing (SMM) services for all your social accounts. Our tailored solutions are designed to
                        skyrocket your online presence without burning a hole in your pocket.
                    </p>
                    <p>
                        Experience unparalleled speed and efficiency as we boost your visibility across various social
                        media platforms.Trust ";
        // line 324
        echo (($__internal_compile_2 = ($context["site"] ?? null)) && is_array($__internal_compile_2) || $__internal_compile_2 instanceof ArrayAccess ? ($__internal_compile_2["site_name"] ?? null) : null);
        echo " to amplify your online reach promptly and affordably. Take the
                        fast track to social media success with our budget-friendly and expedited services.
                    </p>
                    <a href=\"/services\" class=\"btn btn-primary btn-gradient mb-4\">Learn More</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!--  Best smm panel  -->
<section id=\"bestsmmpanel\">
    <div class=\"best_bg_ring\">
        
    </div>
    <div class=\"bestsmm__bg__animations\">
        
    </div>
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-6 col-md-6 col-12\">
<h2 class=\"section__title text-white\">Meilleure Plateforme SMM <br>
                    Pour les Revendeurs</h2>
                <p class=\"text-white\">
                    Discover unparalleled convenience and excellence in social media marketing with ";
        // line 348
        echo (($__internal_compile_3 = ($context["site"] ?? null)) && is_array($__internal_compile_3) || $__internal_compile_3 instanceof ArrayAccess ? ($__internal_compile_3["site_name"] ?? null) : null);
        echo ", the
                    industry's foremost SMM panel for resellers. Our platform offers resellers a seamless experience,
                    providing access to premium services and tools tailored to elevate your SMM ventures.
                </p>
                <div class=\"best__btn__wrap\">
                    <a href=\"\" class=\"btn btn-primary btn-gradient btn__best bg__blck\">Create Account</a>
                    <a href=\"\" class=\"btn btn-primary btn-gradient btn__best bg__white\">Get Discounts</a>
                </div>
            </div>
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"best_smm_panel\">
                    <div class=\"best_smm_animations\">
                        <div class=\"tw_bnn\" style=\"width:70px;height:70px;border-radius:18px;background:#000;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(0,0,0,0.25);\"><i class=\"fab fa-x-twitter\" style=\"font-size:32px;color:#fff;\"></i></div>
                        <div class=\"ig_bnn\" style=\"width:70px;height:70px;border-radius:18px;background:linear-gradient(135deg,#E1306C,#833AB4,#F77737);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(225,48,108,0.4);\"><i class=\"fab fa-instagram\" style=\"font-size:32px;color:#fff;\"></i></div>
                        <div class=\"fb_bnn\" style=\"width:70px;height:70px;border-radius:18px;background:#1877F2;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(24,119,242,0.4);\"><i class=\"fab fa-facebook-f\" style=\"font-size:32px;color:#fff;\"></i></div>
                        <div class=\"yt_bnn\" style=\"width:70px;height:70px;border-radius:18px;background:#FF0000;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(255,0,0,0.4);\"><i class=\"fab fa-youtube\" style=\"font-size:32px;color:#fff;\"></i></div>
                        <div class=\"tik_bnn\" style=\"width:70px;height:70px;border-radius:18px;background:#000;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(0,0,0,0.25);\"><i class=\"fab fa-tiktok\" style=\"font-size:32px;color:#fff;\"></i></div>
                        <div class=\"sp_bnn\" style=\"width:70px;height:70px;border-radius:18px;background:#1DB954;display:flex;align-items:center;justify-content:center;box-shadow:0 8px 25px rgba(29,185,84,0.4);\"><i class=\"fab fa-spotify\" style=\"font-size:32px;color:#fff;\"></i></div>
                    </div>
                    <div class=\"smmpanel_best_main__image\" style=\"background:linear-gradient(135deg,rgba(255,255,255,0.1),rgba(255,255,255,0.05));border-radius:24px;padding:40px;text-align:center;border:2px solid rgba(255,255,255,0.2);\">
                        <i class=\"fas fa-chart-line\" style=\"font-size:64px;color:#fff;margin-bottom:16px;display:block;\"></i>
                        <p style=\"color:#fff;font-size:18px;font-weight:700;margin:0;\">Legrand</p>
                        <p style=\"color:rgba(255,255,255,0.8);font-size:13px;margin:4px 0 0 0;\">Revendeur N°1 en RDC</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section id=\"howItWorksSection\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 col-md-12 col-12\">
                <h2 class=\"section__title text-center mb-5\">Comment Ça Marche</h2>
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-lg-4 col-md-4 col-12\">
                <div class=\"how-its-items\">
                    <div class=\"how_icon_up\">
                        <div style=\"width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#ffc107);display:flex;align-items:center;justify-content:center;margin:0 auto 8px;box-shadow:0 4px 15px rgba(255,140,0,0.3);\"><span style=\"color:#fff;font-size:24px;font-weight:800;\">1</span></div>
                    </div>
                    <div class=\"how-icons\">
                        <iconify-icon icon=\"iconamoon:profile-fill\"></iconify-icon>
                    </div>
                    <div class=\"how-its-content\">
                        <h4>Create An Account <br>
                            & Add Balance</h4>
                        <p>
                            Begin your journey with us by signing up and creating your account. Once registered, access
                            your account by logging in. To get started, deposit funds.
                        </p>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-4 col-md-4 col-12\">
                <div class=\"how-its-items\">
                    <div class=\"how_icon_up\">
                        <div style=\"width:64px;height:64px;border-radius:50%;background:linear-gradient(135deg,#00c853,#00bcd4);display:flex;align-items:center;justify-content:center;margin:0 auto 8px;box-shadow:0 4px 15px rgba(0,200,83,0.3);\"><span style=\"color:#fff;font-size:24px;font-weight:800;\">2</span></div>
                    </div>
                    <div class=\"how-icons\">
                        <iconify-icon icon=\"ic:round-home-repair-service\"></iconify-icon>
                    </div>
                    <div class=\"how-its-content\">
                        <h4>
                            Select Your <br>
                            Targeted Service
                        </h4>
                        <p>
                            Select the services you need from either the Services page or the New Order section. Easily
                            find and choose the desired services to fulfill your requirements.
                        </p>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-4 col-md-4 col-12\">
                <div class=\"how-its-items\">
                    <div class=\"how-icons\">
                        <iconify-icon icon=\"fa-solid:link\"></iconify-icon>
                    </div>
                    <div class=\"how-its-content\">
                        <h4>
                            Provide Link, Quantity <br>
                            & Watch Results!
                        </h4>
                        <p>
                            Providing the correct links and quantities. Instantly view
                            the total cost of your order before finalizing. After Place an order just wait few hours
                            then you will see tha magic of ";
        // line 437
        echo (($__internal_compile_4 = ($context["site"] ?? null)) && is_array($__internal_compile_4) || $__internal_compile_4 instanceof ArrayAccess ? ($__internal_compile_4["site_name"] ?? null) : null);
        echo ".
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- faq -->
<section id=\"faqSection\">
    <div class=\"container\">
        <div class=\"row d-flex align-items-center\">
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"faq__conetent__wrap\">
                    <h2 class=\"section__title\">
                        Frequently Asked Questions
                    </h2>
                    <p>
                        The SMM panel is basically a social media marketing panel where you can buy targeted actions
                        like (followers, likes, subscribers, views, tweets, shares, etc.) but we also understand that
                        our clients may have many questions, and we have prepared some of the most important and
                        frequently asked questions in order to clear up any confusion regarding the Purpose and Process
                        of the ";
        // line 460
        echo (($__internal_compile_5 = ($context["site"] ?? null)) && is_array($__internal_compile_5) || $__internal_compile_5 instanceof ArrayAccess ? ($__internal_compile_5["site_name"] ?? null) : null);
        echo " Panel. After viewing the FAQ, it will be easy for you to place an order
                        with us.
                    </p>
                    <a href=\"/faq\" class=\"btn btn-primary btn-gradient\">View All FAQ</a>
                </div>
            </div>
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"accordion__wraper\">
                    <div class=\"accordion accordion-flush\" id=\"accordionFlushExample\">
                        <div class=\"accordion-item\">
                            <h2 class=\"accordion-header\">
                                <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\"
                                    data-bs-target=\"#flush-collapseOne\" aria-expanded=\"false\"
                                    aria-controls=\"flush-collapseOne\">
                                    What is SMM PANEL?
                                </button>
                            </h2>
                            <div id=\"flush-collapseOne\" class=\"accordion-collapse collapse\"
                                data-bs-parent=\"#accordionFlushExample\">
                                <div class=\"accordion-body\">
                                    <a href=\"/\" tabindex=\"0\"> Smm panel</a> is a panel where you can
                                    buy social media ( Facebook, Twitter, Instagram, YouTube, Spotify, Tiktok, and other
                                    social media ) likes, followers, views, Comments, Subscribers, and as well as
                                    Website
                                    traffic. Customers choose the <a href=\"/\" tabindex=\"0\">cheapest
                                        smm
                                        panel</a> because of its cheap price, faster delivery, and all social media
                                    services
                                    available on 1 website.
                                </div>
                            </div>
                        </div>
                        <div class=\"accordion-item\">
                            <h2 class=\"accordion-header\">
                                <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\"
                                    data-bs-target=\"#flush-collapseTwo\" aria-expanded=\"false\"
                                    aria-controls=\"flush-collapseTwo\">
                                    Is SMM Panel Safe?
                                </button>
                            </h2>
                            <div id=\"flush-collapseTwo\" class=\"accordion-collapse collapse\"
                                data-bs-parent=\"#accordionFlushExample\">
                                <div class=\"accordion-body\">
                                    The SMM panels on our platform are extremely safe, protected against DDoS assaults,
                                    and
                                    updated frequently. Additionally, each and every one of them possesses a
                                    certificate,
                                    which is crucial for protecting the privacy of your clients' and your own personal
                                    information.
                                </div>
                            </div>
                        </div>
                        <div class=\"accordion-item\">
                            <h2 class=\"accordion-header\">
                                <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\"
                                    data-bs-target=\"#flush-collapseThree\" aria-expanded=\"false\"
                                    aria-controls=\"flush-collapseThree\">
                                    How does ";
        // line 517
        echo (($__internal_compile_6 = ($context["site"] ?? null)) && is_array($__internal_compile_6) || $__internal_compile_6 instanceof ArrayAccess ? ($__internal_compile_6["site_name"] ?? null) : null);
        echo " Work?
                                </button>
                            </h2>
                            <div id=\"flush-collapseThree\" class=\"accordion-collapse collapse\"
                                data-bs-parent=\"#accordionFlushExample\">
                                <div class=\"accordion-body\">
                                    ";
        // line 523
        echo (($__internal_compile_7 = ($context["site"] ?? null)) && is_array($__internal_compile_7) || $__internal_compile_7 instanceof ArrayAccess ? ($__internal_compile_7["site_name"] ?? null) : null);
        echo " assist you in connecting and interacting with a bigger base of current and
                                    potential customers. SMM panels assist you in spreading fresh updates about your
                                    company
                                    to a larger audience when you post about a product. We work with you as a Facebook
                                    or
                                    Instagram business to enhance your engagement and conversion.
                                </div>
                            </div>
                        </div>
                        <div class=\"accordion-item\">
                            <h2 class=\"accordion-header\">
                                <button class=\"accordion-button collapsed\" type=\"button\" data-bs-toggle=\"collapse\"
                                    data-bs-target=\"#flush-collapseFour\" aria-expanded=\"false\"
                                    aria-controls=\"flush-collapseFour\">
                                    Which is the best SMM Panel?
                                </button>
                            </h2>
                            <div id=\"flush-collapseFour\" class=\"accordion-collapse collapse\"
                                data-bs-parent=\"#accordionFlushExample\">
                                <div class=\"accordion-body\">
                                    ";
        // line 543
        echo (($__internal_compile_8 = ($context["site"] ?? null)) && is_array($__internal_compile_8) || $__internal_compile_8 instanceof ArrayAccess ? ($__internal_compile_8["site_name"] ?? null) : null);
        echo " is the best SMM panel as they provide smm services in cheap and is the best
                                    <a href=\"/blog/\">SMM reseller
                                        panel</a> in the market as well.
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Why Choose US Section -->
<section id=\"whyChooseUs\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 col-md-12 col-12\">
                <h2 class=\"text-white section__title text-center mb-5\">
                    Why Choose Us
                </h2>
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"why__item__wrap\">
                    <div class=\"why__item__top\">
                        <div class=\"why__icon\">
                            <div style=\"width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#7b2ff7,#9c27b0);display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-award\" style=\"font-size:26px;color:#fff;\"></i></div>
                        </div>
                        <h3>
                            High Quality <br>
                            Service
                        </h3>
                    </div>
                    <p>
                        Experience excellence with our high-quality SMM services. At ";
        // line 579
        echo (($__internal_compile_9 = ($context["site"] ?? null)) && is_array($__internal_compile_9) || $__internal_compile_9 instanceof ArrayAccess ? ($__internal_compile_9["site_name"] ?? null) : null);
        echo ", we're committed to
                        delivering top-tier solutions that elevate your online presence and engagement, ensuring
                        exceptional results for your brand.
                    </p>
                </div>
            </div>

            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"why__item__wrap\">
                    <div class=\"why__item__top\">
                        <div class=\"why__icon\">
                            <div style=\"width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#ff8c00,#ffc107);display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-dollar-sign\" style=\"font-size:26px;color:#fff;\"></i></div>
                        </div>
                        <h3>
                            Starting From <br> \$0.001
                        </h3>
                    </div>
                    <p>
                        Unlock powerful SMM solutions at unbeatable prices, starting from just \$0.001. Access a range of
                        services designed to fit your budget and amplify your online presence affordably.
                    </p>
                </div>
            </div>

            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"why__item__wrap\">
                    <div class=\"why__item__top\">
                        <div class=\"why__icon\">
                            <div style=\"width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#00c853,#00bcd4);display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-rocket\" style=\"font-size:26px;color:#fff;\"></i></div>
                        </div>
                        <h3>
                            We Provide Super <br> Fast Delivery
                        </h3>
                    </div>
                    <p>
                        Experience lightning-fast results with our super-fast delivery. At ";
        // line 614
        echo (($__internal_compile_10 = ($context["site"] ?? null)) && is_array($__internal_compile_10) || $__internal_compile_10 instanceof ArrayAccess ? ($__internal_compile_10["site_name"] ?? null) : null);
        echo ", we prioritize prompt
                        service, ensuring your social media needs are met swiftly and efficiently.
                    </p>
                </div>
            </div>


            <div class=\"col-lg-6 col-md-6 col-12\">
                <div class=\"why__item__wrap\">
                    <div class=\"why__item__top\">
                        <div class=\"why__icon\">
                            <div style=\"width:56px;height:56px;border-radius:14px;background:linear-gradient(135deg,#007bff,#0088CC);display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-tachometer-alt\" style=\"font-size:26px;color:#fff;\"></i></div>
                        </div>
                        <h3>
                            User Friendly <br> Dashboard
                        </h3>
                    </div>
                    <p>
                        Navigate effortlessly through our user-friendly dashboard. Our intuitive interface is designed
                        to offer ease of use, enabling you to manage your SMM activities seamlessly and efficiently.
                    </p>
                </div>
            </div>


        </div>
    </div>
</section>

<section id=\"testimonialSection\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 col-md-12 col-12\">
                <h2 class=\"section__title text-center mb-5\">
                    Testimonials
                </h2>

                <div class=\"testimonials__wraper\">
                    <!-- Slider main container -->
                    <div class=\"swiper swiper-initialized swiper-horizontal swiper-backface-hidden\">
                        <!-- Additional required wrapper -->
                        <div class=\"swiper-wrapper\" id=\"swiper-wrapper-7e57ba3736a1abb3\" aria-live=\"off\" style=\"transition-duration: 0ms; transform: translate3d(0px, 0px, 0px); transition-delay: 0ms;\">
                            <!-- Slides -->
                            <div class=\"swiper-slide swiper-slide-active\" role=\"group\" aria-label=\"1 / 6\" style=\"width: 1116px;\">
                                <div class=\"testimonial__item__wrap\">
                                    <div class=\"top__icon text-center\">
                                        <div style=\"width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#00c853);display:flex;align-items:center;justify-content:center;margin:0 auto;\"><i class=\"fas fa-quote-left\" style=\"font-size:20px;color:#fff;\"></i></div>
                                    </div>
                                    <p>
                                        \"TechSMM has been a game-changer for our social media presence. Their services helped us skyrocket our engagement and reach. Highly recommended!\"
                                    </p>
                                    <div class=\"testi__user text-center\">
                                        <div class=\"stars text-center\">
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                        </div>
                                        <h4>Sarah Johnson</h4>
                                        <p>CEO of Digital Boosters</p>
                                    </div>
                                </div>
                            </div>
                            <div class=\"swiper-slide swiper-slide-next\" role=\"group\" aria-label=\"2 / 6\" style=\"width: 1116px;\">
                                <div class=\"testimonial__item__wrap\">
                                    <div class=\"top__icon text-center\">
                                        <div style=\"width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#00c853);display:flex;align-items:center;justify-content:center;margin:0 auto;\"><i class=\"fas fa-quote-left\" style=\"font-size:20px;color:#fff;\"></i></div>
                                    </div>
                                    <p>
                                        \"Impressed with TechSMM's professionalism and quality service. Their prompt delivery and strategic approach significantly boosted our online visibility.\"
                                    </p>
                                    <div class=\"testi__user text-center\">
                                        <div class=\"stars text-center\">
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                        </div>
                                        <h4>Mark Stevens</h4>
                                        <p>Marketing Manager at Insightful Minds</p>
                                    </div>
                                </div>
                            </div>
                            <div class=\"swiper-slide\" role=\"group\" aria-label=\"3 / 6\" style=\"width: 1116px;\">
                                <div class=\"testimonial__item__wrap\">
                                    <div class=\"top__icon text-center\">
                                        <div style=\"width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#00c853);display:flex;align-items:center;justify-content:center;margin:0 auto;\"><i class=\"fas fa-quote-left\" style=\"font-size:20px;color:#fff;\"></i></div>
                                    </div>
                                    <p>
                                        \"I've tried several SMM services, but TechSMM stands out. Their user-friendly platform and exceptional results have been instrumental in our growth.\"
                                    </p>
                                    <div class=\"testi__user text-center\">
                                        <div class=\"stars text-center\">
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                        </div>
                                        <h4>Emily Carter</h4>
                                        <p>Founder of Sparkling Trends</p>
                                    </div>
                                </div>
                            </div>
                            <div class=\"swiper-slide\" role=\"group\" aria-label=\"4 / 6\" style=\"width: 1116px;\">
                                <div class=\"testimonial__item__wrap\">
                                    <div class=\"top__icon text-center\">
                                        <div style=\"width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#00c853);display:flex;align-items:center;justify-content:center;margin:0 auto;\"><i class=\"fas fa-quote-left\" style=\"font-size:20px;color:#fff;\"></i></div>
                                    </div>
                                    <p>
                                        \"TechSMM's expertise and commitment to quality are commendable. Their fast delivery and comprehensive solutions have been invaluable to our campaigns.\"
                                    </p>
                                    <div class=\"testi__user text-center\">
                                        <div class=\"stars text-center\">
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                        </div>
                                        <h4>Alex Ramirez</h4>
                                        <p>Social Media Strategist at Buzz Hive</p>
                                    </div>
                                </div>
                            </div>
                            <div class=\"swiper-slide\" role=\"group\" aria-label=\"5 / 6\" style=\"width: 1116px;\">
                                <div class=\"testimonial__item__wrap\">
                                    <div class=\"top__icon text-center\">
                                        <div style=\"width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#00c853);display:flex;align-items:center;justify-content:center;margin:0 auto;\"><i class=\"fas fa-quote-left\" style=\"font-size:20px;color:#fff;\"></i></div>
                                    </div>
                                    <p>
                                        \"TechSMM exceeded our expectations. The high-quality services and competitive pricing have made them our go-to choice for all our SMM needs.\"
                                    </p>
                                    <div class=\"testi__user text-center\">
                                        <div class=\"stars text-center\">
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                        </div>
                                        <h4>Rachel Thompson</h4>
                                        <p>Marketing Director at Bright Horizons</p>
                                    </div>
                                </div>
                            </div>
                            <div class=\"swiper-slide\" role=\"group\" aria-label=\"6 / 6\" style=\"width: 1116px;\">
                                <div class=\"testimonial__item__wrap\">
                                    <div class=\"top__icon text-center\">
                                        <div style=\"width:50px;height:50px;border-radius:50%;background:linear-gradient(135deg,#ff8c00,#00c853);display:flex;align-items:center;justify-content:center;margin:0 auto;\"><i class=\"fas fa-quote-left\" style=\"font-size:20px;color:#fff;\"></i></div>
                                    </div>
                                    <p>
                                        \"TechSMM has been instrumental in our social media strategy. Their affordable pricing combined with exceptional service quality helped us reach new audiences and boost sales. Grateful for their impactful contributions to our success!\"
                                    </p>
                                    <div class=\"testi__user text-center\">
                                        <div class=\"stars text-center\">
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                            <i class=\"fa fa-star\"></i>
                                        </div>
                                        <h4>Michael Chen</h4>
                                        <p>E-commerce Entrepreneur at ZenCommerce</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- If we need pagination -->
                        <div class=\"swiper-pagination\"></div>

                        <!-- If we need navigation buttons -->
                        <button class=\"sliderBtn slide-prev swiper-button-disabled\" type=\"button\" tabindex=\"-1\" aria-label=\"Previous slide\" aria-controls=\"swiper-wrapper-7e57ba3736a1abb3\" aria-disabled=\"true\" disabled=\"\">
                            <iconify-icon icon=\"lets-icons:arrow-drop-left\"></iconify-icon>
                        </button>
                        <button class=\"sliderBtn slide-next\" type=\"button\" tabindex=\"0\" aria-label=\"Next slide\" aria-controls=\"swiper-wrapper-7e57ba3736a1abb3\" aria-disabled=\"false\">
                            <iconify-icon icon=\"lets-icons:arrow-drop-right\"></iconify-icon>
                        </button>

                    <span class=\"swiper-notification\" aria-live=\"assertive\" aria-atomic=\"true\"></span></div>
                </div>
            </div>
        </div>
    </div>
</section>
<!------
<section id=\"blogSection\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 col-md-12 col-12\">
                <h2 class=\"section__title text-center mb-5\">
                    Read Our Blog
                </h2>
            </div>
        </div>
        <div class=\"row\">
            <div class=\"col-lg-4 col-md-4 col-12\">
                <div class=\"blog__card\">
                    <div class=\"blog__image\">
                        <div style=\"height:180px;background:linear-gradient(135deg,#667eea,#764ba2);border-radius:12px;display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-share-alt\" style=\"font-size:48px;color:rgba(255,255,255,0.8);\"></i></div>
                    </div>
                    <div class=\"blog__content\">
                        <h6 class=\"blog__date\">Nov 1, 2023</h6>
                        <h3>
                            The Ultimate Guide to Social Media Promotion: Strategies for Business Success
                        </h3>
                        <p>
                            This comprehensive companion explores colorful social media creation strategies that businesses can apply to enhance their online presence...
                        </p>
                        <a href=\"/blog/theultimateguidetosocialmediapromotionstrategiesforbusinesssuccess\" class=\"btn btn-primary btn-sm btn-gradient\">Learn More</a>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-4 col-md-4 col-12\">
                <div class=\"blog__card\">
                    <div class=\"blog__image\">
                        <div style=\"height:180px;background:linear-gradient(135deg,#f093fb,#f5576c);border-radius:12px;display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-chart-bar\" style=\"font-size:48px;color:rgba(255,255,255,0.8);\"></i></div>
                    </div>
                    <div class=\"blog__content\">
                        <h6 class=\"blog__date\">Nov 1, 2023</h6>
                        <h3>
                            Le moins cher SMM Panel - Meilleur Panel SMM sur le marché

                        </h3>
                        <p>
                            Are you looking for ways to earn some extra cash during your study and work hours? Have you ever thought about using your smartphone to earn money online? Well, you can do that now with the help of the ";
        // line 841
        echo (($__internal_compile_11 = ($context["site"] ?? null)) && is_array($__internal_compile_11) || $__internal_compile_11 instanceof ArrayAccess ? ($__internal_compile_11["site_name"] ?? null) : null);
        echo " website.
                        </p>
                        <a href=\"/blog/cheapestsmmpanel-bestsmmpanelintheholemarket\" class=\"btn btn-primary btn-sm btn-gradient\">Learn More</a>
                    </div>
                </div>
            </div>
            <div class=\"col-lg-4 col-md-4 col-12\">
                <div class=\"blog__card\">
                    <div class=\"blog__image\">
                        <div style=\"height:180px;background:linear-gradient(135deg,#4facfe,#00f2fe);border-radius:12px;display:flex;align-items:center;justify-content:center;\"><i class=\"fas fa-mobile-alt\" style=\"font-size:48px;color:rgba(255,255,255,0.8);\"></i></div>
                    </div>
                    <div class=\"blog__content\">
                        <h6 class=\"blog__date\">Nov 1, 2023</h6>
                        <h3>
                            ";
        // line 855
        echo (($__internal_compile_12 = ($context["site"] ?? null)) && is_array($__internal_compile_12) || $__internal_compile_12 instanceof ArrayAccess ? ($__internal_compile_12["site_name"] ?? null) : null);
        echo " - # 1 SMM panel | Le moins cher panel SMM dans le monde entier.
                        </h3>
                        <p>
                            Social media has become an integral part of our lives, both personally and professionally. With the rise of social media platforms, businesses have started to leverage social media to reach their...
                        </p>
                        <a href=\"\" class=\"btn btn-primary btn-sm btn-gradient\">Learn More</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
----------->
<section id=\"paymentSection\">
    <div class=\"container\">
        <div class=\"row\">
            <div class=\"col-lg-12 col-12\">
                <h2 class=\"section__title text-center\">
                    We Accept Multiple Payment Methods
                </h2>
                <div class=\"payment__images\">
                    <div class=\"for_desktop\" style=\"background:linear-gradient(135deg,#667eea,#764ba2);border-radius:20px;padding:40px;text-align:center;\"><i class=\"fas fa-chart-line\" style=\"font-size:60px;color:#fff;display:block;margin-bottom:12px;\"></i><p style=\"color:#fff;font-weight:700;font-size:18px;margin:0;\">Legrand</p></div>
                    
                </div>
            </div>
        </div>
    </div>
</section>
  </main>
        


";
        // line 887
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable((($__internal_compile_13 = ($context["site"] ?? null)) && is_array($__internal_compile_13) || $__internal_compile_13 instanceof ArrayAccess ? ($__internal_compile_13["languages"] ?? null) : null));
        foreach ($context['_seq'] as $context["_key"] => $context["lang"]) {
            // line 888
            echo "  ";
            if ((($__internal_compile_14 = $context["lang"]) && is_array($__internal_compile_14) || $__internal_compile_14 instanceof ArrayAccess ? ($__internal_compile_14["active"] ?? null) : null)) {
                // line 889
                echo "    <script src=\"https://www.google.com/recaptcha/api.js?hl=";
                echo (($__internal_compile_15 = $context["lang"]) && is_array($__internal_compile_15) || $__internal_compile_15 instanceof ArrayAccess ? ($__internal_compile_15["code"] ?? null) : null);
                echo "\"></script>
  ";
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['lang'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 892
        echo "
 ";
        // line 893
        $this->loadTemplate("footer.twig", "login.twig", 893)->display($context);
        // line 894
        echo "\t\t
";
    }

    public function getTemplateName()
    {
        return "login.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  998 => 894,  996 => 893,  993 => 892,  983 => 889,  980 => 888,  976 => 887,  941 => 855,  924 => 841,  694 => 614,  656 => 579,  617 => 543,  594 => 523,  585 => 517,  525 => 460,  499 => 437,  407 => 348,  380 => 324,  371 => 318,  294 => 244,  127 => 80,  107 => 62,  101 => 59,  98 => 58,  96 => 57,  39 => 2,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "login.twig", "/home/tipmrnhl/legrandmalaba.com/app/views/GreenSMM/login.twig");
    }
}
