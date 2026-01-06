<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();

// Estadísticas
$stats = [
    'pendientes' => 0,
    'asignadas' => 0,
    'en_proceso' => 0,
    'completadas' => 0,
    'total_trabajadores' => 0,
    'total_clientes' => 0
];

// Solicitudes pendientes
$stmt = $db->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'");
$stats['pendientes'] = $stmt->fetch()['total'];

// Asignadas
$stmt = $db->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'asignada'");
$stats['asignadas'] = $stmt->fetch()['total'];

// En proceso
$stmt = $db->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'en_proceso'");
$stats['en_proceso'] = $stmt->fetch()['total'];

// Completadas
$stmt = $db->query("SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'completada'");
$stats['completadas'] = $stmt->fetch()['total'];

// Total trabajadores
$stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 2");
$stats['total_trabajadores'] = $stmt->fetch()['total'];

// Total clientes
$stmt = $db->query("SELECT COUNT(*) as total FROM usuarios WHERE rol_id = 3");
$stats['total_clientes'] = $stmt->fetch()['total'];

// Solicitudes recientes
$stmt = $db->query("
    SELECT s.*, u.nombre as cliente_nombre, srv.nombre as servicio_nombre, tv.nombre as tipo_vehiculo
    FROM solicitudes s
    INNER JOIN usuarios u ON s.cliente_id = u.id
    INNER JOIN servicios srv ON s.servicio_id = srv.id
    INNER JOIN tipos_vehiculo tv ON s.tipo_vehiculo_id = tv.id
    ORDER BY s.fecha_solicitud DESC
    LIMIT 10
");
$solicitudes_recientes = $stmt->fetchAll();

// Asignaciones activas
$stmt = $db->query("
    SELECT a.*, s.placa_vehiculo, t.nombre as trabajador_nombre, c.nombre as cliente_nombre
    FROM asignaciones a
    INNER JOIN solicitudes s ON a.solicitud_id = s.id
    INNER JOIN usuarios t ON a.trabajador_id = t.id
    INNER JOIN usuarios c ON s.cliente_id = c.id
    WHERE a.estado IN ('asignada', 'en_proceso')
    ORDER BY a.fecha_asignacion DESC
    LIMIT 10
");
$asignaciones_activas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Dashboard</h1>
                <div class="user-info">
                    <span>👤 <?php echo $_SESSION['nombre']; ?></span>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pendientes']; ?></h3>
                        <p>Solicitudes Pendientes</p>
                    </div>
                </div>
                <div class="stat-card stat-info">
                    <div class="stat-icon">📋</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['asignadas']; ?></h3>
                        <p>Asignadas</p>
                    </div>
                </div>
                <div class="stat-card stat-primary">
                    <div class="stat-icon">⚙️</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['en_proceso']; ?></h3>
                        <p>En Proceso</p>
                    </div>
                </div>
                <div class="stat-card stat-success">
                    <div class="stat-icon">✅</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['completadas']; ?></h3>
                        <p>Completadas</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👨‍🔧</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_trabajadores']; ?></h3>
                        <p>Trabajadores</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_clientes']; ?></h3>
                        <p>Clientes</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Solicitudes Recientes</h2>
                        <a href="solicitudes.php" class="btn btn-sm">Ver Todas</a>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Servicio</th>
                                    <th>Estado</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($solicitudes_recientes as $sol): ?>
                                    <tr>
                                        <td>#<?php echo $sol['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sol['cliente_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($sol['placa_vehiculo']); ?></td>
                                        <td><?php echo htmlspecialchars($sol['servicio_nombre']); ?></td>
                                        <td><span
                                                class="badge badge-<?php echo $sol['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $sol['estado'])); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($sol['fecha_solicitud'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="dashboard-section">
                    <div class="section-header">
                        <h2>Asignaciones Activas</h2>
                        <a href="asignaciones.php" class="btn btn-sm">Ver Todas</a>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trabajador</th>
                                    <th>Cliente</th>
                                    <th>Vehículo</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($asignaciones_activas as $asig): ?>
                                    <tr>
                                        <td>#<?php echo $asig['id']; ?></td>
                                        <td><?php echo htmlspecialchars($asig['trabajador_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($asig['cliente_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($asig['placa_vehiculo']); ?></td>
                                        <td><span
                                                class="badge badge-<?php echo $asig['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $asig['estado'])); ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script src="../assets/js/sidebar.js"></script>
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>

</body>

</html>