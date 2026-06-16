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
    <div class="pes-page-header"><h1 class="h3 mb-0">Gestión de Trabajadores</h1>
    <p class="mb-2">Aquí puede agregar, editar y desactivar trabajadores.</p></div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow pes-card">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Listado de Trabajadores</h6>
                </div>
                <div class="card-header py-3 pes-card-header">
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalAgregarUsuario">
                        Agregar
                    </button>
                </div>
                <div class="card-body">
                    <div class="table-responsive pes-table-wrap">
                        <table class="table table-bordered pes-table" id="dataTable" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">Nombre</th>
                                    <th class="text-center">RUT</th>
                                    <th class="text-center">Teléfono</th>
                                    <th class="text-center">Domicilio</th>
                                    <th class="text-center">Embarcación</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $query = "  SELECT 
                                                    t.*,
                                                    (
                                                        SELECT GROUP_CONCAT(e.nombre_embarcacion2 ORDER BY e.nombre_embarcacion2 SEPARATOR ', ')
                                                        FROM embarcaciones2 e
                                                        WHERE FIND_IN_SET(e.id_embarcacion2, t.embarcacion)
                                                    ) AS embarcaciones
                                                FROM trabajadores t
                                                WHERE t.eliminado = 0";
                                    $result = mysqli_query($connect, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                            echo "<td class='text-center'>" . $row['nombre_trabajador'] . "</td>";
                                            echo "<td class='text-center'>" . $row['rut_trabajador'] . "</td>";
                                            echo "<td class='text-center'>" . $row['telefono_trabajador'] . "</td>";
                                            echo "<td class='text-center'>" . $row['domicilio_trabajador'] . "</td>";
                                            echo "<td class='text-center'>" . $row['embarcaciones'] . "</td>";
                                            echo "<td class='text-center'>" . ($row['estado'] == 1 ? 'Activo' : 'Inactivo') . "</td>";
                                            echo "<td class='text-center'>";
                                            echo "<a href='#' class='btn btn-warning btn-sm text-center btnEditar' data-id='" . $row['id_trabajador'] . "'>Editar</a> ";
                                            if($row['estado'] == 1){
                                                echo "<a href='#' class='btn btn-danger btn-sm text-center btnEliminar' data-id='" . $row['id_trabajador'] . "'>Desactivar</a> ";
                                            }else{
                                                echo "<a href='#' class='btn btn-success btn-sm text-center btnActivar' data-id='" . $row['id_trabajador'] . "'>Activar</a> ";
                                            }
                                            if($_SESSION['id_rol'] == 1){
                                                echo "<a href='#' class='btn btn-danger btn-sm text-center btnEliminar2' data-id='" . $row['id_trabajador'] . "'><i class='fa fa-times' aria-hidden='true'></i></a>";
                                            }
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
                                        <h5 class="modal-title" id="modalAgregarUsuarioLabel">Agregar Trabajador</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="rut">RUT</label>
                                            <input type="search" oninput="checkRut(this)" class="form-control" id="rut" name="rut" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono" name="telefono" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="domicilio">Domicilio</label>
                                            <input type="text" class="form-control" id="domicilio" name="domicilio" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="domicilio">Embarcacion</label>
                                            <select class="form-control" id="embarcacion" name="embarcacion" required>
                                                <?php
                                                    $sql = "SELECT id_embarcacion2, nombre_embarcacion2 FROM embarcaciones2";
                                                    $resultado = $connect->query($sql);
                                                    if ($resultado->num_rows > 0) {
                                                        echo "<option selected value=''>Seleccione una embarcación</option>";
                                                        while ($fila = $resultado->fetch_assoc()) {
                                                            echo "<option value='" . $fila['id_embarcacion2'] . "'>" . $fila['nombre_embarcacion2'] . "</option>";
                                                        }
                                                    } else {
                                                        echo "<option selected value=''>No hay embarcaciones disponibles</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Trabajador</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Editar Trabajador -->
                    <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="formEditar" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditarLabel">Editar Trabajador</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <input type="hidden" id="id_usuario" name="id_usuario">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" class="form-control" id="nombre2" name="nombre" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="rut">RUT</label>
                                            <input type="text" autocomplete="off" oninput="checkRut(this)" class="form-control" id="rut2" name="rut" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="telefono">Teléfono</label>
                                            <input type="text" class="form-control" id="telefono2" name="telefono" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="domicilio">Domicilio</label>
                                            <input type="text" class="form-control" id="domicilio2" name="domicilio" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="domicilio">Embarcacion</label>
                                            <select class="form-control" id="embarcacion2" name="embarcacion" required>
                                                <?php
                                                    $sql = "SELECT id_embarcacion2, nombre_embarcacion2 FROM embarcaciones2";
                                                    $resultado = $connect->query($sql);
                                                    while ($fila = $resultado->fetch_assoc()) {
                                                        echo "<option value='" . $fila['id_embarcacion2'] . "'>" . $fila['nombre_embarcacion2'] . "</option>";
                                                    }
                                                ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                                    </div>
                                </form>
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
        // Limpia los campos del formulario al abrir el modal de Agregar Trabajador
        $('#modalAgregarUsuario').on('show.bs.modal', function () {
            $('#formAgregarUsuario')[0].reset(); // Resetea todos los campos del formulario
        });

        // Limpia los campos del formulario al abrir el modal de Editar Trabajador
        $('#modalEditarUsuario').on('show.bs.modal', function () {
            $('#formEditarUsuario')[0].reset(); // Resetea todos los campos del formulario
        });
    });
</script>

<script>
    $(document).ready(function() {
        // Enviar el formulario de agregar trabajador por AJAX
        $('#formAgregarUsuario').on('submit', function(e) {
            e.preventDefault(); // Evitar que se recargue la página
            $.ajax({
                type: 'POST',
                url: 'agregar.php',
                data: $(this).serialize(),
                success: function(response) {
                    Swal.fire(
                        'Trabajador agregado',
                        'El trabajador ha sido agregado correctamente.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página
                    });
                },
                error: function() {
                    Swal.fire(
                        'Error',
                        'No se pudo agregar el trabajador.',
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
            text: "El trabajador no estará disponible para crear movimientos.",
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
                            title: '¡Trabajador desactivado!',
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
                            'No se pudo desactivar el trabajador.',
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
            text: "El trabajador no se mostrará más en el sistema.",
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
                            title: '¡Trabajador eliminado!',
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
                            'No se pudo eliminar el trabajador.',
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
            text: "El trabajador estará disponible para crear movimientos.",
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
                            title: '¡Trabajador activado!',
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
                            'No se pudo activar el trabajador.',
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
                $('#id_usuario').val(data.id_trabajador);
                $('#nombre2').val(data.nombre_trabajador);
                $('#rut2').val(data.rut_trabajador);
                $('#telefono2').val(data.telefono_trabajador);
                $('#domicilio2').val(data.domicilio_trabajador);
                $('#embarcacion2').val(data.embarcacion);
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
                    '¡Trabajador editado!',
                    'Los datos del trabajador han sido actualizados.',
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