<?php
require_once '../config.php';
requireAuth(['cliente']);

$db = Database::getInstance()->getConnection();

// Estadísticas del cliente
$stats = ['pendientes' => 0, 'en_proceso' => 0, 'completadas' => 0];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE cliente_id = ? AND estado = 'pendiente'");
$stmt->execute([$_SESSION['usuario_id']]);
$stats['pendientes'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE cliente_id = ? AND estado IN ('asignada', 'en_proceso')");
$stmt->execute([$_SESSION['usuario_id']]);
$stats['en_proceso'] = $stmt->fetch()['total'];

$stmt = $db->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE cliente_id = ? AND estado = 'completada'");
$stmt->execute([$_SESSION['usuario_id']]);
$stats['completadas'] = $stmt->fetch()['total'];

// Solicitudes recientes
$stmt = $db->prepare("
    SELECT s.*, srv.nombre as servicio_nombre, tv.nombre as tipo_vehiculo
    FROM solicitudes s
    INNER JOIN servicios srv ON s.servicio_id = srv.id
    INNER JOIN tipos_vehiculo tv ON s.tipo_vehiculo_id = tv.id
    WHERE s.cliente_id = ?
    ORDER BY s.fecha_solicitud DESC
    LIMIT 5
");
$stmt->execute([$_SESSION['usuario_id']]);
$solicitudes_recientes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Cliente</title>
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
                    <div class="stat-icon">⏳</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['pendientes']; ?></h3>
                        <p>Solicitudes Pendientes</p>
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

            <div class="action-buttons">
                <a href="nueva_solicitud.php" class="btn btn-primary btn-large">➕ Nueva Solicitud de Servicio</a>
            </div>

            <div class="dashboard-section">
                <div class="section-header">
                    <h2>Mis Solicitudes Recientes</h2>
                    <a href="solicitudes.php" class="btn btn-sm">Ver Todas</a>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Servicio</th>
                                <th>Vehículo</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($solicitudes_recientes)): ?>
                                <tr>
                                    <td colspan="6" style="text-align: center;">No tienes solicitudes registradas</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($solicitudes_recientes as $sol): ?>
                                    <tr>
                                        <td>#<?php echo $sol['id']; ?></td>
                                        <td><?php echo htmlspecialchars($sol['servicio_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($sol['placa_vehiculo']); ?><br>
                                            <small><?php echo htmlspecialchars($sol['marca_vehiculo'] . ' ' . $sol['modelo_vehiculo']); ?></small>
                                        </td>
                                        <td><span
                                                class="badge badge-<?php echo $sol['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $sol['estado'])); ?></span>
                                        </td>
                                        <td><?php echo date('d/m/Y', strtotime($sol['fecha_solicitud'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info"
                                                onclick='verDetalle(<?php echo json_encode($sol); ?>)'>👁️ Ver</button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalDetalle" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalle de Solicitud</h2>
                <span class="close"
                    onclick="document.getElementById('modalDetalle').style.display='none'">&times;</span>
            </div>
            <div id="contenidoDetalle" class="modal-body"></div>
        </div>
    </div>

    <script>
        function verDetalle(solicitud) {
            const html = `
                <div class="details-grid">
                    <div class="detail-item"><strong>ID:</strong> #${solicitud.id}</div>
                    <div class="detail-item"><strong>Servicio:</strong> ${solicitud.servicio_nombre}</div>
                    <div class="detail-item"><strong>Tipo de Vehículo:</strong> ${solicitud.tipo_vehiculo}</div>
                    <div class="detail-item"><strong>Marca:</strong> ${solicitud.marca_vehiculo}</div>
                    <div class="detail-item"><strong>Modelo:</strong> ${solicitud.modelo_vehiculo}</div>
                    <div class="detail-item"><strong>Año:</strong> ${solicitud.año_vehiculo}</div>
                    <div class="detail-item"><strong>Placa:</strong> ${solicitud.placa_vehiculo}</div>
                    <div class="detail-item"><strong>Estado:</strong> <span class="badge badge-${solicitud.estado}">${solicitud.estado.replace('_', ' ')}</span></div>
                    <div class="detail-item full-width"><strong>Descripción del Problema:</strong><br>${solicitud.descripcion_problema || 'No especificada'}</div>
                    <div class="detail-item"><strong>Fecha de Solicitud:</strong> ${new Date(solicitud.fecha_solicitud).toLocaleString()}</div>
                </div>
            `;
            document.getElementById('contenidoDetalle').innerHTML = html;
            document.getElementById('modalDetalle').style.display = 'block';
        }

        window.onclick = function (event) {
            const modal = document.getElementById('modalDetalle');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>