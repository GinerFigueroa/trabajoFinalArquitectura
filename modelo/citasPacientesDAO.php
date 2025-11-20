<?php
include_once('conexion.php');

class CitasPacientesDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todas las citas de un paciente específico
     */
    public function obtenerCitasPorPaciente($idPaciente)
    {
        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.duracion,
                    c.estado,
                    c.notas,
                    c.creado_en,
                    t.nombre as tratamiento_nombre,
                    t.descripcion as tratamiento_descripcion,
                    t.costo as tratamiento_costo,
                    t.duracion_estimada,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno, ' ', u_med.apellido_materno) as nombre_medico,
                    m.cedula_profesional,
                    e.nombre as especialidad,
                    DATE(c.fecha_hora) as fecha_cita,
                    TIME(c.fecha_hora) as hora_cita,
                    DAYNAME(c.fecha_hora) as dia_semana,
                    CASE 
                        WHEN c.fecha_hora < NOW() AND c.estado = 'Pendiente' THEN 'Vencida'
                        ELSE c.estado
                    END as estado_visual
                FROM citas c
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                JOIN medicos m ON c.id_medico = m.id_medico
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                LEFT JOIN especialidades_medicas e ON m.id_especialidad = e.id_especialidad
                WHERE c.id_paciente = ? 
                ORDER BY c.fecha_hora DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $citas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $citas[] = $fila;
        }
        
        $stmt->close();
        return $citas;
    }

    /**
     * Obtiene citas futuras de un paciente
     */
    public function obtenerCitasFuturas($idPaciente)
    {
        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.duracion,
                    c.estado,
                    c.notas,
                    t.nombre as tratamiento_nombre,
                    t.costo as tratamiento_costo,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) as nombre_medico,
                    e.nombre as especialidad,
                    DATE(c.fecha_hora) as fecha_cita,
                    TIME(c.fecha_hora) as hora_cita
                FROM citas c
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                JOIN medicos m ON c.id_medico = m.id_medico
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                LEFT JOIN especialidades_medicas e ON m.id_especialidad = e.id_especialidad
                WHERE c.id_paciente = ? 
                AND c.fecha_hora >= CURDATE()
                AND c.estado IN ('Pendiente', 'Confirmada')
                ORDER BY c.fecha_hora ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $citas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $citas[] = $fila;
        }
        
        $stmt->close();
        return $citas;
    }

    /**
     * Obtiene estadísticas de citas para el paciente
     */
    public function obtenerEstadisticasCitas($idPaciente)
    {
        $sql = "SELECT 
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado = 'Pendiente' AND fecha_hora >= NOW() THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'Confirmada' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas,
                    SUM(CASE WHEN estado = 'Pendiente' AND fecha_hora < NOW() THEN 1 ELSE 0 END) as vencidas,
                    MIN(CASE WHEN estado IN ('Pendiente', 'Confirmada') AND fecha_hora >= NOW() THEN fecha_hora END) as proxima_cita
                FROM citas 
                WHERE id_paciente = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estadisticas = $resultado->fetch_assoc();
        $stmt->close();

        return $estadisticas;
    }

    /**
     * Obtiene el id_paciente a partir del id_usuario
     */
    public function obtenerIdPacientePorUsuario($idUsuario)
    {
        $sql = "SELECT id_paciente FROM pacientes WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            return $fila['id_paciente'];
        }
        
        return null;
    }

    /**
     * Obtiene información básica del paciente
     */
    public function obtenerInfoPaciente($idPaciente)
    {
        $sql = "SELECT 
                    p.id_paciente,
                    p.dni,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) as nombre_completo,
                    u.telefono,
                    u.email
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
     * Cancela una cita (solo si está pendiente o confirmada)
     */
    public function cancelarCita($idCita, $idPaciente)
    {
        $sql = "UPDATE citas SET estado = 'Cancelada' 
                WHERE id_cita = ? AND id_paciente = ? 
                AND estado IN ('Pendiente', 'Confirmada')";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $idCita, $idPaciente);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Verifica si una cita pertenece al paciente
     */
    public function verificarPropiedadCita($idCita, $idPaciente)
    {
        $sql = "SELECT COUNT(*) as count FROM citas 
                WHERE id_cita = ? AND id_paciente = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $idCita, $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
     * Obtiene tratamientos disponibles para nuevas citas
     */
    public function obtenerTratamientosDisponibles()
    {
        $sql = "SELECT 
                    id_tratamiento, 
                    nombre, 
                    descripcion, 
                    costo, 
                    duracion_estimada,
                    nombre_especialidad
                FROM tratamientos t
                LEFT JOIN especialidades_medicas e ON t.id_especialidad = e.id_especialidad
                WHERE t.activo = 1
                ORDER BY t.nombre";

        $resultado = $this->connection->query($sql);
        $tratamientos = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $tratamientos[] = $fila;
            }
        }

        return $tratamientos;
    }
}
?>