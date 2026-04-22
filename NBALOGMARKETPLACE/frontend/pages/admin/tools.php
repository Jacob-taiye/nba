<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'tools';
$items = $pdo->query('SELECT * FROM tools ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Tools — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <?php include __DIR__ . '/admin_crud_styles.php'; ?>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-screwdriver-wrench" style="color:#0984e3"></i> Formats</div>
      </div>
      <div class="topbar-right">
        <button class="add-btn" onclick="openAdd()"><i class="fas fa-plus"></i> Add Tool</button>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>
    <div class="dash-content">
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-screwdriver-wrench"></i> All Tools <span class="count-badge"><?= count($items) ?></span></div>
        </div>
        <div style="overflow-x:auto">
          <table class="crud-table">
            <thead><tr><th>#</th><th>Title</th><th>Description</th><th>Price</th><th>Status</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($items as $i => $item): ?>
              <tr id="row<?= $item['id'] ?>">
                <td style="color:#6b8ba4"><?= $i+1 ?></td>
                <td style="font-weight:600"><?= htmlspecialchars($item['title']) ?></td>
                <td style="color:#6b8ba4;max-width:240px"><div class="truncate"><?= htmlspecialchars($item['description'] ?? '-') ?></div></td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff"><?= formatMoney((float)$item['price']) ?></td>
                <td><span class="status-dot <?= $item['is_active']?'active':'inactive' ?>"><?= $item['is_active']?'Active':'Hidden' ?></span></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <button class="act-btn edit" onclick='openEdit(<?= json_encode($item) ?>)'><i class="fas fa-pen"></i> Edit</button>
                    <button class="act-btn del" onclick="doDelete(<?= $item['id'] ?>,'tool')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($items)): ?><tr><td colspan="6" class="empty-td">No tools yet. Click "Add Tool" to get started.</td></tr><?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="itemModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">Add Tool</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="formAlert" style="display:none" class="alert mb-2"></div>
    <input type="hidden" id="itemId"/>
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" class="form-control" id="fTitle" placeholder="Format name"/>
    </div>
    <div class="form-group">
      <label class="form-label">Description</label>
      <textarea class="form-control" id="fDesc" rows="3" placeholder="Short description..."></textarea>
    </div>
    <div class="form-group">
      <label class="form-label">Download Link *</label>
      <input type="url" class="form-control" id="fLink" placeholder="https://drive.google.com/..."/>
    </div>
    <div class="form-group">
      <label class="form-label">Price (₦) *</label>
      <input type="number" class="form-control" id="fPrice" placeholder="500" min="0"/>
    </div>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select class="form-control" id="fActive">
        <option value="1">Active (visible to users)</option>
        <option value="0">Hidden</option>
      </select>
    </div>
    <button class="save-btn" id="saveBtn" onclick="saveItem('tool')">
      <i class="fas fa-save"></i> Save Tool
    </button>
  </div>
</div>
<div id="alertToast" class="alert" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<?php include __DIR__ . '/admin_crud_js.php'; ?>
</body>
</html>