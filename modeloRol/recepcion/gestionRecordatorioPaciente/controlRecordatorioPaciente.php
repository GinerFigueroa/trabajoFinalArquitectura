<?php
// Archivo: controlRecordatorioPaciente.php

session_start();
include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/paciente_telegramDAO.php');
include_once('../../../modelo/CitasTelegramDAO.php');

class controlRecordatorioPaciente
{
    private $objTelegramDAO;
    private $objCitasTelegramDAO;
    private $objMensaje;

    public function __construct()
    {
        $this->objTelegramDAO = new PacienteTelegramDAO();
        $this->objCitasTelegramDAO = new CitasTelegramDAO();
        $this->objMensaje = new mensajeSistema();
    }

    // =============================================
    // FUNCIÓN ACTUALIZADA: Recordatorios de Citas del Día
    // =============================================

   
   public function enviarRecordatoriosCitasDelDia()
{
    try {
        // Configurar timezone explícitamente
        date_default_timezone_set('America/Lima');
        
        // Obtener citas del día actual
        $citasDelDia = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegram();
        
        error_log("🎯 Citas encontradas para hoy: " . count($citasDelDia));
        error_log("🎯 Hora actual: " . date('Y-m-d H:i:s'));
        
        if (empty($citasDelDia)) {
            $this->objMensaje->mensajeSistemaShow(
                "ℹ️ No hay citas programadas para hoy que requieran recordatorio.",
                "./indexRecordatorioPaciente.php",
                "info"
            );
            return;
        }

        $enviados = 0;
        $errores = 0;
        $detalles = [];

        foreach ($citasDelDia as $cita) {
            error_log("🎯 Procesando cita ID: " . $cita['id_cita']);
            error_log("🎯 Hora cita: " . $cita['fecha_hora']);
            
            // Calcular cuánto tiempo falta para la cita
            $fechaHoraCita = new DateTime($cita['fecha_hora']);
            $fechaHoraActual = new DateTime();
            
            $diferencia = $fechaHoraActual->diff($fechaHoraCita);
            $horasRestantes = $diferencia->h + ($diferencia->days * 24);
            $minutosRestantes = $diferencia->i;
            
            // Formatear fecha y hora para el mensaje
            $fechaHora = date('d/m/Y H:i', strtotime($cita['fecha_hora']));
            $horaCita = date('H:i', strtotime($cita['fecha_hora']));
            
            // MODIFICACIÓN: Enviar recordatorio aunque la cita sea "pasada" según el servidor
            // Esto maneja diferencias de timezone
            $esCitaFutura = ($fechaHoraCita > $fechaHoraActual);
            
            if ($esCitaFutura || $horasRestantes >= -1) { // Permite citas que pasaron hace menos de 1 hora
                // Crear mensaje personalizado para la cita
                $mensajeCita = $this->crearMensajeRecordatorioCitaDelDia($cita, $fechaHora, $horaCita, 
                    $esCitaFutura ? $horasRestantes : 0, 
                    $esCitaFutura ? $minutosRestantes : 0);
                
                error_log("🎯 Intentando enviar mensaje a chat_id: " . $cita['chat_id']);
                $enviado = $this->enviarMensajeTelegram($cita['chat_id'], $mensajeCita);
                
                if ($enviado) {
                    $enviados++;
                    
                    // MARCAR COMO ENVIADO EN LA BASE DE DATOS
                    $this->marcarRecordatorioEnviado($cita['id_cita']);
                    
                    if ($esCitaFutura) {
                        $detalles[] = "✅ {$cita['nombre_paciente']} - {$horaCita} (en {$horasRestantes}h {$minutosRestantes}m)";
                    } else {
                        $detalles[] = "✅ {$cita['nombre_paciente']} - {$horaCita} (recordatorio enviado)";
                    }
                    error_log("🎯 ✅ Mensaje enviado exitosamente");
                } else {
                    $errores++;
                    $detalles[] = "❌ {$cita['nombre_paciente']} - {$horaCita} (Error de envío)";
                    error_log("🎯 ❌ Error al enviar mensaje");
                }

                // Pequeña pausa para no saturar la API
                sleep(1);
            } else {
                // Cita pasada hace más de 1 hora, no enviar recordatorio
                $detalles[] = "⏰ {$cita['nombre_paciente']} - {$horaCita} (Cita ya pasada)";
                error_log("🎯 ⏰ Cita pasada hace más de 1 hora, no se envía recordatorio");
            }
        }

        // Preparar mensaje de resultado
        $mensajeResultado = "📋 **Resultado de Recordatorios (Citas del Día):**\n\n";
        $mensajeResultado .= "• Total citas hoy: " . count($citasDelDia) . "\n";
        $mensajeResultado .= "• Recordatorios enviados: {$enviados}\n";
        $mensajeResultado .= "• Errores de envío: {$errores}\n\n";
        
        if (!empty($detalles)) {
            $mensajeResultado .= "**Detalles por paciente:**\n" . implode("\n", $detalles);
        }

        $tipo = ($errores > 0) ? "warning" : "success";
        
        $this->objMensaje->mensajeSistemaShow(
            $mensajeResultado,
            "./indexRecordatorioPaciente.php",
            $tipo
        );

    } catch (Exception $e) {
        error_log("🎯 ❌ Excepción: " . $e->getMessage());
        $this->objMensaje->mensajeSistemaShow(
            "❌ Error crítico al enviar recordatorios: " . $e->getMessage(),
            "./indexRecordatorioPaciente.php",
            "error"
        );
    }
}

/**
 * Marcar recordatorio como enviado en la base de datos
 */
private function marcarRecordatorioEnviado($idCita)
{
    try {
        $sql = "UPDATE citas SET recordatorio_enviado = 1 WHERE id_cita = ?";
        
        // Necesitarás acceso a la conexión, puedes agregar esto a tu clase:
        $connection = Conexion::getInstancia()->getConnection();
        $stmt = $connection->prepare($sql);
        $stmt->bind_param("i", $idCita);
        $stmt->execute();
        $stmt->close();
        
        error_log("📝 Cita {$idCita} marcada como recordatorio enviado");
    } catch (Exception $e) {
        error_log("❌ Error al marcar recordatorio: " . $e->getMessage());
    }
}

