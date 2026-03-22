<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Tafuta</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
body { padding-top: 72px; }

/* ── TOPBAR EXTENDED ── */
.topbar { flex-wrap: wrap; height: auto; padding: 10px 14px 8px; gap: 8px; }
.topbar-row1 { display: flex; align-items: center; gap: 10px; width: 100%; }
.topbar-row2 { width: 100%; }

/* ── SEARCH INPUT BIG ── */
.search-big {
  position: relative;
  padding: 10px 14px 0;
  margin-bottom: 2px;
}
.search-big input {
  width: 100%;
  padding: 13px 48px 13px 46px;
  background: rgba(255,255,255,0.04);
  border: 1.5px solid var(--border);
  border-radius: 14px;
  color: var(--text);
  font-size: 15px;
  font-family: 'Outfit', sans-serif;
  outline: none;
  transition: all 0.3s;
}
.search-big input:focus {
  border-color: var(--accent);
  background: rgba(0,212,255,0.04);
  box-shadow: 0 0 0 3px rgba(0,212,255,0.1);
}
.search-big input::placeholder { color: var(--muted); }
.search-big .s-icon {
  position: absolute; left: 26px; top: 50%; transform: translateY(-50%);
  color: var(--accent); font-size: 16px; pointer-events: none;
}
.search-big .s-clear {
  position: absolute; right: 26px; top: 50%; transform: translateY(-50%);
  color: var(--muted); font-size: 14px; cursor: pointer;
  background: none; border: none; padding: 4px; display: none;
  transition: color 0.2s;
}
.search-big .s-clear:hover { color: var(--accent2); }
.search-big input:not(:placeholder-shown) ~ .s-clear { display: block; }

/* ── SOURCE FILTER CHIPS ── */
.source-chips {
  display: flex; gap: 7px;
  padding: 10px 14px 6px;
  overflow-x: auto; scrollbar-width: none;
}
.source-chips::-webkit-scrollbar { display: none; }
.chip {
  flex-shrink: 0;
  display: flex; align-items: center; gap: 5px;
  padding: 6px 13px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  font-size: 11px; font-weight: 700; color: var(--muted);
  cursor: pointer; transition: all 0.2s; white-space: nowrap;
  letter-spacing: 0.3px;
}
.chip:hover { color: var(--text); border-color: rgba(255,255,255,0.2); }
.chip.active { background: rgba(0,212,255,0.12); border-color: var(--accent); color: var(--accent); }
.chip.loading-chip i { animation: spin 0.7s linear infinite; }
.chip .chip-count {
  background: rgba(0,212,255,0.2); color: var(--accent);
  font-size: 9px; padding: 1px 5px; border-radius: 10px; min-width: 18px; text-align: center;
}

/* ── STATS BAR ── */
.stats-bar {
  display: flex; align-items: center; gap: 14px;
  padding: 6px 14px 8px;
  font-size: 11px; color: var(--muted);
  border-bottom: 1px solid rgba(255,255,255,0.04);
  flex-wrap: wrap;
}
.stats-bar span { display: flex; align-items: center; gap: 5px; }
.stats-bar strong { color: var(--text); }
.stats-bar .sep { color: rgba(255,255,255,0.1); }

/* ── RESULTS GRID ── */
.results-wrap { padding: 10px 12px 16px; }

/* ── SOURCE GROUP HEADER ── */
.src-group { margin-bottom: 18px; }
.src-hdr {
  display: flex; align-items: center; gap: 9px;
  padding: 6px 0 10px;
  font-family: 'Bebas Neue', sans-serif;
  font-size: 15px; letter-spacing: 2px; color: var(--muted);
  border-bottom: 1px solid var(--border); margin-bottom: 10px;
}
.src-hdr i { color: var(--accent); font-size: 13px; }
.src-hdr .src-cnt { font-size: 11px; color: var(--muted); font-family: 'Outfit', sans-serif; font-weight: 400; letter-spacing: 0; margin-left: auto; }

/* ── RESULT CARD ── */
.res-grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 10px;
}
@media(min-width: 480px) { .res-grid { grid-template-columns: repeat(3, 1fr); } }
@media(min-width: 700px) { .res-grid { grid-template-columns: repeat(4, 1fr); } }

.res-card {
  background: var(--card);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px; overflow: hidden;
  cursor: pointer; position: relative;
  transition: all 0.3s;
  opacity: 0; transform: translateY(12px);
}
.res-card.vis { opacity: 1; transform: translateY(0); transition: opacity 0.35s ease, transform 0.35s ease, border-color 0.2s, box-shadow 0.2s; }
.res-card:hover { border-color: var(--accent); box-shadow: var(--glow); transform: translateY(-3px); }
.res-card:active { transform: scale(0.96); }

