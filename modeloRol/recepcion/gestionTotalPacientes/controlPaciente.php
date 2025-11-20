<?php
include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/PacienteDAO.php'); 

class controlPaciente
{
    private $objPacienteDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objPacienteDAO = new PacienteDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Intenta eliminar un paciente (solo si es recién registrado)
     */
    public function eliminarPaciente($idPaciente)
    {
        // Validaciones
        if (!is_numeric($idPaciente) || $idPaciente <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de paciente no válido.", "./indexTotalPaciente.php", "error");
            return;
        }

        // Intentar eliminar (solo si no tiene relaciones)
        $resultado = $this->objPacienteDAO->eliminarPacienteSiEsPosible($idPaciente);
        
        if ($resultado['success']) {
            $this->objMensaje->mensajeSistemaShow($resultado['message'], "./indexTotalPaciente.php", "success");
        } else {
            // Si no se puede eliminar, ofrecer desactivar
            $mensaje = $resultado['message'] . ". ¿Desea desactivarlo en su lugar?";
            echo "<script>
                if (confirm('" . $mensaje . "')) {
                    window.location.href = './getPaciente.php?action=desactivar&id=" . $idPaciente . "';
                } else {
                    window.location.href = './indexTotalPaciente.php';
                }
            </script>";
        }
    }
    
    /**
     * Desactiva un paciente
     */
    public function desactivarPaciente($idPaciente)
    {
        // Validaciones
        if (!is_numeric($idPaciente) || $idPaciente <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de paciente no válido.", "./indexTotalPaciente.php", "error");
            return;
        }

        // Ejecutar desactivación
        $resultado = $this->objPacienteDAO->desactivarPaciente($idPaciente);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Paciente desactivado correctamente.", "./indexTotalPaciente.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al desactivar el paciente.", "./indexTotalPaciente.php", "error");
        }
    }

    /**
     * Reactiva un paciente
     */
    public function reactivarPaciente($idPaciente)
    {
        // Validaciones
        if (!is_numeric($idPaciente) || $idPaciente <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de paciente no válido.", "./indexTotalPaciente.php", "error");
            return;
        }

        // Ejecutar reactivación
        $resultado = $this->objPacienteDAO->reactivarPaciente($idPaciente);
        
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Paciente reactivado correctamente.", "./indexTotalPaciente.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al reactivar el paciente.", "./indexTotalPaciente.php", "error");
        }
    }
}
?>