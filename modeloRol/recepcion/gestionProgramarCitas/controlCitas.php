<?php
// Archivo: controlCitas.php
include_once('../../../modelo/CitasDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

class controlCitas // PATRÓN: MEDIATOR / COMMAND
{
    private $objCitaDAO;
    private $objMensaje;

    public function __construct()
    {
        // Se asume que CitasDAO.php contiene la clase CitaDAO
        $this->objCitaDAO = new CitaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * PATRÓN: COMMAND (Ejecuta la acción de negocio).
     * @param int $idCita
     */
    public function eliminarCita($idCita)
    {
        // 1. Validación básica (Chain of Responsibility simple)
        if (empty($idCita) || !is_numeric($idCita) || $idCita <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de cita no válido para eliminar.", "./indexTotalCitas.php", "systemOut", false);
            return;
        }

        // 2. Delegación al DAO (Ejecución del Command)
        $resultado = $this->objCitaDAO->eliminarCita($idCita);
        
        // 3. Manejo de la respuesta
        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Cita eliminada correctamente.", "./indexTotalCitas.php", "success");
        } else {
            // El error puede ser porque la cita no existía o un error de BD.
            $this->objMensaje->mensajeSistemaShow("Error al eliminar la cita. Puede que ya no exista o haya un error en la base de datos.", "./indexTotalCitas.php", "error");
        }
    }
}
?>