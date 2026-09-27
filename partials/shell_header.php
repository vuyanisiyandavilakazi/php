<?php
/**
 * Port of AppLayout.tsx. Include this, then your page content, then
 * shell_footer.php. $pageTitle / $pageDescription / $canonicalPath /
 * $layoutMode ('feed'|'content'|'none') should be set before including.
 */
require_once __DIR__ . '/../lib/bootstrap.php';
require_once __DIR__ . '/../lib/site.php';

$layoutMode  = $layoutMode  ?? 'feed';
$currentPath = $_SERVER['REQUEST_URI'] ?? '/';
$currentPath = parse_url($currentPath, PHP_URL_PATH) ?: '/';
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
<?php include __DIR__ . '/head.php'; ?>
</head>
<body>
<?php if ($layoutMode !== 'none'): ?>
  <?php include __DIR__ . '/topnav.php'; ?>

  <div id="sq-mobile-sidebar-overlay" class="sq-mobile-sidebar-overlay" hidden>
    <div class="sq-overlay-bg" onclick="sqToggleMobileSidebar()"></div>
    <div class="sq-mobile-sidebar-panel">
      <?php $sidebarInstanceId = 'sq-sidebar-communities-mobile'; include __DIR__ . '/sidebar.php'; ?>
    </div>
  </div>

  <div class="sq-layout-grid<?= $layoutMode === 'content' ? ' sq-layout-grid-noright' : '' ?>">
    <aside class="sq-sidebar sq-sidebar-desktop">
      <?php include __DIR__ . '/sidebar.php'; ?>
    </aside>
    <main class="sq-main">
<?php else: ?>
    <main class="sq-main sq-main-bare">
<?php endif; ?>
