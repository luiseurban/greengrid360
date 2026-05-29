<?php
$has_session = isset($_SESSION['usuario']);
$currentAction = $_GET['accion'] ?? 'listar';

$queryParams = $_GET;
unset($queryParams['pagina']);
$baseQuery = http_build_query($queryParams);
$baseUrl = 'index.php?' . $baseQuery;
$pageSeparator = $baseQuery !== '' ? '&' : '';

$rangeKeys = ['temperatura_min', 'temperatura_max', 'humedad_min', 'humedad_max',
              'humedad_suelo_min', 'humedad_suelo_max', 'calidad_aire_min', 'calidad_aire_max',
              'lluvia_min', 'lluvia_max'];

$filtrosQ = http_build_query(array_filter([
    'fecha_desde' => $filtros['fecha_desde'] ?? null,
    'fecha_hasta' => $filtros['fecha_hasta'] ?? null,
    'id_dispositivo' => $filtros['id_dispositivo'] ?? null,
]));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediciones | GreenGrid 360</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body class="dashboard-page">

    <?php if (!empty($alertasDisparadas)): ?>
    <div class="toast-container" id="toastContainer">
        <?php foreach ($alertasDisparadas as $alerta): ?>
        <div class="toast">
            <span class="toast-icon">&#9888;</span>
            <div class="toast-body">
                <div class="toast-title">Alerta en <?php echo htmlspecialchars($alerta['ubicacion']); ?></div>
                <div class="toast-msg">
                    <?php echo htmlspecialchars($alerta['parametro']); ?>: <?php echo $alerta['valor']; ?>
                    (<?php echo $alerta['condicion']; ?> <?php echo $alerta['umbral']; ?>)
                </div>
            </div>
            <button class="toast-close" onclick="cerrarToast(this)" aria-label="Cerrar">&times;</button>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">GreenGrid 360</span>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php?accion=listar" class="sidebar-link <?php echo $currentAction === 'listar' ? 'active' : ''; ?>">
                Mediciones
            </a>
        <a href="index.php?accion=dispositivos" class="sidebar-link">
            Dispositivos
        </a>
        <a href="index.php?accion=alertas" class="sidebar-link">
            Alertas
        </a>
        <a href="index.php?accion=home" class="sidebar-link">
                Inicio
            </a>
        </nav>

        <div class="sidebar-footer">
            <p class="sidebar-user"><?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? 'Usuario'); ?></p>
            <a href="index.php?accion=logout" class="sidebar-logout">Cerrar sesion</a>
        </div>
    </aside>

    <main class="dashboard-main">
        <div class="container">
            <section class="stats-row">
                <article class="stat-card">
                    <p class="stat-label">Total de registros</p>
                    <p class="stat-value"><?php echo htmlspecialchars((string) $stats['total']); ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Ultima actualizacion</p>
                    <p class="stat-value stat-date"><?php echo htmlspecialchars($stats['ultima']); ?></p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Temperatura promedio</p>
                    <p class="stat-value"><?php echo htmlspecialchars((string) $stats['temp_promedio']); ?> °C</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Temp. maxima</p>
                    <p class="stat-value"><?php echo htmlspecialchars((string) $stats['temp_max']); ?> °C</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Humedad promedio</p>
                    <p class="stat-value"><?php echo htmlspecialchars((string) $stats['hum_promedio']); ?> %</p>
                </article>
                <article class="stat-card">
                    <p class="stat-label">Dispositivos activos</p>
                    <p class="stat-value"><?php echo htmlspecialchars((string) $stats['dispositivos']); ?></p>
                </article>
            </section>

            <section class="toolbar">
                <h2 class="section-title">Registros</h2>
                <div class="toolbar-actions">
                    <form method="GET" action="index.php" class="search-form" id="searchForm">
                        <input type="hidden" name="accion" value="listar">
                        <?php if (!empty($filtros['fecha_desde'])): ?>
                            <input type="hidden" name="fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde']); ?>">
                        <?php endif; ?>
                        <?php if (!empty($filtros['fecha_hasta'])): ?>
                            <input type="hidden" name="fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta']); ?>">
                        <?php endif; ?>
                        <?php if (!empty($filtros['id_dispositivo'])): ?>
                            <input type="hidden" name="id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo']); ?>">
                        <?php endif; ?>
                        <?php if (($filtros['hora_desde'] ?? '') !== ''): ?>
                            <input type="hidden" name="hora_desde" value="<?php echo htmlspecialchars($filtros['hora_desde']); ?>">
                        <?php endif; ?>
                        <?php if (($filtros['hora_hasta'] ?? '') !== ''): ?>
                            <input type="hidden" name="hora_hasta" value="<?php echo htmlspecialchars($filtros['hora_hasta']); ?>">
                        <?php endif; ?>
                        <?php foreach ($rangeKeys as $rk):
                            if (($filtros[$rk] ?? '') !== ''):
                        ?>
                            <input type="hidden" name="<?php echo $rk; ?>" value="<?php echo htmlspecialchars((string) $filtros[$rk]); ?>">
                        <?php endif; endforeach; ?>
                        <input type="text" name="buscar" class="search-input" placeholder="Buscar mediciones..." value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>" oninput="buscarEnTiempoReal(this)">
                        <?php if (!empty($filtros['buscar'])): ?>
                            <?php
                                $clearParams = ['accion' => 'listar'];
                                if (!empty($filtros['fecha_desde'])) $clearParams['fecha_desde'] = $filtros['fecha_desde'];
                                if (!empty($filtros['fecha_hasta'])) $clearParams['fecha_hasta'] = $filtros['fecha_hasta'];
                                if (!empty($filtros['id_dispositivo'])) $clearParams['id_dispositivo'] = $filtros['id_dispositivo'];
                            ?>
                            <a href="index.php?<?php echo http_build_query($clearParams); ?>" class="btn-clear-search">Limpiar</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn-create" id="btnCreate" onclick="abrirModalCrear()">+ Crear Medicion</button>
                </div>
            </section>

            <section class="filters-panel">
                <form method="GET" action="index.php" class="filters-form">
                    <input type="hidden" name="accion" value="listar">
                    <?php if (!empty($filtros['buscar'])): ?>
                        <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
                    <?php endif; ?>
                    <?php if (($filtros['hora_desde'] ?? '') !== ''): ?>
                        <input type="hidden" name="hora_desde" value="<?php echo htmlspecialchars($filtros['hora_desde']); ?>">
                    <?php endif; ?>
                    <?php if (($filtros['hora_hasta'] ?? '') !== ''): ?>
                        <input type="hidden" name="hora_hasta" value="<?php echo htmlspecialchars($filtros['hora_hasta']); ?>">
                    <?php endif; ?>
                    <?php
                    foreach ($rangeKeys as $rk):
                        if (($filtros[$rk] ?? '') !== ''):
                    ?>
                        <input type="hidden" name="<?php echo $rk; ?>" value="<?php echo htmlspecialchars((string) $filtros[$rk]); ?>">
                    <?php endif; endforeach; ?>

                    <div class="filter-group">
                        <label for="fecha_desde">Fecha desde</label>
                        <input type="date" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde'] ?? ''); ?>" onchange="this.form.submit()">
                    </div>

                    <div class="filter-group">
                        <label for="fecha_hasta">Fecha hasta</label>
                        <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta'] ?? ''); ?>" onchange="this.form.submit()">
                    </div>

                    <div class="filter-group">
                        <label for="id_dispositivo">Dispositivo</label>
                        <select id="id_dispositivo" name="id_dispositivo" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <?php foreach ($dispositivos as $disp): ?>
                                <option value="<?php echo $disp['id_dispositivo']; ?>" <?php echo ($filtros['id_dispositivo'] ?? '') == $disp['id_dispositivo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($disp['ubicacion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php
                    $horaDesde = $filtros['hora_desde'] ?? '';
                    $horaHasta = $filtros['hora_hasta'] ?? '';
                    if ($horaDesde !== '' || $horaHasta !== ''):
                        $urlParams = $_GET;
                        unset($urlParams['hora_desde'], $urlParams['hora_hasta']);
                        $urlParams['accion'] = 'listar';
                        $removeUrl = 'index.php?' . http_build_query($urlParams);
                    ?>
                        <div class="filter-group">
                            <label>Hora</label>
                            <div class="filter-chip">
                                <span class="chip-text"><?php echo $horaDesde !== '' ? sprintf('%02d:00', $horaDesde) : '···'; ?> &ndash; <?php echo $horaHasta !== '' ? sprintf('%02d:00', $horaHasta) : '···'; ?></span>
                                <a href="<?php echo $removeUrl; ?>" class="chip-remove" title="Quitar filtro">&times;</a>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php
                    $rangosChips = [
                        'temperatura' => 'Temperatura',
                        'humedad' => 'Humedad',
                        'humedad_suelo' => 'Hum. Suelo',
                        'calidad_aire' => 'Calidad Aire',
                        'lluvia' => 'Lluvia',
                    ];
                    foreach ($rangosChips as $key => $label):
                        $vmin = $filtros[$key . '_min'] ?? '';
                        $vmax = $filtros[$key . '_max'] ?? '';
                        if ($vmin === '' && $vmax === '') continue;

                        $urlParams = $_GET;
                        unset($urlParams[$key . '_min'], $urlParams[$key . '_max']);
                        $urlParams['accion'] = 'listar';
                        $removeUrl = 'index.php?' . http_build_query($urlParams);
                    ?>
                        <div class="filter-group">
                            <label><?php echo $label; ?></label>
                            <div class="filter-chip">
                                <span class="chip-text"><?php echo ($vmin !== '' ? $vmin : '···'); ?> &ndash; <?php echo ($vmax !== '' ? $vmax : '···'); ?></span>
                                <a href="<?php echo $removeUrl; ?>" class="chip-remove" title="Quitar filtro">&times;</a>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="filter-group filter-group-action">
                        <label>&nbsp;</label>
                        <button type="button" class="btn-more-filters" onclick="abrirFiltrosAvanzados()">Mas filtros</button>
                    </div>

                    <?php
                    $hasFilters = !empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta']) || !empty($filtros['id_dispositivo'])
                        || !empty($filtros['buscar']) || ($filtros['hora_desde'] ?? '') !== '' || ($filtros['hora_hasta'] ?? '') !== '';
                    foreach ($rangosChips as $key => $label) {
                        if (($filtros[$key . '_min'] ?? '') !== '' || ($filtros[$key . '_max'] ?? '') !== '') {
                            $hasFilters = true;
                            break;
                        }
                    }
                    if ($hasFilters): ?>
                    <div class="filters-actions">
                        <a class="btn-clear" href="index.php?accion=listar">Limpiar</a>
                    </div>
                    <?php endif; ?>
                </form>
            </section>
            
            <?php if (!empty($mediciones)): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>Fecha y Hora</th>
                                <th>Dispositivo</th>
                                <th>Temperatura</th>
                                <th>Humedad</th>
                                <th>Hum. Suelo</th>
                                <th>Calidad del Aire</th>
                                <th>Lluvia</th>
                                <th class="th-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mediciones as $fila): ?>
                                <tr class="row-medicion"
                                    data-id="<?php echo htmlspecialchars((string) $fila['id_medicion']); ?>"
                                    data-temperatura="<?php echo htmlspecialchars((string) $fila['temperatura']); ?>"
                                    data-humedad="<?php echo htmlspecialchars((string) $fila['humedad']); ?>"
                                    data-humedad-suelo="<?php echo htmlspecialchars((string) $fila['humedad_suelo']); ?>"
                                    data-calidad-aire="<?php echo htmlspecialchars((string) $fila['calidad_aire']); ?>"
                                    data-lluvia="<?php echo htmlspecialchars((string) $fila['lluvia']); ?>"
                                    data-fecha-hora="<?php echo htmlspecialchars((string) $fila['fecha_hora']); ?>"
                                    data-id-dispositivo="<?php echo htmlspecialchars((string) $fila['id_dispositivo']); ?>">
                                    <td><?php echo htmlspecialchars($fila['fecha_hora']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['temperatura']); ?> °C</td>
                                    <td><?php echo htmlspecialchars((string) $fila['humedad']); ?> %</td>
                                    <td><?php echo htmlspecialchars((string) $fila['humedad_suelo']); ?> %</td>
                                    <td><?php echo htmlspecialchars((string) $fila['calidad_aire']); ?> PPM</td>
                                    <td><?php echo htmlspecialchars((string) $fila['lluvia']); ?> %</td>
                                    <td class="td-acciones">
                                        <button class="btn-edit" onclick="abrirModalEditar(this)" title="Editar">&#9998;</button>
                                        <button class="btn-delete" onclick="confirmarEliminar(this)" title="Eliminar">&#10005;</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($totalPaginas > 1): ?>
                <nav class="pagination">
                    <?php if ($pagina > 1): ?>
                        <a href="<?php echo $baseUrl . $pageSeparator . 'pagina=' . ($pagina - 1); ?>" class="page-link">&laquo; Anterior</a>
                    <?php endif; ?>

                    <?php
                    $inicio = max(1, $pagina - 2);
                    $fin = min($totalPaginas, $pagina + 2);

                    if ($inicio > 1): ?>
                        <a href="<?php echo $baseUrl . $pageSeparator . 'pagina=1'; ?>" class="page-link">1</a>
                        <?php if ($inicio > 2): ?>
                            <span class="page-ellipsis">&hellip;</span>
                        <?php endif; ?>
                    <?php endif; ?>

                    <?php for ($i = $inicio; $i <= $fin; $i++): ?>
                        <a href="<?php echo $baseUrl . $pageSeparator . 'pagina=' . $i; ?>"
                           class="page-link <?php echo $i === $pagina ? 'page-active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($fin < $totalPaginas): ?>
                        <?php if ($fin < $totalPaginas - 1): ?>
                            <span class="page-ellipsis">&hellip;</span>
                        <?php endif; ?>
                        <a href="<?php echo $baseUrl . $pageSeparator . 'pagina=' . $totalPaginas; ?>" class="page-link"><?php echo $totalPaginas; ?></a>
                    <?php endif; ?>

                    <?php if ($pagina < $totalPaginas): ?>
                        <a href="<?php echo $baseUrl . $pageSeparator . 'pagina=' . ($pagina + 1); ?>" class="page-link">Siguiente &raquo;</a>
                    <?php endif; ?>

                    <span class="page-info"><?php echo $total; ?> registros</span>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="message">
                    <p>No hay datos disponibles</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Crear Medicion</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <form method="POST" action="" id="modalForm" class="modal-form">
                <input type="hidden" name="filtro_fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde'] ?? ''); ?>">
                <input type="hidden" name="filtro_fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta'] ?? ''); ?>">
                <input type="hidden" name="filtro_id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo'] ?? ''); ?>">
                <input type="hidden" name="filtro_buscar" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
                <input type="hidden" name="filtro_temperatura_min" value="<?php echo htmlspecialchars((string) ($filtros['temperatura_min'] ?? '')); ?>">
                <input type="hidden" name="filtro_temperatura_max" value="<?php echo htmlspecialchars((string) ($filtros['temperatura_max'] ?? '')); ?>">
                <input type="hidden" name="filtro_humedad_min" value="<?php echo htmlspecialchars((string) ($filtros['humedad_min'] ?? '')); ?>">
                <input type="hidden" name="filtro_humedad_max" value="<?php echo htmlspecialchars((string) ($filtros['humedad_max'] ?? '')); ?>">
                <input type="hidden" name="filtro_humedad_suelo_min" value="<?php echo htmlspecialchars((string) ($filtros['humedad_suelo_min'] ?? '')); ?>">
                <input type="hidden" name="filtro_humedad_suelo_max" value="<?php echo htmlspecialchars((string) ($filtros['humedad_suelo_max'] ?? '')); ?>">
                <input type="hidden" name="filtro_calidad_aire_min" value="<?php echo htmlspecialchars((string) ($filtros['calidad_aire_min'] ?? '')); ?>">
                <input type="hidden" name="filtro_calidad_aire_max" value="<?php echo htmlspecialchars((string) ($filtros['calidad_aire_max'] ?? '')); ?>">
                <input type="hidden" name="filtro_lluvia_min" value="<?php echo htmlspecialchars((string) ($filtros['lluvia_min'] ?? '')); ?>">
                <input type="hidden" name="filtro_lluvia_max" value="<?php echo htmlspecialchars((string) ($filtros['lluvia_max'] ?? '')); ?>">
                <input type="hidden" name="filtro_hora_desde" value="<?php echo htmlspecialchars((string) ($filtros['hora_desde'] ?? '')); ?>">
                <input type="hidden" name="filtro_hora_hasta" value="<?php echo htmlspecialchars((string) ($filtros['hora_hasta'] ?? '')); ?>">
                <input type="hidden" name="pagina" value="<?php echo $pagina; ?>">

                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="m_temperatura">Temperatura (°C)</label>
                            <input type="number" step="0.1" id="m_temperatura" name="temperatura" required placeholder="Ej: 25.5">
                        </div>
                        <div class="form-group">
                            <label for="m_humedad">Humedad (%)</label>
                            <input type="number" step="0.1" id="m_humedad" name="humedad" required placeholder="Ej: 60.0">
                        </div>
                        <div class="form-group">
                            <label for="m_humedad_suelo">Hum. Suelo (%)</label>
                            <input type="number" step="0.1" id="m_humedad_suelo" name="humedad_suelo" placeholder="Ej: 45.0">
                        </div>
                        <div class="form-group">
                            <label for="m_calidad_aire">Calidad del Aire (PPM)</label>
                            <input type="number" step="0.1" id="m_calidad_aire" name="calidad_aire" required placeholder="Ej: 400.0">
                        </div>
                        <div class="form-group">
                            <label for="m_lluvia">Lluvia (%)</label>
                            <input type="number" step="0.1" id="m_lluvia" name="lluvia" placeholder="Ej: 0.0">
                        </div>
                        <div class="form-group">
                            <label for="m_fecha_hora">Fecha y Hora</label>
                            <input type="datetime-local" step="1" id="m_fecha_hora" name="fecha_hora" required>
                        </div>
                        <div class="form-group">
                            <label for="m_dispositivo">Dispositivo</label>
                            <select id="m_dispositivo" name="id_dispositivo" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($dispositivos as $disp): ?>
                                    <option value="<?php echo $disp['id_dispositivo']; ?>">
                                        <?php echo htmlspecialchars($disp['ubicacion']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-save" id="btnSave">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="filtersModalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Filtros Avanzados</h3>
                <button class="modal-close" onclick="cerrarFiltrosAvanzados()">&times;</button>
            </div>
            <div class="modal-body">
                <form id="filtersForm" method="GET" action="index.php">
                    <input type="hidden" name="accion" value="listar">
                    <input type="hidden" name="fecha_desde" id="ff_fecha_desde">
                    <input type="hidden" name="fecha_hasta" id="ff_fecha_hasta">
                    <input type="hidden" name="id_dispositivo" id="ff_id_dispositivo">
                    <input type="hidden" name="buscar" id="ff_buscar">

                        <div class="form-group">
                            <label>Hora</label>
                            <div class="range-group">
                                <select name="hora_desde">
                                    <option value="">Desde</option>
                                    <?php for ($h = 0; $h <= 23; $h++): ?>
                                        <option value="<?php echo $h; ?>" <?php echo ($filtros['hora_desde'] ?? '') === (string) $h ? 'selected' : ''; ?>><?php printf('%02d:00', $h); ?></option>
                                    <?php endfor; ?>
                                </select>
                                <span class="range-sep">&ndash;</span>
                                <select name="hora_hasta">
                                    <option value="">Hasta</option>
                                    <?php for ($h = 0; $h <= 23; $h++): ?>
                                        <option value="<?php echo $h; ?>" <?php echo ($filtros['hora_hasta'] ?? '') === (string) $h ? 'selected' : ''; ?>><?php printf('%02d:00', $h); ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                    <?php
                    $rangosFields = [
                        'temperatura' => 'Temperatura (°C)',
                        'humedad' => 'Humedad (%)',
                        'humedad_suelo' => 'Hum. Suelo (%)',
                        'calidad_aire' => 'Calidad del Aire (PPM)',
                        'lluvia' => 'Lluvia (%)',
                    ];
                    $hasAdvancedFilters = ($filtros['hora_desde'] ?? '') !== '' || ($filtros['hora_hasta'] ?? '') !== '';
                    foreach ($rangosFields as $key => $label):
                        $minVal = $filtros[$key . '_min'] ?? '';
                        $maxVal = $filtros[$key . '_max'] ?? '';
                        if ($minVal !== '' || $maxVal !== '') $hasAdvancedFilters = true;
                    ?>
                        <div class="form-group">
                            <label><?php echo $label; ?></label>
                            <div class="range-group">
                                <input type="number" step="0.1" name="<?php echo $key; ?>_min" placeholder="Min" value="<?php echo htmlspecialchars((string) $minVal); ?>">
                                <span class="range-sep">&ndash;</span>
                                <input type="number" step="0.1" name="<?php echo $key; ?>_max" placeholder="Max" value="<?php echo htmlspecialchars((string) $maxVal); ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </form>
            </div>
            <div class="modal-footer">
                <?php if ($hasAdvancedFilters): ?>
                    <button type="button" class="btn-cancel" onclick="limpiarFiltrosAvanzados()">Limpiar filtros</button>
                <?php endif; ?>
                <button type="button" class="btn-cancel" onclick="cerrarFiltrosAvanzados()">Cancelar</button>
                <button type="button" class="btn-save" onclick="aplicarFiltrosAvanzados()">Aplicar</button>
            </div>
        </div>
    </div>

    <form id="deleteForm" method="POST" action="" style="display:none;">
        <input type="hidden" name="filtro_fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde'] ?? ''); ?>">
        <input type="hidden" name="filtro_fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta'] ?? ''); ?>">
        <input type="hidden" name="filtro_id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo'] ?? ''); ?>">
        <input type="hidden" name="filtro_buscar" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
        <input type="hidden" name="filtro_temperatura_min" value="<?php echo htmlspecialchars((string) ($filtros['temperatura_min'] ?? '')); ?>">
        <input type="hidden" name="filtro_temperatura_max" value="<?php echo htmlspecialchars((string) ($filtros['temperatura_max'] ?? '')); ?>">
        <input type="hidden" name="filtro_humedad_min" value="<?php echo htmlspecialchars((string) ($filtros['humedad_min'] ?? '')); ?>">
        <input type="hidden" name="filtro_humedad_max" value="<?php echo htmlspecialchars((string) ($filtros['humedad_max'] ?? '')); ?>">
        <input type="hidden" name="filtro_humedad_suelo_min" value="<?php echo htmlspecialchars((string) ($filtros['humedad_suelo_min'] ?? '')); ?>">
        <input type="hidden" name="filtro_humedad_suelo_max" value="<?php echo htmlspecialchars((string) ($filtros['humedad_suelo_max'] ?? '')); ?>">
        <input type="hidden" name="filtro_calidad_aire_min" value="<?php echo htmlspecialchars((string) ($filtros['calidad_aire_min'] ?? '')); ?>">
        <input type="hidden" name="filtro_calidad_aire_max" value="<?php echo htmlspecialchars((string) ($filtros['calidad_aire_max'] ?? '')); ?>">
        <input type="hidden" name="filtro_lluvia_min" value="<?php echo htmlspecialchars((string) ($filtros['lluvia_min'] ?? '')); ?>">
        <input type="hidden" name="filtro_lluvia_max" value="<?php echo htmlspecialchars((string) ($filtros['lluvia_max'] ?? '')); ?>">
        <input type="hidden" name="filtro_hora_desde" value="<?php echo htmlspecialchars((string) ($filtros['hora_desde'] ?? '')); ?>">
        <input type="hidden" name="filtro_hora_hasta" value="<?php echo htmlspecialchars((string) ($filtros['hora_hasta'] ?? '')); ?>">
        <input type="hidden" name="pagina" value="<?php echo $pagina; ?>">
    </form>

<script>
var searchTimer = null;

document.addEventListener('DOMContentLoaded', function() {
    var toasts = document.querySelectorAll('.toast');
    toasts.forEach(function(toast) {
        setTimeout(function() {
            eliminarToast(toast);
        }, 5000);
    });

    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'index.php?accion=enviar-alertas', true);
    xhr.send();
});

function cerrarToast(btn) {
    eliminarToast(btn.closest('.toast'));
}

function eliminarToast(toast) {
    if (!toast || toast.classList.contains('toast-hiding')) return;
    toast.classList.add('toast-hiding');
    setTimeout(function() {
        toast.remove();
        var container = document.getElementById('toastContainer');
        if (container && container.children.length === 0) {
            container.remove();
        }
    }, 300);
}

function buscarEnTiempoReal(input) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        input.form.submit();
    }, 350);
}

