(function () {
  var STORAGE_KEY = "pes-sidebar-theme";
  var FADE_MS = 220;
  var root = document.documentElement;
  var body = document.body;
  var switching = false;

  function uiStylesheet() {
    return document.getElementById("pes-ui-css");
  }

  function isClassic() {
    return root.classList.contains("pes-sidebar-classic");
  }

  function fadeTargets() {
    var nodes = [];
    var main = document.querySelector(".c-main");
    var sidebar = document.getElementById("sidebar");
    var header = document.querySelector(".c-header");
    if (main) nodes.push(main);
    if (sidebar) nodes.push(sidebar);
    if (header) nodes.push(header);
    return nodes;
  }

  function setClassic(enabled) {
    var link = uiStylesheet();
    if (enabled) {
      root.classList.add("pes-sidebar-classic");
      if (body) body.classList.add("pes-sidebar-classic");
      if (link) link.disabled = true;
    } else {
      root.classList.remove("pes-sidebar-classic");
      if (body) body.classList.remove("pes-sidebar-classic");
      if (link) link.disabled = false;
    }
  }

  function updateUi(classic) {
    var label = document.getElementById("pes-theme-toggle-label");
    var btn = document.getElementById("pes-theme-toggle");
    if (label) label.textContent = classic ? "Aspecto nuevo" : "Aspecto clásico";
    if (btn) {
      btn.setAttribute(
        "title",
        classic ? "Volver al diseño actual" : "Ver el diseño original"
      );
      btn.setAttribute("aria-pressed", classic ? "true" : "false");
    }
  }

  function apply(theme, instant) {
    var classic = theme === "classic";
    if (instant) {
      setClassic(classic);
      updateUi(classic);
      try {
        localStorage.setItem(STORAGE_KEY, theme);
      } catch (e) {}
      return;
    }

    if (switching) return;
    switching = true;

    var btn = document.getElementById("pes-theme-toggle");
    if (btn) btn.disabled = true;

    root.classList.add("pes-theme-switching");
    var nodes = fadeTargets();
    nodes.forEach(function (el) {
      el.classList.add("pes-theme-fade-out");
    });

    window.setTimeout(function () {
      setClassic(classic);
      updateUi(classic);
      try {
        localStorage.setItem(STORAGE_KEY, theme);
      } catch (e) {}

      nodes.forEach(function (el) {
        el.classList.remove("pes-theme-fade-out");
        el.classList.add("pes-theme-fade-in");
      });

      window.setTimeout(function () {
        nodes.forEach(function (el) {
          el.classList.remove("pes-theme-fade-in");
        });
        root.classList.remove("pes-theme-switching");
        if (btn) btn.disabled = false;
        switching = false;
      }, FADE_MS);
    }, FADE_MS);
  }

  function readStored() {
    try {
      return localStorage.getItem(STORAGE_KEY);
    } catch (e) {
      return null;
    }
  }

  if (readStored() === "classic") {
    apply("classic", true);
  } else {
    updateUi(false);
  }

  var btn = document.getElementById("pes-theme-toggle");
  if (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      apply(isClassic() ? "modern" : "classic", false);
    });
  }
})();
