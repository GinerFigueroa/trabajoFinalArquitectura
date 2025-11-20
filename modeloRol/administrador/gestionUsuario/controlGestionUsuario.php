<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\controlGestionUsuario.php
include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/UsuarioDAO.php'); // Se corrige la ruta

class controlGestionUsuario // MEDIATOR
{
    private $objUsuarioDAO; // Emula el COMMAND Receiver
    private $objMensaje;
    private $estadoActual = 'Inicial'; // Emula el STATE

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    // Emulación del patrón STATE
    private function setEstado($nuevoEstado) {
        $this->estadoActual = $nuevoEstado;
        // Aquí se podría añadir lógica de transición o registro de estado
    }
    
    /**
     * Elimina un usuario.
     * Emula la lógica de CHAIN OF RESPONSIBILITY (validaciones) y COMMAND (ejecución).
     */
    public function eliminarUsuario($idUsuario) // Emula el COMMAND
    {
        // 1. Emulación del CHAIN OF RESPONSIBILITY (Validaciones)
        $this->setEstado('ValidandoID');
        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de usuario no válido para eliminación.", "./indexGestionUsuario.php", "error");
            return;
        }

        // Se podrían añadir más validaciones (e.g., verificar si el usuario existe antes de intentar eliminar)
        
        // 2. Ejecución del COMMAND (Ejecutar la acción)
        $this->setEstado('EjecutandoComando');
        $resultado = $this->objUsuarioDAO->eliminarUsuario($idUsuario);
        
        // 3. Manejo de respuesta (Delegado al Mensaje)
        if ($resultado) {
            $this->setEstado('Exito');
            $this->objMensaje->mensajeSistemaShow("Usuario eliminado correctamente.", "./indexGestionUsuario.php", "success");
        } else {
            $this->setEstado('Fallo');
            $this->objMensaje->mensajeSistemaShow("Error al eliminar el usuario o el usuario no existe.", "./indexGestionUsuario.php", "error");
        }
    }
}
?>