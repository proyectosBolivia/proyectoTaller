<?php
require_once '../config.php';
requireAuth(['cliente']);

$db = Database::getInstance()->getConnection();

// --- Parámetros de búsqueda ---
$busqueda = trim($_GET['busqueda'] ?? '');
$tipo_busqueda = $_GET['tipo_busqueda'] ?? 'placa'; // 'placa' | 'id'
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construcción dinámica de la consulta
$where = ['s.cliente_id = ?'];
$params = [$_SESSION['usuario_id']];

if ($busqueda !== '') {
    if ($tipo_busqueda === 'id' && is_numeric($busqueda)) {
        $where[] = 'rt.id = ?';
        $params[] = (int)$busqueda;
    }
    else {
        $where[] = 's.placa_vehiculo LIKE ?';
        $params[] = '%' . $busqueda . '%';
    }
}

if ($fecha_desde !== '') {
    $where[] = 'DATE(rt.fecha_reporte) >= ?';
    $params[] = $fecha_desde;
}
if ($fecha_hasta !== '') {
    $where[] = 'DATE(rt.fecha_reporte) <= ?';
    $params[] = $fecha_hasta;
}

$whereSQL = implode(' AND ', $where);

$stmt = $db->prepare("
    SELECT rt.*, u.nombre as trabajador_nombre, s.placa_vehiculo, s.descripcion as solicitud_descripcion
    FROM reportes_trabajo rt
    INNER JOIN asignaciones a  ON rt.asignacion_id = a.id
    INNER JOIN solicitudes  s  ON a.solicitud_id = s.id
    INNER JOIN usuarios     u  ON rt.trabajador_id = u.id
    WHERE $whereSQL
    ORDER BY rt.fecha_reporte DESC
");
$stmt->execute($params);
$reportes = $stmt->fetchAll();

// Estadísticas del cliente
$stmt = $db->prepare("
    SELECT
        COUNT(DISTINCT rt.id)   as total_reportes,
        SUM(rt.horas_trabajadas) as total_horas,
        SUM(rt.costo_total)      as total_gastado
    FROM reportes_trabajo rt
    INNER JOIN asignaciones a ON rt.asignacion_id = a.id
    INNER JOIN solicitudes  s ON a.solicitud_id = s.id
    WHERE s.cliente_id = ?
");
$stmt->execute([$_SESSION['usuario_id']]);
$stats = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reportes - Cliente</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <style>
        /* ── Buscador ─────────────────────────────────────────── */
        .search-panel {
            background: var(--bg-secondary, #f8f9fa);
            border: 1px solid var(--border-color, #e0e0e0);
            border-radius: 12px;
            padding: 1.25rem 1.5rem;
            margin-bottom: 1.25rem;
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            align-items: flex-end;
        }

        .search-panel h3 {
            width: 100%;
            margin: 0 0 .25rem;
            font-size: .95rem;
            color: var(--text-secondary, #555);
            font-weight: 600;
            letter-spacing: .03em;
        }

        .search-group {
            display: flex;
            flex-direction: column;
            gap: .35rem;
            flex: 1 1 180px;
        }

        .search-group label {
            font-size: .78rem;
            font-weight: 600;
            color: var(--text-secondary, #666);
            text-transform: uppercase;
            letter-spacing: .05em;
        }

        .search-type-toggle {
            display: flex;
            border: 1px solid var(--border-color, #ccc);
            border-radius: 8px;
            overflow: hidden;
        }

        .search-type-toggle input[type="radio"] { display: none; }

        .search-type-toggle label {
            flex: 1;
            text-align: center;
            padding: .45rem .75rem;
            font-size: .8rem;
            font-weight: 600;
            cursor: pointer;
            text-transform: none;
            letter-spacing: 0;
            color: var(--text-secondary, #666);
            background: transparent;
            transition: background .2s, color .2s;
            border: none;
        }

        .search-type-toggle input[type="radio"]:checked + label {
            background: var(--primary-color, #3b82f6);
            color: #fff;
        }

        .search-input-wrap {
            display: flex;
            align-items: center;
            border: 1px solid var(--border-color, #ccc);
            border-radius: 8px;
            overflow: hidden;
            background: #fff;
        }

        .search-input-wrap span {
            padding: 0 .6rem;
            color: var(--text-secondary, #888);
            font-size: 1rem;
        }

        .search-input-wrap input {
            border: none;
            outline: none;
            padding: .5rem .5rem .5rem 0;
            font-size: .9rem;
            width: 100%;
            background: transparent;
        }

        .date-range-group {
            display: flex;
            gap: .5rem;
            align-items: center;
            flex: 2 1 280px;
        }

        .date-range-group input[type="date"] {
            border: 1px solid var(--border-color, #ccc);
            border-radius: 8px;
            padding: .5rem .75rem;
            font-size: .88rem;
            background: #fff;
            color: var(--text-primary, #333);
            flex: 1;
        }

        .date-range-group span {
            font-size: .8rem;
            color: var(--text-secondary, #888);
            white-space: nowrap;
        }

        .btn-search, .btn-clear {
            padding: .55rem 1.1rem;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-size: .88rem;
            font-weight: 600;
            transition: opacity .2s;
            white-space: nowrap;
        }

        .btn-search { background: var(--primary-color, #3b82f6); color: #fff; }
        .btn-clear  { background: var(--bg-tertiary, #e5e7eb); color: var(--text-primary, #333); }
        .btn-search:hover, .btn-clear:hover { opacity: .85; }

        .search-results-info {
            font-size: .82rem;
            color: var(--text-secondary, #666);
            margin-bottom: .5rem;
            padding: .35rem .5rem;
            background: var(--bg-secondary, #f0f4ff);
            border-radius: 6px;
            display: inline-block;
        }

        .no-results {
            text-align: center;
            padding: 2.5rem;
            color: var(--text-secondary, #888);
        }

        .no-results span { font-size: 2rem; display: block; margin-bottom: .5rem; }

        @media (max-width: 700px) {
            .search-panel { flex-direction: column; }
            .date-range-group { flex-direction: column; align-items: stretch; }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <?php include 'includes/sidebar.php'; ?>

        <main class="main-content">
            <header class="content-header">
                <h1>Mis Reportes de Servicio</h1>
                <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
            </header>

            <!-- Estadísticas -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📝</div>
                    <div class="stat-details">
                        <h3><?php echo $stats['total_reportes'] ?? 0; ?></h3>
                        <p>Reportes Recibidos</p>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-icon">⏱️</div>
                    <div class="stat-details">
                        <h3><?php echo number_format($stats['total_horas'] ?? 0, 1); ?>h</h3>
                        <p>Horas de Servicio</p>
                    </div>
                </div>
                <div class="stat-card stat-warning">
                    <div class="stat-icon">💳</div>
                    <div class="stat-details">
                        <h3><?php echo formatCurrency($stats['total_gastado'] ?? 0); ?></h3>
                        <p>Total Gastado</p>
                    </div>
                </div>
            </div>

            <div class="dashboard-section">

                <!-- ══ BUSCADOR ══════════════════════════════════════════ -->
                <form method="GET" action="" id="formBusqueda">
                    <div class="search-panel">
                        <h3>🔍 Buscar Reportes</h3>

                        <!-- Toggle placa / ID -->
                        <div class="search-group" style="flex:0 1 auto;">
                            <label>Buscar por</label>
                            <div class="search-type-toggle">
                                <input type="radio" name="tipo_busqueda" id="tipo_placa" value="placa"
                                    <?php echo($tipo_busqueda === 'placa') ? 'checked' : ''; ?>>
                                <label for="tipo_placa">Placa</label>

                                <input type="radio" name="tipo_busqueda" id="tipo_id" value="id"
                                    <?php echo($tipo_busqueda === 'id') ? 'checked' : ''; ?>>
                                <label for="tipo_id">ID</label>
                            </div>
                        </div>

                        <!-- Campo de texto -->
                        <div class="search-group" style="flex:2 1 200px;">
                            <label id="labelBusqueda">
                                <?php echo($tipo_busqueda === 'id') ? 'ID del reporte' : 'Placa del vehículo'; ?>
                            </label>
                            <div class="search-input-wrap">
                                <span>🔎</span>
                                <input type="text" name="busqueda" id="campoBusqueda"
                                    value="<?php echo htmlspecialchars($busqueda); ?>"
                                    placeholder="<?php echo($tipo_busqueda === 'id') ? 'Ej: 42' : 'Ej: ABC-1234'; ?>">
                            </div>
                        </div>

                        <!-- Rango de fechas -->
                        <div class="search-group" style="flex:3 1 300px;">
                            <label>Rango de fechas</label>
                            <div class="date-range-group">
                                <input type="date" name="fecha_desde" id="fecha_desde"
                                    value="<?php echo htmlspecialchars($fecha_desde); ?>" title="Desde">
                                <span>→</span>
                                <input type="date" name="fecha_hasta" id="fecha_hasta"
                                    value="<?php echo htmlspecialchars($fecha_hasta); ?>" title="Hasta">
                            </div>
                        </div>

                        <!-- Botones -->
                        <div style="display:flex;gap:.5rem;align-items:flex-end;flex-shrink:0;">
                            <button type="submit" class="btn-search">Buscar</button>
                            <button type="button" class="btn-clear" onclick="limpiarFiltros()">Limpiar</button>
                        </div>
                    </div>
                </form>
                <!-- ════════════════════════════════════════════════════ -->

                <?php
$hayFiltros = $busqueda !== '' || $fecha_desde !== '' || $fecha_hasta !== '';
if ($hayFiltros): ?>
                    <span class="search-results-info">
                        <?php echo count($reportes); ?> resultado(s) encontrado(s)
                        <?php if ($busqueda !== ''): ?>
                            · <?php echo $tipo_busqueda === 'id' ? 'ID' : 'Placa'; ?>: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
                        <?php
    endif; ?>
                        <?php if ($fecha_desde !== '' || $fecha_hasta !== ''): ?>
                            · Fechas: <strong><?php echo $fecha_desde ?: '∞'; ?></strong> → <strong><?php echo $fecha_hasta ?: '∞'; ?></strong>
                        <?php
    endif; ?>
                    </span>
                <?php
endif; ?>

                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Vehículo</th>
                                <th>Trabajador</th>
                                <th>Descripción</th>
                                <th>Horas</th>
                                <th>Costo</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($reportes)): ?>
                                <tr>
                                    <td colspan="8">
                                        <div class="no-results">
                                            <span>🔍</span>
                                            No se encontraron reportes con los filtros aplicados.
                                        </div>
                                    </td>
                                </tr>
                            <?php
else: ?>
                                <?php foreach ($reportes as $rep): ?>
                                    <tr>
                                        <td>#<?php echo $rep['id']; ?></td>
                                        <td><?php echo htmlspecialchars($rep['placa_vehiculo']); ?></td>
                                        <td><?php echo htmlspecialchars($rep['trabajador_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars(substr($rep['descripcion'], 0, 50)); ?>...</td>
                                        <td><?php echo $rep['horas_trabajadas']; ?>h</td>
                                        <td><?php echo formatCurrency($rep['costo_total']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($rep['fecha_reporte'])); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info"
                                                onclick='verReporte(<?php echo json_encode($rep); ?>)'>👁️ Ver</button>
                                        </td>
                                    </tr>
                                <?php
    endforeach; ?>
                            <?php
endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal detalle -->
    <div id="modalReporte" class="modal">
        <div class="modal-content modal-large">
            <div class="modal-header">
                <h2>Detalle del Reporte</h2>
                <span class="close"
                    onclick="document.getElementById('modalReporte').style.display='none'">&times;</span>
            </div>
            <div id="contenidoReporte" class="modal-body"></div>
        </div>
    </div>

    <script>
        // ── Modal ─────────────────────────────────────────────────
        function verReporte(rep) {
            const html = `
                <div class="details-grid">
                    <div class="detail-item"><strong>ID Reporte:</strong> #${rep.id}</div>
                    <div class="detail-item"><strong>Vehículo:</strong> ${rep.placa_vehiculo}</div>
                    <div class="detail-item"><strong>Trabajador:</strong> ${rep.trabajador_nombre}</div>
                    <div class="detail-item"><strong>Horas Trabajadas:</strong> ${rep.horas_trabajadas}h</div>
                    <div class="detail-item"><strong>Costo Total:</strong> Bs. ${parseFloat(rep.costo_total).toFixed(2)}</div>
                    <div class="detail-item"><strong>Fecha Reporte:</strong> ${new Date(rep.fecha_reporte).toLocaleString()}</div>
                    <div class="detail-item full-width">
                        <strong>Descripción del Trabajo:</strong><br>${rep.descripcion}
                    </div>
                    <div class="detail-item full-width">
                        <strong>Piezas Utilizadas:</strong><br>${rep.piezas_utilizadas || 'Ninguna'}
                    </div>
                </div>`;
            document.getElementById('contenidoReporte').innerHTML = html;
            document.getElementById('modalReporte').style.display = 'block';
        }

        window.onclick = function (e) {
            const m = document.getElementById('modalReporte');
            if (e.target === m) m.style.display = 'none';
        };

        // ── Toggle placa / ID ─────────────────────────────────────
        const radios = document.querySelectorAll('input[name="tipo_busqueda"]');
        const campo  = document.getElementById('campoBusqueda');
        const label  = document.getElementById('labelBusqueda');

        radios.forEach(radio => {
            radio.addEventListener('change', () => {
                if (radio.value === 'id') {
                    campo.placeholder = 'Ej: 42';
                    label.textContent = 'ID del reporte';
                    campo.type = 'number';
                    campo.min  = '1';
                } else {
                    campo.placeholder = 'Ej: ABC-1234';
                    label.textContent = 'Placa del vehículo';
                    campo.type = 'text';
                    campo.removeAttribute('min');
                }
                campo.value = '';
                campo.focus();
            });
        });

        if (document.querySelector('input[name="tipo_busqueda"]:checked')?.value === 'id') {
            campo.type = 'number';
            campo.min  = '1';
        }

        // ── Limpiar ───────────────────────────────────────────────
        function limpiarFiltros() {
            campo.value = '';
            document.getElementById('fecha_desde').value = '';
            document.getElementById('fecha_hasta').value = '';
            document.getElementById('tipo_placa').checked = true;
            campo.placeholder = 'Ej: ABC-1234';
            label.textContent = 'Placa del vehículo';
            campo.type = 'text';
            campo.removeAttribute('min');
            document.getElementById('formBusqueda').submit();
        }

        // ── Validar fechas ────────────────────────────────────────
        document.getElementById('formBusqueda').addEventListener('submit', function (e) {
            const desde = document.getElementById('fecha_desde').value;
            const hasta = document.getElementById('fecha_hasta').value;
            if (desde && hasta && desde > hasta) {
                e.preventDefault();
                alert('La fecha de inicio no puede ser mayor que la fecha de fin.');
            }
        });
    </script>
    <script src="../assets/js/sidebar.js"></script>
    <script src="https://kit.fontawesome.com/2ff5cf379e.js" crossorigin="anonymous"></script>
</body>

</html>
