<?php

// ── Fetch kutoka nbc.php (local API yetu) ─────────────────────
$apiUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['REQUEST_URI']) . "/nbc.php";

$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_SSL_VERIFYPEER => false,
    CURLOPT_TIMEOUT        => 10,
]);
$res = curl_exec($ch);
curl_close($ch);

$data     = json_decode($res, true);
$channels = $data['channels'] ?? [];
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — NBC Live</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
/* variables from style.css */

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Outfit', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  padding-top: 100px;
  padding-bottom: 80px;
  background-image: radial-gradient(ellipse at 50% 0%, rgba(255,215,0,0.04) 0%, transparent 50%);
}

.topbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  background: rgba(6,6,16,0.97);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  padding: 10px 16px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 8px;
}

.back-btn {
  background: rgba(255,215,0,0.1);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--accent);
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; text-decoration: none;
  transition: all 0.3s;
}
.back-btn:hover { background: rgba(255,215,0,0.2); }

.logo {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 24px;
  letter-spacing: 3px;
  color: var(--accent);
}

.count-badge {
  background: rgba(255,215,0,0.15);
  border: 1px solid var(--border);
  color: var(--accent);
  font-size: 12px; font-weight: 700;
  padding: 3px 10px; border-radius: 20px;
}

.grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  padding: 12px;
}

.card {
  background: var(--card);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  transition: all 0.3s;
}

.card:hover {
  border-color: var(--accent);
  box-shadow: var(--glow);
  transform: translateY(-3px);
}

.card img {
  width: 100%; height: 100px;
  object-fit: contain;
  display: block; padding: 10px;
  transition: transform 0.3s;
}
.card:hover img { transform: scale(1.05); }

.card-name {
  text-align: center;
  padding: 8px 10px 12px;
  font-size: 13px; font-weight: 700;
  color: var(--text);
}

/* stream type badge */
.type-badge {
  position: absolute;
  top: 6px; right: 6px;
  font-size: 9px; font-weight: 700;
  padding: 2px 6px; border-radius: 4px;
  text-transform: uppercase;
}
.type-dash { background: #6c3fff; color: #fff; }
.type-hls  { background: #00b894; color: #fff; }
.type-unk  { background: #636e72; color: #fff; }

.play-overlay {
  position: absolute;
  inset: 0; border-radius: 14px;
  background: rgba(255,215,0,0.05);
  opacity: 0; display: flex;
  align-items: center; justify-content: center;
  transition: opacity 0.3s;
}
.play-overlay i { font-size: 32px; color: var(--accent); }
.card:hover .play-overlay { opacity: 1; }

.live-tag {
  position: absolute;
  top: 6px; left: 6px;
  background: var(--accent2);
  color: #fff; font-size: 9px; font-weight: 700;
  padding: 2px 6px; border-radius: 4px;
}

.error-card {
  grid-column: 1/-1;
  padding: 24px;
  background: rgba(255,68,102,0.08);
  border: 1px solid rgba(255,68,102,0.2);
  border-radius: 14px;
  text-align: center; color: #ff6688;
}

.bottom-nav {
  position: fixed;
  bottom: 0; left: 0; right: 0;
  height: 68px;
  background: rgba(6,6,16,0.97);
  backdrop-filter: blur(20px);
  border-top: 1px solid var(--border);
  display: flex; justify-content: space-around; align-items: center;
  z-index: 1000;
}

.nav-item {
  display: flex; flex-direction: column; align-items: center;
  gap: 4px; cursor: pointer; padding: 6px 16px;
  border-radius: 12px; transition: all 0.3s; text-decoration: none;
}
.nav-item i { font-size: 20px; color: var(--muted); transition: all 0.3s; }
.nav-item span { font-size: 10px; color: var(--muted); font-weight: 600; }
.nav-item.active i, .nav-item:hover i { color: var(--accent); }
.nav-item.active span, .nav-item:hover span { color: var(--accent); }
.nav-item.active { background: rgba(255,215,0,0.08); }

@media (min-width: 600px) { .grid { grid-template-columns: repeat(3, 1fr); } }
</style>
</head>
<body>

<div class="topbar">
  <div class="brand">
    <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <div class="logo">📺 NBC LIVE</div>
    <div class="count-badge"><?= count($channels) ?> Channels</div>
  </div>
</div>

<div class="grid">
<?php if (empty($channels)): ?>
  <div class="error-card">
    <i class="fa fa-wifi" style="font-size:28px;margin-bottom:10px;display:block"></i>
    Channels hazipatikani sasa. Jaribu tena baadaye.
  </div>
<?php else: ?>
  <?php foreach ($channels as $c):
    $streamUrl  = $c['url'] ?? '';
    $streamType = strtoupper($c['stream_type'] ?? 'unknown');

    // Fix protocol-relative
    if (strpos($streamUrl, '//') === 0) {
        $streamUrl = 'https:' . $streamUrl;
    }

    // Decide player
    if ($streamType === 'DASH' || stripos($streamUrl, '.mpd') !== false) {
        $player     = 'player.php';
        $badgeClass = 'type-dash';
        $badgeLabel = 'MPD';
    } elseif ($streamType === 'HLS' || stripos($streamUrl, '.m3u8') !== false) {
        $player     = 'player.html';
        $badgeClass = 'type-hls';
        $badgeLabel = 'HLS';
    } else {
        $player     = 'player.html';
        $badgeClass = 'type-unk';
        $badgeLabel = 'LIVE';
    }

    // Build query params for player
    $params = http_build_query([
        'url'     => $streamUrl,
        'title'   => $c['title'] ?? '',
        'logo'    => $c['logo'] ?? '',
        'key'     => ($c['clearkey']['kid'] ?? '') . ':' . ($c['clearkey']['key'] ?? ''),
        'headers' => json_encode($c['headers'] ?? []),
        'drm'     => $c['drm'] ?? 'CLEARKEY',
    ]);
    // Angalia kama channel ni bure au premium
    $freeKw = ['tb1','tbc1','tbc2','safari','dodoma','zbc','azam one'];
    $isFree = false;
    $tl = strtolower($c['title'] ?? '');
    foreach($freeKw as $kw) { if(strpos($tl,$kw)!==false){$isFree=true;break;} }
    $titleJs   = htmlspecialchars(addslashes($c['title'] ?? ''), ENT_QUOTES);
    $playerUrl = htmlspecialchars($player.'?'.$params, ENT_QUOTES);
  ?>
  <div class="card" onclick="goChannel('<?=$titleJs?>','<?=$playerUrl?>')">
    <div class="live-tag">● LIVE</div>
    <div class="type-badge <?= $badgeClass ?>"><?= $badgeLabel ?></div>
    <img src="<?= htmlspecialchars($c['logo'] ?? '') ?>"
         alt="<?= htmlspecialchars($c['title'] ?? '') ?>"
         onerror="this.src='https://via.placeholder.com/200x100/0e0e1c/ffd700?text=📺'">
    <div class="play-overlay"><i class="fa fa-play"></i></div>
    <div class="card-name">
      <?= htmlspecialchars($c['title'] ?? '') ?>
      <?=$isFree ? ' <span style="font-size:9px;background:rgba(0,255,136,0.2);color:#00ff88;padding:1px 5px;border-radius:4px;vertical-align:middle">BURE</span>' : ' <span style="font-size:9px;background:rgba(255,215,0,0.15);color:#ffd700;padding:1px 5px;border-radius:4px;vertical-align:middle">PREMIUM</span>'?>
    </div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item "><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item "><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>
<script src="_auth_guard.js"></script>
<script src="_channel_guard.js"></script>
</body>
</html>
