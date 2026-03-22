/**
 * JAYNES MAX TV — _auth_guard.js v4
 * - Angalia login
 * - Trial ya dakika 30 — countdown inayoonekana
 * - Auto-check kila dakika 1 — paywall inawaka mara moja trial inapoisha
 * - Onyo dakika 5 kabla haijafika
 */

const SB_URL = 'https://dablnrggyfcddmdeiqxi.supabase.co';
const SB_KEY = 'sb_publishable_d8mzJ3iulCU7YdlV_lrdQw_32pOzDXc';

// Pages zinazoruhusiwa bila subscription
const FREE_PAGES = ['login.html', 'index.html', 'malipo.php', 'account.php', 'home.html', 'home.php', 'live.php', 'schedule.php', 'search.php', 'face.html', 'face.php', 'local.php', 'local2.php', 'categories.php', 'admin', 'send_notification'];


/* ═══════════════════════════════════════════════════════════════
   MAINTENANCE MODE
   Inaangalia settings table kila dakika 5
   Kama maintenance=true → onyesha ukurasa wa matengenezo
═══════════════════════════════════════════════════════════════ */

// Cache ya maintenance status — dakika 5
let _maintenanceCache = { value: false, ts: 0 };
const _MAINTENANCE_TTL = 5 * 60 * 1000;

async function checkMaintenance() {
  // Admin hawezi kuzuiwa na maintenance
  const adminEmail = localStorage.getItem('adm_e') || '';
  const myEmail    = localStorage.getItem('jaynesEmail') || '';
  const ADMINS_GUARD = ['swajayfour@gmail.com', 'jaynestvmax@gmail.com'];
  if (adminEmail && ADMINS_GUARD.includes(adminEmail.toLowerCase())) return;
  if (myEmail    && ADMINS_GUARD.includes(myEmail.toLowerCase()))    return;

  // Cache bado ni fresh
  if (Date.now() - _maintenanceCache.ts < _MAINTENANCE_TTL) {
    if (_maintenanceCache.value) showMaintenanceScreen();
    return;
  }

  try {
    // Tumia maintenance.php?action=status (PHP proxy — reliable zaidi)
    const r = await fetch('maintenance.php?action=status', { cache: 'no-store' });
    if (!r.ok) return;
    const d = await r.json();
    const isOn = d.maintenance === true;
    _maintenanceCache = { value: isOn, ts: Date.now() };
    if (isOn) showMaintenanceScreen();
  } catch { /* Network error — ruhusu kuendelea */ }
}

