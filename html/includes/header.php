<?php

include_once("config.php");

$nombre = explode(" ", $_SESSION['nombre_usuario'], 2);
$apellido = explode(" ", $_SESSION['apellido_usuario'], 2);

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
    <title>Sistema</title>
    <!-- <link rel="icon" href="<?=$base_home?>assets/img/geret_2.ico" type="image/svg+xml" /> -->
    <meta name="theme-color" content="#ffffff">
    <link href="<?=$base_home?>css/style.css?v=1.0" rel="stylesheet">
    <link id="pes-ui-css" href="<?=$base_home?>css/pescaderia-ui.css?v=2.8" rel="stylesheet">
    <link href="<?=$base_home?>css/pes-theme-toggle.css?v=1.1" rel="stylesheet">
    <link href="<?=$base_home?>css/pes-collapse.css?v=1.1" rel="stylesheet">
    <script>
(function(){try{var c=localStorage.getItem('pes-sidebar-theme')!=='modern';if(c){document.documentElement.classList.add('pes-sidebar-classic');var l=document.getElementById('pes-ui-css');if(l)l.disabled=true;}}catch(e){}})();
</script>

    <script src="<?=$base_home?>vendors/jquery/jquery.min.js"></script>
    <script src="<?=$base_home?>vendors/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?=$base_home?>vendors/jquery-easing/jquery.easing.min.js"></script>

    <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

    <link href="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/css/bootstrap4-toggle.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/gh/gitbrent/bootstrap4-toggle@3.6.1/js/bootstrap4-toggle.min.js"></script>

    <style>
      .form-control:disabled {
          background-color: #e9ecef; /* Fondo gris claro */
          color: #6c757d;            /* Texto gris */
          border-color: #ced4da;     /* Borde gris claro */
          cursor: not-allowed;       /* Cursor de no permitido */
          opacity: 0.65;             /* Opacidad */
      }
  </style>
  </head>
  <body class="c-app">
<div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-hide c-sidebar-lg-show" id="sidebar">
    <!-- <div class="c-sidebar c-sidebar-dark c-sidebar-fixed c-sidebar-lg-hide" id="sidebar"> -->
      <div class="c-sidebar-brand d-lg-down-none">SISTEMA
        <!-- <img src="<?=$base_home?>assets/img/logo_geret.png" style="width: 100px;height:40px;" alt="Geret" /> -->
      </div>
      <ul class="c-sidebar-nav" id="c-sidebar-nav">

        <li class="c-sidebar-nav-item"><a class="c-sidebar-nav-link" href="<?=$base_home?>index.php">
        <svg class="c-sidebar-nav-icon">
          <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-home"></use>
        </svg> Dashboard</a></li>

        <li class="c-sidebar-nav-item c-sidebar-nav-dropdown"><a class="c-sidebar-nav-dropdown-toggle" href="#">
            <svg class="c-sidebar-nav-icon">
              <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-puzzle"></use>
            </svg> Módulos</a>

            <ul class="c-sidebar-nav-dropdown-items">
              <li class="c-sidebar-nav-item c-sidebar-nav-dropdown">
                <a class="c-sidebar-nav-dropdown-toggle" href="#">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-money"></use>
                  </svg>
                  Movimientos
                </a>
                <ul class="c-sidebar-nav-dropdown-items">
                  <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_movimientos/crear.php?tipo=1">
                      <svg class="c-sidebar-nav-icon">
                        <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-arrow-right"></use>
                      </svg> 
                      Ingresos
                    </a>
                  </li>
                  <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_movimientos/crear.php?tipo=2">
                      <svg class="c-sidebar-nav-icon">
                        <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-arrow-right"></use>
                      </svg> 
                      Egresos
                    </a>
                  </li>
                  <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_movimientos/crear.php?tipo=3">
                      <svg class="c-sidebar-nav-icon">
                        <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-arrow-right"></use>
                      </svg> 
                      Gastos
                    </a>
                  </li>
                  <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_movimientos/crear.php?tipo=4">
                      <svg class="c-sidebar-nav-icon">
                        <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-arrow-right"></use>
                      </svg> 
                      Pagos
                    </a>
                  </li>
                  <li class="c-sidebar-nav-item">
                    <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_movimientos/ver.php">
                      <svg class="c-sidebar-nav-icon">
                        <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-arrow-right"></use>
                      </svg> 
                      Ver movimientos
                    </a>
                  </li>
                </ul>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/banco/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-bank"></use>
                  </svg>
                  Banco
                </a>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/usuarios/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-user"></use>
                  </svg>
                  Usuarios
                </a>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/trabajadores/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-user-follow"></use>
                  </svg>
                  Trabajadores
                </a>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/proveedores/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-people"></use>
                  </svg>
                  Proveedores
                </a>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/acarreos/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-boat-alt"></use>
                  </svg>
                  Acarreos
                </a>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/embarcaciones/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-car-alt"></use>
                  </svg>
                  Embarcaciones
                </a>
              </li>

              <li class="c-sidebar-nav-item">
                <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_maestros/clasificaciones/index.php">
                  <svg class="c-sidebar-nav-icon">
                    <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-list"></use>
                  </svg>
                  Clasificaciones
                </a>
              </li>

            </ul>

        </li>

        <li class="c-sidebar-nav-item">
          <a class="c-sidebar-nav-link" href="<?=$base_home?>vistas/maestro_de_reportes/ver.php">
            <svg class="c-sidebar-nav-icon">
              <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-file"></use>
            </svg>
            Reportes
          </a>
        </li>

      </ul>

