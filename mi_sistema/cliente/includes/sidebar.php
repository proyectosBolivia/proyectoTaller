<aside class="sidebar" id="sidebar">
    <div class="sidebar-header">
        <div class="logo-container">
            <!-- <h2 class="amarillo full-text">Proyecto Taller</h2> -->
            <img src="../assets/img/sector1.png" alt="Logo" width="150px">
        </div>
        <p class="amarillo role-text">Cliente</p>
        <button id="sidebarToggle" class="sidebar-toggle">
            <i class="fa-regular fa-square-caret-left"></i>
        </button>
    </div>
    <nav class="sidebar-nav">
        <a href="dashboard.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-hard-drive"></i>
            <span class="nav-text">Dashboard</span>
        </a>
        <a href="solicitudes.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'solicitudes.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-envelope"></i>
            <span class="nav-text">Mis solicitudes</span>
        </a>
        <a href="nueva_solicitud.php"
            class="nav-item <?php echo basename($_SERVER['PHP_SELF']) == 'nueva_solicitud.php' ? 'active' : ''; ?>">
            <i class="fa-regular fa-address-book"></i>
            <span class="nav-text">Nueva solicitud</span>
        </a>
        <a href="../logout.php" class="nav-item">
            <i class="fa-regular fa-circle-xmark"></i>
            <span class="nav-text">Cerrar Sesión</span>
        </a>
    </nav>
</aside>
<script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>