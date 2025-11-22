<?php
// configCron.php
class ConfigCron 
{
    // Configuración de reintentos y timeouts
    const MAX_INTENTOS = 3;
    const TIMEOUT_CONEXION = 15; // segundos
    const TIEMPO_ENTRE_REINTENTOS = 2; // segundos
    
    // Configuración de archivos de log
    const LOG_FILE = './logs/recordatorios.log';
    const LOG_ERROR_FILE = './logs/errores_recordatorios.log';
    const LOG_MAX_SIZE = 10485760; // 10MB en bytes
    
    // Configuración de horarios de ejecución
    const INTERVALO_EJECUCION = 5; // minutos entre ejecuciones del CRON
    const MARGEN_RECORDATORIO = 5; // minutos de margen para detectar citas (55-65 minutos)
    
    // Configuración de notificaciones
    const HABILITAR_NOTIFICACIONES_ERROR = true;
    const EMAIL_NOTIFICACION_ERROR = '2113110108@untels.edu.pe';
    
   const DB_HOST = 'localhost';
    const DB_NAME = 'opipitaltrabajo';
    const DB_USER = 'root'; // AÑADIDO: Coincide con $this->user en Conexion.php
    const DB_PASS = '';     // AÑADIDO: Coincide con $this->pass en Conexion.php
    const DB_CHARSET = 'utf8';
    
    /**
     * Registrar mensaje en el log principal
     */
    public static function log($mensaje, $tipo = 'INFO') 
    {
        // Crear el directorio de logs si no existe
        $logDir = dirname(self::LOG_FILE);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }

        $fecha = date('Y-m-d H:i:s');
        $logMensaje = "[$fecha] [$tipo] $mensaje\n";
        
        // Verificar tamaño del archivo antes de escribir
        if (file_exists(self::LOG_FILE) && filesize(self::LOG_FILE) > self::LOG_MAX_SIZE) {
            self::rotarLog();
        }
        
