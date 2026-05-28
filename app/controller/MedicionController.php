<?php
require_once(__DIR__ . '/../model/MedicionAmbiental.php');

class MedicionController {
    private $modelo;
    
    public function __construct($conexion) {
        $this->modelo = new MedicionAmbiental($conexion);
    }
    
    /**
     * Obtiene todas las mediciones y las pasa a la vista
     */
    public function listar() {
        $filtros = $this->obtenerFiltros();
        $mediciones = $this->modelo->obtenerMediciones($filtros);
        
        if ($mediciones === false) {
            $error = "Error al obtener los datos";
            require(__DIR__ . '/../view/error.php');
            return;
        }
        
        require(__DIR__ . '/../view/mediciones.php');
    }

    /**
     * Lee y valida filtros del query string para no mezclar logica en la vista.
     */
    private function obtenerFiltros() {
        $filtros = [
            'fecha_desde' => null,
            'fecha_hasta' => null
        ];

        if (!empty($_GET['fecha_desde']) && $this->esFechaValida($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = $_GET['fecha_desde'];
        }

        if (!empty($_GET['fecha_hasta']) && $this->esFechaValida($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
        }

        return $filtros;
    }

    private function esFechaValida($valor) {
        $fecha = DateTime::createFromFormat('Y-m-d', (string) $valor);
        return $fecha && $fecha->format('Y-m-d') === $valor;
    }
    
    /**
     * Obtiene una medición específica por ID
     */
    public function obtener($id) {
        $medicion = $this->modelo->obtenerMedicionPorId($id);
        
        if (!$medicion) {
            $error = "Medición no encontrada";
            require(__DIR__ . '/../view/error.php');
            return;
        }
        
        require(__DIR__ . '/../view/medicion_detalle.php');
    }
    
    /**
     * Procesa la inserción de una nueva medición
     */
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
