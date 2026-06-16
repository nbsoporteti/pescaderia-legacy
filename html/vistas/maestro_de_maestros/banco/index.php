<?php
    session_start();
    if (!isset($_SESSION["correo_usuario"])) {
        header("location: ../../../login.php");
        exit;
    }

    include_once("../../../includes/header.php");
    require '../../../includes/db_connect.php';
?>

<div class="container-fluid px-4">
    <div class="pes-page-header"><h1 class="h3 mb-0">Gestión de Banco</h1>
    <p class="mb-2">Aquí podrá ver todo lo relacionado a cheques.</p></div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow pes-card">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Listado de Cheques</h6>
                </div>
                <div class="card-header py-3 pes-card-header">
                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#modalAgregarUsuario">
                        Agregar cheque nulo
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive pes-table-wrap">
                        <table class="table table-bordered pes-table" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">ID Movimiento</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Monto</th>
                                    <th class="text-center">Nro de cheque</th>
                                    <th class="text-center">Clasificacion</th>
                                    <th class="text-center">Estado actual</th>
                                    <th class="text-center">Comentario</th>
                                    <th class="text-center">Cambiar estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $query = "  SELECT 
                                                    b.id_banco,
                                                    IFNULL(b.comentario, '-') AS comentario,
                                                    IFNULL(md.id_movimiento, '-') AS id_movimiento,
                                                    c.nombre_clasificacion,
                                                    DATE_FORMAT(md.fecha_cheque, '%d-%m-%Y') AS fecha_cheque,
                                                    CONCAT('$', FORMAT(md.monto_cheque, 0, 'es_CL')) AS monto_cheque,
                                                    IFNULL(md.nro_cheque, '-') AS nro_cheque,
                                                    b.estado as estado_reciente
                                                FROM movimientos_detalle md
                                                JOIN movimientos m ON md.id_movimiento = m.id_movimiento
                                                JOIN clasificaciones c ON md.clasificacion = c.id_clasificacion
                                                LEFT JOIN banco b ON b.id_banco = (
                                                    SELECT b2.id_banco 
                                                    FROM banco b2 
                                                    WHERE b2.id_movimiento_detalle = md.id_movimiento 
                                                    ORDER BY b2.id_banco DESC 
                                                    LIMIT 1
                                                )
                                                WHERE md.fecha_cheque IS NOT NULL
                                                AND m.eliminado = 0

                                                UNION

                                                SELECT 
                                                    b.id_banco,
                                                    IFNULL(b.comentario, '-') AS comentario,
                                                    '-' AS id_movimiento,
                                                    c.nombre_clasificacion,
                                                    DATE_FORMAT(b.fecha, '%d-%m-%Y') AS fecha_cheque,
                                                    CONCAT('$', FORMAT(b.monto, 0, 'es_CL')) AS monto_cheque,
                                                    IFNULL(b.nro_cheque, '-') AS nro_cheque,
                                                    b.estado as estado_reciente
                                                FROM banco b
                                                JOIN clasificaciones c ON b.clasificacion = c.id_clasificacion
                                                WHERE b.id_movimiento_detalle IS NULL
                                                AND b.fecha IS NOT NULL;";                    
                                    $result = mysqli_query($connect, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                            echo "<td class='text-center'>" . $row['id_movimiento'] . "</td>";
                                            echo "<td class='text-center'>" . $row['fecha_cheque'] . "</td>";
                                            echo "<td class='text-center'>" . $row['monto_cheque'] . "</td>";
                                            echo "<td class='text-center'>" . $row['nro_cheque'] . "</td>";
                                            echo "<td class='text-center'>" . $row['nombre_clasificacion'] . "</td>";
                                            echo "<td class='text-center'>";
                                                if ($row['estado_reciente'] === NULL || $row['estado_reciente'] == 0) {
                                                    echo '<button class="btn btn-primary">Ingresado</button>';
                                                } elseif ($row['estado_reciente'] == 1) {
                                                    echo '<button class="btn btn-warning">Pendiente</button>';
                                                } elseif ($row['estado_reciente'] == 2) {
                                                    echo '<button class="btn btn-success">Cobrado</button>';
                                                } elseif ($row['estado_reciente'] == 3) {
                                                    echo '<button class="btn btn-danger">Nulo</button>';
                                                } else {
                                                    echo "Estado desconocido"; // En caso de que el valor no sea uno de los esperados
                                                }
                                            echo "</td>";
                                            echo "<td class='text-center'>" . $row['comentario'] . "</td>";
                                            echo "<td class='text-center'>";                                             
                                            echo "<select class='form-control estado-select' data-id='" . $row['id_movimiento'] . "'>";
                                                echo "<option value='0' " . ($row['estado_reciente'] === NULL || $row['estado_reciente'] == 0 ? 'selected' : '') . ">Ingresado</option>";
                                                echo "<option value='1' " . ($row['estado_reciente'] == 1 ? 'selected' : '') . ">Pendiente</option>";
                                                echo "<option value='2' " . ($row['estado_reciente'] == 2 ? 'selected' : '') . ">Cobrado</option>";
                                                echo "<option value='3' " . ($row['estado_reciente'] == 3 ? 'selected' : '') . ">Nulo</option>";
                                            echo "</select>";
                                            echo "</td>";
                                        echo "</tr>";
                                    }                                                      
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="modal fade" id="modalAgregarUsuario" tabindex="-1" role="dialog" aria-labelledby="modalAgregarUsuarioLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="formAgregarUsuario" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalAgregarUsuarioLabel">Agregar Cheque Nulo</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="fecha">Fecha</label>
                                            <input type="date" class="form-control" id="fecha" name="fecha" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="monto">Monto</label>
                                            <input type="text" oninput="formatearMonto(event)" class="form-control" id="monto" name="monto" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="nro_cheque">N° Cheque</label>
                                            <input type="int" class="form-control" id="nro_cheque" name="nro_cheque" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="clasificacion">Clasificación</label>
                                            <select class="form-control" name="clasificacion" id="clasificacion" required>
                                                <?php
                                                    $sql = "SELECT * 
                                                            FROM clasificaciones
                                                            WHERE nombre_clasificacion LIKE '%Banco%'
                                                            OR nombre_clasificacion LIKE '%BANCO%'
                                                            OR nombre_clasificacion LIKE '%Otros%'
                                                            AND estado = 1 AND eliminado = 0";
                                                    $resultado = mysqli_query($connect, $sql) or die("Error: " . mysqli_error($connect));
                                                
                                                    echo '<option selected value="">Seleccionar</option>';
                                                    while ($row = mysqli_fetch_assoc($resultado)) {
                                                        echo '<option value="'.$row['id_clasificacion'].'">'.$row['nombre_clasificacion'].'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalActualizarEstado" tabindex="-1" aria-labelledby="modalActualizarEstadoLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="modalActualizarEstadoLabel">Actualizar Estado</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <!-- Fecha de pago/cobro -->
                                <div class="mb-3">
                                <label for="fechaPago" class="form-label">Fecha de pago/cobro</label>
                                <input type="date" class="form-control" id="fechaPago" required>
                                </div>
                                <!-- Comentarios -->
                                <div class="mb-3">
                                <label for="comentarios" class="form-label">Comentarios</label>
                                <textarea class="form-control" id="comentarios"></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="button" class="btn btn-primary" id="confirmarCambio">Confirmar</button>
                            </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
<?php
    include_once("../../../includes/footer.php");
?>
<script>
    function formatearMonto(event) {
        let valor = event.target.value;
        let valorNumerico = valor.replace(/\D/g, '');
        let valorFormateado = valorNumerico.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        event.target.value = '$' + valorFormateado;
        $(event.target).data('valor', valorNumerico);
    }
</script>

<script>
    $(document).on('change', '.estado-select', function() {
        let id_movimiento = $(this).data('id');
        let nuevoEstado = $(this).val();

        // Abrir el modal para confirmar el cambio de estado
        $('#modalActualizarEstado').modal('show');

        // Al confirmar en el modal
        $('#confirmarCambio').click(function() {
            // Obtener los valores de los campos del modal
            let fechaPago = $('#fechaPago').val();
            let comentarios = $('#comentarios').val();

            // Validar que la fecha de pago/cobro esté seleccionada
            if (!fechaPago) {
                Swal.fire('Error', 'La fecha de pago/cobro es requerida.', 'error');
                return; // Salir si no hay fecha
            }

            // Realizar la petición AJAX
            $.ajax({
                url: 'actualizar_estado.php',
                method: 'POST',
                data: { 
                    id_movimiento: id_movimiento, 
                    estado: nuevoEstado, 
                    fecha_pago: fechaPago, 
                    comentarios: comentarios 
                },
                success: function(response) {
                    Swal.fire(
                        'Cheque actualizado',
                        'El estado del cheque ha sido actualizado correctamente.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página
                    });
                    $('#modalActualizarEstado').modal('hide'); // Cerrar el modal
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo actualizar el estado. Intente nuevamente.', 'error');
                }
            });
        });
    });
