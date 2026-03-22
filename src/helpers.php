<?php
/**
 * JAYNES MAX TV — helpers.php
 * Supabase REST + Auth helpers
 */

// ── Supabase REST request ─────────────────────────────────────────
function sb(string $endpoint, string $method = 'GET', array $body = [], bool $service = false): array {
    $key = $service ? SUPABASE_SERVICE_KEY : SUPABASE_KEY;
    $url = SUPABASE_URL . $endpoint;
    $ch  = curl_init($url);

    $headers = [
        'Content-Type: application/json',
        'apikey: ' . $key,
        'Authorization: Bearer ' . $key,
    ];

    if (in_array($method, ['POST', 'PATCH', 'PUT'])) {
        $headers[] = 'Prefer: return=representation';
    }

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST  => $method,
        CURLOPT_HTTPHEADER     => $headers,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_CONNECTTIMEOUT => 10,
    ]);

    if (!empty($body)) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    }

    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err      = curl_error($ch);
    curl_close($ch);

    if ($err) {
        return ['ok' => false, 'data' => null, 'code' => 0, 'error' => $err];
    }

    $data = json_decode($response, true);
    return ['ok' => $code >= 200 && $code < 300, 'data' => $data, 'code' => $code];
}

// ── Supabase Auth ─────────────────────────────────────────────────
function sbAuth(string $endpoint, array $body): array {
    $url = SUPABASE_URL . '/auth/v1' . $endpoint;
    $ch  = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => json_encode($body),
        CURLOPT_HTTPHEADER     => [
            'Content-Type: application/json',
            'apikey: ' . SUPABASE_KEY,
        ],
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT        => 15,
    ]);
    $response = curl_exec($ch);
    $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($response, true);
    return ['ok' => $code >= 200 && $code < 300, 'data' => $data, 'code' => $code];
}

// ── JSON responses ────────────────────────────────────────────────
function ok(array $data = [], int $code = 200): void {
    http_response_code($code);
    echo json_encode(array_merge(['success' => true], $data), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function fail(string $error, int $code = 400): void {
    http_response_code($code);
    echo json_encode(['success' => false, 'error' => $error], JSON_UNESCAPED_UNICODE);
    exit;
}

// ── Request helpers ───────────────────────────────────────────────
function getBody(): array {
    $raw = file_get_contents('php://input');
    return json_decode($raw, true) ?? [];
}

function getParam(string $key, string $default = ''): string {
    return trim($_GET[$key] ?? $_POST[$key] ?? $default);
}

function requireAdmin(): void {
    $body = getBody();
    $key  = $body['admin_key'] ?? getParam('admin_key');
    if ($key !== ADMIN_KEY) {
        fail('Ruhusa inakataliwa — admin key batili', 403);
    }
}

function requireMethod(string $method): void {
    if ($_SERVER['REQUEST_METHOD'] !== strtoupper($method)) {
        fail('Method ' . $_SERVER['REQUEST_METHOD'] . ' hairuhusiwi', 405);
    }
}
