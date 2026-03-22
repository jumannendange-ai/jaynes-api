<?php
/**
 * JAYNES MAX TV — test_notify.php
 * Fungua kwenye browser → itatuma test notification moja kwa moja
 * FUTA baada ya kutumia!
 */

define('OS_APP_ID',   '10360777-3ada-4145-b83f-00eb0312a53f');
define('OS_REST_KEY', 'os_v2_app_ca3ao5z23jaulob7advqgevfh4qctlprzdauupekggukcgwmz5glfzdu6lkvnkzjeuno3cuuqow7fklo3fehp2puu52sr7sroo63hwy');
define('APP_URL',     'https://dde.ct.ws');

// ── Tuma notification ────────────────────────────────────────────
function sendTest(string $title, string $msg, string $url): array {
    $payload = [
        'app_id'               => OS_APP_ID,
        'included_segments'    => ['All'],
        'headings'             => ['en' => $title, 'sw' => $title],
        'contents'             => ['en' => $msg,   'sw' => $msg],
        'url'                  => $url,
        'small_icon'           => 'ic_launcher',
        'android_accent_color' => 'FF00D4FF',
        'priority'             => 10,
        'ttl'                  => 3600,
    ];

    $ch = curl_init('https://onesignal.com/api/v1/notifications');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_POSTFIELDS     => json_encode($payload),
        CURLOPT_HTTPHEADER     => [
            'Authorization: Key ' . OS_REST_KEY,
            'Content-Type: application/json',
        ],
    ]);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    $r = json_decode($res ?? '{}', true);
    return [
        'http_code'  => $code,
        'curl_error' => $err,
        'response'   => $r,
        'success'    => !empty($r['id']),
        'notif_id'   => $r['id'] ?? null,
        'recipients' => $r['recipients'] ?? 0,
        'errors'     => $r['errors'] ?? [],
    ];
}

// Gundua aina ya test
$type = $_GET['type'] ?? 'general';

$tests = [
    'general' => [
        'title' => '📺 JAYNES MAX TV — Test',
        'msg'   => '✅ Notifications zinafanya kazi vizuri! Karibu kutazama LIVE.',
        'url'   => APP_URL . '/home.html',
    ],
    'goal' => [
        'title' => '🚨 GOOOAL! Simba SC',
        'msg'   => "⚽ Simba SC 1 - 0 Young Africans\n⏱ Dakika 35' | NBC Premier League\nBonyeza kutazama LIVE!",
        'url'   => APP_URL . '/live.php',
    ],
    'kickoff' => [
        'title' => '🔴 LIVE: Simba SC vs Young Africans',
        'msg'   => "⚽ Mchezo unaanza sasa!\nSimba SC vs Young Africans\n🏆 NBC Premier League\nBonyeza kutazama LIVE!",
        'url'   => APP_URL . '/live.php',
    ],
    'warning' => [
        'title' => '⚽ Dakika 30 — Simba SC vs Young Africans',
        'msg'   => "🕐 Mchezo unakaribia kuanza!\nSimba SC vs Young Africans\n⏰ 19:00 | NBC Premier League\n📺 Inaweza kutazamwa kwenye JAYNES MAX TV!",
        'url'   => APP_URL . '/schedule.php',
    ],
    'ht' => [
        'title' => '⏸ NUSU YA MCHEZO',
        'msg'   => "🟡 Simba SC 1 - 0 Young Africans\nNusu ya kwanza imekwisha\n🏆 NBC Premier League",
        'url'   => APP_URL . '/live.php',
    ],
    'ft' => [
        'title' => '🏁 MWISHO: Simba SC vs Young Africans',
        'msg'   => "📊 Matokeo ya Mwisho:\nSimba SC 2 - 1 Young Africans\n🏆 NBC Premier League",
        'url'   => APP_URL . '/live.php',
    ],
];

