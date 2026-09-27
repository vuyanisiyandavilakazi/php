<?php
/**
 * Port of src/components/layout/TopNav.tsx.
 * Search, theme toggle, and user menu are handled by shell.js (vanilla JS)
 * calling /api.php/search, /api.php/users/me, /api.php/auth/logout etc.,
 * the same endpoints the original React version called.
 */
$navUser = $SQ_USER ?? null;
?>
<nav class="sq-topnav" id="sq-topnav">
  <div class="sq-topnav-inner">
    <button class="sq-nav-burger" id="sq-burger" aria-label="Toggle menu" onclick="sqToggleMobileSidebar()">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="20" height="20"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
    </button>
    <a href="/" class="sq-nav-wordmark" aria-label="squids. home">
      <img src="/brand/wordmark-dark.svg" alt="squids." height="25" class="sq-wordmark-dark">
      <img src="/brand/wordmark-light.svg" alt="squids." height="25" class="sq-wordmark-light">
    </a>

    <div class="sq-nav-search" id="sq-nav-search">
      <input type="search" id="sq-search-input" placeholder="Search squids, posts, people…" autocomplete="off">
      <div id="sq-search-results" class="sq-search-results" hidden></div>
    </div>

    <div class="sq-nav-actions">
      <button class="sq-icon-btn" id="sq-theme-toggle" aria-label="Toggle theme" onclick="sqToggleTheme()">
        <svg id="sq-theme-icon-dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="18" height="18"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
      </button>

      <?php if ($navUser): ?>
        <div class="sq-nav-user" id="sq-user-menu">
          <button class="sq-user-btn" onclick="sqToggleUserMenu()">
            <img src="<?= htmlspecialchars(sq_avatar_url($navUser['avatar_cld'] ?? '', 32)) ?>" alt="" width="32" height="32" class="sq-avatar-sm">
          </button>
          <div class="sq-user-dropdown" id="sq-user-dropdown" hidden>
            <a href="/u/<?= htmlspecialchars($navUser['handle'] ?? '') ?>">Profile</a>
            <a href="/settings">Settings</a>
            <a href="#" onclick="sqLogout();return false;">Log out</a>
          </div>
        </div>
      <?php else: ?>
        <a href="/login" class="btn btn-outline sq-nav-login">Log in</a>
        <a href="/signup" class="btn btn-primary sq-nav-signup">Sign up</a>
      <?php endif; ?>
    </div>
  </div>
</nav>
