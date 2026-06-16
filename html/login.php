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

    $password = md5($_POST["password"]);

    $query_validar = "SELECT password FROM usuarios WHERE rut = '$rut' LIMIT 1";

    $resultado_validar = mysqli_query($connect, $query_validar)or die( "Error: " . mysqli_error($connect));

    $filas = mysqli_fetch_row($resultado_validar);

    if($filas >= 1){
    
        $password_bd = $filas[0];

        if($password != $password_bd):

            $alerta = "
                <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Contraseña incorrecta.
                </div>";

        endif;

    }else{

        $alerta = "
                <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Usuario no existe.
                </div>";

    }

    $query = "SELECT * FROM usuarios WHERE rut = '$rut' AND password = '$password' LIMIT 1";
    $resultado = mysqli_query($connect, $query)or die( "Error: " . mysqli_error($connect));

    if($resultado->num_rows > 0):

        $row = $resultado->fetch_array(MYSQLI_ASSOC);

        $id_usuario = $row["id_usuario"];
        $nombre_usuario = $row["nombre"];
        $apellido_usuario = $row["apellido"];
        $correo_usuario = $row["correo"];
        $id_rol = $row["id_rol"];
        $estado = $row["estado"];
        $rut = $row["rut"];

            if($estado == 2):

                $alerta = "
                    <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Usuario desactivado.
                    </div>";

            endif;

            if($estado == 1):

                session_start();

                $_SESSION["id_usuario"] = $id_usuario;
                $_SESSION["nombre_usuario"] = $nombre_usuario;
                $_SESSION["apellido_usuario"] = $apellido_usuario;
                $_SESSION["correo_usuario"] = $correo_usuario;
                $_SESSION["id_rol"] = $id_rol;  
                $_SESSION["rut_usuario"] = $rut;  

                header("Location: home.php");

            endif;

    endif;

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
                                <?php if(isset($_GET['correcto'])){ echo $alertax; } ?>
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