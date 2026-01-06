<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM servicios ORDER BY nombre ASC");
$servicios = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Servicios - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Gestión de Servicios</h1>
                <button class="btn btn-primary" onclick="openModalServicio()">➕ Nuevo Servicio</button>
            </header>

            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Descripción</th>
                            <th>Precio Estimado</th>
                            <th>Duración (hrs)</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($servicios as $serv): ?>
                            <tr>
                                <td><?php echo $serv['id']; ?></td>
                                <td><?php echo htmlspecialchars($serv['nombre']); ?></td>
                                <td><?php echo htmlspecialchars(substr($serv['descripcion'], 0, 50)); ?>...</td>
                                <td><?php echo formatCurrency($serv['precio_estimado']); ?></td>
                                <td><?php echo $serv['duracion_estimada']; ?>h</td>
                                <td>
                                    <button class="btn btn-sm btn-info"
                                        onclick='editarServicio(<?php echo json_encode($serv); ?>)'>✏️</button>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="eliminarServicio(<?php echo $serv['id']; ?>)">🗑️</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>

    <!-- Modal Servicio -->
    <div id="modalServicio" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTituloServicio">Nuevo Servicio</h2>
                <span class="close" onclick="closeModalServicio()">&times;</span>
            </div>
            <form id="formServicio" onsubmit="guardarServicio(event)">
                <input type="hidden" id="servicio_id" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label for="precio_estimado">Precio Estimado (Bs.) *</label>
                    <input type="number" id="precio_estimado" name="precio_estimado" step="0.01" min="0" required>
                </div>
                <div class="form-group">
                    <label for="duracion_estimada">Duración Estimada (horas) *</label>
                    <input type="number" id="duracion_estimada" name="duracion_estimada" min="1" required>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalServicio()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/servicios.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>