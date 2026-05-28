<?php

class MedicionAmbiental {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    private function construirCondiciones($filtros) {
        $condiciones = [];

        if (!empty($filtros['fecha_desde'])) {
            $fechaDesde = $this->conexion->real_escape_string($filtros['fecha_desde']);
            $condiciones[] = "DATE(m.fecha_hora) >= '" . $fechaDesde . "'";
        }

        if (!empty($filtros['fecha_hasta'])) {
            $fechaHasta = $this->conexion->real_escape_string($filtros['fecha_hasta']);
            $condiciones[] = "DATE(m.fecha_hora) <= '" . $fechaHasta . "'";
        }

        if (!empty($filtros['id_dispositivo'])) {
            $idDispositivo = (int) $filtros['id_dispositivo'];
            $condiciones[] = "m.id_dispositivo = " . $idDispositivo;
        }

        return $condiciones;
    }

    public function obtenerMediciones($filtros = [], $limite = null, $offset = 0) {
        $sql = "SELECT m.id_medicion, m.temperatura, m.humedad, m.humedad_suelo, 
                       m.calidad_aire, m.lluvia, m.fecha_hora, m.id_dispositivo,
                       e.ubicacion
                FROM medicion_ambiental m
                LEFT JOIN esp32 e ON m.id_dispositivo = e.id_dispositivo";

        $condiciones = $this->construirCondiciones($filtros);

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY m.fecha_hora DESC";

        if ($limite !== null) {
            $sql .= " LIMIT " . (int)$limite . " OFFSET " . (int)$offset;
        }
        
        $resultado = $this->conexion->query($sql);
        
        if (!$resultado) {
            return false;
        }
        
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function contarMediciones($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM medicion_ambiental m";

        $condiciones = $this->construirCondiciones($filtros);

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $resultado = $this->conexion->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int)$fila['total'];
    }
    
    public function obtenerMedicionPorId($id) {
        $sql = "SELECT m.id_medicion, m.temperatura, m.humedad, m.humedad_suelo,
                       m.calidad_aire, m.lluvia, m.fecha_hora, m.id_dispositivo,
                       e.ubicacion
                FROM medicion_ambiental m
                LEFT JOIN esp32 e ON m.id_dispositivo = e.id_dispositivo
                WHERE m.id_medicion = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }
    
    public function insertar($temperatura, $humedad, $calidad_aire) {
        $sql = "INSERT INTO medicion_ambiental (temperatura, humedad, calidad_aire, fecha_hora) 
                VALUES (?, ?, ?, NOW())";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ddi", $temperatura, $humedad, $calidad_aire);
        
        return $stmt->execute();
    }

    public function obtenerDispositivos() {
        $sql = "SELECT id_dispositivo, ubicacion, estado FROM esp32 ORDER BY id_dispositivo";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>
