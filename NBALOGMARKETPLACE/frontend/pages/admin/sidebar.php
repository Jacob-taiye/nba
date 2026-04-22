<?php
$activePage = $activePage ?? '';
$adminName  = htmlspecialchars($currentAdmin['name']);
$initials   = adminInitials($currentAdmin);
?>
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<aside class="sidebar" id="sidebar">
  <a href="dashboard.php" class="sidebar-logo">
    <div class="logo-icon">NBA</div>
    <div class="logo-text">Admin <span>Panel</span></div>
  </a>

  <nav class="sidebar-nav">
    <div class="nav-label">Overview</div>
    <a href="dashboard.php" class="nav-item <?= $activePage==='dashboard'?'active':'' ?>">
      <i class="fas fa-chart-pie"></i> Dashboard
    </a>

    <div class="nav-label">Manage</div>
    <a href="announcements.php" class="nav-item <?= $activePage==='announcements'?'active':'' ?>">
      <i class="fas fa-bullhorn"></i> Announcements
    </a>
    <a href="users.php" class="nav-item <?= $activePage==='users'?'active':'' ?>">
      <i class="fas fa-users"></i> Users
    </a>
    <a href="transactions.php" class="nav-item <?= $activePage==='transactions'?'active':'' ?>">
      <i class="fas fa-receipt"></i> Transactions
    </a>
    <a href="boost_orders.php" class="nav-item <?= $activePage==='boost-orders'?'active':'' ?>">
      <i class="fas fa-rocket"></i> Boost Orders
    </a>
    <a href="virtual_numbers.php" class="nav-item <?= $activePage==='virtual-numbers'?'active':'' ?>">
      <i class="fas fa-sim-card"></i> Virtual Numbers
    </a>

    <div class="nav-label">Products</div>
    <a href="formats.php" class="nav-item <?= $activePage==='formats'?'active':'' ?>">
      <i class="fas fa-file-lines"></i> Formats
    </a>
    <a href="tools.php" class="nav-item <?= $activePage==='tools'?'active':'' ?>">
      <i class="fas fa-screwdriver-wrench"></i> Updates & Tools
    </a>
    <a href="pictures.php" class="nav-item <?= $activePage==='pictures'?'active':'' ?>">
      <i class="fas fa-images"></i> Working Pictures
    </a>
    <a href="social_logins.php" class="nav-item <?= $activePage==='social-logins'?'active':'' ?>">
      <i class="fas fa-key"></i> Social Logins
    </a>

    <div class="nav-label">Account</div>
    <a href="/NBALOGMARKETPLACE/backend/auth/logout.php" class="nav-item logout-btn">
      <i class="fas fa-right-from-bracket"></i> Logout
    </a>
  </nav>

  <div class="sidebar-user">
    <div class="sidebar-user-avatar"><?= $initials ?></div>
    <div class="sidebar-user-info">
      <div class="sidebar-user-name"><?= $adminName ?></div>
      <div class="sidebar-user-bal" style="color:#7dd3fc">Administrator</div>
    </div>
  </div>
</aside>