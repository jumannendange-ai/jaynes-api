<?php
// proxy.php — Admin Data Proxy
// Inafanya Supabase requests kutoka server (PHP) badala ya browser
// Hii inashughulikia tatizo la sb_secret key kwenye browser

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, PATCH, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

define('SB_URL', 'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SB_KEY', 'sb_publishable_d8mzJ3iulCU7YdlV_lrdQw_32pOzDXc');
define('SB_SVC', 'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');

// Simple admin check
$adminEmails = ['swajayfour@gmail.com'];
$reqAdmin = $_GET['admin'] ?? '';
if (!in_array(strtolower($reqAdmin), $adminEmails)) {
    http_response_code(403);
    echo json_encode(['error' => 'Ruhusa imekataliwa']);
    exit;
}

$action = $_GET['action'] ?? '';

function sbRequest($path, $method = 'GET', $body = null) {
    $ch = curl_init(SB_URL . $path);
    $headers = [
        'Content-Type: application/json',
        'apikey: ' . SB_SVC,          // Service key — inakwepa RLS
        'Authorization: Bearer ' . SB_SVC,
        'Prefer: return=representation',
    ];
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    if ($body) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);
    if ($err) return ['error' => $err, 'code' => 0];
    return ['data' => json_decode($res, true), 'code' => $code];
}

switch ($action) {

    case 'stats':
        $users = sbRequest('/rest/v1/profiles?select=id,plan,sub_end,trial_end,created_at&order=created_at.desc&limit=500');
        $pays  = sbRequest('/rest/v1/payments?select=id,status,amount,email,phone,package,days,reference,notes,admin_note,method,created_at,updated_at&order=created_at.desc&limit=300');
        echo json_encode([
            'users'    => $users['data'] ?? [],
            'payments' => $pays['data'] ?? [],
            'ok'       => true,
        ]);
        break;

    case 'users':
        $r = sbRequest('/rest/v1/profiles?order=created_at.desc&limit=500');
        echo json_encode($r['data'] ?? []);
        break;

    case 'payments':
        $r = sbRequest('/rest/v1/payments?order=created_at.desc&limit=300');
        echo json_encode($r['data'] ?? []);
        break;

    case 'extend':
        $uid  = $_GET['uid']  ?? '';
        $days = (int)($_GET['days'] ?? 30);
        $sub  = $_GET['sub']  ?? '';
        if (!$uid) { echo json_encode(['error'=>'uid inahitajika']); exit; }

        $base = ($sub && strtotime($sub) > time()) ? new DateTime($sub) : new DateTime();
        $base->modify("+{$days} days");
        $newEnd = $base->format('Y-m-d\TH:i:s\Z');

        $r = sbRequest("/rest/v1/profiles?id=eq.{$uid}", 'PATCH', ['plan'=>'premium','sub_end'=>$newEnd]);
        echo json_encode(['ok' => $r['code'] >= 200 && $r['code'] < 300, 'sub_end' => $newEnd]);
        break;

    case 'revoke':
        $uid = $_GET['uid'] ?? '';
        if (!$uid) { echo json_encode(['error'=>'uid inahitajika']); exit; }
        $r = sbRequest("/rest/v1/profiles?id=eq.{$uid}", 'PATCH', ['plan'=>'free','sub_end'=>null]);
        echo json_encode(['ok' => $r['code'] >= 200 && $r['code'] < 300]);
        break;

    case 'approve':
        $pid   = $_GET['pid']   ?? '';
        $email = $_GET['email'] ?? '';
        $days  = (int)($_GET['days'] ?? 30);
        $note  = $_GET['note']  ?? '';
        if (!$pid) { echo json_encode(['error'=>'pid inahitajika']); exit; }

        // Sasisha payment status
        $body = ['status'=>'approved','admin_note'=>$note?:null,'updated_at'=>date('Y-m-d\TH:i:s\Z')];
        sbRequest("/rest/v1/payments?id=eq.{$pid}", 'PATCH', $body);

        // Washa subscription
        if ($email) {
            $ur = sbRequest('/rest/v1/profiles?email=eq.'.urlencode($email).'&select=id,sub_end');
            $user = $ur['data'][0] ?? null;
            if ($user) {
                $base = ($user['sub_end'] && strtotime($user['sub_end']) > time()) ? new DateTime($user['sub_end']) : new DateTime();
                $base->modify("+{$days} days");
                sbRequest("/rest/v1/profiles?id=eq.{$user['id']}", 'PATCH', ['plan'=>'premium','sub_end'=>$base->format('Y-m-d\TH:i:s\Z')]);
            }
        }
        echo json_encode(['ok' => true]);
        break;

    case 'reject':
        $pid  = $_GET['pid']  ?? '';
        $note = $_GET['note'] ?? '';
        if (!$pid) { echo json_encode(['error'=>'pid inahitajika']); exit; }
        $r = sbRequest("/rest/v1/payments?id=eq.{$pid}", 'PATCH', ['status'=>'rejected','admin_note'=>$note?:null,'updated_at'=>date('Y-m-d\TH:i:s\Z')]);
        echo json_encode(['ok' => $r['code'] >= 200 && $r['code'] < 300]);
        break;

    default:
        echo json_encode(['error' => 'Action haijulikani: '.$action]);
}
