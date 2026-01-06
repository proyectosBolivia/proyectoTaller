<?php
require_once '../config.php';
requireAuth(['encargado']);

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            $nombre = sanitize($_POST['nombre']);
            $descripcion = sanitize($_POST['descripcion']);
            $precio_estimado = $_POST['precio_estimado'];
            $duracion_estimada = $_POST['duracion_estimada'];

            $stmt = $db->prepare("
                INSERT INTO servicios (nombre, descripcion, precio_estimado, duracion_estimada) 
                VALUES (?, ?, ?, ?)
            ");

            if ($stmt->execute([$nombre, $descripcion, $precio_estimado, $duracion_estimada])) {
                echo json_encode(['success' => true, 'message' => 'Servicio creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear servicio']);
            }
            break;

        case 'actualizar':
            $id = $_POST['id'];
            $nombre = sanitize($_POST['nombre']);
            $descripcion = sanitize($_POST['descripcion']);
            $precio_estimado = $_POST['precio_estimado'];
            $duracion_estimada = $_POST['duracion_estimada'];

            $stmt = $db->prepare("
                UPDATE servicios 
                SET nombre = ?, descripcion = ?, precio_estimado = ?, duracion_estimada = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$nombre, $descripcion, $precio_estimado, $duracion_estimada, $id])) {
                echo json_encode(['success' => true, 'message' => 'Servicio actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar servicio']);
            }
            break;

        case 'eliminar':
            $id = $_POST['id'];

            // Verificar si tiene solicitudes asociadas
            $stmt = $db->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE servicio_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            if ($result['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar. El servicio tiene solicitudes asociadas']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM servicios WHERE id = ?");

            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Servicio eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar servicio']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>