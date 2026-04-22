<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'dashboard';

// ── Stats ─────────────────────────────────────────────────
$stats = $pdo->query("
  SELECT
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM users WHERE DATE(created_at)=CURDATE()) as new_today,
    (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type IN ('topup','admin_topup') AND status='success') as total_topup,
    (SELECT COALESCE(SUM(amount),0) FROM transactions WHERE type='purchase' AND status='success') as total_revenue,
    (SELECT COUNT(*) FROM transactions) as total_tx,
    (SELECT COUNT(*) FROM transactions WHERE DATE(created_at)=CURDATE()) as tx_today,
    (SELECT COUNT(*) FROM social_logins WHERE is_sold=0) as logins_available,
    (SELECT COUNT(*) FROM virtual_numbers WHERE status='waiting') as active_numbers
")->fetch();

// ── Recent users ──────────────────────────────────────────
$recentUsers = $pdo->query(
    "SELECT id,first_name,last_name,email,balance,created_at FROM users ORDER BY created_at DESC LIMIT 6"
)->fetchAll();

// ── Recent transactions ───────────────────────────────────
$recentTx = $pdo->query(
    "SELECT t.*, u.first_name, u.last_name FROM transactions t
     JOIN users u ON u.id=t.user_id ORDER BY t.created_at DESC LIMIT 8"
)->fetchAll();

