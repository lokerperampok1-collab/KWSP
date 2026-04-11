<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/auth/admin_guard.php"; // DB-based admin-only gate
require_once __DIR__ . "/auth/_csrf.php";

$BASE = "";

$uid = (int)($_SESSION["user_id"] ?? 0);
$stmt = $pdo->prepare("SELECT id, full_name, email FROM users WHERE id=? LIMIT 1");
$stmt->execute([$uid]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);

$displayName = $me ? (string)$me["full_name"] : ("User #".$uid);
$displayEmail = $me ? (string)$me["email"] : "";

$csrf = csrf_token();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no">
  <title>Admin | Global Market Trade</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= $BASE ?>/assets/vendor_components/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="<?= $BASE ?>/user/css/gmtd_member_v2.css">

  <style>
    body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .gmtd-btn[disabled]{opacity:.45;cursor:not-allowed;filter:none;}
    .gmtd-btn--danger{background:rgba(255,92,122,.18);border-color:rgba(255,92,122,.35);}
    .gmtd-btn--ok{background:rgba(31,225,168,.14);border-color:rgba(31,225,168,.35);}
    .gmtd-btn--warn{background:rgba(255,204,102,.14);border-color:rgba(255,204,102,.35);}
    .gmtd-btn--sm{padding:9px 10px;border-radius:14px;font-size:12px;}
    .gmtd-input--sm{padding:10px 10px;border-radius:14px;font-size:13px;}
    .gmtd-section{border-radius:22px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.18);box-shadow:0 18px 60px rgba(0,0,0,.35);padding:14px;}
    .gmtd-section h2{margin:0;font-size:14px;font-weight:1000;letter-spacing:.02em;}
    .gmtd-toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:space-between;margin-top:10px;}
    .gmtd-toolbar .left{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .gmtd-toolbar .right{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:flex-end}
    .gmtd-chip{display:inline-flex;align-items:center;gap:8px;padding:8px 12px;border-radius:999px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);font-weight:900;font-size:12px;color:rgba(238,246,255,.86);}
    .gmtd-chip i{opacity:.85}
  </style>

  <script>
    function confirmLogout(){
      if (confirm("Logout? You will be signed out.")) window.location.href = "<?= $BASE ?>/logout.php";
    }
  </script>
</head>

