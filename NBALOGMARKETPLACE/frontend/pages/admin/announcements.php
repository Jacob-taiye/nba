<?php
require_once __DIR__ . '/../../../backend/includes/admin_guard.php';
$activePage = 'announcements';

$items = $pdo->query("SELECT * FROM announcements ORDER BY created_at DESC")->fetchAll();

$typeColors = [
    'info'    => ['#e8f4ff','#1a8cff','fa-circle-info'],
    'warning' => ['#fff3e8','#e67e22','fa-triangle-exclamation'],
    'success' => ['#e6faf5','#0a8c6a','fa-circle-check'],
    'danger'  => ['#fff0f0','#c0392b','fa-circle-exclamation'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Announcements — Admin</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .ann-card { background:white; border:1.5px solid #e8f4ff; border-radius:14px; padding:18px 20px; display:flex; align-items:flex-start; gap:14px; transition:all .2s; }
    .ann-card:hover { box-shadow:0 4px 14px rgba(26,140,255,.08); }
    .ann-card.inactive { opacity:.55; }
    .ann-icon { width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.1rem; flex-shrink:0; }
    .ann-body { flex:1; min-width:0; }
    .ann-msg  { font-size:.93rem; color:#0d1b2a; font-weight:600; line-height:1.5; word-break:break-word; }
    .ann-meta { font-size:.75rem; color:#6b8ba4; margin-top:5px; display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .ann-actions { display:flex; gap:7px; flex-shrink:0; }
    .act-btn { padding:6px 12px; border:none; border-radius:7px; font-size:.78rem; font-weight:700; cursor:pointer; transition:all .2s; display:inline-flex; align-items:center; gap:5px; }
    .btn-edit   { background:#e8f4ff; color:#1a8cff; border:1.5px solid #b3d9ff; }
    .btn-toggle { background:#fff3e8; color:#e67e22; border:1.5px solid #ffd5a8; }
    .btn-toggle.active { background:#e6faf5; color:#0a8c6a; border-color:#b2dfdb; }
    .btn-del    { background:#fff0f0; color:#c0392b; border:1.5px solid #f5c6c6; }
    .type-pill  { padding:3px 9px; border-radius:100px; font-size:.72rem; font-weight:700; }
    .add-btn { padding:10px 20px; background:linear-gradient(135deg,#1a8cff,#0057b8); color:white; border:none; border-radius:10px; font-family:'Outfit',sans-serif; font-weight:700; font-size:.9rem; cursor:pointer; display:flex; align-items:center; gap:8px; transition:all .2s; }
    .add-btn:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(26,140,255,.3); }
    /* Ticker preview */
    .ticker-preview { background:#0d1b2a; border-radius:12px; padding:14px 18px; overflow:hidden; position:relative; }
    .ticker-track { display:flex; white-space:nowrap; animation:tickerScroll 20s linear infinite; }
    .ticker-track:hover { animation-play-state:paused; }
    @keyframes tickerScroll { 0%{transform:translateX(0)} 100%{transform:translateX(-50%)} }
    .ticker-item { padding:0 40px; font-size:.88rem; color:white; display:inline-flex; align-items:center; gap:8px; }
    /* Modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(13,27,42,.6);backdrop-filter:blur(6px);z-index:9000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all .28s}
    .modal-overlay.active{opacity:1;visibility:visible}
    .modal-box{background:white;border-radius:22px;padding:32px;width:90%;max-width:500px;transform:scale(.93) translateY(20px);transition:all .28s}
    .modal-overlay.active .modal-box{transform:scale(1) translateY(0)}
    .modal-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px}
    .modal-title{font-family:'Outfit',sans-serif;font-size:1.15rem;font-weight:800;color:#0d1b2a}
    .modal-close{width:34px;height:34px;border-radius:50%;border:none;cursor:pointer;background:#f0f7ff;color:#6b8ba4;display:flex;align-items:center;justify-content:center}
    .modal-close:hover{background:#e8f4ff;color:#1a8cff}
    .save-btn{width:100%;padding:13px;background:linear-gradient(135deg,#1a8cff,#0057b8);color:white;border:none;border-radius:10px;font-family:'Outfit',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer;margin-top:6px;display:flex;align-items:center;justify-content:center;gap:8px}
    #alertToast{position:fixed;bottom:24px;right:24px;z-index:9999;display:none;max-width:340px}
    .type-option { display:flex; align-items:center; gap:8px; padding:10px 14px; border:1.5px solid #e8f4ff; border-radius:10px; cursor:pointer; transition:all .18s; }
    .type-option:hover { border-color:#1a8cff; }
    .type-option.selected { border-color:var(--tc); background:var(--tbg); }
    .type-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-bottom:16px; }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-bullhorn" style="color:#e67e22"></i> Announcements</div>
      </div>
      <div class="topbar-right">
        <button class="add-btn" onclick="openAdd()"><i class="fas fa-plus"></i> New Announcement</button>
        <div class="topbar-user"><div class="topbar-avatar"><?= adminInitials($currentAdmin) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentAdmin['name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">

      <!-- Live Ticker Preview -->
      <?php $active = array_filter($items, fn($i) => $i['is_active']); ?>
      <?php if (!empty($active)): ?>
      <div class="section-card" style="margin-bottom:20px">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-eye"></i> Live Preview — What Users See</div>
        </div>
        <div class="section-card-body" style="padding:14px 20px">
          <div class="ticker-preview">
            <div class="ticker-track" id="previewTrack">
              <?php foreach ($active as $a):
                $tc = $typeColors[$a['type']] ?? $typeColors['info'];
              ?>
              <div class="ticker-item">
                <i class="fas <?= $tc[2] ?>" style="color:<?= $tc[1] ?>"></i>
                <?= htmlspecialchars($a['message']) ?>
                <span style="color:#3a5068;margin:0 10px">•</span>
              </div>
              <?php endforeach; ?>
              <?php /* Duplicate for seamless loop */ foreach ($active as $a):
                $tc = $typeColors[$a['type']] ?? $typeColors['info'];
              ?>
              <div class="ticker-item">
                <i class="fas <?= $tc[2] ?>" style="color:<?= $tc[1] ?>"></i>
                <?= htmlspecialchars($a['message']) ?>
                <span style="color:#3a5068;margin:0 10px">•</span>
              </div>
              <?php endforeach; ?>
            </div>
          </div>
        </div>
      </div>
      <?php endif; ?>

      <!-- Announcements List -->
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title"><i class="fas fa-list"></i> All Announcements
            <span style="background:#e8f4ff;color:#1a8cff;padding:3px 10px;border-radius:100px;font-size:.76rem;font-weight:700"><?= count($items) ?></span>
          </div>
        </div>
        <div style="padding:16px;display:flex;flex-direction:column;gap:12px" id="annList">
          <?php if (empty($items)): ?>
            <div style="text-align:center;padding:40px;color:#6b8ba4">
              <i class="fas fa-bullhorn" style="font-size:2rem;opacity:.2;display:block;margin-bottom:12px"></i>
              No announcements yet. Click "New Announcement" to add one.
            </div>
          <?php else: foreach ($items as $a):
            $tc = $typeColors[$a['type']] ?? $typeColors['info'];
          ?>
          <div class="ann-card <?= $a['is_active'] ? '' : 'inactive' ?>" id="ann<?= $a['id'] ?>">
            <div class="ann-icon" style="background:<?= $tc[0] ?>;color:<?= $tc[1] ?>">
              <i class="fas <?= $tc[2] ?>"></i>
            </div>
            <div class="ann-body">
              <div class="ann-msg"><?= htmlspecialchars($a['message']) ?></div>
              <div class="ann-meta">
                <span class="type-pill" style="background:<?= $tc[0] ?>;color:<?= $tc[1] ?>"><?= ucfirst($a['type']) ?></span>
                <span><?= date('M j, Y g:i A', strtotime($a['created_at'])) ?></span>
                <span style="font-weight:700;color:<?= $a['is_active'] ? '#0a8c6a' : '#c0392b' ?>">
                  <?= $a['is_active'] ? '● Live' : '○ Hidden' ?>
                </span>
              </div>
            </div>
            <div class="ann-actions">
              <button class="act-btn btn-edit" onclick='openEdit(<?= json_encode($a) ?>)'>
                <i class="fas fa-pen"></i>
              </button>
              <button class="act-btn btn-toggle <?= $a['is_active'] ? 'active' : '' ?>"
                      id="tog<?= $a['id'] ?>"
                      onclick="toggleAnn(<?= $a['id'] ?>, <?= $a['is_active'] ? 0 : 1 ?>)">
                <i class="fas fa-<?= $a['is_active'] ? 'eye-slash' : 'eye' ?>"></i>
                <?= $a['is_active'] ? 'Hide' : 'Show' ?>
              </button>
              <button class="act-btn btn-del" onclick="deleteAnn(<?= $a['id'] ?>)">
                <i class="fas fa-trash"></i>
              </button>
            </div>
          </div>
          <?php endforeach; endif; ?>
        </div>
      </div>

    </div>
  </div>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay" id="annModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">New Announcement</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div id="formAlert" style="display:none" class="alert mb-2"></div>
    <input type="hidden" id="annId"/>

    <!-- Type selector -->
    <div class="form-group">
      <label class="form-label">Type</label>
      <div class="type-grid">
        <div class="type-option selected" data-type="info" style="--tc:#1a8cff;--tbg:#e8f4ff" onclick="selectType('info',this)">
          <i class="fas fa-circle-info" style="color:#1a8cff"></i><span style="font-weight:700;font-size:.85rem">Info</span>
        </div>
        <div class="type-option" data-type="warning" style="--tc:#e67e22;--tbg:#fff3e8" onclick="selectType('warning',this)">
          <i class="fas fa-triangle-exclamation" style="color:#e67e22"></i><span style="font-weight:700;font-size:.85rem">Warning</span>
        </div>
        <div class="type-option" data-type="success" style="--tc:#0a8c6a;--tbg:#e6faf5" onclick="selectType('success',this)">
          <i class="fas fa-circle-check" style="color:#0a8c6a"></i><span style="font-weight:700;font-size:.85rem">Success</span>
        </div>
        <div class="type-option" data-type="danger" style="--tc:#c0392b;--tbg:#fff0f0" onclick="selectType('danger',this)">
          <i class="fas fa-circle-exclamation" style="color:#c0392b"></i><span style="font-weight:700;font-size:.85rem">Danger</span>
        </div>
      </div>
    </div>

    <div class="form-group">
      <label class="form-label">Message *</label>
      <textarea class="form-control" id="annMessage" rows="4" placeholder="Type your announcement here... e.g. 'System maintenance scheduled for Sunday 2AM – 4AM.'"></textarea>
      <div style="font-size:.76rem;color:#6b8ba4;margin-top:5px"><i class="fas fa-info-circle"></i> This will scroll across all user pages until hidden or deleted.</div>
    </div>

    <button class="save-btn" id="saveBtn" onclick="saveAnn()">
      <i class="fas fa-paper-plane"></i> <span id="saveBtnText">Publish Announcement</span>
    </button>
  </div>
</div>

<div id="alertToast" class="alert"></div>
<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function closeModal(){document.getElementById('annModal').classList.remove('active');}
  function showToast(msg,type='success'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='success'?'circle-check':'circle-exclamation'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',3500);}

  let selectedType = 'info';
  function selectType(type, el) {
    document.querySelectorAll('.type-option').forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    selectedType = type;
  }

  function openAdd() {
    document.getElementById('annId').value = '';
    document.getElementById('annMessage').value = '';
    document.getElementById('modalTitle').textContent = 'New Announcement';
    document.getElementById('saveBtnText').textContent = 'Publish Announcement';
    document.getElementById('formAlert').style.display = 'none';
    selectType('info', document.querySelector('[data-type="info"]'));
    document.getElementById('annModal').classList.add('active');
    setTimeout(() => document.getElementById('annMessage').focus(), 300);
  }

  function openEdit(ann) {
    document.getElementById('annId').value = ann.id;
    document.getElementById('annMessage').value = ann.message;
    document.getElementById('modalTitle').textContent = 'Edit Announcement';
    document.getElementById('saveBtnText').textContent = 'Save Changes';
    document.getElementById('formAlert').style.display = 'none';
    const typeEl = document.querySelector(`[data-type="${ann.type}"]`);
    if (typeEl) selectType(ann.type, typeEl);
    document.getElementById('annModal').classList.add('active');
  }

  async function saveAnn() {
    const message = document.getElementById('annMessage').value.trim();
    if (!message) {
      const al = document.getElementById('formAlert');
      al.className = 'alert alert-error mb-2';
      al.innerHTML = '<i class="fas fa-circle-exclamation"></i> Message cannot be empty.';
      al.style.display = 'flex'; return;
    }
    const id  = document.getElementById('annId').value;
    const btn = document.getElementById('saveBtn');
    btn.disabled = true;
    document.getElementById('saveBtnText').textContent = 'Saving...';

    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/save_announcement.php?action=save', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id: id ? parseInt(id) : 0, message, type: selectedType })
      });
      const data = await res.json();
      if (data.success) {
        closeModal();
        showToast(data.message);
        setTimeout(() => location.reload(), 800);
      } else {
        const al = document.getElementById('formAlert');
        al.className = 'alert alert-error mb-2';
        al.innerHTML = `<i class="fas fa-circle-exclamation"></i> ${data.message}`;
        al.style.display = 'flex';
      }
    } catch(e) { showToast('Connection error.','error'); }
    btn.disabled = false;
    document.getElementById('saveBtnText').textContent = id ? 'Save Changes' : 'Publish Announcement';
  }

  async function toggleAnn(id, newState) {
    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/save_announcement.php?action=toggle', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id, is_active: newState })
      });
      const data = await res.json();
      if (data.success) {
        showToast(data.message);
        setTimeout(() => location.reload(), 600);
      }
    } catch(e) { showToast('Connection error.','error'); }
  }

  async function deleteAnn(id) {
    if (!confirm('Delete this announcement? This cannot be undone.')) return;
    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/admin/save_announcement.php?action=delete', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ id })
      });
      const data = await res.json();
      if (data.success) {
        document.getElementById('ann'+id)?.remove();
        showToast('Deleted.');
      }
    } catch(e) { showToast('Connection error.','error'); }
  }

  document.getElementById('annModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>