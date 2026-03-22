<?php
/**
 * POST /subscription/confirm
 * Admin anathibitisha malipo → subscription inawashwa
 *
 * Body: { admin_key, email, plan, payment_id? }
 */

requireMethod('POST');
requireAdmin();

$body      = getBody();
$email     = strtolower(trim($body['email']      ?? ''));
$plan      = trim($body['plan']                  ?? '');
$paymentId = trim($body['payment_id']            ?? '');

if (empty($email) || empty($plan)) {
    fail('email na plan vinahitajika');
}

$plans = PLANS;
if (!array_key_exists($plan, $plans)) {
    fail('Plan batili. Tumia: ' . implode(', ', array_keys($plans)));
}

$days    = $plans[$plan]['days'];
$now     = time();

// Angalia subscription iliyopo — extend badala ya kuunda mpya
$enc      = urlencode($email);
$existing = sb("/rest/v1/subscriptions?email=eq.{$enc}&select=id,end_date,is_active&limit=1");
$sub      = $existing['data'][0] ?? null;

// Hesabu tarehe ya mwisho
if ($sub && !empty($sub['end_date'])) {
    $currentEnd = strtotime($sub['end_date']);
    // Kama subscription bado inaendelea → extend kutoka mwisho wake
    $base    = $currentEnd > $now ? $currentEnd : $now;
    $endDate = date('Y-m-d\TH:i:s', $base + ($days * 86400));
} else {
    $endDate = date('Y-m-d\TH:i:s', $now + ($days * 86400));
}

$startDate = date('Y-m-d\TH:i:s', $now);

if ($sub) {
    // Update iliyopo
    $r = sb(
        "/rest/v1/subscriptions?email=eq.{$enc}",
        'PATCH',
        ['plan' => $plan, 'end_date' => $endDate, 'is_active' => true, 'updated_at' => $startDate],
        true
    );
} else {
    // Unda mpya
    $r = sb('/rest/v1/subscriptions', 'POST', [
        'email'      => $email,
        'plan'       => $plan,
        'end_date'   => $endDate,
        'is_active'  => true,
        'created_at' => $startDate,
        'updated_at' => $startDate,
    ], true);
}

// Mark payment confirmed kama payment_id imetolewa
if (!empty($paymentId)) {
    sb(
        "/rest/v1/payments?id=eq.{$paymentId}",
        'PATCH',
        ['status' => 'confirmed', 'confirmed_at' => $startDate],
        true
    );
}

if (!$r['ok']) {
    fail('Imeshindwa kuandika subscription: ' . json_encode($r['data']), 500);
}

ok([
    'message'   => "✅ Subscription ya {$email} imewashwa",
    'email'     => $email,
    'plan'      => $plan,
    'end_date'  => $endDate,
    'days_added'=> $days,
]);
