<?php
define('SUPABASE_URL',         'https://dablnrggyfcddmdeiqxi.supabase.co');
define('SUPABASE_KEY',         'sb_publishable_d8mzJ3iulCU7YdlV_lrdQw_32pOzDXc');
define('SUPABASE_SERVICE_KEY', 'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2');
define('SITE_URL',             getenv('SITE_URL') ?: 'https://jaynes-api.onrender.com');
define('JWT_SECRET',           'JaynesMaxTV@2025!SecretKey#Secure');

function supabaseRequest(string $endpoint, string $method = 'GET', array $body = [], bool $useServiceKey = false): array {
    $key = $useServiceKey ? SUPABASE_SERVICE_KEY : SUPABASE_KEY;
    $url = SUPABASE_URL . $endpoint;
    $ch = curl_init($url);
    $headers = ['Content-Type: application/json', 'apikey: ' . $key, 'Authorization: Bearer ' . $key];
    if (in_array($method, ['POST','PATCH','PUT'])) $headers[] = 'Prefer: return=representation';
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_CUSTOMREQUEST => $method,
        CURLOPT_HTTPHEADER => $headers, CURLOPT_SSL_VERIFYPEER => false, CURLOPT_TIMEOUT => 10,
    ]);
    if (!empty($body)) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    $data = json_decode($response, true);
    return ['success' => $httpCode >= 200 && $httpCode < 300, 'data' => $data, 'code' => $httpCode];
}

function supabaseAuth(string $endpoint, array $body): array {
    $url = SUPABASE_URL . '/auth/v1' . $endpoint;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($body),
        CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'apikey: ' . SUPABASE_KEY],
        CURLOPT_SSL_VERIFYPEER => false, CURLOPT_TIMEOUT => 10,
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return ['success' => $httpCode >= 200 && $httpCode < 300, 'data' => json_decode($response, true)];
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
