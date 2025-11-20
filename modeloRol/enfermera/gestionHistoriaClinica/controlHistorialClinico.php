<?php

include_once('../../../modelo/HistoriaClinicaDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

class controlHistorialClinico
{
    private $objHistoriaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objHistoriaDAO = new HistoriaClinicaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    public function eliminarHistoria($idHistoria, $idMedicoLogueado)
    {
        $rutaRetorno = "./indexHistoriaClinica.php";
        
        // 1. Verificar si la historia existe y si pertenece al médico logueado
        $historia = $this->objHistoriaDAO->obtenerHistoriaPorId($idHistoria);

        if (!$historia) {
             $this->objMensaje->mensajeSistemaShow("Error: La Historia Clínica con ID **{$idHistoria}** no existe.", $rutaRetorno, "error");
            return;
        }
        
        // El médico solo puede eliminar sus propias historias
        if ($historia['dr_tratante_id'] != $idMedicoLogueado) {
             $this->objMensaje->mensajeSistemaShow("Acceso Denegado: No tiene permisos para eliminar la historia clínica de otro médico.", $rutaRetorno, "error");
            return;
        }

        // 2. Ejecutar la eliminación (debe ser una eliminación en cascada en el DAO o en la DB)
        $resultado = $this->objHistoriaDAO->eliminarHistoria($idHistoria);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Historia Clínica (ID: {$idHistoria}) eliminada correctamente, junto con sus registros asociados.", $rutaRetorno, "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la historia clínica. Fallo en la base de datos.", $rutaRetorno, "error");
        }
    }
}
?>