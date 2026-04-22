<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign In — NBALOGMARKETPLACE</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(145deg, #e8f4ff 0%, #f5fbff 50%, #e0f0ff 100%);
      display: flex; align-items: center; justify-content: center;
      padding: 20px;
    }

    /* Decorative background blobs */
    body::before {
      content: '';
      position: fixed; top: -120px; right: -80px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(26,140,255,0.15) 0%, transparent 70%);
      pointer-events: none; z-index: 0;
    }
    body::after {
      content: '';
      position: fixed; bottom: -60px; left: -60px;
      width: 360px; height: 360px;
      background: radial-gradient(circle, rgba(0,198,174,0.12) 0%, transparent 70%);
      pointer-events: none; z-index: 0;
    }

    .auth-wrapper {
      width: 100%; max-width: 480px;
      position: relative; z-index: 1;
    }

    /* Top logo link */
    .auth-logo-bar {
      text-align: center; margin-bottom: 28px;
    }

    .auth-card {
      background: white;
      border-radius: var(--radius-xl);
      padding: 44px 40px;
      box-shadow: 0 20px 60px rgba(26,140,255,0.14);
      border: 1.5px solid var(--border);
    }

    .auth-header { text-align: center; margin-bottom: 32px; }
    .auth-icon {
      width: 64px; height: 64px;
      background: linear-gradient(135deg, var(--primary-light), var(--sky-deep));
      border-radius: 18px;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 18px;
      font-size: 1.6rem;
      box-shadow: 0 4px 16px rgba(26,140,255,0.15);
    }
    .auth-title {
      font-family: var(--font-head);
      font-size: 1.7rem; font-weight: 800;
      color: var(--text-dark); margin-bottom: 8px;
      letter-spacing: -0.5px;
    }
    .auth-sub { font-size: 0.93rem; color: var(--text-light); }

    /* Form tabs (User / Admin) */
    .auth-tabs {
      display: flex;
      background: var(--sky);
      border-radius: var(--radius-sm);
      padding: 4px;
      margin-bottom: 28px;
      gap: 4px;
    }
    .auth-tab {
      flex: 1; padding: 10px;
      border: none; background: transparent;
      border-radius: 7px;
      font-family: var(--font-head);
      font-size: 0.9rem; font-weight: 600;
      color: var(--text-light);
      cursor: pointer; transition: var(--transition);
    }
    .auth-tab.active {
      background: white;
      color: var(--primary);
      box-shadow: 0 2px 8px rgba(26,140,255,0.12);
    }

    /* Password field */
    .input-group { position: relative; }
    .input-group .form-control { padding-right: 48px; }
    .toggle-pw {
      position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none;
      cursor: pointer; color: var(--text-light);
      font-size: 1rem; padding: 4px;
      transition: color 0.2s;
    }
    .toggle-pw:hover { color: var(--primary); }

    .forgot-link {
      display: inline-block;
      font-size: 0.85rem; font-weight: 600;
      color: var(--primary); margin-top: 8px;
      transition: color 0.2s;
    }
    .forgot-link:hover { color: var(--primary-dark); }

    .divider {
      display: flex; align-items: center; gap: 14px;
      margin: 22px 0;
    }
    .divider::before, .divider::after {
      content: ''; flex: 1;
      height: 1px; background: var(--border);
    }
    .divider span { font-size: 0.82rem; color: var(--text-light); font-weight: 500; }

    .auth-footer {
      text-align: center; margin-top: 22px;
      font-size: 0.9rem; color: var(--text-light);
    }
    .auth-footer a { color: var(--primary); font-weight: 600; }
    .auth-footer a:hover { text-decoration: underline; }

    /* Error / success messages */
    #loginAlert { display: none; margin-bottom: 18px; }

    /* ─ Reset Password Form (hidden by default) ─ */
    #resetForm { display: none; }

    .btn-full { width: 100%; justify-content: center; }

    @media (max-width: 520px) {
      .auth-card { padding: 32px 24px; }
    }
  </style>
</head>
<body>

