<?php
/**
 * GREENSMM AI Support Backend — bilingual FR/EN
 * Order & services context from live DB. No hallucination.
 * DeepSeek powered — identity: {SITE_NAME} AI
 */
if (session_status() === PHP_SESSION_NONE) session_start();
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); exit; }

define('_GSMM_KEY', 'sk-2cca817bfda84b7fb095902c75c95ece');

require_once dirname(__DIR__) . '/config/database.php';
// $db is now available (PDO)

$input   = json_decode(file_get_contents('php://input'), true) ?? [];
$message = trim($input['message'] ?? '');
$history = is_array($input['history'] ?? null) ? $input['history'] : [];

if (!$message) {
    echo json_encode(['reply' => 'Please type your message. / Veuillez taper votre message.']);
    exit;
}
if (mb_strlen($message) > 800) $message = mb_substr($message, 0, 800);

// ── Session: GREENSMM stores logged-in user as client_id ─────────────────
$sessionClientId = (int)($_SESSION['client_id'] ?? 0);

// ── Detect order IDs mentioned in message ─────────────────────────────────
preg_match_all('/\b(\d{3,8})\b/', $message, $idM);
$mentionedIds = array_unique($idM[1] ?? []);

// ── Detect username/email mention ─────────────────────────────────────────
$mentionedUsername = null;
if (preg_match('/(?:username|utilisateur|user|compte|email)[:\s]+([a-zA-Z0-9_.@\-]{3,40})/i', $message, $um)) {
    $mentionedUsername = trim($um[1]);
}

// ── Site settings ─────────────────────────────────────────────────────────
$siteName = 'SMM Panel'; $currency = 'USD'; $supportPhone = ''; $supportEmail = '';
try {
    $st = $db->query("SELECT name, value FROM settings WHERE name IN ('site_name','site_currency','admin_telephone','admin_mail') LIMIT 10");
    foreach ($st->fetchAll() as $r) {
        if ($r['name'] === 'site_name')        $siteName     = $r['value'];
        if ($r['name'] === 'site_currency')    $currency     = $r['value'];
        if ($r['name'] === 'admin_telephone')  $supportPhone = $r['value'];
        if ($r['name'] === 'admin_mail')       $supportEmail = $r['value'];
    }
} catch (Throwable $e) {}

// ── Context builder ───────────────────────────────────────────────────────
$ctx = '';
$lookupClientId = $sessionClientId;

// Resolve client from mentioned username/email
if (!$lookupClientId && $mentionedUsername) {
    try {
        $col = str_contains($mentionedUsername, '@') ? 'email' : 'username';
        $st  = $db->prepare("SELECT client_id, name FROM clients WHERE {$col} = ? LIMIT 1");
        $st->execute([$mentionedUsername]);
        $cl  = $st->fetch();
        if ($cl) {
            $lookupClientId = (int)$cl['client_id'];
            $ctx .= "Account found: {$cl['name']} (ID #{$lookupClientId}).\n";
        } else {
            $ctx .= "No account found for '{$mentionedUsername}'.\n";
        }
    } catch (Throwable $e) {}
}

// Logged-in user summary
if ($sessionClientId) {
    try {
        $st = $db->prepare("SELECT name, username, balance, spent FROM clients WHERE client_id = ? LIMIT 1");
        $st->execute([$sessionClientId]);
        $cl = $st->fetch();
        if ($cl) {
            $ctx .= "Logged-in: {$cl['name']} (@{$cl['username']}), balance: {$cl['balance']} {$currency}, total spent: {$cl['spent']} {$currency}.\n";
        }
    } catch (Throwable $e) {}
}

// Order lookup by IDs mentioned in message
foreach ($mentionedIds as $oid) {
    try {
        if ($lookupClientId) {
            $st = $db->prepare(
                "SELECT o.order_id, o.order_status, o.order_quantity, o.order_charge,
                        o.order_create, o.order_error, s.name AS service_name
                 FROM orders o
                 LEFT JOIN services s ON s.service_id = o.service_id
                 WHERE o.order_id = ? AND o.client_id = ? LIMIT 1"
            );
            $st->execute([$oid, $lookupClientId]);
        } else {
            $st = $db->prepare(
                "SELECT o.order_id, o.order_status, o.order_quantity, o.order_charge,
                        o.order_create, o.order_error, s.name AS service_name
                 FROM orders o
                 LEFT JOIN services s ON s.service_id = o.service_id
                 WHERE o.order_id = ? LIMIT 1"
            );
            $st->execute([$oid]);
        }
        $order = $st->fetch();
        if ($order) {
            $err = ($order['order_error'] && $order['order_status'] !== 'completed')
                   ? " | Note: " . mb_substr($order['order_error'], 0, 80) : '';
            $ctx .= "Order #{$order['order_id']}: service=\"{$order['service_name']}\","
                  . " qty={$order['order_quantity']}, charge={$order['order_charge']} {$currency},"
                  . " status={$order['order_status']}, placed={$order['order_create']}{$err}.\n";
        } else {
            $ctx .= "Order #{$oid}: not found" . ($lookupClientId ? " in this account" : "") . ".\n";
        }
    } catch (Throwable $e) {}
}

