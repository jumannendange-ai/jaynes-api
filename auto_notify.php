<?php
/**
 * JAYNES MAX TV — auto_notify.php v4
 * Inatumia: AllSportsAPI (livescores/goals/HT/FT) + channels.php (onyo la mechi)
 *
 * CRON (kila dakika 2):
 * * /2 * * * * php /path/to/auto_notify.php >> /tmp/notify_log.txt 2>&1
 */

// ── CONFIG ──────────────────────────────────────────────────────
define('ALLSPORTS_KEY', '6095087686b995f2554af89bac81bb2b11d4182bdc4ff16c603d9130479f2736');
define('ALLSPORTS_URL', 'https://apiv2.allsportsapi.com/football/');
define('CHANNELS_URL',  'https://dde.ct.ws/channels.php?category=mechi+za+leo');
define('APP_URL',       'https://dde.ct.ws');
define('OS_APP_ID',     '10360777-3ada-4145-b83f-00eb0312a53f');
define('OS_REST_KEY',   'os_v2_app_ca3ao5z23jaulob7advqgevfh4qctlprzdauupekggukcgwmz5glfzdu6lkvnkzjeuno3cuuqow7fklo3fehp2puu52sr7sroo63hwy');
define('STATE_FILE',    __DIR__ . '/notify_state.json');
define('SENT_FILE',     __DIR__ . '/sent_matches.json');

// League IDs za kufuatilia kwenye AllSportsAPI
$LEAGUE_IDS = [551, 152, 302, 175, 354, 480, 346, 390, 29];

// ── LOGGING ─────────────────────────────────────────────────────
function log_msg(string $msg): void {
    echo '[' . date('Y-m-d H:i:s') . '] ' . $msg . "\n";
}

// ── ONESIGNAL — Tuma notification kwa wote ──────────────────────
function sendNotif(string $title, string $msg, string $url = ''): bool {
    $payload = [
        'app_id'               => OS_APP_ID,
        'included_segments'    => ['All'],
        'headings'             => ['en' => $title, 'sw' => $title],
        'contents'             => ['en' => $msg,   'sw' => $msg],
        'small_icon'           => 'ic_launcher',
        'android_accent_color' => 'FF00D4FF',
        'priority'             => 10,
        'ttl'                  => 3600,
    ];
    if ($url) $payload['url'] = $url;

    $ch = curl_init('https://onesignal.com/api/v1/notifications');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Key ' . OS_REST_KEY,
            'Content-Type: application/json',
        ],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $r = json_decode($res ?? '{}', true);
    if (!empty($r['id'])) {
        return true;
    }
    log_msg('⚠️ OneSignal error: ' . ($r['errors'][0] ?? json_encode($r)));
    return false;
}

// ── ALLSPORTS API CALL ───────────────────────────────────────────
function allsportsCall(array $params): array {
    $params['APIkey']   = ALLSPORTS_KEY;
    $params['timezone'] = 'Africa/Dar_es_Salaam';
    $url = ALLSPORTS_URL . '?' . http_build_query($params);

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'JaynesMaxTV/4.0',
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $d = json_decode($res ?: '{}', true);
    return $d['result'] ?? [];
}

