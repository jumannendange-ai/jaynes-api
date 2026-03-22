<?php
// ════════════════════════════════════════════════════════════
//  JAYNES MAX TV — pay_submit.php
//  Server-side proxy ya kutuma malipo kwenye Supabase
//  Inatumia service key (haionyeshwi kwa browser)
//  Inakwepa RLS ya Supabase kwa watu wote
// ════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// ── Supabase Config (SERVER SIDE — salama) ──────────────────
define('SB_URL',     'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SB_SVCKEY',  'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success'=>false,'error'=>'Method not allowed']);
    exit;
}

// ── APPROVE ACTION — server-side premium update ─────────────
$raw_early  = file_get_contents('php://input');
$data_early = json_decode($raw_early, true);

if (!empty($data_early['action']) && $data_early['action'] === 'approve_premium') {
    $payId   = trim($data_early['pay_id']   ?? '');
    $userId  = trim($data_early['user_id']  ?? '');
    $email   = trim($data_early['email']    ?? '');
    $days    = (int)($data_early['days']    ?? 30);
    $subEnd  = date('c', strtotime("+{$days} days"));
    $updated = false;
    $errors  = [];

    // Sasisha payments table — status = approved
    if ($payId) {
        $ch1 = curl_init(SB_URL . '/rest/v1/payments?id=eq.' . urlencode($payId));
        curl_setopt_array($ch1, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PATCH',
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => json_encode(['status'=>'approved','updated_at'=>date('c')]),
            CURLOPT_HTTPHEADER     => ['Content-Type: application/json','apikey: '.SB_SVCKEY,'Authorization: Bearer '.SB_SVCKEY],
        ]);
        curl_exec($ch1); curl_close($ch1);
    }

    // Sasisha profiles — jaribu user_id kwanza, kisha email
    $premiumPayload = json_encode(['plan'=>'premium','sub_end'=>$subEnd]);
    $headers = ['Content-Type: application/json','apikey: '.SB_SVCKEY,'Authorization: Bearer '.SB_SVCKEY,'Prefer: return=representation'];

    if ($userId) {
        $ch2 = curl_init(SB_URL . '/rest/v1/profiles?id=eq.' . urlencode($userId));
        curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'PATCH',CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_POSTFIELDS=>$premiumPayload,CURLOPT_HTTPHEADER=>$headers]);
        $r2 = curl_exec($ch2); curl_close($ch2);
        $d2 = json_decode($r2, true);
        if (is_array($d2) && count($d2) > 0) $updated = true;
    }

    if (!$updated && $email) {
        // Tafuta id kwa email
        $ch3 = curl_init(SB_URL . '/rest/v1/profiles?email=eq.' . urlencode($email) . '&select=id');
        curl_setopt_array($ch3, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>['apikey: '.SB_SVCKEY,'Authorization: Bearer '.SB_SVCKEY]]);
        $r3 = json_decode(curl_exec($ch3), true); curl_close($ch3);
        $uid3 = $r3[0]['id'] ?? '';
        if ($uid3) {
            $ch4 = curl_init(SB_URL . '/rest/v1/profiles?id=eq.' . urlencode($uid3));
            curl_setopt_array($ch4, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'PATCH',CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_POSTFIELDS=>$premiumPayload,CURLOPT_HTTPHEADER=>$headers]);
            $r4 = curl_exec($ch4); curl_close($ch4);
            $d4 = json_decode($r4, true);
            if (is_array($d4) && count($d4) > 0) $updated = true;
        }
    }

    echo json_encode(['success'=>true,'premium_updated'=>$updated,'sub_end'=>$subEnd]);
    exit;
}