function abrirModalCrear() {
    document.getElementById('modalTitle').textContent = 'Crear Medicion';
    document.getElementById('modalForm').action = 'index.php?accion=crear';
    document.getElementById('btnSave').textContent = 'Guardar';
    limpiarFormulario();
    document.getElementById('modalOverlay').classList.add('active');
}

function abrirModalEditar(btn) {
    var row = btn.closest('.row-medicion');
    document.getElementById('modalTitle').textContent = 'Editar Medicion';
    document.getElementById('modalForm').action = 'index.php?accion=editar&id=' + row.dataset.id;
    document.getElementById('btnSave').textContent = 'Actualizar';

    document.getElementById('m_temperatura').value = row.dataset.temperatura;
    document.getElementById('m_humedad').value = row.dataset.humedad;
    document.getElementById('m_humedad_suelo').value = row.dataset.humedadSuelo;
    document.getElementById('m_calidad_aire').value = row.dataset.calidadAire;
    document.getElementById('m_lluvia').value = row.dataset.lluvia;
    document.getElementById('m_fecha_hora').value = row.dataset.fechaHora.replace(' ', 'T');
    document.getElementById('m_dispositivo').value = row.dataset.idDispositivo;

    document.getElementById('modalOverlay').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('active');
}

function limpiarFormulario() {
    document.getElementById('m_temperatura').value = '';
    document.getElementById('m_humedad').value = '';
    document.getElementById('m_humedad_suelo').value = '';
    document.getElementById('m_calidad_aire').value = '';
    document.getElementById('m_lluvia').value = '';
    document.getElementById('m_fecha_hora').value = '';
    document.getElementById('m_dispositivo').value = '';
}

