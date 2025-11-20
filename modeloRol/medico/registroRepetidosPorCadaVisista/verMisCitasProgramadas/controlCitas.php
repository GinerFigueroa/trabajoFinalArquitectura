<?php
include_once('../../../../../../modelo/misCitasDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

class controlCitas
{
    private $objCitas;
    private $objMensaje;

    public function __construct()
    {
        $this->objCitas = new MisCitasDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function confirmarCita($idCita)
    {
        $resultado = $this->objCitas->actualizarEstadoCita($idCita, 'Confirmada');
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Cita confirmada correctamente.", "./indexCita.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al confirmar la cita.", "./indexCita.php", "error");
        }
    }

    public function cancelarCita($idCita)
    {
        $resultado = $this->objCitas->actualizarEstadoCita($idCita, 'Cancelada');
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Cita cancelada correctamente.", "./indexCita.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al cancelar la cita.", "./indexCita.php", "error");
        }
    }
}
?>