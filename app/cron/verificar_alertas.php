<?php
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../model/Alerta.php';
require_once __DIR__ . '/../lib/Mailer.php';

$db = new Database();
$conexion = $db->getConexion();
$alertaModel = new Alerta($conexion);
$mailer = new Mailer();

$dispositivos = $conexion->query("SELECT id_dispositivo, ubicacion FROM esp32");

while ($disp = $dispositivos->fetch_assoc()) {
    $medicion = $conexion->query(
        "SELECT temperatura, humedad, humedad_suelo, calidad_aire, lluvia
         FROM medicion_ambiental
         WHERE id_dispositivo = {$disp['id_dispositivo']}
         ORDER BY fecha_hora DESC LIMIT 1"
    )->fetch_assoc();

    if (!$medicion) continue;

    $alertas = $alertaModel->obtenerPorDispositivo($disp['id_dispositivo']);

    foreach ($alertas as $alerta) {
        $valor = $medicion[$alerta['parametro']];
        $disparada = false;

        if ($alerta['tipo_condicion'] === 'minimo' && $valor < $alerta['valor_umbral']) {
            $disparada = true;
        } elseif ($alerta['tipo_condicion'] === 'maximo' && $valor > $alerta['valor_umbral']) {
            $disparada = true;
        }

        if ($disparada) {
            $paramLabel = str_replace('_', ' ', $alerta['parametro']);
            $condicion = $alerta['tipo_condicion'] === 'minimo' ? 'por debajo de' : 'por encima de';
            $asunto = "Alerta GreenGrid360 - {$paramLabel} en {$disp['ubicacion']}";
            $cuerpo = "
                <h2>Alerta GreenGrid 360</h2>
                <p><strong>Dispositivo:</strong> {$disp['ubicacion']}</p>
                <p><strong>Parametro:</strong> {$paramLabel}</p>
                <p><strong>Valor actual:</strong> {$valor}</p>
                <p><strong>Umbral:</strong> {$condicion} {$alerta['valor_umbral']}</p>
                <br><p>GreenGrid 360 - Monitoreo ambiental</p>
            ";

            $mailer->enviarAlerta($alerta['correo_destino'], $asunto, $cuerpo);
        }
    }
}

$db->cerrar();
