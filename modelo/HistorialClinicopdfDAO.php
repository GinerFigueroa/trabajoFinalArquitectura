<?php
include_once('conexion.php');

class HistorialClinicoDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene pacientes con historial clínico asignado
     */
    public function obtenerPacientesConHistorial()
    {
        $sql = "SELECT DISTINCT
                    p.id_paciente,
                    hc.historia_clinica_id,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
                    p.dni,
                    hc.fecha_creacion,
                    CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
                FROM historia_clinica hc
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                LEFT JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno, u.apellido_materno";

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
     * Obtiene historial completo por ID de historia clínica
     */
    public function obtenerHistorialCompletoPorHC($historiaClinicaId)
    {
        $datos = [];

        // Información básica del paciente
        $datos['paciente'] = $this->obtenerInfoPacientePorHC($historiaClinicaId);
        if (!$datos['paciente']) {
            return null;
        }

        // Anamnesis/Historial de anemia
        $datos['anamnesis'] = $this->obtenerAnamnesisPorHC($historiaClinicaId);

        // Registros médicos
        $datos['registros_medicos'] = $this->obtenerRegistrosMedicosPorHC($historiaClinicaId);

        // Evoluciones médicas
        $datos['evoluciones'] = $this->obtenerEvolucionesPorHC($historiaClinicaId);

        // Órdenes de examen
        $datos['ordenes_examen'] = $this->obtenerOrdenesExamenPorHC($historiaClinicaId);

        return $datos;
    }

    private function obtenerInfoPacientePorHC($historiaClinicaId)
    {
        $sql = "SELECT 
                    hc.historia_clinica_id,
                    hc.fecha_creacion,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
                    p.dni,
                    CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
                FROM historia_clinica hc
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                LEFT JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario
                WHERE hc.historia_clinica_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $paciente = $resultado->fetch_assoc();
        $stmt->close();

        return $paciente;
    }

    private function obtenerAnamnesisPorHC($historiaClinicaId)
    {
        $sql = "SELECT * FROM anamnesis WHERE historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $anamnesis = $resultado->fetch_assoc();
        $stmt->close();

        return $anamnesis;
    }

    private function obtenerRegistrosMedicosPorHC($historiaClinicaId)
    {
        $sql = "SELECT 
                    rm.registro_medico_id,
                    rm.fecha_registro,
                    rm.riesgos,
                    rm.motivo_consulta,
                    rm.enfermedad_actual,
                    rm.tiempo_enfermedad,
                    rm.signos_sintomas,
                    rm.motivo_ultima_visita,
                    rm.ultima_visita_medica
                FROM registro_medico rm
                WHERE rm.historia_clinica_id = ?
                ORDER BY rm.fecha_registro DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $registros = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $registros[] = $fila;
            }
        }
        $stmt->close();

        return $registros;
    }

    private function obtenerEvolucionesPorHC($historiaClinicaId)
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

    private function obtenerOrdenesExamenPorHC($historiaClinicaId)
    {
        $sql = "SELECT 
                    oe.id_orden,
                    oe.fecha,
                    oe.tipo_examen,
                    oe.indicaciones,
                    oe.estado,
                    oe.resultados
                FROM orden_examen oe
                WHERE oe.historia_clinica_id = ?
                ORDER BY oe.fecha DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ordenes = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ordenes[] = $fila;
            }
        }
        $stmt->close();

        return $ordenes;
    }

    // Métodos para estadísticas
    public function obtenerTotalRegistros()
    {
        $sql = "SELECT COUNT(*) as total FROM registro_medico";
        $resultado = $this->connection->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila['total'];
    }

    public function obtenerTotalEvoluciones()
    {
        $sql = "SELECT COUNT(*) as total FROM evolucion_medica_paciente";
        $resultado = $this->connection->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila['total'];
    }

    public function obtenerTotalAnamnesis()
    {
        $sql = "SELECT COUNT(*) as total FROM anamnesis";
        $resultado = $this->connection->query($sql);
        $fila = $resultado->fetch_assoc();
        return $fila['total'];
    }
}
?>