<?php
include("../conexion.php");

$sql = "SELECT fecha_hora, temperatura, humedad, calidad_aire 
        FROM medicion_ambiental
        ORDER BY fecha_hora DESC";

$resultado = $conexion->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Datos Ambientales</title>
</head>
<body>

<h2>Mediciones Ambientales</h2>

<table border="1">
    <tr>
        <th>Fecha y Hora</th>
        <th>Temperatura (°C)</th>
        <th>Humedad (%)</th>
        <th>Calidad del Aire (PPM)</th>
    </tr>

    <?php
    if ($resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $fila['fecha_hora'] . "</td>";
            echo "<td>" . $fila['temperatura'] . "</td>";
            echo "<td>" . $fila['humedad'] . "</td>";
            echo "<td>" . $fila['calidad_aire'] . "</td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='4'>No hay datos disponibles</td></tr>";
    }
    ?>

</table>

</body>
</html>