// ── CHANNELS.PHP CALL — Mechi za Leo ────────────────────────────
function getChannelsMatches(): array {
    $ch = curl_init(CHANNELS_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'JaynesMaxTV/4.0',
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    $d = json_decode($res ?: '{}', true);
    if (empty($d['success'])) return [];
    return $d['channels'] ?? [];
}

// ── FORMAT MATCH (AllSports) ─────────────────────────────────────
function fmtMatch(array $m): array {
    $raw = $m['event_final_result'] ?? $m['event_halftime_result'] ?? '';
    $hs = $as = '';
    if ($raw !== '' && str_contains($raw, ' - ')) {
        [$hs, $as] = explode(' - ', $raw, 2);
    }
    $status = strtolower($m['event_status'] ?? '');
    if ($status === 'finished' || $status === 'ft') {
        $st = 'finished';
    } elseif ($status !== '' && $status !== 'not started' && $status !== 'ns') {
        $st = 'live';
    } else {
        $st = 'scheduled';
    }
    return [
        'id'         => (string)($m['event_key'] ?? ''),
        'home'       => $m['event_home_team'] ?? '?',
        'away'       => $m['event_away_team'] ?? '?',
        'home_score' => (string)$hs,
        'away_score' => (string)$as,
        'minute'     => $st === 'live' ? ($m['event_status'] ?? 'LIVE') : '',
        'time'       => $m['event_time'] ?? '',
        'date'       => $m['event_date'] ?? date('Y-m-d'),
        'league'     => $m['league_name'] ?? '',
        'status'     => $st,
    ];
}

// ── GET LIVESCORES (AllSports) ───────────────────────────────────
function getLive(array $leagueIds): array {
    $raw = allsportsCall(['met' => 'Livescore']);
    if (empty($raw)) return [];
    $out = [];
    foreach ($raw as $m) {
        if (in_array((int)($m['league_id'] ?? 0), $leagueIds)) {
            $out[] = fmtMatch($m);
        }
    }
    return $out;
}

// ── GET TODAY FIXTURES (AllSports) ──────────────────────────────
function getTodayFixtures(array $leagueIds): array {
    $today = date('Y-m-d');
    $out   = [];
    foreach ($leagueIds as $lid) {
        $raw = allsportsCall(['met' => 'Fixtures', 'leagueId' => $lid, 'from' => $today, 'to' => $today]);
        foreach ($raw as $m) {
            $fm = fmtMatch($m);
            if ($fm['status'] === 'scheduled') $out[] = $fm;
        }
    }
    usort($out, function($a, $b){ return strcmp($a['time'], $b['time']); });
    return $out;
}

// ── DETECT EVENTS (Goals, HT, FT, Kickoff) ──────────────────────
function detectEvents(array $live, array $prev): array {
    $events = [];

    foreach ($live as $m) {
        $id  = $m['id'];
        $p   = $prev[$id] ?? null;
        $hs  = (int)$m['home_score'];
        $as  = (int)$m['away_score'];
        $min = $m['minute'];

        // Kickoff — mchezo mpya unaanza
        if (!$p) {
            $events[] = [
                'type'  => 'kickoff',
                'm'     => $m,
                'title' => "🔴 LIVE: {$m['home']} vs {$m['away']}",
                'msg'   => "⚽ Mchezo unaanza sasa!\n{$m['home']} vs {$m['away']}\n🏆 {$m['league']}\nBonyeza kutazama LIVE!",
            ];
            continue;
        }

        $phs = (int)($p['home_score'] ?? 0);
        $pas = (int)($p['away_score'] ?? 0);

        // Goal — nyumbani
        if ($hs > $phs) {
            $events[] = [
                'type'  => 'goal_h',
                'm'     => $m,
                'title' => "🚨 GOOOAL! {$m['home']}",
                'msg'   => "⚽ {$m['home']} $hs - $as {$m['away']}\n⏱ Dakika $min | {$m['league']}\nBonyeza kutazama LIVE!",
            ];
        }

        // Goal — wageni
        if ($as > $pas) {
            $events[] = [
                'type'  => 'goal_a',
                'm'     => $m,
                'title' => "🚨 GOOOAL! {$m['away']}",
                'msg'   => "⚽ {$m['home']} $hs - $as {$m['away']}\n⏱ Dakika $min | {$m['league']}\nBonyeza kutazama LIVE!",
            ];
        }

        // Half Time
        $htMins  = ["45'", "45+1'", "45+2'", "45+3'", "HT", "45"];
        $prevMin = $p['minute'] ?? '';
        if (in_array($min, $htMins) && !in_array($prevMin, $htMins)) {
            $events[] = [
                'type'  => 'ht',
                'm'     => $m,
                'title' => "⏸ NUSU YA MCHEZO",
                'msg'   => "🟡 {$m['home']} $hs - $as {$m['away']}\nNusu ya kwanza imekwisha\n🏆 {$m['league']}",
            ];
        }
    }

    // Full Time — mchezo uliokuwa live sasa umekwisha
    foreach ($prev as $id => $p) {
        if (($p['status'] ?? '') !== 'live') continue;
        $still = false;
        foreach ($live as $m) {
            if ($m['id'] === $id) { $still = true; break; }
        }
        if (!$still) {
            $hs = $p['home_score'] ?? '?';
            $as = $p['away_score'] ?? '?';
            $events[] = [
                'type'  => 'ft',
                'm'     => $p,
                'title' => "🏁 MWISHO: {$p['home']} vs {$p['away']}",
                'msg'   => "📊 Matokeo ya Mwisho:\n{$p['home']} $hs - $as {$p['away']}\n🏆 {$p['league']}",
            ];
        }
    }

    return $events;
}

// ════════════════════════════════════════════════════════════════
//  MAIN
// ════════════════════════════════════════════════════════════════
log_msg('🚀 auto_notify.php v4 inaanza...');

$prevState = file_exists(STATE_FILE) ? (json_decode(file_get_contents(STATE_FILE), true) ?? []) : [];
$sentIds   = file_exists(SENT_FILE)  ? (json_decode(file_get_contents(SENT_FILE),  true) ?? []) : [];
$notified  = 0;
$newSent   = [];

// ── A. ONYO LA MECHI — Dakika 30 kabla (channels.php) ───────────
log_msg('📡 Inapata mechi za leo kutoka channels.php...');
$channelMatches = getChannelsMatches();
$countCh = count($channelMatches);
log_msg("📋 Mechi kutoka channels.php: $countCh");

if ($countCh > 0) {
    // Pia pata mechi za AllSports kwa wakati sahihi
    $todayFixtures = getTodayFixtures($LEAGUE_IDS);
    $nowMin = (int)date('H') * 60 + (int)date('i');

    // Unganisha mechi zote mbili kwa kuangalia jina
    $allTodayMatches = [];

    // Kwanza ongeza mechi za AllSports (zina wakati sahihi)
    foreach ($todayFixtures as $fx) {
        $key = strtolower(trim($fx['home'] . '_vs_' . $fx['away']));
        $allTodayMatches[$key] = $fx;
    }

    // Ongeza mechi za channels.php (zina stream URL)
    foreach ($channelMatches as $ch) {
        $title = strtolower(trim($ch['title'] ?? $ch['name'] ?? ''));
        // Angalia kama mchezo huu uko tayari kwenye AllSports list
        $matched = false;
        foreach ($allTodayMatches as $key => $fx) {
            // Match kwa sehemu ya jina
            if (
                str_contains($title, strtolower($fx['home'])) ||
                str_contains($title, strtolower($fx['away'])) ||
                str_contains(strtolower($fx['home']), $title) ||
                str_contains(strtolower($fx['away']), $title)
            ) {
                // Ongeza stream URL kwenye AllSports match
                $allTodayMatches[$key]['stream_url']   = $ch['url'] ?? '';
                $allTodayMatches[$key]['stream_title']  = $ch['title'] ?? $ch['name'] ?? '';
                $matched = true;
                break;
            }
        }

        // Mchezo wa channels.php ambao haupo AllSports — ongeza moja kwa moja
        if (!$matched) {
            $allTodayMatches['ch_' . md5($title)] = [
                'id'           => 'ch_' . md5($title),
                'home'         => $ch['title'] ?? $ch['name'] ?? 'Mchezo',
                'away'         => '',
                'time'         => '',
                'date'         => date('Y-m-d'),
                'league'       => 'Leo',
                'status'       => 'scheduled',
                'stream_url'   => $ch['url'] ?? '',
                'stream_title' => $ch['title'] ?? $ch['name'] ?? '',
            ];
        }
    }

    // Tuma onyo la dakika 30 kabla
    foreach ($allTodayMatches as $fx) {
        if (empty($fx['time'])) continue;

        $sentId = 'pre30_' . ($fx['id'] ?: md5($fx['home'] . $fx['away']));
        if (in_array($sentId, $sentIds)) continue;

        $tp   = explode(':', $fx['time']);
        $tMin = (int)$tp[0] * 60 + (int)($tp[1] ?? 0);
        $diff = $tMin - $nowMin;

        if ($diff >= 25 && $diff <= 40) {
            $hasStream = !empty($fx['stream_url']);
            $away      = $fx['away'] ? " vs {$fx['away']}" : '';
            $title     = "⚽ Dakika {$diff} — {$fx['home']}{$away}";
            $msg       = "🕐 Mchezo unakaribia kuanza!\n{$fx['home']}{$away}\n⏰ {$fx['time']} | {$fx['league']}";
            if ($hasStream) $msg .= "\n📺 Inaweza kutazamwa kwenye JAYNES MAX TV!";
            $url = $hasStream ? APP_URL . '/live.php' : APP_URL . '/schedule.php';

            if (sendNotif($title, $msg, $url)) {
                $notified++;
                $newSent[] = $sentId;
                log_msg("✅ Onyo (dk {$diff}): {$fx['home']}{$away}");
            }
        }
    }
}

// ── B. LIVESCORES — Kickoff, Goals, HT, FT (AllSportsAPI) ───────
log_msg('🔴 Inapata livescores kutoka AllSportsAPI...');
$liveMatches = getLive($LEAGUE_IDS);
$nLive = count($liveMatches);
log_msg("📊 Mechi live: $nLive");

foreach (detectEvents($liveMatches, $prevState) as $e) {
    $m   = $e['m'];
    $hs  = $m['home_score'] ?? 0;
    $as  = $m['away_score'] ?? 0;

    // ID ya kipekee kwa kila event — kuzuia kutuma mara mbili
    $sentId = 'ev_' . $e['type'] . '_' . ($m['id'] ?: md5($m['home'] . $m['away'])) . '_' . $hs . '_' . $as;
    if (in_array($sentId, $sentIds)) continue;

    $notifUrl = APP_URL . '/live.php';

    if (sendNotif($e['title'], $e['msg'], $notifUrl)) {
        $notified++;
        $newSent[] = $sentId;
        log_msg("✅ {$e['type']}: {$m['home']} vs {$m['away']} ({$hs}-{$as})");
        sleep(1); // Epuka spam
    }
}

// ── C. HIFADHI STATE MPYA ────────────────────────────────────────
$newState = [];
foreach ($liveMatches as $m) {
    $id = $m['id'];
    if (!$id) continue;
    $newState[$id] = [
        'home'       => $m['home'],
        'away'       => $m['away'],
        'home_score' => $m['home_score'],
        'away_score' => $m['away_score'],
        'minute'     => $m['minute'],
        'league'     => $m['league'],
        'status'     => 'live',
        'updated'    => time(),
    ];
}

// Hifadhi state ya zamani kwa masaa 2 (kwa FT detection)
foreach ($prevState as $id => $ps) {
    if (!isset($newState[$id]) && (time() - ($ps['updated'] ?? 0)) < 7200) {
        $newState[$id] = $ps;
    }
}

file_put_contents(STATE_FILE, json_encode($newState, JSON_PRETTY_PRINT));

// Hifadhi sent IDs (max 500 za mwisho)
if (!empty($newSent)) {
    $all = array_unique(array_merge($sentIds, $newSent));
    file_put_contents(SENT_FILE, json_encode(array_slice($all, -500)));
}

log_msg("✅ Imekamilika. Notifications: $notified | Live: $nLive | Channels mechi: $countCh");
log_msg(str_repeat('─', 50));
