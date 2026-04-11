(function () {
  var yearEl = document.getElementById("year");
  if (yearEl) yearEl.textContent = new Date().getFullYear();
  var onlineEl = document.getElementById("stat-online");
  var regEl = document.getElementById("stat-registered");
  var online = 14559, reg = 5213;
  if (onlineEl && regEl) {
    setInterval(function () {
      online += Math.floor(Math.random() * 9) - 3;
      reg += Math.floor(Math.random() * 4);
      onlineEl.textContent = Math.max(9000, online);
      regEl.textContent = Math.max(1000, reg);
    }, 2500);
  }
  var notif = document.getElementById("notification-1");
  var text = document.getElementById("notif-text");
  var names = ["Oliver","Mia","Noah","Ava","Ethan","Sofia","Lucas","Amelia","Ryan","Layla"];
  var countries = ["Germany","Mexico","Spain","Canada","Japan","France","Sweden","USA","UK","Italy"];
  function showNotif() {
    if (!notif || !text) return;
    var name = names[Math.floor(Math.random() * names.length)];
    var country = countries[Math.floor(Math.random() * countries.length)];
    var amount = (Math.floor(Math.random() * 900) + 100).toLocaleString();
    text.textContent = name + " from " + country + " just earned $ " + amount;
    notif.style.display = "block";
    notif.style.opacity = "0";
    notif.style.transform = "translateY(10px)";
    setTimeout(function () {
      notif.style.transition = "all .35s ease";
      notif.style.opacity = "1";
      notif.style.transform = "translateY(0)";
    }, 10);
    setTimeout(function () {
      notif.style.opacity = "0";
      notif.style.transform = "translateY(10px)";
      setTimeout(function () { notif.style.display = "none"; }, 350);
    }, 5200);
  }
  setTimeout(showNotif, 1800);
  setInterval(showNotif, 9500);
})();