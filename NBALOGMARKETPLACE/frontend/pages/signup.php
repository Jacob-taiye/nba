<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Create Account — NBALOGMARKETPLACE</title>
  <link rel="stylesheet" href="../css/style.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    body {
      min-height: 100vh;
      background: linear-gradient(145deg, #e8f4ff 0%, #f5fbff 50%, #e0f0ff 100%);
      display: flex; align-items: center; justify-content: center;
      padding: 30px 20px;
    }
    body::before {
      content: '';
      position: fixed; top: -120px; right: -80px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(26,140,255,0.15) 0%, transparent 70%);
      pointer-events: none;
    }
    body::after {
      content: '';
      position: fixed; bottom: -60px; left: -60px;
      width: 360px; height: 360px;
      background: radial-gradient(circle, rgba(0,198,174,0.12) 0%, transparent 70%);
      pointer-events: none;
    }

    .auth-wrapper {
      width: 100%; max-width: 520px;
      position: relative; z-index: 1;
    }
    .auth-logo-bar { text-align: center; margin-bottom: 28px; }

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
      margin: 0 auto 18px; font-size: 1.6rem;
      box-shadow: 0 4px 16px rgba(26,140,255,0.15);
    }
    .auth-title {
      font-family: var(--font-head);
      font-size: 1.7rem; font-weight: 800;
      color: var(--text-dark); margin-bottom: 8px;
      letter-spacing: -0.5px;
    }
    .auth-sub { font-size: 0.93rem; color: var(--text-light); }

    .form-row {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 16px;
    }

    .input-group { position: relative; }
    .input-group .form-control { padding-right: 48px; }
    .toggle-pw {
      position: absolute; right: 14px; top: 50%;
      transform: translateY(-50%);
      background: none; border: none; cursor: pointer;
      color: var(--text-light); font-size: 1rem; padding: 4px;
      transition: color 0.2s;
    }
    .toggle-pw:hover { color: var(--primary); }

    /* Password strength */
    .strength-wrapper { margin-top: 8px; }
    .strength-bars { display: flex; gap: 4px; margin-bottom: 4px; }
    .strength-bar {
      flex: 1; height: 4px; border-radius: 2px;
      background: var(--border); transition: background 0.3s;
    }
    .strength-text { font-size: 0.78rem; color: var(--text-light); }

    /* Terms checkbox */
    .terms-check {
      display: flex; align-items: flex-start; gap: 12px;
      margin-bottom: 24px;
    }
    .terms-check input[type="checkbox"] {
      width: 18px; height: 18px; flex-shrink: 0;
      margin-top: 2px; accent-color: var(--primary); cursor: pointer;
    }
    .terms-check label {
      font-size: 0.88rem; color: var(--text-mid); cursor: pointer;
    }
    .terms-check a { color: var(--primary); font-weight: 600; }

    #signupAlert { display: none; margin-bottom: 18px; }

    .btn-full { width: 100%; justify-content: center; }

    .auth-footer {
      text-align: center; margin-top: 22px;
      font-size: 0.9rem; color: var(--text-light);
    }
    .auth-footer a { color: var(--primary); font-weight: 600; }

    /* Perks row */
    .signup-perks {
      display: flex; gap: 10px; flex-wrap: wrap;
      justify-content: center; margin-bottom: 24px;
    }
    .perk-badge {
      display: inline-flex; align-items: center; gap: 6px;
      background: var(--sky); color: var(--text-mid);
      padding: 5px 12px; border-radius: 100px;
      font-size: 0.8rem; font-weight: 600;
    }
    .perk-badge i { color: var(--accent); }

    @media (max-width: 520px) {
      .auth-card { padding: 32px 22px; }
      .form-row { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-logo-bar">
    <a href="../../index.php" class="logo" style="justify-content:center">
      <div class="logo-icon">NBA</div>
      NBALOG<span>MARKETPLACE</span>
    </a>
  </div>

  <div class="auth-card">
    <div class="auth-header">
      <div class="auth-icon">✨</div>
      <h1 class="auth-title">Create Your Account</h1>
      <p class="auth-sub">Join 5,000+ users on NBALOGMARKETPLACE</p>
    </div>

    <!-- Perks -->
    <div class="signup-perks">
      <span class="perk-badge"><i class="fas fa-check-circle"></i> Free to join</span>
      <span class="perk-badge"><i class="fas fa-bolt"></i> Instant access</span>
      <span class="perk-badge"><i class="fas fa-shield-alt"></i> 100% Secure</span>
    </div>

    <!-- Alert -->
    <div id="signupAlert" class="alert"></div>

    <form id="signupForm" onsubmit="handleSignup(event)" novalidate>

      <div class="form-row">
        <div class="form-group">
          <label class="form-label">First Name</label>
          <input
            type="text"
            class="form-control"
            id="firstName"
            name="first_name"
            placeholder="John"
            required
            autocomplete="given-name"
          />
        </div>
        <div class="form-group">
          <label class="form-label">Last Name</label>
          <input
            type="text"
            class="form-control"
            id="lastName"
            name="last_name"
            placeholder="Doe"
            required
            autocomplete="family-name"
          />
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Username</label>
        <input
          type="text"
          class="form-control"
          id="username"
          name="username"
          placeholder="johndoe"
          required
          autocomplete="username"
          minlength="3"
        />
      </div>

      <div class="form-group">
        <label class="form-label">Email Address</label>
        <input
          type="email"
          class="form-control"
          id="email"
          name="email"
          placeholder="you@example.com"
          required
          autocomplete="email"
        />
      </div>

      <div class="form-group">
        <label class="form-label">Phone Number</label>
        <input
          type="tel"
          class="form-control"
          id="phone"
          name="phone"
          placeholder="+234 800 0000 000"
          autocomplete="tel"
        />
      </div>

      <div class="form-group">
        <label class="form-label">Password</label>
        <div class="input-group">
          <input
            type="password"
            class="form-control"
            id="password"
            name="password"
            placeholder="At least 8 characters"
            required
            minlength="8"
            autocomplete="new-password"
          />
          <button type="button" class="toggle-pw" onclick="togglePassword('password', this)">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <div class="strength-wrapper">
          <div class="strength-bars">
            <div class="strength-bar" id="sb1"></div>
            <div class="strength-bar" id="sb2"></div>
            <div class="strength-bar" id="sb3"></div>
            <div class="strength-bar" id="sb4"></div>
          </div>
          <div class="strength-text" id="strengthText"></div>
        </div>
      </div>

      <div class="form-group">
        <label class="form-label">Confirm Password</label>
        <div class="input-group">
          <input
            type="password"
            class="form-control"
            id="confirmPass"
            name="confirm_password"
            placeholder="Repeat your password"
            required
            autocomplete="new-password"
          />
          <button type="button" class="toggle-pw" onclick="togglePassword('confirmPass', this)">
            <i class="fas fa-eye"></i>
          </button>
        </div>
        <div id="matchMsg" style="font-size:0.8rem;margin-top:5px"></div>
      </div>

      <div class="terms-check">
        <input type="checkbox" id="terms" name="terms" required />
        <label for="terms">
          I agree to the <a href="#" target="_blank">Terms of Service</a> and
          <a href="#" target="_blank">Privacy Policy</a>
        </label>
      </div>

      <button type="submit" class="btn btn-primary btn-lg btn-full" id="signupBtn">
        <i class="fas fa-user-plus"></i> Create Account
      </button>
    </form>

    <div class="auth-footer mt-2">
      Already have an account? <a href="login.php">Sign in</a>
    </div>
  </div>
</div>

<script>
  /* ── PASSWORD VISIBILITY ── */
  function togglePassword(fieldId, btn) {
    const field = document.getElementById(fieldId);
    const icon  = btn.querySelector('i');
    field.type  = field.type === 'password' ? 'text' : 'password';
    icon.className = field.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
  }

  /* ── PASSWORD STRENGTH ── */
  document.getElementById('password').addEventListener('input', function() {
    const v = this.value;
    let score = 0;
    if (v.length >= 8) score++;
    if (/[A-Z]/.test(v)) score++;
    if (/[0-9]/.test(v)) score++;
    if (/[^A-Za-z0-9]/.test(v)) score++;

    const colors = ['var(--border)','#e74c3c','#e67e22','#f1c40f','#2ecc71'];
    const labels = ['','Weak — use uppercase, numbers & symbols','Fair','Good','Strong ✓'];

    for (let i = 1; i <= 4; i++) {
      document.getElementById(`sb${i}`).style.background =
        i <= score ? colors[score] : 'var(--border)';
    }
    document.getElementById('strengthText').textContent = v ? labels[score] : '';
    document.getElementById('strengthText').style.color =
      score <= 1 ? '#e74c3c' : score === 2 ? '#e67e22' : score === 3 ? '#d4ac0d' : '#27ae60';
  });

  /* ── PASSWORD MATCH ── */
  document.getElementById('confirmPass').addEventListener('input', function() {
    const match = this.value === document.getElementById('password').value;
    const el    = document.getElementById('matchMsg');
    el.textContent = this.value
      ? (match ? '✓ Passwords match' : '✗ Passwords do not match')
      : '';
    el.style.color = match ? '#27ae60' : '#e74c3c';
  });

  /* ── ALERTS ── */
  function showAlert(msg, type = 'error') {
    const el = document.getElementById('signupAlert');
    const icons = { error: 'circle-exclamation', success: 'circle-check', info: 'circle-info' };
    el.className = `alert alert-${type}`;
    el.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${msg}`;
    el.style.display = 'flex';
    el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
  }

  /* ── HANDLE SIGNUP ── */
  async function handleSignup(e) {
    e.preventDefault();

    // Client-side validation
    const pw  = document.getElementById('password').value;
    const cpw = document.getElementById('confirmPass').value;
    const terms = document.getElementById('terms').checked;

    if (pw !== cpw) { showAlert('Passwords do not match.'); return; }
    if (pw.length < 8) { showAlert('Password must be at least 8 characters.'); return; }
    if (!terms) { showAlert('Please accept the Terms of Service to continue.'); return; }

    const btn  = document.getElementById('signupBtn');
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner"></span> Creating account...`;

    const formData = new FormData(e.target);

    try {
      const res  = await fetch('../../backend/auth/register.php', {
        method: 'POST',
        body: formData
      });
      const data = await res.json();

      if (data.success) {
        showAlert('Account created! Redirecting to your dashboard...', 'success');
        setTimeout(() => { window.location.href = data.redirect || '../pages/user/dashboard.php'; }, 1200);
      } else {
        showAlert(data.message || 'Registration failed. Please try again.');
        btn.disabled = false;
        btn.innerHTML = orig;
      }
    } catch (err) {
      showAlert('Connection error. Please try again.');
      btn.disabled = false;
      btn.innerHTML = orig;
    }
  }
</script>
</body>
</html>