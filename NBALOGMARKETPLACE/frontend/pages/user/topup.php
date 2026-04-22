<?php
/**
 * NBALOGMARKETPLACE — Top Up Page
 */
require_once __DIR__ . '/../../../backend/includes/auth_guard.php';
$activePage = 'topup';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Top Up — NBALOGMARKETPLACE</title>
  <link rel="stylesheet" href="../../css/style.css"/>
  <link rel="stylesheet" href="../../css/dashboard.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <style>
    .topup-grid {
      display: grid;
      grid-template-columns: 1.1fr 0.9fr;
      gap: 24px;
      align-items: start;
    }
    .amount-presets {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 10px;
      margin-bottom: 20px;
    }
    .preset-btn {
      padding: 13px 8px;
      background: #f0f7ff;
      border: 2px solid #d6ecff;
      border-radius: 10px;
      font-family: 'Outfit', sans-serif;
      font-weight: 700; font-size: 0.95rem;
      color: #3a5068; cursor: pointer;
      transition: all 0.2s; text-align: center;
    }
    .preset-btn:hover, .preset-btn.selected {
      background: #e8f4ff;
      border-color: #1a8cff;
      color: #1a8cff;
    }
    .balance-display {
      background: linear-gradient(135deg, #003d80, #1a8cff);
      border-radius: 18px;
      padding: 28px 24px;
      color: white;
      margin-bottom: 24px;
      position: relative;
      overflow: hidden;
    }
    .balance-display::before {
      content: '';
      position: absolute; top: -40px; right: -40px;
      width: 180px; height: 180px;
      background: rgba(255,255,255,0.07);
      border-radius: 50%;
    }
    .balance-display::after {
      content: '';
      position: absolute; bottom: -60px; right: 30px;
      width: 140px; height: 140px;
      background: rgba(255,255,255,0.05);
      border-radius: 50%;
    }
    .bd-label { font-size: 0.82rem; opacity: 0.75; margin-bottom: 8px; letter-spacing: 0.5px; }
    .bd-amount {
      font-family: 'Outfit', sans-serif;
      font-size: 2.4rem; font-weight: 800;
      letter-spacing: -1px; position: relative; z-index: 1;
    }
    .bd-name { font-size: 0.9rem; opacity: 0.8; margin-top: 8px; }

    .info-box {
      background: #f0f7ff;
      border: 1.5px solid #d6ecff;
      border-radius: 12px;
      padding: 16px 18px;
      font-size: 0.88rem;
      color: #3a5068;
      line-height: 1.6;
      margin-bottom: 16px;
    }
    .info-box i { color: #1a8cff; margin-right: 6px; }

    .pay-btn {
      width: 100%; padding: 15px;
      background: linear-gradient(135deg, #1a8cff, #0057b8);
      color: white; border: none; border-radius: 12px;
      font-family: 'Outfit', sans-serif;
      font-size: 1.05rem; font-weight: 700;
      cursor: pointer; transition: all 0.25s;
      display: flex; align-items: center; justify-content: center; gap: 10px;
      box-shadow: 0 4px 16px rgba(26,140,255,0.35);
    }
    .pay-btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(26,140,255,0.45); }
    .pay-btn:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }

    .secure-note {
      text-align: center; margin-top: 14px;
      font-size: 0.8rem; color: #6b8ba4;
      display: flex; align-items: center; justify-content: center; gap: 6px;
    }
    .secure-note i { color: #0a8c6a; }

    .how-steps { display: flex; flex-direction: column; gap: 14px; }
    .how-step {
      display: flex; align-items: flex-start; gap: 14px;
      padding: 14px 16px;
      background: #f5fbff;
      border-radius: 12px;
      border: 1.5px solid #e8f4ff;
    }
    .how-step-num {
      width: 32px; height: 32px; border-radius: 50%;
      background: linear-gradient(135deg, #1a8cff, #0057b8);
      color: white; font-family: 'Outfit', sans-serif;
      font-weight: 700; font-size: 0.9rem;
      display: flex; align-items: center; justify-content: center;
      flex-shrink: 0;
    }
    .how-step-text { font-size: 0.88rem; color: #3a5068; line-height: 1.5; }
    .how-step-text strong { color: #0d1b2a; display: block; margin-bottom: 2px; }

    @media(max-width: 900px) {
      .topup-grid { grid-template-columns: 1fr; }
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
        <div class="page-title"><i class="fas fa-wallet" style="color:#1a8cff"></i> Top Up Balance</div>
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
      <div class="topup-grid">

        <!-- LEFT: Payment Form -->
        <div>
          <!-- Balance Card -->
          <div class="balance-display">
            <div class="bd-label">CURRENT BALANCE</div>
            <div class="bd-amount"><?= formatMoney((float)$currentUser['balance']) ?></div>
            <div class="bd-name"><?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?></div>
          </div>

          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title">
                <i class="fas fa-plus-circle"></i> Add Funds
              </div>
            </div>
            <div class="section-card-body">

              <div id="topupAlert" style="display:none" class="alert mb-2"></div>

              <!-- Preset amounts -->
              <div style="font-size:0.85rem;font-weight:600;color:#6b8ba4;margin-bottom:10px">Quick Select Amount</div>
              <div class="amount-presets">
                <button class="preset-btn" onclick="setAmount(500)">₦500</button>
                <button class="preset-btn" onclick="setAmount(1000)">₦1,000</button>
                <button class="preset-btn" onclick="setAmount(2000)">₦2,000</button>
                <button class="preset-btn" onclick="setAmount(5000)">₦5,000</button>
                <button class="preset-btn" onclick="setAmount(10000)">₦10,000</button>
                <button class="preset-btn" onclick="setAmount(20000)">₦20,000</button>
              </div>

              <!-- Custom amount -->
              <div class="form-group">
                <label class="form-label">Or Enter Custom Amount (₦)</label>
                <input
                  type="number"
                  class="form-control"
                  id="amountInput"
                  placeholder="e.g. 3500"
                  min="100"
                  max="500000"
                  oninput="clearPresets()"
                />
              </div>

              <div class="info-box">
                <i class="fas fa-circle-info"></i>
                Minimum top-up is <strong>₦100</strong>. Payments are processed securely via <strong>Flutterwave</strong>. Your balance is credited instantly after payment.
              </div>

              <button class="pay-btn" id="payBtn" onclick="initiatePayment()">
                <i class="fas fa-lock"></i> Pay Securely with Flutterwave
              </button>

              <div class="secure-note">
                <i class="fas fa-shield-halved"></i>
                256-bit SSL encrypted · Powered by Flutterwave
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: How it works -->
        <div>
          <div class="section-card">
            <div class="section-card-header">
              <div class="section-card-title">
                <i class="fas fa-circle-question"></i> How Top Up Works
              </div>
            </div>
            <div class="section-card-body">
              <div class="how-steps">
                <div class="how-step">
                  <div class="how-step-num">1</div>
                  <div class="how-step-text">
                    <strong>Enter Amount</strong>
                    Choose a preset or type a custom amount you want to add.
                  </div>
                </div>
                <div class="how-step">
                  <div class="how-step-num">2</div>
                  <div class="how-step-text">
                    <strong>Click Pay</strong>
                    You'll be redirected to Flutterwave's secure checkout.
                  </div>
                </div>
                <div class="how-step">
                  <div class="how-step-num">3</div>
                  <div class="how-step-text">
                    <strong>Complete Payment</strong>
                    Pay with card, bank transfer, USSD or mobile money.
                  </div>
                </div>
                <div class="how-step">
                  <div class="how-step-num">4</div>
                  <div class="how-step-text">
                    <strong>Balance Updated</strong>
                    Your wallet is credited instantly. Start buying services!
                  </div>
                </div>
              </div>

              <div style="margin-top:20px;padding:16px;background:#e6faf5;border-radius:10px;border:1.5px solid #b2dfdb">
                <div style="font-size:0.85rem;font-weight:700;color:#0a8c6a;margin-bottom:6px">
                  <i class="fas fa-shield-check"></i> 100% Safe & Secure
                </div>
                <div style="font-size:0.82rem;color:#2e7d65;line-height:1.6">
                  We never store your card details. All payments go through Flutterwave's PCI-DSS certified platform.
                </div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div><!-- /.dash-content -->
  </div>
</div>

<!-- WhatsApp FAB -->
<a href="https://wa.me/<?= defined('WHATSAPP_NUMBER') ? WHATSAPP_NUMBER : '2348000000000' ?>" target="_blank" class="whatsapp-fab">
  <svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
</a>

<!-- Flutterwave SDK -->
<script src="https://checkout.flutterwave.com/v3.js"></script>
<script>
  const USER_EMAIL   = '<?= htmlspecialchars($currentUser['email']) ?>';
  const USER_NAME    = '<?= htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']) ?>';
  const USER_PHONE   = '<?= htmlspecialchars($currentUser['phone'] ?? '') ?>';
  const FLW_PUB_KEY  = '<?= defined('FLUTTERWAVE_PUBLIC_KEY') ? FLUTTERWAVE_PUBLIC_KEY : '' ?>';

  function setAmount(val) {
    document.getElementById('amountInput').value = val;
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('selected'));
    event.target.classList.add('selected');
  }
  function clearPresets() {
    document.querySelectorAll('.preset-btn').forEach(b => b.classList.remove('selected'));
  }

  function showAlert(msg, type='error') {
    const el = document.getElementById('topupAlert');
    const icons = {error:'circle-exclamation', success:'circle-check', info:'circle-info'};
    el.className = `alert alert-${type}`;
    el.innerHTML = `<i class="fas fa-${icons[type]}"></i> ${msg}`;
    el.style.display = 'flex';
  }

  async function initiatePayment() {
    const amount = parseFloat(document.getElementById('amountInput').value);
    if (!amount || amount < 100) {
      showAlert('Please enter a valid amount (minimum ₦100).'); return;
    }
    if (amount > 500000) {
      showAlert('Maximum top-up per transaction is ₦500,000.'); return;
    }

    const btn = document.getElementById('payBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner"></span> Preparing payment...';

    // Generate unique tx_ref
    const txRef = 'VH-' + Date.now() + '-' + Math.random().toString(36).substr(2,6).toUpperCase();

    // Save pending transaction to our backend first
    try {
      const res  = await fetch('/NBALOGMARKETPLACE/backend/user/initiate_topup.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({ amount, tx_ref: txRef })
      });
      const data = await res.json();
      if (!data.success) {
        showAlert(data.message || 'Failed to initiate payment.');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Pay Securely with Flutterwave';
        return;
      }
    } catch(e) {
      showAlert('Connection error. Please try again.');
      btn.disabled = false;
      btn.innerHTML = '<i class="fas fa-lock"></i> Pay Securely with Flutterwave';
      return;
    }

    // Launch Flutterwave popup
    FlutterwaveCheckout({
      public_key: FLW_PUB_KEY,
      tx_ref:     txRef,
      amount:     amount,
      currency:   'NGN',
      payment_options: 'card,banktransfer,ussd,mobilemoney',
      customer: {
        email: USER_EMAIL,
        name:  USER_NAME,
        phone_number: USER_PHONE
      },
      customizations: {
        title:       'NBALOGMARKETPLACE',
        description: 'Wallet Top Up',
        logo:        window.location.origin + '/NBALOGMARKETPLACE/frontend/images/logo.png'
      },
      callback: async function(response) {
        // Verify on backend
        try {
          const vres  = await fetch('/NBALOGMARKETPLACE/backend/user/verify_topup.php', {
            method: 'POST',
            headers: {'Content-Type':'application/json'},
            body: JSON.stringify({
              transaction_id: response.transaction_id,
              tx_ref:         txRef,
              amount:         amount
            })
          });
          const vdata = await vres.json();
          if (vdata.success) {
            showAlert(`₦${amount.toLocaleString()} added to your wallet successfully! New balance: ${vdata.new_balance}`, 'success');
            // Update displayed balance
            document.querySelectorAll('.topbar-balance').forEach(el => {
              el.innerHTML = `<i class="fas fa-wallet"></i> ${vdata.new_balance}`;
            });
            document.querySelector('.bd-amount').textContent = vdata.new_balance;
          } else {
            showAlert(vdata.message || 'Payment verification failed. Contact support.', 'error');
          }
        } catch(e) {
          showAlert('Verification error. If debited, contact support with your tx ref: ' + txRef, 'error');
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Pay Securely with Flutterwave';
      },
      onclose: function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-lock"></i> Pay Securely with Flutterwave';
      }
    });
  }

  /* Sidebar toggle */
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
</script>
</body>
</html>