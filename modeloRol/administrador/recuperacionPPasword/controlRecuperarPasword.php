<?php

class controlRecuperarPasword
{
    public function procesarRecuperacion($email)
    {
        include_once('../../../modelo/securitUsuario.php');
        $objUsuario = new UsuarioDAO(); 
        
        // 1. Validar que el email existe y está activo
        $usuarioExiste = $objUsuario->validarEmailExiste($email);
        if(!$usuarioExiste) {
            $this->mostrarError("El correo electrónico '$email' no está registrado en el sistema");
            return;
        }
        
        // 2. Obtener información del usuario para el hash
        $usuarioInfo = $objUsuario->obtenerUsuarioPorEmail($email);
        if(!$usuarioInfo) {
            $this->mostrarError("Error al obtener información del usuario");
            return;
        }
        
        // 3. Generar enlace seguro SIN usar tokens en BD
        $enlaceRecuperacion = $this->generarEnlaceSeguro($usuarioInfo);
        
        // 4. Enviar email con el enlace (simulado)
        $enviado = $this->enviarEmailRecuperacion($email, $enlaceRecuperacion);
        
        if($enviado) {
            $this->mostrarExito($email);
        } else {
            $this->mostrarError("Error al enviar el enlace de recuperación.");
        }
    }
    
    /**
     * Genera un enlace seguro usando hash con información del usuario
     * SIN necesidad de guardar tokens en la base de datos
     */
    private function generarEnlaceSeguro($usuarioInfo)
    {
        $idUsuario = $usuarioInfo['id_usuario'];
        $email = $usuarioInfo['email'];
        $fechaRegistro = $usuarioInfo['creado_en'];
        
        // Crear un hash único usando información del usuario + timestamp
        $timestamp = time();
        $datosHash = $idUsuario . $email . $fechaRegistro . $timestamp;
        
        // Generar hash seguro
        $hash = hash('sha256', $datosHash . 'clave_secreta_recuperacion');
        
        // El enlace expira en 1 hora (3600 segundos)
        $expiracion = $timestamp + 3600;
        
        // Codificar parámetros para URL
        $parametros = base64_encode("id=$idUsuario&hash=$hash&exp=$expiracion");
        
        $baseUrl = "http://" . $_SERVER['HTTP_HOST'] . "/TRABAJOFINALARQUITECTURA";
        return $baseUrl . "/modeloRol/recuperacionPasword/restablecerPassword.php?data=" . urlencode($parametros);
    }
    
    /**
     * Valida un enlace de recuperación SIN consultar la base de datos
     */
    public static function validarEnlaceRecuperacion($parametrosCodificados)
    {
        // Decodificar parámetros
        $parametrosDecodificados = base64_decode($parametrosCodificados);
        parse_str($parametrosDecodificados, $datos);
        
        // Verificar que tenemos los parámetros necesarios
        if (!isset($datos['id']) || !isset($datos['hash']) || !isset($datos['exp'])) {
            return false;
        }
        
        // Verificar expiración (1 hora)
        if (time() > $datos['exp']) {
            return false;
        }
        
        // Obtener información del usuario para validar el hash
        include_once('../modelo/securitUsuario.php');
        $objUsuario = new UsuarioDAO();
        $usuarioInfo = $objUsuario->obtenerUsuarioPorId($datos['id']);
        
        if (!$usuarioInfo) {
            return false;
        }
        
        // Re-generar el hash con la misma lógica para validar
        $timestamp = $datos['exp'] - 3600; // Recuperar timestamp original
        $datosHash = $datos['id'] . $usuarioInfo['email'] . $usuarioInfo['creado_en'] . $timestamp;
        $hashEsperado = hash('sha256', $datosHash . 'clave_secreta_recuperacion');
        
        // Comparar hashes
        return hash_equals($hashEsperado, $datos['hash']);
    }
    
    /**
     * Obtiene el ID de usuario desde los parámetros del enlace
     */
    public static function obtenerIdUsuarioDesdeEnlace($parametrosCodificados)
    {
        $parametrosDecodificados = base64_decode($parametrosCodificados);
        parse_str($parametrosDecodificados, $datos);
        
        return isset($datos['id']) ? $datos['id'] : null;
    }
    
    private function enviarEmailRecuperacion($email, $enlaceRecuperacion)
    {
        // En producción, integrar con servicio real de email
        // Por ahora simulamos el envío
        error_log("SIMULACIÓN: Email de recuperación enviado a $email");
        error_log("SIMULACIÓN: Enlace: $enlaceRecuperacion");
        
        // En producción, aquí enviarías el email real con el enlace
        return true;
    }
    
    private function mostrarError($mensaje)
    {
        include_once('../../../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $objMensaje->mensajeSistemaShow($mensaje, "./indexRecuperarPasword.php", "error");
    }
    
    private function mostrarExito($email)
    {
        include_once('../../../shared/mensajeSistema.php'); 
        $objMensaje = new mensajeSistema();
        $mensaje = "Se ha enviado un enlace de recuperación a <strong>$email</strong>. " .
                  "El enlace es válido por 1 hora. " .
                  "Revise su bandeja de entrada y siga las instrucciones.";
        $objMensaje->mensajeSistemaShow($mensaje, "../../../securityModule/indexLoginSegurity.php", "success");
    }
}
?>