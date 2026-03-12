<?php

class Usuario {
    public function __construct() {
        if (!isset($_SESSION['usuarios_locales']) || !is_array($_SESSION['usuarios_locales'])) {
            $_SESSION['usuarios_locales'] = [];
        }

        // Usuario base para pruebas locales en localhost.
        if (empty($_SESSION['usuarios_locales'])) {
            $_SESSION['usuarios_locales'][] = [
                'nombre' => 'Administrador',
                'correo' => 'admin@localhost',
                'password_hash' => password_hash('admin123', PASSWORD_DEFAULT),
                'creado_en' => date('Y-m-d H:i:s')
            ];
        }
    }

    public function registrar($nombre, $correo, $password) {
        foreach ($_SESSION['usuarios_locales'] as $usuario) {
            if (strcasecmp($usuario['correo'], $correo) === 0) {
                return false;
            }
        }

        $_SESSION['usuarios_locales'][] = [
            'nombre' => $nombre,
            'correo' => $correo,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'creado_en' => date('Y-m-d H:i:s')
        ];

        // Futuro: reemplazar almacenamiento en sesion por insercion en tabla `usuarios`.
        return true;
    }

    public function autenticar($correo, $password) {
        foreach ($_SESSION['usuarios_locales'] as $usuario) {
            if (strcasecmp($usuario['correo'], $correo) === 0 && password_verify($password, $usuario['password_hash'])) {
                return [
                    'nombre' => $usuario['nombre'],
                    'correo' => $usuario['correo']
                ];
            }
        }

        // Futuro: autenticar contra tabla `usuarios` en base de datos.
        return null;
    }
}
?>
