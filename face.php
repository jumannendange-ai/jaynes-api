<!DOCTYPE html>
<html lang="sw">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Azam Channels</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">
<style>
/* Topbar ya pili (search row) */
.topbar { flex-wrap:wrap; height:auto; padding:10px 14px; gap:8px; }
.topbar-row2 { width:100%; display:flex; gap:8px; padding-bottom:2px; }
body { padding-top:106px; }
</style>
</head>
<body>
<div class="toast" id="toast"></div>

<div class="topbar">
  <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
  <div class="page-title">AZAM <span>CHANNELS</span></div>
  <span class="badge badge-live" style="font-size:10px;padding:4px 10px">● LIVE</span>
  <div class="topbar-row2">
    <div class="search-bar" style="flex:1">
      <input type="text" id="searchInput" placeholder="Tafuta channels za Azam...">
      <button id="searchBtn"><i class="fa fa-search"></i></button>
    </div>
    <a id="globalSearchBtn" href="search.php" style="display:none;margin-left:7px;flex-shrink:0;padding:0 12px;height:38px;background:rgba(255,107,53,0.1);border:1px solid rgba(255,107,53,0.3);border-radius:10px;color:#ff6b35;font-size:11px;font-weight:700;align-items:center;gap:5px;white-space:nowrap;text-decoration:none;">
      <i class="fa fa-border-all"></i> Zote
    </a>
  </div>
</div>

<div class="grid-2" id="grid" style="padding-top:4px">
  <div style="grid-column:1/-1" class="load-state">
    <div class="spinner"></div>
    Inapakia channels za Azam...
  </div>
</div>

<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item"><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="face.html"    class="nav-item active"><i class="fa fa-satellite-dish"></i><span>Azam</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>

<script src="_auth_guard.js"></script>
<script src="_cache.js"></script>
<script src="_channel_guard.js"></script>
<script>
const grid=document.getElementById('grid');
const searchInput=document.getElementById('searchInput');
let allChannels=[],loadedCount=0;
const BATCH=20;

async function loadChannels(query='') {
  // Tafuta — usitumie cache, fanya fetch upya
  if (query) {
    grid.innerHTML=`<div style="grid-column:1/-1" class="load-state"><div class="spinner"></div>Inapakia...</div>`;
    try {
      const res=await fetch('azam.php?q='+encodeURIComponent(query));
      const data=await res.json();
      if(!data.success||!data.channels.length) throw new Error('empty');
      allChannels=data.channels; loadedCount=0; grid.innerHTML=''; renderMore();
    } catch(e) {
      grid.innerHTML=`<div class="error-box" style="grid-column:1/-1"><i class="fa fa-wifi"></i>Hakuna channels. Angalia mtandao.<br><button class="retry-btn" onclick="loadChannels()">Jaribu Tena</button></div>`;
    }
    return;
  }

  await fetchWithCache(
    'azam_channels',
    async () => {
      const res=await fetch('azam.php');
      const data=await res.json();
      if(!data.success||!data.channels.length) throw new Error('empty');
      return data.channels;
    },
    (channels) => {
      allChannels=channels; loadedCount=0; grid.innerHTML=''; renderMore();
    },
    () => {
      grid.innerHTML=`<div style="grid-column:1/-1" class="load-state"><div class="spinner"></div>Inapakia channels...</div>`;
    },
    () => {
      grid.innerHTML=`<div class="error-box" style="grid-column:1/-1"><i class="fa fa-wifi"></i>Hakuna channels. Angalia mtandao.<br><button class="retry-btn" onclick="loadChannels()">Jaribu Tena</button></div>`;
    }
  );
}

function renderMore() {
  const batch=allChannels.slice(loadedCount,loadedCount+BATCH);
  batch.forEach((ch,bi)=>{
    // Category heading
    const catId='cat-'+ch.category;
    if(!document.getElementById(catId)){
      const h=document.createElement('div');
      h.className='cat-heading'; h.id=catId; h.textContent=ch.category;
      grid.appendChild(h);
    }
    const card=document.createElement('div');
    card.className='ch-card';
    card.innerHTML=`
      <img src="${ch.image||''}" alt="${ch.name}" loading="lazy" onerror="this.src='https://via.placeholder.com/300x110/101020/00d4ff?text=📺'">
      <div class="card-overlay"></div>
      <div class="live-tag">● LIVE</div>
      <div class="play-icon"><i class="fa fa-play"></i></div>
      <div class="card-title">${ch.name}</div>`;
    card.onclick=()=>{
      const p=new URLSearchParams();
      p.set('url',ch.url); if(ch.key)p.set('key',ch.key); p.set('name',ch.name);
      goChannel(ch.name, 'player.php?'+p.toString());
    };
    applyLockBadge(card, ch.name);
    grid.appendChild(card);
    setTimeout(()=>card.classList.add('visible'),bi*40);
  });
  loadedCount+=batch.length;
}

window.addEventListener('scroll',()=>{
  if(window.innerHeight+window.scrollY>=document.body.offsetHeight-300&&loadedCount<allChannels.length) renderMore();
});

document.getElementById('searchBtn').onclick = () => {
  const q = searchInput.value.trim();
  if (q) loadChannels(q);
};
searchInput.addEventListener('input', () => {
  const q = searchInput.value.trim();
  const btn = document.getElementById('globalSearchBtn');
  if (q) {
    btn.href = 'search.php?q=' + encodeURIComponent(q);
    btn.style.display = 'flex';
  } else {
    btn.style.display = 'none';
    loadChannels();
  }
});
searchInput.addEventListener('keypress', e => {
  if (e.key === 'Enter') {
    const q = searchInput.value.trim();
    if (q) location.href = 'search.php?q=' + encodeURIComponent(q);
  }
});

loadChannels();
</script>
</body>
</html>
