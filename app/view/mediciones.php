<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mediciones Ambientales - GreenGrid 360</title>
    <link rel="stylesheet" href="../css/index.css">
</head>
<body>
    <div class="container">
        <div class="top-bar">
            <p>Sesion: <?php echo htmlspecialchars($_SESSION['usuario']['nombre'] ?? ''); ?></p>
            <a class="logout-link" href="index.php?accion=logout">Cerrar sesion</a>
        </div>

        <h1>GreenGrid 360</h1>
        <p class="subtitle">Mediciones Ambientales</p>
        
        <?php if (!empty($mediciones)): ?>
            <table class="data-table" border="1">
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
        <?php else: ?>
            <div class="message">
                <p>No hay datos disponibles</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
