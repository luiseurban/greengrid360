<?php
require_once(__DIR__ . '/../model/MedicionAmbiental.php');

class MedicionController {
    private $modelo;
    private $conexion;
    
    public function __construct($conexion) {
        $this->modelo = new MedicionAmbiental($conexion);
        $this->conexion = $conexion;
    }
    
    public function listar() {
        $filtros = $this->obtenerFiltros();
        $porPagina = 10;
        $pagina = $this->obtenerPagina();

        $todos = $this->modelo->obtenerMediciones($filtros);
        $total = $this->modelo->contarMediciones($filtros);
        $mediciones = $this->modelo->obtenerMediciones($filtros, $porPagina, ($pagina - 1) * $porPagina);
        $dispositivos = $this->modelo->obtenerDispositivos();
        
        if ($mediciones === false || $todos === false) {
            $error = "Error al obtener los datos";
            require(__DIR__ . '/../view/error.php');
            return;
        }
        
        $stats = $this->calcularEstadisticas($todos);
        $totalPaginas = (int)ceil($total / $porPagina);
        $alertasDisparadas = $this->verificarAlertas();

        require(__DIR__ . '/../view/mediciones.php');
    }

    private function verificarAlertas() {
        require_once(__DIR__ . '/../model/Alerta.php');
        $alertaModel = new Alerta($this->conexion);
        $resultado = [];

        $dispositivos = $this->conexion->query("SELECT id_dispositivo, ubicacion FROM esp32");
        while ($disp = $dispositivos->fetch_assoc()) {
            $ultima = $this->conexion->query(
                "SELECT temperatura, humedad, humedad_suelo, calidad_aire, lluvia
                 FROM medicion_ambiental
                 WHERE id_dispositivo = {$disp['id_dispositivo']}
                 ORDER BY fecha_hora DESC LIMIT 1"
            )->fetch_assoc();

            if (!$ultima) continue;

            $alertas = $alertaModel->obtenerPorDispositivo($disp['id_dispositivo']);
            foreach ($alertas as $alerta) {
                $valor = $ultima[$alerta['parametro']];
                $disparada = false;

                if ($alerta['tipo_condicion'] === 'minimo' && $valor < $alerta['valor_umbral']) {
                    $disparada = true;
                } elseif ($alerta['tipo_condicion'] === 'maximo' && $valor > $alerta['valor_umbral']) {
                    $disparada = true;
                }

                if ($disparada) {
                    $resultado[] = [
                        'ubicacion' => $disp['ubicacion'],
                        'parametro' => str_replace('_', ' ', $alerta['parametro']),
                        'valor' => $valor,
                        'condicion' => $alerta['tipo_condicion'] === 'minimo' ? 'por debajo de' : 'por encima de',
                        'umbral' => $alerta['valor_umbral'],
                    ];
                }
            }
        }

        return $resultado;
    }

