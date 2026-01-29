<?php
$servidor = "localhost";
$usuario = "root";
$clave = "";
$baseDatos = "greengrid360";

$conexion = new mysqli($servidor, $usuario, $clave, $baseDatos);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

echo "Conexión exitosa";
?>
