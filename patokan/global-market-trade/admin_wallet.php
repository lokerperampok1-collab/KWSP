<?php
require_once __DIR__ . "/config/app.php";
require_once __DIR__ . "/auth/admin_guard.php";
require_once __DIR__ . "/auth/_csrf.php";

require_admin_db($pdo); // pastikan admin guard pakai DB

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="utf-8">
  <title>Wallet Requests - Admin | Global Market Trade</title>
  <meta content="width=device-width, initial-scale=1" name="viewport">
  <link rel="stylesheet" href="bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="dist/css/AdminLTE.min.css">
  <link rel="stylesheet" href="dist/css/skins/skin-blue.min.css">
  <link rel="stylesheet" href="user/css/custom_ui.css">
</head>

<body class="hold-transition skin-blue sidebar-mini">
<div class="wrapper">
  <?php
    $activePage = basename($_SERVER['PHP_SELF'] ?? '');
    include __DIR__ . '/partials/member_header.php'; // kalau kamu punya partial admin sendiri nanti kita pindah
    include __DIR__ . '/partials/member_sidebar.php';
  ?>

  <div class="content-wrapper">
    <section class="content-header">
      <h1>Wallet Requests</h1>
    </section>

    <section class="content">
      <div class="box" style="border-radius:16px;">
        <div class="box-header with-border">
          <div class="row" style="display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
            <div class="col-sm-3">
              <select id="fltType" class="form-control">
                <option value="">All Types</option>
                <option value="deposit">Deposit</option>
                <option value="withdraw">Withdraw</option>
                <option value="transfer_out">Transfer</option>
              </select>
            </div>
            <div class="col-sm-3">
              <select id="fltStatus" class="form-control">
                <option value="all">All Status</option>
                <option value="pending" >Pending</option>
                <option value="approved">Approved</option>
                <option value="rejected">Rejected</option>
                <option value="void">Void</option>
              </select>
            </div>
            <div class="col-sm-4">
              <input id="fltQ" class="form-control" placeholder="Search name/email...">
            </div>
            <div class="col-sm-2">
              <button class="btn btn-primary btn-block" id="btnReload">Refresh</button>
            </div>
          </div>
        </div>

        <div class="box-body" style="overflow:auto;">
          <table class="table table-bordered table-striped" id="tblTx" style="min-width:980px;">
            <thead>
              <tr>
                <th>ID</th>
                <th>User</th>
                <th>Type</th>
                <th>Amount</th>
                <th>Status</th>
                <th>Created</th>
                <th>Note</th>
                <th>Admin Remark</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
          <div id="emptyHint" class="text-muted" style="padding:10px; display:none;">No requests.</div>
        </div>
      </div>
    </section>
  </div>
</div>

<script src="plugins/jQuery/jquery-2.2.3.min.js"></script>
<script src="bootstrap/js/bootstrap.min.js"></script>
<script>
const CSRF = <?= json_encode($csrf) ?>;

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m])); }

async function loadTx(){
  const type = document.getElementById('fltType').value;
  const status = document.getElementById('fltStatus').value;
  const q = document.getElementById('fltQ').value.trim();

  const url = new URL('admin/api/wallet_tx_list.php', window.location.origin + window.location.pathname.replace(/\/[^\/]*$/, '/') );
  if(type) url.searchParams.set('type', type);
  if(status) url.searchParams.set('status', status);
  if(q) url.searchParams.set('q', q);

  const res = await fetch(url.toString(), {credentials:'include'});
  const data = await res.json();

  const tb = document.querySelector('#tblTx tbody');
  tb.innerHTML = '';

  if(!data.ok){
    alert(data.error || 'Failed to load');
    return;
  }

  const rows = data.rows || [];
  document.getElementById('emptyHint').style.display = rows.length ? 'none' : 'block';

  for(const r of rows){
    const tr = document.createElement('tr');
    tr.innerHTML = `
      <td>#${esc(r.id)}</td>
      <td>
        <div style="font-weight:700">${esc(r.full_name || r.username || 'User')}</div>
        <div class="text-muted" style="font-size:12px">${esc(r.email || '')}</div>
      </td>
      <td>${esc(r.type)}</td>
      <td><?= $CUR ?> ${esc(r.amount)}</td>
      <td><span class="label label-${r.status==='pending'?'warning':r.status==='approved'?'success':r.status==='rejected'?'danger':'default'}">${esc(r.status)}</span></td>
      <td>${esc(r.created_at)}</td>
      <td class="text-muted">${esc(r.note || '')}</td>
      <td>
        <input class="form-control input-sm" placeholder="Remark..." data-remark="${esc(r.id)}" value="${esc(r.admin_remark || '')}">
      </td>
      <td style="min-width:210px">
        <button class="btn btn-xs btn-success" onclick="act(${r.id}, 'approved')">Approve</button>
        <button class="btn btn-xs btn-danger" onclick="act(${r.id}, 'rejected')">Reject</button>
        <button class="btn btn-xs btn-default" onclick="act(${r.id}, 'void')">Void</button>
      </td>
    `;
    tb.appendChild(tr);
  }
}

async function act(id, status){
  let remark = '';
  const inp = document.querySelector(`input[data-remark="${id}"]`);
  if(inp) remark = inp.value.trim();

  if(status === 'rejected' && !remark){
    alert('Reject requires a remark/reason.');
    return;
  }

  const fd = new FormData();
  fd.set('csrf', CSRF);
  fd.set('id', String(id));
  fd.set('status', status);
  fd.set('admin_remark', remark);

  const res = await fetch('admin/api/wallet_tx_update.php', {method:'POST', body: fd, credentials:'include'});
  const data = await res.json();
  if(!data.ok){
    alert(data.error || 'Action failed');
    return;
  }
  await loadTx();
}

document.getElementById('btnReload').addEventListener('click', loadTx);
document.getElementById('fltType').addEventListener('change', loadTx);
document.getElementById('fltStatus').addEventListener('change', loadTx);
document.getElementById('fltQ').addEventListener('keyup', (e)=>{ if(e.key==='Enter') loadTx(); });

loadTx();
</script>
</body>
</html>