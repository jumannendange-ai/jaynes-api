<?php
/**
 * match_widget_api.php — JAYNES MAX TV
 * API ya match widget — inatumia AllSportsAPI (tayari kwenye schedule.php)
 * GET /match_widget_api.php → JSON {ok, live:[], sched:[]}
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

define('APIKEY', '6095087686b995f2554af89bac81bb2b11d4182bdc4ff16c603d9130479f2736');
define('BASE',   'https://apiv2.allsportsapi.com/football/');

// Ligi unazoitaka
$LEAGUE_IDS = [551, 152, 302, 175, 354, 480, 346, 390, 29];

$today     = date('Y-m-d');
$tomorrow  = date('Y-m-d', strtotime('+1 day'));

// ── Cache helper (dakika 1 kwa live, dakika 5 kwa sched) ──────────────
function cached(string $key, callable $fn, int $ttl): array {
    $file = sys_get_temp_dir() . '/jmt_widget_' . md5($key) . '.json';
    if (file_exists($file) && (time() - filemtime($file)) < $ttl) {
        $d = json_decode(file_get_contents($file), true);
        if (is_array($d)) return $d;
    }
    $data = $fn();
    file_put_contents($file, json_encode($data));
    return $data;
}

// ── API call ──────────────────────────────────────────────────────────
function api(array $p): array {
    $p['APIkey'] = APIKEY;
    $url = BASE . '?' . http_build_query($p);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0',
    ]);
    $r = curl_exec($ch);
    curl_close($ch);
    $d = json_decode($r ?: '{}', true);
    return $d['result'] ?? [];
}

// ── Format match ──────────────────────────────────────────────────────
function fmt(array $m, string $leagueMap): array {
    $hs  = $m['event_home_final_result'] ?? $m['event_halftime_result'] ?? '';
    $as  = '';
    if ($hs !== '' && str_contains($hs, ' - ')) {
        [$hs, $as] = explode(' - ', $hs, 2);
    } elseif (isset($m['event_away_final_result'])) {
        $as = $m['event_away_final_result'];
    }

    $status = 'sched';
    $status_raw = strtolower($m['event_status'] ?? '');
    if ($status_raw === 'finished' || $status_raw === 'ft') {
        $status = 'ft';
    } elseif ($status_raw !== '' && $status_raw !== 'not started' && !in_array($status_raw, ['', 'ns'])) {
        $status = 'live';
    }

    return [
        'id'         => $m['event_key'] ?? '',
        'league'     => $leagueMap,
        'home'       => $m['event_home_team']      ?? '—',
        'away'       => $m['event_away_team']      ?? '—',
        'home_score' => $hs,
        'away_score' => $as,
        'minute'     => $status_raw !== 'finished' ? ($m['event_status'] ?? '') : '',
        'time'       => $m['event_time']            ?? '',
        'date'       => $m['event_date']            ?? '',
        'status'     => $status,
    ];
}

// ── League names ──────────────────────────────────────────────────────
$LEAGUE_NAMES = [
    551 => '🇹🇿 NBC Premier',
    152 => '🏴󠁧󠁢󠁥󠁮󠁧󠁿 Premier League',
    302 => '🇪🇸 La Liga',
    175 => '🇩🇪 Bundesliga',
    354 => '⭐ Champions League',
    480 => '🟠 Europa League',
    346 => '🏆 CAF CL',
    390 => '🌿 CAF CC',
    29  => '🌍 AFCON',
];

// ── Fetch live ────────────────────────────────────────────────────────
$live = cached('live_' . date('YmdHi'), function () use ($LEAGUE_IDS, $LEAGUE_NAMES) {
    $raw = api(['action' => 'get_livescore', 'timezone' => 'Africa/Dar_es_Salaam']);
    if (!is_array($raw)) return [];
    $out = [];
    foreach ($raw as $m) {
        $lid = (int)($m['league_id'] ?? 0);
        if (in_array($lid, $LEAGUE_IDS)) {
            $out[] = fmt($m, $LEAGUE_NAMES[$lid] ?? 'Football');
        }
    }
    return $out;
}, 60);

// ── Fetch today schedule (non-live) ───────────────────────────────────
$sched = cached('sched_' . date('Ymd'), function () use ($LEAGUE_IDS, $LEAGUE_NAMES) {
    $today = date('Y-m-d');
    $out   = [];
    foreach ($LEAGUE_IDS as $lid) {
        $raw = api([
            'action'      => 'get_events',
            'from'        => $today,
            'to'          => $today,
            'league_id'   => $lid,
            'timezone'    => 'Africa/Dar_es_Salaam',
        ]);
        if (!is_array($raw)) continue;
        foreach ($raw as $m) {
            $status = strtolower($m['event_status'] ?? '');
            // Onyesha tu ambazo bado hazijachezwa au zinachezwa
            if ($status !== 'finished' && $status !== 'ft') {
                $out[] = fmt($m, $LEAGUE_NAMES[$lid] ?? 'Football');
            }
        }
    }
    // Sort by time
    usort($out, fn($a, $b) => strcmp($a['time'], $b['time']));
    return $out;
}, 300);

echo json_encode([
    'ok'      => true,
    'live'    => $live,
    'sched'   => $sched,
    'updated' => date('c'),
]);
