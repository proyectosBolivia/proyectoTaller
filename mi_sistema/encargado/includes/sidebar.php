<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <img src="../assets/img/sector1.png" alt="Logo" width="150px">
        </div>
        <p class="amarillo role-text">Encargado</p>
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fa-regular fa-square-caret-left"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-hard-drive"></i>
            <span class="nav-text">Inicio</span>
        </a>
        <a href="solicitudes.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'solicitudes.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-envelope"></i>
            <span class="nav-text">Solicitudes</span>
        </a>
        <a href="asignaciones.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'asignaciones.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-address-book"></i>
            <span class="nav-text">Asignaciones</span>
        </a>
        <a href="trabajadores.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'trabajadores.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-user"></i>
            <span class="nav-text">Trabajadores</span>
        </a>
        <a href="clientes.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'clientes.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-circle-user"></i>
            <span class="nav-text">Clientes</span>
        </a>
        <a href="servicios.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'servicios.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-calendar-check"></i>
            <span class="nav-text">Servicios</span>
        </a>
        <a href="vehiculos.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'vehiculos.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-truck"></i>
            <span class="nav-text">Tipos de Vehículo</span>
        </a>
        <a href="reportes.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'reportes.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-file-lines"></i>
            <span class="nav-text">Reportes</span>
        </a>
        <a href="../logout.php" class="nav-item">
            <i class="fa-regular fa-circle-xmark"></i>
            <span class="nav-text">Cerrar Sesión</span>
        </a>
    </nav>
</aside>
<script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>