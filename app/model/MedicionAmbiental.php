<?php

class MedicionAmbiental {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    /**
     * Obtiene todas las mediciones ordenadas por fecha descendente
     */
    public function obtenerMediciones($filtros = []) {
        $sql = "SELECT fecha_hora, temperatura, humedad, calidad_aire 
                FROM medicion_ambiental";

        $condiciones = [];

        if (!empty($filtros['fecha_desde'])) {
            $fechaDesde = $this->conexion->real_escape_string($filtros['fecha_desde']);
            $condiciones[] = "DATE(fecha_hora) >= '" . $fechaDesde . "'";
        }

        if (!empty($filtros['fecha_hasta'])) {
            $fechaHasta = $this->conexion->real_escape_string($filtros['fecha_hasta']);
            $condiciones[] = "DATE(fecha_hora) <= '" . $fechaHasta . "'";
        }

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY fecha_hora DESC";
        
        $resultado = $this->conexion->query($sql);
        
        if (!$resultado) {
            return false;
        }
        
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    /**
     * Obtiene una medición específica por ID
     */
    public function obtenerMedicionPorId($id) {
        $sql = "SELECT fecha_hora, temperatura, humedad, calidad_aire 
                FROM medicion_ambiental
                WHERE id = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }
    
    /**
     * Inserta una nueva medición
     */
    public function insertar($temperatura, $humedad, $calidad_aire) {
        $sql = "INSERT INTO medicion_ambiental (temperatura, humedad, calidad_aire, fecha_hora) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ddi", $temperatura, $humedad, $calidad_aire);
        
        return $stmt->execute();
    }
}
?>
