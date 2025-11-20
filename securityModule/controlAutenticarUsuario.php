<?php
/**
 * Patrón: MVC (Controlador) / Service Layer (Parcial)
 * Responsabilidad: Controlar el flujo de la autenticación.
 */
class controlAutenticarUsuario
{
    /**
     * Patrón: Delegación / Command (Verificar Autenticación)
     * Centraliza la secuencia de validación.
     */
    public function verificarUsuario($login, $password)
    {
        // Patrón DAO: Se instancia el DAO (Usuario) para acceder a los datos.
        include_once('../modelo/securitUsuario.php');
        
        $objUsuario = new UsuarioDAO(); 
        
        // Patrón Strategy (Implícito): Cada validación es una "estrategia" de comprobación.
        // Las llamadas a los métodos del DAO (validarLogin, validarPassword, validarEstado) 
        // son los pasos en la Estrategia de Autenticación.
        
        // 1. Validar login
        $respuesta = $objUsuario->validarLogin($login);
        if(!$respuesta) {
            $this->mostrarError("El login '$login' no está registrado en el sistema");
            return;
        }
        
        // 2. Validar password
        $respuesta = $objUsuario->validarPassword($login, $password);
        if(!$respuesta) {
            $this->mostrarError("El usuario '$login' tiene registrado un password diferente del ingresado");
            return;
        }
        
        // 3. Validar estado (Patrón State Simplificado: 1/0)
        $respuesta = $objUsuario->validarEstado($login);
        if(!$respuesta) {
            $this->mostrarError("El usuario '$login' no está habilitado en el sistema<br>Contacte con el administrador");
            return;
        }
        
        // 4. Autenticación exitosa
        $this->iniciarSesion($login);
    }
    
    /**
     * Patrón: Delegación (Manejo de Mensajes)
     */
    private function mostrarError($mensaje)
    {
        include_once('../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow($mensaje, "../index.php", "systemOut", false);
    }
    
    /**
     * Patrón: Delegación y Coordinación
     * **MODIFICADO:** Ahora almacena id_usuario y rol_id en la sesión.
     */
    private function iniciarSesion($login)
    {
        // DAO para privilegios (contiene obtenerInformacionCompletaUsuario)
        include_once('../modelo/usuarioPrivilegioDAO.php'); 
        // Vista de bienvenida
        include_once('screenBienvenida.php');
        
        $objUsuarioPrivilegio = new UsuarioPrivilegioDAO();
        $objBienvenida = new screenBienvenida();
        
        // 1. OBTENER INFORMACIÓN COMPLETA DEL USUARIO (id_usuario, id_rol, etc.)
        $usuarioInfo = $objUsuarioPrivilegio->obtenerInformacionCompletaUsuario($login);
        
        // 2. Obtiene solo la lista de privilegios/roles
        $listaPrivilegios = $objUsuarioPrivilegio->obtenerPrivilegiosUsuario($login);
        
        // 3. Establecer Variables de Sesión
        $_SESSION['login'] = $login;
        $_SESSION['privilegios'] = $listaPrivilegios;
        
        // 💥 Solución para el error de 'Acceso Denegado' 💥
        if ($usuarioInfo) {
            // Asumiendo que las claves en $usuarioInfo son 'id_usuario' y 'id_rol' (o 'id_rol' si así lo configuró el DAO)
            $_SESSION['id_usuario'] = $usuarioInfo['id_usuario'] ?? null; // Clave requerida en formConsultarCitas
            $_SESSION['rol_id'] = $usuarioInfo['id_rol'] ?? null;       // Clave requerida en formConsultarCitas (asumiendo 'id_rol' en BD)
        }
        
        // 4. Muestra la vista (Delegación a la Vista)
        $objBienvenida->screenBienvenidaShow($listaPrivilegios);
    }
}
?>