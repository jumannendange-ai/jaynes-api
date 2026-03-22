<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Mechi Live</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<!-- OneSignal SDK -->
<script src="https://cdn.onesignal.com/sdks/web/v16/OneSignalSDK.page.js" defer></script>
<style>
body { padding-top: 106px; }
.topbar { flex-wrap:wrap; height:auto; padding:10px 14px; gap:8px; }
.topbar-row1 { display:flex; align-items:center; gap:10px; width:100%; }
.topbar-row2 { width:100%; padding-bottom:2px; }

/* STATS BAR */
.stats-bar {
  background:rgba(0,212,255,0.04); border-bottom:1px solid var(--border);
  padding:8px 14px; display:flex; gap:16px; font-size:11px; color:var(--muted);
  overflow-x:auto; scrollbar-width:none;
}
.stats-bar::-webkit-scrollbar { display:none; }
.stat { display:flex; align-items:center; gap:5px; white-space:nowrap; }
.stat i { color:var(--accent); }
.stat strong { color:var(--text); }

/* DB PANEL */


/* MATCH CARDS */
.match-list { padding:0 12px 14px; }
.cat-hdr { font-family:'Bebas Neue',sans-serif; font-size:16px; letter-spacing:2px; color:var(--accent); padding:12px 0 8px; display:flex; align-items:center; gap:8px; border-bottom:1px solid var(--border); margin-bottom:10px; }
.cat-hdr::before { content:''; width:4px; height:16px; background:var(--accent); border-radius:2px; }

.mcard { background:var(--card); border:1px solid rgba(255,255,255,0.06); border-radius:16px; cursor:pointer; position:relative; transition:all 0.3s; overflow:hidden; opacity:0; transform:translateY(14px); margin-bottom:10px; }
.mcard.vis { opacity:1; transform:translateY(0); transition:opacity 0.4s ease, transform 0.4s ease, border-color 0.3s, box-shadow 0.3s; }
.mcard:hover { border-color:var(--accent); box-shadow:var(--glow); transform:translateY(-2px); }
.mcard.loading::after { content:''; position:absolute; inset:0; background:linear-gradient(90deg,transparent 0%,rgba(255,255,255,0.04) 50%,transparent 100%); animation:shimmer 1.4s infinite; }
@keyframes shimmer { 0%{transform:translateX(-100%)} 100%{transform:translateX(100%)} }

.card-top { display:flex; align-items:center; justify-content:space-between; padding:8px 12px 4px; font-size:10px; color:var(--muted); }
.card-cat { display:flex; align-items:center; gap:5px; }
.card-cat i { color:var(--accent); font-size:9px; }
.live-sm { display:flex; align-items:center; gap:4px; background:rgba(255,68,102,0.12); border:1px solid rgba(255,68,102,0.28); color:var(--accent2); font-size:9px; font-weight:700; padding:2px 7px; border-radius:4px; }
.live-sm::before { content:''; width:5px; height:5px; background:var(--accent2); border-radius:50%; animation:blink 1s infinite; }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }

.matchup { display:flex; align-items:center; justify-content:space-between; padding:10px 14px 8px; gap:6px; }
.t-side { display:flex; flex-direction:column; align-items:center; gap:5px; flex:1; min-width:0; }
.t-logo { width:46px; height:46px; border-radius:50%; background:rgba(255,255,255,0.04); border:1.5px solid rgba(255,255,255,0.1); display:flex; align-items:center; justify-content:center; overflow:hidden; transition:all 0.3s; }
.t-logo img { width:32px; height:32px; object-fit:contain; }
.t-logo.home { border-color:rgba(0,212,255,0.3); }
.t-logo.away { border-color:rgba(255,68,102,0.3); }
.mcard:hover .t-logo.home { border-color:rgba(0,212,255,0.7); box-shadow:0 0 10px rgba(0,212,255,0.2); }
.mcard:hover .t-logo.away { border-color:rgba(255,68,102,0.7); box-shadow:0 0 10px rgba(255,68,102,0.2); }
.t-name { font-size:11px; font-weight:700; text-align:center; line-height:1.3; max-width:80px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }

