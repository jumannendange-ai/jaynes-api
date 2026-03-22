<?php
/**
 * channels.php v3 — Smart Channel Proxy
 * Fixed: PHPSESSID cookie inapitishwa kutoka step2 hadi step3
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache');

define('API_BASE',   'https://ads.desksanalytics.link/dream/data/web/');
define('CACHE_FILE', sys_get_temp_dir() . '/channels_v3_' . md5($_GET['category'] ?? 'mechi za leo') . '.json');
define('CACHE_TTL',  60);

// Local cache
if (file_exists(CACHE_FILE) && (time() - filemtime(CACHE_FILE)) < CACHE_TTL) {
    $c = file_get_contents(CACHE_FILE);
    if ($c) { echo $c; exit; }
}

$category = $_GET['category'] ?? 'mechi za leo';

// ── STEP 1: Pata encrypted data ───────────────────────────────────────────────
$raw = curlGet(API_BASE . 'secure_api.php?endpoint=category_channels.php&category=' . urlencode($category));
if (!$raw) die(json_encode(['success'=>false,'error'=>'Step1: Network failure','channels'=>[]]));

$enc = json_decode($raw, true);
if (empty($enc['data']) || empty($enc['token'])) {
    die(json_encode(['success'=>false,'error'=>'Step1: Bad response','channels'=>[]]));
}

$token = $enc['token'];

// ── STEP 2: Decrypt — hifadhi PHPSESSID cookie ────────────────────────────────
$respHeaders = [];
$ch = curl_init(API_BASE . 'secure_api.php');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['action'=>'decrypt','data'=>$enc['data'],'token'=>$token]),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT      => 'Mozilla/5.0',
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Accept: application/json'],
    CURLOPT_HEADERFUNCTION => function($ch, $header) use (&$respHeaders) {
        $t = trim($header);
        if ($t) $respHeaders[] = $t;
        return strlen($header);
    },
]);
$r2body = curl_exec($ch);
curl_close($ch);

$d2 = json_decode($r2body, true);
if (empty($d2['success'])) {
    die(json_encode(['success'=>false,'error'=>'Step2: ' . ($d2['error'] ?? 'decrypt failed'),'channels'=>[]]));
}

// Chukua PHPSESSID kutoka Set-Cookie header
$sessionCookie = '';
foreach ($respHeaders as $h) {
    if (stripos($h, 'set-cookie:') === 0) {
        $parts = explode(':', $h, 2);
        $cookiePart = trim($parts[1]);
        $sessionCookie .= explode(';', $cookiePart)[0] . '; ';
    }
}
$sessionCookie = trim($sessionCookie, '; ');

// ── STEP 3: get_data — tuma session cookie ────────────────────────────────────
$ch3 = curl_init(API_BASE . 'secure_api.php');
curl_setopt_array($ch3, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['action'=>'get_data','token'=>$token]),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_SSL_VERIFYHOST => false,
    CURLOPT_TIMEOUT        => 20,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_USERAGENT      => 'Mozilla/5.0',
    CURLOPT_HTTPHEADER     => [
        'Content-Type: application/json',
        'Accept: application/json',
        'Cookie: ' . $sessionCookie,
    ],
]);
$r3body = curl_exec($ch3);
curl_close($ch3);

$d3 = json_decode($r3body, true);
if (empty($d3['cache_id'])) {
    die(json_encode(['success'=>false,'error'=>'Step3: ' . ($d3['error'] ?? 'no cache_id'),'channels'=>[]]));
}

// ── STEP 4: Fetch cache file ──────────────────────────────────────────────────
$cacheData = curlGet(API_BASE . 'cache/' . $d3['cache_id'] . '.dat');
if (!$cacheData) {
    die(json_encode(['success'=>false,'error'=>'Step4: cache file missing','channels'=>[]]));
}

$apiResult = json_decode($cacheData, true);
$rawChannels = $apiResult['channels'] ?? (is_array($apiResult) ? $apiResult : null);
if (!is_array($rawChannels)) {
    die(json_encode(['success'=>false,'error'=>'Step4: Bad JSON','sample'=>substr($cacheData,0,100),'channels'=>[]]));
}

// ── STEP 5: Cleanup ───────────────────────────────────────────────────────────
$chClean = curl_init(API_BASE . 'secure_api.php');
curl_setopt_array($chClean, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST           => true,
    CURLOPT_POSTFIELDS     => json_encode(['action'=>'cleanup_cache','cache_id'=>$d3['cache_id']]),
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_USERAGENT      => 'Mozilla/5.0',
    CURLOPT_HTTPHEADER     => ['Content-Type: application/json','Cookie: ' . $sessionCookie],
]);
curl_exec($chClean);
curl_close($chClean);

// ── STEP 6: Score, group, sort channels ──────────────────────────────────────
function scoreUrl(string $url): int {
    if (str_contains($url, 'kinescopecdn.net')) return 100;
    if (str_contains($url, 'akamaized.net'))    return 90;
    if (str_contains($url, 'cloudfront.net'))   return 85;
    if (str_starts_with($url, 'https://'))      return 50;
    if (str_contains($url, '195.178.'))         return 5;
    return 15;
}

// Group channels kwa title (kila mchezo una CH1 + CH2)
$groups = [];
foreach ($rawChannels as $ch) {
    $title = trim($ch['title'] ?? $ch['name'] ?? '');
    if (!$title) continue;
    $groups[$title][] = $ch;
}

$best = [];
foreach ($groups as $title => $chs) {
    // Sort: URL bora kwanza
    usort($chs, fn($a,$b) => scoreUrl($b['url']??'') - scoreUrl($a['url']??''));

    $winner = $chs[0];

    // Tengeneza alternatives (URLs nyingine kama fallback)
    $alts = [];
    foreach (array_slice($chs, 1) as $alt) {
        $altUrl = $alt['url'] ?? '';
        if ($altUrl && $altUrl !== ($winner['url']??'') && scoreUrl($altUrl) > 0) {
            // Build headers object
            $headers = new stdClass();
            foreach ($alt as $k => $v) {
                if (!in_array($k, ['id','event_id','type','channel_id','url','title','name','category'])) {
                    if ($v && $v !== '*') $headers->$k = $v;
                }
            }
            $alts[] = ['url'=>$altUrl, 'headers'=>$headers];
        }
    }
    $winner['_alternatives'] = $alts;
    $winner['_score']        = scoreUrl($winner['url']??'');

    // Build clean headers object for winner
    $headers = new stdClass();
    foreach ($winner as $k => $v) {
        if (!in_array($k, ['id','event_id','type','channel_id','url','title','name','category','_alternatives','_score'])) {
            if ($v && $v !== '*') $headers->$k = $v;
        }
    }
    $winner['headers'] = $headers;

    $best[] = $winner;
}

// Sort alphabetically by title
usort($best, fn($a,$b) => strcmp($a['title']??'', $b['title']??''));

$output = json_encode([
    'success'   => true,
    'count'     => count($best),
    'total_raw' => count($rawChannels),
    'channels'  => $best,
], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

file_put_contents(CACHE_FILE, $output, LOCK_EX);
echo $output;

// ── cURL Helper ───────────────────────────────────────────────────────────────
function curlGet(string $url): string|false {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_TIMEOUT        => 20,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_USERAGENT      => 'Mozilla/5.0',
        CURLOPT_HTTPHEADER     => ['Accept: application/json, */*'],
    ]);
    $r = curl_exec($ch);
    $c = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ($c === 200 && $r && strlen($r) > 2) ? $r : false;
}
