<?php
include_once('../../../modelo/ConsentimientoInformadoDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlConsentimientoInformado
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new ConsentimientoInformadoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarConsentimiento($id)
    {
        if (empty($id) || !is_numeric($id)) {
            $this->objMensaje->mensajeSistemaShow("ID de consentimiento no válido.", "./indexConsentimientoInformado.php", "systemOut", false);
            return;
        }

        $resultado = $this->objDAO->eliminarConsentimiento($id);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Consentimiento Informado eliminado correctamente.", "./indexConsentimientoInformado.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al eliminar el consentimiento informado.", "./indexConsentimientoInformado.php", "error");
        }
    }
}
?>