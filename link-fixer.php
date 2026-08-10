<?php
function resolveFacebookLink(string $url): string {
    if (preg_match('/facebook\.com\/profile\.php\?id=(\d+)/i', $url, $match)) {
        return 'https://www.facebook.com/' . $match[1];
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_NOBODY => true,
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (LinkFixer)',
    ]);
    curl_exec($ch);
    $final = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
    curl_close($ch);

    if (!$final) return $url;

    if (strpos($final, 'facebook.com/login/?next=') !== false) {
        $parts = parse_url($final);
        parse_str($parts['query'] ?? '', $query);
        if (!empty($query['next'])) {
            $decoded = urldecode($query['next']);
            $final = $decoded;
        }
    }

    $final = preg_replace('/[?&](rdid|refsrc|share_url|mibextid)=[^&?#]*/i', '', $final);
    $final = rtrim($final, '?&');

    if (preg_match('/facebook\.com\/profile\.php\?id=(\d+)/i', $final, $match)) {
        return 'https://www.facebook.com/' . $match[1];
    }

    if (preg_match('#facebook\.com/people/[^/]+/(\d+)/?#i', $final, $match)) {
        return 'https://www.facebook.com/' . $match[1];
    }

    return $final;
}

function cleanInstagramLink(string $url): string {
    return preg_replace('/\\?igsh[^\\s]*/i', '', $url);
}

function cleanTikTokLink(string $url): string {
    return preg_replace('/\\?_.*$/i', '', $url);
}

function cleanYouTubeLink(string $url): string {
    if (preg_match('#youtu\.be/([^?&/]+)#i', $url, $match)) {
        return "https://www.youtube.com/watch?v=" . $match[1];
    }

    if (preg_match('#youtube\.com/shorts/([^?&/]+)#i', $url, $match)) {
        return "https://www.youtube.com/watch?v=" . $match[1];
    }

    $parts = parse_url($url);
    parse_str($parts['query'] ?? '', $query);

    if (!empty($query['v'])) {
        return "https://www.youtube.com/watch?v=" . $query['v'];
    }

    $url = preg_replace('/[?&](feature|si|t|pp)=[^&?#]*/i', '', $url);
    return rtrim($url, '?&');
}

function cleanTelegramLink(string $url): string {
    // Normalize tg:// to https://t.me/
    $url = str_replace("tg://resolve?domain=", "https://t.me/", $url);

    // Remove ?start and ?startapp params
    $url = preg_replace('/[?&](start|startapp|domain)=[^&?#]*/i', '', $url);
    return rtrim($url, '?&');
}

function cleanTwitterLink(string $url): string {
    // Normalize x.com → twitter.com
    $url = str_replace("x.com", "twitter.com", $url);

    // Strip tracking params (?s, ?t, utm_*)
    $url = preg_replace('/[?&](s|t|utm_[^=]*)=[^&?#]*/i', '', $url);
    return rtrim($url, '?&');
}

