<?php
// ════════════════════════════════════════════════════════════
//  ⚽ JAYNES MAX TV — Football Dashboard
// ════════════════════════════════════════════════════════════
define('APIKEY', '6095087686b995f2554af89bac81bb2b11d4182bdc4ff16c603d9130479f2736');
define('BASE',   'https://apiv2.allsportsapi.com/football/');

// ── JSON API ENDPOINT — inatumiwa na schedule.php ─────────────
// URL: jaynes_max_tv.php?l=epl&_json=fixtures
if (isset($_GET['_json']) && $_GET['_json'] === 'fixtures') {
    header('Content-Type: application/json; charset=UTF-8');
    header('Access-Control-Allow-Origin: *');
    header('Cache-Control: public, max-age=120'); // Cache dakika 2

    $slug = $_GET['l'] ?? 'epl';
    $LEAGUES_JSON = [
        'nbc'  => ['id'=>551],  'tz'   => ['id'=>551],
        'epl'  => ['id'=>152],  'la'   => ['id'=>302],
        'bund' => ['id'=>175],  'ucl'  => ['id'=>354],
        'uel'  => ['id'=>480],  'cafc' => ['id'=>346],
        'cafcc'=> ['id'=>390],  'afcon'=> ['id'=>29],
    ];
    $LID = $LEAGUES_JSON[$slug]['id'] ?? 152;

    $today  = date('Y-m-d');
    $past   = date('Y-m-d', strtotime('-3 days'));
    $future = date('Y-m-d', strtotime('+7 days'));

    // Chukua data zote kwa wakati mmoja kwa curl_multi
    $reqs = [
        'upcoming' => ['met'=>'Fixtures','leagueId'=>$LID,'from'=>$today,'to'=>$future,'APIkey'=>APIKEY],
        'recent'   => ['met'=>'Fixtures','leagueId'=>$LID,'from'=>$past, 'to'=>$today, 'APIkey'=>APIKEY],
        'live'     => ['met'=>'Livescore','leagueId'=>$LID,'APIkey'=>APIKEY],
    ];

    $mh = curl_multi_init();
    $handles = [];
    foreach ($reqs as $key => $params) {
        $ch = curl_init(BASE . '?' . http_build_query($params));
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false, CURLOPT_USERAGENT => 'Mozilla/5.0',
        ]);
        curl_multi_add_handle($mh, $ch);
        $handles[$key] = $ch;
    }

    $running = null;
    do { curl_multi_exec($mh, $running); curl_multi_select($mh); } while ($running > 0);

    $result = [];
    foreach ($handles as $key => $ch) {
        $raw  = curl_multi_getcontent($ch);
        $data = json_decode($raw, true);
        $result[$key] = is_array($data['result'] ?? null) ? $data['result'] : [];
        curl_multi_remove_handle($mh, $ch);
        curl_close($ch);
    }
    curl_multi_close($mh);

    echo json_encode(['success'=>true, 'league'=>$slug, 'lid'=>$LID] + $result, JSON_UNESCAPED_UNICODE);
    exit;
}
// ── END JSON ENDPOINT ──────────────────────────────────────────

$LEAGUES = [
  'tz'    => ['id'=>551,  'name'=>'NBC Premier League',  'short'=>'NBC',  'flag'=>'🇹🇿', 'color'=>'#22c55e', 'grp'=>'Africa'],
  'epl'   => ['id'=>152,  'name'=>'Premier League',      'short'=>'EPL',  'flag'=>'🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'color'=>'#a855f7', 'grp'=>'Europe'],
  'la'    => ['id'=>302,  'name'=>'La Liga',             'short'=>'LAL',  'flag'=>'🇪🇸', 'color'=>'#ef4444', 'grp'=>'Europe'],
  'bund'  => ['id'=>175,  'name'=>'Bundesliga',          'short'=>'BUN',  'flag'=>'🇩🇪', 'color'=>'#f59e0b', 'grp'=>'Europe'],
  'ucl'   => ['id'=>354,  'name'=>'Champions League',    'short'=>'UCL',  'flag'=>'⭐',  'color'=>'#3b82f6', 'grp'=>'Europe'],
  'uel'   => ['id'=>480,  'name'=>'Europa League',       'short'=>'UEL',  'flag'=>'🟠',  'color'=>'#f97316', 'grp'=>'Europe'],
  'cafc'  => ['id'=>346,  'name'=>'CAF Champions',       'short'=>'CAFC', 'flag'=>'🏆',  'color'=>'#10b981', 'grp'=>'Africa'],
  'cafcc' => ['id'=>390,  'name'=>'CAF Confederation',   'short'=>'CACC', 'flag'=>'🌿',  'color'=>'#14b8a6', 'grp'=>'Africa'],
  'afcon' => ['id'=>29,   'name'=>'AFCON',               'short'=>'AFCN', 'flag'=>'🌍',  'color'=>'#d97706', 'grp'=>'Africa'],
];

function api(array $p): array {
  $p['APIkey'] = APIKEY;
  $url = BASE.'?'.http_build_query($p);
  $ch = curl_init($url);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_TIMEOUT=>12,CURLOPT_SSL_VERIFYPEER=>false,CURLOPT_USERAGENT=>'Mozilla/5.0']);
  $r = curl_exec($ch); curl_close($ch);
  $d = json_decode($r, true);
  $res = $d['result'] ?? [];
  // Standings returns result as object {total:[],home:[],away:[]} — wrap into array
  if(is_array($res) && isset($res['total'])) return [$res];
  return is_array($res) ? $res : [];
}

function parseStandings(array $raw): array {
  $out = [];

  // Helper to extract flat rows from any array
  $extract = function(array $arr) use (&$out) {
    foreach($arr as $item){
      if(!is_array($item)) continue;
      if(isset($item['standing_place']) || isset($item['standing_team']) || isset($item['team_name'])){
        $out[] = $item;
      }
    }
  };

  foreach($raw as $item){
    if(!is_array($item)) continue;

    // ── Format 1: Direct flat row ──
    if(isset($item['standing_place']) || isset($item['standing_team'])){
      $out[] = $item;
      continue;
    }

    // ── Format 2: AllSportsAPI nested { "total":[...], "home":[...], "away":[...] } ──
    // Use "total" group only (contains overall standings)
    if(isset($item['total']) && is_array($item['total'])){
      $extract($item['total']);
      continue;
    }

    // ── Format 3: { "standings": [...] } ──
    if(isset($item['standings']) && is_array($item['standings'])){
      $extract($item['standings']);
      continue;
    }

    // ── Format 4: { "standing_table": [...] } ──
    if(isset($item['standing_table']) && is_array($item['standing_table'])){
      $extract($item['standing_table']);
      continue;
    }

    // ── Format 5: team_name flat ──
    if(isset($item['team_name'])){ $out[] = $item; }
  }

  // Sort by standing_place ascending
  usort($out, fn($a,$b) => (int)($a['standing_place']??99) <=> (int)($b['standing_place']??99));

  // Deduplicate by team name
  $seen=[]; $final=[];
  foreach($out as $r){
    $k = $r['standing_team'] ?? $r['team_name'] ?? '';
    if($k && !in_array($k,$seen)){ $seen[]=$k; $final[]=$r; }
  }
  return $final ?: $out;
}

function sf(array $r, array $keys, $def=0){
  foreach($keys as $k){ if(isset($r[$k])&&$r[$k]!=='') return $r[$k]; }
  return $def;
}

