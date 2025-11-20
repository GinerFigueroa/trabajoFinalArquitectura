<?php
// C:\...\gestionTipoDeTratamientoCosto\controlTratamiento.php
include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/TratamientoDAO.php'); 

class controlTratamiento // MEDIATOR
{
    private $objTratamientoDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objTratamientoDAO = new TratamientoDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Elimina un registro de tratamiento (COMMAND).
     */
    public function eliminarTratamiento($idTratamiento)
    {
        // 1. CHAIN OF RESPONSIBILITY (Validaciones)
        if (!is_numeric($idTratamiento) || $idTratamiento <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de Tratamiento no válido.", "./indexTipoTratamiento.php", "error");
            return;
        }
        
        // Opcional: Validar si el tratamiento está en uso (ej. en citas o internados)

        // 2. Ejecución del COMMAND (Delegación al DAO)
        $resultado = $this->objTratamientoDAO->eliminarTratamiento($idTratamiento); 
        
        // 3. Manejo de Respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Tratamiento ID $idTratamiento eliminado correctamente.", "./indexTipoTratamiento.php", "success");
        } else {
            // Esto puede ocurrir si el tratamiento no existe o si está siendo referenciado por otra tabla (FOREIGN KEY).
            $this->objMensaje->mensajeSistemaShow("Error al eliminar el Tratamiento. Puede estar siendo utilizado en un registro activo.", "./indexTipoTratamiento.php", "error");
        }
    }
}
?>