    private function obtenerFiltros() {
        $filtros = [
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'id_dispositivo' => null,
            'buscar' => null,
            'temperatura_min' => null, 'temperatura_max' => null,
            'humedad_min' => null, 'humedad_max' => null,
            'humedad_suelo_min' => null, 'humedad_suelo_max' => null,
            'calidad_aire_min' => null, 'calidad_aire_max' => null,
            'lluvia_min' => null, 'lluvia_max' => null,
        ];

        if (!empty($_GET['fecha_desde']) && $this->esFechaValida($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = $_GET['fecha_desde'];
        }

        if (!empty($_GET['fecha_hasta']) && $this->esFechaValida($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
        }

        if (!empty($_GET['id_dispositivo']) && ctype_digit($_GET['id_dispositivo'])) {
            $filtros['id_dispositivo'] = (int) $_GET['id_dispositivo'];
        }

        if (!empty($_GET['buscar'])) {
            $filtros['buscar'] = trim($_GET['buscar']);
        }

        $rangos = ['temperatura', 'humedad', 'humedad_suelo', 'calidad_aire', 'lluvia'];
        foreach ($rangos as $r) {
            if (isset($_GET[$r . '_min']) && $_GET[$r . '_min'] !== '') {
                $filtros[$r . '_min'] = (float) $_GET[$r . '_min'];
            }
            if (isset($_GET[$r . '_max']) && $_GET[$r . '_max'] !== '') {
                $filtros[$r . '_max'] = (float) $_GET[$r . '_max'];
            }
        }

        if (isset($_GET['hora_desde']) && ctype_digit($_GET['hora_desde'])) {
            $filtros['hora_desde'] = (int) $_GET['hora_desde'];
        }
        if (isset($_GET['hora_hasta']) && ctype_digit($_GET['hora_hasta'])) {
            $filtros['hora_hasta'] = (int) $_GET['hora_hasta'];
        }

        return $filtros;
    }

    private function esFechaValida($valor) {
        $fecha = DateTime::createFromFormat('Y-m-d', (string) $valor);
        return $fecha && $fecha->format('Y-m-d') === $valor;
    }

    private function obtenerPagina() {
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        return max(1, $pagina);
    }

    private function calcularEstadisticas($mediciones) {
        if (empty($mediciones)) {
            return [
                'total' => 0,
                'ultima' => 'Sin datos',
                'temp_promedio' => 0,
                'temp_max' => 0,
                'hum_promedio' => 0,
                'dispositivos' => 0
            ];
        }

        $temps = array_column($mediciones, 'temperatura');
        $humedades = array_column($mediciones, 'humedad');
        $dispositivosUnicos = array_unique(array_column($mediciones, 'id_dispositivo'));

        return [
            'total' => count($mediciones),
            'ultima' => $mediciones[0]['fecha_hora'],
            'temp_promedio' => round(array_sum($temps) / count($mediciones), 1),
            'temp_max' => round(max($temps), 1),
            'hum_promedio' => round(array_sum($humedades) / count($mediciones), 1),
            'dispositivos' => count($dispositivosUnicos)
        ];
    }
    
    public function obtener($id) {
        $medicion = $this->modelo->obtenerMedicionPorId($id);
        
        if (!$medicion) {
            $error = "Medición no encontrada";
            require(__DIR__ . '/../view/error.php');
            return;
        }
        
        require(__DIR__ . '/../view/medicion_detalle.php');
    }
    
    public function crear() {
        $temperatura = $_POST['temperatura'] ?? null;
        $humedad = $_POST['humedad'] ?? null;
        $humedad_suelo = $_POST['humedad_suelo'] ?? null;
        $calidad_aire = $_POST['calidad_aire'] ?? null;
        $lluvia = $_POST['lluvia'] ?? null;
        $fecha_hora = $_POST['fecha_hora'] ?? null;
        $id_dispositivo = $_POST['id_dispositivo'] ?? null;

        $datos = [
            'temperatura' => $this->aFloat($temperatura),
            'humedad' => $this->aFloat($humedad),
            'humedad_suelo' => $this->aFloat($humedad_suelo),
            'calidad_aire' => $this->aFloat($calidad_aire),
            'lluvia' => $this->aFloat($lluvia),
            'fecha_hora' => $fecha_hora,
            'id_dispositivo' => (int) $id_dispositivo
        ];

        if ($this->modelo->insertar($datos)) {
            $this->redirectToListar();
        } else {
            $error = "Error al insertar la medición";
            require(__DIR__ . '/../view/error.php');
        }
    }

    public function actualizar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->redirectToListar();
            return;
        }

        $temperatura = $_POST['temperatura'] ?? null;
        $humedad = $_POST['humedad'] ?? null;
        $humedad_suelo = $_POST['humedad_suelo'] ?? null;
        $calidad_aire = $_POST['calidad_aire'] ?? null;
        $lluvia = $_POST['lluvia'] ?? null;
        $fecha_hora = $_POST['fecha_hora'] ?? null;
        $id_dispositivo = $_POST['id_dispositivo'] ?? null;

        $datos = [
            'temperatura' => $this->aFloat($temperatura),
            'humedad' => $this->aFloat($humedad),
            'humedad_suelo' => $this->aFloat($humedad_suelo),
            'calidad_aire' => $this->aFloat($calidad_aire),
            'lluvia' => $this->aFloat($lluvia),
            'fecha_hora' => $fecha_hora,
            'id_dispositivo' => (int) $id_dispositivo
        ];

        if ($this->modelo->actualizar($id, $datos)) {
            $this->redirectToListar();
        } else {
            $error = "Error al actualizar la medición";
            require(__DIR__ . '/../view/error.php');
        }
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->redirectToListar();
            return;
        }

