<?php
// Punto de entrada de la aplicación
require_once(__DIR__ . '/../app/database/Database.php');
require_once(__DIR__ . '/../app/controller/MedicionController.php');

// Crear instancia de la base de datos
$db = new Database();
$conexion = $db->getConexion();

// Crear instancia del controlador
$controlador = new MedicionController($conexion);

// Determinar qué acción ejecutar
$accion = $_GET['accion'] ?? 'listar';
$id = $_GET['id'] ?? null;

try {
    switch ($accion) {
        case 'listar':
            $controlador->listar();
            break;
        case 'ver':
            if ($id) {
                $controlador->obtener($id);
            } else {
                $controlador->listar();
            }
            break;
        case 'crear':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $temperatura = $_POST['temperatura'] ?? null;
                $humedad = $_POST['humedad'] ?? null;
                $calidad_aire = $_POST['calidad_aire'] ?? null;
                
                $controlador->crear($temperatura, $humedad, $calidad_aire);
            }
            break;
        default:
            $controlador->listar();
    }
} catch (Exception $e) {
    $error = $e->getMessage();
    require(__DIR__ . '/../app/view/error.php');
}

$db->cerrar();
?>

