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
    <div class="pes-page-header"><h1 class="h3 mb-0">Gestión de Clasificaciones</h1>
    <p class="mb-2">Aquí puede agregar, editar y desactivar clasificaciones.</p></div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow pes-card">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Listado de Clasificaciones</h6>
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
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $query = "SELECT id_clasificacion, nombre_clasificacion, estado FROM clasificaciones WHERE eliminado = 0";
                                    $result = mysqli_query($connect, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                            echo "<td class='text-center'>" . $row['nombre_clasificacion'] . "</td>";
                                            echo "<td class='text-center'>" . ($row['estado'] == 1 ? 'Activo' : 'Inactivo') . "</td>";
                                            echo "<td class='text-center'>";
                                            echo "<a href='#' class='btn btn-warning btn-sm text-center btnEditar' data-id='" . $row['id_clasificacion'] . "'>Editar</a> ";
                                            if($row['estado'] == 1){
                                                echo "<a href='#' class='btn btn-danger btn-sm text-center btnEliminar' data-id='" . $row['id_clasificacion'] . "'>Desactivar</a> ";
                                            }else{
                                                echo "<a href='#' class='btn btn-success btn-sm text-center btnActivar' data-id='" . $row['id_clasificacion'] . "'>Activar</a> ";
                                            }
                                            if($_SESSION['id_rol'] == 1){
                                                echo "<a href='#' class='btn btn-danger btn-sm text-center btnEliminar2' data-id='" . $row['id_clasificacion'] . "'><i class='fa fa-times' aria-hidden='true'></i></a>";
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
                                        <h5 class="modal-title" id="modalAgregarUsuarioLabel">Agregar Clasificación</h5>
                                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label for="nombre">Nombre</label>
                                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Clasificación</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Editar Usuario -->
                    <div class="modal fade" id="modalEditar" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <form id="formEditar" method="POST">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modalEditarLabel">Editar Clasificación</h5>
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
                        'Clasificación agregado',
                        'El clasificacion ha sido agregado correctamente.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página
                    });
                },
                error: function() {
                    Swal.fire(
                        'Error',
                        'No se pudo agregar el clasificacion.',
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
            text: "La clasificacion no estará disponible para crear movimientos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, desactivarla',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar.php',
                    type: 'POST',
                    data: { id_usuario: id },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Clasificación desactivado!',
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
                            'No se pudo desactivar la clasificacion.',
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
            text: "La clasificacion no se mostrará más en el sistema.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminarla',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: 'eliminar2.php',
                    type: 'POST',
                    data: { id_usuario: id },
                    success: function(response) {
                        Swal.fire({
                            title: '¡Clasificación eliminada!',
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
                            'No se pudo eliminar la clasificacion.',
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
            text: "El clasificacion estará disponible para crear movimientos.",
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
                            title: '¡Clasificación activado!',
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
                            'No se pudo activar el clasificacion.',
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
                $('#id_usuario').val(data.id_clasificacion);
                $('#nombre2').val(data.nombre_clasificacion);
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
                    '¡Clasificación editado!',
                    'Los datos del clasificacion han sido actualizados.',
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