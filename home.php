<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>JAYNES MAX TV — Global TV Pro</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
<link rel="stylesheet" href="style.css">

<link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;600;700&display=swap" rel="stylesheet">
<style>
/* variables from style.css */

* { margin: 0; padding: 0; box-sizing: border-box; }

body {
  font-family: 'Outfit', sans-serif;
  background: var(--bg);
  color: var(--text);
  min-height: 100vh;
  padding-top: 120px;
  padding-bottom: 80px;
  background-image: radial-gradient(ellipse at 20% 20%, rgba(168,85,247,0.05) 0%, transparent 50%),
                    radial-gradient(ellipse at 80% 80%, rgba(245,158,11,0.03) 0%, transparent 50%);
}

.topbar {
  position: fixed;
  top: 0; left: 0; right: 0;
  z-index: 100;
  background: rgba(7,7,20,0.96);
  backdrop-filter: blur(20px);
  border-bottom: 1px solid var(--border);
  padding: 10px 16px;
}

.brand {
  display: flex;
  align-items: center;
  gap: 10px;
  margin-bottom: 10px;
}

.back-btn {
  background: rgba(168,85,247,0.1);
  border: 1px solid var(--border);
  border-radius: 10px;
  color: var(--accent);
  width: 36px; height: 36px;
  display: flex; align-items: center; justify-content: center;
  cursor: pointer; text-decoration: none;
  transition: all 0.3s;
}

.back-btn:hover { background: rgba(168,85,247,0.2); }

