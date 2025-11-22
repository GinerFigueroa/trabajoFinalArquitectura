<?php
// Fichero: .../modelo/HistoriaClinicaDAO.php
// Se asume que 'Conexion.php' está disponible en la ruta.

include_once('Conexion.php'); 

class HistoriaClinicaDAO
{
    private $connection;

    public function __construct() {
        // Inicializa $this->connection usando el Singleton de la clase Conexion
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =================================================================================
    // MÉTODOS CRUD DE HISTORIA CLÍNICA (HC)
    // =================================================================================
    
    /**
     * Obtiene todos los registros de historia clínica (solo metadatos).
     * @return array
     */
    public function obtenerTodasHistorias()
    {
        // 1. SELECT solo con los 4 campos existentes
        $sql = "SELECT historia_clinica_id, id_paciente, dr_tratante_id, fecha_creacion 
                FROM historia_clinica
                ORDER BY fecha_creacion DESC";
        
        $resultado = $this->connection->query($sql);
        $historias = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // 2. Enriquecer con nombres usando los métodos fusionados
                $fila['nombre_paciente'] = $this->obtenerNombreCompletoPorIdPaciente($fila['id_paciente']);
                $fila['nombre_doctor'] = $this->obtenerNombreCompletoUsuario($fila['dr_tratante_id']); 
                $historias[] = $fila;
            }
        }
        return $historias;
    }

    /**
     * Obtiene una historia clínica específica por ID (solo metadatos).
     * @param int $historiaClinicaId
     * @return array|null
     */
 // Fichero: .../modelo/HistoriaClinicaDAO.php
// Dentro de la clase HistoriaClinicaDAO

