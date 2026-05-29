<?php

class Alerta {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function obtenerAlertas($filtros = [], $limite = null, $offset = 0) {
        $sql = "SELECT a.id_alerta, a.id_dispositivo, a.parametro, a.tipo_condicion,
                       a.valor_umbral, a.correo_destino, a.activo, a.fecha_creacion,
                       e.ubicacion, e.estado
                FROM alertas a
                INNER JOIN esp32 e ON a.id_dispositivo = e.id_dispositivo";

        $condiciones = $this->construirCondiciones($filtros);

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $sql .= " ORDER BY a.id_alerta ASC";

        if ($limite !== null) {
            $sql .= " LIMIT " . (int)$limite . " OFFSET " . (int)$offset;
        }

        $resultado = $this->conexion->query($sql);

        if (!$resultado) {
            return false;
        }

        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function contarAlertas($filtros = []) {
        $sql = "SELECT COUNT(*) as total FROM alertas a
                INNER JOIN esp32 e ON a.id_dispositivo = e.id_dispositivo";

        $condiciones = $this->construirCondiciones($filtros);

        if (!empty($condiciones)) {
            $sql .= " WHERE " . implode(" AND ", $condiciones);
        }

        $resultado = $this->conexion->query($sql);
        $fila = $resultado->fetch_assoc();
        return (int)$fila['total'];
    }

    public function obtenerPorId($id) {
        $sql = "SELECT id_alerta, id_dispositivo, parametro, tipo_condicion,
                       valor_umbral, correo_destino, activo
                FROM alertas WHERE id_alerta = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_assoc();
    }

    public function insertar($datos) {
        $sql = "INSERT INTO alertas (id_dispositivo, parametro, tipo_condicion,
                valor_umbral, correo_destino, activo) VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("issdsi",
            $datos['id_dispositivo'],
            $datos['parametro'],
            $datos['tipo_condicion'],
            $datos['valor_umbral'],
            $datos['correo_destino'],
            $datos['activo']
        );

        return $stmt->execute();
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE alertas SET id_dispositivo = ?, parametro = ?, tipo_condicion = ?,
                valor_umbral = ?, correo_destino = ?, activo = ? WHERE id_alerta = ?";

        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("issdsii",
            $datos['id_dispositivo'],
            $datos['parametro'],
            $datos['tipo_condicion'],
            $datos['valor_umbral'],
            $datos['correo_destino'],
            $datos['activo'],
            $id
        );

        return $stmt->execute();
    }

    public function eliminar($id) {
        $sql = "DELETE FROM alertas WHERE id_alerta = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function obtenerPorDispositivo($id_dispositivo) {
        $sql = "SELECT id_alerta, parametro, tipo_condicion, valor_umbral, correo_destino, activo,
                       fecha_ultimo_envio
                FROM alertas WHERE id_dispositivo = ? AND activo = 1";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_dispositivo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        return $resultado->fetch_all(MYSQLI_ASSOC);
    }

    public function puedeEnviar($id_alerta, $cooldownMinutos = 15) {
        $sql = "SELECT CASE
                    WHEN fecha_ultimo_envio IS NULL THEN 1
                    WHEN TIMESTAMPDIFF(MINUTE, fecha_ultimo_envio, NOW()) >= ? THEN 1
                    ELSE 0
                END as puede
                FROM alertas WHERE id_alerta = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("ii", $cooldownMinutos, $id_alerta);
        $stmt->execute();
        $fila = $stmt->get_result()->fetch_assoc();
        return (bool)($fila['puede'] ?? false);
    }

    public function registrarEnvio($id_alerta) {
        $sql = "UPDATE alertas SET fecha_ultimo_envio = NOW() WHERE id_alerta = ?";
        $stmt = $this->conexion->prepare($sql);
        $stmt->bind_param("i", $id_alerta);
        return $stmt->execute();
    }

    private function construirCondiciones($filtros) {
        $condiciones = [];

        if (!empty($filtros['buscar'])) {
            $buscar = '%' . $this->conexion->real_escape_string($filtros['buscar']) . '%';
            $condiciones[] = "(e.ubicacion LIKE '$buscar'
                           OR a.correo_destino LIKE '$buscar'
                           OR a.parametro LIKE '$buscar')";
        }

        if (!empty($filtros['id_dispositivo'])) {
            $id = (int)$filtros['id_dispositivo'];
            $condiciones[] = "a.id_dispositivo = $id";
        }

        if (array_key_exists('activo', $filtros) && $filtros['activo'] !== '' && $filtros['activo'] !== null) {
            $activo = (int)$filtros['activo'];
            $condiciones[] = "a.activo = $activo";
        }

        return $condiciones;
    }
}
