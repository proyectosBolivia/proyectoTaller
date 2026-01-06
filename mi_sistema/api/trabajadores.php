<?php
// Este archivo ya existe como api_trabajadores pero necesita soportar GET
// Actualización del archivo api/trabajadores.php para soportar listar

require_once '../config.php';
requireAuth(['encargado']);

header('Content-Type: application/json');

$db = Database::getInstance()->getConnection();

// Si es GET, listar trabajadores
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'listar') {
    $stmt = $db->query("
        SELECT id, nombre, email, telefono, activo 
        FROM usuarios 
        WHERE rol_id = 2 
        ORDER BY nombre ASC
    ");
    echo json_encode($stmt->fetchAll());
    exit;
}

// POST para operaciones CRUD
$action = $_POST['action'] ?? '';

try {
    switch ($action) {
        case 'crear':
            $nombre = sanitize($_POST['nombre']);
            $email = sanitize($_POST['email']);
            $telefono = sanitize($_POST['telefono']);
            $password = $_POST['password'];
            $activo = $_POST['activo'] ?? 1;

            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
            $stmt->execute([$email]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                exit;
            }

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $db->prepare("
                INSERT INTO usuarios (nombre, email, password, telefono, rol_id, activo) 
                VALUES (?, ?, ?, ?, 2, ?)
            ");

            if ($stmt->execute([$nombre, $email, $hashed_password, $telefono, $activo])) {
                echo json_encode(['success' => true, 'message' => 'Trabajador creado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al crear trabajador']);
            }
            break;

        case 'actualizar':
            $id = $_POST['id'];
            $nombre = sanitize($_POST['nombre']);
            $email = sanitize($_POST['email']);
            $telefono = sanitize($_POST['telefono']);
            $password = $_POST['password'];
            $activo = $_POST['activo'];

            $stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ? AND id != ?");
            $stmt->execute([$email, $id]);
            if ($stmt->fetch()) {
                echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
                exit;
            }

            if (!empty($password)) {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $db->prepare("
                    UPDATE usuarios 
                    SET nombre = ?, email = ?, password = ?, telefono = ?, activo = ?
                    WHERE id = ? AND rol_id = 2
                ");
                $result = $stmt->execute([$nombre, $email, $hashed_password, $telefono, $activo, $id]);
            } else {
                $stmt = $db->prepare("
                    UPDATE usuarios 
                    SET nombre = ?, email = ?, telefono = ?, activo = ?
                    WHERE id = ? AND rol_id = 2
                ");
                $result = $stmt->execute([$nombre, $email, $telefono, $activo, $id]);
            }

            if ($result) {
                echo json_encode(['success' => true, 'message' => 'Trabajador actualizado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al actualizar trabajador']);
            }
            break;

        case 'eliminar':
            $id = $_POST['id'];

            $stmt = $db->prepare("
                SELECT COUNT(*) as total FROM asignaciones 
                WHERE trabajador_id = ? AND estado IN ('asignada', 'en_proceso')
            ");
            $stmt->execute([$id]);
            $result = $stmt->fetch();

            if ($result['total'] > 0) {
                echo json_encode(['success' => false, 'message' => 'No se puede eliminar. El trabajador tiene asignaciones activas']);
                exit;
            }

            $stmt = $db->prepare("UPDATE usuarios SET activo = 0 WHERE id = ? AND rol_id = 2");

            if ($stmt->execute([$id])) {
                echo json_encode(['success' => true, 'message' => 'Trabajador desactivado exitosamente']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al eliminar trabajador']);
            }
            break;

        default:
            echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
