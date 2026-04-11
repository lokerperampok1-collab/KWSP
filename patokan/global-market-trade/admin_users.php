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
  <title>Admin Users | Global Market Trade</title>

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="<?= $BASE ?>/assets/vendor_components/font-awesome/css/font-awesome.css">
  <link rel="stylesheet" href="<?= $BASE ?>/user/css/gmtd_member_v2.css">

  <style>
    body{font-family:'Plus Jakarta Sans',ui-sans-serif,system-ui,-apple-system,Segoe UI,Roboto,Arial,sans-serif}
    .gmtd-btn[disabled]{opacity:.45;cursor:not-allowed;filter:none;}
    .gmtd-btn--danger{background:rgba(255,92,122,.18);border-color:rgba(255,92,122,.35);}
    .gmtd-btn--warn{background:rgba(255,197,66,.16);border-color:rgba(255,197,66,.35);}
    .gmtd-btn--sm{padding:9px 10px;border-radius:14px;font-size:12px;}
    .gmtd-input--sm{padding:10px 10px;border-radius:14px;font-size:13px;}
    .gmtd-section{border-radius:22px;border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.18);box-shadow:0 18px 60px rgba(0,0,0,.35);padding:14px;}
    .gmtd-section h2{margin:0;font-size:14px;font-weight:1000;letter-spacing:.02em;}
    .gmtd-toolbar{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end;justify-content:space-between;margin-top:10px;}
    .gmtd-toolbar .left{display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end}
    .gmtd-toolbar .right{display:flex;gap:10px;flex-wrap:wrap;align-items:center;justify-content:flex-end}
    .gmtd-msg{display:none;margin:10px 0 0;padding:10px 12px;border-radius:16px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.06);font-weight:800;}
    .gmtd-msg.ok{display:block;border-color:rgba(31,225,168,.35);background:rgba(31,225,168,.10);color:#bff7e7;}
    .gmtd-msg.err{display:block;border-color:rgba(255,92,122,.35);background:rgba(255,92,122,.10);color:#ffd0da;}
    .gmtd-actions-inline{display:flex;flex-wrap:wrap;gap:8px;}
    .mono{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, 'Liberation Mono', 'Courier New', monospace;}

    .modal{position:fixed;left:0;top:0;right:0;bottom:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.65);z-index:9999}
    .modal.open{display:flex}
    .modal-card{width:min(760px,92vw);max-height:92vh;overflow:auto;background:rgba(20,20,24,.98);border:1px solid rgba(255,255,255,.12);border-radius:18px;padding:14px;box-shadow:0 30px 100px rgba(0,0,0,.55)}
    .modal-head{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:6px 6px 10px}
    .modal-head h3{margin:0;font-size:14px;font-weight:1000;letter-spacing:.02em}
    .modal-close{background:transparent;border:1px solid rgba(255,255,255,.14);color:#fff;border-radius:14px;padding:8px 10px;cursor:pointer}
    .modal-meta{margin:0 6px 10px;color:rgba(255,255,255,.75);font-weight:800;font-size:12px}
    .modal-grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    @media (max-width: 760px){.modal-grid{grid-template-columns:1fr}}
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
          <a href="<?= $BASE ?>/admin.php"><i class="fa fa-credit-card"></i> Wallet</a>
          <button type="button" onclick="confirmLogout()"><i class="fa fa-sign-out"></i> Logout</button>
        </div>
      </div>
    </header>

    <div class="gmtd-pagehead">
      <h1>Users</h1>
      <p>Manage members: profile, password, status, and balance.</p>
    </div>

    <div class="gmtd-actions" style="margin-bottom:10px;">
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin.php"><i class="fa fa-credit-card"></i> Wallet</a>
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin_users.php"><i class="fa fa-users"></i> Users</a>
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin_plans.php"><i class="fa fa-line-chart"></i> Plans</a>
      <a class="gmtd-btn gmtd-btn--ghost" href="<?= $BASE ?>/admin_kyc.php"><i class="fa fa-id-card"></i> KYC</a>
      <button class="gmtd-btn gmtd-btn--danger" type="button" onclick="confirmLogout()"><i class="fa fa-sign-out"></i> Logout</button>
    </div>

    <input type="hidden" id="csrfToken" value="<?= e($csrf) ?>">

    <section class="gmtd-section" aria-label="Users list">
      <div style="display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:flex-end;">
        <div>
          <h2>User List</h2>
          <p class="gmtd-help" style="margin:6px 0 0;">Search by name/email. Use <b>Profile</b>, <b>Balance</b>, <b>Reset</b>, <b>Disable</b>, or <b>Delete</b>.</p>
        </div>
      </div>

      <div class="gmtd-toolbar">
        <div class="left">
          <label style="display:grid;gap:6px;">
            <span class="gmtd-label" style="margin:0;">Search</span>
            <input id="q" class="gmtd-input gmtd-input--sm" style="width:min(360px, 84vw);" placeholder="name or email">
          </label>

          <label style="display:grid;gap:6px;">
            <span class="gmtd-label" style="margin:0;">Role</span>
            <select id="role" class="gmtd-select gmtd-input--sm" style="width:180px;">
              <option value="all">All</option>
              <option value="admin">Admin</option>
              <option value="member">Member</option>
            </select>
          </label>

          <label style="display:grid;gap:6px;">
            <span class="gmtd-label" style="margin:0;">Status</span>
            <select id="status" class="gmtd-select gmtd-input--sm" style="width:180px;">
              <option value="all">All</option>
              <option value="active">Active</option>
              <option value="disabled">Disabled</option>
            </select>
          </label>
        </div>

        <div class="right">
          <button class="gmtd-btn gmtd-btn--sm" type="button" id="btnReload"><i class="fa fa-refresh"></i> Reload</button>
        </div>
      </div>

      <div id="msg" class="gmtd-msg"></div>

      <div class="gmtd-divider"></div>

      <div class="gmtd-tablewrap">
        <table class="gmtd-table" style="min-width:1240px;">
          <thead>
            <tr>
              <th>ID</th>
              <th>Full name</th>
              <th>Email</th>
              <th>Admin</th>
              <th>Status</th>
              <th>Wallet</th>
              <th>Joined</th>
              <th style="min-width:340px;">Action</th>
            </tr>
          </thead>
          <tbody id="userTbody">
            <tr><td colspan="8" class="gmtd-td-muted">Loading...</td></tr>
          </tbody>
        </table>
      </div>

      <div class="gmtd-actions" style="margin-top:10px;">
        <button class="gmtd-btn gmtd-btn--sm" type="button" id="btnPrev"><i class="fa fa-chevron-left"></i> Prev</button>
        <button class="gmtd-btn gmtd-btn--sm" type="button" id="btnNext">Next <i class="fa fa-chevron-right"></i></button>
        <span class="gmtd-help" id="pageInfo" style="margin-left:auto;"></span>
      </div>

    </section>

  </div>


  <!-- PROFILE MODAL -->
  <div class="modal" id="modalProfile" role="dialog" aria-modal="true">
    <div class="modal-card">
      <div class="modal-head">
        <h3><i class="fa fa-user"></i> Edit Profile</h3>
        <button class="modal-close" type="button" onclick="closeModal('modalProfile')"><i class="fa fa-times"></i></button>
      </div>
      <div class="modal-meta" id="profileMeta"></div>

      <div class="modal-grid">
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Full name</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_full_name" placeholder="Full name">
        </label>
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Email</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_email" placeholder="Email">
        </label>

        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Phone</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_phone" placeholder="Phone">
        </label>
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Admin</span>
          <select class="gmtd-select gmtd-input--sm" id="pf_is_admin">
            <option value="0">No</option>
            <option value="1">Yes</option>
          </select>
        </label>

        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Country code</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_country_code" placeholder="MY">
        </label>
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Country name</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_country_name" placeholder="Malaysia">
        </label>

        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Currency code (wallet key)</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_currency_code" placeholder="MYR / USD / SAR">
        </label>
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Currency symbol</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_currency_symbol" placeholder="RM">
        </label>

        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Bank name</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_bank_name" placeholder="Bank name">
        </label>
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Bank account</span>
          <input class="gmtd-input gmtd-input--sm" id="pf_bank_account" placeholder="Account number">
        </label>
      </div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:12px 6px 0;">
        <label class="gmtd-check" style="margin:0;"><input type="checkbox" id="pf_unlock_bank"> Unlock bank info (allow change)</label>
        <span class="gmtd-help" id="pf_bank_lock_hint"></span>
      </div>

      <div class="gmtd-actions" style="margin-top:12px;">
        <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="closeModal('modalProfile')">Cancel</button>
        <button class="gmtd-btn gmtd-btn--primary gmtd-btn--sm" type="button" id="pf_save_btn" onclick="saveProfile()"><i class="fa fa-save"></i> Save profile</button>
      </div>
    </div>
  </div>


  <!-- BALANCE / PROFIT MODAL -->
  <div class="modal" id="modalBalance" role="dialog" aria-modal="true">
    <div class="modal-card">
      <div class="modal-head">
        <h3><i class="fa fa-money"></i> Adjust Profit</h3>
        <button class="modal-close" type="button" onclick="closeModal('modalBalance')"><i class="fa fa-times"></i></button>
      </div>
      <div class="modal-meta" id="balMeta"></div>

      <div class="modal-grid">
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Operation</span>
          <select class="gmtd-select gmtd-input--sm" id="bal_op">
            <option value="add">Add (+)</option>
            <option value="sub">Subtract (-)</option>
          </select>
        </label>

        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Amount</span>
          <input class="gmtd-input gmtd-input--sm" id="bal_amount" placeholder="0.00">
        </label>

        <label style="display:grid;gap:6px;grid-column:1 / -1;">
          <span class="gmtd-label" style="margin:0;">Note (optional)</span>
          <input class="gmtd-input gmtd-input--sm" id="bal_note" placeholder="e.g. Manual correction">
        </label>

        <div style="grid-column:1 / -1;" class="gmtd-help">This action will update the user's <b>Profit</b> and immediately increase/decrease the wallet balance.</div>
      </div>

      <div class="gmtd-actions" style="margin-top:12px;">
        <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="closeModal('modalBalance')">Cancel</button>
        <button class="gmtd-btn gmtd-btn--primary gmtd-btn--sm" type="button" id="bal_save_btn" onclick="saveBalance()"><i class="fa fa-check"></i> Apply</button>
      </div>
    </div>
  </div>


  <!-- RESET PASSWORD MODAL -->
  <div class="modal" id="modalReset" role="dialog" aria-modal="true">
    <div class="modal-card">
      <div class="modal-head">
        <h3><i class="fa fa-key"></i> Reset Password</h3>
        <button class="modal-close" type="button" onclick="closeModal('modalReset')"><i class="fa fa-times"></i></button>
      </div>
      <div class="modal-meta" id="resetMeta"></div>

      <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center;margin:0 6px 10px;">
        <label class="gmtd-check" style="margin:0;"><input type="checkbox" id="rp_generate" checked> Generate random password</label>
        <span class="gmtd-help">If unchecked, you can set a custom password.</span>
      </div>

      <div class="modal-grid" id="rp_fields" style="display:none;">
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">New password</span>
          <input class="gmtd-input gmtd-input--sm" id="rp_pass" type="text" placeholder="Min 6 characters">
        </label>
        <label style="display:grid;gap:6px;">
          <span class="gmtd-label" style="margin:0;">Confirm</span>
          <input class="gmtd-input gmtd-input--sm" id="rp_pass2" type="text" placeholder="Confirm">
        </label>
      </div>

      <div style="margin:10px 6px 0;display:none;" id="rp_result">
        <div class="gmtd-help" style="margin:0 0 6px;">New password (copy & send to user):</div>
        <div style="display:flex;gap:8px;align-items:center;">
          <input class="gmtd-input gmtd-input--sm mono" id="rp_newpass" readonly style="flex:1;">
          <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="copyNewPass()"><i class="fa fa-copy"></i> Copy</button>
        </div>
      </div>

      <div class="gmtd-actions" style="margin-top:12px;">
        <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="closeModal('modalReset')">Close</button>
        <button class="gmtd-btn gmtd-btn--warn gmtd-btn--sm" type="button" id="rp_save_btn" onclick="doResetPassword()"><i class="fa fa-refresh"></i> Reset</button>
      </div>
    </div>
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
    const state = { page: 1, limit: 25, total: 0, byId: {}, activeId: null };

    function esc(s){
      return String(s ?? '').replace(/[&<>\"]+/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[m] || m));
    }

    function setMsg(kind, text){
      const el = document.getElementById('msg');
      el.className = 'gmtd-msg';
      el.textContent = '';
      if(!text) return;
      el.textContent = text;
      el.classList.add(kind === 'ok' ? 'ok' : 'err');
    }

    function pageInfo(){
      const totalPages = Math.max(1, Math.ceil((state.total || 0) / state.limit));
      document.getElementById('pageInfo').textContent = `Page ${state.page} / ${totalPages} — ${state.total} users`;
      document.getElementById('btnPrev').disabled = state.page <= 1;
      document.getElementById('btnNext').disabled = state.page >= totalPages;
    }

    function badgeStatus(isDisabled){
      if (String(isDisabled) === '1') return '<span class="gmtd-badge gmtd-badge--bad"><i class="fa fa-ban"></i> Disabled</span>';
      return '<span class="gmtd-badge gmtd-badge--ok"><i class="fa fa-check"></i> Active</span>';
    }

    function formatMoney(cur, amt){
      const a = (amt ?? '0.00');
      return `<span class="mono">${esc(cur || 'RM')} ${esc(a)}</span>`;
    }

    async function loadUsers(){
      setMsg('', '');
      const tbody = document.getElementById('userTbody');
      tbody.innerHTML = '<tr><td colspan="8" class="gmtd-td-muted">Loading...</td></tr>';

      const q = document.getElementById('q').value || '';
      const role = document.getElementById('role').value || 'all';
      const status = document.getElementById('status').value || 'all';

      const qs = new URLSearchParams();
      if (q.trim()) qs.set('q', q.trim());
      qs.set('role', role);
      qs.set('status', status);
      qs.set('page', String(state.page));
      qs.set('limit', String(state.limit));

      try{
        const res = await fetch(`${BASE}/admin/api/users_list.php?` + qs.toString(), { credentials: 'include' });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Failed');

        state.total = Number(data.total || 0);
        pageInfo();

        const items = data.items || [];
        state.byId = {};
        items.forEach(u => { state.byId[String(u.id)] = u; });

        if (!items.length){
          tbody.innerHTML = '<tr><td colspan="8" class="gmtd-td-muted">No users found</td></tr>';
          return;
        }

        tbody.innerHTML = items.map(u => {
          const id = u.id;
          const nameId = `u_name_${id}`;
          const emailId = `u_email_${id}`;
          const adminId = `u_admin_${id}`;
          const btnId = `u_btn_${id}`;

          const joined = `<span class="gmtd-td-muted mono">${esc(u.created_at || '')}</span>`;
          const statusHtml = badgeStatus(u.is_disabled);
          const bal = formatMoney(u.wallet_currency, u.wallet_balance);
          const toggleLabel = String(u.is_disabled)==='1' ? 'Enable' : 'Disable';
          const toggleIcon  = String(u.is_disabled)==='1' ? 'fa-unlock' : 'fa-ban';

          return `
            <tr>
              <td class="mono">#${esc(id)}</td>
              <td><input class="gmtd-input gmtd-input--sm" id="${nameId}" value="${esc(u.full_name || '')}"></td>
              <td><input class="gmtd-input gmtd-input--sm" id="${emailId}" value="${esc(u.email || '')}"></td>
              <td>
                <select class="gmtd-select gmtd-input--sm" id="${adminId}" style="width:140px;">
                  <option value="0" ${String(u.is_admin)==='0' ? 'selected' : ''}>No</option>
                  <option value="1" ${String(u.is_admin)==='1' ? 'selected' : ''}>Yes</option>
                </select>
              </td>
              <td>${statusHtml}</td>
              <td>${bal}</td>
              <td>${joined}</td>
              <td>
                <div class="gmtd-actions-inline">
                  <button class="gmtd-btn gmtd-btn--primary gmtd-btn--sm" type="button" id="${btnId}" onclick="saveUser(${id})"><i class="fa fa-save"></i> Save</button>
                  <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="openProfile(${id})"><i class="fa fa-user"></i> Profile</button>
                  <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="openBalance(${id})"><i class="fa fa-money"></i> Balance</button>
                  <button class="gmtd-btn gmtd-btn--warn gmtd-btn--sm" type="button" onclick="openReset(${id})"><i class="fa fa-key"></i> Reset</button>
                  <button class="gmtd-btn gmtd-btn--sm" type="button" onclick="toggleDisable(${id})"><i class="fa ${toggleIcon}"></i> ${toggleLabel}</button>
                  <button class="gmtd-btn gmtd-btn--danger gmtd-btn--sm" type="button" onclick="deleteUser(${id})"><i class="fa fa-trash"></i> Delete</button>
                </div>
              </td>
            </tr>
          `;
        }).join('');

      } catch(e){
        tbody.innerHTML = `<tr><td colspan="8" style="color:#ffb3b3;">Error: ${esc(e.message)}</td></tr>`;
      }
    }

    async function saveUser(id){
      setMsg('', '');
      const csrf = document.getElementById('csrfToken').value;
      const btn = document.getElementById(`u_btn_${id}`);

      const full_name = document.getElementById(`u_name_${id}`).value;
      const email = document.getElementById(`u_email_${id}`).value;
      const is_admin = document.getElementById(`u_admin_${id}`).value;

      const form = new FormData();
      form.append('csrf', csrf);
      form.append('user_id', String(id));
      form.append('full_name', full_name);
      form.append('email', email);
      form.append('is_admin', is_admin);

      const prevText = btn ? btn.innerHTML : '';
      if (btn){ btn.disabled = true; btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving'; }

      try{
        const res = await fetch(`${BASE}/admin/api/users_update.php`, { method:'POST', credentials:'include', body: form });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Update failed');
        setMsg('ok', `Saved user #${id}`);
        await loadUsers();
      }catch(e){
        setMsg('err', e.message);
      }finally{
        if (btn){ btn.disabled = false; btn.innerHTML = prevText; }
      }
    }

    function openModal(id){
      const el = document.getElementById(id);
      if (el) el.classList.add('open');
    }
    function closeModal(id){
      const el = document.getElementById(id);
      if (el) el.classList.remove('open');
    }
    window.closeModal = closeModal;

    // Close modals on backdrop click
    ['modalProfile','modalBalance','modalReset'].forEach(mid => {
      const m = document.getElementById(mid);
      if(!m) return;
      m.addEventListener('click', (e)=>{ if(e.target === m) closeModal(mid); });
    });

    // ----- Profile -----
    function openProfile(id){
      const u = state.byId[String(id)];
      if(!u) return;
      state.activeId = id;
      document.getElementById('profileMeta').innerHTML = `<b>${esc(u.full_name||'')}</b> (${esc(u.email||'')}) • ID #${esc(u.id)}`;

      document.getElementById('pf_full_name').value = u.full_name || '';
      document.getElementById('pf_email').value = u.email || '';
      document.getElementById('pf_phone').value = u.phone || '';
      document.getElementById('pf_country_code').value = u.country_code || '';
      document.getElementById('pf_country_name').value = u.country_name || '';
      document.getElementById('pf_currency_code').value = u.currency_code || '';
      document.getElementById('pf_currency_symbol').value = u.currency_symbol || '';
      document.getElementById('pf_bank_name').value = u.bank_name || '';
      document.getElementById('pf_bank_account').value = u.bank_account || '';
      document.getElementById('pf_is_admin').value = String(u.is_admin||0);

      const locked = !!u.bank_locked_at;
      document.getElementById('pf_unlock_bank').checked = !locked;
      document.getElementById('pf_bank_lock_hint').textContent = locked ? `Bank locked at: ${u.bank_locked_at}` : 'Bank editable.';

      openModal('modalProfile');
    }
    window.openProfile = openProfile;

    async function saveProfile(){
      setMsg('', '');
      const id = state.activeId;
      if(!id) return;
      const csrf = document.getElementById('csrfToken').value;
      const btn = document.getElementById('pf_save_btn');

      const form = new FormData();
      form.append('csrf', csrf);
      form.append('user_id', String(id));
      form.append('full_name', document.getElementById('pf_full_name').value);
      form.append('email', document.getElementById('pf_email').value);
      form.append('phone', document.getElementById('pf_phone').value);
      form.append('country_code', document.getElementById('pf_country_code').value);
      form.append('country_name', document.getElementById('pf_country_name').value);
      form.append('currency_code', document.getElementById('pf_currency_code').value);
      form.append('currency_symbol', document.getElementById('pf_currency_symbol').value);
      form.append('bank_name', document.getElementById('pf_bank_name').value);
      form.append('bank_account', document.getElementById('pf_bank_account').value);
      form.append('is_admin', document.getElementById('pf_is_admin').value);

      // If unchecked => keep locked (1). If checked => unlock (0).
      const unlock = document.getElementById('pf_unlock_bank').checked;
      form.append('bank_locked', unlock ? '0' : '1');

      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Saving';

      try{
        const res = await fetch(`${BASE}/admin/api/users_profile_update.php`, { method:'POST', credentials:'include', body: form });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Update failed');
        closeModal('modalProfile');
        setMsg('ok', `Profile updated for user #${id}`);
        await loadUsers();
      }catch(e){
        setMsg('err', e.message);
      }finally{
        btn.disabled = false;
        btn.innerHTML = prev;
      }
    }
    window.saveProfile = saveProfile;


    // ----- Balance -----
    function openBalance(id){
      const u = state.byId[String(id)];
      if(!u) return;
      state.activeId = id;
      document.getElementById('balMeta').innerHTML = `<b>${esc(u.full_name||'')}</b> (${esc(u.email||'')}) • Current: <span class="mono">${esc(u.wallet_currency||'RM')} ${esc(u.wallet_balance||'0.00')}</span>`;
      document.getElementById('bal_op').value = 'add';
      document.getElementById('bal_amount').value = '';
      document.getElementById('bal_note').value = '';
      openModal('modalBalance');
    }
    window.openBalance = openBalance;

    async function saveBalance(){
      setMsg('', '');
      const id = state.activeId;
      if(!id) return;
      const csrf = document.getElementById('csrfToken').value;
      const btn = document.getElementById('bal_save_btn');

      const form = new FormData();
      form.append('csrf', csrf);
      form.append('user_id', String(id));
      form.append('op', document.getElementById('bal_op').value);
      form.append('amount', document.getElementById('bal_amount').value);
      form.append('note', document.getElementById('bal_note').value);

      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Applying';

      try{
        const res = await fetch(`${BASE}/admin/api/users_balance_adjust.php`, { method:'POST', credentials:'include', body: form });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Adjust failed');
        closeModal('modalBalance');
        setMsg('ok', `Balance updated: ${data.currency} ${data.new_balance} (delta ${data.delta})`);
        await loadUsers();
      }catch(e){
        setMsg('err', e.message);
      }finally{
        btn.disabled = false;
        btn.innerHTML = prev;
      }
    }
    window.saveBalance = saveBalance;


    // ----- Reset password -----
    function openReset(id){
      const u = state.byId[String(id)];
      if(!u) return;
      state.activeId = id;
      document.getElementById('resetMeta').innerHTML = `<b>${esc(u.full_name||'')}</b> (${esc(u.email||'')}) • ID #${esc(u.id)}`;
      document.getElementById('rp_generate').checked = true;
      document.getElementById('rp_fields').style.display = 'none';
      document.getElementById('rp_pass').value = '';
      document.getElementById('rp_pass2').value = '';
      document.getElementById('rp_result').style.display = 'none';
      document.getElementById('rp_newpass').value = '';
      openModal('modalReset');
    }
    window.openReset = openReset;

    document.getElementById('rp_generate').addEventListener('change', function(){
      const gen = document.getElementById('rp_generate').checked;
      document.getElementById('rp_fields').style.display = gen ? 'none' : 'grid';
    });

    async function doResetPassword(){
      setMsg('', '');
      const id = state.activeId;
      if(!id) return;
      const csrf = document.getElementById('csrfToken').value;
      const btn = document.getElementById('rp_save_btn');
      const gen = document.getElementById('rp_generate').checked;

      let pass = '';
      if (!gen) {
        pass = document.getElementById('rp_pass').value;
        const pass2 = document.getElementById('rp_pass2').value;
        if (!pass || pass.length < 6) { setMsg('err','Password must be at least 6 characters'); return; }
        if (pass !== pass2) { setMsg('err','Password confirmation does not match'); return; }
      }

      const form = new FormData();
      form.append('csrf', csrf);
      form.append('user_id', String(id));
      form.append('mode', gen ? 'generate' : 'manual');
      if (!gen) form.append('password', pass);

      const prev = btn.innerHTML;
      btn.disabled = true;
      btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Resetting';

      try{
        const res = await fetch(`${BASE}/admin/api/users_password_reset.php`, { method:'POST', credentials:'include', body: form });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Reset failed');
        document.getElementById('rp_newpass').value = data.new_password || '';
        document.getElementById('rp_result').style.display = 'block';
        setMsg('ok', `Password reset for user #${id}. Copy and send the new password to the user.`);
      }catch(e){
        setMsg('err', e.message);
      }finally{
        btn.disabled = false;
        btn.innerHTML = prev;
      }
    }
    window.doResetPassword = doResetPassword;

    function copyNewPass(){
      const el = document.getElementById('rp_newpass');
      el.select();
      el.setSelectionRange(0, 99999);
      try{ document.execCommand('copy'); }catch(e){}
    }
    window.copyNewPass = copyNewPass;


    // ----- Disable/Enable -----
    async function toggleDisable(id){
      const u = state.byId[String(id)];
      if(!u) return;
      const toDisabled = String(u.is_disabled) !== '1';
      const label = toDisabled ? 'disable' : 'enable';
      if(!confirm(`Are you sure you want to ${label} user #${id}?`)) return;

      setMsg('', '');
      const csrf = document.getElementById('csrfToken').value;
      const form = new FormData();
      form.append('csrf', csrf);
      form.append('user_id', String(id));
      form.append('is_disabled', toDisabled ? '1' : '0');

      try{
        const res = await fetch(`${BASE}/admin/api/users_disable.php`, { method:'POST', credentials:'include', body: form });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Failed');
        setMsg('ok', `User #${id} ${toDisabled ? 'disabled' : 'enabled'}`);
        await loadUsers();
      }catch(e){
        setMsg('err', e.message);
      }
    }
    window.toggleDisable = toggleDisable;

    // ----- Delete -----
    async function deleteUser(id){
      const u = state.byId[String(id)];
      if(!u) return;
      if(!confirm(`DELETE user #${id}? This will remove user and related records (wallet tx, kyc, notifications).`)) return;
      if(!confirm('Final confirmation: this cannot be undone. Continue?')) return;

      setMsg('', '');
      const csrf = document.getElementById('csrfToken').value;
      const form = new FormData();
      form.append('csrf', csrf);
      form.append('user_id', String(id));

      try{
        const res = await fetch(`${BASE}/admin/api/users_delete.php`, { method:'POST', credentials:'include', body: form });
        const data = await res.json();
        if(!data.ok) throw new Error(data.error || 'Delete failed');
        setMsg('ok', `User #${id} deleted`);
        await loadUsers();
      }catch(e){
        setMsg('err', e.message);
      }
    }
    window.deleteUser = deleteUser;


    // Debounced search
    let t = null;
    document.getElementById('q').addEventListener('input', function(){
      clearTimeout(t);
      t = setTimeout(function(){ state.page = 1; loadUsers(); }, 250);
    });
    document.getElementById('role').addEventListener('change', function(){ state.page = 1; loadUsers(); });
    document.getElementById('status').addEventListener('change', function(){ state.page = 1; loadUsers(); });
    document.getElementById('btnReload').addEventListener('click', function(){ loadUsers(); });

    document.getElementById('btnPrev').addEventListener('click', function(){
      if (state.page > 1){ state.page -= 1; loadUsers(); }
    });
    document.getElementById('btnNext').addEventListener('click', function(){
      state.page += 1; loadUsers();
    });

    loadUsers();
  </script>
</body>
</html>
