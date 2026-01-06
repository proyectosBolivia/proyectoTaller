<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();
$stmt = $db->query("
    SELECT s.*, u.nombre as cliente_nombre, srv.nombre as servicio_nombre, 
           tv.nombre as tipo_vehiculo
    FROM solicitudes s
    INNER JOIN usuarios u ON s.cliente_id = u.id
    INNER JOIN servicios srv ON s.servicio_id = srv.id
    INNER JOIN tipos_vehiculo tv ON s.tipo_vehiculo_id = tv.id
    ORDER BY s.fecha_solicitud DESC
");
$solicitudes = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Gestión de Solicitudes</h1>
            </header>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Cliente</th>
                            <th>Servicio</th>
                            <th>Vehículo</th>
                            <th>Placa</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($solicitudes as $sol): ?>
                            <tr>
                                <td>#<?php echo $sol['id']; ?></td>
                                <td><?php echo htmlspecialchars($sol['cliente_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($sol['servicio_nombre']); ?></td>
                                <td><?php echo htmlspecialchars($sol['tipo_vehiculo']); ?><br>
                                    <small><?php echo htmlspecialchars($sol['marca_vehiculo'] . ' ' . $sol['modelo_vehiculo']); ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($sol['placa_vehiculo']); ?></td>
                                <td><span
                                        class="badge badge-<?php echo $sol['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $sol['estado'])); ?></span>
                                </td>
                                <td><?php echo date('d/m/Y', strtotime($sol['fecha_solicitud'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                        onclick='verSolicitud(<?php echo json_encode($sol); ?>)'><i
                                            class="fa-regular fa-eye"></i>Ver</button>
                                    <?php if ($sol['estado'] === 'pendiente'): ?>
                                        <button class="btn btn-sm btn-success"
                                            onclick="asignarSolicitud(<?php echo $sol['id']; ?>)"><i
                                                class="fa-regular fa-address-book"></i>Asignar</button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-warning"
                                        onclick='cambiarEstado(<?php echo $sol['id']; ?>, "<?php echo $sol['estado']; ?>")'>
                                        <i class="fa-regular fa-pen-to-square"></i>
                                        Actualizar</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <div id="modalVerSolicitud" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalles de Solicitud</h2>
                <span class="close" onclick="closeModal('modalVerSolicitud')">&times;</span>
            </div>
            <div id="detallesSolicitud" class="modal-body"></div>
        </div>
    </div>

    <div id="modalAsignar" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Asignar Trabajador</h2>
                <span class="close" onclick="closeModal('modalAsignar')">&times;</span>
            </div>
            <form id="formAsignar" onsubmit="guardarAsignacion(event)">
                <input type="hidden" id="solicitud_id_asignar" name="solicitud_id">
                <div class="form-group">
                    <label for="trabajador_id">Trabajador *</label>
                    <select id="trabajador_id" name="trabajador_id" required></select>
                </div>
                <div class="form-group">
                    <label for="observaciones">Observaciones</label>
                    <textarea id="observaciones" name="observaciones" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modalAsignar')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Asignar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="modalCambiarEstado" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Cambiar Estado</h2>
                <span class="close" onclick="closeModal('modalCambiarEstado')">&times;</span>
            </div>
            <form id="formCambiarEstado" onsubmit="guardarCambioEstado(event)">
                <input type="hidden" id="solicitud_id_estado" name="solicitud_id">
                <div class="form-group">
                    <label for="nuevo_estado">Nuevo Estado *</label>
                    <select id="nuevo_estado" name="estado" required>
                        <option value="pendiente">Pendiente</option>
                        <option value="asignada">Asignada</option>
                        <option value="en_proceso">En Proceso</option>
                        <option value="completada">Completada</option>
                        <option value="cancelada">Cancelada</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modalCambiarEstado')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
    <script src="../assets/js/solicitudes.js"></script>
</body>

</html>