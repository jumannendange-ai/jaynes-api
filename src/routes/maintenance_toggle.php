<?php
/**
 * POST /maintenance/toggle
 * Admin anawasha au kuzima maintenance mode
 *
 * Body: { admin_key, state: true|false }
 */

requireMethod('POST');
requireAdmin();

$body  = getBody();
$state = isset($body['state']) ? (bool)$body['state'] : null;

if ($state === null) {
    fail('state inahitajika (true au false)');
}

$val = $state ? 'true' : 'false';
$now = date('Y-m-d\TH:i:s');

// Angalia kama setting ipo tayari
$existing = sb("/rest/v1/settings?key=eq.maintenance&select=id&limit=1");

if (!empty($existing['data'])) {
    sb("/rest/v1/settings?key=eq.maintenance", 'PATCH', ['value' => $val, 'updated_at' => $now], true);
} else {
    sb('/rest/v1/settings', 'POST', ['key' => 'maintenance', 'value' => $val, 'updated_at' => $now], true);
}

ok([
    'maintenance' => $state,
    'message'     => $state ? '🔧 Maintenance imewashwa' : '✅ Maintenance imezimwa',
]);
