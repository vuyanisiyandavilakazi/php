<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

$handle = trim($_GET['handle'] ?? '', '/');
if ($handle === '') {
    $reqPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    if (preg_match('#^/u/([^/]+)#', $reqPath, $m)) $handle = $m[1];
}

$res = sq_api_get('/api/users/' . rawurlencode($handle));
$user = ($res['ok'] && !empty($res['data']['user'])) ? $res['data']['user'] : null;

if (!$user) {
    http_response_code(404);
    $pageTitle = 'User not found';
    $layoutMode = 'content';
    include __DIR__ . '/partials/shell_header.php';
    echo '<div class="empty-state"><div class="empty-title">This user doesn\'t exist</div></div>';
    include __DIR__ . '/partials/shell_footer.php';
    exit;
}

$pageTitle = $user['display_name'] ?? $user['handle'];
$canonicalPath = '/u/' . $handle;
$layoutMode = 'content';
$isSelf = $SQ_USER && ($SQ_USER['handle'] ?? null) === $user['handle'];

$feedRes = sq_api_get('/api/feed?sort=new');
$posts = ($feedRes['ok'] && !empty($feedRes['data']['posts'])) ? array_slice($feedRes['data']['posts'], 0, 3) : [];

include __DIR__ . '/partials/shell_header.php';
?>
<div class="sq-profile-head">
  <img src="<?= htmlspecialchars(sq_avatar_url($user['avatar_cld'] ?? '', 96)) ?>" alt="" width="88" height="88" class="sq-profile-avatar">
  <div class="sq-profile-info">
    <h1><?= htmlspecialchars($user['display_name'] ?? $user['handle']) ?> <?= !empty($user['verified']) ? '<span class="sq-verified-badge">✓</span>' : '' ?></h1>
    <div class="sq-profile-handle">u/<?= htmlspecialchars($user['handle']) ?></div>
    <?php if (!empty($user['bio'])): ?><p class="sq-profile-bio"><?= htmlspecialchars($user['bio']) ?></p><?php endif; ?>
    <div class="sq-profile-stats">
      <span><strong><?= htmlspecialchars(sq_num((int)($user['post_count'] ?? 0))) ?></strong> posts</span>
      <span><strong><?= htmlspecialchars(sq_num((int)($user['friend_count'] ?? 0))) ?></strong> friends</span>
      <span><strong><?= htmlspecialchars(sq_num((int)($user['follower_count'] ?? 0))) ?></strong> followers</span>
    </div>
  </div>
  <?php if (!$isSelf && $SQ_USER): ?>
    <button class="btn btn-primary" onclick="sqToast('Friend request sent (demo)')">+ Add Friend</button>
  <?php elseif ($isSelf): ?>
    <a href="/settings" class="btn btn-outline">Edit profile</a>
  <?php endif; ?>
</div>

<div class="sq-profile-tabs">
  <button class="sort-btn active">Posts</button>
  <button class="sort-btn">Comments</button>
  <button class="sort-btn">About</button>
</div>

<div class="sq-feed-posts">
  <?php if (empty($posts)): ?>
    <div class="empty-state"><div class="empty-title">No posts yet</div></div>
  <?php else: foreach ($posts as $post): $compact = true; ?>
    <?php include __DIR__ . '/partials/post_card.php'; ?>
  <?php endforeach; endif; ?>
</div>
<?php include __DIR__ . '/partials/shell_footer.php'; ?>
