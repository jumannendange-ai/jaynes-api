/**
 * _match_widget.js — JAYNES MAX TV
 * Widget ya ⚽ kona ya chini kulia — inaonyesha mechi live na schedule
 * Inatumia AllSportsAPI (yako tayari kwenye schedule.php)
 * Auto-update kila dakika 1 kwa mechi live
 */
(function () {
  const API_LIVE     = 'match_live.php';   // proxy iliyoundwa hapa chini
  const API_TODAY    = 'match_today.php';  // proxy iliyoundwa hapa chini

  // ─── CSS ─────────────────────────────────────────────────────────────
  const css = document.createElement('style');
  css.textContent = `
    /* ── Bell button ── */
    #jmt-widget-btn {
      position: fixed;
      bottom: 76px; right: 16px;
      width: 48px; height: 48px;
      background: #0d0d22;
      border: 1.5px solid rgba(0,212,255,0.18);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      cursor: pointer; z-index: 8000;
      box-shadow: 0 4px 18px rgba(0,0,0,0.5);
      transition: transform 0.2s, box-shadow 0.2s;
      font-size: 1.3rem;
    }
    #jmt-widget-btn:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 24px rgba(0,212,255,0.2);
    }
    /* Live dot */
    #jmt-w-dot {
      position: absolute; top: 3px; right: 3px;
      width: 11px; height: 11px;
      background: #ff4466;
      border: 2px solid #04040f;
      border-radius: 50%;
      display: none;
      animation: jmtWdotPop 0.3s ease, jmtWdotRing 1.5s infinite 0.3s;
    }
    #jmt-w-dot.show { display: block; }
    @keyframes jmtWdotPop  { from{transform:scale(0)}to{transform:scale(1)} }
    @keyframes jmtWdotRing { 0%,100%{box-shadow:0 0 0 0 rgba(255,68,102,0.4)}50%{box-shadow:0 0 0 5px rgba(255,68,102,0)} }
    /* Count badge */
    #jmt-w-cnt {
      position: absolute; top: -3px; right: -3px;
      min-width: 17px; height: 17px;
      background: #ffd700; color: #000;
      border-radius: 10px; font-size: 0.6rem; font-weight: 800;
      display: none; align-items: center; justify-content: center; padding: 0 3px;
    }
    #jmt-w-cnt.show { display: flex; }

    /* ── Panel ── */
    #jmt-widget-panel {
      position: fixed;
      bottom: 134px; right: 16px;
      width: 300px; max-height: 68vh;
      background: #0d0d22;
      border: 1px solid rgba(0,212,255,0.15);
      border-radius: 18px;
      display: flex; flex-direction: column;
      overflow: hidden; z-index: 7999;
      box-shadow: 0 16px 48px rgba(0,0,0,0.6);
      transform: translateY(16px) scale(0.95);
      opacity: 0; pointer-events: none;
      transition: transform 0.25s cubic-bezier(.34,1.56,.64,1), opacity 0.2s;
    }
    #jmt-widget-panel.open {
      transform: translateY(0) scale(1);
      opacity: 1; pointer-events: all;
    }

    .jmt-w-hdr {
      display: flex; align-items: center; justify-content: space-between;
      padding: 13px 15px;
      border-bottom: 1px solid rgba(0,212,255,0.08);
      position: relative;
    }
    .jmt-w-hdr::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, #00d4ff, #ff4466);
      border-radius: 18px 18px 0 0;
    }
    .jmt-w-title {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 0.88rem; letter-spacing: 1.5px; color: #fff;
    }
    .jmt-w-time { font-size: 0.65rem; color: #5566aa; }

    /* Tabs */
    .jmt-w-tabs {
      display: flex;
      border-bottom: 1px solid rgba(0,212,255,0.06);
    }
    .jmt-w-tab {
      flex: 1; padding: 9px 6px;
      background: none; border: none;
      color: #5566aa; font-size: 0.73rem; font-weight: 700;
      cursor: pointer; transition: color 0.2s;
      font-family: 'Outfit', sans-serif;
      position: relative;
    }
    .jmt-w-tab.active { color: #00d4ff; }
    .jmt-w-tab.active::after {
      content: '';
      position: absolute; bottom: -1px; left: 10%; right: 10%;
      height: 2px; background: #00d4ff; border-radius: 1px;
    }

    /* Body scroll */
    .jmt-w-body {
      overflow-y: auto; flex: 1;
      scrollbar-width: thin;
      scrollbar-color: rgba(0,212,255,0.15) transparent;
    }

    /* Match card */
    .jmt-wm {
      padding: 11px 15px;
      border-bottom: 1px solid rgba(255,255,255,0.04);
      transition: background 0.15s;
      cursor: default;
    }
    .jmt-wm:hover { background: rgba(255,255,255,0.02); }
    .jmt-wm-league {
      font-size: 0.62rem; color: #5566aa;
      text-transform: uppercase; letter-spacing: 0.06em;
      margin-bottom: 6px;
      display: flex; align-items: center; gap: 4px;
    }
    .jmt-wm-teams {
      display: flex; align-items: center; gap: 6px;
    }
    .jmt-wmt {
      flex: 1; text-align: center;
      font-size: 0.73rem; font-weight: 700;
      color: rgba(255,255,255,0.85);
      overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    }
    .jmt-wms {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.2rem; letter-spacing: 2px;
      color: #fff; text-align: center;
      flex-shrink: 0; min-width: 54px;
    }
    .jmt-wms .sh { color: #00d4ff; }
    .jmt-wms .sa { color: #ff4466; }
    .jmt-wms .ss { color: rgba(255,255,255,0.2); }
    .jmt-wm-min {
      font-size: 0.62rem; font-weight: 700;
      padding: 2px 7px; border-radius: 5px;
      text-align: center; margin-top: 3px;
    }
    .jmt-wm-min.live { background:rgba(255,68,102,0.12); color:#ff4466; }
    .jmt-wm-min.ft   { background:rgba(100,110,150,0.12); color:#5566aa; }
    .jmt-wm-min.sched{ background:rgba(0,212,255,0.08);   color:#00d4ff; }

    /* Loader */
    .jmt-w-load {
      padding: 32px 20px; text-align: center; color: #5566aa;
    }
    .jmt-w-spin {
      width: 22px; height: 22px;
      border: 2px solid rgba(0,212,255,0.12);
      border-top-color: #00d4ff;
      border-radius: 50%;
      animation: jmtWspin 0.7s linear infinite;
      margin: 0 auto 8px;
    }
    @keyframes jmtWspin { to{transform:rotate(360deg)} }
    .jmt-w-empty { padding:36px 20px; text-align:center; color:#5566aa; font-size:0.82rem; }
    .jmt-w-empty .ico { font-size:2rem; display:block; margin-bottom:8px; }

    @media (max-width: 380px) {
      #jmt-widget-panel { right:10px; left:10px; width:auto; }
    }
  `;
  document.head.appendChild(css);

  // ─── HTML ─────────────────────────────────────────────────────────────
  document.body.insertAdjacentHTML('beforeend', `
    <div id="jmt-widget-btn" title="Mechi za Leo">
      ⚽
      <div id="jmt-w-dot"></div>
      <div id="jmt-w-cnt"></div>
    </div>
    <div id="jmt-widget-panel">
      <div class="jmt-w-hdr">
        <div class="jmt-w-title">⚽ MECHI ZA LEO</div>
        <div class="jmt-w-time" id="jmt-w-time">—</div>
      </div>
      <div class="jmt-w-tabs">
        <button class="jmt-w-tab active" data-tab="live">🔴 Live</button>
        <button class="jmt-w-tab"        data-tab="sched">📅 Ratiba</button>
      </div>
      <div class="jmt-w-body" id="jmt-w-body">
        <div class="jmt-w-load"><div class="jmt-w-spin"></div>Inapakia...</div>
      </div>
    </div>
  `);

  // ─── State ────────────────────────────────────────────────────────────
  let isOpen   = false;
  let activeTab = 'live';
  let data     = { live: [], sched: [] };
  let timer    = null;

  // ─── Render ───────────────────────────────────────────────────────────
  function render() {
    const body    = document.getElementById('jmt-w-body');
    const matches = activeTab === 'live' ? data.live : data.sched;

    if (!matches.length) {
      body.innerHTML = `<div class="jmt-w-empty">
        <span class="ico">${activeTab==='live' ? '😴' : '📭'}</span>
        ${activeTab==='live' ? 'Hakuna mechi live sasa.' : 'Hakuna mechi leo.'}
      </div>`;
      return;
    }

    body.innerHTML = matches.map(m => {
      const hasScore = m.home_score !== '' && m.home_score != null;
      const minCls   = m.status === 'live' ? 'live' : m.status === 'ft' ? 'ft' : 'sched';
      const minLbl   = m.status === 'live' ? (m.minute ? m.minute+"'" : 'LIVE')
                     : m.status === 'ft'   ? 'FT'
                     : (m.time || '--:--');
      return `
        <div class="jmt-wm">
          <div class="jmt-wm-league">${m.league || ''}</div>
          <div class="jmt-wm-teams">
            <div class="jmt-wmt">${esc(m.home)}</div>
            <div>
              <div class="jmt-wms">
                ${hasScore
                  ? `<span class="sh">${m.home_score}</span><span class="ss">:</span><span class="sa">${m.away_score}</span>`
                  : `<span style="color:rgba(255,255,255,0.2);font-size:0.9rem">vs</span>`
                }
              </div>
              <div class="jmt-wm-min ${minCls}">${minLbl}</div>
            </div>
            <div class="jmt-wmt">${esc(m.away)}</div>
          </div>
        </div>`;
    }).join('');
  }

  // ─── Fetch ────────────────────────────────────────────────────────────
  async function fetchData() {
    try {
      const res  = await fetch('match_widget_api.php');
      const json = await res.json();
      if (json.ok) {
        data.live  = json.live  || [];
        data.sched = json.sched || [];

        // Badge
        const cnt = data.live.length;
        document.getElementById('jmt-w-dot').classList.toggle('show', cnt > 0);
        const cntEl = document.getElementById('jmt-w-cnt');
        if (cnt > 0) {
          cntEl.classList.add('show');
          cntEl.textContent = cnt > 9 ? '9+' : cnt;
        } else {
          cntEl.classList.remove('show');
        }

        // Timestamp
        const now = new Date();
        document.getElementById('jmt-w-time').textContent =
          now.toLocaleTimeString('sw', { hour: '2-digit', minute: '2-digit' });

        if (isOpen) render();
      }
    } catch(e) {
      console.warn('[JMT Widget] Fetch error:', e);
    }
  }

  // ─── Toggle ───────────────────────────────────────────────────────────
  function toggle() {
    isOpen = !isOpen;
    document.getElementById('jmt-widget-panel').classList.toggle('open', isOpen);
    if (isOpen) render();
  }

  // ─── Events ───────────────────────────────────────────────────────────
  document.getElementById('jmt-widget-btn').addEventListener('click', toggle);

  document.querySelectorAll('.jmt-w-tab').forEach(btn => {
    btn.addEventListener('click', function () {
      activeTab = this.dataset.tab;
      document.querySelectorAll('.jmt-w-tab').forEach(t => t.classList.remove('active'));
      this.classList.add('active');
      render();
    });
  });

  document.addEventListener('click', function(e) {
    if (!e.target.closest('#jmt-widget-btn') && !e.target.closest('#jmt-widget-panel')) {
      if (isOpen) toggle();
    }
  });

  // ─── Poll ─────────────────────────────────────────────────────────────
  fetchData();
  timer = setInterval(fetchData, 60000); // kila dakika 1

  // ─── Helper ───────────────────────────────────────────────────────────
  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
  }

})();
