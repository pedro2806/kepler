<?php
require_once 'conn.php';

// Seguridad: Redirigir si no hay sesión activa
if (!isset($_SESSION['user_id'])) {
    echo '<script>window.location.assign("index.php")</script>';
    exit;
}

$noEmpleado = $_SESSION['employee_number'];
?>

<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
        <div class="sidebar-brand-icon">
            <img src="https://www.kepler-metrology.com.mx/apple-touch-icon.png" width="40" alt="Logo">
        </div>
        <div class="sidebar-brand-text mx-2">KEPLER CRM</div>
    </a>

    <li class="nav-item border-bottom-light pb-2 mb-2">
        <div class="text-center pt-0">            
            <div class="small text-white-50">Bienvenido,</div>
            <div class="font-weight-bold text-white"><?php echo $_SESSION['username']; ?></div>
        </div>
    </li>

    <hr class="sidebar-divider my-0">
<!-- 
    <li class="nav-item">
        <a class="nav-link" href="dashboard_laboratorio.php">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Tablero Laboratorios</span>
        </a>
    </li>
-->

    <li class="nav-item">
        <a class="nav-link" href="dashboard.php">
            <i class="fas fa-fw fa-th-large"></i>
            <span>Tablero General</span>
        </a>
    </li>

    <hr class="sidebar-divider">

    <li class="nav-item" id="li-crm">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCRM">
            <i class="fas fa-fw fa-folder"></i>
            <span>CRM</span>
            <i class="fas fa-angle-down float-end"></i>
        </a>
        <div id="collapseCRM" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="proyectos_forecast.php">Proyectos de venta</a>
                <a class="collapse-item" href="actividades.php">Actividades</a>
                <!-- <a class="collapse-item" href="visitas.php">Transcripción de visitas</a>
                <a class="collapse-item" href="notas_voz.php">Notas de voz</a>-->
                <a class="collapse-item" href="prospectos.php">Prospectos</a>
                <a class="collapse-item" href="clientes.php">Clientes</a>
                <a class="collapse-item" href="contactos.php">Contactos</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="li-sales">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseVentas">
            <i class="fas fa-fw fa-money-bill-wave"></i>
            <span>Ventas</span>
            <i class="fas fa-angle-down float-end"></i>
        </a>
        <div id="collapseVentas" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="ordenes_venta.php">Ordenes de venta</a>
                <a class="collapse-item" href="cotizacion.php">Nueva Cotizacion</a>
                <a class="collapse-item" href="ver_cotizaciones.php">Ver Cotizaciones</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="li-catalogos">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCatalogos">
            <i class="fas fa-fw fa-book"></i>
            <span>Catálogos</span>
            <i class="fas fa-angle-down float-end"></i>
        </a>
        <div id="collapseCatalogos" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="servicios.php">Servicios</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="li-ots">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseOTS">
            <i class="fas fa-fw fa-clipboard"></i>
            <span>Control de OTS</span>
            <i class="fas fa-angle-down float-end"></i>
        </a>
        <div id="collapseOTS" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="ordenes_trabajo.php">Órdenes de trabajo</a>
                <a class="collapse-item" href="certificados.php">Certificados</a>
                <a class="collapse-item" href="generar_ot_sitio.php">Generar OT en sitio</a>
                <a class="collapse-item" href="generar_ot_lab.php">Generar OT laboratorio</a>
                <a class="collapse-item" href="calendario.php">Calendario</a>
                <a class="collapse-item" href="nueva_ot_interna.php">Nueva OT Interna</a>
                <a class="collapse-item" href="ver_ot_internas.php">Ver OT Internas</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="li-pattern">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapsePatrones">
            <i class="fas fa-fw fa-boxes"></i>
            <span>Control de patrones</span>
            <i class="fas fa-angle-down float-end"></i>
        </a>
        <div id="collapsePatrones" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="inventario_patrones.php">Inventario</a>
            </div>
        </div>
    </li>

    <li class="nav-item" id="li-customerservice">
        <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseCustomer">
            <i class="fas fa-fw fa-user-friends"></i>
            <span>Atención a clientes</span>
            <i class="fas fa-angle-down float-end"></i>
        </a>
        <div id="collapseCustomer" class="collapse" data-bs-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <a class="collapse-item" href="reporte_entradas.php">Reporte de entradas</a>
                <a class="collapse-item" href="transferencias.php">Transferencias</a>
            </div>
        </div>
    </li>
<!-- 
    <hr class="sidebar-divider">

    <li class="nav-item">
        <a class="nav-link" href="sgc.php"><i class="fas fa-fw fa-file-alt"></i> <span>SGC</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="tickets.php"><i class="fas fa-fw fa-ticket-alt"></i> <span>Tickets</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="exportar.php"><i class="fas fa-fw fa-file-export"></i> <span>Exportar Reportes</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="javascript:void(0)" id="a-qr-code"><i class="fas fa-fw fa-qrcode"></i> <span>Código QR</span></a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="permisos.php"><i class="fas fa-fw fa-user-shield"></i> <span>Permisos</span></a>
    </li>

    <li class="nav-item">
        <a class="nav-link" href="Manual_Planeacion.pdf" target="_blank">
            <i class="fas fa-fw fa-book"></i>
            <span>Documentación</span>
        </a>
    </li>
-->
    <div class="text-center d-none d-md-inline mt-4">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

</ul>

