<?php
// Archivo: controlDashboardBoletas.php

// Inclusión de dependencias (Modelos y Componentes Compartidos)
include_once('../../../modelo/dasboarBoletas.php'); 
include_once('../../../shared/mensajeSistema.php');

/**
 * Patrón: CONTROLLER (MVC) ⚙️
 * Esta clase actúa como el punto de control entre la entrada de datos (filtros GET)
 * la obtención de datos (DAO/Modelo) y la presentación (Vista).
 * * Patrón: FACADE / SERVICE LAYER 🏛️
 * Centraliza y simplifica el acceso a la lógica de obtención de múltiples datos
 * del DAO para el dashboard, preparando un único objeto 'datos' para la vista.
 */
class controlDashboardBoletas
{
    // Atributo: $objDashboard
    // Patrón: DEPENDENCY INJECTION (Inicializado en el constructor)
    private $objDashboard; 
    
    // Atributo: $objMensaje
    // Patrón: DEPENDENCY INJECTION (Dependencia para manejar la comunicación de la interfaz de usuario)
    private $objMensaje; 

    /**
     * Método: __construct (Constructor)
     * Patrón: DEPENDENCY INJECTION 💉
     * Inicializa las dependencias (DAO y Mensajería).
     */
    public function __construct()
    {
        // Instanciación de la dependencia del Modelo/Receptor (DAO)
        $this->objDashboard = new DashboardBoletaDAO();
        // Instanciación de la dependencia de Mensajería
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: mostrarDashboard
     * Procesa los filtros, orquesta la obtención de datos y delega la visualización.
     * Patrón: CONTROLLER ACTION 🎬
     * Patrón: STATE / GUARD CLAUSE 🛡️
     */
    public function mostrarDashboard()
    {
        // Obtener parámetros de filtro (Recolección de datos de la petición)
        $fechaInicio = $_GET['fecha_inicio'] ?? null;
        $fechaFin = $_GET['fecha_fin'] ?? null;
        $anio = $_GET['anio'] ?? date('Y');

        // Validar fechas
        if ($fechaInicio && $fechaFin && strtotime($fechaInicio) > strtotime($fechaFin)) {
            // Patrón STATE: Si el estado de las fechas es inválido, detiene el flujo y muestra un mensaje.
            $this->objMensaje->mensajeSistemaShow(
                "La fecha de inicio no puede ser mayor a la fecha fin.",
                "./indexDashboardBoletas.php",
                "error"
            );
            return;
        }

        // Obtener datos para el dashboard (Uso del Patrón FACADE para centralizar las llamadas al DAO)
        $datos = [
            'estadisticas' => $this->objDashboard->obtenerEstadisticasGenerales($fechaInicio, $fechaFin), // Método: obtenerEstadisticasGenerales (del DAO)
            'ingresos_mensuales' => $this->objDashboard->obtenerIngresosPorMes($anio), // Método: obtenerIngresosPorMes (del DAO)
            'distribucion_tipo' => $this->objDashboard->obtenerDistribucionPorTipo($fechaInicio, $fechaFin), // Método: obtenerDistribucionPorTipo (del DAO)
            'distribucion_metodo_pago' => $this->objDashboard->obtenerDistribucionPorMetodoPago($fechaInicio, $fechaFin), // Método: obtenerDistribucionPorMetodoPago (del DAO)
            'boletas_recientes' => $this->objDashboard->obtenerBoletasRecientes(10), // Método: obtenerBoletasRecientes (del DAO)
            'anios_disponibles' => $this->objDashboard->obtenerAniosDisponibles(), // Método: obtenerAniosDisponibles (del DAO)
            'tendencia' => $this->objDashboard->obtenerTendenciaIngresos(), // Método: obtenerTendenciaIngresos (del DAO)
            'filtros' => [
                'fecha_inicio' => $fechaInicio,
                'fecha_fin' => $fechaFin,
                'anio' => $anio
            ]
        ];

        // Mostrar vista (Patrón: CONTROLLER - Delegación final a la Vista)
        include_once('./formDashboardBoletas.php');
        $objForm = new formDashboardBoletas();
        $objForm->formDashboardBoletasShow($datos); // Método: formDashboardBoletasShow (de la Vista)
    }
}
?>