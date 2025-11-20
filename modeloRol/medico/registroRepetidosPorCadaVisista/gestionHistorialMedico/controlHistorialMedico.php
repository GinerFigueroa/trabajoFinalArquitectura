<?php


include_once('../../../../modelo/RegistroMedicoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlHistorialClinico
{
    private $objDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objDAO = new RegistroMedicoDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método para eliminar un registro médico
     */
    public function eliminarRegistro($id_registro)
    {
        // 1. Validar ID
        if (!is_numeric($id_registro) || $id_registro <= 0) {
            $this->objMensaje->mensajeSistemaShow(
                'ID de Registro no válido.', 
                './indexHistorialMedico.php', 
                'error'
            );
            return;
        }

        // 2. Ejecutar la eliminación
        $resultado = $this->objDAO->eliminarRegistro($id_registro);
        
        // 3. Manejo de resultado
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                'Registro médico eliminado correctamente.', 
                './indexHistorialMedico.php', 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al eliminar el registro médico. Podría no existir.', 
                './indexHistorialMedico.php', 
                'error'
            );
        }
    }
}
?>