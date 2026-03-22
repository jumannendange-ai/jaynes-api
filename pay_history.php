<?php
// ════════════════════════════════════════════════════════════
//  JAYNES MAX TV — pay_history.php
//  Inarudisha historia ya malipo ya mtumiaji
//  Inatumia service key (server-side) kukwepa RLS
//  Inarudisha: notes (ujumbe wa mteja) + admin_note (jibu la admin)
// ════════════════════════════════════════════════════════════

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }
if ($_SERVER['REQUEST_METHOD'] !== 'POST')    { echo json_encode(['success'=>false,'error'=>'Method not allowed']); exit; }

define('SB_URL',    'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SB_SVCKEY', 'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

$uid   = trim($data['user_id'] ?? '');
$email = trim($data['email']   ?? '');

if (!$uid && !$email) {
    echo json_encode(['success'=>false,'error'=>'user_id au email inahitajika']);
    exit;
}

// Jenga filter — tumia user_id kwanza, email kama backup
$fields = 'id,user_id,email,phone,package,amount,method,reference,notes,admin_note,status,days,created_at,updated_at';

if ($uid) {
    $filter = 'user_id=eq.' . urlencode($uid);
} else {
    $filter = 'email=eq.' . urlencode($email);
}

$url = SB_URL . '/rest/v1/payments?' . $filter
     . '&select=' . urlencode($fields)
     . '&order=created_at.desc&limit=20';

$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_HTTPHEADER     => [
        'apikey: '        . SB_SVCKEY,
        'Authorization: Bearer ' . SB_SVCKEY,
    ],
]);
$res  = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

if ($err) {
    echo json_encode(['success'=>false,'error'=>'Tatizo la mtandao: '.$err]);
    exit;
}

$rows = json_decode($res, true);

// Kama user_id haikupata matokeo — jaribu email
if ($uid && $email && (!is_array($rows) || empty($rows))) {
    $url2 = SB_URL . '/rest/v1/payments?email=eq.' . urlencode($email)
          . '&select=' . urlencode($fields)
          . '&order=created_at.desc&limit=20';

    $ch2 = curl_init($url2);
    curl_setopt_array($ch2, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_HTTPHEADER     => [
            'apikey: '        . SB_SVCKEY,
            'Authorization: Bearer ' . SB_SVCKEY,
        ],
    ]);
    $res2  = curl_exec($ch2);
    curl_close($ch2);
    $rows2 = json_decode($res2, true);
    if (is_array($rows2) && !empty($rows2)) $rows = $rows2;
}

if (!is_array($rows)) {
    echo json_encode(['success'=>false,'error'=>'Imeshindwa kupata historia','http'=>$code]);
    exit;
}

echo json_encode(['success'=>true, 'rows'=>$rows, 'count'=>count($rows)]);
