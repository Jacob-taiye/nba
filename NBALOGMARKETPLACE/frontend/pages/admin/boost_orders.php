<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
require_once __DIR__ . '/../../../backend/includes/smm_api.php';
$activePage = 'boost-orders';

$search  = clean($_GET['search']  ?? '');
$status  = clean($_GET['status']  ?? 'all');
$perPage = 20;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

$where  = ['1=1'];
$params = [];

if ($search) {
    $where[]      = '(u.email LIKE :s OR u.username LIKE :s OR b.service_name LIKE :s OR b.link LIKE :s)';
    $params[':s'] = "%$search%";
}
if ($status !== 'all') {
    $where[]         = 'b.status = :status';
    $params[':status'] = $status;
}
$whereStr = 'WHERE ' . implode(' AND ', $where);

$totalStmt = $pdo->prepare("SELECT COUNT(*) FROM boost_orders b JOIN users u ON u.id=b.user_id $whereStr");
$totalStmt->execute($params);
$totalCount = (int)$totalStmt->fetchColumn();
$totalPages = max(1, ceil($totalCount / $perPage));

$params[':lim'] = $perPage;
$params[':off'] = $offset;
$stmt = $pdo->prepare(
    "SELECT b.*, u.first_name, u.last_name, u.email, u.username
     FROM boost_orders b
     JOIN users u ON u.id = b.user_id
     $whereStr
     ORDER BY b.created_at DESC
     LIMIT :lim OFFSET :off"
);
$stmt->execute($params);
$orders = $stmt->fetchAll();

// Summary stats
$stats = $pdo->query(
    "SELECT
        COUNT(*) AS total,
        SUM(amount_paid) AS total_revenue,
        SUM(CASE WHEN status='pending'     THEN 1 ELSE 0 END) AS pending,
        SUM(CASE WHEN status='in_progress' THEN 1 ELSE 0 END) AS in_progress,
        SUM(CASE WHEN status='completed'   THEN 1 ELSE 0 END) AS completed
     FROM boost_orders"
)->fetch();

