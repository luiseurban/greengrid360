<?php
require_once(__DIR__ . '/../model/Usuario.php');

class AuthController {
    private $modeloUsuario;

    public function __construct($conexion) {
        $this->modeloUsuario = new Usuario($conexion);
    }

    public function mostrarLogin($error = '', $mensaje = '') {
        require(__DIR__ . '/../view/login.php');
    }

    public function procesarLogin($correo, $password) {
        $correo = trim((string) $correo);
        $password = (string) $password;

        if ($correo === '' || $password === '') {
            $this->mostrarLogin('Completa correo y contrasena.');
            return;
        }

        $usuario = $this->modeloUsuario->autenticar($correo, $password);

        if (!$usuario) {
            $this->mostrarLogin('Credenciales invalidas.');
            return;
        }

        $_SESSION['usuario'] = $usuario;
        header('Location: index.php?accion=listar');
        exit;
    }

    public function mostrarRegistro($error = '', $mensaje = '') {
        require(__DIR__ . '/../view/registro.php');
    }

    public function procesarRegistro($nombre, $correo, $password, $confirmacion) {
        $nombre = trim((string) $nombre);
        $correo = trim((string) $correo);
        $password = (string) $password;
        $confirmacion = (string) $confirmacion;

        if ($nombre === '' || $correo === '' || $password === '' || $confirmacion === '') {
            $this->mostrarRegistro('Completa todos los campos.');
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            $this->mostrarRegistro('Ingresa un correo valido.');
            return;
        }

        if (strlen($password) < 6) {
            $this->mostrarRegistro('La contrasena debe tener al menos 6 caracteres.');
            return;
        }

        if ($password !== $confirmacion) {
            $this->mostrarRegistro('Las contrasenas no coinciden.');
            return;
        }

        $registrado = $this->modeloUsuario->registrar($nombre, $correo, $password);

        if (!$registrado) {
            $this->mostrarRegistro('El correo ya esta registrado.');
            return;
        }

        $this->mostrarLogin('', 'Registro completado. Ahora puedes iniciar sesion.');
    }

    public function cerrarSesion() {
        unset($_SESSION['usuario']);
        header('Location: index.php?accion=login');
        exit;
    }

    public function requireAuth() {
        if (!isset($_SESSION['usuario'])) {
            header('Location: index.php?accion=login');
            exit;
        }
    }

    public function yaAutenticado() {
        return isset($_SESSION['usuario']);
    }
}
?>
