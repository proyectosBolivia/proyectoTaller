<?php
require_once '../config.php';
requireAuth(['trabajador']);

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT rt.*, a.solicitud_id, s.placa_vehiculo, c.nombre as cliente_nombre
    FROM reportes_trabajo rt
    INNER JOIN asignaciones a ON rt.asignacion_id = a.id
    INNER JOIN solicitudes s ON a.solicitud_id = s.id
    INNER JOIN usuarios c ON s.cliente_id = c.id
    WHERE rt.trabajador_id = ?
    ORDER BY rt.fecha_reporte DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$reportes = $stmt->fetchAll();

// Estadísticas
$stmt = $db->prepare("
    SELECT 
        COUNT(*) as total_reportes,
        SUM(horas_trabajadas) as total_horas,
        SUM(costo_total) as total_ingresos
    FROM reportes_trabajo
    WHERE trabajador_id = ?
");
$stmt->execute([$_SESSION['usuario_id']]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reportes - Trabajador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Mis Reportes de Trabajo</h1>
                <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
            </header>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_reportes']; ?></h3>
                        <p>Reportes Creados</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total_horas'], 1); ?>h</h3>
                        <p>Horas Trabajadas</p>
                    </div>
                </div>
                <div class="stat-card stat-success">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo formatCurrency($stats['total_ingresos']); ?></h3>
                        <p>Ingresos Generados</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Vehículo</th>
                                <th>Descripción</th>
                                <th>Horas</th>
                                <th>Costo</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($reportes as $rep): ?>
                                <tr>
                                    <td>#<?php echo $rep['id']; ?></td>
                                    <td><?php echo htmlspecialchars($rep['cliente_nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($rep['placa_vehiculo']); ?></td>
                                    <td><?php echo htmlspecialchars(substr($rep['descripcion'], 0, 50)); ?>...</td>
                                    <td><?php echo $rep['horas_trabajadas']; ?>h</td>
                                    <td><?php echo formatCurrency($rep['costo_total']); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($rep['fecha_reporte'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick='verReporte(<?php echo json_encode($rep); ?>)'>👁️ Ver</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalReporte" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalle del Reporte</h2>
                <span class="close"
                    onclick="document.getElementById('modalReporte').style.display='none'">&times;</span>
            </div>
            <div id="contenidoReporte" class="modal-body"></div>
        </div>
    </div>

    <script>
        function verReporte(reporte) {
            const html = `
                <div class="details-grid">
                    <div class="detail-item"><strong>ID Reporte:</strong> #${reporte.id}</div>
                    <div class="detail-item"><strong>Cliente:</strong> ${reporte.cliente_nombre}</div>
                    <div class="detail-item"><strong>Vehículo:</strong> ${reporte.placa_vehiculo}</div>
                    <div class="detail-item"><strong>Horas Trabajadas:</strong> ${reporte.horas_trabajadas}h</div>
                    <div class="detail-item"><strong>Costo Total:</strong> Bs. ${parseFloat(reporte.costo_total).toFixed(2)}</div>
                    <div class="detail-item"><strong>Fecha Reporte:</strong> ${new Date(reporte.fecha_reporte).toLocaleString()}</div>
                    <div class="detail-item full-width">
                        <strong>Descripción del Trabajo:</strong><br>
                        ${reporte.descripcion}
                    </div>
                    <div class="detail-item full-width">
                        <strong>Piezas Utilizadas:</strong><br>
                        ${reporte.piezas_utilizadas || 'Ninguna'}
                    </div>
                </div>
            `;
            document.getElementById('contenidoReporte').innerHTML = html;
            document.getElementById('modalReporte').style.display = 'block';
        }

        window.onclick = function (event) {
            const modal = document.getElementById('modalReporte');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        }
    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
</body>

</html>