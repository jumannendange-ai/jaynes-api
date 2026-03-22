<?php
$url  = isset($_GET['url'])  ? $_GET['url']  : '';
$key  = isset($_GET['key'])  ? $_GET['key']  : '';
$name = isset($_GET['name']) ? urldecode($_GET['name']) : 'JAYNES MAX TV';
?>
<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
<title><?=htmlspecialchars($name)?></title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@400;600;700&display=swap" rel="stylesheet">
<script src="https://cdnjs.cloudflare.com/ajax/libs/shaka-player/4.3.6/shaka-player.compiled.js"></script>
<style>
:root{--accent:#00d4ff;--accent2:#ff4466;--panel:rgba(6,6,18,0.97);}
*{margin:0;padding:0;box-sizing:border-box;-webkit-tap-highlight-color:transparent;}
html,body{width:100%;height:100%;background:#000;overflow:hidden;font-family:'Outfit',sans-serif;color:#fff;}

/* ═══ WRAP + VIDEO — full screen ═══ */
#wrap{position:fixed;inset:0;background:#000;overflow:hidden;}
video{
  position:absolute;inset:0;
  width:100%;height:100%;
  object-fit:contain;
  background:#000;display:block;
}
video.ratio-fill {object-fit:fill;}
video.ratio-cover{object-fit:cover;}
video.ratio-169,video.ratio-43,video.ratio-11,video.ratio-2310{object-fit:contain;}

/* ═══ HEADER — auto-hide overlay juu ═══ */
#hdr{
  position:absolute;top:0;left:0;right:0;z-index:40;
  padding:max(env(safe-area-inset-top),12px) 14px 36px;
  background:linear-gradient(to bottom,rgba(0,0,0,0.92) 0%,rgba(0,0,0,0.55) 55%,transparent 100%);
  display:flex;align-items:center;gap:12px;
  transition:opacity 0.35s,visibility 0.35s;
}
#hdr.hidden{opacity:0;visibility:hidden;pointer-events:none;}

.back-btn{
  width:42px;height:42px;background:rgba(0,0,0,0.65);
  border:1.5px solid rgba(255,255,255,0.28);border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  cursor:pointer;text-decoration:none;color:#fff;
  transition:all 0.22s;flex-shrink:0;
  box-shadow:0 2px 12px rgba(0,0,0,0.8);
}
.back-btn:hover,.back-btn:active{background:rgba(0,212,255,0.28);border-color:var(--accent);}
.ch-name{
  font-family:'Bebas Neue',sans-serif;font-size:19px;letter-spacing:2px;
  flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
  text-shadow:0 1px 10px rgba(0,0,0,1),0 0 20px rgba(0,0,0,0.8);
}
.live-pill{
  display:flex;align-items:center;gap:5px;
  background:rgba(255,68,102,0.22);border:1px solid rgba(255,68,102,0.4);
  color:var(--accent2);font-size:10px;font-weight:700;
  padding:5px 11px;border-radius:20px;white-space:nowrap;flex-shrink:0;
}
.ldot{width:6px;height:6px;background:var(--accent2);border-radius:50%;animation:blink 1s infinite;}
@keyframes blink{0%,100%{opacity:1}50%{opacity:0.2}}

/* ═══ CENTER PLAY ═══ */
#centerPlay{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);z-index:25;display:none;flex-direction:column;align-items:center;gap:12px;cursor:pointer;}
.play-ring{width:76px;height:76px;background:rgba(0,212,255,0.12);border:2px solid var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;backdrop-filter:blur(10px);transition:all 0.3s;}
#centerPlay:hover .play-ring{background:rgba(0,212,255,0.28);transform:scale(1.08);box-shadow:0 0 36px rgba(0,212,255,0.4);}
.play-ring i{font-size:28px;color:var(--accent);margin-left:4px;}
#centerPlay p{font-size:11px;color:rgba(255,255,255,0.6);letter-spacing:2px;text-shadow:0 1px 6px rgba(0,0,0,1);}

/* ═══ LOADING ═══ */
#loadBox{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);display:flex;flex-direction:column;align-items:center;gap:14px;z-index:26;}
.ring{width:56px;height:56px;border:3px solid rgba(0,212,255,0.12);border-top-color:var(--accent);border-radius:50%;animation:spin 0.75s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}
#loadBox p{font-size:12px;color:rgba(255,255,255,0.5);letter-spacing:1px;}

/* ═══════════════════════════════════════════════
   CONTROLS — overlay chini, auto-hide
   Gradient nzito sana = buttons zinaonekana
   dhidi ya video yoyote (nyeupe, mechi, nk)
   ═══════════════════════════════════════════════ */
#ctrl{
  position:absolute;bottom:0;left:0;right:0;z-index:40;
  padding:40px 12px max(env(safe-area-inset-bottom),14px);
  background:linear-gradient(
    to top,
    rgba(0,0,0,0.96) 0%,
    rgba(0,0,0,0.82) 30%,
    rgba(0,0,0,0.50) 60%,
    transparent      100%
  );
  transition:opacity 0.35s,visibility 0.35s;
}
#ctrl.hidden{opacity:0;visibility:hidden;pointer-events:none;}

