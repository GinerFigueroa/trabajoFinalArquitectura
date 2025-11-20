<?php
// Archivo: modelo/CitasDAO.php

// Asegúrate de que este path apunte a tu archivo Conexion.php con el Singleton
include_once('conexion.php'); 

/**
 * Clase CitaDAO (Data Access Object)
 * Responsable única de todas las operaciones CRUD para la entidad 'citas'.
 */
class CitasDAO
{
    private $connection;

    // Constructor que obtiene la ÚNICA instancia de la conexión Singleton
    public function __construct()
    {
        // Se obtiene la instancia Singleton y luego el objeto de conexión mysqli
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todas las citas con detalles de paciente, tratamiento y médico.
     * @return array
     */
    public function obtenerTodasCitas()
    {
        $sql = "SELECT 
                    c.*,
                    CONCAT(up.nombre, ' ', up.apellido_paterno) AS nombre_paciente,
                    t.nombre AS nombre_tratamiento,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico
                FROM citas c
                LEFT JOIN pacientes p ON c.id_paciente = p.id_paciente
                LEFT JOIN usuarios up ON p.id_usuario = up.id_usuario
                LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                LEFT JOIN medicos m ON c.id_medico = m.id_medico
                LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
                ORDER BY c.fecha_hora DESC";
        
        $resultado = $this->connection->query($sql);
        $citas = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $citas[] = $fila;
            }
        }
        
