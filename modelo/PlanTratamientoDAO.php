<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\PlanTratamientoDAO.php
include_once('Conexion.php'); 

class PlanTratamientoDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =================================================================================
    // MÉTODOS AUXILIARES (SIMULANDO EntidadAuxiliarDAO)
    // =================================================================================
    
    /**
     * Auxiliar: Obtiene el nombre completo del paciente a partir del ID de Historia Clínica.
     * @param int $idHistoriaClinica
     * @return string|null
     */
    public function obtenerNombrePacientePorIdHC($idHistoriaClinica)
    {
        // Esta consulta asume que existe la tabla 'historia_clinica' con 'id_paciente', 
        // 'pacientes' con 'id_usuario', y 'usuarios' con 'nombre', 'apellido_paterno', 'apellido_materno'.
        $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE hc.historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idHistoriaClinica);
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
     * Auxiliar: Obtiene una lista de todas las Historias Clínicas para que el médico 
     * pueda seleccionar a qué paciente asignarle el plan.
     * @return array
     */
    public function obtenerTodasHistoriasClinicas()
    {
        $sql = "SELECT 
                    hc.historia_clinica_id,
                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        $historias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $fila['nombre_paciente'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
            $historias[] = $fila;
        }
        return $historias;
    }

    // =================================================================================
    // MÉTODOS CRUD PRINCIPALES PARA PLAN DE TRATAMIENTO
    // =================================================================================
    
    /**
     * Obtiene todos los Planes de Tratamiento creados por un médico evaluador.
     * @param int $idMedico El id_usuario del médico logueado.
     * @return array
     */
    public function obtenerPlanesPorMedico($idMedico)
    {
        $sql = "SELECT plan_id, historia_clinica_id, descripcion_plan, fecha_creacion
                FROM plan_tratamiento
                WHERE dr_evaluador_id = ? 
                ORDER BY fecha_creacion DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $planes = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Adjuntar nombre del paciente
        foreach ($planes as $key => $plan) {
            $planes[$key]['nombre_paciente'] = $this->obtenerNombrePacientePorIdHC($plan['historia_clinica_id']);
        }
        
        return $planes;
    }

    /**
     * Obtiene un Plan de Tratamiento específico por ID.
     * @param int $planId
     * @return array|null
     */
    public function obtenerPlanPorId($planId)
    {
        $sql = "SELECT * FROM plan_tratamiento WHERE plan_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $plan = $resultado->fetch_assoc();
        $stmt->close();
        
        return $plan;
    }

    /**
     * Registra un nuevo Plan de Tratamiento.
     * @return bool
     */
    public function registrarPlan($historiaClinicaId, $descripcionPlan, $drEvaluadorId) 
    {
        $fecha = date('Y-m-d');
        $sql = "INSERT INTO plan_tratamiento 
                (historia_clinica_id, descripcion_plan, fecha_creacion, dr_evaluador_id)
                VALUES (?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("issi", 
            $historiaClinicaId, 
            $descripcionPlan, 
            $fecha, 
            $drEvaluadorId
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita la descripción y/o HC de un Plan de Tratamiento.
     * @return bool
     */
    public function editarPlan($planId, $historiaClinicaId, $descripcionPlan)
    {
        $sql = "UPDATE plan_tratamiento SET 
                historia_clinica_id = ?, 
                descripcion_plan = ?
                WHERE plan_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("isi", 
            $historiaClinicaId, 
            $descripcionPlan, 
            $planId
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado; 
    }

    /**
     * Elimina un Plan de Tratamiento (y potencialmente sus presupuestos asociados via CASCADE).
     * Nota: Se recomienda configurar CASCADE DELETE en la DB para la tabla 'presupuesto'.
     * @param int $planId
     * @return bool
     */
    public function eliminarPlan($planId)
    {
        // 1. Eliminar ítems de presupuesto asociados (si no hay CASCADE configurado en DB)
        $this->connection->begin_transaction();
        
        try {
            $stmt_presupuesto = $this->connection->prepare("DELETE FROM presupuesto WHERE plan_id = ?");
            $stmt_presupuesto->bind_param("i", $planId);
            $stmt_presupuesto->execute();
            $stmt_presupuesto->close();
            
            // 2. Eliminar el Plan
            $stmt_plan = $this->connection->prepare("DELETE FROM plan_tratamiento WHERE plan_id = ?");
            $stmt_plan->bind_param("i", $planId);
            $resultado = $stmt_plan->execute();
            $stmt_plan->close();

            if ($resultado) {
                $this->connection->commit();
                return true;
            } else {
                $this->connection->rollback();
                return false;
            }
        } catch (Exception $e) {
            $this->connection->rollback();
            // Log error: $e->getMessage();
            return false;
        }
    }
}
?>