// ── Revenue last 7 days (for mini chart data) ─────────────
$chartData = $pdo->query(
    "SELECT DATE(created_at) as day, COALESCE(SUM(amount),0) as total
     FROM transactions WHERE type='purchase' AND status='success'
     AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
     GROUP BY DATE(created_at) ORDER BY day ASC"
)->fetchAll();
$chartLabels = [];
$chartValues = [];
for ($i = 6; $i >= 0; $i--) {
    $day = date('Y-m-d', strtotime("-{$i} days"));
    $label = date('M j', strtotime($day));
    $chartLabels[] = $label;
    $found = array_filter($chartData, fn($r) => $r['day'] === $day);
    $chartValues[] = $found ? (float)reset($found)['total'] : 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Dashboard — NBALOGMARKETPLACE</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
  <style>
    .admin-stats { display:grid; grid-template-columns:repeat(4,1fr); gap:18px; margin-bottom:24px; }
    .chart-grid  { display:grid; grid-template-columns:1.6fr 1fr; gap:20px; margin-bottom:24px; }
    .tables-grid { display:grid; grid-template-columns:1fr 1fr; gap:20px; }
    .mini-table th { background:#f5fbff; padding:10px 14px; font-size:0.76rem; font-weight:700; color:#6b8ba4; text-transform:uppercase; letter-spacing:.5px; border-bottom:1.5px solid #e8f4ff; white-space:nowrap; }
    .mini-table td { padding:12px 14px; border-bottom:1px solid #f0f7ff; font-size:0.88rem; }
    .mini-table tr:last-child td { border-bottom:none; }
    .mini-table tr:hover td { background:#fafcff; }
    .user-av { width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#1a8cff,#00c6ae);color:white;font-size:0.75rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    .tx-badge { padding:3px 9px;border-radius:100px;font-size:0.74rem;font-weight:700; }
    .badge-topup    { background:#e6faf5;color:#0a8c6a; }
    .badge-purchase { background:#fff3e8;color:#e67e22; }
    .badge-refund   { background:#e8f4ff;color:#1a8cff; }
    .badge-admin_topup { background:#e6faf5;color:#0a8c6a; }
    @media(max-width:1100px){ .admin-stats{grid-template-columns:repeat(2,1fr);} .chart-grid{grid-template-columns:1fr;} .tables-grid{grid-template-columns:1fr;} }
    @media(max-width:600px){ .admin-stats{grid-template-columns:1fr 1fr;} }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">

    <!-- Topbar -->
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-chart-pie" style="color:#1a8cff"></i> Dashboard</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance" style="background:#e8f4ff;color:#0057b8">
          <i class="fas fa-shield-halved"></i> Admin
        </div>
        <div class="topbar-user">
          <div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div>
          <span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span>
        </div>
      </div>
    </div>

    <div class="dash-content">

      <!-- Stat Cards -->
      <div class="admin-stats">
        <div class="stat-card" style="--card-color:#1a8cff">
          <div class="stat-icon" style="background:#e8f4ff;color:#1a8cff"><i class="fas fa-users"></i></div>
          <div class="stat-body">
            <div class="stat-label-sm">Total Users</div>
            <div class="stat-value"><?= number_format($stats['total_users']) ?></div>
            <div class="stat-change up"><i class="fas fa-arrow-up"></i> +<?= $stats['new_today'] ?> today</div>
          </div>
        </div>
        <div class="stat-card" style="--card-color:#0a8c6a">
          <div class="stat-icon" style="background:#e6faf5;color:#0a8c6a"><i class="fas fa-naira-sign"></i></div>
          <div class="stat-body">
            <div class="stat-label-sm">Total Revenue</div>
            <div class="stat-value" style="font-size:1.2rem"><?= formatMoney((float)$stats['total_revenue']) ?></div>
            <div class="stat-change up"><i class="fas fa-chart-line"></i> All time</div>
          </div>
        </div>
        <div class="stat-card" style="--card-color:#e67e22">
          <div class="stat-icon" style="background:#fff3e8;color:#e67e22"><i class="fas fa-receipt"></i></div>
          <div class="stat-body">
            <div class="stat-label-sm">Transactions</div>
            <div class="stat-value"><?= number_format($stats['total_tx']) ?></div>
            <div class="stat-change neutral"><i class="fas fa-clock"></i> <?= $stats['tx_today'] ?> today</div>
          </div>
        </div>
        <div class="stat-card" style="--card-color:#8e44ad">
          <div class="stat-icon" style="background:#f3e8ff;color:#8e44ad"><i class="fas fa-key"></i></div>
          <div class="stat-body">
            <div class="stat-label-sm">Logins in Stock</div>
            <div class="stat-value"><?= $stats['logins_available'] ?></div>
            <div class="stat-change neutral"><i class="fas fa-sim-card"></i> <?= $stats['active_numbers'] ?> numbers active</div>
          </div>
        </div>
      </div>

      <!-- Chart + Quick Stats -->
      <div class="chart-grid">
        <div class="section-card">
          <div class="section-card-header">
            <div class="section-card-title"><i class="fas fa-chart-bar"></i> Revenue — Last 7 Days</div>
          </div>
          <div class="section-card-body">
            <canvas id="revenueChart" height="100"></canvas>
          </div>
        </div>
        <div class="section-card">
          <div class="section-card-header">
            <div class="section-card-title"><i class="fas fa-bolt"></i> Quick Stats</div>
          </div>
          <div class="section-card-body" style="display:flex;flex-direction:column;gap:14px">
            <?php
            $qs = [
              ['Total Topped Up',    formatMoney((float)$stats['total_topup']),  'fas fa-arrow-down-to-line','#e6faf5','#0a8c6a'],
              ['Total Revenue',      formatMoney((float)$stats['total_revenue']),'fas fa-naira-sign',         '#fff3e8','#e67e22'],
              ['Logins Available',   $stats['logins_available'],                  'fas fa-key',               '#f3e8ff','#8e44ad'],
              ['Active VM Numbers',  $stats['active_numbers'],                    'fas fa-sim-card',          '#e8f4ff','#1a8cff'],
            ];
            foreach ($qs as [$label,$val,$icon,$bg,$clr]): ?>
            <div style="display:flex;align-items:center;gap:12px;padding:12px;background:#f5fbff;border-radius:10px;border:1px solid #e8f4ff">
              <div style="width:38px;height:38px;border-radius:10px;background:<?= $bg ?>;color:<?= $clr ?>;display:flex;align-items:center;justify-content:center;font-size:0.95rem;flex-shrink:0">
                <i class="<?= $icon ?>"></i>
              </div>
              <div style="flex:1">
                <div style="font-size:0.78rem;color:#6b8ba4;font-weight:600"><?= $label ?></div>
                <div style="font-family:'Outfit',sans-serif;font-weight:800;font-size:1rem;color:#0d1b2a"><?= $val ?></div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Tables -->
      <div class="tables-grid">

        <!-- Recent Users -->
        <div class="section-card">
          <div class="section-card-header">
            <div class="section-card-title"><i class="fas fa-users"></i> Recent Users</div>
            <a href="users.php" style="font-size:0.83rem;color:#1a8cff;font-weight:600">View all <i class="fas fa-arrow-right"></i></a>
          </div>
          <div style="overflow-x:auto">
            <table class="mini-table" style="width:100%;border-collapse:collapse">
              <thead><tr><th>User</th><th>Balance</th><th>Joined</th></tr></thead>
              <tbody>
                <?php foreach ($recentUsers as $u):
                  $init = strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1));
                ?>
                <tr>
                  <td>
                    <div style="display:flex;align-items:center;gap:10px">
                      <div class="user-av"><?= $init ?></div>
                      <div>
                        <div style="font-weight:600;font-size:0.88rem"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
                        <div style="font-size:0.76rem;color:#6b8ba4"><?= htmlspecialchars($u['email']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#0a8c6a"><?= formatMoney((float)$u['balance']) ?></td>
                  <td style="color:#6b8ba4;font-size:0.82rem"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Recent Transactions -->
        <div class="section-card">
          <div class="section-card-header">
            <div class="section-card-title"><i class="fas fa-receipt"></i> Recent Transactions</div>
            <a href="transactions.php" style="font-size:0.83rem;color:#1a8cff;font-weight:600">View all <i class="fas fa-arrow-right"></i></a>
          </div>
          <div style="overflow-x:auto">
            <table class="mini-table" style="width:100%;border-collapse:collapse">
              <thead><tr><th>User</th><th>Type</th><th>Amount</th><th>Date</th></tr></thead>
              <tbody>
                <?php foreach ($recentTx as $tx): ?>
                <tr>
                  <td style="font-weight:600;font-size:0.87rem"><?= htmlspecialchars($tx['first_name'].' '.$tx['last_name']) ?></td>
                  <td><span class="tx-badge badge-<?= $tx['type'] ?>"><?= ucfirst(str_replace('_',' ',$tx['type'])) ?></span></td>
                  <td style="font-family:'Outfit',sans-serif;font-weight:700;color:<?= in_array($tx['type'],['topup','admin_topup','refund'])?'#0a8c6a':'#e67e22' ?>"><?= formatMoney((float)$tx['amount']) ?></td>
                  <td style="color:#6b8ba4;font-size:0.8rem"><?= date('M j', strtotime($tx['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div><!-- /.dash-content -->
  </div>
</div>

<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}

  // Revenue chart
  const ctx = document.getElementById('revenueChart').getContext('2d');
  new Chart(ctx, {
    type: 'bar',
    data: {
      labels: <?= json_encode($chartLabels) ?>,
      datasets: [{
        label: 'Revenue (₦)',
        data: <?= json_encode($chartValues) ?>,
        backgroundColor: 'rgba(26,140,255,0.15)',
        borderColor: '#1a8cff',
        borderWidth: 2,
        borderRadius: 8,
        borderSkipped: false,
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { beginAtZero: true, grid: { color: '#f0f7ff' },
             ticks: { callback: v => '₦' + v.toLocaleString(), font: { size: 11 } } },
        x: { grid: { display: false }, ticks: { font: { size: 11 } } }
      }
    }
  });
</script>
</body>
</html>