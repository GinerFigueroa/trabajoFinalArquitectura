<?php
include_once('conexion.php');
include_once('EntidadAuxiliarDAO.php');

class VerMisInternadoDAO
{
    private $connection;
    private $objAuxiliar;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
        $this->objAuxiliar = new EntidadAuxiliarDAO();
    }

    /**
     * Obtiene los pacientes internados asignados a un médico específico (SOLO LECTURA)
     */
    public function obtenerMisPacientesInternados($idMedico)
    {
        $sql = "SELECT 
                    i.id_internado,
                    i.id_paciente,
                    i.id_habitacion,
                    i.id_medico,
                    i.fecha_ingreso,
                    i.fecha_alta,
                    i.diagnostico_ingreso,
                    i.diagnostico_egreso,
                    i.observaciones,
                    i.estado,
                    h.numero_puerta,
                    h.tipo as tipo_habitacion,
                    h.piso
                FROM internados i
                INNER JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
                WHERE i.id_medico = ? 
                ORDER BY i.fecha_ingreso DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $internados = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // Enriquecer con datos del paciente (solo lectura)
                $fila['nombre_paciente'] = $this->objAuxiliar->obtenerNombrePacientePorId($fila['id_paciente']);
                $fila['dni_paciente'] = $this->objAuxiliar->obtenerDniPacientePorId($fila['id_paciente']);
                $fila['edad_paciente'] = $this->objAuxiliar->obtenerEdadPacientePorId($fila['id_paciente']);
                $fila['sexo_paciente'] = $this->objAuxiliar->obtenerSexoPacientePorId($fila['id_paciente']);
                $internados[] = $fila;
            }
        }
        $stmt->close();
        
        return $internados;
    }

    /**
     * Obtiene estadísticas de los pacientes del médico (SOLO LECTURA)
     */
    public function obtenerEstadisticasPacientes($idMedico)
    {
        $sql = "SELECT 
                    COUNT(*) as total_pacientes,
                    SUM(CASE WHEN estado = 'Activo' THEN 1 ELSE 0 END) as activos,
                    SUM(CASE WHEN estado = 'Alta' THEN 1 ELSE 0 END) as altas,
                    SUM(CASE WHEN estado = 'Derivado' THEN 1 ELSE 0 END) as derivados,
                    SUM(CASE WHEN estado = 'Fallecido' THEN 1 ELSE 0 END) as fallecidos
                FROM internados 
                WHERE id_medico = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estadisticas = $resultado->fetch_assoc();
        $stmt->close();
        
        return $estadisticas;
    }
}
?>