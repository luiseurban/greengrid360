<?php
session_start();

require_once(__DIR__ . '/../app/database/Database.php');
require_once(__DIR__ . '/../app/controller/MedicionController.php');
require_once(__DIR__ . '/../app/controller/AuthController.php');

$accion = null;
$id = $_GET['id'] ?? null;
$db = null;
$conexion = null;

try {
    $db = new Database();
    $conexion = $db->getConexion();
    $auth = new AuthController($conexion);
    $accion = $_GET['accion'] ?? ($auth->yaAutenticado() ? 'listar' : 'login');

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
                $temperatura = $_POST['temperatura'] ?? null;
                $humedad = $_POST['humedad'] ?? null;
                $calidad_aire = $_POST['calidad_aire'] ?? null;

                $controlador->crear($temperatura, $humedad, $calidad_aire);
            } else {
                header('Location: index.php?accion=listar');
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

