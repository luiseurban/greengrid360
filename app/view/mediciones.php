<?php
$has_session = isset($_SESSION['usuario']);
$currentAction = $_GET['accion'] ?? 'listar';

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
                                <th>Temperatura (°C)</th>
                                <th>Humedad (%)</th>
                                <th>Hum. Suelo (%)</th>
                                <th>Calidad del Aire (PPM)</th>
                                <th>Lluvia (mm)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($mediciones as $fila): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($fila['fecha_hora']); ?></td>
                                    <td><?php echo htmlspecialchars($fila['ubicacion']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['temperatura']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['humedad']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['humedad_suelo']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['calidad_aire']); ?></td>
                                    <td><?php echo htmlspecialchars((string) $fila['lluvia']); ?></td>
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
</body>
</html>
