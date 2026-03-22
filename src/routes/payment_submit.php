<?php
/**
 * POST /payment/submit
 * Mtumiaji anawasilisha malipo — inahifadhiwa Supabase kama "pending"
 *
 * Body: { email, plan, method, reference? }
 *
 * Response:
 * { success, message, payment_id, amount, plan, instructions }
 */

requireMethod('POST');

$body   = getBody();
$email  = strtolower(trim($body['email']  ?? ''));
$plan   = trim($body['plan']              ?? '');
$method = trim($body['method']            ?? '');
$ref    = trim($body['reference']         ?? '');

// Validate
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    fail('Email sahihi inahitajika');
}
if (empty($plan) || !array_key_exists($plan, PLANS)) {
    fail('Plan batili. Tumia: ' . implode(', ', array_keys(PLANS)));
}
if (empty($method) || !array_key_exists($method, PAY_METHODS)) {
    fail('Njia ya malipo batili. Tumia: ' . implode(', ', array_keys(PAY_METHODS)));
}

$planInfo   = PLANS[$plan];
$methodInfo = PAY_METHODS[$method];
$amount     = $planInfo['amount'];
$now        = date('Y-m-d\TH:i:s');

// Hifadhi kwenye Supabase
$r = sb('/rest/v1/payments', 'POST', [
    'email'      => $email,
    'plan'       => $plan,
    'method'     => $method,
    'amount'     => $amount,
    'reference'  => $ref,
    'status'     => 'pending',
    'created_at' => $now,
], true);

$paymentId = $r['data'][0]['id'] ?? null;

// Log (kwa admin)
error_log("[JAYNES PAYMENT] email={$email} plan={$plan} method={$method} amount={$amount} ref={$ref}");

ok([
    'message'    => 'Malipo yamepokelewa. Tutaithibitisha hivi karibuni.',
    'payment_id' => $paymentId,
    'plan'       => $plan,
    'plan_label' => $planInfo['label'],
    'amount'     => $amount,
    'method'     => $methodInfo['name'],
    'instructions' => [
        'step1' => "Fungua {$methodInfo['name']} kwenye simu yako",
        'step2' => "Tuma TSh " . number_format($amount) . " kwa nambari: {$methodInfo['number']}",
        'step3' => "Weka maelezo: \"{$email} {$planInfo['label']}\"",
        'step4' => "Piga picha ya risiti kisha wasiliana nasi",
    ],
    'contact' => [
        'whatsapp' => 'https://wa.me/255616393956',
        'email'    => 'jaynestvmax@gmail.com',
    ],
]);
