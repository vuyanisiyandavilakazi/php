<?php
require_once __DIR__ . '/lib/bootstrap.php';
require_once __DIR__ . '/lib/site.php';

// If already "logged in" (demo cookie set), bounce to home like the original did
if ($SQ_USER) { header('Location: /home'); exit; }

$pageTitle = 'Sign in';
$pageDescription = "Sign in to South Africa's free community forum.";
$canonicalPath = '/login';
$layoutMode = 'none';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pw = $_POST['password'] ?? '';
    if ($email === '' || $pw === '') {
        $error = 'Enter your email and password.';
    } else {
        // Demo mode: any credentials succeed (see lib/demo_data.php)
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
        <h1>South Africa's<br><span class="sq-brand-text">community forum.</span></h1>
        <p>Join communities, share ideas, and connect with people who get it. Proudly built for South Africans.</p>
      </div>
      <p class="auth-split-foot">squids.</p>
    </div>
  </div>

  <div class="auth-split-right">
    <div class="auth-card-wrap">
      <div class="auth-card-head">
        <img src="/brand/wordmark-dark.svg" alt="squids." width="130">
        <p class="auth-sub">Welcome back</p>
      </div>

      <div class="auth-card-gradient-border">
        <div class="auth-card-inner">
          <button type="button" class="btn-google" onclick="sqToast('Google sign-in is a demo stub')">
            <svg viewBox="0 0 24 24" width="18" height="18"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l3.66-2.84z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/></svg>
            Continue with Google
          </button>

          <div class="auth-sep">or</div>

          <?php if ($error): ?>
            <div class="auth-error"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>

          <form method="post" class="auth-form">
            <label class="auth-label">Email</label>
            <input type="email" name="email" required class="auth-input" placeholder="you@example.com" value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

            <label class="auth-label">Password</label>
            <div class="auth-pw-wrap">
              <input type="password" name="password" required class="auth-input" placeholder="••••••••" id="sq-pw-input">
              <button type="button" class="auth-pw-toggle" onclick="const i=document.getElementById('sq-pw-input');i.type=i.type==='password'?'text':'password';">Show</button>
            </div>

            <div class="auth-form-links">
              <a href="/forgot-password">Forgot password?</a>
            </div>

            <button type="submit" class="btn btn-primary auth-submit">Sign in</button>
          </form>

          <p class="auth-footer-text">Don't have an account? <a href="/signup">Sign up</a></p>
          <p class="auth-demo-note">Demo mode: any email/password will sign you in.</p>
        </div>
      </div>
    </div>
  </div>
</div>
<?php
include __DIR__ . '/partials/shell_footer.php';
