<?php
include_once('Conexion.php'); 

class EntidadAuxiliarDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene el nombre completo del paciente a partir del id_internado.
     * @param int $idInternado
     * @return string|null
     */
    public function obtenerNombrePacientePorInternado($idInternado)
    {
        $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
                FROM internados i
                INNER JOIN pacientes p ON i.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE i.id_internado = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idInternado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $stmt->close();
            return trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
        }
        $stmt->close();
        return null;
    }

    /**
     * Obtiene el nombre completo del personal por su ID de usuario (Médico o Enfermera).
     * @param int $idUsuario
     * @return string|null
     */
    public function obtenerNombrePersonalPorIdUsuario($idUsuario)
    {
        $sql = "SELECT nombre, apellido_paterno, apellido_materno 
                FROM usuarios 
                WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $stmt->close();
            return trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
        }
        $stmt->close();
        return null;
    }

    /**
     * Obtiene una lista de Médicos activos (id_rol = 2)
     * @return array
     */
    public function obtenerMedicosActivos()
    {
        $sql = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno FROM usuarios WHERE id_rol = 2 AND activo = 1 ORDER BY apellido_paterno";
        $resultado = $this->connection->query($sql);
        $medicos = [];
        while ($fila = $resultado->fetch_assoc()) {
            $medicos[] = $fila;
        }
        return $medicos;
    }
    
    /**
     * Obtiene una lista de Enfermeros activos (id_rol = 6)
     * @return array
     */
    public function obtenerEnfermerosActivos()
    {
        $sql = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno FROM usuarios WHERE id_rol = 6 AND activo = 1 ORDER BY apellido_paterno";
        $resultado = $this->connection->query($sql);
        $enfermeros = [];
        while ($fila = $resultado->fetch_assoc()) {
            $enfermeros[] = $fila;
        }
        return $enfermeros;
    }

    /**
     * Obtiene una lista de Internados activos
     * @return array
     */
    public function obtenerInternadosActivosConNombrePaciente()
    {
        $sql = "SELECT i.id_internado, u.nombre, u.apellido_paterno, u.apellido_materno, i.fecha_ingreso
                FROM internados i
                INNER JOIN pacientes p ON i.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE i.estado = 'Activo'
                ORDER BY i.fecha_ingreso DESC";
        $resultado = $this->connection->query($sql);
        $internados = [];
        while ($fila = $resultado->fetch_assoc()) {
            $fila['nombre_completo'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
            $internados[] = $fila;
        }
        return $internados;
    }

    /**
     * Obtiene id_medico a partir de id_usuario
     * @param int $idUsuario
     * @return int|null
     */
    public function obtenerIdMedicoPorIdUsuario($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $stmt->close();
            return $fila['id_medico'];
        }
        $stmt->close();
        return null;
    }

    /**
     * Valida si un usuario es médico
     * @param int $idUsuario
     * @return bool
     */
    public function validarUsuarioEsMedico($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $existe = $resultado->num_rows > 0;
        $stmt->close();
        return $existe;
    }

    /**
     * Valida si un usuario es enfermera
     * @param int $idUsuario
     * @return bool
     */
    public function validarUsuarioEsEnfermera($idUsuario)
    {
        $sql = "SELECT id_usuario FROM usuarios WHERE id_usuario = ? AND id_rol = 6";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $existe = $resultado->num_rows > 0;
        $stmt->close();
        return $existe;
    }
}