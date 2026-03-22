<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

define('ALLSPORTS_KEY', '6095087686b995f2554af89bac81bb2b11d4182bdc4ff16c603d9130479f2736');
define('ALLSPORTS_URL', 'https://apiv2.allsportsapi.com/football/');
define('CHANNELS_URL',  'https://dde.ct.ws/channels.php?category=mechi+za+leo');
define('OS_APP_ID',     '10360777-3ada-4145-b83f-00eb0312a53f');
define('OS_REST_KEY',   'os_v2_app_ca3ao5z23jaulob7advqgevfh4qctlprzdauupekggukcgwmz5glfzdu6lkvnkzjeuno3cuuqow7fklo3fehp2puu52sr7sroo63hwy');

function doGet($url, $headers = array()) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'JaynesMaxTV/4.0');
    if (!empty($headers)) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $res  = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    $time = round(curl_getinfo($ch, CURLINFO_TOTAL_TIME), 2);
    curl_close($ch);
    return array('code'=>$code,'body'=>$res,'error'=>$err,'time'=>$time);
}
function ok($v){ return $v ? '#00ff88' : '#ff4466'; }
function row($l,$v,$good=null){
    $c = $good===null?'#e2e8f0':($good?'#00ff88':'#ff4466');
    echo '<tr><td style="color:#7788aa;padding:5px 10px;font-size:12px">'.htmlspecialchars($l).'</td>';
    echo '<td style="color:'.$c.';padding:5px 10px;font-size:12px;font-family:monospace;word-break:break-all">'.htmlspecialchars((string)$v).'</td></tr>';
}
?>
<!DOCTYPE html><html><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Debug</title></head>
<body style="background:#060610;color:#e2e8f0;font-family:sans-serif;padding:16px;margin:0">
<div style="font-size:17px;font-weight:700;color:#00d4ff;margin-bottom:4px">DEBUG — AUTO NOTIFY</div>
<div style="font-size:11px;color:#7788aa;margin-bottom:16px"><?php echo date('Y-m-d H:i:s'); ?></div>
<?php
$secs = array(
    'PHP' => array(
        array('PHP Version', PHP_VERSION, version_compare(PHP_VERSION,'7.0','>=')),
        array('curl', extension_loaded('curl')?'YES':'NO', extension_loaded('curl')),
        array('json', extension_loaded('json')?'YES':'NO', extension_loaded('json')),
        array('Dir writable', is_writable(__DIR__)?'YES':'NO', is_writable(__DIR__)),
        array('Time', date('Y-m-d H:i:s'), null),
    )
);

// AllSports Live
$r1 = doGet(ALLSPORTS_URL.'?met=Livescore&APIkey='.ALLSPORTS_KEY.'&timezone=Africa/Dar_es_Salaam');
$d1 = json_decode($r1['body'],true);
$lc = count(isset($d1['result'])?$d1['result']:array());
$secs['AllSports LIVE'] = array(
    array('HTTP', $r1['code'], $r1['code']==200),
    array('Error', $r1['error']?$r1['error']:'Hakuna', !$r1['error']),
    array('Time(s)', $r1['time'], null),
    array('Live matches', $lc, null),
);
if($lc>0){ $m=$d1['result'][0]; $secs['AllSports LIVE'][]=array('Sample',($m['event_home_team']??'?').' vs '.($m['event_away_team']??'?'),null); }

// AllSports Fixtures
$today = date('Y-m-d');
$r2 = doGet(ALLSPORTS_URL.'?met=Fixtures&leagueId=551&from='.$today.'&to='.$today.'&APIkey='.ALLSPORTS_KEY);
$d2 = json_decode($r2['body'],true);
$fc = count(isset($d2['result'])?$d2['result']:array());
$secs['NBC Fixtures Leo'] = array(
    array('HTTP', $r2['code'], $r2['code']==200),
    array('Error', $r2['error']?$r2['error']:'Hakuna', !$r2['error']),
    array('NBC mechi leo', $fc, $fc>0),
);
if($fc>0){
    foreach(array_slice($d2['result'],0,3) as $m){
        $secs['NBC Fixtures Leo'][]=array('Mchezo',($m['event_home_team']??'?').' vs '.($m['event_away_team']??'?').' @ '.($m['event_time']??'?'),null);
    }
}

// Channels
$r3 = doGet(CHANNELS_URL);
$d3 = json_decode($r3['body'],true);
$cc = count(isset($d3['channels'])?$d3['channels']:array());
$secs['channels.php'] = array(
    array('HTTP', $r3['code'], $r3['code']==200),
    array('Error', $r3['error']?$r3['error']:'Hakuna', !$r3['error']),
    array('Count', $cc, $cc>0),
);
if($cc>0){ $ch=$d3['channels'][0]; $secs['channels.php'][]=array('Sample',isset($ch['title'])?$ch['title']:(isset($ch['name'])?$ch['name']:'?'),null); }

// OneSignal
$r4 = doGet('https://onesignal.com/api/v1/apps/'.OS_APP_ID, array('Authorization: Key '.OS_REST_KEY));
$d4 = json_decode($r4['body'],true);
$secs['OneSignal'] = array(
    array('HTTP', $r4['code'], $r4['code']==200),
    array('Error', $r4['error']?$r4['error']:'Hakuna', !$r4['error']),
    array('App', isset($d4['name'])?$d4['name']:'N/A', isset($d4['id'])),
    array('Subscribers', isset($d4['players'])?$d4['players']:0, isset($d4['players'])&&$d4['players']>0),
);
if(isset($d4['errors'])){ $secs['OneSignal'][]=array('API Error',json_encode($d4['errors']),false); }

// Files
$sf=__DIR__.'/notify_state.json'; $ef=__DIR__.'/sent_matches.json';
$secs['Files'] = array(
    array('notify_state.json', file_exists($sf)?'ANA':'HIPO', null),
    array('sent_matches.json', file_exists($ef)?'ANA':'HIPO', null),
    array('Dir writable', is_writable(__DIR__)?'YES':'NO', is_writable(__DIR__)),
);
if(file_exists($sf)){ $sc=json_decode(file_get_contents($sf),true); $secs['Files'][]=array('State entries',count($sc?$sc:array()),null); }
if(file_exists($ef)){ $ec=json_decode(file_get_contents($ef),true); $secs['Files'][]=array('Sent IDs',count($ec?$ec:array()),null); }

foreach($secs as $title=>$rows){
    echo '<div style="background:#0d0d22;border:1px solid rgba(255,255,255,.08);border-radius:12px;margin-bottom:10px;overflow:hidden">';
    echo '<div style="padding:9px 14px;border-bottom:1px solid rgba(255,255,255,.06);font-size:13px;font-weight:700;color:#00d4ff">'.$title.'</div>';
    echo '<table style="width:100%;border-collapse:collapse">';
    foreach($rows as $r) row($r[0],$r[1],isset($r[2])?$r[2]:null);
    echo '</table></div>';
}
?>
<div style="background:rgba(255,165,0,.1);border:1px solid rgba(255,165,0,.3);border-radius:10px;padding:10px;font-size:11px;color:#ffaa00;margin-top:6px">
Futa file hii baada ya kutumia!
</div>
<button onclick="location.reload()" style="display:block;width:100%;margin-top:10px;padding:12px;background:#00d4ff;color:#000;border:none;border-radius:10px;font-weight:700;cursor:pointer">Refresh</button>
</body></html>
