<?php
require_once(__DIR__ . '/../model/MedicionAmbiental.php');

class MedicionController {
    private $modelo;
    
    public function __construct($conexion) {
        $this->modelo = new MedicionAmbiental($conexion);
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
        
        require(__DIR__ . '/../view/mediciones.php');
    }

    private function obtenerFiltros() {
        $filtros = [
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'id_dispositivo' => null
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
    
    public function crear($temperatura, $humedad, $calidad_aire) {
        if ($this->modelo->insertar($temperatura, $humedad, $calidad_aire)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al insertar la medición";
            require(__DIR__ . '/../view/error.php');
        }
    }
}
?>
