<?php

include_once('../../../../modelo/UsuarioDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlRegistroUsuario // MEDIATOR / COMMAND
{
    private $objUsuarioDAO; // Receiver del COMMAND
    private $objMensaje;
    private $estadoRegistro = 'Pendiente'; // Emulación de STATE

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Patrón: FACTORY METHOD 🏭
     * Crea un hash de la clave para almacenamiento seguro.
     */
    private function createHashedPassword(string $clave): string {
        return password_hash($clave, PASSWORD_DEFAULT);
    }
    
    // Patrón: STATE (Método de control)
    private function setEstadoRegistro(string $estado) {
        $this->estadoRegistro = $estado;
    }

    /**
     * Patrón: CHAIN OF RESPONSIBILITY (Validaciones de unicidad y complejidad) 🔗
     * Ejecuta una serie de validaciones secuenciales que deben cumplirse.
     * @return true|string Retorna TRUE si todas las validaciones son exitosas, o un mensaje de error (string).
     */
    private function validarRegistroChain(string $login, string $email, string $telefono, string $clave): true|string
    {
        $this->setEstadoRegistro('ValidandoClave');

        // Validación 1: Complejidad y longitud de la clave
        if (strlen($clave) < 8) {
            return "La clave debe tener al menos 8 caracteres.";
        }
        if (!preg_match('/[0-9]/', $clave) || !preg_match('/[a-zA-Z]/', $clave)) {
             return "La clave debe contener letras y números.";
        }
        
        $this->setEstadoRegistro('ValidandoUnicidad');

        // Validación 2: Unicidad del nombre de usuario
        if ($this->objUsuarioDAO->validarCampoUnico('usuario_usuario', $login)) {
            return "El nombre de usuario '{$login}' ya está en uso.";
        }
        
        // Validación 3: Unicidad del email
        if ($this->objUsuarioDAO->validarCampoUnico('email', $email)) {
            return "El email '{$email}' ya está en uso.";
        }
        
        // Validación 4: Unicidad del teléfono
        if ($this->objUsuarioDAO->validarCampoUnico('telefono', $telefono)) {
             return "El teléfono '{$telefono}' ya está en uso.";
        }
        
        // Todas las validaciones pasaron
        return true; 
    }

    /**
     * Patrón: COMMAND (Método principal de registro) 🚀
     * Coordina la validación, la creación de la clave y la delegación al DAO.
     */
    public function registrarUsuario(string $login, string $nombre, string $apellidoPaterno, ?string $apellidoMaterno, string $email, string $telefono, string $clave, int $idRol, int $activo)
    {
        $this->setEstadoRegistro('IniciandoRegistro');
        $urlRetorno = '../indexGestionUsuario.php'; // URL a la lista de usuarios
        
        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacion = $this->validarRegistroChain($login, $email, $telefono, $clave);
        
        if ($validacion !== true) {
            // Error de validación: Volver al formulario de registro para corregir
            $this->objMensaje->mensajeSistemaShow($validacion, './indexRegistroUsuario.php', 'error');
            return;
        }

        // 2. Ejecución de la Acción: Preparación de datos
        $this->setEstadoRegistro('CreandoHash');
        $hashed_clave = $this->createHashedPassword($clave); // Uso del FACTORY METHOD

        // 3. Delegación al DAO (Receiver)
        $this->setEstadoRegistro('Guardando');
        $resultado = $this->objUsuarioDAO->registrarUsuario(
            $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, 
            $hashed_clave, $idRol, $activo
        );

        // 4. Manejo de Respuesta
        if ($resultado) {
            $this->setEstadoRegistro('Exito');
            $this->objMensaje->mensajeSistemaShow(
                '✅ Usuario registrado correctamente.', 
                $urlRetorno, 
                'success'
            );
        } else {
            $this->setEstadoRegistro('Fallo');
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error grave al registrar el usuario en la base de datos.', 
                $urlRetorno, 
                'error'
            );
        }
    }
}
?>