.vs-blk { display:flex; flex-direction:column; align-items:center; gap:3px; flex-shrink:0; min-width:64px; }
.vs-score { font-family:'Bebas Neue',sans-serif; font-size:30px; letter-spacing:2px; line-height:1; }
.vs-score .sh { color:var(--accent); }
.vs-score .ss { color:rgba(255,255,255,0.2); font-size:24px; }
.vs-score .sa { color:var(--accent2); }
.vs-txt  { font-family:'Bebas Neue',sans-serif; font-size:22px; letter-spacing:3px; color:rgba(255,255,255,0.18); }
.vs-time { font-size:9px; font-weight:700; color:var(--accent2); background:rgba(255,68,102,0.08); padding:2px 8px; border-radius:4px; }

.card-foot { display:flex; align-items:center; justify-content:space-between; padding:6px 12px 10px; border-top:1px solid rgba(255,255,255,0.04); }
.ch-tag { font-size:10px; font-weight:600; color:var(--muted); display:flex; align-items:center; gap:4px; max-width:60%; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.ch-tag i { color:var(--accent); font-size:9px; flex-shrink:0; }
.watch-btn { display:flex; align-items:center; gap:4px; background:rgba(0,212,255,0.1); border:1px solid rgba(0,212,255,0.2); color:var(--accent); font-size:10px; font-weight:700; padding:4px 11px; border-radius:6px; transition:all 0.2s; flex-shrink:0; }
.mcard:hover .watch-btn { background:rgba(0,212,255,0.2); border-color:var(--accent); }
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-row1">
    <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <div class="page-title">⚽ MECHI <span>LIVE</span></div>
    <span class="badge badge-live" style="margin-left:auto;padding:4px 10px;font-size:10px">
      <span class="live-dot" style="margin-right:3px"></span>LIVE
    </span>
    <button id="bellBtn" onclick="toggleNotifications()" title="Arifa za mechi" style="
      width:38px;height:38px;flex-shrink:0;
      background:rgba(0,212,255,0.07);
      border:1px solid var(--border);border-radius:10px;
      color:var(--muted);font-size:15px;cursor:pointer;
      display:flex;align-items:center;justify-content:center;
      transition:all 0.25s;position:relative;
    ">
      <i class="fa fa-bell" id="bellIcon"></i>
      <span id="bellDot" style="display:none;position:absolute;top:5px;right:5px;width:8px;height:8px;background:var(--accent2);border-radius:50%;border:2px solid var(--bg)"></span>
    </button>
  </div>
  <div class="topbar-row2">
    <div class="search-bar">
      <input type="text" id="searchInput" placeholder="Tafuta mechi, timu, channels...">
      <button id="searchBtn"><i class="fa fa-search"></i></button>
    </div>
    <a id="globalSearchBtn" href="search.php" style="display:none;margin-left:7px;flex-shrink:0;padding:0 12px;height:38px;background:rgba(0,212,255,0.1);border:1px solid var(--border);border-radius:10px;color:var(--accent);font-size:11px;font-weight:700;align-items:center;gap:5px;white-space:nowrap;text-decoration:none;">
      <i class="fa fa-border-all"></i> Zote
    </a>
  </div>
</div>

<!-- STATS BAR -->
<div class="stats-bar" id="statsBar">
  <div class="stat"><i class="fa fa-spinner fa-spin"></i> Inapakia...</div>
</div>


<!-- MATCH LIST -->
<div class="match-list" id="matchList">
  <div class="load-state"><div class="spinner"></div>Inapakia mechi za leo...</div>
</div>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item active"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item"><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script src="_auth_guard.js"></script>
<script src="_cache.js"></script>
<script src="_channel_guard.js"></script>
<script src="_channel_popup.js"></script>
<script src="_match_widget.js"></script>
<script>
const matchList   = document.getElementById('matchList');
const statsBar    = document.getElementById('statsBar');
const searchInput = document.getElementById('searchInput');
let allChannels = [], currentKey = null, keyTimer = null, countInterval = null;

// ══════════════════════════════════════════════════════════════
//  TEAM LOGOS — Zinapakuliwa kutoka AllSportsAPI kupitia team_logos.php
// ══════════════════════════════════════════════════════════════
let teamLogosMap = {};

async function loadTeamLogos() {
  try {
    const res  = await fetch('team_logos.php');
    const data = await res.json();
    if (data.success && data.logos) {
      teamLogosMap = data.logos;
      console.log('[Logos] Zimepakuliwa:', data.count, 'timu');
      refreshCardLogos();
    }
  } catch (e) {
    console.warn('[Logos] Imeshindwa:', e.message);
  }
}

function findTeamLogo(teamName) {
  if (!teamName) return null;
  const key = teamName.toLowerCase().trim();

  // Mechi kamili kwanza
  if (teamLogosMap[key]) return teamLogosMap[key].logo;

  // Jina moja linajumuishwa ndani ya lingine
  for (const [k, v] of Object.entries(teamLogosMap)) {
    if (key.includes(k) || k.includes(key)) return v.logo;
  }

  // Neno la kwanza (mfano "Yanga SC" → "Yanga")
  const firstWord = key.split(' ')[0];
  if (firstWord.length > 3) {
    for (const [k, v] of Object.entries(teamLogosMap)) {
      if (k.startsWith(firstWord)) return v.logo;
    }
  }

  return null;
}

function refreshCardLogos() {
  document.querySelectorAll('.mcard[data-home]').forEach(card => {
    const home = card.dataset.home || '';
    const away = card.dataset.away || '';
    const hl = findTeamLogo(home);
    const al = findTeamLogo(away);
    const homeEl = card.querySelector('.t-logo.home');
    const awayEl = card.querySelector('.t-logo.away');
    if (hl && homeEl && !homeEl.querySelector('img.api-logo')) {
      const img = document.createElement('img');
      img.src = hl; img.loading = 'lazy'; img.alt = home;
      img.className = 'api-logo';
      img.style.cssText = 'width:32px;height:32px;object-fit:contain';
      img.onerror = () => img.remove();
      homeEl.innerHTML = '';
      homeEl.appendChild(img);
    }
    if (al && awayEl && !awayEl.querySelector('img.api-logo')) {
      const img = document.createElement('img');
      img.src = al; img.loading = 'lazy'; img.alt = away;
      img.className = 'api-logo';
      img.style.cssText = 'width:32px;height:32px;object-fit:contain';
      img.onerror = () => img.remove();
      awayEl.innerHTML = '';
      awayEl.appendChild(img);
    }
  });
}

/* ── DETECT SPORT ── */
function detectSport(title) {
  const t = (title || '').toLowerCase();
  if (t.includes('formula') || t.includes('grand prix') || (t.includes(' gp ') || t.includes('gp china') || t.includes('gp japan'))) return { icon: 'fa-flag-checkered', label: 'F1 🏎️', color: '#ff2800' };
  if (t.includes('tennis') || t.includes('atp') || t.includes('wta') || t.includes('indian wells')) return { icon: 'fa-circle', label: 'Tennis 🎾', color: '#c8ff00' };
  if (t.includes('lakers') || t.includes('bulls') || t.includes('celtics') || t.includes('warriors') || t.includes('heat') || t.includes('knicks') || t.includes('bucks') || t.includes('thunder') || t.includes('nuggets') || t.includes('spurs') || t.includes('grizzlies') || t.includes('mavericks') || t.includes('clippers') || t.includes('suns') || t.includes('kings') || t.includes('pelicans') || t.includes('nba')) return { icon: 'fa-basketball', label: 'NBA 🏀', color: '#ff6400' };
  if (t.includes('nhl') || t.includes('jets') || t.includes('rangers') || t.includes('golden knights') || t.includes('penguins') || t.includes('canucks') || t.includes('predators') || t.includes('kraken') || t.includes('avalanche') || t.includes('blackhawks') || t.includes('mammoth')) return { icon: 'fa-hockey-puck', label: 'NHL 🏒', color: '#6496ff' };
  if (t.includes('whitecaps') || t.includes('sounders') || t.includes('cincinnati') || t.includes('tigres') || t.includes('river plate') || t.includes('gremio') || t.includes('huracan') || t.includes('medellin') || t.includes('mls')) return { icon: 'fa-futbol', label: 'Soccer ⚽', color: '#00d4ff' };
  if (t.includes('north carolina') || t.includes('clemson') || t.includes('ncaa') || t.includes('duke')) return { icon: 'fa-graduation-cap', label: 'NCAA 🏫', color: '#ffa000' };
  if (t.includes('boxing') || t.includes('ufc') || t.includes('mma')) return { icon: 'fa-hand-fist', label: 'Combat 🥊', color: '#ff4466' };
  return { icon: 'fa-satellite-dish', label: 'Live', color: '#00d4ff' };
}

/* ── BUILD CARD ── */
function buildCard(ch, delay) {
  const title  = ch.title || '';
  const m      = title.match(/^(.+?)\s+(?:vs\.?|VS\.?|[-\u2013])\s+(.+)$/i);
  const home   = m ? m[1].trim() : title;
  const away   = m ? m[2].trim() : '';
  const sport  = detectSport(title);

  // Logo: tumia API logo ikiwa ipo, vinginevyo angalia channels data, vinginevyo fallback emoji
  const getLogo = (apiLogoUrl, channelLogoUrl, side, teamName) => {
    const placeholder = `<span style="font-size:22px">${side==='home'?'🏠':'🛡️'}</span>`;
    // Jaribu URL iliyopewa na channels API kwanza
    const src = channelLogoUrl && channelLogoUrl !== 'https://i.imgur.com/HzAuIlC.png'
      ? channelLogoUrl
      : apiLogoUrl || null;

    if (src) {
      return `<img src="${src}" loading="lazy" class="api-logo" style="width:32px;height:32px;object-fit:contain"
        onerror="this.replaceWith(Object.assign(document.createElement('span'),{textContent:'${side==='home'?'🏠':'🛡️'}',style:'font-size:22px'}))">`;
    }
    return placeholder;
  };

  // Pata logo kutoka teamLogosMap (inayopakuliwa na loadTeamLogos)
  const homeApiLogo = findTeamLogo(home);
  const awayApiLogo = findTeamLogo(away);

  const hasScore = ch.home_score !== undefined && ch.away_score !== undefined;
  const vs = hasScore
    ? `<div class="vs-score"><span class="sh">${ch.home_score}</span><span class="ss"> : </span><span class="sa">${ch.away_score}</span></div><div class="vs-time">${ch.minute ? ch.minute+"'" : 'LIVE'}</div>`
    : `<div class="vs-txt">VS</div><div class="vs-time">LIVE</div>`;

  const card = document.createElement('div');
  card.className = 'mcard';
  // Hifadhi majina ya timu kwenye data attributes kwa refreshCardLogos()
  card.dataset.home = home;
  card.dataset.away = away || '';
  card.innerHTML = `
    <div class="card-top">
      <div class="card-cat"><i class="fa ${sport.icon}" style="color:${sport.color}"></i>&nbsp;${sport.label}</div>
      <div class="live-sm">LIVE</div>
    </div>
    <div class="matchup">
      <div class="t-side"><div class="t-logo home">${getLogo(homeApiLogo, ch.home_logo||ch.logo, 'home', home)}</div><div class="t-name">${home}</div></div>
      <div class="vs-blk">${vs}</div>
      <div class="t-side"><div class="t-logo away">${getLogo(awayApiLogo, ch.away_logo, 'away', away)}</div><div class="t-name">${away||'—'}</div></div>
    </div>
    <div class="card-foot">
      <div class="ch-tag"><i class="fa fa-satellite-dish"></i>${ch.channel_name||ch.title||'—'}</div>
      <div class="watch-btn"><i class="fa fa-play"></i> Tazama</div>
    </div>`;

  card.onclick = () => {
    if (currentKey) ch.key = currentKey;
    const playerUrl = 'player.html?data=' + encodeURIComponent(JSON.stringify(ch));
    // goChannelFull inaonyesha popup na data zote (score, timu, nk)
    // Imefafanuliwa kwenye _channel_popup.js; ikiwa haipo, tumia goChannel ya kawaida
    if (typeof goChannelFull === 'function') {
      goChannelFull(ch, playerUrl);
    } else {
      goChannel(ch.title || ch.channel_name || '', playerUrl);
    }
  };
  setTimeout(() => card.classList.add('vis'), delay || 0);
  return card;
}

/* ── RENDER ── */
function renderChannels(list, isSearch) {
  matchList.innerHTML = '';
  if (!list.length) {
    matchList.innerHTML = `<div class="load-state"><i class="fa fa-search" style="font-size:28px;color:var(--muted);display:block;margin-bottom:10px"></i>Hakuna matokeo</div>`;
    return;
  }
  if (isSearch) {
    const h = document.createElement('div');
    h.className = 'cat-hdr';
    h.innerHTML = `<i class="fa fa-search"></i> Matokeo: ${list.length}`;
    matchList.appendChild(h);
    list.forEach((ch, i) => matchList.appendChild(buildCard(ch, i * 40)));
    return;
  }
  const groups = {};
  list.forEach(ch => { const c = ch.category||'Channels'; if(!groups[c])groups[c]=[]; groups[c].push(ch); });
  let delay = 0;
  Object.entries(groups).forEach(([cat, chs]) => {
    const h = document.createElement('div'); h.className='cat-hdr';
    h.innerHTML = `<i class="fa fa-futbol"></i> ${cat}`;
    matchList.appendChild(h);
    chs.forEach(ch => { matchList.appendChild(buildCard(ch, delay)); delay += 40; });
  });
}

/* ── LOAD with cache ── */
async function loadMatches() {
  await fetchWithCache(
    'live_channels',
    async () => {
      const res  = await fetch('channels.php?category=mechi%20za%20leo&t=' + Date.now());
      const data = await res.json();
      if (!data.success || !data.channels.length) throw new Error('empty');
      return data.channels;
    },
    (channels, fromCache) => {
      allChannels = channels;
      const cats  = [...new Set(allChannels.map(c => c.category).filter(Boolean))];
      const secs  = JMCache.ttlLeft('live_channels');
      statsBar.innerHTML = `
        <div class="stat"><i class="fa fa-circle-play"></i><strong>${allChannels.length}</strong> Channels</div>
        <div class="stat"><i class="fa fa-layer-group"></i><strong>${cats.length}</strong> Makundi</div>
        <div class="stat" style="color:var(--green)"><i class="fa fa-signal"></i><strong>LIVE</strong></div>
        ${fromCache ? `<div class="stat" style="color:var(--muted)"><i class="fa fa-clock"></i>Cache ${secs}s</div>` : ''}`;
      renderChannels(allChannels, false);
    },
    () => {
      matchList.innerHTML = `<div class="load-state"><div class="spinner"></div>Inapakia mechi za leo...</div>`;
    },
    () => {
      matchList.innerHTML = `<div class="error-box"><i class="fa fa-wifi"></i>Hakuna mechi sasa. Angalia mtandao.<br><button class="retry-btn" onclick="loadMatches()">Jaribu Tena</button></div>`;
      statsBar.innerHTML = `<div class="stat"><i class="fa fa-times-circle" style="color:var(--accent2)"></i> Imeshindwa</div>`;
    }
  );
}

/* ── SEARCH ── */
function doSearch() {
  const q = searchInput.value.trim();
  if (!q) {
    renderChannels(allChannels, false);
    document.getElementById('globalSearchBtn').style.display = 'none';
    return;
  }
  const ql = q.toLowerCase();
  const r = allChannels.filter(ch => [ch.title,ch.category,ch.channel_name,ch.network].join(' ').toLowerCase().includes(ql));
  renderChannels(r, true);
  // Show "Search All" button with current query
  const btn = document.getElementById('globalSearchBtn');
  btn.href = 'search.php?q=' + encodeURIComponent(q);
  btn.style.display = 'flex';
}
document.getElementById('searchBtn').onclick = doSearch;
searchInput.addEventListener('input', doSearch);
searchInput.addEventListener('keypress', e => {
  if (e.key === 'Enter') {
    const q = searchInput.value.trim();
    if (q) location.href = 'search.php?q=' + encodeURIComponent(q);
  }
});

/* ── NBC KEY REFRESH ── */
async function fetchKey() {
  try {
    // Jaribu kuchukua key kutoka nbc.php kama ipo, otherwise skip
    const res  = await fetch('nbc.php?t=' + Date.now());
    if (!res.ok) throw new Error('nbc.php haipo');
    const data = await res.json();
    if (data.success && data.key) {
      currentKey = data.key;
      const el = document.getElementById('dbKeyDisplay');
      if (el) el.textContent = currentKey.substring(0, 40) + '…';
      startCountdown();
    }
  } catch(e) {
    // nbc.php haipo — sio tatizo, endelea kawaida
    console.log('NBC key not available:', e.message);
  }
}

function startCountdown() {
  clearInterval(countInterval); clearTimeout(keyTimer);
  let cd = 30;
  // Remove old indicator
  document.getElementById('keyStatEl')?.remove();
  const el = document.createElement('div');
  el.className='stat'; el.id='keyStatEl';
  el.innerHTML = `<i class="fa fa-key"></i> KEY <span id="keyCd" style="color:var(--green);font-weight:700">⏱30s</span>`;
  statsBar.appendChild(el);

  countInterval = setInterval(() => {
    cd--;
    const e = document.getElementById('keyCd');
    if (e) e.textContent = `⏱${cd}s`;
    if (cd <= 0) clearInterval(countInterval);
  }, 1000);

  keyTimer = setTimeout(() => { clearInterval(countInterval); fetchKey(); }, 30000);
}

/* ══════════════════════════════════════════════════════════════
   NOTIFICATIONS — OneSignal
   - Omba ruhusa mara moja ukurasa unapofunguka (baada ya sekunde 3)
   - Fuatilia channels kila dakika 15 — gundua magol na mechi mpya
   - Tuma browser notification moja kwa moja bila server
   ══════════════════════════════════════════════════════════════ */

const OS_APP_ID = '10360777-3ada-4145-b83f-00eb0312a53f';

// State ya notifications
let notifState = {
  enabled:  false,
  subId:    null,
  lastData: {}, // { channelId: { score, minute, status } }
  ticker:   null,
};

// ── INIT OneSignal ──────────────────────────────────────────────
window.OneSignalDeferred = window.OneSignalDeferred || [];
OneSignalDeferred.push(async function(OneSignal) {
  await OneSignal.init({
    appId:                        OS_APP_ID,
    safari_web_id:               'web.onesignal.auto.YOUR_SAFARI_ID',
    notifyButton:                { enabled: false }, // Tunatumia bell yetu
    allowLocalhostAsSecureOrigin: true,
  });

  // Angalia hali ya awali
  const isPushed = await OneSignal.User.PushSubscription.optedIn;
  updateBell(isPushed);
  if (isPushed) {
    notifState.enabled = true;
    startWatcher();
  }

  // Sikiliza mabadiliko
  OneSignal.User.PushSubscription.addEventListener('change', e => {
    const on = e.current.optedIn;
    notifState.enabled = on;
    updateBell(on);
    showToast(on ? '🔔 Arifa zimewashwa! Utapata taarifa za mechi.' : '🔕 Arifa zimezimwa.');
    if (on) startWatcher(); else stopWatcher();
  });

  // Omba ruhusa baada ya sekunde 3 (mara ya kwanza tu)
  const asked = localStorage.getItem('jmtv_notif_asked');
  if (!asked && !isPushed) {
    setTimeout(async () => {
      showNotifBanner();
    }, 3000);
  }
});

// ── TOGGLE (Bell button) ────────────────────────────────────────
async function toggleNotifications() {
  OneSignalDeferred.push(async function(OneSignal) {
    const on = await OneSignal.User.PushSubscription.optedIn;
    if (on) {
      await OneSignal.User.PushSubscription.optOut();
      showToast('🔕 Arifa zimezimwa.');
    } else {
      await OneSignal.User.PushSubscription.optIn();
    }
  });
}

// ── BANNER YA RUHUSA ───────────────────────────────────────────
function showNotifBanner() {
  if (document.getElementById('__notif_banner')) return;
  localStorage.setItem('jmtv_notif_asked', '1');

  const b = document.createElement('div');
  b.id = '__notif_banner';
  b.style.cssText = `
    position:fixed;bottom:80px;left:12px;right:12px;z-index:9000;
    background:linear-gradient(135deg,#0d0d22,#0a0a1e);
    border:1px solid rgba(0,212,255,0.25);
    border-radius:16px;padding:14px 16px;
    display:flex;align-items:center;gap:12px;
    box-shadow:0 8px 32px rgba(0,0,0,0.7),0 0 0 1px rgba(0,212,255,0.1);
    animation:bannerUp 0.4s cubic-bezier(0.34,1.56,0.64,1);
    font-family:'Outfit',sans-serif;
  `;
  b.innerHTML = `
    <style>
      @keyframes bannerUp{from{opacity:0;transform:translateY(20px)}to{opacity:1;transform:translateY(0)}}
      .__nb-btn{padding:9px 18px;border:none;border-radius:9px;font-weight:700;font-size:12px;cursor:pointer;font-family:'Outfit',sans-serif;transition:all 0.2s;white-space:nowrap;}
      .__nb-yes{background:var(--accent,#00d4ff);color:#000;}
      .__nb-yes:hover{transform:scale(1.05);}
      .__nb-no{background:rgba(255,255,255,0.07);color:#6677aa;border:1px solid rgba(255,255,255,0.1);}
    </style>
    <span style="font-size:26px;flex-shrink:0">🔔</span>
    <div style="flex:1;min-width:0">
      <div style="font-size:13px;font-weight:700;color:#fff;margin-bottom:2px">Pata Arifa za Mechi!</div>
      <div style="font-size:11px;color:#6677aa">Goals, kickoff, na vipindi — moja kwa moja</div>
    </div>
    <button class="__nb-btn __nb-yes" onclick="acceptNotif()">Washa 🔔</button>
    <button class="__nb-btn __nb-no"  onclick="denyNotif()">×</button>
  `;
  document.body.appendChild(b);

  // Auto-hide baada ya sekunde 12
  setTimeout(() => b?.remove(), 12000);
}

function acceptNotif() {
  document.getElementById('__notif_banner')?.remove();
  OneSignalDeferred.push(async function(OneSignal) {
    await OneSignal.User.PushSubscription.optIn();
  });
}

function denyNotif() {
  document.getElementById('__notif_banner')?.remove();
}

// ── BELL ICON UPDATE ───────────────────────────────────────────
function updateBell(on) {
  const btn  = document.getElementById('bellBtn');
  const icon = document.getElementById('bellIcon');
  const dot  = document.getElementById('bellDot');
  if (!btn) return;
  if (on) {
    btn.style.background  = 'rgba(0,212,255,0.15)';
    btn.style.borderColor = 'var(--accent)';
    btn.style.color       = 'var(--accent)';
    icon.className        = 'fa fa-bell';
    dot.style.display     = 'none';
  } else {
    btn.style.background  = 'rgba(0,212,255,0.07)';
    btn.style.borderColor = 'var(--border)';
    btn.style.color       = 'var(--muted)';
    icon.className        = 'fa fa-bell-slash';
    dot.style.display     = 'block'; // Dot inaonyesha arifa zimezimwa
  }
}

// ══════════════════════════════════════════════════════════════
//  WATCHER — Fuatilia channels kila dakika 15
//  Gundua: mechi mpya, goals, score inabadilika
// ══════════════════════════════════════════════════════════════
function startWatcher() {
  stopWatcher();
  checkForUpdates(); // Angalia mara moja sasa hivi
  notifState.ticker = setInterval(checkForUpdates, 15 * 60 * 1000); // Kila dk 15
  console.log('[Notify] Watcher imeanza — kila dakika 15');
}

function stopWatcher() {
  if (notifState.ticker) { clearInterval(notifState.ticker); notifState.ticker = null; }
}

async function checkForUpdates() {
  if (!notifState.enabled) return;
  try {
    const res  = await fetch('channels.php?category=mechi%20za%20leo&t=' + Date.now());
    const data = await res.json();
    if (!data.success || !data.channels?.length) return;

    const channels  = data.channels;
    const prevState = notifState.lastData;
    const events    = [];

    channels.forEach(ch => {
      const id    = ch.id || ch.title || '';
      const title = ch.title || '';
      const hs    = ch.home_score;
      const as_   = ch.away_score;
      const min   = ch.minute || '';
      const prev  = prevState[id];

      // Mechi mpya inaonekana
      if (!prev) {
        events.push({
          title:   `🔴 LIVE: ${title}`,
          body:    `Mchezo unaendelea sasa! Bonyeza kutazama.`,
          icon:    'https://dde.ct.ws/icon.png',
          url:     'https://dde.ct.ws/live.php',
        });
      } else {
        // Angalia mabadiliko ya score
        const prevHs = prev.home_score;
        const prevAs = prev.away_score;
        const m = title.match(/^(.+?)\s+(?:vs\.?|VS\.?|[-–])\s+(.+)$/i);
        const home = m ? m[1].trim() : title;
        const away = m ? m[2].trim() : '';

        if (hs !== undefined && prevHs !== undefined) {
          if (hs > prevHs) events.push({
            title: `🚨 GOOOAL! ${home}`,
            body:  `⚽ ${home} ${hs} - ${as_} ${away}  ⏱ ${min}`,
            icon:  'https://dde.ct.ws/icon.png',
            url:   'https://dde.ct.ws/live.php',
          });
          if (as_ > prevAs) events.push({
            title: `🚨 GOOOAL! ${away}`,
            body:  `⚽ ${home} ${hs} - ${as_} ${away}  ⏱ ${min}`,
            icon:  'https://dde.ct.ws/icon.png',
            url:   'https://dde.ct.ws/live.php',
          });
        }
      }

      // Hifadhi state mpya
      notifState.lastData[id] = { home_score: hs, away_score: as_, minute: min, title };
    });

    // Tuma notifications
    for (const ev of events) {
      await sendBrowserNotif(ev.title, ev.body, ev.url);
      await new Promise(r => setTimeout(r, 800)); // Epuka spam
    }

    // Sasisha channels kwenye screen bila reload
    if (channels.length) {
      JMCache.clear('live_channels');
      allChannels = channels;
      renderChannels(allChannels, false);
    }

    console.log(`[Notify] Check: ${channels.length} channels, ${events.length} events`);
  } catch(e) {
    console.warn('[Notify] Check failed:', e.message);
  }
}

// ── TUMA BROWSER NOTIFICATION ─────────────────────────────────
async function sendBrowserNotif(title, body, url) {
  // Njia 1: OneSignal API ya moja kwa moja
  try {
    await fetch('https://onesignal.com/api/v1/notifications', {
      method: 'POST',
      headers: {
        'Authorization': 'Basic os_v2_app_ca3ao5z23jaulob7advqgevfh4qctlprzdauupekggukcgwmz5glfzdu6lkvnkzjeuno3cuuqow7fklo3fehp2puu52sr7sroo63hwy',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        app_id:             OS_APP_ID,
        included_segments:  ['All'],
        headings:           { en: title, sw: title },
        contents:           { en: body,  sw: body  },
        url:                url,
        small_icon:         'ic_launcher',
        android_accent_color: 'FF00D4FF',
        priority:           10,
        ttl:                3600,
      }),
    });
  } catch(e) {
    // Njia 2: Fallback — Web Notification API ya browser (ikiwa imeruhusiwa)
    if ('Notification' in window && Notification.permission === 'granted') {
      const n = new Notification(title, { body, icon: 'https://dde.ct.ws/icon.png' });
      n.onclick = () => { window.focus(); if (url) location.href = url; };
    }
  }
}

/* ── INIT ── */
loadMatches();
fetchKey();
loadTeamLogos(); // Pakua logo za timu kutoka AllSportsAPI (background)
</script>
</body>
</html>
