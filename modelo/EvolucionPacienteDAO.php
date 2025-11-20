<?php
// EvolucionPacienteDAO.php

include_once('conexion.php');

class EvolucionPacienteDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Registra una nueva evolución médica (nota SOAP)
     * @param int $historiaClinicaId
     * @param int $idMedico
     * @param string $notaSubjetiva
     * @param string $notaObjetiva
     * @param string $analisis
     * @param string $planDeAccion
     * @return bool
     */
    public function registrarEvolucion($historiaClinicaId, $idMedico, $notaSubjetiva, $notaObjetiva, $analisis, $planDeAccion)
    {
        $sql = "INSERT INTO evolucion_medica_paciente 
                (historia_clinica_id, id_medico, nota_subjetiva, nota_objetiva, analisis, plan_de_accion, fecha_evolucion) 
                VALUES (?, ?, ?, ?, ?, ?, NOW())";
        
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }
        
        $stmt->bind_param("iissss", 
            $historiaClinicaId, 
            $idMedico, 
            $notaSubjetiva, 
            $notaObjetiva, 
            $analisis, 
            $planDeAccion
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando consulta: " . $stmt->error);
        }
        
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene el id_medico a partir del id_usuario
     * @param int $idUsuario
     * @return int|null
     */
    public function obtenerIdMedicoPorUsuario($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila ? $fila['id_medico'] : null;
    }

    /**
     * Obtiene todas las evoluciones de una historia clínica específica
     * @param int $historiaClinicaId
     * @return array
     */
    public function obtenerEvolucionesPorHistoria($historiaClinicaId)
    {
        $sql = "SELECT 
                    emp.id_evolucion,
                    emp.fecha_evolucion,
                    emp.nota_subjetiva,
                    emp.nota_objetiva,
                    emp.analisis,
                    emp.plan_de_accion,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_medico
                FROM evolucion_medica_paciente emp
                JOIN medicos m ON emp.id_medico = m.id_medico
                JOIN usuarios u ON m.id_usuario = u.id_usuario
                WHERE emp.historia_clinica_id = ?
                ORDER BY emp.fecha_evolucion DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $evoluciones = [];
        
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $evoluciones[] = $fila;
            }
        }
        $stmt->close();
        return $evoluciones;
    }

    /**
     * Elimina una evolución médica por su ID
     * @param int $idEvolucion
     * @return bool
     */
    public function eliminarEvolucion($idEvolucion)
    {
        $sql = "DELETE FROM evolucion_medica_paciente WHERE id_evolucion = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idEvolucion);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Obtiene TODAS las evoluciones médicas registradas
     * @return array
     */
    public function obtenerTodasEvoluciones()
    {
        $sql = "SELECT 
                    emp.id_evolucion,
                    emp.historia_clinica_id,
                    emp.fecha_evolucion,
                    emp.nota_subjetiva,
                    emp.nota_objetiva,
                    emp.analisis,
                    emp.plan_de_accion,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico,
                    hc.fecha_creacion
                FROM evolucion_medica_paciente emp
                JOIN historia_clinica hc ON emp.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN medicos m ON emp.id_medico = m.id_medico
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                ORDER BY emp.fecha_evolucion DESC";
        
        $resultado = $this->connection->query($sql);
        $evoluciones = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $evoluciones[] = $fila;
            }
            if (is_object($resultado)) {
                $resultado->free();
            }
        }
        
        return $evoluciones;
    }

    /**
     * Obtiene evoluciones por historia clínica específica
     * @param int $historiaClinicaId
     * @return array
     */
    public function obtenerEvolucionePorHistoria($historiaClinicaId)
    {
        $sql = "SELECT 
                    emp.id_evolucion,
                    emp.fecha_evolucion,
                    emp.nota_subjetiva,
                    emp.nota_objetiva,
                    emp.analisis,
                    emp.plan_de_accion,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico
                FROM evolucion_medica_paciente emp
                JOIN medicos m ON emp.id_medico = m.id_medico
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                WHERE emp.historia_clinica_id = ?
                ORDER BY emp.fecha_evolucion DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $evoluciones = [];
        
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $evoluciones[] = $fila;
            }
        }
        $stmt->close();
        return $evoluciones;
    }
// En EvolucionPacienteDAO.php - Agregar este método
/**
 * Edita una evolución médica existente
 * @param int $idEvolucion
 * @param string $notaSubjetiva
 * @param string $notaObjetiva
 * @param string $analisis
 * @param string $planDeAccion
 * @return bool
 */
public function editarEvolucion($idEvolucion, $notaSubjetiva, $notaObjetiva, $analisis, $planDeAccion)
{
    $sql = "UPDATE evolucion_medica_paciente SET 
            nota_subjetiva = ?, 
            nota_objetiva = ?, 
            analisis = ?, 
            plan_de_accion = ?
            WHERE id_evolucion = ?";

    $stmt = $this->connection->prepare($sql);
    
    if (!$stmt) {
        error_log("Error preparando consulta de edición: " . $this->connection->error);
        return false;
    }
    
    $stmt->bind_param("ssssi", 
        $notaSubjetiva, 
        $notaObjetiva, 
        $analisis, 
        $planDeAccion,
        $idEvolucion
    );
    
    $resultado = $stmt->execute();
    
    if (!$resultado) {
        error_log("Error ejecutando edición: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}

/**
 * Obtiene una evolución específica por su ID
 * @param int $idEvolucion
 * @return array|null
 */
public function obtenerEvolucionPorId($idEvolucion)
{
    $sql = "SELECT 
                emp.id_evolucion,
                emp.historia_clinica_id,
                emp.fecha_evolucion,
                emp.nota_subjetiva,
                emp.nota_objetiva,
                emp.analisis,
                emp.plan_de_accion,
                emp.id_medico
            FROM evolucion_medica_paciente emp
            WHERE emp.id_evolucion = ?";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idEvolucion);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $evolucion = $resultado->fetch_assoc();
    $stmt->close();
    
    return $evolucion;
}
// En EvolucionPacienteDAO.php - Agregar este método
/**
 * Obtiene los datos de una historia clínica por su ID
 * @param int $historiaClinicaId
 * @return array|null
 */
public function obtenerHistoriaPorId($historiaClinicaId)
{
    $sql = "SELECT 
                hc.historia_clinica_id, 
                hc.id_paciente,
                hc.fecha_creacion,
                CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
            FROM historia_clinica hc
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
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
  
}
?>