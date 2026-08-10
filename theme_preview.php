<?php
session_start();

$allowed = ['Eternity','xblur','ShineVibes','Pink2','GreenSMM','smmpanelP','smmxz','legend',
            'caste','smmrealm','whitepro','sunwhite2','growest','bestexpertz','smmworld',
            'smmgen','1xpanel-Purple'];

$theme = $_GET['theme'] ?? 'ShineVibes';
if (!in_array($theme, $allowed)) { die('Invalid theme.'); }

try {
    $conn = new PDO('mysql:host=127.0.0.1;port=3307;dbname=ssd_boost', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $conn->exec("UPDATE settings SET site_theme='" . addslashes($theme) . "'");
    $user = $conn->query("SELECT * FROM clients WHERE client_id=1")->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die('DB error: ' . $e->getMessage());
}

$_SESSION["msmbilisim_userlogin"]  = 1;
$_SESSION["msmbilisim_userid"]     = $user["client_id"];
$_SESSION["msmbilisim_userpass"]   = $user["password"];
$_SESSION["currency_hash"]         = $user["currency_type"];
$_SESSION["lang"]                  = "en";

header("Location: /neworder");
exit;
