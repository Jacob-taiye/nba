<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'virtual-numbers';

$search  = clean($_GET['search']  ?? '');
$status  = clean($_GET['status']  ?? 'all');
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]      = '(u.email LIKE :s OR u.username LIKE :s OR n.phone_number LIKE :s OR n.service LIKE :s)';
    $params[':s'] = "%$search%";
}
if ($status !== 'all') {
    $where[]          = 'n.status = :status';
    $params[':status']= $status;
}
$whereStr = 'WHERE ' . implode(' AND ', $where);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM virtual_numbers n JOIN users u ON u.id=n.user_id $whereStr");
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

$params[':lim'] = $perPage;
$params[':off'] = $offset;
$stmt = $pdo->prepare(
    "SELECT n.*, u.first_name, u.last_name, u.email, u.username
     FROM virtual_numbers n
     JOIN users u ON u.id = n.user_id
     $whereStr
     ORDER BY n.created_at DESC
     LIMIT :lim OFFSET :off"
);
$stmt->execute($params);
$numbers = $stmt->fetchAll();

// Stats
$stats = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(price) AS total_revenue,
        SUM(CASE WHEN status='waiting'   THEN 1 ELSE 0 END) AS active,
        SUM(CASE WHEN status='received'  THEN 1 ELSE 0 END) AS received,
        SUM(CASE WHEN status='expired'   THEN 1 ELSE 0 END) AS expired,
        SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) AS cancelled
     FROM virtual_numbers"
)->fetch();

