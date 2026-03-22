<?php
/**
 * GET /channels?source=azam|nbc|global|local|all
 * GET /channels?category=sports|tamthiliya|habari|muziki
 *
 * Inarejesha channels kutoka vyanzo vyote vya site yako
 * Kila channel ina: id, title, logo, stream_url, stream_url_mpd,
 *                   category, source, is_live, is_free, clearkey
 */

requireMethod('GET');

$source   = strtolower(getParam('source',   'all'));
$category = strtolower(getParam('category', ''));
$q        = strtolower(getParam('q',        ''));

// ClearKey map — kutoka local2.php yako
$KEY_MAP = [
    'sports 1'  => ['kid'=>'c31df1600afc33799ecac543331803f2','key'=>'dd2101530e222f545997d4c553787f85','cat'=>'Sports'],
    'sports 2'  => ['kid'=>'739e7499125b31cc9948da8057b84cf9','key'=>'1b7d44d798c351acc02f33ddfbb7682a','cat'=>'Sports'],
    'sports 3'  => ['kid'=>'2f12d7b889de381a9fb5326ca3aa166d','key'=>'51c2d733a54306fdf89acd4c9d4f6005','cat'=>'Sports'],
    'sports 4'  => ['kid'=>'1606cddebd3c36308ec5072350fb790a','key'=>'04ece212a9201531afdd91c6f468e0b3','cat'=>'Sports'],
    'azm two'   => ['kid'=>'3b92b644635f3bad9f7d09ded676ec47','key'=>'d012a9d5834f69be1313d4864d150a5f','cat'=>'Tamthiliya'],
    'azam two'  => ['kid'=>'3b92b644635f3bad9f7d09ded676ec47','key'=>'d012a9d5834f69be1313d4864d150a5f','cat'=>'Tamthiliya'],
    'sinema'    => ['kid'=>'d628ae37a8f0336b970f250d9699461e','key'=>'1194c3d60bb494aabe9114ca46c2738e','cat'=>'Tamthiliya'],
    'wasafi'    => ['kid'=>'8714fe102679348e9c76cfd315dacaa0','key'=>'a8b86ceda831061c13c7c4c67bd77f8e','cat'=>'Muziki'],
    'utv'       => ['kid'=>'31b8fc6289fe3ca698588a59d845160c','key'=>'f8c4e73f419cb80db3bdf4a974e31894','cat'=>'Burudani'],
    'zbc'       => ['kid'=>'2d60429f7d043a638beb7349ae25f008','key'=>'f9b38900f31ce549425df1de2ea28f9d','cat'=>'Burudani'],
    'nbc'       => ['kid'=>'c31df1600afc33799ecac543331803f2','key'=>'dd2101530e222f545997d4c553787f85','cat'=>'Habari'],
];

$allChannels = [];

// ── 1. AZAM CHANNELS ─────────────────────────────────────────────
if ($source === 'all' || $source === 'azam') {
    $azam = fetchZimoChannels('azam');
    foreach ($azam as $ch) {
        $title = trim($ch['title'] ?? '');
        $tl    = strtolower($title);
        $ck    = findClearKey($tl, $KEY_MAP);
        $allChannels[] = buildChannel($ch, 'azam', $ck, isFreeChannel($tl));
    }
}

// ── 2. NBC CHANNELS ───────────────────────────────────────────────
if ($source === 'all' || $source === 'nbc') {
    $nbc = fetchZimoChannels('nbc');
    foreach ($nbc as $ch) {
        $title = trim($ch['title'] ?? '');
        $tl    = strtolower($title);
        $ck    = findClearKey($tl, $KEY_MAP);
        $allChannels[] = buildChannel($ch, 'nbc', $ck, isFreeChannel($tl));
    }
}

// ── 3. LOCAL CHANNELS (Server 2 / zimotv local) ───────────────────
if ($source === 'all' || $source === 'local' || $source === 'local2') {
    $local = fetchZimoChannels('local channels');
    foreach ($local as $ch) {
        $title = trim($ch['title'] ?? '');
        $tl    = strtolower($title);
        $ck    = findClearKey($tl, $KEY_MAP);
        $allChannels[] = buildChannel($ch, 'local', $ck, isFreeChannel($tl));
    }
}

// ── 4. GLOBAL CHANNELS ───────────────────────────────────────────
if ($source === 'all' || $source === 'global') {
    $global = fetchZimoChannels('international');
    foreach ($global as $ch) {
        $title = trim($ch['title'] ?? '');
        $tl    = strtolower($title);
        $ck    = findClearKey($tl, $KEY_MAP);
        $allChannels[] = buildChannel($ch, 'global', $ck, false);
    }
}

