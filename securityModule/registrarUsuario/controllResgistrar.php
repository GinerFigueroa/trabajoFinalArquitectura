<?php
include_once('../../modelo/UsuarioDAO.php');
include_once('../../shared/mensajeSistema.php');

class controlRegistroUsuario
{
    private $objUsuarioDAO;
    private $objMensaje;
    private $estadoRegistro = 'Pendiente';

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    private function createHashedPassword($clave) {
        return password_hash($clave, PASSWORD_DEFAULT);
    }
    
    private function setEstadoRegistro($estado) {
        $this->estadoRegistro = $estado;
    }

    /**
     * VALIDACIÓN ESPECÍFICA PARA REGISTRO DE PACIENTES
     */
    private function validarRegistroPaciente($login, $email, $telefono, $clave, $idRol)
    {
        $this->setEstadoRegistro('ValidandoRol');
        
        // ✅ FORZAR ROL DE PACIENTE (ID 4)
        if ($idRol != 4) {
            return "Error: Solo se permiten registros de pacientes. (CHAIN: Rol Inválido)";
        }
        
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

    /**
     * REGISTRO EXCLUSIVO PARA PACIENTES
     */
    public function registrarUsuario($login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo)
    {
        $this->setEstadoRegistro('IniciandoRegistroPaciente');
        
        // 1. Validación específica para pacientes
        $validacion = $this->validarRegistroPaciente($login, $email, $telefono, $clave, $idRol);
        
        if ($validacion !== true) {
            $this->objMensaje->mensajeSistemaShow($validacion, '../indexLoginSegurity.php', 'systemOut', false);
            return;
        }

        // 2. Crear hash de contraseña
        $this->setEstadoRegistro('CreandoHash');
        $hashed_clave = $this->createHashedPassword($clave);

        // 3. Registrar usuario como PACIENTE
        $this->setEstadoRegistro('GuardandoPaciente');
        $resultado = $this->objUsuarioDAO->registrarUsuario(
            $login, 
            $nombre, 
            $apellidoPaterno, 
            $apellidoMaterno, 
            $email, 
            $telefono, 
            $hashed_clave, 
            $idRol, // Siempre será 4 (Paciente)
            $activo // Siempre será 1 (Activo)
        );

        // 4. Manejo de respuesta
        if ($resultado) {
            $this->setEstadoRegistro('ExitoPaciente');
            // ✅ Redirigir al login con mensaje de éxito
            $this->objMensaje->mensajeSistemaShow(
                '¡Registro exitoso! Ahora puede iniciar sesión como paciente.', 
                '../indexLoginSegurity.php', 
                'success'
            );
        } else {
            $this->setEstadoRegistro('FalloPaciente');
            $this->objMensaje->mensajeSistemaShow(
                'Error al registrar el paciente. Por favor, intente nuevamente.', 
                './indexLoginSegurity.php', 
                'error'
            );
        }
    }
}
?>