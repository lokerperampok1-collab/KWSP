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

$csrf = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>KYC Requests - Admin | Global Market Trade</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#0b1220;
      --card:#121a2b;
      --muted:rgba(255,255,255,0.65);
      --line:rgba(255,255,255,0.10);
      --ok:#21c07a;
      --warn:#ffb020;
      --danger:#ff4d4d;
      --blue:#2ea7ff;
      --btn:#1c2740;
    }
    *{box-sizing:border-box}
    body{margin:0;font-family:Inter,system-ui,Segoe UI,Arial;background:linear-gradient(180deg,#07101d 0%, #0b1220 55%, #07101d 100%);color:#fff}
    .wrap{max-width:1100px;margin:0 auto;padding:22px 14px 60px}
    .top{
      display:flex;gap:12px;align-items:center;justify-content:space-between;flex-wrap:wrap;
      padding:14px 16px;border-radius:14px;background:rgba(18,26,43,0.88);border:1px solid var(--line);
      box-shadow:0 20px 60px rgba(0,0,0,0.35);
    }
    .brand h1{margin:0;font-size:18px}
    .brand .sub{color:var(--muted);font-size:12px;margin-top:4px}
    .btns{display:flex;gap:8px;flex-wrap:wrap}
    .btn{
      background:var(--btn);border:1px solid var(--line);color:#fff;text-decoration:none;
      padding:9px 12px;border-radius:10px;font-weight:600;font-size:13px;display:inline-flex;gap:8px;align-items:center;
    }
    .btn:hover{border-color:rgba(255,255,255,0.2)}
    .btn-primary{background:rgba(46,167,255,0.16);border-color:rgba(46,167,255,0.35)}
    .btn-warn{background:rgba(255,176,32,0.16);border-color:rgba(255,176,32,0.35)}
    .btn-danger{background:rgba(255,77,77,0.14);border-color:rgba(255,77,77,0.35)}
    .card{
      margin-top:14px;background:rgba(18,26,43,0.88);border:1px solid var(--line);border-radius:14px;
      box-shadow:0 20px 60px rgba(0,0,0,0.35);
      overflow:hidden;
    }
    .card-h{padding:14px 16px;border-bottom:1px solid var(--line);display:flex;justify-content:space-between;gap:10px;flex-wrap:wrap;align-items:center}
    .title{font-weight:700}
    .tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center}
    .select,.input{
      background:#0e1627;border:1px solid var(--line);color:#fff;border-radius:10px;padding:9px 10px;font-size:13px;
    }
    table{width:100%;border-collapse:collapse}
    th,td{padding:12px 10px;border-bottom:1px solid var(--line);text-align:left;font-size:13px;vertical-align:top}
    th{color:rgba(255,255,255,0.75);font-weight:700}
    .muted{color:var(--muted)}
    .pill{display:inline-flex;align-items:center;gap:6px;padding:4px 10px;border-radius:999px;border:1px solid var(--line);font-size:12px;font-weight:700}
    .pill.pending{background:rgba(255,176,32,0.14);border-color:rgba(255,176,32,0.35);color:#ffd48a}
    .pill.approved{background:rgba(33,192,122,0.14);border-color:rgba(33,192,122,0.35);color:#aef0cf}
    .pill.rejected{background:rgba(255,77,77,0.14);border-color:rgba(255,77,77,0.35);color:#ffb2b2}
    .row-actions{display:flex;gap:8px;flex-wrap:wrap}
    .smallbtn{padding:7px 10px;border-radius:10px;font-size:12px;font-weight:700;cursor:pointer}
    .smallbtn.approve{background:rgba(33,192,122,0.14);border:1px solid rgba(33,192,122,0.35);color:#d6ffef}
    .smallbtn.reject{background:rgba(255,77,77,0.14);border:1px solid rgba(255,77,77,0.35);color:#ffe0e0}
    .smallbtn.view{background:rgba(46,167,255,0.14);border:1px solid rgba(46,167,255,0.35);color:#d8f0ff}
    .smallbtn.pending{background:rgba(255,176,32,0.12);border:1px solid rgba(255,176,32,0.28);color:#ffe7b9}
    .notice{padding:12px 16px;color:var(--muted);font-size:13px}
    .modal{
      position:fixed;inset:0;background:rgba(0,0,0,0.62);display:none;align-items:center;justify-content:center;padding:16px;
    }
    .modal .box{
      width:min(820px, 96vw);
      background:#0e1627;border:1px solid rgba(255,255,255,0.16);border-radius:14px;
      box-shadow:0 40px 100px rgba(0,0,0,0.6);
      overflow:hidden;
    }
    .modal .mh{padding:12px 14px;border-bottom:1px solid rgba(255,255,255,0.12);display:flex;justify-content:space-between;gap:10px;align-items:center}
    .modal .mc{padding:14px}
    .grid{display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px}
    .doc{border:1px solid rgba(255,255,255,0.12);border-radius:12px;padding:10px;background:rgba(255,255,255,0.04)}
    .doc h4{margin:0 0 8px 0;font-size:13px}
    .doc a{color:#9dd7ff;text-decoration:none;word-break:break-all;font-size:12px}
    .doc img{width:100%;max-height:240px;object-fit:cover;border-radius:10px;border:1px solid rgba(255,255,255,0.10)}
    .close{cursor:pointer;border:1px solid rgba(255,255,255,0.18);background:transparent;color:#fff;border-radius:10px;padding:7px 10px;font-weight:700}
    @media(max-width:720px){
      .grid{grid-template-columns:1fr}
      th:nth-child(4), td:nth-child(4){display:none}
    }
  </style>
</head>
<body>
  <div class="wrap">
    <div class="top">
      <div class="brand">
        <h1>KYC Requests</h1>
        <div class="sub">Logged in as <b><?= htmlspecialchars($display, ENT_QUOTES, "UTF-8") ?></b></div>
      </div>
      <div class="btns">
        <a class="btn" href="<?= $BASE ?>/admin.php">Admin Dashboard</a>
        <a class="btn btn-warn" href="<?= $BASE ?>/admin_plans.php">Investment Plans</a>
        <a class="btn btn-primary" href="<?= $BASE ?>/admin_kyc.php">KYC Requests</a>
        <a class="btn btn-danger" href="<?= $BASE ?>/logout.php">Logout</a>
      </div>
    </div>

    <div class="card">
      <div class="card-h">
        <div class="title">Requests</div>
        <div class="tools">
          <select id="statusSel" class="select">
            <option value="all">All</option>
            <option value="pending">Pending</option>
            <option value="approved">Approved</option>
            <option value="rejected">Rejected</option>
          </select>
          <input id="qInput" class="input" placeholder="Search name/email..." />
          <button id="refreshBtn" class="btn">Refresh</button>
        </div>
      </div>

      <div style="overflow:auto; -webkit-overflow-scrolling:touch;">
        <table>
          <thead>
            <tr>
              <th style="width:72px;">ID</th>
              <th>User</th>
              <th style="width:120px;">Status</th>
              <th style="width:200px;">Updated</th>
              <th style="width:290px;">Action</th>
            </tr>
          </thead>
          <tbody id="tbody">
            <tr><td colspan="5" class="muted">Loading...</td></tr>
          </tbody>
        </table>
      </div>
      <div class="notice">
        Tips: <span class="muted">Approve/Reject will update user verification gate instantly. Reject requires a reason (note).</span>
      </div>
    </div>
  </div>

  <div id="modal" class="modal" role="dialog" aria-modal="true">
    <div class="box">
      <div class="mh">
        <div style="font-weight:800">KYC Documents</div>
        <button class="close" id="closeModal">Close</button>
      </div>
      <div class="mc">
        <div id="modalMeta" class="muted" style="margin-bottom:12px"></div>
        <div class="grid" id="docsGrid"></div>
        <div style="margin-top:12px">
          <div class="muted" style="font-size:12px">If preview doesn't show (PDF or unsupported), use the link to open in new tab.</div>
        </div>
      </div>
    </div>
  </div>

<script>
const BASE = <?= json_encode($BASE) ?>;
const CSRF = <?= json_encode($csrf) ?>;

const tbody = document.getElementById('tbody');
const statusSel = document.getElementById('statusSel');
const qInput = document.getElementById('qInput');
const refreshBtn = document.getElementById('refreshBtn');

const modal = document.getElementById('modal');
const closeModal = document.getElementById('closeModal');
const docsGrid = document.getElementById('docsGrid');
const modalMeta = document.getElementById('modalMeta');

function esc(s){
  return String(s ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
}
function pill(status){
  const cls = status === 'approved' ? 'approved' : (status === 'rejected' ? 'rejected' : 'pending');
  return `<span class="pill ${cls}">${esc(status)}</span>`;
}

function openModal(item){
  modalMeta.innerHTML = `<b>${esc(item.full_name)}</b> (${esc(item.email)}) • Status: ${pill(item.status)} ${item.note ? ('• Note: ' + esc(item.note)) : ''}`;
  docsGrid.innerHTML = '';

  const docs = [
    {label:'ID Front', path:item.id_front_path},
    {label:'Selfie', path:item.selfie_path},
    {label:'ID Back', path:item.id_back_path},
  ];

  for (const d of docs){
    const path = d.path ? String(d.path) : '';
    const ext = path.split('.').pop().toLowerCase();
    const isImg = ['jpg','jpeg','png','webp'].includes(ext);

    const el = document.createElement('div');
    el.className = 'doc';
    el.innerHTML = `
      <h4>${esc(d.label)}</h4>
      ${path ? `<a href="${esc(path)}" target="_blank" rel="noopener">Open</a>` : `<div class="muted">Not uploaded</div>`}
      ${path && isImg ? `<div style="margin-top:8px"><img src="${esc(path)}" alt="${esc(d.label)}"></div>` : ``}
    `;
    docsGrid.appendChild(el);
  }

  modal.style.display = 'flex';
}

function close(){
  modal.style.display = 'none';
}
closeModal.addEventListener('click', close);
modal.addEventListener('click', (e)=>{ if(e.target === modal) close(); });

async function load(){
  tbody.innerHTML = `<tr><td colspan="5" class="muted">Loading...</td></tr>`;
  const qs = new URLSearchParams();
  qs.set('status', statusSel.value);
  if (qInput.value.trim()) qs.set('q', qInput.value.trim());

  const url = `${BASE}/admin/api/kyc_list.php?` + qs.toString();
  try{
    const res = await fetch(url, {credentials:'include'});
    const txt = await res.text();
    let data;
    try { data = JSON.parse(txt); } catch(e){ throw new Error(txt.slice(0,200)); }
    if(!res.ok || !data.ok) throw new Error(data.error || 'Failed');

    if(!data.items || data.items.length === 0){
      tbody.innerHTML = `<tr><td colspan="5" class="muted">No items</td></tr>`;
      return;
    }

    tbody.innerHTML = data.items.map(item => {
      const updated = item.updated_at ? esc(item.updated_at) : '';
      const note = item.note ? `<div class="muted" style="margin-top:6px;font-size:12px">Note: ${esc(item.note)}</div>` : '';
      return `
        <tr>
          <td>#${esc(item.id)}</td>
          <td>
            <div style="font-weight:800">${esc(item.full_name)}</div>
            <div class="muted">${esc(item.email)}</div>
            ${note}
          </td>
          <td>${pill(item.status)}</td>
          <td class="muted">${updated}</td>
          <td>
            <div class="row-actions">
              <button class="smallbtn view" data-act="view" data-id="${esc(item.id)}">View</button>
              <button class="smallbtn approve" data-act="approve" data-id="${esc(item.id)}">Approve</button>
              <button class="smallbtn reject" data-act="reject" data-id="${esc(item.id)}">Reject</button>
              <button class="smallbtn pending" data-act="set_pending" data-id="${esc(item.id)}">Set Pending</button>
            </div>
          </td>
        </tr>
      `;
    }).join('');
    // attach item lookup
    tbody._items = data.items;
  } catch(e){
    tbody.innerHTML = `<tr><td colspan="5" style="color:#ffb2b2">Error: ${esc(e.message || e)}</td></tr>`;
  }
}

async function updateKyc(kycId, action){
  let note = '';
  if(action === 'reject'){
    note = prompt('Reject reason (required):', '') || '';
    if(!note.trim()){
      alert('Reject requires a reason.');
      return;
    }
  } else if(action === 'set_pending'){
    note = prompt('Optional note (leave empty to clear):', '') || '';
    if(!note.trim()) note = '';
  } else if(action === 'approve'){
    // clear note on approve
    note = '';
  }

  const fd = new FormData();
  fd.set('csrf', CSRF);
  fd.set('kyc_id', String(kycId));
  fd.set('action', action);
  if(note !== null) fd.set('note', note);

  const url = `${BASE}/admin/api/kyc_update.php`;
  const res = await fetch(url, {method:'POST', body: fd, credentials:'include'});
  const txt = await res.text();
  let data;
  try{ data = JSON.parse(txt); } catch(e){ throw new Error(txt.slice(0,200)); }
  if(!res.ok || !data.ok) throw new Error(data.error || 'Failed');
}

tbody.addEventListener('click', async (e)=>{
  const btn = e.target.closest('button[data-act]');
  if(!btn) return;
  const act = btn.getAttribute('data-act');
  const id = Number(btn.getAttribute('data-id') || '0');
  const items = tbody._items || [];
  const item = items.find(x => Number(x.id) === id);

  try{
    if(act === 'view'){
      if(item) openModal(item);
      return;
    }
    if(!confirm(`Confirm ${act.replace('_',' ')} for KYC #${id}?`)) return;
    await updateKyc(id, act);
    await load();
  } catch(err){
    alert('Error: ' + (err.message || err));
  }
});

refreshBtn.addEventListener('click', load);
statusSel.addEventListener('change', load);
qInput.addEventListener('keydown', (e)=>{ if(e.key === 'Enter') load(); });

load();
</script>
</body>
</html>
