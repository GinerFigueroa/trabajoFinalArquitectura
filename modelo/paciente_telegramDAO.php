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
            
            $stmt->bind_param("issss", $idPaciente, $chatId, $username, $firstName, $lastName); 
            
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
                    pt.id, pt.id_paciente, pt.chat_id, pt.username_telegram, pt.first_name, 
                    pt.last_name, pt.activo, pt.fecha_registro, pt.fecha_actualizacion, 
                    p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
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
      
    /**
     * Obtener lista de pacientes que NO tienen chat de Telegram activo
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
     * Obtener todos los chats de Telegram registrados
     */
    public function obtenerTodosChatsTelegram() 
    {
        $sql = "SELECT 
                    pt.id, pt.id_paciente, pt.chat_id, pt.username_telegram, pt.first_name, 
                    pt.last_name, pt.activo, pt.fecha_registro, pt.fecha_actualizacion, 
                    p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
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
        $sql = "SELECT chat_id, activo FROM paciente_telegram 
                  WHERE id_paciente = ? 
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

    /**
     * Buscar chats por término (nombre, username, DNI)
     */
    public function buscarChats($termino) 
    {
        $sql = "SELECT 
                    pt.id, pt.id_paciente, pt.chat_id, pt.username_telegram, pt.first_name, 
                    pt.last_name, pt.activo, pt.fecha_registro, p.dni, 
                    CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
                FROM paciente_telegram pt
                INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE pt.activo = 1 AND (
                    u.nombre LIKE ? OR u.apellido_paterno LIKE ? OR p.dni LIKE ? OR 
                    pt.username_telegram LIKE ? OR pt.first_name LIKE ? OR pt.last_name LIKE ?
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

    /**
     * Obtener chat por Chat ID de Telegram
     */
    public function obtenerChatPorChatId($chatId) 
    {
        $sql = "SELECT 
                    pt.id, pt.id_paciente, pt.chat_id, pt.username_telegram, pt.first_name, 
                    pt.last_name, pt.activo, p.dni, 
                    CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
                FROM paciente_telegram pt
                INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE pt.chat_id = ? AND pt.activo = 1
                LIMIT 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $chatId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chat = $resultado->fetch_assoc();
        $stmt->close();
        
        return $chat;
    }

    /**
     * Obtener todos los chats (activos e inactivos) - COMPLETO
     */
    public function obtenerTodosChatsTelegramCompleto() 
    {
        $sql = "SELECT 
                    pt.id, pt.id_paciente, pt.chat_id, pt.username_telegram, pt.first_name, 
                    pt.last_name, pt.activo, pt.fecha_registro, pt.fecha_actualizacion, 
                    p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
                FROM paciente_telegram pt
                INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY pt.activo DESC, pt.fecha_registro DESC";

        $resultado = $this->connection->query($sql);
        $chats = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        
        return $chats;
    }

    /**
     * Buscar chats por término (incluyendo inactivos)
     */
    public function buscarChatsCompleto($termino) 
    {
        $sql = "SELECT 
                    pt.id, pt.id_paciente, pt.chat_id, pt.username_telegram, pt.first_name, 
                    pt.last_name, pt.activo, pt.fecha_registro, p.dni, 
                    CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente
                FROM paciente_telegram pt
                INNER JOIN pacientes p ON pt.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE (
                    u.nombre LIKE ? OR u.apellido_paterno LIKE ? OR p.dni LIKE ? OR 
                    pt.username_telegram LIKE ? OR pt.first_name LIKE ? OR pt.last_name LIKE ?
                )
                ORDER BY pt.activo DESC, pt.fecha_registro DESC";

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
     * ACTUALIZAR - Editar información de un chat existente
     */
    public function actualizarChatTelegram($idChat, $chatId, $username = null, $firstName = null, $lastName = null) 
    {
        try {
            // Validaciones más estrictas
            if (empty($idChat) || $idChat <= 0) {
                throw new Exception("ID de chat inválido");
            }

            if (empty($chatId) || !is_numeric($chatId) || $chatId <= 0) {
                throw new Exception("Chat ID debe ser un número positivo");
            }

            // Iniciar transacción
            $this->connection->autocommit(false);

            // Query con manejo adecuado de NULL
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

            // Manejar NULL correctamente (usar null en lugar de string vacío)
            $username = (!empty($username)) ? $username : null;
            $firstName = (!empty($firstName)) ? $firstName : null;
            $lastName = (!empty($lastName)) ? $lastName : null;
            
            // CORRECCIÓN: Usar "ssssi" en lugar de "isssi"
            $stmt->bind_param("ssssi", $chatId, $username, $firstName, $lastName, $idChat);

            // Ejecutar
            if (!$stmt->execute()) {
                throw new Exception("Error ejecutando update: " . $stmt->error);
            }

            // Verificar si se actualizó
            if ($stmt->affected_rows === 0) {
                throw new Exception("No se encontró el registro con ID: $idChat");
            }

            $this->connection->commit();
            
            return [
                'success' => true,
                'mensaje' => '✅ Registro actualizado correctamente',
                'filas_afectadas' => $stmt->affected_rows
            ];

        } catch(Exception $e) {
            $this->connection->rollback();
            error_log("Error en actualizarChatTelegram: " . $e->getMessage());
            
            return [
                'success' => false,
                'mensaje' => '❌ ' . $e->getMessage()
            ];
        } finally {
            $this->connection->autocommit(true);
            if (isset($stmt)) $stmt->close();
        }
    }
    /**
 * Verificar si un paciente existe en la base de datos
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
     * Obtener información básica del paciente
     */
    public function obtenerInfoPaciente($idPaciente) 
    {
        $sql = "SELECT 
                    p.id_paciente, p.dni,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) as nombre_completo,
                    u.email, u.telefono
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
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
     * Verificar si el paciente está activo
     */
    public function pacienteActivo($idPaciente) 
    {
        $sql = "SELECT COUNT(*) as total 
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.id_paciente = ? AND u.activo = 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }
}
?>