function confirmarEliminar(btn) {
    if (!confirm('¿Estás seguro de eliminar esta medición?')) {
        return;
    }
    var row = btn.closest('.row-medicion');
    var form = document.getElementById('deleteForm');
    form.action = 'index.php?accion=eliminar&id=' + row.dataset.id;
    form.submit();
}

document.getElementById('modalOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModal();
    }
});

document.getElementById('filtersModalOverlay').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarFiltrosAvanzados();
    }
});

function abrirFiltrosAvanzados() {
    document.getElementById('ff_fecha_desde').value = document.getElementById('fecha_desde').value;
    document.getElementById('ff_fecha_hasta').value = document.getElementById('fecha_hasta').value;
    document.getElementById('ff_id_dispositivo').value = document.getElementById('id_dispositivo').value;
    document.getElementById('ff_buscar').value = document.querySelector('.search-input').value;
    document.getElementById('filtersModalOverlay').classList.add('active');
}

function cerrarFiltrosAvanzados() {
    document.getElementById('filtersModalOverlay').classList.remove('active');
}

function aplicarFiltrosAvanzados() {
    document.getElementById('filtersForm').submit();
}

function limpiarFiltrosAvanzados() {
    var inputs = document.querySelectorAll('#filtersForm input[type="number"]');
    for (var i = 0; i < inputs.length; i++) {
        inputs[i].value = '';
    }
    var selects = document.querySelectorAll('#filtersForm select[name^="hora_"]');
    for (var i = 0; i < selects.length; i++) {
        selects[i].value = '';
    }
    document.getElementById('filtersForm').submit();
}
</script>
</body>
</html>
