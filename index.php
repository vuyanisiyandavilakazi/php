<?php
/**
 * Port of src/app/page.tsx wrapping HomeFeed.tsx.
 * Server-renders the first page of the "hot" feed (avoids the original's
 * loading-skeleton flash on first paint), then shell.js takes over for
 * sort-switching and "load more" via /api.php/feed.
 */
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

$pageTitle = '';
$pageDescription = SQ_SITE_DESC;
$canonicalPath = '/';
$layoutMode = 'feed';

$feedRes = sq_api_get('/api/feed?sort=hot&page=1');
$posts = ($feedRes['ok'] && !empty($feedRes['data']['posts'])) ? $feedRes['data']['posts'] : [];

include __DIR__ . '/partials/shell_header.php';
?>
<div class="sq-feed-sortbar" id="sq-feed-sortbar">
  <button class="sort-btn" data-sort="foryou" onclick="sqSwitchSort('foryou')">For You</button>
  <button class="sort-btn active" data-sort="hot" onclick="sqSwitchSort('hot')">Hot</button>
  <button class="sort-btn" data-sort="new" onclick="sqSwitchSort('new')">New</button>
  <button class="sort-btn" data-sort="top" onclick="sqSwitchSort('top')">Top</button>
  <button class="sort-btn" data-sort="rising" onclick="sqSwitchSort('rising')">Rising</button>
  <?php if ($SQ_USER): ?>
    <a href="/submit" class="sort-btn sq-new-post-btn">+ New Post</a>
  <?php endif; ?>
</div>

<div id="sq-feed-posts" class="sq-feed-posts">
  <?php if (empty($posts)): ?>
    <div class="empty-state" id="sq-feed-empty">
      <div class="empty-title">Nothing here yet</div>
      <div class="empty-sub"><?= $SQ_USER ? 'Be the first to post something!' : 'Sign up to personalise your feed.' ?></div>
      <a href="/communities" class="btn btn-primary">Explore Squads</a>
    </div>
  <?php else: ?>
    <?php foreach ($posts as $post): ?>
      <?php include __DIR__ . '/partials/post_card.php'; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</div>

<div id="sq-feed-loadmore" class="sq-feed-loadmore">
  <button class="btn btn-outline" id="sq-loadmore-btn" onclick="sqLoadMore()">Load more</button>
</div>

<?php
$extraScript = <<<'JS'
(function(){
  let sort = 'hot';
  let page = 1;
  const seen = new Set(document.querySelectorAll('.sq-post-card').length
    ? Array.from(document.querySelectorAll('.sq-post-card')).map(el => el.dataset.postId)
    : []);

  window.sqSwitchSort = async function(s) {
    sort = s; page = 1; seen.clear();
    document.querySelectorAll('#sq-feed-sortbar .sort-btn[data-sort]').forEach(b => b.classList.toggle('active', b.dataset.sort === s));
    const container = document.getElementById('sq-feed-posts');
    container.innerHTML = '<div class="sq-feed-loading">Loading…</div>';
    const data = await sqApiFetch(`/feed?sort=${s}&page=1`).catch(() => null);
    renderPosts(data, true);
  };

  window.sqLoadMore = async function() {
    page += 1;
    const btn = document.getElementById('sq-loadmore-btn');
    btn.disabled = true; btn.textContent = 'Loading…';
    const data = await sqApiFetch(`/feed?sort=${sort}&page=${page}`).catch(() => null);
    renderPosts(data, false);
    btn.disabled = false; btn.textContent = 'Load more';
  };

  function renderPosts(data, reset) {
    const container = document.getElementById('sq-feed-posts');
    const posts = (data && data.posts) || [];
    const fresh = posts.filter(p => !seen.has(p.id));
    fresh.forEach(p => seen.add(p.id));
    if (reset) container.innerHTML = '';
    if (reset && fresh.length === 0) {
      container.innerHTML = '<div class="empty-state"><div class="empty-title">Nothing here yet</div></div>';
    } else {
      container.insertAdjacentHTML('beforeend', fresh.map(renderPostCard).join(''));
    }
    document.getElementById('sq-feed-loadmore').style.display = posts.length >= 5 ? '' : 'none';
  }

  function esc(s) { const d = document.createElement('div'); d.textContent = s || ''; return d.innerHTML; }
  function renderPostCard(p) {
    const score = (p.votes_up || 0) - (p.votes_down || 0);
    const img = (p.images && p.images[0]) ? `/api.php/media/c_limit,w_900,h_900/${p.images[0]}` : null;
    return `<div class="sq-post-card" data-post-id="${esc(p.id)}">
      <div class="sq-post-vote">
        <button class="sq-vote-btn sq-vote-up" onclick="sqVote('${esc(p.id)}','up',this)">▲</button>
        <span class="sq-vote-score">${score}</span>
        <button class="sq-vote-btn sq-vote-down" onclick="sqVote('${esc(p.id)}','down',this)">▼</button>
      </div>
      <div class="sq-post-body">
        <div class="sq-post-meta">
          ${p.community_slug ? `<a href="/s/${esc(p.community_slug)}" class="sq-post-community">s/${esc(p.community_name || p.community_slug)}</a><span class="sq-post-dot">·</span>` : ''}
          <span class="sq-post-author">u/${esc(p.author_handle || 'unknown')}</span>
        </div>
        <a href="/p/${esc(p.id)}" class="sq-post-title">${esc(p.title)}</a>
        ${img ? `<a href="/p/${esc(p.id)}" class="sq-post-image-link"><img src="${img}" loading="lazy" class="sq-post-image"></a>` : ''}
        <div class="sq-post-actions">
          <a href="/p/${esc(p.id)}#comments" class="sq-post-action">💬 ${p.comment_count || 0}</a>
        </div>
      </div>
    </div>`;
  }
})();

async function sqVote(postId, dir, btn) {
  if (!window.SQ_USER) { window.location.href = '/login'; return; }
  const card = btn.closest('.sq-post-card');
  const scoreEl = card.querySelector('.sq-vote-score');
  const prev = parseInt(scoreEl.textContent, 10) || 0;
  scoreEl.textContent = dir === 'up' ? prev + 1 : prev - 1;
  const data = await sqApiFetch('/posts/vote', { method: 'POST', body: { post_id: postId, direction: dir } }).catch(() => null);
  if (!data || !data.ok) { scoreEl.textContent = prev; sqToast('Vote failed'); }
}

async function sqBookmark(postId, btn) {
  if (!window.SQ_USER) { window.location.href = '/login'; return; }
  const data = await sqApiFetch('/posts/bookmark', { method: 'POST', body: { post_id: postId } }).catch(() => null);
  if (data && data.ok) sqToast(data.bookmarked ? 'Bookmarked' : 'Removed from bookmarks');
  else sqToast('Failed');
}
JS;
include __DIR__ . '/partials/shell_footer.php';