// ── 5. Filter by category ─────────────────────────────────────────
if (!empty($category)) {
    $allChannels = array_filter($allChannels, function($ch) use ($category) {
        return str_contains(strtolower($ch['category']), $category);
    });
    $allChannels = array_values($allChannels);
}

// ── 6. Filter by search query ─────────────────────────────────────
if (!empty($q)) {
    $allChannels = array_filter($allChannels, function($ch) use ($q) {
        return str_contains(strtolower($ch['title']), $q) ||
               str_contains(strtolower($ch['category']), $q);
    });
    $allChannels = array_values($allChannels);
}

// ── 7. Remove duplicates kwa title ───────────────────────────────
$seen = [];
$unique = [];
foreach ($allChannels as $ch) {
    $key = strtolower($ch['title']);
    if (!isset($seen[$key])) {
        $seen[$key] = true;
        $unique[] = $ch;
    }
}

ok([
    'channels' => $unique,
    'count'    => count($unique),
    'source'   => $source,
]);

// ════════════════════════════════════════════════════════════════
// HELPERS
// ════════════════════════════════════════════════════════════════

function fetchZimoChannels(string $category): array {
    $url = 'https://zimotv.com/mb/api/get-channels.php?category=' . urlencode($category);
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_HTTPHEADER     => [
            'User-Agent: Mozilla/5.0 (Linux; Android 13) Chrome/120',
            'Referer: https://zimotv.com/',
            'Origin: https://zimotv.com',
        ],
    ]);
    $res = curl_exec($ch);
    curl_close($ch);

    if (!$res) return [];
    $data = json_decode($res, true);
    return $data['channels'] ?? [];
}

function buildChannel(array $raw, string $source, array $ck, bool $isFree): array {
    $url = $raw['url'] ?? '';
    if (str_starts_with($url, '//')) $url = 'https:' . $url;

    $title    = trim($raw['title'] ?? $raw['name'] ?? '');
    $logo     = $raw['logo'] ?? $raw['image'] ?? '';
    $headers  = $raw['headers'] ?? [];

    // Stream type
    $isMpd = str_contains($url, '.mpd');
    $streamUrl    = $isMpd ? '' : $url;
    $streamUrlMpd = $isMpd ? $url : '';

    // Category
    $cat = guessCategory($title, $source, $ck['cat'] ?? '');

    // Sort order
    $sortMap = ['azam'=>1,'nbc'=>2,'local'=>3,'global'=>4];
    $sort = $sortMap[$source] ?? 5;

    return [
        'id'             => $raw['id'] ?? md5($title . $source),
        'title'          => $title,
        'name'           => $title,
        'logo'           => $logo,
        'stream_url'     => $streamUrl,
        'stream_url_mpd' => $streamUrlMpd,
        'category'       => $cat,
        'source'         => $source,
        'is_live'        => true,
        'is_free'        => $isFree,
        'headers'        => $headers,
        'clearkey'       => empty($ck) ? null : [
            'kid' => $ck['kid'] ?? '',
            'key' => $ck['key'] ?? '',
        ],
        '_sort' => $sort,
    ];
}

function findClearKey(string $titleLower, array $keyMap): array {
    foreach ($keyMap as $match => $info) {
        if (str_contains($titleLower, $match)) {
            return $info;
        }
    }
    return [];
}

function guessCategory(string $title, string $source, string $hint): string {
    $t = strtolower($title);

    if ($hint && $hint !== 'General') return $hint;

    if (preg_match('/sport|nbc.*league|premier|mechi|yanga|simba|azam.*sport/', $t)) return 'Sports';
    if (preg_match('/sinema|drama|tamthiliya|movie|film|kix/', $t))                  return 'Tamthiliya';
    if (preg_match('/wasafi|cheka|crown|muziki|music|zamaradi/', $t))                return 'Muziki';
    if (preg_match('/habari|news|tbc|ibc|iqra/', $t))                               return 'Habari';
    if (preg_match('/watoto|kids|cartoon/', $t))                                     return 'Watoto';

    return match($source) {
        'azam'   => 'Azam',
        'nbc'    => 'Habari',
        'local'  => 'Burudani',
        'global' => 'Global',
        default  => 'Burudani',
    };
}

function isFreeChannel(string $titleLower): bool {
    $freeList = ['tbc1','tbc 1','tb1','tbc2','tbc 2','zbc','azam one',
                 'azamone','azam 1','wasafi','wasafi tv'];
    foreach ($freeList as $free) {
        if (str_contains($titleLower, $free)) return true;
    }
    return false;
}
