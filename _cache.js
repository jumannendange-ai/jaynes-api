/**
 * JAYNES MAX TV — _cache.js v2
 * Cache ya data kwenye sessionStorage na localStorage
 *
 * TTL kwa aina tofauti:
 *   - live_channels (mechi za leo)  : dakika 1 (data inabadilika mara kwa mara)
 *   - azam_channels, nbc_channels   : dakika 10 (inabadilika polepole)
 *   - global_channels (key.php)     : dakika 15 (inabadilika polepole sana)
 *   - search_mechi                  : dakika 1
 *   - search_azam, search_nbc       : dakika 10
 *   - search_global                 : dakika 15
 *   - schedule / fixtures           : dakika 2
 */

const CACHE_TTL = {
  'live_channels':   1  * 60 * 1000,  // dakika 1
  'search_mechi':    1  * 60 * 1000,
  'azam_channels':   10 * 60 * 1000,  // dakika 10
  'nbc_channels':    10 * 60 * 1000,
  'search_azam':     10 * 60 * 1000,
  'search_nbc':      10 * 60 * 1000,
  'global_channels': 15 * 60 * 1000,  // dakika 15
  'search_global':   15 * 60 * 1000,
  'schedule':         2 * 60 * 1000,  // dakika 2
  'local2_state':    5 * 60 * 1000,   // dakika 5
  'default':          3 * 60 * 1000,  // dakika 3 (kwa zingine zote)
};

const JMCache = (() => {

  function getTTL(name) {
    // Angalia mechi exact, au prefix match
    for (const [key, ttl] of Object.entries(CACHE_TTL)) {
      if (key === 'default') continue;
      if (name === key || name.startsWith(key)) return ttl;
    }
    return CACHE_TTL['default'];
  }

  function cacheKey(name) { return 'jmtv_cache_' + name; }

  // Tumia sessionStorage kwa default, localStorage kwa channels za global/azam/nbc
  function getStorage(name) {
    const longCache = ['global_channels','azam_channels','nbc_channels','search_global','search_azam','search_nbc'];
    return longCache.some(k => name.startsWith(k)) ? localStorage : sessionStorage;
  }

  function set(name, data) {
    try {
      const storage = getStorage(name);
      storage.setItem(cacheKey(name), JSON.stringify({
        ts:   Date.now(),
        ttl:  getTTL(name),
        data: data,
      }));
    } catch(e) {
      // Storage imejaa — futa cache za zamani na jaribu tena
      try { clearOld(); getStorage(name).setItem(cacheKey(name), JSON.stringify({ts:Date.now(),ttl:getTTL(name),data})); }
      catch { /* ignore */ }
    }
  }

  function get(name) {
    try {
      // Angalia sessionStorage kwanza, kisha localStorage
      let raw = sessionStorage.getItem(cacheKey(name));
      if (!raw) raw = localStorage.getItem(cacheKey(name));
      if (!raw) return null;

      const obj = JSON.parse(raw);
      if (!obj || !obj.ts || obj.data === undefined) return null;

      const ttl = obj.ttl || getTTL(name);
      if (Date.now() - obj.ts > ttl) {
        // Imekwisha — futa
        sessionStorage.removeItem(cacheKey(name));
        localStorage.removeItem(cacheKey(name));
        return null;
      }
      return obj.data;
    } catch { return null; }
  }

  function clear(name) {
    try { sessionStorage.removeItem(cacheKey(name)); } catch {}
    try { localStorage.removeItem(cacheKey(name));   } catch {}
  }

  function clearAll() {
    try {
      [sessionStorage, localStorage].forEach(s => {
        Object.keys(s)
          .filter(k => k.startsWith('jmtv_cache_'))
          .forEach(k => s.removeItem(k));
      });
    } catch {}
  }

  // Futa cache zilizokwisha tu
  function clearOld() {
    try {
      [sessionStorage, localStorage].forEach(s => {
        Object.keys(s)
          .filter(k => k.startsWith('jmtv_cache_'))
          .forEach(k => {
            try {
              const obj = JSON.parse(s.getItem(k));
              const ttl = obj?.ttl || CACHE_TTL['default'];
              if (obj && Date.now() - obj.ts > ttl) s.removeItem(k);
            } catch { s.removeItem(k); }
          });
      });
    } catch {}
  }

  function has(name) { return get(name) !== null; }

  function ttlLeft(name) {
    try {
      let raw = sessionStorage.getItem(cacheKey(name));
      if (!raw) raw = localStorage.getItem(cacheKey(name));
      if (!raw) return 0;
      const obj  = JSON.parse(raw);
      const ttl  = obj?.ttl || getTTL(name);
      const left = ttl - (Date.now() - (obj?.ts || 0));
      return Math.max(0, Math.round(left / 1000));
    } catch { return 0; }
  }

  // Futa cache za zamani mara moja ukurasa unapofunguka
  clearOld();

  return { set, get, has, clear, clearAll, clearOld, ttlLeft, TTL: CACHE_TTL };
})();

/**
 * fetchWithCache(cacheKey, fetchFn, onData, onLoading, onError)
 */
async function fetchWithCache(cacheKey, fetchFn, onData, onLoading, onError) {
  const cached = JMCache.get(cacheKey);
  if (cached) {
    onData(cached, true);
    return;
  }
  if (onLoading) onLoading();
  try {
    const data = await fetchFn();
    JMCache.set(cacheKey, data);
    onData(data, false);
  } catch(err) {
    if (onError) onError(err);
  }
}
