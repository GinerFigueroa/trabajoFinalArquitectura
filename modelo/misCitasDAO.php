<?php
include_once('conexion.php');

class MisCitasDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todas las citas programadas para un médico específico
     */
    public function obtenerCitasPorMedico($idMedico)
    {
        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.duracion,
                    c.estado,
                    c.notas,
                    c.creado_en,
                    t.nombre as tratamiento_nombre,
                    t.costo as tratamiento_costo,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) as nombre_paciente,
                    p.dni,
                    u.telefono,  -- CORREGIDO: telefono viene de usuarios, no de pacientes
                    TIMESTAMP(c.fecha_hora) as fecha_completa,
                    DATE(c.fecha_hora) as fecha_cita,
                    TIME(c.fecha_hora) as hora_cita,
                    DAYNAME(c.fecha_hora) as dia_semana
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                WHERE c.id_medico = ? 
                AND c.fecha_hora >= CURDATE()
                AND c.estado IN ('Pendiente', 'Confirmada')
                ORDER BY c.fecha_hora ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
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
     * Obtiene citas por fecha específica para un médico
     */
    public function obtenerCitasPorFecha($idMedico, $fecha)
    {
        $sql = "SELECT 
                    c.id_cita,
                    c.fecha_hora,
                    c.duracion,
                    c.estado,
                    c.notas,
                    t.nombre as tratamiento_nombre,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) as nombre_paciente,
                    p.dni,
                    u.telefono,  -- CORREGIDO
                    TIME(c.fecha_hora) as hora_cita
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                WHERE c.id_medico = ? 
                AND DATE(c.fecha_hora) = ?
                AND c.estado IN ('Pendiente', 'Confirmada')
                ORDER BY c.fecha_hora ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("is", $idMedico, $fecha);
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
     * Obtiene estadísticas de citas para el médico
     */
    public function obtenerEstadisticasCitas($idMedico)
    {
        $sql = "SELECT 
                    COUNT(*) as total_citas,
                    SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                    SUM(CASE WHEN estado = 'Confirmada' THEN 1 ELSE 0 END) as confirmadas,
                    SUM(CASE WHEN estado = 'Completada' THEN 1 ELSE 0 END) as completadas,
                    SUM(CASE WHEN estado = 'Cancelada' THEN 1 ELSE 0 END) as canceladas,
                    MIN(fecha_hora) as proxima_cita
                FROM citas 
                WHERE id_medico = ? 
                AND fecha_hora >= CURDATE()";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estadisticas = $resultado->fetch_assoc();
        $stmt->close();

        return $estadisticas;
    }

    /**
     * Obtiene el id_medico a partir del id_usuario
     */
    public function obtenerIdMedicoPorUsuario($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            return $fila['id_medico'];
        }
        
        return null;
    }

    /**
     * Actualiza el estado de una cita
     */
    public function actualizarEstadoCita($idCita, $estado)
    {
        $sql = "UPDATE citas SET estado = ? WHERE id_cita = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("si", $estado, $idCita);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Obtiene citas agrupadas por día para calendario
     */
    public function obtenerCitasAgrupadasPorDia($idMedico, $fechaInicio = null, $fechaFin = null)
    {
        if (!$fechaInicio) $fechaInicio = date('Y-m-d');
        if (!$fechaFin) $fechaFin = date('Y-m-d', strtotime('+7 days'));
        
        $sql = "SELECT 
                    DATE(fecha_hora) as fecha,
                    COUNT(*) as total_citas,
                    GROUP_CONCAT(CONCAT(TIME(fecha_hora), '|', 
                              (SELECT CONCAT(nombre, ' ', apellido_paterno) 
                               FROM usuarios u 
                               JOIN pacientes p ON u.id_usuario = p.id_usuario 
                               WHERE p.id_paciente = c.id_paciente), '|',
                              t.nombre) SEPARATOR ';') as detalles_citas
                FROM citas c
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                WHERE c.id_medico = ? 
                AND DATE(c.fecha_hora) BETWEEN ? AND ?
                AND c.estado IN ('Pendiente', 'Confirmada')
                GROUP BY DATE(fecha_hora)
                ORDER BY fecha ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iss", $idMedico, $fechaInicio, $fechaFin);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $citasPorDia = [];
        while ($fila = $resultado->fetch_assoc()) {
            $citasPorDia[$fila['fecha']] = $fila;
        }
        
        $stmt->close();
        return $citasPorDia;
    }

    /**
     * Obtiene información detallada de una cita específica
     */
    public function obtenerDetalleCita($idCita)
    {
        $sql = "SELECT 
                    c.*,
                    t.nombre as tratamiento_nombre,
                    t.descripcion as tratamiento_descripcion,
                    t.costo as tratamiento_costo,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) as nombre_paciente,
                    p.dni,
                    p.fecha_nacimiento,
                    p.ocupacion,
                    p.domicilio,
                    u_pac.telefono,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) as nombre_medico,
                    m.cedula_profesional
                FROM citas c
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                JOIN medicos m ON c.id_medico = m.id_medico
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                WHERE c.id_cita = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idCita);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $cita = $resultado->fetch_assoc();
        $stmt->close();

        return $cita;
    }
}
?>