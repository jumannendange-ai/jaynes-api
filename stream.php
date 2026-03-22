<?php
/**
 * stream.php - HLS Stream Proxy v3.0
 * Inafanya kama request inatoka fawanews.sc (77.81.121.211)
 */

// ─── CONFIG ───────────────────────────────────────────────────────────────────
define('CACHE_ENABLED',   true);
define('CACHE_DIR',       sys_get_temp_dir() . '/hls_cache/');
define('CACHE_TTL',       10);
define('PLAYLIST_TTL',   3);
define('CURL_TIMEOUT',   30);
define('CURL_CONNECT_TO', 10);
define('MAX_URL_LENGTH',  4096);

// Fawanews IP — server inaona hii kama chanzo cha request
define('FAWA_IP',     '77.81.121.211');
define('FAWA_ORIGIN', 'http://www.fawanews.sc');
define('FAWA_REFER',  'http://www.fawanews.sc/');
define('FAWA_HOST',   'www.fawanews.sc');

// ─── CORS ─────────────────────────────────────────────────────────────────────
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: *");
header("Access-Control-Expose-Headers: Content-Length, Content-Range");
header("X-Content-Type-Options: nosniff");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204); exit;
}

// ─── VALIDATE ─────────────────────────────────────────────────────────────────
if (!isset($_GET['url'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing url parameter']); exit;
}

$streamUrl = urldecode($_GET['url']);

if (strlen($streamUrl) > MAX_URL_LENGTH || !preg_match('#^https?://#i', $streamUrl)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid URL']); exit;
}

// ─── PARSE CUSTOM HEADERS ─────────────────────────────────────────────────────
$customHeaders = [];
if (isset($_GET['headers'])) {
    $decoded = json_decode(urldecode($_GET['headers']), true);
    if (is_array($decoded)) {
        $skip = ['host','connection','content-length','transfer-encoding','accept-encoding'];
        foreach ($decoded as $k => $v) {
            if ($v !== '*' && !in_array(strtolower($k), $skip)) {
                $customHeaders[strtolower($k)] = $v;
            }
        }
    }
}

// ─── BUILD FINAL HEADERS ──────────────────────────────────────────────────────
// Anza na headers za fawanews, kisha ongeza custom headers juu
$finalHeaders = [
    // IP spoofing headers — server itaona hii kama inatoka fawanews.sc
    'X-Forwarded-For'   => FAWA_IP,
    'X-Real-IP'         => FAWA_IP,
    'X-Client-IP'       => FAWA_IP,
    'CF-Connecting-IP'  => FAWA_IP,
    'True-Client-IP'    => FAWA_IP,
    'X-Originating-IP'  => FAWA_IP,

    // Fawanews identity
    'Referer'           => FAWA_REFER,
    'Origin'            => FAWA_ORIGIN,

    // Browser-like headers
    'User-Agent'        => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/136.0.0.0 Safari/537.36',
    'Accept'            => '*/*',
    'Accept-Language'   => 'en-US,en;q=0.9',
    'Accept-Encoding'   => 'identity',
    'Cache-Control'     => 'no-cache',
    'Pragma'            => 'no-cache',
    'Sec-Fetch-Dest'    => 'empty',
    'Sec-Fetch-Mode'    => 'cors',
    'Sec-Fetch-Site'    => 'cross-site',
];

// Merge custom headers (zinaoverride defaults isipokuwa IP headers)
foreach ($customHeaders as $k => $v) {
    $keep_override = ['referer','origin','user-agent'];
    if (in_array($k, $keep_override)) {
        $finalHeaders[ucfirst($k)] = $v;
    }
}

// Convert to curl format
$curlHeaders = [];
foreach ($finalHeaders as $k => $v) {
    $curlHeaders[] = "$k: $v";
}

// ─── FILE TYPE DETECTION ──────────────────────────────────────────────────────
$urlPath    = strtolower(parse_url($streamUrl, PHP_URL_PATH) ?? '');
$isPlaylist = str_contains($urlPath, '.m3u8');
$isSegment  = str_contains($urlPath, '.ts');
$isAudio    = str_contains($urlPath, '.aac') || str_contains($urlPath, '.mp4');
$isKey      = str_contains($urlPath, '.key');
$isSubtitle = str_contains($urlPath, '.vtt') || str_contains($urlPath, '.srt');

// ─── CACHE ────────────────────────────────────────────────────────────────────
function getCacheKey(string $url): string {
    return CACHE_DIR . md5($url) . '.cache';
}
function getFromCache(string $url, int $ttl): string|false {
    if (!CACHE_ENABLED) return false;
    $f = getCacheKey($url);
    if (file_exists($f) && (time() - filemtime($f)) < $ttl) return file_get_contents($f);
    return false;
}
function saveToCache(string $url, string $data): void {
    if (!CACHE_ENABLED) return;
    if (!is_dir(CACHE_DIR)) mkdir(CACHE_DIR, 0755, true);
    file_put_contents(getCacheKey($url), $data, LOCK_EX);
}

// ─── FETCH ────────────────────────────────────────────────────────────────────
function fetchStream(string $url, array $curlHeaders): array {
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER  => true,
        CURLOPT_FOLLOWLOCATION  => true,
        CURLOPT_MAXREDIRS       => 10,
        CURLOPT_HTTPHEADER      => $curlHeaders,
        CURLOPT_SSL_VERIFYPEER  => false,
        CURLOPT_SSL_VERIFYHOST  => false,
        CURLOPT_TIMEOUT         => CURL_TIMEOUT,
        CURLOPT_CONNECTTIMEOUT  => CURL_CONNECT_TO,
        CURLOPT_ENCODING        => 'identity',
        CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
        CURLOPT_IPRESOLVE       => CURL_IPRESOLVE_V4,
        CURLOPT_TCP_KEEPALIVE   => 1,
        CURLOPT_FRESH_CONNECT   => true,
        // Hii ni muhimu sana — interface ya network ya server
        // CURLOPT_INTERFACE    => '0.0.0.0', // Uncomment kama server ina IPs nyingi
    ]);

    $body     = curl_exec($ch);
    $code     = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $ctype    = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
    $errNo    = curl_errno($ch);
    $errMsg   = curl_error($ch);
    $ms       = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME) * 1000);
    $ip       = curl_getinfo($ch, CURLINFO_PRIMARY_IP);
    curl_close($ch);

    return compact('body','code','ctype','errNo','errMsg','ms','ip');
}