function showMaintenanceScreen() {
  if (document.getElementById('__maintenance')) return; // Ipo tayari

  // Ficha content yote
  document.body.style.overflow = 'hidden';

  const overlay = document.createElement('div');
  overlay.id = '__maintenance';
  overlay.style.cssText = `
    position:fixed;inset:0;z-index:99999;
    background:#04040f;
    display:flex;align-items:center;justify-content:center;
    padding:24px;font-family:'Outfit',sans-serif;
    animation:mFadeIn 0.5s ease;
  `;

  overlay.innerHTML = `
    <style>
      @keyframes mFadeIn{from{opacity:0}to{opacity:1}}
      @keyframes mPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.05)}}
      @keyframes mRotate{to{transform:rotate(360deg)}}
      @keyframes mBar{0%{width:0%}100%{width:100%}}
      .mw{max-width:380px;width:100%;text-align:center;}
      .m-gear{
        width:100px;height:100px;border-radius:50%;
        background:linear-gradient(135deg,rgba(0,212,255,0.15),rgba(255,68,102,0.1));
        border:2px solid rgba(0,212,255,0.2);
        display:flex;align-items:center;justify-content:center;
        margin:0 auto 24px;
        animation:mPulse 2s ease-in-out infinite;
      }
      .m-gear i{font-size:44px;color:#00d4ff;animation:mRotate 4s linear infinite;}
      .m-title{
        font-family:'Bebas Neue',sans-serif;font-size:32px;letter-spacing:4px;
        background:linear-gradient(135deg,#00d4ff,#ff4466);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
        margin-bottom:8px;
      }
      .m-sub{font-size:14px;color:#5566aa;letter-spacing:2px;text-transform:uppercase;margin-bottom:28px;}
      .m-card{
        background:rgba(13,13,34,0.8);border:1px solid rgba(0,212,255,0.12);
        border-radius:20px;padding:24px 20px;margin-bottom:20px;
      }
      .m-msg{font-size:16px;color:rgba(255,255,255,0.85);line-height:1.7;margin-bottom:20px;}
      .m-progress{height:3px;background:rgba(255,255,255,0.06);border-radius:2px;overflow:hidden;margin-bottom:20px;}
      .m-prog-fill{height:100%;background:linear-gradient(90deg,#00d4ff,#ff4466);border-radius:2px;animation:mBar 3s ease-in-out infinite alternate;}
      .m-info{display:flex;flex-direction:column;gap:10px;}
      .m-row{display:flex;align-items:center;gap:10px;padding:10px 14px;background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.06);border-radius:10px;font-size:12px;color:#5566aa;}
      .m-row i{color:#00d4ff;font-size:14px;flex-shrink:0;}
      .m-row a{color:#00d4ff;font-weight:700;}
      .m-footer{font-size:11px;color:#2a3a4a;margin-top:16px;line-height:1.8;}
    </style>

    <div class="mw">
      <div class="m-gear">
        <i class="fa fa-gear"></i>
      </div>
      <div class="m-title">MATENGENEZO</div>
      <div class="m-sub">MAINTENANCE MODE</div>

      <div class="m-card">
        <p class="m-msg">
          🔧 App imefungwa kwa muda kwa ajili ya matengenezo.<br>
          Tutarudi hivi karibuni na maboresho mapya!
        </p>
        <div class="m-progress"><div class="m-prog-fill"></div></div>
        <div class="m-info">
          <div class="m-row">
            <i class="fa fa-clock"></i>
            <span>Inatarajiwa kumaliza hivi karibuni</span>
          </div>
          <div class="m-row">
            <i class="fa fa-whatsapp" style="color:#25d366"></i>
            <span>Maswali? <a href="https://wa.me/255616393956">WhatsApp 0616 393 956</a></span>
          </div>
          <div class="m-row">
            <i class="fa fa-envelope"></i>
            <span><a href="mailto:jaynestvmax@gmail.com">jaynestvmax@gmail.com</a></span>
          </div>
        </div>
      </div>

      <div class="m-footer">
        JAYNES MAX TV · Powered by AllSportsAPI<br>
        <span id="__m_time" style="color:#334455"></span>
      </div>
    </div>
  `;

  document.body.appendChild(overlay);

  // Onyesha saa
  const clockEl = overlay.querySelector('#__m_time');
  const tick = () => { if (clockEl) clockEl.textContent = new Date().toLocaleTimeString('sw-TZ'); };
  tick();
  setInterval(tick, 1000);

  // Re-check kila dakika 2 — kama maintenance imezimwa, reload
  setInterval(async () => {
    _maintenanceCache.ts = 0; // Force refresh
    const r = await fetch('maintenance.php?action=status', { cache: 'no-store' }).catch(() => null);
    if (!r || !r.ok) return;
    const d = await r.json().catch(() => ({}));
    const isOn = d.maintenance === true;
    if (!isOn) {
      // Maintenance imezimwa — reload page
      overlay.style.animation = 'mFadeIn 0.5s ease reverse';
      setTimeout(() => location.reload(), 500);
    }
  }, 2 * 60 * 1000);
}

// ── INIT ────────────────────────────────────────────────────────
(async function authInit() {
  // ── Angalia maintenance mode kwanza ──────────────────────────
  await checkMaintenance();

  const email = localStorage.getItem('jaynesEmail');
  const token = localStorage.getItem('jaynesToken');

  if (!email || !token) {
    const next = encodeURIComponent(location.pathname + location.search);
    location.replace('login.html?next=' + next);
    return;
  }

  // Token check kutoka server — kila dakika 5
  const lastCheck = parseInt(localStorage.getItem('jaynesLastCheck') || '0');
  const now = Date.now();

  // Angalia old user rule mara moja kutoka cache (bila kusubiri server)
  const cachedCreatedAt = localStorage.getItem('jaynesCreatedAt') || '';
  if (cachedCreatedAt) {
    applyOldUserRule(cachedCreatedAt, localStorage.getItem('jaynesTrialEnd') || '');
  }

  if (now - lastCheck >= 5 * 60 * 1000) {
    fetch('auth.php?action=me', { headers: { 'Authorization': 'Bearer ' + token } })
      .then(r => r.json())
      .then(d => {
        if (!d.success) { clearSession(); location.replace('login.html'); return; }
        localStorage.setItem('jaynesLastCheck', String(now));
        if (d.user) {
          if (d.user.name)       localStorage.setItem('jaynesName',      d.user.name);
          if (d.user.plan)       localStorage.setItem('jaynesPlan',      d.user.plan);
          if (d.user.trial_end)  localStorage.setItem('jaynesTrialEnd',  d.user.trial_end);
          if (d.user.sub_end)    localStorage.setItem('jaynesSubEnd',    d.user.sub_end);
          if (d.user.created_at) localStorage.setItem('jaynesCreatedAt', d.user.created_at);
          applyOldUserRule(d.user.created_at, d.user.trial_end);
        }
        checkAndSchedule();
      })
      .catch(() => {
        localStorage.setItem('jaynesLastCheck', String(now));
        checkAndSchedule();
      });
  } else {
    checkAndSchedule();
  }
})();

