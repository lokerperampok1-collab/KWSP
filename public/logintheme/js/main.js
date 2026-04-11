(function(){
  function ready(fn){ if(document.readyState !== "loading") fn(); else document.addEventListener("DOMContentLoaded", fn); }

  ready(function(){
    // Set background image from data-bg-image attribute to CSS variable
    var sec = document.querySelector(".fxt-template-layout28");
    if(sec){
      var bg = sec.getAttribute("data-bg-image");
      if(bg){
        sec.style.setProperty("--bg-img", "url(" + bg + ")");
      }
    }

    // Toggle password visibility
    document.querySelectorAll(".toggle-password").forEach(function(icon){
      icon.addEventListener("click", function(){
        var sel = icon.getAttribute("toggle");
        if(!sel) return;
        var input = document.querySelector(sel);
        if(!input) return;
        var isPass = input.getAttribute("type") === "password";
        input.setAttribute("type", isPass ? "text" : "password");
        icon.classList.toggle("fa-eye");
        icon.classList.toggle("fa-eye-slash");
      });
    });
  });
})();
