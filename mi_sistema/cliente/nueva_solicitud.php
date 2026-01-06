<?php
require_once '../config.php';
requireAuth(['cliente']);

$db = Database::getInstance()->getConnection();

// Obtener tipos de vehículo y servicios
$tipos_vehiculo = $db->query("SELECT * FROM tipos_vehiculo ORDER BY nombre ASC")->fetchAll();
$servicios = $db->query("SELECT * FROM servicios ORDER BY nombre ASC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nueva Solicitud - Cliente</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Nueva Solicitud de Servicio</h1>
            </header>

            <div class="form-container">
                <form id="formSolicitud" onsubmit="crearSolicitud(event)">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="servicio_id">Servicio Solicitado *</label>
                            <select id="servicio_id" name="servicio_id" required onchange="mostrarInfoServicio()">
                                <option value="">Seleccione un servicio</option>
                                <?php foreach ($servicios as $serv): ?>
                                    <option value="<?php echo $serv['id']; ?>"
                                        data-precio="<?php echo $serv['precio_estimado']; ?>"
                                        data-duracion="<?php echo $serv['duracion_estimada']; ?>"
                                        data-descripcion="<?php echo htmlspecialchars($serv['descripcion']); ?>">
                                        <?php echo htmlspecialchars($serv['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div id="infoServicio" class="info-box" style="display: none;"></div>
                        </div>

                        <div class="form-group">
                            <label for="tipo_vehiculo_id">Tipo de Vehículo *</label>
                            <select id="tipo_vehiculo_id" name="tipo_vehiculo_id" required>
                                <option value="">Seleccione el tipo</option>
                                <?php foreach ($tipos_vehiculo as $tipo): ?>
                                    <option value="<?php echo $tipo['id']; ?>">
                                        <?php echo htmlspecialchars($tipo['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="marca_vehiculo">Marca del Vehículo *</label>
                            <input type="text" id="marca_vehiculo" name="marca_vehiculo" required
                                placeholder="Ej: Toyota, Honda, Ford">
                        </div>

                        <div class="form-group">
                            <label for="modelo_vehiculo">Modelo *</label>
                            <input type="text" id="modelo_vehiculo" name="modelo_vehiculo" required
                                placeholder="Ej: Corolla, CR-V, F-150">
                        </div>

                        <div class="form-group">
                            <label for="año_vehiculo">Año *</label>
                            <input type="number" id="año_vehiculo" name="año_vehiculo" required min="1900" max="2025"
                                placeholder="Ej: 2020">
                        </div>

                        <div class="form-group">
                            <label for="placa_vehiculo">Placa del Vehículo *</label>
                            <input type="text" id="placa_vehiculo" name="placa_vehiculo" required
                                placeholder="Ej: ABC-123" style="text-transform: uppercase;">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion_problema">Descripción del Problema</label>
                        <textarea id="descripcion_problema" name="descripcion_problema" rows="4"
                            placeholder="Describa detalladamente el problema o servicio que necesita..."></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Enviar Solicitud</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        function mostrarInfoServicio() {
            const select = document.getElementById('servicio_id');
            const option = select.options[select.selectedIndex];
            const infoBox = document.getElementById('infoServicio');

            if (option.value) {
                const precio = option.dataset.precio;
                const duracion = option.dataset.duracion;
                const descripcion = option.dataset.descripcion;

                infoBox.innerHTML = `
                    <strong>Información del Servicio:</strong><br>
                    ${descripcion}<br>
                    <strong>Precio estimado:</strong> Bs. ${parseFloat(precio).toFixed(2)}<br>
                    <strong>Duración estimada:</strong> ${duracion} hora(s)
                `;
                infoBox.style.display = 'block';
            } else {
                infoBox.style.display = 'none';
            }
        }

        async function crearSolicitud(event) {
            event.preventDefault();

            const formData = new FormData(event.target);
            formData.append('action', 'crear');

            try {
                const response = await fetch('../api/solicitudes.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    alert(result.message);
                    window.location.href = 'solicitudes.php';
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error al procesar la solicitud: ' + error.message);
            }
        }
    </script>
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
    <script src="../assets/js/sidebar.js"></script>
</body>

</html>