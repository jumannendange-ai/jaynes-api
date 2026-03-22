<?php
/**
 * admin_proxy.php — Supabase proxy kwa admin actions
 * GET: pakia data fresh
 * POST: fanya PATCH operations
 */
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Pakia data fresh
    $path = $_GET['path'] ?? '';
    if (!$path) { echo json_encode([]); exit; }
    $r = supabaseRequest($path, 'GET', [], true);
    echo json_encode($r['success'] ? ($r['data'] ?? []) : []);

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input  = json_decode(file_get_contents('php://input'), true);
    $path   = $input['path'] ?? '';
    $body   = $input['body'] ?? [];
    $method = strtoupper($input['method'] ?? 'PATCH');

    if (!$path) { http_response_code(400); echo json_encode(['error'=>'path inahitajika']); exit; }

    // UPSERT support kwa settings table
    if ($method === 'UPSERT' && isset($input['upsert_data'])) {
        $upsertData = $input['upsert_data'];
        // Kwanza jaribu PATCH
        $r = supabaseRequest($path, 'PATCH', $body, true);
        // Kama record haipo (404 au 0 rows), fanya INSERT
        if (!$r['success'] || $r['code'] === 404) {
            $insertPath = preg_replace('/\?.*/', '', $path); // ondoa filter
            $r = supabaseRequest($insertPath, 'POST', array_merge($upsertData, $body), true);
        }
        echo json_encode(['ok' => $r['success'], 'code' => $r['code']]);
        exit;
    }

    // Kwa settings table: kama PATCH inarudisha 0 rows, fanya INSERT
    $r = supabaseRequest($path, $method === 'PATCH' ? 'PATCH' : 'POST', $body, true);

    // Angalia kama PATCH iliathiri rows (settings inaweza kuwa haikuundwa bado)
    if ($method === 'PATCH' && isset($body['value']) && strpos($path, 'settings') !== false) {
        // Jaribu kupata data baada ya PATCH
        $checkPath = preg_replace('/\?.*/', '', $path);
        $filter    = '';
        if (preg_match('/\?(.*)/', $path, $m)) $filter = '?' . $m[1];
        $check = supabaseRequest($checkPath . $filter . '&select=key', 'GET', [], true);
        $rows  = $check['data'] ?? [];
        if (empty($rows)) {
            // Row haipo — fanya INSERT
            if (preg_match('/key=eq\.(\w+)/', $path, $km)) {
                $key = $km[1];
                $insertBody = array_merge(['key' => $key], $body);
                $r = supabaseRequest($checkPath, 'POST', $insertBody, true);
            }
        }
    }

    echo json_encode(['ok' => $r['success'], 'code' => $r['code']]);
}
