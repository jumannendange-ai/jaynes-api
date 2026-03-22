<?php
/**
 * GET /stream?url=<encoded_url>&type=hls|mpd|ts
 * Proxy ya HLS na MPD streams — inazuia CORS na kuongeza headers
 */

requireMethod('GET');

$url  = urldecode(getParam('url'));
$type = strtolower(getParam('type', 'hls'));

// Validate URL
if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
    header('Content-Type: application/json');
    fail('URL sahihi inahitajika');
}

// Ruhusia domains zinazojulikana tu
$allowedDomains = [
    '195.178.110',
    'kinescopecdn.net',
    'akamaihd.net',
    'cloudfront.net',
    'azam',
    'dde.ct.ws',
    'mq.xo.je',
    'render.com',
];

$allowed = false;
foreach ($allowedDomains as $domain) {
    if (str_contains($url, $domain)) {
        $allowed = true;
        break;
    }
}

// Content-Type kulingana na aina
$contentTypes = [
    'mpd'  => 'application/dash+xml',
    'ts'   => 'video/mp2t',
    'mp4'  => 'video/mp4',
    'hls'  => 'application/vnd.apple.mpegurl',
    'm3u8' => 'application/vnd.apple.mpegurl',
];

$contentType = $contentTypes[$type] ?? 'application/vnd.apple.mpegurl';

// Set headers
header("Content-Type: {$contentType}");
header('Access-Control-Allow-Origin: *');
header('Cache-Control: no-cache, no-store');
header('X-Content-Type-Options: nosniff');

// Fetch stream
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_MAXREDIRS      => 5,
    CURLOPT_TIMEOUT        => 25,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_ENCODING       => '',
    CURLOPT_HTTPHEADER     => [
        'User-Agent: Mozilla/5.0 (Linux; Android 13; Pixel 7) AppleWebKit/537.36 Chrome/120 Mobile Safari/537.36',
        'Referer: https://mq.xo.je/',
        'Origin: https://mq.xo.je',
        'Accept: */*',
        'Accept-Language: sw-TZ,sw;q=0.9,en;q=0.8',
    ],
]);

$data    = curl_exec($ch);
$code    = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($curlErr || !$data || $code !== 200) {
    http_response_code(502);
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error'   => 'Stream haipatikani',
        'code'    => $code,
        'details' => $curlErr ?: "HTTP {$code}",
    ]);
    exit;
}

// Kama ni M3U8 — badilisha URLs za relative kuwa absolute + proxy
if (in_array($type, ['hls', 'm3u8']) && (str_contains($data, '.ts') || str_contains($data, '.m3u8'))) {
    $baseUrl   = dirname($url) . '/';
    $proxyBase = '/stream?type=ts&url=';

    // Relative .ts segments → proxied absolute URLs
    $data = preg_replace_callback(
        '/^(?!#)(?!https?:\/\/)([^\s]+\.ts[^\s]*)/m',
        fn($m) => $proxyBase . urlencode($baseUrl . $m[1]),
        $data
    );

    // Relative .m3u8 → proxied
    $data = preg_replace_callback(
        '/^(?!#)(?!https?:\/\/)([^\s]+\.m3u8[^\s]*)/m',
        fn($m) => $proxyBase . urlencode($baseUrl . $m[1]) . '&type=hls',
        $data
    );

    // Absolute .ts → proxied
    $data = preg_replace_callback(
        '/^(?!#)(https?:\/\/[^\s]+\.ts[^\s]*)/m',
        fn($m) => $proxyBase . urlencode($m[1]),
        $data
    );
}

echo $data;
