(function(){
  function $(sel){ return document.querySelector(sel); }
  function setText(sel, txt){ var el=$(sel); if(el) el.textContent = txt; }
  function setHTML(sel, html){ var el=$(sel); if(el) el.innerHTML = html; }
  function setAttr(sel, attr, val){ var el=$(sel); if(el) el.setAttribute(attr, val); }

  function base(){
    if(window.GMT_BASE) return window.GMT_BASE.replace(/\/$/,'');
    // fallback: infer from current path (assumes project folder name is Global-Market-Trade)
    var p = location.pathname;
    var idx = p.indexOf('/Global-Market-Trade');
    return idx>=0 ? p.slice(0, idx+('/Global-Market-Trade'.length)) : '';
  }

  async function fetchJson(url){
    try{
      const res = await fetch(url, {credentials:'same-origin'});
      if(!res.ok) return null;
      return await res.json();
    }catch(e){ return null; }
  }

  async function hydrateMe(){
    const b = base();
    const me = await fetchJson(b + '/api/me.php');
    if(me && (me.full_name || me.username || me.email)){
      const name = me.full_name || me.username || (me.email ? me.email.split('@')[0] : 'User');
      const uname = me.username || (me.email ? me.email.split('@')[0] : 'user');
      setText('#gmtUserName', name);
      setText('#gmtSidebarName', name);
      setText('#gmtUserUsername', uname);
      setText('#topUserName', name);
      setText('#topUserUsername', uname);
      if(me.profile_image){
        setAttr('#gmtUserAvatar', 'src', b + '/images/profile/' + me.profile_image);
        setAttr('#gmtSidebarAvatar', 'src', b + '/images/profile/' + me.profile_image);
        setAttr('#topUserAvatar', 'src', b + '/images/profile/' + me.profile_image);
      }
      if(typeof me.is_admin !== 'undefined'){
        var adminLink = document.getElementById('gmtAdminLink');
        if(adminLink) adminLink.style.display = (parseInt(me.is_admin,10)===1) ? '' : 'none';
      }
    }
  }

  async function hydrateBalance(){
    const b = base();
    const bal = await fetchJson(b + '/api/balance.php');
    if(bal){
      const rm = (typeof bal.balance_rm !== 'undefined') ? bal.balance_rm :
                 (typeof bal.balance !== 'undefined') ? bal.balance : null;
      if(rm!==null){
        setText('#topBalance', parseFloat(rm).toFixed(2));
      }
    }
  }

  async function hydrateNotifs(){
    const b = base();
    const n = await fetchJson(b + '/api/notifications.php');
    if(!n) return;
    // accept either array or object with items/unread_count
    const items = Array.isArray(n) ? n : (n.items || n.data || []);
    const unread = (!Array.isArray(n) && (n.unread_count ?? n.unread ?? null)) ?? null;
    if(unread!==null){
      var dot = document.getElementById('gmtNotifDot');
      if(dot) dot.style.display = (parseInt(unread,10)>0) ? '' : 'none';
    }
    var list = document.getElementById('gmtNotifList');
    if(list && items && items.length){
      var html = items.slice(0,6).map(function(it){
        var title = it.title || it.subject || 'Notification';
        var msg = it.message || it.body || '';
        return '<li><a href="#"><i class="fa fa-bell text-aqua"></i> '+escapeHtml(title)+'<br><small class="text-muted">'+escapeHtml(msg).slice(0,80)+'</small></a></li>';
      }).join('');
      list.innerHTML = html;
    }
  }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g,function(c){
      return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]);
    });
  }


  function enhanceTables(){
    // Wrap DataTables / tables in a horizontal scroll container on mobile
    var tables = document.querySelectorAll('table');
    tables.forEach(function(tbl){
      // ignore tiny tables (like layout) - only wrap if looks like a data table
      var isData = tbl.classList.contains('table') || tbl.classList.contains('dataTable') || tbl.closest('.dataTables_wrapper');
      if(!isData) return;
      // already wrapped?
      var p = tbl.parentElement;
      if(p && p.classList && p.classList.contains('table-responsive')) return;
      // wrap
      var wrap = document.createElement('div');
      wrap.className = 'table-responsive gmt-table-scroll';
      tbl.parentElement.insertBefore(wrap, tbl);
      wrap.appendChild(tbl);
    });

    
    // Ensure DataTables-generated wrappers remain swipeable (some pages init DT after DOMContentLoaded)
    try{
      if(!window.GMT_TABLE_OBSERVER){
        window.GMT_TABLE_OBSERVER = true;
        var obs = new MutationObserver(function(){
          document.querySelectorAll('.dataTables_wrapper table').forEach(function(tbl){
            var p = tbl.parentElement;
            // DataTables may nest table inside .dataTables_scrollBody; wrap that container instead
            var scrollBody = tbl.closest('.dataTables_scrollBody');
            var target = scrollBody || tbl;
            var parent = target.parentElement;
            if(parent && parent.classList && parent.classList.contains('table-responsive')) return;
            var wrap = document.createElement('div');
            wrap.className = 'table-responsive gmt-table-scroll';
            parent.insertBefore(wrap, target);
            wrap.appendChild(target);
          });
        });
        obs.observe(document.body, {childList:true, subtree:true});
      }
    }catch(e){}

// If DataTables is present, set sane defaults (avoid responsive hiding; prefer swipe)
    if(window.jQuery && window.jQuery.fn && window.jQuery.fn.dataTable){
      try{
        if(!window.GMT_DT_PATCHED){
          window.GMT_DT_PATCHED = true;
          var old = window.jQuery.fn.dataTable.defaults;
          old.scrollX = true;
          old.autoWidth = false;
          if(old.responsive === undefined) old.responsive = false;
        }
      }catch(e){}
    }
  }

  function enhanceMobileUI(){
    enhanceTables();

    // Make dropdown menus easier to tap on mobile
    document.querySelectorAll('.dropdown-menu a').forEach(function(a){
      a.style.minHeight = '44px';
      a.style.display = 'flex';
      a.style.alignItems = 'center';
    });
  }

  document.addEventListener('DOMContentLoaded', function(){
    enhanceMobileUI();
    hydrateMe();
    hydrateBalance();
    hydrateNotifs();
    // refresh balance every 30s
    setInterval(hydrateBalance, 30000);
  });
})();