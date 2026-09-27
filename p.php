<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

$id = trim($_GET['id'] ?? '', '/');
if ($id === '') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/p/([^/]+)#', $reqPath, $m)) $id = $m[1];
}

$res = sq_api_get('/api/posts/' . rawurlencode($id));
$post = ($res['ok'] && !empty($res['data']['post'])) ? $res['data']['post'] : null;

if (!$post) {
    http_response_code(404);
    $pageTitle = 'Post not found';
    $layoutMode = 'content';
    include __DIR__ . '/partials/shell_header.php';
    echo '<div class="empty-state"><div class="empty-title">This post doesn\'t exist or was removed</div><a href="/" class="btn btn-primary">Back to feed</a></div>';
    include __DIR__ . '/partials/shell_footer.php';
    exit;
}

$pageTitle = $post['title'];
$pageDescription = mb_strimwidth(strip_tags($post['body'] ?? ''), 0, 160, '…');
$canonicalPath = '/p/' . $id;
$layoutMode = 'feed';

// Demo comments
$demoComments = [
    ['author' => 'nomsa_k', 'body' => "This is exactly what I needed to see today, thanks for sharing!", 'created_at' => time() - 1800, 'votes' => 24],
    ['author' => 'sipho92', 'body' => "Agreed 100%. Been saying this for ages.", 'created_at' => time() - 3600, 'votes' => 11],
    ['author' => 'lerato_m', 'body' => "Interesting take — do you have a source for that stat?", 'created_at' => time() - 5400, 'votes' => 4],
];

include __DIR__ . '/partials/shell_header.php';
?>
<div class="sq-post-full">
  <?php $compact = false; include __DIR__ . '/partials/post_card.php'; ?>
</div>

<div class="sq-comments" id="comments">
  <h2 class="sq-comments-heading"><?= htmlspecialchars((string)($post['comment_count'] ?? count($demoComments))) ?> comments</h2>

  <?php if ($SQ_USER): ?>
    <form class="sq-comment-form" onsubmit="return sqPostComment(event)">
      <textarea name="body" placeholder="What are your thoughts?" rows="3" required></textarea>
      <button type="submit" class="btn btn-primary">Comment</button>
    </form>
  <?php else: ?>
    <div class="sq-comment-login-prompt"><a href="/login">Log in</a> to join the discussion.</div>
  <?php endif; ?>

  <div class="sq-comment-list" id="sq-comment-list">
    <?php foreach ($demoComments as $c): ?>
      <div class="sq-comment">
        <div class="sq-comment-meta">
          <span class="sq-post-author">u/<?= htmlspecialchars($c['author']) ?></span>
          <span class="sq-post-dot">·</span>
          <span class="sq-post-time"><?= htmlspecialchars(sq_ago($c['created_at'])) ?></span>
        </div>
        <div class="sq-comment-body"><?= htmlspecialchars($c['body']) ?></div>
        <div class="sq-comment-actions">
          <button class="sq-post-action">▲ <?= htmlspecialchars((string)$c['votes']) ?> ▼</button>
          <button class="sq-post-action">Reply</button>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?php
$extraScript = <<<'JS'
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
function sqPostComment(e) {
  e.preventDefault();
  const ta = e.target.querySelector('textarea');
  const body = ta.value.trim();
  if (!body) return false;
  const list = document.getElementById('sq-comment-list');
  const div = document.createElement('div');
  div.className = 'sq-comment';
  div.innerHTML = `<div class="sq-comment-meta"><span class="sq-post-author">u/${(window.SQ_USER && window.SQ_USER.handle) || 'you'}</span><span class="sq-post-dot">·</span><span class="sq-post-time">just now</span></div>
    <div class="sq-comment-body"></div>
    <div class="sq-comment-actions"><button class="sq-post-action">▲ 0 ▼</button><button class="sq-post-action">Reply</button></div>`;
  div.querySelector('.sq-comment-body').textContent = body;
  list.prepend(div);
  ta.value = '';
  sqToast('Comment posted (demo)');
  return false;
}
JS;
include __DIR__ . '/partials/shell_footer.php';
