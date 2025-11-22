<?php
include_once('../../../modelo/RecuperacionPasswordDAO.php');
include_once('../../../shared/mensajeSistema.php');

class controlRecuperarPasword
{
    private $objRecuperacionDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objRecuperacionDAO = new RecuperacionPasswordDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Procesa la solicitud de recuperación de contraseña
     */
    public function procesarSolicitud($email)
    {
        // Validar email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->objMensaje->mensajeSistemaShow(
                "Por favor ingresa un email válido",
                "./indexRecuperarPasword.php",
                "error"
            );
            return;
        }

        // Verificar si el email existe y tiene Telegram vinculado
        $usuarioData = $this->objRecuperacionDAO->validarEmailExiste($email);
        
        if (!$usuarioData) {
            $this->objMensaje->mensajeSistemaShow(
                "No encontramos una cuenta activa con ese email",
                "./indexRecuperarPasword.php",
                "error"
            );
            return;
        }

        // Verificar si tiene Telegram vinculado
        if (empty($usuarioData['chat_id']) || $usuarioData['telegram_activo'] != 1) {
            $this->objMensaje->mensajeSistemaShow(
                "Tu cuenta no tiene vinculado Telegram. Contacta al administrador.",
                "./indexRecuperarPasword.php",
                "error"
            );
            return;
        }

        // Generar código de verificación
        $codigo = $this->generarCodigoVerificacion();
        
        // Guardar código en la base de datos
        $guardado = $this->objRecuperacionDAO->generarCodigoVerificacion(
            $usuarioData['id_usuario'], 
            $codigo
        );

        if (!$guardado) {
            $this->objMensaje->mensajeSistemaShow(
                "Error al generar el código de verificación. Intenta nuevamente.",
                "./indexRecuperarPasword.php",
                "error"
            );
            return;
        }

        // Enviar código por Telegram
        $enviado = $this->enviarCodigoTelegram($usuarioData['chat_id'], $codigo);

        if ($enviado) {
            // Guardar datos en sesión para el siguiente paso
            $_SESSION['recuperacion_usuario_id'] = $usuarioData['id_usuario'];
            $_SESSION['recuperacion_email'] = $email;
            
            $this->objMensaje->mensajeSistemaShow(
                "✅ Código enviado! Revisa tu Telegram para continuar con el proceso.",
                "./restablecerPassword.php",
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error al enviar el código por Telegram. Intenta nuevamente.",
                "./indexRecuperarPasword.php",
                "error"
            );
        }
    }

    /**
     * Verifica el código y permite restablecer la contraseña
     */
    public function verificarCodigoYRestablecer($idUsuario, $codigo, $nuevaPassword)
    {
        // Verificar código
        $codigoValido = $this->objRecuperacionDAO->verificarCodigo($idUsuario, $codigo);
        
        if (!$codigoValido) {
            return [
                'success' => false,
                'message' => "Código inválido o expirado. Solicita uno nuevo."
            ];
        }

        // Validar fortaleza de la nueva contraseña
        if (strlen($nuevaPassword) < 6) {
            return [
                'success' => false,
                'message' => "La contraseña debe tener al menos 6 caracteres."
            ];
        }

        // Hash de la nueva contraseña
        $nuevaPasswordHash = password_hash($nuevaPassword, PASSWORD_DEFAULT);

        // Actualizar contraseña
        $actualizado = $this->objRecuperacionDAO->actualizarPassword($idUsuario, $nuevaPasswordHash);

        if ($actualizado) {
            // Limpiar sesión
            unset($_SESSION['recuperacion_usuario_id']);
            unset($_SESSION['recuperacion_email']);
            
            return [
                'success' => true,
                'message' => "✅ Contraseña actualizada correctamente. Ya puedes iniciar sesión."
            ];
        } else {
            return [
                'success' => false,
                'message' => "Error al actualizar la contraseña. Intenta nuevamente."
            ];
        }
    }

    /**
     * Genera un código de 6 dígitos
     */
    private function generarCodigoVerificacion()
    {
        return str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Envía el código por Telegram
     */
    private function enviarCodigoTelegram($chatId, $codigo)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        $mensaje = "🔐 *Código de Verificación - Clínica*\n\n";
        $mensaje .= "Tu código para recuperar la contraseña es:\n\n";
        $mensaje .= "```\n";
        $mensaje .= $codigo . "\n";
        $mensaje .= "```\n\n";
        $mensaje .= "⚠️ *Este código expira en 15 minutos*\n\n";
        $mensaje .= "_No compartas este código con nadie._";

        $url = "https://api.telegram.org/bot{$botToken}/sendMessage?" . 
               http_build_query([
                   'chat_id' => $chatId,
                   'text' => $mensaje,
                   'parse_mode' => 'Markdown'
               ]);

        // Configurar contexto para evitar errores de SSL
        $context = stream_context_create([
            'http' => [
                'timeout' => 10,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            error_log("Error: No se pudo conectar con Telegram API para chat_id: $chatId");
            return false;
        }

        $data = json_decode($response, true);
        
        return isset($data['ok']) && $data['ok'] === true;
    }
}
?>