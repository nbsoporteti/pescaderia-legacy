<?php

ob_start();

include_once("includes/config.php");

?>

<?php

require 'includes/db_connect.php';


if(isset($_POST['login'])){

    $estado = 0;
    $alerta = "";

    $rut = $_POST["rut"];
    $passwordInput = $_POST["password"];

    // Consulta preparada: evita inyeccion SQL en el RUT.
    $stmt = $connect->prepare("SELECT * FROM usuarios WHERE rut = ? LIMIT 1");
    $stmt->bind_param("s", $rut);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if($resultado->num_rows > 0){

        $row = $resultado->fetch_assoc();
        $stored = $row["password"];

        // Verificacion de clave: bcrypt si ya esta migrada; MD5 legacy con upgrade transparente.
        $ok = false;
        if (password_verify($passwordInput, $stored)) {
            $ok = true;
        } elseif (strlen($stored) === 32 && hash_equals(strtolower($stored), md5($passwordInput))) {
            $ok = true;
            // Migracion transparente del hash viejo (MD5) a bcrypt.
            $nuevoHash = password_hash($passwordInput, PASSWORD_DEFAULT);
            $up = $connect->prepare("UPDATE usuarios SET password = ? WHERE id_usuario = ?");
            $up->bind_param("si", $nuevoHash, $row["id_usuario"]);
            $up->execute();
            $up->close();
        }

        if($ok){

            if($row["estado"] == 2):

                $alerta = "
                    <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Usuario desactivado.
                    </div>";

            endif;

            if($row["estado"] == 1):

                session_start();

                $_SESSION["id_usuario"] = $row["id_usuario"];
                $_SESSION["nombre_usuario"] = $row["nombre"];
                $_SESSION["apellido_usuario"] = $row["apellido"];
                $_SESSION["correo_usuario"] = $row["correo"];
                $_SESSION["id_rol"] = $row["id_rol"];
                $_SESSION["rut_usuario"] = $row["rut"];

                header("Location: home.php");
                exit;

            endif;

        }else{

            $alerta = "
                <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Contraseña incorrecta.
                </div>";

        }

    }else{

        $alerta = "
                <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Usuario no existe.
                </div>";

    }

    $stmt->close();

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <!-- <meta name="description" content="Geret - Vacaciones">
    <meta name="author" content="Geret"> -->
    <title>SISTEMA</title>
    <!-- <link rel="icon" href="<?=$base_home?>assets/img/geret_2.ico" type="image/svg+xml" /> -->
    <meta name="theme-color" content="#ffffff">
    <link href="css/style.css" rel="stylesheet">
</head>
<body class="c-app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card-group">
                    <div class="card p-4">
                        <div class="card-body">
                            <form class="validate-form" method="POST" action="">
                                <h1>Login</h1>
                                <p class="text-muted">Datos de ingreso</p>
                                <?php if(isset($_POST['login'])){ echo $alerta; } ?>
                                <div class="input-group mb-3">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <svg class="c-icon">
                                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                                            </svg>
                                        </span>
                                    </div>
                                    <input class="form-control" maxlength="10" type="search" name="rut"
                                        oninput="checkRut(this)" autocomplete="off" placeholder="Usuario" required>
                                </div>
                                <div class="input-group mb-4">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <svg class="c-icon">
                                                <use xlink:href="vendors/@coreui/icons/svg/free.svg#cil-lock-locked">
                                                </use>
                                            </svg>
                                        </span>
                                    </div>
                                    <input class="form-control" type="password" name="password" autocomplete="off"
                                        placeholder="Contraseña" required>
                                </div>
                                <div class="input-group mb-4 text-right">
                                    <a href="recovery.php"><button class="btn btn-link px-0"
                                            style="height: 23px; color: #3c4b64;font-weight: bold;"
                                            type="button">Olvidaste tu contraseña?</button></a>
                                </div>
                                <br>
                                <div class="row">
                                    <div class="col-md-12">
                                        <button class="btn btn-primary mx-auto d-block btn-lg" name="login"
                                            type="submit">Ingresar</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
    <script src="vendors/@coreui/icons/js/svgxuse.min.js"></script>
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
</body>

</html>