<div class="auth-wrapper">

  <!-- Logo at top -->
  <div class="auth-logo-bar">
    <a href="../../index.php" class="logo" style="justify-content:center">
      <div class="logo-icon">NBA</div>
      NBALOG<span>MARKETPLACE</span>
    </a>
  </div>

  <div class="auth-card">

    <!-- ══ LOGIN FORM ══ -->
    <div id="loginSection">
      <div class="auth-header">
        <div class="auth-icon">🔐</div>
        <h1 class="auth-title">Welcome Back</h1>
        <p class="auth-sub">Sign in to your account to continue</p>
      </div>

      <!-- User / Admin Tabs -->
      <div class="auth-tabs">
        <button class="auth-tab active" onclick="switchTab('user', this)">
          <i class="fas fa-user"></i> User Login
        </button>
        <button class="auth-tab" onclick="switchTab('admin', this)">
          <i class="fas fa-shield-halved"></i> Admin Login
        </button>
      </div>

      <!-- Alert Box -->
      <div id="loginAlert" class="alert"></div>

      <form id="loginForm" onsubmit="handleLogin(event)">
        <input type="hidden" name="login_type" id="loginType" value="user" />

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <div class="input-group">
            <input
              type="email"
              class="form-control"
              id="loginEmail"
              name="email"
              placeholder="you@example.com"
              required
              autocomplete="email"
            />
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Password</label>
          <div class="input-group">
            <input
              type="password"
              class="form-control"
              id="loginPassword"
              name="password"
              placeholder="Enter your password"
              required
              autocomplete="current-password"
            />
            <button type="button" class="toggle-pw" onclick="togglePassword('loginPassword', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
          <a href="#" class="forgot-link" onclick="checkForgotPassword(event)">
            Forgot password?
          </a>
        </div>

        <button type="submit" class="btn btn-primary btn-lg btn-full" id="loginBtn">
          <i class="fas fa-sign-in-alt"></i> Sign In
        </button>
      </form>

      <div class="divider"><span>New to NBALOGMARKETPLACE?</span></div>

      <div class="auth-footer">
        Don't have an account?
        <a href="signup.php">Create one for free</a>
      </div>
    </div>

    <!-- ══ RESET PASSWORD FORM ══ -->
    <div id="resetSection" style="display:none">
      <div class="auth-header">
        <div class="auth-icon">🔑</div>
        <h1 class="auth-title">Reset Password</h1>
        <p class="auth-sub">Enter your new password below</p>
      </div>

      <div id="resetAlert" class="alert" style="display:none"></div>

      <form id="resetForm" onsubmit="handleReset(event)">
        <input type="hidden" id="resetEmail" name="email" />

        <div class="form-group">
          <label class="form-label">Email Address</label>
          <input type="email" class="form-control" id="resetEmailDisplay" readonly />
        </div>

        <div class="form-group">
          <label class="form-label">New Password</label>
          <div class="input-group">
            <input
              type="password"
              class="form-control"
              id="newPassword"
              name="new_password"
              placeholder="At least 8 characters"
              minlength="8"
              required
            />
            <button type="button" class="toggle-pw" onclick="togglePassword('newPassword', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">Confirm New Password</label>
          <div class="input-group">
            <input
              type="password"
              class="form-control"
              id="confirmPassword"
              name="confirm_password"
              placeholder="Repeat new password"
              minlength="8"
              required
            />
            <button type="button" class="toggle-pw" onclick="togglePassword('confirmPassword', this)">
              <i class="fas fa-eye"></i>
            </button>
          </div>
        </div>

        <!-- Password strength bar -->
        <div id="strengthBar" style="height:5px;border-radius:4px;background:var(--border);margin-bottom:16px;overflow:hidden">
          <div id="strengthFill" style="height:100%;width:0;border-radius:4px;transition:all 0.3s"></div>
        </div>
        <div id="strengthLabel" style="font-size:0.8rem;color:var(--text-light);margin-bottom:16px"></div>

        <button type="submit" class="btn btn-primary btn-lg btn-full">
          <i class="fas fa-lock"></i> Set New Password
        </button>

        <button type="button" class="btn btn-outline btn-lg btn-full mt-2" onclick="showLoginSection()">
          <i class="fas fa-arrow-left"></i> Back to Login
        </button>
      </form>
    </div>

  </div><!-- /.auth-card -->
</div><!-- /.auth-wrapper -->

