if (typeof particlesJS === "function") {
  particlesJS("particles-js", {
    particles: {
      number: { value: 65, density: { enable: true, value_area: 900 } },
      color: { value: "#ffffff" },
      shape: { type: "circle" },
      opacity: { value: 0.22, random: true },
      size: { value: 3, random: true },
      line_linked: { enable: true, distance: 140, color: "#ffffff", opacity: 0.10, width: 1 },
      move: { enable: true, speed: 2.0, direction: "none", out_mode: "out" }
    },
    interactivity: {
      detect_on: "canvas",
      events: { onhover: { enable: true, mode: "grab" }, onclick: { enable: true, mode: "push" }, resize: true },
      modes: { grab: { distance: 160, line_linked: { opacity: 0.18 } }, push: { particles_nb: 2 } }
    },
    retina_detect: true
  });
}