// ── SET PLAN — admin anaweza kuweka premium/trial/free ──────
if (!empty($data_early['action']) && $data_early['action'] === 'set_plan') {
    $userId   = trim($data_early['user_id']   ?? '');
    $email    = trim($data_early['email']      ?? '');
    $plan     = trim($data_early['plan']       ?? 'free');
    $subEnd   = $data_early['sub_end']   ?? null;
    $trialEnd = $data_early['trial_end'] ?? null;

    if ($plan === 'free') {
        $payload = ['plan'=>'free', 'sub_end'=>null, 'trial_end'=>null];
    } else {
        $payload = ['plan'=>$plan];
        if ($subEnd)   $payload['sub_end']   = $subEnd;
        if ($trialEnd) $payload['trial_end'] = $trialEnd;
    }

    $headers = ['Content-Type: application/json','apikey: '.SB_SVCKEY,'Authorization: Bearer '.SB_SVCKEY,'Prefer: return=representation'];
    $updated = false;

    if ($userId) {
        $ch = curl_init(SB_URL . '/rest/v1/profiles?id=eq.' . urlencode($userId));
        curl_setopt_array($ch, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'PATCH',CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>$headers]);
        $r = json_decode(curl_exec($ch), true); curl_close($ch);
        if (is_array($r) && count($r) > 0) $updated = true;
    }

    if (!$updated && $email) {
        $ch2 = curl_init(SB_URL . '/rest/v1/profiles?email=eq.' . urlencode($email) . '&select=id');
        curl_setopt_array($ch2, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_HTTPHEADER=>['apikey: '.SB_SVCKEY,'Authorization: Bearer '.SB_SVCKEY]]);
        $r2 = json_decode(curl_exec($ch2), true); curl_close($ch2);
        $uid2 = $r2[0]['id'] ?? '';
        if ($uid2) {
            $ch3 = curl_init(SB_URL . '/rest/v1/profiles?id=eq.' . urlencode($uid2));
            curl_setopt_array($ch3, [CURLOPT_RETURNTRANSFER=>true,CURLOPT_CUSTOMREQUEST=>'PATCH',CURLOPT_TIMEOUT=>10,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_POSTFIELDS=>json_encode($payload),CURLOPT_HTTPHEADER=>$headers]);
            $r3 = json_decode(curl_exec($ch3), true); curl_close($ch3);
            if (is_array($r3) && count($r3) > 0) $updated = true;
        }
    }

    echo json_encode(['success'=>true,'updated'=>$updated,'plan'=>$plan]);
    exit;
}


// ── Soma na validate input ──────────────────────────────────
// MUHIMU: Tumia $raw_early — php://input inaweza kusomwa mara moja tu
$raw  = $raw_early; // Tayari imesomwa juu
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['success'=>false,'error'=>'Invalid JSON']);
    exit;
}

// Validate fields zinazohitajika
$required = ['phone','reference','package','method','amount','days'];
foreach ($required as $field) {
    if (empty($data[$field])) {
        echo json_encode(['success'=>false,'error'=>"Thamani ya '$field' haipo"]);
        exit;
    }
}

// Sanitize
$payload = [
    'user_id'    => $data['user_id']    ?? null,
    'email'      => substr(trim($data['email']??''), 0, 200),
    'amount'     => (int) preg_replace('/[^0-9]/','',$data['amount']),
    'method'     => substr(trim($data['method']),  0, 20),
    'phone'      => substr(trim($data['phone']),   0, 20),
    'package'    => substr(trim($data['package']), 0, 100),
    'days'       => (int) $data['days'],
    'reference'  => strtoupper(substr(trim($data['reference']), 0, 100)),
    'notes'      => substr(trim($data['notes']??''), 0, 500) ?: null,
    'status'     => 'pending',
    'created_at' => date('c'),
];

// Hakikisha amount na days ni nzuri
if ($payload['amount'] < 100) {
    echo json_encode(['success'=>false,'error'=>'Kiasi hakiko sahihi']);
    exit;
}

// ── Tuma kwenye Supabase kwa service key ────────────────────
$ch = curl_init(SB_URL . '/rest/v1/payments');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 15,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POSTFIELDS     => json_encode($payload),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'apikey: '        . SB_SVCKEY,
        'Authorization: Bearer ' . SB_SVCKEY,
        'Prefer: return=representation',
    ],
]);

$response   = curl_exec($ch);
$httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError  = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(['success'=>false,'error'=>'Tatizo la mtandao: '.$curlError]);
    exit;
}

$result = json_decode($response, true);

if ($httpCode === 200 || $httpCode === 201) {
    echo json_encode([
        'success' => true,
        'message' => 'Ombi limetumwa! Admin atakithibitisha hivi karibuni.',
        'id'      => $result[0]['id'] ?? null,
    ]);
} else {
    $errMsg = 'Tatizo. Jaribu tena.';
    if (!empty($result['message'])) $errMsg = $result['message'];
    elseif (!empty($result['error'])) $errMsg = $result['error'];
    echo json_encode(['success'=>false,'error'=>$errMsg,'http'=>$httpCode]);
}
