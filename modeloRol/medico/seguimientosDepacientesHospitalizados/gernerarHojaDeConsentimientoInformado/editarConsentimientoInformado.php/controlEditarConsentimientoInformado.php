<?php
include_once('../../../../modelo/ConsentimientoInformadoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarConsentimientoInformado
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new ConsentimientoInformadoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function editarConsentimiento($id, $diagnostico, $tratamiento)
    {
        $urlRetorno = './indexEditarConsentimientoInformado.php?id=' . $id;

        // 1. Validación de campos obligatorios
        if (empty($id) || empty($diagnostico) || empty($tratamiento)) {
            $this->objMensaje->mensajeSistemaShow('Faltan campos obligatorios.', $urlRetorno, 'systemOut', false);
            return;
        }

        // 2. Ejecutar la edición
        $resultado = $this->objDAO->editarConsentimiento($id, $diagnostico, $tratamiento);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Consentimiento N° ' . $id . ' actualizado correctamente.', '../indexConsentimientoInformado.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al editar el consentimiento. Verifique que se hayan realizado cambios.', $urlRetorno, 'error');
        }
    }
}
?>