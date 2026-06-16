(function () {
  var SIDEBAR_KEY = "pes-sidebar-collapsed";
  var FILTERS_KEY = "pes-filters-expanded";

  /* —— Sidebar contraer / expandir —— */
  function initSidebarCollapse() {
    var sidebar = document.getElementById("sidebar");
    var btn = document.getElementById("pes-sidebar-collapse");
    var label = document.getElementById("pes-sidebar-collapse-label");
    if (!sidebar || !btn) return;

    function isCollapsed() {
      return sidebar.classList.contains("c-sidebar-minimized");
    }

    function updateUi() {
      var collapsed = isCollapsed();
      if (label) label.textContent = collapsed ? "Expandir" : "Contraer";
      btn.setAttribute("title", collapsed ? "Expandir menú" : "Contraer menú");
      btn.setAttribute("aria-expanded", collapsed ? "false" : "true");
      document.body.classList.toggle("pes-sidebar-is-collapsed", collapsed);
    }

    function setCollapsed(collapsed) {
      if (collapsed) {
        sidebar.querySelectorAll(".c-sidebar-nav-dropdown.c-show").forEach(function (el) {
          el.classList.remove("c-show");
        });
        sidebar.classList.add("c-sidebar-minimized");
      } else {
        sidebar.classList.remove("c-sidebar-minimized");
      }
      try {
        localStorage.setItem(SIDEBAR_KEY, collapsed ? "1" : "0");
      } catch (e) {}
      updateUi();
      if (window.jQuery) {
        window.jQuery(window).trigger("resize");
      }
    }

    try {
      if (localStorage.getItem(SIDEBAR_KEY) === "1") {
        sidebar.classList.add("c-sidebar-minimized");
      }
    } catch (e) {}

    updateUi();

    btn.addEventListener("click", function (e) {
      e.preventDefault();
      setCollapsed(!isCollapsed());
    });
  }

  /* —— Panel de filtros (reportes, etc.) —— */
  function initFiltersCollapse() {
    document.querySelectorAll(".pes-filters-panel").forEach(function (panel, index) {
      if (panel.dataset.pesCollapseInit) return;
      panel.dataset.pesCollapseInit = "1";

      var storageId = panel.id || "filtros-form" || "panel-" + index;
      var key = FILTERS_KEY + ":" + storageId;

      var bar = document.createElement("div");
      bar.className = "pes-filters-collapse-bar";

      var toggle = document.createElement("button");
      toggle.type = "button";
      toggle.className = "pes-filters-collapse-btn";
      toggle.setAttribute("aria-expanded", "true");

      var title = document.createElement("span");
      title.className = "pes-filters-collapse-title";
      title.textContent = "Filtros";

      var chevron = document.createElement("span");
      chevron.className = "pes-filters-collapse-chevron";
      chevron.setAttribute("aria-hidden", "true");

      toggle.appendChild(title);
      toggle.appendChild(chevron);

      function setExpanded(expanded) {
        panel.classList.toggle("pes-filters-collapsed", !expanded);
        toggle.classList.toggle("is-collapsed", !expanded);
        toggle.setAttribute("aria-expanded", expanded ? "true" : "false");
        try {
          localStorage.setItem(key, expanded ? "1" : "0");
        } catch (e) {}
      }

      try {
        if (localStorage.getItem(key) === "0") {
          setExpanded(false);
        }
      } catch (e) {}

      toggle.addEventListener("click", function () {
        setExpanded(panel.classList.contains("pes-filters-collapsed"));
      });

      bar.appendChild(toggle);
      panel.parentNode.insertBefore(bar, panel);

      setExpanded(!panel.classList.contains("pes-filters-collapsed"));
    });
  }

  /* —— Tarjeta: clic en cabecera principal —— */
  function initCardCollapse() {
    document.querySelectorAll(".pes-card").forEach(function (card) {
      var header = card.querySelector(":scope > .card-header.pes-card-header");
      var body = card.querySelector(":scope > .card-body");
      if (!header || !body || header.dataset.pesCardCollapse) return;
      if (header.querySelector(".btn, button")) return;
      if (card.querySelector(".pes-table-wrap, .dataTables_wrapper, table.dataTable")) return;

      header.dataset.pesCardCollapse = "1";
      header.classList.add("pes-card-header-collapsible");
      header.setAttribute("role", "button");
      header.setAttribute("tabindex", "0");
      header.setAttribute("aria-expanded", "true");

      var chevron = document.createElement("span");
      chevron.className = "pes-card-collapse-chevron";
      chevron.setAttribute("aria-hidden", "true");
      header.appendChild(chevron);

      function toggle() {
        var collapsed = body.classList.toggle("pes-card-body-collapsed");
        header.classList.toggle("is-collapsed", collapsed);
        header.setAttribute("aria-expanded", collapsed ? "false" : "true");
      }

      header.addEventListener("click", toggle);
      header.addEventListener("keydown", function (e) {
        if (e.key === "Enter" || e.key === " ") {
          e.preventDefault();
          toggle();
        }
      });
    });
  }

  function init() {
    initSidebarCollapse();
    initFiltersCollapse();
    initCardCollapse();
  }

  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", init);
  } else {
    init();
  }
})();
