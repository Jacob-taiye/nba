<?php
/**
 * VirtualHub Pro — Transactions Page
 */
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';
$activePage = 'transactions';
$uid = $currentUser['id'];

// ── Pagination ────────────────────────────────────────────
$perPage = 15;
$page    = max(1, (int)($_GET['page'] ?? 1));
$offset  = ($page - 1) * $perPage;

// ── Filter ────────────────────────────────────────────────
$filterType = $_GET['type'] ?? 'all';
$allowedTypes = ['all','topup','purchase','refund','admin_topup'];
if (!in_array($filterType, $allowedTypes)) $filterType = 'all';

// Build query
$whereExtra = $filterType !== 'all' ? " AND type = :type" : '';
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE user_id = :uid $whereExtra");
$params = [':uid' => $uid];
if ($filterType !== 'all') $params[':type'] = $filterType;
$countStmt->execute($params);
$total = (int)$countStmt->fetchColumn();
$totalPages = max(1, ceil($total / $perPage));

$txStmt = $pdo->prepare(
    "SELECT t.*, p.secret_content, p.item_title AS purchase_title
     FROM transactions t
     LEFT JOIN purchases p ON p.transaction_id = t.id AND t.type = 'purchase'
     WHERE t.user_id = :uid $whereExtra
     ORDER BY t.created_at DESC LIMIT :limit OFFSET :offset"
);
$params[':limit']  = $perPage;
$params[':offset'] = $offset;
$txStmt->execute($params);
$transactions = $txStmt->fetchAll();