.logo {
  font-family: 'Bebas Neue', sans-serif;
  font-size: 22px;
  letter-spacing: 3px;
  background: linear-gradient(135deg, var(--accent), var(--accent2));
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

.search-bar {
  display: flex;
  background: rgba(255,255,255,0.04);
  border: 1px solid var(--border);
  border-radius: 10px;
  overflow: hidden;
  transition: all 0.3s;
}

.search-bar:focus-within {
  border-color: var(--accent);
  box-shadow: 0 0 0 3px rgba(168,85,247,0.1);
}

.search-bar input {
  flex: 1;
  padding: 10px 14px;
  background: transparent;
  border: none; outline: none;
  color: var(--text); font-size: 13px;
  font-family: 'Outfit', sans-serif;
}

.search-bar input::placeholder { color: var(--muted); }

.search-bar button {
  padding: 0 14px;
  background: var(--accent);
  border: none; color: #fff;
  font-weight: 700; font-size: 13px;
  cursor: pointer; transition: background 0.2s;
}

.search-bar button:hover { background: #9333ea; }

/* GRID */
.grid {
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 12px;
  padding: 12px 12px 14px;
}

.card {
  background: var(--card);
  border: 1px solid rgba(255,255,255,0.06);
  border-radius: 14px;
  overflow: hidden;
  cursor: pointer;
  position: relative;
  transition: all 0.3s;
}

.card:hover {
  border-color: var(--accent);
  box-shadow: var(--glow);
  transform: translateY(-5px);
}

.card img {
  width: 100%;
  height: 130px;
  object-fit: cover;
  display: block;
  transition: transform 0.4s;
}

.card:hover img { transform: scale(1.06); }

.card-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(to top, rgba(0,0,0,0.85) 0%, transparent 55%);
  opacity: 0;
  transition: opacity 0.3s;
  display: flex;
  align-items: center;
  justify-content: center;
}

.card:hover .card-overlay { opacity: 1; }
.card-overlay i { font-size: 32px; color: var(--accent); }

.card-info {
  padding: 10px 12px;
}

.card-name {
  font-size: 13px;
  font-weight: 700;
  color: var(--text);
  margin-bottom: 3px;
}

.card-drm {
  font-size: 11px;
  color: var(--muted);
}

.drm-badge {
  display: inline-block;
  background: rgba(255,215,0,0.15);
  border: 1px solid rgba(255,215,0,0.3);
  color: #ffd700;
  font-size: 9px;
  font-weight: 700;
  padding: 2px 8px;
  border-radius: 4px;
  margin-top: 2px;
}

/* COUNTER */
.count-bar {
  text-align: center;
  padding: 6px;
  font-size: 12px;
  color: var(--muted);
  background: rgba(168,85,247,0.05);
  border-bottom: 1px solid var(--border);
}

.count-bar strong { color: var(--accent); }

.spinner {
  width: 44px; height: 44px;
  border: 3px solid rgba(168,85,247,0.1);
  border-top-color: var(--accent);
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 40px auto;
}

@keyframes spin { to { transform: rotate(360deg); } }

.error-box {
  margin: 20px;
  padding: 24px;
  background: rgba(255,68,102,0.08);
  border: 1px solid rgba(255,68,102,0.2);
  border-radius: 14px;
  text-align: center;
  color: #ff6688;
  display: none;
}

.retry-btn {
  margin-top: 12px;
  padding: 9px 20px;
  background: var(--accent);
  border: none; border-radius: 8px;
  color: #fff; font-weight: 700;
  cursor: pointer; font-family: 'Outfit', sans-serif;
  display: block; transition: all 0.3s;
}

.retry-btn:hover { transform: scale(1.05); }

.bottom-nav {
  position: fixed;
  bottom: 0; left: 0; right: 0;
  height: 68px;
  background: rgba(7,7,20,0.97);
  backdrop-filter: blur(20px);
  border-top: 1px solid var(--border);
  display: flex; justify-content: space-around; align-items: center;
  z-index: 1000;
}

.nav-item {
  display: flex; flex-direction: column; align-items: center;
  gap: 4px; cursor: pointer; padding: 6px 16px;
  border-radius: 12px; transition: all 0.3s; text-decoration: none;
}

.nav-item i { font-size: 20px; color: var(--muted); transition: all 0.3s; }
.nav-item span { font-size: 10px; color: var(--muted); font-weight: 600; }
.nav-item.active i, .nav-item:hover i { color: var(--accent); }
.nav-item.active span, .nav-item:hover span { color: var(--accent); }
.nav-item.active { background: rgba(168,85,247,0.08); }
</style>
</head>
<body>

<!-- TOP BAR -->
<div class="topbar">
  <div class="brand">
    <a href="javascript:history.back()" class="back-btn"><i class="fa fa-arrow-left"></i></a>
    <div class="logo">🌍 GLOBAL TV PRO</div>
  </div>
  <div class="search-bar">
    <input type="text" id="searchInput" placeholder="Search channels worldwide...">
    <button id="searchBtn"><i class="fa fa-search"></i></button>
  </div>
  <a id="globalSearchBtn" href="search.php" style="display:none;flex-shrink:0;margin-left:7px;padding:0 12px;height:38px;background:rgba(168,85,247,0.1);border:1px solid rgba(168,85,247,0.3);border-radius:10px;color:#a855f7;font-size:11px;font-weight:700;align-items:center;gap:5px;white-space:nowrap;text-decoration:none;">
    <i class="fa fa-border-all"></i> Zote
  </a>
</div>

<!-- COUNT BAR -->
<div class="count-bar" id="countBar">Inapakia channels...</div>

<!-- SPINNER -->
<div class="spinner" id="spinner"></div>

<!-- GRID -->
<div class="grid" id="grid"></div>

<!-- ERROR -->
<div class="error-box" id="errorBox">
  <i class="fa fa-wifi" style="font-size:28px;margin-bottom:10px;display:block"></i>
  Hakuna mtandao au data haipatikani
  <button class="retry-btn" onclick="location.reload()">Try Again</button>
</div>

<!-- BOTTOM NAV -->
<nav class="bottom-nav">
  <a href="home.html"    class="nav-item"><i class="fa fa-home"></i><span>Nyumbani</span></a>
  <a href="live.php"     class="nav-item "><i class="fa fa-circle-play"></i><span>Live</span></a>
  <a href="schedule.php" class="nav-item "><i class="fa fa-calendar-days"></i><span>Ratiba</span></a>
  <a href="malipo.php"   class="nav-item"><i class="fa fa-credit-card"></i><span>Malipo</span></a>
  <a href="account.php"  class="nav-item"><i class="fa fa-user"></i><span>Akaunti</span></a>
</nav>
<script src="_cache.js"></script>
<script src="_auth_guard.js"></script>
<script src="_channel_guard.js"></script>
<script src="_channel_popup.js"></script>
<script>
const grid = document.getElementById('grid');
const spinner = document.getElementById('spinner');
const errorBox = document.getElementById('errorBox');
const countBar = document.getElementById('countBar');
const searchInput = document.getElementById('searchInput');
let allChannels = [], loadedCount = 0, originalChannels = [];
const batchSize = 20;

async function loadChannels() {
  await fetchWithCache(
    'global_channels',
    async () => {
      const r    = await fetch('key.php');
      const data = await r.json();
      if (!data.success || !data.channels?.length) throw new Error('empty');
      return data.channels;
    },
    (channels) => {
      spinner.style.display = 'none';
      allChannels = channels;
      originalChannels = [...allChannels];
      loadedCount = 0;
      grid.innerHTML = '';
      countBar.innerHTML = `Channels <strong>${allChannels.length}</strong> zimepatikana`;
      loadMore();
    },
    () => { /* spinner inaonekana tayari */ },
    () => {
      spinner.style.display = 'none';
      errorBox.style.display = 'block';
    }
  );
}

function loadMore() {
  const batch = allChannels.slice(loadedCount, loadedCount + batchSize);
  batch.forEach(ch => {
    const card = document.createElement('div');
    card.className = 'card';
    card.innerHTML = `
      <img src="${ch.image || ''}" alt="${ch.name}" loading="lazy"
           onerror="this.src='https://via.placeholder.com/200x130/0e0e22/a855f7?text=📺'">
      <div class="card-overlay"><i class="fa fa-play"></i></div>
      ${ch.key ? `<div style="position:absolute;top:6px;right:6px;background:rgba(255,215,0,0.9);color:#000;font-size:8px;font-weight:700;padding:2px 7px;border-radius:4px;display:flex;align-items:center;gap:3px;z-index:3"><i class="fa fa-crown" style="font-size:7px"></i> PREMIUM</div>` : ''}
      <div class="card-info">
        <div class="card-name">${ch.name}</div>
      </div>`;
    card.addEventListener('click', () => {
      const playerUrl = `player.php?url=${encodeURIComponent(ch.url)}&key=${encodeURIComponent(ch.key||'')}&name=${encodeURIComponent(ch.name)}`;
      if (typeof goChannelFull === 'function') {
        goChannelFull({title:ch.name, name:ch.name, url:ch.url, key:ch.key||'', category:'Global TV', image:ch.image||'', source:'global'}, playerUrl);
      } else {
        window.location.href = playerUrl;
      }
    });
    grid.appendChild(card);
  });
  loadedCount += batch.length;
}

window.addEventListener('scroll', () => {
  if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 300) {
    if (loadedCount < allChannels.length) loadMore();
  }
});

function doSearch() {
  const q = searchInput.value.toLowerCase().trim();
  grid.innerHTML = '';
  loadedCount = 0;
  if (!q) {
    allChannels = [...originalChannels];
  } else {
    allChannels = originalChannels.filter(ch => ch.name.toLowerCase().includes(q));
  }
  countBar.innerHTML = `Channels <strong>${allChannels.length}</strong> ${q ? 'zilizopatikana' : 'zote'}`;
  if (allChannels.length) loadMore();
  else grid.innerHTML = '<div style="text-align:center;color:#8888aa;padding:30px;">No channels found</div>';
}

document.getElementById('searchBtn').addEventListener('click', doSearch);
searchInput.addEventListener('keypress', e => { if(e.key==='Enter') doSearch(); });
searchInput.addEventListener('input', doSearch);

loadChannels();
</script>
<script src="_auth_guard.js"></script>
</body>
</html>