// ── CHECK + SCHEDULE auto-repeat ────────────────────────────────
let _paywallTimer = null;

/* ═══════════════════════════════════════════════════════════════
   OLD USER RULE
   Watumiaji waliojisajili KABLA ya leo hawana trial —
   trial_end inawekwa kuwa wakati uliopita ili malipo yadaiwe mara moja.
═══════════════════════════════════════════════════════════════ */
function isRegisteredToday(createdAt) {
  if (!createdAt) return true; // Kama haijulikani, wape trial
  try {
    const regDate = new Date(createdAt);
    const today   = new Date();
    return (
      regDate.getFullYear() === today.getFullYear() &&
      regDate.getMonth()    === today.getMonth()    &&
      regDate.getDate()     === today.getDate()
    );
  } catch(e) { return true; }
}

function applyOldUserRule(createdAt, trialEnd) {
  // Kama user amesajiliwa leo — wape trial ya kawaida dk 30
  if (isRegisteredToday(createdAt)) return;

  // User wa zamani — angalia kama ana premium halisi
  const plan   = localStorage.getItem('jaynesPlan')   || 'free';
  const subEnd = localStorage.getItem('jaynesSubEnd') || '';
  if (plan === 'premium' && subEnd && new Date(subEnd) > new Date()) return; // Premium valid

  // User wa zamani bila premium — futa trial yoyote iliyobaki
  // Weka trial_end = wakati uliopita → paywall itawaka mara moja
  const expiredTime = new Date(Date.now() - 1000).toISOString();
  localStorage.setItem('jaynesTrialEnd', expiredTime);
  localStorage.setItem('jaynesPlan',    plan === 'premium' ? 'free' : plan);
}

// ── REFRESH PROFILE — inaitwa wakati premium inawashwa ──────────
// Inapigia auth.php?action=me na kusasisha localStorage mara moja
async function refreshProfile() {
  const token = localStorage.getItem('jaynesToken') || localStorage.getItem('sb_token') || '';
  if (!token) return false;
  try {
    // Futa lastCheck ili ifanye fresh fetch
    localStorage.removeItem('jaynesLastCheck');
    const res = await fetch('auth.php?action=me', {
      headers: { 'Authorization': 'Bearer ' + token },
      cache: 'no-store'
    });
    if (!res.ok) return false;
    const d = await res.json();
    if (!d.success || !d.user) return false;
    // Sasisha YOTE kutoka server
    localStorage.setItem('jaynesPlan',      d.user.plan      || 'free');
    localStorage.setItem('jaynesTrialEnd',  d.user.trial_end || '');
    localStorage.setItem('jaynesSubEnd',    d.user.sub_end   || '');
    if (d.user.name) localStorage.setItem('jaynesName', d.user.name);
    localStorage.setItem('jaynesLastCheck', String(Date.now()));
    console.log('[Auth] Profile refreshed → plan:', d.user.plan, '| sub_end:', d.user.sub_end);
    return true;
  } catch(e) { return false; }
}
window.refreshProfile = refreshProfile;

function checkAndSchedule() {
  checkPaywall();

  // Acha timer wa zamani
  if (_paywallTimer) clearInterval(_paywallTimer);

  // Angalia kila sekunde 30 — ili paywall ionekane haraka inapowaka
  _paywallTimer = setInterval(() => {
    // Kama paywall tayari ipo, acha
    if (document.getElementById('__paywall')) {
      clearInterval(_paywallTimer);
      return;
    }
    checkPaywall();
  }, 60 * 1000); // Kila dakika 1
}

