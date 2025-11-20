<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\editarUsuario\controlEditarUsuario.php
include_once('../../../../modelo/UsuarioDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarUsuario // MEDIATOR
{
    private $objUsuarioDAO;
    private $objMensaje;
    private $estadoEdicion = 'Pendiente'; // Emulación de STATE

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    // Emulación del patrón FACTORY METHOD
    private function createHashedPassword($clave) {
        return password_hash($clave, PASSWORD_DEFAULT);
    }
    
    // Emulación del patrón STATE
    private function setEstadoEdicion($estado) {
        $this->estadoEdicion = $estado;
    }

    // Emulación del patrón CHAIN OF RESPONSIBILITY
    private function validarEdicionChain($idUsuario, $login, $email)
    {
        if (!is_numeric($idUsuario) || $idUsuario <= 0) {
            return "ID de usuario no válido para edición. (CHAIN FAILED)";
        }
        if ($this->objUsuarioDAO->validarCampoUnicoExcepto('usuario_usuario', $login, $idUsuario)) {
            return "El nombre de usuario '{$login}' ya está en uso por otro usuario. (CHAIN: Usuario Único)";
        }
        if ($this->objUsuarioDAO->validarCampoUnicoExcepto('email', $email, $idUsuario)) {
            return "El email '{$email}' ya está en uso por otro usuario. (CHAIN: Email Único)";
        }
        return true; 
    }

    public function editarUsuario($idUsuario, $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo) // COMMAND
    {
        $this->setEstadoEdicion('Validando');
        
        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacion = $this->validarEdicionChain($idUsuario, $login, $email);
        
        if ($validacion !== true) {
            $this->objMensaje->mensajeSistemaShow($validacion, './indexEditarUsuario.php?id=' . $idUsuario, 'systemOut', false);
            return;
        }

        // 2. Ejecución de la Acción (COMMAND)
        $this->setEstadoEdicion('PreparandoClave');
        $claveHasheada = null;
        if (!empty($clave)) {
            // Uso del FACTORY METHOD si la clave se va a cambiar
            $claveHasheada = $this->createHashedPassword($clave);
        }
        
        $this->setEstadoEdicion('Guardando');
        $resultado = $this->objUsuarioDAO->editarUsuario(
            $idUsuario, 
            $login, 
            $nombre, 
            $apellidoPaterno, 
            $apellidoMaterno, 
            $email, 
            $telefono, 
            $claveHasheada,
            $idRol, 
            $activo
        );

        // 3. Manejo de Respuesta
        if ($resultado) {
            $this->setEstadoEdicion('Exito');
            $this->objMensaje->mensajeSistemaShow('Usuario editado correctamente.', '../indexGestionUsuario.php', 'success');
        } else {
            $this->setEstadoEdicion('Fallo');
            $this->objMensaje->mensajeSistemaShow('Error al editar el usuario. (DAO FAIL)', './indexEditarUsuario.php?id=' . $idUsuario, 'error');
        }
    }
}
?>