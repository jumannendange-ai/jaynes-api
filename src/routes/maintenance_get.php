<?php
/**
 * GET /maintenance
 * Angalia kama app iko maintenance mode
 *
 * Response: { success, maintenance: bool }
 */

requireMethod('GET');

$r    = sb("/rest/v1/settings?key=eq.maintenance&select=value&limit=1");
$isOn = false;

if ($r['ok'] && !empty($r['data'])) {
    $val  = $r['data'][0]['value'] ?? 'false';
    $isOn = filter_var($val, FILTER_VALIDATE_BOOLEAN);
}

ok(['maintenance' => $isOn]);