/* ═══════════════════════════════════════════════════════════════
   PAYWALL CHECK
═══════════════════════════════════════════════════════════════ */
function checkPaywall() {
  const page = location.pathname.split('/').pop() || 'index.html';

  // ── Kurasa zinazoruhusiwa ZOTE bila subscription ──
  // Paywall inakaa kwenye goChannel() tu — si hapa
  const BLOCK_PAGES = ['player.html', 'player.php'];
  if (!BLOCK_PAGES.some(p => page.includes(p))) {
    // Sio player — angalia tu kama kuna onyo la dakika 5 zilizobaki
    const trialEnd = localStorage.getItem('jaynesTrialEnd') || '';
    const plan     = localStorage.getItem('jaynesPlan')     || 'free';
    const subEnd   = localStorage.getItem('jaynesSubEnd')   || '';
    const now      = new Date();
    if (plan === 'premium' && subEnd && new Date(subEnd) > now) { hideWarning(); return; }
    if (trialEnd && new Date(trialEnd) > now) {
      const msLeft  = new Date(trialEnd) - now;
      const minLeft = Math.ceil(msLeft / 60000);
      if (minLeft <= 5) showTrialWarning(minLeft, msLeft);
      else hideWarning();
    } else {
      hideWarning(); // Usionyeshe modal — acha mtumiaji atembee huru
    }
    return;
  }

  // ── Player pages — hapa ndipo subscription inadaiwa ──
  const plan     = localStorage.getItem('jaynesPlan')     || 'free';
  const trialEnd = localStorage.getItem('jaynesTrialEnd') || '';
  const subEnd   = localStorage.getItem('jaynesSubEnd')   || '';
  const now      = new Date();

  if (plan === 'premium' && subEnd && new Date(subEnd) > now) { hideWarning(); return; }
  if (trialEnd && new Date(trialEnd) > now) {
    const msLeft  = new Date(trialEnd) - now;
    const minLeft = Math.ceil(msLeft / 60000);
    if (minLeft <= 5) showTrialWarning(minLeft, msLeft);
    else hideWarning();
    return;
  }

  if (!trialEnd && !subEnd && plan === 'trial') {
    if (!window.__paywallRetried) {
      window.__paywallRetried = true;
      setTimeout(checkPaywall, 2000);
      return;
    }
  }

  hideWarning();
  const reason = (trialEnd && new Date(trialEnd) <= now) ? 'trial_ended'
    : (plan === 'premium' && subEnd && new Date(subEnd) <= now) ? 'sub_ended'
    : 'no_access';
  showPaywallModal(reason);
}

