(function(){
  // Minimal particle canvas (no dependency)
  window.particlesJS = function(id, cfg){
    var el = document.getElementById(id);
    if(!el) return;

    var canvas = document.createElement("canvas");
    var ctx = canvas.getContext("2d");
    el.appendChild(canvas);

    function resize(){
      canvas.width = el.clientWidth;
      canvas.height = el.clientHeight;
    }
    window.addEventListener("resize", resize);
    resize();

    var count = (cfg && cfg.particles && cfg.particles.number && cfg.particles.number.value) || 55;
    var speed = 0.35;
    var dots = [];
    for(var i=0;i<count;i++){
      dots.push({
        x: Math.random()*canvas.width,
        y: Math.random()*canvas.height,
        vx: (Math.random()*2-1)*speed,
        vy: (Math.random()*2-1)*speed,
        r: 1 + Math.random()*2
      });
    }

    function step(){
      ctx.clearRect(0,0,canvas.width,canvas.height);

      // draw dots
      for(var i=0;i<dots.length;i++){
        var d = dots[i];
        d.x += d.vx; d.y += d.vy;
        if(d.x<0||d.x>canvas.width) d.vx *= -1;
        if(d.y<0||d.y>canvas.height) d.vy *= -1;

        ctx.beginPath();
        ctx.arc(d.x,d.y,d.r,0,Math.PI*2);
        ctx.fillStyle = "rgba(255,255,255,.35)";
        ctx.fill();
      }

      // draw lines
      for(var a=0;a<dots.length;a++){
        for(var b=a+1;b<dots.length;b++){
          var dx = dots[a].x - dots[b].x;
          var dy = dots[a].y - dots[b].y;
          var dist = Math.sqrt(dx*dx+dy*dy);
          if(dist < 120){
            ctx.beginPath();
            ctx.moveTo(dots[a].x, dots[a].y);
            ctx.lineTo(dots[b].x, dots[b].y);
            ctx.strokeStyle = "rgba(255,255,255," + (0.12*(1 - dist/120)) + ")";
            ctx.lineWidth = 1;
            ctx.stroke();
          }
        }
      }

      requestAnimationFrame(step);
    }
    step();
  };
})();
