<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'social-logins';
$items = $pdo->query('SELECT * FROM social_logins ORDER BY platform ASC, created_at DESC')->fetchAll();
$platforms = ['Facebook','Instagram','TikTok','Twitter','Snapchat','YouTube','LinkedIn','WhatsApp','Other'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Social Logins — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <?php include __DIR__ . '/admin_crud_styles.php'; ?>
  <style>
    .sold-badge{background:#fff0f0;color:#c0392b;padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700}
    .avail-badge{background:#e6faf5;color:#0a8c6a;padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700}
    .platform-tag{padding:3px 10px;border-radius:100px;font-size:.75rem;font-weight:700;background:#e8f4ff;color:#0057b8}
    .creds-cell{max-width:180px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;font-family:monospace;font-size:.8rem;color:#6b8ba4}
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-key" style="color:#e67e22"></i> Social Logins</div>
      </div>
      <div class="topbar-right">
        <button class="add-btn" onclick="openAdd()"><i class="fas fa-plus"></i> Add Login</button>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>
    <div class="dash-content">
      <!-- Summary -->
      <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px">
        <?php
        $total = count($items);
        $sold  = count(array_filter($items, fn($i) => $i['is_sold']));
        $avail = $total - $sold;
        ?>
        <div class="stat-card" style="--card-color:#1a8cff"><div class="stat-icon" style="background:#e8f4ff;color:#1a8cff"><i class="fas fa-key"></i></div><div class="stat-body"><div class="stat-label-sm">Total Stock</div><div class="stat-value"><?= $total ?></div></div></div>
        <div class="stat-card" style="--card-color:#0a8c6a"><div class="stat-icon" style="background:#e6faf5;color:#0a8c6a"><i class="fas fa-circle-check"></i></div><div class="stat-body"><div class="stat-label-sm">Available</div><div class="stat-value"><?= $avail ?></div></div></div>
        <div class="stat-card" style="--card-color:#e74c3c"><div class="stat-icon" style="background:#fff0f0;color:#e74c3c"><i class="fas fa-circle-xmark"></i></div><div class="stat-body"><div class="stat-label-sm">Sold</div><div class="stat-value"><?= $sold ?></div></div></div>
      </div>

      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-key"></i> All Logins <span class="count-badge"><?= $total ?></span></div>
        </div>
        <div style="overflow-x:auto">
          <table class="crud-table">
            <thead><tr><th>Platform</th><th>Title</th><th>Credentials Preview</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr id="row<?= $item['id'] ?>">
                <td><span class="platform-tag"><?= htmlspecialchars($item['platform']) ?></span></td>
                <td style="font-weight:600"><?= htmlspecialchars($item['title']) ?></td>
                <td><div class="creds-cell"><?= htmlspecialchars(substr($item['credentials'],0,40)) ?>...</div></td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff"><?= formatMoney((float)$item['price']) ?></td>
                <td><span class="<?= $item['is_sold']?'sold-badge':'avail-badge' ?>"><?= $item['is_sold']?'Sold':'Available' ?></span></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <?php if (!$item['is_sold']): ?>
                    <button class="act-btn edit" onclick='openEdit(<?= json_encode($item) ?>)'><i class="fas fa-pen"></i> Edit</button>
                    <?php endif; ?>
                    <button class="act-btn del" onclick="doDelete(<?= $item['id'] ?>,'social_login')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($items)): ?><tr><td colspan="6" class="empty-td">No social logins yet.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="itemModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">Add Social Login</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="formAlert" style="display:none" class="alert mb-2"></div>
    <input type="hidden" id="itemId"/>
    <div class="form-group">
      <label class="form-label">Platform *</label>
      <select class="form-control" id="fPlatform">
        <?php foreach ($platforms as $p): ?><option value="<?= $p ?>"><?= $p ?></option><?php endforeach; ?>
      </select>
    </div>
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" class="form-control" id="fTitle" placeholder="e.g. Facebook Account #1"/>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <input type="text" class="form-control" id="fDesc" placeholder="e.g. US region, 2yr old"/>
    </div>
    <div class="form-group">
      <label class="form-label">Credentials * <span style="font-size:.8rem;color:#6b8ba4">(shown only after purchase)</span></label>
      <textarea class="form-control" id="fLink2" rows="4" placeholder="Email: user@fb.com&#10;Password: Pass123!&#10;Recovery: recovery@email.com"></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Price (₦) *</label>
      <input type="number" class="form-control" id="fPrice" placeholder="1000" min="0"/>
    </div>
    <!-- hidden fields not used for social login but needed by shared JS -->
    <input type="hidden" id="fLink"/><input type="hidden" id="fActive" value="1"/><input type="hidden" id="fSample"/>
    <button class="save-btn" id="saveBtn" onclick="saveItem('social_login')"><i class="fas fa-save"></i> Save Login</button>
  </div>
</div>
<div id="alertToast" class="alert" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<?php include __DIR__ . '/admin_crud_js.php'; ?>
</body>
</html>