// ─── HANDLE M3U8 PLAYLIST ─────────────────────────────────────────────────────
if ($isPlaylist) {

    $cached = getFromCache($streamUrl, PLAYLIST_TTL);
    if ($cached !== false) {
        header('Content-Type: application/vnd.apple.mpegurl');
        header('Cache-Control: no-cache');
        header('X-Cache: HIT');
        echo $cached; exit;
    }

    $r = fetchStream($streamUrl, $curlHeaders);

    if ($r['code'] !== 200 || !$r['body']) {
        http_response_code(502);
        header('Content-Type: application/json');
        echo json_encode([
            'error'   => 'Stream haipatikani',
            'code'    => $r['code'],
            'errno'   => $r['errNo'],
            'detail'  => $r['errMsg'],
            'server'  => $r['ip'],
            'ms'      => $r['ms'],
            'url'     => $streamUrl,
        ]);
        exit;
    }

    // Rewrite playlist URLs kupitia proxy hii hii
    $baseUrl        = dirname($streamUrl) . '/';
    $selfUrl        = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https' : 'http')
                    . '://' . $_SERVER['HTTP_HOST']
                    . strtok($_SERVER['REQUEST_URI'], '?');
    $encodedHeaders = urlencode(json_encode($customHeaders));
    $lines          = explode("\n", $r['body']);
    $output         = [];

    foreach ($lines as $line) {
        $line = rtrim($line);

        if ($line === '') { $output[] = ''; continue; }

        // Rewrite URI= inside tags (e.g. EXT-X-KEY)
        if (str_starts_with($line, '#') && preg_match('/URI="([^"]+)"/', $line, $m)) {
            $uri = $m[1];
            if (!preg_match('#^https?://#', $uri)) $uri = $baseUrl . $uri;
            $proxied = $selfUrl . '?url=' . urlencode($uri) . '&headers=' . $encodedHeaders;
            $line = str_replace('URI="' . $m[1] . '"', 'URI="' . $proxied . '"', $line);
            $output[] = $line; continue;
        }

        if (str_starts_with($line, '#')) { $output[] = $line; continue; }

        // Rewrite segment/sub-playlist URLs
        if (!preg_match('#^https?://#', $line)) $line = $baseUrl . $line;
        $output[] = $selfUrl . '?url=' . urlencode($line) . '&headers=' . $encodedHeaders;
    }

    $rewritten = implode("\n", $output);
    saveToCache($streamUrl, $rewritten);

    header('Content-Type: application/vnd.apple.mpegurl');
    header('Cache-Control: no-cache');
    header('X-Cache: MISS');
    header('X-Proxy-Time: ' . $r['ms'] . 'ms');
    echo $rewritten;
    exit;
}

// ─── HANDLE SEGMENTS / KEYS / SUBTITLES ──────────────────────────────────────

// Cache segments
if ($isSegment || $isAudio) {
    $cached = getFromCache($streamUrl, CACHE_TTL);
    if ($cached !== false) {
        header('Content-Type: ' . ($isAudio ? 'audio/aac' : 'video/mp2t'));
        header('X-Cache: HIT');
        echo $cached; exit;
    }
}

$r = fetchStream($streamUrl, $curlHeaders);

if ($r['code'] !== 200 || $r['body'] === false) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode([
        'error'  => 'Segment/key haipatikani',
        'code'   => $r['code'],
        'detail' => $r['errMsg'],
    ]);
    exit;
}

// Content-Type
if ($isKey)          $type = 'application/octet-stream';
elseif ($isSubtitle) $type = str_contains($urlPath, '.vtt') ? 'text/vtt' : 'text/plain';
elseif ($isAudio)    $type = 'audio/aac';
elseif ($isSegment)  $type = 'video/mp2t';
else                 $type = $r['ctype'] ?: 'application/octet-stream';

header('Content-Type: ' . $type);
header('Content-Length: ' . strlen($r['body']));
header('Cache-Control: no-cache');
header('X-Cache: MISS');
header('X-Proxy-Time: ' . $r['ms'] . 'ms');

if ($isSegment || $isAudio) saveToCache($streamUrl, $r['body']);

echo $r['body'];