$slug = $_GET['l'] ?? 'epl';
if(!isset($LEAGUES[$slug])) $slug='epl';
$L   = $LEAGUES[$slug];
$LID = $L['id'];
$fxId= (int)($_GET['fx']??0);

$today  = date('Y-m-d');
$past   = date('Y-m-d', strtotime('-30 days'));
$future = date('Y-m-d', strtotime('+21 days'));

$standRaw = api(['met'=>'Standings','leagueId'=>$LID]);
$table    = parseStandings($standRaw);

// DEBUG: Uncomment below to see raw API response
// echo '<pre style="background:#000;color:#0f0;padding:20px;font-size:11px;overflow:auto;max-height:400px">';
// echo 'RAW COUNT: '.count($standRaw)."\n";
// echo 'TABLE COUNT: '.count($table)."\n";
// echo 'FIRST RAW KEYS: '.implode(', ', array_keys($standRaw[0] ?? []))."\n";
// echo json_encode($standRaw, JSON_PRETTY_PRINT);
// echo '</pre>'; exit;
$recent   = api(['met'=>'Fixtures','leagueId'=>$LID,'from'=>$past,'to'=>$today]);
$upcoming = api(['met'=>'Fixtures','leagueId'=>$LID,'from'=>$today,'to'=>$future]);
$liveAll  = api(['met'=>'Livescore']);
$live     = array_values(array_filter($liveAll,fn($m)=>($m['league_key']??0)==$LID));
$scorers  = array_slice(api(['met'=>'Topscorers','leagueId'=>$LID]),0,10);
$nFx      = !empty($upcoming)&&!$fxId ? $upcoming[0] : null;

$fxData=$homeLU=$awayLU=$homeSub=$awaySub=$homeStats=$h2h=[];
$homeForm=$awayForm='';
if($fxId){
  $fd=api(['met'=>'Fixtures','matchId'=>$fxId,'withPlayerStats'=>1]);
  $fxData=!empty($fd)?$fd[0]:[];
  if($fxData){
    $lin=$fxData['lineups']??[];
    $homeLU=$lin['home']['starting_lineups']??[];
    $awayLU=$lin['away']['starting_lineups']??[];
    $homeSub=$lin['home']['substitutes']??[];
    $awaySub=$lin['away']['substitutes']??[];
    $homeForm=$lin['home']['formation']??'';
    $awayForm=$lin['away']['formation']??'';
    $homeStats=$fxData['statistics']??[];
    $t1=$fxData['home_team_key']??0; $t2=$fxData['away_team_key']??0;
    if($t1&&$t2) $h2h=array_slice(api(['met'=>'H2H','firstTeamId'=>$t1,'secondTeamId'=>$t2]),0,5);
  }
}
function fd($d,$t=''){ return date('D d M Y',strtotime($d)).($t?" $t":''); }
function ratc($r){ return $r>=7.5?'rh':($r>=6?'rm':'rl'); }
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>JAYNES MAX TV · <?=$L['name']?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Bebas+Neue&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#0a0e17;--s1:#0f1623;--s2:#141d2e;--s3:#192438;--s4:#1e2d44;
  --rim:#243450;--rim2:#2d3f5a;
  --ac:<?=$L['color']?>;
  --w:#e8f0fc;--d:#4a6080;--m:#162030;
  --go:#22c55e;--wa:#f59e0b;--re:#ef4444;--bl:#3b82f6;
  --ff:'Inter',sans-serif;--fh:'Bebas Neue',sans-serif;
}
*{box-sizing:border-box;margin:0;padding:0}
html{scroll-behavior:smooth}
body{background:var(--bg);color:var(--w);font-family:var(--ff);font-size:14px;min-height:100vh}
a{text-decoration:none;color:inherit}
img{display:block}

/* ══ TOPBAR ══ */
.topbar{
  background:var(--s1);
  border-bottom:2px solid var(--ac);
  position:sticky;top:0;z-index:300;
  box-shadow:0 4px 30px rgba(0,0,0,.7);
}
.tb1{
  max-width:1600px;margin:0 auto;
  display:flex;align-items:center;gap:0;padding:0 20px;height:56px;
}
/* BRAND */
.brand{
  display:flex;flex-direction:column;justify-content:center;
  padding:0 20px 0 0;border-right:1px solid var(--rim);margin-right:0;min-width:180px;
}
.brand-top{
  font-family:var(--fh);font-size:22px;letter-spacing:3px;
  color:#fff;line-height:1;display:flex;align-items:center;gap:8px;
}
.brand-dot{width:8px;height:8px;border-radius:50%;background:var(--ac);box-shadow:0 0 10px var(--ac);animation:glow 2s infinite;flex-shrink:0}
.brand-sub{font-size:9px;color:var(--d);letter-spacing:4px;margin-top:1px;text-transform:uppercase}

/* SEARCH */
.search-wrap{
  padding:0 16px;border-right:1px solid var(--rim);
  display:flex;align-items:center;gap:8px;
}
.search-box{
  display:flex;align-items:center;gap:8px;
  background:var(--s3);border:1px solid var(--rim);border-radius:6px;
  padding:6px 12px;
}
.search-box input{
  background:none;border:none;outline:none;
  color:var(--w);font-size:13px;width:180px;
  font-family:var(--ff);
}
.search-box input::placeholder{color:var(--d)}
.search-icon{color:var(--d);font-size:14px}

/* LIVE BADGE */
.live-tag{
  background:var(--re);color:#fff;font-size:10px;font-weight:600;
  padding:3px 10px;border-radius:4px;letter-spacing:1px;
  animation:blink 1s infinite;white-space:nowrap;
}

/* CLOCK */
.tb-clk{
  margin-left:auto;font-family:monospace;font-size:13px;color:var(--d);
  padding-left:16px;border-left:1px solid var(--rim);white-space:nowrap;
}

