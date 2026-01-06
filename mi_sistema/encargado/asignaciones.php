<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT a.*, s.placa_vehiculo, s.marca_vehiculo, s.modelo_vehiculo,
           t.nombre as trabajador_nombre, c.nombre as cliente_nombre, 
           srv.nombre as servicio_nombre
    FROM asignaciones a
    INNER JOIN solicitudes s ON a.solicitud_id = s.id
    INNER JOIN usuarios t ON a.trabajador_id = t.id
    INNER JOIN usuarios c ON s.cliente_id = c.id
    INNER JOIN servicios srv ON s.servicio_id = srv.id
    ORDER BY a.fecha_asignacion DESC
");
$asignaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asignaciones - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Asignaciones de Trabajo</h1>
            </header>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Trabajador</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Vehículo</th>
                            <th>Estado</th>
                            <th>Fecha Asignación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($asignaciones as $asig): ?>
                            <tr>
                                <td>#<?php echo $asig['id']; ?></td>
                                <td><?php echo htmlspecialchars($asig['trabajador_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($asig['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($asig['servicio_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($asig['placa_vehiculo']); ?><br>
                                    <small><?php echo htmlspecialchars($asig['marca_vehiculo'] . ' ' . $asig['modelo_vehiculo']); ?></small>
                                </td>
                                <td><span
                                        class="badge badge-<?php echo $asig['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $asig['estado'])); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y H:i', strtotime($asig['fecha_asignacion'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                        onclick='verAsignacion(<?php echo json_encode($asig); ?>)'>👁️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="modalVerAsignacion" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalles de Asignación</h2>
                <span class="close" onclick="closeModal('modalVerAsignacion')">&times;</span>
            </div>
            <div id="detallesAsignacion" class="modal-body"></div>
        </div>
    </div>

    <script>
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function verAsignacion(asignacion) {
            const fechaInicio = asignacion.fecha_inicio ? new Date(asignacion.fecha_inicio).toLocaleString() : 'No iniciado';
            const fechaFin = asignacion.fecha_finalizacion ? new Date(asignacion.fecha_finalizacion).toLocaleString() : 'No finalizado';

            const detalles = `
                <div class="details-grid">
                    <div class="detail-item"><strong>ID:</strong> #${asignacion.id}</div>
                    <div class="detail-item"><strong>Trabajador:</strong> ${asignacion.trabajador_nombre}</div>
                    <div class="detail-item"><strong>Cliente:</strong> ${asignacion.cliente_nombre}</div>
                    <div class="detail-item"><strong>Servicio:</strong> ${asignacion.servicio_nombre}</div>
                    <div class="detail-item"><strong>Vehículo:</strong> ${asignacion.marca_vehiculo} ${asignacion.modelo_vehiculo}</div>
                    <div class="detail-item"><strong>Placa:</strong> ${asignacion.placa_vehiculo}</div>
                    <div class="detail-item"><strong>Estado:</strong> <span class="badge badge-${asignacion.estado}">${asignacion.estado.replace('_', ' ')}</span></div>
                    <div class="detail-item"><strong>Fecha Asignación:</strong> ${new Date(asignacion.fecha_asignacion).toLocaleString()}</div>
                    <div class="detail-item"><strong>Fecha Inicio:</strong> ${fechaInicio}</div>
                    <div class="detail-item"><strong>Fecha Finalización:</strong> ${fechaFin}</div>
                    <div class="detail-item full-width"><strong>Observaciones:</strong><br>${asignacion.observaciones || 'Sin observaciones'}</div>
                </div>
            `;

            document.getElementById('detallesAsignacion').innerHTML = detalles;
            document.getElementById('modalVerAsignacion').style.display = 'block';
        }

        window.onclick = function (event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="../assets/js/asignaciones.js"></script>
</body>

</html>