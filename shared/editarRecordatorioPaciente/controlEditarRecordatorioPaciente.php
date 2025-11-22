<?php
session_start();
include_once('../../../../shared/mensajeSistema.php');
include_once('../../../../modelo/paciente_telegramDAO.php');

class controlEditarRecordatorioPaciente
{
    private $objTelegramDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Procesa la edición de un chat existente
     */
    public function procesarEdicionChat()
    {
        if (!isset($_POST['idChat']) || empty($_POST['idChat'])) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no proporcionado", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $idChat = filter_var($_POST['idChat'], FILTER_VALIDATE_INT);
        $chatId = filter_var($_POST['chatId'] ?? null, FILTER_VALIDATE_INT);
        $username = $this->sanitizeInput($_POST['username'] ?? null);
        $firstName = $this->sanitizeInput($_POST['firstName'] ?? null);
        $lastName = $this->sanitizeInput($_POST['lastName'] ?? null);

        // Validaciones
        if (!$idChat || $idChat <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        if (!$chatId || $chatId <= 0) {
            $this->objMensaje->mensajeSistemaShow("El Chat ID debe ser un número válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        // Verificar que el chat existe
        $chatExistente = $this->objTelegramDAO->obtenerChatPorId($idChat);
        if (!$chatExistente) {
            $this->objMensaje->mensajeSistemaShow("El chat no existe", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        // Actualizar en la base de datos
        $resultado = $this->objTelegramDAO->actualizarChatTelegram(
            $idChat, $chatId, $username, $firstName, $lastName
        );

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "✅ Información de Telegram actualizada correctamente",
                "./indexEditarRecordatorioPaciente.php",
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al actualizar la información de Telegram",
                "./indexEditarRecordatorioPaciente.php",
                "error"
            );
        }
    }

    /**
     * Desactiva un chat de Telegram
     */
    public function desactivarChat($idChat)
    {
        if (empty($idChat)) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $resultado = $this->objTelegramDAO->eliminarChatTelegram($idChat);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "✅ Chat desactivado correctamente. El paciente dejará de recibir recordatorios.",
                "./indexEditarRecordatorioPaciente.php",
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al desactivar el chat",
                "./indexEditarRecordatorioPaciente.php",
                "error"
            );
        }
    }

    /**
     * Reactiva un chat de Telegram
     */
    public function reactivarChat($idChat)
    {
        if (empty($idChat)) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $resultado = $this->objTelegramDAO->reactivarChatTelegram($idChat);

        if ($resultado) {
            $this->objMensaje->mensajeSistemaShow(
                "✅ Chat reactivado correctamente. El paciente volverá a recibir recordatorios.",
                "./indexEditarRecordatorioPaciente.php",
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "Error al reactivar el chat",
                "./indexEditarRecordatorioPaciente.php",
                "error"
            );
        }
    }

    /**
     * Envía un mensaje de prueba
     */
    public function enviarMensajePrueba($idChat)
    {
        if (empty($idChat)) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $idChat = filter_var($idChat, FILTER_VALIDATE_INT);
        
        if (!$idChat || $idChat <= 0) {
            $this->objMensaje->mensajeSistemaShow("ID de chat no válido", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $chat = $this->objTelegramDAO->obtenerChatPorId($idChat);
        
        if (!$chat) {
            $this->objMensaje->mensajeSistemaShow("Chat no encontrado", "./indexEditarRecordatorioPaciente.php", "error");
            return;
        }

        $enviado = $this->enviarMensajeTelegramPrueba($chat['chat_id']);

        if ($enviado) {
            $this->objMensaje->mensajeSistemaShow(
                "✅ Mensaje de prueba enviado correctamente a " . ($chat['username_telegram'] ? '@' . $chat['username_telegram'] : 'el paciente'),
                "./indexEditarRecordatorioPaciente.php",
                "success"
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error al enviar mensaje de prueba. Verifique la configuración del bot.",
                "./indexEditarRecordatorioPaciente.php",
                "error"
            );
        }
    }
    
}