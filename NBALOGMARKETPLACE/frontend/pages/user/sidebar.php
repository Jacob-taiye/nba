<?php
/**
 * User Sidebar Include
 * Usage: include __DIR__ . '/sidebar.php';
 * Requires: $currentUser array + $activePage string to be set before including
 */

$activePage = $activePage ?? '';
$whatsapp   = defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '2348000000000';

$initials = userInitials($currentUser);
$balance  = formatMoney((float)$currentUser['balance']);
$name     = htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']);
?>

<!-- ── SIDEBAR OVERLAY (mobile) ── -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">

  <!-- Logo -->
  <a href="dashboard.php" class="sidebar-logo">
    <div class="logo-icon">NBA</div>
    <div class="logo-text">NBALOG<span>MARKETPLACE</span></div>
  </a>

  <nav class="sidebar-nav">
    <div class="nav-label">Main Menu</div>

    <a href="dashboard.php"
       class="nav-item <?= $activePage === 'dashboard' ? 'active' : '' ?>">
      <i class="fas fa-grid-2"></i> Dashboard
    </a>

    <a href="virtual-numbers.php"
       class="nav-item <?= $activePage === 'virtual-numbers' ? 'active' : '' ?>">
      <i class="fas fa-sim-card"></i> Virtual Numbers
    </a>

    <a href="boosting.php"
       class="nav-item <?= $activePage === 'boosting' ? 'active' : '' ?>">
      <i class="fas fa-rocket"></i> Social Boosting
    </a>

    <a href="social-logins.php"
       class="nav-item <?= $activePage === 'social-logins' ? 'active' : '' ?>">
      <i class="fas fa-key"></i> Social Logins
    </a>

    <a href="working-pictures.php"
       class="nav-item <?= $activePage === 'working-pictures' ? 'active' : '' ?>">
      <i class="fas fa-images"></i> Working Pictures
    </a>

    <a href="formats.php"
       class="nav-item <?= $activePage === 'formats' ? 'active' : '' ?>">
      <i class="fas fa-file-lines"></i> Formats
    </a>

    <a href="tools.php"
       class="nav-item <?= $activePage === 'tools' ? 'active' : '' ?>">
      <i class="fas fa-screwdriver-wrench"></i> Updates & Tools
    </a>

    <div class="nav-label" style="margin-top:10px">Account</div>

    <a href="topup.php"
       class="nav-item <?= $activePage === 'topup' ? 'active' : '' ?>">
      <i class="fas fa-wallet"></i> Top Up Balance
    </a>

    <a href="virtual_numbers.php"
       class="nav-item <?= $activePage === 'virtual-numbers' ? 'active' : '' ?>">
      <i class="fas fa-sim-card"></i> Virtual Numbers
    </a>

    <a href="boosting.php"
       class="nav-item <?= $activePage === 'boosting' ? 'active' : '' ?>">
      <i class="fas fa-rocket"></i> Social Boosting
    </a>

    <a href="transactions.php"
       class="nav-item <?= $activePage === 'transactions' ? 'active' : '' ?>">
      <i class="fas fa-receipt"></i> Transactions
    </a>

    <a href="/NBALOGMARKETPLACE/backend/auth/logout.php" class="nav-item logout-btn">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </nav>

  <!-- User info at bottom of sidebar -->
  <div class="sidebar-user">
    <div class="sidebar-user-avatar">
      <?php if (!empty($currentUser['profile_image'])): ?>
        <img src="<?= htmlspecialchars($currentUser['profile_image']) ?>" alt="avatar" />
      <?php else: ?>
        <?= $initials ?>
      <?php endif; ?>
    </div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?= $name ?></div>
      <div class="sidebar-user-bal"><?= $balance ?></div>
    </div>
  </div>

</aside>