/* LEAGUE NAV ROW */
.tb2{border-top:1px solid var(--rim)}
.tb2-inner{
  max-width:1600px;margin:0 auto;
  display:flex;align-items:stretch;overflow-x:auto;
  scrollbar-width:none;padding:0 20px;
}
.tb2-inner::-webkit-scrollbar{display:none}
.grp-lbl{
  display:flex;align-items:center;padding:0 10px;
  font-size:9px;letter-spacing:2px;color:var(--d);
  border-right:1px solid var(--rim);white-space:nowrap;flex-shrink:0;
}
.ltab{
  display:flex;align-items:center;gap:6px;padding:9px 14px;
  font-size:12px;font-weight:500;color:var(--d);
  white-space:nowrap;border-bottom:3px solid transparent;
  transition:all .2s;flex-shrink:0;cursor:pointer;
}
.ltab:hover{color:var(--w);background:rgba(255,255,255,.02)}
.ltab.on{color:#fff;border-bottom-color:var(--ac);background:rgba(255,255,255,.03)}

/* ══ LIVE STRIP ══ */
.live-strip{max-width:1600px;margin:14px auto 0;padding:0 20px}
.live-wrap{
  background:rgba(239,68,68,.07);border:1px solid rgba(239,68,68,.2);
  border-radius:8px;padding:12px 14px;
}
.live-hd{font-size:10px;letter-spacing:3px;color:var(--re);display:flex;align-items:center;gap:6px;margin-bottom:10px;font-weight:600}
.ldot{width:7px;height:7px;border-radius:50%;background:var(--re);animation:blink .8s infinite}
.live-list{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px}
.live-list::-webkit-scrollbar{display:none}
.lc{
  flex-shrink:0;min-width:220px;
  background:rgba(239,68,68,.05);border:1px solid rgba(239,68,68,.15);
  border-radius:7px;padding:10px 12px;cursor:pointer;transition:all .2s;
  display:grid;grid-template-columns:1fr 60px 1fr;align-items:center;gap:6px;
}
.lc:hover{background:rgba(239,68,68,.12);transform:translateY(-1px)}
.lt2{display:flex;align-items:center;gap:5px;font-size:12px;font-weight:500}
.lt2.r{flex-direction:row-reverse;text-align:right}
.lt2 img{width:18px;height:18px;object-fit:contain}
.lsc{text-align:center}
.lsc-n{font-family:monospace;font-size:18px;font-weight:700;letter-spacing:2px;line-height:1}
.lsc-m{font-size:10px;color:var(--re);font-weight:600;display:block;margin-top:1px}

/* ══ PAGE LAYOUT ══ */
.page{
  max-width:1600px;margin:16px auto;padding:0 20px;
  display:grid;grid-template-columns:320px 1fr 300px;gap:14px;
}
@media(max-width:1280px){.page{grid-template-columns:300px 1fr}}
@media(max-width:860px){.page{grid-template-columns:1fr;padding:0 10px}}
.col{display:flex;flex-direction:column;gap:12px}

/* ══ PANEL ══ */
.panel{
  background:var(--s2);border:1px solid var(--rim);border-radius:10px;
  overflow:hidden;animation:up .35s ease both;
}
.panel:nth-child(2){animation-delay:.06s}.panel:nth-child(3){animation-delay:.12s}
.pac{height:2px;background:linear-gradient(90deg,var(--ac),transparent)}
.phd{
  display:flex;align-items:center;gap:8px;padding:11px 15px;
  background:rgba(255,255,255,.02);border-bottom:1px solid var(--rim);
}
.phd h3{font-size:12px;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;flex:1;color:var(--w)}
.ptag{font-size:10px;color:var(--d);background:var(--s4);padding:2px 8px;border-radius:3px;font-family:monospace}

/* ══ STANDINGS TABLE — Livescore Style ══ */
.st-search{
  display:flex;align-items:center;gap:8px;padding:10px 14px;
  border-bottom:1px solid var(--rim);background:var(--s1);
}
.st-search input{
  flex:1;background:var(--s3);border:1px solid var(--rim);border-radius:5px;
  padding:6px 10px;color:var(--w);font-size:12px;font-family:var(--ff);outline:none;
}
.st-search input::placeholder{color:var(--d)}
.st-wrap{overflow-x:auto}
table.st{width:100%;border-collapse:collapse;font-size:12px}
table.st thead{position:sticky;top:0;z-index:2}
table.st th{
  background:var(--s1);color:var(--ac);font-size:10px;letter-spacing:1.5px;
  font-weight:600;padding:8px 8px;text-align:center;border-bottom:1px solid var(--rim);
}
table.st th:nth-child(2){text-align:left;padding-left:10px}
table.st tbody tr{border-bottom:1px solid rgba(255,255,255,.04);transition:background .1s;cursor:pointer}
table.st tbody tr:hover{background:rgba(255,255,255,.03)}
table.st tbody tr.st-hide{display:none}
table.st td{padding:9px 8px;text-align:center;color:var(--d)}
table.st td:nth-child(2){text-align:left;padding-left:10px;color:var(--w);font-weight:500}
.rk{width:20px;height:20px;border-radius:50%;display:inline-flex;align-items:center;justify-content:center;font-size:9px;font-weight:700}
.rk1{background:var(--wa);color:#000}.rk2{background:#94a3b8;color:#000}.rk3{background:#b45309;color:#fff}.rko{background:var(--m);color:var(--d)}
.tc{display:flex;align-items:center;gap:7px}
.tc img{width:18px;height:18px;object-fit:contain}
.pts-c{color:var(--wa)!important;font-weight:700!important}
.fb{display:inline-flex;align-items:center;justify-content:center;width:15px;height:15px;border-radius:3px;font-size:8px;font-weight:700;margin:0 1px}
.fbW{background:rgba(34,197,94,.2);color:var(--go)}.fbD{background:rgba(245,158,11,.2);color:var(--wa)}.fbL{background:rgba(239,68,68,.2);color:var(--re)}
/* Promotion zones */
.zone-ucl td:first-child{border-left:3px solid #3b82f6}
.zone-uecl td:first-child{border-left:3px solid #f97316}
.zone-rel td:first-child{border-left:3px solid #ef4444}

/* ══ TOP SCORERS ══ */
.scr{display:flex;align-items:center;gap:10px;padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.04);transition:background .1s}
.scr:hover{background:rgba(255,255,255,.02)}
.scr-pos{font-size:16px;font-weight:700;color:var(--m);width:20px;text-align:center;flex-shrink:0;font-family:monospace}
.scr-pos.t{color:var(--wa)}
.scr-img{width:34px;height:34px;border-radius:50%;object-fit:cover;border:2px solid var(--rim)}
.scr-info{flex:1;min-width:0}
.scr-nm{font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.scr-cl{font-size:11px;color:var(--d)}
.scr-g{font-family:monospace;font-size:22px;font-weight:700;color:var(--wa)}
.scr-gl{font-size:9px;color:var(--d);display:block}

/* ══ FIXTURES — Livescore Style ══ */
.fx-search{
  display:flex;align-items:center;gap:8px;padding:9px 14px;
  border-bottom:1px solid var(--rim);background:var(--s1);
}
.fx-search input{
  flex:1;background:var(--s3);border:1px solid var(--rim);border-radius:5px;
  padding:6px 10px;color:var(--w);font-size:12px;font-family:var(--ff);outline:none;
}
.fx-search input::placeholder{color:var(--d)}
.fx-date-grp{background:var(--s3);padding:7px 14px;font-size:11px;font-weight:600;color:var(--d);letter-spacing:1px;border-bottom:1px solid var(--rim)}
.fxrow{
  display:grid;grid-template-columns:1fr 90px 1fr;align-items:center;
  padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);
  cursor:pointer;transition:background .1s;
}
.fxrow:hover{background:rgba(255,255,255,.025)}
.fxrow.sel{background:rgba(255,255,255,.04);border-left:3px solid var(--ac)}
.fxrow.fx-hide{display:none}
.fxt{display:flex;align-items:center;gap:7px;font-size:12px;font-weight:500}
.fxt.r{flex-direction:row-reverse;text-align:right}
.fxt img{width:20px;height:20px;object-fit:contain}
.fxm{text-align:center}
.fxm-s{
  font-family:monospace;font-size:15px;font-weight:700;
  background:var(--s1);border:1px solid var(--rim);
  border-radius:5px;padding:4px 9px;display:inline-block;
}
.fxm-d{font-size:10px;color:var(--d);text-align:center;margin-top:3px}
.bl{background:var(--re);color:#fff;font-size:9px;padding:2px 5px;border-radius:3px;font-family:monospace;animation:blink 1s infinite}
.bu{background:rgba(59,130,246,.15);color:var(--bl);font-size:9px;padding:2px 5px;border-radius:3px}
.be{background:var(--m);color:var(--d);font-size:9px;padding:2px 5px;border-radius:3px}

/* ══ TABS ══ */
.tabs{display:flex;background:var(--s1);border-bottom:1px solid var(--rim)}
.tab{padding:9px 14px;font-size:12px;font-weight:500;color:var(--d);cursor:pointer;border-bottom:2px solid transparent;transition:all .2s;letter-spacing:.3px}
.tab.on{color:var(--w);border-bottom-color:var(--ac)}
.tp{display:none}.tp.on{display:block}

/* ══ DETAIL HERO ══ */
.det-hero{
  background:linear-gradient(150deg,var(--s3),var(--s4));
  padding:26px 18px;text-align:center;position:relative;overflow:hidden;
  border-bottom:1px solid var(--rim);
}
.det-hero::before{content:'';position:absolute;inset:0;background:radial-gradient(ellipse at 50% -10%,rgba(255,255,255,.05),transparent 65%)}
.det-hero::after{content:'';position:absolute;bottom:0;left:0;right:0;height:2px;background:linear-gradient(90deg,transparent,var(--ac),transparent)}
.det-teams{display:flex;align-items:center;justify-content:center;gap:18px;position:relative;z-index:1}
.det-t{flex:1;text-align:center}
.det-t img{width:64px;height:64px;object-fit:contain;margin:0 auto;filter:drop-shadow(0 6px 16px rgba(0,0,0,.5))}
.det-t-n{font-size:15px;font-weight:600;margin-top:7px;line-height:1.2}
.det-sc{font-family:monospace;font-size:48px;font-weight:700;letter-spacing:4px;min-width:130px;text-align:center;line-height:1;color:#fff}
.det-st{font-size:11px;color:var(--d);margin-top:5px}
.det-meta{display:flex;justify-content:center;flex-wrap:wrap;gap:8px;margin-top:12px;position:relative;z-index:1}
.det-meta span{background:var(--s1);border:1px solid var(--rim);padding:3px 10px;border-radius:20px;font-size:10px;color:var(--d)}

/* ══ LINEUP ══ */
.lu-wrap{display:grid;grid-template-columns:1fr 1fr;gap:1px;background:var(--rim)}
.lu-col{background:var(--s2);padding:12px 10px}
.lu-team-hd{display:flex;align-items:center;gap:7px;margin-bottom:10px;padding-bottom:8px;border-bottom:1px solid var(--rim)}
.lu-team-hd img{width:24px;height:24px;object-fit:contain}
.lu-team-n{font-size:12px;font-weight:600;flex:1;min-width:0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.lu-fm{font-family:monospace;font-size:11px;color:var(--ac);background:var(--s4);padding:2px 6px;border-radius:4px}
.lu-sec{font-size:9px;letter-spacing:2px;color:var(--d);padding:5px 0 3px;text-transform:uppercase;font-weight:600}
.prow{display:flex;align-items:center;gap:5px;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.03)}
.prow.sub{opacity:.55}
.pnum{width:20px;height:20px;border-radius:50%;background:var(--m);display:flex;align-items:center;justify-content:center;font-family:monospace;font-size:9px;color:var(--d);flex-shrink:0}
.ppic{width:26px;height:26px;border-radius:50%;object-fit:cover;border:1.5px solid var(--rim);flex-shrink:0}
.pinf{flex:1;min-width:0}
.pnm{font-size:11px;font-weight:500;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.ppos{font-size:9px;color:var(--d)}
.prat{font-family:monospace;font-size:12px;font-weight:700;padding:2px 6px;border-radius:4px;min-width:32px;text-align:center;flex-shrink:0}
.rh{background:rgba(34,197,94,.15);color:var(--go)}.rm{background:rgba(245,158,11,.15);color:var(--wa)}.rl{background:rgba(239,68,68,.15);color:var(--re)}
.lu-empty{padding:28px;text-align:center;color:var(--d);font-size:12px;background:var(--s2)}
.lu-empty span{font-size:28px;display:block;margin-bottom:6px}

/* ══ STATS BARS ══ */
.stat-row{padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.04)}
.stat-lbl{display:flex;justify-content:space-between;font-size:11px;margin-bottom:5px}
.stat-lbl .hv{color:var(--ac);font-weight:600;font-family:monospace}
.stat-lbl .nm{color:var(--d)}
.stat-lbl .av{color:var(--wa);font-weight:600;font-family:monospace}
.stat-bar{height:4px;background:var(--s4);border-radius:2px;overflow:hidden}
.stat-fill{height:100%;background:linear-gradient(90deg,var(--ac),var(--wa));border-radius:2px}

/* ══ H2H ══ */
.h2h-row{display:grid;grid-template-columns:1fr 65px 1fr;align-items:center;padding:8px 14px;border-bottom:1px solid rgba(255,255,255,.03);font-size:12px}
.h2ht{display:flex;align-items:center;gap:5px}
.h2ht.r{flex-direction:row-reverse;text-align:right}
.h2ht img{width:14px;height:14px;object-fit:contain}
.h2hs{text-align:center;font-family:monospace;font-size:15px;font-weight:700}

/* ══ NEXT MATCH ══ */
.nm{background:rgba(59,130,246,.07);border:1px solid rgba(59,130,246,.2);border-radius:10px;padding:16px}
.nm-lbl{font-size:10px;letter-spacing:3px;color:var(--bl);margin-bottom:12px;font-weight:600}
.nm-vs{display:flex;align-items:center;justify-content:space-between;gap:10px}
.nm-t{text-align:center;flex:1}
.nm-t img{width:48px;height:48px;object-fit:contain;margin:0 auto}
.nm-tn{font-size:13px;font-weight:600;margin-top:6px}
.nm-mid{font-family:monospace;font-size:26px;color:var(--d)}
.nm-date{text-align:center;background:rgba(59,130,246,.1);border-radius:7px;padding:7px;margin-top:10px;font-size:11px;color:var(--bl);font-weight:500}

/* ══ STAT BOXES ══ */
.sb-grid{display:grid;grid-template-columns:1fr 1fr;gap:8px;padding:12px}
.sb{background:var(--s3);border-radius:8px;padding:12px;text-align:center;border:1px solid var(--rim)}
.sb-n{font-family:monospace;font-size:24px;font-weight:700;margin-bottom:2px}
.sb-l{font-size:9px;color:var(--d);letter-spacing:1px;text-transform:uppercase}
.leader{display:flex;align-items:center;gap:10px;margin:0 12px 12px;padding:11px;background:var(--s3);border-radius:8px;border:1px solid var(--rim)}
.leader img{width:38px;height:38px;object-fit:contain}
.leader-n{font-size:13px;font-weight:600}
.leader-s{font-size:11px;color:var(--d);margin-top:1px}

/* ══ LEAGUE LIST ══ */
.ll{display:flex;align-items:center;gap:10px;padding:10px 14px;border-bottom:1px solid rgba(255,255,255,.04);transition:background .1s}
.ll:hover{background:rgba(255,255,255,.02)}
.ll.on{background:rgba(255,255,255,.04)}
.ll-flag{font-size:20px;flex-shrink:0}
.ll-nm{font-size:12px;font-weight:500}
.ll-id{font-family:monospace;font-size:9px;color:var(--d);margin-top:1px}
.ll-badge{font-size:9px;font-family:monospace;background:var(--ac);color:#000;padding:2px 7px;border-radius:3px;font-weight:700;margin-left:auto}
.ll-grp{padding:6px 14px;font-size:9px;letter-spacing:2px;color:var(--d);background:var(--s1);border-bottom:1px solid var(--rim);font-weight:600}

/* ══ EMPTY ══ */
.empty{padding:32px;text-align:center;color:var(--d);font-size:12px}
.empty i{font-size:32px;display:block;margin-bottom:6px;font-style:normal}

/* ══ FOOTER ══ */
footer{text-align:center;padding:16px;font-size:10px;color:var(--d);border-top:1px solid var(--rim);margin-top:10px}

/* ══ ANIMS ══ */
@keyframes blink{0%,100%{opacity:1}50%{opacity:.3}}
@keyframes glow{0%,100%{box-shadow:0 0 8px var(--ac)}50%{box-shadow:0 0 18px var(--ac)}}
@keyframes up{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
</style>
</head>
<body>

<!-- ══ TOPBAR ═══════════════════════════════ -->
<div class="topbar">
  <div class="tb1">
    <div class="brand">
      <div class="brand-top"><span class="brand-dot"></span>JAYNES MAX TV</div>
      <div class="brand-sub">⚽ Football Live</div>
    </div>
    <div class="search-wrap">
      <div class="search-box">
        <span class="search-icon">🔍</span>
        <input type="text" id="global-search" placeholder="Tafuta timu, mechi..." oninput="globalSearch(this.value)" autocomplete="off">
      </div>
    </div>
    <?php if(!empty($live)): ?>
      <span class="live-tag" style="margin-left:12px">● LIVE <?=count($live)?></span>
    <?php endif; ?>
    <div class="tb-clk" id="clk">──:──:──</div>
  </div>
  <div class="tb2">
    <div class="tb2-inner">
      <?php
      $gs=[];
      $gl=['Africa'=>'🌍 AFRICA','Europe'=>'🌍 EUROPE','World'=>'🌎 WORLD'];
      foreach($LEAGUES as $k=>$lg):
        $g=$lg['grp']??'Other';
        if(!in_array($g,$gs)){$gs[]=$g;echo '<span class="grp-lbl">'.($gl[$g]??$g).'</span>';}
      ?>
      <a href="?l=<?=$k?>" class="ltab <?=$k===$slug?'on':''?>"
         style="<?=$k===$slug?"--ac:{$lg['color']}":''?>">
        <?=$lg['flag']?> <?=$lg['name']?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ══ LIVE STRIP ════════════════════════════ -->
<?php if(!empty($live)): ?>
<div class="live-strip">
  <div class="live-wrap">
    <div class="live-hd"><span class="ldot"></span>LIVE SASA HIVI</div>
    <div class="live-list">
      <?php foreach($live as $m): ?>
      <a href="?l=<?=$slug?>&fx=<?=$m['event_key']?>" class="lc">
        <div class="lt2"><img src="<?=htmlspecialchars($m['home_team_logo']??'')?>" onerror="this.style.display='none'" alt=""><span><?=htmlspecialchars($m['event_home_team']??'')?></span></div>
        <div class="lsc"><div class="lsc-n"><?=htmlspecialchars($m['event_final_result']??'0-0')?></div><span class="lsc-m"><?=htmlspecialchars($m['event_status']??'')?>'</span></div>
        <div class="lt2 r"><img src="<?=htmlspecialchars($m['away_team_logo']??'')?>" onerror="this.style.display='none'" alt=""><span><?=htmlspecialchars($m['event_away_team']??'')?></span></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
<?php endif; ?>

<!-- ══ MAIN PAGE ══════════════════════════════ -->
<div class="page">

  <!-- LEFT: STANDINGS + SCORERS -->
  <div class="col">

    <!-- JEDWALI -->
    <div class="panel">
      <div class="pac"></div>
      <div class="phd">
        <span><?=$L['flag']?></span>
        <h3>Jedwali la Ligi</h3>
        <span class="ptag"><?=$L['name']?></span>
      </div>
      <!-- SEARCH -->
      <div class="st-search">
        <input type="text" id="st-srch" placeholder="🔍 Tafuta timu..." oninput="filterTable(this.value)">
      </div>
      <?php if(empty($table)): ?>
        <div class="empty"><i>📋</i>Hakuna jedwali</div>
      <?php else: ?>
      <div class="st-wrap">
      <table class="st" id="st-table">
        <thead>
          <tr>
            <th>#</th>
            <th>Timu</th>
            <th title="Mechi">P</th>
            <th title="Ushindi">W</th>
            <th title="Sare">D</th>
            <th title="Kushindwa">L</th>
            <th title="Tofauti ya Magoli">GD</th>
            <th title="Pointi">PTS</th>
          </tr>
        </thead>
        <tbody>
        <?php
        $total=count($table);
        foreach($table as $i=>$row):
          $pos = $row['standing_place'] ?? '?';
          $tn  = $row['standing_team']  ?? $row['team_name'] ?? '?';
          $tl  = $row['team_logo']      ?? '';
          $pl  = $row['standing_P']     ?? 0;
          $w   = $row['standing_W']     ?? 0;
          $d2  = $row['standing_D']     ?? 0;
          $l   = $row['standing_L']     ?? 0;
          $gf  = $row['standing_F']     ?? 0;
          $ga  = $row['standing_A']     ?? 0;
          $gd  = $row['standing_GD']    ?? 0;
          $pts = $row['standing_PTS']   ?? 0;
          $rc  = match((int)$pos){1=>'rk1',2=>'rk2',3=>'rk3',default=>'rko'};
          $zone='';
          if($total>=10){
            if((int)$pos<=4) $zone='zone-ucl';
            elseif((int)$pos<=6) $zone='zone-uecl';
            elseif((int)$pos>=$total-2) $zone='zone-rel';
          }
        ?>
        <tr class="st-row <?=$zone?>" data-team="<?=strtolower(htmlspecialchars($tn))?>">
          <td><span class="rk <?=$rc?>"><?=$pos?></span></td>
          <td>
            <div class="tc">
              <?php if($tl):?><img src="<?=htmlspecialchars($tl)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
              <?=htmlspecialchars($tn)?>
            </div>
          </td>
          <td><?=$pl?></td>
          <td><?=$w?></td>
          <td><?=$d2?></td>
          <td><?=$l?></td>
          <td><?=$gd>0?'+'.$gd:$gd?></td>
          <td class="pts-c"><?=$pts?></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      </div>
      <!-- Zone legend -->
      <div style="display:flex;gap:12px;padding:8px 14px;font-size:10px;color:var(--d);border-top:1px solid var(--rim);background:var(--s1)">
        <span><span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:2px;margin-right:4px"></span>UCL</span>
        <span><span style="display:inline-block;width:8px;height:8px;background:#f97316;border-radius:2px;margin-right:4px"></span>UEL/UECL</span>
        <span><span style="display:inline-block;width:8px;height:8px;background:#ef4444;border-radius:2px;margin-right:4px"></span>Relegation</span>
      </div>
      <?php endif; ?>
    </div>

    <!-- TOP SCORERS -->
    <div class="panel">
      <div class="pac"></div>
      <div class="phd"><h3>🥇 Washambuliaji Bora</h3></div>
      <?php if(empty($scorers)): ?>
        <div class="empty"><i>⚽</i>Hakuna data</div>
      <?php else: ?>
        <?php foreach($scorers as $i=>$e):
          $pn=trim($e['player_name']??$e['player_key_name']??'—');
          $pp=$e['player_image']??'';
          $pc=$e['team_name']??'';
          $pg=$e['goals']??$e['player_goals']??0;
        ?>
        <div class="scr">
          <div class="scr-pos <?=$i===0?'t':''?>"><?=$i+1?></div>
          <img class="scr-img" src="<?=htmlspecialchars($pp)?>" onerror="this.src='https://placehold.co/34/141d2e/4a6080?text=P'" alt="">
          <div class="scr-info">
            <div class="scr-nm"><?=htmlspecialchars($pn)?></div>
            <div class="scr-cl"><?=htmlspecialchars($pc)?></div>
          </div>
          <div class="scr-g"><?=$pg?><span class="scr-gl">goli</span></div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>

  <!-- CENTER: FIXTURES + DETAIL -->
  <div class="col">

    <!-- NEXT MATCH -->
    <?php if($nFx && !$fxId): ?>
    <div class="nm">
      <div class="nm-lbl">📅 MECHI INAYOFUATA</div>
      <div class="nm-vs">
        <div class="nm-t">
          <img src="<?=htmlspecialchars($nFx['home_team_logo']??'')?>" onerror="this.style.display='none'" alt="">
          <div class="nm-tn"><?=htmlspecialchars($nFx['event_home_team']??'')?></div>
        </div>
        <div class="nm-mid">VS</div>
        <div class="nm-t">
          <img src="<?=htmlspecialchars($nFx['away_team_logo']??'')?>" onerror="this.style.display='none'" alt="">
          <div class="nm-tn"><?=htmlspecialchars($nFx['event_away_team']??'')?></div>
        </div>
      </div>
      <div class="nm-date">
        🕐 <?=fd($nFx['event_date']??'',$nFx['event_time']??'')?>
        <?php if($nFx['event_stadium']??''):?> · <?=htmlspecialchars($nFx['event_stadium'])?><?php endif;?>
      </div>
    </div>
    <?php endif; ?>

    <!-- FIXTURE DETAIL -->
    <?php if($fxId && !empty($fxData)):
      $dh=$fxData['event_home_team']??''; $da=$fxData['event_away_team']??'';
      $dhl=$fxData['home_team_logo']??''; $dal=$fxData['away_team_logo']??'';
      $dsc=$fxData['event_final_result']??$fxData['event_halftime_result']??'? – ?';
      $dst=$fxData['event_status']??''; $dlv=$fxData['event_live']??'0';
    ?>
    <div class="panel">
      <div class="pac"></div>
      <div class="det-hero">
        <div class="det-teams">
          <div class="det-t">
            <img src="<?=htmlspecialchars($dhl)?>" onerror="this.style.display='none'" alt="">
            <div class="det-t-n"><?=htmlspecialchars($dh)?></div>
          </div>
          <div>
            <div class="det-sc"><?=htmlspecialchars($dsc)?></div>
            <div class="det-st">
              <?php if($dst==='Finished'):?><span style="color:var(--go)">✅ Mwisho</span>
              <?php elseif($dlv==='1'):?><span style="color:var(--re)">● LIVE</span>
              <?php else:?><?=fd($fxData['event_date']??'',$fxData['event_time']??'')?><?php endif;?>
            </div>
          </div>
          <div class="det-t">
            <img src="<?=htmlspecialchars($dal)?>" onerror="this.style.display='none'" alt="">
            <div class="det-t-n"><?=htmlspecialchars($da)?></div>
          </div>
        </div>
        <div class="det-meta">
          <?php if($fxData['league_name']??''):?><span>🏆 <?=htmlspecialchars($fxData['league_name'])?></span><?php endif;?>
          <?php if($fxData['event_stadium']??''):?><span>🏟 <?=htmlspecialchars($fxData['event_stadium'])?></span><?php endif;?>
          <?php if($fxData['event_referee']??''):?><span>👨‍⚖️ <?=htmlspecialchars($fxData['event_referee'])?></span><?php endif;?>
        </div>
      </div>
      <div class="tabs" id="dtabs">
        <div class="tab on" data-p="plu">👥 Kikosi</div>
        <div class="tab" data-p="pst">📊 Takwimu</div>
        <div class="tab" data-p="ph2">🔁 H2H</div>
      </div>

      <!-- LINEUP -->
      <div id="plu" class="tp on">
        <?php if(!empty($homeLU)||!empty($awayLU)): ?>
        <div class="lu-wrap">
          <?php foreach([
            ['nm'=>$dh,'lg'=>$dhl,'st'=>$homeLU,'sb'=>$homeSub,'fm'=>$homeForm],
            ['nm'=>$da,'lg'=>$dal,'st'=>$awayLU,'sb'=>$awaySub,'fm'=>$awayForm],
          ] as $side): ?>
          <div class="lu-col">
            <div class="lu-team-hd">
              <img src="<?=htmlspecialchars($side['lg'])?>" onerror="this.style.display='none'" alt="">
              <div class="lu-team-n"><?=htmlspecialchars($side['nm'])?></div>
              <?php if($side['fm']):?><span class="lu-fm"><?=htmlspecialchars($side['fm'])?></span><?php endif;?>
            </div>
            <?php if(!empty($side['st'])): ?>
              <div class="lu-sec">Wakuu</div>
              <?php foreach($side['st'] as $pl):
                $pn=$pl['lineup_player']??''; $pno=$pl['lineup_number']??'';
                $pp=$pl['player_photo']??''; $ppos=$pl['lineup_position']??'';
                $prat=$pl['player_rating']??null;
              ?>
              <div class="prow">
                <span class="pnum"><?=$pno?></span>
                <img class="ppic" src="<?=htmlspecialchars($pp)?>" onerror="this.src='https://placehold.co/26/141d2e/4a6080?text=P'" alt="">
                <div class="pinf"><div class="pnm"><?=htmlspecialchars($pn)?></div><div class="ppos"><?=htmlspecialchars($ppos)?></div></div>
                <?php if($prat):?><div class="prat <?=ratc((float)$prat)?>"><?=number_format((float)$prat,1)?></div><?php endif;?>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
            <?php if(!empty($side['sb'])): ?>
              <div class="lu-sec" style="margin-top:5px">Wabadilishaji</div>
              <?php foreach($side['sb'] as $pl):
                $pn=$pl['lineup_player']??''; $pno=$pl['lineup_number']??''; $pp=$pl['player_photo']??'';
              ?>
              <div class="prow sub">
                <span class="pnum"><?=$pno?></span>
                <img class="ppic" src="<?=htmlspecialchars($pp)?>" onerror="this.src='https://placehold.co/26/141d2e/4a6080?text=P'" alt="">
                <div class="pinf"><div class="pnm"><?=htmlspecialchars($pn)?></div><div class="ppos">Mbadala</div></div>
              </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
          <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="lu-empty"><span>👥</span>Kikosi hakijatangazwa bado<br><small style="font-size:10px;margin-top:4px;display:block">Hutangazwa karibu na siku ya mechi</small></div>
        <?php endif; ?>
      </div>

      <!-- STATS -->
      <div id="pst" class="tp">
        <?php if(!empty($homeStats)): ?>
          <?php foreach($homeStats as $s):
            $hn=$s['home']??'0'; $an=$s['away']??'0'; $nm=$s['type']??'';
            $tot=(float)$hn+(float)$an; $pct=$tot>0?round((float)$hn/$tot*100):50;
          ?>
          <div class="stat-row">
            <div class="stat-lbl"><span class="hv"><?=$hn?></span><span class="nm"><?=htmlspecialchars($nm)?></span><span class="av"><?=$an?></span></div>
            <div class="stat-bar"><div class="stat-fill" style="width:<?=$pct?>%"></div></div>
          </div>
          <?php endforeach; ?>
        <?php else: ?><div class="empty"><i>📊</i>Takwimu zinapatikana wakati wa mechi</div><?php endif; ?>
      </div>

      <!-- H2H -->
      <div id="ph2" class="tp">
        <?php if(!empty($h2h)): ?>
          <div style="padding:7px 14px;font-size:10px;letter-spacing:2px;color:var(--d);background:var(--s1);border-bottom:1px solid var(--rim);font-weight:600">HISTORIA YA MECHI 5</div>
          <?php foreach($h2h as $hg):
            $hhn=$hg['event_home_team']??''; $han=$hg['event_away_team']??'';
            $hhl=$hg['home_team_logo']??''; $hal=$hg['away_team_logo']??'';
            $hsc=$hg['event_final_result']??'–';
          ?>
          <div class="h2h-row">
            <div class="h2ht"><img src="<?=htmlspecialchars($hhl)?>" onerror="this.style.display='none'" alt=""><?=htmlspecialchars($hhn)?></div>
            <div class="h2hs"><?=htmlspecialchars($hsc)?></div>
            <div class="h2ht r"><?=htmlspecialchars($han)?><img src="<?=htmlspecialchars($hal)?>" onerror="this.style.display='none'" alt=""></div>
          </div>
          <?php endforeach; ?>
        <?php else: ?><div class="empty"><i>🔁</i>Hakuna historia</div><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>

    <!-- MECHI LIST -->
    <div class="panel">
      <div class="pac"></div>
      <div class="phd"><h3>📋 Mechi & Jedwali</h3><span class="ptag"><?=$L['name']?></span></div>
      <div class="tabs" id="fxtabs">
        <div class="tab on" data-p="pup">Zijazo</div>
        <div class="tab" data-p="prc">Zilizopita</div>
        <div class="tab" data-p="pst2">🏆 Jedwali</div>
      </div>

      <!-- UPCOMING -->
      <div id="pup" class="tp on">
        <div class="fx-search">
          <input type="text" id="fx-up-srch" placeholder="🔍 Tafuta timu..." oninput="filterFx('up',this.value)">
        </div>
        <?php if(empty($upcoming)): ?>
          <div class="empty"><i>📅</i>Hakuna mechi zilizopangwa</div>
        <?php else: ?>
          <?php
          $curDate='';
          foreach($upcoming as $fx):
            $fid=$fx['event_key']; $sel=$fid==$fxId?'sel':'';
            $fdate=$fx['event_date']??'';
            $tns=strtolower(($fx['event_home_team']??'').' '.($fx['event_away_team']??''));
            if($fdate!==$curDate){ $curDate=$fdate;
              echo '<div class="fx-date-grp">📅 '.date('l, d M Y',strtotime($fdate)).'</div>';
            }
          ?>
          <a href="?l=<?=$slug?>&fx=<?=$fid?>" class="fxrow <?=$sel?>" data-teams="<?=htmlspecialchars($tns)?>">
            <div class="fxt"><img src="<?=htmlspecialchars($fx['home_team_logo']??'')?>" onerror="this.style.display='none'" alt=""><?=htmlspecialchars($fx['event_home_team']??'')?></div>
            <div class="fxm"><div class="fxm-s">VS</div><div class="fxm-d"><span class="bu"><?=htmlspecialchars($fx['event_time']??'')?></span></div></div>
            <div class="fxt r"><img src="<?=htmlspecialchars($fx['away_team_logo']??'')?>" onerror="this.style.display='none'" alt=""><?=htmlspecialchars($fx['event_away_team']??'')?></div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>

      <!-- RECENT -->
      <div id="prc" class="tp">
        <div class="fx-search">
          <input type="text" id="fx-rc-srch" placeholder="🔍 Tafuta timu..." oninput="filterFx('rc',this.value)">
        </div>
        <?php if(empty($recent)): ?>
          <div class="empty"><i>⚽</i>Hakuna matokeo</div>
        <?php else: ?>
          <?php
          $curDate2='';
          foreach(array_reverse($recent) as $fx):
            $fid=$fx['event_key']; $sel=$fid==$fxId?'sel':'';
            $fdate=$fx['event_date']??'';
            $sc=$fx['event_final_result']??$fx['event_halftime_result']??'–';
            $isLv=($fx['event_live']??'0')==='1';
            $tns=strtolower(($fx['event_home_team']??'').' '.($fx['event_away_team']??''));
            if($fdate!==$curDate2){ $curDate2=$fdate;
              echo '<div class="fx-date-grp">📅 '.date('l, d M Y',strtotime($fdate)).'</div>';
            }
          ?>
          <a href="?l=<?=$slug?>&fx=<?=$fid?>" class="fxrow <?=$sel?>" data-teams="<?=htmlspecialchars($tns)?>">
            <div class="fxt"><img src="<?=htmlspecialchars($fx['home_team_logo']??'')?>" onerror="this.style.display='none'" alt=""><?=htmlspecialchars($fx['event_home_team']??'')?></div>
            <div class="fxm">
              <div class="fxm-s"><?=htmlspecialchars($sc)?></div>
              <div class="fxm-d"><?php if($isLv):?><span class="bl">LIVE</span><?php else:?><span class="be"><?=htmlspecialchars($fdate)?></span><?php endif;?></div>
            </div>
            <div class="fxt r"><img src="<?=htmlspecialchars($fx['away_team_logo']??'')?>" onerror="this.style.display='none'" alt=""><?=htmlspecialchars($fx['event_away_team']??'')?></div>
          </a>
          <?php endforeach; ?>
        <?php endif; ?>
      </div>
      <!-- STANDINGS IN CENTER -->
      <div id="pst2" class="tp">
        <?php if(empty($table)): ?>
          <div class="empty"><i>📋</i>Hakuna jedwali</div>
        <?php else: ?>
        <div class="st-search" style="padding:10px 14px;background:var(--s1);border-bottom:1px solid var(--rim)">
          <input type="text" id="st-srch2" placeholder="🔍 Tafuta timu..." oninput="filterTable2(this.value)" style="width:100%;background:var(--s3);border:1px solid var(--rim);border-radius:5px;padding:6px 10px;color:var(--w);font-size:12px;font-family:var(--ff);outline:none">
        </div>
        <table class="st" id="st-table2">
          <thead>
            <tr>
              <th>#</th><th>Timu</th><th>P</th><th>W</th><th>D</th><th>L</th>
              <th>GF</th><th>GA</th><th>GD</th><th title="Pointi">PTS</th>
            </tr>
          </thead>
          <tbody>
          <?php
          $total2=count($table);
          foreach($table as $i=>$row):
            $pos = $row['standing_place'] ?? '?';
            $tn  = $row['standing_team']  ?? $row['team_name'] ?? '?';
            $tl  = $row['team_logo']      ?? '';
            $pl  = $row['standing_P']     ?? 0;
            $w   = $row['standing_W']     ?? 0;
            $d2  = $row['standing_D']     ?? 0;
            $l   = $row['standing_L']     ?? 0;
            $gf  = $row['standing_F']     ?? 0;
            $ga  = $row['standing_A']     ?? 0;
            $gd  = $row['standing_GD']    ?? 0;
            $pts = $row['standing_PTS']   ?? 0;
            $rc  = match((int)$pos){1=>'rk1',2=>'rk2',3=>'rk3',default=>'rko'};
            $zone='';
            if($total2>=10){
              if((int)$pos<=4) $zone='zone-ucl';
              elseif((int)$pos<=6) $zone='zone-uecl';
              elseif((int)$pos>=$total2-2) $zone='zone-rel';
            }
          ?>
          <tr class="st-row2 <?=$zone?>" data-team2="<?=strtolower(htmlspecialchars($tn))?>">
            <td><span class="rk <?=$rc?>"><?=$pos?></span></td>
            <td>
              <div class="tc">
                <?php if($tl):?><img src="<?=htmlspecialchars($tl)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
                <?=htmlspecialchars($tn)?>
              </div>
            </td>
            <td><?=$pl?></td><td><?=$w?></td><td><?=$d2?></td><td><?=$l?></td>
            <td><?=$gf?></td><td><?=$ga?></td>
            <td><?=$gd>0?'+'.$gd:$gd?></td>
            <td class="pts-c"><?=$pts?></td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <div style="display:flex;gap:12px;padding:8px 14px;font-size:10px;color:var(--d);border-top:1px solid var(--rim);background:var(--s1)">
          <span><span style="display:inline-block;width:8px;height:8px;background:#3b82f6;border-radius:2px;margin-right:4px"></span>UCL</span>
          <span><span style="display:inline-block;width:8px;height:8px;background:#f97316;border-radius:2px;margin-right:4px"></span>UEL/UECL</span>
          <span><span style="display:inline-block;width:8px;height:8px;background:#ef4444;border-radius:2px;margin-right:4px"></span>Relegation</span>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- RIGHT: STATS + LEAGUES -->
  <div class="col">

    <div class="panel">
      <div class="pac"></div>
      <div class="phd"><h3>📊 Takwimu</h3></div>
      <?php
        $tp=0;$tg=0;
        foreach($table as $r2){ $tp+=$r2['standing_P']??0; $tg+=$r2['standing_F']??0; }
        $avg=$tp>0?round($tg/max($tp/2,1),2):0;
        $ld=$table[0]??null;
      ?>
      <div class="sb-grid">
        <div class="sb"><div class="sb-n" style="color:var(--bl)"><?=count($table)?></div><div class="sb-l">Timu</div></div>
        <div class="sb"><div class="sb-n" style="color:var(--go)"><?=$avg?></div><div class="sb-l">Goli/Mechi</div></div>
        <div class="sb"><div class="sb-n" style="color:var(--wa)"><?=$tg?></div><div class="sb-l">Magoli</div></div>
        <div class="sb"><div class="sb-n" style="color:var(--re)"><?=!empty($scorers)?($scorers[0]['goals']??0):0?></div><div class="sb-l">Rekodi</div></div>
      </div>
      <?php if($ld): ?>
      <div class="leader">
        <img src="<?=htmlspecialchars($ld['team_logo']??'')?>" onerror="this.style.display='none'" alt="">
        <div>
          <div class="leader-n"><?=htmlspecialchars($ld['standing_team']??$ld['team_name']??'')?></div>
          <div class="leader-s">🏆 Kiongozi · <?=$ld['standing_PTS']??0?> pts</div>
        </div>
      </div>
      <?php endif; ?>
    </div>

    <div class="panel">
      <div class="pac"></div>
      <div class="phd"><h3>🌍 Liga Zote</h3></div>
      <?php
      $gs2=[];
      foreach($LEAGUES as $k=>$lg):
        $g=$lg['grp']??'Other';
        if(!in_array($g,$gs2)){$gs2[]=$g;echo '<div class="ll-grp">'.($gl[$g]??$g).'</div>';}
      ?>
      <a href="?l=<?=$k?>" class="ll <?=$k===$slug?'on':''?>"
         style="<?=$k===$slug?"border-left:3px solid {$lg['color']}":''?>">
        <span class="ll-flag"><?=$lg['flag']?></span>
        <div>
          <div class="ll-nm" style="<?=$k===$slug?"color:{$lg['color']}":''?>"><?=$lg['name']?></div>
          <div class="ll-id">ID: <?=$lg['id']?></div>
        </div>
        <?php if($k===$slug):?><span class="ll-badge">ACTIVE</span><?php endif;?>
      </a>
      <?php endforeach; ?>
    </div>

    <div style="background:var(--s2);border:1px solid var(--rim);border-radius:10px;padding:12px 14px;font-size:11px;color:var(--d)">
      <div style="font-size:9px;letter-spacing:2px;color:var(--wa);margin-bottom:6px;font-weight:600">🔄 AUTO REFRESH</div>
      <?php if(!empty($live)):?><span style="color:var(--go)">✅ Inasasishwa kila sek 30</span><?php else:?>Hakuna LIVE kwa sasa<?php endif;?>
      <div style="margin-top:6px;padding-top:6px;border-top:1px solid var(--rim);font-family:monospace;font-size:10px">Sasisha: <strong style="color:var(--w)"><?=date('H:i:s')?></strong></div>
    </div>
  </div>
</div>

<footer>⚽ JAYNES MAX TV · Powered by AllSportsAPI · <?=date('d M Y H:i')?></footer>

<script>
// Clock
const clk=document.getElementById('clk');
setInterval(()=>{clk.textContent=new Date().toTimeString().slice(0,8)},1000);

// Tabs
document.querySelectorAll('.tabs').forEach(tg=>{
  tg.querySelectorAll('.tab').forEach(t=>{
    t.addEventListener('click',()=>{
      tg.querySelectorAll('.tab').forEach(x=>x.classList.remove('on'));
      t.classList.add('on');
      const card=tg.closest('.panel')||tg.parentElement;
      card.querySelectorAll('.tp').forEach(p=>p.classList.remove('on'));
      const el=card.querySelector('#'+t.dataset.p);
      if(el)el.classList.add('on');
    });
  });
});

// Filter standings table (left col)
function filterTable2(q){
  q=q.toLowerCase().trim();
  document.querySelectorAll('#st-table2 .st-row2').forEach(r=>{
    r.classList.toggle('st-hide', q && !r.dataset.team2.includes(q));
  });
}

// Filter standings table
function filterTable(q){
  q=q.toLowerCase().trim();
  document.querySelectorAll('#st-table .st-row').forEach(r=>{
    r.classList.toggle('st-hide', q && !r.dataset.team.includes(q));
  });
}

// Filter fixtures
function filterFx(tab, q){
  q=q.toLowerCase().trim();
  const sel=tab==='up'?'#pup .fxrow':'#prc .fxrow';
  document.querySelectorAll(sel).forEach(r=>{
    r.classList.toggle('fx-hide', q && !r.dataset.teams.includes(q));
  });
}

// Global search — searches both tables and fixtures
function globalSearch(q){
  q=q.toLowerCase().trim();
  if(!q){ filterTable(''); filterFx('up',''); filterFx('rc',''); return; }
  filterTable(q); filterFx('up',q); filterFx('rc',q);
  // Also fill local inputs
  const si=document.getElementById('st-srch');
  const ui=document.getElementById('fx-up-srch');
  const ri=document.getElementById('fx-rc-srch');
  if(si) si.value=q; if(ui) ui.value=q; if(ri) ri.value=q;
}

// Auto refresh
<?php if(!empty($live)):?>setTimeout(()=>location.reload(),30000);<?php endif;?>
</script>
</body>
</html>
