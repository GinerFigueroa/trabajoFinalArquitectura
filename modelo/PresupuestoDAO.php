<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\PresupuestoDAO.php
include_once('Conexion.php'); 

class PresupuestoDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =================================================================================
    // MÉTODOS AUXILIARES (SIMULANDO EntidadAuxiliarDAO)
    // =================================================================================
    
    /**
     * Auxiliar: Obtiene la información clave del Plan de Tratamiento.
     * @param int $planId
     * @return array|null
     */
    public function obtenerInfoPlan($planId)
    {
        $sql = "SELECT pt.plan_id, pt.historia_clinica_id, pt.descripcion_plan, pt.fecha_creacion,
                       hc.id_paciente
                FROM plan_tratamiento pt
                INNER JOIN historia_clinica hc ON pt.historia_clinica_id = hc.historia_clinica_id
                WHERE pt.plan_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $info = $resultado->fetch_assoc();
        $stmt->close();
        
        if ($info) {
            $info['nombre_paciente'] = $this->obtenerNombrePacientePorIdHC($info['historia_clinica_id']);
        }
        return $info;
    }

    /**
     * Auxiliar: Obtiene el nombre completo del paciente a partir del ID de Historia Clínica.
     * @param int $idHistoriaClinica
     * @return string|null
     */
    public function obtenerNombrePacientePorIdHC($idHistoriaClinica)
    {
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
     * Auxiliar: Obtiene una lista de Planes de Tratamiento creados por el médico.
     * @param int $idMedico
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
        
        // Agregar nombre del paciente a cada plan
        foreach ($planes as $key => $plan) {
            $planes[$key]['nombre_paciente'] = $this->obtenerNombrePacientePorIdHC($plan['historia_clinica_id']);
        }
        
        return $planes;
    }

    /**
     * Auxiliar: Calcula el costo total de un presupuesto (cantidad * costo_unitario).
     * @param int $cantidad
     * @param float $costoUnitario
     * @return float
     */
    private function calcularCostoTotal($cantidad, $costoUnitario) {
        return (float)$cantidad * (float)$costoUnitario;
    }

    // =================================================================================
    // MÉTODOS CRUD PRINCIPALES PARA PRESUPUESTO
    // =================================================================================
    
    /**
     * Obtiene todos los ítems de presupuesto para un Plan específico.
     * @param int $planId
     * @return array
     */
    public function obtenerPresupuestosPorPlan($planId)
    {
        $sql = "SELECT * FROM presupuesto
                WHERE plan_id = ? 
                ORDER BY presupuesto_id ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $planId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $presupuestos = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $presupuestos;
    }

    /**
     * Obtiene un ítem de presupuesto específico por ID.
     * @param int $presupuestoId
     * @return array|null
     */
    public function obtenerPresupuestoPorId($presupuestoId)
    {
        $sql = "SELECT * FROM presupuesto WHERE presupuesto_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $presupuestoId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $presupuesto = $resultado->fetch_assoc();
        $stmt->close();
        
        return $presupuesto;
    }

    /**
     * Registra un nuevo ítem de presupuesto.
     * @return bool
     */
    public function registrarPresupuesto($planId, $tratamientoDescripcion, $cantidad, $costoUnitario) 
    {
        $fecha = date('Y-m-d');
        $costoTotal = $this->calcularCostoTotal($cantidad, $costoUnitario);

        $sql = "INSERT INTO presupuesto 
                (plan_id, fecha_presupuesto, tratamiento_descripcion, cantidad, costo_unitario, costo_total)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("isssdd", 
            $planId, 
            $fecha, 
            $tratamientoDescripcion, 
            $cantidad, 
            $costoUnitario,
            $costoTotal
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita un ítem de presupuesto existente.
     * @return bool
     */
    public function editarPresupuesto($presupuestoId, $tratamientoDescripcion, $cantidad, $costoUnitario)
    {
        $costoTotal = $this->calcularCostoTotal($cantidad, $costoUnitario);

        $sql = "UPDATE presupuesto SET 
                tratamiento_descripcion = ?, 
                cantidad = ?, 
                costo_unitario = ?, 
                costo_total = ?
                WHERE presupuesto_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sdddi", 
            $tratamientoDescripcion, 
            $cantidad, 
            $costoUnitario, 
            $costoTotal, 
            $presupuestoId
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado; 
    }

    /**
     * Elimina un ítem de presupuesto.
     * @param int $presupuestoId
     * @return bool
     */
    public function eliminarPresupuesto($presupuestoId)
    {
        $stmt = $this->connection->prepare("DELETE FROM presupuesto WHERE presupuesto_id = ?");
        $stmt->bind_param("i", $presupuestoId);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>