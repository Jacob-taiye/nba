<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'transactions';

$search  = clean($_GET['search']  ?? '');
$type    = clean($_GET['type']    ?? 'all');
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]        = '(u.email LIKE :s OR u.username LIKE :s OR u.first_name LIKE :s OR t.note LIKE :s)';
    $params[':s']   = "%$search%";
}
if ($type !== 'all') {
    $where[]        = 't.type = :type';
    $params[':type']= $type;
}
$whereStr = 'WHERE ' . implode(' AND ', $where);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions t JOIN users u ON u.id=t.user_id $whereStr");
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

$params[':lim'] = $perPage;
$params[':off'] = $offset;
$stmt = $pdo->prepare(
    "SELECT t.*, u.first_name, u.last_name, u.email, u.username
     FROM transactions t
     JOIN users u ON u.id = t.user_id
     $whereStr
     ORDER BY t.created_at DESC
     LIMIT :lim OFFSET :off"
);
$stmt->execute($params);
$txs = $stmt->fetchAll();

// Summary stats
$stats = $pdo->query(
    "SELECT
        SUM(CASE WHEN type='topup'    THEN amount ELSE 0 END) AS total_topup,
        SUM(CASE WHEN type='purchase' THEN amount ELSE 0 END) AS total_purchases,
        SUM(CASE WHEN type='refund'   THEN amount ELSE 0 END) AS total_refunds,
        COUNT(*) AS total_count
     FROM transactions WHERE status='success'"
)->fetch();

