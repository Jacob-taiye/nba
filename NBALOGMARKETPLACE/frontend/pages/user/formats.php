<?php
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';
$activePage = 'formats';
$stmt = $pdo->prepare('SELECT * FROM formats WHERE is_active=1 ORDER BY created_at DESC');
$stmt->execute();
$formats = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Formats — NBALOGMARKETPLACE</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .items-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(280px,1fr)); gap:20px; }
    .item-card { background:white; border:1.5px solid #d6ecff; border-radius:16px; padding:22px; transition:all 0.25s; display:flex; flex-direction:column; gap:12px; }
    .item-card:hover { box-shadow:0 8px 28px rgba(26,140,255,0.13); transform:translateY(-3px); border-color:#1a8cff; }
    .item-icon { width:48px; height:48px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; }
    .item-title { font-family:'Outfit',sans-serif; font-weight:700; font-size:1rem; color:#0d1b2a; }
    .item-desc { font-size:0.87rem; color:#6b8ba4; line-height:1.55; flex:1; }
    .item-footer { display:flex; align-items:center; justify-content:space-between; padding-top:12px; border-top:1.5px solid #f0f7ff; margin-top:auto; }
    .item-price { font-family:'Outfit',sans-serif; font-size:1.15rem; font-weight:800; color:#1a8cff; }
    .buy-btn { padding:9px 20px; background:linear-gradient(135deg,#1a8cff,#0057b8); color:white; border:none; border-radius:9px; font-family:'Outfit',sans-serif; font-weight:700; font-size:0.88rem; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; gap:7px; }
    .buy-btn:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(26,140,255,0.35); }
    .buy-btn:disabled { opacity:0.6; cursor:not-allowed; transform:none; }
    .buy-btn.owned { background:linear-gradient(135deg,#0a8c6a,#057a5a); }
    /* Modal */
    .modal-overlay { position:fixed; inset:0; background:rgba(13,27,42,0.6); backdrop-filter:blur(6px); z-index:9000; display:flex; align-items:center; justify-content:center; opacity:0; visibility:hidden; transition:all 0.28s; }
    .modal-overlay.active { opacity:1; visibility:visible; }
    .modal-box { background:white; border-radius:22px; padding:36px; width:90%; max-width:480px; transform:scale(0.93) translateY(20px); transition:all 0.28s; }
    .modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
    .modal-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .modal-title { font-family:'Outfit',sans-serif; font-size:1.2rem; font-weight:800; color:#0d1b2a; }
    .modal-close { width:34px; height:34px; border-radius:50%; border:none; cursor:pointer; background:#f0f7ff; color:#6b8ba4; font-size:1rem; display:flex; align-items:center; justify-content:center; transition:all 0.2s; }
    .modal-close:hover { background:#e8f4ff; color:#1a8cff; }
    .copy-wrap { background:#f0f7ff; border:1.5px solid #d6ecff; border-radius:10px; overflow:hidden; display:flex; margin-bottom:14px; }
    .copy-wrap input { flex:1; padding:13px 14px; border:none; background:transparent; font-family:monospace; font-size:0.88rem; color:#0d1b2a; outline:none; }
    .copy-btn { padding:0 18px; background:#1a8cff; color:white; border:none; cursor:pointer; font-weight:700; font-size:0.85rem; transition:background 0.2s; white-space:nowrap; }
    .copy-btn:hover { background:#0057b8; }
    .copy-btn.copied { background:#0a8c6a; }
    .empty-state { grid-column:1/-1; text-align:center; padding:60px 20px; color:#6b8ba4; }
    .empty-state i { font-size:3rem; opacity:0.2; display:block; margin-bottom:14px; }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>
  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()"><span></span><span></span><span></span></button>
        <div class="page-title"><i class="fas fa-file-lines" style="color:#e74c3c"></i> Formats</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance" id="balDisplay"><i class="fas fa-wallet"></i> <?= formatMoney((float)$currentUser['balance']) ?></div>
        <div class="topbar-user"><div class="topbar-avatar"><?= userInitials($currentUser) ?></div><span class="topbar-uname"><?= htmlspecialchars($currentUser['first_name']) ?></span></div>
      </div>
    </div>
    <div class="dash-content">
      <div class="section-card" style="margin-bottom:22px">
        <div class="section-card-body" style="padding:14px 20px">
          <span style="font-size:0.88rem;color:#3a5068"><i class="fas fa-circle-info" style="color:#1a8cff;margin-right:7px"></i>Buy any format below — the download link appears instantly after purchase.</span>
        </div>
      </div>
      <div class="items-grid">
        <?php if (empty($formats)): ?>
          <div class="empty-state"><i class="fas fa-file-lines"></i><p>No formats available yet.</p></div>
        <?php else: foreach ($formats as $f): ?>
          <div class="item-card">
            <div class="item-icon" style="background:#ffeef0;color:#e74c3c"><i class="fas fa-file-lines"></i></div>
            <div class="item-title"><?= htmlspecialchars($f['title']) ?></div>
            <div class="item-desc"><?= nl2br(htmlspecialchars($f['description'] ?? '')) ?></div>
            <div class="item-footer">
              <div class="item-price"><?= formatMoney((float)$f['price']) ?></div>
              <button class="buy-btn" onclick="buyItem('format',<?= $f['id'] ?>,this)"><i class="fas fa-bolt"></i> Buy Now</button>
            </div>
          </div>
        <?php endforeach; endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="purchaseModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="modalTitle">Purchase Successful!</div>
      <button class="modal-close" onclick="closeModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <div style="text-align:center;font-size:2.8rem;margin-bottom:16px">🎉</div>
    <p style="text-align:center;margin-bottom:16px;font-size:0.92rem;color:#3a5068">Your link is ready — copy it below:</p>
    <div class="copy-wrap">
      <input type="text" id="secretValue" readonly/>
      <button class="copy-btn" id="copyBtn" onclick="copySecret()"><i class="fas fa-copy"></i> Copy</button>
    </div>
    <div style="font-size:0.8rem;color:#6b8ba4;text-align:center"><i class="fas fa-shield-halved" style="color:#0a8c6a"></i> Re-purchase anytime to retrieve this link again.</div>
  </div>
</div>
<div id="alertToast" style="position:fixed;bottom:80px;right:24px;z-index:9999;display:none;max-width:320px"></div>
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '2348000000000' ?>" target="_blank" class="whatsapp-fab"><svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg></a>
<script>
  function openSidebar(){document.getElementById('sidebar').classList.add('open');document.getElementById('sidebarOverlay').classList.add('active');document.body.style.overflow='hidden';}
  function closeSidebar(){document.getElementById('sidebar').classList.remove('open');document.getElementById('sidebarOverlay').classList.remove('active');document.body.style.overflow='';}
  function showToast(msg,type='error'){const el=document.getElementById('alertToast');el.className=`alert alert-${type}`;el.innerHTML=`<i class="fas fa-${type==='error'?'circle-exclamation':'circle-check'}"></i> ${msg}`;el.style.display='flex';setTimeout(()=>el.style.display='none',4000);}
  async function buyItem(type,id,btn){
    const orig=btn.innerHTML; btn.disabled=true; btn.innerHTML='<span class="spinner"></span>';
    try{
      const res=await fetch('/NBALOGMARKETPLACE/backend/user/buy_item.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({item_type:type,item_id:id})});
      const data=await res.json();
      if(data.success){
        document.getElementById('balDisplay').innerHTML=`<i class="fas fa-wallet"></i> ${data.new_balance}`;
        document.getElementById('modalTitle').textContent=data.already_owned?'📂 Your Link':'🎉 Purchase Successful!';
        document.getElementById('secretValue').value=data.secret;
        document.getElementById('purchaseModal').classList.add('active');
        if(!data.already_owned){btn.innerHTML='<i class="fas fa-check"></i> Owned';btn.classList.add('owned');}
        else{btn.disabled=false;btn.innerHTML=orig;}
      }else{showToast(data.message||'Purchase failed.');btn.disabled=false;btn.innerHTML=orig;}
    }catch(e){showToast('Connection error.');btn.disabled=false;btn.innerHTML=orig;}
  }
  function closeModal(){document.getElementById('purchaseModal').classList.remove('active');}
  function copySecret(){
    navigator.clipboard.writeText(document.getElementById('secretValue').value).then(()=>{
      const b=document.getElementById('copyBtn');b.innerHTML='<i class="fas fa-check"></i> Copied!';b.classList.add('copied');
      setTimeout(()=>{b.innerHTML='<i class="fas fa-copy"></i> Copy';b.classList.remove('copied');},2500);
    });
  }
  document.getElementById('purchaseModal').addEventListener('click',function(e){if(e.target===this)closeModal();});
</script>
</body>
</html>