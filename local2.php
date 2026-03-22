<?php
// ── Proxy logic ──────────────────────────────────────────────────
if (!empty($_SERVER['QUERY_STRING']) && isset($_GET['channel'])) {
    set_time_limit(0); ini_set('output_buffering','off'); ini_set('zlib.output_compression',false);
    while(ob_get_level()) ob_end_flush();
    $target = "https://cdn.zimotv.com/v6/handler.php?" . $_SERVER['QUERY_STRING'];
    $ch = curl_init($target);
    $hdrs = ["Referer: https://zimotv.com/","Origin: https://zimotv.com","User-Agent: Mozilla/5.0"];
    if(!empty($_SERVER['HTTP_RANGE'])) $hdrs[] = "Range: ".$_SERVER['HTTP_RANGE'];
    if($_SERVER['REQUEST_METHOD']==='HEAD') curl_setopt($ch,CURLOPT_NOBODY,true);
    curl_setopt_array($ch,[CURLOPT_FOLLOWLOCATION=>true,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_SSL_VERIFYHOST=>false,CURLOPT_HTTPHEADER=>$hdrs,CURLOPT_BUFFERSIZE=>8192,CURLOPT_NOPROGRESS=>true,CURLOPT_RETURNTRANSFER=>false,CURLOPT_HEADER=>false,CURLOPT_WRITEFUNCTION=>function($ch,$data){echo $data;flush();return strlen($data);},CURLOPT_HEADERFUNCTION=>function($ch,$header){if(stripos($header,'Content-Type:')===0||stripos($header,'Content-Length:')===0||stripos($header,'Content-Range:')===0||stripos($header,'Accept-Ranges:')===0)header(trim($header));return strlen($header);}]);
    curl_exec($ch); if(curl_errno($ch)){http_response_code(502);echo "Proxy Error: ".curl_error($ch);} curl_close($ch); exit;
}

// ════ KEY MAP — ClearKey za channels (kid:key) ════════════════
$KEY_MAP = [
    'sports 1'   => ['key'=>'c31df1600afc33799ecac543331803f2:dd2101530e222f545997d4c553787f85','cat'=>'Sports'],
    'sports 2'   => ['key'=>'739e7499125b31cc9948da8057b84cf9:1b7d44d798c351acc02f33ddfbb7682a','cat'=>'Sports'],
    'sports 3'   => ['key'=>'2f12d7b889de381a9fb5326ca3aa166d:51c2d733a54306fdf89acd4c9d4f6005','cat'=>'Sports'],
    'sports 4'   => ['key'=>'1606cddebd3c36308ec5072350fb790a:04ece212a9201531afdd91c6f468e0b3','cat'=>'Sports'],
    'azm two'    => ['key'=>'3b92b644635f3bad9f7d09ded676ec47:d012a9d5834f69be1313d4864d150a5f','cat'=>'Tamthiliya'],
    'azam two'   => ['key'=>'3b92b644635f3bad9f7d09ded676ec47:d012a9d5834f69be1313d4864d150a5f','cat'=>'Tamthiliya'],
    'sinema'     => ['key'=>'d628ae37a8f0336b970f250d9699461e:1194c3d60bb494aabe9114ca46c2738e','cat'=>'Tamthiliya'],
    'wasafi'     => ['key'=>'8714fe102679348e9c76cfd315dacaa0:a8b86ceda831061c13c7c4c67bd77f8e','cat'=>'Music'],
    'utv'        => ['key'=>'31b8fc6289fe3ca698588a59d845160c:f8c4e73f419cb80db3bdf4a974e31894','cat'=>'General'],
    'zbc'        => ['key'=>'2d60429f7d043a638beb7349ae25f008:f9b38900f31ce549425df1de2ea28f9d','cat'=>'General'],
    'nbc'        => ['key'=>'c31df1600afc33799ecac543331803f2:dd2101530e222f545997d4c553787f85','cat'=>'General'],
    'crown'      => ['key'=>'','cat'=>'Music'],
    'cheka'      => ['key'=>'','cat'=>'Music'],
    'zamaradi'   => ['key'=>'','cat'=>'Music'],
    'azam one'   => ['key'=>'','cat'=>'Tamthiliya'],
    'arise'      => ['key'=>'','cat'=>'General'],
];

