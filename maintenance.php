<?php
/**
 * maintenance.php — Maintenance Mode & Trial Check API
 * JAYNES MAX TV
 *
 * GET ?action=status           → Angalia maintenance mode (public)
 * GET ?action=trial&uid=XXX    → Angalia trial status ya mtumiaji
 * GET ?action=check&uid=XXX    → Full check: maintenance + trial + premium
 */

// Hakuna require_once hapa kwa sababu inaweza kuitwa kabla ya config
// Tumia constants moja kwa moja

define('SB_URL_M', 'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SB_KEY_M', 'sb_publishable_d8mzJ3iulCU7YdlV_lrdQw_32pOzDXc');
define('SB_SVC_M', 'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');
define('TRIAL_DAYS', 3); // Siku za trial kwa watumiaji wapya

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

$action = $_GET['action'] ?? 'status';
$uid    = trim($_GET['uid'] ?? '');
$email  = trim($_GET['email'] ?? '');

// ── HELPER ───────────────────────────────────────────────────────────
function sbGet_M($path, $useService = false) {
    $key = $useService ? SB_SVC_M : SB_KEY_M;
    $ch  = curl_init(SB_URL_M . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . SB_KEY_M,
            'Authorization: Bearer ' . $key,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if (!$res) return null;
    return json_decode($res, true);
}

function sbPatch_M($path, $body) {
    $ch = curl_init(SB_URL_M . $path);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => 'PATCH',
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . SB_KEY_M,
            'Authorization: Bearer ' . SB_SVC_M,
            'Prefer: return=minimal',
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 10,
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300;
}

// ── ACTIONS ───────────────────────────────────────────────────────────

switch($action) {

    // ── 1. STATUS: Angalia maintenance mode ───────────────────────────
    case 'status':
        $data = sbGet_M('/rest/v1/settings?key=eq.maintenance_mode&select=value&limit=1');
        $isOn = false;
        if (is_array($data) && !empty($data)) {
            $val  = $data[0]['value'] ?? 'false';
            $isOn = ($val === 'true' || $val === '1');
        }

        // Pia angalia auto_approve
        $dataAA = sbGet_M('/rest/v1/settings?key=eq.auto_approve&select=value&limit=1');
        $autoApprove = false;
        if (is_array($dataAA) && !empty($dataAA)) {
            $v = $dataAA[0]['value'] ?? 'false';
            $autoApprove = ($v === 'true' || $v === '1');
        }

        echo json_encode([
            'ok'           => true,
            'maintenance'  => $isOn,
            'auto_approve' => $autoApprove,
            'message'      => $isOn ? 'App imefungwa kwa muda. Tutarudi hivi karibuni! 🔧' : 'App inafanya kazi kawaida',
        ]);
        break;

    // ── 2. TRIAL: Angalia trial ya mtumiaji ───────────────────────────
    case 'trial':
        if (!$uid && !$email) {
            echo json_encode(['ok'=>false,'error'=>'uid au email inahitajika']); exit;
        }

        $filter = $uid ? "id=eq.$uid" : "email=eq." . urlencode($email);
        $data   = sbGet_M("/rest/v1/profiles?$filter&select=id,email,plan,trial_end,sub_end,created_at&limit=1", true);

        if (!is_array($data) || empty($data)) {
            echo json_encode(['ok'=>false,'error'=>'Mtumiaji hajapatikana']); exit;
        }

        $user      = $data[0];
        $now       = time();
        $plan      = $user['plan'] ?? 'free';
        $trialEnd  = $user['trial_end'] ? strtotime($user['trial_end']) : 0;
        $subEnd    = $user['sub_end']   ? strtotime($user['sub_end'])   : 0;
        $createdAt = $user['created_at']? strtotime($user['created_at']): 0;

        // Hesabu siku zilizobaki
        $trialDaysLeft  = $trialEnd > $now  ? ceil(($trialEnd - $now) / 86400)  : 0;
        $subDaysLeft    = $subEnd   > $now  ? ceil(($subEnd   - $now) / 86400)  : 0;

        // Angalia kama trial bado ipo
        $trialActive   = ($plan === 'trial') && ($trialEnd > $now);
        $premiumActive = ($plan === 'premium') && ($subEnd > $now);
        $hasAccess     = $trialActive || $premiumActive;

        // Kama trial imeisha, badilisha plan kuwa 'free' automatically
        if ($plan === 'trial' && $trialEnd > 0 && $trialEnd <= $now) {
            sbPatch_M("/rest/v1/profiles?id=eq.{$user['id']}", [
                'plan'       => 'free',
                'updated_at' => date('Y-m-d\TH:i:s\Z'),
            ]);
            $plan = 'free';
        }

        echo json_encode([
            'ok'             => true,
            'uid'            => $user['id'],
            'email'          => $user['email'],
            'plan'           => $plan,
            'has_access'     => $hasAccess,
            'trial_active'   => $trialActive,
            'trial_end'      => $user['trial_end'],
            'trial_days_left'=> (int)$trialDaysLeft,
            'premium_active' => $premiumActive,
            'sub_end'        => $user['sub_end'],
            'sub_days_left'  => (int)$subDaysLeft,
            'trial_total_days'=> TRIAL_DAYS,
            'message'        => $trialActive
                ? "Trial ina siku $trialDaysLeft zilizobaki 🟢"
                : ($premiumActive
                    ? "Premium — siku $subDaysLeft zilizobaki 👑"
                    : "Trial imekwisha. Tafadhali lipa kuendelea. 🔒"),
        ]);
        break;

    // ── 3. CHECK: Full check — maintenance + access ───────────────────
    case 'check':
        // Angalia maintenance kwanza
        $settingsData = sbGet_M('/rest/v1/settings?key=in.(maintenance_mode,auto_approve)&select=key,value');
        $settings = [];
        if (is_array($settingsData)) {
            foreach ($settingsData as $s) $settings[$s['key']] = $s['value'];
        }
        $maintenance = ($settings['maintenance_mode'] ?? 'false') === 'true';
        $autoApprove = ($settings['auto_approve']     ?? 'false') === 'true';

        if ($maintenance) {
            echo json_encode([
                'ok'          => true,
                'maintenance' => true,
                'has_access'  => false,
                'plan'        => 'blocked',
                'message'     => 'App imefungwa kwa matengenezo. Tutarudi hivi karibuni! 🔧',
            ]);
            exit;
        }

        // Angalia user access
        if (!$uid && !$email) {
            echo json_encode([
                'ok'          => true,
                'maintenance' => false,
                'has_access'  => false,
                'plan'        => 'guest',
                'message'     => 'Tafadhali ingia kwenye akaunti yako.',
            ]);
            exit;
        }

        $filter = $uid ? "id=eq.$uid" : "email=eq." . urlencode($email);
        $userData = sbGet_M("/rest/v1/profiles?$filter&select=id,email,plan,trial_end,sub_end&limit=1", true);

        if (!is_array($userData) || empty($userData)) {
            echo json_encode([
                'ok'          => true,
                'maintenance' => false,
                'has_access'  => false,
                'plan'        => 'unknown',
                'message'     => 'Akaunti haijapatikana.',
            ]);
            exit;
        }

        $u          = $userData[0];
        $now        = time();
        $plan       = $u['plan'] ?? 'free';
        $trialEnd   = $u['trial_end'] ? strtotime($u['trial_end']) : 0;
        $subEnd     = $u['sub_end']   ? strtotime($u['sub_end'])   : 0;
        $trialOk    = ($plan === 'trial') && ($trialEnd > $now);
        $premiumOk  = ($plan === 'premium') && ($subEnd > $now);
        $hasAccess  = $trialOk || $premiumOk;

        $daysLeft = 0;
        if ($trialOk)   $daysLeft = (int)ceil(($trialEnd - $now) / 86400);
        if ($premiumOk) $daysLeft = (int)ceil(($subEnd   - $now) / 86400);

        // Auto-expire trial
        if ($plan === 'trial' && $trialEnd > 0 && $trialEnd <= $now) {
            sbPatch_M("/rest/v1/profiles?id=eq.{$u['id']}", [
                'plan' => 'free', 'updated_at' => date('Y-m-d\TH:i:s\Z')
            ]);
            $plan      = 'free';
            $hasAccess = false;
        }

        echo json_encode([
            'ok'           => true,
            'maintenance'  => false,
            'auto_approve' => $autoApprove,
            'has_access'   => $hasAccess,
            'plan'         => $plan,
            'days_left'    => $daysLeft,
            'trial_active' => $trialOk,
            'premium_active'=> $premiumOk,
            'trial_end'    => $u['trial_end'],
            'sub_end'      => $u['sub_end'],
            'message'      => $hasAccess
                ? ($trialOk ? "Trial — siku $daysLeft zilizobaki 🟢" : "Premium — siku $daysLeft zilizobaki 👑")
                : "Huduma imekwisha. Lipa kuendelea kutazama. 🔒",
        ]);
        break;

    default:
        echo json_encode(['ok'=>false,'error'=>'Action haijulikani: '.$action]);
}
