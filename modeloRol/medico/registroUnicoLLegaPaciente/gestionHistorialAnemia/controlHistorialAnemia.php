<?php
include_once('../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlHistorialAnemia
{
    private $objHistorial;
    private $objMensaje;

    public function __construct()
    {
        $this->objHistorial = new HistorialAnemiaPacienteDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarHistorial($idHistorial)
    {
        if (empty($idHistorial) || !is_numeric($idHistorial)) {
            $this->objMensaje->mensajeSistemaShow("ID de historial no válido.", "./indexHistorialAnemia.php", "error");
            return;
        }

        // Verificar que el historial existe
        $historial = $this->objHistorial->obtenerHistorialPorId($idHistorial);
        if (!$historial) {
            $this->objMensaje->mensajeSistemaShow("El historial no existe o ya fue eliminado.", "./indexHistorialAnemia.php", "error");
            return;
        }

        $resultado = $this->objHistorial->eliminarHistorial($idHistorial);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "Historial de anemia eliminado correctamente.", 
                "./indexHistorialAnemia.php", 
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al eliminar el historial de anemia.", 
                "./indexHistorialAnemia.php", 
                "error"
            );
        }
    }

    public function buscarHistoriales($termino)
    {
        if (empty($termino)) {
            $this->objMensaje->mensajeSistemaShow("Término de búsqueda vacío.", "./indexHistorialAnemia.php", "error");
            return;
        }

        $historiales = $this->objHistorial->buscarHistorialesPorPaciente($termino);
        
        // Mostrar resultados en una vista especial o redirigir con parámetros
        include_once('./formHistorialAnemia.php');
        $objForm = new formHistorialAnemia();
        
        // Podrías modificar formHistorialAnemia para aceptar resultados de búsqueda
        // Por simplicidad, aquí redirigimos mostrando un mensaje
        if (count($historiales) > 0) {
            $this->objMensaje->mensajeSistemaShow(
                "Se encontraron " . count($historiales) . " resultados para: " . htmlspecialchars($termino), 
                "./indexHistorialAnemia.php", 
                "info"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "No se encontraron resultados para: " . htmlspecialchars($termino), 
                "./indexHistorialAnemia.php", 
                "warning"
            );
        }
    }

    /**
     * Método para obtener estadísticas (puede ser usado por otros componentes)
     */
    public function obtenerEstadisticas()
    {
        return $this->objHistorial->obtenerEstadisticasFactoresRiesgo();
    }

    /**
     * Método para verificar existencia de historial por historia clínica
     */
    public function existeHistorialParaHistoriaClinica($historiaClinicaId)
    {
        return $this->objHistorial->existeHistorialParaHistoriaClinica($historiaClinicaId);
    }
}