// ════ FETCH zimotv local channels ════════════════════════════
$ch = curl_init("https://zimotv.com/mb/api/get-channels.php?category=local%20channels");
curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_TIMEOUT=>12,CURLOPT_HTTPHEADER=>["User-Agent: Mozilla/5.0","Referer: https://zimotv.com/","Origin: https://zimotv.com"]]);
$res = curl_exec($ch); curl_close($ch);
$data = json_decode($res, true);
$rawChs = $data['channels'] ?? [];

// ════ PROCESS ════════════════════════════════════════════════
function detectType(string $url): string {
    if (stripos($url,'.mpd')  !== false) return 'DASH';
    if (stripos($url,'.m3u8') !== false) return 'HLS';
    return 'HLS';
}
function getCat(string $t): string {
    $t = strtolower($t);
    if (preg_match('/sport/',$t))                        return 'Sports';
    if (preg_match('/sinema|kix|movie|film|drama/',$t))  return 'Tamthiliya';
    if (preg_match('/wasafi|cheka|crown|zamaradi|music/',$t)) return 'Music';
    return 'General';
}

$channels = [];
foreach ($rawChs as $c) {
    $url = $c['url'] ?? '';
    if (empty($url)) continue;
    if (strpos($url,'//') === 0) $url = 'https:'.$url;

    $title   = trim($c['title'] ?? $c['name'] ?? '');
    $logo    = $c['logo'] ?? $c['image'] ?? '';
    $headers = $c['headers'] ?? [];
    $type    = detectType($url);
    $tl      = strtolower($title);

    // Find key from map
    $key = ''; $cat = getCat($title);
    foreach ($KEY_MAP as $match => $info) {
        if (strpos($tl, $match) !== false) {
            $key = $info['key'];
            if ($info['cat'] !== 'General') $cat = $info['cat'];
            break;
        }
    }
    // Fallback: key from channel headers
    if (empty($key)) {
        $hk = $headers['kid'] ?? $headers['KID'] ?? '';
        $hv = $headers['key'] ?? $headers['KEY'] ?? '';
        if ($hk && $hv) $key = "$hk:$hv";
    }

    $channels[] = ['title'=>$title,'logo'=>$logo,'url'=>$url,'type'=>$type,'key'=>$key,'headers'=>$headers,'cat'=>$cat,'has_drm'=>!empty($key)];
}

// Group
$groups = []; $catOrder = ['Sports','Tamthiliya','Music','General'];
foreach ($channels as $c) $groups[$c['cat']][] = $c;

// Logo strip
$logos = array_values(array_filter(array_map(fn($c)=>['logo'=>$c['logo'],'title'=>$c['title']],$channels),fn($c)=>!empty($c['logo'])));
$half = max(1,(int)ceil(count($logos)/2));
$r1 = array_merge(array_slice($logos,0,$half),array_slice($logos,0,$half));
$r2b = array_slice($logos,$half); if(empty($r2b)) $r2b=$logos;
$r2 = array_merge($r2b,$r2b);

