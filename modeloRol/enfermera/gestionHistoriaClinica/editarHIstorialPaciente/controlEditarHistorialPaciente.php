<?php
// Fichero: gestionHistoriaClinica/editarHistorialPaciente/controlEditarHistorialPaciente.php

include_once('../../../../modelo/HistoriaClinicaDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarHistorialPaciente
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new HistoriaClinicaDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function obtenerHistoriaParaEdicion($historiaClinicaId)
    {
        return $this->objDAO->obtenerHistoriaPorId($historiaClinicaId);
    }
    
    public function editarHistoria($datos)
    {
        $hcId = (int)($datos['historia_clinica_id'] ?? 0);
        $idPac = (int)($datos['id_paciente'] ?? 0); 
        $drId = (int)($datos['dr_tratante_id'] ?? 0);
        $fecha = $datos['fecha_creacion'] ?? date('Y-m-d');
        
        if (empty($hcId) || empty($idPac) || empty($drId)) {
            $this->objMensaje->mensajeSistemaShow("Faltan datos obligatorios para editar la Historia Clínica.", 
                "../indexHistoriaClinica.php", 'error');
            return;
        }

        $resultado = $this->objDAO->editarHistoria($hcId, $idPac, $drId, $fecha);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Historia Clínica N° $hcId actualizada correctamente.", 
                '../indexHistoriaClinica.php', 'success');
        } else {
            // El mensaje de error ahora es más genérico, ya que el select previene el error 24.
            $this->objMensaje->mensajeSistemaShow("Error al actualizar la Historia Clínica o no se detectaron cambios.", 
                "./indexEditarHistorialPaciente.php?id=" . $hcId, 'error');
        }
    }
}
?>