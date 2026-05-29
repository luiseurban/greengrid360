<?php
require_once(__DIR__ . '/../model/Alerta.php');
require_once(__DIR__ . '/../model/Esp32.php');

class AlertaController {
    private $modelo;
    private $modeloEsp32;

    public function __construct($conexion) {
        $this->modelo = new Alerta($conexion);
        $this->modeloEsp32 = new Esp32($conexion);
    }

    public function listar() {
        $filtros = $this->obtenerFiltros();
        $porPagina = 15;
        $pagina = $this->obtenerPagina();

        $alertas = $this->modelo->obtenerAlertas($filtros, $porPagina, ($pagina - 1) * $porPagina);
        $total = $this->modelo->contarAlertas($filtros);
        $dispositivos = $this->modeloEsp32->obtenerDispositivos();

        if ($alertas === false) {
            $error = "Error al obtener las alertas";
            require(__DIR__ . '/../view/error.php');
            return;
        }

        $totalPaginas = (int)ceil($total / $porPagina);

        require(__DIR__ . '/../view/alertas.php');
    }

    public function crear() {
        $id_dispositivo = $_POST['id_dispositivo'] ?? '';
        $parametro = $_POST['parametro'] ?? '';
        $tipo_condicion = $_POST['tipo_condicion'] ?? '';
        $valor_umbral = $_POST['valor_umbral'] ?? '';
        $correo_destino = $_POST['correo_destino'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;

        $datos = [
            'id_dispositivo' => (int)$id_dispositivo,
            'parametro' => $parametro,
            'tipo_condicion' => $tipo_condicion,
            'valor_umbral' => (float)$valor_umbral,
            'correo_destino' => $correo_destino,
            'activo' => $activo
        ];

        if ($this->modelo->insertar($datos)) {
            $this->redirectToListar();
        } else {
            $error = "Error al insertar la alerta";
            require(__DIR__ . '/../view/error.php');
        }
    }

    public function actualizar() {
        $id = $_GET['id'] ?? null;

        if (!$id) {
            $this->redirectToListar();
            return;
        }

        $id_dispositivo = $_POST['id_dispositivo'] ?? '';
        $parametro = $_POST['parametro'] ?? '';
        $tipo_condicion = $_POST['tipo_condicion'] ?? '';
        $valor_umbral = $_POST['valor_umbral'] ?? '';
        $correo_destino = $_POST['correo_destino'] ?? '';
        $activo = isset($_POST['activo']) ? 1 : 0;

        $datos = [
            'id_dispositivo' => (int)$id_dispositivo,
            'parametro' => $parametro,
            'tipo_condicion' => $tipo_condicion,
            'valor_umbral' => (float)$valor_umbral,
            'correo_destino' => $correo_destino,
            'activo' => $activo
        ];

        if ($this->modelo->actualizar($id, $datos)) {
            $this->redirectToListar();
        } else {
            $error = "Error al actualizar la alerta";
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
            $error = "Error al eliminar la alerta";
            require(__DIR__ . '/../view/error.php');
        }
    }

    private function obtenerFiltros() {
        $filtros = [
            'buscar' => null,
            'id_dispositivo' => null,
            'activo' => null
        ];

        if (!empty($_GET['buscar'])) {
            $filtros['buscar'] = trim($_GET['buscar']);
        }

        if (!empty($_GET['id_dispositivo'])) {
            $filtros['id_dispositivo'] = $_GET['id_dispositivo'];
        }

        if (array_key_exists('activo', $_GET) && $_GET['activo'] !== '') {
            $filtros['activo'] = $_GET['activo'];
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
        $id_dispositivo = $_POST['filtro_id_dispositivo'] ?? $_GET['id_dispositivo'] ?? '';
        $activo = array_key_exists('filtro_activo', $_POST) ? $_POST['filtro_activo'] : null;
        if ($activo === null) {
            $activo = array_key_exists('activo', $_GET) ? $_GET['activo'] : null;
        }
        $pagina = $_POST['pagina'] ?? $_GET['pagina'] ?? '';

        if ($buscar !== '') {
            $params['buscar'] = $buscar;
        }
        if ($id_dispositivo !== '') {
            $params['id_dispositivo'] = $id_dispositivo;
        }
        if ($activo !== null && $activo !== '') {
            $params['activo'] = $activo;
        }
        if ($pagina !== '') {
            $params['pagina'] = $pagina;
        }

        if (empty($params)) {
            header('Location: index.php?accion=alertas');
        } else {
            $params['accion'] = 'alertas';
            header('Location: index.php?' . http_build_query($params));
        }
        exit;
    }
}
