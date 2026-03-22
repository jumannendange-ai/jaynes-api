<?php
/**
 * GET /subscription/check?email=user@example.com
 * Angalia kama mtumiaji ana subscription inayoendelea
 *
 * Response:
 * { success, active, plan, end_date, days_left }
 */

requireMethod('GET');

$email = strtolower(getParam('email'));

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Email sahihi inahitajika');
}

$enc    = urlencode($email);
$result = sb(
    "/rest/v1/subscriptions?email=eq.{$enc}&select=id,email,plan,end_date,is_active&order=created_at.desc&limit=1"
);

// Kama hakuna subscription au Supabase imeshindwa
if (!$result['ok'] || empty($result['data'])) {
    ok([
        'active'    => false,
        'plan'      => 'free',
        'end_date'  => '',
        'days_left' => 0,
    ]);
}

$sub      = $result['data'][0];
$isActive = (bool)($sub['is_active'] ?? false);
$endDate  = $sub['end_date'] ?? '';
$plan     = $sub['plan'] ?? 'free';
$daysLeft = 0;

// Double-check tarehe — isipite
if ($isActive && !empty($endDate)) {
    $endTs    = strtotime($endDate);
    $isActive = $endTs > time();
    $daysLeft = $isActive ? (int)ceil(($endTs - time()) / 86400) : 0;
}

ok([
    'active'    => $isActive,
    'plan'      => $isActive ? $plan : 'free',
    'end_date'  => $endDate,
    'days_left' => $daysLeft,
]);
