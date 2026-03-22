<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Admin Malipo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
body { padding-top: 72px; padding-bottom: 30px; }

/* ADMIN HEADER */
.admin-badge { display:inline-flex; align-items:center; gap:5px; background:rgba(255,68,102,0.15); border:1px solid rgba(255,68,102,0.3); color:var(--accent2); font-size:10px; font-weight:700; padding:3px 10px; border-radius:20px; }

/* STATS CARDS */
.stats-grid { display:grid; grid-template-columns:repeat(2,1fr); gap:10px; padding:14px; }
.stat-card { background:var(--card); border:1px solid var(--border); border-radius:14px; padding:14px; }
.stat-card .s-icon { font-size:22px; margin-bottom:6px; }
.stat-card .s-val  { font-family:'Bebas Neue',sans-serif; font-size:28px; color:var(--accent); line-height:1; }
.stat-card .s-lbl  { font-size:11px; color:var(--muted); margin-top:2px; }
.stat-card.pending .s-val { color:var(--gold); }
.stat-card.approved .s-val { color:var(--green); }
.stat-card.revenue .s-val { color:var(--accent2); font-size:20px; }

/* FILTER TABS */
.filter-tabs { display:flex; gap:6px; padding:0 14px 14px; overflow-x:auto; scrollbar-width:none; }
.filter-tabs::-webkit-scrollbar { display:none; }
.ftab { padding:7px 16px; border-radius:20px; font-size:12px; font-weight:700; cursor:pointer; white-space:nowrap; transition:all 0.2s; border:1px solid var(--border); color:var(--muted); background:var(--card); }
.ftab.active { background:var(--accent); color:#000; border-color:var(--accent); }

/* PAYMENT CARDS */
.payments-list { padding:0 14px; }
.pay-card { background:var(--card); border:1px solid var(--border); border-radius:14px; margin-bottom:10px; overflow:hidden; transition:border-color 0.2s; }
.pay-card.pending  { border-left:3px solid var(--gold); }
.pay-card.approved { border-left:3px solid var(--green); }
.pay-card.rejected { border-left:3px solid var(--accent2); }

.pay-card-hdr { display:flex; align-items:center; justify-content:space-between; padding:12px 14px 8px; }
.pay-status { padding:3px 10px; border-radius:20px; font-size:10px; font-weight:700; }
.pay-status.pending  { background:rgba(255,215,0,0.12); color:var(--gold); border:1px solid rgba(255,215,0,0.2); }
.pay-status.approved { background:rgba(0,255,136,0.12); color:var(--green); border:1px solid rgba(0,255,136,0.2); }
.pay-status.rejected { background:rgba(255,68,102,0.12); color:var(--accent2); border:1px solid rgba(255,68,102,0.2); }
.pay-date { font-size:10px; color:var(--muted); }

.pay-card-body { padding:0 14px 10px; display:grid; grid-template-columns:1fr 1fr; gap:6px; }
.pay-field { font-size:12px; }
.pay-field .lbl { color:var(--muted); font-size:10px; text-transform:uppercase; letter-spacing:0.3px; }
.pay-field .val { color:var(--text); font-weight:600; margin-top:1px; word-break:break-all; }
.pay-field .val.accent { color:var(--accent); font-family:monospace; }
.pay-field .val.gold { color:var(--gold); font-family:'Bebas Neue',sans-serif; font-size:18px; }
.pay-field .val.green { color:var(--green); }

.pay-card-actions { display:flex; gap:8px; padding:10px 14px; border-top:1px solid rgba(255,255,255,0.05); }
.action-btn { flex:1; padding:9px; border:none; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; font-family:'Outfit',sans-serif; display:flex; align-items:center; justify-content:center; gap:6px; transition:all 0.2s; }
.action-btn:disabled { opacity:0.4; cursor:not-allowed; }
.btn-approve { background:rgba(0,255,136,0.15); color:var(--green); border:1px solid rgba(0,255,136,0.25); }
.btn-approve:hover:not(:disabled) { background:rgba(0,255,136,0.3); }
.btn-reject  { background:rgba(255,68,102,0.12); color:var(--accent2); border:1px solid rgba(255,68,102,0.22); }
.btn-reject:hover:not(:disabled)  { background:rgba(255,68,102,0.25); }
.btn-view    { background:rgba(0,212,255,0.1); color:var(--accent); border:1px solid rgba(0,212,255,0.2); }
.btn-view:hover { background:rgba(0,212,255,0.2); }

/* CONFIRM MODAL */
.modal-bg { position:fixed; inset:0; background:rgba(0,0,0,0.8); backdrop-filter:blur(6px); z-index:1000; display:none; align-items:center; justify-content:center; padding:20px; }
.modal-bg.show { display:flex; }
.modal { background:#0d0d22; border:1px solid var(--border); border-radius:18px; padding:24px; max-width:360px; width:100%; }
.modal h3 { font-family:'Bebas Neue',sans-serif; font-size:20px; letter-spacing:2px; margin-bottom:8px; }
.modal p  { font-size:13px; color:var(--muted); line-height:1.6; margin-bottom:16px; }
.modal-info { background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.08); border-radius:10px; padding:12px; margin-bottom:16px; }
.modal-info-row { display:flex; justify-content:space-between; font-size:12px; margin-bottom:6px; }
.modal-info-row:last-child { margin-bottom:0; }
.modal-info-row .lbl { color:var(--muted); }
.modal-info-row .val { font-weight:700; }
.modal-inp { width:100%; padding:11px 14px; background:rgba(255,255,255,0.04); border:1px solid rgba(255,255,255,0.1); border-radius:10px; color:var(--text); font-size:13px; font-family:'Outfit',sans-serif; outline:none; margin-bottom:12px; }
.modal-inp:focus { border-color:var(--accent); }
.modal-btns { display:flex; gap:8px; }
.modal-btn { flex:1; padding:12px; border:none; border-radius:10px; font-weight:700; font-size:13px; cursor:pointer; font-family:'Outfit',sans-serif; transition:all 0.2s; }
.modal-btn.confirm-approve { background:var(--green); color:#000; }
.modal-btn.confirm-reject  { background:var(--accent2); color:#fff; }
.modal-btn.cancel { background:rgba(255,255,255,0.08); color:var(--muted); }

/* EMPTY STATE */
.empty-state { text-align:center; padding:40px 20px; color:var(--muted); }
.empty-state i { font-size:36px; display:block; margin-bottom:10px; color:rgba(255,255,255,0.15); }

/* REFRESH BTN */
.refresh-btn { background:rgba(0,212,255,0.1); border:1px solid rgba(0,212,255,0.2); color:var(--accent); padding:8px 16px; border-radius:10px; font-size:12px; font-weight:700; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:6px; }
.refresh-btn:hover { background:rgba(0,212,255,0.2); }
.refresh-btn.spinning i { animation:spin 0.7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- CONFIRM MODAL -->
<div class="modal-bg" id="modalBg">
  <div class="modal">
    <h3 id="modalTitle">THIBITISHA</h3>
    <p id="modalDesc">Je, una uhakika?</p>
    <div class="modal-info" id="modalInfo"></div>
    <input class="modal-inp" id="modalNote" placeholder="Maelezo kwa mtumiaji (hiari)" style="display:none">
    <div class="modal-btns">
      <button class="modal-btn cancel" onclick="closeModal()">Ghairi</button>
      <button class="modal-btn" id="modalConfirmBtn" onclick="confirmAction()">Thibitisha</button>
    </div>
  </div>
</div>

<!-- TOPBAR -->
<div class="topbar">
  <a href="home.html" class="back-btn"><i class="fa fa-arrow-left"></i></a>
  <div class="page-title">ADMIN <span>MALIPO</span></div>
  <span class="admin-badge" style="margin-left:auto"><i class="fa fa-shield-halved"></i> ADMIN</span>
</div>

<!-- STATS -->
<div class="stats-grid">
  <div class="stat-card pending">
    <div class="s-icon">⏳</div>
    <div class="s-val" id="statPending">—</div>
    <div class="s-lbl">Yanasubiri</div>
  </div>
  <div class="stat-card approved">
    <div class="s-icon">✅</div>
    <div class="s-val" id="statApproved">—</div>
    <div class="s-lbl">Yamekubaliwa</div>
  </div>
  <div class="stat-card">
    <div class="s-icon">❌</div>
    <div class="s-val" id="statRejected" style="color:var(--accent2)">—</div>
    <div class="s-lbl">Yamekataliwa</div>
  </div>
  <div class="stat-card revenue">
    <div class="s-icon">💰</div>
    <div class="s-val" id="statRevenue">—</div>
    <div class="s-lbl">Mapato (TSh)</div>
  </div>
</div>

<!-- FILTER + REFRESH -->
<div style="display:flex;align-items:center;justify-content:space-between;padding:0 14px 10px">
  <div class="filter-tabs" style="padding:0;flex:1">
    <div class="ftab active" onclick="setFilter('all')">Yote</div>
    <div class="ftab"        onclick="setFilter('pending')">Inasubiri</div>
    <div class="ftab"        onclick="setFilter('approved')">Imekubaliwa</div>
    <div class="ftab"        onclick="setFilter('rejected')">Imekataliwa</div>
  </div>
  <button class="refresh-btn" id="refreshBtn" onclick="loadPayments()" style="margin-left:10px">
    <i class="fa fa-rotate-right"></i>
  </button>
</div>

<!-- PAYMENTS LIST -->
<div class="payments-list" id="paymentsList">
  <div class="empty-state"><div class="spinner"></div></div>
</div>

<script>
const SB_URL    = 'https://dablnrggyfcddmdeiqxi.supabase.co';
const SB_SVCKEY = 'sb_secret_VlGl6UXSTT8CB_YIqJZ-zw_anyyL2d2'; // Service key kwa admin

// ── ADMIN AUTH CHECK ────────────────────────────────────────────
// Weka emails za admin hapa
const ADMIN_EMAILS = ['jaynestvmax@gmail.com', 'swajayfour@gmail.com'];

(function adminCheck() {
  const email = localStorage.getItem('jaynesEmail');
  if (!email || !ADMIN_EMAILS.includes(email.toLowerCase())) {
    document.body.innerHTML = `
      <div style="display:flex;align-items:center;justify-content:center;min-height:100vh;text-align:center;padding:20px;font-family:'Outfit',sans-serif;background:#060610;color:#fff">
        <div>
          <div style="font-size:48px;margin-bottom:16px">🚫</div>
          <h2 style="font-family:'Bebas Neue',sans-serif;font-size:28px;letter-spacing:3px;color:#ff4466">RUHUSA IMEKATALIWA</h2>
          <p style="color:#7788aa;font-size:14px;margin:10px 0 20px">Ukurasa huu ni kwa admin peke yake.</p>
          <a href="home.html" style="background:linear-gradient(135deg,#00d4ff,#0088bb);color:#000;padding:12px 28px;border-radius:12px;font-weight:700;text-decoration:none;font-family:'Outfit',sans-serif">Rudi Nyumbani</a>
        </div>
      </div>`;
    return;
  }
})();

let allPayments = [], currentFilter = 'all', pendingAction = null;

// ── LOAD PAYMENTS ───────────────────────────────────────────────
async function loadPayments() {
  const btn = document.getElementById('refreshBtn');
  btn.classList.add('spinning');
  document.getElementById('paymentsList').innerHTML = '<div class="empty-state"><div class="spinner"></div></div>';

  try {
    const res  = await fetch(`${SB_URL}/rest/v1/payments?select=id,user_id,email,phone,package,amount,method,reference,notes,admin_note,status,days,created_at,updated_at&order=created_at.desc&limit=100`, {
      headers: {
        'apikey':        SB_SVCKEY,
        'Authorization': 'Bearer ' + SB_SVCKEY,
      }
    });
    allPayments = await res.json();
    if (!Array.isArray(allPayments)) allPayments = [];

    updateStats();
    renderList();
  } catch(e) {
    document.getElementById('paymentsList').innerHTML = '<div class="empty-state"><i class="fa fa-wifi"></i>Tatizo la mtandao</div>';
  } finally {
    btn.classList.remove('spinning');
  }
}

// ── STATS ───────────────────────────────────────────────────────
function updateStats() {
  const pending  = allPayments.filter(p => p.status === 'pending').length;
  const approved = allPayments.filter(p => p.status === 'approved').length;
  const rejected = allPayments.filter(p => p.status === 'rejected').length;
  const revenue  = allPayments.filter(p => p.status === 'approved').reduce((s,p) => s + (p.amount||0), 0);

  document.getElementById('statPending').textContent  = pending;
  document.getElementById('statApproved').textContent = approved;
  document.getElementById('statRejected').textContent = rejected;
  document.getElementById('statRevenue').textContent  = 'TSh ' + revenue.toLocaleString();
}

// ── FILTER ──────────────────────────────────────────────────────
function setFilter(f) {
  currentFilter = f;
  document.querySelectorAll('.ftab').forEach((t,i) => {
    t.classList.toggle('active', ['all','pending','approved','rejected'][i] === f);
  });
  renderList();
}

// ── RENDER ──────────────────────────────────────────────────────
const METHOD_ICONS = { mpesa:'💚', tigo:'💙', airtel:'❤️' };
const STATUS_LABELS = { pending:'Inasubiri', approved:'Imekubaliwa', rejected:'Imekataliwa' };

function esc(s) {
  if (!s) return '';
  return String(s).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;').replace(/'/g,'&#39;');
}

function renderList() {
  const list = currentFilter === 'all' ? allPayments : allPayments.filter(p => p.status === currentFilter);
  const el   = document.getElementById('paymentsList');

  if (!list.length) {
    el.innerHTML = `<div class="empty-state"><i class="fa fa-inbox"></i>Hakuna malipo ${currentFilter==='all'?'':STATUS_LABELS[currentFilter]||''}</div>`;
    return;
  }

  el.innerHTML = list.map(p => {
    const d    = new Date(p.created_at);
    const date = d.toLocaleDateString('sw') + ' ' + d.toLocaleTimeString('sw',{hour:'2-digit',minute:'2-digit'});
    const isPending = p.status === 'pending';

    // Maelezo ya mteja — onyesha kila wakati (tupu au la)
    const notesHtml = (p.notes && p.notes.trim())
      ? `<div class="pay-field" style="grid-column:1/-1">
           <div class="lbl" style="color:var(--accent)"><i class="fa fa-comment-dots" style="margin-right:4px"></i>Ujumbe wa Mteja</div>
           <div class="val" style="background:rgba(0,212,255,0.06);border:1px solid rgba(0,212,255,0.15);border-radius:8px;padding:8px 10px;line-height:1.6;font-size:12px;white-space:pre-wrap">${esc(p.notes)}</div>
         </div>`
      : `<div class="pay-field" style="grid-column:1/-1">
           <div class="lbl" style="color:var(--muted)"><i class="fa fa-comment-slash" style="margin-right:4px"></i>Ujumbe wa Mteja</div>
           <div class="val" style="color:var(--muted);font-style:italic;font-size:11px">Hakuna maelezo ya ziada</div>
         </div>`;

    const adminNoteHtml = (p.admin_note && p.admin_note.trim())
      ? `<div class="pay-field" style="grid-column:1/-1">
           <div class="lbl" style="color:var(--accent2)"><i class="fa fa-reply" style="margin-right:4px"></i>Jibu la Admin</div>
           <div class="val" style="background:rgba(255,68,102,0.06);border:1px solid rgba(255,68,102,0.15);border-radius:8px;padding:8px 10px;line-height:1.6;font-size:12px;white-space:pre-wrap;color:var(--accent2)">${esc(p.admin_note)}</div>
         </div>`
      : '';

    return `
    <div class="pay-card ${esc(p.status)}" id="card_${esc(p.id)}">
      <div class="pay-card-hdr">
        <span class="pay-status ${esc(p.status)}">${STATUS_LABELS[p.status]||esc(p.status)}</span>
        <span class="pay-date">${date}</span>
      </div>
      <div class="pay-card-body">
        <div class="pay-field">
          <div class="lbl">Mtumiaji</div>
          <div class="val">${esc(p.email)||'—'}</div>
        </div>
        <div class="pay-field">
          <div class="lbl">Simu</div>
          <div class="val accent">${esc(p.phone)||'—'}</div>
        </div>
        <div class="pay-field">
          <div class="lbl">Package</div>
          <div class="val green">${METHOD_ICONS[p.method]||''} ${esc(p.package)||'—'}</div>
        </div>
        <div class="pay-field">
          <div class="lbl">Kiasi</div>
          <div class="val gold">TSh ${Number(p.amount||0).toLocaleString()}</div>
        </div>
        <div class="pay-field" style="grid-column:1/-1">
          <div class="lbl">Transaction ID</div>
          <div class="val accent" style="font-family:monospace;letter-spacing:1px">${esc(p.reference)||'—'}</div>
        </div>
        ${notesHtml}
        ${adminNoteHtml}
      </div>
      <div class="pay-card-actions">
        ${isPending ? `
          <button class="action-btn btn-approve" onclick="openConfirm('approve','${esc(p.id)}','${esc(p.email)}','${esc(p.package)}',${p.days||30},${p.amount||0},'${esc(p.user_id||'')}')">
            <i class="fa fa-check"></i> Kubali
          </button>
          <button class="action-btn btn-reject" onclick="openConfirm('reject','${esc(p.id)}','${esc(p.email)}','${esc(p.package)}',0,${p.amount||0},'${esc(p.user_id||'')}')">
            <i class="fa fa-times"></i> Kataa
          </button>
        ` : `
          <button class="action-btn btn-view" disabled>
            <i class="fa fa-${p.status==='approved'?'check-circle':'times-circle'}"></i>
            ${STATUS_LABELS[p.status]||p.status}
          </button>
        `}
      </div>
    </div>`;
  }).join('');
}

// ── MODAL ───────────────────────────────────────────────────────
function openConfirm(action, id, email, pkg, days, amount, userId = '') {
  pendingAction = { action, id, email, pkg, days, amount, user_id: userId };

  const isApprove = action === 'approve';
  document.getElementById('modalTitle').textContent = isApprove ? '✅ KUBALI MALIPO' : '❌ KATAA MALIPO';
  document.getElementById('modalDesc').textContent  = isApprove
    ? 'Thibitisha malipo haya na uwashe subscription ya mtumiaji.'
    : 'Kataa malipo haya. Mtumiaji ataarifu.';
  document.getElementById('modalInfo').innerHTML = `
    <div class="modal-info-row"><span class="lbl">Mtumiaji</span><span class="val">${email}</span></div>
    <div class="modal-info-row"><span class="lbl">Package</span><span class="val">${pkg}</span></div>
    <div class="modal-info-row"><span class="lbl">Kiasi</span><span class="val" style="color:var(--gold)">TSh ${Number(amount).toLocaleString()}</span></div>
    ${isApprove ? `<div class="modal-info-row"><span class="lbl">Muda</span><span class="val" style="color:var(--green)">Siku ${days}</span></div>` : ''}
  `;
  document.getElementById('modalNote').style.display = 'block';
  document.getElementById('modalNote').placeholder   = isApprove ? 'Ujumbe kwa mtumiaji (hiari)' : 'Sababu ya kukataa (hiari)';
  document.getElementById('modalNote').value         = '';

  const confirmBtn = document.getElementById('modalConfirmBtn');
  confirmBtn.textContent = isApprove ? 'KUBALI' : 'KATAA';
  confirmBtn.className   = 'modal-btn ' + (isApprove ? 'confirm-approve' : 'confirm-reject');

  document.getElementById('modalBg').classList.add('show');
}

function closeModal() {
  document.getElementById('modalBg').classList.remove('show');
  pendingAction = null;
}

// ── CONFIRM ACTION ──────────────────────────────────────────────
async function confirmAction() {
  if (!pendingAction) return;
  const { action, id, email, pkg, days, amount, user_id } = pendingAction;
  const note = document.getElementById('modalNote').value.trim();
  closeModal();

  const confirmBtn = document.getElementById('modalConfirmBtn');
  confirmBtn.disabled = true;

  try {
    // 1. Sasisha status kwenye payments table
    const updateRes = await fetch(`${SB_URL}/rest/v1/payments?id=eq.${id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
        'apikey': SB_SVCKEY,
        'Authorization': 'Bearer ' + SB_SVCKEY,
      },
      body: JSON.stringify({
        status:     action === 'approve' ? 'approved' : 'rejected',
        admin_note: note || null,
        updated_at: new Date().toISOString(),
      }),
    });

    if (!updateRes.ok) throw new Error('Payment update failed');

    // 2. Kama imekubaliwa — sasisha profile ya mtumiaji
    if (action === 'approve') {
      const subEnd = new Date(Date.now() + days * 86400000).toISOString();

      // Tafuta user_id kwa email
      const userRes = await fetch(`${SB_URL}/rest/v1/profiles?email=eq.${encodeURIComponent(email)}&select=id`, {
        headers: { 'apikey': SB_SVCKEY, 'Authorization': 'Bearer ' + SB_SVCKEY }
      });
      const users = await userRes.json();
      const uid   = users[0]?.id;

      if (uid) {
        await fetch(`${SB_URL}/rest/v1/profiles?id=eq.${uid}`, {
          method: 'PATCH',
          headers: {
            'Content-Type': 'application/json',
            'apikey': SB_SVCKEY,
            'Authorization': 'Bearer ' + SB_SVCKEY,
          },
          body: JSON.stringify({ plan:'premium', sub_end: subEnd }),
        });
      }

      showToast('✅ Malipo yamekubaliwa! Subscription imewashwa.');
    } else {
      showToast('❌ Malipo yamekataliwa.');
    }

    // 3. Tuma notification kwa mtumiaji (OneSignal + Supabase inbox)
    try {
      const notifRes = await fetch('notify_payment.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          action:  action,
          email:   email,
          user_id: user_id || '',
          package: pkg,
          amount:  amount,
          days:    days,
          note:    note || '',
        }),
      });
      const notifData = await notifRes.json();
      if (notifData.success) {
        console.log('[Notify] ✅ Notification imetumwa:', notifData);
      } else {
        console.warn('[Notify] ⚠️ Notification imeshindwa:', notifData.errors);
        // Ikiwa notifications table haipo — onyesha SQL
        if (notifData.supabase_sql) {
          console.info('[Notify] 💡 Unda table kwenye Supabase:\n' + notifData.supabase_sql);
        }
      }
    } catch(ne) {
      console.warn('[Notify] Exception:', ne.message);
    }

    // Reload orodha
    await loadPayments();

  } catch(e) {
    showToast('❌ Tatizo: ' + e.message);
  }
}

// Close modal on bg click
document.getElementById('modalBg').addEventListener('click', e => {
  if (e.target === document.getElementById('modalBg')) closeModal();
});

// ── INIT ────────────────────────────────────────────────────────
loadPayments();

// Auto-refresh kila dakika 1
setInterval(loadPayments, 60000);
</script>
</body>
</html>