/* ═══════════════════════════════════════════════════════════════
   PAYWALL MODAL — inaonekana juu ya channel ukurasa
   Mtumiaji anabonyeza channel → modal inawaka, si redirect
═══════════════════════════════════════════════════════════════ */
function showPaywallModal(reason) {
  if (document.getElementById('__paywall')) return;

  const MSGS = {
    trial_ended: {
      icon: '⏰',
      title: 'DAKIKA 30 ZIMEKWISHA',
      msg: 'Majaribio yako ya bure yamekwisha.<br>Jiunge Premium ili uendelee kutazama.',
      color: '#ff8c00',
    },
    sub_ended: {
      icon: '👑',
      title: 'SUBSCRIPTION IMEKWISHA',
      msg: 'Subscription yako imekwisha.<br>Renewi sasa usipite mechi yoyote.',
      color: '#ffd700',
    },
    no_access: {
      icon: '🔒',
      title: 'JIUNGE PREMIUM',
      msg: 'Jiunge na JAYNES MAX TV Premium.<br>Angalia channels zote, mechi live, na zaidi.',
      color: '#00d4ff',
    },
  };

  const info = MSGS[reason] || MSGS.no_access;

  const overlay = document.createElement('div');
  overlay.id = '__paywall';
  overlay.style.cssText = `
    position:fixed;inset:0;z-index:99995;
    background:rgba(4,4,14,0.96);
    backdrop-filter:blur(20px);
    display:flex;align-items:center;justify-content:center;
    padding:20px;font-family:'Outfit',sans-serif;
    animation:pwFadeIn 0.35s ease;
  `;

  overlay.innerHTML = `
    <style>
      @keyframes pwFadeIn{from{opacity:0;transform:scale(0.95)}to{opacity:1;transform:scale(1)}}
      @keyframes pwBounce{0%,100%{transform:translateY(0)}40%{transform:translateY(-8px)}70%{transform:translateY(-3px)}}
      .pw-box{
        max-width:340px;width:100%;
        background:linear-gradient(160deg,#0c0c22,#08081a);
        border:1px solid rgba(255,255,255,0.08);
        border-radius:22px;padding:30px 22px;text-align:center;
        box-shadow:0 32px 80px rgba(0,0,0,0.9);
      }
      .pw-icon{font-size:56px;display:block;margin-bottom:12px;animation:pwBounce 0.7s ease 0.1s}
      .pw-title{
        font-family:'Bebas Neue',sans-serif;font-size:24px;letter-spacing:3px;
        color:${info.color};margin-bottom:10px;
      }
      .pw-msg{font-size:14px;color:#8899bb;line-height:1.7;margin-bottom:22px}
      .pw-pkgs{display:flex;flex-direction:column;gap:9px;margin-bottom:22px;text-align:left}
      .pw-pkg{
        display:flex;align-items:center;justify-content:space-between;
        padding:12px 14px;
        background:rgba(255,255,255,0.04);border:1px solid rgba(255,255,255,0.07);
        border-radius:12px;cursor:pointer;transition:all 0.2s;
      }
      .pw-pkg:hover{background:rgba(0,212,255,0.07);border-color:rgba(0,212,255,0.25);}
      .pw-pkg.selected{background:rgba(0,212,255,0.1);border-color:#00d4ff;}
      .pw-pkg-name{display:flex;align-items:center;gap:8px;font-size:13px;font-weight:700}
      .pw-pkg-pop{background:#ffd700;color:#000;font-size:9px;font-weight:700;padding:2px 7px;border-radius:20px}
      .pw-pkg-price{font-family:'Bebas Neue',sans-serif;font-size:20px;color:#00d4ff}
      .pw-btn{
        width:100%;padding:15px;border:none;border-radius:14px;
        background:linear-gradient(135deg,#00d4ff,#0088cc);
        color:#000;font-family:'Bebas Neue',sans-serif;font-size:18px;
        letter-spacing:1px;cursor:pointer;
        box-shadow:0 8px 28px rgba(0,212,255,0.3);
        transition:all 0.2s;margin-bottom:10px;
        display:flex;align-items:center;justify-content:center;gap:8px;
      }
      .pw-btn:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(0,212,255,0.45)}
      .pw-close{
        width:100%;padding:12px;background:rgba(255,255,255,0.05);
        border:1px solid rgba(255,255,255,0.08);border-radius:12px;
        color:#5566aa;font-size:13px;font-weight:600;
        font-family:'Outfit',sans-serif;cursor:pointer;transition:all 0.2s;
      }
      .pw-close:hover{background:rgba(255,255,255,0.1);color:#fff}
      .pw-note{font-size:11px;color:#334455;margin-top:12px;line-height:1.8}
      .pw-note a{color:#0099bb;text-decoration:none}
      .pw-wa{
        display:flex;align-items:center;justify-content:center;gap:8px;
        padding:11px 14px;margin-top:8px;
        background:rgba(37,211,102,0.08);border:1px solid rgba(37,211,102,0.2);
        border-radius:12px;text-decoration:none;
        color:#25d366;font-size:13px;font-weight:700;
      }
    </style>

    <div class="pw-box">
      <span class="pw-icon">${info.icon}</span>
      <div class="pw-title">${info.title}</div>
      <p class="pw-msg">${info.msg}</p>

      <div class="pw-pkgs" id="__pwPkgs">
        <div class="pw-pkg selected" data-pkg="wiki1" data-days="7" data-amt="1000" onclick="pwSelect(this)">
          <div class="pw-pkg-name">📅 Wiki 1</div>
          <div class="pw-pkg-price">TSh 1,000</div>
        </div>
        <div class="pw-pkg" data-pkg="mwezi1" data-days="30" data-amt="3000" onclick="pwSelect(this)">
          <div class="pw-pkg-name">📅 Mwezi 1 <span class="pw-pkg-pop">★ MAARUFU</span></div>
          <div class="pw-pkg-price">TSh 3,000</div>
        </div>
        <div class="pw-pkg" data-pkg="miezi3" data-days="90" data-amt="8000" onclick="pwSelect(this)">
          <div class="pw-pkg-name">📅 Miezi 3</div>
          <div class="pw-pkg-price">TSh 8,000</div>
        </div>
        <div class="pw-pkg" data-pkg="miezi6" data-days="180" data-amt="15000" onclick="pwSelect(this)">
          <div class="pw-pkg-name">📅 Miezi 6</div>
          <div class="pw-pkg-price">TSh 15,000</div>
        </div>
        <div class="pw-pkg" data-pkg="mwaka1" data-days="365" data-amt="25000" onclick="pwSelect(this)">
          <div class="pw-pkg-name">🏆 Mwaka 1</div>
          <div class="pw-pkg-price">TSh 25,000</div>
        </div>
      </div>

      <button class="pw-btn" onclick="pwPay()">
        <i class="fa fa-credit-card"></i> LIPIA SASA
      </button>

      <button class="pw-close" onclick="document.getElementById('__paywall').remove()">
        ← Rudi nyuma
      </button>

      <p class="pw-note">
        au wasiliana moja kwa moja
      </p>
      <a href="https://wa.me/255616393956" target="_blank" class="pw-wa">
        💬 WhatsApp — 0616 393 956
      </a>
      <p class="pw-note">
        Airtel Money: <strong style="color:#fff">0695 753 176</strong> (JUMANNE HASSAN NDANGE)<br>
        Email: <a href="mailto:jaynestvmax@gmail.com">jaynestvmax@gmail.com</a><br>
        Tayari umelipa? <a href="malipo.php">Angalia hali ya malipo →</a>
      </p>
    </div>
  `;

  document.body.appendChild(overlay);
}

// Chagua package
function pwSelect(el) {
  document.querySelectorAll('.pw-pkg').forEach(p => p.classList.remove('selected'));
  el.classList.add('selected');
}

