<?php
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';
require_once __DIR__ . '/../../../backend/includes/sms_activate.php';
$activePage = 'virtual-numbers';

// Active/recent numbers for this user
$myNumbers = $pdo->prepare(
    "SELECT * FROM virtual_numbers WHERE user_id = ? ORDER BY created_at DESC LIMIT 10"
);
$myNumbers->execute([$currentUser['id']]);
$myNumbers = $myNumbers->fetchAll();

$countries = SmsActivate::popularCountries();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Virtual Numbers — VirtualHub Pro</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .vn-layout { display: grid; grid-template-columns: 1fr 1.1fr; gap: 22px; }
    @media(max-width:900px){ .vn-layout { grid-template-columns: 1fr; } }

    /* Service grid */
    .service-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px,1fr)); gap: 10px; margin-bottom: 18px; }
    .service-search-wrap { position:relative; margin-bottom:12px; }
    .service-search { width:100%; padding:10px 14px 10px 38px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.9rem; font-family:inherit; outline:none; box-sizing:border-box; }
    .service-search:focus { border-color:#1a8cff; }
    .service-search-icon { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#b3d9ff; font-size:.9rem; pointer-events:none; }
    .no-match { grid-column:1/-1; text-align:center; padding:20px; color:#6b8ba4; font-size:.88rem; }
    .service-tile {
      border: 2px solid #e8f4ff; border-radius: 13px; padding: 14px 8px;
      text-align: center; cursor: pointer; transition: all 0.2s; background: white;
    }
    .service-tile:hover { border-color: #1a8cff; transform: translateY(-2px); box-shadow: 0 4px 14px rgba(26,140,255,0.12); }
    .service-tile.selected { border-color: #1a8cff; background: #e8f4ff; }
    .service-tile i { font-size: 1.5rem; display: block; margin-bottom: 6px; }
    .service-tile span { font-size: 0.76rem; font-weight: 700; color: #3a5068; }

    /* Country selector */
    .country-select { width:100%; padding:11px 14px; border:1.5px solid #d6ecff; border-radius:10px; font-size:.9rem; font-family:inherit; outline:none; }
    .country-select:focus { border-color:#1a8cff; }

    /* Price display */
    .price-display {
      background: linear-gradient(135deg, #e8f4ff, #f0f9ff);
      border: 1.5px solid #b3d9ff; border-radius: 14px;
      padding: 18px 20px; margin: 16px 0; display: flex; align-items: center; justify-content: space-between;
    }
    .price-big { font-family:'Outfit',sans-serif; font-size:1.6rem; font-weight:800; color:#1a8cff; }
    .price-note { font-size:.8rem; color:#6b8ba4; }
    .avail-count { font-size:.82rem; color:#0a8c6a; font-weight:700; }

    /* Buy button */
    .buy-number-btn {
      width:100%; padding:14px; background:linear-gradient(135deg,#1a8cff,#0057b8);
      color:white; border:none; border-radius:12px; font-family:'Outfit',sans-serif;
      font-weight:700; font-size:1rem; cursor:pointer; transition:all .2s;
      display:flex; align-items:center; justify-content:center; gap:10px;
    }
    .buy-number-btn:hover:not(:disabled){ transform:translateY(-2px); box-shadow:0 6px 20px rgba(26,140,255,.35); }
    .buy-number-btn:disabled{ opacity:.6; cursor:not-allowed; transform:none; }

    /* Active number card */
    .number-card {
      border: 2px solid #d6ecff; border-radius: 16px; padding: 20px;
      background: white; transition: all .2s;
    }
    .number-card.active-card { border-color: #1a8cff; background: #f5fbff; }
    .number-card.received-card { border-color: #0a8c6a; background: #f0faf7; }
    .number-card.expired-card, .number-card.cancelled-card { opacity:.65; }

    .phone-display {
      font-family: 'Courier New', monospace; font-size: 1.3rem; font-weight: 700;
      color: #0d1b2a; letter-spacing: 2px; margin: 10px 0;
      display: flex; align-items: center; gap: 10px;
    }
    .copy-phone-btn {
      padding:5px 12px; background:#e8f4ff; color:#1a8cff; border:1.5px solid #b3d9ff;
      border-radius:7px; font-size:.78rem; font-weight:700; cursor:pointer; transition:all .2s;
    }
    .copy-phone-btn:hover{ background:#d6ecff; }

    /* Countdown */
    .countdown-bar { margin: 12px 0; }
    .countdown-track { height: 6px; background: #e8f4ff; border-radius: 100px; overflow:hidden; }
    .countdown-fill { height:100%; border-radius:100px; transition: width 1s linear, background .5s; background:#1a8cff; }
    .countdown-text { font-size:.82rem; color:#6b8ba4; margin-top:5px; display:flex; justify-content:space-between; }
    .countdown-text.urgent { color:#e74c3c; }

    /* SMS code display */
    .sms-box {
      background: linear-gradient(135deg,#e6faf5,#f0fdf8);
      border: 2px solid #0a8c6a; border-radius: 12px;
      padding: 16px; margin-top: 12px; text-align:center;
    }
    .sms-code { font-family:'Courier New',monospace; font-size:2rem; font-weight:800; color:#0a8c6a; letter-spacing:4px; }
    .sms-label { font-size:.78rem; color:#6b8ba4; margin-bottom:4px; }

    /* Status badges */
    .status-pill { padding:4px 12px; border-radius:100px; font-size:.74rem; font-weight:700; }
    .sp-waiting   { background:#fff3e8; color:#e67e22; }
    .sp-received  { background:#e6faf5; color:#0a8c6a; }
    .sp-cancelled { background:#f0f0f0; color:#6b8ba4; }
    .sp-expired   { background:#fff0f0; color:#c0392b; }

    .cancel-btn { padding:8px 16px; background:#fff0f0; color:#c0392b; border:1.5px solid #f5c6c6; border-radius:8px; font-weight:700; font-size:.82rem; cursor:pointer; transition:all .2s; }
    .cancel-btn:hover { background:#ffe0e0; }

    .empty-numbers { text-align:center; padding:40px 20px; color:#6b8ba4; }
    .empty-numbers i { font-size:2.5rem; opacity:.2; display:block; margin-bottom:12px; }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-sim-card" style="color:#0984e3"></i> Virtual Numbers</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance" id="balDisplay"><i class="fas fa-wallet"></i> <?= formatMoney((float)$currentUser['balance']) ?></div>
        <div class="topbar-user"><div class="topbar-avatar"><?= userInitials($currentUser) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentUser['first_name']) ?></span></div>
      </div>
    </div>

    <div class="dash-content">
      <div class="vn-layout">

        <!-- LEFT: Order Panel -->
        <div>
          <div class="section-card" style="margin-bottom:18px">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-bolt"></i> Get a Number</div>
            </div>
            <div class="section-card-body">
              <div style="font-size:.85rem;color:#3a5068;margin-bottom:14px">
                <i class="fas fa-circle-info" style="color:#1a8cff"></i>
                Select a service and country, then buy a temporary number to receive one SMS.
                <strong>Valid for 10 minutes. Auto-refund if no SMS is received.</strong>
              </div>

              <!-- Step 1: Service -->
              <div style="font-size:.82rem;font-weight:700;color:#3a5068;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
                1. Choose Service
              </div>
              <div class="service-search-wrap" id="searchWrap" style="display:none">
                <i class="fas fa-search service-search-icon"></i>
                <input type="text" class="service-search" id="serviceSearch" placeholder="Search service e.g. WhatsApp, Telegram..." oninput="filterServices(this.value)"/>
              </div>
              <div id="servicesLoading" style="text-align:center;padding:20px;color:#6b8ba4">
                <span class="spinner"></span> Loading available services...
              </div>
              <div class="service-grid" id="serviceGrid" style="display:none"></div>

              <!-- Step 2: Country -->
              <div style="font-size:.82rem;font-weight:700;color:#3a5068;text-transform:uppercase;letter-spacing:.5px;margin-bottom:10px">
                2. Choose Country
              </div>
              <select class="country-select" id="countrySelect" onchange="loadServices()">
                <?php foreach ($countries as $c): ?>
                  <option value="<?= $c['id'] ?>" data-name="<?= htmlspecialchars($c['name']) ?>">
                    <?= htmlspecialchars($c['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>

              <!-- Price display -->
              <div class="price-display" id="priceDisplay" style="display:none">
                <div>
                  <div style="font-size:.78rem;color:#6b8ba4;margin-bottom:4px">Price for this number</div>
                  <div class="price-big" id="priceValue">₦0</div>
                  <div class="avail-count" id="availCount"></div>
                </div>
                <div style="text-align:right">
                  <div class="price-note">10-minute timer</div>
                  <div class="price-note">Auto-refund if no SMS</div>
                </div>
              </div>


              <div id="orderAlert" style="display:none" class="alert mt-2"></div>

              <button class="buy-number-btn" id="buyBtn" style="margin-top:12px" onclick="buyNumber()" disabled>
                <i class="fas fa-sim-card"></i> <span id="buyBtnText">Select a service first</span>
              </button>
            </div>
          </div>
        </div>

        <!-- RIGHT: My Numbers -->
        <div>
          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title"><i class="fas fa-clock-rotate-left"></i> My Numbers</div>
            </div>
            <div id="myNumbersList" style="padding:16px;display:flex;flex-direction:column;gap:14px">
              <?php if (empty($myNumbers)): ?>
                <div class="empty-numbers"><i class="fas fa-sim-card"></i><p>No numbers yet. Order your first one!</p></div>
              <?php else: ?>
                <?php foreach ($myNumbers as $num): ?>
                <?php
                  $statusClass = match($num['status']) {
                    'waiting'   => 'active-card',
                    'received'  => 'received-card',
                    'cancelled' => 'cancelled-card',
                    'expired'   => 'expired-card',
                    default     => '',
                  };
                  $expTs = strtotime($num['expires_at']) * 1000;
                ?>
                <div class="number-card <?= $statusClass ?>" id="numCard<?= $num['id'] ?>">
                  <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
                    <div>
                      <div style="font-weight:700;font-size:.88rem;color:#3a5068"><?= htmlspecialchars($num['service']) ?> — <?= htmlspecialchars($num['country']) ?></div>
                      <div style="font-size:.76rem;color:#6b8ba4"><?= date('M j, g:i A', strtotime($num['created_at'])) ?></div>
                    </div>
                    <span class="status-pill sp-<?= $num['status'] ?>" id="sp<?= $num['id'] ?>"><?= ucfirst($num['status']) ?></span>
                  </div>

                  <div class="phone-display">
                    <span id="ph<?= $num['id'] ?>"><?= htmlspecialchars($num['phone_number']) ?></span>
                    <button class="copy-phone-btn" onclick="copyText('<?= htmlspecialchars($num['phone_number']) ?>', this)"><i class="fas fa-copy"></i> Copy</button>
                  </div>

                  <?php if ($num['status'] === 'waiting'): ?>
                  <div class="countdown-bar">
                    <div class="countdown-track"><div class="countdown-fill" id="cf<?= $num['id'] ?>"></div></div>
                    <div class="countdown-text" id="ct<?= $num['id'] ?>">
                      <span id="ctLeft<?= $num['id'] ?>">Calculating...</span>
                      <span>Waiting for SMS...</span>
                    </div>
                  </div>
                  <div style="display:flex;gap:8px;margin-top:8px">
                    <button class="cancel-btn" id="cancelBtn<?= $num['id'] ?>" onclick="cancelNumber(<?= $num['id'] ?>)">
                      <i class="fas fa-xmark"></i> Cancel & Refund
                    </button>
                  </div>
                  <?php elseif ($num['status'] === 'received'): ?>
                  <div class="sms-box">
                    <div class="sms-label">SMS Code Received</div>
                    <div class="sms-code" id="smsCode<?= $num['id'] ?>"><?= htmlspecialchars($num['sms_code'] ?? '—') ?></div>
                    <button class="copy-phone-btn" style="margin-top:8px" onclick="copyText('<?= htmlspecialchars($num['sms_code'] ?? '') ?>',this)"><i class="fas fa-copy"></i> Copy Code</button>
                  </div>
                  <?php elseif ($num['status'] === 'expired'): ?>
                  <div style="font-size:.8rem;color:#c0392b;margin-top:8px"><i class="fas fa-circle-exclamation"></i> Expired — refund was issued automatically.</div>
                  <?php elseif ($num['status'] === 'cancelled'): ?>
                  <div style="font-size:.8rem;color:#6b8ba4;margin-top:8px"><i class="fas fa-ban"></i> Cancelled — refund was issued.</div>
                  <?php endif; ?>
                </div>
                <?php endforeach; ?>
              <?php endif; ?>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</div>
<div id="alertToast" style="position:fixed;bottom:80px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '' ?>" target="_blank" class="whatsapp-fab"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>

<script>
  // ── Sidebar ────────────────────────────────────────────────────────────────
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function showToast(msg,type='error'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='error'?'circle-exclamation':'circle-check'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',5000);}

  // ── State ──────────────────────────────────────────────────────────────────
  let selectedService = null;
  let selectedServiceName = '';
  let currentPrice = 0;
  let pollTimers  = {};
  let countTimers = {};
  let allServices = [];  // loaded from API

  // ── Load services for selected country ───────────────────────────────────
  async function loadServices() {
    const country = document.getElementById('countrySelect').value;
    const grid    = document.getElementById('serviceGrid');
    const loading = document.getElementById('servicesLoading');

    grid.style.display   = 'none';
    loading.style.display = 'block';

    // Reset selection
    selectedService = null;
    selectedServiceName = '';
    document.getElementById('priceDisplay').style.display = 'none';
    document.getElementById('buyBtn').disabled = true;
    document.getElementById('buyBtnText').textContent = 'Select a service first';

    try {
      const res  = await fetch(`/NBALOGMARKETPLACE/backend/user/get_services.php?country=${country}`);
      const services = await res.json();
      allServices = Array.isArray(services) ? services : [];

      grid.innerHTML = '';
      if (!allServices.length) {
        grid.innerHTML = '<div style="grid-column:1/-1;text-align:center;color:#6b8ba4;padding:16px"><i class="fas fa-circle-exclamation"></i> No services available for this country. Try another.</div>';
      } else {
        allServices.forEach(svc => {
          const tile = document.createElement('div');
          tile.className = 'service-tile';
          tile.id = 'svc_' + svc.code;
          tile.title = svc.name + ' — ₦' + svc.cost.toLocaleString();
          tile.onclick = () => selectService(svc.code, svc.name, svc.color, svc.cost);
          tile.innerHTML = `<i class="${svc.icon}" style="color:${svc.color}"></i><span>${svc.name}</span><div style="font-size:.68rem;font-weight:700;color:#1a8cff;margin-top:3px">₦${svc.cost.toLocaleString()}</div>`;
          grid.appendChild(tile);
        });
      }

      loading.style.display = 'none';
      grid.style.display    = 'grid';
      document.getElementById('searchWrap').style.display = allServices.length ? 'block' : 'none';
      document.getElementById('serviceSearch').value = '';
    } catch(e) {
      loading.innerHTML = '<div style="color:#e74c3c"><i class="fas fa-circle-exclamation"></i> Failed to load services. <a href="#" onclick="loadServices()" style="color:#1a8cff">Retry</a></div>';
    }
  }

  // ── Filter services by search ─────────────────────────────────────────────
  function filterServices(query) {
    const q    = query.trim().toLowerCase();
    const grid = document.getElementById('serviceGrid');
    let visible = 0;

    grid.querySelectorAll('.service-tile').forEach(tile => {
      const name = tile.querySelector('span').textContent.toLowerCase();
      const match = !q || name.includes(q);
      tile.style.display = match ? '' : 'none';
      if (match) visible++;
    });

    // Show/hide no-match message
    let noMatch = grid.querySelector('.no-match');
    if (!visible) {
      if (!noMatch) {
        noMatch = document.createElement('div');
        noMatch.className = 'no-match';
        noMatch.innerHTML = `<i class="fas fa-magnifying-glass"></i> No services match "<strong>${query}</strong>"`;
        grid.appendChild(noMatch);
      } else {
        noMatch.innerHTML = `<i class="fas fa-magnifying-glass"></i> No services match "<strong>${query}</strong>"`;
        noMatch.style.display = '';
      }
    } else if (noMatch) {
      noMatch.style.display = 'none';
    }
  }

  // ── Service selection (price already in tile data) ────────────────────────
  function selectService(code, name, color, priceNgn) {
    document.querySelectorAll('.service-tile').forEach(el => el.classList.remove('selected'));
    document.getElementById('svc_' + code)?.classList.add('selected');
    selectedService     = code;
    selectedServiceName = name;
    currentPrice        = priceNgn;

    document.getElementById('priceValue').textContent  = '₦' + priceNgn.toLocaleString();
    const svc = allServices.find(s => s.code === code);
    document.getElementById('availCount').textContent  = svc ? `${svc.count} numbers available` : '';
    document.getElementById('priceDisplay').style.display = 'flex';
    document.getElementById('buyBtn').disabled          = false;
    document.getElementById('buyBtnText').textContent   = `Buy Number — ₦${priceNgn.toLocaleString()}`;
    document.getElementById('orderAlert').style.display = 'none';
  }

  // ── Buy number ────────────────────────────────────────────────────────────
  async function buyNumber() {
    if (!selectedService || currentPrice <= 0) return;
    const countryEl  = document.getElementById('countrySelect');
    const countryId  = parseInt(countryEl.value);
    const countryName= countryEl.options[countryEl.selectedIndex].dataset.name;
    const btn        = document.getElementById('buyBtn');
    const origText   = document.getElementById('buyBtnText').textContent;

    btn.disabled = true;
    document.getElementById('buyBtnText').textContent = 'Purchasing...';
    document.getElementById('orderAlert').style.display = 'none';

    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/user/buy_number.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
          service_code: selectedService,
          service_name: selectedServiceName,
          country_id:   countryId,
          country_name: countryName,
          price:        currentPrice,
        })
      });
      const data = await res.json();

      if (data.success) {
        document.getElementById('balDisplay').innerHTML = `<i class="fas fa-wallet"></i> ${data.new_balance}`;
        showToast('Number assigned! Check "My Numbers" panel.', 'success');
        // Prepend new number card
        prependNumberCard(data);
      } else {
        const alert = document.getElementById('orderAlert');
        alert.className = 'alert alert-error';
        alert.innerHTML = `<i class="fas fa-circle-exclamation"></i> ${data.message}`;
        alert.style.display = 'flex';
        btn.disabled = false;
        document.getElementById('buyBtnText').textContent = origText;
      }
    } catch(e) {
      const alert = document.getElementById('orderAlert');
      alert.className = 'alert alert-error';
      alert.innerHTML = '<i class="fas fa-circle-exclamation"></i> Connection error. Please try again.';
      alert.style.display = 'flex';
      btn.disabled = false;
      document.getElementById('buyBtnText').textContent = origText;
    }
  }

  // ── Prepend new number card to list ──────────────────────────────────────
  function prependNumberCard(data) {
    const list = document.getElementById('myNumbersList');
    // Remove empty state if present
    const empty = list.querySelector('.empty-numbers');
    if (empty) empty.remove();

    const id = data.number_id;
    const card = document.createElement('div');
    card.className = 'number-card active-card';
    card.id = 'numCard' + id;
    card.innerHTML = `
      <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px">
        <div>
          <div style="font-weight:700;font-size:.88rem;color:#3a5068">${data.service} — ${data.country}</div>
          <div style="font-size:.76rem;color:#6b8ba4">Just now</div>
        </div>
        <span class="status-pill sp-waiting" id="sp${id}">Waiting</span>
      </div>
      <div class="phone-display">
        <span id="ph${id}">${data.phone_number}</span>
        <button class="copy-phone-btn" onclick="copyText('${data.phone_number}',this)"><i class="fas fa-copy"></i> Copy</button>
      </div>
      <div class="countdown-bar">
        <div class="countdown-track"><div class="countdown-fill" id="cf${id}" style="width:100%"></div></div>
        <div class="countdown-text" id="ct${id}">
          <span id="ctLeft${id}">10:00</span>
          <span>Waiting for SMS...</span>
        </div>
      </div>
      <div style="display:flex;gap:8px;margin-top:8px">
        <button class="cancel-btn" id="cancelBtn${id}" onclick="cancelNumber(${id})">
          <i class="fas fa-xmark"></i> Cancel & Refund
        </button>
      </div>
    `;
    list.prepend(card);
    startCountdown(id, data.expires_ts);
    startPolling(id);
  }

  // ── Countdown timer ──────────────────────────────────────────────────────
  function startCountdown(id, expiresTs) {
    const totalMs = 10 * 60 * 1000;
    function tick() {
      const remaining = expiresTs - Date.now();
      const fill = document.getElementById('cf' + id);
      const leftEl = document.getElementById('ctLeft' + id);
      const ctEl = document.getElementById('ct' + id);
      if (!fill || !leftEl) { clearInterval(countTimers[id]); return; }
      if (remaining <= 0) {
        fill.style.width = '0%';
        fill.style.background = '#e74c3c';
        leftEl.textContent = '0:00';
        clearInterval(countTimers[id]);
        return;
      }
      const pct = (remaining / totalMs) * 100;
      fill.style.width = pct + '%';
      fill.style.background = pct > 40 ? '#1a8cff' : pct > 20 ? '#e67e22' : '#e74c3c';
      const min = Math.floor(remaining / 60000);
      const sec = Math.floor((remaining % 60000) / 1000);
      leftEl.textContent = `${min}:${sec.toString().padStart(2,'0')} remaining`;
      if (pct < 20) ctEl.classList.add('urgent');
    }
    tick();
    countTimers[id] = setInterval(tick, 1000);
  }

  // ── Poll for SMS ──────────────────────────────────────────────────────────
  function startPolling(id) {
    pollTimers[id] = setInterval(() => pollSms(id), 5000);
  }
  async function pollSms(id) {
    try {
      const res  = await fetch('//backend/user/check_sms.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ number_id: id })
      });
      const data = await res.json();
      if (!data.success) return;

      if (data.status === 'received') {
        clearInterval(pollTimers[id]); clearInterval(countTimers[id]);
        onSmsReceived(id, data.sms_code);
      } else if (data.status === 'expired') {
        clearInterval(pollTimers[id]); clearInterval(countTimers[id]);
        onExpired(id);
      } else if (data.status === 'cancelled') {
        clearInterval(pollTimers[id]); clearInterval(countTimers[id]);
        onCancelled(id);
      }
    } catch(e) { /* ignore polling errors */ }
  }

  function onSmsReceived(id, code) {
    const card = document.getElementById('numCard' + id);
    if (!card) return;
    card.className = 'number-card received-card';
    document.getElementById('sp' + id).className = 'status-pill sp-received'; document.getElementById('sp' + id).textContent = 'Received';
    const cbar = document.getElementById('cf' + id)?.closest('.countdown-bar');
    if (cbar) cbar.remove();
    const cancelBtn = document.getElementById('cancelBtn' + id);
    if (cancelBtn) cancelBtn.closest('div[style]')?.remove();
    // Add SMS box
    const smsBox = document.createElement('div');
    smsBox.className = 'sms-box';
    smsBox.innerHTML = `<div class="sms-label">SMS Code Received 🎉</div><div class="sms-code" id="smsCode${id}">${code}</div><button class="copy-phone-btn" style="margin-top:8px" onclick="copyText('${code}',this)"><i class="fas fa-copy"></i> Copy Code</button>`;
    card.appendChild(smsBox);
    showToast('SMS received! Your code is ' + code, 'success');
  }
  function onExpired(id) {
    const card = document.getElementById('numCard' + id);
    if (!card) return;
    card.className = 'number-card expired-card';
    document.getElementById('sp' + id).className = 'status-pill sp-expired'; document.getElementById('sp' + id).textContent = 'Expired';
    const cbar = document.getElementById('cf' + id)?.closest('.countdown-bar');
    if (cbar) cbar.remove();
    const cancelBtn = document.getElementById('cancelBtn' + id);
    if (cancelBtn) cancelBtn.closest('div[style]')?.remove();
    const msg = document.createElement('div');
    msg.style = 'font-size:.8rem;color:#c0392b;margin-top:8px';
    msg.innerHTML = '<i class="fas fa-circle-exclamation"></i> Expired — refund was issued to your wallet.';
    card.appendChild(msg);
    showToast('Number expired. Refund has been issued to your wallet.');
  }
  function onCancelled(id) {
    const card = document.getElementById('numCard' + id);
    if (!card) return;
    card.className = 'number-card cancelled-card';
    document.getElementById('sp' + id).className = 'status-pill sp-cancelled'; document.getElementById('sp' + id).textContent = 'Cancelled';
  }

  // ── Cancel number ─────────────────────────────────────────────────────────
  async function cancelNumber(id) {
    if (!confirm('Cancel this number and get a refund?')) return;
    const btn = document.getElementById('cancelBtn' + id);
    if (btn) { btn.disabled = true; btn.innerHTML = '<span class="spinner"></span>'; }
    clearInterval(pollTimers[id]); clearInterval(countTimers[id]);
    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/user/cancel_number.php', {
        method:'POST', headers:{'Content-Type':'application/json'},
        body: JSON.stringify({ number_id: id })
      });
      const data = await res.json();
      if (data.success) {
        document.getElementById('balDisplay').innerHTML = `<i class="fas fa-wallet"></i> ${data.new_balance}`;
        onCancelled(id);
        showToast('Cancelled. Refund issued to your wallet.', 'success');
      } else {
        showToast(data.message || 'Cancel failed.');
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-xmark"></i> Cancel & Refund'; }
      }
    } catch(e) {
      showToast('Connection error.');
      if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fas fa-xmark"></i> Cancel & Refund'; }
    }
  }

  // ── Copy helper ───────────────────────────────────────────────────────────
  function copyText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
      const orig = btn.innerHTML;
      btn.innerHTML = '<i class="fas fa-check"></i> Copied!';
      setTimeout(() => btn.innerHTML = orig, 2000);
    });
  }

  // ── Auto-start timers for waiting numbers loaded from DB ─────────────────
  document.addEventListener('DOMContentLoaded', () => {
    loadServices();  // load services for default country on page load
    <?php foreach ($myNumbers as $num): ?>
      <?php if ($num['status'] === 'waiting'): ?>
        startCountdown(<?= $num['id'] ?>, <?= strtotime($num['expires_at']) * 1000 ?>);
        startPolling(<?= $num['id'] ?>);
      <?php endif; ?>
    <?php endforeach; ?>
  });
</script>
</body>
</html>