</script>

<script>
    $(document).ready(function() {
        $('#dataTable').DataTable({
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
            },
            "order": [[0, "asc"]],
            "pageLength": 10
        });
    });
</script>

<script>
    $(document).ready(function () {
        // Limpia los campos del formulario al abrir el modal de Agregar Usuario
        $('#modalAgregarUsuario').on('show.bs.modal', function () {
            $('#formAgregarUsuario')[0].reset(); // Resetea todos los campos del formulario
        });

        // Limpia los campos del formulario al abrir el modal de Editar Usuario
        $('#modalEditarUsuario').on('show.bs.modal', function () {
            $('#formEditarUsuario')[0].reset(); // Resetea todos los campos del formulario
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Enviar el formulario de agregar usuario por AJAX
        $('#formAgregarUsuario').on('submit', function(e) {
            e.preventDefault(); // Evitar que se recargue la página
            $.ajax({
                type: 'POST',
                url: 'agregar.php',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire(
                        'Cheque agregado',
                        'El cheque nulo ha sido agregado correctamente.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página
                    });
                },
                error: function() {
                    Swal.fire(
                        'Error',
                        'No se pudo agregar el cheque.',
                        'error'
                    );
                }
            });
        });
    });
</script>

<script>
    $(document).on('click', '.btnEliminar', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?',
            text: "El usuario no podrá ingresar al Sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar.php',
                    type: 'POST',
                    data: { id_usuario: id },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Usuario desactivado!',
                            text: response,
                            icon: 'success',
                            allowOutsideClick: true, // Permite cerrar el modal al hacer clic afuera
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error',
                            'No se pudo desactivar el usuario.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).on('click', '.btnEliminar2', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?',
            text: "El usuario no se mostrará más en el Sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar2.php',
                    type: 'POST',
                    data: { id_usuario: id },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Usuario eliminado!',
                            text: response,
                            icon: 'success',
                            allowOutsideClick: true, // Permite cerrar el modal al hacer clic afuera
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error',
                            'No se pudo eliminar el usuario.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).on('click', '.btnActivar', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        Swal.fire({
            title: '¿Está seguro?',
            text: "El usuario podrá ingresar al Sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, activarlo',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'activar.php',
                    type: 'POST',
                    data: { id_usuario: id },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Usuario activado!',
                            text: response,
                            icon: 'success',
                            allowOutsideClick: true, // Permite cerrar el modal al hacer clic afuera
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr, status, error) {
                        Swal.fire(
                            'Error',
                            'No se pudo activar el usuario.',
                            'error'
                        );
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).on('click', '.btnEditar', function(e) {
        e.preventDefault();
        var id = $(this).data('id');
        $.ajax({
            url: 'obtener.php',
            type: 'POST',
            data: { id_usuario: id },
            dataType: 'json',
            success: function(data) {
                $('#id_usuario').val(data.id_usuario);
                $('#nombre2').val(data.nombre);
                $('#apellido2').val(data.apellido);
                $('#correo2').val(data.correo);
                $('#rut2').val(data.rut);
                $('#id_rol2').val(data.id_rol);
                $('#modalEditar').modal('show');
            }
        });
    });
