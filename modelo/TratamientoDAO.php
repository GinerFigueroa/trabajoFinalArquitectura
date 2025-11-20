<?php
// Archivo: modelo/TratamientoDAO.php

include_once('conexion.php');
// Nota: En un proyecto real, el DTO Tratamiento debería estar definido aquí o incluido.

/**
 * Clase TratamientoDAO (Data Access Object)
 * Responsable de las operaciones CRUD y validaciones de la entidad 'tratamientos'.
 */
class TratamientoDAO
{
    private $connection;

    public function __construct()
    {
        // Se obtiene la ÚNICA instancia de la conexión a través del Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todos los tratamientos con el nombre de su especialidad asociada.
     * MEJORA: Usando sentencia preparada para consistencia.
     * @return array
     */
    public function obtenerTodosTratamientos()
    {
        $sql = "SELECT t.*, e.nombre AS nombre_especialidad 
                FROM tratamientos t
                LEFT JOIN especialidades_medicas e ON t.id_especialidad = e.id_especialidad
                ORDER BY t.nombre ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        // Retorna un array asociativo de resultados o un array vacío
        $lista = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        
        return $lista;
    }
    
    /**
     * Obtiene un tratamiento específico por su ID.
     * @param int $idTratamiento
     * @return array|null
     */
    public function obtenerTratamientoPorId($idTratamiento)
    {
        $sql = "SELECT * FROM tratamientos WHERE id_tratamiento = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idTratamiento);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $tratamiento = $resultado->fetch_assoc();
        
        $stmt->close();
        return $tratamiento;
    }

    /**
     * Valida si el nombre del tratamiento ya existe dentro de la misma especialidad.
     * @param string $nombre
     * @param int $idEspecialidad
     * @param int|null $idTratamiento (Para excluirse a sí mismo en edición)
     * @return bool (true si ya existe)
     */
    public function validarNombreUnico($nombre, $idEspecialidad, $idTratamiento = null)
    {
        $sql = "SELECT COUNT(*) FROM tratamientos WHERE nombre = ? AND id_especialidad = ?";
        
        if ($idTratamiento) {
            $sql .= " AND id_tratamiento != ?";
        }
        
        $stmt = $this->connection->prepare($sql);
        
        if ($idTratamiento) {
            $stmt->bind_param("sii", $nombre, $idEspecialidad, $idTratamiento);
        } else {
            $stmt->bind_param("si", $nombre, $idEspecialidad);
        }
        
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Valida si la especialidad médica referenciada existe.
     * Se mantiene temporalmente, pero la buena práctica es delegar a EspecialidadDAO.
     * @param int $idEspecialidad
     * @return bool
     */
    public function especialidadExiste($idEspecialidad)
    {
        // Idealmente, esto se haría:
        // $objEspecialidadDAO = new EspecialidadDAO();
        // return $objEspecialidadDAO->obtenerEspecialidadPorId($idEspecialidad) !== null;
        
        $sql = "SELECT COUNT(*) FROM especialidades_medicas WHERE id_especialidad = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idEspecialidad);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Registra un nuevo tratamiento en la base de datos.
     * MEJORA: Asumiendo que se recibe un DTO o un array asociativo como entrada
     * para reducir el número de parámetros.
     * @param array $datosTratamiento (Representando el DTO)
     * @return bool
     */
    public function registrarTratamiento(array $datosTratamiento)
    {
        $nombre = $datosTratamiento['nombre'];
        $idEspecialidad = $datosTratamiento['id_especialidad'];
        $descripcion = $datosTratamiento['descripcion'];
        $duracion = $datosTratamiento['duracion_estimada']; // Asumiendo el nombre de la columna
        $costo = $datosTratamiento['costo'];
        $requisitos = $datosTratamiento['requisitos'];
        $activo = $datosTratamiento['activo'];
        
        $sql = "INSERT INTO tratamientos (nombre, id_especialidad, descripcion, duracion_estimada, costo, requisitos, activo)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: s (nombre), i (idEspecialidad), s (descripcion), i (duracion), d (costo), s (requisitos), i (activo)
        $stmt->bind_param("sisidsi", $nombre, $idEspecialidad, $descripcion, $duracion, $costo, $requisitos, $activo);
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Edita un tratamiento existente.
     * MEJORA: Asumiendo que se recibe un DTO o un array asociativo.
     * @param array $datosTratamiento (Incluyendo id_tratamiento)
     * @return bool
     */
 /**
     * Edita un tratamiento existente.
     * 🚀 PATRÓN DTO: Ahora acepta un solo array asociativo (DTO)
     * @param array $datos (Debe incluir idTratamiento)
     * @return bool
     */
    public function editarTratamiento(array $datos)
    {
        $sql = "UPDATE tratamientos SET nombre = ?, id_especialidad = ?, descripcion = ?, duracion_estimada = ?, costo = ?, requisitos = ?, activo = ? WHERE id_tratamiento = ?";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: s (nombre), i (idEspecialidad), s (descripcion), i (duracion), d (costo), s (requisitos), i (activo), i (idTratamiento)
        $stmt->bind_param("sisidsii", 
            $datos['nombre'], 
            $datos['idEspecialidad'], 
            $datos['descripcion'], 
            $datos['duracion'], 
            $datos['costo'], 
            $datos['requisitos'], 
            $datos['activo'], 
            $datos['idTratamiento'] // El ID va al final
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Elimina un tratamiento por su ID.
     * @return bool
     */
    public function eliminarTratamiento($idTratamiento)
    {
        $sql = "DELETE FROM tratamientos WHERE id_tratamiento = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idTratamiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }
}


/**
 * Clase EspecialidadDAO (Data Access Object)
 * MEJORA: Renombrada de EntidadDAO a EspecialidadDAO para aplicar el Principio de Responsabilidad Única.
 */
class EspecialidadDAO
{
    private $connection;

    public function __construct()
    {
        // Se obtiene la ÚNICA instancia de la conexión a través del Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    /**
     * Obtiene todas las especialidades médicas.
     * @return array
     */
    public function obtenerTodasEspecialidades()
    {
        $sql = "SELECT * FROM especialidades_medicas ORDER BY nombre ASC";
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerEspecialidadPorId($idEspecialidad)
    {
        $sql = "SELECT * FROM especialidades_medicas WHERE id_especialidad = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idEspecialidad);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $especialidad = $resultado->fetch_assoc();
        $stmt->close();
        
        return $especialidad;
    }
}
    
    // Aquí se añadirían los métodos registrar, editar y eliminar si fueran necesarios para Especialidades.
