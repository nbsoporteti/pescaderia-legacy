<?php
    session_start();
    if (!isset($_SESSION["correo_usuario"])) {
        header("location: ../../login.php");
        exit;
    }

    include_once("../../includes/header.php");
    require '../../includes/db_connect.php';
?>

    <div class="container-fluid px-4">
        <div class="pes-page-header"><h1 class="h3 mb-0">Reportes</h1><p class="mb-0">En esta sección podrá filtrar para obtener información acerca de Ingresos y Egresos.</p></div>

        <div class="row justify-content-center">
            <div class="col-lg-12">
                <div class="card shadow pes-card">
                    <div class="card-header py-3 pes-card-header">
                        <h6 class="m-0 font-weight-bold text-primary">Listado de movimientos</h6>
                    </div>
                    <div class="card-body">
                        <!-- Formulario de Filtros -->
                        <form id="filtros-form" class="pes-filters-panel">
                            <div class="row">
                                <div class="form-group col-md-3">
                                    <label for="proveedor">Proveedor:</label>
                                    <select id="proveedor" class="form-control" name="proveedor">
                                        <option value="">Todos</option>
                                        <?php
                                        $query = "SELECT id_proveedor, nombre_proveedor FROM proveedores WHERE estado = 1 AND eliminado = 0 ORDER BY nombre_proveedor";
                                        $result = $connect->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id_proveedor'] . "'>" . $row['nombre_proveedor'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-3">
                                    <label for="trabajador">Trabajador:</label>
                                    <select id="trabajador" class="form-control" name="trabajador">
                                        <option value="">Todos</option>
                                        <?php
                                        $query = "SELECT id_trabajador, nombre_trabajador FROM trabajadores WHERE estado = 1 AND eliminado = 0 ORDER BY nombre_trabajador";
                                        $result = $connect->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id_trabajador'] . "'>" . $row['nombre_trabajador'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="embarcacion">Acarreo:</label>
                                    <select id="embarcacion" class="form-control" name="embarcacion">
                                        <option value="">Todas</option>
                                        <?php
                                        $query = "SELECT id_embarcacion, nombre_embarcacion FROM embarcaciones WHERE estado = 1 AND eliminado = 0 ORDER BY nombre_embarcacion";
                                        $result = $connect->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id_embarcacion'] . "'>" . $row['nombre_embarcacion'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="embarcacion2">Embarcacion:</label>
                                    <select id="embarcacion2" class="form-control" name="embarcacion2">
                                        <option value="">Todas</option>
                                        <?php
                                        $query = "SELECT id_embarcacion2, nombre_embarcacion2 FROM embarcaciones2 WHERE estado = 1 AND eliminado = 0 ORDER BY nombre_embarcacion2";
                                        $result = $connect->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id_embarcacion2'] . "'>" . $row['nombre_embarcacion2'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="clasificacion">Clasificación:</label>
                                    <select id="clasificacion" class="form-control" name="clasificacion">
                                        <option value="">Todas</option>
                                        <?php
                                        $query = "SELECT id_clasificacion, nombre_clasificacion FROM clasificaciones WHERE estado = 1 AND eliminado = 0 ORDER BY nombre_clasificacion";
                                        $result = $connect->query($query);
                                        while ($row = $result->fetch_assoc()) {
                                            echo "<option value='" . $row['id_clasificacion'] . "'>" . $row['nombre_clasificacion'] . "</option>";
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>
                            <div class="row justify-content-center">
                                <div class="form-group col-md-2">
                                    <label for="fecha_inicio">Fecha desde:</label>
                                    <input type="date" id="fecha_inicio" class="form-control" name="fecha_inicio">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="fecha_fin">Fecha hasta:</label>
                                    <input type="date" id="fecha_fin" class="form-control" name="fecha_fin">
                                </div>
                                <div class="form-group col-md-2">
                                    <label for="tipo_movimiento">Tipo de movimiento:</label>
                                    <select id="tipo_movimiento" class="form-control" name="tipo_movimiento">
                                        <option value="">Todos</option>
                                        <option value="1">Ingresos</option>
                                        <option value="2">Egresos</option>
                                        <option value="3">Gastos</option>
                                        <option value="4">Pagos</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row mt-3">
                                <div class="form-group col-md-12 text-center">
                                    <button type="button" id="filtrar" class="btn btn-warning">
                                        <i class="fa fa-filter"></i> Aplicar Filtros
                                    </button>
                                </div>
                            </div>
                        </form>

                        <p class="pes-hint">Sin filtros de fecha se muestra todo el historial. Use fechas para acotar la búsqueda.</p>
                        <div id="reportes-resumen" class="alert alert-light border small mb-3" style="display:none">
                            <strong>Resumen del filtro aplicado</strong> (<span id="t-count">0</span> movimientos):
                            Ingreso <span id="t-ingreso" class="text-success font-weight-bold">$0</span> ·
                            Egreso <span id="t-egreso" class="text-danger font-weight-bold">$0</span> ·
                            Gastos <span id="t-gastos" class="text-warning font-weight-bold">$0</span> ·
                            Pagos <span id="t-pagos" class="text-info font-weight-bold">$0</span> ·
                            <strong>Saldo <span id="t-saldo">$0</span></strong>
                        </div>
                        <hr>

                        <!-- Tabla -->
                        <div class="table-responsive pes-table-wrap">
                            <table id="reportes-table" class="table table-bordered table-striped display nowrap pes-table" style="width:100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">N° Movimiento</th>
                                        <th class="text-center">Fecha</th>
                                        <th class="text-center">Proveedor</th>
                                        <th class="text-center">Trabajador</th>
                                        <th class="text-center">Acarreo</th>
                                        <th class="text-center">Embarcacion</th>
                                        <th class="text-center">Clasificación</th>
                                        <th class="text-center">Tipo de movimiento</th>
                                        <th class="text-right">Monto</th>
                                        <th class="text-center">Detalles</th>
                                    </tr>
                                </thead>
                                <tfoot>
                                    <tr>
                                        <th colspan="8"></th>
                                        <th></th>
                                        <th>
                                            <a id="ver-detalle-general" class="btn btn-danger btn-sm" target="_blank" title="Ver detalle">
                                                <i class="fas fa-file-pdf"></i> Ver detalle general
                                            </a>
                                        </th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php
        include_once("../../includes/footer.php");
    ?>

    <script>

        $(document).ready(function() {

            $('#proveedor').on('change', function() {
                if ($(this).val() !== "") {
                    $('#trabajador').prop('disabled', true);
                    const proveedorSeleccionado = $(this).val();
                    const $embarcacionSelect = $('#embarcacion2');
                    $.ajax({
                        url: '../maestro_de_movimientos/obtener_embarcaciones2.php',
                        method: 'POST',
                        data: { id_proveedor: proveedorSeleccionado },
                        dataType: 'json',
                        success: function(response) {
                            $embarcacionSelect.empty().append('<option value="">Seleccione una opción</option>'); 
                            if (response && response.length > 0) {
                                response.forEach(function(embarcacion) {
                                    $embarcacionSelect.append(
                                        `<option value="${embarcacion.id_embarcacion2}">${embarcacion.nombre_embarcacion2}</option>`
                                    );
                                });
                            } else {
                                Swal.fire('Error', 'No hay embarcaciones disponibles para este proveedor. <br><br>Para continuar debe asociar una Embarcación al Proveedor seleccionado.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al obtener las embarcaciones. Intente nuevamente.', 'error');
                        }
                    });
                } else {
                    const $embarcacionSelect = $('#embarcacion2');
                    $('#trabajador').prop('disabled', false);
                    $.ajax({
                        url: '../maestro_de_maestros/embarcaciones/obtener_todas.php',
                        method: 'POST',
                        dataType: 'json',
                        success: function(response) {
                            $embarcacionSelect.empty().append('<option value="">Todas</option>');
                            if (response && response.length > 0) {
                                response.forEach(function(embarcacion) {
                                    $embarcacionSelect.append(
                                        `<option value="${embarcacion.id_embarcacion2}">${embarcacion.nombre_embarcacion2}</option>`
                                    );
                                });
                            } else {
                                Swal.fire('Error', 'No hay embarcaciones disponibles.', 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'Error al obtener las embarcaciones. Intente nuevamente.', 'error');
                        }
                    });
                }
            });

            $('#trabajador').on('change', function() {
                if ($(this).val() !== "") {
                    $('#proveedor').prop('disabled', true);
                } else {
                    $('#proveedor').prop('disabled', false);
                }
            });

            const table = $('#reportes-table').DataTable({
                processing: true,
                serverSide: true,
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
                deferRender: true,
                order: [[1, 'desc']],
                ajax: {
                    url: 'fetch_reportes.php',
                    type: 'POST',
                    data: function(d) {
                        d.proveedor = $('#proveedor').val();
                        d.trabajador = $('#trabajador').val();
                        d.embarcacion = $('#embarcacion').val();
                        d.embarcacion2 = $('#embarcacion2').val();
                        d.tipo_movimiento = $('#tipo_movimiento').val();
                        d.fecha_inicio = $('#fecha_inicio').val();
                        d.fecha_fin = $('#fecha_fin').val();
                        d.clasificacion = $('#clasificacion').val();
                    },
                    dataSrc: function(json) {
                        const fmt = (n) => '$' + Number(n || 0).toLocaleString('es-CL', { minimumFractionDigits: 0 });
                        if (json.totales) {
                            window._reportesTotales = json.totales;
                            $('#t-count').text(json.totales.count);
                            $('#t-ingreso').text(fmt(json.totales.ingreso));
                            $('#t-egreso').text(fmt(json.totales.egreso));
                            $('#t-gastos').text(fmt(json.totales.gastos));
                            $('#t-pagos').text(fmt(json.totales.pagos));
                            $('#t-saldo').text(fmt(json.totales.saldo));
                            $('#reportes-resumen').show();
                        }
                        return json.data || [];
                    }
                },
                columns: [
                    { data: 'id_movimiento', class: 'text-center' },
                    { data: 'fecha', class: 'text-center',
                        render: function(data, type, row) {
                            if (type === 'display') {
                                // Convertir a formato dd-mm-yyyy solo para visualización
                                const dateParts = data.split('-'); // Dividir por guiones
                                return `${dateParts[2]}-${dateParts[1]}-${dateParts[0]}`;
                            }
                            return data; // Retornar el valor original para ordenación y filtrado
                        } 
                    },
                    { data: 'proveedor', class: 'text-center' },
                    { data: 'trabajador', class: 'text-center' },
                    { data: 'embarcacion', class: 'text-center' },
                    { data: 'embarcacion2', class: 'text-center' },
                    { data: 'clasificacion', class: 'text-center' },
                    { data: 'tipo_movimiento', class: 'text-center' },
                    { data: 'total', class: 'text-center', 
                        render: $.fn.dataTable.render.number(',', '.', 0, '$') 
                    },
                    {
                        data: 'id_movimiento', class: 'text-center',
                        render: function(data) {
                            return `
                                <a href='generar_pdf_detalle.php?id_movimiento=${data}' 
                                    class="btn btn-primary btn-sm" 
                                    target="_blank" 
                                    title="Ver detalle">
                                    <i class="fas fa-file-pdf"></i> Ver detalle
                                </a>
                            `;
                        }
                    }
                ],
                footerCallback: function(row, data, start, end, display) {
                    const api = this.api();
                    const fmt = (n) => '$' + Number(n || 0).toLocaleString('es-CL', { minimumFractionDigits: 0 });
                    const tot = window._reportesTotales;
                    if (tot) {
                        $(api.column(8).footer()).html(fmt(tot.general) + ' <span class="text-muted small">(todo el filtro)</span>');
                        return;
                    }
                    const total = api.column(8).data().reduce((a, b) => parseFloat(a) + parseFloat(b), 0);
                    $(api.column(8).footer()).html(fmt(total));
                },
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
                },
                responsive: true,
                lengthChange: true, // Permite seleccionar la cantidad de registros
                paging: true,       // Activa la paginación
                searching: true,    // Activa la barra de búsqueda
                ordering: true      // Activa la ordenación de columnas
            });

            // Función para obtener IDs visibles y actualizar el enlace
            function actualizarBotonDetalleGeneral() {
                $('#ver-detalle-general').off('click').on('click', function (e) {
                    e.preventDefault();

                    const filtro = {
                        proveedor:     $('#proveedor').val()     || '',
                        trabajador:    $('#trabajador').val()    || '',
                        embarcacion:   $('#embarcacion').val()   || '',
                        embarcacion2:  $('#embarcacion2').val()  || '',
                        clasificacion: $('#clasificacion').val() || '',
                        fecha_inicio:  $('#fecha_inicio').val()  || '',
                        fecha_fin:     $('#fecha_fin').val()     || '',
                        tipo_movimiento: $('#tipo_movimiento').val() || ''
                    };

                    const groupBy =
                        filtro.trabajador   ? 'trabajador'   :
                        filtro.proveedor    ? 'proveedor'    :
                        filtro.embarcacion  ? 'embarcacion'  :
                        filtro.embarcacion2 ? 'embarcacion2' : 'auto';

                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = 'generar_pdf_general.php';
                    form.target = '_blank';

                    const modeInput = document.createElement('input');
                    modeInput.type = 'hidden';
                    modeInput.name = 'export_mode';
                    modeInput.value = 'filters';
                    form.appendChild(modeInput);

                    // Filtros
                    Object.entries(filtro).forEach(([k, v]) => {
                        const inp = document.createElement('input');
                        inp.type = 'hidden';
                        inp.name = k;
                        inp.value = v;
                        form.appendChild(inp);
                    });

                    const g = document.createElement('input');
                    g.type = 'hidden'; g.name = 'group_by'; g.value = groupBy;
                    form.appendChild(g);

                    document.body.appendChild(form);
                    form.submit();
                    form.remove();
                });

            }

            // Evento para actualizar el botón al redibujar la tabla
            table.on('draw', function () {
                actualizarBotonDetalleGeneral();
            });

            // Llamar a la función al inicio para establecer el enlace inicial
            actualizarBotonDetalleGeneral();

            // Evento de clic en el botón de filtro
            $('#filtrar').on('click', function () {
                table.ajax.reload(null, false); // Recargar datos sin reiniciar la tabla
            });

        });
    </script>