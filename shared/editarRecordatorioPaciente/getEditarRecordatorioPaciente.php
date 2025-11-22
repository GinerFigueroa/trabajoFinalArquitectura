<?php
include_once('../../../../modelo/paciente_telegramDAO.php');
include_once('../configTelegram.php');

$objTelegramDAO = new PacienteTelegramDAO();

header('Content-Type: application/json');

// Manejar diferentes acciones
$action = $_GET['action'] ?? '';

switch ($action) {
    case 'obtener_chat':
        $idChat = $_GET['id'] ?? null;
        if ($idChat) {
            $chat = $objTelegramDAO->obtenerChatPorId($idChat);
            if ($chat) {
                echo json_encode(['success' => true, 'data' => $chat]);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Chat no encontrado']);
            }
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'ID no proporcionado']);
        }
        break;

    case 'editar_chat':
        $idChat = $_POST['idChat'] ?? null;
        $chatId = $_POST['chatId'] ?? null;
        $username = $_POST['username'] ?? null;
        $firstName = $_POST['firstName'] ?? null;
        $lastName = $_POST['lastName'] ?? null;

        if ($idChat && $chatId) {
            $resultado = $objTelegramDAO->actualizarChatTelegram(
                $idChat, $chatId, $username, $firstName, $lastName
            );
            
            if ($resultado) {
                echo json_encode(['success' => true, 'mensaje' => 'Chat actualizado correctamente']);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Error al actualizar el chat']);
            }
        } else {
            echo json_encode(['success' => false, 'mensaje' => 'Datos incompletos']);
        }
        break;

    case 'probar_mensaje':
        $idChat = $_GET['id'] ?? null;
        if ($idChat) {
            $chat = $objTelegramDAO->obtenerChatPorId($idChat);
            if ($chat) {
                $enviado = enviarMensajePrueba($chat['chat_id']);
                if ($enviado) {
                    echo json_encode(['success' => true, 'mensaje' => 'Mensaje de prueba enviado correctamente']);
                } else {
                    echo json_encode(['success' => false, 'mensaje' => 'Error al enviar mensaje de prueba']);
                }
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Chat no encontrado']);
            }
        }
        break;

    case 'desactivar_chat':
        $idChat = $_GET['id'] ?? null;
        if ($idChat) {
            $resultado = $objTelegramDAO->eliminarChatTelegram($idChat);
            if ($resultado) {
                echo json_encode(['success' => true, 'mensaje' => 'Chat desactivado correctamente']);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Error al desactivar el chat']);
            }
        }
        break;

    case 'reactivar_chat':
        $idChat = $_GET['id'] ?? null;
        if ($idChat) {
            $resultado = $objTelegramDAO->reactivarChatTelegram($idChat);
            if ($resultado) {
                echo json_encode(['success' => true, 'mensaje' => 'Chat reactivado correctamente']);
            } else {
                echo json_encode(['success' => false, 'mensaje' => 'Error al reactivar el chat']);
            }
        }
        break;

    case 'buscar_chats':
        $termino = $_GET['q'] ?? '';
        $chats = $objTelegramDAO->buscarChats($termino);
        echo json_encode(['success' => true, 'data' => $chats]);
        break;

    default:
        echo json_encode(['success' => false, 'mensaje' => 'Acción no válida']);
        break;
}

function enviarMensajePrueba($chatId) {
    $config = ConfigTelegram::getConfig();
    
    $mensaje = "🧪 *Mensaje de Prueba - Sistema de Recordatorios*\n\n" .
               "Hola, este es un mensaje de prueba del sistema de recordatorios.\n\n" .
               "📋 *Información del sistema:*\n" .
               "• Clínica: SmileCare Dental\n" .
               "• Función: Recordatorios automáticos\n" .
               "• Horario: 1 hora antes de cada cita\n\n" .
               "✅ *Estado:* Configuración correcta\n\n" .
               "_Si recibes este mensaje, todo está funcionando perfectamente._ 🦷";

    $url = $config['api_url'] . 'sendMessage';
    $payload = [
        'chat_id' => $chatId,
        'text' => $mensaje,
        'parse_mode' => 'Markdown'
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_TIMEOUT => 10
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode === 200;
}
?>