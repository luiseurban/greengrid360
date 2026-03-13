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

                <div class="filter-group">
                    <label for="temperatura_min">Temp. min (°C)</label>
                    <input type="number" step="0.1" id="temperatura_min" name="temperatura_min" value="<?php echo htmlspecialchars((string) ($filtros['temperatura_min'] ?? '')); ?>">
                </div>

                <div class="filter-group">
                    <label for="temperatura_max">Temp. max (°C)</label>
                    <input type="number" step="0.1" id="temperatura_max" name="temperatura_max" value="<?php echo htmlspecialchars((string) ($filtros['temperatura_max'] ?? '')); ?>">
                </div>

                <div class="filter-group">
                    <label for="humedad_min">Humedad min (%)</label>
                    <input type="number" step="0.1" id="humedad_min" name="humedad_min" value="<?php echo htmlspecialchars((string) ($filtros['humedad_min'] ?? '')); ?>">
                </div>

                <div class="filter-group">
                    <label for="humedad_max">Humedad max (%)</label>
                    <input type="number" step="0.1" id="humedad_max" name="humedad_max" value="<?php echo htmlspecialchars((string) ($filtros['humedad_max'] ?? '')); ?>">
                </div>

                <div class="filter-group">
                    <label for="calidad_aire_min">Aire min (PPM)</label>
                    <input type="number" step="1" id="calidad_aire_min" name="calidad_aire_min" value="<?php echo htmlspecialchars((string) ($filtros['calidad_aire_min'] ?? '')); ?>">
                </div>

                <div class="filter-group">
                    <label for="calidad_aire_max">Aire max (PPM)</label>
                    <input type="number" step="1" id="calidad_aire_max" name="calidad_aire_max" value="<?php echo htmlspecialchars((string) ($filtros['calidad_aire_max'] ?? '')); ?>">
                </div>

                <div class="filters-actions">
                    <button type="submit" class="btn-filter">Aplicar filtros</button>
                    <a class="btn-clear" href="index.php?accion=listar">Limpiar</a>
                </div>
            </form>
        </section>

        <?php if (!empty($datosGrafica['svg'])): ?>
            <section class="chart-panel">
                <h2>Tendencia de mediciones</h2>
                <div class="chart-legend">
                    <span class="legend-item"><i class="legend-dot temp"></i>Temperatura (C)</span>
                    <span class="legend-item"><i class="legend-dot hum"></i>Humedad (%)</span>
                    <span class="legend-item"><i class="legend-dot air"></i>Calidad del aire (PPM)</span>
                </div>
                <div class="chart-container">
                    <svg
                        class="trend-chart"
                        viewBox="0 0 <?php echo (int) $datosGrafica['svg']['width']; ?> <?php echo (int) $datosGrafica['svg']['height']; ?>"
                        role="img"
                        aria-label="Grafica de tendencia de temperatura, humedad y calidad del aire"
                    >
                        <?php foreach ($datosGrafica['svg']['grid'] as $linea): ?>
                            <line
                                x1="<?php echo (float) $datosGrafica['svg']['left']; ?>"
                                y1="<?php echo htmlspecialchars((string) $linea['y']); ?>"
                                x2="<?php echo (float) $datosGrafica['svg']['plot_right']; ?>"
                                y2="<?php echo htmlspecialchars((string) $linea['y']); ?>"
                                class="grid-line"
                            />
                            <text x="16" y="<?php echo ((float) $linea['y']) + 4; ?>" class="axis-label">
                                <?php echo htmlspecialchars($linea['left_label']); ?>
                            </text>
                            <text x="<?php echo (float) $datosGrafica['svg']['plot_right'] + 8; ?>" y="<?php echo ((float) $linea['y']) + 4; ?>" class="axis-label axis-right">
                                <?php echo htmlspecialchars($linea['right_label']); ?>
                            </text>
                        <?php endforeach; ?>

                        <?php foreach ($datosGrafica['svg']['x_labels'] as $xLabel): ?>
                            <text x="<?php echo htmlspecialchars((string) $xLabel['x']); ?>" y="<?php echo (float) $datosGrafica['svg']['plot_bottom'] + 24; ?>" class="axis-label axis-x">
                                <?php echo htmlspecialchars($xLabel['label']); ?>
                            </text>
                        <?php endforeach; ?>

                        <polyline points="<?php echo htmlspecialchars($datosGrafica['svg']['series']['temperatura']['line']); ?>" class="line-temp" />
                        <polyline points="<?php echo htmlspecialchars($datosGrafica['svg']['series']['humedad']['line']); ?>" class="line-hum" />
                        <polyline points="<?php echo htmlspecialchars($datosGrafica['svg']['series']['aire']['line']); ?>" class="line-air" />

                        <?php foreach ($datosGrafica['svg']['series']['temperatura']['points'] as $punto): ?>
                            <circle cx="<?php echo htmlspecialchars((string) $punto['x']); ?>" cy="<?php echo htmlspecialchars((string) $punto['y']); ?>" r="3" class="dot-temp" />
                        <?php endforeach; ?>

                        <?php foreach ($datosGrafica['svg']['series']['humedad']['points'] as $punto): ?>
                            <circle cx="<?php echo htmlspecialchars((string) $punto['x']); ?>" cy="<?php echo htmlspecialchars((string) $punto['y']); ?>" r="3" class="dot-hum" />
                        <?php endforeach; ?>

                        <?php foreach ($datosGrafica['svg']['series']['aire']['points'] as $punto): ?>
                            <circle cx="<?php echo htmlspecialchars((string) $punto['x']); ?>" cy="<?php echo htmlspecialchars((string) $punto['y']); ?>" r="3" class="dot-air" />
                        <?php endforeach; ?>
                    </svg>
                </div>
            </section>
        <?php endif; ?>
        
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
