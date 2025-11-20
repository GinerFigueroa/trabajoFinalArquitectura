<?php
// Archivo: controlDashboardBoletas.php

include_once('../../../modelo/dasboarBoletas.php');
include_once('../../../shared/mensajeSistema.php');

class controlDashboardBoletas
{
    private $objDashboard;
    private $objMensaje;

    public function __construct()
    {
        $this->objDashboard = new DashboardBoletaDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Procesa los filtros y muestra el dashboard
     */
    public function mostrarDashboard()
    {
        // Obtener parámetros de filtro
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');

        // Validar fechas
        if ($fechaInicio && $fechaFin && strtotime($fechaInicio) > strtotime($fechaFin)) {
            $this->objMensaje->mensajeSistemaShow(
                "La fecha de inicio no puede ser mayor a la fecha fin.",
                "./indexDashboardBoletas.php",
                "error"
            );
            return;
        }

        // Obtener datos para el dashboard
        $datos = [
            'estadisticas' => $this->objDashboard->obtenerEstadisticasGenerales($fechaInicio, $fechaFin),
            'ingresos_mensuales' => $this->objDashboard->obtenerIngresosPorMes($anio),
            'distribucion_tipo' => $this->objDashboard->obtenerDistribucionPorTipo($fechaInicio, $fechaFin),
            'distribucion_metodo_pago' => $this->objDashboard->obtenerDistribucionPorMetodoPago($fechaInicio, $fechaFin),
            'boletas_recientes' => $this->objDashboard->obtenerBoletasRecientes(10),
            'anios_disponibles' => $this->objDashboard->obtenerAniosDisponibles(),
            'tendencia' => $this->objDashboard->obtenerTendenciaIngresos(),
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'anio' => $anio
            ]
        ];

        // Mostrar vista
        include_once('./formDashboardBoletas.php');
        $objForm = new formDashboardBoletas();
        $objForm->formDashboardBoletasShow($datos);
    }
}
?>