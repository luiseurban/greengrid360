<?php
require_once(__DIR__ . '/../model/Esp32.php');

class Esp32Controller {
    private $modelo;

    public function __construct($conexion) {
        $this->modelo = new Esp32($conexion);
    }

    public function listar() {
        $filtros = $this->obtenerFiltros();
        $porPagina = 15;
        $pagina = $this->obtenerPagina();

        $dispositivos = $this->modelo->obtenerDispositivos($filtros, $porPagina, ($pagina - 1) * $porPagina);
        $total = $this->modelo->contarDispositivos($filtros);
        $estados = $this->modelo->obtenerEstados();

        if ($dispositivos === false) {
            $error = "Error al obtener los dispositivos";
            require(__DIR__ . '/../view/error.php');
            return;
        }

        $totalPaginas = (int)ceil($total / $porPagina);

        require(__DIR__ . '/../view/dispositivos.php');
    }

    public function crear() {
        $ubicacion = $_POST['ubicacion'] ?? '';
        $direccion_ip = $_POST['direccion_ip'] ?? '';
        $estado = $_POST['estado'] ?? '';

        $datos = [
            'ubicacion' => $ubicacion,
            'direccion_ip' => $direccion_ip,
            'estado' => $estado
        ];

        if ($this->modelo->insertar($datos)) {
            $this->redirectToListar();
        } else {
            $error = "Error al insertar el dispositivo";
            require(__DIR__ . '/../view/error.php');
        }
    }

    public function actualizar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->redirectToListar();
            return;
        }

        $ubicacion = $_POST['ubicacion'] ?? '';
        $direccion_ip = $_POST['direccion_ip'] ?? '';
        $estado = $_POST['estado'] ?? '';

        $datos = [
            'ubicacion' => $ubicacion,
            'direccion_ip' => $direccion_ip,
            'estado' => $estado
        ];

        if ($this->modelo->actualizar($id, $datos)) {
            $this->redirectToListar();
        } else {
            $error = "Error al actualizar el dispositivo";
            require(__DIR__ . '/../view/error.php');
        }
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->redirectToListar();
            return;
        }

        if ($this->modelo->eliminar($id)) {
            $this->redirectToListar();
        } else {
            $error = "Error al eliminar el dispositivo";
            require(__DIR__ . '/../view/error.php');
        }
    }

    private function obtenerFiltros() {
        $filtros = [
            'buscar' => null,
            'estado' => null
        ];

        if (!empty($_GET['buscar'])) {
            $filtros['buscar'] = trim($_GET['buscar']);
        }

        if (!empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }

        return $filtros;
    }

    private function obtenerPagina() {
        $pagina = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        return max(1, $pagina);
    }

    private function redirectToListar() {
        $params = [];

        $buscar = $_POST['filtro_buscar'] ?? $_GET['buscar'] ?? '';
        $estado = $_POST['filtro_estado'] ?? $_GET['estado'] ?? '';
        $pagina = $_POST['pagina'] ?? $_GET['pagina'] ?? '';

        if ($buscar !== '') {
            $params['buscar'] = $buscar;
        }
        if ($estado !== '') {
            $params['estado'] = $estado;
        }
        if ($pagina !== '') {
            $params['pagina'] = $pagina;
        }

        if (empty($params)) {
            header('Location: index.php?accion=dispositivos');
        } else {
            $params['accion'] = 'dispositivos';
            header('Location: index.php?' . http_build_query($params));
        }
        exit;
    }
}
?>
