<?php
include_once('conexion.php');

class CitasTelegramDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    public function obtenerCitasDelDiaConTelegram()
    {
        $fechaHoy = date('Y-m-d');
        
        // DEBUG: Mostrar la fecha que se está usando
        error_log("Fecha hoy en PHP: " . $fechaHoy);

        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.id_paciente,
                    p.dni,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    t.nombre AS tratamiento,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico,
                    pt.chat_id,
                    pt.username_telegram,
                    c.estado
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                JOIN medicos m ON c.id_medico = m.id_medico
                JOIN usuarios um ON m.id_usuario = um.id_usuario
                JOIN paciente_telegram pt ON p.id_paciente = pt.id_paciente
                WHERE DATE(c.fecha_hora) = ?
                AND c.estado IN ('Confirmada', 'Pendiente')
                AND pt.activo = 1
                ORDER BY c.fecha_hora ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $fechaHoy);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $citas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // DEBUG: Mostrar el número de citas encontradas
        error_log("Número de citas encontradas: " . count($citas));

        return $citas;
    }

    /**
     * MÉTODO ALTERNATIVO - Para depuración
     */
    public function obtenerCitasDelDiaConTelegramAlternativo()
    {
        $fechaHoy = date('Y-m-d');
        
        // Consulta más simple y directa
        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.id_paciente,
                    c.estado,
                    p.dni,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                    t.nombre AS tratamiento,
                    pt.chat_id,
                    pt.username_telegram
                FROM citas c
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                INNER JOIN paciente_telegram pt ON p.id_paciente = pt.id_paciente
                WHERE c.fecha_hora >= ? AND c.fecha_hora < ? + INTERVAL 1 DAY
                AND c.estado IN ('Confirmada', 'Pendiente')
                AND pt.activo = 1
                ORDER BY c.fecha_hora ASC";

        $fechaInicio = $fechaHoy . " 00:00:00";
        
        error_log("🔍 [ALTERNATIVO] Fecha inicio: " . $fechaInicio);
        error_log("🔍 [ALTERNATIVO] Fecha hoy: " . $fechaHoy);

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ss", $fechaInicio, $fechaHoy);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $citas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        error_log("🔍 [ALTERNATIVO] Citas encontradas: " . count($citas));
        
        return $citas;
    }

    /**
     * MÉTODO DE EMERGENCIA - Consulta directa sin parámetros
     */
    public function obtenerCitasDelDiaConTelegramDirecto()
    {
        $fechaHoy = date('Y-m-d');
        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.id_paciente,
                    c.estado,
                    p.dni,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    t.nombre AS tratamiento,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico,
                    pt.chat_id,
                    pt.username_telegram
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                JOIN medicos m ON c.id_medico = m.id_medico
                JOIN usuarios um ON m.id_usuario = um.id_usuario
                JOIN paciente_telegram pt ON p.id_paciente = pt.id_paciente
                WHERE DATE(c.fecha_hora) = '$fechaHoy'
                AND c.estado IN ('Confirmada', 'Pendiente')
                AND pt.activo = 1
                ORDER BY c.fecha_hora ASC";

        error_log("🔍 [DIRECTO] SQL: " . $sql);
        
        $resultado = $this->connection->query($sql);
        if (!$resultado) {
            error_log("❌ [DIRECTO] Error en consulta: " . $this->connection->error);
            return [];
        }
        
        $citas = $resultado->fetch_all(MYSQLI_ASSOC);
        error_log("🔍 [DIRECTO] Citas encontradas: " . count($citas));
        
        return $citas;
    }

    /**
     * Obtiene estadísticas de citas del día
     */
    public function obtenerEstadisticasCitasDelDia()
    {
        $fechaHoy = date('Y-m-d');
        
        $sql = "SELECT 
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN pt.chat_id IS NOT NULL AND pt.activo = 1 THEN 1 ELSE 0 END) as citas_con_telegram,
                    SUM(CASE WHEN pt.chat_id IS NULL OR pt.activo = 0 THEN 1 ELSE 0 END) as citas_sin_telegram
                FROM citas c
                LEFT JOIN pacientes p ON c.id_paciente = p.id_paciente
                LEFT JOIN paciente_telegram pt ON p.id_paciente = pt.id_paciente AND pt.activo = 1
                WHERE DATE(c.fecha_hora) = ?
                AND c.estado IN ('Confirmada', 'Pendiente')";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $fechaHoy);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estadisticas = $resultado->fetch_assoc();
        $stmt->close();

        return $estadisticas;
    }

    /**
     * Registrar paciente en Telegram desde el sistema administrativo
     */
    public function registrarDesdeSistema($idPaciente, $chatId, $username = null, $firstName = null, $lastName = null)
    {
        try {
            // Verificar si ya existe
            if ($this->existeChatPaciente($idPaciente)) {
                return [
                    'success' => false,
                    'mensaje' => '❌ Este paciente ya tiene un chat de Telegram registrado'
                ];
            }

            // Verificar si el chat_id ya está en uso
            if ($this->chatIdExiste($chatId)) {
                return [
                    'success' => false,
                    'mensaje' => '❌ Este Chat ID de Telegram ya está registrado'
                ];
            }

            $sql = "INSERT INTO paciente_telegram 
                    (id_paciente, chat_id, username_telegram, first_name, last_name, activo) 
                    VALUES (?, ?, ?, ?, ?, 1)";

            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("issss", $idPaciente, $chatId, $username, $firstName, $lastName);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'mensaje' => '✅ Paciente registrado en Telegram correctamente',
                    'id_insertado' => $stmt->insert_id
                ];
            } else {
                throw new Exception("Error en la inserción: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Error en registrarDesdeSistema: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => '❌ Error al registrar: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Verificar si un paciente ya tiene un chat registrado
     */
    public function existeChatPaciente($idPaciente) 
    {
        $sql = "SELECT COUNT(*) as total FROM paciente_telegram 
                  WHERE id_paciente = ? AND activo = 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Verificar si un Chat ID de Telegram ya existe y está activo.
     */
    public function chatIdExiste($chatId) 
    {
        $sql = "SELECT COUNT(*) as total FROM paciente_telegram 
                 WHERE chat_id = ? AND activo = 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $chatId); 
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }
}
?>