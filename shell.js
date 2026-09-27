/**
 * shell.js — vanilla-JS port of the client-side plumbing that used to live in:
 *   - src/lib/store.ts (useAuth, useUI zustand stores)
 *   - components/layout/TopNav.tsx (theme toggle, search, user menu)
 *   - components/layout/AppLayout.tsx (mobile sidebar open/close)
 *   - components/ui/ToasterClient.tsx (toast notifications)
 * Talks to the same backend endpoints via /api.php/* (see api.php).
 */

// ---------- tiny helpers (port of api.ts's apiFetch, browser-side) ----------
async function sqApiFetch(path, opts = {}) {
  const method = opts.method || 'GET';
  const headers = { Accept: 'application/json' };
  if (opts.body) headers['Content-Type'] = 'application/json';
  const res = await fetch('/api.php' + path, {
    method,
    headers,
    credentials: 'include',
    body: opts.body ? JSON.stringify(opts.body) : undefined,
  });
  return res.json().catch(() => ({ ok: false, error: `HTTP ${res.status}` }));
}

// ---------- theme (port of useUI theme/autoTheme) ----------
function sqApplyTheme(theme) {
  document.documentElement.setAttribute('data-theme', theme);
  try { localStorage.setItem('sq_theme', theme); } catch {}
}
function sqToggleTheme() {
  const current = document.documentElement.getAttribute('data-theme') || 'dark';
  sqApplyTheme(current === 'dark' ? 'light' : 'dark');
}
(function initTheme() {
  let stored = null;
  try { stored = localStorage.getItem('sq_theme'); } catch {}
  if (stored) sqApplyTheme(stored);
  else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: light)').matches) sqApplyTheme('light');
})();

// ---------- mobile sidebar (port of useUI mobileSidebar + AppLayout effect) ----------
function sqToggleMobileSidebar() {
  const overlay = document.getElementById('sq-mobile-sidebar-overlay');
  if (!overlay) return;
  const isHidden = overlay.hasAttribute('hidden');
  if (isHidden) {
    overlay.removeAttribute('hidden');
    document.body.style.overflow = 'hidden';
  } else {
    overlay.setAttribute('hidden', '');
    document.body.style.overflow = '';
  }
}

// ---------- user menu dropdown ----------
function sqToggleUserMenu() {
  const dd = document.getElementById('sq-user-dropdown');
  if (!dd) return;
  dd.hasAttribute('hidden') ? dd.removeAttribute('hidden') : dd.setAttribute('hidden', '');
}
document.addEventListener('click', (e) => {
  const menu = document.getElementById('sq-user-menu');
  const dd = document.getElementById('sq-user-dropdown');
  if (menu && dd && !menu.contains(e.target)) dd.setAttribute('hidden', '');
});

// ---------- logout (port of useAuth().logout + the fetch it triggers) ----------
async function sqLogout() {
  await sqApiFetch('/auth/logout', { method: 'POST' }).catch(() => {});
  window.location.href = '/';
}

// ---------- toasts (port of react-hot-toast usage) ----------
function sqToast(message, type = 'default') {
  const container = document.getElementById('sq-toast-container');
  if (!container) return;
  const el = document.createElement('div');
  el.className = `sq-toast sq-toast-${type}`;
  el.textContent = message;
  container.appendChild(el);
  requestAnimationFrame(() => el.classList.add('sq-toast-show'));
  setTimeout(() => {
    el.classList.remove('sq-toast-show');
    setTimeout(() => el.remove(), 200);
  }, 3000);
}
window.sqToast = sqToast;

// ---------- search (port of TopNav's doSearch + debounce + dropdown) ----------
(function initSearch() {
  const input = document.getElementById('sq-search-input');
  const resultsBox = document.getElementById('sq-search-results');
  if (!input || !resultsBox) return;
  let timer = null;

  function renderResults(data) {
    const posts = data.posts || [], users = data.users || [], communities = data.communities || [], topics = data.topics || [];
    if (!posts.length && !users.length && !communities.length && !topics.length) {
      resultsBox.innerHTML = '<div class="sq-search-empty">No results</div>';
      resultsBox.hidden = false;
      return;
    }
    const section = (label, items, toRow) => items.length
      ? `<div class="sq-search-section"><div class="sq-search-label">${label}</div>${items.map(toRow).join('')}</div>`
      : '';
    resultsBox.innerHTML = [
      section('Communities', communities.slice(0, 4), c => `<a href="/s/${c.slug}" class="sq-search-row">s/${c.name}</a>`),
      section('People', users.slice(0, 4), u => `<a href="/u/${u.handle}" class="sq-search-row">${u.display_name || u.handle}</a>`),
      section('Posts', posts.slice(0, 5), p => `<a href="/p/${p.id}" class="sq-search-row">${p.title}</a>`),
      section('Topics', topics.slice(0, 4), t => `<a href="/topics/${t.slug}" class="sq-search-row">${t.name}</a>`),
    ].join('');
    resultsBox.hidden = false;
  }

  input.addEventListener('input', () => {
    clearTimeout(timer);
    const val = input.value.trim();
    if (val.length < 2) { resultsBox.hidden = true; return; }
    timer = setTimeout(async () => {
      const data = await sqApiFetch('/search?q=' + encodeURIComponent(val)).catch(() => null);
      if (data) renderResults(data);
    }, 280);
  });

  document.addEventListener('click', (e) => {
    const wrap = document.getElementById('sq-nav-search');
    if (wrap && !wrap.contains(e.target)) resultsBox.hidden = true;
  });

  input.addEventListener('keydown', (e) => {
    if (e.key === 'Enter' && input.value.trim()) {
      window.location.href = '/search?q=' + encodeURIComponent(input.value.trim());
    }
  });
})();

// ---------- sidebar joined-communities + right-sidebar top squads ----------
(function loadSidebarData() {
  const joinedEls = document.querySelectorAll('.js-sq-sidebar-communities');
  const topEl = document.getElementById('sq-top-squads');

  function renderCommList(el, comms, prefixLabel) {
    if (!el) return;
    if (!comms.length) { el.innerHTML = `<div class="sq-sidebar-empty">${prefixLabel}</div>`; return; }
    el.innerHTML = comms.map(c => `
      <a href="/s/${c.slug}" class="sq-comm-row">
        <span class="sq-comm-icon" style="background:${c.color || 'var(--brand)'}">
          ${c.icon_cld ? `<img src="/api.php/media/c_fill,w_32,h_32/${c.icon_cld}" alt="">` : (c.name || 'S')[0].toUpperCase()}
        </span>
        <span class="sq-comm-name">s/${c.name}</span>
      </a>`).join('');
  }

  if (joinedEls.length) {
    if (window.SQ_USER) {
      sqApiFetch('/users/my-communities').then(d => joinedEls.forEach(el => renderCommList(el, d.communities || [], 'No squads joined yet'))).catch(() => {
        joinedEls.forEach(el => el.innerHTML = '<div class="sq-sidebar-empty">Couldn\u2019t load</div>');
      });
    } else {
      joinedEls.forEach(el => el.innerHTML = '<div class="sq-sidebar-empty"><a href="/login">Log in to see your squads</a></div>');
    }
  }

  if (topEl) {
    sqApiFetch('/communities?limit=6&sort=popular').then(d => renderCommList(topEl, d.communities || [], 'No squads yet')).catch(() => {
      topEl.innerHTML = '<div class="sq-sidebar-empty">Couldn\u2019t load</div>';
    });
  }
})();
