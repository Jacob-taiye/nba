<?php
/**
 * VirtualHub Pro — User Dashboard
 */
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';

$activePage = 'dashboard';

// ── Fetch dashboard statistics ────────────────────────────
$uid = $currentUser['id'];

// Total spent
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE user_id=? AND type='purchase' AND status='success'");
$stmt->execute([$uid]);
$totalSpent = (float)$stmt->fetchColumn();

// Total topped up
$stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) as total FROM transactions WHERE user_id=? AND type IN ('topup','admin_topup') AND status='success'");
$stmt->execute([$uid]);
$totalTopup = (float)$stmt->fetchColumn();

// Total transactions count
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id=?");
$stmt->execute([$uid]);
$totalTx = (int)$stmt->fetchColumn();

// Recent transactions (last 6)
$stmt = $pdo->prepare(
    "SELECT * FROM transactions WHERE user_id=? ORDER BY created_at DESC LIMIT 6"
);
$stmt->execute([$uid]);
$recentTx = $stmt->fetchAll();

// Active virtual numbers
$stmt = $pdo->prepare("SELECT COUNT(*) FROM virtual_numbers WHERE user_id=? AND status='waiting'");
$stmt->execute([$uid]);
$activeNums = (int)$stmt->fetchColumn();

$pageTitle = 'Dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Dashboard — NBALOGMARKETPLACE</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dash-shell">

  <?php include __DIR__ . '/sidebar.php'; ?>

  <!-- ══ MAIN ══ -->
  <div class="dash-main">

    <!-- Top Bar -->
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()" id="hamburgerBtn">
          <span></span><span></span><span></span>
        </button>
        <div class="page-title">👋 Welcome back, <?= htmlspecialchars($currentUser['first_name']) ?>!</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance">
          <i class="fas fa-wallet"></i>
          <?= formatMoney((float)$currentUser['balance']) ?>
        </div>
        <div class="topbar-user">
          <div class="topbar-avatar">
            <?php if (!empty($currentUser['profile_image'])): ?>
              <img src="<?= htmlspecialchars($currentUser['profile_image']) ?>" alt="avatar"/>
            <?php else: ?>
              <?= userInitials($currentUser) ?>
            <?php endif; ?>
          </div>
          <span class="topbar-uname"><?= htmlspecialchars($currentUser['first_name']) ?></span>
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <?php include __DIR__ . '/announcement_ticker.php'; ?>

    <div class="dash-content">

      <!-- ── STAT CARDS ── -->
      <div class="stats-row">

        <div class="stat-card" style="--card-color:#1a8cff">
          <div class="stat-icon" style="background:#e8f4ff; color:#1a8cff">
            <i class="fas fa-wallet"></i>
          </div>
          <div class="stat-body">
            <div class="stat-label-sm">Wallet Balance</div>
            <div class="stat-value" id="balanceVal"><?= formatMoney((float)$currentUser['balance']) ?></div>
            <div class="stat-change neutral"><i class="fas fa-circle-info"></i> Available to spend</div>
          </div>
        </div>

        <div class="stat-card" style="--card-color:#0a8c6a">
          <div class="stat-icon" style="background:#e6faf5; color:#0a8c6a">
            <i class="fas fa-arrow-up-from-bracket"></i>
          </div>
          <div class="stat-body">
            <div class="stat-label-sm">Total Topped Up</div>
            <div class="stat-value"><?= formatMoney($totalTopup) ?></div>
            <div class="stat-change up"><i class="fas fa-arrow-up"></i> All time</div>
          </div>
        </div>

        <div class="stat-card" style="--card-color:#e67e22">
          <div class="stat-icon" style="background:#fff3e8; color:#e67e22">
            <i class="fas fa-shopping-bag"></i>
          </div>
          <div class="stat-body">
            <div class="stat-label-sm">Total Spent</div>
            <div class="stat-value"><?= formatMoney($totalSpent) ?></div>
            <div class="stat-change neutral"><i class="fas fa-receipt"></i> On purchases</div>
          </div>
        </div>

        <div class="stat-card" style="--card-color:#8e44ad">
          <div class="stat-icon" style="background:#f3e8ff; color:#8e44ad">
            <i class="fas fa-list-check"></i>
          </div>
          <div class="stat-body">
            <div class="stat-label-sm">Transactions</div>
            <div class="stat-value"><?= $totalTx ?></div>
            <div class="stat-change neutral"><i class="fas fa-sim-card"></i> <?= $activeNums ?> number(s) active</div>
          </div>
        </div>

      </div><!-- /.stats-row -->

      <!-- ── QUICK ACTIONS ── -->
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title">
            <i class="fas fa-bolt"></i> Quick Actions
          </div>
        </div>
        <div class="section-card-body">
          <div class="quick-grid">

            <a href="virtual-numbers.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#e8f4ff; color:#1a8cff">
                <i class="fas fa-sim-card"></i>
              </div>
              <div class="quick-btn-label">Virtual Number</div>
            </a>

            <a href="boosting.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#e6faf5; color:#0a8c6a">
                <i class="fas fa-rocket"></i>
              </div>
              <div class="quick-btn-label">Boost Social</div>
            </a>

            <a href="social-logins.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#fff3e8; color:#e67e22">
                <i class="fas fa-key"></i>
              </div>
              <div class="quick-btn-label">Social Login</div>
            </a>

            <a href="working-pictures.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#f3e8ff; color:#8e44ad">
                <i class="fas fa-images"></i>
              </div>
              <div class="quick-btn-label">Pictures</div>
            </a>

            <a href="formats.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#ffeef0; color:#e74c3c">
                <i class="fas fa-file-lines"></i>
              </div>
              <div class="quick-btn-label">Formats</div>
            </a>

            <a href="tools.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#e8f9ff; color:#0984e3">
                <i class="fas fa-screwdriver-wrench"></i>
              </div>
              <div class="quick-btn-label">Tools</div>
            </a>

            <a href="topup.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#e6faf5; color:#0a8c6a">
                <i class="fas fa-plus-circle"></i>
              </div>
              <div class="quick-btn-label">Top Up</div>
            </a>

            <a href="transactions.php" class="quick-btn">
              <div class="quick-btn-icon" style="background:#f0f7ff; color:#3a5068">
                <i class="fas fa-receipt"></i>
              </div>
              <div class="quick-btn-label">Transactions</div>
            </a>

          </div>
        </div>
      </div>

      <!-- ── RECENT TRANSACTIONS ── -->
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title">
            <i class="fas fa-clock-rotate-left"></i> Recent Transactions
          </div>
          <a href="transactions.php" style="font-size:0.85rem;color:#1a8cff;font-weight:600">
            View all <i class="fas fa-arrow-right"></i>
          </a>
        </div>
        <div class="section-card-body" style="padding:0 22px">

          <?php if (empty($recentTx)): ?>
            <div style="text-align:center;padding:40px 20px;color:#6b8ba4">
              <i class="fas fa-receipt" style="font-size:2.5rem;opacity:0.3;display:block;margin-bottom:12px"></i>
              No transactions yet. <a href="topup.php" style="color:#1a8cff;font-weight:600">Top up your wallet</a> to get started!
            </div>
          <?php else: ?>
            <?php foreach ($recentTx as $tx):
              // Pick icon + color based on type
              $iconMap = [
                'topup'       => ['fas fa-arrow-down-to-line', '#e6faf5', '#0a8c6a'],
                'admin_topup' => ['fas fa-arrow-down-to-line', '#e6faf5', '#0a8c6a'],
                'purchase'    => ['fas fa-shopping-bag',       '#fff3e8', '#e67e22'],
                'refund'      => ['fas fa-rotate-left',        '#e8f4ff', '#1a8cff'],
              ];
              [$icon, $bg, $clr] = $iconMap[$tx['type']] ?? ['fas fa-circle', '#f0f7ff', '#6b8ba4'];
              $isCredit = in_array($tx['type'], ['topup','admin_topup','refund']);
              $date = date('M j, Y · g:i A', strtotime($tx['created_at']));
              $label = match($tx['type']) {
                'topup'       => 'Wallet Top Up',
                'admin_topup' => 'Admin Top Up',
                'refund'      => 'Refund — ' . ($tx['item_type'] ?? ''),
                'purchase'    => 'Purchase — ' . ucfirst(str_replace('_', ' ', $tx['item_type'] ?? '')),
                default       => ucfirst($tx['type'])
              };
            ?>
            <div class="tx-row">
              <div class="tx-icon" style="background:<?= $bg ?>;color:<?= $clr ?>">
                <i class="<?= $icon ?>"></i>
              </div>
              <div class="tx-info">
                <div class="tx-name"><?= htmlspecialchars($label) ?></div>
                <div class="tx-date"><?= $date ?></div>
              </div>
              <div class="tx-amount <?= $isCredit ? 'credit' : 'debit' ?>">
                <?= $isCredit ? '+' : '-' ?><?= formatMoney((float)$tx['amount']) ?>
              </div>
            </div>
            <?php endforeach; ?>
          <?php endif; ?>

        </div>
      </div>

    </div><!-- /.dash-content -->
  </div><!-- /.dash-main -->
</div><!-- /.dash-shell -->

<!-- WhatsApp FAB -->
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '2348000000000' ?>"
   target="_blank" class="whatsapp-fab" title="Chat with us on WhatsApp">
  <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
    <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
  </svg>
</a>

<script>
  /* ── SIDEBAR TOGGLE ── */
  function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
    document.body.style.overflow = '';
  }

  /* ── NUMBER COUNT-UP ANIMATION ── */
  function animateValue(el, start, end, duration, prefix = '', suffix = '') {
    const range = end - start;
    const startTime = performance.now();
    function update(currentTime) {
      const elapsed = currentTime - startTime;
      const progress = Math.min(elapsed / duration, 1);
      const eased = 1 - Math.pow(1 - progress, 3);
      const value = Math.round(start + range * eased);
      el.textContent = prefix + value.toLocaleString() + suffix;
      if (progress < 1) requestAnimationFrame(update);
    }
    requestAnimationFrame(update);
  }

  window.addEventListener('load', () => {
    // Animate balance
    const balEl = document.getElementById('balanceVal');
    if (balEl) {
      const raw = parseFloat('<?= $currentUser['balance'] ?>');
      animateValue(balEl, 0, Math.round(raw), 900, '₦');
    }
  });
</script>
</body>
</html>