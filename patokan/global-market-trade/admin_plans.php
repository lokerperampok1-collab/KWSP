<?php
declare(strict_types=1);

session_start();

require_once __DIR__ . "/config/db.php";
require_once __DIR__ . "/auth/admin_guard.php";
require_once __DIR__ . "/auth/_csrf.php";

$BASE = "";
$uid = (int)($_SESSION["user_id"] ?? 0);
$stmt = $pdo->prepare("SELECT id, email, is_admin FROM users WHERE id=? LIMIT 1");
$stmt->execute([$uid]);
$me = $stmt->fetch(PDO::FETCH_ASSOC);
$display = $me["email"] ?? ("User #".$uid);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>Investment Plans - Admin | Global Market Trade</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link href="https://fonts.googleapis.com/css?family=Roboto:400,700|Open+Sans:400,700" rel="stylesheet">
  <link rel="stylesheet" href="myasset/css/style.css">
  <style>
    .admin-wrap{min-height:100vh;padding:20px;}
    .admin-card{max-width:1100px;margin:0 auto;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);border-radius:14px;padding:18px;box-shadow:var(--shadow);}
    .muted{color:#b9bcc8;}
    .toprow{display:flex;justify-content:space-between;align-items:center;gap:10px;flex-wrap:wrap;}
    .btn{display:inline-block;padding:10px 14px;border-radius:10px;border:1px solid rgba(255,255,255,.12);background:rgba(255,255,255,.08);color:#fff;text-decoration:none;cursor:pointer;font-family:Roboto,sans-serif;font-weight:700;}
    .btn:hover{background:rgba(255,255,255,.12)}
    .btn-danger{background:rgba(255,40,40,.22);border-color:rgba(255,40,40,.35)}
    .btn-okay{background:rgba(0,200,120,.18);border-color:rgba(0,200,120,.28)}
    .btn-warn{background:rgba(255,160,0,.18);border-color:rgba(255,160,0,.28)}
    .panel{border:1px solid rgba(255,255,255,.10);background:rgba(0,0,0,.15);border-radius:14px;padding:14px;margin-top:14px;}
    table{width:100%;border-collapse:collapse;margin-top:10px}
    th,td{padding:10px;border-bottom:1px solid rgba(255,255,255,.10);vertical-align:top}
    th{font-size:12px;text-transform:uppercase;letter-spacing:.06em;color:#b9bcc8;text-align:left}
    td{font-size:14px}
    .mono{font-family:ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace}
    .input{width:100%;padding:10px 12px;border-radius:10px;background:rgba(255,255,255,.06);border:1px solid rgba(255,255,255,.10);color:#fff;outline:none;}
    .actions{display:flex;gap:8px;flex-wrap:wrap}
    .modal{position:fixed;left:0;top:0;right:0;bottom:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.65);z-index:9999}
    .modal.open{display:flex}
    .modal-card{width:min(680px,92vw);background:rgba(20,20,24,.98);border:1px solid rgba(255,255,255,.12);border-radius:14px;padding:14px}
    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    @media(max-width:720px){.grid2{grid-template-columns:1fr}}
  </style>
</head>
<body>
  <div class="admin-wrap">
    <div class="admin-card">
      <div class="toprow">
        <div>
          <h2 style="margin:0;font-family:Roboto,sans-serif;font-weight:700;">Investment Plans</h2>
          <div class="muted">Logged in as <b><?= htmlspecialchars((string)$display) ?></b></div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
          <a class="btn" href="<?= $BASE ?>/admin.php">Admin</a>
          <a class="btn btn-danger" href="<?= $BASE ?>/logout.php">Logout</a>
        </div>
      </div>

      <input type="hidden" id="csrfToken" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8'); ?>">

      <div class="panel">
        <div class="actions" style="justify-content:space-between;align-items:center;">
          <div class="muted">Manage plan list shown in member <span class="mono">investment.php</span></div>
          <button class="btn btn-okay" id="btnAdd">+ Add Plan</button>
        </div>

        <div style="overflow:auto;">
          <table>
            <thead>
              <tr>
                <th>ID</th><th>Name</th><th>Min</th><th>Max</th><th>ROI/hour</th><th>Hours</th><th>Status</th><th>Sort</th><th>Action</th>
              </tr>
            </thead>
            <tbody id="tbody">
              <tr><td colspan="9" class="muted">Loading...</td></tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <div class="modal" id="modal">
    <div class="modal-card">
      <div class="toprow" style="margin-bottom:10px;">
        <h3 style="margin:0;font-family:Roboto,sans-serif;">Edit Plan</h3>
        <button class="btn" id="btnClose">Close</button>
      </div>

      <input type="hidden" id="f_id" value="">
      <div class="grid2">
        <div>
          <label class="muted">Name</label>
          <input class="input" id="f_name" placeholder="Starter / Standard / ...">
        </div>
        <div>
          <label class="muted">Status</label>
          <select class="input" id="f_status">
            <option value="1">Active</option>
            <option value="0">Disabled</option>
          </select>
        </div>
        <div>
          <label class="muted">Min amount (<?= $CUR ?>)</label>
          <input class="input" id="f_min" placeholder="10.00">
        </div>
        <div>
          <label class="muted">Max amount (optional)</label>
          <input class="input" id="f_max" placeholder="">
        </div>
        <div>
          <label class="muted">ROI daily percent</label>
          <input class="input" id="f_roi" placeholder="2.0">
        </div>
        <div>
          <label class="muted">Duration days</label>
          <input class="input" id="f_dur" placeholder="30">
        </div>
        <div style="grid-column:1/-1">
          <label class="muted">Description</label>
          <input class="input" id="f_desc" placeholder="Min <?= $CUR ?> 10, ...">
        </div>
        <div>
          <label class="muted">Sort order</label>
          <input class="input" id="f_sort" placeholder="10">
        </div>
      </div>

      <div class="actions" style="margin-top:12px;justify-content:flex-end;">
        <button class="btn btn-danger" id="btnDelete">Disable</button>
        <button class="btn btn-okay" id="btnSave">Save</button>
      </div>
      <div class="muted" style="margin-top:10px;font-size:12px;">Delete is implemented as <b>disable</b> (status=0) to avoid breaking existing investments.</div>
    </div>
  </div>

<script>
const BASE = <?= json_encode($BASE) ?>;
const csrf = document.getElementById('csrfToken').value;

function esc(s){ return String(s ?? '').replace(/[&<>"']/g, m => ({"&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#039;"}[m])); }

function openModal(plan){
  document.getElementById('modal').classList.add('open');
  document.getElementById('f_id').value = plan?.id || '';
  document.getElementById('f_name').value = plan?.name || '';
  document.getElementById('f_desc').value = plan?.description || '';
  document.getElementById('f_min').value = plan?.min_amount ?? '';
  document.getElementById('f_max').value = plan?.max_amount ?? '';
  document.getElementById('f_roi').value = plan?.roi_daily_percent ?? '';
  document.getElementById('f_dur').value = plan?.duration_days ?? '';
  document.getElementById('f_status').value = String(plan?.status ?? 1);
  document.getElementById('f_sort').value = plan?.sort_order ?? 0;
}

function closeModal(){ document.getElementById('modal').classList.remove('open'); }

async function loadPlans(){
  const tbody = document.getElementById('tbody');
  tbody.innerHTML = `<tr><td colspan="9" class="muted">Loading...</td></tr>`;
  const res = await fetch(`${BASE}/admin/api/investment_plans_list.php`, {credentials:'include'});
  const j = await res.json();
  if(!j.ok) throw new Error(j.error||'Failed');
  const rows = j.plans || [];
  if(!rows.length){ tbody.innerHTML = `<tr><td colspan="9" class="muted">No plans</td></tr>`; return; }

  tbody.innerHTML = rows.map(p => `
    <tr>
      <td class="mono">#${esc(p.id)}</td>
      <td><b>${esc(p.name)}</b><div class="muted" style="font-size:12px;">${esc(p.description||'')}</div></td>
      <td class="mono">${esc(p.min_amount)}</td>
      <td class="mono">${esc(p.max_amount ?? '')}</td>
      <td class="mono">${esc(p.roi_daily_percent)}</td>
      <td class="mono">${esc(p.duration_days)}</td>
      <td>${String(p.status)==='1' ? '<span class="btn btn-okay" style="padding:4px 10px;">Active</span>' : '<span class="btn btn-danger" style="padding:4px 10px;">Off</span>'}</td>
      <td class="mono">${esc(p.sort_order)}</td>
      <td><button class="btn btn-warn" onclick='window.__edit(${JSON.stringify(p).replace(/</g,'\\u003c')})'>Edit</button></td>
    </tr>
  `).join('');
  window.__edit = (p)=>openModal(p);
}

async function savePlan(){
  const fd = new FormData();
  fd.append('csrf', csrf);
  fd.append('id', document.getElementById('f_id').value);
  fd.append('name', document.getElementById('f_name').value);
  fd.append('description', document.getElementById('f_desc').value);
  fd.append('min_amount', document.getElementById('f_min').value);
  fd.append('max_amount', document.getElementById('f_max').value);
  fd.append('roi_daily_percent', document.getElementById('f_roi').value);
  fd.append('duration_days', document.getElementById('f_dur').value);
  fd.append('status', document.getElementById('f_status').value);
  fd.append('sort_order', document.getElementById('f_sort').value);

  const res = await fetch(`${BASE}/admin/api/investment_plans_save.php`, {method:'POST', body:fd, credentials:'include'});
  const j = await res.json();
  if(!j.ok) throw new Error(j.error||'Failed');
  closeModal();
  await loadPlans();
  alert('Saved');
}

async function disablePlan(){
  const id = document.getElementById('f_id').value;
  if(!id){ closeModal(); return; }
  if(!confirm('Disable this plan?')) return;
  const fd = new FormData();
  fd.append('csrf', csrf);
  fd.append('id', id);
  const res = await fetch(`${BASE}/admin/api/investment_plans_delete.php`, {method:'POST', body:fd, credentials:'include'});
  const j = await res.json();
  if(!j.ok) throw new Error(j.error||'Failed');
  closeModal();
  await loadPlans();
  alert('Disabled');
}

document.getElementById('btnAdd').addEventListener('click', ()=>openModal({status:1, sort_order:10, duration_days:30, roi_daily_percent:2.0, min_amount:10}));
document.getElementById('btnClose').addEventListener('click', closeModal);
document.getElementById('btnSave').addEventListener('click', ()=>savePlan().catch(e=>alert(e.message)));
document.getElementById('btnDelete').addEventListener('click', ()=>disablePlan().catch(e=>alert(e.message)));

loadPlans().catch(e=>alert(e.message));
</script>
</body>
</html>
