<?php

class Usuario {
    private $conexion;

    public function __construct($conexion) {
        $this->conexion = $conexion;
    }

    public function registrar($nombre, $correo, $password) {
        $sqlExiste = "SELECT id_usuario FROM usuarios WHERE LOWER(correo) = LOWER(?) LIMIT 1";
        $stmtExiste = $this->conexion->prepare($sqlExiste);

        if (!$stmtExiste) {
            return false;
        }

        $stmtExiste->bind_param("s", $correo);
        $stmtExiste->execute();
        $resultadoExiste = $stmtExiste->get_result();

        if ($resultadoExiste && $resultadoExiste->num_rows > 0) {
            return false;
        }

        $passwordHash = password_hash($password, PASSWORD_DEFAULT);

        $sqlInsertar = "INSERT INTO usuarios (nombre, correo, `contraseña`, fecha_registro) VALUES (?, ?, ?, NOW())";
        $stmtInsertar = $this->conexion->prepare($sqlInsertar);

        if (!$stmtInsertar) {
            return false;
        }

        $stmtInsertar->bind_param("sss", $nombre, $correo, $passwordHash);

        return $stmtInsertar->execute();
    }

    public function autenticar($correo, $password) {
        $sql = "SELECT nombre, correo, `contraseña` FROM usuarios WHERE LOWER(correo) = LOWER(?) LIMIT 1";
        $stmt = $this->conexion->prepare($sql);

        if (!$stmt) {
            return null;
        }

        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado ? $resultado->fetch_assoc() : null;

        if (!$usuario) {
            return null;
        }

        if (!password_verify($password, $usuario['contraseña'])) {
            return null;
        }

        return [
            'nombre' => $usuario['nombre'],
            'correo' => $usuario['correo']
        ];
    }
}
?>
