/**
 * JAYNES MAX TV — _channel_guard.js
 * Logic:
 * - Channels BURE (FREE_CHANNELS): tb1, tbc2, safari dodoma, zbc, azam one — zote zinaweza kutazamwa bila subscription
 * - Channels ZINGINE ZOTE: zinahitaji premium au trial inayoendelea
 */

// ══════════════════════════════════════════════════════════════
//  CHANNELS BURE — hazihitaji subscription
//  Channels ZOTE ZINGINE zinahitaji malipo
// ══════════════════════════════════════════════════════════════
const FREE_CHANNELS = [
  // TBC
  'tbc1', 'tbc 1', 'tb1',
  'tbc2', 'tbc 2',
  // ZBC
  'zbc',
  // Azam One tu (si Azam Sports, Azam Plus, n.k.)
  'azam one', 'azamone', 'azam 1',
  // Wasafi
  'wasafi', 'wasafi tv', 'wasafi channel',
];

/**
 * Je channel hii ni bure?
 * Inatumia mechi sahihi — "Azam Sports 1" haitafanana na "Azam One"
 */
function isFreeChannel(title) {
  if (!title) return false;
  const t = title.toLowerCase().trim();

  return FREE_CHANNELS.some(free => {
    // Mechi kamili ya jina lote
    if (t === free) return true;
    // Jina linaanza na free keyword (mfano "tbc1 hd" → "tbc1")
    if (t.startsWith(free + ' ') || t.startsWith(free + '-')) return true;
    // Jina linafanana kabisa na keyword yenyewe
    if (t.includes(free)) {
      // Zuia false positives: "azam one" haifanani na "azam sports one"
      // Hakikisha si sehemu ya neno kubwa zaidi
      const idx = t.indexOf(free);
      const before = idx > 0 ? t[idx - 1] : ' ';
      const after  = idx + free.length < t.length ? t[idx + free.length] : ' ';
      const beforeOk = before === ' ' || before === '-' || idx === 0;
      const afterOk  = after  === ' ' || after  === '-' || idx + free.length === t.length;
      return beforeOk && afterOk;
    }
    return false;
  });
}

/**
 * Je mtumiaji ana premium/trial inayoendelea?
 */
function hasAccess() {
  const plan     = localStorage.getItem('jaynesPlan')     || 'free';
  const trialEnd = localStorage.getItem('jaynesTrialEnd') || '';
  const subEnd   = localStorage.getItem('jaynesSubEnd')   || '';
  const now      = new Date();
  if (plan === 'premium' && subEnd   && new Date(subEnd)   > now) return true;
  if (trialEnd && new Date(trialEnd) > now) return true;
  return false;
}

/**
 * Angalia channel kabla ya kwenda player
 * - Channel bure → play bila kizuizi
 * - Channel premium + ana access → play
 * - Channel premium + hana access → mini-paywall
 */
function goChannel(title, playerUrl) {
  // Channel bure — ruhusiwa kila mtu
  if (isFreeChannel(title)) {
    location.href = playerUrl;
    return;
  }
  // Channel premium — angalia subscription
  if (hasAccess()) {
    location.href = playerUrl;
    return;
  }
  // Hana access — onyesha paywall ndogo
  showPremiumModal(title);
}

