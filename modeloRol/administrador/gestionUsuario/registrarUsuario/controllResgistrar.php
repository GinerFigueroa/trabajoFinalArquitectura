<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modeloRol\administrador\gestionUsuario\registrarUsuario\controlRegistroUsuario.php
include_once('../../../../modelo/UsuarioDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlRegistroUsuario // MEDIATOR
{
    private $objUsuarioDAO; // Receives the COMMAND
    private $objMensaje;
    private $estadoRegistro = 'Pendiente'; // Emulación de STATE

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    // Emulación del patrón FACTORY METHOD: Crea la clave hasheada
    private function createHashedPassword($clave) {
        return password_hash($clave, PASSWORD_DEFAULT);
    }
    
    // Emulación del patrón STATE
    private function setEstadoRegistro($estado) {
        $this->estadoRegistro = $estado;
    }

    // Emulación del patrón CHAIN OF RESPONSIBILITY: Valida la complejidad y unicidad
    private function validarRegistroChain($login, $email, $telefono, $clave)
    {
        $this->setEstadoRegistro('ValidandoClave');
        if (strlen($clave) < 8) {
            return "La clave debe tener al menos 8 caracteres. (CHAIN: Longitud)";
        }
        if (!preg_match('/[0-9]/', $clave) || !preg_match('/[a-zA-Z]/', $clave)) {
             return "La clave debe contener letras y números. (CHAIN: Complejidad)";
        }
        
        $this->setEstadoRegistro('ValidandoUnicidad');
        if ($this->objUsuarioDAO->validarCampoUnico('usuario_usuario', $login)) {
            return "El nombre de usuario '{$login}' ya está en uso. (CHAIN: Usuario Único)";
        }
        if ($this->objUsuarioDAO->validarCampoUnico('email', $email)) {
            return "El email '{$email}' ya está en uso. (CHAIN: Email Único)";
        }
        if ($this->objUsuarioDAO->validarCampoUnico('telefono', $telefono)) {
             return "El teléfono '{$telefono}' ya está en uso. (CHAIN: Teléfono Único)";
        }
        
        return true; 
    }

    public function registrarUsuario($login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo) // COMMAND
    {
        $this->setEstadoRegistro('IniciandoRegistro');
        
        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacion = $this->validarRegistroChain($login, $email, $telefono, $clave);
        
        if ($validacion !== true) {
            // Se usa './indexRegistroUsuario.php' para volver al formulario
            $this->objMensaje->mensajeSistemaShow($validacion, './indexRegistroUsuario.php', 'systemOut', false);
            return;
        }

        // 2. Ejecución de la Acción (COMMAND)
        $this->setEstadoRegistro('CreandoHash');
        // Uso del FACTORY METHOD
        $hashed_clave = $this->createHashedPassword($clave);

        $this->setEstadoRegistro('Guardando');
        // Delegación al DAO (Receiver del COMMAND)
        $resultado = $this->objUsuarioDAO->registrarUsuario($login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $hashed_clave, $idRol, $activo);

        // 3. Manejo de Respuesta
        if ($resultado) {
            $this->setEstadoRegistro('Exito');
            // Vuelve a la lista principal
            $this->objMensaje->mensajeSistemaShow('Usuario registrado correctamente.', './../indexGestionUsuario.php', 'success');
        } else {
            $this->setEstadoRegistro('Fallo');
             // Vuelve al formulario de registro
            $this->objMensaje->mensajeSistemaShow('Error al registrar el usuario.', './indexRegistroUsuario.php', 'error');
        }
    }
}
?>