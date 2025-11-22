<?php
// configTelegram.php
class ConfigTelegram 
{
    // Configuración del Bot de Telegram
    // *********************************************************************
    // ** ACTUALIZADO CON TUS DATOS DEL BOTFATHER  ************************
    // *********************************************************************
    const BOT_TOKEN = '8373740218:AAGgap4PguZUSkszklilyTbHxbdszeYWR3g'; // El token que te dio BotFather
    const BOT_USERNAME = 'prueba_paciente_bot'; // El nombre de usuario que elegiste
    
    // Configuración de la API de Telegram
    const API_BASE_URL = 'https://api.telegram.org/bot';
    const TIMEOUT = 10;
    
    // Configuración de mensajes - CLÍNICA GONZÁLEZ
    const MENSAJE_PRUEBA = "🏥 *Mensaje de Prueba - Clínica González*\n\nHola, este es un mensaje de prueba del sistema de recordatorios de *Clínica González*.\n\n✅ Si recibes este mensaje, tu configuración de Telegram está correcta.\n\n📅 Recibirás recordatorios automáticos 1 hora antes de tus consultas médicas.\n\n_¡Gracias por confiar en nosotros!_ 👨‍⚕️";
    
    const MENSAJE_RECORDATORIO = "🏥 *RECORDATORIO DE CITA - CLÍNICA GONZÁLEZ*\n\nHola {nombre_paciente},\n\nSu cita médica es en *1 hora*:\n\n📅 *Fecha y Hora:* {fecha_hora}\n🩺 *Especialidad:* {tratamiento}\n👨‍⚕️ *Médico:* {medico}\n\n📍 *Lugar:* Clínica González\n🏢 *Dirección:* Av. Ignacio Merino 1884, Lince\n📞 *WhatsApp:* 997 584 512\n📞 *Teléfono:* (01) 471-1579\n\n💡 *Recomendaciones:*\n• Llegue 15 minutos antes\n• Traer DNI y orden médica si tiene\n• Confirmar su asistencia\n\n_Si no puede asistir, responda a este mensaje para reprogramar._";

    /**
     * Obtener la configuración completa del bot
     */
    public static function getConfig() 
    {
        return [
            'bot_token' => self::BOT_TOKEN,
            'bot_username' => self::BOT_USERNAME,
            'api_url' => self::API_BASE_URL . self::BOT_TOKEN . '/',
            'timeout' => self::TIMEOUT
        ];
    }
    
    /**
     * Obtener solo el token del bot
     */
    public static function getBotToken() 
    {
        return self::BOT_TOKEN;
    }
    
    /**
     * Obtener la URL base de la API
     */
    public static function getApiUrl() 
    {
        return self::API_BASE_URL . self::BOT_TOKEN . '/';
    }
    
    /**
     * Construir mensaje de recordatorio personalizado - CLÍNICA GONZÁLEZ
     */
    public static function construirMensajeRecordatorio($datosCita) 
    {
        $fechaHora = date('d/m/Y \a \l\a\s H:i', strtotime($datosCita['fecha_hora']));
        
        $mensaje = str_replace(
            [
                '{nombre_paciente}',
                '{fecha_hora}', 
                '{tratamiento}',
                '{medico}'
            ],
            [
                $datosCita['nombre_paciente'] ?? 'Estimado paciente',
                $fechaHora,
                $datosCita['nombre_tratamiento'] ?? 'Consulta médica',
                $datosCita['nombre_medico'] ?? 'Médico asignado'
            ],
            self::MENSAJE_RECORDATORIO
        );
        
        return $mensaje;
    }
    
    /**
     * Validar formato del token del bot
     */
    public static function validarToken($token) 
    {
        return preg_match('/^\d+:[\w-]+$/', $token);
    }
}