// ══════════════════════════════════════════════════════════════
//  MINI-PAYWALL KWA CHANNEL YA PREMIUM
// ══════════════════════════════════════════════════════════════
function showPremiumModal(channelName) {
  const old = document.getElementById('__ch_lock');
  if (old) old.remove();

  const modal = document.createElement('div');
  modal.id = '__ch_lock';
  modal.style.cssText = `
    position:fixed;inset:0;z-index:99990;
    background:rgba(4,4,14,0.94);
    backdrop-filter:blur(16px);
    display:flex;align-items:center;justify-content:center;
    padding:20px;font-family:'Outfit',sans-serif;
    animation:chLockFade 0.3s ease;
  `;

  modal.innerHTML = `
    <style>
      @keyframes chLockFade{from{opacity:0;transform:scale(0.94)}to{opacity:1;transform:scale(1)}}
      @keyframes chLockBounce{0%,100%{transform:translateY(0)}40%{transform:translateY(-8px)}70%{transform:translateY(-4px)}}
      .cl-box{
        max-width:320px;width:100%;
        background:linear-gradient(160deg,#0c0c22,#0a0a1e);
        border:1px solid rgba(0,212,255,0.2);
        border-radius:20px;padding:28px 22px;text-align:center;
        box-shadow:0 24px 60px rgba(0,0,0,0.8),0 0 0 1px rgba(0,212,255,0.08);
      }
      .cl-icon{font-size:52px;display:block;margin-bottom:10px;animation:chLockBounce 0.6s ease 0.1s}
      .cl-title{
        font-family:'Bebas Neue',sans-serif;font-size:22px;letter-spacing:3px;
        background:linear-gradient(135deg,#ffd700,#ffaa00);
        -webkit-background-clip:text;-webkit-text-fill-color:transparent;
        background-clip:text;margin-bottom:8px;
      }
      .cl-name{
        font-size:14px;font-weight:700;color:rgba(255,255,255,0.85);
        background:rgba(255,255,255,0.06);border:1px solid rgba(255,255,255,0.1);
        border-radius:8px;padding:7px 14px;margin-bottom:12px;display:inline-block;
      }
      .cl-msg{font-size:13px;color:#6677aa;line-height:1.7;margin-bottom:20px}
      .cl-msg strong{color:var(--accent,#00d4ff)}
      .cl-perks{
        display:flex;flex-direction:column;gap:7px;
        background:rgba(255,215,0,0.04);border:1px solid rgba(255,215,0,0.12);
        border-radius:12px;padding:12px 14px;margin-bottom:18px;text-align:left;
      }
      .cl-perk{font-size:12px;color:rgba(255,255,255,0.7);display:flex;align-items:center;gap:8px;}
      .cl-perk i{color:#ffd700;font-size:11px;flex-shrink:0;}
      .cl-btn-pay{
        display:flex;align-items:center;justify-content:center;gap:8px;
        width:100%;padding:15px;
        background:linear-gradient(135deg,#ffd700,#ff8c00);
        border:none;border-radius:12px;
        color:#000;font-size:15px;font-weight:800;
        font-family:'Bebas Neue',sans-serif;letter-spacing:1px;
        cursor:pointer;margin-bottom:10px;
        box-shadow:0 6px 22px rgba(255,215,0,0.3);
        transition:all 0.2s;
      }
      .cl-btn-pay:hover{transform:translateY(-2px);box-shadow:0 10px 28px rgba(255,215,0,0.45)}
      .cl-btn-close{
        width:100%;padding:11px;
        background:rgba(255,255,255,0.05);
        border:1px solid rgba(255,255,255,0.1);
        border-radius:12px;color:#6677aa;
        font-size:13px;font-weight:600;font-family:'Outfit',sans-serif;
        cursor:pointer;transition:all 0.2s;
      }
      .cl-btn-close:hover{background:rgba(255,255,255,0.09);color:#fff}
      .cl-note{font-size:11px;color:#334455;margin-top:12px;line-height:1.6}
      .cl-note a{color:#00aabb;text-decoration:none}
    </style>
    <div class="cl-box">
      <span class="cl-icon">👑</span>
      <div class="cl-title">CHANNEL YA PREMIUM</div>
      <div class="cl-name">📺 ${escHtml(channelName)}</div>
      <p class="cl-msg">
        Channel hii inahitaji <strong>subscription ya premium</strong>.<br>
        Jiunge sasa — bei nafuu, channels zote.
      </p>
      <div class="cl-perks">
        <div class="cl-perk"><i class="fa fa-check-circle"></i>Channels zote za Azam Sports 1–4</div>
        <div class="cl-perk"><i class="fa fa-check-circle"></i>Mechi za NBC Premier League LIVE</div>
        <div class="cl-perk"><i class="fa fa-check-circle"></i>Tamthiliya, Music, na zaidi</div>
        <div class="cl-perk"><i class="fa fa-check-circle"></i>Ubora wa HD na FHD</div>
      </div>
      <button class="cl-btn-pay" onclick="location.href='malipo.php'">
        <i class="fa fa-crown"></i> JIUNGE PREMIUM — TSh 1,000/wiki
      </button>
      <button class="cl-btn-close" onclick="document.getElementById('__ch_lock').remove()">
        ← Rudi — tazama channels za bure
      </button>
      <div class="cl-note">
        Tayari umelipa? <a href="malipo.php">Angalia hali ya malipo →</a><br>
        WhatsApp: <a href="https://wa.me/255616393956">0616 393 956</a>
      </div>
    </div>
  `;

  modal.addEventListener('click', e => { if (e.target === modal) modal.remove(); });
  document.body.appendChild(modal);
}

function escHtml(s) {
  const d = document.createElement('div');
  d.textContent = s || '';
  return d.innerHTML;
}

// ══════════════════════════════════════════════════════════════
//  LOCK BADGE — kwa channels za premium (visual)
// ══════════════════════════════════════════════════════════════
function applyLockBadge(cardEl, title) {
  // Channels bure — onyesha "FREE" badge badogo
  if (isFreeChannel(title)) {
    const badge = document.createElement('div');
    badge.style.cssText = `
      position:absolute;top:5px;left:5px;z-index:6;
      background:rgba(0,255,136,0.9);color:#000;
      font-size:8px;font-weight:700;padding:2px 7px;
      border-radius:4px;display:flex;align-items:center;gap:3px;
    `;
    badge.innerHTML = `<i class="fa fa-unlock" style="font-size:8px"></i> BURE`;
    const imgWrap = cardEl.querySelector('.c-img, .c-img-wrap, .cimg') || cardEl;
    imgWrap.appendChild(badge);
    return;
  }
  // Channel premium — onyesha crown badge
  if (!hasAccess()) {
    const badge = document.createElement('div');
    badge.style.cssText = `
      position:absolute;top:5px;left:5px;z-index:6;
      background:rgba(255,215,0,0.9);color:#000;
      font-size:8px;font-weight:700;padding:2px 7px;
      border-radius:4px;display:flex;align-items:center;gap:3px;
    `;
    badge.innerHTML = `<i class="fa fa-crown" style="font-size:8px"></i> PREMIUM`;
    // Dim image kidogo
    const img = cardEl.querySelector('img');
    if (img) img.style.filter = 'brightness(0.75)';
    const imgWrap = cardEl.querySelector('.c-img, .c-img-wrap, .cimg') || cardEl;
    imgWrap.appendChild(badge);
  }
}