<body>
  <div class="gmtd-app">

    <header class="gmtd-top">
      <a class="gmtd-brand" href="<?= $BASE ?>/dashboard.php">
        <img src="<?= $BASE ?>/myasset/image/logo.png" alt="Global Market Trade">
        <div>
          <b>Global Market Trade</b>
          <span>Admin panel</span>
        </div>
      </a>

      <div class="gmtd-user">
        <button class="gmtd-userbtn" id="userMenuBtn" type="button" aria-haspopup="true" aria-expanded="false">
          <span class="gmtd-username"><?= e($displayName) ?></span>
          <i class="fa fa-chevron-down"></i>
        </button>
        <div class="gmtd-menu" id="userMenu" role="menu" aria-label="User menu">
          <a href="<?= $BASE ?>/dashboard.php"><i class="fa fa-home"></i> Dashboard</a>
          <a href="<?= $BASE ?>/profile.php"><i class="fa fa-user"></i> Profile</a>
          <a href="<?= $BASE ?>/admin_users.php"><i class="fa fa-users"></i> Users</a>
          <button type="button" onclick="confirmLogout()"><i class="fa fa-sign-out"></i> Logout</button>
        </div>
      </div>
    </header>

    <div class="gmtd-pagehead">
      <h1>Admin</h1>
      <p>Logged in as <b><?= e($displayEmail !== '' ? $displayEmail : $displayName) ?></b></p>
    </div>

    <div class="gmtd-actions" style="margin-bottom:10px;">
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin.php"><i class="fa fa-credit-card"></i> Wallet</a>
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin_users.php"><i class="fa fa-users"></i> Users</a>
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin_plans.php"><i class="fa fa-line-chart"></i> Plans</a>
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin_kyc.php"><i class="fa fa-id-card"></i> KYC</a>
      <button class="gmtd-btn gmtd-btn--danger" type="button" onclick="confirmLogout()"><i class="fa fa-sign-out"></i> Logout</button>
    </div>

    <input type="hidden" id="csrfToken" value="<?= e($csrf) ?>">

    <section class="gmtd-section" aria-label="Wallet requests">
      <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div>
          <h2>Wallet Requests</h2>
          <p class="gmtd-help" style="margin:6px 0 0;">Approve / reject pending deposits & withdrawals.</p>
        </div>
        <div class="gmtd-chip"><i class="fa fa-clock-o"></i> Live</div>
      </div>

      <div class="gmtd-toolbar">
        <div class="left">
          <label style="display:grid;gap:6px;">
            <span class="gmtd-label" style="margin:0;">Type</span>
            <select id="filterType" class="gmtd-select gmtd-input--sm" style="width:220px;">
              <option value="">All</option>
              <option value="deposit">Deposit</option>
              <option value="withdraw">Withdraw</option>
            </select>
          </label>

          <label style="display:grid;gap:6px;">
            <span class="gmtd-label" style="margin:0;">Status</span>
            <select id="filterStatus" class="gmtd-select gmtd-input--sm" style="width:220px;">
              <option value="all">All</option>
              <option value="pending">Pending</option>
              <option value="approved">Approved</option>
              <option value="rejected">Rejected</option>
              <option value="void">Void</option>
            </select>
          </label>
        </div>

        <div class="right">
          <button class="gmtd-btn gmtd-btn--warn gmtd-btn--sm" id="btnRefresh" type="button"><i class="fa fa-refresh"></i> Refresh</button>
        </div>
      </div>

      <div class="gmtd-divider"></div>

      <div class="gmtd-tablewrap">
        <table class="gmtd-table">
          <thead>
            <tr>
              <th>ID</th>
              <th>User</th>
              <th>Type</th>
              <th>Amount</th>
              <th>Status</th>
              <th>Created</th>
              <th style="min-width:340px;">Action</th>
            </tr>
          </thead>
          <tbody id="txTbody">
            <tr><td colspan="7" class="gmtd-td-muted">Loading...</td></tr>
          </tbody>
        </table>
      </div>

      <p class="gmtd-help" style="margin-top:10px;">Tip: you can add an optional admin note before approving/rejecting.</p>
    </section>

  </div>

  <script>
    // User menu toggle
    (function(){
      var btn = document.getElementById('userMenuBtn');
      var menu = document.getElementById('userMenu');
      if(!btn || !menu) return;
      function close(){ menu.style.display='none'; btn.setAttribute('aria-expanded','false'); }
      btn.addEventListener('click', function(){
        var open = menu.style.display === 'block';
        menu.style.display = open ? 'none' : 'block';
        btn.setAttribute('aria-expanded', open ? 'false' : 'true');
      });
      document.addEventListener('click', function(e){
        if(!menu.contains(e.target) && !btn.contains(e.target)) close();
      });
    })();

    const BASE = <?= json_encode($BASE) ?>;

    function badge(status){
      const s = String(status || '').toLowerCase();
      if (s === 'approved') return '<span class="gmtd-badge gmtd-badge--ok">approved</span>';
      if (s === 'rejected') return '<span class="gmtd-badge gmtd-badge--bad">rejected</span>';
      if (s === 'void') return '<span class="gmtd-badge">void</span>';
      return '<span class="gmtd-badge gmtd-badge--pending">pending</span>';
    }

    function esc(s){
      return String(s ?? '').replace(/[&<>"']/g, m => ({
        '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
      }[m]));
    }

    async function loadTx(){
      const tbody = document.getElementById('txTbody');
      const status = document.getElementById('filterStatus').value;
      const type = document.getElementById('filterType').value;

      tbody.innerHTML = '<tr><td colspan="7" class="gmtd-td-muted">Loading...</td></tr>';

      const qs = new URLSearchParams();
      qs.set('status', status);
      if (type) qs.set('type', type);

      const url = `${BASE}/admin/api/wallet_tx_list.php?` + qs.toString();

      try{
        const res = await fetch(url, { credentials: 'include' });
        const data = await res.json();
        if (!data.ok) throw new Error(data.error || 'Failed');

        const items = data.items || [];
        if (!items.length){
          tbody.innerHTML = '<tr><td colspan="7" class="gmtd-td-muted">No data</td></tr>';
          return;
        }

        tbody.innerHTML = items.map(row => {
          const id = row.id;
          const user = `${esc(row.full_name || '')}<div class="gmtd-td-muted" style="margin-top:4px;font-size:12px;">${esc(row.email || '')}</div>`;
          const typeTxt = `<b>${esc(row.type)}</b><div class="gmtd-td-muted" style="margin-top:4px;font-size:12px;">${esc(row.currency)}</div>`;
          const amount = `<b>${esc(row.amount)}</b>`;
          const statusHtml = badge(row.status);
          const created = `<span class="gmtd-td-muted" style="font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;">${esc(row.created_at)}</span>`;

          const canAct = String(row.status).toLowerCase() === 'pending';
          const noteId = `note_${id}`;

          return `
            <tr>
              <td>#${esc(id)}</td>
              <td>${user}</td>
              <td>${typeTxt}</td>
              <td>${amount}</td>
              <td>${statusHtml}</td>
              <td>${created}</td>
              <td>
                <div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;">
                  <input class="gmtd-input gmtd-input--sm" id="${noteId}" placeholder="Admin note (optional)" style="flex:1;min-width:180px;">
                  <button class="gmtd-btn gmtd-btn--ok gmtd-btn--sm" ${canAct ? '' : 'disabled'} onclick="txUpdate(${id},'approve','${noteId}')"><i class="fa fa-check"></i> Approve</button>
                  <button class="gmtd-btn gmtd-btn--danger gmtd-btn--sm" ${canAct ? '' : 'disabled'} onclick="txUpdate(${id},'reject','${noteId}')"><i class="fa fa-times"></i> Reject</button>
                  <button class="gmtd-btn gmtd-btn--sm" ${canAct ? '' : 'disabled'} onclick="txUpdate(${id},'void','${noteId}')"><i class="fa fa-ban"></i> Void</button>
                </div>
                ${row.note ? `<div class="gmtd-td-muted" style="margin-top:6px;font-size:12px;">Note: ${esc(row.note)}</div>` : ``}
              </td>
            </tr>
          `;
        }).join('');

      } catch(e){
        tbody.innerHTML = `<tr><td colspan="7" style="color:#ffb3b3;">Error: ${esc(e.message)}</td></tr>`;
      }
    }

    async function txUpdate(id, action, noteInputId){
      const csrf = document.getElementById('csrfToken').value;
      const note = document.getElementById(noteInputId)?.value || '';

      if (!confirm(`Confirm ${action.toUpperCase()} TX #${id}?`)) return;

      const form = new FormData();
      form.append('csrf', csrf);
      form.append('tx_id', String(id));
      form.append('action', action);
      if (note.trim()) form.append('admin_note', note.trim());

      try{
        const res = await fetch(`${BASE}/admin/api/wallet_tx_update.php`, {
          method: 'POST',
          credentials: 'include',
          body: form
        });
        const data = await res.json();

        if (!data.ok) throw new Error(data.error || 'Update failed');
        alert(`OK: TX #${id} -> ${data.status}`);

        loadTx();
      } catch(e){
        alert('Error: ' + e.message);
      }
    }

    document.getElementById('btnRefresh').addEventListener('click', loadTx);
    document.getElementById('filterStatus').addEventListener('change', loadTx);
    document.getElementById('filterType').addEventListener('change', loadTx);

    loadTx();
  </script>

</body>
</html>
