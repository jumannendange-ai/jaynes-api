<?php
// reset_notify.php — Futa state files ili auto_notify ianze upya
// FUTA baada ya kutumia!

$sf = __DIR__ . '/notify_state.json';
$ef = __DIR__ . '/sent_matches.json';

$results = array();

// Futa notify_state.json
if (file_exists($sf)) {
    if (unlink($sf)) {
        $results[] = array('file' => 'notify_state.json', 'status' => 'IMEFUTWA', 'ok' => true);
    } else {
        $results[] = array('file' => 'notify_state.json', 'status' => 'IMESHINDWA', 'ok' => false);
    }
} else {
    // Ipo tayari wazi
    $results[] = array('file' => 'notify_state.json', 'status' => 'HAIKUWEPO', 'ok' => true);
}

// Futa sent_matches.json
if (file_exists($ef)) {
    if (unlink($ef)) {
        $results[] = array('file' => 'sent_matches.json', 'status' => 'IMEFUTWA', 'ok' => true);
    } else {
        $results[] = array('file' => 'sent_matches.json', 'status' => 'IMESHINDWA', 'ok' => false);
    }
} else {
    $results[] = array('file' => 'sent_matches.json', 'status' => 'HAIKUWEPO', 'ok' => true);
}
?>
<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Reset Notify</title>
</head>
<body style="background:#060610;color:#e2e8f0;font-family:sans-serif;padding:20px;margin:0">
<div style="font-size:17px;font-weight:700;color:#00d4ff;margin-bottom:16px">🔄 RESET AUTO NOTIFY</div>

<?php foreach($results as $r): ?>
<div style="background:#0d0d22;border:1px solid <?php echo $r['ok']?'rgba(0,255,136,.2)':'rgba(255,68,102,.2)'; ?>;border-radius:12px;padding:14px;margin-bottom:10px;display:flex;align-items:center;gap:12px">
  <div style="font-size:22px"><?php echo $r['ok'] ? '✅' : '❌'; ?></div>
  <div>
    <div style="font-size:13px;font-weight:700;color:#fff"><?php echo $r['file']; ?></div>
    <div style="font-size:12px;color:<?php echo $r['ok']?'#00ff88':'#ff4466'; ?>;margin-top:2px"><?php echo $r['status']; ?></div>
  </div>
</div>
<?php endforeach; ?>

<div style="background:rgba(0,212,255,.08);border:1px solid rgba(0,212,255,.2);border-radius:12px;padding:14px;margin-top:6px;font-size:13px;color:#00d4ff">
  ✅ Auto notify imeanza upya! Notifications zitatumwa mechi zitapoanza.
</div>

<div style="background:rgba(255,165,0,.1);border:1px solid rgba(255,165,0,.3);border-radius:10px;padding:10px;font-size:11px;color:#ffaa00;margin-top:12px">
  ⚠️ Futa file hii baada ya kutumia!
</div>

<a href="debug_notify.php" style="display:block;text-align:center;margin-top:12px;padding:12px;background:#0d0d22;border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#00d4ff;text-decoration:none;font-weight:700;font-size:13px">
  🔍 Rudi Debug
</a>
</body>
</html>
