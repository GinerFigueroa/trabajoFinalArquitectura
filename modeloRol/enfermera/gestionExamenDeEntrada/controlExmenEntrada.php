<?php

include_once('../../../modelo/ExamenClinicoDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

class controlExmenEntrada
{
    private $objExamenDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objExamenDAO = new ExamenClinicoDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    public function eliminarExamen($examenId)
    {
        $rutaRetorno = "./indexExamenEntrada.php";
        
        // **Validación de Existencia**
        if (!$this->objExamenDAO->obtenerExamenPorId($examenId)) {
             $this->objMensaje->mensajeSistemaShow("Error: El Examen Clínico con ID **{$examenId}** no existe.", $rutaRetorno, "error");
            return;
        }

        $resultado = $this->objExamenDAO->eliminarExamen($examenId);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Examen Clínico eliminado correctamente.", $rutaRetorno, "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar el Examen Clínico. Puede haber restricciones de integridad.", $rutaRetorno, "error");
        }
    }
}
?>