// Recent orders if user asks without specific ID and is logged in
$asksOrders = (bool)preg_match('/\b(order|commande|livr|statut|suivi|my order|mes commandes)\b/i', $message);
if ($asksOrders && $lookupClientId && empty($mentionedIds)) {
    try {
        $st = $db->prepare(
            "SELECT o.order_id, o.order_status, o.order_quantity, o.order_create, s.name AS service_name
             FROM orders o
             LEFT JOIN services s ON s.service_id = o.service_id
             WHERE o.client_id = ? ORDER BY o.order_create DESC LIMIT 5"
        );
        $st->execute([$lookupClientId]);
        $orders = $st->fetchAll();
        if ($orders) {
            $ctx .= "Recent orders:\n";
            foreach ($orders as $o) {
                $ctx .= "  #{$o['order_id']} — {$o['service_name']} — qty {$o['order_quantity']} — {$o['order_status']} ({$o['order_create']})\n";
            }
        }
    } catch (Throwable $e) {}
}

// Sample active services
try {
    $svcRows = $db->query(
        "SELECT name, category, rate FROM services WHERE status = 1 ORDER BY RAND() LIMIT 6"
    )->fetchAll();
    if ($svcRows) {
        $svcList = implode('; ', array_map(
            fn($s) => "{$s['name']} ({$s['category']}) {$s['rate']} {$currency}/1000",
            $svcRows
        ));
        $ctx .= "Sample services: {$svcList}.\n";
    }
} catch (Throwable $e) {}

// ── System prompt ─────────────────────────────────────────────────────────
$supportContact = implode(' | ', array_filter([
    $supportPhone ? "Tel/WhatsApp: {$supportPhone}" : '',
    $supportEmail ? "Email: {$supportEmail}" : '',
])) ?: 'via the support ticket page';

$sys = <<<SYS
You are the AI support assistant for **{$siteName}**, a Social Media Marketing (SMM) panel.

ABOUT THE BUSINESS:
- We sell social media services: followers, likes, views, comments, shares, subscribers, etc.
- Platforms: Instagram, TikTok, YouTube, Facebook, Twitter/X, Telegram, Snapchat, and more.
- To place an order: log in → New Order → choose service → enter your link and quantity → pay.
- Funds are added via Add Funds page. Payments are confirmed within minutes.

ORDER STATUSES (always explain honestly):
- pending / en attente: Received, not yet started
- inprogress / en cours: Being delivered right now
- completed / terminé: 100% delivered
- partial / partiel: Partially delivered — some may have dropped. Contact support for refill.
- processing / traitement: Being processed by our system
- canceled / annulé: Canceled — balance was refunded if you were charged

LIVE DATA FROM DATABASE (use this to answer — do NOT invent):
{$ctx}

CRITICAL RULES — NEVER BREAK:
1. ONLY report information present in LIVE DATA above. NEVER invent or guess order status, balance, or prices.
2. If the user asks about an order but provides NO order ID:
   → Ask: "Please share your **Order ID** (the number visible in your Orders page / votre page Commandes)."
3. If an order ID was given but not found in LIVE DATA:
   → Say: "I couldn't find Order #{ID} on your account. Please double-check the number in your Orders page."
4. If NO live data at all and user asks about specific account info:
   → Ask them to log in or provide their username.
5. NEVER reveal: provider names (API suppliers), database credentials, server details, API keys.
6. RESPOND IN THE SAME LANGUAGE as the user. If they write in French → answer in French. English → English.
7. Max 5 sentences or a short bullet list. Be concise and helpful.
8. For unresolved issues: direct to support — {$supportContact}
9. Identity: You are **{$siteName} AI**. Never mention DeepSeek, OpenAI, or any AI provider.
SYS;

// ── Build API messages ────────────────────────────────────────────────────
$messages = [['role' => 'system', 'content' => $sys]];
foreach (array_slice($history, -8) as $h) {
    if (in_array($h['role'] ?? '', ['user', 'assistant'])) {
        $messages[] = ['role' => $h['role'], 'content' => mb_substr($h['content'] ?? '', 0, 400)];
    }
}
$messages[] = ['role' => 'user', 'content' => $message];

// ── DeepSeek call ─────────────────────────────────────────────────────────
$ch = curl_init('https://api.deepseek.com/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 22,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . _GSMM_KEY,
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'model'       => 'deepseek-chat',
        'messages'    => $messages,
        'max_tokens'  => 450,
        'temperature' => 0.3,
        'stream'      => false,
    ]),
]);
$raw  = curl_exec($ch);
$cerr = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($cerr || $code !== 200) {
    $fb = strpos(strtolower($message), 'order') !== false || strpos($message, 'commande') !== false
        ? "Pour vérifier votre commande, merci de fournir votre **Order ID** (numéro dans votre page Commandes). / To check your order, please provide your **Order ID** (found in your Orders page)."
        : "Notre assistant est temporairement indisponible. Contactez-nous: {$supportContact} / Support temporarily unavailable. Contact: {$supportContact}";
    echo json_encode(['reply' => $fb]);
    exit;
}

$data  = json_decode($raw, true);
$reply = trim($data['choices'][0]['message']['content'] ?? '');
if (!$reply) {
    $reply = "Je n'ai pas pu obtenir une réponse. / Could not get a response. Contact: {$supportContact}";
}
echo json_encode(['reply' => $reply]);
