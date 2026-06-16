<?php
session_start();
if (!isset($_SESSION["correo_usuario"])) {
    header("location: ../../login.php");
    exit;
}

include_once("../../includes/header.php");
?>

<div class="container-fluid px-4">
    <div class="pes-page-header">
        <h1 class="h3 mb-0">Movimientos</h1>
        <p class="mb-0">En esta sección podrá visualizar y descargar los movimientos registrados.</p>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-12">
            <div class="card shadow pes-card">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold">Lista de movimientos</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive pes-table-wrap">
                        <table class="table table-bordered pes-table" id="tablaMovimientos" width="100%" cellspacing="0">
                            <thead>
                                <tr>
                                    <th class="text-center">N° Mov</th>
                                    <th class="text-center">Tipo</th>
                                    <th class="text-center">Fecha</th>
                                    <th class="text-center">Acarreo</th>
                                    <th class="text-center">Embarcacion</th>
                                    <th class="text-center">Proveedor</th>
                                    <th class="text-center">Trabajador</th>
                                    <th class="text-center">Total</th>
                                    <th class="text-center">Acciones</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once("../../includes/footer.php"); ?>

<script>
$(document).ready(function () {
    $('#tablaMovimientos').DataTable({
        processing: true,
        serverSide: true,
        pageLength: 25,
        lengthMenu: [[10, 25, 50, 100], [10, 25, 50, 100]],
        order: [[0, 'desc']],
        ajax: {
            url: 'listar_movimientos.php',
            type: 'POST'
        },
        columns: [
            { data: 'id_movimiento', className: 'text-center' },
            { data: 'tipo_movimiento', className: 'text-center' },
            { data: 'fecha', className: 'text-center' },
            { data: 'nombre_embarcacion', className: 'text-center' },
            { data: 'nombre_embarcacion2', className: 'text-center' },
            { data: 'nombre_proveedor', className: 'text-center' },
            { data: 'nombre_trabajador', className: 'text-center' },
            { data: 'total_fmt', className: 'text-center pes-total-cell', orderable: false },
            { data: 'acciones', className: 'text-center', orderable: false, searchable: false }
        ],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.5/i18n/es-ES.json'
        }
    });
});

$(document).on('click', '.btnEliminar', function(e) {
    e.preventDefault();
    var id = $(this).data('id');
    Swal.fire({
        title: '¿Está seguro?',
        text: 'El movimiento será eliminado del Sistema.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminarlo',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: 'eliminar.php',
                type: 'POST',
                data: { id_usuario: id },
                success: function(response) {
                    Swal.fire('¡Movimiento eliminado!', response, 'success').then(() => {
                        $('#tablaMovimientos').DataTable().ajax.reload(null, false);
                    });
                },
                error: function() {
                    Swal.fire('Error', 'No se pudo eliminar el movimiento.', 'error');
                }
            });
        }
    });
});
</script>
