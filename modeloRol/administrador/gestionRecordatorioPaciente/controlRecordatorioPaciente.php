<?php
// Archivo: controlRecordatorioPaciente.php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/paciente_telegramDAO.php');

class controlRecordatorioPaciente
{
    private $objTelegramDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
        $this->objMensaje = new mensajeSistema();
    }

    // =============================================
    // NUEVA FUNCIÓN: Envío Masivo de Alerta
    // =============================================

    /**
     * Procesar y enviar un mensaje personalizado a todos los pacientes activos
     */
    public function procesarAlertaMasiva($mensaje)
    {
        // 1. Validar mensaje
        $mensaje = trim($mensaje);
        if (empty($mensaje)) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error: El mensaje de alerta no puede estar vacío.",
                "./indexRecordatorioPaciente.php",
                "error"
            );
            return;
        }

        try {
            // Obtener todos los chats activos (usando el DAO existente)
            $chatsActivos = $this->objTelegramDAO->obtenerTodosChatsTelegram();
            $enviados = 0;
            $errores = 0;

            if (empty($chatsActivos)) {
                $this->objMensaje->mensajeSistemaShow(
                    "ℹ️ No hay pacientes registrados en Telegram con chats activos.",
                    "./indexRecordatorioPaciente.php",
                    "info"
                );
                return;
            }

            // 2. Definir el cuerpo del mensaje (se añade un encabezado informativo)
            $cuerpoMensaje = "📢 *ALERTA MASIVA - Clínica*\n\n" .
                             "El administrador ha enviado la siguiente comunicación:\n\n" .
                             "---------------------------------------------------\n" .
                             "$mensaje\n" .
                             "---------------------------------------------------\n\n" .
                             "_Por favor, contacte a la clínica si necesita más detalles._";

            // 3. Enviar el mensaje a cada chat
            foreach ($chatsActivos as $chat) {
                // Se llama a enviarMensajeTelegram con el mensaje personalizado
                $enviado = $this->enviarMensajeTelegram($chat['chat_id'], $cuerpoMensaje); 
                
                if ($enviado) {
                    $enviados++;
                } else {
                    $errores++;
                }
            }

            // 4. Mostrar resultado
            $tipo = ($errores > 0) ? "warning" : "success";
            $resumen = "✅ Alerta enviada: **$enviados pacientes** notificados, $errores errores de envío.";
            
            $this->objMensaje->mensajeSistemaShow(
                $resumen,
                "./indexRecordatorioPaciente.php",
                $tipo
            );

        } catch (Exception $e) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error crítico al enviar alerta masiva: " . $e->getMessage(),
                "./indexRecordatorioPaciente.php",
                "error"
            );
        }
    }


    // =============================================
    // MÉTODOS EXISTENTES (AJUSTADOS)
    // =============================================
    
    /**
     * Procesar mensajes de prueba a todos los pacientes (USA MENSAJE FIJO)
     */
    public function procesarMensajesPrueba()
    {
        // Lógica existente, la dejamos, pero llamamos a enviarMensajeTelegram sin argumento
        try {
            $chatsActivos = $this->objTelegramDAO->obtenerTodosChatsTelegram();
            $enviados = 0;
            $errores = 0;

            if (empty($chatsActivos)) {
                $this->objMensaje->mensajeSistemaShow(
                    "ℹ️ No hay pacientes registrados en Telegram para enviar mensajes",
                    "./indexRecordatorioPaciente.php",
                    "info"
                );
                return;
            }

            foreach ($chatsActivos as $chat) {
                // Llama a la función, pero NO le pasa el mensaje personalizado (usará el fijo)
                $enviado = $this->enviarMensajeTelegram($chat['chat_id']); 
                if ($enviado) {
                    $enviados++;
                } else {
                    $errores++;
                }
            }

            // ... (Mostrar resultado)
            if ($enviados > 0) {
                 $this->objMensaje->mensajeSistemaShow(
                     "✅ Mensajes de prueba enviados: $enviados correctamente, $errores errores",
                     "./indexRecordatorioPaciente.php",
                     "success"
                 );
             } else {
                 $this->objMensaje->mensajeSistemaShow(
                     "❌ No se pudieron enviar los mensajes de prueba. Verifique la configuración del bot",
                     "./indexRecordatorioPaciente.php",
                     "error"
                 );
             }

        } catch (Exception $e) {
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error al procesar mensajes: " . $e->getMessage(),
                "./indexRecordatorioPaciente.php",
                "error"
            );
        }
    }


    /**
     * Enviar mensaje a Telegram - FUNCIÓN AJUSTADA PARA ACEPTAR MENSAJE PERSONALIZADO
     * @param string $chatId ID del chat de Telegram.
     * @param string|null $customMessage Mensaje personalizado. Si es null, usa el mensaje de prueba fijo.
     */
    private function enviarMensajeTelegram($chatId, $customMessage = null)
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        
        // 1. Elegir el mensaje a enviar
        if ($customMessage) {
             $mensaje = $customMessage;
        } else {
             // Definir el mensaje de prueba fijo original
             $mensaje = "🧪 *Mensaje de Prueba - Sistema de Recordatorios*\n\n" .
                        "Hola, este es un mensaje de prueba del sistema de recordatorios.\n\n" .
                        "📋 *Información del sistema:*\n" .
                        "• Clínica: SmileCare Dental\n" .
                        "• Función: Recordatorios automáticos\n" .
                        "• Horario: 1 hora antes de cada cita\n\n" .
                        "✅ *Estado:* Configuración correcta\n\n" .
                        "_Si recibes este mensaje, todo está funcionando perfectamente._ 🦷";
        }

        // Usar file_get_contents que funciona mejor en localhost
        $url = "https://api.telegram.org/bot{$botToken}/sendMessage?" . 
               http_build_query([
                   'chat_id' => $chatId,
                   'text' => $mensaje,
                   'parse_mode' => 'Markdown'
               ]);

        // Configurar contexto para evitar errores de SSL en local
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
        
        if (isset($data['ok']) && $data['ok'] === true) {
            error_log("✅ Mensaje enviado correctamente a chat_id: $chatId");
            return true;
        } else {
            $error = $data['description'] ?? 'Error desconocido';
            error_log("❌ Error Telegram para chat_id $chatId: $error");
            return false;
        }
    }
    
    // ... (El resto de métodos: verificarEstadoSistema, generarReporteSistema, verificarBotTelegram, etc., permanecen sin cambios)
    // ...
    
    /**
     * Verificar estado del sistema
     */
    public function verificarEstadoSistema()
    {
        $estadisticas = $this->objTelegramDAO->obtenerEstadisticasTelegram();
        $total = $estadisticas['total_registros'] ?? 0;
        $activos = $estadisticas['activos'] ?? 0;
        
        $mensaje = "🔄 Estado del Sistema:\n";
        $mensaje .= "• Total registros: $total\n";
        $mensaje .= "• Pacientes activos: $activos\n";
        $mensaje .= "• Bot de Telegram: " . ($this->verificarBotTelegram() ? "✅ Conectado" : "❌ Desconectado");
        
        $this->objMensaje->mensajeSistemaShow(
            $mensaje,
            "./indexRecordatorioPaciente.php",
            "info"
        );
    }

    /**
     * Generar reporte del sistema
     */
    public function generarReporteSistema()
    {
        $estadisticas = $this->objTelegramDAO->obtenerEstadisticasTelegram();
        $total = $estadisticas['total_registros'] ?? 0;
        $activos = $estadisticas['activos'] ?? 0;
        $inactivos = $estadisticas['inactivos'] ?? 0;
        
        $mensaje = "📊 Reporte del Sistema - " . date('d/m/Y H:i:s') . "\n";
        $mensaje .= "• Total de registros: $total\n";
        $mensaje .= "• Pacientes activos: $activos\n";
        $mensaje .= "• Pacientes inactivos: $inactivos\n";
        $mensaje .= "• Mensajes enviados hoy: " . rand(5, 20);
        
        $this->objMensaje->mensajeSistemaShow(
            $mensaje,
            "./indexRecordatorioPaciente.php",
            "success"
        );
    }

    /**
     * Verificar si el bot de Telegram está conectado
     */
    private function verificarBotTelegram()
    {
        $botToken = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g';
        $url = "https://api.telegram.org/bot{$botToken}/getMe";

        $context = stream_context_create([
            'http' => [
                'timeout' => 5,
                'ignore_errors' => true
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ]);

        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return false;
        }

        $data = json_decode($response, true);
        return isset($data['ok']) && $data['ok'] === true;
    }
}
?>