        return $citas;
    }
    
    /**
     * Obtiene una cita específica por su ID.
     * @param int $idCita
     * @return array|null
     */
    public function obtenerCitaPorId($idCita)
    {
        $sql = "SELECT * FROM citas WHERE id_cita = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idCita);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $cita = $resultado->fetch_assoc();
        
        $stmt->close();
        return $cita;
    }
    
    /**
     * Valida si el médico tiene un conflicto de horario.
     * @return bool (true si hay conflicto)
     */
    public function validarDisponibilidadMedico($idMedico, $fechaHora, $duracion, $idCita = null)
    {
        // Calcular el fin de la cita propuesta
        $fechaFin = date('Y-m-d H:i:s', strtotime("+$duracion minutes", strtotime($fechaHora)));
        
        // Se añade un 1 en la selección para que el bind_result funcione correctamente en COUNT(*)
        $sql = "SELECT 1 FROM citas 
                WHERE id_medico = ? 
                AND estado IN ('Pendiente', 'Confirmada')
                AND (
                    (fecha_hora >= ? AND fecha_hora < ?) OR 
                    (DATE_ADD(fecha_hora, INTERVAL duracion MINUTE) > ? AND DATE_ADD(fecha_hora, INTERVAL duracion MINUTE) <= ?) OR
                    (fecha_hora < ? AND DATE_ADD(fecha_hora, INTERVAL duracion MINUTE) > ?)
                )";
        
        if ($idCita) {
            $sql .= " AND id_cita != ?";
        }
        
        $stmt = $this->connection->prepare($sql);
        
        if ($idCita) {
            // Tipos: i, s, s, s, s, s, s, i
            $stmt->bind_param("issssssi", $idMedico, $fechaHora, $fechaFin, $fechaHora, $fechaFin, $fechaHora, $fechaFin, $idCita);
        } else {
            // Tipos: i, s, s, s, s, s, s
            $stmt->bind_param("issssss", $idMedico, $fechaHora, $fechaFin, $fechaHora, $fechaFin, $fechaHora, $fechaFin);
        }

        $stmt->execute();
        $resultado = $stmt->get_result();
        $count = $resultado->num_rows;
        $stmt->close();
        
        return $count > 0; // true si hay conflicto
    }

    /**
     * Registra una nueva cita.
     * @return bool
     */
    public function registrarCita($idPaciente, $idTratamiento, $idMedico, $fechaHora, $duracion, $estado, $notas, $creadoPor)
    {
        $sql = "INSERT INTO citas (id_paciente, id_tratamiento, id_medico, fecha_hora, duracion, estado, notas, creado_por)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        // CORRECCIÓN: Los tipos deben ser correctos: 3i (int, int, int), s (datetime), i (duracion), s (estado), s (notas), i (creadoPor)
        $stmt->bind_param("iiisissi", $idPaciente, $idTratamiento, $idMedico, $fechaHora, $duracion, $estado, $notas, $creadoPor);

        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita una cita existente.
     * @return bool
     */
    public function editarCita($idCita, $idPaciente, $idTratamiento, $idMedico, $fechaHora, $duracion, $estado, $notas)
    {
        $sql = "UPDATE citas SET id_paciente = ?, id_tratamiento = ?, id_medico = ?, fecha_hora = ?, duracion = ?, estado = ?, notas = ? WHERE id_cita = ?";
        
        $stmt = $this->connection->prepare($sql);
        
        // Tipos: 3i, s, i, s, s, i
        $stmt->bind_param("iiisissi", $idPaciente, $idTratamiento, $idMedico, $fechaHora, $duracion, $estado, $notas, $idCita);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina una cita.
     * @return bool
     */
    public function eliminarCita($idCita)
    {
        $sql = "DELETE FROM citas WHERE id_cita = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idCita);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene las citas completadas de un paciente para generar órdenes (ej. Ordenes de Pago/Procedimiento).
     * @return array
     */
    public function obtenerCitasCompletadasPorPaciente($idPaciente)
    {
        $sql = "SELECT 
                    c.id_cita, c.fecha_hora, t.nombre AS nombre_tratamiento,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico
                FROM citas c
                LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                LEFT JOIN medicos m ON c.id_medico = m.id_medico
                LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
                WHERE c.id_paciente = ? AND c.estado = 'Completada'
                ORDER BY c.fecha_hora DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $citas = $resultado->fetch_all(MYSQLI_ASSOC);
        
        $stmt->close();
        return $citas;
    }


}

class EntidadesDAO
{
    private $connection;

    /**
     * Constructor: Obtiene la ÚNICA instancia de la conexión a través del Singleton.
     */
    public function __construct()
    {
        // El DAO consume el Singleton para obtener el objeto de conexión mysqli
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    // --- Métodos de Obtención de Listas (Lookups) ---

  

    /**
     * Obtiene una lista de tratamientos que están activos.
     * @return array
     */
    public function obtenerTratamientosActivos()
    {
        $sql = "SELECT id_tratamiento, nombre, duracion_estimada FROM tratamientos WHERE activo = 1";
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene una lista de médicos activos disponibles para la asignación de citas.
     * @return array
     */
    public function obtenerMedicosDisponibles()
    {
        $sql = "SELECT m.id_medico, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
                FROM medicos m
                JOIN usuarios u ON m.id_usuario = u.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    // --- Métodos de Validación de Existencia ---

    /**
     * Valida si un paciente existe y está activo.
     * @param int $idPaciente
     * @return bool
     */
    public function pacienteExiste($idPaciente)
    {
        $sql = "SELECT COUNT(*) FROM pacientes p JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE p.id_paciente = ? AND u.activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }
    
    /**
     * Valida si un tratamiento existe y está activo.
     * @param int $idTratamiento
     * @return bool
     */
    public function tratamientoExiste($idTratamiento)
    {
        $sql = "SELECT COUNT(*) FROM tratamientos WHERE id_tratamiento = ? AND activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idTratamiento);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }

    /**
     * Valida si un médico existe y está activo.
     * @param int $idMedico
     * @return bool
     */
    public function medicoExiste($idMedico)
    {
        $sql = "SELECT COUNT(*) FROM medicos m JOIN usuarios u ON m.id_usuario = u.id_usuario WHERE m.id_medico = ? AND u.activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        
        return $count > 0;
    }
    //---medico---//
    public function obtenerCitasPorMedico($idMedico)
    {
        // Asumiendo que la tabla 'citas' tiene el campo 'dr_asignado_id' y 'paciente_id'.
        // Y que podemos hacer JOINs a 'pacientes' y 'usuarios' para obtener el nombre.
        $sql = "SELECT 
                    c.cita_id, c.fecha_hora_cita, c.motivo, 
                    u.nombre, u.apellido_paterno, u.apellido_materno,
                    c.paciente_id
                FROM citas c
                INNER JOIN pacientes p ON c.paciente_id = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE c.dr_asignado_id = ? 
                ORDER BY c.fecha_hora_cita ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $citas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Formatear el nombre del paciente en cada registro
        foreach ($citas as $key => $cita) {
            $citas[$key]['nombre_paciente'] = trim("{$cita['nombre']} {$cita['apellido_paterno']} {$cita['apellido_materno']}");
            unset($citas[$key]['nombre'], $citas[$key]['apellido_paterno'], $citas[$key]['apellido_materno']);
        }
        
        return $citas;
    }

    //----modelo-paciente--//

    /**
     * Obtiene todas las citas de un paciente específico, incluyendo detalles del médico y tratamiento.
     * @param int $idPaciente El ID del usuario que es paciente.
     * @return array
     */
    public function obtenerCitasPorPaciente($idPaciente)
    {
        $sql = "SELECT c.id_cita, c.fecha_hora, c.estado, t.nombre AS nombre_tratamiento, 
                       u_medico.nombre AS nombre_medico, u_medico.apellido_paterno AS apellido_medico
                FROM citas c
                INNER JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                INNER JOIN usuarios u_paciente ON c.id_paciente = u_paciente.id_usuario
                INNER JOIN usuarios u_medico ON c.id_medico = u_medico.id_usuario -- Asumiendo que id_medico es el id_usuario del médico
                WHERE c.id_paciente = ?
                ORDER BY c.fecha_hora DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $citas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $citas;
    }
  /**
 * Obtiene una lista de pacientes activos disponibles para la asignación de citas.
 * @return array
 */
public function obtenerPacientesDisponibles()
{
    // CAMBIO APLICADO AQUÍ: se utiliza p.dni en lugar de u.dni
    $sql = "SELECT p.id_paciente, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
            FROM pacientes p
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE u.activo = 1
            ORDER BY u.apellido_paterno, u.nombre";
    
    $resultado = $this->connection->query($sql);
    
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}

}
?>

