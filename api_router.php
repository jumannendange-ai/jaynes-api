<?php
/**
 * JAYNES MAX TV API — Router
 * https://jaynes-api.onrender.com
 */

// CORS — ruhusu app yote
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, apikey');
header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

require_once __DIR__ . '/../src/config.php';
require_once __DIR__ . '/../src/helpers.php';

// ── Router ────────────────────────────────────────────────────────
$uri    = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri    = rtrim($uri, '/');
$method = $_SERVER['REQUEST_METHOD'];

// Remove leading slash
$route = ltrim($uri, '/');

switch ($route) {

    case '':
    case 'health':
        echo json_encode([
            'app'     => 'JAYNES MAX TV API',
            'version' => '4.0',
            'status'  => 'online',
            'time'    => date('Y-m-d H:i:s T'),
            'routes'  => [
                'GET  /channels'           => 'Channels zote ?source=azam|nbc|local|global|all',
                'GET  /channels?source=azam' => 'Azam channels tu',
                'GET  /channels?source=nbc'  => 'NBC channels tu',
                'GET  /channels?source=local'=> 'Local/Server2 channels',
                'GET  /channels?source=global'=> 'Global channels',
                'GET  /channels?category=sports' => 'Filter by category',
                'GET  /categories'         => 'Makundi ya channels',
                'GET  /subscription/check' => 'Angalia subscription ?email=',
                'POST /subscription/confirm'=> 'Admin: thibitisha malipo',
                'POST /payment/submit'     => 'Wasilisha malipo',
                'GET  /maintenance'        => 'Hali ya matengenezo',
                'POST /maintenance/toggle' => 'Admin: washa/zima maintenance',
                'GET  /stream'             => 'Proxy HLS/MPD ?url=&type=',
            ]
        ], JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        break;

    case 'channels':
        require __DIR__ . '/../src/routes/channels.php';
        break;

    case 'categories':
        require __DIR__ . '/../src/routes/categories.php';
        break;

    case 'subscription/check':
        require __DIR__ . '/../src/routes/subscription_check.php';
        break;

    case 'subscription/confirm':
        require __DIR__ . '/../src/routes/subscription_confirm.php';
        break;

    case 'payment/submit':
        require __DIR__ . '/../src/routes/payment_submit.php';
        break;

    case 'maintenance':
        require __DIR__ . '/../src/routes/maintenance_get.php';
        break;

    case 'maintenance/toggle':
        require __DIR__ . '/../src/routes/maintenance_toggle.php';
        break;

    case 'stream':
        require __DIR__ . '/../src/routes/stream.php';
        break;

    default:
        http_response_code(404);
        echo json_encode([
            'error'  => 'Route haipatikani',
            'route'  => $route,
            'hint'   => 'Angalia /health kwa orodha ya routes'
        ], JSON_UNESCAPED_UNICODE);
        break;
}
