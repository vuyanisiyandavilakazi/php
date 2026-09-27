<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

$slug = trim($_GET['slug'] ?? ($_SERVER['PATH_INFO'] ?? ''), '/');
// When hit as /s.php?path=slug via router, or /s/slug via .htaccess -> s.php (slug comes from REQUEST_URI)
if ($slug === '') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/s/([^/]+)#', $reqPath, $m)) $slug = $m[1];
}

$res = sq_api_get('/api/communities/' . rawurlencode($slug));
$community = ($res['ok'] && !empty($res['data']['community'])) ? $res['data']['community'] : null;

if (!$community) {
    http_response_code(404);
    $pageTitle = 'Squad not found';
    $layoutMode = 'content';
    include __DIR__ . '/partials/shell_header.php';
    echo '<div class="empty-state"><div class="empty-title">This squad doesn\'t exist</div><a href="/communities" class="btn btn-primary">Browse squads</a></div>';
    include __DIR__ . '/partials/shell_footer.php';
    exit;
}

$pageTitle = 's/' . $community['name'];
$pageDescription = $community['description'] ?? '';
$canonicalPath = '/s/' . $slug;
$layoutMode = 'feed';

$feedRes = sq_api_get('/api/feed?sort=hot&community=' . rawurlencode($slug));
$posts = ($feedRes['ok'] && !empty($feedRes['data']['posts'])) ? array_values(array_filter($feedRes['data']['posts'], fn($p) => ($p['community_slug'] ?? '') === $slug)) : [];

include __DIR__ . '/partials/shell_header.php';
?>
<div class="sq-comm-banner" style="background:linear-gradient(135deg, <?= htmlspecialchars($community['color']) ?>, var(--bg2))">
  <div class="sq-comm-banner-inner">
    <span class="sq-comm-avatar" style="background:<?= htmlspecialchars($community['color']) ?>">
      <?= htmlspecialchars(mb_strtoupper(mb_substr($community['name'], 0, 1))) ?>
    </span>
    <div>
      <h1>s/<?= htmlspecialchars($community['name']) ?> <?= !empty($community['verified']) ? '<span class="sq-verified-badge">✓</span>' : '' ?></h1>
      <div class="sq-comm-meta"><?= htmlspecialchars(sq_num((int)$community['member_count'])) ?> members</div>
    </div>
    <button class="btn btn-primary sq-join-btn-lg" onclick="sqJoinCommunity('<?= htmlspecialchars($slug) ?>', this)">Join</button>
  </div>
</div>
<p class="sq-comm-desc"><?= htmlspecialchars($community['description'] ?? '') ?></p>

<div class="sq-feed-sortbar">
  <button class="sort-btn active">Hot</button>
  <button class="sort-btn">New</button>
  <button class="sort-btn">Top</button>
  <?php if ($SQ_USER): ?><a href="/submit?community=<?= htmlspecialchars($slug) ?>" class="sort-btn sq-new-post-btn">+ New Post</a><?php endif; ?>
</div>

<div class="sq-feed-posts">
  <?php if (empty($posts)): ?>
    <div class="empty-state"><div class="empty-title">No posts yet in s/<?= htmlspecialchars($community['name']) ?></div></div>
  <?php else: foreach ($posts as $post): ?>
    <?php include __DIR__ . '/partials/post_card.php'; ?>
  <?php endforeach; endif; ?>
</div>

<?php
$extraScript = <<<'JS'
async function sqJoinCommunity(slug, btn) {
  if (!window.SQ_USER) { window.location.href = '/login'; return; }
  btn.textContent = 'Joined ✓'; btn.disabled = true;
  const data = await sqApiFetch('/communities/' + slug + '/join', { method: 'POST' }).catch(() => null);
  if (data && data.ok) sqToast('Joined s/' + slug); else sqToast('Something went wrong');
}
async function sqVote(postId, dir, btn) {
  if (!window.SQ_USER) { window.location.href = '/login'; return; }
  const card = btn.closest('.sq-post-card');
  const scoreEl = card.querySelector('.sq-vote-score');
  const prev = parseInt(scoreEl.textContent, 10) || 0;
  scoreEl.textContent = dir === 'up' ? prev + 1 : prev - 1;
  await sqApiFetch('/posts/vote', { method: 'POST', body: { post_id: postId, direction: dir } }).catch(() => {});
}
async function sqBookmark(postId, btn) {
  if (!window.SQ_USER) { window.location.href = '/login'; return; }
  const data = await sqApiFetch('/posts/bookmark', { method: 'POST', body: { post_id: postId } }).catch(() => null);
  sqToast(data && data.ok ? 'Bookmarked' : 'Failed');
}
JS;
include __DIR__ . '/partials/shell_footer.php';
