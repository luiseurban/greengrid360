<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediciones Ambientales - GreenGrid 360</title>
    <link rel="stylesheet" href="../css/mediciones.css">
</head>
<body>
    <div class="container">
        <?php
            $totalMediciones = is_array($mediciones ?? null) ? count($mediciones) : 0;
            $ultimaActualizacion = $totalMediciones > 0 ? $mediciones[0]['fecha_hora'] : 'Sin datos';
        ?>

        <header class="panel-header">
            <div>
                <p class="brand-tag">GreenGrid 360</p>
                <h1>Mediciones Ambientales</h1>
                <p class="subtitle">Panel de consulta de registros ambientales</p>
            </div>
            <div class="top-bar">
                <p>Sesion: <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? ''); ?></p>
                <a class="logout-link" href="index.php?accion=logout">Cerrar sesion</a>
            </div>
        </header>

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
</body>
</html>
