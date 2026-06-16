<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>

<?php 
ob_start();
require 'includes/db_connect.php';

//RECUPERAR CLAVE X EMAIL
if(isset($_POST["recuperarPass"])):
    
    $rutt = $_POST["rutt"];
    $nombreCorreoPass = "SELECT * FROM usuarios WHERE rut = '$rutt' LIMIT 1";

    $resultado = mysqli_query($connect, $nombreCorreoPass)or die( "Error: " . mysqli_error($connect));  

    if(!$resultado):
        echo "error de bd";
        $arreglo["data"][] = mysqli_error($connect);
    else:
        $filas = $resultado->num_rows;
        if($filas == 0):

            $alerta = "
                <div class='alert alert-danger alert-dismissable' style='font-size: 15.5px;'>
                    <button type='button' class='close' data-dismiss='alert'>&times;</button>
                    Error, rut de usuario no existe
                </div>";

        else:
            $data = mysqli_fetch_assoc($resultado);

            if ($data) {
                $rut = $data["rut"];

                if ($rut != null) {
                    $sql = "UPDATE usuarios SET password = MD5(12345) WHERE rut = '$rut'";
                    $resultado = mysqli_query($connect, $sql) or die("Error: " . mysqli_error($connect));

                    $alerta = "
                        <div class='alert alert-success' style='font-size: 15.5px;'>
                            <button type='button' class='close' data-dismiss='alert'>&times;</button>
                            ¡Correcto!<br>Su nueva contraseña temporal es: <b>12345</b><br>
                            Le recomendamos cambiar su contraseña temporal a la brevedad.
                        </div>
                        <script>
                            setTimeout(function() {
                                var boton = document.getElementById('recuperarPass');
                                if (boton) {
                                    boton.style.display = 'none';
                                }
                                var boton2 = document.getElementById('rutt');
                                if (boton2) {
                                    boton2.style.display = 'none';
                                }
                            }, 100); // Espera 100ms antes de ocultarlo
                        </script>
                    ";

                }
            }

        endif;
    endif;
endif;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <base href="./">
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <meta name="description" content="Geret - Vacaciones">
    <meta name="author" content="Geret">
    <title>Sistema</title>
    <meta name="theme-color" content="#ffffff">
    <!-- Main styles for this application-->
    <link href="css/style.css" rel="stylesheet">
    <!-- Global site tag (gtag.js) - Google Analytics-->
</head>

<body class="c-app flex-row align-items-center">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-4">
                <div class="card-group">
                    <div class="card p-9">
                        <div class="card-body">
                            <form class="validate-form" method="POST" action="">
                                <div class="body text-center">
                                    <h2>Recuperar contraseña</h2>
                                    <p class="text-muted">Ingrese su rut para validar su identidad</p>
                                    <?php if(isset($_POST['recuperarPass'])) { echo $alerta; } ?>
                                    <div class="col-md-8 offset-md-2">
                                        <input type="text" class="form-control" name="rutt" id="rutt" oninput="checkRut(this)" autocomplete="off" placeholder="Rut" required>
                                    </div>
                                    <br>
                                </div>
                                <div class="text-center">
                                    <button class="btn btn-danger" name="recuperarPass" type="submit" id="recuperarPass">Recuperar contraseña</button>
                                    <a href="login.php"><input type="button" class="btn btn-primary" value="Volver al login"></a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- CoreUI and necessary plugins-->
            <script src="vendors/@coreui/coreui/js/coreui.bundle.min.js"></script>
            <!--[if IE]><!-->
            <script src="vendors/@coreui/icons/js/svgxuse.min.js"></script>
            <!--<![endif]-->
            <script type="text/javascript">
            function checkRut(rut) {
                // Despejar Puntos
                var valor = rut.value.replace('.', '');
                // Despejar Guión
                valor = valor.replace('-', '');

                // Aislar Cuerpo y Dígito Verificador
                cuerpo = valor.slice(0, -1);
                dv = valor.slice(-1).toUpperCase();

                // Formatear RUN
                rut.value = cuerpo + '-' + dv

                // Si no cumple con el mínimo ej. (n.nnn.nnn)
                if (cuerpo.length < 7) {
                    rut.setCustomValidity("RUT Incompleto");
                    return false;
                }

                // Calcular Dígito Verificador
                suma = 0;
                multiplo = 2;

                // Para cada dígito del Cuerpo
                for (i = 1; i <= cuerpo.length; i++) {

                    // Obtener su Producto con el Múltiplo Correspondiente
                    index = multiplo * valor.charAt(cuerpo.length - i);

                    // Sumar al Contador General
                    suma = suma + index;

                    // Consolidar Múltiplo dentro del rango [2,7]
                    if (multiplo < 7) {
                        multiplo = multiplo + 1;
                    } else {
                        multiplo = 2;
                    }

                }

                // Calcular Dígito Verificador en base al Módulo 11
                dvEsperado = 11 - (suma % 11);

                // Casos Especiales (0 y K)
                dv = (dv == 'K') ? 10 : dv;
                dv = (dv == 0) ? 11 : dv;

                // Validar que el Cuerpo coincide con su Dígito Verificador
                if (dvEsperado != dv) {
                    rut.setCustomValidity("RUT Inválido");
                    return false;
                }

                // Si todo sale bien, eliminar errores (decretar que es válido)
                rut.setCustomValidity('');

            }
            </script>
</body>

<?php

if(isset($_SESSION["id_usuario"])){
    include_once("includes/footer.php");
}else{
    '';
}
?>

</html>