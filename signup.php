<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

if ($SQ_USER) { header('Location: /home'); exit; }

$pageTitle = 'Sign up';
$pageDescription = "Join South Africa's free community forum.";
$canonicalPath = '/signup';
$layoutMode = 'none';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $handle = trim($_POST['handle'] ?? '');
    $pw = $_POST['password'] ?? '';
    if ($email === '' || $handle === '' || $pw === '') {
        $error = 'Fill in all fields.';
    } else {
        sq_api_post('/api/auth/login', ['email' => strtolower($email), 'password' => $pw]);
        header('Location: /home');
        exit;
    }
}

include __DIR__ . '/partials/shell_header.php';
?>
<div class="auth-split-wrap">
  <div class="auth-split-panel">
    <div class="auth-split-glow-a"></div>
    <div class="auth-split-glow-b"></div>
    <div class="auth-split-inner">
      <img src="/brand/wordmark-dark.svg" alt="squids." width="132">
      <div class="auth-split-copy">
        <h1>Join South Africa's<br><span class="sq-brand-text">community forum.</span></h1>
        <p>Create an account to post, comment, join squads, and connect with people who get it.</p>
      </div>
      <p class="auth-split-foot">squids.</p>
    </div>
  </div>
  <div class="auth-split-right">
    <div class="auth-card-wrap">
      <div class="auth-card-head">
        <img src="/brand/wordmark-dark.svg" alt="squids." width="130">
        <p class="auth-sub">Create your account</p>
      </div>
      <div class="auth-card-gradient-border">
        <div class="auth-card-inner">
          <?php if ($error): ?><div class="auth-error"><?= htmlspecialchars($error) ?></div><?php endif; ?>
          <form method="post" class="auth-form">
            <label class="auth-label">Display name / handle</label>
            <input type="text" name="handle" required class="auth-input" placeholder="yourname" value="<?= htmlspecialchars($_POST['handle'] ?? '') ?>">
            <label class="auth-label">Email</label>
            <input type="email" name="email" required class="auth-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
            <label class="auth-label">Password</label>
            <input type="password" name="password" required class="auth-input" placeholder="••••••••">
            <button type="submit" class="btn btn-primary auth-submit">Create account</button>
          </form>
          <p class="auth-footer-text">Already have an account? <a href="/login">Sign in</a></p>
          <p class="auth-demo-note">Demo mode: any details will create a session.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/partials/shell_footer.php'; ?>
