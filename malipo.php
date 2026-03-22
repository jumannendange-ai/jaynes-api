<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Malipo</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;600;700;800&display=swap" rel="stylesheet">
<style>
:root {
  --bg:      #060610;
  --s1:      #0c0c1e;
  --card:    #0f0f22;
  --accent:  #00d4ff;
  --accent2: #ff4466;
  --gold:    #ffd700;
  --green:   #00ff88;
  --muted:   #4a5568;
  --text:    #e8eaf6;
  --border:  rgba(0,212,255,0.12);
  --glow:    0 0 24px rgba(0,212,255,0.18);
}
*{box-sizing:border-box;margin:0;padding:0;-webkit-tap-highlight-color:transparent}
html,body{background:var(--bg);color:var(--text);font-family:'Outfit',sans-serif;font-size:14px;min-height:100vh}
body{padding-bottom:90px}

/* TOPBAR */
.topbar{position:sticky;top:0;z-index:200;background:rgba(6,6,16,0.97);backdrop-filter:blur(20px);border-bottom:1px solid var(--border);display:flex;align-items:center;gap:12px;padding:0 14px;height:56px}
.back-btn{width:38px;height:38px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;display:flex;align-items:center;justify-content:center;color:#fff;text-decoration:none;flex-shrink:0;font-size:15px}
.tb-title{font-family:'Bebas Neue',sans-serif;font-size:20px;letter-spacing:3px;flex:1}
.tb-title span{color:var(--accent)}

/* SECTION LABELS */
.sec-lbl{font-family:'Bebas Neue',sans-serif;font-size:13px;letter-spacing:2px;color:var(--muted);padding:16px 16px 8px;display:flex;align-items:center;gap:8px}
.sec-lbl::after{content:'';flex:1;height:1px;background:rgba(255,255,255,.05)}

/* PACKAGES */
.pkgs{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;padding:0 14px 4px}
.pkg{background:var(--card);border:2px solid rgba(255,255,255,.07);border-radius:16px;padding:14px 12px;cursor:pointer;transition:all .25s;position:relative;overflow:hidden;user-select:none}
.pkg::before{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(0,212,255,.04),transparent);opacity:0;transition:opacity .25s}
.pkg:hover::before,.pkg.sel::before{opacity:1}
.pkg.sel{border-color:var(--accent);box-shadow:var(--glow)}
.pkg.hot{border-color:rgba(255,215,0,.3)}
.pkg-badge{display:none;position:absolute;top:8px;right:8px;background:var(--gold);color:#000;font-size:8px;font-weight:800;padding:2px 7px;border-radius:20px;align-items:center;gap:3px}
.pkg.hot .pkg-badge{display:flex}
.pkg-chk{display:none;position:absolute;top:8px;right:8px;width:20px;height:20px;background:var(--accent);color:#000;font-size:10px;font-weight:800;border-radius:50%;align-items:center;justify-content:center}
.pkg.sel .pkg-chk{display:flex}
.pkg.hot.sel .pkg-chk{right:76px}
.pkg-name{font-family:'Bebas Neue',sans-serif;font-size:15px;letter-spacing:2px;margin-bottom:4px}
.pkg-price{font-family:'Bebas Neue',sans-serif;font-size:26px;color:var(--accent);line-height:1}
.pkg-price sup{font-size:11px;vertical-align:super;color:var(--muted)}
.pkg-price small{font-size:11px;color:var(--muted);font-family:'Outfit',sans-serif;font-weight:400}
.pkg-perks{margin-top:10px;display:flex;flex-direction:column;gap:5px}
.perk{font-size:10px;color:rgba(255,255,255,.65);display:flex;align-items:center;gap:5px}
.perk i{color:var(--green);font-size:9px;flex-shrink:0}

/* METHODS */
.methods{display:grid;grid-template-columns:repeat(4,1fr);gap:8px;padding:0 14px 4px}
.mth{background:var(--card);border:2px solid rgba(255,255,255,.07);border-radius:12px;padding:11px 6px;text-align:center;cursor:pointer;transition:all .25s;user-select:none}
.mth.sel{border-color:var(--accent);background:rgba(0,212,255,.06)}
.mth-icon{font-size:24px;margin-bottom:4px}
.mth span{font-size:10px;color:var(--muted);font-weight:700;display:block}
.mth.sel span{color:var(--accent)}

/* STEPS */
.steps{padding:0 14px}
.step{background:var(--card);border:1px solid var(--border);border-radius:16px;padding:16px;margin-bottom:10px}
.step-hdr{display:flex;align-items:center;gap:10px;margin-bottom:14px}
.step-num{width:30px;height:30px;border-radius:50%;background:var(--accent);color:#000;font-weight:800;font-size:13px;display:flex;align-items:center;justify-content:center;flex-shrink:0}
.step-title{font-weight:700;font-size:14px;line-height:1.2}
.step-sub{font-size:11px;color:var(--muted);margin-top:2px}

/* AMOUNT BANNER */
.amt-banner{text-align:center;padding:12px;background:rgba(255,215,0,.05);border:1px solid rgba(255,215,0,.15);border-radius:12px;margin-bottom:14px;display:none}
.amt-lbl{font-size:11px;color:var(--muted);margin-bottom:2px}
.amt-val{font-family:'Bebas Neue',sans-serif;font-size:34px;color:var(--gold);letter-spacing:3px}

/* PAY NUMBER */
.pay-num{background:rgba(0,212,255,.05);border:1px solid rgba(0,212,255,.15);border-radius:12px;padding:12px 14px;display:flex;align-items:center;justify-content:space-between;margin-bottom:8px}
.pn-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:3px}
.pn-val{font-size:22px;font-weight:800;color:var(--accent);font-family:monospace;letter-spacing:1px}
.copy-btn{background:rgba(0,212,255,.12);border:1px solid rgba(0,212,255,.25);color:var(--accent);font-size:11px;font-weight:700;padding:8px 14px;border-radius:8px;cursor:pointer;transition:all .2s;white-space:nowrap;font-family:'Outfit',sans-serif}
.copy-btn.copied{background:rgba(0,255,136,.15);border-color:var(--green);color:var(--green)}

/* WHATSAPP ROW */
.wa-row{display:flex;align-items:center;gap:10px;background:rgba(37,211,102,.06);border:1px solid rgba(37,211,102,.2);border-radius:12px;padding:12px 14px;text-decoration:none;transition:all .2s;margin-bottom:8px}
.wa-row:active{background:rgba(37,211,102,.12)}
.wa-icon{font-size:26px;flex-shrink:0}
.wa-lbl{font-size:10px;color:rgba(37,211,102,.7);text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px}
.wa-num{font-size:18px;font-weight:800;color:#25d366;font-family:monospace;letter-spacing:1px}

/* CONFIRM BOX */
.confirm-box{background:rgba(0,212,255,.04);border:1px solid rgba(0,212,255,.12);border-radius:12px;padding:14px;margin-bottom:14px;display:none}
.cf-row{display:flex;justify-content:space-between;align-items:center;font-size:12px;padding:5px 0}
.cf-row:not(:last-child){border-bottom:1px solid rgba(255,255,255,.04)}
.cf-lbl{color:var(--muted)}
.cf-val{font-weight:700;color:var(--text)}
.cf-val.accent{color:var(--accent);font-family:monospace}
.cf-val.gold{color:var(--gold);font-family:'Bebas Neue',sans-serif;font-size:18px}
.cf-val.green{color:var(--green)}

/* FORM */
.form-group{margin-bottom:10px}
.form-group label{font-size:11px;color:var(--muted);display:block;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px}
.form-inp{width:100%;padding:12px 14px;background:rgba(255,255,255,.04);border:1.5px solid rgba(255,255,255,.08);border-radius:11px;color:var(--text);font-size:14px;font-family:'Outfit',sans-serif;outline:none;transition:all .25s}
.form-inp:focus{border-color:var(--accent);background:rgba(0,212,255,.04)}
.form-inp::placeholder{color:var(--muted)}
.form-inp.err{border-color:var(--accent2)}
.hint{font-size:10px;color:var(--muted);margin-top:4px;padding-left:2px;line-height:1.5}

/* SUBMIT */
.submit-btn{width:100%;padding:15px;background:linear-gradient(135deg,var(--accent),#0088cc);border:none;border-radius:13px;color:#000;font-size:16px;font-weight:800;cursor:pointer;font-family:'Bebas Neue',sans-serif;letter-spacing:2px;display:flex;align-items:center;justify-content:center;gap:8px;transition:all .3s;margin-top:4px}
.submit-btn:hover:not(:disabled){transform:translateY(-2px);box-shadow:0 10px 30px rgba(0,212,255,.35)}
.submit-btn:disabled{opacity:.45;cursor:not-allowed;transform:none}
.submit-btn.loading .btxt{display:none}
.submit-btn.loading .bspin{display:block}
.bspin{display:none;width:18px;height:18px;border:2px solid rgba(0,0,0,.3);border-top-color:#000;border-radius:50%;animation:spin .7s linear infinite}
@keyframes spin{to{transform:rotate(360deg)}}

/* SUCCESS */
.success-screen{display:none;text-align:center;padding:40px 20px}
.success-screen.show{display:block}
.suc-icon{font-size:64px;display:block;margin-bottom:16px;animation:pop .6s ease}
@keyframes pop{0%{transform:scale(0)}60%{transform:scale(1.15)}100%{transform:scale(1)}}
.suc-title{font-family:'Bebas Neue',sans-serif;font-size:28px;letter-spacing:3px;color:var(--green);margin-bottom:8px}
.suc-msg{font-size:13px;color:var(--muted);line-height:1.8;margin-bottom:20px}
.suc-id{background:rgba(0,255,136,.06);border:1px solid rgba(0,255,136,.2);border-radius:12px;padding:14px;margin-bottom:20px;display:none}
.suc-id-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.5px;margin-bottom:4px}
.suc-id-val{font-family:monospace;font-size:13px;color:var(--green);font-weight:700;word-break:break-all}

/* WA CONFIRM BTN */
.wa-confirm{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;padding:15px;background:rgba(37,211,102,.1);border:1.5px solid rgba(37,211,102,.3);border-radius:13px;color:#25d366;font-size:14px;font-weight:700;text-decoration:none;transition:all .2s;margin-bottom:10px}
.wa-confirm:active{background:rgba(37,211,102,.2)}
.wa-confirm-info{text-align:left}
.wa-confirm-title{font-size:14px;font-weight:800}
.wa-confirm-sub{font-size:11px;opacity:.75;margin-top:1px}

.home-btn{width:100%;padding:13px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.1);border-radius:13px;color:var(--muted);font-size:13px;font-weight:600;cursor:pointer;font-family:'Outfit',sans-serif;transition:all .2s}
.home-btn:hover{background:rgba(255,255,255,.1);color:#fff}

/* HISTORY */
.hist-wrap{padding:0 14px}
.hist-card{background:var(--card);border:1px solid var(--border);border-radius:14px;overflow:hidden}
.hist-row{display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-bottom:1px solid rgba(255,255,255,.04)}
.hist-row:last-child{border-bottom:none}
.hbadge{padding:3px 10px;border-radius:20px;font-size:9px;font-weight:800;white-space:nowrap;flex-shrink:0;letter-spacing:.5px;margin-top:2px}
.hbadge.pending{background:rgba(255,215,0,.12);color:var(--gold);border:1px solid rgba(255,215,0,.2)}
.hbadge.approved{background:rgba(0,255,136,.12);color:var(--green);border:1px solid rgba(0,255,136,.2)}
.hbadge.rejected{background:rgba(255,68,102,.12);color:var(--accent2);border:1px solid rgba(255,68,102,.2)}
.hinfo{flex:1;min-width:0}
.h-pkg{font-weight:700;color:var(--text)}
.h-meta{color:var(--muted);font-size:10px;margin-top:1px}
.h-note{font-size:11px;color:var(--accent2);margin-top:5px;padding:5px 8px;background:rgba(255,68,102,.06);border-radius:6px;line-height:1.5}
.h-amt{font-family:'Bebas Neue',sans-serif;font-size:19px;color:var(--accent);flex-shrink:0}

/* PENDING NOTICE */
.pending-notice{background:rgba(255,215,0,.05);border:1px solid rgba(255,215,0,.15);border-radius:12px;padding:12px 14px;font-size:12px;color:var(--muted);line-height:1.7;margin-bottom:10px}
.pending-notice strong{color:var(--gold)}

/* TOAST */
.toast{position:fixed;bottom:90px;left:50%;transform:translateX(-50%) translateY(20px);background:#1a1a3e;border:1px solid var(--border);border-radius:12px;padding:11px 20px;font-size:13px;font-weight:600;color:var(--text);z-index:9999;opacity:0;transition:all .3s;pointer-events:none;max-width:90vw;text-align:center}
.toast.show{opacity:1;transform:translateX(-50%) translateY(0)}

/* BOTTOM NAV */
.bottom-nav{position:fixed;bottom:0;left:0;right:0;height:64px;background:rgba(6,6,16,.97);backdrop-filter:blur(20px);border-top:1px solid rgba(255,255,255,.06);display:flex;justify-content:space-around;align-items:center;z-index:200}
.nav-item{display:flex;flex-direction:column;align-items:center;gap:3px;padding:6px 14px;border-radius:12px;cursor:pointer;font-size:10px;font-weight:700;color:var(--muted);text-decoration:none;transition:all .2s}
.nav-item.active,.nav-item:hover{color:var(--accent)}
.nav-item.active{background:rgba(0,212,255,.07)}
.nav-item i{font-size:18px}
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<!-- TOPBAR -->
<div class="topbar">
  <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
  <div class="tb-title">MALIPO <span>&amp;</span> SUBSCRIPTION</div>
</div>

<!-- ══ MAIN FORM ══ -->
<div id="mainForm">

  <!-- REASON BANNER -->
  <div id="reasonBanner" style="display:none;margin:10px 14px 0;padding:13px 16px;border-radius:14px;line-height:1.6"></div>

  <!-- 1. PACKAGES -->
  <div class="sec-lbl"><i class="fa fa-box-open" style="color:var(--accent)"></i> CHAGUA PACKAGE</div>
  <div class="pkgs">
    <div class="pkg" id="pkg0" onclick="selPkg(0,'Wiki 1',1000,7)">
      <div class="pkg-chk">✓</div>
      <div class="pkg-name">Wiki 1</div>
      <div class="pkg-price"><sup>TSh</sup>1,000<small>/wiki</small></div>
      <div class="pkg-perks">
        <div class="perk"><i class="fa fa-check"></i>Channels zote</div>
        <div class="perk"><i class="fa fa-check"></i>HD Quality</div>
      </div>
    </div>
    <div class="pkg hot" id="pkg1" onclick="selPkg(1,'Mwezi 1',3000,30)">
      <div class="pkg-badge">★ MAARUFU</div>
      <div class="pkg-chk">✓</div>
      <div class="pkg-name">Mwezi 1</div>
      <div class="pkg-price"><sup>TSh</sup>3,000<small>/mwezi</small></div>
      <div class="pkg-perks">
        <div class="perk"><i class="fa fa-check"></i>Channels zote</div>
        <div class="perk"><i class="fa fa-check"></i>HD + FHD Quality</div>
      </div>
    </div>
    <div class="pkg" id="pkg2" onclick="selPkg(2,'Miezi 3',8000,90)">
      <div class="pkg-chk">✓</div>
      <div class="pkg-name">Miezi 3</div>
      <div class="pkg-price"><sup>TSh</sup>8,000<small>/m3</small></div>
      <div class="pkg-perks">
        <div class="perk"><i class="fa fa-check"></i>Channels zote</div>
        <div class="perk"><i class="fa fa-check"></i>FHD Quality</div>
      </div>
    </div>
    <div class="pkg" id="pkg3" onclick="selPkg(3,'Miezi 6',15000,180)">
      <div class="pkg-chk">✓</div>
      <div class="pkg-name">Miezi 6</div>
      <div class="pkg-price"><sup>TSh</sup>15,000<small>/m6</small></div>
      <div class="pkg-perks">
        <div class="perk"><i class="fa fa-check"></i>Channels zote</div>
        <div class="perk"><i class="fa fa-check"></i>FHD Quality</div>
      </div>
    </div>
    <div class="pkg" id="pkg4" onclick="selPkg(4,'Mwaka 1',25000,365)" style="grid-column:1/-1">
      <div class="pkg-chk">✓</div>
      <div class="pkg-name">🏆 Mwaka 1</div>
      <div class="pkg-price"><sup>TSh</sup>25,000<small>/mwaka</small></div>
      <div class="pkg-perks" style="flex-direction:row;flex-wrap:wrap;gap:10px">
        <div class="perk"><i class="fa fa-check"></i>Channels zote</div>
        <div class="perk"><i class="fa fa-check"></i>4K Quality</div>
        <div class="perk"><i class="fa fa-check"></i>Thamani Bora</div>
      </div>
    </div>
  </div>

  <!-- 2. NJIA YA MALIPO -->
  <div class="sec-lbl"><i class="fa fa-mobile-screen-button" style="color:var(--accent)"></i> NJIA YA MALIPO</div>
  <div class="methods">
    <div class="mth" id="mth-mpesa"  onclick="selMth('mpesa')"><div class="mth-icon">💚</div><span>M-Pesa</span></div>
    <div class="mth" id="mth-tigo"   onclick="selMth('tigo')"><div class="mth-icon">💙</div><span>Tigo Pesa</span></div>
    <div class="mth sel" id="mth-airtel" onclick="selMth('airtel')"><div class="mth-icon">❤️</div><span>Airtel</span></div>
    <div class="mth" id="mth-halo"   onclick="selMth('halo')"><div class="mth-icon">🟣</div><span>Halo Pesa</span></div>
  </div>

  <!-- 3. HATUA -->
  <div class="sec-lbl" style="margin-top:6px"><i class="fa fa-list-ol" style="color:var(--accent)"></i> HATUA ZA MALIPO</div>
  <div class="steps">

    <!-- STEP 1: Tuma pesa -->
    <div class="step">
      <div class="step-hdr">
        <div class="step-num">1</div>
        <div>
          <div class="step-title" id="s1Title">Tuma Pesa</div>
          <div class="step-sub"   id="s1Sub">Chagua package na njia ya malipo kwanza</div>
        </div>
      </div>

      <div class="amt-banner" id="amtBanner">
        <div class="amt-lbl">Kiasi cha kulipa</div>
        <div class="amt-val" id="amtVal">TSh —</div>
      </div>

      <div id="paySection">
        <!-- Namba ya kulipa -->
        <div class="pay-num" id="payNumRow">
          <div>
            <div class="pn-lbl" id="numLabel">Namba ya Airtel Money</div>
            <div class="pn-val" id="payNum">0695 753 176</div>
            <div style="font-size:12px;font-weight:700;color:var(--text);margin-top:3px" id="payName">JUMANNE HASSAN NDANGE</div>
          </div>
          <button class="copy-btn" id="copyBtn" onclick="copyNum()">NAKILI <i class="fa fa-copy"></i></button>
        </div>

        <!-- WhatsApp link (kwa njia za WA) -->
        <a class="wa-row" id="waPayRow" href="#" target="_blank" style="display:none">
          <div class="wa-icon">💬</div>
          <div>
            <div class="wa-lbl">Au tuma pesa kwa WhatsApp</div>
            <div class="wa-num" id="waPayNum">0695 753 176</div>
          </div>
          <i class="fa fa-arrow-right" style="color:#25d366;margin-left:auto;font-size:13px"></i>
        </a>
      </div>

      <div id="noSelMsg" style="display:none;text-align:center;padding:10px 0;font-size:12px;color:var(--muted)">
        ☝️ Chagua package na njia ya malipo hapo juu
      </div>
    </div>

    <!-- STEP 2: Jaza fomu -->
    <div class="step">
      <div class="step-hdr">
        <div class="step-num">2</div>
        <div>
          <div class="step-title">Jaza Taarifa za Malipo</div>
          <div class="step-sub">Baada ya kutuma pesa, jaza fomu hii na utume</div>
        </div>
      </div>

      <!-- Summary ya chaguo -->
      <div class="confirm-box" id="confirmBox">
        <div class="cf-row"><span class="cf-lbl">Package</span><span class="cf-val green" id="cfPkg">—</span></div>
        <div class="cf-row"><span class="cf-lbl">Njia</span><span class="cf-val" id="cfMth">—</span></div>
        <div class="cf-row"><span class="cf-lbl">Kiasi</span><span class="cf-val gold" id="cfAmt">—</span></div>
        <div class="cf-row"><span class="cf-lbl">Muda</span><span class="cf-val" id="cfDays">—</span></div>
      </div>

      <div class="form-group">
        <label><i class="fa fa-phone" style="color:var(--accent);margin-right:4px"></i> Namba Uliyotumia Kulipa <span style="color:var(--accent2)">*</span></label>
        <input class="form-inp" id="fPhone" type="tel" placeholder="0712 345 678" maxlength="15" inputmode="tel">
      </div>

      <div class="form-group">
        <label><i class="fa fa-receipt" style="color:var(--accent);margin-right:4px"></i> Transaction ID / Namba ya Muamala <span style="color:var(--accent2)">*</span></label>
        <input class="form-inp" id="fRef" type="text" placeholder="Mfano: CI25HJK8M3" maxlength="50" autocomplete="off" style="text-transform:uppercase" oninput="this.value=this.value.toUpperCase()">
        <div class="hint"><i class="fa fa-info-circle" style="color:var(--accent)"></i> Itaonekana kwenye SMS ya uthibitisho baada ya kulipa</div>
      </div>

      <div class="form-group">
        <label><i class="fa fa-comment-dots" style="color:var(--accent);margin-right:4px"></i> Maelezo ya Ziada (Hiari)</label>
        <input class="form-inp" id="fNotes" type="text" placeholder="Mfano: Nililipa saa 3 usiku..." maxlength="200">
      </div>

      <button class="submit-btn" id="submitBtn" onclick="doSubmit()">
        <span class="btxt"><i class="fa fa-paper-plane"></i> TUMA OMBI LA MALIPO</span>
        <div class="bspin"></div>
      </button>
    </div>

  </div><!-- /steps -->

  <!-- HISTORY -->
  <div id="histSection" style="display:none">
    <div class="sec-lbl"><i class="fa fa-clock-rotate-left" style="color:var(--accent)"></i> HISTORIA YA MALIPO</div>
    <div class="hist-wrap">
      <div id="histNotice"></div>
      <div class="hist-card" id="histList"></div>
    </div>
  </div>

</div><!-- /mainForm -->

<!-- ══ SUCCESS SCREEN ══ -->
<div class="success-screen" id="successScreen">
  <span class="suc-icon">✅</span>
  <div class="suc-title">OMBI LIMETUMWA!</div>
  <p class="suc-msg">
    Malipo yako yamepokelewa na yatakaguliwa hivi karibuni.<br>
    <strong style="color:var(--text)">Kawaida ndani ya dakika 5–15.</strong>
  </p>

  <div class="suc-id" id="sucIdBox">
    <div class="suc-id-lbl">Namba ya Ombi</div>
    <div class="suc-id-val" id="sucIdVal">—</div>
  </div>

  <!-- ★ SEHEMU YA KUTUMA UTHIBITISHO WA MUAMALA KWA ADMIN ★ -->
  <a class="wa-confirm" id="waAdminBtn" href="#" target="_blank">
    <i class="fa-brands fa-whatsapp" style="font-size:26px;flex-shrink:0"></i>
    <div class="wa-confirm-info">
      <div class="wa-confirm-title">Tuma Uthibitisho kwa Admin</div>
      <div class="wa-confirm-sub">Tuma namba ya muamala wako kwa WhatsApp</div>
    </div>
    <i class="fa fa-arrow-right" style="margin-left:auto;opacity:.7"></i>
  </a>

  <div class="pending-notice">
    <strong>⏳ Inakaguliwa...</strong><br>
    Utapata notification mara tu unaposubiriwa. Kama hujapata jibu baada ya dakika 30, wasiliana nasi kwa WhatsApp
    <strong style="color:#25d366">0616 393 956</strong>.
  </div>

  <button class="home-btn" onclick="location.href='home.html'">
    <i class="fa fa-home"></i> Rudi Nyumbani
  </button>
</div>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item"><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item active"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script>
// ════════════════════════════════════════════════════
//  JAYNES MAX TV — malipo.php v3
// ════════════════════════════════════════════════════

const SB_URL   = 'https://dablnrggyfcddmdeiqxi.supabase.co';
const SB_KEY   = 'sb_publishable_d8mzJ3iulCU7YdlV_lrdQw_32pOzDXc';
const ADMIN_WA = '255616393956';

// ── NJIA ZA MALIPO ────────────────────────────────
const MTH = {
  mpesa:  { num:'0695753176', name:'JUMANNE HASSAN NDANGE', label:'M-Pesa (WhatsApp)',    icon:'💚', wa:true  },
  tigo:   { num:'0695753176', name:'JUMANNE HASSAN NDANGE', label:'Tigo Pesa (WhatsApp)', icon:'💙', wa:true  },
  airtel: { num:'0695753176', name:'JUMANNE HASSAN NDANGE', label:'Airtel Money',          icon:'❤️', wa:false },
  halo:   { num:'0695753176', name:'JUMANNE HASSAN NDANGE', label:'Halo Pesa (WhatsApp)', icon:'🟣', wa:true  },
};

// ── STATE ─────────────────────────────────────────
let sPkgIdx = -1, sMth = 'airtel';
let sPkgName = '', sPkgAmt = 0, sPkgDays = 0;

// ── INIT ──────────────────────────────────────────
(function() {
  selMth('airtel');
  loadHistory();

  // URL params
  const p   = new URLSearchParams(location.search);
  const pkg = (p.get('pkg') || '').toLowerCase();
  const pkgMap = {
    wiki1:[0,'Wiki 1',1000,7], 'wiki 1':[0,'Wiki 1',1000,7],
    mwezi1:[1,'Mwezi 1',3000,30], 'mwezi 1':[1,'Mwezi 1',3000,30],
    miezi3:[2,'Miezi 3',8000,90], 'miezi 3':[2,'Miezi 3',8000,90],
    miezi6:[3,'Miezi 6',15000,180], 'miezi 6':[3,'Miezi 6',15000,180],
    mwaka1:[4,'Mwaka 1',25000,365], 'mwaka 1':[4,'Mwaka 1',25000,365],
  };
  if (pkgMap[pkg]) setTimeout(() => selPkg(...pkgMap[pkg]), 80);

  // Reason banner
  const r  = p.get('reason');
  const rb = document.getElementById('reasonBanner');
  const msgs = {
    trial_ended:{ bg:'rgba(255,100,0,.08)', br:'rgba(255,100,0,.2)',   icon:'⏰', html:'<strong style="color:#ff8844">Dakika 30 za majaribio zimekwisha.</strong><br><span style="color:var(--muted);font-size:12px">Lipia sasa ili uendelee kutazama.</span>' },
    sub_ended:  { bg:'rgba(255,215,0,.07)', br:'rgba(255,215,0,.2)',   icon:'👑', html:'<strong style="color:var(--gold)">Subscription yako imekwisha.</strong><br><span style="color:var(--muted);font-size:12px">Renewi sasa usipitiwe na mechi.</span>' },
    no_access:  { bg:'rgba(0,212,255,.06)', br:'rgba(0,212,255,.15)',  icon:'🔒', html:'<strong style="color:var(--accent)">Jiunge JAYNES MAX TV Premium!</strong><br><span style="color:var(--muted);font-size:12px">Chagua package ili uanze kutazama channels zote.</span>' },
  };
  if (msgs[r]) {
    const d = msgs[r];
    rb.style.cssText = `display:flex;align-items:flex-start;gap:10px;margin:10px 14px 0;padding:13px 16px;border-radius:14px;background:${d.bg};border:1px solid ${d.br}`;
    rb.innerHTML = `<span style="font-size:24px;flex-shrink:0;margin-top:2px">${d.icon}</span><div style="line-height:1.6;font-size:13px">${d.html}</div>`;
  }
})();

// ── SELECT PACKAGE ────────────────────────────────
function selPkg(idx, name, amt, days) {
  sPkgIdx = idx; sPkgName = name; sPkgAmt = amt; sPkgDays = days;
  document.querySelectorAll('.pkg').forEach((el, i) => el.classList.toggle('sel', i === idx));
  updateStep1();
}

// ── SELECT METHOD ─────────────────────────────────
function selMth(key) {
  sMth = key;
  document.querySelectorAll('.mth').forEach(el => el.classList.remove('sel'));
  document.getElementById('mth-' + key)?.classList.add('sel');
  updateStep1();
}

// ── UPDATE STEP 1 ─────────────────────────────────
function updateStep1() {
  const m  = MTH[sMth];
  const ok = sPkgIdx >= 0;

  // paySection inaonekana SIKU ZOTE — namba daima inaonekana
  document.getElementById('amtBanner').style.display  = ok ? 'block' : 'none';
  document.getElementById('confirmBox').style.display = ok ? 'block' : 'none';

  // Method label na namba ibadilishwe kila wakati (hata kabla ya package)
  const m0 = MTH[sMth];
  document.getElementById('numLabel').textContent = m0.label;
  document.getElementById('payNum').textContent   = fmt(m0.num);
  document.getElementById('payName').textContent  = m0.name;
  // WA row
  const waRow0 = document.getElementById('waPayRow');
  if (m0.wa) {
    document.getElementById('waPayNum').textContent = fmt(m0.num);
    waRow0.style.display = 'flex';
  } else {
    waRow0.style.display = 'none';
  }

  if (!ok) return;

  // Amount
  document.getElementById('amtVal').textContent = 'TSh ' + sPkgAmt.toLocaleString();

  // Step 1 header
  document.getElementById('s1Title').textContent = `Tuma ${m.icon} TSh ${sPkgAmt.toLocaleString()} kwa ${m.label}`;
  document.getElementById('s1Sub').textContent   = m.wa ? 'Tuma kupitia WhatsApp au number moja kwa moja' : 'Tuma moja kwa moja kwa namba hii';

  // Number
  document.getElementById('numLabel').textContent = m.label;
  document.getElementById('payNum').textContent   = fmt(m.num);
  document.getElementById('payName').textContent  = m.name;

  // WA pay link
  const waRow = document.getElementById('waPayRow');
  if (m.wa) {
    const msg = `Habari! Nataka kulipa JAYNES MAX TV\n\n📦 Package: *${sPkgName}*\n💰 Kiasi: *TSh ${sPkgAmt.toLocaleString()}*\n📅 Muda: *Siku ${sPkgDays}*\n📞 Namba yangu: ${localStorage.getItem('jaynesPhone')||'—'}`;
    // WA row inaelekeza kwa namba ya KULIPA (0695), si admin
    const waPayNum = m.num.startsWith('0') ? '255' + m.num.slice(1) : m.num;
    waRow.href = `https://wa.me/${waPayNum}?text=${encodeURIComponent(msg)}`;
    document.getElementById('waPayNum').textContent = fmt(m.num);
    waRow.style.display = 'flex';
  } else {
    waRow.style.display = 'none';
  }

  // Confirm box
  document.getElementById('cfPkg').textContent  = sPkgName;
  document.getElementById('cfMth').textContent  = `${m.icon} ${m.label}`;
  document.getElementById('cfAmt').textContent  = 'TSh ' + sPkgAmt.toLocaleString();
  document.getElementById('cfDays').textContent = `Siku ${sPkgDays}`;
}

// ── COPY NUMBER ───────────────────────────────────
function copyNum() {
  const num = MTH[sMth]?.num || '';
  const btn = document.getElementById('copyBtn');
  navigator.clipboard?.writeText(num).then(() => {
    btn.classList.add('copied'); btn.innerHTML = 'IMENAKILIWA ✓';
    setTimeout(() => { btn.classList.remove('copied'); btn.innerHTML = 'NAKILI <i class="fa fa-copy"></i>'; }, 2500);
  }).catch(() => toast('📋 ' + fmt(num)));
}

// ── SUBMIT ────────────────────────────────────────
async function doSubmit() {
  const phone = document.getElementById('fPhone').value.trim();
  const ref   = document.getElementById('fRef').value.trim().toUpperCase();
  const notes = document.getElementById('fNotes').value.trim();

  // Clear errors
  ['fPhone','fRef'].forEach(id => document.getElementById(id).classList.remove('err'));

  if (sPkgIdx < 0) { toast('❌ Chagua package kwanza'); return; }
  if (!phone)      { document.getElementById('fPhone').classList.add('err'); toast('❌ Weka namba uliyotumia kulipa'); return; }
  if (!ref)        { document.getElementById('fRef').classList.add('err');   toast('❌ Weka Transaction ID ya muamala'); return; }
  if (ref.length < 4){ document.getElementById('fRef').classList.add('err'); toast('❌ Transaction ID ni fupi sana'); return; }

  const btn = document.getElementById('submitBtn');
  btn.disabled = true; btn.classList.add('loading');

  try {
    const uid   = localStorage.getItem('jaynesUid')   || '';
    const email = localStorage.getItem('jaynesEmail') || '';

    const res  = await fetch('pay_submit.php', {
      method:  'POST',
      headers: { 'Content-Type': 'application/json' },
      body:    JSON.stringify({
        user_id: uid || null, email: email || null,
        phone, reference: ref, package: sPkgName,
        method: sMth, amount: sPkgAmt, days: sPkgDays,
        notes: notes || null,
      }),
    });
    const data = await res.json();
    if (!data.success) throw new Error(data.error || 'Tatizo la seva');

    showSuccess(data.id || '', phone, ref);

  } catch(e) {
    toast('❌ ' + e.message, 4000);
    btn.disabled = false; btn.classList.remove('loading');
  }
}

// ── SHOW SUCCESS ──────────────────────────────────
function showSuccess(payId, phone, ref) {
  document.getElementById('mainForm').style.display      = 'none';
  document.getElementById('successScreen').style.display = 'block';
  document.getElementById('successScreen').classList.add('show');

  // ID box
  if (payId) {
    document.getElementById('sucIdBox').style.display = 'block';
    document.getElementById('sucIdVal').textContent   = payId;
  }

  // ★ Build WhatsApp confirmation message kwa admin
  const email   = localStorage.getItem('jaynesEmail') || '—';
  const mthInfo = MTH[sMth] || {};
  const waMsg   = [
    `🧾 *UTHIBITISHO WA MALIPO — JAYNES MAX TV*`,
    ``,
    `📦 Package:      *${sPkgName}*`,
    `💰 Kiasi:        *TSh ${sPkgAmt.toLocaleString()}*`,
    `📅 Muda:         *Siku ${sPkgDays}*`,
    `💳 Njia:         *${mthInfo.label || sMth}*`,
    `📞 Namba Ilitumia: *${phone}*`,
    `🔑 Transaction ID: *${ref}*`,
    `📧 Email:        *${email}*`,
    payId ? `🆔 Ombi ID: *${payId}*` : '',
    ``,
    `✅ Tafadhali thibitisha malipo haya.`,
  ].filter(Boolean).join('\n');

  document.getElementById('waAdminBtn').href = `https://wa.me/${ADMIN_WA}?text=${encodeURIComponent(waMsg)}`;
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── LOAD HISTORY ──────────────────────────────────
async function loadHistory() {
  const email = localStorage.getItem('jaynesEmail');
  const uid   = localStorage.getItem('jaynesUid');
  if (!email && !uid) return;

  try {
    const filter = uid
      ? `user_id=eq.${encodeURIComponent(uid)}`
      : `email=eq.${encodeURIComponent(email)}`;

    const res  = await fetch(`${SB_URL}/rest/v1/payments?${filter}&select=id,package,amount,method,status,created_at,admin_note&order=created_at.desc&limit=10`, {
      headers: { 'apikey': SB_KEY, 'Authorization': 'Bearer ' + SB_KEY }
    });
    const rows = await res.json();
    if (!Array.isArray(rows) || !rows.length) return;

    document.getElementById('histSection').style.display = 'block';

    const labels = { pending:'Inasubiri', approved:'Imekubaliwa', rejected:'Imekataliwa' };
    const icons  = { mpesa:'💚', tigo:'💙', airtel:'❤️', halo:'🟣' };

    // Pending notice
    const hasPending = rows.some(r => r.status === 'pending');
    if (hasPending) {
      document.getElementById('histNotice').innerHTML = `
        <div class="pending-notice">
          <strong>⏳ Una malipo yanayosubiri ukaguzi.</strong><br>
          Admin atayakagua hivi karibuni. Kama imechelewa zaidi ya dakika 30, wasiliana kwa
          <a href="https://wa.me/255616393956" style="color:#25d366;font-weight:700">WhatsApp 0616 393 956</a>.
        </div>`;
    }

    document.getElementById('histList').innerHTML = rows.map(r => {
      const d   = new Date(r.created_at);
      const dt  = d.toLocaleDateString('sw') + ' ' + d.toLocaleTimeString('sw',{hour:'2-digit',minute:'2-digit'});
      const note = (r.admin_note && r.status === 'rejected')
        ? `<div class="h-note"><i class="fa fa-comment-slash" style="margin-right:4px"></i>${esc(r.admin_note)}</div>` : '';
      return `
        <div class="hist-row">
          <span class="hbadge ${r.status||'pending'}">${labels[r.status]||r.status}</span>
          <div class="hinfo">
            <div class="h-pkg">${icons[r.method]||'💳'} ${esc(r.package||'—')}</div>
            <div class="h-meta">${dt}</div>${note}
          </div>
          <div class="h-amt">TSh ${Number(r.amount||0).toLocaleString()}</div>
        </div>`;
    }).join('');
  } catch(e) { /* Historia ni optional */ }
}

// ── HELPERS ───────────────────────────────────────
function fmt(num) {
  const n = (num||'').replace(/\D/g,'');
  return n.length===10 ? n.replace(/(\d{4})(\d{3})(\d{3})/,'$1 $2 $3') : num;
}
function toast(msg, dur=3000) {
  const el = document.getElementById('toast');
  el.textContent = msg; el.classList.add('show');
  clearTimeout(el._t); el._t = setTimeout(() => el.classList.remove('show'), dur);
}
function esc(s) {
  const d = document.createElement('div');
  d.textContent = s||''; return d.innerHTML;
}
</script>
</body>
</html>
