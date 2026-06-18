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

<style>
    .pes-strength-bar { height:6px; background:#e5e7eb; border-radius:4px; overflow:hidden; margin-bottom:3px; }
    .pes-strength-bar span { display:block; height:100%; width:0; border-radius:4px; transition:width .2s ease, background .2s ease; }
    .pes-strength-label { font-weight:600; font-size:.78rem; }
</style>

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
                            <div class="pes-strength mt-1" id="pes-strength" style="display:none">
                                <div class="pes-strength-bar"><span id="pes-strength-fill"></span></div>
                                <small class="pes-strength-label" id="pes-strength-label"></small>
                            </div>
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
    // Estimacion simple de fortaleza (orientativa, no bloquea): longitud + variedad de caracteres
    function pesPwScore(pw) {
        if (!pw) return 0;
        var s = 0;
        if (pw.length >= 6) s++;
        if (pw.length >= 10) s++;
        if (/[a-z]/.test(pw) && /[A-Z]/.test(pw)) s++;
        if (/\d/.test(pw)) s++;
        if (/[^A-Za-z0-9]/.test(pw)) s++;
        return s; // 0..5
    }
    function pesPwLevel(s) {
        if (s <= 1) return { pct: 20,  color: '#dc2626', label: 'Muy débil' };
        if (s === 2) return { pct: 40,  color: '#f59e0b', label: 'Débil' };
        if (s === 3) return { pct: 65,  color: '#eab308', label: 'Media' };
        if (s === 4) return { pct: 85,  color: '#16a34a', label: 'Fuerte' };
        return            { pct: 100, color: '#15803d', label: 'Muy fuerte' };
    }
    $(function () {
        $('#mostrar').on('change', function () {
            $('.pes-pwd').attr('type', this.checked ? 'text' : 'password');
        });
        $('#nueva').on('input', function () {
            var v = this.value, box = $('#pes-strength');
            if (!v) { box.hide(); return; }
            box.show();
            var lv = pesPwLevel(pesPwScore(v));
            $('#pes-strength-fill').css({ width: lv.pct + '%', background: lv.color });
            $('#pes-strength-label').text('Seguridad: ' + lv.label).css('color', lv.color);
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
