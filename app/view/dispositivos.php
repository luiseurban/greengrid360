<?php
$currentAction = $_GET['accion'] ?? 'dispositivos';

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
    <title>Dispositivos | GreenGrid 360</title>
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
            <a href="index.php?accion=dispositivos" class="sidebar-link active">
                Dispositivos
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
                <h2 class="section-title">Dispositivos</h2>
                <div class="toolbar-actions">
                    <form method="GET" action="index.php" class="search-form" id="searchForm">
                        <input type="hidden" name="accion" value="dispositivos">
                        <?php if (!empty($filtros['estado'])): ?>
                            <input type="hidden" name="estado" value="<?php echo htmlspecialchars($filtros['estado']); ?>">
                        <?php endif; ?>
                        <input type="text" name="buscar" class="search-input" placeholder="Buscar dispositivo..." value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>" oninput="buscarEnTiempoReal(this)">
                        <?php if (!empty($filtros['buscar'])): ?>
                            <a href="index.php?accion=dispositivos<?php echo !empty($filtros['estado']) ? '&estado=' . urlencode($filtros['estado']) : ''; ?>" class="btn-clear-search">Limpiar</a>
                        <?php endif; ?>
                    </form>
                    <button class="btn-create" onclick="abrirModalCrear()">+ Nuevo Dispositivo</button>
                </div>
            </section>

            <section class="filters-panel">
                <form method="GET" action="index.php" class="filters-form filters-dispositivos">
                    <input type="hidden" name="accion" value="dispositivos">
                    <?php if (!empty($filtros['buscar'])): ?>
                        <input type="hidden" name="buscar" value="<?php echo htmlspecialchars($filtros['buscar']); ?>">
                    <?php endif; ?>

                    <div class="filter-group">
                        <label for="estado">Estado</label>
                        <select id="estado" name="estado" onchange="this.form.submit()">
                            <option value="">Todos</option>
                            <?php foreach ($estados as $est): ?>
                                <option value="<?php echo htmlspecialchars($est['estado']); ?>" <?php echo ($filtros['estado'] ?? '') === $est['estado'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($est['estado']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($filtros['estado']) || !empty($filtros['buscar'])): ?>
                        <div class="filters-actions">
                            <a class="btn-clear" href="index.php?accion=dispositivos">Limpiar filtros</a>
                        </div>
                    <?php endif; ?>
                </form>
            </section>

            <?php if (!empty($dispositivos)): ?>
                <div class="table-wrapper">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ubicacion</th>
                                <th>Direccion IP</th>
                                <th>Estado</th>
                                <th class="th-acciones">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($dispositivos as $disp): ?>
                                <tr class="row-medicion"
                                    data-id="<?php echo htmlspecialchars((string) $disp['id_dispositivo']); ?>"
                                    data-ubicacion="<?php echo htmlspecialchars($disp['ubicacion']); ?>"
                                    data-direccion-ip="<?php echo htmlspecialchars($disp['direccion_ip']); ?>"
                                    data-estado="<?php echo htmlspecialchars($disp['estado']); ?>">
                                    <td><?php echo htmlspecialchars((string) $disp['id_dispositivo']); ?></td>
                                    <td><?php echo htmlspecialchars($disp['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars($disp['direccion_ip']); ?></td>
                                    <td>
                                        <span class="badge-estado <?php echo $disp['estado'] === 'Activo' ? 'badge-activo' : 'badge-inactivo'; ?>">
                                            <?php echo htmlspecialchars($disp['estado']); ?>
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

                    <span class="page-info"><?php echo $total; ?> dispositivos</span>
                </nav>
                <?php endif; ?>
            <?php else: ?>
                <div class="message">
                    <p>No hay dispositivos disponibles</p>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <div class="modal-overlay" id="modalOverlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Nuevo Dispositivo</h3>
                <button class="modal-close" onclick="cerrarModal()">&times;</button>
            </div>
            <form method="POST" action="" id="modalForm" class="modal-form">
                <input type="hidden" name="filtro_buscar" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
                <input type="hidden" name="filtro_estado" value="<?php echo htmlspecialchars($filtros['estado'] ?? ''); ?>">
                <input type="hidden" name="pagina" value="<?php echo $pagina; ?>">

                <div class="modal-body">
                    <div class="form-grid">
                        <div class="form-group form-group-full">
                            <label for="m_ubicacion">Ubicacion</label>
                            <input type="text" id="m_ubicacion" name="ubicacion" required placeholder="Ej: Huerto de Tomates" maxlength="100">
                        </div>
                        <div class="form-group">
                            <label for="m_direccion_ip">Direccion IP</label>
                            <input type="text" id="m_direccion_ip" name="direccion_ip" required placeholder="Ej: 192.168.1.10" maxlength="50">
                        </div>
                        <div class="form-group">
                            <label for="m_estado">Estado</label>
                            <select id="m_estado" name="estado" required>
                                <option value="">Seleccionar...</option>
                                <option value="Activo">Activo</option>
                                <option value="Inactivo">Inactivo</option>
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
        <input type="hidden" name="filtro_buscar" value="<?php echo htmlspecialchars($filtros['buscar'] ?? ''); ?>">
        <input type="hidden" name="filtro_estado" value="<?php echo htmlspecialchars($filtros['estado'] ?? ''); ?>">
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
    document.getElementById('modalTitle').textContent = 'Nuevo Dispositivo';
    document.getElementById('modalForm').action = 'index.php?accion=crear-dispositivo';
    document.getElementById('btnSave').textContent = 'Guardar';
    limpiarFormulario();
    document.getElementById('modalOverlay').classList.add('active');
}

function abrirModalEditar(btn) {
    var row = btn.closest('.row-medicion');
    document.getElementById('modalTitle').textContent = 'Editar Dispositivo';
    document.getElementById('modalForm').action = 'index.php?accion=editar-dispositivo&id=' + row.dataset.id;
    document.getElementById('btnSave').textContent = 'Actualizar';

    document.getElementById('m_ubicacion').value = row.dataset.ubicacion;
    document.getElementById('m_direccion_ip').value = row.dataset.direccionIp;
    document.getElementById('m_estado').value = row.dataset.estado;

    document.getElementById('modalOverlay').classList.add('active');
}

function cerrarModal() {
    document.getElementById('modalOverlay').classList.remove('active');
}

function limpiarFormulario() {
    document.getElementById('m_ubicacion').value = '';
    document.getElementById('m_direccion_ip').value = '';
    document.getElementById('m_estado').value = '';
}

function confirmarEliminar(btn) {
    if (!confirm('¿Estás seguro de eliminar este dispositivo? Se eliminarán también sus mediciones asociadas.')) {
        return;
    }
    var row = btn.closest('.row-medicion');
    var form = document.getElementById('deleteForm');
    form.action = 'index.php?accion=eliminar-dispositivo&id=' + row.dataset.id;
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
