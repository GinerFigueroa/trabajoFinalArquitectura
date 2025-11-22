<?php
// Archivo: modelo/PacienteTelegramDAO.php

include_once('conexion.php'); 

class PacienteTelegramDAO 
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =============================================
    // MÉTODOS CRUD PARA paciente_telegram
    // =============================================

    /**
     * REGISTRAR - Crear nuevo registro en paciente_telegram
     */
    public function registrarChatTelegram($idPaciente, $chatId, $username, $firstName, $lastName)
    {
        try {
            $sql = "INSERT INTO paciente_telegram 
                    (id_paciente, chat_id, username_telegram, first_name, last_name, activo) 
                    VALUES (?, ?, ?, ?, ?, 1)";
            
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new Exception("Error preparando consulta: " . $this->connection->error);
            }
            
            $stmt->bind_param("iisss", $idPaciente, $chatId, $username, $firstName, $lastName);
            
            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'mensaje' => 'Registro exitoso',
                    'id_insertado' => $stmt->insert_id
                ];
            } else {
                throw new Exception("Error ejecutando consulta: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            error_log("Error en registrarChatTelegram: " . $e->getMessage());
            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    /**
     * LEER - Obtener un chat específico por ID
     */
    public function obtenerChatPorId($idChat) 
    {
        $sql = "SELECT 
                pt.id,
                pt.id_paciente,
                pt.chat_id,
                pt.username_telegram,
                pt.first_name,
                pt.last_name,
                pt.activo,
                pt.fecha_registro,
                pt.fecha_actualizacion,
                p.dni,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
            FROM paciente_telegram pt
            INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE pt.id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idChat);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chat = $resultado->fetch_assoc();
        $stmt->close();
        
        return $chat;
    }

    /**
     * ACTUALIZAR - Editar información de un chat existente
     */
   public function actualizarChatTelegram($idChat, $chatId, $username = null, $firstName = null, $lastName = null) 
{
    try {
        // Iniciar transacción
        $this->connection->autocommit(false);

        // Query parametrizado
        $sql = "UPDATE paciente_telegram 
                SET chat_id = ?,
                    username_telegram = ?,
                    first_name = ?,
                    last_name = ?,
                    fecha_actualizacion = NOW()
                WHERE id = ?";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new Exception("Error preparando query: " . $this->connection->error);
        }

        // Vincular parámetros (tipos: i-integer, s-string)
        $stmt->bind_param("isssi", $chatId, $username, $firstName, $lastName, $idChat);

        // Ejecutar y verificar
        if (!$stmt->execute()) {
            throw new Exception("Error ejecutando update: " . $stmt->error);
        }

        // Confirmar cambios
        $this->connection->commit();
        return true;

    } catch(Exception $e) {
        // Revertir cambios en caso de error
        $this->connection->rollback();
        error_log("[" . date('Y-m-d H:i:s') . "] Error DAO: " . $e->getMessage());
        return false;
    } finally {
        // Restaurar modo autocommit
        $this->connection->autocommit(true);
        if (isset($stmt)) $stmt->close();
    }
}


    /**
     * ELIMINAR - Desactivar un chat de Telegram (eliminación lógica)
     */
    public function eliminarChatTelegram($idChat) 
    {
        $sql = "UPDATE paciente_telegram 
                SET activo = 0, fecha_actualizacion = NOW()
                WHERE id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idChat);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * REACTIVAR - Reactivar un chat previamente desactivado
     */
    public function reactivarChatTelegram($idChat) 
    {
        $sql = "UPDATE paciente_telegram 
                SET activo = 1, fecha_actualizacion = NOW()
                WHERE id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idChat);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    // =============================================
    // MÉTODOS DE CONSULTA PARA paciente_telegram
    // =============================================

    /**
     * Obtener todos los chats de Telegram registrados
     */
    public function obtenerTodosChatsTelegram() 
    {
        $sql = "SELECT 
                pt.id,
                pt.id_paciente,
                pt.chat_id,
                pt.username_telegram,
                pt.first_name,
                pt.last_name,
                pt.activo,
                pt.fecha_registro,
                pt.fecha_actualizacion,
                p.dni,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
            FROM paciente_telegram pt
            INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE pt.activo = 1
            ORDER BY pt.fecha_registro DESC";

        $resultado = $this->connection->query($sql);
        $chats = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        
        return $chats;
    }

    /**
     * Obtener el chat de Telegram por ID de paciente
     */
    public function obtenerChatPorPaciente($idPaciente) 
    {
        $sql = "SELECT * FROM paciente_telegram 
                WHERE id_paciente = ? AND activo = 1 
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chat = $resultado->fetch_assoc();
        $stmt->close();
        
        return $chat;
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
     * Buscar chats por término (nombre, username, DNI)
     */
    public function buscarChats($termino) 
    {
        $sql = "SELECT 
                pt.id,
                pt.id_paciente,
                pt.chat_id,
                pt.username_telegram,
                pt.first_name,
                pt.last_name,
                pt.activo,
                pt.fecha_registro,
                p.dni,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
            FROM paciente_telegram pt
            INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE pt.activo = 1 AND (
                u.nombre LIKE ? OR
                u.apellido_paterno LIKE ? OR
                p.dni LIKE ? OR
                pt.username_telegram LIKE ? OR
                pt.first_name LIKE ? OR
                pt.last_name LIKE ?
            )
            ORDER BY pt.fecha_registro DESC";

        $stmt = $this->connection->prepare($sql);
        $terminoBusqueda = "%$termino%";
        $stmt->bind_param("ssssss", $terminoBusqueda, $terminoBusqueda, $terminoBusqueda, 
                         $terminoBusqueda, $terminoBusqueda, $terminoBusqueda);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chats = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $chats;
    }

    /**
     * Verificar la propiedad del chat (seguridad)
     */
    public function verificarPropiedadChat($idChat, $idUsuario) 
    {
        $sql = "SELECT COUNT(*) as total 
                FROM paciente_telegram pt
                INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
                WHERE pt.id = ? AND p.id_usuario = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $idChat, $idUsuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Obtener estadísticas de los registros de Telegram
     */
    public function obtenerEstadisticasTelegram() 
    {
        $sql = "SELECT 
                COUNT(*) as total_registros,
                SUM(CASE WHEN activo = 1 THEN 1 ELSE 0 END) as activos,
                SUM(CASE WHEN activo = 0 THEN 1 ELSE 0 END) as inactivos,
                COUNT(DISTINCT id_paciente) as pacientes_unicos
            FROM paciente_telegram";

        $resultado = $this->connection->query($sql);
        $estadisticas = $resultado ? $resultado->fetch_assoc() : [
            'total_registros' => 0,
            'activos' => 0,
            'inactivos' => 0,
            'pacientes_unicos' => 0
        ];
        
        return $estadisticas;
    }

    // =============================================
    // MÉTODOS DE CITAS SOLO PARA RECORDATORIOS
    // =============================================

    /**
     * Obtener citas que están en 1 hora (para recordatorios automáticos)
     */
    public function obtenerCitasEnUnaHora() 
    {
        $sql = "SELECT 
                c.id_cita, 
                c.id_paciente, 
                c.fecha_hora, 
                c.id_tratamiento,
                t.nombre as nombre_tratamiento,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente,
                m.id_medico,
                CONCAT(um.nombre, ' ', um.apellido_paterno) as nombre_medico
            FROM citas c
            INNER JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            LEFT JOIN medicos m ON c.id_medico = m.id_medico
            LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
            WHERE c.estado IN ('Pendiente', 'Confirmada')
            AND c.fecha_hora BETWEEN DATE_ADD(NOW(), INTERVAL 55 MINUTE) 
                                AND DATE_ADD(NOW(), INTERVAL 65 MINUTE)
            AND c.recordatorio_enviado = 0
            ORDER BY c.fecha_hora ASC";

        $resultado = $this->connection->query($sql);
        $citas = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        
        return $citas;
    }

    /**
     * Marcar recordatorio como enviado
     */
    public function marcarRecordatorioEnviado($idCita) 
    {
        $sql = "UPDATE citas SET recordatorio_enviado = 1 WHERE id_cita = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idCita);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    // =============================================
    // MÉTODOS AUXILIARES PARA ENTIDADES
    // =============================================

    /**
     * Obtener pacientes disponibles (todos los pacientes activos)
     */
    public function obtenerPacientesDisponibles() 
    {
        $sql = "SELECT p.id_paciente, p.dni, 
                       CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtener pacientes que NO tienen registro en Telegram
     */
    public function obtenerPacientesSinTelegram() 
    {
        $sql = "SELECT p.id_paciente, p.dni, 
                       CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_completo
                FROM pacientes p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.activo = 1 
                AND p.id_paciente NOT IN (
                    SELECT id_paciente FROM paciente_telegram WHERE activo = 1
                )
                ORDER BY u.apellido_paterno, u.nombre";

        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Verificar si un Chat ID ya está en uso
     */
    public function chatIdExiste($chatId) 
    {
        $sql = "SELECT COUNT(*) as total FROM paciente_telegram 
                WHERE chat_id = ? AND activo = 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Obtener información completa de un paciente por ID
     */
    public function obtenerPacienteCompleto($idPaciente) 
    {
        $sql = "SELECT 
                p.id_paciente,
                p.dni,
                p.fecha_nacimiento,
                p.ocupacion,
                p.domicilio,
                p.distrito,
                p.edad,
                p.sexo,
                p.estado_civil,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_completo,
                u.telefono,
                u.email
            FROM pacientes p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE p.id_paciente = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $paciente = $resultado->fetch_assoc();
        $stmt->close();
        
        return $paciente;
    }

    /**
     * Limpiar registros inactivos antiguos (más de 30 días)
     */
    public function limpiarRegistrosInactivos() 
    {
        $sql = "DELETE FROM paciente_telegram 
                WHERE activo = 0 
                AND fecha_actualizacion < DATE_SUB(NOW(), INTERVAL 30 DAY)";

        $stmt = $this->connection->prepare($sql);
        $resultado = $stmt->execute();
        $filasAfectadas = $stmt->affected_rows;
        $stmt->close();
        
        return [
            'success' => $resultado,
            'filas_afectadas' => $filasAfectadas
        ];
    }

    /**
     * Obtener chat por Chat ID de Telegram
     */
    public function obtenerChatPorChatId($chatId) 
    {
        $sql = "SELECT 
                pt.id,
                pt.id_paciente,
                pt.chat_id,
                pt.username_telegram,
                pt.first_name,
                pt.last_name,
                pt.activo,
                p.dni,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
            FROM paciente_telegram pt
            INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE pt.chat_id = ? AND pt.activo = 1
            LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $chatId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chat = $resultado->fetch_assoc();
        $stmt->close();
        
        return $chat;
    }
    /**
 * Verificar si un paciente existe
 */
public function pacienteExiste($idPaciente) 
{
    $sql = "SELECT COUNT(*) as total FROM pacientes WHERE id_paciente = ?";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idPaciente);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();
    
    return $count > 0;
}
/**
 * Verificar si un paciente existe
 */
// Agregar este método para mejor control de citas
/**
 * Obtener citas que requieren recordatorio y tienen chat de Telegram activo.
 * (Busca citas programadas para dentro de X minutos, usando un margen)
 */
public function obtenerCitasParaRecordatorio($minutosAntes = 60) 
{
    // Citas que están a X minutos de ocurrir, que están Pendientes/Confirmadas y cuyo recordatorio no ha sido enviado.
    $sql = "SELECT 
                c.id_cita, 
                c.id_paciente, 
                c.fecha_hora, 
                c.id_tratamiento,
                t.nombre as nombre_tratamiento,
                CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente,
                m.id_medico,
                CONCAT(um.nombre, ' ', um.apellido_paterno) as nombre_medico,
                pt.chat_id -- << AÑADIDO: ID de Telegram
            FROM citas c
            INNER JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
            INNER JOIN pacientes p ON c.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN paciente_telegram pt ON c.id_paciente = pt.id_paciente -- << AÑADIDO: JOIN a paciente_telegram
            LEFT JOIN medicos m ON c.id_medico = m.id_medico
            LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
            WHERE c.estado IN ('Pendiente', 'Confirmada')
            AND c.fecha_hora BETWEEN 
                DATE_ADD(NOW(), INTERVAL ? MINUTE) 
                AND DATE_ADD(NOW(), INTERVAL ? MINUTE)
            AND c.recordatorio_enviado = 0
            AND pt.activo = 1 -- << AÑADIDO: Solo si el registro de Telegram está activo
            ORDER BY c.fecha_hora ASC";

    $stmt = $this->connection->prepare($sql);
    $margen = 5; // Margen de ±5 minutos para el cron
    $minRango = $minutosAntes - $margen;
    $maxRango = $minutosAntes + $margen;
    
    // NOTA: El primer parámetro del rango debe ser el más pequeño, por eso usamos $minRango
    $stmt->bind_param("ii", $minRango, $maxRango); 
    $stmt->execute();
    $resultado = $stmt->get_result();
    $citas = $resultado->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    
    return $citas;
}

}
?>