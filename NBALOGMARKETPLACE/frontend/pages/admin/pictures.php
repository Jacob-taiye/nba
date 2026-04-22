<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'pictures';
$items = $pdo->query('SELECT * FROM working_pictures ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/><meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Working Pictures — Admin</title>
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
        <div class="page-title"><i class="fas fa-images" style="color:#8e44ad"></i> Working Pictures</div>
      </div>
      <div class="topbar-right">
        <button class="add-btn" onclick="openAdd()"><i class="fas fa-plus"></i> Add Picture</button>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>
    <div class="dash-content">
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-images"></i> All Pictures <span class="count-badge"><?= count($items) ?></span></div>
        </div>
        <div style="overflow-x:auto">
          <table class="crud-table">
            <thead><tr><th>Preview</th><th>Title</th><th>Price</th><th>Status</th><th>Added</th><th>Actions</th></tr></thead>
            <tbody>
              <?php foreach ($items as $item): ?>
              <tr id="row<?= $item['id'] ?>">
                <td>
                  <?php if ($item['sample_image']): ?>
                    <img src="<?= htmlspecialchars($item['sample_image']) ?>" style="width:60px;height:48px;object-fit:cover;border-radius:8px;border:1.5px solid #d6ecff" loading="lazy"/>
                  <?php else: ?>
                    <div style="width:60px;height:48px;background:#f0f7ff;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#b3d9ff"><i class="fas fa-image"></i></div>
                  <?php endif; ?>
                </td>
                <td style="font-weight:600"><?= htmlspecialchars($item['title']) ?></td>
                <td style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff"><?= formatMoney((float)$item['price']) ?></td>
                <td><span class="status-dot <?= $item['is_active']?'active':'inactive' ?>"><?= $item['is_active']?'Active':'Hidden' ?></span></td>
                <td style="color:#6b8ba4;font-size:.82rem"><?= date('M j, Y', strtotime($item['created_at'])) ?></td>
                <td>
                  <div style="display:flex;gap:6px">
                    <button class="act-btn edit" onclick='openEdit(<?= json_encode($item) ?>)'><i class="fas fa-pen"></i> Edit</button>
                    <button class="act-btn del" onclick="doDelete(<?= $item['id'] ?>,'working_picture')"><i class="fas fa-trash"></i></button>
                  </div>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($items)): ?><tr><td colspan="6" class="empty-td">No pictures yet.</td></tr><?php endif; ?>
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
      <div class="modal-title" id="modalTitle">Add Picture</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="formAlert" style="display:none" class="alert mb-2"></div>
    <input type="hidden" id="itemId"/>
    <div class="form-group">
      <label class="form-label">Title *</label>
      <input type="text" class="form-control" id="fTitle" placeholder="Picture pack name"/>
    </div>
    <div class="form-group">
      <label class="form-label">Sample Image URL * <span style="font-size:.8rem;color:#6b8ba4">(visible to all users)</span></label>
      <input type="url" class="form-control" id="fSample" placeholder="https://..." oninput="previewSample()"/>
      <img id="samplePreview" class="sample-preview" style="display:none;margin-top:8px"/>
    </div>
    <div class="form-group">
      <label class="form-label">Download Link * <span style="font-size:.8rem;color:#6b8ba4">(hidden until purchased)</span></label>
      <input type="url" class="form-control" id="fLink" placeholder="https://drive.google.com/..."/>
    </div>
    <div class="form-group">
      <label class="form-label">Price (₦) *</label>
      <input type="number" class="form-control" id="fPrice" placeholder="500" min="0"/>
    </div>
    <div class="form-group">
      <label class="form-label">Status</label>
      <select class="form-control" id="fActive">
        <option value="1">Active</option><option value="0">Hidden</option>
      </select>
    </div>
    <button class="save-btn" id="saveBtn" onclick="saveItem('working_picture')"><i class="fas fa-save"></i> Save Picture</button>
  </div>
</div>
<div id="alertToast" class="alert" style="position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<?php include __DIR__ . '/admin_crud_js.php'; ?>
</body>
</html>