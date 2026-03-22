<?php

// ============================================================

// ANGALIA KAMA INATAKIWA DOWNLOAD AU JSON

// ============================================================

$download = isset($_GET['download']) && $_GET['download'] === '1';

if ($download) {

    header("Content-Type: application/octet-stream");

    header("Content-Disposition: attachment; filename=channels.json");

} else {

    header("Content-Type: application/json");

}

header("Access-Control-Allow-Origin: *");

// ============================================================

// HELPER: FETCH URL KWA CURL

// ============================================================

function fetchUrl(string $url): string {

    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL            => $url,

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_FOLLOWLOCATION => true,

        CURLOPT_TIMEOUT        => 15,

        CURLOPT_HTTPHEADER     => [

            'User-Agent: Mozilla/5.0 (Linux; Android 11) AppleWebKit/537.36 Chrome/112.0 Mobile Safari/537.36',

            'Referer: https://lipopotv.live/',

            'Accept: text/html,application/json,*/*',

        ],

    ]);

    $res = curl_exec($ch);

    curl_close($ch);

    return $res ?: '';

}

// ============================================================

// HELPER: TOA stream_url NA clear_key KUTOKA HTML

// ============================================================

function extractFromHtml(string $html): array {

    $streamUrl = '';

    $clearKey  = '';

    if (preg_match('/var\s+streamUrl\s*=\s*["\']([^"\']+)["\']/', $html, $m)) {

        $streamUrl = trim($m[1]);

    }

    if (preg_match('/var\s+clearKey\s*=\s*["\']([^"\']+)["\']/', $html, $m)) {

        $clearKey = trim($m[1]);

    }

    return ['stream_url' => $streamUrl, 'clear_key' => $clearKey];

}

// ============================================================

// HELPER: JENGA CHANNEL KUTOKA LIPOPOTV

// ============================================================

function lipopoChannel(string $phpUrl, string $name, string $category, string $image): ?array {

    $html = fetchUrl($phpUrl);

    $data = extractFromHtml($html);

    if (empty($data['stream_url'])) return null;

    return [

        "name"     => $name,

        "category" => $category,

        "url"      => $data['stream_url'],

        "image"    => $image,

        "key"      => !empty($data['clear_key']) ? $data['clear_key'] : null,

        "type"     => str_ends_with($data['stream_url'], ".m3u8") ? "hls" : "dash",

    ];

}

// ============================================================

// SEHEMU 1: LIPOPOTV CHANNELS (4)

// ============================================================

$lipopoChannels = [];

$lipopoList = [

    [

        "url"      => "https://lipopotv.live/one.php",

        "name"     => "Azam One",

        "category" => "TAMTHILIYA",

        "image"    => "https://i.postimg.cc/RFfMP31f/1770047388328-Master-Chef-Azam-ONE-poster-Image.webp",

    ],

    [

        "url"      => "https://lipopotv.live/crown.php",

        "name"     => "Crown TV",

        "category" => "MUSIC",

        "image"    => "https://i.postimg.cc/pTYdyxDW/1745514813150-Crown-TVPoster-Image.webp",

    ],

    [

        "url"      => "https://lipopotv.live/cheka.php",

        "name"     => "Cheka Plus TV",

        "category" => "MUSIC",

        "image"    => "https://i.postimg.cc/T2Fqj5jf/1746270439707-Cheka-Plus-TV-poster-Image.webp",

    ],

    [

        "url"      => "https://lipopotv.live/zama.php",

        "name"     => "Zamaradi TV",

        "category" => "MUSIC",

        "image"    => "https://i.postimg.cc/0rgLy7wK/Zamaradi-TV-d7c13bcf55a3290fd85d8155f0888e85.png",

    ],

];

foreach ($lipopoList as $item) {

    $ch = lipopoChannel($item['url'], $item['name'], $item['category'], $item['image']);

    if ($ch) $lipopoChannels[] = $ch;

}

// ============================================================

// SEHEMU 2: ZIMOTV CHANNELS (9)

// ============================================================

$keyMap = [

    "sports 1" => ["name" => "AzamSports 1 HD", "key" => "c31df1600afc33799ecac543331803f2:dd2101530e222f545997d4c553787f85", "category" => "NBC PREMIER LEAGUE"],

    "sports 2" => ["name" => "AzamSports 2 HD", "key" => "739e7499125b31cc9948da8057b84cf9:1b7d44d798c351acc02f33ddfbb7682a", "category" => "NBC PREMIER LEAGUE"],

    "sports 3" => ["name" => "AzamSports 3 HD", "key" => "2f12d7b889de381a9fb5326ca3aa166d:51c2d733a54306fdf89acd4c9d4f6005", "category" => "NBC PREMIER LEAGUE"],

    "sports 4" => ["name" => "AzamSports 4 HD", "key" => "1606cddebd3c36308ec5072350fb790a:04ece212a9201531afdd91c6f468e0b3", "category" => "NBC PREMIER LEAGUE"],

    "azm two"  => ["name" => "Azam Two",        "key" => "3b92b644635f3bad9f7d09ded676ec47:d012a9d5834f69be1313d4864d150a5f", "category" => "TAMTHILIYA"],

    "sinema"   => ["name" => "Sinema Zetu",     "key" => "d628ae37a8f0336b970f250d9699461e:1194c3d60bb494aabe9114ca46c2738e", "category" => "TAMTHILIYA"],

    "utv"      => ["name" => "UTV",             "key" => "31b8fc6289fe3ca698588a59d845160c:f8c4e73f419cb80db3bdf4a974e31894", "category" => "OTHER CHANNELS"],

    "wasafi"   => ["name" => "Wasafi TV",       "key" => "8714fe102679348e9c76cfd315dacaa0:a8b86ceda831061c13c7c4c67bd77f8e", "category" => "MUSIC"],

    "zbc"      => ["name" => "ZBC",             "key" => "2d60429f7d043a638beb7349ae25f008:f9b38900f31ce549425df1de2ea28f9d", "category" => "OTHER CHANNELS"],

];

$zimoUrl  = "https://zimotv.com/mb/api/get-channels.php?category=local%20channels";

$zimoData = json_decode(fetchUrl($zimoUrl), true);

$zimoResult = [];

if (isset($zimoData['channels'])) {

    foreach ($zimoData['channels'] as $ch) {

        $title = strtolower(trim($ch['title']));

        foreach ($keyMap as $match => $info) {

            if (strpos($title, $match) !== false) {

                $zimoResult[] = [

                    "name"     => $info['name'],

                    "category" => $info['category'],

                    "url"      => $ch['url'],

                    "image"    => $ch['logo'] ?? null,

                    "key"      => $info['key'],

                    "type"     => str_ends_with($ch['url'], ".m3u8") ? "hls" : "dash",

                ];

                break;

            }

        }

    }

}

// ============================================================

// UNGANISHA ZOTE

// ============================================================

$finalChannels = array_merge($lipopoChannels, $zimoResult);

$json = json_encode([

    "success"    => true,

    "count"      => count($finalChannels),

    "fetched_at" => date("Y-m-d H:i:s"),

    "channels"   => $finalChannels,

], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

if ($download) {

    header("Content-Length: " . strlen($json));

}

echo $json;