/* Progress */
.prog-wrap{height:4px;background:rgba(255,255,255,0.2);border-radius:3px;margin-bottom:12px;cursor:pointer;position:relative;touch-action:none;transition:height 0.15s;}
.prog-wrap:hover,.prog-wrap:active{height:6px;margin-bottom:10px;}
.prog-fill{height:100%;width:0%;background:linear-gradient(90deg,var(--accent),var(--accent2));border-radius:3px;position:relative;pointer-events:none;transition:width 0.1s linear;}
.prog-thumb{position:absolute;right:-7px;top:50%;transform:translateY(-50%);width:14px;height:14px;background:var(--accent);border-radius:50%;box-shadow:0 0 8px var(--accent);pointer-events:none;display:none;}
.prog-wrap:hover .prog-thumb,.prog-wrap:active .prog-thumb{display:block;}

/* Button row — wrap ili buttons zisionekane zote */
.ctrl-row{
  display:flex;align-items:center;
  justify-content:space-between;
  gap:5px;
  flex-wrap:wrap;
  row-gap:8px;
}
.ctrl-l,.ctrl-r{display:flex;align-items:center;gap:4px;flex-wrap:nowrap;}

/* ───────────────────────────────────────
   BUTTONS — zinaonekana dhidi ya rangi yoyote
   ─────────────────────────────────────── */
.cbtn{
  background:rgba(0,0,0,0.65);
  border:1.5px solid rgba(255,255,255,0.40);
  border-radius:10px;
  color:#fff;
  padding:7px 10px;
  font-size:12px;font-weight:700;
  cursor:pointer;
  transition:all 0.18s;
  display:flex;align-items:center;gap:5px;
  white-space:nowrap;
  font-family:'Outfit',sans-serif;
  min-height:38px;
  box-shadow:0 2px 14px rgba(0,0,0,0.9), 0 0 0 1px rgba(0,0,0,0.5);
  text-shadow:0 1px 5px rgba(0,0,0,1);
  user-select:none;flex-shrink:0;
  backdrop-filter:blur(8px);-webkit-backdrop-filter:blur(8px);
}
.cbtn:hover,.cbtn:active{
  background:rgba(0,212,255,0.22);
  border-color:var(--accent);color:var(--accent);
  transform:scale(1.06);
}
.cbtn.on{background:rgba(0,212,255,0.22);border-color:var(--accent);color:var(--accent);}
.cbtn i{font-size:15px;filter:drop-shadow(0 1px 4px rgba(0,0,0,1));}
.cbtn-play{
  background:rgba(0,40,60,0.78);
  border-color:var(--accent);color:var(--accent);
  padding:7px 16px;
  box-shadow:0 0 18px rgba(0,212,255,0.22), 0 2px 14px rgba(0,0,0,0.9);
}
.cbtn-sm{padding:7px 9px;}

