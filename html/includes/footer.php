</main>
    
        <footer class="c-footer">
          <!-- div><a href="https://geret.cl/">Sistema de solicitud de vacaciones</a> © 2021</div>
          <div class="ml-auto">Realizado por&nbsp;<a href="https://geret.cl/">Geret</a></div> -->
        </footer>
      </div>
    </div>
    <!-- CoreUI and necessary plugins-->
    <script src="<?=$base_home?>vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <!--[if IE]><!-->
    <script src="<?=$base_home?>vendors/@coreui/icons/js/svgxuse.min.js"></script>
    <!--<![endif]-->
    <!-- Plugins and scripts required by this view-->
    <script src="<?=$base_home?>vendors/@coreui/utils/js/coreui-utils.js"></script>

  
<script src="<?=$base_home?>js/pes-sidebar-theme.js?v=1.2"></script>

<script src="<?=$base_home?>js/pes-ui-collapse.js?v=1.1"></script>
</body>

</html>

<!-- Scroll to Top Button-->
<!-- <a class="scroll-to-top rounded" href="#page-top">
  <i class="fa fa-angle-up"></i>
</a> -->

<!-- Logout Modal-->
<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Está seguro de cerrar sesión?</h5>
        <button class="close" type="button" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">×</span>
        </button>
      </div>
      <div class="modal-body">Presione <b>Cerrar sesión</b> si desea terminar su sesión actual.</div>
      <div class="modal-footer">
        <button class="btn btn-secondary" type="button" data-dismiss="modal">Cancelar</button>
        <a class="btn btn-primary" href="<?=$base_home?>logout.php">Cerrar sesión</a>
      </div>
    </div>
  </div>
</div>

<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/dataTables.buttons.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>

<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.66/vfs_fonts.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.html5.min.js"></script>

<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/buttons/1.6.1/js/buttons.colVis.min.js"></script>
<script type="text/javascript" charset="utf8" src="//cdn.jsdelivr.net/momentjs/latest/moment-with-locales.min.js"></script>

<script type="text/javascript" charset="utf8" src="//cdn.datatables.net/plug-ins/1.10.20/dataRender/datetime.js"></script>
<script src="<?=$base_home?>vendors/datatables/dataTables.bootstrap4.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<script src="//cdn.datatables.net/select/1.3.1/js/select.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/datetime/1.1.1/js/dataTables.dateTime.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/datetime/1.1.2/css/dataTables.dateTime.min.css" />
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/js/tempusdominus-bootstrap-4.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />

<!-- #2: Select2 (ya cargado arriba) — buscador en todos los selects del sistema -->
<script>
  $(function () {
    if (!window.jQuery || !$.fn.select2) return;

    function pesApplySelect2($s) {
      if ($s.closest('.dataTables_wrapper').length) return; // no tocar selects internos de DataTables
      if ($s.hasClass('no-select2')) return;
      if ($s.data('select2')) return;
      $s.select2({
        width: '100%',
        language: {
          noResults: function () { return 'Sin resultados'; },
          searching: function () { return 'Buscando…'; }
        }
      });
      // Cascada: re-sincroniza Select2 cuando el <select> cambia disabled u opciones
      // (repoblado por AJAX), sin disparar otros handlers. Evita romper proveedor↔embarcacion↔trabajador.
      try {
        var obs = new MutationObserver(function () { $s.trigger('change.select2'); });
        obs.observe($s[0], { attributes: true, attributeFilter: ['disabled'], childList: true });
      } catch (e) {}
    }

    window.pesInitSelect2 = function (root) {
      $(root || document).find('select').each(function () { pesApplySelect2($(this)); });
    };
    window.pesInitSelect2(document);
  });
</script>

</body>

</html>