// Main handler
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = $_POST['type'] ?? '';
    $link = trim($_POST['link'] ?? '');

    switch ($type) {
        case 'facebook':
            $fixed = resolveFacebookLink($link);
            break;
        case 'instagram':
            $fixed = cleanInstagramLink($link);
            break;
        case 'tiktok':
            $fixed = cleanTikTokLink($link);
            break;
        case 'youtube':
            $fixed = cleanYouTubeLink($link);
            break;
        case 'telegram':
            $fixed = cleanTelegramLink($link);
            break;
        case 'twitter':
            $fixed = cleanTwitterLink($link);
            break;
        default:
            $fixed = $link;
            break;
    }

    header('Content-Type: application/json');
    echo json_encode(['fixed' => $fixed], JSON_UNESCAPED_SLASHES);
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Link Fixer (FB | IG | TikTok)</title>
<meta name="viewport" content="width=device-width, initial-scale=1" />
<style>
    * {
        box-sizing: border-box;
    }
    body {
        margin: 0; padding: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #fff; 
        color: #333;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
        transition: background-color 0.3s, color 0.3s;
    }
    #modeBanner {
        position: sticky;
        top: 0;
        z-index: 9999;
        background-color: #9cc8ff;
        color: #222;
        padding: 15px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
    }

    #modeBanner span {
        font-size: 22px;
        font-weight: bold;
        text-transform: uppercase;
        letter-spacing: 1px;
    }
    
    body.dark {
        background: #121212;
        color: #eee;
    }

    .container {
        max-width: 600px;
        margin: 40px auto 60px;
        padding: 30px 32px;
        background: #9cc8ff; 
        border-radius: 14px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        text-align: center;
        transition: background-color 0.3s, color 0.3s;
    }
    body.dark .container {
        background: #1e1e1e;
        color: #eee;
        box-shadow: 0 8px 30px rgba(255 255 255 / 0.1);
    }

    h2 {
        margin-top: 0;
        font-size: 28px;
        margin-bottom: 10px;
        font-weight: 700;
    }
    p {
        color: #040303;
        font-size: 16px;
        margin-bottom: 30px;
    }
    body.dark p {
        color: #bbb;
    }

    #modeToggle {
        position: fixed;
        top: 16px;
        right: 16px;
        cursor: pointer;
        background: none;
        border: none;
        font-size: 24px;
        color: #ffb600;
        transition: color 0.3s;
        user-select: none;
        z-index: 1000;
    }
    body.dark #modeToggle {
        color: #ffdd57;
    }
    #modeToggle:hover {
        color: #ffa500;
    }

    .platform-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 26px;
        font-size: 16px;
        margin: 8px 6px;
        border: none;
        border-radius: 8px;
        background: rgba(51, 144, 255, 0.7);
        color: #fff;
        cursor: pointer;
        transition: background 0.25s ease;
        box-shadow: 0 3px 6px rgba(0,123,255,0.4);
        user-select: none;
    }
    body.dark .platform-btn {
        background: #3390ff;
        box-shadow: 0 3px 6px rgba(51,144,255,0.7);
    }
    .platform-btn svg {
        width: 20px; height: 20px;
        fill: currentColor;
    }
    .platform-btn:hover {
        background: rgb(159 45 222 / 70%);
        box-shadow: 0 4px 12px rgb(159 45 222 / 70%);
    }
    body.dark .platform-btn:hover {
        background: #2078e0;
    }

    .link-box {
        display: none;
        margin-top: 28px;
        text-align: left;
    }
    .link-box h3 {
        margin-bottom: 12px;
        font-weight: 600;
    }
    input[type="text"] {
        width: 100%;
        padding: 14px 16px;
        font-size: 16px;
        border: 1.8px solid #ccc;
        border-radius: 8px;
        transition: border-color 0.3s;
    }
    input[type="text"]:focus {
        outline: none;
        border-color: #007bff;
        box-shadow: 0 0 8px rgba(0,123,255,0.4);
    }
    body.dark input[type="text"] {
        background: #2a2a2a;
        color: #eee;
        border-color: #555;
    }
    body.dark input[type="text"]:focus {
        border-color: #3390ff;
        box-shadow: 0 0 10px rgba(51,144,255,0.7);
    }

    .submit {
        margin-top: 18px;
        padding: 12px 28px;
        font-size: 17px;
        border: none;
        background: #dc0dc9;
        color: #fff;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s ease;
        box-shadow: 0 4px 12px rgb(216 54 184 / 50%);
        display: block;
        margin-left: auto;
        margin-right: auto;
        width: 150px;
    }

    .submit:hover {
        background: #e851d4;
        box-shadow: 0 6px 16px rgb(208 97 141 / 70%);
    }
    body.dark .submit {
        background: #3cd14a;
        box-shadow: 0 4px 14px rgba(60,209,74,0.8);
        color: #111;
    }
    body.dark .submit:hover {
        background: #2db93b;
        box-shadow: 0 6px 18px rgba(45,185,59,0.9);
    }

    .result {
        margin-top: 32px;
        font-weight: 600;
        word-break: break-word;
        text-align: center;
        background: #f8a6e0; /* green shade */
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 6px 16px rgba(40,167,69,0.15);
        transition: background-color 0.3s, color 0.3s;
    }
    body.dark .result {
        background: #2b5a2e;
        color: #d4f1d4;
        box-shadow: 0 8px 22px rgba(60,209,74,0.4);
    }
    .result a {
        color: #28a745;
        text-decoration: none;
        word-break: break-all;
    }
    .result a:hover {
        text-decoration: underline;
    }

    .copy-btn {
        margin-top: 18px;
        padding: 11px 24px;
        font-size: 15px;
        background: #ce0d91;
        color: #fff;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: background 0.3s ease;
        user-select: none;
        box-shadow: 0 5px 14px rgb(224 75 178 / 50%);
    }
    .copy-btn:hover {
        background: #ed85a6;
        box-shadow:0 6px 18px rgb(214 122 206 / 80%);
    }
    body.dark .copy-btn {
        background: #ffa733;
        color: #222;
        box-shadow: 0 5px 16px rgba(255,167,51,0.6);
    }
    body.dark .copy-btn:hover {
        background: #ff9f1a;
        box-shadow: 0 6px 20px rgba(255,159,26,0.9);
    }

    #returnLink a {
        display: inline-block;
        margin-top: 22px;
        color: #007bff;
        text-decoration: none;
        font-size: 15px;
        font-weight: 600;
    }
    #returnLink a:hover {
        text-decoration: underline;
    }
    body.dark #returnLink a {
        color: #66aaff;
    }

    footer {
        text-align: center;
        margin-top: auto;
        padding: 20px;
        font-size: 14px;
        background-color: #9cc8ff; 
        color: #444;
        user-select: none;
        transition: background-color 0.3s, color 0.3s;
    }
    body.dark footer {
        background-color: #222;
        color: #bbb;
    }
    footer a {
        color: #007bff;
        text-decoration: none;
        font-weight: 600;
    }
    footer a:hover {
        text-decoration: underline;
    }
    body.dark footer a {
        color: #66aaff;
    }