.rc-img {
  width: 100%; height: 90px;
  object-fit: contain; padding: 8px;
  background: rgba(0,0,0,0.3);
  display: block; transition: transform 0.3s;
}
.res-card:hover .rc-img { transform: scale(1.06); }

.rc-overlay {
  position: absolute; top: 0; left: 0; right: 0; height: 90px;
  background: linear-gradient(to top, rgba(0,0,0,0.75) 0%, transparent 60%);
}
.rc-live {
  position: absolute; top: 6px; left: 6px;
  background: var(--accent2); color: #fff;
  font-size: 8px; font-weight: 700; padding: 2px 6px;
  border-radius: 4px; letter-spacing: 0.5px;
  display: flex; align-items: center; gap: 3px;
}
.rc-live::before {
  content: ''; width: 5px; height: 5px; border-radius: 50%;
  background: #fff; animation: blink 1s infinite;
}
.rc-source {
  position: absolute; top: 6px; right: 6px;
  font-size: 8px; font-weight: 700;
  padding: 2px 6px; border-radius: 4px; letter-spacing: 0.3px;
}
.src-mechi  { background: rgba(0,212,255,0.85); color: #000; }
.src-azam   { background: rgba(255,107,53,0.85); color: #fff; }
.src-nbc    { background: rgba(255,215,0,0.85); color: #000; }
.src-global { background: rgba(168,85,247,0.85); color: #fff; }

.rc-play {
  position: absolute; top: 50%; left: 50%;
  transform: translate(-50%, -70%);
  width: 34px; height: 34px;
  background: rgba(0,0,0,0.65); border-radius: 50%;
  display: flex; align-items: center; justify-content: center;
  opacity: 0; transition: opacity 0.25s;
}
.rc-play i { color: var(--accent); font-size: 13px; margin-left: 2px; }
.res-card:hover .rc-play { opacity: 1; }

.rc-info { padding: 8px 9px 10px; }
.rc-name {
  font-size: 11.5px; font-weight: 700;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
  color: var(--text); margin-bottom: 3px;
}
.rc-cat {
  font-size: 10px; color: var(--muted);
  display: flex; align-items: center; gap: 4px;
}
.rc-cat i { font-size: 9px; color: var(--accent); }

/* ── MATCH CARD (for Mechi) — Muundo kama live.php ── */
.match-card {
  background: var(--card);
  border: 1px solid rgba(255,255,255,0.06);
  border-left: 3px solid var(--accent);
  border-radius: 14px; padding: 12px 14px;
  cursor: pointer; transition: all 0.3s;
  opacity: 0; transform: translateY(12px);
  grid-column: 1 / -1;
  position: relative; overflow: hidden;
}
.match-card::after {
  content: '';
  position: absolute; inset: 0;
  background: linear-gradient(135deg, rgba(0,212,255,0.03) 0%, transparent 60%);
  pointer-events: none;
}
.match-card.vis { opacity: 1; transform: translateY(0); transition: opacity 0.35s ease, transform 0.35s ease, border-color 0.2s, box-shadow 0.2s; }
.match-card:hover { border-color: var(--accent); box-shadow: var(--glow); transform: translateY(-2px); }
.match-card:active { transform: scale(0.98); }

.mc-top-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 10px; }
.mc-league-tag { font-size: 10px; font-weight: 700; padding: 3px 9px; border-radius: 20px; background: rgba(0,212,255,0.12); color: var(--accent); display: flex; align-items: center; gap: 4px; }
.mc-live-badge { background: rgba(255,68,102,0.2); color: var(--accent2); border: 1px solid rgba(255,68,102,0.3); font-size: 9px; font-weight: 700; padding: 3px 8px; border-radius: 10px; display: flex; align-items: center; gap: 4px; animation: blinkBg 1.2s infinite; }
.mc-live-badge::before { content: ''; width: 5px; height: 5px; background: var(--accent2); border-radius: 50%; animation: blink 1s infinite; }
@keyframes blinkBg { 0%,100%{opacity:1} 50%{opacity:0.65} }
@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }

.mc-matchup { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
.mc-team-blk { flex: 1; display: flex; flex-direction: column; align-items: center; gap: 5px; text-align: center; }
.mc-t-logo { width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,0.04); border: 1.5px solid rgba(255,255,255,0.1); display: flex; align-items: center; justify-content: center; overflow: hidden; }
.mc-t-logo img { width: 28px; height: 28px; object-fit: contain; }
.mc-t-name { font-size: 12px; font-weight: 700; max-width: 80px; line-height: 1.3; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.mc-vs-blk { display: flex; flex-direction: column; align-items: center; gap: 2px; flex-shrink: 0; min-width: 56px; }
.mc-vs-txt { font-family: 'Bebas Neue', sans-serif; font-size: 20px; color: rgba(255,255,255,0.18); letter-spacing: 3px; }
.mc-score { font-family: 'Bebas Neue', sans-serif; font-size: 26px; color: var(--green); letter-spacing: 3px; line-height: 1; }
.mc-min { font-size: 10px; color: var(--accent2); font-weight: 700; }

.mc-foot-row { display: flex; align-items: center; justify-content: space-between; margin-top: 10px; padding-top: 8px; border-top: 1px solid rgba(255,255,255,0.05); }
.mc-ch { font-size: 10px; color: var(--muted); display: flex; align-items: center; gap: 4px; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; max-width: 60%; }
.mc-watch { display: flex; align-items: center; gap: 5px; background: rgba(0,212,255,0.1); border: 1px solid rgba(0,212,255,0.2); color: var(--accent); font-size: 10px; font-weight: 700; padding: 5px 12px; border-radius: 20px; flex-shrink: 0; transition: all 0.2s; }
.match-card:hover .mc-watch { background: rgba(0,212,255,0.2); border-color: var(--accent); }

.mc-team { flex: 1; font-size: 13px; font-weight: 700; }
.mc-team.away { text-align: right; }
.mc-vs { font-family: 'Bebas Neue', sans-serif; font-size: 16px; color: var(--muted); flex-shrink: 0; }
.mc-meta { display: flex; align-items: center; justify-content: space-between; font-size: 10px; color: var(--muted); }

/* ── EMPTY / NO RESULTS ── */
.empty-wrap { text-align: center; padding: 44px 20px; color: var(--muted); }
.empty-wrap .e-ico { font-size: 44px; display: block; margin-bottom: 14px; color: rgba(255,255,255,0.1); }
.empty-wrap h3 { font-family: 'Bebas Neue', sans-serif; font-size: 20px; letter-spacing: 2px; color: rgba(255,255,255,0.25); margin-bottom: 8px; }
.empty-wrap p { font-size: 12px; line-height: 1.7; }

/* ── SKELETON ── */
.skel {
  background: linear-gradient(90deg, rgba(255,255,255,0.04) 25%, rgba(255,255,255,0.08) 50%, rgba(255,255,255,0.04) 75%);
  background-size: 200% 100%;
  animation: skel-anim 1.4s infinite;
  border-radius: 8px;
}
@keyframes skel-anim { 0% { background-position: 200% 0; } 100% { background-position: -200% 0; } }

.skel-card { background: var(--card); border: 1px solid rgba(255,255,255,0.05); border-radius: 14px; overflow: hidden; }
.skel-img  { height: 90px; }
.skel-line { height: 10px; margin: 8px; border-radius: 5px; }
.skel-line.short { width: 60%; }

/* ── SUGGESTIONS ── */
.suggestions {
  padding: 6px 14px 12px;
  display: none;
}
.suggestions.show { display: block; }
.sug-title { font-size: 11px; color: var(--muted); font-weight: 700; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 8px; }
.sug-chips { display: flex; flex-wrap: wrap; gap: 7px; }
.sug-chip {
  padding: 6px 13px; background: rgba(255,255,255,0.04);
  border: 1px solid rgba(255,255,255,0.08); border-radius: 20px;
  font-size: 11px; color: var(--muted); cursor: pointer;
  transition: all 0.2s;
}
.sug-chip:hover { color: var(--accent); border-color: var(--accent); background: rgba(0,212,255,0.06); }

/* ── HIGHLIGHT ── */
mark {
  background: rgba(0,212,255,0.22); color: var(--accent);
  border-radius: 3px; padding: 0 2px;
}

@keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.2} }
@keyframes spin   { to { transform: rotate(360deg); } }
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- TOPBAR -->
<div class="topbar">
  <div class="topbar-row1">
    <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <div class="page-title">TAFUTA <span>CHANNELS</span></div>
  </div>
</div>

<!-- SEARCH INPUT -->
<div class="search-big">
  <i class="fa fa-magnifying-glass s-icon"></i>
  <input type="text" id="mainSearch" placeholder="Tafuta mechi, channels, makundi..." autofocus autocomplete="off">
  <button class="s-clear" id="clearBtn" onclick="clearSearch()" title="Futa"><i class="fa fa-xmark"></i></button>
</div>

<!-- SUGGESTIONS (shown when empty) -->
<div class="suggestions" id="suggestions">
  <div class="sug-title"><i class="fa fa-bolt" style="color:var(--accent)"></i> Maarufu</div>
  <div class="sug-chips" id="sugChips">
    <div class="sug-chip" onclick="setSearch('sports')">⚽ Sports</div>
    <div class="sug-chip" onclick="setSearch('NBC')">📺 NBC</div>
    <div class="sug-chip" onclick="setSearch('Azam')">🛰️ Azam</div>
    <div class="sug-chip" onclick="setSearch('music')">🎵 Music</div>
    <div class="sug-chip" onclick="setSearch('drama')">🎭 Drama</div>
    <div class="sug-chip" onclick="setSearch('Champions')">🌟 UCL</div>
    <div class="sug-chip" onclick="setSearch('Premier')">🏆 EPL</div>
    <div class="sug-chip" onclick="setSearch('Simba')">🦁 Simba</div>
    <div class="sug-chip" onclick="setSearch('Yanga')">💚 Yanga</div>
    <div class="sug-chip" onclick="setSearch('NBA')">🏀 NBA</div>
    <div class="sug-chip" onclick="setSearch('Formula')">🏎️ F1</div>
    <div class="sug-chip" onclick="setSearch('wasafi')">🎤 Wasafi</div>
  </div>
</div>

<!-- SOURCE FILTER CHIPS -->
<div class="source-chips" id="sourceChips">
  <div class="chip active" data-src="all" onclick="setSource(this,'all')">
    <i class="fa fa-border-all"></i> Zote
    <span class="chip-count" id="cnt-all">0</span>
  </div>
  <div class="chip" data-src="mechi" onclick="setSource(this,'mechi')">
    <i class="fa fa-futbol"></i> Mechi Live
    <span class="chip-count" id="cnt-mechi">0</span>
  </div>
  <div class="chip" data-src="azam" onclick="setSource(this,'azam')">
    <i class="fa fa-satellite-dish"></i> Azam TV
    <span class="chip-count" id="cnt-azam">0</span>
  </div>
  <div class="chip" data-src="nbc" onclick="setSource(this,'nbc')">
    <i class="fa fa-tv"></i> NBC / Local
    <span class="chip-count" id="cnt-nbc">0</span>
  </div>
  <div class="chip" data-src="global" onclick="setSource(this,'global')">
    <i class="fa fa-globe"></i> Global TV
    <span class="chip-count" id="cnt-global">0</span>
  </div>
</div>

<!-- STATS BAR -->
<div class="stats-bar" id="statsBar" style="display:none">
  <span><i class="fa fa-list" style="color:var(--accent)"></i> Matokeo: <strong id="totalCount">0</strong></span>
  <span class="sep">·</span>
  <span id="queryDisplay" style="color:var(--accent)"></span>
  <span class="sep">·</span>
  <span id="timeDisplay"></span>
</div>

<!-- RESULTS -->
<div class="results-wrap" id="resultsWrap">
  <!-- Shown on first load -->
  <div class="empty-wrap" id="emptyState">
    <i class="fa fa-magnifying-glass e-ico"></i>
    <h3>TAFUTA CHOCHOTE</h3>
    <p>Andika jina la channel, mchezo, au timu.<br>Tutakutafutia kwenye vyanzo vyote kwa wakati mmoja.</p>
  </div>
</div>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item"><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script src="_cache.js"></script>
<script src="_auth_guard.js"></script>
<script src="_channel_popup.js"></script>
<script>
/* ════════════════════════════════════════════════
   JAYNES MAX TV — search.php
   Inatafuta kwenye vyanzo VINNE kwa wakati mmoja:
   1. channels.php  → Mechi Live (desksanalytics)
   2. azam.php      → Azam TV channels
   3. nbc.php       → NBC / Local channels
   4. key.php       → Global TV channels
   ════════════════════════════════════════════════ */

// ── STATE ──────────────────────────────────────────────────────
const allData = { mechi: [], azam: [], nbc: [], global: [] };
const loadState = { mechi: 'idle', azam: 'idle', nbc: 'idle', global: 'idle' };
let currentSource = 'all';
let currentQuery  = '';
let searchTimer   = null;
let searchStart   = 0;

// ── ELEMENTS ───────────────────────────────────────────────────
const mainSearch  = document.getElementById('mainSearch');
const resultsWrap = document.getElementById('resultsWrap');
const statsBar    = document.getElementById('statsBar');
const emptyState  = document.getElementById('emptyState');
const suggestions = document.getElementById('suggestions');

// ── URL PARAM: ?q=... ──────────────────────────────────────────
(function checkUrlQuery() {
  const q = new URLSearchParams(location.search).get('q') || '';
  if (q) {
    mainSearch.value = q;
    suggestions.classList.remove('show');
    loadAllSources(q);
  } else {
    suggestions.classList.add('show');
  }
})();

// ── INPUT HANDLER ──────────────────────────────────────────────
mainSearch.addEventListener('input', () => {
  const q = mainSearch.value.trim();
  clearTimeout(searchTimer);
  if (!q) {
    clearSearch();
    return;
  }
  suggestions.classList.remove('show');
  searchTimer = setTimeout(() => {
    if (q !== currentQuery) {
      loadAllSources(q);
    } else {
      renderResults();
    }
  }, 280);
});

mainSearch.addEventListener('keydown', e => {
  if (e.key === 'Enter') {
    clearTimeout(searchTimer);
    const q = mainSearch.value.trim();
    if (q) loadAllSources(q);
  }
});

function clearSearch() {
  mainSearch.value = '';
  currentQuery = '';
  resultsWrap.innerHTML = '';
  resultsWrap.appendChild(emptyState);
  emptyState.style.display = 'block';
  statsBar.style.display = 'none';
  suggestions.classList.add('show');
  updateChipCounts({});
  // Reset URL
  history.replaceState(null, '', 'search.php');
}

function setSearch(q) {
  mainSearch.value = q;
  suggestions.classList.remove('show');
  loadAllSources(q);
}

// ── SOURCE FILTER ──────────────────────────────────────────────
function setSource(el, src) {
  document.querySelectorAll('.chip').forEach(c => c.classList.remove('active'));
  el.classList.add('active');
  currentSource = src;
  renderResults();
}

// ── LOAD ALL SOURCES IN PARALLEL ───────────────────────────────
async function loadAllSources(query) {
  currentQuery = query;
  searchStart  = Date.now();
  emptyState.style.display = 'none';
  statsBar.style.display   = 'none';

  // Update URL
  history.replaceState(null, '', `search.php?q=${encodeURIComponent(query)}`);

  // Show skeletons immediately
  showSkeletons();

  // Fire all 4 fetches at once
  await Promise.allSettled([
    fetchMechi(query),
    fetchAzam(query),
    fetchNbc(query),
    fetchGlobal(query),
  ]);

  // Final render
  renderResults();
  updateStats(query);
}

// ══════════════════════════════════════════════
// 1. MECHI LIVE — channels.php (desksanalytics)
// ══════════════════════════════════════════════
async function fetchMechi(query) {
  loadState.mechi = 'loading';
  try {
    // Tumia cache — dakika 3
    let channels = JMCache.get('search_mechi');
    if (!channels) {
      const res  = await fetch('channels.php?category=mechi%20za%20leo&t=' + Date.now());
      const data = await res.json();
      channels   = data.channels || [];
      JMCache.set('search_mechi', channels);
    }
    allData.mechi = channels.map(ch => ({
      id:       ch.id || '',
      name:     ch.title || ch.name || '',
      category: ch.category || 'Mechi za Leo',
      image:    ch.logo || ch.image || '',
      url:      ch.url || '',
      key:      ch.key || ch.clearkey || '',
      headers:  ch.headers || {},
      source:   'mechi',
      type:     detectType(ch.url || ''),
      home:     extractTeam(ch.title, 'home'),
      away:     extractTeam(ch.title, 'away'),
      isMatch:  isMatchTitle(ch.title || ''),
    }));
    loadState.mechi = 'done';
  } catch(e) {
    allData.mechi   = [];
    loadState.mechi = 'error';
    console.warn('[Search] Mechi fetch failed:', e.message);
  }
  renderResults(true);
}

// ══════════════════════════════════════════════
// 2. AZAM TV — azam.php
// ══════════════════════════════════════════════
async function fetchAzam(query) {
  loadState.azam = 'loading';
  try {
    let channels = JMCache.get('search_azam');
    if (!channels) {
      const res  = await fetch('azam.php');
      const data = await res.json();
      channels   = data.channels || [];
      JMCache.set('search_azam', channels);
    }
    allData.azam = channels.map(ch => ({
      id:       ch.name || '',
      name:     ch.name || '',
      category: ch.category || 'Azam TV',
      image:    ch.image || '',
      url:      ch.url  || '',
      key:      ch.key  || '',
      headers:  {},
      source:   'azam',
      type:     ch.type || detectType(ch.url || ''),
      isMatch:  false,
    }));
    loadState.azam = 'done';
  } catch(e) {
    allData.azam   = [];
    loadState.azam = 'error';
    console.warn('[Search] Azam fetch failed:', e.message);
  }
  renderResults(true);
}

// ══════════════════════════════════════════════
// 3. NBC / LOCAL — nbc.php
// ══════════════════════════════════════════════
async function fetchNbc(query) {
  loadState.nbc = 'loading';
  try {
    let channels = JMCache.get('search_nbc');
    if (!channels) {
      const res  = await fetch('nbc.php');
      const data = await res.json();
      channels   = data.channels || [];
      JMCache.set('search_nbc', channels);
    }
    allData.nbc = channels.map(ch => ({
      id:       ch.id || ch.channel_id || '',
      name:     ch.title || '',
      category: 'NBC / Local',
      image:    ch.logo  || '',
      url:      ch.url   || '',
      key:      ch.clearkey ? `${ch.clearkey.kid}:${ch.clearkey.key}` : '',
      headers:  ch.headers || {},
      source:   'nbc',
      type:     ch.stream_type || detectType(ch.url || ''),
      isMatch:  false,
    }));
    loadState.nbc = 'done';
  } catch(e) {
    allData.nbc   = [];
    loadState.nbc = 'error';
    console.warn('[Search] NBC fetch failed:', e.message);
  }
  renderResults(true);
}

// ══════════════════════════════════════════════
// 4. GLOBAL TV — key.php
// ══════════════════════════════════════════════
async function fetchGlobal(query) {
  loadState.global = 'loading';
  try {
    let channels = JMCache.get('search_global');
    if (!channels) {
      const res  = await fetch('key.php');
      const data = await res.json();
      channels   = data.channels || [];
      JMCache.set('search_global', channels);
    }
    allData.global = channels.map(ch => ({
      id:       ch.id    || '',
      name:     ch.name  || '',
      category: 'Global TV',
      image:    ch.image || '',
      url:      ch.url   || '',
      key:      ch.key   || '',
      headers:  {},
      source:   'global',
      type:     detectType(ch.url || ''),
      isMatch:  false,
    }));
    loadState.global = 'done';
  } catch(e) {
    allData.global   = [];
    loadState.global = 'error';
    console.warn('[Search] Global fetch failed:', e.message);
  }
  renderResults(true);
}

// ── FILTER BY QUERY ────────────────────────────────────────────
function filterChannels(channels, query) {
  if (!query) return channels;
  const q = query.toLowerCase();
  return channels.filter(ch => {
    const haystack = [ch.name, ch.category, ch.home, ch.away].filter(Boolean).join(' ').toLowerCase();
    return haystack.includes(q);
  });
}

// ── GET VISIBLE DATA ───────────────────────────────────────────
function getFiltered() {
  const sources = currentSource === 'all'
    ? ['mechi','azam','nbc','global']
    : [currentSource];

  const result = {};
  let total = 0;
  sources.forEach(src => {
    const filtered = filterChannels(allData[src], currentQuery);
    result[src] = filtered;
    total += filtered.length;
  });
  result._total = total;
  return result;
}

// ── CHIP COUNTS ────────────────────────────────────────────────
function updateChipCounts(filtered) {
  const sources = ['mechi','azam','nbc','global'];
  let total = 0;
  sources.forEach(src => {
    const cnt = filtered[src]?.length || 0;
    total += cnt;
    const el = document.getElementById('cnt-' + src);
    if (el) el.textContent = cnt;
  });
  const allEl = document.getElementById('cnt-all');
  if (allEl) allEl.textContent = total;
}

// ── STATS BAR ──────────────────────────────────────────────────
function updateStats(query) {
  const filtered = getFiltered();
  updateChipCounts(filtered);
  const total = filtered._total || 0;
  const ms    = Date.now() - searchStart;

  document.getElementById('totalCount').textContent = total;
  document.getElementById('queryDisplay').textContent = `"${query}"`;
  document.getElementById('timeDisplay').textContent = `${ms}ms`;
  statsBar.style.display = 'flex';
}

// ── RENDER RESULTS ─────────────────────────────────────────────
function renderResults(partial = false) {
  const filtered = getFiltered();
  updateChipCounts(filtered);

  const sources = currentSource === 'all'
    ? ['mechi','azam','nbc','global']
    : [currentSource];

  const total = filtered._total || 0;

  // If all done and no results
  const allDone = sources.every(s => loadState[s] === 'done' || loadState[s] === 'error');
  if (allDone && total === 0 && currentQuery) {
    resultsWrap.innerHTML = `
      <div class="empty-wrap">
        <i class="fa fa-magnifying-glass e-ico"></i>
        <h3>HAKUNA MATOKEO</h3>
        <p>Hakuna kitu kilichopatikana kwa "<strong style="color:var(--accent)">${esc(currentQuery)}</strong>".<br>
        Jaribu maneno mengine au angalia tahajia.</p>
      </div>`;
    statsBar.style.display = 'flex';
    document.getElementById('totalCount').textContent = 0;
    document.getElementById('queryDisplay').textContent = `"${currentQuery}"`;
    document.getElementById('timeDisplay').textContent = `${Date.now()-searchStart}ms`;
    return;
  }

  if (total === 0 && !allDone) return; // Still loading, skeletons shown

  // Build HTML per source group
  const html = [];
  let delay = 0;

  sources.forEach(src => {
    const items = filtered[src] || [];
    const state = loadState[src];

    if (state === 'loading') {
      html.push(buildSkeletonGroup(src));
      return;
    }
    if (!items.length) return;

    const srcInfo = SOURCE_META[src];
    html.push(`
      <div class="src-group" id="grp-${src}">
        <div class="src-hdr">
          <i class="fa ${srcInfo.icon}"></i> ${srcInfo.label}
          <span class="src-cnt">${items.length} matokeo</span>
        </div>
        <div class="res-grid">
          ${src === 'mechi'
            ? items.map((ch, i) => buildMatchOrCard(ch, i, delay++)).join('')
            : items.map((ch, i) => buildChannelCard(ch, i, delay++)).join('')
          }
        </div>
      </div>`);
  });

  if (!html.length && partial) return; // Nothing to show yet

  resultsWrap.innerHTML = html.join('') || `
    <div class="empty-wrap">
      <i class="fa fa-magnifying-glass e-ico"></i>
      <h3>TAFUTA CHOCHOTE</h3>
      <p>Andika jina la channel au mchezo.</p>
    </div>`;

  // Animate cards in
  requestAnimationFrame(() => {
    document.querySelectorAll('.res-card, .match-card').forEach((el, i) => {
      setTimeout(() => el.classList.add('vis'), i * 30);
    });
  });

  if (!partial) updateStats(currentQuery);
}

// ── SOURCE METADATA ────────────────────────────────────────────
const SOURCE_META = {
  mechi:  { label: 'MECHI LIVE',  icon: 'fa-futbol',        badge: 'src-mechi',  tag: 'MECHI'  },
  azam:   { label: 'AZAM TV',     icon: 'fa-satellite-dish', badge: 'src-azam',   tag: 'AZAM'   },
  nbc:    { label: 'NBC / LOCAL', icon: 'fa-tv',             badge: 'src-nbc',    tag: 'NBC'    },
  global: { label: 'GLOBAL TV',   icon: 'fa-globe',          badge: 'src-global', tag: 'GLOBAL' },
};

// ── BUILD CHANNEL CARD ─────────────────────────────────────────
function buildChannelCard(ch, idx, delay) {
  const src   = SOURCE_META[ch.source];
  const img   = ch.image || '';
  const name  = highlightQuery(ch.name, currentQuery);
  const cat   = highlightQuery(ch.category || '', currentQuery);
  const href  = buildPlayerUrl(ch);

  return `
    <div class="res-card" style="animation-delay:${delay*28}ms" onclick="goPlay(${JSON.stringify(JSON.stringify(ch))})">
      ${img
        ? `<img class="rc-img" src="${esc(img)}" alt="${esc(ch.name)}" loading="lazy"
               onerror="this.src='https://via.placeholder.com/200x90/101020/00d4ff?text=📺'">`
        : `<div class="rc-img" style="display:flex;align-items:center;justify-content:center;font-size:30px;height:90px">📺</div>`
      }
      <div class="rc-overlay"></div>
      <div class="rc-live">LIVE</div>
      <div class="rc-source ${src.badge}">${src.tag}</div>
      <div class="rc-play"><i class="fa fa-play"></i></div>
      <div class="rc-info">
        <div class="rc-name">${name}</div>
        <div class="rc-cat"><i class="fa fa-folder"></i>${cat}</div>
      </div>
    </div>`;
}

// ── BUILD MATCH CARD ───────────────────────────────────────────
function buildMatchOrCard(ch, idx, delay) {
  if (!ch.isMatch) return buildChannelCard(ch, idx, delay);

  const home    = ch.home || ch.name;
  const away    = ch.away || '—';
  const hasScore = ch.home_score !== undefined && ch.away_score !== undefined;
  const isLive  = hasScore;

  const homeLogo = ch.home_logo
    ? `<img src="${esc(ch.home_logo)}" loading="lazy" onerror="this.style.display='none'" alt="${esc(home)}">`
    : `<span style="font-size:20px">🏠</span>`;
  const awayLogo = ch.away_logo
    ? `<img src="${esc(ch.away_logo)}" loading="lazy" onerror="this.style.display='none'" alt="${esc(away)}">`
    : `<span style="font-size:20px">🛡️</span>`;

  const midBlock = hasScore
    ? `<div class="mc-score">${ch.home_score} - ${ch.away_score}</div>${ch.minute ? `<div class="mc-min">⏱ ${esc(ch.minute)}'</div>` : ''}`
    : `<div class="mc-vs-txt">VS</div>`;

  const hHome = highlightQuery(home, currentQuery);
  const hAway = highlightQuery(away, currentQuery);
  const hCat  = esc(ch.category || 'Mechi za Leo');

  return `
    <div class="match-card" style="animation-delay:${delay*28}ms" onclick="goPlay(${JSON.stringify(JSON.stringify(ch))})">
      <div class="mc-top-row">
        <div class="mc-league-tag"><i class="fa fa-futbol"></i> ${hCat}</div>
        ${isLive ? '<div class="mc-live-badge">LIVE</div>' : '<div style="font-size:9px;color:var(--muted)">📺 Inachezwa</div>'}
      </div>
      <div class="mc-matchup">
        <div class="mc-team-blk">
          <div class="mc-t-logo">${homeLogo}</div>
          <div class="mc-t-name">${hHome}</div>
        </div>
        <div class="mc-vs-blk">${midBlock}</div>
        <div class="mc-team-blk">
          <div class="mc-t-logo">${awayLogo}</div>
          <div class="mc-t-name">${hAway}</div>
        </div>
      </div>
      <div class="mc-foot-row">
        <div class="mc-ch"><i class="fa fa-satellite-dish" style="color:var(--accent);font-size:9px"></i> ${esc(ch.name || '')}</div>
        <div class="mc-watch"><i class="fa fa-play"></i> Tazama LIVE</div>
      </div>
    </div>`;
}

// ── SKELETONS ──────────────────────────────────────────────────
function showSkeletons() {
  resultsWrap.innerHTML =
    ['mechi','azam','nbc','global']
    .map(buildSkeletonGroup)
    .join('');
}

function buildSkeletonGroup(src) {
  const srcInfo = SOURCE_META[src];
  const cards = Array(4).fill(0).map(() => `
    <div class="skel-card">
      <div class="skel skel-img"></div>
      <div class="skel skel-line" style="margin-top:10px"></div>
      <div class="skel skel-line short"></div>
    </div>`).join('');
  return `
    <div class="src-group" id="skel-${src}">
      <div class="src-hdr" style="opacity:0.4">
        <i class="fa ${srcInfo.icon}"></i> ${srcInfo.label}
        <span class="src-cnt">Inapakia...</span>
      </div>
      <div class="res-grid">${cards}</div>
    </div>`;
}

// ── NAVIGATE TO PLAYER ─────────────────────────────────────────
function goPlay(chJson) {
  const ch   = JSON.parse(chJson);
  const url  = ch.url  || '';
  const key  = ch.key  || '';
  const name = ch.name || ch.title || 'LIVE';

  // NBC / DASH + clearkey → player.php
  const isDASH = ch.source === 'nbc'
    || (ch.type||'').toLowerCase() === 'dash'
    || (ch.type||'').toLowerCase() === 'clearkey'
    || url.includes('.mpd');

  if (isDASH) {
    location.href = 'player.php?url=' + encodeURIComponent(url)
      + '&key=' + encodeURIComponent(key)
      + '&name=' + encodeURIComponent(name);
    return;
  }

  // Tumia goChannelFull (popup) kama ipo, vinginevyo nenda moja kwa moja
  const playerUrl = 'player.html?data=' + encodeURIComponent(JSON.stringify({
    url,
    title:      name,
    name,
    key,
    clearkey:   ch.clearkey || key,
    headers:    ch.headers  || {},
    source:     ch.source   || '',
    category:   ch.category || '',
    image:      ch.image    || '',
    home_logo:  ch.home_logo || '',
    away_logo:  ch.away_logo || '',
    home_score: ch.home_score,
    away_score: ch.away_score,
    minute:     ch.minute   || '',
    channel_name: name,
  }));

  if (typeof goChannelFull === 'function') {
    goChannelFull(ch, playerUrl);
  } else if (typeof goChannel === 'function') {
    goChannel(name, playerUrl);
  } else {
    location.href = playerUrl;
  }
}

function buildPlayerUrl(ch) {
  const isHLS = ch.type === 'hls' || ch.type === 'HLS' || (ch.url || '').includes('.m3u8');
  if (isHLS) return 'player.html?data=' + encodeURIComponent(JSON.stringify({url:ch.url,title:ch.name,key:ch.key,headers:ch.headers}));
  return `player.php?url=${encodeURIComponent(ch.url)}&key=${encodeURIComponent(ch.key||'')}&name=${encodeURIComponent(ch.name)}`;
}

// ── HELPERS ────────────────────────────────────────────────────
function detectType(url) {
  if (!url) return 'unknown';
  if (url.includes('.m3u8')) return 'hls';
  if (url.includes('.mpd'))  return 'dash';
  return 'unknown';
}

function isMatchTitle(title) {
  return /\bvs\.?\b/i.test(title) || / - /.test(title) || /\bv\b/.test(title);
}

function extractTeam(title, side) {
  if (!title) return '';
  const m = title.match(/^(.+?)\s+(?:vs\.?|VS\.?|[-–])\s+(.+)$/i);
  if (!m) return side === 'home' ? title : '';
  return side === 'home' ? m[1].trim() : m[2].trim();
}

function highlightQuery(text, query) {
  if (!query || !text) return esc(text || '');
  const escaped = esc(text);
  const escapedQ = esc(query).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
  return escaped.replace(new RegExp(escapedQ, 'gi'), m => `<mark>${m}</mark>`);
}

function esc(s) {
  const d = document.createElement('div');
  d.textContent = s || '';
  return d.innerHTML;
}

function showToast(msg, dur=3000) {
  const t = document.getElementById('toast');
  t.textContent = msg; t.classList.add('show');
  clearTimeout(t._t); t._t = setTimeout(() => t.classList.remove('show'), dur);
}
</script>
</body>
</html>
