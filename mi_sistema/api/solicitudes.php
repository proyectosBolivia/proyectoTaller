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
            requireAuth(['cliente']);

            $cliente_id = $_SESSION['usuario_id'];
            $servicio_id = $_POST['servicio_id'];
            $tipo_vehiculo_id = $_POST['tipo_vehiculo_id'];
            $marca_vehiculo = sanitize($_POST['marca_vehiculo']);
            $modelo_vehiculo = sanitize($_POST['modelo_vehiculo']);
            $año_vehiculo = $_POST['año_vehiculo'];
            $placa_vehiculo = sanitize($_POST['placa_vehiculo']);
            $descripcion_problema = sanitize($_POST['descripcion_problema']);

            $db->beginTransaction();

            $stmt = $db->prepare("
                INSERT INTO solicitudes (cliente_id, tipo_vehiculo_id, servicio_id, marca_vehiculo, modelo_vehiculo, año_vehiculo, placa_vehiculo, descripcion_problema) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)
            ");

            if ($stmt->execute([$cliente_id, $tipo_vehiculo_id, $servicio_id, $marca_vehiculo, $modelo_vehiculo, $año_vehiculo, $placa_vehiculo, $descripcion_problema])) {
                $db->commit();
                echo json_encode(['success' => true, 'message' => 'Solicitud creada exitosamente']);
            } else {
                $db->rollBack();
                echo json_encode(['success' => false, 'message' => 'Error al crear la solicitud']);
            }
            break;

        case 'cambiar_estado':
            requireAuth(['encargado']);

            $solicitud_id = $_POST['solicitud_id'];
            $nuevo_estado = $_POST['estado'];

            $stmt = $db->prepare("UPDATE solicitudes SET estado = ? WHERE id = ?");

            if ($stmt->execute([$nuevo_estado, $solicitud_id])) {
                echo json_encode(['success' => true, 'message' => 'Estado actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar estado']);
            }
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