<div class="pes-sidebar-footer">
        <button type="button" class="pes-sidebar-action-btn" id="pes-sidebar-collapse" title="Contraer menú" aria-expanded="true">
          <svg class="pes-sidebar-action-icon" aria-hidden="true">
            <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-arrow-left"></use>
          </svg>
          <span id="pes-sidebar-collapse-label">Contraer</span>
        </button>
        <button type="button" class="pes-theme-toggle-btn" id="pes-theme-toggle" title="Sitio como antes (diseño original)">
          <svg class="pes-theme-toggle-icon" aria-hidden="true">
            <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-moon"></use>
          </svg>
          <span id="pes-theme-toggle-label">Aspecto clásico</span>
        </button>
      </div>
    </div>
    <div class="c-wrapper c-fixed-components">
      <header class="c-header c-header-light c-header-fixed c-header-with-subheader">
        <button class="c-header-toggler c-class-toggler d-lg-none mfe-auto" type="button" data-target="#sidebar" data-class="c-sidebar-show">
          <svg class="c-icon c-icon-lg">
            <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-menu"></use>
          </svg>
        </button>
        <button class="c-header-toggler c-class-toggler mfs-3 d-md-down-none" type="button" data-target="#sidebar" data-class="c-sidebar-lg-show" responsive="true">
          <svg class="c-icon c-icon-lg">
            <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-menu"></use>
          </svg>
        </button>

        <?php

          if($_SESSION['id_rol'] == 1){

            $cargo = "&nbsp;(Administrador)";

          }else{

            $cargo = "";

          }

        ?>

        <ul class="c-header-nav ml-auto mr-4"><b>Bienvenid@</b>&nbsp;<?php echo $nombre[0]." ".$apellido[0];?><i><?php echo $cargo; ?></i>
         
          <li class="c-header-nav-item dropdown">
            
            <a class="c-header-nav-link" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">     
               <svg class="c-icon">
                <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-chevron-circle-down-alt"></use>
               </svg>
            </a>

            <div class="dropdown-menu dropdown-menu-right pt-0">

              <div class="dropdown-header bg-light py-2">
                <strong>Perfil</strong>
              </div>
              <a class="dropdown-item" href="<?=$base_home?>password.php">
                <svg class="c-icon mr-2">
                  <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-lock-locked"></use>
                </svg>
                  Cambiar contraseña
              </a>
              <a class="dropdown-item" href="#" data-toggle="modal" data-target="#logoutModal">
                <svg class="c-icon mr-2">
                  <use xlink:href="<?=$base_home?>vendors/@coreui/icons/svg/free.svg#cil-account-logout"></use>
                </svg> Cerrar sesión
              </a>

            </div>

          </li>

        </ul>

      </header>

      <div class="c-body">
        <main class="c-main">
