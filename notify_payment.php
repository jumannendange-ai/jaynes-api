<?php
// ════════════════════════════════════════════════════════════
//  JAYNES MAX TV — notify_payment.php
//  Inatuma notification kwa mtumiaji baada ya malipo
//  kuthibitishwa au kukataliwa na admin.
//
//  Inafanya mambo mawili:
//  1. Inatuma OneSignal push notification kwa mtumiaji
//  2. Inahifadhi message kwenye Supabase (notifications table)
//     ili mtumiaji aione kwenye account.php pia
// ════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }

// ── CONFIG ──────────────────────────────────────────────────
define('SB_URL',     'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SB_SVCKEY',  'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');
define('OS_APP_ID',  '10360777-3ada-4145-b83f-00eb0312a53f');
define('OS_REST',    'os_v2_app_ca3ao5z23jaulob7advqgevfh4qctlprzdauupekggukcgwmz5glfzdu6lkvnkzjeuno3cuuqow7fklo3fehp2puu52sr7sroo63hwy');
define('APP_URL',    'https://dde.ct.ws');

// ── INPUT ───────────────────────────────────────────────────
$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) { echo json_encode(['success'=>false,'error'=>'Invalid JSON']); exit; }

$action  = $data['action']  ?? '';   // 'approve' | 'reject'
$email   = trim($data['email']  ?? '');
$user_id = trim($data['user_id'] ?? '');
$package = trim($data['package'] ?? '');
$amount  = (int)($data['amount'] ?? 0);
$days    = (int)($data['days']   ?? 0);
$note    = trim($data['note']    ?? '');

if (!in_array($action, ['approve','reject'])) {
    echo json_encode(['success'=>false,'error'=>'Action si sahihi']); exit;
}
if (!$email && !$user_id) {
    echo json_encode(['success'=>false,'error'=>'Email au user_id inahitajika']); exit;
}

// ── TENGENEZA UJUMBE ────────────────────────────────────────
if ($action === 'approve') {
    $title   = '✅ Malipo Yamekubaliwa!';
    $body    = "Hongera! 🎉 Malipo yako ya TSh " . number_format($amount) .
               " kwa package \"$package\" yamekubaliwa.\n" .
               "Subscription yako imeanzishwa kwa siku $days.\n" .
               ($note ? "📝 $note\n" : '') .
               "Furahia JAYNES MAX TV! 📺";
    $icon    = '✅';
    $type    = 'payment_approved';
    $color   = '#00ff88';
} else {
    $title   = '❌ Malipo Yamekataliwa';
    $body    = "Samahani, malipo yako ya TSh " . number_format($amount) .
               " kwa package \"$package\" yamekataliwa.\n" .
               ($note ? "📝 Sababu: $note\n" : '') .
               "Tuma tena au wasiliana nasi kwa msaada.";
    $icon    = '❌';
    $type    = 'payment_rejected';
    $color   = '#ff4466';
}

$results = ['onesignal'=>false, 'supabase'=>false, 'errors'=>[]];

