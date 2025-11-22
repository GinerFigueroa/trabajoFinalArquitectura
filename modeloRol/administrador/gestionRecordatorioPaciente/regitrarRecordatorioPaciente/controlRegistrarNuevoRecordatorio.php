<?php

include_once('../../../../shared/mensajeSistema.php');
include_once('../../../../modelo/paciente_telegramDAO.php');

class controlRegistrarNuevoRecordatorio
{
    private $objTelegramDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
        $this->objMensaje = new mensajeSistema();
    }

    public function procesarRegistroPaciente()
    {
        // Validar checkbox de confirmación
        if (!isset($_POST['confirmarRegistro']) || $_POST['confirmarRegistro'] !== 'on') {
            $this->objMensaje->mensajeSistemaShow("Debe confirmar que la información es correcta", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        // Validar campos requeridos
        if (!isset($_POST['idPaciente']) || empty($_POST['idPaciente'])) {
            $this->objMensaje->mensajeSistemaShow("Debe seleccionar un paciente", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        if (!isset($_POST['chatId']) || empty($_POST['chatId'])) {
            $this->objMensaje->mensajeSistemaShow("Chat ID es obligatorio", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        // Sanitizar datos
        $idPaciente = filter_var($_POST['idPaciente'], FILTER_VALIDATE_INT);
        $chatId = filter_var($_POST['chatId'], FILTER_VALIDATE_INT);
        $username = $this->sanitizeInput($_POST['username'] ?? '');
        $firstName = $this->sanitizeInput($_POST['firstName'] ?? '');
        $lastName = $this->sanitizeInput($_POST['lastName'] ?? '');

        // Validaciones
        if (!$idPaciente || $idPaciente <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de paciente no válido", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        if (!$chatId || $chatId <= 0) {
            $this->objMensaje->mensajeSistemaShow("Chat ID debe ser un número positivo", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        // Verificar que el paciente existe
        if (!$this->objTelegramDAO->pacienteExiste($idPaciente)) {
            $this->objMensaje->mensajeSistemaShow("El paciente seleccionado no existe", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        // Verificar que el paciente no tenga chat registrado
        if ($this->objTelegramDAO->existeChatPaciente($idPaciente)) {
            $this->objMensaje->mensajeSistemaShow("El paciente ya tiene un chat de Telegram registrado", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        // Verificar que el Chat ID no esté en uso
        if ($this->objTelegramDAO->chatIdExiste($chatId)) {
            $this->objMensaje->mensajeSistemaShow("Este Chat ID ya está registrado para otro paciente", "./indexRegistrarNuevoRecordatorio.php", "error");
            return;
        }

        // Registrar en la base de datos
        $resultado = $this->objTelegramDAO->registrarChatTelegram(
            $idPaciente, 
            $chatId, 
            $username ?: null, 
            $firstName ?: null, 
            $lastName ?: null
        );

        if ($resultado['success']) {
            // Enviar mensaje de bienvenida
            $mensajeEnviado = $this->enviarMensajeBienvenida($chatId);
            
            $mensajeExito = "✅ Paciente registrado correctamente en el sistema de recordatorios";
            if ($mensajeEnviado) {
                $mensajeExito .= " y mensaje de bienvenida enviado";
            } else {
                $mensajeExito .= " (pero no se pudo enviar el mensaje de bienvenida)";
            }
            
            $this->objMensaje->mensajeSistemaShow($mensajeExito, "../indexRecordatorioPaciente.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error al registrar el paciente: " . $resultado['mensaje'],
                "./indexRegistrarNuevoRecordatorio.php",
                "error"
            );
        }
    }

    /**
     * Probar Chat ID antes de registrar
     */
    public function probarChatId()
    {
        header('Content-Type: application/json');
        
        $chatId = $_POST['chatIdTest'] ?? null;
        
        if (!$chatId) {
            echo json_encode(['success' => false, 'mensaje' => 'Chat ID no proporcionado']);
            return;
        }

        $resultado = $this->enviarMensajePrueba($chatId);

        if ($resultado['success']) {
            echo json_encode([
                'success' => true, 
                'mensaje' => 'Chat ID funcional. Mensaje de prueba enviado correctamente.'
            ]);
        } else {
            echo json_encode([
                'success' => false, 
                'mensaje' => 'Chat ID no funcional: ' . $resultado['mensaje']
            ]);
        }
    }

    /**
     * Función CORREGIDA para enviar mensaje de bienvenida
     */
    private function enviarMensajeBienvenida($chatId)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        $mensaje = "🎉 *¡Bienvenido al Sistema de Recordatorios!*\n\n" .
                   "Hola, has sido registrado exitosamente en nuestro sistema de recordatorios automáticos.\n\n" .
                   "📋 *Qué puedes esperar:*\n" .
                   "• Recordatorios 1 hora antes de tus citas\n" .
                   "• Mensajes importantes sobre tu tratamiento\n" .
                   "• Comunicación directa con la clínica\n\n" .
                   "💡 *Recomendaciones:*\n" .
                   "• Mantén esta conversación activa\n" .
                   "• Responde si no puedes asistir a una cita\n" .
                   "• Contacta con nosotros si tienes dudas\n\n" .
                   "_¡Gracias por confiar en nosotros!_ 🦷";

        return $this->enviarMensajeTelegram($chatId, $mensaje);
    }

    /**
     * Enviar mensaje de prueba
     */
    private function enviarMensajePrueba($chatId)
    {
        $mensaje = "🧪 *Prueba de Chat ID*\n\n" .
                   "Este es un mensaje de prueba del sistema.\n\n" .
                   "✅ *Estado:* Chat ID verificado correctamente\n\n" .
                   "Puedes proceder con el registro completo.";

        return $this->enviarMensajeTelegram($chatId, $mensaje);
    }

    /**
     * Función centralizada para enviar mensajes a Telegram
     */
    private function enviarMensajeTelegram($chatId, $mensaje)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
        // USAR http_build_query para formato correcto
        $payload = http_build_query([
            'chat_id' => $chatId,
            'text' => $mensaje,
            'parse_mode' => 'Markdown'
        ]);

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/x-www-form-urlencoded'
            ]
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        // Debug: Ver respuesta de Telegram
        error_log("Telegram Response - HTTP Code: " . $httpCode);
        error_log("Telegram Response: " . $response);

        if ($httpCode === 200) {
            return ['success' => true, 'mensaje' => 'Mensaje enviado correctamente'];
        } else {
            $responseData = json_decode($response, true);
            $errorMensaje = $responseData['description'] ?? 'Error desconocido de Telegram';
            return ['success' => false, 'mensaje' => $errorMensaje];
        }
    }

    private function sanitizeInput($input)
    {
        return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
?>