// Nenda malipo.php na package iliyochaguliwa
function pwPay() {
  const sel = document.querySelector('.pw-pkg.selected');
  const pkg  = sel ? sel.dataset.pkg  : 'wiki1';
  const days = sel ? sel.dataset.days : '7';
  const amt  = sel ? sel.dataset.amt  : '1000';
  location.href = 'malipo.php?pkg=' + pkg + '&days=' + days + '&amt=' + amt;
}

/* ═══════════════════════════════════════════════════════════════
   TRIAL WARNING — onyo dakika 5 kabla haijafika
═══════════════════════════════════════════════════════════════ */
let _warnTimer = null;

function showTrialWarning(minLeft, msLeft) {
  // Usirudie kama ipo tayari
  if (document.getElementById('__trial_warn')) {
    updateWarningTime(minLeft, msLeft);
    return;
  }

  const bar = document.createElement('div');
  bar.id = '__trial_warn';
  bar.style.cssText = `
    position:fixed;top:0;left:0;right:0;z-index:99998;
    background:linear-gradient(135deg,rgba(255,140,0,0.96),rgba(255,68,0,0.96));
    backdrop-filter:blur(8px);
    padding:10px 16px;
    display:flex;align-items:center;justify-content:space-between;gap:10px;
    font-family:'Outfit',sans-serif;font-size:13px;color:#fff;
    box-shadow:0 4px 20px rgba(255,100,0,0.4);
    animation:warnSlide 0.4s ease;
  `;

  const secsLeft = Math.ceil(msLeft / 1000);

  bar.innerHTML = `
    <style>
      @keyframes warnSlide{from{transform:translateY(-100%)}to{transform:translateY(0)}}
      @keyframes warnPulse{0%,100%{opacity:1}50%{opacity:0.6}}
      #__warn_ico{animation:warnPulse 1s infinite;font-size:18px}
      #__warn_time{font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:2px;color:#fff;white-space:nowrap}
      .__warn_btn{padding:6px 14px;background:#fff;border:none;border-radius:8px;color:#ff4400;font-weight:700;font-size:12px;cursor:pointer;font-family:'Outfit',sans-serif;white-space:nowrap;flex-shrink:0}
    </style>
    <span id="__warn_ico">⏳</span>
    <div style="flex:1;min-width:0">
      <div style="font-weight:700;font-size:12px;letter-spacing:0.5px">TRIAL INAISHA HIVI KARIBUNI</div>
      <div style="font-size:11px;opacity:0.85">Lipia sasa ili usipoteze channels zako</div>
    </div>
    <span id="__warn_time">${formatCountdown(secsLeft)}</span>
    <button class="__warn_btn" onclick="location.href='malipo.php'">LIPIA 💳</button>
  `;

  document.body.prepend(bar);

  // Countdown inayosogea kila sekunde
  _warnTimer = setInterval(() => {
    const te = localStorage.getItem('jaynesTrialEnd');
    if (!te) { clearInterval(_warnTimer); hideWarning(); return; }
    const remaining = new Date(te) - new Date();
    if (remaining <= 0) {
      clearInterval(_warnTimer);
      hideWarning();
      showPaywall('trial', te);
      return;
    }
    updateWarningTime(Math.ceil(remaining/60000), remaining);
  }, 1000);
}

function updateWarningTime(minLeft, msLeft) {
  const el = document.getElementById('__warn_time');
  if (el) el.textContent = formatCountdown(Math.ceil(msLeft / 1000));
}

function hideWarning() {
  const el = document.getElementById('__trial_warn');
  if (el) el.remove();
  if (_warnTimer) { clearInterval(_warnTimer); _warnTimer = null; }
}

function formatCountdown(totalSecs) {
  if (totalSecs <= 0) return '00:00';
  const m = Math.floor(totalSecs / 60);
  const s = totalSecs % 60;
  return `${String(m).padStart(2,'0')}:${String(s).padStart(2,'0')}`;
}

