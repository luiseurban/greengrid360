<?php

class Esp32 {
    private $conexion;
    
    public function __construct($conexion) {
        $this->conexion = $conexion;
    }
    
    public function obtenerDispositivos($filtros = [], $limite = null, $offset = 0) {
        $sql = "SELECT id_dispositivo, estado, direccion_ip, ubicacion FROM esp32";
        
        $condiciones = $this->construirCondiciones($filtros);

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY id_dispositivo ASC";

        if ($limite !== null) {
            $sql .= " LIMIT " . (int)$limite . " OFFSET " . (int)$offset;
        }

        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            return false;
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function contarDispositivos($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM esp32";

        $condiciones = $this->construirCondiciones($filtros);

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $resultado = $this->conexion->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int)$fila['total'];
    }

    public function obtenerPorId($id) {
        $sql = "SELECT id_dispositivo, estado, direccion_ip, ubicacion FROM esp32 WHERE id_dispositivo = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function obtenerEstados() {
        $sql = "SELECT DISTINCT estado FROM esp32 ORDER BY estado";
        $resultado = $this->conexion->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function insertar($datos) {
        $sql = "INSERT INTO esp32 (estado, direccion_ip, ubicacion) VALUES (?, ?, ?)";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sss", $datos['estado'], $datos['direccion_ip'], $datos['ubicacion']);
        
        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE esp32 SET estado = ?, direccion_ip = ?, ubicacion = ? WHERE id_dispositivo = ?";
        
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("sssi", $datos['estado'], $datos['direccion_ip'], $datos['ubicacion'], $id);
        
        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM esp32 WHERE id_dispositivo = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    private function construirCondiciones($filtros) {
        $condiciones = [];

        if (!empty($filtros['buscar'])) {
            $buscar = '%' . $this->conexion->real_escape_string($filtros['buscar']) . '%';
            $condiciones[] = "(ubicacion LIKE '$buscar' OR direccion_ip LIKE '$buscar' OR estado LIKE '$buscar')";
        }

        if (!empty($filtros['estado'])) {
            $estado = $this->conexion->real_escape_string($filtros['estado']);
            $condiciones[] = "estado = '$estado'";
        }

        return $condiciones;
    }
}
?>
