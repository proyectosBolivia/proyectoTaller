<?php
require_once '../config.php';
requireAuth(['trabajador']);

$db = Database::getInstance()->getConnection();

$stmt = $db->prepare("
    SELECT a.*, s.placa_vehiculo, s.marca_vehiculo, s.modelo_vehiculo,
           c.nombre as cliente_nombre, srv.nombre as servicio_nombre
    FROM asignaciones a
    INNER JOIN solicitudes s ON a.solicitud_id = s.id
    INNER JOIN usuarios c ON s.cliente_id = c.id
    INNER JOIN servicios srv ON s.servicio_id = srv.id
    WHERE a.trabajador_id = ?
    ORDER BY a.fecha_asignacion DESC
");
$stmt->execute([$_SESSION['usuario_id']]);
$asignaciones = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Asignaciones - Trabajador</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Mis Asignaciones</h1>
            </header>

            <div class="dashboard-section">
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
                            <?php foreach ($asignaciones as $asig): ?>
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
                                    <td><?php echo date('d/m/Y H:i', strtotime($asig['fecha_asignacion'])); ?></td>
                                    <td>
                                        <button class="btn btn-sm btn-info"
                                            onclick='verDetalle(<?php echo json_encode($asig); ?>)'>👁️</button>
                                        <?php if ($asig['estado'] === 'asignada'): ?>
                                            <button class="btn btn-sm btn-success"
                                                onclick="cambiarEstado(<?php echo $asig['id']; ?>, 'en_proceso')">▶️
                                                Iniciar</button>
                                        <?php elseif ($asig['estado'] === 'en_proceso'): ?>
                                            <button class="btn btn-sm btn-primary"
                                                onclick="completarTrabajo(<?php echo $asig['id']; ?>)">✅ Completar</button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <div id="modalDetalle" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalle de Asignación</h2>
                <span class="close" onclick="closeModal('modalDetalle')">&times;</span>
            </div>
            <div id="contenidoDetalle" class="modal-body"></div>
        </div>
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

    <script src="../assets/js/trabajador.js"></script>
    <script>
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function verDetalle(asignacion) {
            const fechaInicio = asignacion.fecha_inicio ? new Date(asignacion.fecha_inicio).toLocaleString() : 'No iniciado';
            const fechaFin = asignacion.fecha_finalizacion ? new Date(asignacion.fecha_finalizacion).toLocaleString() : 'No finalizado';

            const html = `
                <div class="details-grid">
                    <div class="detail-item"><strong>ID:</strong> #${asignacion.id}</div>
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
            document.getElementById('contenidoDetalle').innerHTML = html;
            document.getElementById('modalDetalle').style.display = 'block';
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
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
</body>

</html>