</script>

<script type="text/javascript">
    $(document).on('submit', '#formEditar', function(e) {
        e.preventDefault();

        // Recoger los datos del formulario
        var formData = $(this).serialize();

        $.ajax({
            url: 'editar.php', // Archivo PHP que procesará la actualización
            type: 'POST',
            data: formData,
            success: function(response) {
                Swal.fire(
                    '¡Usuario editado!',
                    'Los datos del usuario han sido actualizados.',
                    'success'
                ).then(() => {
                    $('#modalEditar').modal('hide');
                    location.reload();
                });
            },
            error: function(xhr, status, error) {
                Swal.fire(
                    'Error',
                    'Hubo un problema al enviar los datos.',
                    'error'
                );
            }
        });
    });
</script>

<script type="text/javascript">
    function checkRut(rut) {
        var valor = rut.value.replace('.', '');
        valor = valor.replace('-', '');
        cuerpo = valor.slice(0, -1);
        dv = valor.slice(-1).toUpperCase();
        rut.value = cuerpo + '-' + dv
        if (cuerpo.length < 7) {
            rut.setCustomValidity("RUT Incompleto");
            return false;
        }
        suma = 0;
        multiplo = 2;
        for (i = 1; i <= cuerpo.length; i++) {
            index = multiplo * valor.charAt(cuerpo.length - i);
            suma = suma + index;
            if (multiplo < 7) {
                multiplo = multiplo + 1;
            } else {
                multiplo = 2;
            }
        }
        dvEsperado = 11 - (suma % 11);
        dv = (dv == 'K') ? 10 : dv;
        dv = (dv == 0) ? 11 : dv;
        if (dvEsperado != dv) {
            rut.setCustomValidity("RUT Inválido");
            return false;
        }
        rut.setCustomValidity('');
    }
</script>