/* ═══════════════════════════════════════════════════════════════
   PAYWALL POPUP — haiwezi kufungwa
═══════════════════════════════════════════════════════════════ */
function showPaywall(plan, trialEnd) {
  if (document.getElementById('__paywall')) return;

  // Tambua sababu + icon
  let reason, icon, title;
  if (trialEnd && new Date(trialEnd) <= new Date()) {
    icon   = '⏰';
    title  = 'TRIAL IMEKWISHA';
    reason = 'Dakika 30 za majaribio yako zimekwisha. Asante kwa kujaribu JAYNES MAX TV!';
  } else if (plan === 'premium') {
    icon   = '👑';
    title  = 'SUBSCRIPTION IMEKWISHA';
    reason = 'Subscription yako ya premium imekwisha. Renewi ili uendelee kutazama.';
  } else {
    icon   = '🔒';
    title  = 'JIUNGE SASA';
    reason = 'Unahitaji subscription ili kutazama channels zote bila kikwazo.';
  }

  const overlay = document.createElement('div');
  overlay.id = '__paywall';
  overlay.style.cssText = `
    position:fixed;inset:0;z-index:99999;
    background:rgba(4,4,14,0.97);
    backdrop-filter:blur(16px);
    display:flex;align-items:center;justify-content:center;
    padding:20px;font-family:'Outfit',sans-serif;
    animation:pwFade 0.4s ease;
    overflow-y:auto;
  `;

  // Zuia back button kufunga paywall
  history.pushState(null, '', location.href);
  window.addEventListener('popstate', () => history.pushState(null, '', location.href));

  overlay.innerHTML = `
    <style>
      @keyframes pwFade{from{opacity:0;transform:scale(0.95)}to{opacity:1;transform:scale(1)}}
      @keyframes pwPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
      @keyframes pwShine{0%{background-position:200% center}100%{background-position:-200% center}}
      .pw-box{max-width:360px;width:100%;text-align:center}
      .pw-icon{font-size:58px;margin-bottom:8px;display:block;animation:pwPulse 2.2s ease-in-out infinite}
      .pw-title{
        font-family:'Bebas Neue',sans-serif;font-size:30px;letter-spacing:4px;
        background:linear-gradient(135deg,#00d4ff,#ffffff,#ff4466);
        background-size:200% auto;
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;
        background-clip:text;
        animation:pwShine 3s linear infinite;
        margin-bottom:10px
      }
      .pw-reason{
        color:#ffaa66;font-size:13px;font-weight:600;line-height:1.5;
        background:rgba(255,100,0,0.08);border:1px solid rgba(255,100,0,0.2);
        border-radius:10px;padding:10px 14px;margin-bottom:14px;
      }
      .pw-sub{color:#6677aa;font-size:13px;line-height:1.7;margin-bottom:16px}
      .pw-pkgs{
        background:rgba(0,212,255,0.04);border:1px solid rgba(0,212,255,0.12);
        border-radius:14px;padding:14px 16px;margin-bottom:16px;text-align:left
      }
      .pw-pkg-hdr{font-size:10px;color:#7788aa;text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;display:flex;align-items:center;gap:6px}
      .pw-pkg-row{
        display:flex;justify-content:space-between;align-items:center;
        padding:9px 0;border-bottom:1px solid rgba(255,255,255,0.05);font-size:13px;
        cursor:pointer;transition:all 0.2s;border-radius:0;
      }
      .pw-pkg-row:last-child{border-bottom:none}
      .pw-pkg-row:hover{padding-left:6px;color:#00d4ff}
      .pw-pkg-row:hover .pw-pkg-price{color:#fff}
      .pw-pkg-name{color:#fff;font-weight:600;display:flex;align-items:center;gap:7px}
      .pw-pkg-price{color:#00d4ff;font-family:'Bebas Neue',sans-serif;font-size:20px}
      .pw-popular{background:rgba(255,215,0,0.15);color:gold;font-size:9px;font-weight:700;padding:2px 7px;border-radius:10px;border:1px solid rgba(255,215,0,0.3)}
      .pw-btn-main{
        display:flex;align-items:center;justify-content:center;gap:8px;
        width:100%;padding:16px;
        background:linear-gradient(135deg,#00d4ff,#0088bb);
        border-radius:14px;color:#000;font-size:16px;font-weight:800;
        text-decoration:none;letter-spacing:1px;margin-bottom:10px;
        box-shadow:0 8px 28px rgba(0,212,255,0.35);
        transition:transform 0.2s,box-shadow 0.2s;
        font-family:'Bebas Neue',sans-serif;
      }
      .pw-btn-main:hover{transform:translateY(-2px);box-shadow:0 12px 36px rgba(0,212,255,0.5)}
      .pw-btn-wa{
        display:flex;align-items:center;justify-content:center;gap:8px;
        width:100%;padding:13px;
        background:rgba(37,211,102,0.08);border:1px solid rgba(37,211,102,0.25);
        border-radius:14px;color:#25d366;font-size:13px;font-weight:700;
        text-decoration:none;margin-bottom:14px;transition:background 0.2s;
      }
      .pw-btn-wa:hover{background:rgba(37,211,102,0.16)}
      .pw-divider{display:flex;align-items:center;gap:10px;color:#334;font-size:11px;margin-bottom:14px}
      .pw-divider::before,.pw-divider::after{content:'';flex:1;height:1px;background:rgba(255,255,255,0.06)}
      .pw-footer{font-size:11px;color:#2a3a4a;line-height:1.9}
      .pw-footer a{color:#00aabb;text-decoration:none}
      .pw-free-hint{font-size:11px;color:#445566;margin-top:8px}
    </style>

    <div class="pw-box">
      <span class="pw-icon">${icon}</span>
      <div class="pw-title">${title}</div>
      <div class="pw-reason">${reason}</div>
      <p class="pw-sub">Jiunge na JAYNES MAX TV Premium.<br>Angalia channels zote, mechi live, na zaidi.</p>

      <div class="pw-pkgs">
        <div class="pw-pkg-hdr"><i style="color:#00d4ff;font-size:10px" class="fa fa-star"></i> CHAGUA PACKAGE LAKO</div>
        <div class="pw-pkg-row" onclick="location.href='malipo.php'">
          <span class="pw-pkg-name">📅 Wiki 1</span>
          <span class="pw-pkg-price">TSh 1,000</span>
        </div>
        <div class="pw-pkg-row" onclick="location.href='malipo.php'">
          <span class="pw-pkg-name">📅 Mwezi 1 <span class="pw-popular">★ MAARUFU</span></span>
          <span class="pw-pkg-price">TSh 3,000</span>
        </div>
        <div class="pw-pkg-row" onclick="location.href='malipo.php'">
          <span class="pw-pkg-name">📅 Miezi 3</span>
          <span class="pw-pkg-price">TSh 8,000</span>
        </div>
        <div class="pw-pkg-row" onclick="location.href='malipo.php'">
          <span class="pw-pkg-name">📅 Miezi 6</span>
          <span class="pw-pkg-price">TSh 15,000</span>
        </div>
        <div class="pw-pkg-row" onclick="location.href='malipo.php'">
          <span class="pw-pkg-name">🏆 Mwaka 1</span>
          <span class="pw-pkg-price">TSh 25,000</span>
        </div>
      </div>

      <a href="malipo.php" class="pw-btn-main">
        <i class="fa fa-credit-card"></i> LIPIA SASA
      </a>

      <div class="pw-divider">au wasiliana moja kwa moja</div>

      <a href="https://wa.me/255616393956?text=Habari%2C%20nataka%20kujiunga%20na%20JAYNES%20MAX%20TV" target="_blank" class="pw-btn-wa">
        <span style="font-size:20px">💬</span>
        WhatsApp — 0616 393 956
      </a>

      <div class="pw-footer">
        Airtel Money: <a href="tel:+255695753176">0695 753 176</a> (JUMANNE HASSAN NDANGE)<br>
        Email: <a href="mailto:jaynestvmax@gmail.com">jaynestvmax@gmail.com</a>
      </div>
      <div class="pw-free-hint">Tayari umelipa? <a href="malipo.php" style="color:#00d4ff">Angalia hali ya malipo →</a></div>
    </div>
  `;

  document.body.appendChild(overlay);
  document.body.style.overflow = 'hidden';
}

