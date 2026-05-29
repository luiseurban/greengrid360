<?php
$currentAction = $_GET['accion'] ?? 'alertas';

$queryParams = $_GET;
unset($queryParams['pagina']);
$baseQuery = http_build_query($queryParams);
$baseUrl = 'index.php?' . $baseQuery;
$pageSeparator = $baseQuery !== '' ? '&' : '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alertas | GreenGrid 360</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body class="dashboard-page">
    <aside class="dashboard-sidebar">
        <div class="sidebar-brand">
            <span class="brand-mark">GreenGrid 360</span>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php?accion=listar" class="sidebar-link">
                Mediciones
            </a>
            <a href="index.php?accion=dispositivos" class="sidebar-link">
                Dispositivos
            </a>
            <a href="index.php?accion=alertas" class="sidebar-link active">
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
            <section class="toolbar">
                <h2 class="section-title">Alertas</h2>
                <div class="toolbar-actions">
                    <form method="GET" action="index.php" class="search-form" id="searchForm">
                        <input type="hidden" name="accion" value="alertas">
                        <?php if (!empty($filtros['id_dispositivo'])): ?>
                            <input type="hidden" name="id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo']); ?>">
                        <?php endif; ?>
                        <?php if ($filtros['activo'] !== null && $filtros['activo'] !== ''): ?>
                            <input type="hidden" name="activo" value="<?php echo htmlspecialchars($filtros['activo']); ?>">
                        <?php endif; ?>
                        <input type="text" name="buscar" class="search-input" placeholder="Buscar alerta (ubicacion, correo, parametro)..." value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>" oninput="buscarEnTiempoReal(this)">
                        <?php if (!empty($filtros['buscar'])): ?>
                            <a href="<?php
                                $clearParams = $_GET;
                                unset($clearParams['buscar']);
                                $clearParams['accion'] = 'alertas';
                                echo 'index.php?' . http_build_query($clearParams);
                            ?>" class="btn-clear-search">Limpiar</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn-create" onclick="abrirModalCrear()">+ Nueva Alerta</button>
                </div>
            </section>

            <section class="filters-panel">
                <form method="GET" action="index.php" class="filters-form filters-dispositivos">
                    <input type="hidden" name="accion" value="alertas">
                    <?php if (!empty($filtros['buscar'])): ?>
                        <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
                    <?php endif; ?>

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

                    <div class="filter-group">
                        <label for="activo">Estado</label>
                        <select id="activo" name="activo" onchange="this.form.submit()">
                            <option value="">Todas</option>
                            <option value="1" <?php echo ($filtros['activo'] ?? null) === '1' ? 'selected' : ''; ?>>Activas</option>
                            <option value="0" <?php echo ($filtros['activo'] ?? null) === '0' ? 'selected' : ''; ?>>Inactivas</option>
                        </select>
                    </div>

                    <?php if (!empty($filtros['id_dispositivo']) || ($filtros['activo'] ?? null) !== null && $filtros['activo'] !== '' || !empty($filtros['buscar'])): ?>
                        <div class="filters-actions">
                            <a class="btn-clear" href="index.php?accion=alertas">Limpiar filtros</a>
                        </div>
                    <?php endif; ?>
                </form>
            </section>

            <?php if (!empty($alertas)): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Dispositivo</th>
                                <th>Parametro</th>
                                <th>Condicion</th>
                                <th>Umbral</th>
                                <th>Correo</th>
                                <th>Estado</th>
                                <th class="th-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($alertas as $alerta): ?>
                                <tr class="row-medicion"
                                    data-id="<?php echo htmlspecialchars((string) $alerta['id_alerta']); ?>"
                                    data-id-dispositivo="<?php echo htmlspecialchars((string) $alerta['id_dispositivo']); ?>"
                                    data-parametro="<?php echo htmlspecialchars($alerta['parametro']); ?>"
                                    data-tipo-condicion="<?php echo htmlspecialchars($alerta['tipo_condicion']); ?>"
                                    data-valor-umbral="<?php echo htmlspecialchars((string) $alerta['valor_umbral']); ?>"
                                    data-correo-destino="<?php echo htmlspecialchars($alerta['correo_destino']); ?>"
                                    data-activo="<?php echo $alerta['activo'] ? '1' : '0'; ?>">
                                    <td><?php echo htmlspecialchars((string) $alerta['id_alerta']); ?></td>
                                    <td><?php echo htmlspecialchars($alerta['ubicacion']); ?></td>
                                    <td class="td-capitalize"><?php echo htmlspecialchars(str_replace('_', ' ', $alerta['parametro'])); ?></td>
                                    <td>
                                        <?php if ($alerta['tipo_condicion'] === 'minimo'): ?>
                                            <span class="badge-estado badge-minimo">Por debajo de</span>
                                        <?php else: ?>
                                            <span class="badge-estado badge-maximo">Por encima de</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars((string) $alerta['valor_umbral']); ?></td>
                                    <td><?php echo htmlspecialchars($alerta['correo_destino']); ?></td>
                                    <td>
                                        <span class="badge-estado <?php echo $alerta['activo'] ? 'badge-activo' : 'badge-inactivo'; ?>">
                                            <?php echo $alerta['activo'] ? 'Activa' : 'Inactiva'; ?>
                                        </span>
                                    </td>
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

                    <span class="page-info"><?php echo $total; ?> alertas</span>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="message">
                    <p>No hay alertas configuradas</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Nueva Alerta</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <form method="POST" action="" id="modalForm" class="modal-form">
                <input type="hidden" name="filtro_buscar" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
                <input type="hidden" name="filtro_id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo'] ?? ''); ?>">
                <input type="hidden" name="filtro_activo" value="<?php echo htmlspecialchars($filtros['activo'] ?? ''); ?>">
                <input type="hidden" name="pagina" value="<?php echo $pagina; ?>">

                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="m_id_dispositivo">Dispositivo</label>
                            <select id="m_id_dispositivo" name="id_dispositivo" required>
                                <option value="">Seleccionar...</option>
                                <?php foreach ($dispositivos as $disp): ?>
                                    <option value="<?php echo $disp['id_dispositivo']; ?>"><?php echo htmlspecialchars($disp['ubicacion']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="m_parametro">Parametro</label>
                            <select id="m_parametro" name="parametro" required>
                                <option value="">Seleccionar...</option>
                                <option value="temperatura">Temperatura</option>
                                <option value="humedad">Humedad</option>
                                <option value="humedad_suelo">Humedad Suelo</option>
                                <option value="calidad_aire">Calidad Aire</option>
                                <option value="lluvia">Lluvia</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="m_tipo_condicion">Condicion</label>
                            <select id="m_tipo_condicion" name="tipo_condicion" required>
                                <option value="">Seleccionar...</option>
                                <option value="minimo">Por debajo de</option>
                                <option value="maximo">Por encima de</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="m_valor_umbral">Valor umbral</label>
                            <input type="number" id="m_valor_umbral" name="valor_umbral" step="any" required placeholder="Ej: 30">
                        </div>
                        <div class="form-group">
                            <label for="m_correo_destino">Correo destino</label>
                            <input type="email" id="m_correo_destino" name="correo_destino" required placeholder="usuario@gmail.com" maxlength="100" value="<?php echo htmlspecialchars($_SESSION['usuario']['correo'] ?? ''); ?>">
                        </div>
                        <div class="form-group">
                            <label for="m_activo">Estado de alerta</label>
                            <div class="checkbox-wrapper">
                                <input type="checkbox" id="m_activo" name="activo" value="1" checked>
                                <label for="m_activo" class="checkbox-label">Alerta activa</label>
                            </div>
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
        <input type="hidden" name="filtro_buscar" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
        <input type="hidden" name="filtro_id_dispositivo" value="<?php echo htmlspecialchars($filtros['id_dispositivo'] ?? ''); ?>">
        <input type="hidden" name="filtro_activo" value="<?php echo htmlspecialchars($filtros['activo'] ?? ''); ?>">
        <input type="hidden" name="pagina" value="<?php echo $pagina; ?>">
    </form>

<script>
var searchTimer = null;

function buscarEnTiempoReal(input) {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(function() {
        input.form.submit();
    }, 350);
}

function abrirModalCrear() {
    document.getElementById('modalTitle').textContent = 'Nueva Alerta';
    document.getElementById('modalForm').action = 'index.php?accion=crear-alerta';
    document.getElementById('btnSave').textContent = 'Guardar';
    limpiarFormulario();
    document.getElementById('m_activo').checked = true;
    document.getElementById('modalOverlay').classList.add('active');
}

function abrirModalEditar(btn) {
    var row = btn.closest('.row-medicion');
    document.getElementById('modalTitle').textContent = 'Editar Alerta';
    document.getElementById('modalForm').action = 'index.php?accion=editar-alerta&id=' + row.dataset.id;
    document.getElementById('btnSave').textContent = 'Actualizar';

    document.getElementById('m_id_dispositivo').value = row.dataset.idDispositivo;
    document.getElementById('m_parametro').value = row.dataset.parametro;
    document.getElementById('m_tipo_condicion').value = row.dataset.tipoCondicion;
    document.getElementById('m_valor_umbral').value = row.dataset.valorUmbral;
    document.getElementById('m_correo_destino').value = row.dataset.correoDestino;
    document.getElementById('m_activo').checked = row.dataset.activo === '1';

    document.getElementById('modalOverlay').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('active');
}

function limpiarFormulario() {
    document.getElementById('m_id_dispositivo').value = '';
    document.getElementById('m_parametro').value = '';
    document.getElementById('m_tipo_condicion').value = '';
    document.getElementById('m_valor_umbral').value = '';
    document.getElementById('m_correo_destino').value = '<?php echo htmlspecialchars($_SESSION['usuario']['correo'] ?? ''); ?>';
}

function confirmarEliminar(btn) {
    if (!confirm('Estas seguro de eliminar esta alerta?')) {
        return;
    }
    var row = btn.closest('.row-medicion');
    var form = document.getElementById('deleteForm');
    form.action = 'index.php?accion=eliminar-alerta&id=' + row.dataset.id;
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