#modeBanner {
  display: flex;
  justify-content: flex-start; /* logo stays on the left */
  align-items: center; /* vertical center */
  background-color: #89b9f5; /* match your theme */
  padding: 15px 40px; /* space from left/right edges */
}

  #modeBanner img {
    max-width: 380px; /* desktop size */
    width: 100%;
    height: auto;
  }

  @media (max-width: 768px) {
    #modeBanner {
      padding: 4px 0;
    }

    #modeBanner img {
      max-width: 220px; /* mobile size */
    }
  }

  @media (max-width: 480px) {
    #modeBanner img {
      max-width: 180px; /* smaller phones */
    }
    }
</style>
</head>
<body>
<div id="modeBanner">
  
</div>

<style>
  /* Fixed Header Container */
  #modeBanner {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 80px; /* fixed height for header */
    background-color: #89b9f5; /* match your theme color */
    display: flex;
    align-items: center;
    justify-content: flex-start;
    padding: 0 40px; /* spacing on left/right */
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
    z-index: 9999;
  }

  /* Logo */
  #modeBanner img {
    height: 55px; /* desktop size */
    width: auto;
  }

  /* Offset content so it doesn't hide behind fixed header */
  body {
    margin: 0;
    padding-top: 80px; /* equal to header height */
  }

  /* Responsive Adjustments */
  @media (max-width: 992px) {
    #modeBanner {
      height: 70px;
      padding: 0 25px;
    }

    #modeBanner img {
      height: 45px;
    }

    body {
      padding-top: 70px;
    }
  }

  @media (max-width: 576px) {
    #modeBanner {
      height: 60px;
      padding: 0 15px;
    }

    #modeBanner img {
      height: 38px;
    }

    body {
      padding-top: 60px;
    }
  }
</style>
<div class="container">
    <h2>Link Fixer Tool</h2>
    <p>Automatically fix shortened and messy links for Facebook, Instagram, TikTok, YouTube, Telegram, and Twitter (X).</p>
    <div>
        <!-- Facebook -->
        <button class="platform-btn" onclick="selectPlatform('facebook')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/6/6c/Facebook_Logo_2023.png" alt="Facebook" style="width:20px;height:20px;"> Facebook
        </button>

        <!-- Instagram -->
        <button class="platform-btn" onclick="selectPlatform('instagram')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/a/a5/Instagram_icon.png" alt="Instagram" style="width:20px;height:20px;"> Instagram
        </button>

        <!-- TikTok -->
        <button class="platform-btn" onclick="selectPlatform('tiktok')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/3/34/Ionicons_logo-tiktok.svg" alt="TikTok" style="width:20px;height:20px;"> TikTok
        </button>

        <!-- YouTube -->
        <button class="platform-btn" onclick="selectPlatform('youtube')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/d/d1/Youtube-variation.png/250px-Youtube-variation.png" alt="YouTube" style="width:20px;height:20px;"> YouTube
        </button>

        <!-- Telegram -->
        <button class="platform-btn" onclick="selectPlatform('telegram')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/8/82/Telegram_logo.svg" alt="Telegram" style="width:20px;height:20px;"> Telegram
        </button>

        <!-- Twitter / X -->
        <button class="platform-btn" onclick="selectPlatform('twitter')">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/c/c6/X_Twitter_icon.svg/250px-X_Twitter_icon.svg.png" alt="Twitter" style="width:20px;height:20px;"> Twitter / X
        </button>
    </div>
    <form id="fixerForm" method="POST">
        <div class="link-box" id="linkBox">
            <h3>Paste your link:</h3>
            <input type="hidden" name="type" id="platformType">
            <input type="text" name="link" id="linkInput" placeholder="Paste your link here" required />
            <button type="submit" class="submit">Fix Link</button>
        </div>
    </form>
    <div id="resultBox" class="result" style="display:none;"></div>
    <button class="copy-btn" id="copyBtn" style="display:none;">Copy Fixed Link</button>
    <div id="returnLink">
        <a href="/">Return to Website</a>
    </div>
</div>
<footer>
    © 2025 Link Fixer. All rights reserved.
</footer>
<script>
    function selectPlatform(type) {
        document.getElementById('linkBox').style.display = 'block';
        document.getElementById('platformType').value = type;
        document.getElementById('linkInput').focus();
        document.getElementById('resultBox').style.display = 'none';
        document.getElementById('copyBtn').style.display = 'none';
    }
    document.getElementById('fixerForm').addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(this);
        const resultBox = document.getElementById('resultBox');
        const copyBtn = document.getElementById('copyBtn');
        resultBox.innerHTML = 'Loading...';
        resultBox.style.display = 'block';
        copyBtn.style.display = 'none';
        fetch('', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            resultBox.innerHTML = `<a href="${data.fixed}" target="_blank">${data.fixed}</a>`;
            copyBtn.style.display = 'inline-block';
        })
        .catch(() => {
            resultBox.innerHTML = 'An error occurred.';
        });
    });
    document.getElementById('copyBtn').addEventListener('click', function () {
        const text = document.getElementById('resultBox').innerText;
        navigator.clipboard.writeText(text).then(() => {
            this.innerText = 'Copied!';
            setTimeout(() => { this.innerText = 'Copy Fixed Link'; }, 2000);
        });
    });
</script>
</body>
</html