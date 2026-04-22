<?php
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';
$activePage = 'social_logins';

// Fetch available (unsold) logins grouped by platform
$stmt = $pdo->prepare(
    'SELECT * FROM social_logins WHERE is_sold=0 ORDER BY platform ASC, created_at DESC'
);
$stmt->execute();
$allLogins = $stmt->fetchAll();

// Group by platform
$grouped = [];
foreach ($allLogins as $login) {
    $grouped[$login['platform']][] = $login;
}

// Platform colors and icons
$platformMeta = [
    'Facebook'  => ['#1877f2','#e7f0fd','fab fa-facebook-f'],
    'Instagram' => ['#e1306c','#fde8ef','fab fa-instagram'],
    'TikTok'    => ['#010101','#f0f0f0','fab fa-tiktok'],
    'Twitter'   => ['#1da1f2','#e8f5fe','fab fa-twitter'],
    'Snapchat'  => ['#fffc00','#fffde7','fab fa-snapchat'],
    'YouTube'   => ['#ff0000','#ffe8e8','fab fa-youtube'],
    'LinkedIn'  => ['#0a66c2','#e8f1fb','fab fa-linkedin-in'],
    'WhatsApp'  => ['#25d366','#e8faf0','fab fa-whatsapp'],
];
$defaultMeta = ['#6b8ba4','#f0f7ff','fas fa-globe'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Social Logins — VirtualHub Pro</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .platform-section { margin-bottom: 32px; }
    .platform-header {
      display: flex; align-items: center; gap: 12px;
      margin-bottom: 16px; padding-bottom: 12px;
      border-bottom: 2px solid #f0f7ff;
    }
    .platform-icon {
      width: 40px; height: 40px; border-radius: 11px;
      display: flex; align-items: center; justify-content: center;
      font-size: 1.1rem; flex-shrink: 0;
    }
    .platform-name {
      font-family: 'Outfit', sans-serif;
      font-size: 1.1rem; font-weight: 800; color: #0d1b2a;
    }
    .platform-count {
      background: #e8f4ff; color: #1a8cff;
      padding: 3px 10px; border-radius: 100px;
      font-size: 0.78rem; font-weight: 700;
    }
    .logins-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(260px,1fr)); gap: 16px; }
    .login-card {
      background: white; border: 1.5px solid #d6ecff;
      border-radius: 14px; padding: 18px;
      transition: all 0.22s; display: flex; flex-direction: column; gap: 10px;
    }
    .login-card:hover { box-shadow: 0 6px 22px rgba(26,140,255,0.12); transform: translateY(-2px); border-color: #1a8cff; }
    .login-title { font-weight: 700; font-size: 0.95rem; color: #0d1b2a; }
    .login-desc { font-size: 0.82rem; color: #6b8ba4; line-height: 1.5; flex: 1; }
    .login-footer { display: flex; align-items: center; justify-content: space-between; padding-top: 10px; border-top: 1px solid #f0f7ff; margin-top: auto; }
    .item-price { font-family: 'Outfit', sans-serif; font-size: 1.05rem; font-weight: 800; color: #1a8cff; }
    .buy-btn { padding: 8px 18px; background: linear-gradient(135deg,#1a8cff,#0057b8); color: white; border: none; border-radius: 8px; font-family: 'Outfit',sans-serif; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; }
    .buy-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(26,140,255,0.35); }
    .buy-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
    /* Modal */
    .modal-overlay { position: fixed; inset: 0; background: rgba(13,27,42,0.6); backdrop-filter: blur(6px); z-index: 9000; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: all 0.28s; }
    .modal-overlay.active { opacity: 1; visibility: visible; }
    .modal-box { background: white; border-radius: 22px; padding: 36px; width: 90%; max-width: 500px; transform: scale(0.93) translateY(20px); transition: all 0.28s; max-height: 85vh; overflow-y: auto; }
    .modal-overlay.active .modal-box { transform: scale(1) translateY(0); }
    .modal-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; }
    .modal-title { font-family: 'Outfit',sans-serif; font-size: 1.2rem; font-weight: 800; color: #0d1b2a; }
    .modal-close { width: 34px; height: 34px; border-radius: 50%; border: none; cursor: pointer; background: #f0f7ff; color: #6b8ba4; font-size: 1rem; display: flex; align-items: center; justify-content: center; }
    .modal-close:hover { background: #e8f4ff; color: #1a8cff; }
    .credentials-box {
      background: #f5fbff; border: 1.5px solid #d6ecff;
      border-radius: 12px; padding: 18px; margin-bottom: 16px;
      font-family: monospace; font-size: 0.9rem;
      color: #0d1b2a; line-height: 1.8; word-break: break-all;
      white-space: pre-wrap;
    }
    .copy-all-btn {
      width: 100%; padding: 13px; background: linear-gradient(135deg,#1a8cff,#0057b8);
      color: white; border: none; border-radius: 10px;
      font-family: 'Outfit',sans-serif; font-weight: 700; font-size: 0.95rem;
      cursor: pointer; transition: all 0.2s; display: flex; align-items: center; justify-content: center; gap: 8px;
    }
    .copy-all-btn:hover { transform: translateY(-1px); box-shadow: 0 4px 14px rgba(26,140,255,0.35); }
    .copy-all-btn.copied { background: linear-gradient(135deg,#0a8c6a,#057a5a); }
    .empty-state { text-align: center; padding: 60px 20px; color: #6b8ba4; }
    .empty-state i { font-size: 3rem; opacity: 0.2; display: block; margin-bottom: 14px; }
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
        <div class="topbar-balance" id="balDisplay"><i class="fas fa-wallet"></i> <?= formatMoney((float)$currentUser['balance']) ?></div>
        <div class="topbar-user"><div class="topbar-avatar"><?= userInitials($currentUser) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentUser['first_name']) ?></span></div>
      </div>
    </div>
    <div class="dash-content">
      <div class="section-card" style="margin-bottom:22px">
        <div class="section-card-body" style="padding:14px 20px">
          <span style="font-size:0.88rem;color:#3a5068"><i class="fas fa-circle-info" style="color:#e67e22;margin-right:7px"></i>Each login is unique and sold only once. Once you purchase, the credentials are shown immediately in a popup. Act fast — stock is limited!</span>
        </div>
      </div>

      <?php if (empty($grouped)): ?>
        <div class="empty-state"><i class="fas fa-key"></i><p>No social logins in stock right now. Check back soon!</p></div>
      <?php else: ?>
        <?php foreach ($grouped as $platform => $logins):
          [$color, $bg, $icon] = $platformMeta[$platform] ?? $defaultMeta;
        ?>
        <div class="platform-section">
          <div class="platform-header">
            <div class="platform-icon" style="background:<?= $bg ?>;color:<?= $color ?>">
              <i class="<?= $icon ?>"></i>
            </div>
            <div class="platform-name"><?= htmlspecialchars($platform) ?></div>
            <span class="platform-count"><?= count($logins) ?> available</span>
          </div>
          <div class="logins-grid">
            <?php foreach ($logins as $l): ?>
            <div class="login-card" id="loginCard<?= $l['id'] ?>">
              <div class="login-title"><?= htmlspecialchars($l['title']) ?></div>
              <?php if (!empty($l['description'])): ?>
                <div class="login-desc"><?= nl2br(htmlspecialchars($l['description'])) ?></div>
              <?php endif; ?>
              <div class="login-footer">
                <div class="item-price"><?= formatMoney((float)$l['price']) ?></div>
                <button class="buy-btn" onclick="buySocialLogin(<?= $l['id'] ?>, this)">
                  <i class="fas fa-key"></i> Buy Login
                </button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- Credentials Modal -->
<div class="modal-overlay" id="loginModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title">🔑 Your Login Credentials</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div style="text-align:center;margin-bottom:16px">
      <span style="background:#e6faf5;color:#0a8c6a;padding:6px 18px;border-radius:100px;font-size:0.85rem;font-weight:700"><i class="fas fa-circle-check"></i> Purchase Successful!</span>
    </div>
    <p style="font-size:0.88rem;color:#3a5068;margin-bottom:14px;text-align:center">Copy all credentials below and save them somewhere safe:</p>
    <div class="credentials-box" id="credentialsBox"></div>
    <button class="copy-all-btn" id="copyAllBtn" onclick="copyAll()">
      <i class="fas fa-copy"></i> Copy All Credentials
    </button>
    <div style="font-size:0.78rem;color:#6b8ba4;text-align:center;margin-top:12px">
      <i class="fas fa-triangle-exclamation" style="color:#e67e22"></i>
      This login is exclusively yours — it has been removed from stock.
    </div>
  </div>
</div>
<div id="alertToast" style="position:fixed;bottom:80px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '2348000000000' ?>" target="_blank" class="whatsapp-fab"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>

<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function showToast(msg,type='error'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='error'?'circle-exclamation':'circle-check'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',4500);}

  async function buySocialLogin(id, btn) {
    const orig = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span>';
    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/user/buy_item.php', {
        method: 'POST', headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ item_type: 'social_login', item_id: id })
      });
      const data = await res.json();
      if (data.success) {
        document.getElementById('balDisplay').innerHTML = `<i class="fas fa-wallet"></i> ${data.new_balance}`;
        document.getElementById('credentialsBox').textContent = data.secret;
        document.getElementById('loginModal').classList.add('active');
        // Remove the card from the page (it's now sold)
        const card = document.getElementById('loginCard' + id);
        if (card) card.remove();
      } else {
        showToast(data.message || 'Purchase failed.');
        btn.disabled = false; btn.innerHTML = orig;
      }
    } catch(e) {
      showToast('Connection error. Please try again.');
      btn.disabled = false; btn.innerHTML = orig;
    }
  }

  function closeModal() {
    document.getElementById('loginModal').classList.remove('active');
    document.getElementById('copyAllBtn').innerHTML = '<i class="fas fa-copy"></i> Copy All Credentials';
    document.getElementById('copyAllBtn').classList.remove('copied');
  }

  function copyAll() {
    const text = document.getElementById('credentialsBox').textContent;
    navigator.clipboard.writeText(text).then(() => {
      const b = document.getElementById('copyAllBtn');
      b.innerHTML = '<i class="fas fa-check"></i> Copied Successfully!';
      b.classList.add('copied');
      setTimeout(() => {
        b.innerHTML = '<i class="fas fa-copy"></i> Copy All Credentials';
        b.classList.remove('copied');
      }, 3000);
    });
  }

  document.getElementById('loginModal').addEventListener('click', function(e) { if(e.target===this) closeModal(); });
</script>
</body>
</html>