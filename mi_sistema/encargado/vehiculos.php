<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT * FROM tipos_vehiculo ORDER BY nombre ASC");
$tipos = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tipos de Vehículo - Encargado</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Tipos de Vehículo</h1>
                <button class="btn btn-primary" onclick="openModalVehiculo()">➕ Nuevo Tipo</button>
            </header>

            <div class="cards-grid">
                <?php foreach ($tipos as $tipo): ?>
                    <div class="card">
                        <div class="card-header">
                            <h3><?php echo htmlspecialchars($tipo['nombre']); ?></h3>
                        </div>
                        <div class="card-body">
                            <p><?php echo htmlspecialchars($tipo['descripcion']); ?></p>
                            <small>Creado: <?php echo date('d/m/Y', strtotime($tipo['fecha_creacion'])); ?></small>
                        </div>
                        <div class="card-footer">
                            <button class="btn btn-sm btn-info"
                                onclick='editarVehiculo(<?php echo json_encode($tipo); ?>)'>✏️ Editar</button>
                            <button class="btn btn-sm btn-danger" onclick="eliminarVehiculo(<?php echo $tipo['id']; ?>)">🗑️
                                Eliminar</button>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <div id="modalVehiculo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTituloVehiculo">Nuevo Tipo de Vehículo</h2>
                <span class="close" onclick="closeModalVehiculo()">&times;</span>
            </div>
            <form id="formVehiculo" onsubmit="guardarVehiculo(event)">
                <input type="hidden" id="vehiculo_id" name="id">
                <div class="form-group">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModalVehiculo()">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <script src="../assets/js/vehiculos.js"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>