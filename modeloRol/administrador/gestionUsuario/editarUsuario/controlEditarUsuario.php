<?php

include_once('../../../../modelo/UsuarioDAO.php');
include_once('../../../../shared/mensajeSistema.php');

class controlEditarUsuario // MEDIATOR / COMMAND
{
    private $objUsuarioDAO; // Receiver
    private $objMensaje;
    private $estadoEdicion = 'Pendiente'; // Emulación de STATE

    public function __construct()
    {
        $this->objUsuarioDAO = new UsuarioDAO(); 
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Patrón: FACTORY METHOD 🏭
     * Crea un hash seguro de la clave.
     */
    private function createHashedPassword(string $clave): string {
        return password_hash($clave, PASSWORD_DEFAULT);
    }
    
    // Patrón: STATE (Método de control para seguimiento)
    private function setEstadoEdicion(string $estado) {
        $this->estadoEdicion = $estado;
    }

    /**
     * Patrón: CHAIN OF RESPONSIBILITY (Validaciones de unicidad) 🔗
     * Valida que los campos únicos (login, email, teléfono) no pertenezcan a otro usuario.
     * @return true|string Retorna TRUE si es válido, o un mensaje de error (string).
     */
    private function validarEdicionChain(int $idUsuario, string $login, string $email, string $telefono): true|string
    {
        $this->setEstadoEdicion('ValidandoUnicidad');
        
        // Validación 1: Unicidad de Login (exceptuando el ID actual)
        if ($this->objUsuarioDAO->validarCampoUnicoExcepto('usuario_usuario', $login, $idUsuario)) {
            return "El nombre de usuario '{$login}' ya está en uso por otro usuario.";
        }
        
        // Validación 2: Unicidad de Email (exceptuando el ID actual)
        if ($this->objUsuarioDAO->validarCampoUnicoExcepto('email', $email, $idUsuario)) {
            return "El email '{$email}' ya está en uso por otro usuario.";
        }
        
        // Validación 3: Unicidad de Teléfono (exceptuando el ID actual)
        if ($this->objUsuarioDAO->validarCampoUnicoExcepto('telefono', $telefono, $idUsuario)) {
            return "El teléfono '{$telefono}' ya está en uso por otro usuario.";
        }
        
        return true; 
    }

    /**
     * Patrón: COMMAND (Método principal de edición) 🚀
     * Coordina la validación, el hasheo de la clave y la persistencia de datos.
     */
    public function editarUsuario(int $idUsuario, string $login, string $nombre, string $apellidoPaterno, ?string $apellidoMaterno, string $email, string $telefono, string $clave, int $idRol, int $activo)
    {
        $this->setEstadoEdicion('IniciandoEdicion');
        $urlRetornoFormulario = './indexEditarUsuario.php?id=' . $idUsuario;
        $urlRetornoLista = '../indexGestionUsuario.php';

        // 1. Ejecución del CHAIN OF RESPONSIBILITY
        $validacion = $this->validarEdicionChain($idUsuario, $login, $email, $telefono);
        
        if ($validacion !== true) {
            // Error de validación: Volver al formulario
            $this->objMensaje->mensajeSistemaShow($validacion, $urlRetornoFormulario, 'error');
            return;
        }

        // 2. Preparación de la clave (solo si se proporciona una nueva)
        $this->setEstadoEdicion('PreparandoClave');
        $claveHasheada = null;
        if (!empty($clave)) {
            // Se recomienda añadir aquí la validación de complejidad de la clave si no se hizo en la vista/chain
            $claveHasheada = $this->createHashedPassword($clave); // Uso del FACTORY METHOD
        }
        
        // 3. Delegación al DAO (Receiver)
        $this->setEstadoEdicion('Guardando');
        $resultado = $this->objUsuarioDAO->editarUsuario(
            $idUsuario, 
            $login, 
            $nombre, 
            $apellidoPaterno, 
            $apellidoMaterno, 
            $email, 
            $telefono, 
            $claveHasheada, // null si no se cambia
            $idRol, 
            $activo
        );

        // 4. Manejo de Respuesta
        if ($resultado) {
            $this->setEstadoEdicion('Exito');
            $this->objMensaje->mensajeSistemaShow(
                '✅ Usuario editado correctamente.', 
                $urlRetornoLista, 
                'success'
            );
        } else {
            $this->setEstadoEdicion('Fallo');
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error al editar el usuario en la base de datos.', 
                $urlRetornoLista, // En caso de error de BD, mejor volver a la lista principal
                'error'
            );
        }
    }
}
?>