<?php
session_start();
include_once('../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

class controlDetalleCita
{
    private $objDetalle;
    private $objMensaje;

    public function __construct()
    {
        $this->objDetalle = new RecetaDetalleDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarDetalle($idDetalle)
    {
        // Verificar que el usuario sea médico
        if (!$this->validarUsuarioMedico()) {
            $this->objMensaje->mensajeSistemaShow('Solo los médicos pueden eliminar detalles.', '../../../index.php', 'error');
            return false;
        }

        if (empty($idDetalle) || !is_numeric($idDetalle)) {
            $this->objMensaje->mensajeSistemaShow("ID de detalle no válido.", "./indexDetalleCita.php", "error");
            return false;
        }

        // Verificar que el detalle existe y pertenece al médico
        if (!$this->objDetalle->validarPropiedadDetalle($idDetalle, $_SESSION['id_usuario'])) {
            $this->objMensaje->mensajeSistemaShow("No tiene permisos para eliminar este detalle.", "./indexDetalleCita.php", "error");
            return false;
        }

        $resultado = $this->objDetalle->eliminarDetalle($idDetalle);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("✅ Detalle de receta eliminado correctamente.", "./indexDetalleCita.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("❌ Error al eliminar el detalle de receta.", "./indexDetalleCita.php", "error");
        }
    }

    /**
     * Valida si el usuario logueado es médico
     */
    private function validarUsuarioMedico()
    {
        return isset($_SESSION['rol_id']) && $_SESSION['rol_id'] == 2 && isset($_SESSION['id_usuario']);
    }

    /**
     * Obtiene estadísticas de detalles
     */
    public function obtenerEstadisticas()
    {
        return [
            'total_detalles' => count($this->objDetalle->obtenerTodosDetalles()),
            'medicamentos_populares' => $this->objDetalle->obtenerMedicamentosMasRecetados(5)
        ];
    }
}
?>