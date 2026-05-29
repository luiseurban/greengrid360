<?php
require_once __DIR__ . '/../database/Database.php';
require_once __DIR__ . '/../model/Alerta.php';
require_once __DIR__ . '/../lib/Mailer.php';

$db = new Database();
$conexion = $db->getConexion();
$alertaModel = new Alerta($conexion);
$mailer = new Mailer();

$dispositivos = $conexion->query("SELECT id_dispositivo, ubicacion FROM esp32");

echo "=== Verificando alertas ===\n";

while ($disp = $dispositivos->fetch_assoc()) {
    echo "Dispositivo #{$disp['id_dispositivo']} ({$disp['ubicacion']}): ";

    $medicion = $conexion->query(
        "SELECT temperatura, humedad, humedad_suelo, calidad_aire, lluvia
         FROM medicion_ambiental
         WHERE id_dispositivo = {$disp['id_dispositivo']}
         ORDER BY fecha_hora DESC LIMIT 1"
    )->fetch_assoc();

    if (!$medicion) {
        echo "sin mediciones\n";
        continue;
    }

    $alertas = $alertaModel->obtenerPorDispositivo($disp['id_dispositivo']);

    if (empty($alertas)) {
        echo "sin alertas activas\n";
        continue;
    }

    echo count($alertas) . " alerta(s)\n";

    foreach ($alertas as $alerta) {
        $valor = $medicion[$alerta['parametro']];
        $disparada = false;

        if ($alerta['tipo_condicion'] === 'minimo' && $valor < $alerta['valor_umbral']) {
            $disparada = true;
        } elseif ($alerta['tipo_condicion'] === 'maximo' && $valor > $alerta['valor_umbral']) {
            $disparada = true;
        }

        $paramLabel = str_replace('_', ' ', $alerta['parametro']);
        $condLabel = $alerta['tipo_condicion'] === 'minimo' ? '<' : '>';

        echo "  - {$paramLabel}: valor={$valor} {$condLabel} umbral={$alerta['valor_umbral']} -> "
           . ($disparada ? "DISPARADA" : "OK") . "\n";

        if ($disparada) {
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

            echo "    Enviando a: {$alerta['correo_destino']}... ";
            if ($alertaModel->puedeEnviar($alerta['id_alerta'])) {
                $enviado = $mailer->enviarAlerta($alerta['correo_destino'], $asunto, $cuerpo);
                if ($enviado) {
                    $alertaModel->registrarEnvio($alerta['id_alerta']);
                }
                echo ($enviado ? "OK" : "ERROR") . "\n";
            } else {
                echo "omitido (cooldown)\n";
            }
        }
    }
}

echo "=== Fin ===\n";

$db->cerrar();