.time-txt{
  font-size:11px;color:#fff;
  min-width:36px;text-align:center;
  font-weight:700;letter-spacing:0.3px;
  text-shadow:0 1px 8px rgba(0,0,0,1),0 0 14px rgba(0,0,0,1);
  flex-shrink:1;
}
#btnRatio span.ratio-lbl{font-size:10px;font-weight:700;}

/* ═══ POPUP MENUS ═══ */
.pmenu{
  position:absolute;right:12px;bottom:84px;
  background:var(--panel);
  border:1px solid rgba(255,255,255,0.1);border-radius:16px;
  padding:8px 0;min-width:200px;display:none;z-index:50;
  backdrop-filter:blur(24px);-webkit-backdrop-filter:blur(24px);
  box-shadow:0 12px 48px rgba(0,0,0,0.95);
  animation:popIn 0.2s ease;
}
@keyframes popIn{from{opacity:0;transform:translateY(10px)}to{opacity:1;transform:translateY(0)}}
.pm-title{font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:2px;color:var(--accent);padding:4px 14px 10px;border-bottom:1px solid rgba(255,255,255,0.07);margin-bottom:4px;}
.pm-item{padding:11px 14px;font-size:14px;cursor:pointer;transition:background 0.18s;display:flex;align-items:center;gap:10px;}
.pm-item:hover,.pm-item:active{background:rgba(0,212,255,0.09);color:var(--accent);}
.pm-item.active{color:var(--accent);}
.pm-item.active::after{content:'✓';margin-left:auto;font-weight:700;}
.pm-back{color:rgba(255,255,255,0.45);font-size:13px;border-bottom:1px solid rgba(255,255,255,0.06);margin-bottom:4px;}
.pm-back:hover{color:var(--accent);}

/* ═══ ROTATE FLASH ═══ */
#rotateInfo{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);background:rgba(0,0,0,0.82);padding:18px 28px;border-radius:16px;text-align:center;display:none;z-index:60;backdrop-filter:blur(16px);border:1px solid rgba(0,212,255,0.2);animation:popIn 0.2s ease;}
#rotateInfo i{font-size:38px;color:var(--accent);display:block;margin-bottom:8px;}
#rotateInfo p{font-size:13px;color:rgba(255,255,255,0.75);letter-spacing:1px;}