/* ── HELPERS ────────────────────────────────────────────────────*/
function getUser() {
  return {
    email:     localStorage.getItem('jaynesEmail')     || '',
    name:      localStorage.getItem('jaynesName')      || 'Mtumiaji',
    uid:       localStorage.getItem('jaynesUid')       || '',
    token:     localStorage.getItem('jaynesToken')     || '',
    plan:      localStorage.getItem('jaynesPlan')      || 'free',
    trialEnd:  localStorage.getItem('jaynesTrialEnd')  || '',
    subEnd:    localStorage.getItem('jaynesSubEnd')    || '',
    provider:  localStorage.getItem('jaynesProvider')  || 'email',
    createdAt: localStorage.getItem('jaynesCreatedAt') || '',
  };
}

function logout() {
  const token = localStorage.getItem('jaynesToken');
  if (token) fetch('auth.php?action=logout',{method:'POST',headers:{'Authorization':'Bearer '+token}}).catch(()=>{});
  clearSession();
  location.href = 'login.html';
}

function clearSession() {
  ['jaynesEmail','jaynesUser','jaynesName','jaynesUid','jaynesToken',
   'jaynesRefreshToken','jaynesPlan','jaynesProvider','jaynesLastCheck',
   'jaynesTrial','jaynesTrialEnd','jaynesSubEnd','jaynesCreatedAt'].forEach(k => localStorage.removeItem(k));
}

function isPremium() {
  const plan     = localStorage.getItem('jaynesPlan')     || 'free';
  const trialEnd = localStorage.getItem('jaynesTrialEnd') || '';
  const subEnd   = localStorage.getItem('jaynesSubEnd')   || '';
  if (plan === 'premium' && subEnd   && new Date(subEnd)   > new Date()) return true;
  if (trialEnd && new Date(trialEnd) > new Date()) return true;
  return false;
}

function showToast(msg, duration = 3000) {
  let t = document.getElementById('toast');
  if (!t) { t = Object.assign(document.createElement('div'),{id:'toast',className:'toast'}); document.body.appendChild(t); }
  t.textContent = msg;
  t.classList.add('show');
  clearTimeout(t._timer);
  t._timer = setTimeout(() => t.classList.remove('show'), duration);
}
