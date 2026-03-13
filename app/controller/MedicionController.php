<?php
require_once(__DIR__ . '/../model/MedicionAmbiental.php');

class MedicionController {
    private $modelo;
    
    public function __construct($conexion) {
        $this->modelo = new MedicionAmbiental($conexion);
    }
    
    /**
     * Obtiene todas las mediciones y las pasa a la vista
     */
    public function listar() {
        $filtros = $this->obtenerFiltros();
        $mediciones = $this->modelo->obtenerMediciones($filtros);
        
        if ($mediciones === false) {
            $error = "Error al obtener los datos";
            require(__DIR__ . '/../view/error.php');
            return;
        }

        $datosGrafica = $this->prepararDatosGrafica($mediciones);
        
        require(__DIR__ . '/../view/mediciones.php');
    }

    /**
     * Genera series para la grafica en orden cronologico.
     */
    private function prepararDatosGrafica($mediciones) {
        $labels = [];
        $temperaturas = [];
        $humedades = [];
        $calidadAire = [];

        $ordenCronologico = array_reverse($mediciones);

        foreach ($ordenCronologico as $fila) {
            $timestamp = strtotime((string) ($fila['fecha_hora'] ?? ''));
            $labels[] = $timestamp ? date('d/m H:i', $timestamp) : (string) ($fila['fecha_hora'] ?? '');
            $temperaturas[] = isset($fila['temperatura']) ? (float) $fila['temperatura'] : null;
            $humedades[] = isset($fila['humedad']) ? (float) $fila['humedad'] : null;
            $calidadAire[] = isset($fila['calidad_aire']) ? (float) $fila['calidad_aire'] : null;
        }

        return [
            'labels' => $labels,
            'temperaturas' => $temperaturas,
            'humedades' => $humedades,
            'calidad_aire' => $calidadAire,
            'svg' => $this->construirGraficaSvg($labels, $temperaturas, $humedades, $calidadAire)
        ];
    }

    private function construirGraficaSvg($labels, $temperaturas, $humedades, $calidadAire) {
        if (empty($labels)) {
            return null;
        }

        $width = 980;
        $height = 320;
        $left = 56;
        $right = 56;
        $top = 24;
        $bottom = 42;
        $plotWidth = $width - $left - $right;
        $plotHeight = $height - $top - $bottom;
        $total = count($labels);

        $leftValues = array_merge($this->soloNumericos($temperaturas), $this->soloNumericos($humedades));
        $rightValues = $this->soloNumericos($calidadAire);

        list($leftMin, $leftMax) = $this->normalizarRango($leftValues);
        list($rightMin, $rightMax) = $this->normalizarRango($rightValues);

        $mapX = function ($i) use ($total, $left, $plotWidth) {
            if ($total <= 1) {
                return $left + ($plotWidth / 2);
            }
            return $left + (($i / ($total - 1)) * $plotWidth);
        };

        $mapY = function ($v, $min, $max) use ($top, $plotHeight) {
            if ($v === null || !is_numeric($v)) {
                return null;
            }
            $ratio = ((float) $v - $min) / ($max - $min);
            return $top + ($plotHeight * (1 - $ratio));
        };

        $grid = [];
        $steps = 5;
        for ($i = 0; $i <= $steps; $i++) {
            $y = $top + ($plotHeight * ($i / $steps));
            $leftValue = $leftMax - (($leftMax - $leftMin) * ($i / $steps));
            $rightValue = $rightMax - (($rightMax - $rightMin) * ($i / $steps));
            $grid[] = [
                'y' => round($y, 2),
                'left_label' => number_format($leftValue, 1),
                'right_label' => number_format($rightValue, 0)
            ];
        }

        $xLabels = [];
        $maxLabels = 6;
        $jump = max(1, (int) ceil($total / $maxLabels));
        for ($i = 0; $i < $total; $i += $jump) {
            $xLabels[] = [
                'x' => round($mapX($i), 2),
                'label' => (string) $labels[$i]
            ];
        }
        if (($total - 1) % $jump !== 0) {
            $xLabels[] = [
                'x' => round($mapX($total - 1), 2),
                'label' => (string) $labels[$total - 1]
            ];
        }

        return [
            'width' => $width,
            'height' => $height,
            'left' => $left,
            'right' => $right,
            'top' => $top,
            'bottom' => $bottom,
            'plot_right' => $width - $right,
            'plot_bottom' => $height - $bottom,
            'grid' => $grid,
            'x_labels' => $xLabels,
            'series' => [
                'temperatura' => $this->construirSerie($temperaturas, $mapX, $mapY, $leftMin, $leftMax),
                'humedad' => $this->construirSerie($humedades, $mapX, $mapY, $leftMin, $leftMax),
                'aire' => $this->construirSerie($calidadAire, $mapX, $mapY, $rightMin, $rightMax)
            ]
        ];
    }

