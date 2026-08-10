<!---------   
=== Modified By: User Request

-----------> 

<?php include 'admin_security.php'; ?>
<?php
if (!defined('BASEPATH')) {
    die('Direct access to the script is not allowed');
}

// Check for successful logout message
if (isset($_SESSION['logout_success'])) {
    $logout_message = $_SESSION['logout_success'];
    unset($_SESSION['logout_success']);
}
?>
<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Legrand Panel - Administration</title>
    <link rel="icon" href="https://" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Space+Grotesk:wght@500;700&display=swap" rel="stylesheet">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <style>
        :root {
            --background-light: #f0f4ff;
            --background-dark: #050d1a;
            --card-light: rgba(255, 255, 255, 0.85);
            --card-dark: rgba(8, 20, 45, 0.85);
            --border-light: rgba(37, 99, 235, 0.2);
            --border-dark: rgba(59, 130, 246, 0.3);
            --text-primary-light: #1e3a8a;
            --text-primary-dark: #bfdbfe;
            --text-secondary-light: #1d4ed8;
            --text-secondary-dark: #93c5fd;
            --input-bg-light: #f0f4ff;
            --input-bg-dark: #050d1a;
            --focus-ring: #2563eb;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--background-light);
            transition: background-color 0.5s ease;
            margin: 0;
        }
        
        html.dark body {
            background-color: var(--background-dark);
        }

        .heading-font {
            font-family: 'Space Grotesk', sans-serif;
        }

        /* Animated gradient background */
        .aurora-wrapper {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            overflow: hidden;
            z-index: -1;
        }
        .aurora-blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(110px);
            opacity: 0.25;
        }
        .dark .aurora-blob { opacity: 0.35; }
        .aurora-blob:nth-child(1) {
            background: #2563eb;
            width: 500px; height: 500px;
            top: -180px; left: -120px;
            animation: move-blob-1 22s infinite alternate;
        }
        .aurora-blob:nth-child(2) {
            background: #7c3aed;
            width: 600px; height: 600px;
            bottom: -250px; right: -180px;
            animation: move-blob-2 28s infinite alternate;
        }
        .aurora-blob:nth-child(3) {
            background: #0ea5e9;
            width: 350px; height: 350px;
            bottom: 80px; left: 120px;
            animation: move-blob-3 20s infinite alternate;
        }
        .aurora-blob:nth-child(4) {
            background: #6366f1;
            width: 280px; height: 280px;
            top: 30%; right: 20%;
            animation: move-blob-1 16s infinite alternate-reverse;
        }

        @keyframes move-blob-1 {
            from { transform: translate(-20px, 10px) scale(1); }
            to   { transform: translate(30px, -60px) scale(1.15); }
        }
        @keyframes move-blob-2 {
            from { transform: translate(-30px, -15px) scale(0.85); }
            to   { transform: translate(50px, 40px) scale(1.12); }
        }
        @keyframes move-blob-3 {
            from { transform: translate(30px, -25px) scale(0.9); }
            to   { transform: translate(-15px, 25px) scale(1.05); }
        }

        /* Glassmorphism card */
        .glass-card {
            background-color: var(--card-light);
            backdrop-filter: blur(28px);
            -webkit-backdrop-filter: blur(28px);
            border: 1px solid var(--border-light);
            transition: background-color 0.5s ease, border 0.5s ease;
        }
        .dark .glass-card {
            background-color: var(--card-dark);
            border: 1px solid var(--border-dark);
        }

        /* Floating label inputs */
        .floating-input-group { position: relative; }
        .floating-input { padding-top: 1.5rem; }
        .floating-label {
            position: absolute;
            top: 50%; left: 2.75rem;
            transform: translateY(-50%);
            color: var(--text-secondary-light);
            transition: all 0.2s ease-out;
            pointer-events: none;
            font-size: 1rem;
        }
        .dark .floating-label { color: var(--text-secondary-dark); }
        .floating-input:focus + .floating-label,
        .floating-input:not(:placeholder-shown) + .floating-label {
            top: 0.6rem;
            transform: translateY(0);
            font-size: 0.72rem;
            font-weight: 600;
            color: var(--focus-ring);
            letter-spacing: 0.3px;
        }
        .dark .floating-input:focus + .floating-label { color: #93c5fd; }

        /* Animations */
        .form-element {
            opacity: 0;
            transform: translateY(18px);
            animation: fadeInUp 0.5s ease forwards;
        }
        @keyframes fadeInUp {
            to { opacity: 1; transform: translateY(0); }
        }

        /* Logo text */
        .logo-text {
            font-family: 'Space Grotesk', sans-serif;
            font-size: 2.2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 60%, #0ea5e9 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.5px;
        }
        .dark .logo-text {
            background: linear-gradient(135deg, #60a5fa 0%, #a78bfa 60%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Badge */
        .admin-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(135deg, rgba(37,99,235,0.12), rgba(124,58,237,0.12));
            border: 1px solid rgba(37,99,235,0.25);
            color: #2563eb;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 999px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
        }
        .dark .admin-badge {
            background: linear-gradient(135deg, rgba(96,165,250,0.12), rgba(167,139,250,0.12));
            border-color: rgba(96,165,250,0.25);
            color: #93c5fd;
        }

        /* Input styles */
        .admin-input {
            width: 100%;
            padding: 1.1rem 1rem 0.5rem 3rem;
            border-radius: 0.6rem;
            background-color: var(--input-bg-light);
            border: 2px solid transparent;
            outline: none;
            transition: all 0.2s;
            color: #1e3a8a;
            font-size: 0.95rem;
        }
        html.dark .admin-input {
            background-color: rgba(5,13,26,0.8);
            color: #bfdbfe;
        }
        .admin-input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37,99,235,0.15);
        }

        /* Submit button */
        .btn-admin {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 0.85rem 1.5rem;
            font-weight: 700;
            font-size: 0.95rem;
            border-radius: 0.6rem;
            color: #fff;
            background: linear-gradient(135deg, #2563eb 0%, #7c3aed 100%);
            border: none;
            cursor: pointer;
            letter-spacing: 0.3px;
            box-shadow: 0 4px 20px rgba(37,99,235,0.35);
            transition: all 0.2s;
        }
        .btn-admin:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #6d28d9 100%);
            box-shadow: 0 6px 25px rgba(37,99,235,0.45);
            transform: translateY(-1px);
        }
        .btn-admin:active { transform: scale(0.98); }

        /* Theme toggle */
        #themeToggle {
            position: fixed; top: 1.2rem; right: 1.2rem;
            z-index: 50;
            padding: 0.7rem;
            border-radius: 50%;
            font-size: 1.1rem;
            color: #2563eb;
            border: none;
            cursor: pointer;
        }
        html.dark #themeToggle { color: #93c5fd; }

        /* Divider */
        .divider {
            display: flex; align-items: center; gap: 12px;
            margin: 0.5rem 0;
        }
        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: rgba(37,99,235,0.15);
        }

        /* Alert overrides */
        .alert {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-size: 0.88rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="aurora-wrapper">
        <div class="aurora-blob"></div>
        <div class="aurora-blob"></div>
        <div class="aurora-blob"></div>
        <div class="aurora-blob"></div>
    </div>
    
    <button id="themeToggle" class="glass-card">
        <i class="fas fa-sun dark:hidden"></i>
        <i class="fas fa-moon hidden dark:block"></i>
    </button>

    <main style="min-height:100vh;width:100%;display:flex;align-items:center;justify-content:center;padding:1.5rem;">
        <div style="width:100%;max-width:440px;">
            <div class="glass-card" style="border-radius:1.25rem;padding:2.5rem;box-shadow:0 8px 40px rgba(37,99,235,0.12);">
                
                <!-- Header -->
                <div class="form-element" style="text-align:center;margin-bottom:2rem;animation-delay:80ms;">
                    <div style="margin-bottom:0.75rem;">
                        <h1 class="logo-text">Legrand</h1>
                    </div>
                    <span class="admin-badge"><i class="fas fa-shield-halved"></i> Administration</span>
                    <p style="margin-top:0.75rem;font-size:0.88rem;color:#1d4ed8;" class="dark:text-blue-300">
                        Secure access for authorized administrators only.
                    </p>
                </div>

                <!-- Alerts -->
                <div style="margin-bottom:1rem;">
                    <?php if (isset($success)) : ?>
                        <div class="alert" style="background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.25);color:#065f46;animation-delay:160ms;" class="form-element">
                            <?= htmlspecialchars($successText, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php elseif (isset($error)) : ?>
                        <div class="alert form-element" style="background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.25);color:#991b1b;animation-delay:160ms;">
                            <?= htmlspecialchars($errorText, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($logout_message)): ?>
                        <div class="alert form-element" style="background:rgba(59,130,246,0.1);border:1px solid rgba(59,130,246,0.25);color:#1e3a8a;animation-delay:160ms;">
                            <?= htmlspecialchars($logout_message, ENT_QUOTES, 'UTF-8') ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Form -->
                <form id="loginForm" action="admin" method="post" style="display:flex;flex-direction:column;gap:1.1rem;">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                    
                    <!-- Username -->
                    <div class="floating-input-group form-element" style="animation-delay:240ms;">
                        <i class="fas fa-user" style="position:absolute;top:50%;left:1rem;transform:translateY(-50%);color:#3b82f6;pointer-events:none;"></i>
                        <input id="username" name="username" type="text" required placeholder=" "
                               class="admin-input floating-input">
                        <label for="username" class="floating-label">Username</label>
                    </div>

                    <!-- Password -->
                    <div class="floating-input-group form-element" style="position:relative;animation-delay:320ms;">
                        <i class="fas fa-lock" style="position:absolute;top:50%;left:1rem;transform:translateY(-50%);color:#3b82f6;pointer-events:none;"></i>
                        <input id="password" name="password" type="password" required placeholder=" "
                               class="admin-input floating-input" style="padding-right:3rem;">
                        <label for="password" class="floating-label">Password</label>
                        <button type="button" onclick="togglePassword()"
                                style="position:absolute;top:50%;right:1rem;transform:translateY(-50%);background:none;border:none;color:#3b82f6;cursor:pointer;padding:0;">
                           <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>

                    <!-- 2FA -->
                    <div class="floating-input-group form-element" style="animation-delay:400ms;">
                        <i class="fas fa-shield-halved" style="position:absolute;top:50%;left:1rem;transform:translateY(-50%);color:#3b82f6;pointer-events:none;"></i>
                        <input id="two_factor_code" name="two_factor_code" type="number" placeholder=" "
                               class="admin-input floating-input">
                        <label for="two_factor_code" class="floating-label">2FA Code (Optional)</label>
                    </div>
                    
                    <?php if ($_SESSION["recaptcha"]) : ?>
                        <div class="form-element" style="display:flex;justify-content:center;animation-delay:480ms;">
                            <div class="g-recaptcha" data-sitekey="<?= htmlspecialchars($settings["recaptcha_key"], ENT_QUOTES, 'UTF-8') ?>" data-theme="dark"></div>
                        </div>
                    <?php endif; ?>

                    <!-- Submit -->
                    <div class="form-element" style="animation-delay:560ms;">
                        <button type="submit" class="btn-admin">
                            <i class="fas fa-arrow-right-to-bracket"></i>
                            Secure Login
                        </button>
                    </div>
                </form>

                <!-- Footer note -->
                <div class="form-element" style="text-align:center;margin-top:1.5rem;animation-delay:640ms;">
                    <div class="divider"><span style="font-size:0.75rem;color:#64748b;">Secure</span></div>
                    <p style="font-size:0.78rem;color:#3b82f6;margin-top:0.5rem;">
                        <i class="fas fa-lock" style="margin-right:4px;"></i>Your IP address and login attempts are monitored.
                    </p>
                </div>
            </div>
        </div>
    </main>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const themeToggle = document.getElementById('themeToggle');
            const html = document.documentElement;
            const recaptcha = document.querySelector('.g-recaptcha');

            const applyTheme = (theme) => {
                html.classList.remove('light', 'dark');
                html.classList.add(theme);
                localStorage.setItem('theme', theme);
                if (recaptcha) {
                    recaptcha.setAttribute('data-theme', theme);
                }
            };

            const savedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
            applyTheme(savedTheme);

            themeToggle.addEventListener('click', () => {
                const newTheme = html.classList.contains('dark') ? 'light' : 'dark';
                applyTheme(newTheme);
            });

            window.togglePassword = function() {
                const passwordInput = document.getElementById('password');
                const toggleIcon = document.getElementById('toggleIcon');
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                toggleIcon.classList.toggle('fa-eye', !isPassword);
                toggleIcon.classList.toggle('fa-eye-slash', isPassword);
            };
        });
    </script>
</body>
</html>
