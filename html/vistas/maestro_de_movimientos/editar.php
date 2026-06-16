<?php

    session_start();

    if(!isset($_SESSION["correo_usuario"])):
        header("location: ../../login.php");
    endif;

    require '../../includes/db_connect.php';

    include_once("../../includes/header.php");

    // Obtener el id del movimiento desde la URL
    $id_movimiento = isset($_GET['id_movimiento']) ? $_GET['id_movimiento'] : null;

    // Si no se proporciona un id_movimiento, redirigir o mostrar un mensaje de error
    if ($id_movimiento === null) {
        echo "ID de movimiento no proporcionado.";
        exit();
    }

    // Consultar los detalles del movimiento
    $sql = "SELECT m.*, e.nombre_embarcacion AS embarcacion_nombre, f.nombre_embarcacion2 AS embarcacion2_nombre, p.nombre_proveedor, t.nombre_trabajador
            FROM movimientos m
            LEFT JOIN embarcaciones e ON m.embarcacion = e.id_embarcacion
            LEFT JOIN embarcaciones2 f ON m.embarcacion2 = f.id_embarcacion2
            LEFT JOIN proveedores p ON m.proveedor = p.id_proveedor
            LEFT JOIN trabajadores t ON m.trabajador = t.id_trabajador
            WHERE m.id_movimiento = $id_movimiento";
    $result = mysqli_query($connect, $sql);

    // Verificar si el movimiento existe
    if (mysqli_num_rows($result) > 0) {
        $movimiento = mysqli_fetch_assoc($result);
    } else {
        echo "Movimiento no encontrado.";
        exit();
    }

    // Guardar los datos del movimiento en variables para usarlas en el formulario
    $tipo = $movimiento['tipo_movimiento'];
    $fecha = $movimiento['fecha'];
    $embarcacion = $movimiento['embarcacion'];
    $embarcacion2 = $movimiento['embarcacion2'];
    $proveedor = $movimiento['proveedor'];
    $trabajador = $movimiento['trabajador'];
    $total = $movimiento['total'];
    $total = "$" . number_format($total, 0, ',', '.');

?>

