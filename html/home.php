<?php

    session_start();

    if(!isset($_SESSION["correo_usuario"])):
        header("location: login.php");
    endif;

    include_once("includes/header.php");
    require 'includes/db_connect.php'; 

    $total_total = 0;

    $query1 = "SELECT SUM(total) AS total_gasto FROM movimientos WHERE tipo_movimiento = 3 AND eliminado = 0 LIMIT 1";
    $result1 = mysqli_query($connect, $query1); // Nota: El orden de los parámetros es conexión, luego consulta.

    if ($result1 && mysqli_num_rows($result1) > 0) {
        $row = mysqli_fetch_assoc($result1);
        $total_gasto = $row['total_gasto'];
    } else {
        $total_gasto = 0;
    }

    $total_gasto = '$' . number_format($total_gasto, 0, ',', '.');

    $query2 = "SELECT SUM(total) AS total_ingreso FROM movimientos WHERE tipo_movimiento = 1 AND eliminado = 0 LIMIT 1";
    $result2 = mysqli_query($connect, $query2); // Nota: El orden de los parámetros es conexión, luego consulta.

    if ($result2 && mysqli_num_rows($result2) > 0) {
        $row = mysqli_fetch_assoc($result2);
        $total_ingreso = $row['total_ingreso'];
    } else {
        $total_ingreso = 0;
    }

    $total_total += $total_ingreso;

    $total_ingreso = '$' . number_format($total_ingreso, 0, ',', '.');

    $query3 = "SELECT SUM(total) AS total_egreso FROM movimientos WHERE tipo_movimiento = 2 AND eliminado = 0 LIMIT 1";
    $result3 = mysqli_query($connect, $query3); // Nota: El orden de los parámetros es conexión, luego consulta.

    if ($result3 && mysqli_num_rows($result3) > 0) {
        $row = mysqli_fetch_assoc($result3);
        $total_egreso = $row['total_egreso'];
    } else {
        $total_egreso = 0;
    }

    $total_total = ($total_total - $total_egreso);

    $total_egreso = '$' . number_format($total_egreso, 0, ',', '.');
    $total_total = '$' . number_format($total_total, 0, ',', '.');

?>

    <div class="container-fluid px-4">
        
        <div class="row">

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 pes-stat-card py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Total Gastos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_gasto; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-money fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 pes-stat-card py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Total Ingresos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_ingreso; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-plus fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 pes-stat-card py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Total Egresos</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_egreso; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-minus fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 pes-stat-card py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Resultado</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_total; ?></div>
                            </div>
                            <div class="col-auto">
                                <i class="fa fa-archive fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
        </div>

    </div>

<?php

    include_once("includes/footer.php");

?>