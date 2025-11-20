
<?php
// Archivo: DocumentosDAO.php
include_once('conexion.php'); // Se asume que este archivo contiene la clase 'Conexion' implementada como Singleton

/**
 * Clase DocumentosDAO
 * Objeto de Acceso a Datos para la tabla `documentos`.
 */
class DocumentosDAO
{
    private $connection;

    public function __construct()
    {
        // PATRÓN SINGLETON: Obtener la única instancia de la conexión
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // ===================================
    // L: LISTAR / CONSULTAR
    // ===================================

    /**
     * Obtiene todos los documentos con el nombre completo del paciente.
     * @return array
     */
    public function obtenerTodosDocumentos()
    {
        $sql = "SELECT d.id_documento, d.id_paciente, d.tipo, d.nombre, d.ruta_archivo, d.notas, 
                       DATE_FORMAT(d.subido_en, '%d/%m/%Y %H:%i') as subido_en_formato,
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente
                FROM documentos d
                JOIN pacientes p ON d.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY d.subido_en DESC";
                
        $resultado = $this->connection->query($sql);
        $documentos = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $documentos[] = $fila;
            }
        }
        return $documentos;
    }

    /**
     * Obtiene un documento específico por su ID.
     * @param int $idDocumento
     * @return array|null
     */
    public function obtenerDocumentoPorId($idDocumento)
    {
        $stmt = $this->connection->prepare("SELECT * FROM documentos WHERE id_documento = ?");
        if ($stmt === false) { return null; }
        
        $stmt->bind_param("i", $idDocumento);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            return $fila;
        }
        return null;
    }

    // ===================================
    // R: REGISTRAR
    // ===================================

    /**
     * Registra un nuevo documento.
     * @param int $idPaciente
     * @param string $tipo
     * @param string $nombre
     * @param string $rutaArchivo
     * @param string $notas
     * @param int $subidoPor (ID de usuario)
     * @return bool
     */
    public function registrarDocumento($idPaciente, $tipo, $nombre, $rutaArchivo, $notas, $subidoPor)
    {
        $sql = "INSERT INTO documentos (id_paciente, tipo, nombre, ruta_archivo, notas, subido_por) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) { return false; }
        
        $stmt->bind_param("issssi", $idPaciente, $tipo, $nombre, $rutaArchivo, $notas, $subidoPor);
        return $stmt->execute();
    }

    // ===================================
    // E: EDITAR / ACTUALIZAR
    // ===================================

    /**
     * Edita los metadatos de un documento existente.
     * (No cambia la ruta del archivo ni la fecha de subida)
     * @param int $idDocumento
     * @param int $idPaciente
     * @param string $tipo
     * @param string $nombre
     * @param string $notas
     * @return bool
     */
    public function editarDocumento($idDocumento, $idPaciente, $tipo, $nombre, $notas)
    {
        $sql = "UPDATE documentos SET id_paciente = ?, tipo = ?, nombre = ?, notas = ? WHERE id_documento = ?";
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) { return false; }

        $stmt->bind_param("isssi", $idPaciente, $tipo, $nombre, $notas, $idDocumento);
        return $stmt->execute();
    }

    // ===================================
    // D: ELIMINAR
    // ===================================

    /**
     * Elimina un documento por su ID.
     * @param int $idDocumento
     * @return bool
     */
    public function eliminarDocumento($idDocumento)
    {
        $sql = "DELETE FROM documentos WHERE id_documento = ?";
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) { return false; }
        
        $stmt->bind_param("i", $idDocumento);
        return $stmt->execute();
    }
}



class EntidadesDAO
{
    private $connection;

    public function __construct()
    {
        // PATRÓN SINGLETON: Obtener la única instancia de la conexión
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene una lista de pacientes activos para ser utilizados en formularios.
     * Incluye id_paciente, DNI y nombre completo.
     * @return array Lista de pacientes
     */
    public function obtenerPacientesDisponibles()
    {
        $sql = "SELECT p.id_paciente, p.dni, 
                       CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.activo = 1
                ORDER BY nombre_completo ASC";
                
        $resultado = $this->connection->query($sql);
        $pacientes = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $pacientes[] = $fila;
            }
        }
        return $pacientes;
    }

    /**
     * Verifica si un paciente existe dado su ID.
     * Esto es crucial para la validación de la Cadena de Responsabilidad (Chain of Responsibility).
     * @param int $idPaciente
     * @return bool
     */
    public function pacienteExiste($idPaciente)
    {
        $sql = "SELECT p.id_paciente
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.id_paciente = ? AND u.activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        if ($stmt === false) { return false; }

        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->store_result();
        
        return $stmt->num_rows === 1;
    }

    // Opcionalmente, puedes añadir métodos similares para otras entidades (médicos, tratamientos, etc.)
    // que puedan ser útiles en otros módulos del sistema:

    /*
    public function obtenerMedicosDisponibles() {
        $sql = "SELECT m.id_medico, m.cedula_profesional, 
                       CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
                FROM medicos m
                JOIN usuarios u ON m.id_usuario = u.id_usuario
                WHERE m.activo = 1
                ORDER BY nombre_completo ASC";
        // ... Lógica de ejecución y retorno
    }
    */
}