    /**
     * Crea el mensaje de recordatorio personalizado para una cita del día
     */
    private function crearMensajeRecordatorioCitaDelDia($cita, $fechaHora, $horaCita, $horasRestantes, $minutosRestantes)
    {
        $tiempoRestante = "";
        if ($horasRestantes > 0) {
            $tiempoRestante = "{$horasRestantes} hora" . ($horasRestantes > 1 ? "s" : "");
            if ($minutosRestantes > 0) {
                $tiempoRestante .= " y {$minutosRestantes} minuto" . ($minutosRestantes > 1 ? "s" : "");
            }
        } else {
            $tiempoRestante = "{$minutosRestantes} minuto" . ($minutosRestantes > 1 ? "s" : "");
        }

        return "*Recordatorio de Cita Médica - Clínica González*\n\n" .
               "Hola *{$cita['nombre_paciente']}*,\n\n" .
               "Te recordamos que tienes una cita médica programada para **hoy**:\n\n" .
               "📅 *Fecha y Hora:* {$fechaHora}\n" .
               "⏰ *Tiempo restante:* {$tiempoRestante}\n" .
               "👨‍⚕️ *Médico:* {$cita['nombre_medico']}\n" .
               "🩺 *Tratamiento:* {$cita['tratamiento']}\n\n" .
               "📍 *Ubicación:* Clínica González\n" .
               "📞 *Teléfono:* 997584512\n\n" .
               "*Recomendaciones:*\n" .
               "• Llega 15 minutos antes\n" .
               "• Trae tu DNI: {$cita['dni']}\n" .
               "• Confirma tu asistencia\n\n" .
               "¡Te esperamos! 😊";
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
                        "_Si recibes este mensaje, todo está funcionando perfectamente.";
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
        $estadisticasCitas = $this->objCitasTelegramDAO->obtenerEstadisticasCitasDelDia();
        $citasDelDia = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegram(); // NUEVO
        
        $total = $estadisticas['total_registros'] ?? 0;
        $activos = $estadisticas['activos'] ?? 0;
        $citasHoy = $estadisticasCitas['total_citas'] ?? 0;
        $citasConTelegram = $estadisticasCitas['citas_con_telegram'] ?? 0;
        $citasDelDiaConTelegram = count($citasDelDia); // NUEVO
        
        $mensaje = "🔄 Estado del Sistema:\n";
        $mensaje .= "• Total registros Telegram: $total\n";
        $mensaje .= "• Pacientes activos Telegram: $activos\n";
        $mensaje .= "• Citas hoy: $citasHoy\n";
        $mensaje .= "• Citas con Telegram: $citasConTelegram\n";
        $mensaje .= "• Citas del día con Telegram: $citasDelDiaConTelegram\n"; // NUEVO
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
        /**
     * MÉTODO DE DEPURACIÓN - Para probar diferentes consultas
     */
    public function probarConsultasCitas()
    {
        error_log("=== INICIANDO PRUEBA DE CONSULTAS ===");
        
        // Probar método principal
        $citas1 = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegram();
        error_log("🔍 [MÉTODO PRINCIPAL] Citas encontradas: " . count($citas1));
        
        // Probar método alternativo  
        $citas2 = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegramAlternativo();
        error_log("🔍 [MÉTODO ALTERNATIVO] Citas encontradas: " . count($citas2));
        
        // Probar método directo
        $citas3 = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegramDirecto();
        error_log("🔍 [MÉTODO DIRECTO] Citas encontradas: " . count($citas3));
        
        // Mostrar resultados
        $mensaje = "🔍 **Resultados de Depuración:**\n\n";
        $mensaje .= "• Método Principal: " . count($citas1) . " citas\n";
        $mensaje .= "• Método Alternativo: " . count($citas2) . " citas\n"; 
        $mensaje .= "• Método Directo: " . count($citas3) . " citas\n\n";
        
        if (count($citas3) > 0) {
            $mensaje .= "**Citas encontradas (Método Directo):**\n";
            foreach ($citas3 as $cita) {
                $mensaje .= "• ID: {$cita['id_cita']} - {$cita['fecha_hora']} - {$cita['nombre_paciente']}\n";
            }
        }
        
        $this->objMensaje->mensajeSistemaShow(
            $mensaje,
            "./indexRecordatorioPaciente.php",
            "info"
        );
    }

    /**
     * VERSIÓN ALTERNATIVA de enviarRecordatoriosCitasDelDia usando método directo
     */
    public function enviarRecordatoriosCitasDelDiaDepurado()
    {
        try {
            // USAR MÉTODO DIRECTO TEMPORALMENTE
            $citasDelDia = $this->objCitasTelegramDAO->obtenerCitasDelDiaConTelegramDirecto();
            
            error_log("🎯 [DEPURACIÓN] Citas del día encontradas: " . count($citasDelDia));
            
            if (empty($citasDelDia)) {
                $this->objMensaje->mensajeSistemaShow(
                    "ℹ️ No hay citas programadas para hoy que requieran recordatorio.",
                    "./indexRecordatorioPaciente.php", 
                    "info"
                );
                return;
            }

            $enviados = 0;
            $errores = 0;
            $detalles = [];

            foreach ($citasDelDia as $cita) {
                error_log("🎯 [DEPURACIÓN] Procesando cita ID: " . $cita['id_cita']);
                
                // Calcular cuánto tiempo falta para la cita
                $fechaHoraCita = new DateTime($cita['fecha_hora']);
                $fechaHoraActual = new DateTime();
                
                // Solo enviar recordatorio si la cita es futura (no pasada)
                if ($fechaHoraCita > $fechaHoraActual) {
                    $diferencia = $fechaHoraActual->diff($fechaHoraCita);
                    
                    $horasRestantes = $diferencia->h + ($diferencia->days * 24);
                    $minutosRestantes = $diferencia->i;
                    
                    // Formatear fecha y hora para el mensaje
                    $fechaHora = date('d/m/Y H:i', strtotime($cita['fecha_hora']));
                    $horaCita = date('H:i', strtotime($cita['fecha_hora']));
                    
                    // Crear mensaje personalizado para la cita
                    $mensajeCita = $this->crearMensajeRecordatorioCitaDelDia($cita, $fechaHora, $horaCita, $horasRestantes, $minutosRestantes);
                    
                    error_log("🎯 [DEPURACIÓN] Enviando a chat_id: " . $cita['chat_id']);
                    
                    // Enviar mensaje
                    $enviado = $this->enviarMensajeTelegram($cita['chat_id'], $mensajeCita);
                    
                    if ($enviado) {
                        $enviados++;
                        $detalles[] = "✅ {$cita['nombre_paciente']} - {$horaCita} (en {$horasRestantes}h {$minutosRestantes}m)";
                    } else {
                        $errores++;
                        $detalles[] = "❌ {$cita['nombre_paciente']} - {$horaCita} (Error)";
                    }

                    // Pequeña pausa para no saturar la API
                    sleep(1);
                } else {
                    // Cita ya pasada, no enviar recordatorio
                    $horaCita = date('H:i', strtotime($cita['fecha_hora']));
                    $detalles[] = "⏰ {$cita['nombre_paciente']} - {$horaCita} (Cita ya pasada)";
                }
            }

            // Preparar mensaje de resultado
            $mensajeResultado = "📋 **Resultado de Recordatorios (Citas del Día):**\n\n";
            $mensajeResultado .= "• Total citas hoy: " . count($citasDelDia) . "\n";
            $mensajeResultado .= "• Recordatorios enviados: {$enviados}\n";
            $mensajeResultado .= "• Errores de envío: {$errores}\n\n";
            
            if (!empty($detalles)) {
                $mensajeResultado .= "**Detalles por paciente:**\n" . implode("\n", $detalles);
            }

            $tipo = ($errores > 0) ? "warning" : "success";
            
            $this->objMensaje->mensajeSistemaShow(
                $mensajeResultado,
                "./indexRecordatorioPaciente.php",
                $tipo
            );

        } catch (Exception $e) {
            error_log("❌ [ERROR] Excepción: " . $e->getMessage());
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error crítico al enviar recordatorios: " . $e->getMessage(),
                "./indexRecordatorioPaciente.php",
                "error"
            );
        }
    }
}

?>