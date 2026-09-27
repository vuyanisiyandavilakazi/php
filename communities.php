<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

$pageTitle = 'Squads';
$pageDescription = 'Browse all communities on squids.';
$canonicalPath = '/communities';
$layoutMode = 'content';

$res = sq_api_get('/api/communities?limit=50');
$communities = ($res['ok'] && !empty($res['data']['communities'])) ? $res['data']['communities'] : [];

include __DIR__ . '/partials/shell_header.php';
?>
<div class="sq-page-head">
  <h1>Squads</h1>
  <p class="sq-page-sub">Find your people. Join a squad that matches your interests.</p>
  <?php if ($SQ_USER): ?><a href="/communities/create" class="btn btn-primary">+ Create a squad</a><?php endif; ?>
</div>

<div class="sq-comm-grid">
  <?php foreach ($communities as $c): ?>
    <a href="/s/<?= htmlspecialchars($c['slug']) ?>" class="sq-comm-tile">
      <span class="sq-comm-tile-icon" style="background:<?= htmlspecialchars($c['color'] ?? '#2DD4A8') ?>">
        <?= htmlspecialchars(mb_strtoupper(mb_substr($c['name'], 0, 1))) ?>
      </span>
      <div class="sq-comm-tile-body">
        <div class="sq-comm-tile-name">
          s/<?= htmlspecialchars($c['name']) ?>
          <?php if (!empty($c['verified'])): ?><span class="sq-verified-badge" title="Verified">✓</span><?php endif; ?>
        </div>
        <div class="sq-comm-tile-count"><?= htmlspecialchars(sq_num((int)($c['member_count'] ?? 0))) ?> members</div>
      </div>
      <button class="btn btn-outline sq-join-btn" onclick="event.preventDefault();sqJoinCommunity('<?= htmlspecialchars($c['slug']) ?>', this)">Join</button>
    </a>
  <?php endforeach; ?>
</div>

<?php
$extraScript = <<<'JS'
async function sqJoinCommunity(slug, btn) {
  if (!window.SQ_USER) { window.location.href = '/login'; return; }
  btn.textContent = 'Joined ✓';
  btn.disabled = true;
  btn.classList.add('sq-joined');
  const data = await sqApiFetch('/communities/' + slug + '/join', { method: 'POST' }).catch(() => null);
  if (!data || !data.ok) sqToast('Something went wrong'); else sqToast('Joined s/' + slug);
}
JS;
include __DIR__ . '/partials/shell_footer.php';
