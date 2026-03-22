<?php
// ═══════════════════════════════════════════════
//  JAYNES MAX TV — auth.php
//  Backend ya usajili, kuingia, Google OAuth
//  Actions: register | login | logout |
//           google_redirect | google_callback |
//           reset_password | me
// ═══════════════════════════════════════════════

require_once 'config.php';

// ── CORS na Anti-bot bypass — lazima iwe KABLA ya session ───────
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With, apikey');
header('Access-Control-Max-Age: 3600');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

session_start();

$action = $_GET['action'] ?? ($_POST['action'] ?? 'login');
}

// ── Soma JSON body kama POST ──────────────────────────────────
$body = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $body = json_decode($raw, true) ?? [];
    } else {
        $body = $_POST;
    }
}

// ═══════════════════════════════════════════════════════════════
//  ROUTER
// ═══════════════════════════════════════════════════════════════
switch ($action) {

    // ── 1. REGISTER ─────────────────────────────────────────────
    case 'register':
        $email    = trim($body['email']    ?? '');
        $password = trim($body['password'] ?? '');
        $name     = trim($body['name']     ?? '');

        if (!$email || !$password || !$name) {
            jsonResponse(['success' => false, 'error' => 'Jaza sehemu zote: jina, email, nywila'], 400);
        }
        if (!isValidEmail($email)) {
            jsonResponse(['success' => false, 'error' => 'Email si sahihi'], 400);
        }
        if (strlen($password) < 6) {
            jsonResponse(['success' => false, 'error' => 'Nywila lazima iwe herufi 6 au zaidi'], 400);
        }

        // Supabase signup — tumia service key ili kuepuka email confirmation
        $signupUrl = SUPABASE_URL . '/auth/v1/admin/users';
        $ch = curl_init($signupUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => json_encode([
                'email'            => $email,
                'password'         => $password,
                'email_confirm'    => true,   // Thibitisha moja kwa moja — bila email
                'user_metadata'    => ['full_name' => $name],
            ]),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'apikey: ' . SUPABASE_SERVICE_KEY,
                'Authorization: Bearer ' . SUPABASE_SERVICE_KEY,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 10,
        ]);
        $signupResp = json_decode(curl_exec($ch), true);
        $signupCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($signupCode !== 200 && $signupCode !== 201) {
            $msg = $signupResp['message'] ?? $signupResp['msg'] ?? 'Usajili umeshindwa. Jaribu tena.';
            if (str_contains(strtolower($msg), 'already registered') || str_contains(strtolower($msg), 'already exists')) {
                $msg = 'Email hii tayari imesajiliwa. Ingia badala yake.';
            }
            jsonResponse(['success' => false, 'error' => $msg], 400);
        }

        $user = $signupResp;
        $uid  = $user['id'] ?? '';

        // ── Angalia kama user hii imesajiliwa kabla ya leo ──────────
        // Kama "created_at" ni siku iliyopita → user wa zamani → hakuna trial
        $userCreatedAt = $user['created_at'] ?? date('c');
        $registeredToday = date('Y-m-d', strtotime($userCreatedAt)) === date('Y-m-d');

        // Wape trial dakika 30 tu kama wamesajiliwa LEO
        $trialEnd = $registeredToday
            ? date('c', strtotime('+30 minutes'))
            : date('c', strtotime('-1 second')); // Imekwisha mara moja kwa watumiaji wa zamani
        if ($uid) {
            supabaseRequest('/rest/v1/profiles', 'POST', [
                'id'        => $uid,
                'email'     => $email,
                'full_name' => $name,
                'plan'      => 'trial',
                'trial_end' => $trialEnd,
                'created_at'=> date('c'),
            ], true);
        }

        // Login mara moja baada ya usajili
        $loginRes = supabaseAuth('/token?grant_type=password', [
            'email'    => $email,
            'password' => $password,
        ]);
        $token   = $loginRes['data']['access_token']  ?? '';
        $refresh = $loginRes['data']['refresh_token'] ?? '';

        jsonResponse([
            'success'   => true,
            'message'   => $registeredToday
                ? 'Umesajiliwa! Karibu JAYNES MAX TV. Una dakika 30 za majaribio.'
                : 'Karibu tena! Tafadhali lipia subscription ili kutazama channels.',
            'user'      => [
                'id'                => $uid,
                'email'             => $email,
                'name'              => $name,
                'plan'              => 'trial',
                'trial_end'         => $trialEnd,
                'sub_end'           => '',
                'created_at'        => date('c'),
                'registered_today'  => $registeredToday,
            ],
            'token'         => $token,
            'refresh_token' => $refresh,
        ]);

    // ── 2. LOGIN ─────────────────────────────────────────────────
    case 'login':
        $email    = trim($body['email']    ?? '');
        $password = trim($body['password'] ?? '');

        if (!$email || !$password) {
            jsonResponse(['success' => false, 'error' => 'Weka email na nywila'], 400);
        }
        if (!isValidEmail($email)) {
            jsonResponse(['success' => false, 'error' => 'Email si sahihi'], 400);
        }

        $res = supabaseAuth('/token?grant_type=password', [
            'email'    => $email,
            'password' => $password,
        ]);

        if (!$res['success'] || empty($res['data']['access_token'])) {
            $msg = $res['data']['error_description']
                ?? $res['data']['msg']
                ?? 'Email au nywila si sahihi';
            if (str_contains(strtolower($msg), 'invalid') || str_contains(strtolower($msg), 'wrong')) {
                $msg = 'Email au nywila si sahihi';
            }
            jsonResponse(['success' => false, 'error' => $msg], 401);
        }

        $user  = $res['data']['user'] ?? [];
        $token = $res['data']['access_token'];
        $refresh = $res['data']['refresh_token'] ?? '';
        $name  = $user['user_metadata']['full_name'] ?? explode('@', $email)[0];
        $uid   = $user['id'] ?? '';

        // Chukua plan/trial_end/sub_end kutoka profiles table
        $profileRes = supabaseRequest('/rest/v1/profiles?id=eq.' . $uid . '&select=plan,trial_end,sub_end,created_at', 'GET', [], true);
        $profile    = $profileRes['data'][0] ?? [];

        // Hifadhi session PHP
        $_SESSION['jaynes_uid']   = $uid;
        $_SESSION['jaynes_email'] = $email;
        $_SESSION['jaynes_name']  = $name;
        $_SESSION['jaynes_token'] = $token;

        jsonResponse([
            'success' => true,
            'user'    => [
                'id'         => $uid,
                'email'      => $email,
                'name'       => $name,
                'plan'       => $profile['plan']      ?? $user['user_metadata']['plan'] ?? 'free',
                'trial_end'  => $profile['trial_end'] ?? '',
                'sub_end'    => $profile['sub_end']   ?? '',
                'created_at' => $profile['created_at'] ?? $user['created_at'] ?? '',
            ],
            'token'        => $token,
            'refresh_token'=> $refresh,
        ]);

    // ── 3. LOGOUT ────────────────────────────────────────────────
    case 'logout':
        $_SESSION = [];
        session_destroy();
        jsonResponse(['success' => true, 'message' => 'Umetoka. Kwa heri!']);

    // ── 4. GET CURRENT USER ───────────────────────────────────────
    case 'me':
        $token = getBearerToken();
        if (!$token) {
            jsonResponse(['success' => false, 'error' => 'Hujaingia'], 401);
        }
        // Verify token na Supabase
        $ch = curl_init(SUPABASE_URL . '/auth/v1/user');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_KEY,
                'Authorization: Bearer ' . $token,
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT        => 8,
        ]);
        $res  = json_decode(curl_exec($ch), true);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($code !== 200 || empty($res['id'])) {
            jsonResponse(['success' => false, 'error' => 'Token batili au imeisha'], 401);
        }

        $name = $res['user_metadata']['full_name']
             ?? $res['user_metadata']['name']
             ?? explode('@', $res['email'])[0];

        // Chukua plan/trial_end/sub_end kutoka profiles table
        $profileRes = supabaseRequest('/rest/v1/profiles?id=eq.' . $res['id'] . '&select=plan,trial_end,sub_end,created_at', 'GET', [], true);
        $profile    = $profileRes['data'][0] ?? [];

        jsonResponse([
            'success' => true,
            'user'    => [
                'id'         => $res['id'],
                'email'      => $res['email'],
                'name'       => $name,
                'plan'       => $profile['plan']      ?? $res['user_metadata']['plan'] ?? 'free',
                'trial_end'  => $profile['trial_end'] ?? '',
                'sub_end'    => $profile['sub_end']   ?? '',
                'created_at' => $profile['created_at'] ?? $res['created_at'] ?? '',
                'provider'   => $res['app_metadata']['provider'] ?? 'email',
            ],
        ]);

    // ── 5. RESET PASSWORD ─────────────────────────────────────────
    case 'reset_password':
        $email = trim($body['email'] ?? '');
        if (!$email || !isValidEmail($email)) {
            jsonResponse(['success' => false, 'error' => 'Weka email sahihi'], 400);
        }
        $res = supabaseAuth('/recover', ['email' => $email]);
        // Daima jibu "ok" hata kama email haipo (security)
        jsonResponse(['success' => true, 'message' => 'Kama email ipo, utapokea barua ya kubadilisha nywila.']);

    // ── 6. GOOGLE REDIRECT ────────────────────────────────────────
    case 'google_redirect':
        // Tumia Supabase OAuth redirect → oauth_callback.html (HTML file, si PHP)
        $supabaseOAuthUrl = SUPABASE_URL . '/auth/v1/authorize'
            . '?provider=google'
            . '&redirect_to=' . urlencode(SITE_URL . '/oauth_callback.html');
        header('Location: ' . $supabaseOAuthUrl);
        exit;

    // ── 7. GOOGLE CALLBACK ────────────────────────────────────────
    case 'google_callback':
        // Supabase inatumiwa token kwenye fragment (#) — JS lazima ishughulikie
        // Hapa tunarudisha HTML inayosoma fragment na kutuma kwa server
        ?>
        <!DOCTYPE html>
        <html><head><meta charset="UTF-8"><title>Inaingia...</title>
        <link rel="stylesheet" href="style.css">
        <style>
        body{display:flex;align-items:center;justify-content:center;min-height:100vh;background:var(--bg);}
        .box{text-align:center;color:var(--text);}
        .spinner{width:44px;height:44px;border:3px solid rgba(0,212,255,0.1);border-top-color:var(--accent);border-radius:50%;animation:spin 0.75s linear infinite;margin:0 auto 16px;}
        @keyframes spin{to{transform:rotate(360deg)}}
        p{color:var(--muted);font-size:14px;}
        </style>
        </head>
        <body>
        <div class="box">
          <div class="spinner"></div>
          <p>Inaingia kwa Google...</p>
        </div>
        <script>
        // Supabase inapeleka token kwenye URL fragment
        const hash = window.location.hash.substring(1);
        const params = new URLSearchParams(hash);
        const accessToken  = params.get('access_token');
        const refreshToken = params.get('refresh_token');

        if (accessToken) {
          // Thibitisha token na upate maelezo ya mtumiaji
          fetch('<?= SUPABASE_URL ?>/auth/v1/user', {
            headers: {
              'apikey': '<?= SUPABASE_KEY ?>',
              'Authorization': 'Bearer ' + accessToken
            }
          })
          .then(r => r.json())
          .then(user => {
            if (user.id) {
              const name = user.user_metadata?.full_name || user.user_metadata?.name || user.email.split('@')[0];
              // Hifadhi kwenye localStorage kama auth ya kawaida
              localStorage.setItem('jaynesEmail',        user.email);
              localStorage.setItem('jaynesUser',         'ok');
              localStorage.setItem('jaynesName',         name);
              localStorage.setItem('jaynesUid',          user.id);
              localStorage.setItem('jaynesToken',        accessToken);
              localStorage.setItem('jaynesRefreshToken', refreshToken || '');
              localStorage.setItem('jaynesProvider',     'google');
              window.location.href = 'home.html';
            } else {
              document.querySelector('p').textContent = 'Imeshindwa. Rudi na ujaribu tena.';
              setTimeout(() => window.location.href = 'login.html', 2000);
            }
          })
          .catch(() => {
            document.querySelector('p').textContent = 'Tatizo la mtandao.';
            setTimeout(() => window.location.href = 'login.html', 2000);
          });
        } else {
          // Kama token haipo kwenye fragment, angalia query string
          const qp = new URLSearchParams(window.location.search);
          if (qp.get('error')) {
            document.querySelector('p').textContent = 'Kuingia kwa Google kumeshindwa: ' + qp.get('error_description');
            setTimeout(() => window.location.href = 'login.html', 3000);
          } else {
            window.location.href = 'login.html';
          }
        }
        </script>
        </body></html>
        <?php
        exit;

    default:
        jsonResponse(['success' => false, 'error' => 'Action haijulikani: ' . htmlspecialchars($action)], 400);
}

// ── Helper: Toa Bearer token kutoka header ──────────────────────
function getBearerToken(): string {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) {
        return substr($auth, 7);
    }
    // Pia angalia localStorage token iliyotumwa kama POST
    return $_POST['token'] ?? '';
}
