<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");

// ── CONFIG ────────────────────────────────────────────────────
$CLEARKEY = "c31df1600afc33799ecac543331803f2:dd2101530e222f545997d4c553787f85";
$ZIMO_URL = "https://zimotv.com/mb/api/get-channels.php?category=nbc";

// ── Fetch ZimoTV ──────────────────────────────────────────────
$ch = curl_init($ZIMO_URL);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10,
    CURLOPT_HTTPHEADER     => [
        "User-Agent: Mozilla/5.0",
        "Referer: https://zimotv.com/",
        "Origin: https://zimotv.com",
    ]
]);
$response  = curl_exec($ch);
$curlError = curl_error($ch);
curl_close($ch);

if ($curlError) {
    echo json_encode(["success" => false, "error" => "cURL: $curlError"], JSON_PRETTY_PRINT);
    exit;
}

$data     = json_decode($response, true);
$channels = $data['channels'] ?? [];

if (empty($channels)) {
    echo json_encode(["success" => false, "error" => "Hakuna channels zilizopatikana"], JSON_PRETTY_PRINT);
    exit;
}

// ── Process each channel ──────────────────────────────────────
$output = [];

foreach ($channels as $c) {
    $rawUrl = $c['url'] ?? '';

    // Fix protocol-relative URL
    if (strpos($rawUrl, '//') === 0) {
        $rawUrl = 'https:' . $rawUrl;
    }

    // Detect stream type
    $streamType = 'unknown';
    $cleanUrl   = strtok($rawUrl, '?'); // URL bila query string
    $ext        = strtolower(pathinfo($cleanUrl, PATHINFO_EXTENSION));

    if ($ext === 'mpd' || stripos($rawUrl, '.mpd') !== false) {
        $streamType = 'DASH';
    } elseif ($ext === 'm3u8' || stripos($rawUrl, '.m3u8') !== false) {
        $streamType = 'HLS';
    } elseif ($ext === 'ts') {
        $streamType = 'HLS-TS';
    }

    // Headers
    $headers = $c['headers'] ?? [];

    // Key split
    [$kid, $key] = array_pad(explode(':', $CLEARKEY, 2), 2, '');

    $output[] = [
        "id"          => $c['id'] ?? null,
        "channel_id"  => $c['channel_id'] ?? '',
        "title"       => $c['title'] ?? '',
        "logo"        => $c['logo'] ?? '',
        "stream_type" => $streamType,
        "url"         => $rawUrl,
        "headers"     => $headers,
        "free"        => $c['free'] ?? '0',
        "drm"         => "CLEARKEY",
        "clearkey"    => [
            "kid" => $kid,
            "key" => $key,
        ],
    ];
}

// ── Final JSON ────────────────────────────────────────────────
echo json_encode([
    "success"  => true,
    "total"    => count($output),
    "category" => $data['category'] ?? 'nbc',
    "channels" => $output,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
