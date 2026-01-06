<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isAuthenticated()) {
    echo json_encode(['success' => false, 'message' => 'No autenticado']);
    exit;
}

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            requireAuth(['encargado']);

            $solicitud_id = $_POST['solicitud_id'];
            $trabajador_id = $_POST['trabajador_id'];
            $observaciones = sanitize($_POST['observaciones']);

            $db->beginTransaction();

            // Crear asignación
            $stmt = $db->prepare("
                INSERT INTO asignaciones (solicitud_id, trabajador_id, encargado_id, observaciones) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$solicitud_id, $trabajador_id, $_SESSION['usuario_id'], $observaciones]);

            // Actualizar estado de solicitud
            $stmt = $db->prepare("UPDATE solicitudes SET estado = 'asignada' WHERE id = ?");
            $stmt->execute([$solicitud_id]);

            $db->commit();

            echo json_encode(['success' => true, 'message' => 'Asignación creada exitosamente']);
            break;

        case 'actualizar_estado':
            requireAuth(['trabajador', 'encargado']);

            $asignacion_id = $_POST['asignacion_id'];
            $nuevo_estado = $_POST['estado'];
            $fecha_campo = '';

            if ($nuevo_estado === 'en_proceso') {
                $fecha_campo = ', fecha_inicio = NOW()';
            } elseif ($nuevo_estado === 'completada') {
                $fecha_campo = ', fecha_finalizacion = NOW()';
            }

            $db->beginTransaction();

            // Actualizar asignación
            $stmt = $db->prepare("UPDATE asignaciones SET estado = ? $fecha_campo WHERE id = ?");
            $stmt->execute([$nuevo_estado, $asignacion_id]);

            // Actualizar solicitud correspondiente
            $stmt = $db->prepare("
                UPDATE solicitudes s
                INNER JOIN asignaciones a ON s.id = a.solicitud_id
                SET s.estado = ?
                WHERE a.id = ?
            ");
            $stmt->execute([$nuevo_estado, $asignacion_id]);

            $db->commit();

            echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
            break;

        case 'listar':
            if ($_SESSION['rol'] === 'trabajador') {
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
            } else {
                $stmt = $db->query("
                    SELECT a.*, s.placa_vehiculo, t.nombre as trabajador_nombre, 
                           c.nombre as cliente_nombre, srv.nombre as servicio_nombre
                    FROM asignaciones a
                    INNER JOIN solicitudes s ON a.solicitud_id = s.id
                    INNER JOIN usuarios t ON a.trabajador_id = t.id
                    INNER JOIN usuarios c ON s.cliente_id = c.id
                    INNER JOIN servicios srv ON s.servicio_id = srv.id
                    ORDER BY a.fecha_asignacion DESC
                ");
            }

            echo json_encode($stmt->fetchAll());
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    $db->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>