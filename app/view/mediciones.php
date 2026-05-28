<?php
$has_session = isset($_SESSION['usuario']);
$currentAction = $_GET['accion'] ?? 'listar';

$queryParams = $_GET;
unset($queryParams['pagina']);
$baseQuery = http_build_query($queryParams);
$baseUrl = 'index.php?' . $baseQuery;
$pageSeparator = $baseQuery !== '' ? '&' : '';

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
    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">GreenGrid 360</span>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php?accion=listar" class="sidebar-link <?php echo $currentAction === 'listar' ? 'active' : ''; ?>">
                Mediciones
            </a>
            <a href="index.php?accion=registro" class="sidebar-link <?php echo $currentAction === 'registro' ? 'active' : ''; ?>">
                Registro
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
                <button class="btn-create" id="btnCreate" onclick="abrirModalCrear()">+ Crear Medicion</button>
            </section>

            <section class="filters-panel">
                <form method="GET" action="index.php" class="filters-form">
                    <input type="hidden" name="accion" value="listar">

                    <div class="filter-group">
                        <label for="fecha_desde">Fecha desde</label>
                        <input type="date" id="fecha_desde" name="fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="fecha_hasta">Fecha hasta</label>
                        <input type="date" id="fecha_hasta" name="fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta'] ?? ''); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="id_dispositivo">Dispositivo</label>
                        <select id="id_dispositivo" name="id_dispositivo">
                            <option value="">Todos</option>
                            <?php foreach ($dispositivos as $disp): ?>
                                <option value="<?php echo $disp['id_dispositivo']; ?>" <?php echo ($filtros['id_dispositivo'] ?? '') == $disp['id_dispositivo'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($disp['ubicacion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filters-actions">
                        <button type="submit" class="btn-filter">Aplicar filtros</button>
                        <a class="btn-clear" href="index.php?accion=listar">Limpiar</a>
                    </div>
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

    <form id="deleteForm" method="POST" action="" style="display:none;">
        <input type="hidden" name="filtro_fecha_desde" value="<?php echo htmlspecialchars($filtros['fecha_desde'] ?? ''); ?>">
        <input type="hidden" name="filtro_fecha_hasta" value="<?php echo htmlspecialchars($filtros['fecha_hasta'] ?? ''); ?>">
        <input type="hidden" name="filtro_id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo'] ?? ''); ?>">
        <input type="hidden" name="pagina" value="<?php echo $pagina; ?>">
    </form>

<script>
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
</script>
</body>
</html>