// Summary stats
$summaryStmt = $pdo->prepare(
    "SELECT
       COALESCE(SUM(CASE WHEN type IN ('topup','admin_topup') AND status='success' THEN amount ELSE 0 END),0) as total_topup,
       COALESCE(SUM(CASE WHEN type='purchase' AND status='success' THEN amount ELSE 0 END),0) as total_spent,
       COALESCE(SUM(CASE WHEN type='refund' AND status='success' THEN amount ELSE 0 END),0) as total_refund,
       COUNT(*) as total_count
     FROM transactions WHERE user_id = ?"
);
$summaryStmt->execute([$uid]);
$summary = $summaryStmt->fetch();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transactions — VirtualHub Pro</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .tx-summary-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 16px;
      margin-bottom: 24px;
    }
    .tx-sum-card {
      background: white;
      border-radius: 14px;
      padding: 18px 16px;
      border: 1.5px solid #d6ecff;
      box-shadow: 0 2px 8px rgba(26,140,255,0.06);
    }
    .tx-sum-label {
      font-size: 0.76rem; font-weight: 600;
      color: #6b8ba4; text-transform: uppercase;
      letter-spacing: 0.5px; margin-bottom: 8px;
    }
    .tx-sum-val {
      font-family: 'Outfit', sans-serif;
      font-size: 1.3rem; font-weight: 800; color: #0d1b2a;
    }

    /* Filter tabs */
    .filter-tabs {
      display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 18px;
    }
    .filter-tab {
      padding: 8px 18px;
      border-radius: 100px;
      font-size: 0.85rem; font-weight: 600;
      border: 2px solid #d6ecff;
      background: white; color: #6b8ba4;
      text-decoration: none; transition: all 0.2s;
    }
    .filter-tab:hover { border-color: #1a8cff; color: #1a8cff; }
    .filter-tab.active {
      background: #1a8cff; border-color: #1a8cff;
      color: white;
    }

    /* Transaction table */
    .tx-table-wrap {
      background: white;
      border-radius: 16px;
      border: 1.5px solid #d6ecff;
      overflow: hidden;
      box-shadow: 0 2px 10px rgba(26,140,255,0.06);
    }
    .tx-table-header {
      padding: 16px 22px;
      border-bottom: 1.5px solid #e8f4ff;
      display: flex; align-items: center; justify-content: space-between;
    }
    .tx-table-title {
      font-family: 'Outfit', sans-serif;
      font-weight: 700; font-size: 1rem; color: #0d1b2a;
      display: flex; align-items: center; gap: 8px;
    }
    .tx-count-badge {
      background: #e8f4ff; color: #1a8cff;
      padding: 3px 10px; border-radius: 100px;
      font-size: 0.78rem; font-weight: 700;
    }
    .tx-table { width: 100%; border-collapse: collapse; }
    .tx-table th {
      background: #f5fbff;
      padding: 12px 16px;
      text-align: left;
      font-size: 0.78rem; font-weight: 700;
      color: #6b8ba4; text-transform: uppercase;
      letter-spacing: 0.5px; border-bottom: 1.5px solid #e8f4ff;
      white-space: nowrap;
    }
    .tx-table td {
      padding: 14px 16px;
      border-bottom: 1px solid #f0f7ff;
      font-size: 0.9rem; vertical-align: middle;
    }
    .tx-table tr:last-child td { border-bottom: none; }
    .tx-table tr:hover td { background: #fafcff; }

    .tx-type-icon {
      width: 36px; height: 36px; border-radius: 10px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.9rem; flex-shrink: 0;
    }
    .tx-label-cell { display: flex; align-items: center; gap: 12px; }
    .tx-label-text { font-weight: 600; color: #0d1b2a; font-size: 0.9rem; }
    .tx-ref-text   { font-size: 0.76rem; color: #6b8ba4; margin-top: 2px; font-family: monospace; }

    .amount-credit { color: #0a8c6a; font-weight: 700; font-family: 'Outfit', sans-serif; }
    .amount-debit  { color: #c0392b; font-weight: 700; font-family: 'Outfit', sans-serif; }

    .status-badge {
      padding: 4px 10px; border-radius: 100px;
      font-size: 0.76rem; font-weight: 700; display: inline-block;
    }
    .status-success  { background: #e6faf5; color: #0a8c6a; }
    .status-pending  { background: #fff8e1; color: #d4ac0d; }
    .status-failed   { background: #fff0f0; color: #c0392b; }
    .status-refunded { background: #e8f4ff; color: #1a8cff; }
    .copy-again-btn {
      padding: 5px 12px; background: #f0f7ff;
      border: 1.5px solid #b3d9ff; border-radius: 7px;
      color: #1a8cff; font-size: 0.78rem; font-weight: 700;
      cursor: pointer; transition: all 0.2s;
      display: inline-flex; align-items: center; gap: 5px;
    }
    .copy-again-btn:hover { background: #e8f4ff; border-color: #1a8cff; }
    /* Copy modal */
    .modal-overlay { position:fixed; inset:0; background:rgba(13,27,42,0.6); backdrop-filter:blur(6px); z-index:9000; display:flex; align-items:center; justify-content:center; opacity:0; visibility:hidden; transition:all 0.28s; }
    .modal-overlay.active { opacity:1; visibility:visible; }
    .modal-box { background:white; border-radius:22px; padding:36px; width:90%; max-width:480px; transform:scale(0.93) translateY(20px); transition:all 0.28s; }
    .modal-overlay.active .modal-box { transform:scale(1) translateY(0); }
    .modal-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:20px; }
    .modal-title { font-family:'Outfit',sans-serif; font-size:1.2rem; font-weight:800; color:#0d1b2a; }
    .modal-close { width:34px; height:34px; border-radius:50%; border:none; cursor:pointer; background:#f0f7ff; color:#6b8ba4; font-size:1rem; display:flex; align-items:center; justify-content:center; }
    .modal-close:hover { background:#e8f4ff; color:#1a8cff; }
    .secret-box { background:#f5fbff; border:1.5px solid #d6ecff; border-radius:10px; padding:16px; font-family:monospace; font-size:0.88rem; color:#0d1b2a; word-break:break-all; white-space:pre-wrap; margin-bottom:14px; max-height:200px; overflow-y:auto; }
    .copy-secret-btn { width:100%; padding:13px; background:linear-gradient(135deg,#1a8cff,#0057b8); color:white; border:none; border-radius:10px; font-family:'Outfit',sans-serif; font-weight:700; font-size:0.95rem; cursor:pointer; transition:all 0.2s; display:flex; align-items:center; justify-content:center; gap:8px; }
    .copy-secret-btn:hover { transform:translateY(-1px); box-shadow:0 4px 14px rgba(26,140,255,0.35); }
    .copy-secret-btn.copied { background:linear-gradient(135deg,#0a8c6a,#057a5a); }

    /* Pagination */
    .pagination {
      display: flex; align-items: center; gap: 6px;
      padding: 16px 22px; justify-content: center;
      border-top: 1.5px solid #e8f4ff;
    }
    .page-btn {
      width: 36px; height: 36px; border-radius: 8px;
      display: flex; align-items: center; justify-content: center;
      font-size: 0.88rem; font-weight: 600;
      border: 1.5px solid #d6ecff; background: white;
      color: #3a5068; text-decoration: none; transition: all 0.2s;
    }
    .page-btn:hover { border-color: #1a8cff; color: #1a8cff; }
    .page-btn.active { background: #1a8cff; border-color: #1a8cff; color: white; }
    .page-btn.disabled { opacity: 0.4; pointer-events: none; }

    .empty-state {
      text-align: center; padding: 60px 20px;
      color: #6b8ba4;
    }
    .empty-state i {
      font-size: 3rem; opacity: 0.25;
      display: block; margin-bottom: 16px;
    }
    .empty-state p { font-size: 1rem; margin-bottom: 16px; }

    @media(max-width: 900px) {
      .tx-summary-grid { grid-template-columns: repeat(2,1fr); }
      .tx-table th:nth-child(4),
      .tx-table td:nth-child(4) { display: none; }
    }
    @media(max-width: 600px) {
      .tx-summary-grid { grid-template-columns: 1fr 1fr; }
      .tx-table th:nth-child(3),
      .tx-table td:nth-child(3) { display: none; }
    }
  </style>
</head>
<body>
<div class="dash-shell">
  <?php include __DIR__ . '/sidebar.php'; ?>

  <div class="dash-main">
    <div class="topbar">
      <div class="topbar-left">
        <button class="hamburger-btn" onclick="openSidebar()">
          <span></span><span></span><span></span>
        </button>
        <div class="page-title"><i class="fas fa-receipt" style="color:#1a8cff"></i> Transactions</div>
      </div>
      <div class="topbar-right">
        <div class="topbar-balance">
          <i class="fas fa-wallet"></i>
          <?= formatMoney((float)$currentUser['balance']) ?>
        </div>
        <div class="topbar-user">
          <div class="topbar-avatar"><?= userInitials($currentUser) ?></div>
          <span class="topbar-uname"><?= htmlspecialchars($currentUser['first_name']) ?></span>
        </div>
      </div>
    </div>

    <div class="dash-content">

      <!-- Summary Cards -->
      <div class="tx-summary-grid">
        <div class="tx-sum-card">
          <div class="tx-sum-label">Total Topped Up</div>
          <div class="tx-sum-val" style="color:#0a8c6a"><?= formatMoney((float)$summary['total_topup']) ?></div>
        </div>
        <div class="tx-sum-card">
          <div class="tx-sum-label">Total Spent</div>
          <div class="tx-sum-val" style="color:#e67e22"><?= formatMoney((float)$summary['total_spent']) ?></div>
        </div>
        <div class="tx-sum-card">
          <div class="tx-sum-label">Total Refunded</div>
          <div class="tx-sum-val" style="color:#1a8cff"><?= formatMoney((float)$summary['total_refund']) ?></div>
        </div>
        <div class="tx-sum-card">
          <div class="tx-sum-label">All Transactions</div>
          <div class="tx-sum-val"><?= number_format((int)$summary['total_count']) ?></div>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="filter-tabs">
        <?php
        $tabs = [
          'all'        => 'All',
          'topup'      => 'Top Ups',
          'purchase'   => 'Purchases',
          'refund'     => 'Refunds',
          'admin_topup'=> 'Admin Credits',
        ];
        foreach ($tabs as $val => $label):
          $active = $filterType === $val ? 'active' : '';
          $url = '?type=' . $val . ($page > 1 ? '&page=1' : '');
        ?>
          <a href="<?= $url ?>" class="filter-tab <?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Transactions Table -->
      <div class="tx-table-wrap">
        <div class="tx-table-header">
          <div class="tx-table-title">
            <i class="fas fa-list" style="color:#1a8cff"></i>
            Transaction History
            <span class="tx-count-badge"><?= $total ?> records</span>
          </div>
        </div>

        <?php if (empty($transactions)): ?>
          <div class="empty-state">
            <i class="fas fa-receipt"></i>
            <p>No transactions found<?= $filterType !== 'all' ? ' for this filter' : '' ?>.</p>
            <?php if ($filterType === 'all'): ?>
              <a href="topup.php" class="btn btn-primary btn-sm">
                <i class="fas fa-plus"></i> Top Up Your Wallet
              </a>
            <?php endif; ?>
          </div>
        <?php else: ?>
          <div style="overflow-x:auto">
            <table class="tx-table">
              <thead>
                <tr>
                  <th>Description</th>
                  <th>Amount</th>
                  <th>Balance After</th>
                  <th>Date & Time</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($transactions as $tx):
                  $iconMap = [
                    'topup'       => ['fas fa-arrow-down-to-line','#e6faf5','#0a8c6a'],
                    'admin_topup' => ['fas fa-user-shield',       '#e6faf5','#0a8c6a'],
                    'purchase'    => ['fas fa-shopping-bag',      '#fff3e8','#e67e22'],
                    'refund'      => ['fas fa-rotate-left',       '#e8f4ff','#1a8cff'],
                  ];
                  [$icon, $bg, $clr] = $iconMap[$tx['type']] ?? ['fas fa-circle','#f0f7ff','#6b8ba4'];
                  $isCredit = in_array($tx['type'], ['topup','admin_topup','refund']);
                  $label = match($tx['type']) {
                    'topup'        => 'Wallet Top Up',
                    'admin_topup'  => 'Admin Credit',
                    'refund'       => 'Refund — ' . ucfirst(str_replace('_',' ',$tx['item_type'] ?? '')),
                    'purchase'     => 'Purchase — ' . ucfirst(str_replace('_',' ',$tx['item_type'] ?? '')),
                    default        => ucfirst($tx['type'])
                  };
                  $statusClass = 'status-' . $tx['status'];
                ?>
                <tr>
                  <td>
                    <div class="tx-label-cell">
                      <div class="tx-type-icon" style="background:<?= $bg ?>;color:<?= $clr ?>">
                        <i class="<?= $icon ?>"></i>
                      </div>
                      <div>
                        <div class="tx-label-text"><?= htmlspecialchars($label) ?></div>
                        <?php if ($tx['reference']): ?>
                          <div class="tx-ref-text"><?= htmlspecialchars($tx['reference']) ?></div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </td>
                  <td>
                    <span class="<?= $isCredit ? 'amount-credit' : 'amount-debit' ?>">
                      <?= $isCredit ? '+' : '-' ?><?= formatMoney((float)$tx['amount']) ?>
                    </span>
                  </td>
                  <td><?= formatMoney((float)$tx['balance_after']) ?></td>
                  <td style="color:#6b8ba4;font-size:0.85rem;white-space:nowrap">
                    <?= date('M j, Y', strtotime($tx['created_at'])) ?><br>
                    <span style="font-size:0.78rem"><?= date('g:i A', strtotime($tx['created_at'])) ?></span>
                  </td>
                  <td>
                    <div style="display:flex;flex-direction:column;gap:6px;align-items:flex-start">
                      <span class="status-badge <?= $statusClass ?>">
                        <?= ucfirst($tx['status']) ?>
                      </span>
                      <?php if ($tx['type'] === 'purchase' && !empty($tx['secret_content'])): ?>
                        <button
                          class="copy-again-btn"
                          onclick="showSecret(<?= htmlspecialchars(json_encode($tx['secret_content']), ENT_QUOTES) ?>, <?= htmlspecialchars(json_encode($tx['purchase_title'] ?? $label), ENT_QUOTES) ?>)"
                        >
                          <i class="fas fa-copy"></i> Copy Again
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <?php if ($totalPages > 1): ?>
          <div class="pagination">
            <a href="?type=<?= $filterType ?>&page=<?= max(1,$page-1) ?>"
               class="page-btn <?= $page <= 1 ? 'disabled' : '' ?>">
              <i class="fas fa-chevron-left"></i>
            </a>

            <?php
            $start = max(1, $page - 2);
            $end   = min($totalPages, $page + 2);
            for ($i = $start; $i <= $end; $i++):
            ?>
              <a href="?type=<?= $filterType ?>&page=<?= $i ?>"
                 class="page-btn <?= $i === $page ? 'active' : '' ?>">
                <?= $i ?>
              </a>
            <?php endfor; ?>

            <a href="?type=<?= $filterType ?>&page=<?= min($totalPages,$page+1) ?>"
               class="page-btn <?= $page >= $totalPages ? 'disabled' : '' ?>">
              <i class="fas fa-chevron-right"></i>
            </a>
          </div>
          <?php endif; ?>

        <?php endif; ?>
      </div>

    </div><!-- /.dash-content -->
  </div>
</div>

<!-- WhatsApp FAB -->
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '2348000000000' ?>" target="_blank" class="whatsapp-fab">
  <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<!-- Copy Again Modal -->
<div class="modal-overlay" id="secretModal">
  <div class="modal-box">
    <div class="modal-head">
      <div class="modal-title" id="secretModalTitle">Your Purchase</div>
      <button class="modal-close" onclick="closeSecretModal()"><i class="fas fa-xmark"></i></button>
    </div>
    <p style="font-size:0.88rem;color:#6b8ba4;margin-bottom:12px">Here is what you purchased — copy it below:</p>
    <div class="secret-box" id="secretBox"></div>
    <button class="copy-secret-btn" id="copySecretBtn" onclick="copySecret()">
      <i class="fas fa-copy"></i> Copy
    </button>
  </div>
</div>

<script>
  function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('active');
    document.body.style.overflow = 'hidden';
  }
  function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
    document.body.style.overflow = '';
  }

  function showSecret(secret, title) {
    document.getElementById('secretModalTitle').textContent = title || 'Your Purchase';
    document.getElementById('secretBox').textContent = secret;
    document.getElementById('secretModal').classList.add('active');
    // Reset copy btn
    const b = document.getElementById('copySecretBtn');
    b.innerHTML = '<i class="fas fa-copy"></i> Copy';
    b.classList.remove('copied');
  }
  function closeSecretModal() {
    document.getElementById('secretModal').classList.remove('active');
  }
  function copySecret() {
    const text = document.getElementById('secretBox').textContent;
    navigator.clipboard.writeText(text).then(() => {
      const b = document.getElementById('copySecretBtn');
      b.innerHTML = '<i class="fas fa-check"></i> Copied!';
      b.classList.add('copied');
      setTimeout(() => {
        b.innerHTML = '<i class="fas fa-copy"></i> Copy';
        b.classList.remove('copied');
      }, 2500);
    });
  }
  document.getElementById('secretModal').addEventListener('click', function(e) {
    if (e.target === this) closeSecretModal();
  });
</script>
</body>
</html>