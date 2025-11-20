<?php
// Archivo: modelo/PacienteDAO.php (Contiene lógica principal de pacientes)

include_once('conexion.php');

/**
 * Clase PacienteDAO (Data Access Object)
 * Responsable de las operaciones CRUD y validaciones de la entidad 'pacientes'.
 */
class PacienteDAO
{
    private $connection;

    public function __construct()
    {
        // Obtiene la ÚNICA instancia de la conexión a través del Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todos los pacientes con la información de su usuario asociado.
     * @return array
     */
    public function obtenerTodosPacientes()
    {
        $sql = "SELECT p.*, u.nombre, u.apellido_paterno, u.apellido_materno, u.telefono, u.email, u.usuario_usuario, u.activo
                FROM pacientes p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene un paciente específico por su ID.
     * @param int $idPaciente
     * @return array|null
     */
    public function obtenerPacientePorId($idPaciente)
    {
        $sql = "SELECT p.*, u.nombre, u.apellido_paterno, u.apellido_materno, u.telefono, u.email, u.usuario_usuario
                FROM pacientes p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
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
     * Obtiene un paciente por el ID de usuario asociado.
     * @param int $idUsuario
     * @return array|null
     */
    public function obtenerPacientePorIdUsuario($idUsuario)
    {
        $sql = "SELECT * FROM pacientes WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $paciente = $resultado->fetch_assoc();
        
        $stmt->close();
        return $paciente;
    }

    /**
     * Registra un nuevo paciente.
     * @return bool
     */
    public function registrarPaciente($idUsuario, $fechaNacimiento, $lugarNacimiento, $ocupacion, $dni, $domicilio, $distrito, $edad, $sexo, $estadoCivil, $nombreApoderado, $apellidoPaternoApoderado, $apellidoMaternoApoderado, $parentescoApoderado)
    {
        $sql = "INSERT INTO pacientes (id_usuario, fecha_nacimiento, lugar_nacimiento, ocupacion, dni, domicilio, distrito, edad, sexo, estado_civil, nombre_apoderado, apellido_paterno_apoderado, apellido_materno_apoderado, parentesco_apoderado)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: i (idUsuario) ssssss i s s ssss
        $stmt->bind_param("issssssissssss", $idUsuario, $fechaNacimiento, $lugarNacimiento, $ocupacion, $dni, $domicilio, $distrito, $edad, $sexo, $estadoCivil, $nombreApoderado, $apellidoPaternoApoderado, $apellidoMaternoApoderado, $parentescoApoderado);
        
        $resultado = $stmt->execute();
        
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita los detalles de un paciente existente.
     * @return bool
     */
    public function editarPaciente($idPaciente, $fechaNacimiento, $lugarNacimiento, $ocupacion, $dni, $domicilio, $distrito, $edad, $sexo, $estadoCivil, $nombreApoderado, $apellidoPaternoApoderado, $apellidoMaternoApoderado, $parentescoApoderado)
    {
        $sql = "UPDATE pacientes SET fecha_nacimiento = ?, lugar_nacimiento = ?, ocupacion = ?, dni = ?, domicilio = ?, distrito = ?, edad = ?, sexo = ?, estado_civil = ?, nombre_apoderado = ?, apellido_paterno_apoderado = ?, apellido_materno_apoderado = ?, parentesco_apoderado = ? WHERE id_paciente = ?";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: ssssss i s s ssss i (idPaciente al final)
        $stmt->bind_param("ssssssissssssi", $fechaNacimiento, $lugarNacimiento, $ocupacion, $dni, $domicilio, $distrito, $edad, $sexo, $estadoCivil, $nombreApoderado, $apellidoPaternoApoderado, $apellidoMaternoApoderado, $parentescoApoderado, $idPaciente);
        
        $resultado = $stmt->execute();
        
        $stmt->close();
        return $resultado;
    }

    /**
     * Desactiva un paciente cambiando el estado del usuario asociado
     * @param int $idPaciente
     * @return bool
     */
    public function desactivarPaciente($idPaciente)
    {
        // Primero obtenemos el id_usuario asociado al paciente
        $sql = "SELECT id_usuario FROM pacientes WHERE id_paciente = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($idUsuario);
        $stmt->fetch();
        $stmt->close();
        
        if (!$idUsuario) {
            return false; // No se encontró el paciente
        }
        
        // Desactivamos el usuario (esto afecta al login y visibilidad)
        $sql = "UPDATE usuarios SET activo = 0 WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Reactiva un paciente
     * @param int $idPaciente
     * @return bool
     */
    public function reactivarPaciente($idPaciente)
    {
        // Obtenemos el id_usuario asociado
        $sql = "SELECT id_usuario FROM pacientes WHERE id_paciente = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($idUsuario);
        $stmt->fetch();
        $stmt->close();
        
        if (!$idUsuario) {
            return false;
        }
        
        // Reactivamos el usuario
        $sql = "UPDATE usuarios SET activo = 1 WHERE id_usuario = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Verifica si un paciente puede ser eliminado (sin relaciones)
     * @param int $idPaciente
     * @return bool
     */
    public function puedeEliminarPaciente($idPaciente)
    {
        $verificaciones = [
            "SELECT COUNT(*) FROM citas WHERE id_paciente = ?",
            "SELECT COUNT(*) FROM historia_clinica WHERE id_paciente = ?", 
            "SELECT COUNT(*) FROM internados WHERE id_paciente = ?",
            "SELECT COUNT(*) FROM orden_pago WHERE id_paciente = ?",
            "SELECT COUNT(*) FROM documentos WHERE id_paciente = ?"
        ];
        
        foreach ($verificaciones as $sql) {
            $stmt = $this->connection->prepare($sql);
            $stmt->bind_param("i", $idPaciente);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            
            if ($count > 0) {
                return false; // Tiene relaciones, NO se puede eliminar
            }
        }
        
        return true; // No tiene relaciones, SÍ se puede eliminar
    }

    /**
     * Elimina paciente SOLO si no tiene relaciones
     * @param int $idPaciente
     * @return array ['success' => bool, 'message' => string]
     */
    public function eliminarPacienteSiEsPosible($idPaciente)
    {
        // Verificar si se puede eliminar
        if (!$this->puedeEliminarPaciente($idPaciente)) {
            return ['success' => false, 'message' => 'No se puede eliminar - paciente tiene historial médico'];
        }
        
        // Obtener id_usuario antes de eliminar
        $idUsuario = null;
        $sql = "SELECT id_usuario FROM pacientes WHERE id_paciente = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($idUsuario);
        $stmt->fetch();
        $stmt->close();
        
        // Eliminar paciente
        $sql = "DELETE FROM pacientes WHERE id_paciente = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $resultado = $stmt->execute();
        $stmt->close();
        
        if ($resultado && $idUsuario) {
            // También eliminar el usuario si ya no tiene pacientes
            $sqlCheck = "SELECT COUNT(*) FROM pacientes WHERE id_usuario = ?";
            $stmt = $this->connection->prepare($sqlCheck);
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();
            
            if ($count == 0) {
                // Eliminar usuario también
                $sqlDeleteUser = "DELETE FROM usuarios WHERE id_usuario = ?";
                $stmt = $this->connection->prepare($sqlDeleteUser);
                $stmt->bind_param("i", $idUsuario);
                $stmt->execute();
                $stmt->close();
            }
            
            return ['success' => true, 'message' => 'Paciente eliminado completamente'];
        }
        
        return ['success' => false, 'message' => 'Error al eliminar paciente'];
    }

    // --- Validaciones y Lookups Específicos ---
    
    /**
     * Verifica si un DNI ya existe, excluyendo un ID de paciente opcional (para edición).
     * @param string $dni
     * @param int|null $idPaciente
     * @return bool
     */
    public function dniExiste($dni, $idPaciente = null)
    {
        $sql = "SELECT COUNT(*) FROM pacientes WHERE dni = ?";
        if ($idPaciente) {
            $sql .= " AND id_paciente != ?";
        }
        
        $stmt = $this->connection->prepare($sql);
        
        if ($idPaciente) {
            $stmt->bind_param("si", $dni, $idPaciente);
        } else {
            $stmt->bind_param("s", $dni);
        }
        
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Obtiene pacientes que tienen un rol específico (Usado generalmente para obtener la lista de pacientes).
     * @param int $idRol (Debe ser el ID de rol de paciente, ej: 4)
     * @return array
     */
    public function obtenerPacientesPorRol($idRol)
    {
        $sql = "SELECT p.id_paciente, p.dni, u.nombre, u.apellido_paterno
                FROM pacientes p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.id_rol = ? AND u.activo = 1
                ORDER BY u.nombre";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idRol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Busca todos los usuarios que tienen un rol específico (e.g., id_rol = 4).
     * @param int $idRol El ID del rol a buscar (e.g., 4 para Paciente).
     * @return array La lista de usuarios o un array vacío si no hay resultados.
     */
    public function buscarUsuariosPorRol($idRol)
    {
        $sql = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno, usuario_usuario 
                FROM usuarios 
                WHERE id_rol = ? AND activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idRol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}

// -------------------------------------------------------------------------

/**
 * Clase PacienteAuxiliarDAO (Data Access Object Auxiliar)
 * Responsable de obtener listas de datos auxiliares (usuarios sin paciente asignado, etc.).
 */
class EntidadPacienteDAO
{
    private $connection;
    
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    /**
     * Obtiene usuarios que tienen el rol de paciente (ID_ROL = 4) y aún no tienen una fila en la tabla 'pacientes'.
     * @return array
     */
    public function obtenerTodosUsuariosPacientesSinAsignar()
    {
        $sql = "SELECT u.id_usuario, u.usuario_usuario, u.nombre, u.apellido_paterno, u.apellido_materno 
                FROM usuarios u
                LEFT JOIN pacientes p ON u.id_usuario = p.id_usuario
                WHERE u.id_rol = 4 AND u.activo = 1 AND p.id_paciente IS NULL
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Verifica si un ID de usuario existe y tiene el rol de paciente.
     * @param int $idUsuario
     * @return bool
     */
    public function usuarioExiste($idUsuario)
    {
        $sql = "SELECT COUNT(*) FROM usuarios WHERE id_usuario = ? AND id_rol = 4 AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }
}
?>