$selected = $tests[$type] ?? $tests['general'];
$result   = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['send'])) {
    $result = sendTest($selected['title'], $selected['msg'], $selected['url']);
}
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Test Notifications — JAYNES MAX TV</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<style>
*{margin:0;padding:0;box-sizing:border-box}
body{background:#060610;color:#e2e8f0;font-family:'Segoe UI',sans-serif;padding:20px;min-height:100vh}
h1{font-size:18px;font-weight:700;margin-bottom:6px;color:#00d4ff;letter-spacing:1px}
.sub{font-size:12px;color:#7788aa;margin-bottom:24px}
.card{background:#0d0d22;border:1px solid rgba(255,255,255,.08);border-radius:14px;padding:16px;margin-bottom:14px}
.card h3{font-size:13px;font-weight:700;margin-bottom:12px;color:#fff;letter-spacing:.5px}
.type-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px}
.type-btn{padding:10px 12px;border-radius:10px;border:1px solid rgba(255,255,255,.1);background:rgba(255,255,255,.04);color:#7788aa;font-size:12px;font-weight:600;text-decoration:none;text-align:center;transition:all .2s;display:flex;flex-direction:column;align-items:center;gap:4px}
.type-btn:hover,.type-btn.active{background:rgba(0,212,255,.12);border-color:rgba(0,212,255,.3);color:#00d4ff}
.type-btn .icon{font-size:20px}
.preview{background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.06);border-radius:10px;padding:14px;margin:12px 0}
.preview .p-title{font-size:14px;font-weight:700;margin-bottom:6px;color:#fff}
.preview .p-msg{font-size:12px;color:#7788aa;line-height:1.7;white-space:pre-wrap}
.send-btn{width:100%;padding:14px;border:none;border-radius:12px;background:linear-gradient(135deg,#00d4ff,#0088bb);color:#000;font-size:14px;font-weight:700;cursor:pointer;letter-spacing:.5px;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px}
.send-btn:hover{transform:translateY(-1px);box-shadow:0 4px 20px rgba(0,212,255,.3)}
.send-btn:active{transform:translateY(0)}

/* Result */
.result{border-radius:12px;padding:16px;margin-top:14px;border:1px solid}
.result.ok{background:rgba(0,255,136,.08);border-color:rgba(0,255,136,.2)}
.result.fail{background:rgba(255,68,102,.08);border-color:rgba(255,68,102,.2)}
.result .r-icon{font-size:32px;margin-bottom:8px}
.result .r-title{font-size:15px;font-weight:700;margin-bottom:4px}
.result .r-title.ok{color:#00ff88}
.result .r-title.fail{color:#ff4466}
.result .r-detail{font-size:11px;color:#7788aa;margin-top:8px;font-family:monospace;background:rgba(0,0,0,.3);border-radius:8px;padding:10px;word-break:break-all;line-height:1.8}
.r-row{display:flex;justify-content:space-between;font-size:12px;margin-bottom:4px}
.r-row .lbl{color:#7788aa}
.r-row .val{font-weight:700;font-family:monospace}
.r-row .val.ok{color:#00ff88}
.r-row .val.fail{color:#ff4466}

.warn{background:rgba(255,165,0,.08);border:1px solid rgba(255,165,0,.2);border-radius:10px;padding:12px;font-size:11px;color:#ffaa00;margin-top:14px;line-height:1.7}
.warn i{margin-right:6px}
</style>
</head>
<body>

<h1><i class="fa fa-bell"></i> TEST NOTIFICATIONS</h1>
<p class="sub">JAYNES MAX TV · OneSignal Test Panel</p>

<!-- Chagua aina ya notification -->
<div class="card">
  <h3>📋 CHAGUA AINA YA NOTIFICATION</h3>
  <div class="type-grid">
    <a href="?type=general" class="type-btn <?=$type==='general'?'active':''?>">
      <span class="icon">📺</span>General
    </a>
    <a href="?type=warning" class="type-btn <?=$type==='warning'?'active':''?>">
      <span class="icon">⚽</span>Onyo (dk 30)
    </a>
    <a href="?type=kickoff" class="type-btn <?=$type==='kickoff'?'active':''?>">
      <span class="icon">🔴</span>Kickoff
    </a>
    <a href="?type=goal" class="type-btn <?=$type==='goal'?'active':''?>">
      <span class="icon">🚨</span>Goli
    </a>
    <a href="?type=ht" class="type-btn <?=$type==='ht'?'active':''?>">
      <span class="icon">⏸</span>Nusu (HT)
    </a>
    <a href="?type=ft" class="type-btn <?=$type==='ft'?'active':''?>">
      <span class="icon">🏁</span>Mwisho (FT)
    </a>
  </div>
</div>

<!-- Preview -->
<div class="card">
  <h3>👁 PREVIEW YA NOTIFICATION</h3>
  <div class="preview">
    <div class="p-title"><?=htmlspecialchars($selected['title'])?></div>
    <div class="p-msg"><?=htmlspecialchars($selected['msg'])?></div>
  </div>

  <!-- Send button -->
  <form method="POST" action="?type=<?=$type?>">
    <button type="submit" class="send-btn">
      <i class="fa fa-paper-plane"></i> TUMA KWA WATUMIAJI WOTE
    </button>
  </form>

  <!-- Result -->
  <?php if ($result !== null): ?>
  <div class="result <?=$result['success']?'ok':'fail'?>">
    <div class="r-icon"><?=$result['success']?'✅':'❌'?></div>
    <div class="r-title <?=$result['success']?'ok':'fail'?>">
      <?=$result['success']?'Notification imetumwa!':'Imeshindwa kutuma'?>
    </div>

    <div style="margin-top:10px">
      <div class="r-row">
        <span class="lbl">HTTP Code</span>
        <span class="val <?=$result['http_code']===200?'ok':'fail'?>"><?=$result['http_code']?></span>
      </div>
      <div class="r-row">
        <span class="lbl">Notification ID</span>
        <span class="val <?=$result['notif_id']?'ok':'fail'?>"><?=$result['notif_id']??'—'?></span>
      </div>
      <div class="r-row">
        <span class="lbl">Wapokeaji</span>
        <span class="val ok"><?=$result['recipients']?> watumiaji</span>
      </div>
      <?php if (!empty($result['errors'])): ?>
      <div class="r-row">
        <span class="lbl">Errors</span>
        <span class="val fail"><?=htmlspecialchars(implode(', ', (array)$result['errors']))?></span>
      </div>
      <?php endif; ?>
      <?php if ($result['curl_error']): ?>
      <div class="r-row">
        <span class="lbl">cURL Error</span>
        <span class="val fail"><?=htmlspecialchars($result['curl_error'])?></span>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!$result['success']): ?>
    <div class="r-detail"><?=htmlspecialchars(json_encode($result['response'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))?></div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
</div>

<div class="warn">
  <i class="fa fa-triangle-exclamation"></i>
  <strong>Kumbuka:</strong> Futa au linda file hii baada ya kutumia — ina REST key ya OneSignal.
  Isiachiwe wazi kwenye server.
</div>

</body>
</html>