$statusColors = [
    'waiting'   => ['#e8f4ff','#1a8cff'],
    'received'  => ['#e6faf5','#0a8c6a'],
    'expired'   => ['#fff3e8','#e67e22'],
    'cancelled' => ['#fff0f0','#c0392b'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Virtual Numbers — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .stats-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(155px,1fr)); gap:14px; margin-bottom:22px; }
    .stat-card  { background:white; border:1.5px solid #e8f4ff; border-radius:14px; padding:16px 18px; }
    .stat-val   { font-family:'Outfit',sans-serif; font-size:1.35rem; font-weight:800; color:#0d1b2a; }
    .stat-lbl   { font-size:.76rem; color:#6b8ba4; font-weight:600; margin-top:3px; }
    .filter-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
    .filter-bar input { flex:1; min-width:200px; padding:10px 16px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.92rem; outline:none; font-family:inherit; }
    .filter-bar input:focus { border-color:#1a8cff; }
    .filter-bar select { padding:10px 14px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.88rem; font-family:inherit; outline:none; background:white; color:#3a5068; font-weight:600; }
    .filter-bar button { padding:10px 20px; background:#1a8cff; color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-family:'Outfit',sans-serif; }
    .type-pill  { padding:3px 10px; border-radius:100px; font-size:.73rem; font-weight:700; display:inline-block; white-space:nowrap; }
    .sms-code   { font-family:monospace; font-size:.95rem; font-weight:800; color:#0a8c6a; background:#e6faf5; padding:3px 10px; border-radius:7px; }
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
        <div class="page-title"><i class="fas fa-sim-card" style="color:#1a8cff"></i> Virtual Numbers</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance"><i class="fas fa-sim-card"></i> <?= number_format($totalCount) ?> total</div>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">

      <!-- Stats -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-val"><?= formatMoney((float)$stats['total_revenue']) ?></div>
          <div class="stat-lbl"><i class="fas fa-naira-sign" style="color:#0a8c6a"></i> Revenue</div>
        </div>
        <div class="stat-card">
          <div class="stat-val"><?= number_format((int)$stats['total']) ?></div>
          <div class="stat-lbl"><i class="fas fa-list" style="color:#6b8ba4"></i> Total Rented</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" style="color:#1a8cff"><?= number_format((int)$stats['active']) ?></div>
          <div class="stat-lbl"><i class="fas fa-signal" style="color:#1a8cff"></i> Active</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" style="color:#0a8c6a"><?= number_format((int)$stats['received']) ?></div>
          <div class="stat-lbl"><i class="fas fa-circle-check" style="color:#0a8c6a"></i> SMS Received</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" style="color:#e67e22"><?= number_format((int)$stats['expired']) ?></div>
          <div class="stat-lbl"><i class="fas fa-clock" style="color:#e67e22"></i> Expired</div>
        </div>
      </div>

      <!-- Filters -->
      <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Search user, number, service..." value="<?= htmlspecialchars($search) ?>"/>
        <select name="status">
          <option value="all"      <?= $status==='all'      ?'selected':'' ?>>All Statuses</option>
          <option value="waiting"  <?= $status==='waiting'  ?'selected':'' ?>>Active</option>
          <option value="received" <?= $status==='received' ?'selected':'' ?>>SMS Received</option>
          <option value="expired"  <?= $status==='expired'  ?'selected':'' ?>>Expired</option>
          <option value="cancelled"<?= $status==='cancelled'?'selected':'' ?>>Cancelled</option>
        </select>
        <button type="submit"><i class="fas fa-filter"></i> Filter</button>
        <?php if ($search || $status !== 'all'): ?>
          <a href="virtual_numbers.php" style="padding:10px 16px;background:#f0f7ff;border:1.5px solid #d6ecff;border-radius:10px;color:#3a5068;font-weight:600;font-size:.88rem;display:flex;align-items:center;gap:6px;text-decoration:none">Clear</a>
        <?php endif; ?>
      </form>

      <!-- Table -->
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-table"></i> All Virtual Numbers
            <span style="background:#e8f4ff;color:#1a8cff;padding:3px 10px;border-radius:100px;font-size:.76rem;font-weight:700"><?= number_format($totalCount) ?></span>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table>
            <thead>
              <tr><th>User</th><th>Phone Number</th><th>Service</th><th>Country</th><th>SMS Code</th><th>Amount</th><th>Status</th><th>Expires</th><th>Date</th></tr>
            </thead>
            <tbody>
              <?php if (empty($numbers)): ?>
                <tr><td colspan="9" style="text-align:center;padding:40px;color:#6b8ba4">No records found.</td></tr>
              <?php else: foreach ($numbers as $n):
                $sc = $statusColors[$n['status']] ?? ['#f0f7ff','#3a5068'];
                $expired = strtotime($n['expires_at']) < time();
              ?>
              <tr>
                <td>
                  <div style="font-weight:700;color:#0d1b2a;font-size:.85rem"><?= htmlspecialchars($n['first_name'].' '.$n['last_name']) ?></div>
                  <div style="font-size:.74rem;color:#6b8ba4"><?= htmlspecialchars($n['username']) ?></div>
                </td>
                <td style="font-family:monospace;font-weight:700;color:#0d1b2a;font-size:.9rem">
                  <?= htmlspecialchars($n['phone_number']) ?>
                </td>
                <td style="font-weight:600;color:#3a5068"><?= htmlspecialchars($n['service']) ?></td>
                <td style="color:#6b8ba4"><?= htmlspecialchars($n['country']) ?></td>
                <td>
                  <?php if ($n['sms_code']): ?>
                    <span class="sms-code"><?= htmlspecialchars($n['sms_code']) ?></span>
                  <?php else: ?>
                    <span style="color:#b3c9d6;font-size:.8rem">—</span>
                  <?php endif; ?>
                </td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff"><?= formatMoney((float)$n['price']) ?></td>
                <td>
                  <span class="type-pill" style="background:<?= $sc[0] ?>;color:<?= $sc[1] ?>">
                    <?= ucfirst($n['status']) ?>
                  </span>
                </td>
                <td style="font-size:.78rem;color:<?= $expired && $n['status']==='waiting' ? '#c0392b' : '#6b8ba4' ?>">
                  <?= date('M j, g:i A', strtotime($n['expires_at'])) ?>
                </td>
                <td style="color:#6b8ba4;font-size:.78rem;white-space:nowrap"><?= date('M j, Y', strtotime($n['created_at'])) ?></td>
              </tr>
              <?php endforeach; endif; ?>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1):
          $q = http_build_query(array_filter(['search'=>$search,'status'=>$status!=='all'?$status:'']));
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
<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
</script>
</body>
</html>