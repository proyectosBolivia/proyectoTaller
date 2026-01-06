<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();

// Estadísticas generales
$stats = [];

// Solicitudes por estado
$stmt = $db->query("
    SELECT estado, COUNT(*) as total 
    FROM solicitudes 
    GROUP BY estado
");
$stats['por_estado'] = $stmt->fetchAll();

// Servicios más solicitados
$stmt = $db->query("
    SELECT s.nombre, COUNT(sol.id) as total
    FROM servicios s
    LEFT JOIN solicitudes sol ON s.id = sol.servicio_id
    GROUP BY s.id
    ORDER BY total DESC
    LIMIT 5
");
$stats['servicios_top'] = $stmt->fetchAll();

// Trabajadores con más asignaciones
$stmt = $db->query("
    SELECT u.nombre, COUNT(a.id) as total
    FROM usuarios u
    INNER JOIN asignaciones a ON u.id = a.trabajador_id
    WHERE u.rol_id = 2
    GROUP BY u.id
    ORDER BY total DESC
    LIMIT 5
");
$stats['trabajadores_top'] = $stmt->fetchAll();

// Reportes de trabajo completados
$stmt = $db->query("
    SELECT rt.*, u.nombre as trabajador_nombre, a.solicitud_id
    FROM reportes_trabajo rt
    INNER JOIN usuarios u ON rt.trabajador_id = u.id
    INNER JOIN asignaciones a ON rt.asignacion_id = a.id
    ORDER BY rt.fecha_reporte DESC
    LIMIT 10
");
$reportes_trabajo = $stmt->fetchAll();

// Ingresos estimados
$stmt = $db->query("
    SELECT SUM(costo_total) as total_ingresos
    FROM reportes_trabajo
");
$ingresos = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Reportes y Estadísticas</h1>
                <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
            </header>

            <div class="stats-grid">
                <div class="stat-card stat-success">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo formatCurrency($ingresos['total_ingresos'] ?? 0); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>
            </div>

            <div class="reports-grid">
                <div class="report-section">
                    <h2>Solicitudes por Estado</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['por_estado'] as $item): ?>
                                    <tr>
                                        <td><span
                                                class="badge badge-<?php echo $item['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $item['estado'])); ?></span>
                                        </td>
                                        <td><?php echo $item['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-section">
                    <h2>Servicios Más Solicitados</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Solicitudes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['servicios_top'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                        <td><?php echo $item['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-section">
                    <h2>Trabajadores Más Activos</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Trabajador</th>
                                    <th>Asignaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['trabajadores_top'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                        <td><?php echo $item['total']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-section full-width">
                    <h2>Reportes de Trabajo Recientes</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Trabajador</th>
                                    <th>Descripción</th>
                                    <th>Horas</th>
                                    <th>Costo</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reportes_trabajo as $rt): ?>
                                    <tr>
                                        <td>#<?php echo $rt['id']; ?></td>
                                        <td><?php echo htmlspecialchars($rt['trabajador_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($rt['descripcion'], 0, 50)); ?>...</td>
                                        <td><?php echo $rt['horas_trabajadas']; ?>h</td>
                                        <td><?php echo formatCurrency($rt['costo_total']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rt['fecha_reporte'])); ?></td>
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

</body>

</html>