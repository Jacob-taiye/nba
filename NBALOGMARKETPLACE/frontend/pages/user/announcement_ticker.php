<?php
/**
 * Announcement Ticker — include on every user page
 */
$announcements = [];
try {
    $annStmt = $pdo->query("SELECT id, message, type FROM announcements WHERE is_active=1 ORDER BY created_at DESC");
    $announcements = $annStmt ? $annStmt->fetchAll(PDO::FETCH_ASSOC) : [];
} catch (Throwable $e) {
    $announcements = [];
}

$typeIcons = [
    'info'    => ['#1a8cff', 'fa-circle-info'],
    'warning' => ['#e67e22', 'fa-triangle-exclamation'],
    'success' => ['#0a8c6a', 'fa-circle-check'],
    'danger'  => ['#c0392b', 'fa-circle-exclamation'],
];

if (!empty($announcements)): ?>
<div class="ann-ticker-wrap" id="annTickerWrap">
  <div class="ann-ticker-inner">
    <div class="ann-ticker-label">
      <i class="fas fa-bullhorn"></i> NOTICE
    </div>
    <div class="ann-ticker-scroll">
      <div class="ann-ticker-track" id="annTrack">
        <?php foreach ($announcements as $ann):
          $ic = $typeIcons[$ann['type']] ?? $typeIcons['info'];
        ?>
        <span class="ann-ticker-item">
          <i class="fas <?= $ic[1] ?>" style="color:<?= $ic[0] ?>"></i>
          <?= htmlspecialchars($ann['message']) ?>
          <span class="ann-sep">•</span>
        </span>
        <?php endforeach; ?>
        <?php foreach ($announcements as $ann):
          $ic = $typeIcons[$ann['type']] ?? $typeIcons['info'];
        ?>
        <span class="ann-ticker-item">
          <i class="fas <?= $ic[1] ?>" style="color:<?= $ic[0] ?>"></i>
          <?= htmlspecialchars($ann['message']) ?>
          <span class="ann-sep">•</span>
        </span>
        <?php endforeach; ?>
      </div>
    </div>
    <button class="ann-close-btn" onclick="dismissTicker()" title="Dismiss"><i class="fas fa-xmark"></i></button>
  </div>
</div>
<style>
.ann-ticker-wrap{background:#0d1b2a;border-bottom:2px solid #1a3a5c;overflow:hidden;position:relative;z-index:100;flex-shrink:0;}
.ann-ticker-inner{display:flex;align-items:center;height:40px;}
.ann-ticker-label{background:#1a8cff;color:white;font-size:.72rem;font-weight:800;padding:0 14px;height:100%;display:flex;align-items:center;gap:6px;white-space:nowrap;letter-spacing:.5px;flex-shrink:0;}
.ann-ticker-scroll{flex:1;overflow:hidden;height:100%;display:flex;align-items:center;}
.ann-ticker-track{display:inline-flex;align-items:center;white-space:nowrap;animation:annScroll 30s linear infinite;will-change:transform;}
.ann-ticker-track:hover{animation-play-state:paused;}
@keyframes annScroll{0%{transform:translateX(0)}100%{transform:translateX(-50%)}}
.ann-ticker-item{color:#c8dff0;font-size:.84rem;font-weight:500;padding:0 24px 0 0;display:inline-flex;align-items:center;gap:8px;}
.ann-sep{color:#3a5068;margin-left:24px;}
.ann-close-btn{background:none;border:none;color:#6b8ba4;cursor:pointer;padding:0 14px;height:100%;font-size:.9rem;display:flex;align-items:center;flex-shrink:0;transition:color .2s;}
.ann-close-btn:hover{color:white;}
</style>
<script>
function dismissTicker(){
  const el=document.getElementById('annTickerWrap');
  if(el){el.style.height=el.offsetHeight+'px';el.style.overflow='hidden';el.style.transition='height .3s';setTimeout(()=>el.style.height='0',10);setTimeout(()=>el.remove(),320);}
}
</script>
<?php endif; ?>