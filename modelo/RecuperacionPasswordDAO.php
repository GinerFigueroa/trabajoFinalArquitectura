<?php
include_once('Conexion.php');

class RecuperacionPasswordDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Verifica si un email existe y está activo
     */
    public function validarEmailExiste($email)
    {
        $sql = "SELECT u.id_usuario, u.email, u.usuario_usuario, 
                       p.id_paciente, pt.chat_id, pt.activo as telegram_activo
                FROM usuarios u
                LEFT JOIN pacientes p ON u.id_usuario = p.id_usuario
                LEFT JOIN paciente_telegram pt ON p.id_paciente = pt.id_paciente AND pt.activo = 1
                WHERE u.email = ? AND u.activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        
        return $usuario;
    }

    /**
     * Genera y guarda un código de verificación
     */
    public function generarCodigoVerificacion($idUsuario, $codigo, $expiracionMinutos = 15)
    {
        // Primero eliminar códigos anteriores del usuario
        $this->eliminarCodigosAnteriores($idUsuario);

        $fechaExpiracion = date('Y-m-d H:i:s', strtotime("+{$expiracionMinutos} minutes"));
        
        $sql = "INSERT INTO codigos_verificacion (id_usuario, codigo, fecha_expiracion) 
                VALUES (?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iss", $idUsuario, $codigo, $fechaExpiracion);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Verifica un código de verificación
     */
    public function verificarCodigo($idUsuario, $codigo)
    {
        $sql = "SELECT id, fecha_expiracion 
                FROM codigos_verificacion 
                WHERE id_usuario = ? AND codigo = ? AND utilizado = 0 
                AND fecha_expiracion > NOW()";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("is", $idUsuario, $codigo);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $codigoData = $resultado->fetch_assoc();
        $stmt->close();
        
        if ($codigoData) {
            // Marcar como utilizado
            $this->marcarCodigoUtilizado($codigoData['id']);
            return true;
        }
        
        return false;
    }

    /**
     * Actualiza la contraseña del usuario
     */
    public function actualizarPassword($idUsuario, $nuevaPasswordHash)
    {
        $sql = "UPDATE usuarios SET usuario_clave = ? WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $nuevaPasswordHash, $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Elimina códigos anteriores del usuario
     */
    private function eliminarCodigosAnteriores($idUsuario)
    {
        $sql = "DELETE FROM codigos_verificacion WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->close();
    }

    /**
     * Marca un código como utilizado
     */
    private function marcarCodigoUtilizado($idCodigo)
    {
        $sql = "UPDATE codigos_verificacion SET utilizado = 1 WHERE id = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idCodigo);
        $stmt->execute();
        $stmt->close();
    }
}
?>