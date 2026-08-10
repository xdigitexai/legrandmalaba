<?php
function calculate_price_kes($costUsd, $db) {
    static $rule = null;

    if ($rule === null) {
        $stmt = $db->query("SELECT usd_to_kes, markup_percent FROM pricing_rules WHERE id = 1");
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    if (!$rule) return 0;

    $base = $costUsd * $rule['usd_to_kes'];
    $final = $base * (1 + ($rule['markup_percent'] / 100));

    return round($final, 2);
}