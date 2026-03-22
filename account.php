<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Akaunti</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
/* ── PROFILE HERO ── */
.profile-hero{background:linear-gradient(160deg,#090918 0%,#0c0c1e 60%,#080816 100%);padding:28px 16px 22px;text-align:center;border-bottom:1px solid var(--border);position:relative;overflow:hidden;}
.profile-hero::before{content:'';position:absolute;top:-60px;left:50%;transform:translateX(-50%);width:300px;height:300px;background:radial-gradient(ellipse,rgba(0,212,255,0.07) 0%,transparent 65%);pointer-events:none;}
.avatar-wrap{position:relative;display:inline-block;margin-bottom:14px;}
.avatar{width:84px;height:84px;border-radius:50%;background:linear-gradient(135deg,var(--accent),var(--accent2));display:flex;align-items:center;justify-content:center;font-family:'Bebas Neue',sans-serif;font-size:32px;color:#000;border:3px solid rgba(0,212,255,0.3);box-shadow:0 0 28px rgba(0,212,255,0.2);}
.avatar-ring{position:absolute;inset:-5px;border-radius:50%;border:2px dashed rgba(0,212,255,0.15);animation:spin 14s linear infinite;}
@keyframes spin{to{transform:rotate(360deg)}}
.profile-name{font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:2px;margin-bottom:3px;}
.profile-email{font-size:12px;color:var(--muted);margin-bottom:14px;}
.member-badge{display:inline-flex;align-items:center;gap:6px;padding:5px 14px;border-radius:20px;font-size:11px;font-weight:700;}
.badge-free{background:rgba(255,255,255,0.05);border:1px solid rgba(255,255,255,0.1);color:var(--muted);}
.badge-premium{background:rgba(255,215,0,0.1);border:1px solid rgba(255,215,0,0.28);color:var(--gold);}

/* ── STATS ── */
.stats-row{display:grid;grid-template-columns:repeat(3,1fr);background:var(--card);border-bottom:1px solid var(--border);}
.stat-box{padding:15px 10px;text-align:center;border-right:1px solid var(--border);}
.stat-box:last-child{border-right:none;}
.stat-num{font-family:'Bebas Neue',sans-serif;font-size:26px;color:var(--accent);line-height:1;}
.stat-lbl{font-size:10px;color:var(--muted);margin-top:3px;text-transform:uppercase;letter-spacing:0.5px;}

/* ── LIST ── */
.section{padding:14px 14px 4px;}
.sec-title{font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:2px;color:var(--muted);margin-bottom:8px;display:flex;align-items:center;gap:6px;}
.sec-title i{color:var(--accent);font-size:11px;}
.clist{background:var(--card);border-radius:14px;overflow:hidden;border:1px solid var(--border);}
.crow{display:flex;align-items:center;gap:12px;padding:13px 14px;cursor:pointer;transition:background 0.18s;border-bottom:1px solid var(--border);text-decoration:none;color:var(--text);}
.crow:last-child{border-bottom:none;}
.crow:hover{background:rgba(0,212,255,0.05);}
.cicon{width:36px;height:36px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.ctxt{flex:1;min-width:0;}
.ctxt strong{font-size:13px;font-weight:600;display:block;margin-bottom:1px;}
.ctxt span{font-size:11px;color:var(--muted);}
.carrow{color:rgba(255,255,255,0.2);font-size:11px;}

/* Toggle */
.toggle-sw{width:42px;height:23px;background:rgba(255,255,255,0.1);border-radius:12px;position:relative;cursor:pointer;transition:background 0.28s;flex-shrink:0;}
.toggle-sw.on{background:var(--accent);}
.toggle-sw::after{content:'';position:absolute;width:17px;height:17px;border-radius:50%;background:#fff;top:3px;left:3px;transition:transform 0.28s;box-shadow:0 1px 4px rgba(0,0,0,0.4);}
.toggle-sw.on::after{transform:translateX(19px);}

/* Logout */
.logout-btn{width:calc(100% - 28px);margin:14px;padding:13px;background:rgba(255,68,102,0.1);border:1px solid rgba(255,68,102,0.25);border-radius:12px;color:var(--accent2);font-size:14px;font-weight:700;font-family:'Outfit',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.25s;}
.logout-btn:hover{background:rgba(255,68,102,0.2);transform:scale(1.01);}
.version-txt{text-align:center;font-size:11px;color:rgba(255,255,255,0.15);padding-bottom:16px;}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<div class="topbar">
  <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
  <div class="page-title">AKAUNTI <span>YANGU</span></div>
</div>

<!-- PROFILE HERO -->
<div class="profile-hero">
  <div class="avatar-wrap">
    <div class="avatar" id="avatarEl">?</div>
    <div class="avatar-ring"></div>
  </div>
  <div class="profile-name" id="profileName">Inapakia...</div>
  <div class="profile-email" id="profileEmail">—</div>
  <span class="member-badge badge-free" id="memberBadge"><i class="fa fa-star"></i> FREE</span>
</div>

<!-- STATS -->
<div class="stats-row">
  <div class="stat-box"><div class="stat-num">120+</div><div class="stat-lbl">Channels</div></div>
  <div class="stat-box"><div class="stat-num" id="statDays">—</div><div class="stat-lbl">Siku</div></div>
  <div class="stat-box"><div class="stat-num">HD</div><div class="stat-lbl">Quality</div></div>
</div>

<!-- SUBSCRIPTION -->
<div class="section">
  <div class="sec-title"><i class="fa fa-crown"></i> SUBSCRIPTION</div>
  <div class="clist">
    <div class="crow" onclick="location.href='malipo.php'">
      <div class="cicon" style="background:rgba(255,215,0,0.1)"><i class="fa fa-crown" style="color:var(--gold)"></i></div>
      <div class="ctxt"><strong>Hali ya Subscription</strong><span id="subStatus">Bonyeza kuangalia</span></div>
      <i class="fa fa-chevron-right carrow"></i>
    </div>
    <div class="crow" onclick="location.href='malipo.php'">
      <div class="cicon" style="background:rgba(0,212,255,0.1)"><i class="fa fa-credit-card" style="color:var(--accent)"></i></div>
      <div class="ctxt"><strong>Lipia Subscription</strong><span>M-Pesa, Tigo, Airtel Money</span></div>
      <i class="fa fa-chevron-right carrow"></i>
    </div>
  </div>
</div>

<!-- QUICK LINKS -->
<div class="section">
  <div class="sec-title"><i class="fa fa-tv"></i> CHANNELS</div>
  <div class="clist">
    <a href="face.html" class="crow">
      <div class="cicon" style="background:rgba(255,107,53,0.1)"><i class="fa fa-satellite-dish" style="color:#ff6b35"></i></div>
      <div class="ctxt"><strong>Azam Channels</strong><span>Sports, Drama, Music</span></div>
      <i class="fa fa-chevron-right carrow"></i>
    </a>
    <a href="live.php" class="crow">
      <div class="cicon" style="background:rgba(0,255,136,0.1)"><i class="fa fa-futbol" style="color:var(--green)"></i></div>
      <div class="ctxt"><strong>Mechi za Leo</strong><span>NBC Premier League, UEFA</span></div>
      <i class="fa fa-chevron-right carrow"></i>
    </a>
    <a href="schedule.php" class="crow">
      <div class="cicon" style="background:rgba(168,85,247,0.1)"><i class="fa fa-calendar-days" style="color:var(--purple)"></i></div>
      <div class="ctxt"><strong>Ratiba</strong><span>Vipindi vya wiki hii</span></div>
      <i class="fa fa-chevron-right carrow"></i>
    </a>
  </div>
</div>

<!-- SETTINGS -->
<div class="section">
  <div class="sec-title"><i class="fa fa-gear"></i> MIPANGILIO</div>
  <div class="clist">
    <div class="crow" onclick="shareApp()">
      <div class="cicon" style="background:rgba(0,212,255,0.1)"><i class="fa fa-share-nodes" style="color:var(--accent)"></i></div>
      <div class="ctxt"><strong>Shiriki App</strong><span>Waambie marafiki wako</span></div>
      <i class="fa fa-share carrow"></i>
    </div>
    <div class="crow">
      <div class="cicon" style="background:rgba(255,215,0,0.1)"><i class="fa fa-bell" style="color:var(--gold)"></i></div>
      <div class="ctxt"><strong>Arifa za Mechi</strong><span>Pata notification za mechi</span></div>
      <div class="toggle-sw" id="notifToggle" onclick="this.classList.toggle('on')"></div>
    </div>
  </div>
</div>

<button id="refreshStatusBtn" onclick="doRefreshStatus()" style="width:calc(100% - 28px);margin:14px 14px 0;padding:12px;background:rgba(0,212,255,0.08);border:1px solid rgba(0,212,255,0.2);border-radius:12px;color:var(--accent);font-size:13px;font-weight:700;font-family:'Outfit',sans-serif;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px">
  <i class="fa fa-rotate-right"></i> Sasisha Hali ya Subscription
</button>
<button class="logout-btn" onclick="doLogout()">
  <i class="fa fa-right-from-bracket"></i> TOKA (LOGOUT)
</button>
<div class="version-txt">JAYNES MAX TV v2.0 • Tanzania</div>

<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item"><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item active"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script src="_auth_guard.js"></script>
<script>
const u = getUser();
const initials = u.name ? u.name.split(' ').map(w=>w[0]).join('').toUpperCase().slice(0,2) : u.email[0].toUpperCase();
document.getElementById('avatarEl').textContent   = initials;
document.getElementById('profileName').textContent = u.name || 'Mtumiaji';
document.getElementById('profileEmail').textContent= u.email;

// Subscription status — soma kutoka localStorage
const plan    = localStorage.getItem('jaynesPlan')    || 'free';
const subEnd  = localStorage.getItem('jaynesSubEnd')  || '';
const trialE  = localStorage.getItem('jaynesTrialEnd')|| '';
const now     = new Date();
const badge   = document.getElementById('memberBadge');
const statEl  = document.getElementById('statDays');

if (plan === 'premium' && subEnd && new Date(subEnd) > now) {
  const daysLeft = Math.ceil((new Date(subEnd) - now) / 86400000);
  badge.className = 'member-badge badge-premium';
  badge.innerHTML = '<i class="fa fa-crown"></i> PREMIUM';
  document.getElementById('subStatus').textContent = 'Premium — hadi ' + new Date(subEnd).toLocaleDateString('sw');
  if(statEl) statEl.textContent = daysLeft + ' siku';
} else if (trialE && new Date(trialE) > now) {
  const minsLeft = Math.ceil((new Date(trialE) - now) / 60000);
  badge.innerHTML = '<i class="fa fa-star"></i> TRIAL';
  document.getElementById('subStatus').textContent = 'Trial — zimebaki dakika ' + minsLeft;
  if(statEl) statEl.textContent = 'Trial';
} else {
  document.getElementById('subStatus').textContent = 'Bure — lipia ili upate access';
  if(statEl) statEl.textContent = '—';
}

// Refresh Status button — inaitwa mara mtumiaji akisubiri premium
async function doRefreshStatus() {
  const btn = document.getElementById('refreshStatusBtn');
  if(btn){ btn.disabled=true; btn.innerHTML='<i class="fa fa-spinner fa-spin"></i> Inaangalia...'; }
  try {
    const token = localStorage.getItem('jaynesToken') || '';
    if (!token) { location.reload(); return; }
    const res = await fetch('auth.php?action=me', {
      headers: { 'Authorization': 'Bearer ' + token },
      cache: 'no-store'
    });
    const d = await res.json();
    if (d.success && d.user) {
      localStorage.setItem('jaynesPlan',      d.user.plan      || 'free');
      localStorage.setItem('jaynesSubEnd',    d.user.sub_end   || '');
      localStorage.setItem('jaynesTrialEnd',  d.user.trial_end || '');
      localStorage.removeItem('jaynesLastCheck');
      location.reload();
    } else {
      location.reload();
    }
  } catch(e) { location.reload(); }
}

function shareApp(){
  const url='https://ddde-ch.us/jaynestv.html';
  if(navigator.share){
    navigator.share({title:'JAYNES MAX TV',text:'Angalia TV live kwa bure!',url});
  } else {
    navigator.clipboard?.writeText(url);
    showToast('Link imenakiliwa!');
  }
}

function doLogout(){
  if(confirm('Una uhakika unataka kutoka?')) logout();
}
</script>
</body>
</html>
