<?php
    require __DIR__ . '/../../../includes/require_admin.php';

    include_once("../../../includes/header.php");
    require '../../../includes/db_connect.php';
?>

<div class="container-fluid px-4">
    <div class="pes-page-header"><h1 class="h3 mb-0">Gestión de Usuarios</h1>
    <p class="mb-2">Aquí puede agregar, editar y desactivar usuarios.</p></div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow pes-card">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Listado de Usuarios</h6>
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
                                    <th class="text-center">Apellido</th>
                                    <th class="text-center">Correo</th>
                                    <th class="text-center">RUT</th>
                                    <th class="text-center">Rol</th>
                                    <th class="text-center">Estado</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                    $query = "SELECT id_usuario, nombre, apellido, correo, rut, id_rol, estado FROM usuarios WHERE eliminado = 0";
                                    $result = mysqli_query($connect, $query);

                                    while ($row = mysqli_fetch_assoc($result)) {
                                        echo "<tr>";
                                            echo "<td class='text-center'>" . $row['nombre'] . "</td>";
                                            echo "<td class='text-center'>" . $row['apellido'] . "</td>";
                                            echo "<td class='text-center'>" . $row['correo'] . "</td>";
                                            echo "<td class='text-center'>" . $row['rut'] . "</td>";
                                            echo "<td class='text-center'>" . ($row['id_rol'] == 1 ? 'Administrador' : 'Trabajador') . "</td>";
                                            echo "<td class='text-center'>" . ($row['estado'] == 1 ? 'Activo' : 'Inactivo') . "</td>";
                                            echo "<td class='text-center'>";
                                            echo "<a href='#' class='btn btn-warning btn-sm text-center btnEditar' data-id='" . $row['id_usuario'] . "'>Editar</a> ";
                                            if($row['estado'] == 1){
                                                echo "<a href='#' class='btn btn-danger btn-sm text-center btnEliminar' data-id='" . $row['id_usuario'] . "'>Desactivar</a> ";
                                            }else{
                                                echo "<a href='#' class='btn btn-success btn-sm text-center btnActivar' data-id='" . $row['id_usuario'] . "'>Activar</a> ";
                                            }
                                            if($_SESSION['id_rol'] == 1){
                                                echo "<a href='#' class='btn btn-danger btn-sm text-center btnEliminar2' data-id='" . $row['id_usuario'] . "'><i class='fa fa-times' aria-hidden='true'></i></a>";
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
                                        <h5 class="modal-title" id="modalAgregarUsuarioLabel">Agregar Usuario</h5>
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
                                            <label for="apellido">Apellido</label>
                                            <input type="text" class="form-control" id="apellido" name="apellido" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="correo">Correo</label>
                                            <input type="email" class="form-control" id="correo" name="correo" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="rut">RUT</label>
                                            <input type="search" oninput="checkRut(this)" class="form-control" id="rut" name="rut" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="password">Contraseña</label>
                                            <input type="password" class="form-control" id="password" name="password" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_rol">Rol</label>
                                            <select class="form-control" id="id_rol" name="id_rol" required>
                                                <option value="1">Administrador</option>
                                                <option value="2">Trabajador</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                        <button type="submit" class="btn btn-primary">Guardar Usuario</button>
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
                                        <h5 class="modal-title" id="modalEditarLabel">Editar Usuario</h5>
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
                                            <label for="apellido">Apellido</label>
                                            <input type="text" class="form-control" id="apellido2" name="apellido" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="correo">Correo</label>
                                            <input type="email" class="form-control" id="correo2" name="correo" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="rut">RUT</label>
                                            <input type="text" autocomplete="off" oninput="checkRut(this)" class="form-control" id="rut2" name="rut" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_rol">Rol</label>
                                            <select class="form-control" id="id_rol2" name="id_rol" required>
                                                <option value="1">Administrador</option>
                                                <option value="2">Trabajador</option>
                                            </select>
                                        </div>
                                        <div class="form-group">
                                            <label for="id_rol">Nueva contraseña (opcional)</label>
                                            <input type="search" autocomplete="off" class="form-control" id="password2" name="password">
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
                        'Usuario agregado',
                        'El usuario ha sido agregado correctamente.',
                        'success'
                    ).then(() => {
                        location.reload(); // Recargar la página
                    });
                },
                error: function() {
                    Swal.fire(
                        'Error',
                        'No se pudo agregar el usuario.',
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