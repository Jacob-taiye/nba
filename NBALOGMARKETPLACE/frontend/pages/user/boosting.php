<?php
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';
$activePage = 'boosting';

// My recent boost orders
$myOrders = $pdo->prepare(
    "SELECT * FROM boost_orders WHERE user_id = ? ORDER BY created_at DESC LIMIT 20"
);
$myOrders->execute([$currentUser['id']]);
$myOrders = $myOrders->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Social Boosting — VirtualHub Pro</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    /* Desktop: services | form | orders  — Mobile: single column */
    .boost-layout { display:grid; grid-template-columns:1fr 380px 1fr; gap:20px; align-items:start; }
    @media(max-width:1200px){ .boost-layout{ grid-template-columns:1fr 1fr; } }
    @media(max-width:1200px){ .col-orders{ grid-column:1/-1; } }
    @media(max-width:700px){ .boost-layout{ grid-template-columns:1fr; gap:14px; } }
    @media(max-width:700px){ .col-orders{ grid-column:auto; } }

    /* Filter bar — sits above scrollable list, never moves */
    .svc-picker-card { display:flex; flex-direction:column; }
    .svc-filter-bar { flex-shrink:0; padding:14px 18px 12px; border-bottom:1.5px solid #e8f4ff; background:white; }
    .filter-top { display:flex; gap:10px; align-items:center; margin-bottom:10px; }
    .filter-search-wrap { position:relative; flex:1; }
    .filter-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#b3d9ff; font-size:.9rem; pointer-events:none; }
    .svc-search-big { width:100%; padding:10px 14px 10px 38px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.9rem; font-family:inherit; outline:none; box-sizing:border-box; }
    .svc-search-big:focus { border-color:#1a8cff; box-shadow:0 0 0 3px rgba(26,140,255,.1); }
    .svc-result-count { font-size:.75rem; color:#6b8ba4; white-space:nowrap; font-weight:700; min-width:60px; text-align:right; }

    /* Category tabs — horizontally scrollable */
    .cat-tabs { display:flex; gap:6px; overflow-x:auto; padding-bottom:3px; scrollbar-width:none; }
    .cat-tabs::-webkit-scrollbar { display:none; }
    .cat-tab { padding:6px 13px; border-radius:100px; border:1.5px solid #d6ecff; background:white; color:#3a5068; font-size:.76rem; font-weight:700; cursor:pointer; transition:all .18s; display:flex; align-items:center; gap:5px; white-space:nowrap; flex-shrink:0; user-select:none; }
    .cat-tab:hover { border-color:#1a8cff; color:#1a8cff; }
    .cat-tab.active { background:#1a8cff; border-color:#1a8cff; color:white; }
    .cat-tab i { font-size:.82rem; }
    .cat-tab .tab-count { padding:1px 5px; border-radius:100px; font-size:.68rem; background:#e8f4ff; color:#1a8cff; }
    .cat-tab.active .tab-count { background:rgba(255,255,255,.25); color:white; }

    /* Scrollable service list */
    .svc-list-wrap { flex:1; overflow-y:auto; padding:12px 18px 18px; min-height:0; max-height:360px; }
    .svc-list-wrap::-webkit-scrollbar { width:4px; }
    .svc-list-wrap::-webkit-scrollbar-track { background:#f0f7ff; }
    .svc-list-wrap::-webkit-scrollbar-thumb { background:#b3d9ff; border-radius:4px; }
    .service-list { display:flex; flex-direction:column; gap:7px; }
    .svc-row { border:1.5px solid #e8f4ff; border-radius:11px; padding:12px 14px; cursor:pointer; transition:all .18s; background:white; display:flex; align-items:center; gap:12px; }
    .svc-row:hover { border-color:#1a8cff; background:#fafcff; transform:translateX(2px); }
    .svc-row.selected { border-color:#1a8cff; background:#f0f8ff; }
    .svc-row-icon { width:36px; height:36px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.05rem; flex-shrink:0; }
    .svc-row-body { flex:1; min-width:0; }
    .svc-name { font-weight:700; font-size:.88rem; color:#0d1b2a; margin-bottom:4px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .svc-meta { display:flex; gap:6px; flex-wrap:wrap; }
    .svc-badge { font-size:.7rem; font-weight:700; padding:2px 7px; border-radius:100px; }
    .badge-rate  { background:#e6faf5; color:#0a8c6a; }
    .badge-min   { background:#fff3e8; color:#e67e22; }
    .badge-max   { background:#e8f4ff; color:#1a8cff; }
    .badge-refill{ background:#f3e8ff; color:#8e44ad; }
    .svc-row-price { font-family:'Outfit',sans-serif; font-weight:800; font-size:.88rem; color:#1a8cff; white-space:nowrap; flex-shrink:0; text-align:right; }
    .svc-row-price small { display:block; font-size:.65rem; font-weight:600; color:#6b8ba4; }

    /* Order form */
    .order-form { display:flex; flex-direction:column; gap:14px; }
    .selected-svc-box { background:#f0f8ff; border:1.5px solid #b3d9ff; border-radius:12px; padding:14px 16px; }
    .selected-svc-name { font-weight:700; color:#0d1b2a; font-size:.95rem; margin-bottom:6px; }
    .qty-presets { display:flex; gap:8px; flex-wrap:wrap; margin-top:6px; }
    .qty-btn { padding:6px 14px; border:1.5px solid #d6ecff; border-radius:8px; background:white; font-size:.82rem; font-weight:700; color:#3a5068; cursor:pointer; transition:all .2s; }
    .qty-btn:hover { border-color:#1a8cff; color:#1a8cff; }
    .qty-btn.active { background:#1a8cff; border-color:#1a8cff; color:white; }
    .cost-display { background:linear-gradient(135deg,#e8f4ff,#f0f9ff); border:1.5px solid #b3d9ff; border-radius:12px; padding:16px 20px; display:flex; align-items:center; justify-content:space-between; }
    .cost-big { font-family:'Outfit',sans-serif; font-size:1.5rem; font-weight:800; color:#1a8cff; }
    .place-btn { width:100%; padding:14px; background:linear-gradient(135deg,#1a8cff,#0057b8); color:white; border:none; border-radius:12px; font-family:'Outfit',sans-serif; font-weight:700; font-size:1rem; cursor:pointer; transition:all .2s; display:flex; align-items:center; justify-content:center; gap:10px; }
    .place-btn:hover:not(:disabled){ transform:translateY(-2px); box-shadow:0 6px 20px rgba(26,140,255,.35); }
    .place-btn:disabled { opacity:.6; cursor:not-allowed; transform:none; }

    /* Orders list */
    .order-card { border:1.5px solid #e8f4ff; border-radius:14px; padding:16px; background:white; transition:all .2s; }
    .order-card:hover { box-shadow:0 4px 14px rgba(26,140,255,.08); }
    .order-progress { height:6px; background:#e8f4ff; border-radius:100px; overflow:hidden; margin:10px 0 6px; }
    .order-fill { height:100%; border-radius:100px; background:linear-gradient(90deg,#1a8cff,#00c6ae); transition:width .5s; }
    .status-pill { padding:3px 10px; border-radius:100px; font-size:.73rem; font-weight:700; }
    .sp-pending     { background:#fff3e8; color:#e67e22; }
    .sp-in_progress { background:#e8f4ff; color:#1a8cff; }
    .sp-completed   { background:#e6faf5; color:#0a8c6a; }
    .sp-partial     { background:#f3e8ff; color:#8e44ad; }
    .sp-cancelled,.sp-failed { background:#fff0f0; color:#c0392b; }
    .empty-orders { text-align:center; padding:40px 20px; color:#6b8ba4; }
    .empty-orders i { font-size:2.5rem; opacity:.2; display:block; margin-bottom:12px; }

    /* Success modal */
    .modal-overlay{position:fixed;inset:0;background:rgba(13,27,42,.6);backdrop-filter:blur(6px);z-index:9000;display:flex;align-items:center;justify-content:center;opacity:0;visibility:hidden;transition:all .28s}
    .modal-overlay.active{opacity:1;visibility:visible}
    .modal-box{background:white;border-radius:22px;padding:36px;width:90%;max-width:420px;transform:scale(.93) translateY(20px);transition:all .28s;text-align:center}
    .modal-overlay.active .modal-box{transform:scale(1) translateY(0)}
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-rocket" style="color:#e67e22"></i> Social Boosting</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance" id="balDisplay"><i class="fas fa-wallet"></i> <?= formatMoney((float)$currentUser['balance']) ?></div>
        <div class="topbar-user"><div class="topbar-avatar"><?= userInitials($currentUser) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentUser['first_name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">
      <div class="boost-layout">

        <!-- COL 1: Service Picker -->
        <div class="section-card svc-picker-card">
          <div class="section-card-header">
            <div class="section-card-title"><i class="fas fa-list"></i> Choose a Service</div>
          </div>
          <div class="section-card-body" style="padding:0;display:flex;flex-direction:column;flex:1;min-height:0;">
            <!-- Filter bar -->
            <div class="svc-filter-bar">
              <div class="filter-top">
                <div class="filter-search-wrap">
                  <i class="fas fa-search filter-search-icon"></i>
                  <input type="text" class="svc-search-big" id="svcSearch" placeholder="Search e.g. Instagram Followers..." oninput="filterSearch(this.value)"/>
                </div>
                <div class="svc-result-count" id="svcResultCount"></div>
              </div>
              <div class="cat-tabs" id="catTabs">
                <div class="cat-tab active" onclick="filterCategory('all', this)" data-cat="all">
                  <i class="fas fa-th-large"></i> All
                </div>
              </div>
            </div>
            <!-- Service list -->
            <div class="svc-list-wrap">
              <div id="svcLoading" style="text-align:center;padding:30px;color:#6b8ba4">
                <span class="spinner"></span> Loading services...
              </div>
              <div class="service-list" id="svcList" style="display:none"></div>
            </div>
          </div>
        </div>

        <!-- COL 2: Order Form -->
        <div class="section-card" id="orderFormCard">
          <div class="section-card-header">
            <div class="section-card-title"><i class="fas fa-bolt"></i> Place Order</div>
          </div>
          <div class="section-card-body">
            <!-- Empty state — before service selected -->
            <div id="formEmptyState" style="text-align:center;padding:40px 20px;color:#6b8ba4">
              <i class="fas fa-hand-pointer" style="font-size:2.2rem;opacity:.2;display:block;margin-bottom:14px"></i>
              <div style="font-weight:700;color:#3a5068;margin-bottom:6px">Select a Service</div>
              <div style="font-size:.85rem">Click any service on the left to place an order</div>
            </div>
            <!-- Order form — shown after service selected -->
            <div class="order-form" id="orderFormInner" style="display:none">
              <!-- Selected service info -->
              <div class="selected-svc-box" id="selectedSvcBox">
                <div class="selected-svc-name" id="selectedSvcName">—</div>
                <div style="font-size:.78rem;color:#6b8ba4" id="selectedSvcRange"></div>
              </div>
              <!-- Link input -->
              <div class="form-group" style="margin:0">
                <label class="form-label">Profile / Post URL *</label>
                <input type="url" class="form-control" id="boostLink" placeholder="https://instagram.com/yourprofile"/>
              </div>
              <!-- Quantity -->
              <div class="form-group" style="margin:0">
                <label class="form-label">Quantity</label>
                <input type="number" class="form-control" id="boostQty" placeholder="e.g. 500" min="1" oninput="recalcCost()"/>
                <div class="qty-presets" id="qtyPresets"></div>
              </div>
              <!-- Cost -->
              <div class="cost-display" id="costDisplay" style="display:none">
                <div>
                  <div style="font-size:.78rem;color:#6b8ba4;margin-bottom:4px">Total Cost</div>
                  <div class="cost-big" id="costValue">₦0</div>
                </div>
                <div style="text-align:right;font-size:.78rem;color:#6b8ba4">
                  <div id="costPerK"></div>
                  <div>Charged from wallet</div>
                </div>
              </div>
              <div id="boostAlert" style="display:none" class="alert"></div>
              <button class="place-btn" id="placeBtn" onclick="placeOrder()" disabled>
                <i class="fas fa-rocket"></i> <span id="placeBtnText">Select a service first</span>
              </button>
            </div>
          </div>
        </div>

        <!-- COL 3: My Orders -->
        <div class="col-orders">
          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-clock-rotate-left"></i> My Orders</div>
              <button onclick="refreshOrders()" style="background:none;border:none;color:#1a8cff;cursor:pointer;font-size:.85rem;font-weight:600"><i class="fas fa-rotate-right"></i> Refresh</button>
            </div>
            <div id="ordersList" style="padding:16px;display:flex;flex-direction:column;gap:12px">
              <?php if (empty($myOrders)): ?>
                <div class="empty-orders"><i class="fas fa-rocket"></i><p>No orders yet. Place your first boost!</p></div>
              <?php else: foreach ($myOrders as $ord):
                $statusClass = 'sp-' . $ord['status'];
                $progress = 0;
                if ($ord['start_count'] && $ord['quantity']) {
                    $delivered = $ord['quantity'] - ($ord['remains'] ?? $ord['quantity']);
                    $progress  = min(100, round(($delivered / $ord['quantity']) * 100));
                }
              ?>
              <div class="order-card" id="ocard<?= $ord['id'] ?>">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap">
                  <div style="flex:1">
                    <div style="font-weight:700;font-size:.9rem;color:#0d1b2a"><?= htmlspecialchars($ord['service_name']) ?></div>
                    <div style="font-size:.76rem;color:#6b8ba4;margin-top:2px;word-break:break-all"><?= htmlspecialchars($ord['link']) ?></div>
                  </div>
                  <span class="status-pill <?= $statusClass ?>" id="ost<?= $ord['id'] ?>"><?= ucfirst(str_replace('_',' ',$ord['status'])) ?></span>
                </div>
                <div class="order-progress">
                  <div class="order-fill" id="ofill<?= $ord['id'] ?>" style="width:<?= $progress ?>%"></div>
                </div>
                <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#6b8ba4">
                  <span id="oprog<?= $ord['id'] ?>"><?= number_format($ord['quantity']) ?> ordered<?= $ord['remains'] !== null ? ' · ' . number_format($ord['remains']) . ' remaining' : '' ?></span>
                  <span style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff"><?= formatMoney((float)$ord['amount_paid']) ?></span>
                </div>
                <div style="font-size:.74rem;color:#b3c9d6;margin-top:4px"><?= date('M j, Y g:i A', strtotime($ord['created_at'])) ?></div>
              </div>
              <?php endforeach; endif; ?>
            </div>
          </div>
        </div>

      </div><!-- end boost-layout -->
    </div><!-- end dash-content -->
  </div>
</div>

<!-- Success Modal -->
<div class="modal-overlay" id="successModal">
  <div class="modal-box">
    <div style="font-size:3rem;margin-bottom:12px">🚀</div>
    <div style="font-family:'Outfit',sans-serif;font-size:1.2rem;font-weight:800;color:#0d1b2a;margin-bottom:8px">Order Placed!</div>
    <p style="font-size:.9rem;color:#6b8ba4;margin-bottom:6px" id="successDesc"></p>
    <p style="font-size:.82rem;color:#0a8c6a;margin-bottom:20px"><i class="fas fa-circle-check"></i> Delivery starts within minutes</p>
    <button onclick="closeModal()" style="width:100%;padding:13px;background:linear-gradient(135deg,#1a8cff,#0057b8);color:white;border:none;border-radius:12px;font-family:'Outfit',sans-serif;font-weight:700;font-size:.95rem;cursor:pointer">
      Done
    </button>
  </div>
</div>

<div id="alertToast" style="position:fixed;bottom:80px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>" target="_blank" class="whatsapp-fab"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>

<script>
  // ── Sidebar ───────────────────────────────────────────────────────────────
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function showToast(msg,type='error'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='error'?'circle-exclamation':'circle-check'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',4000);}
  function closeModal(){document.getElementById('successModal').classList.remove('active');}

  // ── State ─────────────────────────────────────────────────────────────────
  let allServices  = {};   // { category: [services] }
  let activeCategory = 'all';
  let selectedSvc  = null;

  // ── Platform icon helper ──────────────────────────────────────────────────
  const platformMeta = {
    instagram: ['fab fa-instagram','#e1306c'],
    tiktok:    ['fab fa-tiktok','#010101'],
    facebook:  ['fab fa-facebook','#1877f2'],
    twitter:   ['fab fa-twitter','#1da1f2'],
    youtube:   ['fab fa-youtube','#ff0000'],
    telegram:  ['fab fa-telegram','#2ca5e0'],
    spotify:   ['fab fa-spotify','#1db954'],
    snapchat:  ['fab fa-snapchat','#f7b731'],
    linkedin:  ['fab fa-linkedin','#0a66c2'],
    twitch:    ['fab fa-twitch','#9146ff'],
    google:    ['fab fa-google','#ea4335'],
    whatsapp:  ['fab fa-whatsapp','#25d366'],
  };
  function getPlatformMeta(cat) {
    const k = cat.toLowerCase();
    for (const [name, meta] of Object.entries(platformMeta)) {
      if (k.includes(name)) return meta;
    }
    return ['fas fa-share-nodes','#1a8cff'];
  }

  // ── Load services ─────────────────────────────────────────────────────────
  async function loadServices() {
    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/user/get_smm_services.php');
      const data = await res.json();
      allServices = data;

      // Build category tabs with counts
      const tabs = document.getElementById('catTabs');
      const cats = Object.keys(data);
      cats.forEach(cat => {
        const [icon] = getPlatformMeta(cat);
        const count  = (data[cat] || []).length;
        const tab    = document.createElement('div');
        tab.className    = 'cat-tab';
        tab.dataset.cat  = cat;
        tab.onclick      = () => filterCategory(cat, tab);
        tab.innerHTML    = `<i class="${icon}"></i> ${cat} <span class="tab-count">${count}</span>`;
        tabs.appendChild(tab);
      });
      // Update "All" count
      const total = Object.values(data).reduce((s, arr) => s + arr.length, 0);
      document.querySelector('.cat-tab[data-cat="all"]').innerHTML =
        `<i class="fas fa-th-large"></i> All <span class="tab-count">${total}</span>`;

      renderServices('all');
      document.getElementById('svcLoading').style.display = 'none';
      document.getElementById('svcList').style.display = 'flex';
    } catch(e) {
      document.getElementById('svcLoading').innerHTML = '<span style="color:#e74c3c"><i class="fas fa-circle-exclamation"></i> Failed to load. <a href="#" onclick="loadServices()" style="color:#1a8cff">Retry</a></span>';
    }
  }

  // ── Render service rows ───────────────────────────────────────────────────
  function renderServices(cat, query='') {
    const list = document.getElementById('svcList');
    list.innerHTML = '';
    const q = query.trim().toLowerCase();
    const cats = cat === 'all' ? Object.keys(allServices) : [cat];
    let count = 0;

    cats.forEach(c => {
      const [icon, color] = getPlatformMeta(c);
      const bgColor = color + '18';  // transparent version
      (allServices[c] || []).forEach(svc => {
        const nameMatch = svc.name.toLowerCase().includes(q);
        const catMatch  = c.toLowerCase().includes(q);
        if (q && !nameMatch && !catMatch) return;
        count++;

        const row = document.createElement('div');
        row.className = 'svc-row';
        row.id = 'srow' + svc.service;
        row.onclick = () => selectService(svc, c);
        row.innerHTML = `
          <div class="svc-row-icon" style="background:${bgColor};color:${color}">
            <i class="${icon}"></i>
          </div>
          <div class="svc-row-body">
            <div class="svc-name">${svc.name}</div>
            <div class="svc-meta">
              <span class="svc-badge badge-min">Min ${svc.min.toLocaleString()}</span>
              <span class="svc-badge badge-max">Max ${svc.max.toLocaleString()}</span>
              ${svc.refill ? '<span class="svc-badge badge-refill"><i class="fas fa-rotate-right"></i> Refill</span>' : ''}
            </div>
          </div>
          <div class="svc-row-price">
            ₦${svc.rate_ngn.toLocaleString()}
            <small>per 1,000</small>
          </div>`;
        list.appendChild(row);
      });
    });

    // Update result count
    const countEl = document.getElementById('svcResultCount');
    if (countEl) countEl.textContent = q || cat !== 'all' ? `${count} result${count !== 1 ? 's' : ''}` : '';

    if (!count) {
      list.innerHTML = `<div style="text-align:center;padding:30px;color:#6b8ba4">
        <i class="fas fa-magnifying-glass" style="font-size:1.5rem;opacity:.3;display:block;margin-bottom:10px"></i>
        No services match "<strong>${query || cat}</strong>"
      </div>`;
    }
  }

  function filterCategory(cat, el) {
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    activeCategory = cat;
    // Clear search when switching tabs
    const search = document.getElementById('svcSearch');
    search.value = '';
    renderServices(cat);
    // Scroll service list to top
    document.getElementById('svcList').scrollTop = 0;
  }

  function filterSearch(q) {
    // When searching, switch to All tab
    if (q) {
      document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
      document.querySelector('.cat-tab[data-cat="all"]').classList.add('active');
      activeCategory = 'all';
    }
    renderServices(activeCategory, q);
  }

  // ── Select service → populate form ───────────────────────────────────────
  function selectService(svc, cat) {
    document.querySelectorAll('.svc-row').forEach(r => r.classList.remove('selected'));
    document.getElementById('srow' + svc.service)?.classList.add('selected');
    selectedSvc = { ...svc, category: cat };

    // Show form, hide empty state
    document.getElementById('formEmptyState').style.display  = 'none';
    document.getElementById('orderFormInner').style.display  = 'flex';
    // On mobile, scroll the form card into view
    if (window.innerWidth <= 700) {
      document.getElementById('orderFormCard').scrollIntoView({ behavior:'smooth', block:'start' });
    }
    document.getElementById('selectedSvcName').textContent = svc.name;
    document.getElementById('selectedSvcRange').textContent = `Min: ${svc.min.toLocaleString()} · Max: ${svc.max.toLocaleString()} · ₦${svc.rate_ngn.toLocaleString()} per 1,000`;

    // Preset qty buttons
    const presets = document.getElementById('qtyPresets');
    presets.innerHTML = '';
    const vals = generatePresets(svc.min, svc.max);
    vals.forEach(v => {
      const b = document.createElement('button');
      b.className = 'qty-btn';
      b.textContent = v.toLocaleString();
      b.type = 'button';
      b.onclick = () => {
        document.getElementById('boostQty').value = v;
        presets.querySelectorAll('.qty-btn').forEach(x => x.classList.remove('active'));
        b.classList.add('active');
        recalcCost();
      };
      presets.appendChild(b);
    });

    // Set default qty to min
    document.getElementById('boostQty').value = svc.min;
    document.getElementById('boostLink').value = '';
    document.getElementById('boostAlert').style.display = 'none';
    recalcCost();
  }

  function generatePresets(min, max) {
    const steps = [min, Math.round(max * 0.1), Math.round(max * 0.25), Math.round(max * 0.5), max];
    return [...new Set(steps.filter(v => v >= min && v <= max))].slice(0, 5);
  }

  // ── Recalculate cost live ─────────────────────────────────────────────────
  function recalcCost() {
    if (!selectedSvc) return;
    const qty = parseInt(document.getElementById('boostQty').value) || 0;
    if (qty < selectedSvc.min || qty > selectedSvc.max) {
      document.getElementById('costDisplay').style.display = 'none';
      document.getElementById('placeBtn').disabled = true;
      document.getElementById('placeBtnText').textContent = `Quantity must be ${selectedSvc.min.toLocaleString()} – ${selectedSvc.max.toLocaleString()}`;
      return;
    }
    const cost = Math.ceil((selectedSvc.rate_ngn / 1000) * qty);
    document.getElementById('costValue').textContent   = '₦' + cost.toLocaleString();
    document.getElementById('costPerK').textContent    = `₦${selectedSvc.rate_ngn.toLocaleString()} per 1,000`;
    document.getElementById('costDisplay').style.display = 'flex';
    document.getElementById('placeBtn').disabled       = false;
    document.getElementById('placeBtnText').textContent = `Place Order — ₦${cost.toLocaleString()}`;
  }

  // ── Place order ───────────────────────────────────────────────────────────
  async function placeOrder() {
    if (!selectedSvc) return;
    const link = document.getElementById('boostLink').value.trim();
    const qty  = parseInt(document.getElementById('boostQty').value) || 0;
    const cost = Math.ceil((selectedSvc.rate_ngn / 1000) * qty);

    if (!link) {
      showAlert('Please enter the profile / post URL.'); return;
    }
    if (qty < selectedSvc.min || qty > selectedSvc.max) {
      showAlert(`Quantity must be between ${selectedSvc.min.toLocaleString()} and ${selectedSvc.max.toLocaleString()}.`); return;
    }

    const btn = document.getElementById('placeBtn');
    const orig = document.getElementById('placeBtnText').textContent;
    btn.disabled = true;
    document.getElementById('placeBtnText').textContent = 'Placing order...';
    document.getElementById('boostAlert').style.display = 'none';

    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/user/place_boost.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
          service_id:   selectedSvc.service,
          service_name: selectedSvc.name,
          category:     selectedSvc.category,
          link, quantity: qty, cost,
        })
      });
      const data = await res.json();

      if (data.success) {
        document.getElementById('balDisplay').innerHTML = `<i class="fas fa-wallet"></i> ${data.new_balance}`;
        document.getElementById('successDesc').textContent =
          `${selectedSvc.name} × ${qty.toLocaleString()} for ${link}`;
        document.getElementById('successModal').classList.add('active');
        // Reset form
        document.getElementById('boostLink').value = '';
        document.getElementById('boostQty').value  = selectedSvc.min;
        recalcCost();
        // Prepend order card
        prependOrderCard(data, qty, link, cost);
      } else {
        showAlert(data.message || 'Order failed.');
        btn.disabled = false;
        document.getElementById('placeBtnText').textContent = orig;
      }
    } catch(e) {
      showAlert('Connection error. Please try again.');
      btn.disabled = false;
      document.getElementById('placeBtnText').textContent = orig;
    }
  }

  function showAlert(msg) {
    const el = document.getElementById('boostAlert');
    el.className = 'alert alert-error';
    el.innerHTML = `<i class="fas fa-circle-exclamation"></i> ${msg}`;
    el.style.display = 'flex';
  }

  // ── Prepend new order card ────────────────────────────────────────────────
  function prependOrderCard(data, qty, link, cost) {
    const list = document.getElementById('ordersList');
    const empty = list.querySelector('.empty-orders');
    if (empty) empty.remove();

    const id   = data.boost_id;
    const card = document.createElement('div');
    card.className = 'order-card';
    card.id = 'ocard' + id;
    card.innerHTML = `
      <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:8px;flex-wrap:wrap">
        <div style="flex:1">
          <div style="font-weight:700;font-size:.9rem;color:#0d1b2a">${selectedSvc.name}</div>
          <div style="font-size:.76rem;color:#6b8ba4;margin-top:2px;word-break:break-all">${link}</div>
        </div>
        <span class="status-pill sp-pending" id="ost${id}">Pending</span>
      </div>
      <div class="order-progress"><div class="order-fill" id="ofill${id}" style="width:0%"></div></div>
      <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#6b8ba4">
        <span id="oprog${id}">${qty.toLocaleString()} ordered</span>
        <span style="font-family:'Outfit',sans-serif;font-weight:700;color:#1a8cff">₦${cost.toLocaleString()}</span>
      </div>
      <div style="font-size:.74rem;color:#b3c9d6;margin-top:4px">Just now</div>`;
    list.prepend(card);
  }

  // ── Refresh all pending orders ────────────────────────────────────────────
  async function refreshOrders() {
    const cards = document.querySelectorAll('.order-card');
    for (const card of cards) {
      const id = card.id.replace('ocard','');
      const statusEl = document.getElementById('ost' + id);
      if (!statusEl) continue;
      const status = statusEl.textContent.toLowerCase().replace(' ','_');
      if (['completed','cancelled','failed'].includes(status)) continue;

      try {
        const res  = await fetch(`/NBALOGMARKETPLACE/backend/user/check_boost.php?boost_id=${id}`);
        const data = await res.json();
        if (data.success) updateOrderCard(id, data);
      } catch(e) { /* ignore */ }
    }
    showToast('Orders refreshed.', 'success');
  }

  function updateOrderCard(id, data) {
    const statusEl = document.getElementById('ost' + id);
    const fillEl   = document.getElementById('ofill' + id);
    const progEl   = document.getElementById('oprog' + id);
    if (!statusEl) return;

    const labelMap = { pending:'Pending', in_progress:'In Progress', completed:'Completed', partial:'Partial', cancelled:'Cancelled', failed:'Failed' };
    statusEl.className = `status-pill sp-${data.status}`;
    statusEl.textContent = labelMap[data.status] || data.status;

    if (fillEl && data.remains !== undefined && data.start_count !== undefined) {
      const qty       = data.start_count + data.remains;
      const delivered = qty - data.remains;
      const pct       = qty > 0 ? Math.min(100, Math.round((delivered / qty) * 10)) : 0;
      fillEl.style.width = pct + '%';
      if (progEl) progEl.textContent = `${qty.toLocaleString()} ordered · ${data.remains.toLocaleString()} remaining`;
    }
  }

  // ── Init ──────────────────────────────────────────────────────────────────
  document.addEventListener('DOMContentLoaded', loadServices);
  document.getElementById('successModal').addEventListener('click', function(e){ if(e.target===this) closeModal(); });
</script>
</body>
</html>