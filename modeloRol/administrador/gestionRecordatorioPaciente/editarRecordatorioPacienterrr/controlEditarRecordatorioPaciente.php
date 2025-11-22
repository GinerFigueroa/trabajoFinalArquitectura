<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('../../../../modelo/paciente_telegramDAO.php');

class controlEditarRecordatorioPaciente
{
    private $objTelegramDAO;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
    }

    /**
     * Obtener información de un chat específico
     */
    public function obtenerChat()
    {
        $idChat = $_GET['id'] ?? null;
        
        if (!$idChat) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no proporcionado']);
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no válido']);
            return;
        }

        $chat = $this->objTelegramDAO->obtenerChatPorId($idChat);
        
        if ($chat) {
            echo json_encode(['success' => true, 'data' => $chat]);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Chat no encontrado']);
        }
    }

    /**
     * Editar información de un chat
     */
    public function editarChat()
    {
        $idChat = $_POST['idChat'] ?? null;
        $chatId = $_POST['chatId'] ?? null;
        $username = $_POST['username'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $lastName = $_POST['lastName'] ?? null;

        // Validaciones
        if (!$idChat || !$chatId) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
            return;
        }

        // Sanitizar datos
        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        $chatId = filter_var($chatId, FILTER_VALIDATE_INT);
        $username = $this->sanitizeInput($username);
        $firstName = $this->sanitizeInput($firstName);
        $lastName = $this->sanitizeInput($lastName);

        if (!$idChat || !$chatId) {
            echo json_encode(['success' => false, 'mensaje' => 'Datos inválidos']);
            return;
        }

        // Verificar que el chat existe
        $chatExistente = $this->objTelegramDAO->obtenerChatPorId($idChat);
        if (!$chatExistente) {
            echo json_encode(['success' => false, 'mensaje' => 'El chat no existe']);
            return;
        }

        // Actualizar en la base de datos
        $resultado = $this->objTelegramDAO->actualizarChatTelegram(
            $idChat, 
            $chatId, 
            $username ?: null, 
            $firstName ?: null, 
            $lastName ?: null
        );

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Información actualizada correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar la información']);
        }
    }

    /**
     * Probar mensaje a un chat específico
     */
    public function probarMensaje()
    {
        $idChat = $_GET['id'] ?? null;
        
        if (!$idChat) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no proporcionado']);
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no válido']);
            return;
        }

        // Obtener información del chat
        $chat = $this->objTelegramDAO->obtenerChatPorId($idChat);
        
        if (!$chat) {
            echo json_encode(['success' => false, 'mensaje' => 'Chat no encontrado']);
            return;
        }

        // Enviar mensaje de prueba
        $enviado = $this->enviarMensajeTelegramPrueba($chat['chat_id']);

        if ($enviado['success']) {
            echo json_encode(['success' => true, 'mensaje' => 'Mensaje de prueba enviado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al enviar mensaje: ' . $enviado['mensaje']]);
        }
    }

    /**
     * Desactivar un chat
     */
    public function desactivarChat()
    {
        $idChat = $_GET['id'] ?? null;
        
        if (!$idChat) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no proporcionado']);
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no válido']);
            return;
        }

        $resultado = $this->objTelegramDAO->eliminarChatTelegram($idChat);

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Chat desactivado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al desactivar el chat']);
        }
    }

    /**
     * Reactivar un chat
     */
    public function reactivarChat()
    {
        $idChat = $_GET['id'] ?? null;
        
        if (!$idChat) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no proporcionado']);
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            echo json_encode(['success' => false, 'mensaje' => 'ID de chat no válido']);
            return;
        }

        $resultado = $this->objTelegramDAO->reactivarChatTelegram($idChat);

        if ($resultado) {
            echo json_encode(['success' => true, 'mensaje' => 'Chat reactivado correctamente']);
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Error al reactivar el chat']);
        }
    }

    /**
     * Enviar mensaje de prueba a Telegram
     */
    private function enviarMensajeTelegramPrueba($chatId)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        $mensaje = "🧪 *Prueba de Mensaje - Sistema de Recordatorios*\n\n" .
                   "Este es un mensaje de prueba del sistema.\n\n" .
                   "✅ *Estado:* Conexión verificada correctamente\n\n" .
                   "Tu configuración está funcionando perfectamente.";

        return $this->enviarMensajeTelegram($chatId, $mensaje);
    }

    /**
     * Función centralizada para enviar mensajes a Telegram
     */
    private function enviarMensajeTelegram($chatId, $mensaje)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
        
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