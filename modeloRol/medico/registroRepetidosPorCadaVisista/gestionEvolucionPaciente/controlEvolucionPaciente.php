<?php
// controlEvolucionPaciente.php
include_once('../../../../modelo/EvolucionPacienteDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEvolucionPaciente
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new EvolucionPacienteDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método para eliminar una evolución médica
     */
    public function eliminarEvolucion($id_evolucion)
    {
        // 1. Validar ID
        if (!is_numeric($id_evolucion) || $id_evolucion <= 0) {
            $this->objMensaje->mensajeSistemaShow(
                'ID de Evolución no válido.', 
                './indexEvolucionPaciente.php', 
                'error'
            );
            return;
        }

        // 2. Ejecutar la eliminación
        $resultado = $this->objDAO->eliminarEvolucion($id_evolucion);
        
        // 3. Manejo de resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Evolución médica eliminada correctamente.', 
                './indexEvolucionPaciente.php', 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al eliminar la evolución médica. Podría no existir.', 
                './indexEvolucionPaciente.php', 
                'error'
            );
        }
    }

    /**
     * Método para registrar nueva evolución (si lo necesitas aquí también)
     */
    public function registrarEvolucion($data)
    {
        // ... (tu código existente para registrar)
    }
}
?>