<?php
session_start();

require_once(__DIR__ . '/../app/database/Database.php');
require_once(__DIR__ . '/../app/controller/MedicionController.php');
require_once(__DIR__ . '/../app/controller/Esp32Controller.php');
require_once(__DIR__ . '/../app/controller/AlertaController.php');
require_once(__DIR__ . '/../app/controller/AuthController.php');

$accion = null;
$id = $_GET['id'] ?? null;
$db = null;
$conexion = null;
$accion = $_GET['accion'] ?? 'home';

if ($accion === 'home') {
    require(__DIR__ . '/../app/view/home.php');
    exit;
}

try {
    $db = new Database();
    $conexion = $db->getConexion();
    $auth = new AuthController($conexion);

    switch ($accion) {
        case 'login':
            if ($auth->yaAutenticado()) {
                header('Location: index.php?accion=listar');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $correo = $_POST['correo'] ?? '';
                $password = $_POST['password'] ?? '';
                $auth->procesarLogin($correo, $password);
                exit;
            }

            $auth->mostrarLogin();
            break;

        case 'registro':
            if ($auth->yaAutenticado()) {
                header('Location: index.php?accion=listar');
                exit;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $nombre = $_POST['nombre'] ?? '';
                $correo = $_POST['correo'] ?? '';
                $password = $_POST['password'] ?? '';
                $confirmacion = $_POST['confirmacion'] ?? '';
                $auth->procesarRegistro($nombre, $correo, $password, $confirmacion);
                exit;
            }

            $auth->mostrarRegistro();
            break;

        case 'logout':
            $auth->cerrarSesion();
            break;

        case 'listar':
        case 'ver':
        case 'crear':
        case 'editar':
        case 'eliminar':
        case 'enviar-alertas':
            $auth->requireAuth();

            $controlador = new MedicionController($conexion);

            if ($accion === 'listar') {
                $controlador->listar();
            } elseif ($accion === 'ver') {
                if ($id) {
                    $controlador->obtener($id);
                } else {
                    $controlador->listar();
                }
            } elseif ($accion === 'crear' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controlador->crear();
            } elseif ($accion === 'editar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controlador->actualizar();
            } elseif ($accion === 'eliminar' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controlador->eliminar();
            } elseif ($accion === 'enviar-alertas') {
                $controlador->enviarAlertas();
            } else {
                header('Location: index.php?accion=listar');
                exit;
            }
            break;

        case 'dispositivos':
        case 'crear-dispositivo':
        case 'editar-dispositivo':
        case 'eliminar-dispositivo':
            $auth->requireAuth();

            $controladorEsp = new Esp32Controller($conexion);

            if ($accion === 'dispositivos') {
                $controladorEsp->listar();
            } elseif ($accion === 'crear-dispositivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controladorEsp->crear();
            } elseif ($accion === 'editar-dispositivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controladorEsp->actualizar();
            } elseif ($accion === 'eliminar-dispositivo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controladorEsp->eliminar();
            } else {
                header('Location: index.php?accion=dispositivos');
                exit;
            }
            break;

        case 'alertas':
        case 'crear-alerta':
        case 'editar-alerta':
        case 'eliminar-alerta':
            $auth->requireAuth();

            $controladorAlerta = new AlertaController($conexion);

            if ($accion === 'alertas') {
                $controladorAlerta->listar();
            } elseif ($accion === 'crear-alerta' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controladorAlerta->crear();
            } elseif ($accion === 'editar-alerta' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controladorAlerta->actualizar();
            } elseif ($accion === 'eliminar-alerta' && $_SERVER['REQUEST_METHOD'] === 'POST') {
                $controladorAlerta->eliminar();
            } else {
                header('Location: index.php?accion=alertas');
                exit;
            }
            break;

        default:
            header('Location: index.php?accion=login');
            exit;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    require(__DIR__ . '/../app/view/error.php');
} finally {
    if ($db instanceof Database) {
        $db->cerrar();
    }
}
?>

