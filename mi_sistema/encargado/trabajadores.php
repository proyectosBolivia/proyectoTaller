<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();

// Obtener todos los trabajadores
$stmt = $db->query("
    SELECT u.* FROM usuarios u 
    WHERE u.rol_id = 2 
    ORDER BY u.nombre ASC
");
$trabajadores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trabajadores - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Gestión de Trabajadores</h1>
                <button class="btn btn-primary" onclick="openModal('modalTrabajador')">➕ Nuevo Trabajador</button>
            </header>

            <div class="table-container">
                <table id="tablaTrabajadores">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Teléfono</th>
                            <th>Fecha Registro</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($trabajadores as $trab): ?>
                            <tr>
                                <td><?php echo $trab['id']; ?></td>
                                <td><?php echo htmlspecialchars($trab['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($trab['email']); ?></td>
                                <td><?php echo htmlspecialchars($trab['telefono']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($trab['fecha_registro'])); ?></td>
                                <td><span
                                        class="badge <?php echo $trab['activo'] ? 'badge-success' : 'badge-error'; ?>"><?php echo $trab['activo'] ? 'Activo' : 'Inactivo'; ?></span>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                        onclick="editarTrabajador(<?php echo htmlspecialchars(json_encode($trab)); ?>)">✏️</button>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="eliminarTrabajador(<?php echo $trab['id']; ?>)">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Trabajador -->
    <div id="modalTrabajador" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitulo">Nuevo Trabajador</h2>
                <span class="close" onclick="closeModal('modalTrabajador')">&times;</span>
            </div>
            <form id="formTrabajador" onsubmit="guardarTrabajador(event)">
                <input type="hidden" id="trabajador_id" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="email">Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono">
                </div>
                <div class="form-group">
                    <label for="password">Contraseña <span id="passRequired">*</span></label>
                    <input type="password" id="password" name="password" minlength="6">
                    <small>Dejar en blanco para mantener la actual (al editar)</small>
                </div>
                <div class="form-group">
                    <label for="activo">Estado</label>
                    <select id="activo" name="activo">
                        <option value="1">Activo</option>
                        <option value="0">Inactivo</option>
                    </select>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                        onclick="closeModal('modalTrabajador')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/trabajadores.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>