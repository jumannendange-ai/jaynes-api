<?php
// ════════════════════════════════════════════════════════════
//  ⚽ JAYNES MAX TV — Football Dashboard
// ════════════════════════════════════════════════════════════
define('APIKEY', '6095087686b995f2554af89bac81bb2b11d4182bdc4ff16c603d9130479f2736');
define('BASE',   'https://apiv2.allsportsapi.com/football/');

$LEAGUES = [
  'tz'    => ['id'=>551,  'ids'=>[551],             'name'=>'NBC Premier League',  'short'=>'NBC',  'flag'=>'🇹🇿', 'color'=>'#22c55e', 'grp'=>'Africa',  'past_days'=>90],
  'epl'   => ['id'=>152,  'ids'=>[152],             'name'=>'Premier League',      'short'=>'EPL',  'flag'=>'🏴󠁧󠁢󠁥󠁮󠁧󠁿', 'color'=>'#a855f7', 'grp'=>'Europe',  'past_days'=>30],
  'la'    => ['id'=>302,  'ids'=>[302],             'name'=>'La Liga',             'short'=>'LAL',  'flag'=>'🇪🇸', 'color'=>'#ef4444', 'grp'=>'Europe',  'past_days'=>30],
  'bund'  => ['id'=>175,  'ids'=>[175],             'name'=>'Bundesliga',          'short'=>'BUN',  'flag'=>'🇩🇪', 'color'=>'#f59e0b', 'grp'=>'Europe',  'past_days'=>30],
  'ucl'   => ['id'=>354,  'ids'=>[354,244,333],     'name'=>'Champions League',    'short'=>'UCL',  'flag'=>'⭐',  'color'=>'#3b82f6', 'grp'=>'Europe',  'past_days'=>180],
  'uel'   => ['id'=>480,  'ids'=>[480,334],         'name'=>'Europa League',       'short'=>'UEL',  'flag'=>'🟠',  'color'=>'#f97316', 'grp'=>'Europe',  'past_days'=>90],
  'cafc'  => ['id'=>346,  'ids'=>[346],             'name'=>'CAF Champions',       'short'=>'CAFC', 'flag'=>'🏆',  'color'=>'#10b981', 'grp'=>'Africa',  'past_days'=>60],
  'cafcc' => ['id'=>390,  'ids'=>[390],             'name'=>'CAF Confederation',   'short'=>'CACC', 'flag'=>'🌿',  'color'=>'#14b8a6', 'grp'=>'Africa',  'past_days'=>60],
  'afcon' => ['id'=>29,   'ids'=>[29],              'name'=>'AFCON',               'short'=>'AFCN', 'flag'=>'🌍',  'color'=>'#d97706', 'grp'=>'Africa',  'past_days'=>180],
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

// Jaribu IDs nyingi za ligi — rejesha matokeo ya kwanza yanayopatikana
// Hutumika hasa kwa UCL/UEL ambazo zinaweza kuwa na ID tofauti
function apiMulti(array $ids, array $params): array {
  foreach($ids as $lid) {
    $params['leagueId'] = $lid;
    $result = api($params);
    if(!empty($result)) return $result;
  }
  return [];
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
$L    = $LEAGUES[$slug];
$LID  = $L['id'];
$LIDS = $L['ids'] ?? [$LID];
$fxId = (int)($_GET['fx']??0);

$today      = date('Y-m-d');
$pastDays   = $L['past_days'] ?? 30;
$futureDays = ($slug === 'ucl' || $slug === 'uel') ? 60 : 21;
$past       = date('Y-m-d', strtotime("-{$pastDays} days"));
$future     = date('Y-m-d', strtotime("+{$futureDays} days"));

// Kwa UCL: pia angalia mwanzo wa msimu (Sep 2024)
if ($slug === 'ucl' || $slug === 'uel') {
  $seasonStart = '2024-09-01';
  if ($past > $seasonStart) $past = $seasonStart;
}

$standRaw = apiMulti($LIDS, ['met'=>'Standings']);
$table    = parseStandings($standRaw);

// DEBUG: Uncomment below to see raw API response
// echo '<pre style="background:#000;color:#0f0;padding:20px;font-size:11px;overflow:auto;max-height:400px">';
// echo 'RAW COUNT: '.count($standRaw)."\n";
// echo 'TABLE COUNT: '.count($table)."\n";
// echo 'FIRST RAW KEYS: '.implode(', ', array_keys($standRaw[0] ?? []))."\n";
// echo json_encode($standRaw, JSON_PRETTY_PRINT);
// echo '</pre>'; exit;

$recent   = apiMulti($LIDS, ['met'=>'Fixtures','from'=>$past,  'to'=>$today]);
$upcoming = apiMulti($LIDS, ['met'=>'Fixtures','from'=>$today, 'to'=>$future]);

// Sort recent: mpya zaidi kwanza
usort($recent, fn($a,$b) => strcmp($b['event_date']??'', $a['event_date']??''));

$liveAll  = api(['met'=>'Livescore']);
// Angalia live kwa IDs zote za ligi
$live = array_values(array_filter($liveAll, function($m) use($LIDS){
  return in_array((int)($m['league_key']??0), $LIDS);
}));
$scorers  = array_slice(apiMulti($LIDS, ['met'=>'Topscorers']), 0, 10);
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
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1">
<title>JAYNES MAX TV · Ratiba</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --bg:#060610;--s1:#0c0c1e;--s2:#101020;--s3:#141430;
  --accent:#00d4ff;--accent2:#ff4466;--gold:#ffd700;--green:#22c55e;--red:#ef4444;
  --text:#fff;--muted:#4a6080;--border:rgba(0,212,255,0.1);
  --ac:<?=$L['color']?>;
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html,body{background:var(--bg);color:var(--text);font-family:'Outfit',sans-serif;font-size:14px}
body{padding:0 0 80px}

/* TOPBAR */
.topbar{
  position:sticky;top:0;z-index:200;
  background:rgba(6,6,16,0.97);backdrop-filter:blur(20px);
  border-bottom:2px solid var(--ac);
  display:flex;align-items:center;gap:10px;
  padding:0 14px;height:54px;
}
.back-btn{width:38px;height:38px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;flex-shrink:0}
.tb-brand{font-family:'Bebas Neue',sans-serif;font-size:16px;letter-spacing:2px;flex:1}
.tb-brand span{color:var(--ac)}
.tb-clk{font-size:12px;color:var(--muted);font-family:monospace;flex-shrink:0}

/* LEAGUE TABS */
.ltabs{
  display:flex;overflow-x:auto;scrollbar-width:none;
  background:var(--s1);border-bottom:1px solid rgba(255,255,255,.06);
  padding:0 6px;
}
.ltabs::-webkit-scrollbar{display:none}
.ltab{
  display:flex;align-items:center;gap:5px;padding:10px 12px;
  font-size:11px;font-weight:700;color:var(--muted);
  white-space:nowrap;border-bottom:2px solid transparent;
  cursor:pointer;transition:all .2s;flex-shrink:0;
}
.ltab:hover{color:var(--text)}
.ltab.on{color:#fff;border-bottom-color:var(--ac)}

/* LIVE STRIP */
.live-strip{
  background:rgba(239,68,68,.07);border-bottom:1px solid rgba(239,68,68,.15);
  padding:10px 14px;display:none;
}
.live-strip.show{display:block}
.live-hdr{font-size:10px;letter-spacing:2px;color:var(--red);display:flex;align-items:center;gap:6px;margin-bottom:8px;font-weight:700}
.ldot{width:7px;height:7px;border-radius:50%;background:var(--red);animation:blink .8s infinite;flex-shrink:0}
@keyframes blink{0%,100%{opacity:1}50%{opacity:.2}}
.live-scroll{display:flex;gap:8px;overflow-x:auto;scrollbar-width:none;padding-bottom:2px}
.live-scroll::-webkit-scrollbar{display:none}
.live-card{
  flex-shrink:0;min-width:200px;
  background:rgba(239,68,68,.06);border:1px solid rgba(239,68,68,.18);
  border-radius:10px;padding:10px 12px;cursor:pointer;
  display:grid;grid-template-columns:1fr 52px 1fr;align-items:center;gap:4px;
}
.lteam{display:flex;align-items:center;gap:5px;font-size:11px;font-weight:600}
.lteam.r{flex-direction:row-reverse;text-align:right}
.lteam img{width:16px;height:16px;object-fit:contain}
.lscore{text-align:center}
.lscore-n{font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:2px;line-height:1}
.lscore-t{font-size:9px;color:var(--red);font-weight:700}

/* SECTION TABS */
.stabs{
  display:flex;background:var(--s1);
  border-bottom:1px solid rgba(255,255,255,.06);
}
.stab{
  flex:1;padding:11px 6px;text-align:center;
  font-size:12px;font-weight:700;color:var(--muted);
  cursor:pointer;border-bottom:2px solid transparent;transition:all .2s;
}
.stab.on{color:#fff;border-bottom-color:var(--ac);background:rgba(0,212,255,.04)}

/* SECTION CONTENT */
.section{display:none;padding:12px 14px}
.section.on{display:block}

/* LEAGUE INFO BANNER */
.league-info{
  margin:12px 14px 0;padding:12px 14px;
  background:linear-gradient(135deg,rgba(0,0,0,.4),rgba(0,0,0,.2));
  border:1px solid rgba(255,255,255,.08);border-left:3px solid var(--ac);
  border-radius:12px;display:flex;align-items:center;gap:10px;
}
.li-flag{font-size:26px;flex-shrink:0}
.li-info{flex:1;min-width:0}
.li-name{font-family:'Bebas Neue',sans-serif;font-size:17px;letter-spacing:2px;color:#fff}
.li-meta{font-size:10px;color:var(--muted);margin-top:2px}
.li-stats{display:flex;gap:14px;flex-shrink:0}
.li-stat{text-align:center}
.li-stat-n{font-family:'Bebas Neue',sans-serif;font-size:20px;color:var(--ac);line-height:1}
.li-stat-l{font-size:9px;color:var(--muted);text-transform:uppercase;letter-spacing:1px}

/* LIVESCORE TABLE */
.ls-table-wrap{padding:0 14px;overflow-x:auto}
.ls-date-grp{margin-bottom:16px}
.ls-date-lbl{
  font-size:10px;letter-spacing:2px;color:var(--muted);font-weight:700;
  text-transform:uppercase;padding:8px 0 6px;margin-bottom:0;
  display:flex;align-items:center;gap:8px;
}
.ls-date-lbl::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.06)}

/* TABLE */
.ls-tbl{width:100%;border-collapse:collapse;font-size:12px;table-layout:fixed}
.ls-tbl th{
  font-size:9px;color:var(--muted);font-weight:700;
  padding:6px 4px;text-align:center;border-bottom:1px solid rgba(255,255,255,.07);
  text-transform:uppercase;letter-spacing:.5px;
}
.ls-tbl th.th-team{text-align:left;padding-left:4px}
.ls-tbl td{padding:0;border-bottom:1px solid rgba(255,255,255,.04)}
.ls-tbl tr:last-child td{border-bottom:none}
.ls-tbl tr:hover td{background:rgba(255,255,255,.025)}
.ls-tbl colgroup .col-tm{width:auto}
.ls-tbl colgroup .col-sc{width:64px}
.ls-tbl colgroup .col-st{width:54px}

/* MATCH ROW */
.mrow{
  display:grid;
  grid-template-columns:1fr 64px 1fr;
  align-items:center;
  padding:9px 4px;
  gap:4px;
  cursor:pointer;
  transition:background .15s;
}
.mrow:hover{background:rgba(255,255,255,.03)}
.mrow-home{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;min-width:0}
.mrow-away{display:flex;align-items:center;gap:6px;font-size:12px;font-weight:600;min-width:0;flex-direction:row-reverse;text-align:right}
.mrow-home img,.mrow-away img{width:18px;height:18px;object-fit:contain;flex-shrink:0}
.mrow-tname{overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.mrow-mid{display:flex;flex-direction:column;align-items:center;gap:1px}
.mrow-score{font-family:'Bebas Neue',sans-serif;font-size:18px;color:var(--accent);letter-spacing:2px;line-height:1;white-space:nowrap}
.mrow-score.fin{color:rgba(255,255,255,.7)}
.mrow-time{font-size:9px;color:var(--gold);font-family:monospace;font-weight:700}
.mrow-live{font-size:9px;color:var(--red);font-weight:700;animation:blink .9s infinite}
.mrow-fin{font-size:9px;color:var(--muted)}
.mrow-vs{font-size:11px;color:var(--muted)}

/* STANDINGS TABLE */
.st-wrap{overflow-x:auto;-webkit-overflow-scrolling:touch}
.st{width:100%;border-collapse:collapse;font-size:12px}
.st th{font-size:10px;color:var(--muted);font-weight:600;padding:8px 6px;text-align:center;border-bottom:1px solid rgba(255,255,255,.07);white-space:nowrap}
.st th:nth-child(2){text-align:left;padding-left:8px}
.st td{padding:9px 6px;text-align:center;border-bottom:1px solid rgba(255,255,255,.04)}
.st td:nth-child(2){text-align:left;padding-left:8px}
.st tr:last-child td{border-bottom:none}
.st tr:hover td{background:rgba(255,255,255,.03)}
.tc{display:flex;align-items:center;gap:7px;font-weight:600;font-size:12px;white-space:nowrap}
.tc img{width:18px;height:18px;object-fit:contain;flex-shrink:0}
.rk{display:inline-flex;align-items:center;justify-content:center;width:22px;height:22px;border-radius:5px;font-size:11px;font-weight:700;flex-shrink:0}
.rk1{background:rgba(255,215,0,.2);color:var(--gold)}
.rk2{background:rgba(192,192,192,.15);color:#ccc}
.rk3{background:rgba(205,127,50,.15);color:#cd7f32}
.rko{color:var(--muted)}
.zone-ucl td:first-child{border-left:3px solid #3b82f6}
.zone-uecl td:first-child{border-left:3px solid #22c55e}
.zone-rel td:first-child{border-left:3px solid #ef4444}
.pts-c{font-weight:700;color:var(--text)}
.st-zone-key{display:flex;gap:14px;flex-wrap:wrap;padding:10px 14px;font-size:10px;color:var(--muted)}
.zk{display:flex;align-items:center;gap:5px}
.zk-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0}

/* SCORERS */
.scorer-row{
  display:flex;align-items:center;gap:10px;
  padding:10px 0;border-bottom:1px solid rgba(255,255,255,.05);
}
.scorer-row:last-child{border-bottom:none}
.sc-rank{width:24px;font-family:'Bebas Neue',sans-serif;font-size:18px;color:var(--muted);text-align:center;flex-shrink:0}
.sc-rank.top{color:var(--gold)}
.sc-info{flex:1;min-width:0}
.sc-name{font-weight:700;font-size:13px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sc-team{font-size:11px;color:var(--muted);margin-top:1px}
.sc-goals{font-family:'Bebas Neue',sans-serif;font-size:22px;color:var(--ac);flex-shrink:0}

/* SEARCH */
.search-row{padding:10px 14px;display:flex;gap:8px;background:var(--s1);border-bottom:1px solid rgba(255,255,255,.05)}
.search-row input{flex:1;padding:9px 12px;background:var(--s2);border:1px solid rgba(255,255,255,.08);border-radius:10px;color:var(--text);font-size:13px;font-family:'Outfit',sans-serif;outline:none}
.search-row input::placeholder{color:var(--muted)}

/* EMPTY */
.empty-box{text-align:center;padding:32px 20px;color:var(--muted)}
.empty-box i{font-size:36px;display:block;margin-bottom:10px;opacity:.25}

/* BOTTOM NAV */
.bot-nav{position:fixed;bottom:0;left:0;right:0;height:64px;background:rgba(6,6,16,0.97);backdrop-filter:blur(20px);border-top:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-around;align-items:center;z-index:200}
.bn{display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 16px;border-radius:12px;cursor:pointer;font-size:10px;font-weight:700;color:var(--muted);text-decoration:none;transition:all .2s}
.bn.on{background:rgba(0,212,255,.07);color:var(--accent)}
.bn i{font-size:18px}

@keyframes fadeup{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.section.on{animation:fadeup .25s ease}
</style>
</head>
<body>

<!-- TOPBAR -->
<div class="topbar">
  <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
  <div class="tb-brand">JAYNES <span>MAX TV</span> · <?=$L['flag']?> <?=$L['short']?></div>
  <div class="tb-clk" id="clk"></div>
</div>

<!-- LEAGUE TABS -->
<div class="ltabs">
<?php foreach($LEAGUES as $key=>$lg): ?>
  <div class="ltab<?=$key===$slug?' on':''?>" onclick="location.href='schedule.php?l=<?=$key?>'">
    <?=$lg['flag']?> <?=$lg['short']?>
  </div>
<?php endforeach; ?>
</div>

<!-- LIVE STRIP -->
<?php if(!empty($live)): ?>
<div class="live-strip show">
  <div class="live-hdr"><div class="ldot"></div> INAYOENDELEA SASA</div>
  <div class="live-scroll">
  <?php foreach($live as $m): ?>
    <?php
      $ht  = $m['event_home_team']??''; $at = $m['event_away_team']??'';
      $hg  = $m['event_home_final_result']??''; $ag = $m['event_away_final_result']??'';
      $min = $m['event_status']??'';
      $hlo = $m['home_team_logo']??''; $alo = $m['away_team_logo']??'';
    ?>
    <div class="live-card">
      <div class="lteam">
        <?php if($hlo):?><img src="<?=htmlspecialchars($hlo)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
        <span><?=htmlspecialchars(mb_substr($ht,0,12))?></span>
      </div>
      <div class="lscore">
        <div class="lscore-n"><?=$hg?> – <?=$ag?></div>
        <div class="lscore-t"><?=htmlspecialchars($min)?>'</div>
      </div>
      <div class="lteam r">
        <?php if($alo):?><img src="<?=htmlspecialchars($alo)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
        <span><?=htmlspecialchars(mb_substr($at,0,12))?></span>
      </div>
    </div>
  <?php endforeach; ?>
  </div>
</div>
<?php endif; ?>

<?php
// ── Taarifa ya ligi ──
$upcomingCount = count($upcoming);
$recentCount   = count($recent);
$teamsSet = [];
foreach(array_merge($upcoming,$recent) as $m){
  if(!empty($m['event_home_team'])) $teamsSet[$m['event_home_team']]=1;
  if(!empty($m['event_away_team'])) $teamsSet[$m['event_away_team']]=1;
}
?>
<!-- LEAGUE INFO BANNER -->
<div class="league-info">
  <div class="li-flag"><?=$L['flag']?></div>
  <div class="li-info">
    <div class="li-name"><?=htmlspecialchars($L['name'])?></div>
    <div class="li-meta"><?=$L['grp']?> · Msimu 2024/25</div>
  </div>
  <div class="li-stats">
    <div class="li-stat"><div class="li-stat-n"><?=$upcomingCount?></div><div class="li-stat-l">Zijazo</div></div>
    <div class="li-stat"><div class="li-stat-n"><?=$recentCount?></div><div class="li-stat-l">Matokeo</div></div>
    <div class="li-stat"><div class="li-stat-n"><?=count($teamsSet)?></div><div class="li-stat-l">Timu</div></div>
  </div>
</div>

<!-- SECTION TABS -->
<div class="stabs">
  <div class="stab on" id="tab-up" onclick="showSec('upcoming')">📅 Zijazo</div>
  <div class="stab"    id="tab-rc" onclick="showSec('recent')">🏁 Matokeo</div>
  <div class="stab"    id="tab-st" onclick="showSec('standings')">📊 Jedwali</div>
  <div class="stab"    id="tab-sc" onclick="showSec('scorers')">🥇 Wachezaji</div>
</div>

<!-- SEARCH -->
<div class="search-row">
  <input id="srch" placeholder="🔍 Tafuta timu..." oninput="doSearch(this.value)">
</div>

<!-- ══ UPCOMING ══ -->
<div class="section on" id="sec-upcoming">
<?php
// Timu maarufu za UCL/dunia
$famousTeams = [
  'real madrid','barcelona','manchester city','manchester united','liverpool',
  'arsenal','chelsea','tottenham','bayern munich','borussia dortmund',
  'paris saint-germain','psg','juventus','ac milan','inter milan','napoli',
  'atletico madrid','sevilla','ajax','porto','benfica','celtic','rangers',
  'simba sc','young africans','yanga',
];
if(!function_exists('isFamous')){
function isFamous(string $name, array $list): bool {
  $n = strtolower(trim($name));
  foreach($list as $f){ if(str_contains($n,$f)||str_contains($f,$n)) return true; }
  return false;
}
}

$byDate = [];
foreach($upcoming as $m) {
  $d = $m['event_date'] ?? date('Y-m-d');
  $byDate[$d][] = $m;
}
ksort($byDate);
if(empty($byDate)): ?>
  <div class="empty-box"><i class="fa fa-calendar-xmark"></i>Hakuna mechi zijazo</div>
<?php else: ?>
<div class="ls-table-wrap">
<?php foreach($byDate as $date => $matches):
  $isToday    = ($date === date('Y-m-d'));
  $isTomorrow = ($date === date('Y-m-d', strtotime('+1 day')));
  if($isToday)         $dayLabel = '📅 LEO — '.date('d M Y');
  elseif($isTomorrow)  $dayLabel = '📅 KESHO — '.date('d M Y', strtotime('+1 day'));
  else                 $dayLabel = '📅 '.date('D, d M Y', strtotime($date));
?>
  <div class="ls-date-grp">
    <div class="ls-date-lbl" style="color:<?=$isToday?'var(--accent)':'var(--muted)'?>"><?=$dayLabel?></div>
    <table class="ls-tbl">
      <thead>
        <tr>
          <th class="th-team" style="width:40%">Nyumbani</th>
          <th style="width:60px">Wakati</th>
          <th style="width:40%;text-align:right;padding-right:4px">Wageni</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($matches as $m):
        $ht  = $m['event_home_team']??''; $at = $m['event_away_team']??'';
        $hlo = $m['home_team_logo']??'';  $alo = $m['away_team_logo']??'';
        $tm  = $m['event_time']??'--:--';
        $mid = $m['event_key']??0;
        $round = $m['event_round']??$m['league_round']??'';
        $stage = $m['event_stage_name']??$m['stage_name']??'';
        $roundLabel = $stage ?: ($round ? 'Raundi '.$round : '');
        $isLiveMatch = false;
        foreach($live as $lv){ if(($lv['event_key']??'0')==$mid){ $isLiveMatch=true; break; } }
        $hFam = isFamous($ht, $famousTeams);
        $aFam = isFamous($at, $famousTeams);
      ?>
      <tr data-teams="<?=strtolower(htmlspecialchars($ht.' '.$at))?>"
          style="<?=($hFam||$aFam)?'background:rgba(59,130,246,.04);':''?>">
        <td style="padding:8px 4px">
          <?php if($roundLabel && $slug==='ucl'): ?>
            <div style="font-size:8px;color:var(--ac);font-weight:700;letter-spacing:.5px;margin-bottom:2px"><?=htmlspecialchars($roundLabel)?></div>
          <?php endif; ?>
          <div class="mrow-home">
            <?php if($hlo):?><img src="<?=htmlspecialchars($hlo)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
            <span class="mrow-tname" style="<?=$hFam?'font-weight:700':''?>"><?=htmlspecialchars(mb_substr($ht,0,16))?></span>
            <?php if($hFam):?><span style="font-size:8px;color:var(--gold);margin-left:2px">★</span><?php endif;?>
          </div>
        </td>
        <td style="padding:8px 2px;text-align:center">
          <?php if($isLiveMatch): ?>
            <div class="mrow-live">● LIVE</div>
          <?php else: ?>
            <div class="mrow-time"><?=htmlspecialchars($tm)?></div>
            <div style="font-size:8px;color:var(--muted)">vs</div>
          <?php endif; ?>
        </td>
        <td style="padding:8px 4px">
          <div class="mrow-away">
            <?php if($alo):?><img src="<?=htmlspecialchars($alo)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
            <span class="mrow-tname" style="<?=$aFam?'font-weight:700':''?>"><?=htmlspecialchars(mb_substr($at,0,16))?></span>
            <?php if($aFam):?><span style="font-size:8px;color:var(--gold);margin-left:2px">★</span><?php endif;?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- ══ RECENT / MATOKEO ══ -->
<div class="section" id="sec-recent">
<?php
$byDateR = [];
foreach(array_reverse($recent) as $m) {
  $d = $m['event_date'] ?? date('Y-m-d');
  $byDateR[$d][] = $m;
}
krsort($byDateR);
if(empty($byDateR)): ?>
  <div class="empty-box"><i class="fa fa-clock-rotate-left"></i>Hakuna matokeo</div>
<?php else: ?>
<div class="ls-table-wrap">
<?php foreach($byDateR as $date => $matches): ?>
  <div class="ls-date-grp">
    <div class="ls-date-lbl">🏁 <?=date('D, d M Y', strtotime($date))?></div>
    <table class="ls-tbl">
      <thead>
        <tr>
          <th class="th-team" style="width:38%">Nyumbani</th>
          <th style="width:68px">Matokeo</th>
          <th style="width:38%;text-align:right;padding-right:4px">Wageni</th>
        </tr>
      </thead>
      <tbody>
      <?php foreach($matches as $m):
        $ht  = $m['event_home_team']??''; $at = $m['event_away_team']??'';
        $hlo = $m['home_team_logo']??'';  $alo = $m['away_team_logo']??'';
        $hg  = $m['event_home_final_result']??'–';
        $ag  = $m['event_away_final_result']??'–';
        $hgn = is_numeric($hg) ? (int)$hg : -1;
        $agn = is_numeric($ag) ? (int)$ag : -1;
        $hW  = ($hgn > $agn);
        $aW  = ($agn > $hgn);
        $isDraw = ($hgn === $agn && $hgn >= 0);
        $round  = $m['event_round']??$m['league_round']??'';
        $stage  = $m['event_stage_name']??$m['stage_name']??'';
        $roundLabel = $stage ?: ($round ? 'Raundi '.$round : '');
        $hFam = isFamous($ht, $famousTeams);
        $aFam = isFamous($at, $famousTeams);
      ?>
      <tr data-teams="<?=strtolower(htmlspecialchars($ht.' '.$at))?>"
          style="<?=($hFam||$aFam)?'background:rgba(59,130,246,.04);':''?>">
        <td style="padding:8px 4px">
          <?php if($roundLabel && $slug==='ucl'): ?>
            <div style="font-size:8px;color:var(--ac);font-weight:700;letter-spacing:.5px;margin-bottom:2px"><?=htmlspecialchars($roundLabel)?></div>
          <?php endif; ?>
          <div class="mrow-home" style="<?=$hW?'opacity:1':'opacity:.55'?>;<?=$hFam?'color:#fff':'color:inherit'?>">
            <?php if($hlo):?><img src="<?=htmlspecialchars($hlo)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
            <span class="mrow-tname" style="<?=$hFam?'font-weight:700':''?>"><?=htmlspecialchars(mb_substr($ht,0,16))?></span>
            <?php if($hFam):?><span style="font-size:8px;color:var(--gold);margin-left:2px">★</span><?php endif;?>
          </div>
        </td>
        <td style="padding:8px 2px;text-align:center">
          <div class="mrow-score fin" style="<?=$isDraw?'color:var(--gold)':($hW?'color:var(--green)':'color:var(--accent2)')?>">
            <?=htmlspecialchars($hg)?>–<?=htmlspecialchars($ag)?>
          </div>
          <div class="mrow-fin">FT</div>
        </td>
        <td style="padding:8px 4px">
          <div class="mrow-away" style="<?=$aW?'opacity:1':'opacity:.55'?>;<?=$aFam?'color:#fff':'color:inherit'?>">
            <?php if($alo):?><img src="<?=htmlspecialchars($alo)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
            <span class="mrow-tname" style="<?=$aFam?'font-weight:700':''?>"><?=htmlspecialchars(mb_substr($at,0,16))?></span>
            <?php if($aFam):?><span style="font-size:8px;color:var(--gold);margin-left:2px">★</span><?php endif;?>
          </div>
        </td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
  </div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- ══ STANDINGS ══ -->
<div class="section" id="sec-standings">
<?php if(empty($table)): ?>
  <div class="empty-box"><i class="fa fa-table"></i>Hakuna jedwali</div>
<?php else: ?>
  <div class="st-wrap">
  <table class="st">
    <thead>
      <tr>
        <th>#</th><th>Timu</th><th>P</th><th>W</th><th>D</th><th>L</th><th>GD</th><th>PTS</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $total = count($table);
    foreach($table as $row):
      $pos = $row['standing_place']??'?';
      $tn  = $row['standing_team'] ?? $row['team_name']??'?';
      $tl  = $row['team_logo']??'';
      $pl  = $row['standing_P']??0; $w = $row['standing_W']??0;
      $d2  = $row['standing_D']??0; $l = $row['standing_L']??0;
      $gd  = $row['standing_GD']??0; $pts = $row['standing_PTS']??0;
      $rc  = match((int)$pos){1=>'rk1',2=>'rk2',3=>'rk3',default=>'rko'};
      $zone='';
      if($total>=10){
        if((int)$pos<=4) $zone='zone-ucl';
        elseif((int)$pos<=6) $zone='zone-uecl';
        elseif((int)$pos>=$total-2) $zone='zone-rel';
      }
    ?>
    <tr class="<?=$zone?>">
      <td><span class="rk <?=$rc?>"><?=$pos?></span></td>
      <td>
        <div class="tc">
          <?php if($tl):?><img src="<?=htmlspecialchars($tl)?>" onerror="this.style.display='none'" alt=""><?php endif;?>
          <?=htmlspecialchars(mb_substr($tn,0,18))?>
        </div>
      </td>
      <td><?=$pl?></td><td><?=$w?></td><td><?=$d2?></td><td><?=$l?></td>
      <td><?=$gd>0?'+'.$gd:$gd?></td>
      <td class="pts-c"><?=$pts?></td>
    </tr>
    <?php endforeach; ?>
    </tbody>
  </table>
  </div>
  <div class="st-zone-key">
    <div class="zk"><div class="zk-dot" style="background:#3b82f6"></div> UCL</div>
    <div class="zk"><div class="zk-dot" style="background:#22c55e"></div> UEFA</div>
    <div class="zk"><div class="zk-dot" style="background:#ef4444"></div> Kushuka</div>
  </div>
<?php endif; ?>
</div>

<!-- ══ SCORERS ══ -->
<div class="section" id="sec-scorers">
<?php if(empty($scorers)): ?>
  <div class="empty-box"><i class="fa fa-trophy"></i>Hakuna data</div>
<?php else: ?>
  <?php foreach($scorers as $i => $sc):
    $name  = $sc['player_name']??'?';
    $team  = $sc['team_name']??'';
    $goals = $sc['goals']??$sc['player_goals_count']??0;
  ?>
  <div class="scorer-row">
    <div class="sc-rank<?=$i<3?' top':''?>"><?=$i+1?></div>
    <div class="sc-info">
      <div class="sc-name"><?=htmlspecialchars($name)?></div>
      <div class="sc-team"><?=htmlspecialchars($team)?></div>
    </div>
    <div class="sc-goals"><?=$goals?> ⚽</div>
  </div>
  <?php endforeach; ?>
<?php endif; ?>
</div>

<!-- BOTTOM NAV -->
<nav class="bot-nav">
  <a href="home.html"    class="bn"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="bn"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="bn on"><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="bn"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="bn"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script src="_auth_guard.js"></script>
<script>
// Clock
setInterval(() => {
  document.getElementById('clk').textContent = new Date().toLocaleTimeString('sw-TZ',{hour:'2-digit',minute:'2-digit'});
}, 1000);

// Tabs
function showSec(id) {
  document.querySelectorAll('.section').forEach(s => s.classList.remove('on'));
  document.querySelectorAll('.stab').forEach(t => t.classList.remove('on'));
  document.getElementById('sec-'+id).classList.add('on');
  document.getElementById('tab-'+id).classList.add('on');
}

// Search
function doSearch(q) {
  q = q.toLowerCase().trim();
  document.querySelectorAll('.fx-card').forEach(c => {
    c.style.display = (!q || c.dataset.teams?.includes(q)) ? '' : 'none';
  });
  const grps = document.querySelectorAll('.fx-date-grp');
  grps.forEach(g => {
    const visible = [...g.querySelectorAll('.fx-card')].some(c => c.style.display !== 'none');
    g.style.display = visible ? '' : 'none';
  });
}

// Auto-refresh kama kuna live
<?php if(!empty($live)):?>
setTimeout(() => location.reload(), 30000);
<?php endif;?>
</script>
</body>
</html>