public function obtenerHistoriaPorId($historiaClinicaId)
{
    // Usamos JOINs para obtener los nombres completos del paciente y del tratante
    $sql = "SELECT 
                hc.historia_clinica_id, 
                hc.id_paciente, 
                hc.dr_tratante_id, 
                hc.fecha_creacion,
                -- Alias para el nombre del paciente
                CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                -- Alias para el nombre del tratante (Enfermero/Médico)
                CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
            FROM historia_clinica hc
    
            -- JOIN para obtener el nombre del paciente
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
            -- JOIN para obtener el nombre del tratante
            JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario 
            WHERE hc.historia_clinica_id = ?";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $historiaClinicaId);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $historia = $resultado->fetch_assoc();
    $stmt->close();
    
    return $historia;
}

    /**
     * Registra una nueva historia clínica (solo metadatos: id_paciente, dr_tratante_id, fecha_creacion).
     */
    public function registrarHistoria(
        $idPaciente, $drTratanteId, $fechaCreacion
    ) {
        // La consulta SQL se reduce a los 3 campos obligatorios.
        $sql = "INSERT INTO historia_clinica 
                (id_paciente, dr_tratante_id, fecha_creacion)
                VALUES (?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iis", 
            $idPaciente, $drTratanteId, $fechaCreacion
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    /**
     * Obtiene una lista de usuarios válidos para ser Tratantes (dr_tratante_id).
     * Se asumen roles 2 (Médico) y 6 (Enfermero).
     * @return array
     */
    public function obtenerPersonalTratante()
    {
        // Se asume que los roles de personal médico/enfermero son 2 y 6
        $sql = "SELECT 
                    id_usuario, 
                    CONCAT(nombre, ' ', apellido_paterno) AS nombre_completo,
                    id_rol 
                FROM usuarios
                WHERE id_rol IN (2, 6) AND activo = 1 
                ORDER BY nombre_completo";

        $resultado = $this->connection->query($sql);
        $personal = [];
        if ($resultado) {
            $personal = $resultado->fetch_all(MYSQLI_ASSOC);
        }
        return $personal;
    }

    /**
     * Edita los metadatos de una historia clínica. (Solo campos esenciales si se permite).
     * Se usa la versión que actualiza los 3 campos de metadatos más el ID de HC.
     */
    public function editarHistoria(
        $historiaClinicaId, 
        $idPaciente, 
        $drTratanteId, 
        $fechaCreacion
    ) {
        $sql = "UPDATE historia_clinica SET 
                    id_paciente = ?, 
                    dr_tratante_id = ?, 
                    fecha_creacion = ?
                WHERE historia_clinica_id = ?";

        $stmt = $this->connection->prepare($sql);
        // NOTA: El bind_param debe ser "iisi" (i para idPaciente, i para drTratanteId, s para fechaCreacion, i para historiaClinicaId)
        $stmt->bind_param("iisi", 
            $idPaciente, 
            $drTratanteId, 
            $fechaCreacion,
            $historiaClinicaId
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    /**
     * Elimina una historia clínica.
     */
    public function eliminarHistoria($historiaClinicaId)
    {
        $stmt = $this->connection->prepare("DELETE FROM historia_clinica WHERE historia_clinica_id = ?");
        $stmt->bind_param("i", $historiaClinicaId);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    // =================================================================================
    // MÉTODOS AUXILIARES FUSIONADOS (De la antigua EntidadAuxiliarDAO)
    // =================================================================================

    /**
     * Obtiene una lista de pacientes (id_paciente, nombre_completo, dni) que 
     * actualmente NO tienen un registro en la tabla `historia_clinica`.
     * Crucial para el formulario de creación de una nueva HC.
     * @return array
     */
 /**
 * Obtiene una lista de pacientes activos (rol 4) que NO tienen registro en historia_clinica.
 * Útil para la creación de una nueva Historia Clínica.
 * @return array
 */
public function obtenerPacientesSinHistoriaAsignada()
{
    $sql = "SELECT 
                p.id_paciente, 
                u.nombre, 
                u.apellido_paterno, 
                u.apellido_materno
            FROM pacientes p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            LEFT JOIN historia_clinica hc ON p.id_paciente = hc.id_paciente
            WHERE u.id_rol = 4 -- Asume que 4 es el rol de Paciente
            AND u.activo = 1 
            AND hc.historia_clinica_id IS NULL -- Filtra solo los que NO tienen HC
            ORDER BY u.apellido_paterno";

    // Nota: Se asume que $this->connection es la conexión mysqli activa.
    $resultado = $this->connection->query($sql);
    $pacientes = [];
    
    if ($resultado === FALSE) {
        // Manejo de error de consulta
        error_log("Error en obtenerPacientesSinHistoriaAsignada: " . $this->connection->error);
        return [];
    }
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            // Concatenar el nombre completo para facilitar el uso en la vista (dropdown)
            $fila['nombre_completo'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
            $pacientes[] = $fila;
        }
    }
    return $pacientes;
}
    /**
     * Obtiene el nombre completo del paciente a partir del ID de Paciente (id_paciente).
     * @param int $idPaciente
     * @return string|null
     */
    public function obtenerNombreCompletoPorIdPaciente($idPaciente)
    {
        $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
                FROM pacientes p
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE p.id_paciente = ? AND u.activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
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
 * Obtiene el nombre completo de cualquier usuario (Médico, Enfermero, etc.) 
 * a partir de su ID de usuario.
 * @param int $idUsuario
 * @return string|null Nombre completo o NULL si no existe o no está activo.
 */
public function obtenerNombreCompletoUsuario($idUsuario)
{
    // Se asume que la tabla 'usuarios' contiene 'nombre', 'apellido_paterno', 'apellido_materno' y 'activo'.
    $sql = "SELECT nombre, apellido_paterno, apellido_materno 
            FROM usuarios 
            WHERE id_usuario = ? AND activo = 1";
    
    $stmt = $this->connection->prepare($sql);
    
    if (!$stmt) {
        // Manejo básico de errores de preparación
        error_log("Error al preparar obtenerNombreCompletoUsuario: " . $this->connection->error);
        return null;
    }
    
    $stmt->bind_param("i", $idUsuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    
    if ($fila = $resultado->fetch_assoc()) {
        $stmt->close();
        // Concatena y limpia los espacios extra.
        return trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
    }
    
    $stmt->close();
    return null;
}

    /**
     * Obtiene una lista de TODOS los usuarios activos con rol de Médico.
     * @return array
     */
    public function obtenerMedicosActivos()
    {
        $sql = "SELECT id_usuario, nombre, apellido_paterno 
                FROM usuarios 
                WHERE id_rol = 2 AND activo = 1 
                ORDER BY apellido_paterno";
        
        // Devuelve un array asociativo con los resultados
        return $this->connection->query($sql)->fetch_all(MYSQLI_ASSOC);
    }
    
    // NOTA: Se ha eliminado la función 'obtenerNombrePersonalPorIdUsuario()' porque es redundante 
    // con 'obtenerNombreCompletoUsuario()' y 'obtenerNombreCompletoPorIdPaciente()'.
    
    // NOTA: Se ha eliminado la función obsoleta 'editarHistoria' (la que tenía 7 parámetros obsoletos).
    // NOTA: Se ha eliminado la función obsoleta 'obtenerResumenHistorialPorPaciente' (usaba columnas y tablas viejas).
/**
/**
 * Obtiene las historias clínicas asociadas al ID del personal (Enfermero) tratante.
 * (Asume que el idEnfermero está registrado en la columna dr_tratante_id).
 * @param int $idEnfermero
 * @return array
 */
/**
 * Obtiene TODAS las historias clínicas para el rol de Enfermero
 * (Los enfermeros pueden ver todas las historias, no solo las asignadas a ellos)
 * @param int $idEnfermero (se mantiene por compatibilidad, pero no se usa en el filtro)
 * @return array
 */
public function obtenerHistoriasPorEnfermero($idEnfermero)
{
    $sql = "SELECT 
                hc.historia_clinica_id, 
                hc.id_paciente, 
                hc.dr_tratante_id, 
                hc.fecha_creacion,
                u.nombre, 
                u.apellido_paterno, 
                u.apellido_materno, 
                rm.motivo_consulta,
                -- Agregar nombre del tratante para mostrar en la lista
                CONCAT(ut.nombre, ' ', ut.apellido_paterno) as nombre_tratante
            FROM historia_clinica hc
            INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN usuarios ut ON hc.dr_tratante_id = ut.id_usuario  -- JOIN para ver quién es el tratante
            LEFT JOIN registro_medico rm ON hc.historia_clinica_id = rm.historia_clinica_id
            ORDER BY hc.fecha_creacion DESC, hc.historia_clinica_id DESC";

    $stmt = $this->connection->prepare($sql);
    
    if (!$stmt) {
        error_log("Fallo al preparar SQL: " . $this->connection->error);
        return [];
    }
    
    // NOTA: Ya no usamos bind_param porque no hay parámetros WHERE
    if (!$stmt->execute()) {
        error_log("Fallo al ejecutar la consulta: " . $stmt->error);
        return [];
    }
    
    $resultado = $stmt->get_result();
    $historias = [];

    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            $fila['nombre_paciente'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
            $historias[] = $fila;
        }
    }
    $stmt->close();
    
    error_log("DEBUG: Enfermero ID $idEnfermero - Se encontraron " . count($historias) . " historias");
    return $historias;
}


}
?>