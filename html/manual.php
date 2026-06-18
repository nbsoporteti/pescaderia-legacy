<?php

    session_start();

    if (!isset($_SESSION["correo_usuario"])) {
        header("location: login.php");
        exit;
    }

    include_once("includes/header.php");

    // Rol para mostrar/ocultar la sección de Usuarios (sólo administradores)
    $es_admin = isset($_SESSION['id_rol']) && $_SESSION['id_rol'] == 1;

?>

<style>
/* ===== Manual de usuario (estilos locales) ===== */
.pes-manual { max-width: 1100px; margin: 0 auto; padding: 0 0 60px; }
.pes-manual .man-hero {
    background: linear-gradient(120deg, #0f766e 0%, #0891b2 100%);
    color: #fff; border-radius: 14px; padding: 28px 32px; margin-bottom: 22px;
    box-shadow: 0 10px 30px rgba(8,145,178,.18);
}
.pes-manual .man-hero h1 { font-weight: 700; margin: 0 0 6px; font-size: 1.7rem; }
.pes-manual .man-hero p { margin: 0; opacity: .95; max-width: 760px; }
.pes-manual .man-hero .man-hero-icon { font-size: 2.4rem; opacity: .9; }

/* Tabla de contenidos */
.pes-manual .man-toc { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:18px 20px; margin-bottom:26px; }
.pes-manual .man-toc h2 { font-size: .8rem; text-transform: uppercase; letter-spacing:.06em; color:#64748b; margin:0 0 12px; }
.pes-manual .man-toc ol { list-style:none; counter-reset: toc; margin:0; padding:0;
    display:grid; grid-template-columns: repeat(auto-fill, minmax(240px, 1fr)); gap:6px 22px; }
.pes-manual .man-toc li { counter-increment: toc; }
.pes-manual .man-toc a { display:flex; align-items:center; gap:10px; padding:7px 8px; border-radius:8px; color:#334155; text-decoration:none; font-size:.92rem; }
.pes-manual .man-toc a:hover { background:#f0fdfa; color:#0f766e; }
.pes-manual .man-toc a::before { content: counter(toc); flex:0 0 auto; width:24px; height:24px; border-radius:50%;
    background:#e0f2fe; color:#0369a1; font-size:.78rem; font-weight:700; display:flex; align-items:center; justify-content:center; }

/* Secciones */
.pes-manual .man-section { background:#fff; border:1px solid #e5e7eb; border-radius:12px; padding:26px 30px; margin-bottom:22px; scroll-margin-top:80px; }
.pes-manual .man-section > h2 { display:flex; align-items:center; gap:12px; font-size:1.3rem; font-weight:700; color:#0f172a; margin:0 0 6px; }
.pes-manual .man-section > h2 .man-num { flex:0 0 auto; width:34px; height:34px; border-radius:9px; background:#0f766e; color:#fff;
    font-size:1rem; display:flex; align-items:center; justify-content:center; }
.pes-manual .man-section .man-lead { color:#475569; margin:2px 0 18px; }
.pes-manual .man-section h3 { font-size:1.02rem; font-weight:700; color:#0f766e; margin:22px 0 10px; }

/* Pasos numerados */
.pes-manual ol.man-steps { counter-reset: step; list-style:none; margin:0 0 6px; padding:0; }
.pes-manual ol.man-steps > li { counter-increment: step; position:relative; padding:6px 0 14px 44px; }
.pes-manual ol.man-steps > li::before { content: counter(step); position:absolute; left:0; top:2px; width:30px; height:30px;
    border-radius:50%; background:#ecfeff; border:2px solid #67e8f9; color:#0e7490; font-weight:700; font-size:.9rem;
    display:flex; align-items:center; justify-content:center; }
.pes-manual ol.man-steps > li:not(:last-child)::after { content:""; position:absolute; left:14px; top:34px; bottom:2px; width:2px; background:#e2e8f0; }

/* Capturas de pantalla con marco tipo navegador */
.pes-manual figure.pes-shot { margin:18px 0 6px; }
.pes-manual .pes-shot .shot-frame { border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; box-shadow:0 6px 18px rgba(15,23,42,.08); background:#fff; }
.pes-manual .pes-shot .shot-bar { background:#f1f5f9; border-bottom:1px solid #e2e8f0; padding:8px 12px; display:flex; align-items:center; gap:6px; }
.pes-manual .pes-shot .shot-bar i { width:10px; height:10px; border-radius:50%; display:inline-block; }
.pes-manual .pes-shot .shot-bar .d1{background:#f87171}.pes-manual .pes-shot .shot-bar .d2{background:#fbbf24}.pes-manual .pes-shot .shot-bar .d3{background:#34d399}
.pes-manual .pes-shot .shot-bar span { margin-left:8px; font-size:.74rem; color:#94a3b8; }
.pes-manual .pes-shot img { width:100%; display:block; }
.pes-manual .pes-shot .shot-ph { padding:46px 16px; text-align:center; color:#94a3b8; font-size:.9rem; background:repeating-linear-gradient(45deg,#f8fafc,#f8fafc 12px,#f1f5f9 12px,#f1f5f9 24px); }
.pes-manual .pes-shot figcaption { text-align:center; font-size:.82rem; color:#64748b; margin-top:8px; font-style:italic; }

/* Avisos */
.pes-manual .man-note { border-radius:10px; padding:12px 16px; margin:16px 0; font-size:.92rem; display:flex; gap:10px; align-items:flex-start; }
.pes-manual .man-note .fa { margin-top:2px; }
.pes-manual .man-note.tip { background:#f0fdf4; border:1px solid #bbf7d0; color:#166534; }
.pes-manual .man-note.info { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; }
.pes-manual .man-note.warn { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }

.pes-manual .man-back { display:inline-block; margin-top:10px; font-size:.82rem; color:#0891b2; text-decoration:none; }
.pes-manual .man-back:hover { text-decoration:underline; }
.pes-manual .man-pill { display:inline-block; background:#e0f2fe; color:#0369a1; border-radius:6px; padding:1px 8px; font-size:.82rem; font-weight:600; }
.pes-manual kbd { background:#1e293b; color:#fff; border-radius:5px; padding:1px 7px; font-size:.8rem; }
@media print { .c-sidebar, .c-header, .pes-sidebar-footer { display:none !important; } .pes-manual .man-section { break-inside: avoid; } }
</style>

<div class="container-fluid px-4">
  <div class="pes-manual" id="top">

    <!-- HERO -->
    <div class="man-hero d-flex align-items-center justify-content-between">
      <div>
        <h1>Manual de usuario</h1>
        <p>Guía paso a paso para usar el sistema de gestión de la pescadería: ingreso, registro de movimientos (ingresos, egresos, gastos y pagos), catálogos, reportes y administración. Pensado para usar sin conocimientos técnicos.</p>
      </div>
      <div class="man-hero-icon d-none d-md-block"><i class="fa fa-book"></i></div>
    </div>

    <!-- TABLA DE CONTENIDOS -->
    <nav class="man-toc">
      <h2>Contenido</h2>
      <ol>
        <li><a href="#intro">¿Qué es el sistema?</a></li>
        <li><a href="#ingresar">Ingresar al sistema</a></li>
        <li><a href="#recuperar">Recuperar contraseña</a></li>
        <li><a href="#inicio">Pantalla de inicio</a></li>
        <li><a href="#navegacion">Cómo moverse por el sistema</a></li>
        <li><a href="#mov-registrar">Registrar un movimiento</a></li>
        <li><a href="#mov-ver">Ver y editar movimientos</a></li>
        <li><a href="#reportes">Reportes</a></li>
        <li><a href="#maestros">Catálogos (maestros)</a></li>
        <?php if ($es_admin): ?><li><a href="#usuarios">Usuarios (administrador)</a></li><?php endif; ?>
        <li><a href="#sesion">Cerrar sesión</a></li>
        <li><a href="#faq">Preguntas frecuentes</a></li>
      </ol>
    </nav>

    <!-- 1. INTRO -->
    <section id="intro" class="man-section">
      <h2><span class="man-num">1</span> ¿Qué es el sistema?</h2>
      <p class="man-lead">Es una plataforma web para llevar el control del dinero y la operación diaria de la pescadería.</p>
      <p>Con el sistema usted puede:</p>
      <ul>
        <li><b>Registrar movimientos de dinero:</b> ingresos, egresos, gastos y pagos, con su anticipo y/o cheques.</li>
        <li><b>Asociar cada movimiento</b> a un proveedor, una embarcación, un acarreo, un trabajador y una clasificación.</li>
        <li><b>Mantener los catálogos</b> (maestros): trabajadores, proveedores, embarcaciones, acarreos, clasificaciones y bancos.</li>
        <li><b>Obtener reportes en PDF</b> filtrando por fecha, proveedor, trabajador, tipo de movimiento, etc.</li>
      </ul>
      <div class="man-note info"><i class="fa fa-info-circle"></i><div>Este manual está siempre disponible dentro del sistema, en el menú lateral, opción <span class="man-pill">Manual de usuario</span>.</div></div>
    </section>

    <!-- 2. INGRESAR -->
    <section id="ingresar" class="man-section">
      <h2><span class="man-num">2</span> Ingresar al sistema</h2>
      <p class="man-lead">Para entrar necesita su <b>RUT</b> y su <b>contraseña</b>.</p>
      <ol class="man-steps">
        <li>Abra el navegador (Chrome, Edge o Firefox) y entre a la dirección del sistema.</li>
        <li>En el campo <b>Usuario</b> escriba su <b>RUT</b> (el sistema lo formatea automáticamente, por ejemplo <kbd>12345678-9</kbd>).</li>
        <li>Escriba su <b>Contraseña</b>.</li>
        <li>Presione el botón <span class="man-pill">Ingresar</span>.</li>
      </ol>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Pantalla de ingreso</span></div>
          <div class="shot-ph">Captura: pantalla de ingreso (login)</div>
          <img src="<?=$base_home?>assets/manual/01-login.png" alt="Pantalla de ingreso" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Pantalla de ingreso al sistema</figcaption>
      </figure>
      <div class="man-note warn"><i class="fa fa-exclamation-triangle"></i><div>Si aparece <b>“Usuario no existe”</b> revise el RUT; si dice <b>“Contraseña incorrecta”</b> revise la clave; y si dice <b>“Usuario desactivado”</b> pida a un administrador que reactive su cuenta.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 3. RECUPERAR -->
    <section id="recuperar" class="man-section">
      <h2><span class="man-num">3</span> Recuperar contraseña</h2>
      <p class="man-lead">Si olvidó su clave, puede recuperarla desde la misma pantalla de ingreso.</p>
      <ol class="man-steps">
        <li>En la pantalla de ingreso, haga clic en <span class="man-pill">¿Olvidaste tu contraseña?</span></li>
        <li>Siga las instrucciones en pantalla para restablecerla.</li>
        <li>Vuelva a la pantalla de ingreso e inicie sesión con su nueva contraseña.</li>
      </ol>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Recuperar contraseña</span></div>
          <div class="shot-ph">Captura: recuperación de contraseña</div>
          <img src="<?=$base_home?>assets/manual/02-recovery.png" alt="Recuperar contraseña" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Pantalla de recuperación de contraseña</figcaption>
      </figure>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 4. INICIO -->
    <section id="inicio" class="man-section">
      <h2><span class="man-num">4</span> Pantalla de inicio (Dashboard)</h2>
      <p class="man-lead">Al ingresar verá un resumen general con cuatro tarjetas:</p>
      <ul>
        <li><b>Total Gastos:</b> suma de todos los gastos registrados.</li>
        <li><b>Total Ingresos:</b> suma de todos los ingresos.</li>
        <li><b>Total Egresos:</b> suma de todos los egresos.</li>
        <li><b>Resultado:</b> ingresos menos egresos (el saldo general).</li>
      </ul>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Inicio</span></div>
          <div class="shot-ph">Captura: pantalla de inicio</div>
          <img src="<?=$base_home?>assets/manual/03-dashboard.png" alt="Pantalla de inicio" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Pantalla de inicio con el resumen de totales</figcaption>
      </figure>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 5. NAVEGACION -->
    <section id="navegacion" class="man-section">
      <h2><span class="man-num">5</span> Cómo moverse por el sistema</h2>
      <p class="man-lead">Todo se controla desde el <b>menú lateral</b> izquierdo.</p>
      <h3>Menú lateral</h3>
      <ul>
        <li><b>Dashboard:</b> vuelve a la pantalla de inicio.</li>
        <li><b>Módulos:</b> despliega <b>Movimientos</b> (Ingresos, Egresos, Gastos, Pagos, Ver movimientos) y los catálogos (Banco, Usuarios, Trabajadores, Proveedores, Acarreos, Embarcaciones, Clasificaciones).</li>
        <li><b>Reportes:</b> filtros e informes en PDF.</li>
        <li><b>Manual de usuario:</b> esta guía.</li>
      </ul>
      <h3>Barra superior y perfil</h3>
      <p>Arriba a la derecha verá el saludo con su nombre. Al hacer clic en la flecha se abre el menú de perfil, desde donde puede <b>Cerrar sesión</b>. Para cambiar su clave, use la opción <b>¿Olvidaste tu contraseña?</b> de la pantalla de ingreso (ver <a href="#recuperar">Recuperar contraseña</a>).</p>
      <h3>Opciones del pie del menú</h3>
      <ul>
        <li><span class="man-pill">Contraer</span>: achica el menú lateral para tener más espacio.</li>
        <li><span class="man-pill">Aspecto clásico / Aspecto nuevo</span>: cambia el diseño visual del sistema. Es sólo estético; las funciones son las mismas.</li>
      </ul>
      <div class="man-note info"><i class="fa fa-mobile"></i><div>En celulares o pantallas chicas, el menú se oculta. Toque el botón <b>☰</b> (arriba a la izquierda) para mostrarlo.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 6. REGISTRAR MOVIMIENTO -->
    <section id="mov-registrar" class="man-section">
      <h2><span class="man-num">6</span> Registrar un movimiento</h2>
      <p class="man-lead">Es la tarea principal del sistema. Hay cuatro tipos: <b>Ingresos</b>, <b>Egresos</b>, <b>Gastos</b> y <b>Pagos</b>. El procedimiento es el mismo para todos.</p>
      <ol class="man-steps">
        <li>En el menú, abra <b>Módulos → Movimientos</b> y elija el tipo que quiere registrar (por ejemplo <b>Ingresos</b>).</li>
        <li>Elija la <b>Fecha</b> del movimiento.</li>
        <li>Seleccione el <b>Acarreo</b>.</li>
        <li>Indique el origen del movimiento. Tiene dos caminos (se elige <b>uno u otro</b>, no ambos):
          <ul>
            <li><b>Proveedor + Embarcación:</b> al elegir el proveedor se cargan sus embarcaciones. También puede elegir primero la embarcación y el proveedor se completa solo.</li>
            <li><b>Trabajador:</b> si el movimiento es de un trabajador, selecciónelo (esto bloquea el proveedor).</li>
          </ul>
        </li>
        <li>Presione <span class="man-pill">Agregar fila</span> y complete cada línea: <b>Cantidad de cheques</b>, <b>Anticipo</b>, <b>Detalle</b>, <b>Clasificación</b> y <b>Total</b>.</li>
        <li>Si indicó cheques, complete por cada cheque su <b>Fecha</b>, <b>Monto</b> y <b>N° de cheque</b>. El <b>Total final</b> se calcula automáticamente.</li>
        <li>Revise todo y presione <span class="man-pill">Registrar movimiento</span>. El sistema guarda y genera un <b>comprobante en PDF</b>.</li>
      </ol>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Registro de movimiento</span></div>
          <div class="shot-ph">Captura: registro de movimiento</div>
          <img src="<?=$base_home?>assets/manual/04-movimiento-crear.png" alt="Registro de movimiento" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Formulario para registrar un movimiento</figcaption>
      </figure>
      <div class="man-note tip"><i class="fa fa-lightbulb-o"></i><div><b>Gastos:</b> al registrar un <b>Gasto</b> no se piden proveedor ni trabajador; sólo el acarreo y las líneas de detalle.</div></div>
      <div class="man-note warn"><i class="fa fa-exclamation-triangle"></i><div>Si aparece <b>“No hay embarcaciones disponibles para este proveedor”</b>, primero debe asociar una embarcación a ese proveedor en el catálogo de <b>Embarcaciones</b>.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 7. VER MOVIMIENTOS -->
    <section id="mov-ver" class="man-section">
      <h2><span class="man-num">7</span> Ver y editar movimientos</h2>
      <p class="man-lead">En <b>Módulos → Movimientos → Ver movimientos</b> encontrará el listado de todo lo registrado.</p>
      <ul>
        <li><b>Buscar:</b> use el cuadro de búsqueda para encontrar un movimiento rápidamente.</li>
        <li><b>Ordenar:</b> haga clic en el título de una columna para ordenar.</li>
        <li><b>Editar:</b> botón <span class="man-pill">Editar</span> de la fila. Al guardar, el sistema lo devuelve al listado.</li>
        <li><b>Eliminar:</b> botón para quitar un movimiento (pide confirmación).</li>
        <li><b>PDF:</b> genera el comprobante del movimiento.</li>
      </ul>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Ver movimientos</span></div>
          <div class="shot-ph">Captura: listado de movimientos</div>
          <img src="<?=$base_home?>assets/manual/05-movimientos-ver.png" alt="Ver movimientos" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Listado de movimientos</figcaption>
      </figure>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 8. REPORTES -->
    <section id="reportes" class="man-section">
      <h2><span class="man-num">8</span> Reportes</h2>
      <p class="man-lead">En <b>Reportes</b> puede filtrar la información y descargar informes en PDF.</p>
      <ol class="man-steps">
        <li>Elija los filtros que necesite: <b>Proveedor</b>, <b>Trabajador</b>, <b>Acarreo</b>, <b>Embarcación</b>, <b>Clasificación</b>, <b>Fecha desde / hasta</b> y <b>Tipo de movimiento</b>.</li>
        <li>Presione <span class="man-pill">Aplicar Filtros</span>. Aparece un <b>resumen</b> (Ingreso, Egreso, Gastos, Pagos y Saldo) y la tabla con los resultados.</li>
        <li>Para el detalle de un movimiento, use <span class="man-pill">Ver detalle</span> en su fila (abre un PDF).</li>
        <li>Para el informe completo del filtro, use <span class="man-pill">Ver detalle general</span> (PDF con los datos del proveedor: RUT, teléfono y embarcación).</li>
      </ol>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Reportes</span></div>
          <div class="shot-ph">Captura: reportes y filtros</div>
          <img src="<?=$base_home?>assets/manual/13-reportes.png" alt="Reportes" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Pantalla de reportes con filtros y resumen</figcaption>
      </figure>
      <div class="man-note info"><i class="fa fa-info-circle"></i><div>Sin filtros de fecha se muestra <b>todo el historial</b>. Use las fechas para acotar la búsqueda.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 9. MAESTROS -->
    <section id="maestros" class="man-section">
      <h2><span class="man-num">9</span> Catálogos (maestros)</h2>
      <p class="man-lead">Los catálogos son las listas base que después se usan al registrar movimientos. Todos funcionan igual.</p>
      <h3>Acciones comunes en cualquier catálogo</h3>
      <ul>
        <li><span class="man-pill">Agregar</span>: crea un nuevo registro (abre una ventana con el formulario).</li>
        <li><span class="man-pill">Editar</span>: modifica los datos de una fila.</li>
        <li><span class="man-pill">Desactivar / Activar</span>: deja un registro fuera de uso sin borrarlo, o lo vuelve a habilitar.</li>
        <li><span class="man-pill">Eliminar</span> (✕): quita el registro definitivamente (pide confirmación).</li>
        <li><b>Buscar:</b> cuadro de búsqueda en la parte superior de la tabla.</li>
      </ul>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Trabajadores</span></div>
          <div class="shot-ph">Captura: catálogo de trabajadores</div>
          <img src="<?=$base_home?>assets/manual/06-trabajadores.png" alt="Trabajadores" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Ejemplo de catálogo: Trabajadores</figcaption>
      </figure>
      <h3>Catálogos disponibles</h3>
      <ul>
        <li><b>Trabajadores:</b> nombre, RUT, teléfono, domicilio y embarcación.</li>
        <li><b>Proveedores:</b> nombre, RUT, teléfono y embarcaciones asociadas. (Estos datos aparecen luego en los reportes PDF).</li>
        <li><b>Embarcaciones:</b> las embarcaciones que se asocian a cada proveedor.</li>
        <li><b>Acarreos:</b> los acarreos que se eligen en cada movimiento.</li>
        <li><b>Clasificaciones:</b> categorías para clasificar las líneas de un movimiento.</li>
        <li><b>Banco:</b> bancos usados para los cheques.</li>
      </ul>
      <div class="man-note tip"><i class="fa fa-lightbulb-o"></i><div>Para que una embarcación aparezca al registrar un movimiento, debe estar <b>asociada a su proveedor</b> y estar <b>activa</b>.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <?php if ($es_admin): ?>
    <!-- 10. USUARIOS -->
    <section id="usuarios" class="man-section">
      <h2><span class="man-num">10</span> Usuarios <small class="text-muted" style="font-size:.7em;">(sólo administradores)</small></h2>
      <p class="man-lead">Desde <b>Módulos → Usuarios</b> los administradores gestionan quién puede entrar al sistema.</p>
      <ul>
        <li><span class="man-pill">Agregar</span>: crea un usuario (nombre, apellido, correo, RUT, contraseña y rol).</li>
        <li><b>Roles:</b> <b>Administrador</b> (acceso total, incluida la gestión de usuarios) o usuario normal.</li>
        <li><span class="man-pill">Editar</span>: cambia los datos de un usuario.</li>
        <li><span class="man-pill">Desactivar / Activar</span>: un usuario desactivado no puede ingresar.</li>
      </ul>
      <figure class="pes-shot">
        <div class="shot-frame"><div class="shot-bar"><i class="d1"></i><i class="d2"></i><i class="d3"></i><span>Usuarios</span></div>
          <div class="shot-ph">Captura: administración de usuarios</div>
          <img src="<?=$base_home?>assets/manual/12-usuarios.png" alt="Usuarios" loading="lazy" onload="this.previousElementSibling.style.display='none'" onerror="this.style.display='none'"></div>
        <figcaption>Administración de usuarios</figcaption>
      </figure>
      <div class="man-note warn"><i class="fa fa-exclamation-triangle"></i><div>Por seguridad, entregue el rol <b>Administrador</b> sólo a las personas que realmente lo necesiten.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>
    <?php endif; ?>

    <!-- 11. CERRAR SESION -->
    <section id="sesion" class="man-section">
      <h2><span class="man-num"><?= $es_admin ? '11' : '10' ?></span> Cerrar sesión</h2>
      <p class="man-lead">Al terminar, cierre su sesión para proteger la información.</p>
      <ol class="man-steps">
        <li>Arriba a la derecha, haga clic en la flecha junto a su nombre.</li>
        <li>Elija <span class="man-pill">Cerrar sesión</span> y confirme.</li>
      </ol>
      <div class="man-note tip"><i class="fa fa-lightbulb-o"></i><div>Si usa un computador compartido, cierre siempre la sesión al retirarse.</div></div>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

    <!-- 12. FAQ -->
    <section id="faq" class="man-section">
      <h2><span class="man-num"><?= $es_admin ? '12' : '11' ?></span> Preguntas frecuentes</h2>
      <h3>No me deja registrar el movimiento</h3>
      <p>Revise que estén completos todos los campos obligatorios (fecha, acarreo, origen, y en cada línea el anticipo o los cheques, el detalle y la clasificación). El sistema le avisará en rojo qué falta.</p>
      <h3>No aparece una embarcación al registrar</h3>
      <p>La embarcación debe estar <b>asociada al proveedor</b> seleccionado y estar <b>activa</b>. Verifíquelo en el catálogo de <b>Embarcaciones</b>.</p>
      <h3>¿Cómo saco un informe de un proveedor en un rango de fechas?</h3>
      <p>Vaya a <b>Reportes</b>, elija el proveedor y las fechas <b>desde/hasta</b>, presione <b>Aplicar Filtros</b> y luego <b>Ver detalle general</b> para el PDF.</p>
      <h3>Cambié el “aspecto” y se ve distinto</h3>
      <p>El botón <b>Aspecto clásico / Aspecto nuevo</b> sólo cambia los colores y el estilo. Las funciones son idénticas. Puede volver al anterior con el mismo botón.</p>
      <a class="man-back" href="#top">↑ Volver al inicio</a>
    </section>

  </div>
</div>

<?php
    include_once("includes/footer.php");
?>
