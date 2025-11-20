C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\TriageDAO.php
<?php
include_once('Conexion.php'); 

/**
 * Clase TriageDAO (Data Access Object)
 * Responsable única del acceso a la base de datos para la entidad 'triage' y 'niveles_triage'.
 * Utiliza el patrón Singleton para la conexión.
 */
class TriageDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Registra la llegada inicial de un paciente a urgencias.
     * Solo se registra la hora de llegada y el motivo principal, quedando en estado 'Pendiente'.
     * @param int $idPaciente ID del usuario que es paciente.
     * @param string $motivoPrincipal Razón de la visita a urgencias.
     * @return bool Resultado de la operación.
     */
    public function registrarIngresoInicial($idPaciente, $motivoPrincipal)
    {
        // El nivel se asigna a un valor por defecto (ej. Nivel 5, que debe existir en niveles_triage) o se deja nulo en la DB.
        // Asumiendo que id_nivel es obligatorio, usaremos el Nivel 5 (Riesgo bajo), y se reclasificará después. 
        // Alternativamente, se puede usar un ID_NIVEL especial para 'NO CLASIFICADO'.
        // POR SIMPLICIDAD: Asignaremos un valor por defecto (ej. ID=5), y la enfermera lo actualizará.
        $idNivelInicial = 5; // Asume que el ID 5 en niveles_triage es el "No Clasificado" o el más bajo.
        $estadoInicial = 'Pendiente'; // Estado inicial de la atención (Pendiente de Triage).
        
        $sql = "INSERT INTO triage (id_paciente, id_nivel, fecha_hora_llegada, fecha_hora_clasificacion, motivo_principal, estado_triage)
                VALUES (?, ?, NOW(), NOW(), ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iiss", $idPaciente, $idNivelInicial, $motivoPrincipal, $estadoInicial);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
    
    /**
     * Obtiene el listado de pacientes en estado 'Pendiente' (por clasificar) para la Recepcionista.
     * Une con usuarios para obtener el nombre del paciente.
     * @return array
     */
    public function obtenerPacientesPendientesTriage()
    {
        $sql = "SELECT t.id_triage, u.nombre, u.apellido_paterno, u.apellido_materno, t.fecha_hora_llegada, t.motivo_principal 
                FROM triage t
                INNER JOIN usuarios u ON t.id_paciente = u.id_usuario
                WHERE t.estado_triage = 'Pendiente'
                ORDER BY t.fecha_hora_llegada ASC"; 

        $resultado = $this->connection->query($sql);
        $pacientes = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $pacientes[] = $fila;
            }
        }
        return $pacientes;
    }
    
    //---------------------------------------------------------
    // Métodos para Enfermera (Triage)
    //---------------------------------------------------------

    /**
     * Obtiene todos los niveles de triage para el formulario.
     * @return array
     */
    public function obtenerNivelesTriage()
    {
        $sql = "SELECT id_nivel, codigo, color, descripcion, tiempo_max_espera_min FROM niveles_triage ORDER BY id_nivel ASC"; 
        $resultado = $this->connection->query($sql);
        $niveles = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $niveles[] = $fila;
            }
        }
        return $niveles;
    }
    
    /**
     * Obtiene pacientes que necesitan triage (estado 'Pendiente') o que ya fueron clasificados.
     * @param string $estado Si es 'Pendiente' (para triage inicial) o 'Clasificado' (para re-triage)
     * @return array
     */
    public function obtenerPacientesParaTriage($estado)
    {
        $sql = "SELECT t.id_triage, u.nombre, u.apellido_paterno, u.apellido_materno, t.fecha_hora_llegada, t.motivo_principal, n.codigo AS nivel_triage_codigo
                FROM triage t
                INNER JOIN usuarios u ON t.id_paciente = u.id_usuario
                LEFT JOIN niveles_triage n ON t.id_nivel = n.id_nivel
                WHERE t.estado_triage = ?
                ORDER BY t.fecha_hora_llegada ASC"; 

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $estado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $pacientes = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        return $pacientes;
    }

    /**
     * Obtiene los datos de un registro de triage específico para su edición.
     * @param int $idTriage
     * @return array|null
     */
    public function obtenerTriagePorId($idTriage)
    {
        $sql = "SELECT t.*, u.usuario_usuario, u.nombre, u.apellido_paterno, u.apellido_materno, u.telefono
                FROM triage t
                INNER JOIN usuarios u ON t.id_paciente = u.id_usuario
                WHERE t.id_triage = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idTriage);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $triage = $resultado->fetch_assoc();
        $stmt->close();
        return $triage;
    }

    /**
     * Actualiza un registro de triage (clasificación inicial o re-triage).
     * @param int $idTriage
     * @param int $idEnfermera ID del usuario enfermera.
     * @param int $idNivel Nuevo nivel de triage.
     * @param string $observaciones_signos Signos vitales y/u observaciones.
     * @param string $estado_triage Nuevo estado ('Clasificado' o 'En Atención').
     * @param bool $esRetriage Indica si es una reclasificación (Re-Triage).
     * @return bool
     */
    public function clasificarTriage(
        $idTriage, $idEnfermera, $idNivel, $observaciones_signos, 
        $estado_triage = 'Clasificado', $esRetriage = false
    )
    {
        // Si es re-triage, actualizamos la fecha de clasificación, si no es re-triage, el campo debe ser actualizado a la hora actual
        $setClasificacion = $esRetriage ? ", fecha_hora_clasificacion = NOW()" : "";
        $esRetriageValor = $esRetriage ? 1 : 0;
        
        $sql = "UPDATE triage SET 
                id_enfermera = ?, 
                id_nivel = ?, 
                observaciones_signos = ?, 
                estado_triage = ?,
                es_retriage = ?
                {$setClasificacion}
                WHERE id_triage = ?";
                
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iissii", $idEnfermera, $idNivel, $observaciones_signos, $estado_triage, $esRetriageValor, $idTriage);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    //---------------------------------------------------------
    // Métodos para Médico (Atención)
    //---------------------------------------------------------
    
    /**
     * Obtiene la cola de pacientes clasificados y listos para ser atendidos.
     * Ordena por prioridad (id_nivel ASC) y luego por tiempo de clasificación (FIFO).
     * @return array
     */
    public function obtenerColaUrgenciasMedicos()
    {
        // Estados: 'Clasificado' (listo para médico) y 'En Atención'
        $sql = "SELECT t.id_triage, u.nombre, u.apellido_paterno, t.motivo_principal, 
                       n.codigo AS nivel_codigo, n.color AS nivel_color, n.tiempo_max_espera_min,
                       t.fecha_hora_clasificacion, t.estado_triage
                FROM triage t
                INNER JOIN usuarios u ON t.id_paciente = u.id_usuario
                INNER JOIN niveles_triage n ON t.id_nivel = n.id_nivel
                WHERE t.estado_triage IN ('Clasificado', 'En Atención')
                ORDER BY n.id_nivel ASC, t.fecha_hora_clasificacion ASC"; 

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
     * Actualiza el estado de atención de un paciente (Ej: 'En Atención', 'Atendido', 'Derivado', 'Alta').
     * @param int $idTriage
     * @param string $nuevoEstado El nuevo estado (e.g., 'En Atención').
     * @return bool
     */
    public function actualizarEstadoTriage($idTriage, $nuevoEstado)
    {
        $sql = "UPDATE triage SET estado_triage = ? WHERE id_triage = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $nuevoEstado, $idTriage);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>