// ══════════════════════════════════════════════════════════════
//  1. ONESIGNAL — Tuma push notification kwa mtumiaji husika
//     Inatumia "filter" ya email (external_user_id au tag)
// ══════════════════════════════════════════════════════════════
function sendOneSignal(string $email, string $userId, string $title, string $body, string $url): bool {
    // Jaribu njia mbili: kwa email tag NA kwa external_user_id
    $targets = [];

    // Njia 1: kwa email tag (ikiwa mtumiaji alihifadhi email tag)
    if ($email) {
        $targets[] = [
            'filters' => [
                ['field'=>'tag', 'key'=>'email', 'relation'=>'=', 'value'=>$email]
            ]
        ];
    }

    // Njia 2: kwa external_user_id (user_id wa Supabase)
    if ($userId) {
        $targets[] = [
            'include_aliases' => ['external_id' => [$userId]],
            'target_channel'  => 'push',
        ];
    }

    $sent = false;
    foreach ($targets as $target) {
        $payload = array_merge($target, [
            'app_id'                => OS_APP_ID,
            'headings'              => ['en'=>$title, 'sw'=>$title],
            'contents'              => ['en'=>$body,  'sw'=>$body],
            'url'                   => $url,
            'small_icon'            => 'ic_launcher',
            'android_accent_color'  => 'FF00D4FF',
            'priority'              => 10,
            'ttl'                   => 86400,
        ]);

        $ch = curl_init('https://onesignal.com/api/v1/notifications');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Key ' . OS_REST,
                'Content-Type: application/json',
            ],
        ]);
        $res = curl_exec($ch);
        curl_close($ch);

        $r = json_decode($res ?? '{}', true);
        if (!empty($r['id'])) { $sent = true; break; }
    }

    // Fallback: tuma kwa WOTE (ikiwa hakuna target iliyofanikiwa)
    // Maoni: futa fallback hii kama hutaki wote wapate notification
    if (!$sent) {
        $fallback = [
            'app_id'               => OS_APP_ID,
            'included_segments'    => ['All'],
            'headings'             => ['en'=>$title, 'sw'=>$title],
            'contents'             => ['en'=>$body,  'sw'=>$body],
            'url'                  => $url,
            'small_icon'           => 'ic_launcher',
            'android_accent_color' => 'FF00D4FF',
            'priority'             => 10,
            'ttl'                  => 86400,
        ];
        $ch = curl_init('https://onesignal.com/api/v1/notifications');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_TIMEOUT        => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_POSTFIELDS     => json_encode($fallback),
            CURLOPT_HTTPHEADER     => [
                'Authorization: Key ' . OS_REST,
                'Content-Type: application/json',
            ],
        ]);
        $res2 = curl_exec($ch);
        curl_close($ch);
        $r2 = json_decode($res2 ?? '{}', true);
        if (!empty($r2['id'])) $sent = true;
    }

    return $sent;
}

$notifUrl = APP_URL . '/malipo.php';
$osSent   = sendOneSignal($email, $user_id, $title, $body, $notifUrl);
$results['onesignal'] = $osSent;
if (!$osSent) $results['errors'][] = 'OneSignal: notification haikufika';

// ══════════════════════════════════════════════════════════════
//  2. SUPABASE — Hifadhi notification kwenye notifications table
//     Mtumiaji ataiona kwenye account.php (inbox yake)
// ══════════════════════════════════════════════════════════════
$notifRecord = [
    'user_id'    => $user_id ?: null,
    'email'      => $email   ?: null,
    'type'       => $type,
    'title'      => $title,
    'body'       => $body,
    'is_read'    => false,
    'created_at' => date('c'),
    'meta'       => json_encode([
        'package' => $package,
        'amount'  => $amount,
        'days'    => $days,
        'action'  => $action,
    ]),
];

$ch = curl_init(SB_URL . '/rest/v1/notifications');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_POSTFIELDS     => json_encode($notifRecord),
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'apikey: '        . SB_SVCKEY,
        'Authorization: Bearer ' . SB_SVCKEY,
        'Prefer: return=minimal',
    ],
]);
$sbRes  = curl_exec($ch);
$sbCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($sbCode === 200 || $sbCode === 201) {
    $results['supabase'] = true;
} else {
    $sbData = json_decode($sbRes ?? '{}', true);
    $sbErr  = $sbData['message'] ?? $sbData['error'] ?? "HTTP $sbCode";
    // Ikiwa table haipo bado — ignore (notification bado imetumwa kwa OneSignal)
    if ($sbCode === 404 || str_contains($sbErr ?? '', 'relation') || str_contains($sbErr ?? '', 'does not exist')) {
        $results['supabase']        = false;
        $results['supabase_note']   = 'notifications table haipo — unda kwenye Supabase';
        $results['supabase_sql']    = 'CREATE TABLE notifications (id uuid DEFAULT gen_random_uuid() PRIMARY KEY, user_id uuid REFERENCES auth.users(id), email text, type text, title text, body text, is_read boolean DEFAULT false, meta jsonb, created_at timestamptz DEFAULT now());';
    } else {
        $results['errors'][] = "Supabase: $sbErr";
    }
}

// ── JIBU ────────────────────────────────────────────────────
$success = $results['onesignal'] || $results['supabase'];
echo json_encode(array_merge(['success'=>$success], $results), JSON_UNESCAPED_UNICODE);
