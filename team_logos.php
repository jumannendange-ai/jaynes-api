<?php
// ═══════════════════════════════════════════════
//  JAYNES MAX TV — team_logos.php
//  Inatoa logo za timu kutoka AllSportsAPI
//  Inatumika na live.php kuonyesha nembo za timu
// ═══════════════════════════════════════════════

header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');

define('APIKEY', '6095087686b995f2554af89bac81bb2b11d4182bdc4ff16c603d9130479f2736');
define('API_BASE', 'https://apiv2.allsportsapi.com/football/');

// Cache kwenye /tmp — wiki moja (logo hazibadiliki mara kwa mara)
$cacheFile = sys_get_temp_dir() . '/jmtv_team_logos_v1.json';
$cacheTTL  = 7 * 24 * 3600; // wiki moja

if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < $cacheTTL) {
    $cached = file_get_contents($cacheFile);
    if ($cached) {
        echo $cached;
        exit;
    }
}

// Vikengerwe vya ligi — zote zinazotumika kwenye live.php na schedule.php
$leagues = [
    551,  // NBC Premier League (Tanzania)
    152,  // Premier League (EPL)
    302,  // La Liga
    175,  // Bundesliga
    354,  // Champions League
    480,  // Europa League
    346,  // CAF Champions
    390,  // CAF Confederation
    29,   // AFCON
    207,  // Serie A
    168,  // Ligue 1
];

$today  = date('Y-m-d');
$past   = date('Y-m-d', strtotime('-7 days'));
$future = date('Y-m-d', strtotime('+14 days'));

// Pata logos kutoka standings (zinaaminika zaidi)
$teamLogos = [];

foreach ($leagues as $lid) {
    // Standings — kila timu ina team_logo
    $standUrl = API_BASE . '?' . http_build_query([
        'met'      => 'Standings',
        'leagueId' => $lid,
        'APIkey'   => APIKEY,
    ]);
    $ch = curl_init($standUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0',
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    $data = json_decode($raw, true);
    $result = $data['result'] ?? [];

    // AllSportsAPI standings inaweza kuwa { "total": [...], "home": [...] }
    $rows = [];
    if (isset($result['total']) && is_array($result['total'])) {
        $rows = $result['total'];
    } elseif (is_array($result)) {
        // Flat array au wrapped
        foreach ($result as $item) {
            if (is_array($item) && isset($item['total'])) {
                $rows = array_merge($rows, $item['total']);
            } elseif (is_array($item) && (isset($item['standing_team']) || isset($item['team_name']))) {
                $rows[] = $item;
            }
        }
    }

    foreach ($rows as $row) {
        $name = $row['standing_team'] ?? $row['team_name'] ?? '';
        $logo = $row['team_logo'] ?? '';
        if ($name && $logo) {
            // Hifadhi kwa jina (lowercase, trim)
            $key = strtolower(trim($name));
            if (!isset($teamLogos[$key])) {
                $teamLogos[$key] = [
                    'name' => $name,
                    'logo' => $logo,
                ];
            }
        }
    }

    // Kuchelewa kidogo kuzuia rate limiting
    usleep(120000); // 120ms
}

// Pia pata logos kutoka fixtures za wiki hii (inatoa majina tofauti ya timu)
foreach ([152, 551, 354] as $lid) {
    $fxUrl = API_BASE . '?' . http_build_query([
        'met'      => 'Fixtures',
        'leagueId' => $lid,
        'from'     => $past,
        'to'       => $future,
        'APIkey'   => APIKEY,
    ]);
    $ch = curl_init($fxUrl);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT      => 'Mozilla/5.0',
    ]);
    $raw = curl_exec($ch);
    curl_close($ch);

    $data   = json_decode($raw, true);
    $result = $data['result'] ?? [];

    if (is_array($result)) {
        foreach ($result as $fx) {
            $hn = $fx['event_home_team'] ?? '';
            $an = $fx['event_away_team'] ?? '';
            $hl = $fx['home_team_logo'] ?? '';
            $al = $fx['away_team_logo'] ?? '';

            if ($hn && $hl) {
                $k = strtolower(trim($hn));
                if (!isset($teamLogos[$k])) $teamLogos[$k] = ['name' => $hn, 'logo' => $hl];
            }
            if ($an && $al) {
                $k = strtolower(trim($an));
                if (!isset($teamLogos[$k])) $teamLogos[$k] = ['name' => $an, 'logo' => $al];
            }
        }
    }

    usleep(120000);
}

$output = [
    'success'   => true,
    'count'     => count($teamLogos),
    'logos'     => $teamLogos,
    'cached_at' => date('Y-m-d H:i:s'),
];

$json = json_encode($output, JSON_UNESCAPED_UNICODE);
file_put_contents($cacheFile, $json);
echo $json;
