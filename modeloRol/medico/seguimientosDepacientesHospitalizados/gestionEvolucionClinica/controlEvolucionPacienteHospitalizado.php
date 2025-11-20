<?php
include_once('../../../../modelo/InternadoSeguimientoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEvolucionPacienteHospitalizado
{
    private $objSeguimientoDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function eliminarSeguimiento($idSeguimiento)
    {
        $resultado = $this->objSeguimientoDAO->eliminarSeguimiento($idSeguimiento);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Registro de evolución eliminado correctamente.', './indexEvolucionClinicaPacienteHospitalizado.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al eliminar el registro de evolución.', './indexEvolucionClinicaPacienteHospitalizado.php', 'error');
        }
    }
}
?>