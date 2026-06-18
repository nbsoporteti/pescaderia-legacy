<?php

session_start();

if (!isset($_SESSION["correo_usuario"])) {
    header("location: login.php");
    exit;
}

require 'includes/db_connect.php';

$alerta = "";
$exito  = "";

// Mensaje de exito tras el redirect (patron PRG: evita reenvio al refrescar)
if (isset($_GET['ok'])) {
    $exito = "Su contraseña fue actualizada correctamente.";
}

if (isset($_POST['cambiar'])) {

    $actual  = isset($_POST['actual'])  ? $_POST['actual']  : '';
    $nueva   = isset($_POST['nueva'])   ? $_POST['nueva']   : '';
    $repetir = isset($_POST['repetir']) ? $_POST['repetir'] : '';
    $id      = $_SESSION['id_usuario'];

    // Hash actual del usuario en sesion (consulta preparada: evita inyeccion SQL)
    $stmt = $connect->prepare("SELECT password FROM usuarios WHERE id_usuario = ? LIMIT 1");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Verificacion de la clave actual: bcrypt o MD5 legacy (mismo criterio que login.php)
    $ok_actual = false;
    if ($row) {
        $stored = $row['password'];
        if (password_verify($actual, $stored)) {
            $ok_actual = true;
        } elseif (strlen($stored) === 32 && hash_equals(strtolower($stored), md5($actual))) {
            $ok_actual = true;
        }
    }

    if (!$ok_actual) {
        $alerta = "La contraseña actual es incorrecta.";
    } elseif (strlen($nueva) < 6) {
        $alerta = "La nueva contraseña debe tener al menos 6 caracteres.";
    } elseif ($nueva !== $repetir) {
        $alerta = "La nueva contraseña y su confirmación no coinciden.";
    } elseif ($nueva === $actual) {
        $alerta = "La nueva contraseña debe ser distinta de la actual.";
    } else {
        $nuevoHash = password_hash($nueva, PASSWORD_DEFAULT);
        $up = $connect->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
        $up->bind_param("si", $nuevoHash, $id);
        if ($up->execute()) {
            $up->close();
            header("Location: password.php?ok=1");
            exit;
        } else {
            $alerta = "No se pudo actualizar la contraseña. Intente nuevamente.";
            $up->close();
        }
    }
}

include_once("includes/header.php");
?>

<div class="container-fluid px-4">

    <div class="pes-page-header"><h1 class="h3 mb-0">Cambiar contraseña</h1>
    <p class="mb-2">Actualice la contraseña de su cuenta.</p></div>

    <div class="row justify-content-center">
        <div class="col-lg-6 col-md-8">
            <div class="card shadow pes-card">
                <div class="card-header py-3 pes-card-header">
                    <h6 class="m-0 font-weight-bold text-primary">Datos de acceso</h6>
                </div>
                <div class="card-body">

                    <?php if ($exito !== ""): ?>
                        <div class="alert alert-success" role="alert"><?php echo $exito; ?></div>
                    <?php endif; ?>
                    <?php if ($alerta !== ""): ?>
                        <div class="alert alert-danger" role="alert"><?php echo $alerta; ?></div>
                    <?php endif; ?>

                    <form method="POST" action="password.php" id="form-password" autocomplete="off">
                        <div class="form-group">
                            <label for="actual" class="text-gray-600 font-weight-bold">Contraseña actual</label>
                            <input type="password" class="form-control pes-pwd" id="actual" name="actual" autocomplete="current-password" required>
                        </div>
                        <div class="form-group">
                            <label for="nueva" class="text-gray-600 font-weight-bold">Nueva contraseña</label>
                            <input type="password" class="form-control pes-pwd" id="nueva" name="nueva" minlength="6" autocomplete="new-password" required>
                            <small class="form-text text-muted">Mínimo 6 caracteres. Debe ser distinta de la actual.</small>
                        </div>
                        <div class="form-group">
                            <label for="repetir" class="text-gray-600 font-weight-bold">Repetir nueva contraseña</label>
                            <input type="password" class="form-control pes-pwd" id="repetir" name="repetir" minlength="6" autocomplete="new-password" required>
                        </div>
                        <div class="form-group form-check">
                            <input type="checkbox" class="form-check-input" id="mostrar">
                            <label class="form-check-label" for="mostrar">Mostrar contraseñas</label>
                        </div>
                        <div class="text-center mt-4">
                            <a href="home.php" class="btn btn-secondary mr-2">Volver</a>
                            <button type="submit" class="btn btn-primary" name="cambiar" value="1">Guardar cambios</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

</div>

<?php
include_once("includes/footer.php");
?>

<script>
    $(function () {
        $('#mostrar').on('change', function () {
            $('.pes-pwd').attr('type', this.checked ? 'text' : 'password');
        });
        $('#form-password').on('submit', function (e) {
            var n = $('#nueva').val(), r = $('#repetir').val();
            if (n !== r) {
                e.preventDefault();
                if (window.Swal) { Swal.fire('Error', 'La nueva contraseña y su confirmación no coinciden.', 'error'); }
                else { alert('La nueva contraseña y su confirmación no coinciden.'); }
            }
        });
    });
</script>