        file_put_contents(self::LOG_FILE, $logMensaje, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Registrar error en el log de errores
     */
    public static function logError($mensaje, $excepcion = null) 
    {
        // Crear el directorio de logs si no existe
        $logDir = dirname(self::LOG_ERROR_FILE);
        if (!is_dir($logDir)) {
            @mkdir($logDir, 0777, true);
        }
        
        $fecha = date('Y-m-d H:i:s');
        $logMensaje = "[$fecha] [ERROR] $mensaje";
        
        if ($excepcion) {
            $logMensaje .= " - Excepción: " . $excepcion->getMessage();
            $logMensaje .= " - Archivo: " . $excepcion->getFile() . ":" . $excepcion->getLine();
        }
        
        $logMensaje .= "\n";
        
        file_put_contents(self::LOG_ERROR_FILE, $logMensaje, FILE_APPEND | LOCK_EX);
        
        // Opcional: enviar notificación por email en caso de error crítico
        if (self::HABILITAR_NOTIFICACIONES_ERROR) {
            self::notificarError($mensaje, $excepcion);
        }
    }
    
    /**
     * Rotar archivo de log cuando alcance el tamaño máximo
     */
    private static function rotarLog() 
    {
        $fecha = date('Y-m-d_His');
        $nuevoNombre = self::LOG_FILE . '.' . $fecha . '.bak';
        
        if (file_exists(self::LOG_FILE)) {
            rename(self::LOG_FILE, $nuevoNombre);
        }
        
        // Comprimir el archivo antiguo para ahorrar espacio
        if (function_exists('gzcompress')) {
            $contenido = file_get_contents($nuevoNombre);
            $comprimido = gzcompress($contenido, 9);
            file_put_contents($nuevoNombre . '.gz', $comprimido);
            unlink($nuevoNombre);
        }
    }
    
    /**
     * Notificar error por email (opcional)
     */
   
    private static function notificarError($mensaje, $excepcion = null) 
    {
        // Implementación básica de notificación por email
        $asunto = "ERROR CRON - Sistema de Recordatorios Telegram";
        $cuerpo = "Se ha producido un error en el sistema de recordatorios:\n\n";
        $cuerpo .= "Mensaje: $mensaje\n";
        $cuerpo .= "Fecha: " . date('Y-m-d H:i:s') . "\n";
        
        if ($excepcion) {
            $cuerpo .= "Excepción: " . $excepcion->getMessage() . "\n";
            $cuerpo .= "Archivo: " . $excepcion->getFile() . ":" . $excepcion->getLine() . "\n";
            $cuerpo .= "Traza: " . $excepcion->getTraceAsString() . "\n";
        }
        
        // ************************************************************
        // ** CAMBIO: Descomentar para activar el envío de email **
        // ************************************************************
        // Nota: La función mail() debe estar configurada en el servidor (SMTP).
        @mail(self::EMAIL_NOTIFICACION_ERROR, $asunto, $cuerpo); 
        
        // NOTA: EL @ suprime los errores si la función mail no puede enviar el correo.
    }
    /**
     * Obtener configuración de conexión a la base de datos para el CRON
     */
    public static function getDbConfig() 
    {
        return [
            'host' => self::DB_HOST,
            'dbname' => self::DB_NAME,
            'username' => self::DB_USER,
            'password' => self::DB_PASS,
            'charset' => self::DB_CHARSET
        ];
    }
    
    /**
     * Verificar si el sistema puede ejecutar el CRON
     */
    public static function verificarRequisitos() 
    {
        $errores = [];
        
        if (!function_exists('curl_init')) {
            $errores[] = "La extensión cURL no está disponible";
        }
        
        if (!is_writable(dirname(self::LOG_FILE))) {
            $errores[] = "No hay permisos de escritura en el directorio de logs";
        }
        
        // NOTA: ConfigTelegram debe ser incluida antes de llamar a esto
        if (!class_exists('ConfigTelegram') || !ConfigTelegram::validarToken(ConfigTelegram::getBotToken())) {
            $errores[] = "El token del bot de Telegram no es válido o ConfigTelegram no está disponible";
        }
        
        return $errores;
    }
    
    /**
     * Obtener estadísticas del sistema
     */
    public static function getEstadisticas() 
    {
        $estadisticas = [
            'ultima_ejecucion' => null,
            'total_ejecuciones' => 0,
            'errores_recientes' => 0
        ];
        
        if (file_exists(self::LOG_FILE)) {
            // NOTA: Leer todo el log puede ser lento con archivos muy grandes. 
            // Esto es solo para propósitos de monitoreo básico.
            $contenido = file_get_contents(self::LOG_FILE);
            $lineas = explode("\n", $contenido);
            
            // Contar ejecuciones
            $estadisticas['total_ejecuciones'] = count(preg_grep('/INICIANDO SISTEMA DE RECORDATORIOS/', $lineas));
            
            // Obtener última ejecución
            $ejecuciones = array_reverse(preg_grep('/INICIANDO SISTEMA DE RECORDATORIOS/', $lineas));
            if (!empty($ejecuciones)) {
                $ultima = reset($ejecuciones);
                preg_match('/\[(.*?)\]/', $ultima, $matches);
                $estadisticas['ultima_ejecucion'] = $matches[1] ?? null;
            }
            
            // Contar errores recientes (últimas 24 horas)
            $hace24Horas = time() - (24 * 60 * 60);
            foreach ($lineas as $linea) {
                if (strpos($linea, '[ERROR]') !== false) {
                    preg_match('/\[(.*?)\]/', $linea, $matches);
                    if (isset($matches[1])) {
                        $fechaLinea = strtotime($matches[1]);
                        if ($fechaLinea !== false && $fechaLinea >= $hace24Horas) {
                            $estadisticas['errores_recientes']++;
                        }
                    }
                }
            }
        }
        
        return $estadisticas;
    }
}