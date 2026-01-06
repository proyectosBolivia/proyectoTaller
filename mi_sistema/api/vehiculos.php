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

            $stmt = $db->prepare("
                INSERT INTO tipos_vehiculo (nombre, descripcion) 
                VALUES (?, ?)
            ");

            if ($stmt->execute([$nombre, $descripcion])) {
                echo json_encode(['success' => true, 'message' => 'Tipo de vehículo creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear tipo de vehículo']);
            }
            break;

        case 'actualizar':
            $id = $_POST['id'];
            $nombre = sanitize($_POST['nombre']);
            $descripcion = sanitize($_POST['descripcion']);

            $stmt = $db->prepare("
                UPDATE tipos_vehiculo 
                SET nombre = ?, descripcion = ?
                WHERE id = ?
            ");

            if ($stmt->execute([$nombre, $descripcion, $id])) {
                echo json_encode(['success' => true, 'message' => 'Tipo de vehículo actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar tipo de vehículo']);
            }
            break;

        case 'eliminar':
            $id = $_POST['id'];

            $stmt = $db->prepare("SELECT COUNT(*) as total FROM solicitudes WHERE tipo_vehiculo_id = ?");
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            if ($result['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar. Hay solicitudes con este tipo de vehículo']);
                exit;
            }

            $stmt = $db->prepare("DELETE FROM tipos_vehiculo WHERE id = ?");

            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Tipo de vehículo eliminado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar tipo de vehículo']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>