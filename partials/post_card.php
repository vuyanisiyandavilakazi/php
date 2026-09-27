<?php
/**
 * Port of PostCard.tsx — core rendering: vote arrows, title/body, image,
 * community/author line, comment count, bookmark. Voting/bookmark call
 * the same /api/posts/vote + /api/posts/bookmark endpoints via JS.
 *
 * NOTE: polls, NSFW gating modal, report/share/options drawers, and view
 * tracking are not yet ported — the original PostCard.tsx is ~400 lines
 * and pulls in five other components; this covers the primary feed
 * reading/voting/commenting path. Flagging so it doesn't look finished
 * when it isn't.
 *
 * Expects $post (assoc array shaped like the API's post object) and
 * optional $compact bool.
 */
$compact = $compact ?? false;
$p = $post;
$score = ($p['votes_up'] ?? 0) - ($p['votes_down'] ?? 0);
$commentCount = $p['comment_count'] ?? 0;
$img = !empty($p['images'][0]) ? $p['images'][0] : null;
?>
<div class="sq-post-card<?= $compact ? ' compact' : '' ?>" data-post-id="<?= htmlspecialchars($p['id']) ?>">
  <div class="sq-post-vote">
    <button class="sq-vote-btn sq-vote-up" onclick="sqVote('<?= htmlspecialchars($p['id']) ?>','up',this)" aria-label="Upvote">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M12 19V5M5 12l7-7 7 7"/></svg>
    </button>
    <span class="sq-vote-score"><?= htmlspecialchars((string)$score) ?></span>
    <button class="sq-vote-btn sq-vote-down" onclick="sqVote('<?= htmlspecialchars($p['id']) ?>','down',this)" aria-label="Downvote">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" width="16" height="16"><path d="M12 5v14M5 12l7 7 7-7"/></svg>
    </button>
  </div>

  <div class="sq-post-body">
    <div class="sq-post-meta">
      <?php if (!empty($p['community_slug'])): ?>
        <a href="/s/<?= htmlspecialchars($p['community_slug']) ?>" class="sq-post-community">s/<?= htmlspecialchars($p['community_name'] ?? $p['community_slug']) ?></a>
        <span class="sq-post-dot">·</span>
      <?php endif; ?>
      <span class="sq-post-author">u/<?= htmlspecialchars($p['author_handle'] ?? 'unknown') ?></span>
      <span class="sq-post-dot">·</span>
      <span class="sq-post-time"><?= htmlspecialchars(sq_ago((int)($p['created_at'] ?? 0))) ?></span>
    </div>

    <a href="/p/<?= htmlspecialchars($p['id']) ?>" class="sq-post-title"><?= htmlspecialchars($p['title'] ?? '') ?></a>

    <?php if (!$compact && !empty($p['body'])): ?>
      <div class="sq-post-excerpt"><?= htmlspecialchars(mb_strimwidth(strip_tags($p['body']), 0, 220, '…')) ?></div>
    <?php endif; ?>

    <?php if (!$compact && $img): ?>
      <a href="/p/<?= htmlspecialchars($p['id']) ?>" class="sq-post-image-link">
        <img src="<?= htmlspecialchars(sq_cdn_url_fit($img, 900, 900)) ?>" alt="" loading="lazy" class="sq-post-image">
      </a>
    <?php endif; ?>

    <div class="sq-post-actions">
      <a href="/p/<?= htmlspecialchars($p['id']) ?>#comments" class="sq-post-action">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>
        <?= htmlspecialchars((string)$commentCount) ?>
      </a>
      <button class="sq-post-action sq-post-bookmark" onclick="sqBookmark('<?= htmlspecialchars($p['id']) ?>',this)">
        <svg viewBox="0 0 24 24" fill="<?= !empty($p['is_bookmarked']) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" width="15" height="15"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"/></svg>
      </button>
    </div>
  </div>
</div>