$totalDash = count(array_filter($channels,fn($c)=>$c['type']==='DASH'));
$totalHls  = count(array_filter($channels,fn($c)=>$c['type']==='HLS'));
$totalDrm  = count(array_filter($channels,fn($c)=>$c['has_drm']));
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover">
<title>JAYNES MAX TV — Local Channels</title>
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
body{padding-top:104px;padding-bottom:76px;}
.topbar{flex-wrap:wrap;height:auto;padding:10px 14px 8px;gap:8px;}
.tb1{display:flex;align-items:center;gap:10px;width:100%;}
.tb2{width:100%;display:flex;align-items:center;gap:8px;}
/* Stats */
.sbar{background:rgba(0,212,255,.04);border-bottom:1px solid var(--border);padding:7px 14px;display:flex;gap:14px;flex-wrap:wrap;font-size:11px;color:var(--muted);}
.sbar span{display:flex;align-items:center;gap:5px;}
.sbar strong{color:var(--text);}
/* Hero strip */
.hero{overflow:hidden;padding:14px 0 16px;border-bottom:1px solid var(--border);background:linear-gradient(180deg,rgba(0,212,255,.05) 0%,transparent 100%);}
.hlbl{font-size:10px;font-weight:700;letter-spacing:3px;text-transform:uppercase;color:var(--muted);padding:0 14px 10px;display:flex;align-items:center;gap:8px;}
.hlbl i{color:var(--accent);}
.hlbl::after{content:'';flex:1;height:1px;background:linear-gradient(to right,var(--border),transparent);}
.strip-wrap{display:flex;flex-direction:column;gap:8px;overflow:hidden;}
.srow{display:flex;gap:8px;width:max-content;animation:slide 30s linear infinite;}
.srow.r2{animation-direction:reverse;animation-duration:38s;}
@keyframes slide{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.strip-wrap:hover .srow{animation-play-state:paused;}
.lb{width:76px;height:52px;border-radius:10px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;overflow:hidden;flex-shrink:0;transition:all .22s;}
.lb:hover{border-color:var(--accent);transform:translateY(-2px) scale(1.06);}
.lb img{max-width:86%;max-height:42px;object-fit:contain;}
/* Tabs */
.tabs-bar{position:sticky;top:104px;z-index:100;background:rgba(6,6,16,.97);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);}
.tabs{display:flex;gap:6px;padding:9px 14px;overflow-x:auto;scrollbar-width:none;}
.tabs::-webkit-scrollbar{display:none;}
.tab{flex-shrink:0;padding:7px 16px;border-radius:20px;font-size:11px;font-weight:700;cursor:pointer;border:none;background:rgba(255,255,255,.04);color:var(--muted);transition:all .2s;white-space:nowrap;font-family:'Outfit',sans-serif;}
.tab:hover{color:var(--text);background:rgba(255,255,255,.08);}
.tab.on{background:var(--accent);color:#000;box-shadow:0 0 18px rgba(0,212,255,.4);}
/* Content */
.content{padding:12px 12px 10px;}
/* Cat header */
.cat-hdr{display:flex;align-items:center;gap:8px;font-family:'Bebas Neue',sans-serif;font-size:16px;letter-spacing:2px;color:var(--accent);padding:14px 2px 10px;border-bottom:1px solid var(--border);margin-bottom:10px;}
.cat-hdr::before{content:'';width:4px;height:16px;background:var(--accent);border-radius:2px;}
.cat-hdr .cnt{font-family:'Outfit',sans-serif;font-size:11px;font-weight:400;letter-spacing:0;color:var(--muted);margin-left:auto;}
/* Grid */
.ch-grid{display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:6px;}
@media(min-width:480px){.ch-grid{grid-template-columns:repeat(4,1fr);}}
@media(min-width:700px){.ch-grid{grid-template-columns:repeat(5,1fr);}}
/* Card */
.ch-card{background:var(--card);border:1px solid rgba(255,255,255,.06);border-radius:14px;overflow:hidden;cursor:pointer;position:relative;transition:all .28s;animation:cUp .4s ease both;}
@keyframes cUp{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:none}}
.ch-card:hover{border-color:var(--accent);box-shadow:var(--glow);transform:translateY(-4px);}
.ch-card:active{transform:scale(.95);}
/* Img */
.c-img{height:80px;background:rgba(0,0,0,.35);display:flex;align-items:center;justify-content:center;overflow:hidden;position:relative;}
.c-img img{max-width:88%;max-height:66px;object-fit:contain;transition:transform .3s;}
.ch-card:hover .c-img img{transform:scale(1.08);}
.c-av{width:48px;height:48px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:18px;color:#fff;}
/* Badges */
.b-live{position:absolute;top:5px;left:5px;z-index:3;background:var(--accent2);color:#fff;font-size:7.5px;font-weight:700;padding:2px 5px;border-radius:4px;display:flex;align-items:center;gap:3px;}
.b-live::before{content:'';width:4px;height:4px;border-radius:50%;background:#fff;animation:bl 1s infinite;}
@keyframes bl{0%,100%{opacity:1}50%{opacity:.1}}
.b-type{position:absolute;top:5px;right:5px;z-index:3;font-size:7.5px;font-weight:700;padding:2px 5px;border-radius:4px;text-transform:uppercase;}
.b-type.mpd{background:rgba(120,70,255,.9);color:#fff;}
.b-type.hls{background:rgba(0,190,115,.9);color:#fff;}
.b-key{position:absolute;bottom:5px;right:5px;z-index:3;font-size:7px;font-weight:700;padding:1px 5px;border-radius:4px;background:rgba(255,215,0,.85);color:#000;}
/* Play overlay */
.c-play{position:absolute;inset:0;z-index:4;background:rgba(6,6,16,.5);display:flex;align-items:center;justify-content:center;opacity:0;transition:opacity .2s;}
.c-play .p-ico{width:38px;height:38px;border-radius:50%;background:var(--accent);display:flex;align-items:center;justify-content:center;color:#000;font-size:13px;padding-left:2px;box-shadow:0 0 20px rgba(0,212,255,.5);transform:scale(.8);transition:transform .2s;}
.ch-card:hover .c-play{opacity:1;}
.ch-card:hover .c-play .p-ico{transform:scale(1);}
/* Footer */
.c-foot{padding:7px 8px 9px;}
.c-name{font-size:11px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;margin-bottom:2px;}
.c-meta{font-size:9.5px;color:var(--muted);display:flex;align-items:center;gap:4px;}
.c-meta i{font-size:8px;color:var(--accent);}
/* Empty */
.empty-st{text-align:center;padding:50px 20px;color:var(--muted);}
.empty-st i{font-size:42px;display:block;margin-bottom:14px;color:rgba(255,255,255,.1);}
.empty-st h3{font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:2px;color:rgba(255,255,255,.2);margin-bottom:8px;}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<div class="topbar">
  <div class="tb1">
    <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <div class="page-title">LOCAL <span>CHANNELS</span></div>
    <span class="badge badge-live" style="margin-left:auto;padding:4px 10px;font-size:10px"><span class="live-dot" style="margin-right:3px"></span>LIVE</span>
  </div>
  <div class="tb2">
    <div class="search-bar" style="flex:1">
      <input type="text" id="searchInput" placeholder="Tafuta channel...">
      <button id="searchBtn"><i class="fa fa-search"></i></button>
    </div>
    <a id="gSearchBtn" href="search.php" style="display:none;flex-shrink:0;padding:0 11px;height:38px;background:rgba(0,212,255,.08);border:1px solid var(--border);border-radius:10px;color:var(--accent);font-size:11px;font-weight:700;align-items:center;gap:5px;text-decoration:none;"><i class="fa fa-border-all"></i> Zote</a>
  </div>
</div>

<!-- Stats -->
<div class="sbar">
  <span><i class="fa fa-satellite-dish" style="color:var(--accent)"></i>Channels: <strong><?= count($channels) ?></strong></span>
  <span><i class="fa fa-photo-film" style="color:var(--purple)"></i>MPD: <strong><?= $totalDash ?></strong></span>
  <span><i class="fa fa-film" style="color:var(--green)"></i>HLS: <strong><?= $totalHls ?></strong></span>
  <span><i class="fa fa-key" style="color:var(--gold)"></i>DRM: <strong><?= $totalDrm ?></strong></span>
</div>

<?php if(!empty($logos)): ?>
<div class="hero">
  <div class="hlbl"><i class="fa fa-satellite-dish"></i>Channels Zinazo Sambaza Sasa Hivi</div>
  <div class="strip-wrap">
    <?php foreach([[$r1,'srow'],[$r2,'srow r2']] as [$row,$cls]): ?>
    <div class="<?=$cls?>">
      <?php foreach($row as $l): if(empty($l['logo'])) continue; ?>
      <div class="lb" title="<?=htmlspecialchars($l['title'])?>"><img src="<?=htmlspecialchars($l['logo'])?>" alt="<?=htmlspecialchars($l['title'])?>" loading="lazy" onerror="this.parentElement.style.display='none'"></div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs-bar">
  <div class="tabs">
    <button class="tab on" data-cat="all" onclick="setTab(this,'all')">⚡ Zote (<?=count($channels)?>)</button>
    <?php foreach($catOrder as $cat): if(empty($groups[$cat])) continue; $emo=match($cat){'Sports'=>'⚽','Tamthiliya'=>'🎭','Music'=>'🎵',default=>'📺'}; ?>
    <button class="tab" data-cat="<?=$cat?>" onclick="setTab(this,'<?=$cat?>')"><?=$emo?> <?=$cat?> (<?=count($groups[$cat])?>)</button>
    <?php endforeach; ?>
    <button class="tab" data-cat="mpd" onclick="setTab(this,'mpd')">📀 MPD (<?=$totalDash?>)</button>
    <button class="tab" data-cat="hls" onclick="setTab(this,'hls')">📡 HLS (<?=$totalHls?>)</button>
  </div>
</div>

<div class="content" id="contentWrap">
<?php if(empty($channels)): ?>
  <div class="empty-st"><i class="fa fa-circle-exclamation"></i><h3>HAKUNA CHANNELS</h3><p>Channels hazikupatikana kutoka ZimoTV.</p><button class="retry-btn" style="margin-top:16px" onclick="location.reload()"><i class="fa fa-rotate-right"></i> Jaribu Tena</button></div>
<?php else: ?>
<?php
$catMeta=['Sports'=>['ic'=>'fa-futbol','gr'=>'linear-gradient(135deg,#0a2050,#0d50a0)'],'Tamthiliya'=>['ic'=>'fa-film','gr'=>'linear-gradient(135deg,#200930,#5a1880)'],'Music'=>['ic'=>'fa-music','gr'=>'linear-gradient(135deg,#1a0a30,#6020a0)'],'General'=>['ic'=>'fa-tv','gr'=>'linear-gradient(135deg,#101525,#1e2d5a)']];
foreach($catOrder as $cat):
  if(empty($groups[$cat])) continue;
  $m=$catMeta[$cat]??$catMeta['General'];
  $emo=match($cat){'Sports'=>'⚽','Tamthiliya'=>'🎭','Music'=>'🎵',default=>'📺'};
?>
<div class="cat-section" data-cat="<?=$cat?>">
  <div class="cat-hdr"><i class="fa <?=$m['ic']?>"></i><?=$emo?> <?=$cat?><span class="cnt"><?=count($groups[$cat])?> channels</span></div>
  <div class="ch-grid">
  <?php foreach($groups[$cat] as $i=>$c):
    $isDash = $c['type']==='DASH';
    $tTag   = $isDash ? 'mpd' : 'hls';
    $tLbl   = $isDash ? 'MPD' : 'HLS';
    // Player URL
    if($isDash){
      $pUrl='player.php?'.http_build_query(['url'=>$c['url'],'key'=>$c['key'],'name'=>$c['title']]);
    } else {
      $pUrl='player.html?data='.urlencode(json_encode(['url'=>$c['url'],'title'=>$c['title'],'name'=>$c['title'],'key'=>$c['key'],'headers'=>$c['headers']]));
    }
    $words=preg_split('/\s+/',trim($c['title']?:'TV'));
    $ini=strtoupper(substr($words[0],0,1).(isset($words[1])?substr($words[1],0,1):''));
    $dl=($i%6)*0.05;
  ?>
  <?php
  $freeKw = ['tb1','tbc1','tbc2','safari','dodoma','zbc','azam one','azamone'];
  $isFree = false;
  $titleLower = strtolower($c['title']);
  foreach($freeKw as $kw) {
    if(strpos($titleLower, $kw) !== false) { $isFree = true; break; }
  }
  $pUrlJs  = htmlspecialchars($pUrl, ENT_QUOTES);
  $titleJs = htmlspecialchars(addslashes($c['title']), ENT_QUOTES);
  ?>
  <div class="ch-card"
       data-cat="<?=$cat?>"
       data-type="<?=strtolower($c['type'])?>"
       data-title="<?=htmlspecialchars(strtolower($c['title']))?>"
       data-free="<?=$isFree?'1':'0'?>"
       onclick="goChannel('<?=$titleJs?>','<?=$pUrlJs?>')"
       style="animation-delay:<?=$dl?>s">
    <div class="c-img">
      <?php if($c['logo']): ?>
        <img src="<?=htmlspecialchars($c['logo'])?>" alt="<?=htmlspecialchars($c['title'])?>" loading="lazy"
             onerror="this.style.display='none';this.nextElementSibling.style.display='flex'">
        <div class="c-av" style="display:none;background:<?=$m['gr']?>"><?=$ini?></div>
      <?php else: ?>
        <div class="c-av" style="background:<?=$m['gr']?>"><?=$ini?></div>
      <?php endif; ?>
      <div class="c-play"><div class="p-ico"><i class="fa fa-play"></i></div></div>
    </div>
    <div class="b-live">LIVE</div>
    <div class="b-type <?=$tTag?>"><?=$tLbl?></div>
    <?php if($c['has_drm']): ?><div class="b-key">🔑 KEY</div><?php endif; ?>
    <div class="c-foot">
      <div class="c-name"><?=htmlspecialchars($c['title'])?></div>
      <div class="c-meta">
        <i class="fa fa-folder"></i><?=$cat?>
        <?php if($isFree): ?>
        · <i class="fa fa-unlock" style="color:var(--green)"></i><span style="color:var(--green)">BURE</span>
        <?php else: ?>
        · <i class="fa fa-crown" style="color:var(--gold)"></i><span style="color:var(--gold)">PREMIUM</span>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endforeach; ?>
<?php endif; ?>
</div>

<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item"><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script src="_auth_guard.js"></script>
<script src="_cache.js"></script>
<script src="_channel_guard.js"></script>
<script>
// ── Hifadhi na rejesha hali (tab + scroll) anapokuja kutoka player ──
(function restoreState() {
  const saved = JMCache.get('local2_state');
  if (saved) {
    // Rejesha tab iliyokuwa imechaguliwa
    if (saved.cat) {
      const btn = document.querySelector(`.tab[data-cat="${saved.cat}"]`);
      if (btn) { document.querySelectorAll('.tab').forEach(t=>t.classList.remove('on')); btn.classList.add('on'); filterCards(saved.cat,''); }
    }
    // Rejesha scroll baada ya DOM kuwa tayari
    if (saved.scrollY) {
      requestAnimationFrame(() => { window.scrollTo(0, saved.scrollY); });
    }
  }
})();

// ── Hifadhi hali kabla ya kwenda player ──
document.querySelectorAll('.ch-card').forEach(card => {
  card.addEventListener('click', () => {
    JMCache.set('local2_state', {
      cat: document.querySelector('.tab.on')?.dataset.cat || 'all',
      scrollY: window.scrollY,
    });
  }, { capture: true });
});
function setTab(btn,cat){
  document.querySelectorAll('.tab').forEach(t=>t.classList.remove('on'));
  btn.classList.add('on');
  filterCards(cat,document.getElementById('searchInput').value.trim().toLowerCase());
}
function activeCat(){return document.querySelector('.tab.on')?.dataset.cat||'all';}
function filterCards(cat,q){
  let vis=0;
  document.querySelectorAll('.cat-section').forEach(sec=>{
    let sv=0;
    sec.querySelectorAll('.ch-card').forEach(card=>{
      const mc=cat==='all'||(cat==='mpd'&&card.dataset.type==='dash')||(cat==='hls'&&card.dataset.type==='hls')||card.dataset.cat===cat;
      const mq=!q||card.dataset.title.includes(q);
      card.style.display=(mc&&mq)?'':'none';
      if(mc&&mq){sv++;vis++;}
    });
    sec.style.display=sv>0?'':'none';
  });
  let nr=document.getElementById('noRes');
  if(vis===0&&(q||cat!=='all')){
    if(!nr){nr=document.createElement('div');nr.id='noRes';nr.className='empty-st';document.getElementById('contentWrap').appendChild(nr);}
    nr.innerHTML=`<i class="fa fa-magnifying-glass"></i><h3>HAKUNA MATOKEO</h3><p>Hakuna channel inayolingana.</p>`;
    nr.style.display='block';
  } else if(nr){nr.style.display='none';}
}
const si=document.getElementById('searchInput');
si.addEventListener('input',()=>{
  const q=si.value.trim().toLowerCase();
  const gb=document.getElementById('gSearchBtn');
  filterCards(activeCat(),q);
  if(q){gb.href='search.php?q='+encodeURIComponent(q);gb.style.display='flex';}
  else gb.style.display='none';
});
document.getElementById('searchBtn').onclick=()=>{const q=si.value.trim();if(q)filterCards(activeCat(),q.toLowerCase());};
si.addEventListener('keypress',e=>{if(e.key==='Enter'){const q=si.value.trim();if(q)location.href='search.php?q='+encodeURIComponent(q);}});
function showToast(m,d=3000){const t=document.getElementById('toast');t.textContent=m;t.classList.add('show');clearTimeout(t._t);t._t=setTimeout(()=>t.classList.remove('show'),d);}
</script>
</body>
</html>
