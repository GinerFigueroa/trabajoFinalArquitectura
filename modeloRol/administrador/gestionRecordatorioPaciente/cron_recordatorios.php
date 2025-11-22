<?php
// cron_recordatorios.php - SCRIPT PRINCIPAL CRON - CLÍNICA GONZÁLEZ
require_once 'configTelegram.php';
require_once 'configCron.php';
require_once '../../../modelo/PacienteTelegramDAO.php';

class SistemaRecordatorios 
{
    private $telegramDAO;
    private $ultimaEjecucion;
    
    public function __construct() 
    {
        // El DAO se inicializa y a su vez establece la conexión a la BD
        $this->telegramDAO = new PacienteTelegramDAO(); 
        $this->ultimaEjecucion = date('Y-m-d H:i:s');
    }
    
    public function ejecutarRecordatorios() 
    {
        try {
            ConfigCron::log("🚀 INICIANDO SISTEMA DE RECORDATORIOS - CLÍNICA GONZÁLEZ");
            
            // 1. Obtener citas que están en 1 hora
            $citasProximas = $this->telegramDAO->obtenerCitasEnUnaHora();
            ConfigCron::log("📊 Citas encontradas: " . count($citasProximas));
            
            $estadisticas = [
                'enviados' => 0,
                'errores' => 0,
                'sin_telegram' => 0
            ];
            
            // 2. Procesar cada cita
            foreach ($citasProximas as $cita) {
                $this->procesarCita($cita, $estadisticas);
            }
            
            // 3. Generar reporte final
            $this->generarReporte($estadisticas);
            
        } catch (Exception $e) {
            // Si hay un error general (e.g., error de conexión a DB al inicio)
            ConfigCron::logError("Error en ejecución principal - Clínica González", $e);
            // No hacemos throw aquí para evitar que el cron job falle sin loguear.
        }
    }
    
    private function procesarCita($cita, &$estadisticas) 
    {
        try {
            ConfigCron::log("📋 Procesando cita ID: {$cita['id_cita']} - Paciente: {$cita['nombre_paciente']}");
            
            // 1. Verificar si el paciente tiene Telegram registrado
            $chatInfo = $this->telegramDAO->obtenerChatPorPaciente($cita['id_paciente']);
            
            if (!$chatInfo) {
                ConfigCron::log("❌ Paciente sin Telegram registrado - Cita ID: {$cita['id_cita']}");
                $estadisticas['sin_telegram']++;
                return;
            }
            
            if ($chatInfo['activo'] != 1) {
                ConfigCron::log("⚠️ Chat de Telegram inactivo - Cita ID: {$cita['id_cita']}");
                $estadisticas['sin_telegram']++;
                return;
            }
            
            // 2. Enviar recordatorio por Telegram
            $enviado = $this->enviarMensajeTelegram($chatInfo['chat_id'], $cita);
            
            if ($enviado) {
                // 3. Marcar como enviado en la base de datos
                $this->telegramDAO->marcarRecordatorioEnviado($cita['id_cita']);
                ConfigCron::log("✅ Recordatorio enviado - Chat ID: {$chatInfo['chat_id']}");
                $estadisticas['enviados']++;
            } else {
                ConfigCron::log("❌ Error enviando mensaje - Cita ID: {$cita['id_cita']}");
                $estadisticas['errores']++;
            }
            
        } catch (Exception $e) {
            ConfigCron::logError("Error procesando cita ID: {$cita['id_cita']}", $e);
            $estadisticas['errores']++;
        }
    }
    
    private function enviarMensajeTelegram($chatId, $cita) 
    {
        // Utilizamos la función de ConfigTelegram para construir el mensaje
        $mensaje = ConfigTelegram::construirMensajeRecordatorio($cita);
        $config = ConfigTelegram::getConfig();
        
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
            CURLOPT_POSTFIELDS => http_build_query($payload), // Usar http_build_query para POST fields
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => ConfigCron::TIMEOUT_CONEXION,
            CURLOPT_CONNECTTIMEOUT => ConfigCron::TIMEOUT_CONEXION
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            ConfigCron::log("❌ Error HTTP $httpCode - Response: $response - Error: $error");
            return false;
        }
        
        // Telegram API devuelve JSON. Debemos verificar si la respuesta es 'ok'
        $responseData = json_decode($response, true);
        if (!$responseData || $responseData['ok'] !== true) {
            $description = $responseData['description'] ?? 'No se pudo decodificar la respuesta JSON.';
            ConfigCron::log("❌ Error en respuesta de Telegram API: $description - Response: $response");
            return false;
        }

        return true;
    }
    
    private function generarReporte($estadisticas) 
    {
        $reporte = "\n📈 **REPORTE DE EJECUCIÓN - CLÍNICA GONZÁLEZ**\n" .
                     "================================\n" .
                     "✅ Recordatorios enviados: {$estadisticas['enviados']}\n" .
                     "❌ Errores de envío: {$estadisticas['errores']}\n" .
                     "⚠️  Sin Telegram: {$estadisticas['sin_telegram']}\n" .
                     "⏰ Ejecutado: {$this->ultimaEjecucion}\n" .
                     "================================\n";
        
        ConfigCron::log($reporte);
        
        // Opcional: Enviar reporte por Telegram al administrador si hay errores
        if (ConfigCron::HABILITAR_NOTIFICACIONES_ERROR && $estadisticas['errores'] > 0) {
            $this->enviarReporteAdmin($estadisticas);
        }
    }
    
   private function enviarReporteAdmin($estadisticas) 
    {
      
        $chatIdAdmin = '8492891837'; // <<< Tu ID real para recibir reportes de error
        
        $mensajeAdmin = "👨‍💼 *REPORTE ADMINISTRATIVO - CLÍNICA GONZÁLEZ*\n\n" .
                         "Ejecución: {$this->ultimaEjecucion}\n" .
                         "✅ Recordatorios enviados: {$estadisticas['enviados']}\n" .
                         "❌ Errores de envío: {$estadisticas['errores']}\n" .
                         "⚠️  Pacientes sin Telegram: {$estadisticas['sin_telegram']}";
        // Se llama directamente a la API de Telegram con un mensaje preconstruido
        $config = ConfigTelegram::getConfig();
        $url = $config['api_url'] . 'sendMessage';
        $payload = [
            'chat_id' => $chatIdAdmin,
            'text' => $mensajeAdmin,
            'parse_mode' => 'Markdown'
        ];
        
        // Simplemente ejecutamos el envío, sin la lógica de reintento/log de error detallada
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($payload), 
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 5, // Timeout más corto para notificaciones
            CURLOPT_CONNECTTIMEOUT => 5
        ]);
        curl_exec($ch);
        curl_close($ch);
    }
}

// EJECUCIÓN PRINCIPAL
if (php_sapi_name() === 'cli') {
    // Verificar requisitos básicos antes de empezar (opcional)
    if (class_exists('ConfigTelegram')) {
        $errores = ConfigCron::verificarRequisitos();
        if (!empty($errores)) {
            $errorMsg = "❌ Errores de Requisitos:\n" . implode("\n", $errores);
            ConfigCron::logError($errorMsg);
            echo $errorMsg . "\n";
            exit(1);
        }
    }
    
    $sistema = new SistemaRecordatorios();
    $sistema->ejecutarRecordatorios();
} else {
    // Bloquear acceso por navegador
    header('HTTP/1.1 403 Forbidden');
    echo "Acceso denegado - Solo ejecución por CLI";
    exit;
}