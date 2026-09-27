<?php
/**
 * Port of LeftSidebar.tsx + GuestSidebar.tsx.
 * Joined-communities and pinned-communities lists are loaded client-side
 * by shell.js (calls /api.php/users/my-communities), same as the SWR call
 * in the original — avoids blocking page render on that extra request.
 */
$navItems = [
    ['href' => '/home',        'label' => 'Home',       'icon' => 'home'],
    ['href' => '/popular',     'label' => 'Trending',   'icon' => 'trend'],
    ['href' => '/communities', 'label' => 'Squads',     'icon' => 'squads'],
    ['href' => '/games',       'label' => 'Games',      'icon' => 'games'],
    ['href' => '/search',      'label' => 'Search',     'icon' => 'search'],
    ['href' => '/submit',      'label' => 'New post',   'icon' => 'compose'],
    ['href' => '/ama',         'label' => 'AMA',        'icon' => 'ama'],
    ['href' => '/friends',     'label' => 'Friends',    'icon' => 'friends'],
    ['href' => '/family',      'label' => 'Family',     'icon' => 'family'],
    ['href' => '/settings',    'label' => 'Settings',   'icon' => 'gear'],
];
$sqIcons = [
    'home'    => '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>',
    'trend'   => '<polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>',
    'squads'  => '<rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/>',
    'games'   => '<rect x="2" y="7" width="20" height="12" rx="4"/><circle cx="8" cy="13" r="1.5"/><circle cx="16" cy="12" r="1"/><circle cx="17.5" cy="14" r="1"/>',
    'search'  => '<circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
    'compose' => '<path d="M12 5v14M5 12h14"/>',
    'ama'     => '<path d="M12 19c-4.4 0-8-2.7-8-6s3.6-6 8-6 8 2.7 8 6c0 1.3-.6 2.5-1.5 3.5.2 1 .8 2 1.5 2.5-1.2.3-2.4 0-3.3-.6-1.4.6-3 .9-4.7.9z"/>',
    'friends' => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
    'family'  => '<path d="M18 21a6 6 0 0 0-12 0"/><circle cx="12" cy="9" r="4"/><path d="M23 21a4 4 0 0 0-3-3.87M1 21a4 4 0 0 1 3-3.87"/>',
    'gear'    => '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>',
];
$currentPath = $currentPath ?? '/';
if (!function_exists('sq_nav_icon')) {
function sq_nav_icon(string $key, array $sqIcons): string {
    return $sqIcons[$key] ?? '';
}
}
?>
<div class="sq-sidebar-inner">
  <nav class="sq-sidebar-nav">
    <?php foreach ($navItems as $item):
        $active = $currentPath === $item['href'] || ($item['href'] !== '/' && str_starts_with($currentPath, $item['href'] . '/'));
        if (!$navUser && in_array($item['href'], ['/submit', '/friends', '/family', '/settings'], true)) continue;
    ?>
      <a href="<?= htmlspecialchars($item['href']) ?>" class="sidebar-link<?= $active ? ' active' : '' ?>">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="22" height="22"><?= sq_nav_icon($item['icon'], $sqIcons) ?></svg>
        <span><?= htmlspecialchars($item['label']) ?></span>
      </a>
    <?php endforeach; ?>
  </nav>

  <hr class="sidebar-sep">
  <div class="sq-sidebar-label">Your squads</div>
  <div id="<?= $sidebarInstanceId ?? 'sq-sidebar-communities' ?>" class="sq-sidebar-communities js-sq-sidebar-communities">
    <div class="sq-sidebar-loading">Loading…</div>
  </div>
</div>