<script>
  /* ── TAB SWITCH ── */
  function switchTab(type, btn) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('loginType').value = type;
    hideAlert('loginAlert');
  }

  /* ── PASSWORD VISIBILITY ── */
  function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    if (field.type === 'password') {
      field.type = 'text';
      icon.className = 'fas fa-eye-slash';
    } else {
      field.type = 'password';
      icon.className = 'fas fa-eye';
    }
  }

  /* ── ALERT HELPERS ── */
  function showAlert(id, msg, type = 'error') {
    const el = document.getElementById(id);
    el.className = `alert alert-${type}`;
    el.innerHTML = `<i class="fas fa-${type === 'error' ? 'circle-exclamation' : type === 'success' ? 'circle-check' : 'circle-info'}"></i> ${msg}`;
    el.style.display = 'flex';
  }
  function hideAlert(id) {
    document.getElementById(id).style.display = 'none';
  }

  /* ── PASSWORD STRENGTH ── */
  document.getElementById('newPassword')?.addEventListener('input', function() {
    const val = this.value;
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const colors = ['', '#e74c3c', '#e67e22', '#f1c40f', '#2ecc71'];
    const labels = ['', 'Weak', 'Fair', 'Good', 'Strong'];
    document.getElementById('strengthFill').style.width = `${score * 25}%`;
    document.getElementById('strengthFill').style.background = colors[score];
    document.getElementById('strengthLabel').textContent = val ? `Password strength: ${labels[score]}` : '';
  });

  /* ── FORGOT PASSWORD LOGIC ──
     If the user typed their email into the email field and
     clicks "Forgot Password", we capture that email and
     show the reset form directly. ──────────────────────── */
  function checkForgotPassword(e) {
    e.preventDefault();
    const emailVal = document.getElementById('loginEmail').value.trim();
    if (!emailVal) {
      showAlert('loginAlert', 'Please enter your email address first, then click "Forgot password?"', 'info');
      document.getElementById('loginEmail').focus();
      return;
    }
    // Valid email? Show reset section
    if (/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailVal)) {
      showResetSection(emailVal);
    } else {
      showAlert('loginAlert', 'Please enter a valid email address.', 'error');
    }
  }

  function showResetSection(email) {
    document.getElementById('loginSection').style.display = 'none';
    document.getElementById('resetSection').style.display = 'block';
    document.getElementById('resetEmail').value = email;
    document.getElementById('resetEmailDisplay').value = email;
    hideAlert('resetAlert');
  }

  function showLoginSection() {
    document.getElementById('resetSection').style.display = 'none';
    document.getElementById('loginSection').style.display = 'block';
    hideAlert('loginAlert');
  }

  /* ── HANDLE LOGIN SUBMIT ── */
  async function handleLogin(e) {
    e.preventDefault();
    hideAlert('loginAlert');
    const btn = document.getElementById('loginBtn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner"></span> Signing in...`;

    const formData = new FormData(e.target);

    try {
      const res = await fetch('../../backend/auth/login.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        showAlert('loginAlert', 'Login successful! Redirecting...', 'success');
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 900);
      } else {
        showAlert('loginAlert', data.message || 'Invalid credentials. Please try again.');
        btn.disabled = false;
        btn.innerHTML = orig;
      }
    } catch (err) {
      showAlert('loginAlert', 'Connection error. Please try again.');
      btn.disabled = false;
      btn.innerHTML = orig;
    }
  }

  /* ── HANDLE RESET SUBMIT ── */
  async function handleReset(e) {
    e.preventDefault();
    hideAlert('resetAlert');
    const np = document.getElementById('newPassword').value;
    const cp = document.getElementById('confirmPassword').value;

    if (np !== cp) {
      showAlert('resetAlert', 'Passwords do not match.', 'error');
      return;
    }
    if (np.length < 8) {
      showAlert('resetAlert', 'Password must be at least 8 characters.', 'error');
      return;
    }

    const formData = new FormData(e.target);

    try {
      const res = await fetch('../../backend/auth/reset_password.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        showAlert('resetAlert', 'Password reset successfully! Redirecting to login...', 'success');
        setTimeout(() => showLoginSection(), 2000);
      } else {
        showAlert('resetAlert', data.message || 'Failed to reset password.', 'error');
      }
    } catch (err) {
      showAlert('resetAlert', 'Connection error. Please try again.', 'error');
    }
  }
</script>
</body>
</html>