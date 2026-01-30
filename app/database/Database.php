<?php

class Database {
    private $servidor = "localhost";
    private $usuario = "root";
    private $clave = "";
    private $baseDatos = "greengrid360";
    private $conexion;
    
    public function __construct() {
        $this->conexion = new mysqli(
            $this->servidor,
            $this->usuario,
            $this->clave,
            $this->baseDatos
        );
        
        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }
    }
    
    /**
     * Obtiene la conexión
     */
    public function getConexion() {
        return $this->conexion;
    }
    
    /**
     * Cierra la conexión
     */
    public function cerrar() {
        $this->conexion->close();
    }
}
?>
