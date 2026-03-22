/**
 * _channel_popup.js — JAYNES MAX TV
 * Inabadilisha goChannel() kuonyesha popup kwanza ukibonyeza channel
 * Ina-override goChannel() iliyopo kwenye _channel_guard.js
 *
 * Ongeza script hii BAADA ya _channel_guard.js:
 *   <script src="_channel_guard.js"></script>
 *   <script src="_channel_popup.js"></script>
 */

(function () {
  // ─── CSS ─────────────────────────────────────────────────────────────
  const css = document.createElement('style');
  css.textContent = `
    /* ── Overlay ── */
    #jmt-ch-overlay {
      display: none;
      position: fixed; inset: 0;
      background: rgba(4,4,14,0.88);
      backdrop-filter: blur(14px);
      -webkit-backdrop-filter: blur(14px);
      z-index: 99990;
      align-items: flex-end;
      justify-content: center;
      padding: 0;
    }
    #jmt-ch-overlay.open {
      display: flex;
      animation: jmtOvFade 0.22s ease;
    }
    @keyframes jmtOvFade { from { opacity:0 } to { opacity:1 } }

    /* ── Card (bottom sheet style) ── */
    #jmt-ch-card {
      background: #0d0d22;
      border: 1px solid rgba(0,212,255,0.15);
      border-bottom: none;
      border-radius: 22px 22px 0 0;
      width: 100%;
      max-width: 480px;
      padding-bottom: env(safe-area-inset-bottom, 0);
      position: relative;
      overflow: hidden;
      animation: jmtSlideUp 0.3s cubic-bezier(.34,1.56,.64,1);
    }
    @keyframes jmtSlideUp {
      from { transform: translateY(100%) }
      to   { transform: translateY(0) }
    }

    /* top accent line */
    #jmt-ch-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0; height: 2px;
      background: linear-gradient(90deg, #00d4ff, #ff4466);
    }

    /* drag handle */
    .jmt-drag {
      width: 40px; height: 4px;
      background: rgba(255,255,255,0.12);
      border-radius: 2px;
      margin: 14px auto 0;
    }

    /* ── Channel header ── */
    .jmt-ch-head {
      display: flex; align-items: center; gap: 14px;
      padding: 16px 18px 14px;
    }
    .jmt-ch-logo {
      width: 56px; height: 56px;
      border-radius: 14px;
      background: rgba(255,255,255,0.05);
      border: 1px solid rgba(0,212,255,0.2);
      display: flex; align-items: center; justify-content: center;
      font-size: 1.8rem;
      flex-shrink: 0;
      overflow: hidden;
    }
    .jmt-ch-logo img {
      width: 100%; height: 100%;
      object-fit: contain;
    }
    .jmt-ch-meta { flex: 1; min-width: 0; }
    .jmt-ch-name {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.25rem;
      letter-spacing: 1px;
      color: #fff;
      line-height: 1.1;
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .jmt-ch-cat {
      font-size: 0.72rem;
      color: #5566aa;
      margin-top: 3px;
    }
    .jmt-live-pill {
      display: inline-flex; align-items: center; gap: 5px;
      background: rgba(255,68,102,0.12);
      border: 1px solid rgba(255,68,102,0.3);
      color: #ff4466;
      border-radius: 20px;
      padding: 4px 10px;
      font-size: 0.68rem;
      font-weight: 800;
      flex-shrink: 0;
      letter-spacing: 0.05em;
    }
    .jmt-live-dot {
      width: 6px; height: 6px;
      border-radius: 50%;
      background: #ff4466;
      animation: jmtPulse 1.3s infinite;
    }
    @keyframes jmtPulse {
      0%,100% { opacity:1; transform:scale(1) }
      50%      { opacity:0.35; transform:scale(0.7) }
    }

    /* ── Match info (live score) ── */
    .jmt-match-box {
      margin: 0 18px 14px;
      background: rgba(255,255,255,0.03);
      border: 1px solid rgba(0,212,255,0.1);
      border-radius: 12px;
      padding: 12px 14px;
    }
    .jmt-match-label {
      font-size: 0.68rem;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #5566aa;
      margin-bottom: 8px;
    }
    .jmt-match-teams {
      display: flex; align-items: center;
      gap: 8px;
    }
    .jmt-mt {
      flex: 1; text-align: center;
      font-size: 0.82rem; font-weight: 700;
      color: rgba(255,255,255,0.85);
    }
    .jmt-ms {
      font-family: 'Bebas Neue', sans-serif;
      font-size: 1.6rem;
      letter-spacing: 3px;
      color: #fff;
      flex-shrink: 0;
      min-width: 60px;
      text-align: center;
    }
    .jmt-ms .sh { color: #00d4ff; }
    .jmt-ms .sa { color: #ff4466; }
    .jmt-ms .ss { color: rgba(255,255,255,0.2); }
    .jmt-min-badge {
      display: inline-block;
      font-size: 0.65rem; font-weight: 700;
      background: rgba(255,68,102,0.12);
      color: #ff4466;
      padding: 2px 8px; border-radius: 6px;
      margin-top: 4px;
    }

    /* ── Divider ── */
    .jmt-div {
      height: 1px;
      background: rgba(255,255,255,0.06);
      margin: 0 18px 16px;
    }

    /* ── Body ── */
    .jmt-ch-body { padding: 0 18px 22px; }

    /* FREE badge */
    .jmt-free-badge {
      display: flex; align-items: center; gap: 8px;
      background: rgba(0,255,136,0.07);
      border: 1px solid rgba(0,255,136,0.2);
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 0.82rem;
      color: #00ff88;
      margin-bottom: 14px;
    }

    /* PREMIUM badge */
    .jmt-prem-badge {
      display: flex; align-items: center; gap: 8px;
      background: rgba(255,215,0,0.06);
      border: 1px solid rgba(255,215,0,0.18);
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 0.82rem;
      color: #ffd700;
      margin-bottom: 14px;
    }
    .jmt-prem-badge .perks {
      margin-top: 8px;
      display: flex; flex-direction: column; gap: 5px;
    }
    .jmt-prem-badge .perk {
      font-size: 0.75rem;
      color: rgba(255,255,255,0.6);
      display: flex; align-items: center; gap: 6px;
    }
    .jmt-prem-badge .perk i { color: #ffd700; font-size: 0.65rem; }

    /* Buttons */
    .jmt-btn-watch {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; padding: 15px;
      background: linear-gradient(135deg, #00d4ff, #0088bb);
      border: none; border-radius: 12px;
      color: #000; font-size: 0.95rem; font-weight: 800;
      font-family: 'Bebas Neue', sans-serif; letter-spacing: 1px;
      cursor: pointer; text-decoration: none;
      transition: all 0.2s;
      margin-bottom: 10px;
    }
    .jmt-btn-watch:hover { opacity: 0.9; transform: translateY(-1px); }
    .jmt-btn-watch:active { transform: scale(0.98); }

    .jmt-btn-pay {
      display: flex; align-items: center; justify-content: center; gap: 8px;
      width: 100%; padding: 15px;
      background: linear-gradient(135deg, #ffd700, #ff8c00);
      border: none; border-radius: 12px;
      color: #000; font-size: 0.95rem; font-weight: 800;
      font-family: 'Bebas Neue', sans-serif; letter-spacing: 1px;
      cursor: pointer; text-decoration: none;
      transition: all 0.2s;
      margin-bottom: 10px;
      box-shadow: 0 6px 22px rgba(255,215,0,0.25);
    }
    .jmt-btn-pay:hover { opacity: 0.9; transform: translateY(-1px); }
    .jmt-btn-pay:active { transform: scale(0.98); }

    .jmt-btn-close {
      display: block; width: 100%;
      padding: 11px;
      background: rgba(255,255,255,0.04);
      border: 1px solid rgba(255,255,255,0.08);
      border-radius: 12px;
      color: #5566aa; font-size: 0.85rem; font-weight: 600;
      font-family: 'Outfit', sans-serif;
      cursor: pointer;
      transition: all 0.2s;
    }
    .jmt-btn-close:hover { background: rgba(255,255,255,0.09); color: #fff; }

    /* close X */
    .jmt-x {
      position: absolute; top: 14px; right: 14px;
      width: 28px; height: 28px;
      background: rgba(255,255,255,0.06);
      border: none; border-radius: 50%;
      color: #5566aa; font-size: 0.9rem;
      cursor: pointer;
      display: flex; align-items: center; justify-content: center;
      transition: background 0.2s;
    }
    .jmt-x:hover { background: rgba(255,255,255,0.12); color: #fff; }

    .jmt-login-note {
      text-align: center;
      font-size: 0.72rem;
      color: #5566aa;
      margin-top: 10px;
    }
    .jmt-login-note a { color: #00d4ff; text-decoration: none; }
  `;
  document.head.appendChild(css);

  // ─── HTML ─────────────────────────────────────────────────────────────
  const overlay = document.createElement('div');
  overlay.id = 'jmt-ch-overlay';
  overlay.innerHTML = `
    <div id="jmt-ch-card">
      <button class="jmt-x" id="jmt-ch-x">✕</button>
      <div class="jmt-drag"></div>
      <div class="jmt-ch-head">
        <div class="jmt-ch-logo" id="jmt-logo">📺</div>
        <div class="jmt-ch-meta">
          <div class="jmt-ch-name" id="jmt-cname">Channel</div>
          <div class="jmt-ch-cat"  id="jmt-ccat">Live TV</div>
        </div>
        <div class="jmt-live-pill"><div class="jmt-live-dot"></div> LIVE</div>
      </div>
      <div id="jmt-match-area"></div>
      <div class="jmt-div"></div>
      <div class="jmt-ch-body" id="jmt-ch-body"></div>
    </div>
  `;
  document.body.appendChild(overlay);

  // ─── State ────────────────────────────────────────────────────────────
  let _playerUrl = '';
  let _chTitle   = '';

  // ─── Close ────────────────────────────────────────────────────────────
  function close() {
    overlay.classList.remove('open');
  }

  document.getElementById('jmt-ch-x').addEventListener('click', close);
  overlay.addEventListener('click', e => { if (e.target === overlay) close(); });
  document.addEventListener('keydown', e => { if (e.key === 'Escape') close(); });

  // touch swipe down to close
  let touchStart = 0;
  document.getElementById('jmt-ch-card').addEventListener('touchstart', e => {
    touchStart = e.touches[0].clientY;
  }, { passive: true });
  document.getElementById('jmt-ch-card').addEventListener('touchmove', e => {
    if (e.touches[0].clientY - touchStart > 70) close();
  }, { passive: true });

  // ─── Build popup ──────────────────────────────────────────────────────
  function buildPopup(chData, playerUrl) {
    _playerUrl = playerUrl;
    _chTitle   = chData.title || chData.channel_name || '';

    // Header
    document.getElementById('jmt-cname').textContent = chData.channel_name || chData.title || 'Channel';
    document.getElementById('jmt-ccat').textContent  = chData.category || 'Live TV';

    const logoEl = document.getElementById('jmt-logo');
    const logo   = chData.home_logo || chData.logo || '';
    if (logo && logo.startsWith('http')) {
      logoEl.innerHTML = `<img src="${logo}" alt="" onerror="this.parentElement.textContent='📺'">`;
    } else {
      logoEl.textContent = '📺';
    }

    // Match score area
    const matchArea = document.getElementById('jmt-match-area');
    if (chData.home_score !== undefined && chData.home_score !== '') {
      const min = chData.minute ? `${chData.minute}'` : 'LIVE';
      matchArea.innerHTML = `
        <div class="jmt-match-box">
          <div class="jmt-match-label">🔴 Mechi Inayoendelea</div>
          <div class="jmt-match-teams">
            <div class="jmt-mt">${esc(chData.home || chData.home_team || '')}</div>
            <div style="text-align:center">
              <div class="jmt-ms">
                <span class="sh">${chData.home_score}</span>
                <span class="ss"> : </span>
                <span class="sa">${chData.away_score}</span>
              </div>
              <div class="jmt-min-badge">${min}</div>
            </div>
            <div class="jmt-mt">${esc(chData.away || chData.away_team || '')}</div>
          </div>
        </div>`;
    } else if (chData.home) {
      matchArea.innerHTML = `
        <div class="jmt-match-box">
          <div class="jmt-match-label">📅 Inayokuja</div>
          <div class="jmt-match-teams">
            <div class="jmt-mt">${esc(chData.home)}</div>
            <div class="jmt-ms" style="font-size:1rem;letter-spacing:2px;color:#5566aa">VS</div>
            <div class="jmt-mt">${esc(chData.away || '')}</div>
          </div>
        </div>`;
    } else {
      matchArea.innerHTML = '';
    }

    // Body
    const body  = document.getElementById('jmt-ch-body');
    const isFree = isFreeChannel(_chTitle);
    const access = hasAccess();

    if (isFree) {
      // ── BURE ──
      body.innerHTML = `
        <div class="jmt-free-badge">✅ Channel hii ni ya bure — tazama bila malipo</div>
        <a href="${playerUrl}" class="jmt-btn-watch"><i class="fa fa-play"></i> TAZAMA SASA</a>
        <button class="jmt-btn-close" id="jmt-dismiss">Funga</button>`;
    } else if (access) {
      // ── PREMIUM ANA ACCESS ──
      body.innerHTML = `
        <div class="jmt-free-badge">⭐ Umesajiliwa — Premium. Furahia!</div>
        <a href="${playerUrl}" class="jmt-btn-watch"><i class="fa fa-play"></i> TAZAMA SASA</a>
        <button class="jmt-btn-close" id="jmt-dismiss">Funga</button>`;
    } else {
      // ── PREMIUM HANA ACCESS ──
      const isLoggedIn = !!localStorage.getItem('jaynesUid');
      body.innerHTML = `
        <div class="jmt-prem-badge">
          <div>
            <div>👑 <strong>Channel ya Premium</strong></div>
            <div class="perks" style="margin-top:8px">
              <div class="perk"><i class="fa fa-check-circle"></i>Channels zote za Azam Sports 1–4</div>
              <div class="perk"><i class="fa fa-check-circle"></i>Mechi za NBC Premier League LIVE</div>
              <div class="perk"><i class="fa fa-check-circle"></i>HD na FHD Quality</div>
            </div>
          </div>
        </div>
        <a href="malipo.php" class="jmt-btn-pay"><i class="fa fa-crown"></i> JIUNGE PREMIUM — TSh 1,000/wiki</a>
        <button class="jmt-btn-close" id="jmt-dismiss">← Rudi</button>
        <div class="jmt-login-note">
          Tayari umelipa? <a href="malipo.php">Angalia hali →</a> &nbsp;|&nbsp;
          WhatsApp: <a href="https://wa.me/255616393956">0616 393 956</a>
        </div>`;
    }

    document.getElementById('jmt-dismiss')?.addEventListener('click', close);
    overlay.classList.add('open');
  }

  // ─── Override goChannel() ─────────────────────────────────────────────
  // Hii inabadilisha goChannel() ya _channel_guard.js
  window.goChannel = function(title, playerUrl) {
    buildPopup({ title, channel_name: title }, playerUrl);
  };

  // ─── Extended version with full channel data ───────────────────────────
  // Tumia hii badala ya goChannel kama una data zaidi
  window.goChannelFull = function(chData, playerUrl) {
    buildPopup(chData, playerUrl);
  };

  // ─── Helper ───────────────────────────────────────────────────────────
  function esc(s) {
    const d = document.createElement('div');
    d.textContent = s || '';
    return d.innerHTML;
  }

})();