        if ($this->modelo->eliminar($id)) {
            $this->redirectToListar();
        } else {
            $error = "Error al eliminar la medición";
            require(__DIR__ . '/../view/error.php');
        }
    }

    public function enviarAlertas() {
        session_write_close();
        require_once(__DIR__ . '/../model/Alerta.php');
        require_once(__DIR__ . '/../lib/Mailer.php');
        $alertaModel = new Alerta($this->conexion);
        $mailer = new Mailer();
        $enviados = 0;

        $dispositivos = $this->conexion->query("SELECT id_dispositivo, ubicacion FROM esp32");
        while ($disp = $dispositivos->fetch_assoc()) {
            $ultima = $this->conexion->query(
                "SELECT temperatura, humedad, humedad_suelo, calidad_aire, lluvia
                 FROM medicion_ambiental
                 WHERE id_dispositivo = {$disp['id_dispositivo']}
                 ORDER BY fecha_hora DESC LIMIT 1"
            )->fetch_assoc();

            if (!$ultima) continue;

            $alertas = $alertaModel->obtenerPorDispositivo($disp['id_dispositivo']);
            foreach ($alertas as $alerta) {
                $valor = $ultima[$alerta['parametro']];
                $disparada = false;

                if ($alerta['tipo_condicion'] === 'minimo' && $valor < $alerta['valor_umbral']) {
                    $disparada = true;
                } elseif ($alerta['tipo_condicion'] === 'maximo' && $valor > $alerta['valor_umbral']) {
                    $disparada = true;
                }

                if ($disparada && $alertaModel->puedeEnviar($alerta['id_alerta'])) {
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
                    if ($mailer->enviarAlerta($alerta['correo_destino'], $asunto, $cuerpo)) {
                        $alertaModel->registrarEnvio($alerta['id_alerta']);
                        $enviados++;
                    }
                }
            }
        }

        echo json_encode(['enviados' => $enviados]);
    }

    private function aFloat($valor) {
        if ($valor === null || $valor === '') {
            return null;
        }
        return (float) $valor;
    }

    private function redirectToListar() {
        $params = [];
        $filtroKeys = ['fecha_desde', 'fecha_hasta', 'id_dispositivo', 'buscar',
                       'temperatura_min', 'temperatura_max',
                       'humedad_min', 'humedad_max',
                       'humedad_suelo_min', 'humedad_suelo_max',
                       'calidad_aire_min', 'calidad_aire_max',
                       'lluvia_min', 'lluvia_max',
                       'hora_desde', 'hora_hasta'];

        foreach ($filtroKeys as $key) {
            $valor = $_POST['filtro_' . $key] ?? $_GET[$key] ?? '';
            if ($valor !== '') {
                $params[$key] = $valor;
            }
        }

        $pagina = $_POST['pagina'] ?? $_GET['pagina'] ?? '';
        if ($pagina !== '') {
            $params['pagina'] = $pagina;
        }

        if (empty($params)) {
            header('Location: index.php?accion=listar');
        } else {
            $params['accion'] = 'listar';
            header('Location: index.php?' . http_build_query($params));
        }
        exit;
    }
}
?>