<div class="container-fluid px-4">
    <div class="pes-page-header"><h1 class="h3 mb-0">Editar Movimiento</h1>
    <p class="mb-2">En esta sección podrá editar el movimiento con ID <b><?php echo $id_movimiento; ?></b>.</p>
    <p class="mb-2">Movimiento realizado por: <b><?php echo  $_SESSION["nombre_usuario"]." ".$_SESSION["apellido_usuario"]; ?></b></p></div>

    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="card shadow pes-card" id="imprimir">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Editar Movimiento</h6>
                </div>
                <div class="card-body">
                    <form method="POST" id="egreso_create">

                        <div class="d-flex justify-content-center align-items-center">
                            <div class="row">
                                <div class="form-group col-lg-12">
                                    <label for="tipo_movimiento" class="text-gray-600 font-weight-bold">Tipo de movimiento</label>
                                    <select class="form-control" name="tipo_movimiento" id="tipo_movimiento" disabled>
                                        <option value="1" <?= ($tipo == 1) ? 'selected' : '' ?>>Ingreso</option>
                                        <option value="2" <?= ($tipo == 2) ? 'selected' : '' ?>>Egreso</option>
                                        <option value="3" <?= ($tipo == 3) ? 'selected' : '' ?>>Gasto</option>
                                        <option value="4" <?= ($tipo == 4) ? 'selected' : '' ?>>Pago</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-lg-2">
                                <label for="fecha" class="text-gray-600">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" value="<?php echo $fecha; ?>" min="2020-01-01" max="<?php echo (date('Y') + 1); ?>-12-31" required />
                            </div>

                            <div class="form-group col-lg-2">
                                <label for="embarcacion" class="text-gray-600">Acarreo</label>
                                <select class="form-control" name="embarcacion" id="embarcacion" required>
                                    <option selected value="0">Seleccionar</option>
                                    <?php 
                                    $sql_embarcaciones = "SELECT * FROM embarcaciones WHERE estado = 1 AND eliminado = 0";
                                    $resultado_embarcaciones = mysqli_query($connect, $sql_embarcaciones);
                                    while ($row = mysqli_fetch_assoc($resultado_embarcaciones)) {
                                        $selected = ($row['id_embarcacion'] == $embarcacion) ? 'selected' : '';
                                        echo '<option value="'.$row['id_embarcacion'].'" '.$selected.'>'.$row['nombre_embarcacion'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <?php if($tipo != 3){
                                // Movimiento de trabajador (proveedor 0/NULL): no exigir proveedor ni embarcacion.
                                $option_proveedor = ($proveedor == 0 || $proveedor === null) ? "disabled" : "required";
                            ?>
                            <div class="form-group col-lg-3">
                                <label for="proveedor" class="text-gray-600">Proveedor</label>
                                <select class="form-control" name="proveedor" id="proveedor" <?php echo $option_proveedor; ?>>
                                    <option selected value="0">Seleccionar</option>
                                    <?php
                                    $sql_proveedores = "SELECT * FROM proveedores WHERE estado = 1 AND eliminado = 0";
                                    $resultado_proveedores = mysqli_query($connect, $sql_proveedores);
                                    while ($row = mysqli_fetch_assoc($resultado_proveedores)) {
                                        $selected = ($row['id_proveedor'] == $proveedor) ? 'selected' : '';
                                        echo '<option value="'.$row['id_proveedor'].'" '.$selected.'>'.$row['nombre_proveedor'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group col-lg-3">
                                <label for="embarcacion2" class="text-gray-600">Embarcación</label>
                                <select class="form-control" name="embarcacion2" id="embarcacion2" <?php echo $option_proveedor; ?>>
                                    <option selected value="0">Seleccionar</option>
                                    <?php 
                                    $sql_embarcacion2 = "
                                        SELECT * 
                                        FROM embarcaciones2 
                                        WHERE estado = 1 
                                        AND eliminado = 0 
                                        AND FIND_IN_SET(id_embarcacion2, (SELECT embarcacion FROM proveedores WHERE id_proveedor = $proveedor)) > 0
                                    ";                                
                                    $resultado_embarcacion2 = mysqli_query($connect, $sql_embarcacion2);
                                    while ($row = mysqli_fetch_assoc($resultado_embarcacion2)) {
                                        $selected = ($row['id_embarcacion2'] == $embarcacion2) ? 'selected' : '';
                                        echo '<option value="'.$row['id_embarcacion2'].'" '.$selected.'>'.$row['nombre_embarcacion2'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="form-group col-lg-2">
                                <label for="trabajador" class="text-gray-600">Trabajador</label>
                                <?php 
                                $option_trabajador = "";
                                if($trabajador == 0){
                                    $option_trabajador = "disabled";
                                }else{
                                    $option_trabajador = "required";
                                }
                                ?>
                                <select class="form-control" name="trabajador" id="trabajador" <?php echo $option_trabajador; ?>>
                                    <option selected value="0">Seleccionar</option>
                                    <?php 
                                    $sql_trabajadores = "SELECT * FROM trabajadores WHERE estado = 1 AND eliminado = 0";
                                    $resultado_trabajadores = mysqli_query($connect, $sql_trabajadores);
                                    while ($row = mysqli_fetch_assoc($resultado_trabajadores)) {
                                        $selected = ($row['id_trabajador'] == $trabajador) ? 'selected' : '';
                                        echo '<option value="'.$row['id_trabajador'].'" '.$selected.'>'.$row['nombre_trabajador'].'</option>';
                                    }
                                    ?>
                                </select>
                            </div>

                            <?php } ?>

                        </div>

                        <div id="lineas-dinamicas"></div>

                        <div class="row" id="div_total_final">
                            <div class="col-md-10"></div>
                            <div class="col-md-2">
                                <label for="total_final" class="text-gray-600 font-weight-bold">Total final</label>
                                <input type="text" value="<?php echo $total; ?>" id="total_final" class="form-control" readonly />
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="button" class="btn btn-primary" id="guardar">Actualizar movimiento</button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<?php
    include_once("../../includes/footer.php");
?>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script> 
    $(document).ready(function() {

        let idMovimiento = <?php echo $id_movimiento; ?>;

        $.ajax({
            url: 'obtener_movimientos.php',
            type: 'GET',
            data: { id_movimiento: idMovimiento },
            dataType: 'json',
            success: function(movimientos) {
                let html = '';

                movimientos.forEach((mov, index) => {

                    const anticipoValor = mov.cantidad_cheques >= 1 ? '0' : formatearMontoCLPInicial(mov.anticipo || 0);
                    const anticipoDisabled = mov.cantidad_cheques >= 1 ? 'disabled' : '';
                    const total = mov.cantidad_cheques >= 1 ? mov.monto_cheque : mov.total;

                    html += `
                        <div class="form-group">
                            <hr class="my-4 border-primary">
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="cheques_cantidad_${index}" class="text-gray-600">Cantidad de cheques</label>
                                    <input type="number" min="0" value="${mov.cantidad_cheques}" class="form-control cheques-cantidad" id="cheques_cantidad_${index}" name="cheques_cantidad[]" data-index="${index}" disabled />
                                </div>
                                <div class="col-md-2">
                                    <label for="anticipo_${index}" class="text-gray-600">Anticipo</label>
                                    <input type="text" min="0" value="${anticipoValor}" oninput="formatearMonto(event); actualizarTotalesFila(${index})" class="form-control" id="anticipo_${index}" name="anticipo[]" ${anticipoDisabled} required />
                                </div>
                                <div class="col-md-4">
                                    <label for="detalle_anticipo_${index}" class="text-gray-600">Detalle</label>
                                    <input type="text" class="form-control" value="${mov.detalle_anticipo}" id="detalle_anticipo_${index}" name="detalle_anticipo[]" required />
                                </div>
                                <div class="col-md-2">
                                    <label for="clasificacion_${index}" class="text-gray-600">Clasificación</label>
                                    <select class="form-control" id="clasificacion_${index}" name="clasificacion[]" required>
                                        ${mov.clasificacion_options}
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label for="total_${index}" class="text-gray-600 font-weight-bold">Total</label>
                                    <input type="text" value="${formatearMontoCLPInicial(total)}" oninput="formatearMonto(event);" class="form-control" id="total_${index}" name="total[]" readonly />
                                </div>
                            </div>
                        `;
                    
                    let filasCheques = '';
                    for (let i = 0; i < mov.cantidad_cheques; i++) {
                        filasCheques += `
                            ${i > 0 ? '<br>' : ''}
                            <div class="row">
                                <div class="col-md-2">
                                    <label for="fecha_cheque_${index}_${i}" class="text-gray-600">Fecha Cheque ${i + 1}</label>
                                    <input type="date" class="form-control" value="${mov.fecha_cheque}" id="fecha_cheque_${index}_${i}" name="fecha_cheque_${index}[]" required />
                                </div>
                                <div class="col-md-2">
                                    <label for="total_cheque_${index}_${i}" class="text-gray-600">Monto Cheque ${i + 1}</label>
                                    <input type="text" value="${formatearMontoCLPInicial(total)}" class="form-control cheque-monto" oninput="formatearMonto(event)" id="total_cheque_${index}_${i}" name="total_cheque_${index}[]" data-index="${index}" required />
                                </div>
                                <div class="col-md-2">
                                    <label for="nro_cheque_${index}_${i}" class="text-gray-600">N° Cheque ${i + 1}</label>
                                    <input type="int" min="0" step="1" value="${mov.nro_cheque}" class="form-control" id="nro_cheque_${index}_${i}" name="nro_cheque_${index}[]" required />
                                </div>
                            </div>`;
                    }

                    html += `<br>
                            <div id="cheques_filas_${index}">
                                ${filasCheques}
                            </div>
                        </div>`;
                });
                html += `<hr class="my-4 border-primary">`;

                $('#lineas-dinamicas').html(html);
            },
            error: function() {
                console.log('Error al cargar los movimientos.');
            }
        });
    });
</script>

<script type="text/javascript">

    function actualizarTotalesFila(index) {
        let anticipoValor = $('#anticipo_' + index).val().replace(/[^\d.-]/g, '');
        valor = anticipoValor.replace(/[^\d.-]/g, '');              

        if (valor.indexOf('.') !== -1) {
            valor = valor.replace(/\.(?=\d{3})/g, ''); 
        }

        if(valor == "" || valor == 0){
            $(`#cheques_cantidad_${index}`).prop('disabled', false).val(0);
        }else{
            $(`#cheques_cantidad_${index}`).prop('disabled', true).val(0);
        }

        let totalFila = parseFloat(valor) || 0;

        let total = 0;
        $(`[id^="total_cheque_${index}_"]`).each(function() {
            const valor = parseFloat($(this).val().replace(/\D/g, '')) || 0; 
            total += valor;
        });

        const total_total = total+totalFila; 

        $('#total_' + index).val(formatearMontoCLP(total_total));

        actualizarTotalFinal();
    }

    function actualizarTotalFinal(index) {

        let totalFinal = 0;
        $('[id^="total_"]').each(function() {
            const id = $(this).attr('id');
            
            if (/^total_\d+$/.test(id)) {
                let valor = $(this).val();

                valor = valor.replace(/[^\d.-]/g, '');
                
                if (valor.indexOf('.') !== -1) {
                    valor = valor.replace(/\.(?=\d{3})/g, '');
                }


                const numero = parseFloat(valor) || 0;

                totalFinal += numero;
            }
        });

        $('#total_final').val(formatearMontoCLP(totalFinal));
    }

    function formatearMontoCLPInicial(monto) {
        return Number(monto).toLocaleString('es-CL', { style: 'currency', currency: 'CLP' });
    }

    function formatearMontoCLP(monto) {
        return monto.toLocaleString('es-CL', { style: 'currency', currency: 'CLP' });
    }

    function formatearMonto(event) {
        let valor = event.target.value;
        let valorNumerico = valor.replace(/\D/g, '');
        let valorFormateado = valorNumerico.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        event.target.value = '$' + valorFormateado;
        $(event.target).data('valor', valorNumerico);
    }
    function actualizarTotal(index) {
        let total = 0;

        $(`[id^="total_cheque_${index}_"]`).each(function() {
            const valor = parseFloat($(this).val().replace(/\D/g, '')) || 0;
            total += valor;
        });

        let anticipoValor = $('#anticipo_' + index).val().replace(/[^\d.-]/g, '');
        valor = anticipoValor.replace(/[^\d.-]/g, '');          

        if (valor.indexOf('.') !== -1) {
            valor = valor.replace(/\.(?=\d{3})/g, ''); 
        }
        let totalFila = parseFloat(valor) || 0;

        const total_total = totalFila + total;

        const totalFormatted = formatearMontoCLP(total_total);
        $(`#total_${index}`).val(totalFormatted);
    }

    $(document).ready(function() {

        $(document).on('change', '#proveedor', function() {
            const proveedorSeleccionado = $(this).val();

            const $embarcacionSelect = $('#embarcacion2');
            $embarcacionSelect.empty().append('<option selected value="0">Seleccionar</option>').prop('disabled', true);

            if (proveedorSeleccionado != 0) {

                $.ajax({
                    url: 'obtener_embarcaciones2.php',
                    method: 'POST',
                    data: {
                        id_proveedor: proveedorSeleccionado
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response && response.length > 0) {
                            response.forEach(function(embarcacion) {
                                $embarcacionSelect.append(
                                    `<option value="${embarcacion.id_embarcacion2}">${embarcacion.nombre_embarcacion2}</option>`
                                );
                            });
                            $embarcacionSelect.prop('disabled', false);
                        } else {
                            $('#guardar').prop('disabled', true);
                            Swal.fire('Error', 'No hay embarcaciones disponibles para este proveedor. <br><br>Para continuar debe asociar una Embarcación al Proveedor seleccionado.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Error al obtener las embarcaciones. Intente nuevamente.', 'error');
                    }
                });

                $('#agregar-fila').show();
                $('#eliminar-fila').show();
                $(`#trabajador`).prop('disabled', true).val(0);
            } else {
                $(`#trabajador`).prop('disabled', false).val(0);
            }
        });

        $(document).on('change', '#trabajador', function() {
            const trabajadorSeleccionado = $(this).val();

            if (trabajadorSeleccionado != 0) {
                $('#agregar-fila').show();
                $('#eliminar-fila').show();
                $(`#proveedor`).prop('disabled', true).val(0);
            } else {
                $(`#proveedor`).prop('disabled', false).val(0);
            }
        });

        $(document).on('input', '.cheques-cantidad', function() {
            const cantidad = parseInt($(this).val(), 10);
            const index = $(this).data('index');
            const contenedor = $(`#cheques_filas_${index}`);
            contenedor.empty();

            if (cantidad === 0 || cantidad == "") {
                $(`#anticipo_${index}`).prop('disabled', false).val('$');
                $(`#detalle_anticipo_${index}`).prop('disabled', false).val('');
                $(`#total_${index}`).prop('readonly', false).val('');
            } else {
                $(`#anticipo_${index}`).prop('disabled', true).val(0);
                //$(`#detalle_anticipo_${index}`).prop('disabled', true).val('-');
                $(`#total_${index}`).prop('readonly', true).val('');
                actualizarTotal(index);
                actualizarTotalFinal(index);
            }

            if (!isNaN(cantidad) && cantidad > 0) {
                for (let i = 0; i < cantidad; i++) {
                    contenedor.append(`
                        <br>
                        <div class="row">
                            <div class="col-md-2">
                                <label for="fecha_cheque_${index}_${i}" class="text-gray-600">Fecha Cheque ${i + 1}</label>
                                <input type="date" class="form-control" id="fecha_cheque_${index}_${i}" name="fecha_cheque_${index}[]" required />
                            </div>
                            <div class="col-md-2">
                                <label for="total_cheque_${index}_${i}" class="text-gray-600">Monto Cheque ${i + 1}</label>
                                <input type="text" value="$" class="form-control cheque-monto" oninput="formatearMonto(event)" id="total_cheque_${index}_${i}" name="total_cheque_${index}[]" data-index="${index}" required />
                            </div>
                            <div class="col-md-2">
                                <label for="nro_cheque_${index}_${i}" class="text-gray-600">N° Cheque ${i + 1}</label>
                                <input type="int" min="0" step="1" class="form-control" id="nro_cheque_${index}_${i}" name="nro_cheque_${index}[]" required />
                            </div>
                        </div>
                    `);
                }
            }
        });

        $(document).on('input', '.cheque-monto', function() {
            const index = $(this).data('index');
            actualizarTotal(index);
            actualizarTotalFinal(index);
        });

        $('#guardar').click(function() {

            let valid = true;

            $('#egreso_create .form-group').each(function() {
                const input = $(this).find('select, input');
                const nombreCampo = input.attr('name');
                //console.log(`Campo: ${nombreCampo} | Valor: ${input.val()}`);

                if ( (input.prop('required') && !input.prop('disabled')) && (input.val() === "" || input.val() === null || input.val() == 0)) {
                    input.addClass('is-invalid');
                    valid = false;
                    Swal.fire('Error', 'Por favor, complete todos los campos requeridos.', 'error');
                    return false;
                } else {
                    input.removeClass('is-invalid');
                }

            });

            $('#lineas-dinamicas .form-group').each(function(index) {
                const anticipo = $(`#anticipo_${index}`).val().trim();
                //console.log(`Campo: anticipo | Valor: ${anticipo}`);
                const chequesCantidad = $(`#cheques_cantidad_${index}`).val().trim();
                //console.log(`Campo: chequesCantidad | Valor: ${chequesCantidad}`);
                const total = $(`#total_${index}`).val().trim();
                //console.log(`Campo: total (fila) | Valor: ${total}`);
                const detalleAnticipo = $(`#detalle_anticipo_${index}`).val();
                //console.log(`Campo: detalleAnticipo | Valor: ${detalleAnticipo}`);
                const clasificacion = $(`#clasificacion_${index}`).val();
                //console.log(`Campo: clasificacion | Valor: ${clasificacion}`);

                if ( (anticipo === "" || anticipo === "$" || anticipo == 0) && chequesCantidad == 0) {
                    $(`#anticipo_${index}`).addClass('is-invalid');
                    $(`#cheques_cantidad_${index}`).addClass('is-invalid');
                    valid = false;
                    Swal.fire('Error', 'Debe ingresar un anticipo o cheques.', 'error');
                    return false;
                }else{
                    $(`#anticipo_${index}`).removeClass('is-invalid');
                    $(`#cheques_cantidad_${index}`).removeClass('is-invalid');
                }

                if ($(`#detalle_anticipo_${index}`).prop('disabled') === false) {
                    if (detalleAnticipo === "") {
                        $(`#detalle_anticipo_${index}`).addClass('is-invalid');
                        valid = false;
                        Swal.fire('Error', 'Debe completar el detalle del anticipo.', 'error');
                        return false;
                    } else {
                        $(`#detalle_anticipo_${index}`).removeClass('is-invalid');
                    }
                }

                if (clasificacion === "") {
                    $(`#clasificacion_${index}`).addClass('is-invalid');
                    valid = false;
                    Swal.fire('Error', 'Debe seleccionar la clasificación.', 'error');
                    return false;
                } else {
                    $(`#clasificacion_${index}`).removeClass('is-invalid');
                }

                if(chequesCantidad >= 1){
                    for (let i = 0; i < chequesCantidad; i++) {
                        const fechaCheque = $(`#fecha_cheque_${index}_${i}`).val();
                        const montoCheque = $(`#total_cheque_${index}_${i}`).val();
                        const nroCheque = $(`#nro_cheque_${index}_${i}`).val();

                        if (fechaCheque === "" || fechaCheque === "$") {
                            $(`#fecha_cheque_${index}_${i}`).addClass('is-invalid');
                            valid = false; 
                            Swal.fire('Error', 'Por favor, complete la fecha del cheque.', 'error');
                            return false;
                        } else {
                            $(`#fecha_cheque_${index}_${i}`).removeClass('is-invalid');
                        }

                        if (montoCheque === "" || montoCheque === "$0" || montoCheque === "0" || montoCheque === "$") {
                            $(`#total_cheque_${index}_${i}`).addClass('is-invalid');
                            valid = false;
                            Swal.fire('Error', 'Por favor, complete el monto del cheque.', 'error');
                            return false;
                        } else {
                            $(`#total_cheque_${index}_${i}`).removeClass('is-invalid');
                        }

                        if (nroCheque === "" || nroCheque === "0") {
                            $(`#nro_cheque_${index}_${i}`).addClass('is-invalid');
                            valid = false;
                            Swal.fire('Error', 'Por favor, complete el número del cheque.', 'error');
                            return false;
                        } else {
                            $(`#nro_cheque_${index}_${i}`).removeClass('is-invalid');
                        }

                    }
                }

            });

            if (valid) {

                let idMovimiento = <?php echo $id_movimiento; ?>;

                var formData = new FormData();
                formData.append('id_movimiento', idMovimiento);
                formData.append('tipo_movimiento', $('#tipo_movimiento').val());
                formData.append('fecha', $('#fecha').val());
                formData.append('embarcacion', $('#embarcacion').val());

                if ($('#proveedor').length) {
                    formData.append('proveedor', $('#proveedor').val());
                } else {
                    formData.append('proveedor', null);
                }

                if ($('#embarcacion2').length) {
                    formData.append('embarcacion2', $('#embarcacion2').val());
                } else {
                    formData.append('embarcacion2', null);
                }

                if ($('#trabajador').length) {
                    formData.append('trabajador', $('#trabajador').val());
                } else {
                    formData.append('trabajador', null);
                }

                var totalFinal = $('#total_final').val();
                if (totalFinal.includes('$')) {
                    totalFinal = totalFinal.replace(/\$/g, '').replace(/\./g, '');
                    totalFinal = parseInt(totalFinal, 10);
                }
                formData.append('total_final', totalFinal);

                $('#lineas-dinamicas .form-group').each(function() {
                    $(this).find('input, select').each(function() {
                        var fieldName = $(this).attr('name');
                        var fieldValue = $(this).val();
                        var fieldId = $(this).attr('id'); // Obtener el ID del campo

                        // Si el ID empieza con "detalle_anticipo_", no modificar el valor
                        if (fieldId && fieldId.startsWith('detalle_anticipo_')) {
                            formData.append(fieldName, fieldValue);
                        } else {
                            // Si contiene $, eliminarlo junto con los puntos y convertirlo en número
                            if (fieldValue.includes('$')) {
                                fieldValue = fieldValue.replace(/\$/g, '').replace(/\./g, '');
                                fieldValue = parseInt(fieldValue, 10);
                            }
                            formData.append(fieldName, fieldValue);
                        }
                    });
                });

                $.ajax({
                    url: 'actualizacion.php',
                    method: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        try {
                            const data = JSON.parse(response);
                            if (data.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Correcto',
                                    text: data.message,
                                }).then(() => {
                                    // Recargar la página después de iniciar la descarga
                                    location.reload();
                                });
                            } else {
                                Swal.fire('Error', data.message, 'error');
                            }
                        } catch (e) {
                            Swal.fire('Error', 'Hubo un problema al procesar la respuesta.', 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('Error', 'Hubo un problema al procesar la solicitud.', 'error');
                    }
                });

            }
            
        });

    });
</script>