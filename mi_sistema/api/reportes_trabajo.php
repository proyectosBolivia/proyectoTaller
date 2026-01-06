<?php
require_once '../config.php';
requireAuth(['trabajador']);

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? $_GET['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            $asignacion_id = $_POST['asignacion_id'];
            $descripcion = sanitize($_POST['descripcion']);
            $piezas_utilizadas = sanitize($_POST['piezas_utilizadas']);
            $horas_trabajadas = $_POST['horas_trabajadas'];
            $costo_total = $_POST['costo_total'];

            $db->beginTransaction();

            // Crear reporte
            $stmt = $db->prepare("
                INSERT INTO reportes_trabajo (
                    asignacion_id, trabajador_id, descripcion, 
                    piezas_utilizadas, horas_trabajadas, costo_total
                ) VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $asignacion_id,
                $_SESSION['usuario_id'],
                $descripcion,
                $piezas_utilizadas,
                $horas_trabajadas,
                $costo_total
            ]);

            // Actualizar estado de asignación a completada
            $stmt = $db->prepare("
                UPDATE asignaciones 
                SET estado = 'completada', fecha_finalizacion = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$asignacion_id]);

            // Actualizar solicitud correspondiente
            $stmt = $db->prepare("
                UPDATE solicitudes s
                INNER JOIN asignaciones a ON s.id = a.solicitud_id
                SET s.estado = 'completada'
                WHERE a.id = ?
            ");
            $stmt->execute([$asignacion_id]);

            $db->commit();

            echo json_encode(['success' => true, 'message' => 'Reporte creado y trabajo completado exitosamente']);
            break;

        case 'listar':
            $stmt = $db->prepare("
                SELECT rt.*, a.solicitud_id, s.placa_vehiculo
                FROM reportes_trabajo rt
                INNER JOIN asignaciones a ON rt.asignacion_id = a.id
                INNER JOIN solicitudes s ON a.solicitud_id = s.id
                WHERE rt.trabajador_id = ?
                ORDER BY rt.fecha_reporte DESC
            ");
            $stmt->execute([$_SESSION['usuario_id']]);

            echo json_encode($stmt->fetchAll());
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>