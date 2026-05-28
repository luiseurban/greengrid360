<?php
$has_session = isset($_SESSION['usuario']);
$currentAction = $_GET['accion'] ?? 'listar';
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
            <?php
                $totalMediciones = is_array($mediciones ?? null) ? count($mediciones) : 0;
                $ultimaActualizacion = $totalMediciones > 0 ? $mediciones[0]['fecha_hora'] : 'Sin datos';
            ?>

        <section class="stats-row">
            <article class="stat-card">
                <p class="stat-label">Total de registros</p>
                <p class="stat-value"><?php echo htmlspecialchars((string) $totalMediciones); ?></p>
            </article>
            <article class="stat-card">
                <p class="stat-label">Ultima actualizacion</p>
                <p class="stat-value stat-date"><?php echo htmlspecialchars($ultimaActualizacion); ?></p>
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
                            <th>Temperatura (°C)</th>
                            <th>Humedad (%)</th>
                            <th>Calidad del Aire (PPM)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($mediciones as $fila): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['fecha_hora']); ?></td>
                                <td><?php echo htmlspecialchars($fila['temperatura']); ?></td>
                                <td><?php echo htmlspecialchars($fila['humedad']); ?></td>
                                <td><?php echo htmlspecialchars($fila['calidad_aire']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="message">
                <p>No hay datos disponibles</p>
            </div>
        <?php endif; ?>
    </div>
</main>
</body>
</html>