/* ═══ SEEK FLASH ═══ */
.seek-flash{position:absolute;top:50%;transform:translateY(-50%);pointer-events:none;display:flex;flex-direction:column;align-items:center;gap:6px;opacity:0;transition:opacity 0.25s;z-index:20;}
.seek-flash.show{opacity:1;}
.seek-flash i{font-size:34px;color:#fff;filter:drop-shadow(0 2px 6px rgba(0,0,0,1));}
.seek-flash span{font-size:12px;font-weight:700;color:#fff;text-shadow:0 1px 6px rgba(0,0,0,1);}
#seekLeft{left:8%;}#seekRight{right:8%;}

/* ═══ LANDSCAPE ═══ */
@media(orientation:landscape){
  #hdr{padding-top:max(env(safe-area-inset-top),4px);padding-bottom:18px;}
  #ctrl{padding-top:22px;padding-bottom:max(env(safe-area-inset-bottom),8px);}
  .cbtn{min-height:32px;padding:5px 8px;font-size:11px;}
  .cbtn-play{padding:5px 12px;}
  .cbtn i{font-size:13px;}
  .time-txt{font-size:10px;min-width:28px;}
  .ctrl-row{flex-wrap:nowrap;} /* landscape ina nafasi zaidi */
}

/* Small screens — buttons zipunguze */
@media(max-width:360px){
  .cbtn{padding:6px 7px;font-size:11px;}
  .cbtn i{font-size:13px;}
  .cbtn-play{padding:6px 12px;}
  .time-txt{min-width:28px;font-size:10px;}
  #btnRatio span.ratio-lbl{display:none;} /* ficha lbl kwenye simu ndogo */
}

/* ═══ FULLSCREEN ═══ */
:fullscreen #wrap,:-webkit-full-screen #wrap{width:100vw!important;height:100vh!important;position:fixed;inset:0;}
</style>
</head>
<body>
<div id="wrap">
  <video id="video" playsinline webkit-playsinline></video>

  <div class="seek-flash" id="seekLeft"><i class="fa fa-backward"></i><span>-10s</span></div>
  <div class="seek-flash" id="seekRight"><i class="fa fa-forward"></i><span>+10s</span></div>

  <!-- PiP Overlay -->
  <div id="pipOverlay" style="position:absolute;inset:0;display:none;flex-direction:column;align-items:center;justify-content:center;gap:12px;z-index:28;background:rgba(0,0,0,0.7);backdrop-filter:blur(4px);"><i class="fa fa-picture-in-picture-alt" style="font-size:52px;color:var(--accent)"></i><p style="font-size:14px;color:rgba(255,255,255,0.7);letter-spacing:1px">Picture-in-Picture Mode</p></div>

  <!-- HEADER -->
  <div id="hdr">
    <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <div class="ch-name"><?=htmlspecialchars($name)?></div>
    <div class="live-pill"><div class="ldot"></div> LIVE</div>
  </div>

  <!-- CENTER PLAY -->
  <div id="centerPlay">
    <div class="play-ring"><i class="fa fa-play"></i></div>
    <p>BONYEZA KUCHEZA</p>
  </div>

  <!-- LOADING -->
  <div id="loadBox"><div class="ring"></div><p>INAPAKIA STREAM...</p></div>

  <!-- ROTATE FLASH -->
  <div id="rotateInfo"><i class="fa fa-rotate" id="rotIco"></i><p id="rotateMsg">Landscape Mode</p></div>

  <!-- CONTROLS -->
  <div id="ctrl">
    <div class="prog-wrap" id="progWrap">
      <div class="prog-fill" id="progFill"><div class="prog-thumb"></div></div>
    </div>
    <div class="ctrl-row">
      <div class="ctrl-l">
        <button class="cbtn" id="btnRew"><i class="fa fa-backward-step"></i><span style="font-size:11px">10s</span></button>
        <button class="cbtn cbtn-play" id="btnPlay"><i class="fa fa-pause" id="pIcon"></i></button>
        <button class="cbtn" id="btnFwd"><span style="font-size:11px">10s</span><i class="fa fa-forward-step"></i></button>
        <span class="time-txt" id="timeTxt">LIVE</span>
      </div>
      <div class="ctrl-r">
        <button class="cbtn cbtn-sm" id="btnRatio"><i class="fa fa-crop"></i><span class="ratio-lbl">16:9</span></button>
        <button class="cbtn cbtn-sm" id="btnRotate"><i class="fa fa-rotate"></i></button>
        <button class="cbtn cbtn-sm" id="btnQual"><i class="fa fa-sliders"></i></button>
        <button class="cbtn cbtn-sm" id="btnLang"><i class="fa fa-language"></i></button>
        <button class="cbtn cbtn-sm" id="btnPip" title="Picture in Picture"><i class="fa fa-picture-in-picture-alt"></i></button>
        <button class="cbtn cbtn-sm" id="btnFull"><i class="fa fa-expand" id="fullIco"></i></button>
      </div>
    </div>
  </div>

  <!-- MENUS -->
  <div class="pmenu" id="qualMenu">
    <div class="pm-title">📊 UBORA WA VIDEO</div>
    <div class="pm-item pm-back" id="qClose"><i class="fa fa-xmark"></i> Funga</div>
  </div>
  <div class="pmenu" id="langMenu">
    <div class="pm-title">🔊 LUGHA / SAUTI</div>
    <div class="pm-item pm-back" id="lClose"><i class="fa fa-xmark"></i> Funga</div>
  </div>
  <div class="pmenu" id="ratioMenu">
    <div class="pm-title">📐 SCREEN RATIO</div>
    <div class="pm-item pm-back" id="rClose"><i class="fa fa-xmark"></i> Funga</div>
    <div class="pm-item active" id="r-169"   onclick="setRatio('169')">🖥️ 16:9 <small style="color:#555;margin-left:4px">Kawaida</small></div>
    <div class="pm-item"        id="r-cover" onclick="setRatio('cover')">🔍 Zoom Fill</div>
    <div class="pm-item"        id="r-43"    onclick="setRatio('43')">📺 4:3 <small style="color:#555;margin-left:4px">Zamani</small></div>
    <div class="pm-item"        id="r-2310"  onclick="setRatio('2310')">🎬 21:9 <small style="color:#555;margin-left:4px">Cinema</small></div>
    <div class="pm-item"        id="r-11"    onclick="setRatio('11')">⬛ 1:1 <small style="color:#555;margin-left:4px">Square</small></div>
    <div class="pm-item"        id="r-fill"  onclick="setRatio('fill')">↔️ Stretch</div>
  </div>
</div>

<script>
const video    = document.getElementById('video');
const wrap     = document.getElementById('wrap');
const hdr      = document.getElementById('hdr');
const ctrl     = document.getElementById('ctrl');
const loadBox  = document.getElementById('loadBox');
const centerP  = document.getElementById('centerPlay');
const pIcon    = document.getElementById('pIcon');
const progFill = document.getElementById('progFill');
const progWrap = document.getElementById('progWrap');
const timeTxt  = document.getElementById('timeTxt');
const qualMenu = document.getElementById('qualMenu');
const langMenu = document.getElementById('langMenu');
const ratioMenu= document.getElementById('ratioMenu');
const rotInfo  = document.getElementById('rotateInfo');
const rotMsg   = document.getElementById('rotateMsg');
const rotIco   = document.getElementById('rotIco');
const seekL    = document.getElementById('seekLeft');
const seekR    = document.getElementById('seekRight');

const manifest = <?=json_encode($url)?>;
const keyStr   = <?=json_encode($key)?>;
let player, uiTimer, isLandscape = false;

/* ─── INIT ─── */
async function init(){
  loadBox.style.display='flex'; centerP.style.display='none';

  // Destroy player wa zamani kama ipo
  if(player){ try{ await player.destroy(); }catch(e){} }

  player = new shaka.Player(video);

  // ── ClearKey — configure KABLA ya load() ili isicheleweshe ──
  if(keyStr){
    const p = keyStr.split(':');
    if(p.length === 2){
      const ck = {};
      ck[p[0]] = p[1];
      player.configure({
        drm: {
          clearKeys: ck,
          // Epuka timeout ya license server
          retryParameters: { maxAttempts: 1, baseDelay: 100, backoffFactor: 1, fuzzFactor: 0 }
        },
        // Punguza buffer ili video ianze haraka
        streaming: {
          bufferingGoal: 10,
          rebufferingGoal: 2,
          bufferBehind: 10,
          retryParameters: { maxAttempts: 3, baseDelay: 200, backoffFactor: 1.5, fuzzFactor: 0.1 }
        }
      });
    }
  } else {
    // HLS / stream ya kawaida — optimize streaming
    player.configure({
      streaming: {
        bufferingGoal: 10,
        rebufferingGoal: 2,
        bufferBehind: 10,
      }
    });
  }

  player.addEventListener('error',e=>{ loadBox.innerHTML=errHtml(e.detail?.message||''); loadBox.style.display='flex'; });
  try{
    await player.load(manifest);
    loadBox.style.display='none';
    buildQuality(); buildLang();
    video.play().catch(showCenter);
  }catch(e){ loadBox.innerHTML=errHtml(e.message||''); loadBox.style.display='flex'; }
}
function errHtml(m){
  return `<div style="text-align:center;color:#ff4466;padding:24px">
    <i class="fa fa-circle-exclamation" style="font-size:44px;display:block;margin-bottom:14px"></i>
    <div style="font-size:16px;font-weight:700;margin-bottom:6px">Stream Imezimwa</div>
    <small style="color:#666;display:block;margin-bottom:18px">${m}</small>
    <button onclick="init()" style="padding:10px 24px;background:var(--accent);border:none;border-radius:10px;font-weight:700;cursor:pointer;font-size:14px;font-family:Outfit,sans-serif">
      <i class="fa fa-rotate-right"></i> Jaribu Tena</button></div>`;
}
function showCenter(){ centerP.style.display='flex'; }
centerP.onclick = ()=>{ video.play(); centerP.style.display='none'; };

/* ─── VIDEO EVENTS ─── */
video.addEventListener('play',    ()=>{ pIcon.className='fa fa-pause'; centerP.style.display='none'; });
video.addEventListener('pause',   ()=>{ pIcon.className='fa fa-play';  showCenter(); });
video.addEventListener('waiting', ()=>{ loadBox.style.display='flex'; });
video.addEventListener('playing', ()=>{ loadBox.style.display='none'; });
video.addEventListener('timeupdate',()=>{
  // Live stream — duration inakuwa Infinity au NaN au kubwa sana
  const isLive = !video.duration || isNaN(video.duration) || video.duration === Infinity || video.duration > 86400;
  if(isLive){
    progFill.style.width='100%';
    timeTxt.textContent='🔴 LIVE';
  }else{
    progFill.style.width=(video.currentTime/video.duration*100)+'%';
    timeTxt.textContent=fmt(video.currentTime)+' / '+fmt(video.duration);
  }
});
function fmt(s){
  const h=Math.floor(s/3600);
  const m=Math.floor((s%3600)/60);
  const sec=Math.floor(s%60);
  if(h>0) return h+':'+m.toString().padStart(2,'0')+':'+sec.toString().padStart(2,'0');
  return m+':'+sec.toString().padStart(2,'0');
}

/* ─── PLAY/SEEK ─── */
document.getElementById('btnPlay').onclick = ()=> video.paused?video.play():video.pause();
document.getElementById('btnFwd').onclick  = ()=>{ if(video.duration&&!isNaN(video.duration)) video.currentTime+=10; flash('right'); };
document.getElementById('btnRew').onclick  = ()=>{ if(video.duration&&!isNaN(video.duration)) video.currentTime-=10; flash('left'); };
progWrap.addEventListener('click',e=>{
  const r=progWrap.getBoundingClientRect();
  if(video.duration) video.currentTime=((e.clientX-r.left)/r.width)*video.duration;
});
function flash(dir){ const el=dir==='right'?seekR:seekL; el.classList.add('show'); clearTimeout(el._t); el._t=setTimeout(()=>el.classList.remove('show'),700); }

/* Double-tap */
let lastTap=0;
wrap.addEventListener('touchend',e=>{
  const now=Date.now(); const touch=e.changedTouches[0];
  if(now-lastTap<280){ if(touch.clientX>window.innerWidth/2){video.currentTime+=10;flash('right');}else{video.currentTime-=10;flash('left');} lastTap=0; }
  else lastTap=now;
});

/* ─── ASPECT RATIO ─── */
const ratioMap={
  '169':'ratio-169','cover':'ratio-cover',
  '43':'ratio-43','2310':'ratio-2310',
  '11':'ratio-11','fill':'ratio-fill'
};
const ratioLbl={'169':'16:9','cover':'Zoom','43':'4:3','2310':'21:9','11':'1:1','fill':'Fill'};
const ratioEl= {'169':'r-169','cover':'r-cover','43':'r-43','2310':'r-2310','11':'r-11','fill':'r-fill'};

function setRatio(key){
  Object.values(ratioMap).forEach(c=>video.classList.remove(c));
  video.classList.add(ratioMap[key]);
  document.querySelector('#btnRatio .ratio-lbl').textContent=ratioLbl[key];
  document.querySelectorAll('#ratioMenu .pm-item:not(.pm-back)').forEach(el=>el.classList.remove('active'));
  document.getElementById(ratioEl[key]).classList.add('active');
  closeMenus();
}
setRatio('169');

/* ─── ROTATE + FULLSCREEN ─── */
const btnRotate = document.getElementById('btnRotate');
const btnFull   = document.getElementById('btnFull');
const fullIco   = document.getElementById('fullIco');

btnRotate.onclick = async function(){
  isLandscape=!isLandscape;

  if(screen.orientation && screen.orientation.lock){
    try{
      if(isLandscape){
        await screen.orientation.lock('landscape');
        enterFS();
      } else {
        await screen.orientation.unlock();
        exitFS();
        wrap.style.cssText=''; // ondoa CSS rotation kama ilikuwepo
      }
    }catch{
      // Fallback CSS kwa iOS
      cssRotate(isLandscape);
      if(isLandscape) enterFS(); else exitFS();
    }
  } else {
    cssRotate(isLandscape);
    if(isLandscape) enterFS(); else exitFS();
  }

  this.classList.toggle('on',isLandscape);
  rotFlash(isLandscape?'fa-rotate':'fa-mobile-screen-rotation', isLandscape?'Landscape Mode':'Portrait Mode');
};

function cssRotate(land){
  wrap.style.cssText = land
    ? 'position:fixed;width:100vh;height:100vw;top:calc((100vh - 100vw)/2);left:calc((100vw - 100vh)/2);transform:rotate(90deg);transform-origin:center center;z-index:9999;'
    : '';
}

/* Physical rotation detection */
window.matchMedia('(orientation:landscape)').addEventListener('change',e=>{
  if(e.matches&&!isLandscape){ isLandscape=true; btnRotate.classList.add('on'); enterFS(); rotFlash('fa-rotate','Landscape Mode'); }
  else if(!e.matches&&isLandscape){ isLandscape=false; btnRotate.classList.remove('on'); wrap.style.cssText=''; rotFlash('fa-mobile-screen','Portrait Mode'); }
});

btnFull.onclick = ()=>{ (!document.fullscreenElement&&!document.webkitFullscreenElement)?enterFS():exitFS(); };
function enterFS(){ const rq=wrap.requestFullscreen||wrap.webkitRequestFullscreen||wrap.mozRequestFullScreen; if(rq)rq.call(wrap).catch(()=>{}); fullIco.className='fa fa-compress'; }
function exitFS(){ const ex=document.exitFullscreen||document.webkitExitFullscreen||document.mozCancelFullScreen; if(ex&&(document.fullscreenElement||document.webkitFullscreenElement))ex.call(document).catch(()=>{}); fullIco.className='fa fa-expand'; }
document.addEventListener('fullscreenchange',()=>{ fullIco.className=(document.fullscreenElement?'fa fa-compress':'fa fa-expand'); });
document.addEventListener('webkitfullscreenchange',()=>{ fullIco.className=(document.webkitFullscreenElement?'fa fa-compress':'fa fa-expand'); });

function rotFlash(icon,msg){
  rotIco.className='fa '+icon; rotMsg.textContent=msg;
  rotInfo.style.display='block'; clearTimeout(rotInfo._t);
  rotInfo._t=setTimeout(()=>rotInfo.style.display='none',1600);
}

/* ─── QUALITY MENU ─── */
function buildQuality(){
  qualMenu.querySelectorAll('.q-item').forEach(e=>e.remove());
  const auto=mkItem('⚡ AUTO (Inayopendekezwa)','q-item active');
  auto.onclick=()=>{ player.configure({abr:{enabled:true}}); setActive(qualMenu,auto); closeMenus(); };
  qualMenu.appendChild(auto);
  const tracks=player.getVariantTracks().sort((a,b)=>(b.height||0)-(a.height||0));
  const seen={};
  tracks.forEach(t=>{
    if(!t.height||seen[t.height])return; seen[t.height]=true;
    const item=mkItem(`${t.height}p — ${Math.round((t.bandwidth||0)/1000)}kbps`,'q-item');
    item.onclick=()=>{ player.configure({abr:{enabled:false}}); player.selectVariantTrack(t,true); setActive(qualMenu,item); closeMenus(); };
    qualMenu.appendChild(item);
  });
  if(!tracks.length){ const ni=mkItem('Ubora mmoja unapatikana','q-item'); ni.style.color='#555'; qualMenu.appendChild(ni); }
}

/* ─── LANGUAGE MENU ─── */
function buildLang(){
  langMenu.querySelectorAll('.l-item').forEach(e=>e.remove());
  const langs=player.getAudioLanguages();
  if(!langs.length){ langMenu.appendChild(mkItem('🔊 Default Audio','l-item active')); return; }
  langs.forEach((l,i)=>{
    const item=mkItem('🔊 '+l.toUpperCase(),'l-item'+(i===0?' active':''));
    item.onclick=()=>{ player.selectAudioLanguage(l); setActive(langMenu,item); closeMenus(); };
    langMenu.appendChild(item);
  });
}

function mkItem(txt,cls){ const d=document.createElement('div'); d.className='pm-item '+cls; d.textContent=txt; return d; }
function setActive(menu,el){ menu.querySelectorAll('.pm-item').forEach(i=>i.classList.remove('active')); el.classList.add('active'); }

/* Menu toggles */
document.getElementById('btnQual').onclick  = e=>{ e.stopPropagation(); toggleMenu(qualMenu); };
document.getElementById('btnLang').onclick  = e=>{ e.stopPropagation(); toggleMenu(langMenu); };
document.getElementById('btnRatio').onclick = e=>{ e.stopPropagation(); toggleMenu(ratioMenu); };
document.getElementById('qClose').onclick   = ()=> qualMenu.style.display='none';
document.getElementById('lClose').onclick   = ()=> langMenu.style.display='none';
document.getElementById('rClose').onclick   = ()=> ratioMenu.style.display='none';
function toggleMenu(m){ const o=m.style.display==='block'; closeMenus(); if(!o)m.style.display='block'; }
function closeMenus(){ qualMenu.style.display='none'; langMenu.style.display='none'; ratioMenu.style.display='none'; }

/* ─── AUTO-HIDE UI ─── */
function showUI(){
  hdr.classList.remove('hidden');
  ctrl.classList.remove('hidden');
  clearTimeout(uiTimer);
  uiTimer=setTimeout(()=>{
    if([qualMenu,langMenu,ratioMenu].some(m=>m.style.display==='block'))return;
    hdr.classList.add('hidden');
    ctrl.classList.add('hidden');
  }, 4000);
}
['mousemove','touchstart','click','keydown'].forEach(ev=> wrap.addEventListener(ev,showUI,{passive:true}));

/* Keyboard shortcuts */
document.addEventListener('keydown',e=>{
  if(e.key===' '||e.key==='k'){ e.preventDefault(); video.paused?video.play():video.pause(); }
  if(e.key==='ArrowRight'){ video.currentTime+=10; flash('right'); }
  if(e.key==='ArrowLeft') { video.currentTime-=10; flash('left'); }
  if(e.key==='f'||e.key==='F') btnFull.click();
  if(e.key==='Escape') closeMenus();
});

/* ─── PICTURE IN PICTURE ─── */
const btnPip = document.getElementById('btnPip');
const pipOverlay = document.getElementById('pipOverlay');
if (!document.pictureInPictureEnabled || video.disablePictureInPicture) {
  btnPip.style.display = 'none';
} else {
  btnPip.onclick = async () => {
    try {
      if (document.pictureInPictureElement) {
        await document.exitPictureInPicture();
        btnPip.classList.remove('on');
      } else {
        await video.requestPictureInPicture();
        btnPip.classList.add('on');
      }
    } catch(e) { console.warn('PiP error:', e); }
  };
  video.addEventListener('enterpictureinpicture', () => { btnPip.classList.add('on'); pipOverlay.style.display = 'flex'; });
  video.addEventListener('leavepictureinpicture', () => { btnPip.classList.remove('on'); pipOverlay.style.display = 'none'; });
}

showUI();
init();
</script>
</body>
</html>
