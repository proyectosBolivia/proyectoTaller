<?php
require_once '../config.php';
requireAuth(['trabajador']);

$db = Database::getInstance()->getConnection();

// Estadísticas del trabajador
$stats = [
    'pendientes' => 0,
    'en_proceso' => 0,
    'completadas' => 0,
];

$stmt = $db->prepare("
    SELECT COUNT(*) as total FROM asignaciones 
    WHERE trabajador_id = ? AND estado = 'asignada'
");
$stmt->execute([$_SESSION['usuario_id']]);
$stats['pendientes'] = $stmt->fetch()['total'];

$stmt = $db->prepare("
    SELECT COUNT(*) as total FROM asignaciones 
    WHERE trabajador_id = ? AND estado = 'en_proceso'
");
$stmt->execute([$_SESSION['usuario_id']]);
$stats['en_proceso'] = $stmt->fetch()['total'];

$stmt = $db->prepare("
    SELECT COUNT(*) as total FROM asignaciones 
    WHERE trabajador_id = ? AND estado = 'completada'
");
$stmt->execute([$_SESSION['usuario_id']]);
$stats['completadas'] = $stmt->fetch()['total'];

// Asignaciones activas
$stmt = $db->prepare("
    SELECT a.*, s.placa_vehiculo, s.marca_vehiculo, s.modelo_vehiculo,
           c.nombre as cliente_nombre, srv.nombre as servicio_nombre
    FROM asignaciones a
    INNER JOIN solicitudes s ON a.solicitud_id = s.id
    INNER JOIN usuarios c ON s.cliente_id = c.id
    INNER JOIN servicios srv ON s.servicio_id = srv.id
    WHERE a.trabajador_id = ? AND a.estado IN ('asignada', 'en_proceso')
    ORDER BY a.fecha_asignacion DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$asignaciones_activas = $stmt->fetchAll();

// Trabajos completados recientemente
$stmt = $db->prepare("
    SELECT a.*, s.placa_vehiculo, c.nombre as cliente_nombre
    FROM asignaciones a
    INNER JOIN solicitudes s ON a.solicitud_id = s.id
    INNER JOIN usuarios c ON s.cliente_id = c.id
    WHERE a.trabajador_id = ? AND a.estado = 'completada'
    ORDER BY a.fecha_finalizacion DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['usuario_id']]);
$completadas_recientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Trabajador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Mi Dashboard</h1>
                <div class="user-info">
                    <span>👤 <?php echo $_SESSION['nombre']; ?></span>
                </div>
            </header>

            <div class="stats-grid">
                <div class="stat-card stat-warning">
                    <div class="stat-icon">📋</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pendientes']; ?></h3>
                        <p>Asignaciones Pendientes</p>
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
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Asignaciones Activas</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Vehículo</th>
                                <th>Estado</th>
                                <th>Fecha Asignación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($asignaciones_activas)): ?>
                                <tr>
                                    <td colspan="7" style="text-align: center;">No tienes asignaciones activas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($asignaciones_activas as $asig): ?>
                                    <tr>
                                        <td>#<?php echo $asig['id']; ?></td>
                                        <td><?php echo htmlspecialchars($asig['cliente_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($asig['servicio_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($asig['placa_vehiculo']); ?><br>
                                            <small><?php echo htmlspecialchars($asig['marca_vehiculo'] . ' ' . $asig['modelo_vehiculo']); ?></small>
                                        </td>
                                        <td><span
                                                class="badge badge-<?php echo $asig['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $asig['estado'])); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($asig['fecha_asignacion'])); ?></td>
                                        <td>
                                            <?php if ($asig['estado'] === 'asignada'): ?>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="cambiarEstado(<?php echo $asig['id']; ?>, 'en_proceso')">Iniciar</button>
                                            <?php elseif ($asig['estado'] === 'en_proceso'): ?>
                                                <button class="btn btn-sm btn-primary"
                                                    onclick="completarTrabajo(<?php echo $asig['id']; ?>)">Completar</button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Trabajos Completados Recientemente</h2>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Fecha Finalización</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($completadas_recientes as $comp): ?>
                                <tr>
                                    <td>#<?php echo $comp['id']; ?></td>
                                    <td><?php echo htmlspecialchars($comp['cliente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($comp['placa_vehiculo']); ?></td>
                                    <td><?php echo date('d/m/Y H:i', strtotime($comp['fecha_finalizacion'])); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalReporte" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Completar Trabajo y Crear Reporte</h2>
                <span class="close" onclick="closeModal('modalReporte')">&times;</span>
            </div>
            <form id="formReporte" onsubmit="guardarReporte(event)">
                <input type="hidden" id="asignacion_id_reporte" name="asignacion_id">
                <div class="form-group">
                    <label for="descripcion">Descripción del Trabajo *</label>
                    <textarea id="descripcion" name="descripcion" rows="4" required></textarea>
                </div>
                <div class="form-group">
                    <label for="piezas_utilizadas">Piezas Utilizadas</label>
                    <textarea id="piezas_utilizadas" name="piezas_utilizadas" rows="2"></textarea>
                </div>
                <div class="form-group">
                    <label for="horas_trabajadas">Horas Trabajadas *</label>
                    <input type="number" id="horas_trabajadas" name="horas_trabajadas" step="0.5" min="0.5" required>
                </div>
                <div class="form-group">
                    <label for="costo_total">Costo Total (Bs.) *</label>
                    <input type="number" id="costo_total" name="costo_total" step="0.01" min="0" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modalReporte')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar y Completar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>