$typeColors = [
    'topup'    => ['#e6faf5','#0a8c6a'],
    'purchase' => ['#e8f4ff','#1a8cff'],
    'refund'   => ['#fff3e8','#e67e22'],
    'admin_topup'=> ['#f3e8ff','#8e44ad'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transactions — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .stats-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(180px,1fr)); gap:14px; margin-bottom:22px; }
    .stat-card  { background:white; border:1.5px solid #e8f4ff; border-radius:14px; padding:18px 20px; }
    .stat-val   { font-family:'Outfit',sans-serif; font-size:1.4rem; font-weight:800; color:#0d1b2a; }
    .stat-lbl   { font-size:.78rem; color:#6b8ba4; font-weight:600; margin-top:3px; }
    .filter-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
    .filter-bar input { flex:1; min-width:200px; padding:10px 16px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.92rem; outline:none; font-family:inherit; }
    .filter-bar input:focus { border-color:#1a8cff; }
    .filter-bar select { padding:10px 14px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.88rem; font-family:inherit; outline:none; background:white; color:#3a5068; font-weight:600; }
    .filter-bar button { padding:10px 20px; background:#1a8cff; color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-family:'Outfit',sans-serif; }
    .type-pill  { padding:3px 10px; border-radius:100px; font-size:.73rem; font-weight:700; display:inline-block; }
    .status-ok  { background:#e6faf5; color:#0a8c6a; }
    .status-fail{ background:#fff0f0; color:#c0392b; }
    .pagination { display:flex; gap:6px; padding:16px 22px; justify-content:center; border-top:1.5px solid #e8f4ff; }
    .page-btn   { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.88rem; font-weight:600; border:1.5px solid #d6ecff; background:white; color:#3a5068; text-decoration:none; transition:all .2s; }
    .page-btn:hover,.page-btn.active { background:#1a8cff; border-color:#1a8cff; color:white; }
    .page-btn.disabled { opacity:.4; pointer-events:none; }
    table { width:100%; border-collapse:collapse; }
    th { background:#f5fbff; padding:11px 14px; text-align:left; font-size:.76rem; font-weight:700; color:#6b8ba4; text-transform:uppercase; letter-spacing:.5px; border-bottom:1.5px solid #e8f4ff; white-space:nowrap; }
    td { padding:11px 14px; border-bottom:1px solid #f0f7ff; font-size:.87rem; vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fafcff; }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-receipt" style="color:#8e44ad"></i> Transactions</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance"><i class="fas fa-receipt"></i> <?= number_format($totalCount) ?> records</div>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">

      <!-- Stats -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-val"><?= formatMoney((float)$stats['total_topup']) ?></div>
          <div class="stat-lbl"><i class="fas fa-arrow-down" style="color:#0a8c6a"></i> Total Top-ups</div>
        </div>
        <div class="stat-card">
          <div class="stat-val"><?= formatMoney((float)$stats['total_purchases']) ?></div>
          <div class="stat-lbl"><i class="fas fa-bag-shopping" style="color:#1a8cff"></i> Total Purchases</div>
        </div>
        <div class="stat-card">
          <div class="stat-val"><?= formatMoney((float)$stats['total_refunds']) ?></div>
          <div class="stat-lbl"><i class="fas fa-rotate-left" style="color:#e67e22"></i> Total Refunds</div>
        </div>
        <div class="stat-card">
          <div class="stat-val"><?= number_format((int)$stats['total_count']) ?></div>
          <div class="stat-lbl"><i class="fas fa-list" style="color:#6b8ba4"></i> All Transactions</div>
        </div>
      </div>

      <!-- Filters -->
      <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Search user, email, note..." value="<?= htmlspecialchars($search) ?>"/>
        <select name="type">
          <option value="all"       <?= $type==='all'        ? 'selected':'' ?>>All Types</option>
          <option value="topup"     <?= $type==='topup'      ? 'selected':'' ?>>Top-ups</option>
          <option value="purchase"  <?= $type==='purchase'   ? 'selected':'' ?>>Purchases</option>
          <option value="refund"    <?= $type==='refund'     ? 'selected':'' ?>>Refunds</option>
          <option value="admin_topup" <?= $type==='admin_topup'?'selected':'' ?>>Admin Top-ups</option>
        </select>
        <button type="submit"><i class="fas fa-filter"></i> Filter</button>
        <?php if ($search || $type !== 'all'): ?>
          <a href="transactions.php" style="padding:10px 16px;background:#f0f7ff;border:1.5px solid #d6ecff;border-radius:10px;color:#3a5068;font-weight:600;font-size:.88rem;display:flex;align-items:center;gap:6px;text-decoration:none">Clear</a>
        <?php endif; ?>
      </form>

      <!-- Table -->
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-table"></i> Transactions
            <span style="background:#e8f4ff;color:#1a8cff;padding:3px 10px;border-radius:100px;font-size:.76rem;font-weight:700"><?= number_format($totalCount) ?></span>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table>
            <thead>
              <tr><th>User</th><th>Type</th><th>Item</th><th>Amount</th><th>Balance After</th><th>Status</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php if (empty($txs)): ?>
                <tr><td colspan="7" style="text-align:center;padding:40px;color:#6b8ba4">No transactions found.</td></tr>
              <?php else: foreach ($txs as $t):
                $typeColor = $typeColors[$t['type']] ?? ['#f0f7ff','#3a5068'];
              ?>
              <tr>
                <td>
                  <div style="font-weight:700;color:#0d1b2a"><?= htmlspecialchars($t['first_name'].' '.$t['last_name']) ?></div>
                  <div style="font-size:.76rem;color:#6b8ba4"><?= htmlspecialchars($t['email']) ?></div>
                </td>
                <td>
                  <span class="type-pill" style="background:<?= $typeColor[0] ?>;color:<?= $typeColor[1] ?>">
                    <?= ucfirst(str_replace('_',' ',$t['type'])) ?>
                  </span>
                </td>
                <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#3a5068">
                  <?= htmlspecialchars($t['note'] ?? ($t['item_type'] ?? '—')) ?>
                </td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:<?= in_array($t['type'],['topup','refund','admin_topup']) ? '#0a8c6a' : '#c0392b' ?>">
                  <?= in_array($t['type'],['topup','refund','admin_topup']) ? '+' : '-' ?><?= formatMoney((float)$t['amount']) ?>
                </td>
                <td style="font-family:'Outfit',sans-serif;font-weight:600;color:#3a5068"><?= formatMoney((float)$t['balance_after']) ?></td>
                <td>
                  <span class="type-pill <?= $t['status']==='success' ? 'status-ok' : 'status-fail' ?>">
                    <?= ucfirst($t['status']) ?>
                  </span>
                </td>
                <td style="color:#6b8ba4;font-size:.8rem;white-space:nowrap"><?= date('M j, Y g:i A', strtotime($t['created_at'])) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1):
          $q = http_build_query(array_filter(['search'=>$search,'type'=>$type]));
        ?>
        <div class="pagination">
          <a href="?<?= $q ?>&page=<?= $page-1 ?>" class="page-btn <?= $page<=1?'disabled':'' ?>"><i class="fas fa-chevron-left"></i></a>
          <?php for ($i=max(1,$page-2); $i<=min($totalPages,$page+2); $i++): ?>
            <a href="?<?= $q ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a href="?<?= $q ?>&page=<?= $page+1 ?>" class="page-btn <?= $page>=$totalPages?'disabled':'' ?>"><i class="fas fa-chevron-right"></i></a>
        </div>
        <?php endif; ?>
      </div>

    </div>
  </div>
</div>
<div id="alertToast" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
</script>
</body>
</html>