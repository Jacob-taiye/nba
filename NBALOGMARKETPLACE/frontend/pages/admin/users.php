<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'users';

$search  = clean($_GET['search'] ?? '');
$perPage = 15;
$page    = max(1,(int)($_GET['page'] ?? 1));
$offset  = ($page-1)*$perPage;

$where  = $search ? "WHERE email LIKE :s OR first_name LIKE :s OR last_name LIKE :s OR username LIKE :s" : '';
$params = $search ? [':s' => "%$search%"] : [];

$total = $pdo->prepare("SELECT COUNT(*) FROM users $where");
$total->execute($params);
$totalCount = (int)$total->fetchColumn();
$totalPages = max(1, ceil($totalCount/$perPage));

$stmt = $pdo->prepare("SELECT * FROM users $where ORDER BY created_at DESC LIMIT :lim OFFSET :off");
$params[':lim'] = $perPage; $params[':off'] = $offset;
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Users — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .search-bar { display:flex;gap:10px;margin-bottom:20px; }
    .search-bar input { flex:1;padding:10px 16px;border:1.5px solid #d6ecff;border-radius:10px;font-size:0.92rem;outline:none;font-family:inherit; }
    .search-bar input:focus { border-color:#1a8cff; }
    .search-bar button { padding:10px 20px;background:#1a8cff;color:white;border:none;border-radius:10px;font-weight:700;cursor:pointer;font-family:'Outfit',sans-serif; }
    .action-btn { padding:6px 12px;border:none;border-radius:7px;font-size:0.8rem;font-weight:700;cursor:pointer;transition:all 0.2s;display:inline-flex;align-items:center;gap:5px; }
    .btn-topup  { background:#e6faf5;color:#0a8c6a;border:1.5px solid #b2dfdb; }
    .btn-topup:hover { background:#c8f0e5; }
    .btn-delete { background:#fff0f0;color:#c0392b;border:1.5px solid #f5c6c6; }
    .btn-delete:hover { background:#ffe0e0; }
    .user-av { width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#1a8cff,#00c6ae);color:white;font-size:0.78rem;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(13,27,42,.6);backdrop-filter:blur(6px);z-index:9000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all .28s}
    .modal-overlay.active{opacity:1;visibility:visible}
    .modal-box{background:white;border-radius:22px;padding:36px;width:90%;max-width:420px;transform:scale(.93) translateY(20px);transition:all .28s}
    .modal-overlay.active .modal-box{transform:scale(1) translateY(0)}
    .modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:20px}
    .modal-title{font-family:'Outfit',sans-serif;font-size:1.15rem;font-weight:800;color:#0d1b2a}
    .modal-close{width:34px;height:34px;border-radius:50%;border:none;cursor:pointer;background:#f0f7ff;color:#6b8ba4;font-size:1rem;display:flex;align-items:center;justify-content:center}
    .modal-close:hover{background:#e8f4ff;color:#1a8cff}
    .confirm-btn{width:100%;padding:13px;border:none;border-radius:10px;font-family:'Outfit',sans-serif;font-weight:700;font-size:0.95rem;cursor:pointer;transition:all .2s;display:flex;align-items:center;justify-content:center;gap:8px;margin-top:16px}
    .pagination{display:flex;gap:6px;padding:16px 22px;justify-content:center;border-top:1.5px solid #e8f4ff}
    .page-btn{width:36px;height:36px;border-radius:8px;display:flex;align-items:center;justify-content:center;font-size:.88rem;font-weight:600;border:1.5px solid #d6ecff;background:white;color:#3a5068;text-decoration:none;transition:all .2s}
    .page-btn:hover,.page-btn.active{background:#1a8cff;border-color:#1a8cff;color:white}
    .page-btn.disabled{opacity:.4;pointer-events:none}
    #alertToast{position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:320px}
    table{width:100%;border-collapse:collapse}
    th{background:#f5fbff;padding:11px 14px;text-align:left;font-size:.76rem;font-weight:700;color:#6b8ba4;text-transform:uppercase;letter-spacing:.5px;border-bottom:1.5px solid #e8f4ff;white-space:nowrap}
    td{padding:12px 14px;border-bottom:1px solid #f0f7ff;font-size:.88rem;vertical-align:middle}
    tr:last-child td{border-bottom:none}
    tr:hover td{background:#fafcff}
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-users" style="color:#1a8cff"></i> Users</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance"><i class="fas fa-users"></i> <?= number_format($totalCount) ?> total</div>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">
      <!-- Search -->
      <form method="GET" class="search-bar">
        <input type="text" name="search" placeholder="Search by name, email or username..." value="<?= htmlspecialchars($search) ?>"/>
        <button type="submit"><i class="fas fa-search"></i> Search</button>
        <?php if ($search): ?><a href="users.php" style="padding:10px 16px;background:#f0f7ff;border:1.5px solid #d6ecff;border-radius:10px;color:#3a5068;font-weight:600;font-size:0.88rem;display:flex;align-items:center;gap:6px">Clear</a><?php endif; ?>
      </form>

      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-table"></i> All Users <span style="background:#e8f4ff;color:#1a8cff;padding:3px 10px;border-radius:100px;font-size:.76rem;font-weight:700"><?= $totalCount ?></span></div>
        </div>
        <div style="overflow-x:auto">
          <table>
            <thead><tr><th>User</th><th>Username</th><th>Balance</th><th>Joined</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($users as $u):
                $init = strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1));
              ?>
              <tr id="urow<?= $u['id'] ?>">
                <td>
                  <div style="display:flex;align-items:center;gap:10px">
                    <div class="user-av"><?= $init ?></div>
                    <div>
                      <div style="font-weight:600"><?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?></div>
                      <div style="font-size:.76rem;color:#6b8ba4"><?= htmlspecialchars($u['email']) ?></div>
                    </div>
                  </div>
                </td>
                <td style="color:#6b8ba4">@<?= htmlspecialchars($u['username']) ?></td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#0a8c6a" id="bal<?= $u['id'] ?>"><?= formatMoney((float)$u['balance']) ?></td>
                <td style="color:#6b8ba4;font-size:.82rem"><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                <td>
                  <span style="padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700;background:<?= $u['is_active']?'#e6faf5':'#fff0f0' ?>;color:<?= $u['is_active']?'#0a8c6a':'#c0392b' ?>">
                    <?= $u['is_active'] ? 'Active' : 'Suspended' ?>
                  </span>
                </td>
                <td>
                  <div style="display:flex;gap:6px;flex-wrap:wrap">
                    <button class="action-btn btn-topup" onclick="openTopup(<?= $u['id'] ?>,'<?= htmlspecialchars($u['first_name'].' '.$u['last_name']) ?>')">
                      <i class="fas fa-plus"></i> Top Up
                    </button>
                    <button class="action-btn btn-delete" onclick="openDelete(<?= $u['id'] ?>,'<?= htmlspecialchars($u['first_name']) ?>')">
                      <i class="fas fa-trash"></i> Delete
                    </button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if (empty($users)): ?>
              <tr><td colspan="6" style="text-align:center;padding:40px;color:#6b8ba4">No users found.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
        <?php if ($totalPages > 1): ?>
        <div class="pagination">
          <a href="?search=<?= urlencode($search) ?>&page=<?= max(1,$page-1) ?>" class="page-btn <?= $page<=1?'disabled':'' ?>"><i class="fas fa-chevron-left"></i></a>
          <?php for($i=max(1,$page-2);$i<=min($totalPages,$page+2);$i++): ?>
            <a href="?search=<?= urlencode($search) ?>&page=<?= $i ?>" class="page-btn <?= $i===$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <a href="?search=<?= urlencode($search) ?>&page=<?= min($totalPages,$page+1) ?>" class="page-btn <?= $page>=$totalPages?'disabled':'' ?>"><i class="fas fa-chevron-right"></i></a>
        </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Top Up Modal -->
<div class="modal-overlay" id="topupModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title">💰 Top Up User Balance</div>
      <button class="modal-close" onclick="closeModal('topupModal')"><i class="fas fa-xmark"></i></button>
    </div>
    <p style="font-size:.88rem;color:#6b8ba4;margin-bottom:16px">Adding funds for: <strong id="topupName" style="color:#0d1b2a"></strong></p>
    <div id="topupAlert" style="display:none" class="alert mb-2"></div>
    <div class="form-group">
      <label class="form-label">Amount to Add (₦)</label>
      <input type="number" class="form-control" id="topupAmount" placeholder="e.g. 1000" min="1"/>
    </div>
    <div class="form-group">
      <label class="form-label">Note (optional)</label>
      <input type="text" class="form-control" id="topupNote" placeholder="e.g. Manual credit"/>
    </div>
    <button class="confirm-btn" style="background:linear-gradient(135deg,#0a8c6a,#057a5a);color:white" onclick="doTopup()">
      <i class="fas fa-plus-circle"></i> Add Funds
    </button>
  </div>
</div>

<!-- Delete Confirm Modal -->
<div class="modal-overlay" id="deleteModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title">⚠️ Delete User</div>
      <button class="modal-close" onclick="closeModal('deleteModal')"><i class="fas fa-xmark"></i></button>
    </div>
    <p style="font-size:.92rem;color:#3a5068;margin-bottom:8px">Are you sure you want to delete <strong id="deleteName" style="color:#c0392b"></strong>?</p>
    <p style="font-size:.83rem;color:#6b8ba4;margin-bottom:4px">This will permanently remove the user and all their data.</p>
    <div id="deleteAlert" style="display:none" class="alert mt-2"></div>
    <button class="confirm-btn" style="background:linear-gradient(135deg,#e74c3c,#c0392b);color:white" onclick="doDelete()">
      <i class="fas fa-trash"></i> Yes, Delete User
    </button>
    <button class="confirm-btn" style="background:#f0f7ff;color:#3a5068;margin-top:8px" onclick="closeModal('deleteModal')">Cancel</button>
  </div>
</div>

<div id="alertToast" class="alert"></div>
<script>
  let activeUserId = null;
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function closeModal(id){document.getElementById(id).classList.remove('active');}
  function showToast(msg,type='success'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='success'?'circle-check':'circle-exclamation'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',4000);}
  function showModalAlert(id,msg,type='error'){const el=document.getElementById(id);el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-circle-exclamation"></i> ${msg}`;el.style.display='flex';}

  function openTopup(uid, name) {
    activeUserId = uid;
    document.getElementById('topupName').textContent = name;
    document.getElementById('topupAmount').value = '';
    document.getElementById('topupNote').value = '';
    document.getElementById('topupAlert').style.display = 'none';
    document.getElementById('topupModal').classList.add('active');
  }
  function openDelete(uid, name) {
    activeUserId = uid;
    document.getElementById('deleteName').textContent = name;
    document.getElementById('deleteAlert').style.display = 'none';
    document.getElementById('deleteModal').classList.add('active');
  }

  async function doTopup() {
    const amount = parseFloat(document.getElementById('topupAmount').value);
    const note   = document.getElementById('topupNote').value;
    if (!amount || amount <= 0) { showModalAlert('topupAlert','Enter a valid amount.'); return; }
    const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/topup_user.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:activeUserId,amount,note})});
    const data = await res.json();
    if (data.success) {
      closeModal('topupModal');
      document.getElementById('bal'+activeUserId).textContent = data.new_balance;
      showToast(`Balance updated to ${data.new_balance}`);
    } else {
      showModalAlert('topupAlert', data.message || 'Failed.');
    }
  }

  async function doDelete() {
    const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/delete_user.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({user_id:activeUserId})});
    const data = await res.json();
    if (data.success) {
      closeModal('deleteModal');
      const row = document.getElementById('urow'+activeUserId);
      if (row) row.remove();
      showToast('User deleted successfully.');
    } else {
      showModalAlert('deleteAlert', data.message || 'Failed.');
    }
  }

  document.querySelectorAll('.modal-overlay').forEach(m => m.addEventListener('click',function(e){if(e.target===this)this.classList.remove('active');}));
</script>
</body>
</html>