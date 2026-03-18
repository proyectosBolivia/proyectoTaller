<?php
require_once '../config.php';
requireAuth(['encargado']);

$db = Database::getInstance()->getConnection();

// --- Parámetros de búsqueda (sección Reportes de Trabajo) ---
$busqueda = trim($_GET['busqueda'] ?? '');
$tipo_busqueda = $_GET['tipo_busqueda'] ?? 'nombre'; // 'nombre' | 'id'
$fecha_desde = $_GET['fecha_desde'] ?? '';
$fecha_hasta = $_GET['fecha_hasta'] ?? '';

// Construcción dinámica de la consulta de reportes
$where = ['1=1'];
$params = [];

if ($busqueda !== '') {
    if ($tipo_busqueda === 'id' && is_numeric($busqueda)) {
        $where[] = 'rt.id = ?';
        $params[] = (int)$busqueda;
    }
    else {
        $where[] = 'u.nombre LIKE ?';
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
    SELECT rt.*, u.nombre as trabajador_nombre, a.solicitud_id
    FROM reportes_trabajo rt
    INNER JOIN usuarios u ON rt.trabajador_id = u.id
    INNER JOIN asignaciones a ON rt.asignacion_id = a.id
    WHERE $whereSQL
    ORDER BY rt.fecha_reporte DESC
");
$stmt->execute($params);
$reportes_trabajo = $stmt->fetchAll();

// Estadísticas generales
$stats = [];

// Solicitudes por estado
$stmt = $db->query("
    SELECT estado, COUNT(*) as total 
    FROM solicitudes 
    GROUP BY estado
");
$stats['por_estado'] = $stmt->fetchAll();

// Servicios más solicitados
$stmt = $db->query("
    SELECT s.nombre, COUNT(sol.id) as total
    FROM servicios s
    LEFT JOIN solicitudes sol ON s.id = sol.servicio_id
    GROUP BY s.id
    ORDER BY total DESC
    LIMIT 5
");
$stats['servicios_top'] = $stmt->fetchAll();

// Trabajadores con más asignaciones
$stmt = $db->query("
    SELECT u.nombre, COUNT(a.id) as total
    FROM usuarios u
    INNER JOIN asignaciones a ON u.id = a.trabajador_id
    WHERE u.rol_id = 2
    GROUP BY u.id
    ORDER BY total DESC
    LIMIT 5
");
$stats['trabajadores_top'] = $stmt->fetchAll();

// Ingresos estimados
$stmt = $db->query("
    SELECT SUM(costo_total) as total_ingresos
    FROM reportes_trabajo
");
$ingresos = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reportes - Encargado</title>
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
                <h1>Reportes y Estadísticas</h1>
                <button class="btn btn-primary" onclick="window.print()">🖨️ Imprimir</button>
            </header>

            <div class="stats-grid">
                <div class="stat-card stat-success">
                    <div class="stat-icon">💰</div>
                    <div class="stat-details">
                        <h3><?php echo formatCurrency($ingresos['total_ingresos'] ?? 0); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>
            </div>

            <div class="reports-grid">
                <div class="report-section">
                    <h2>Solicitudes por Estado</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th>Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['por_estado'] as $item): ?>
                                    <tr>
                                        <td><span
                                                class="badge badge-<?php echo $item['estado']; ?>"><?php echo ucfirst(str_replace('_', ' ', $item['estado'])); ?></span>
                                        </td>
                                        <td><?php echo $item['total']; ?></td>
                                    </tr>
                                <?php
endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-section">
                    <h2>Servicios Más Solicitados</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>Solicitudes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['servicios_top'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                        <td><?php echo $item['total']; ?></td>
                                    </tr>
                                <?php
endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="report-section">
                    <h2>Trabajadores Más Activos</h2>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Trabajador</th>
                                    <th>Asignaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($stats['trabajadores_top'] as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nombre']); ?></td>
                                        <td><?php echo $item['total']; ?></td>
                                    </tr>
                                <?php
endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ══ REPORTES DE TRABAJO RECIENTES + BUSCADOR ══════ -->
                <div class="report-section full-width">
                    <h2>Reportes de Trabajo Recientes</h2>

                    <!-- Buscador -->
                    <form method="GET" action="" id="formBusqueda">
                        <div class="search-panel">
                            <h3>🔍 Buscar Reportes</h3>

                            <!-- Toggle nombre / ID -->
                            <div class="search-group" style="flex:0 1 auto;">
                                <label>Buscar por</label>
                                <div class="search-type-toggle">
                                    <input type="radio" name="tipo_busqueda" id="tipo_nombre" value="nombre"
                                        <?php echo($tipo_busqueda === 'nombre') ? 'checked' : ''; ?>>
                                    <label for="tipo_nombre">Nombre</label>

                                    <input type="radio" name="tipo_busqueda" id="tipo_id" value="id"
                                        <?php echo($tipo_busqueda === 'id') ? 'checked' : ''; ?>>
                                    <label for="tipo_id">ID</label>
                                </div>
                            </div>

                            <!-- Campo de texto -->
                            <div class="search-group" style="flex:2 1 200px;">
                                <label id="labelBusqueda">
                                    <?php echo($tipo_busqueda === 'id') ? 'ID del reporte' : 'Nombre del trabajador'; ?>
                                </label>
                                <div class="search-input-wrap">
                                    <span>🔎</span>
                                    <input type="text" name="busqueda" id="campoBusqueda"
                                        value="<?php echo htmlspecialchars($busqueda); ?>"
                                        placeholder="<?php echo($tipo_busqueda === 'id') ? 'Ej: 42' : 'Ej: Carlos López'; ?>">
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

                    <?php
$hayFiltros = $busqueda !== '' || $fecha_desde !== '' || $fecha_hasta !== '';
if ($hayFiltros): ?>
                        <span class="search-results-info">
                            <?php echo count($reportes_trabajo); ?> resultado(s) encontrado(s)
                            <?php if ($busqueda !== ''): ?>
                                · <?php echo $tipo_busqueda === 'id' ? 'ID' : 'Trabajador'; ?>: <strong><?php echo htmlspecialchars($busqueda); ?></strong>
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
                                    <th>Trabajador</th>
                                    <th>Descripción</th>
                                    <th>Horas</th>
                                    <th>Costo</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($reportes_trabajo)): ?>
                                    <tr>
                                        <td colspan="6">
                                            <div class="no-results">
                                                <span>🔍</span>
                                                No se encontraron reportes con los filtros aplicados.
                                            </div>
                                        </td>
                                    </tr>
                                <?php
else: ?>
                                    <?php foreach ($reportes_trabajo as $rt): ?>
                                        <tr>
                                            <td>#<?php echo $rt['id']; ?></td>
                                            <td><?php echo htmlspecialchars($rt['trabajador_nombre']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($rt['descripcion'], 0, 50)); ?>...</td>
                                            <td><?php echo $rt['horas_trabajadas']; ?>h</td>
                                            <td><?php echo formatCurrency($rt['costo_total']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($rt['fecha_reporte'])); ?></td>
                                        </tr>
                                    <?php
    endforeach; ?>
                                <?php
endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <!-- ══════════════════════════════════════════════════ -->
            </div>
        </main>
    </div>

    <script>
        // ── Toggle nombre / ID ────────────────────────────────────
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
                    campo.placeholder = 'Ej: Carlos López';
                    label.textContent = 'Nombre del trabajador';
                    campo.type = 'text';
                    campo.removeAttribute('min');
                }
                campo.value = '';
                campo.focus();
            });
        });

        // Tipo inicial al cargar
        if (document.querySelector('input[name="tipo_busqueda"]:checked')?.value === 'id') {
            campo.type = 'number';
            campo.min  = '1';
        }

        // ── Limpiar ───────────────────────────────────────────────
        function limpiarFiltros() {
            campo.value = '';
            document.getElementById('fecha_desde').value = '';
            document.getElementById('fecha_hasta').value = '';
            document.getElementById('tipo_nombre').checked = true;
            campo.placeholder = 'Ej: Carlos López';
            label.textContent = 'Nombre del trabajador';
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
</body>

</html>