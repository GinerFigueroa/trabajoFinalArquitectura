<?php
include_once('../../../../modelo/ConsentimientoInformadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlRegistrarConsentimientoInformado
{
    private $objDAO;
    private $objHC;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new ConsentimientoInformadoDAO();
        $this->objHC = new EntidadHistoriaClinica();
        $this->objMensaje = new mensajeSistema();
    }

    // Método para AJAX
    public function obtenerInfoPacientePorHC($idHC)
    {
        return $this->objHC->obtenerInfoPorHistoriaClinica($idHC);
    }
    
    public function registrarConsentimiento($historia_clinica_id, $id_paciente, $dr_tratante_id, $diagnostico, $tratamiento)
    {
        $urlRetorno = './indexRegistrarConsintimientoInformado.php';

        // 1. Sanitización y validación
        $historia_clinica_id = (int)$historia_clinica_id;
        $id_paciente = (int)$id_paciente;
        $dr_tratante_id = (int)$dr_tratante_id;

        if ($historia_clinica_id <= 0 || $id_paciente <= 0 || $dr_tratante_id <= 0 || empty($diagnostico) || empty($tratamiento)) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios o no son válidos (HC, Paciente, Doctor, Diagnóstico, Tratamiento).', $urlRetorno, 'systemOut', false);
            return;
        }
        
        // 2. Ejecutar el registro
        $resultado = $this->objDAO->registrarConsentimiento($historia_clinica_id, $id_paciente, $dr_tratante_id, $diagnostico, $tratamiento);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Consentimiento Informado registrado correctamente.', '../indexConsentimientoInformado.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al registrar el Consentimiento Informado.', $urlRetorno, 'error');
        }
    }
}
?>