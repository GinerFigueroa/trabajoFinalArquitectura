<?php
include_once('conexion.php'); 

class PacienteTelegramDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Registra un nuevo chat de Telegram para un paciente
     */
    public function registrarChatTelegram($idPaciente, $chatId, $username = null, $firstName = null, $lastName = null)
    {
        $sql = "INSERT INTO paciente_telegram (id_paciente, chat_id, username_telegram, first_name, last_name, activo) 
                VALUES (?, ?, ?, ?, ?, 1) 
                ON DUPLICATE KEY UPDATE 
                username_telegram = VALUES(username_telegram),
                first_name = VALUES(first_name),
                last_name = VALUES(last_name),
                activo = 1,
                fecha_actualizacion = NOW()";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iisss", $idPaciente, $chatId, $username, $firstName, $lastName);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Obtiene todos los chats de Telegram registrados
     */
    public function obtenerTodosChatsTelegram()
    {
        $sql = "SELECT pt.*, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
                FROM paciente_telegram pt
                JOIN pacientes p ON pt.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE pt.activo = 1
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        $chats = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $chats[] = $fila;
            }
        }
        
        return $chats;
    }

    /**
     * Obtiene un chat específico por ID
     */
    public function obtenerChatPorId($idChat)
    {
        $sql = "SELECT pt.*, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
                FROM paciente_telegram pt
                JOIN pacientes p ON pt.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
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
     * Obtiene el chat de un paciente específico
     */
    public function obtenerChatPorPaciente($idPaciente)
    {
        $sql = "SELECT pt.*, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
                FROM paciente_telegram pt
                JOIN pacientes p ON pt.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE pt.id_paciente = ? AND pt.activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chat = $resultado->fetch_assoc();
        $stmt->close();
        
        return $chat;
    }

    /**
     * Actualiza un chat existente
     */
    public function actualizarChatTelegram($idChat, $chatId, $username = null, $firstName = null, $lastName = null)
    {
        $sql = "UPDATE paciente_telegram 
                SET chat_id = ?, username_telegram = ?, first_name = ?, last_name = ?, 
                    fecha_actualizacion = NOW() 
                WHERE id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("isssi", $chatId, $username, $firstName, $lastName, $idChat);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Desactiva un chat (eliminación lógica)
     */
    public function desactivarChatTelegram($idChat)
    {
        $sql = "UPDATE paciente_telegram SET activo = 0, fecha_actualizacion = NOW() WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idChat);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Verifica si un paciente tiene Telegram registrado
     */
    public function pacienteTieneTelegram($idPaciente)
    {
        $sql = "SELECT COUNT(*) as total FROM paciente_telegram WHERE id_paciente = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Obtiene pacientes que NO tienen Telegram registrado
     */
    public function obtenerPacientesSinTelegram()
    {
        $sql = "SELECT p.id_paciente, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente, u.email, u.telefono
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.activo = 1 
                AND p.id_paciente NOT IN (
                    SELECT id_paciente FROM paciente_telegram WHERE activo = 1
                )
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Busca chats por nombre de paciente o username de Telegram
     */
    public function buscarChats($termino)
    {
        $sql = "SELECT pt.*, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
                FROM paciente_telegram pt
                JOIN pacientes p ON pt.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE pt.activo = 1 
                AND (u.nombre LIKE ? OR u.apellido_paterno LIKE ? OR pt.username_telegram LIKE ?)
                ORDER BY u.apellido_paterno, u.nombre";
        
        $terminoLike = "%$termino%";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sss", $terminoLike, $terminoLike, $terminoLike);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $chats = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $chats;
    }
}
?>b