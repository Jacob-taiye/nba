<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>NBALOGMARKETPLACE — Your Digital Services Hub</title>
  <link rel="stylesheet" href="frontend/css/style.css" />
  <link rel="stylesheet" href="frontend/css/index.css" />
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <style>
    /* ── NAVBAR ── */
    .navbar {
      position: fixed; top: 0; left: 0; right: 0;
      z-index: 1000;
      padding: 0 5%;
      height: 72px;
      display: flex; align-items: center; justify-content: space-between;
      background: rgba(255,255,255,0.92);
      backdrop-filter: blur(14px);
      border-bottom: 1.5px solid rgba(204,227,245,0.7);
      transition: box-shadow 0.3s;
    }
    .navbar.scrolled { box-shadow: 0 4px 20px rgba(26,140,255,0.12); }
    .nav-links { display: flex; align-items: center; gap: 32px; }
    .nav-links a {
      font-size: 0.93rem; font-weight: 600;
      color: var(--text-mid);
      transition: color 0.2s;
      position: relative;
    }
    .nav-links a::after {
      content: '';
      position: absolute; bottom: -4px; left: 0; right: 0;
      height: 2px; background: var(--primary);
      transform: scaleX(0); transition: transform 0.2s;
    }
    .nav-links a:hover { color: var(--primary); }
    .nav-links a:hover::after { transform: scaleX(1); }
    .nav-actions { display: flex; align-items: center; gap: 12px; }
    .hamburger {
      display: none; flex-direction: column; gap: 5px;
      cursor: pointer; padding: 8px;
    }
    .hamburger span {
      width: 24px; height: 2.5px;
      background: var(--text-dark); border-radius: 2px;
      transition: var(--transition);
    }
    .mobile-menu {
      display: none;
      position: fixed; top: 72px; left: 0; right: 0;
      background: white;
      border-bottom: 1.5px solid var(--border);
      padding: 20px 5% 28px;
      z-index: 999;
      box-shadow: var(--shadow-md);
    }
    .mobile-menu.open { display: block; }
    .mobile-menu a {
      display: block; padding: 12px 0;
      font-weight: 600; color: var(--text-mid);
      border-bottom: 1px solid var(--border);
      font-size: 1rem;
    }
    .mobile-menu a:last-child { border-bottom: none; }
    .mobile-menu .btn { margin-top: 16px; width: 100%; justify-content: center; }

    /* ── HERO ── */
    .hero {
      min-height: 100vh;
      background: linear-gradient(160deg, #f0f8ff 0%, #e3f2ff 40%, #f5fbff 100%);
      display: flex; align-items: center;
      padding: 100px 5% 60px;
      position: relative;
      overflow: hidden;
    }
    .hero::before {
      content: '';
      position: absolute; top: -120px; right: -80px;
      width: 650px; height: 650px;
      background: radial-gradient(circle, rgba(26,140,255,0.12) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero::after {
      content: '';
      position: absolute; bottom: -60px; left: -60px;
      width: 400px; height: 400px;
      background: radial-gradient(circle, rgba(0,198,174,0.1) 0%, transparent 70%);
      pointer-events: none;
    }
    .hero-inner {
      display: grid; grid-template-columns: 1fr 1fr;
      gap: 60px; align-items: center;
      max-width: 1200px; margin: 0 auto; width: 100%;
      position: relative; z-index: 1;
    }
    .hero-badge {
      display: inline-flex; align-items: center; gap: 8px;
      background: white;
      border: 1.5px solid var(--primary-mid);
      color: var(--primary);
      padding: 7px 16px;
      border-radius: 100px;
      font-size: 0.82rem; font-weight: 700;
      letter-spacing: 0.5px;
      margin-bottom: 22px;
      box-shadow: 0 2px 10px rgba(26,140,255,0.1);
    }
    .hero-badge i { font-size: 0.75rem; }
    .hero-title {
      font-family: var(--font-head);
      font-size: clamp(2.2rem, 5vw, 3.6rem);
      font-weight: 800;
      line-height: 1.1;
      letter-spacing: -1.5px;
      color: var(--text-dark);
      margin-bottom: 20px;
    }
    .hero-title .highlight {
      color: var(--primary);
      position: relative;
    }
    .hero-title .highlight::after {
      content: '';
      position: absolute;
      bottom: 2px; left: 0; right: 0;
      height: 8px;
      background: rgba(26,140,255,0.15);
      z-index: -1;
      border-radius: 4px;
    }
    .hero-sub {
      font-size: 1.1rem; color: var(--text-light);
      line-height: 1.75; margin-bottom: 36px;
      max-width: 500px;
    }
    .hero-actions { display: flex; gap: 14px; flex-wrap: wrap; align-items: center; }
    .hero-trust {
      margin-top: 36px;
      display: flex; align-items: center; gap: 20px;
    }
    .trust-avatars { display: flex; }
    .trust-avatars .av {
      width: 34px; height: 34px; border-radius: 50%;
      border: 2.5px solid white;
      background: linear-gradient(135deg, var(--primary), var(--accent));
      margin-left: -8px; display: flex; align-items: center; justify-content: center;
      font-size: 0.7rem; font-weight: 700; color: white;
    }
    .trust-avatars .av:first-child { margin-left: 0; }
    .trust-text { font-size: 0.88rem; color: var(--text-light); }
    .trust-text strong { color: var(--text-dark); }

    /* ── HERO VISUAL ── */
    .hero-visual { position: relative; }
    .hero-card-main {
      background: white;
      border-radius: 24px;
      padding: 30px;
      box-shadow: 0 20px 60px rgba(26,140,255,0.18);
      border: 1.5px solid var(--border);
      animation: floatCard 4s ease-in-out infinite;
    }
    @keyframes floatCard {
      0%, 100% { transform: translateY(0px); }
      50% { transform: translateY(-12px); }
    }
    .hero-card-header {
      display: flex; align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }
    .hero-card-title {
      font-family: var(--font-head);
      font-size: 1rem; font-weight: 700;
      color: var(--text-dark);
    }
    .live-dot {
      display: flex; align-items: center; gap: 6px;
      font-size: 0.78rem; color: #0a8c6a; font-weight: 600;
    }
    .live-dot::before {
      content: '';
      width: 8px; height: 8px; background: #2ecc71;
      border-radius: 50%;
      animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50% { opacity: 0.5; transform: scale(0.85); }
    }
    .service-list { display: flex; flex-direction: column; gap: 14px; }
    .service-item {
      display: flex; align-items: center; gap: 14px;
      padding: 14px 16px;
      background: var(--sky);
      border-radius: var(--radius-sm);
      transition: var(--transition);
    }
    .service-item:hover { background: var(--primary-light); }
    .service-icon {
      width: 40px; height: 40px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; flex-shrink: 0;
    }
    .service-info { flex: 1; }
    .service-name { font-weight: 600; font-size: 0.9rem; color: var(--text-dark); }
    .service-desc { font-size: 0.78rem; color: var(--text-light); margin-top: 2px; }
    .service-price {
      font-family: var(--font-head);
      font-weight: 700; font-size: 0.92rem; color: var(--primary);
    }
    .hero-badge-float {
      position: absolute;
      background: white; border-radius: 14px;
      padding: 12px 16px; font-size: 0.82rem; font-weight: 600;
      box-shadow: 0 8px 24px rgba(26,140,255,0.15);
      border: 1.5px solid var(--border);
      display: flex; align-items: center; gap: 10px;
      white-space: nowrap;
    }
    .float-1 { top: -20px; right: -20px; color: var(--accent-dark); }
    .float-2 { bottom: -16px; left: -24px; color: var(--primary); }
    .float-icon { font-size: 1.1rem; }

    /* ── STATS STRIP ── */
    .stats-strip {
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      padding: 40px 5%;
    }
    .stats-grid {
      max-width: 1100px; margin: 0 auto;
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 30px; text-align: center;
    }
    .stat-item { color: white; }
    .stat-number {
      font-family: var(--font-head);
      font-size: 2.2rem; font-weight: 800;
      line-height: 1;
      margin-bottom: 6px;
    }
    .stat-label { font-size: 0.88rem; opacity: 0.82; }

    /* ── SERVICES SECTION ── */
    .services { padding: 90px 5%; background: var(--white); }
    .services-inner { max-width: 1200px; margin: 0 auto; }
    .services-header { text-align: center; margin-bottom: 56px; }
    .services-header .section-sub { margin: 0 auto; }
    .services-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 24px;
    }
    .service-card {
      background: white;
      border: 1.5px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 32px 28px;
      transition: var(--transition);
      position: relative;
      overflow: hidden;
    }
    .service-card::before {
      content: '';
      position: absolute; top: 0; left: 0; right: 0;
      height: 4px;
      background: linear-gradient(90deg, var(--primary), var(--accent));
      opacity: 0; transition: opacity 0.3s;
    }
    .service-card:hover {
      box-shadow: var(--shadow-lg);
      transform: translateY(-6px);
      border-color: var(--primary-mid);
    }
    .service-card:hover::before { opacity: 1; }
    .sc-icon {
      width: 60px; height: 60px;
      border-radius: 16px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.5rem; margin-bottom: 22px;
    }
    .sc-title {
      font-family: var(--font-head);
      font-size: 1.15rem; font-weight: 700;
      color: var(--text-dark); margin-bottom: 10px;
    }
    .sc-desc {
      font-size: 0.92rem; color: var(--text-light);
      line-height: 1.65; margin-bottom: 20px;
    }
    .sc-link {
      display: inline-flex; align-items: center; gap: 6px;
      color: var(--primary); font-weight: 600; font-size: 0.9rem;
      transition: gap 0.2s;
    }
    .sc-link:hover { gap: 10px; }

    /* ── HOW IT WORKS ── */
    .how-it-works {
      padding: 90px 5%;
      background: linear-gradient(180deg, #f5fbff 0%, white 100%);
    }
    .hiw-inner { max-width: 1100px; margin: 0 auto; }
    .hiw-header { text-align: center; margin-bottom: 60px; }
    .hiw-steps {
      display: grid; grid-template-columns: repeat(4, 1fr);
      gap: 32px; position: relative;
    }
    .hiw-steps::before {
      content: '';
      position: absolute;
      top: 30px; left: 12%; right: 12%;
      height: 2px;
      background: linear-gradient(90deg, var(--primary-mid), var(--accent));
      z-index: 0;
    }
    .hiw-step { text-align: center; position: relative; z-index: 1; }
    .step-num {
      width: 60px; height: 60px;
      background: linear-gradient(135deg, var(--primary), var(--primary-dark));
      color: white; border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-family: var(--font-head); font-weight: 800; font-size: 1.2rem;
      margin: 0 auto 20px;
      box-shadow: 0 6px 20px rgba(26,140,255,0.3);
      border: 4px solid white;
    }
    .step-title {
      font-family: var(--font-head);
      font-weight: 700; font-size: 1rem; margin-bottom: 10px;
    }
    .step-text { font-size: 0.88rem; color: var(--text-light); line-height: 1.6; }

    /* ── CTA SECTION ── */
    .cta-section {
      padding: 80px 5%;
      background: linear-gradient(135deg, var(--primary-dark) 0%, #003d80 100%);
      position: relative; overflow: hidden;
    }
    .cta-section::before {
      content: '';
      position: absolute; top: -100px; right: -60px;
      width: 500px; height: 500px;
      background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 70%);
    }
    .cta-inner {
      max-width: 700px; margin: 0 auto;
      text-align: center; position: relative; z-index: 1;
    }
    .cta-title {
      font-family: var(--font-head);
      font-size: clamp(1.8rem, 3.5vw, 2.8rem);
      font-weight: 800; color: white;
      margin-bottom: 16px; letter-spacing: -0.8px;
    }
    .cta-sub { color: rgba(255,255,255,0.75); font-size: 1.05rem; margin-bottom: 36px; }
    .cta-actions { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
    .btn-white {
      background: white; color: var(--primary-dark);
      font-weight: 700;
    }
    .btn-white:hover { background: var(--sky); transform: translateY(-2px); }
    .btn-ghost {
      background: transparent; color: white;
      border: 2px solid rgba(255,255,255,0.4);
      font-weight: 600;
    }
    .btn-ghost:hover {
      background: rgba(255,255,255,0.1);
      border-color: white;
      transform: translateY(-2px);
    }

    /* ── FOOTER ── */
    footer {
      background: var(--text-dark);
      color: rgba(255,255,255,0.75);
      padding: 60px 5% 30px;
    }
    .footer-inner {
      max-width: 1200px; margin: 0 auto;
    }
    .footer-top {
      display: grid; grid-template-columns: 1.4fr 1fr 1fr 1fr;
      gap: 48px; margin-bottom: 48px;
    }
    .footer-brand .logo { color: white; margin-bottom: 16px; }
    .footer-brand .logo .logo-icon { background: linear-gradient(135deg, var(--primary), var(--accent)); }
    .footer-brand p {
      font-size: 0.9rem; line-height: 1.7;
      color: rgba(255,255,255,0.55); max-width: 260px;
    }
    .footer-col h4 {
      font-family: var(--font-head);
      font-size: 0.9rem; font-weight: 700;
      color: white; text-transform: uppercase;
      letter-spacing: 0.8px; margin-bottom: 18px;
    }
    .footer-col ul li { margin-bottom: 11px; }
    .footer-col ul li a {
      font-size: 0.9rem;
      color: rgba(255,255,255,0.55);
      transition: color 0.2s;
    }
    .footer-col ul li a:hover { color: var(--primary-mid); }
    .footer-bottom {
      display: flex; align-items: center; justify-content: space-between;
      border-top: 1px solid rgba(255,255,255,0.1);
      padding-top: 24px; font-size: 0.85rem;
      color: rgba(255,255,255,0.4);
      flex-wrap: wrap; gap: 14px;
    }
    .social-links { display: flex; gap: 14px; }
    .social-links a {
      width: 36px; height: 36px;
      background: rgba(255,255,255,0.08);
      border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      color: rgba(255,255,255,0.6); font-size: 0.95rem;
      transition: var(--transition);
    }
    .social-links a:hover { background: var(--primary); color: white; }

    /* ── ANIMATIONS ── */
    .fade-up {
      opacity: 0; transform: translateY(28px);
      transition: opacity 0.6s ease, transform 0.6s ease;
    }
    .fade-up.visible { opacity: 1; transform: none; }

    /* ── RESPONSIVE ── */
    @media (max-width: 1024px) {
      .services-grid { grid-template-columns: repeat(2, 1fr); }
      .hiw-steps { grid-template-columns: repeat(2, 1fr); }
      .hiw-steps::before { display: none; }
      .footer-top { grid-template-columns: 1fr 1fr; }
      .stats-grid { grid-template-columns: repeat(2, 1fr); }
    }
    @media (max-width: 768px) {
      .nav-links, .nav-actions { display: none; }
      .hamburger { display: flex; }
      .hero-inner { grid-template-columns: 1fr; text-align: center; }
      .hero-visual { display: none; }
      .hero-sub { max-width: 100%; }
      .hero-actions, .hero-trust { justify-content: center; }
      .services-grid { grid-template-columns: 1fr; }
      .hiw-steps { grid-template-columns: 1fr 1fr; }
      .footer-top { grid-template-columns: 1fr 1fr; }
    }
    @media (max-width: 480px) {
      .hiw-steps { grid-template-columns: 1fr; }
      .footer-top { grid-template-columns: 1fr; }
      .stats-grid { grid-template-columns: 1fr 1fr; }
      .footer-bottom { flex-direction: column; text-align: center; }
    }
  </style>
</head>
<body>

<!-- ═══════════════════════════════
     NAVBAR
═══════════════════════════════ -->
<nav class="navbar" id="navbar">
  <a href="index.php" class="logo">
    <div class="logo-icon">NBA</div>
    NBALOG<span>MARKETPLACE</span>
  </a>

  <ul class="nav-links">
    <li><a href="#services">Services</a></li>
    <li><a href="#how-it-works">How It Works</a></li>
    <li><a href="#pricing">Pricing</a></li>
    <li><a href="#contact">Contact</a></li>
  </ul>

  <div class="nav-actions">
    <a href="frontend/pages/login.php" class="btn btn-outline btn-sm">Sign In</a>
    <a href="frontend/pages/signup.php" class="btn btn-primary btn-sm">Get Started</a>
  </div>

  <div class="hamburger" id="hamburger" onclick="toggleMenu()">
    <span></span><span></span><span></span>
  </div>
</nav>

<!-- Mobile Menu -->
<div class="mobile-menu" id="mobileMenu">
  <a href="#services">Services</a>
  <a href="#how-it-works">How It Works</a>
  <a href="#pricing">Pricing</a>
  <a href="#contact">Contact</a>
  <a href="frontend/pages/login.php" class="btn btn-outline">Sign In</a>
  <a href="frontend/pages/signup.php" class="btn btn-primary">Get Started</a>
</div>

<!-- ═══════════════════════════════
     HERO
═══════════════════════════════ -->
<section class="hero">
  <div class="hero-inner">
    <div class="hero-content">
      <div class="hero-badge fade-up">
        <i class="fas fa-bolt"></i>
        #1 Digital Services Platform
      </div>

      <h1 class="hero-title fade-up">
        Your All-in-One<br>
        <span class="highlight">Digital Services</span><br>
        Hub
      </h1>

      <p class="hero-sub fade-up">
        Virtual numbers, social media boosting, secure logins, premium tools and working pictures — everything you need to grow online, in one place.
      </p>

      <div class="hero-actions fade-up">
        <a href="frontend/pages/signup.php" class="btn btn-primary btn-lg">
          <i class="fas fa-rocket"></i> Start for Free
        </a>
        <a href="#services" class="btn btn-outline btn-lg">
          Explore Services
        </a>
      </div>

      <div class="hero-trust fade-up">
        <div class="trust-avatars">
          <div class="av">JO</div>
          <div class="av">AM</div>
          <div class="av">KF</div>
          <div class="av">+</div>
        </div>
        <div class="trust-text">
          <strong>5,000+</strong> happy customers already using NBALOGMARKETPLACE
        </div>
      </div>
    </div>

    <div class="hero-visual fade-up">
      <div class="hero-badge-float float-1">
        <span class="float-icon">✅</span> SMS Received!
      </div>

      <div class="hero-card-main">
        <div class="hero-card-header">
          <div class="hero-card-title">Available Services</div>
          <div class="live-dot">Live</div>
        </div>

        <div class="service-list">
          <div class="service-item">
            <div class="service-icon" style="background:#e8f4ff">📱</div>
            <div class="service-info">
              <div class="service-name">Virtual Numbers</div>
              <div class="service-desc">USA, UK, NG & more</div>
            </div>
            <div class="service-price">₦150+</div>
          </div>
          <div class="service-item">
            <div class="service-icon" style="background:#e8fff8">🚀</div>
            <div class="service-info">
              <div class="service-name">Social Boosting</div>
              <div class="service-desc">Followers, Likes, Views</div>
            </div>
            <div class="service-price">₦200+</div>
          </div>
          <div class="service-item">
            <div class="service-icon" style="background:#fff3e8">🔑</div>
            <div class="service-info">
              <div class="service-name">Social Logins</div>
              <div class="service-desc">Facebook, IG, TikTok</div>
            </div>
            <div class="service-price">₦500+</div>
          </div>
          <div class="service-item">
            <div class="service-icon" style="background:#f3e8ff">🖼️</div>
            <div class="service-info">
              <div class="service-name">Working Pictures</div>
              <div class="service-desc">Premium quality images</div>
            </div>
            <div class="service-price">₦300+</div>
          </div>
        </div>
      </div>

      <div class="hero-badge-float float-2">
        <span class="float-icon">💰</span>
        Balance: <strong style="color:var(--primary);margin-left:4px">₦12,500</strong>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════
     STATS STRIP
═══════════════════════════════ -->
<div class="stats-strip">
  <div class="stats-grid">
    <div class="stat-item fade-up">
      <div class="stat-number" data-target="5000">5,000+</div>
      <div class="stat-label">Registered Users</div>
    </div>
    <div class="stat-item fade-up">
      <div class="stat-number">50,000+</div>
      <div class="stat-label">Transactions Completed</div>
    </div>
    <div class="stat-item fade-up">
      <div class="stat-number">99.9%</div>
      <div class="stat-label">Uptime Guaranteed</div>
    </div>
    <div class="stat-item fade-up">
      <div class="stat-number">24/7</div>
      <div class="stat-label">Customer Support</div>
    </div>
  </div>
</div>

<!-- ═══════════════════════════════
     SERVICES
═══════════════════════════════ -->
<section class="services" id="services">
  <div class="services-inner">
    <div class="services-header fade-up">
      <div class="section-tag"><i class="fas fa-stars"></i> What We Offer</div>
      <h2 class="section-title">Everything You Need,<br>In One Place</h2>
      <p class="section-sub">
        From virtual phone numbers to social media boosting, we provide premium digital services at competitive prices with instant delivery.
      </p>
    </div>

    <div class="services-grid">
      <!-- Virtual Numbers -->
      <div class="service-card fade-up">
        <div class="sc-icon" style="background:#e8f4ff; color:var(--primary)">
          <i class="fas fa-sim-card"></i>
        </div>
        <div class="sc-title">Virtual Numbers</div>
        <p class="sc-desc">
          Get temporary phone numbers from 50+ countries instantly. Perfect for SMS verification on any platform.
        </p>
        <a href="frontend/pages/signup.php" class="sc-link">
          Get a Number <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <!-- Social Boosting -->
      <div class="service-card fade-up">
        <div class="sc-icon" style="background:#e8fff8; color:var(--accent-dark)">
          <i class="fas fa-rocket"></i>
        </div>
        <div class="sc-title">Social Media Boosting</div>
        <p class="sc-desc">
          Grow your social media presence with followers, likes, views and engagement across all major platforms.
        </p>
        <a href="frontend/pages/signup.php" class="sc-link">
          Boost Now <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <!-- Social Logins -->
      <div class="service-card fade-up">
        <div class="sc-icon" style="background:#fff3e8; color:#e67e22">
          <i class="fas fa-key"></i>
        </div>
        <div class="sc-title">Social Logins</div>
        <p class="sc-desc">
          Access verified social media accounts across Facebook, Instagram, TikTok, Twitter and more.
        </p>
        <a href="frontend/pages/signup.php" class="sc-link">
          Browse Logins <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <!-- Working Pictures -->
      <div class="service-card fade-up">
        <div class="sc-icon" style="background:#f3e8ff; color:#8e44ad">
          <i class="fas fa-images"></i>
        </div>
        <div class="sc-title">Working Pictures</div>
        <p class="sc-desc">
          Premium verified picture packages for your business or personal use with instant access links.
        </p>
        <a href="frontend/pages/signup.php" class="sc-link">
          View Gallery <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <!-- Formats & Tools -->
      <div class="service-card fade-up">
        <div class="sc-icon" style="background:#ffeef0; color:#e74c3c">
          <i class="fas fa-tools"></i>
        </div>
        <div class="sc-title">Formats & Tools</div>
        <p class="sc-desc">
          Access professional document formats, templates, and productivity tools to help streamline your work.
        </p>
        <a href="frontend/pages/signup.php" class="sc-link">
          See Tools <i class="fas fa-arrow-right"></i>
        </a>
      </div>

      <!-- Top Up -->
      <div class="service-card fade-up">
        <div class="sc-icon" style="background:#e8f9ff; color:#0984e3">
          <i class="fas fa-wallet"></i>
        </div>
        <div class="sc-title">Easy Top-Up</div>
        <p class="sc-desc">
          Fund your wallet instantly via Flutterwave. Secure, fast and reliable payment processing you can trust.
        </p>
        <a href="frontend/pages/signup.php" class="sc-link">
          Top Up Now <i class="fas fa-arrow-right"></i>
        </a>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════
     HOW IT WORKS
═══════════════════════════════ -->
<section class="how-it-works" id="how-it-works">
  <div class="hiw-inner">
    <div class="hiw-header fade-up">
      <div class="section-tag"><i class="fas fa-list-ol"></i> Simple Process</div>
      <h2 class="section-title">Get Started in 4 Easy Steps</h2>
      <p class="section-sub" style="margin: 0 auto">
        Our platform is designed to be as simple and fast as possible, so you spend less time figuring things out and more time getting results.
      </p>
    </div>

    <div class="hiw-steps">
      <div class="hiw-step fade-up">
        <div class="step-num">1</div>
        <div class="step-title">Create Account</div>
        <p class="step-text">Sign up for free in under 60 seconds. No complex verification required.</p>
      </div>
      <div class="hiw-step fade-up">
        <div class="step-num">2</div>
        <div class="step-title">Fund Your Wallet</div>
        <p class="step-text">Top up your balance using Flutterwave — fast, secure, and reliable.</p>
      </div>
      <div class="hiw-step fade-up">
        <div class="step-num">3</div>
        <div class="step-title">Choose a Service</div>
        <p class="step-text">Browse and purchase from our wide range of digital services instantly.</p>
      </div>
      <div class="hiw-step fade-up">
        <div class="step-num">4</div>
        <div class="step-title">Get Delivered</div>
        <p class="step-text">Receive your service immediately. Copy links, codes and credentials in one click.</p>
      </div>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════
     CTA
═══════════════════════════════ -->
<section class="cta-section">
  <div class="cta-inner">
    <h2 class="cta-title fade-up">Ready to Get Started?</h2>
    <p class="cta-sub fade-up">Join thousands of users already using NBALOGMARKETPLACE to power their digital needs.</p>
    <div class="cta-actions fade-up">
      <a href="frontend/pages/signup.php" class="btn btn-white btn-lg">
        <i class="fas fa-user-plus"></i> Create Free Account
      </a>
      <a href="frontend/pages/login.php" class="btn btn-ghost btn-lg">
        Sign In Instead
      </a>
    </div>
  </div>
</section>

<!-- ═══════════════════════════════
     FOOTER
═══════════════════════════════ -->
<footer id="contact">
  <div class="footer-inner">
    <div class="footer-top">
      <div class="footer-brand">
        <a class="logo" href="index.php">
          <div class="logo-icon">NBA</div>
          NBA<span style="color:var(--accent)">MARKETPLACE</span>
        </a>
        <p>Your trusted platform for premium digital services. Fast, secure and always available.</p>
      </div>

      <div class="footer-col">
        <h4>Services</h4>
        <ul>
          <li><a href="#">Virtual Numbers</a></li>
          <li><a href="#">Social Boosting</a></li>
          <li><a href="#">Social Logins</a></li>
          <li><a href="#">Working Pictures</a></li>
          <li><a href="#">Formats & Tools</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Account</h4>
        <ul>
          <li><a href="frontend/pages/signup.php">Sign Up</a></li>
          <li><a href="frontend/pages/login.php">Sign In</a></li>
          <li><a href="#">Top Up</a></li>
          <li><a href="#">Transactions</a></li>
        </ul>
      </div>

      <div class="footer-col">
        <h4>Support</h4>
        <ul>
          <li><a href="#">FAQ</a></li>
          <li><a href="#">Privacy Policy</a></li>
          <li><a href="#">Terms of Service</a></li>
          <li><a href="https://wa.me/2348000000000" target="_blank">WhatsApp Support</a></li>
        </ul>
      </div>
    </div>

    <div class="footer-bottom">
      <span>© 2026 NBALOGMARKETPLACE. All rights reserved.</span>
      <div class="social-links">
        <a href="#"><i class="fab fa-facebook-f"></i></a>
        <a href="#"><i class="fab fa-twitter"></i></a>
        <a href="#"><i class="fab fa-instagram"></i></a>
        <a href="https://wa.me/2348000000000" target="_blank"><i class="fab fa-whatsapp"></i></a>
      </div>
    </div>
  </div>
</footer>

<!-- WhatsApp FAB (visible everywhere) -->
<a href="https://wa.me/2348000000000" target="_blank" class="whatsapp-fab" title="Chat on WhatsApp">
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>

<script>
  // ── NAVBAR SCROLL EFFECT ──
  const navbar = document.getElementById('navbar');
  window.addEventListener('scroll', () => {
    navbar.classList.toggle('scrolled', window.scrollY > 30);
  });

  // ── HAMBURGER MENU ──
  function toggleMenu() {
    document.getElementById('mobileMenu').classList.toggle('open');
  }
  // Close menu on outside click
  document.addEventListener('click', (e) => {
    const menu = document.getElementById('mobileMenu');
    const hamburger = document.getElementById('hamburger');
    if (menu.classList.contains('open') && !menu.contains(e.target) && !hamburger.contains(e.target)) {
      menu.classList.remove('open');
    }
  });

  // ── FADE UP SCROLL ANIMATION ──
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('visible');
      }
    });
  }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });

  document.querySelectorAll('.fade-up').forEach(el => observer.observe(el));

  // ── STAGGER FADE UPS ──
  document.querySelectorAll('.services-grid .service-card, .hiw-steps .hiw-step').forEach((el, i) => {
    el.style.transitionDelay = `${i * 0.1}s`;
  });
</script>
</body>
</html>