$statusColors = [
    'pending'     => ['#fff3e8','#e67e22'],
    'in_progress' => ['#e8f4ff','#1a8cff'],
    'completed'   => ['#e6faf5','#0a8c6a'],
    'partial'     => ['#f3e8ff','#8e44ad'],
    'cancelled'   => ['#fff0f0','#c0392b'],
    'failed'      => ['#fff0f0','#c0392b'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Boost Orders — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .stats-row { display:grid; grid-template-columns:repeat(auto-fill,minmax(160px,1fr)); gap:14px; margin-bottom:22px; }
    .stat-card  { background:white; border:1.5px solid #e8f4ff; border-radius:14px; padding:16px 18px; }
    .stat-val   { font-family:'Outfit',sans-serif; font-size:1.4rem; font-weight:800; color:#0d1b2a; }
    .stat-lbl   { font-size:.76rem; color:#6b8ba4; font-weight:600; margin-top:3px; }
    .filter-bar { display:flex; gap:10px; margin-bottom:20px; flex-wrap:wrap; }
    .filter-bar input { flex:1; min-width:200px; padding:10px 16px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.92rem; outline:none; font-family:inherit; }
    .filter-bar input:focus { border-color:#1a8cff; }
    .filter-bar select { padding:10px 14px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.88rem; font-family:inherit; outline:none; background:white; color:#3a5068; font-weight:600; }
    .filter-bar button { padding:10px 20px; background:#1a8cff; color:white; border:none; border-radius:10px; font-weight:700; cursor:pointer; font-family:'Outfit',sans-serif; white-space:nowrap; }
    .type-pill  { padding:3px 10px; border-radius:100px; font-size:.73rem; font-weight:700; display:inline-block; white-space:nowrap; }
    .action-btn { padding:6px 12px; border:none; border-radius:7px; font-size:.78rem; font-weight:700; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:5px; }
    .btn-refresh { background:#e8f4ff; color:#1a8cff; border:1.5px solid #b3d9ff; }
    .btn-refresh:hover { background:#d6ecff; }
    .progress-bar { height:5px; background:#e8f4ff; border-radius:100px; overflow:hidden; margin-top:5px; width:100px; }
    .progress-fill { height:100%; border-radius:100px; background:linear-gradient(90deg,#1a8cff,#00c6ae); }
    .pagination { display:flex; gap:6px; padding:16px 22px; justify-content:center; border-top:1.5px solid #e8f4ff; }
    .page-btn   { width:36px; height:36px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:.88rem; font-weight:600; border:1.5px solid #d6ecff; background:white; color:#3a5068; text-decoration:none; transition:all .2s; }
    .page-btn:hover,.page-btn.active { background:#1a8cff; border-color:#1a8cff; color:white; }
    .page-btn.disabled { opacity:.4; pointer-events:none; }
    table { width:100%; border-collapse:collapse; }
    th { background:#f5fbff; padding:11px 14px; text-align:left; font-size:.76rem; font-weight:700; color:#6b8ba4; text-transform:uppercase; letter-spacing:.5px; border-bottom:1.5px solid #e8f4ff; white-space:nowrap; }
    td { padding:11px 14px; border-bottom:1px solid #f0f7ff; font-size:.87rem; vertical-align:middle; }
    tr:last-child td { border-bottom:none; }
    tr:hover td { background:#fafcff; }
    #alertToast { position:fixed; bottom:24px; right:24px; z-index:9999; display:none; max-width:320px; }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-rocket" style="color:#e67e22"></i> Boost Orders</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance"><i class="fas fa-rocket"></i> <?= number_format($totalCount) ?> orders</div>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">

      <!-- Stats -->
      <div class="stats-row">
        <div class="stat-card">
          <div class="stat-val"><?= formatMoney((float)$stats['total_revenue']) ?></div>
          <div class="stat-lbl"><i class="fas fa-naira-sign" style="color:#0a8c6a"></i> Total Revenue</div>
        </div>
        <div class="stat-card">
          <div class="stat-val"><?= number_format((int)$stats['total']) ?></div>
          <div class="stat-lbl"><i class="fas fa-list" style="color:#6b8ba4"></i> Total Orders</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" style="color:#e67e22"><?= number_format((int)$stats['pending']) ?></div>
          <div class="stat-lbl"><i class="fas fa-clock" style="color:#e67e22"></i> Pending</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" style="color:#1a8cff"><?= number_format((int)$stats['in_progress']) ?></div>
          <div class="stat-lbl"><i class="fas fa-spinner" style="color:#1a8cff"></i> In Progress</div>
        </div>
        <div class="stat-card">
          <div class="stat-val" style="color:#0a8c6a"><?= number_format((int)$stats['completed']) ?></div>
          <div class="stat-lbl"><i class="fas fa-circle-check" style="color:#0a8c6a"></i> Completed</div>
        </div>
      </div>

      <!-- Filters -->
      <form method="GET" class="filter-bar">
        <input type="text" name="search" placeholder="Search user, service, link..." value="<?= htmlspecialchars($search) ?>"/>
        <select name="status">
          <option value="all"        <?= $status==='all'        ?'selected':'' ?>>All Statuses</option>
          <option value="pending"    <?= $status==='pending'    ?'selected':'' ?>>Pending</option>
          <option value="in_progress"<?= $status==='in_progress'?'selected':'' ?>>In Progress</option>
          <option value="completed"  <?= $status==='completed'  ?'selected':'' ?>>Completed</option>
          <option value="partial"    <?= $status==='partial'    ?'selected':'' ?>>Partial</option>
          <option value="cancelled"  <?= $status==='cancelled'  ?'selected':'' ?>>Cancelled</option>
          <option value="failed"     <?= $status==='failed'     ?'selected':'' ?>>Failed</option>
        </select>
        <button type="submit"><i class="fas fa-filter"></i> Filter</button>
        <?php if ($search || $status !== 'all'): ?>
          <a href="boost-orders.php" style="padding:10px 16px;background:#f0f7ff;border:1.5px solid #d6ecff;border-radius:10px;color:#3a5068;font-weight:600;font-size:.88rem;display:flex;align-items:center;gap:6px;text-decoration:none">Clear</a>
        <?php endif; ?>
        <button type="button" onclick="refreshAll()" style="background:#e6faf5;color:#0a8c6a;border:1.5px solid #b2dfdb">
          <i class="fas fa-rotate-right"></i> Refresh Active
        </button>
      </form>

      <!-- Table -->
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-table"></i> All Boost Orders
            <span style="background:#e8f4ff;color:#1a8cff;padding:3px 10px;border-radius:100px;font-size:.76rem;font-weight:700"><?= number_format($totalCount) ?></span>
          </div>
        </div>
        <div style="overflow-x:auto">
          <table>
            <thead>
              <tr><th>User</th><th>Service</th><th>Link</th><th>Qty</th><th>Progress</th><th>Amount</th><th>Status</th><th>Date</th><th></th></tr>
            </thead>
            <tbody id="ordersBody">
              <?php if (empty($orders)): ?>
                <tr><td colspan="9" style="text-align:center;padding:40px;color:#6b8ba4">No orders found.</td></tr>
              <?php else: foreach ($orders as $o):
                $sc = $statusColors[$o['status']] ?? ['#f0f7ff','#3a5068'];
                $delivered = $o['start_count'] ? ($o['quantity'] - ($o['remains'] ?? $o['quantity'])) : 0;
                $pct = $o['quantity'] > 0 ? min(100, round(($delivered / $o['quantity']) * 100)) : 0;
                $isActive = in_array($o['status'], ['pending','in_progress']);
              ?>
              <tr id="orow<?= $o['id'] ?>">
                <td>
                  <div style="font-weight:700;color:#0d1b2a;font-size:.85rem"><?= htmlspecialchars($o['first_name'].' '.$o['last_name']) ?></div>
                  <div style="font-size:.74rem;color:#6b8ba4"><?= htmlspecialchars($o['username']) ?></div>
                </td>
                <td>
                  <div style="font-weight:600;color:#0d1b2a;font-size:.85rem;max-width:150px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap"><?= htmlspecialchars($o['service_name']) ?></div>
                  <div style="font-size:.74rem;color:#6b8ba4"><?= htmlspecialchars($o['category']) ?></div>
                </td>
                <td style="max-width:160px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                  <a href="<?= htmlspecialchars($o['link']) ?>" target="_blank" style="color:#1a8cff;font-size:.8rem;text-decoration:none" title="<?= htmlspecialchars($o['link']) ?>">
                    <i class="fas fa-arrow-up-right-from-square"></i> <?= htmlspecialchars(parse_url($o['link'], PHP_URL_HOST) ?: $o['link']) ?>
                  </a>
                </td>
                <td style="font-weight:700;color:#0d1b2a"><?= number_format($o['quantity']) ?></td>
                <td>
                  <div style="font-size:.76rem;color:#6b8ba4" id="prog<?= $o['id'] ?>"><?= $o['remains'] !== null ? number_format($o['remains']).' left' : '—' ?></div>
                  <div class="progress-bar"><div class="progress-fill" id="fill<?= $o['id'] ?>" style="width:<?= $pct ?>%"></div></div>
                </td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff"><?= formatMoney((float)$o['amount_paid']) ?></td>
                <td>
                  <span class="type-pill" id="pill<?= $o['id'] ?>" style="background:<?= $sc[0] ?>;color:<?= $sc[1] ?>">
                    <?= ucfirst(str_replace('_',' ',$o['status'])) ?>
                  </span>
                </td>
                <td style="color:#6b8ba4;font-size:.78rem;white-space:nowrap"><?= date('M j, Y', strtotime($o['created_at'])) ?></td>
                <td>
                  <?php if ($isActive): ?>
                  <button class="action-btn btn-refresh" onclick="refreshOrder(<?= $o['id'] ?>, this)" title="Check latest status from SMM panel">
                    <i class="fas fa-rotate-right"></i>
                  </button>
                  <?php endif; ?>
                </td>
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

<div id="alertToast"></div>
<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function showToast(msg,type='success'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='error'?'circle-exclamation':'circle-check'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',3500);}

  const statusColors = {
    pending:     ['#fff3e8','#e67e22'],
    in_progress: ['#e8f4ff','#1a8cff'],
    completed:   ['#e6faf5','#0a8c6a'],
    partial:     ['#f3e8ff','#8e44ad'],
    cancelled:   ['#fff0f0','#c0392b'],
    failed:      ['#fff0f0','#c0392b'],
  };
  const statusLabels = { pending:'Pending', in_progress:'In Progress', completed:'Completed', partial:'Partial', cancelled:'Cancelled', failed:'Failed' };

  async function refreshOrder(id, btn) {
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner" style="width:14px;height:14px"></span>'; }

    try {
      const res  = await fetch(`/NBALOGMARKETPLACE/backend/user/check_boost.php?boost_id=${id}`);
      const data = await res.json();
      if (data.success) updateRow(id, data);
    } catch(e) { /* silent */ }

    if (btn) { btn.disabled = false; btn.innerHTML = origHtml; }
  }

  function updateRow(id, data) {
    const pill = document.getElementById('pill' + id);
    const prog = document.getElementById('prog' + id);
    const fill = document.getElementById('fill' + id);
    if (!pill) return;

    const colors = statusColors[data.status] || ['#f0f7ff','#3a5068'];
    pill.style.background = colors[0];
    pill.style.color       = colors[1];
    pill.textContent       = statusLabels[data.status] || data.status;

    if (data.remains !== undefined && data.start_count !== undefined) {
      const qty       = data.start_count + data.remains;
      const delivered = qty - data.remains;
      const pct       = qty > 0 ? Math.min(100, Math.round(delivered/qty*100)) : 0;
      if (prog) prog.textContent = number_format(data.remains) + ' left';
      if (fill) fill.style.width = pct + '%';
    }

    // Hide refresh btn if now terminal
    if (['completed','cancelled','failed','partial'].includes(data.status)) {
      const row = document.getElementById('orow' + id);
      const btn = row?.querySelector('.btn-refresh');
      if (btn) btn.style.display = 'none';
    }
  }

  function number_format(n) { return parseInt(n||0).toLocaleString(); }

  async function refreshAll() {
    const btns = document.querySelectorAll('.btn-refresh');
    if (!btns.length) { showToast('No active orders to refresh.'); return; }
    showToast(`Refreshing ${btns.length} active order(s)...`);
    for (const btn of btns) {
      const row = btn.closest('tr');
      const id  = row?.id?.replace('orow','');
      if (id) await refreshOrder(id, btn);
    }
    showToast('All active orders refreshed.');
  }
</script>
</body>
</html>