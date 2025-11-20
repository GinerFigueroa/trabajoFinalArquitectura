<?php
include_once('../../../modelo/citasPacientesDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlCitas
{
    private $objCitas;
    private $objMensaje;

    public function __construct()
    {
        $this->objCitas = new CitasPacientesDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function cancelarCita($idCita, $idUsuario)
    {
        // Obtener id_paciente del usuario
        $idPaciente = $this->objCitas->obtenerIdPacientePorUsuario($idUsuario);
        
        if (!$idPaciente) {
            $this->objMensaje->mensajeSistemaShow("Error: No se pudo identificar al paciente.", "./indexCitas.php", "error");
            return;
        }

        // Verificar que la cita pertenece al paciente
        if (!$this->objCitas->verificarPropiedadCita($idCita, $idPaciente)) {
            $this->objMensaje->mensajeSistemaShow("No tiene permisos para cancelar esta cita.", "./indexCitas.php", "error");
            return;
        }

        $resultado = $this->objCitas->cancelarCita($idCita, $idPaciente);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Cita cancelada correctamente.", "./indexCitas.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al cancelar la cita. La cita no puede ser cancelada en su estado actual.", "./indexCitas.php", "error");
        }
    }
}
?>