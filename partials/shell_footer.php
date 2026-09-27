<?php /** Closing half of the shell, paired with shell_header.php */ ?>
    </main>
    <?php if ($layoutMode === 'feed'): ?>
      <aside class="sq-right sq-right-desktop-only">
        <?php include __DIR__ . '/rightbar.php'; ?>
      </aside>
    <?php endif; ?>
<?php if ($layoutMode !== 'none'): ?>
  </div>
<?php endif; ?>

<div id="sq-toast-container" class="sq-toast-container" aria-live="polite"></div>

<script nonce="<?= htmlspecialchars($SQ_NONCE) ?>">
  window.SQ_USER = <?= json_encode($SQ_USER ?? null, JSON_UNESCAPED_SLASHES) ?>;
  window.SQ_DEVICE = <?= json_encode($SQ_DEVICE ?? 'desktop') ?>;
</script>
<script src="/shell.js" nonce="<?= htmlspecialchars($SQ_NONCE) ?>"></script>
<?php if (!empty($extraScript)): ?>
<script nonce="<?= htmlspecialchars($SQ_NONCE) ?>"><?= $extraScript ?></script>
<?php endif; ?>
</body>
</html>