    private function construirSerie($valores, $mapX, $mapY, $min, $max) {
        $linea = [];
        $puntos = [];

        foreach ($valores as $i => $valor) {
            $x = $mapX($i);
            $y = $mapY($valor, $min, $max);
            if ($y === null) {
                continue;
            }
            $linea[] = round($x, 2) . ',' . round($y, 2);
            $puntos[] = [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'value' => $valor
            ];
        }

        return [
            'line' => implode(' ', $linea),
            'points' => $puntos
        ];
    }

    private function soloNumericos($valores) {
        return array_values(array_filter($valores, function ($v) {
            return $v !== null && is_numeric($v);
        }));
    }

    private function normalizarRango($valores) {
        if (empty($valores)) {
            return [0.0, 1.0];
        }

        $min = (float) min($valores);
        $max = (float) max($valores);

        if ($min === $max) {
            $min -= 1;
            $max += 1;
        }

        return [$min, $max];
    }

    /**
     * Lee y valida filtros del query string para no mezclar logica en la vista.
     */
    private function obtenerFiltros() {
        $filtros = [
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'temperatura_min' => null,
            'temperatura_max' => null,
            'humedad_min' => null,
            'humedad_max' => null,
            'calidad_aire_min' => null,
            'calidad_aire_max' => null
        ];

        if (!empty($_GET['fecha_desde']) && $this->esFechaValida($_GET['fecha_desde'])) {
            $filtros['fecha_desde'] = $_GET['fecha_desde'];
        }

        if (!empty($_GET['fecha_hasta']) && $this->esFechaValida($_GET['fecha_hasta'])) {
            $filtros['fecha_hasta'] = $_GET['fecha_hasta'];
        }

        $mapaNumerico = [
            'temperatura_min',
            'temperatura_max',
            'humedad_min',
            'humedad_max',
            'calidad_aire_min',
            'calidad_aire_max'
        ];

        foreach ($mapaNumerico as $campo) {
            if (isset($_GET[$campo]) && $_GET[$campo] !== '' && is_numeric($_GET[$campo])) {
                $filtros[$campo] = (float) $_GET[$campo];
            }
        }

        return $filtros;
    }

    private function esFechaValida($valor) {
        $fecha = DateTime::createFromFormat('Y-m-d', (string) $valor);
        return $fecha && $fecha->format('Y-m-d') === $valor;
    }
    
    /**
     * Obtiene una medición específica por ID
     */
    public function obtener($id) {
        $medicion = $this->modelo->obtenerMedicionPorId($id);
        
        if (!$medicion) {
            $error = "Medición no encontrada";
            require(__DIR__ . '/../view/error.php');
            return;
        }
        
        require(__DIR__ . '/../view/medicion_detalle.php');
    }
    
    /**
     * Procesa la inserción de una nueva medición
     */
    public function crear($temperatura, $humedad, $calidad_aire) {
        if ($this->modelo->insertar($temperatura, $humedad, $calidad_aire)) {
            header("Location: index.php");
            exit;
        } else {
            $error = "Error al insertar la medición";
            require(__DIR__ . '/../view/error.php');
        }
    }
}
?>
