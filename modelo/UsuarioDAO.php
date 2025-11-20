<?php
include_once('Conexion.php'); 

class UsuarioDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    public function obtenerTodosUsuarios()
    {
        $sql = "SELECT u.id_usuario, u.usuario_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.email, u.telefono, u.id_rol, u.activo, r.nombre as rol_nombre
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.id_usuario DESC";

        $resultado = $this->connection->query($sql);
        $usuarios = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $usuarios[] = $fila;
            }
        }
        return $usuarios;
    }

    public function obtenerUsuarioPorId($idUsuario)
    {
        $sql = "SELECT * FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $usuario = $resultado->fetch_assoc();
        $stmt->close();
        return $usuario;
    }

    public function validarCampoUnico($campo, $valor)
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE {$campo} = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $valor);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function validarCampoUnicoExcepto($campo, $valor, $idUsuario)
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE {$campo} = ? AND id_usuario != ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $valor, $idUsuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function registrarUsuario($login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo)
    {
        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, usuario_clave, telefono, usuario_usuario, id_rol, activo) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sssssssii", $nombre, $apellidoPaterno, $apellidoMaterno, $email, $clave, $telefono, $login, $idRol, $activo);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function editarUsuario($idUsuario, $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo)
    {
        if (!empty($clave)) {
            $sql = "UPDATE usuarios SET usuario_usuario = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, telefono = ?, usuario_clave = ?, id_rol = ?, activo = ? WHERE id_usuario = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("sssssssiii", $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $idRol, $activo, $idUsuario);
        } else {
            $sql = "UPDATE usuarios SET usuario_usuario = ?, nombre = ?, apellido_paterno = ?, apellido_materno = ?, email = ?, telefono = ?, id_rol = ?, activo = ? WHERE id_usuario = ?";
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("sssssiiii", $login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $idRol, $activo, $idUsuario);
        }
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Verifica si un usuario tiene relaciones antes de eliminar
     */
    public function usuarioTieneRelaciones($idUsuario)
    {
        $tablasRelacionadas = [
            "SELECT COUNT(*) FROM medicos WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM pacientes WHERE id_usuario = ?",
            "SELECT COUNT(*) FROM consentimiento_informado WHERE dr_tratante_id = ?",
            "SELECT COUNT(*) FROM historia_clinica WHERE dr_tratante_id = ?",
            "SELECT COUNT(*) FROM citas WHERE creado_por = ?"
        ];
        
        foreach ($tablasRelacionadas as $sql) {
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            
            if ($count > 0) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Desactiva un usuario en lugar de eliminarlo
     */
    public function desactivarUsuario($idUsuario)
    {
        $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina usuario SOLO si no tiene relaciones
     */
    public function eliminarUsuarioSiEsPosible($idUsuario)
    {
        // Verificar si tiene relaciones
        if ($this->usuarioTieneRelaciones($idUsuario)) {
            return ['success' => false, 'message' => 'No se puede eliminar - usuario tiene relaciones en el sistema'];
        }
        
        // Intentar eliminar
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        
        if ($resultado) {
            return ['success' => true, 'message' => 'Usuario eliminado correctamente'];
        } else {
            return ['success' => false, 'message' => 'Error al eliminar el usuario'];
        }
    }
     /**
     * Reactiva un usuario cambiando su estado a activo (1)
     */
    public function reactivarUsuario($idUsuario)
    {
        $sql = "UPDATE usuarios SET activo = 1 WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    public function registrarUsuarioPaciente($login, $nombre, $apellidoPaterno, $apellidoMaterno, $email, $telefono, $clave, $activo = 1)
    {
        // Forzar rol de paciente (ID 4)
        $idRol = 4;
        
        $sql = "INSERT INTO usuarios (nombre, apellido_paterno, apellido_materno, email, usuario_clave, telefono, usuario_usuario, id_rol, activo) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sssssssii", 
            $nombre, 
            $apellidoPaterno, 
            $apellidoMaterno, 
            $email, 
            $clave, 
            $telefono, 
            $login, 
            $idRol, 
            $activo
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * NUEVO MÉTODO: Obtener estadísticas de registros de pacientes
     */
    public function obtenerEstadisticasRegistroPacientes()
    {
        $sql = "SELECT 
                    COUNT(*) as total_pacientes,
                    COUNT(CASE WHEN activo = 1 THEN 1 END) as pacientes_activos,
                    COUNT(CASE WHEN activo = 0 THEN 1 END) as pacientes_inactivos,
                    DATE(creado_en) as fecha_registro
                FROM usuarios 
                WHERE id_rol = 4 
                GROUP BY DATE(creado_en)
                ORDER BY fecha_registro DESC
                LIMIT 30";
        
        $resultado = $this->connection->query($sql);
        $estadisticas = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $estadisticas[] = $fila;
            }
        }
        return $estadisticas;
    }
}