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
        $mediciones = $this->modelo->obtenerMediciones();
        
        if ($mediciones === false) {
            $error = "Error al obtener los datos";
            require(__DIR__ . '/../view/error.php');
            return;
        }
        
        require(__DIR__ . '/../view/mediciones.php');
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
