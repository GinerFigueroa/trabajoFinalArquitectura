<?php
include_once('conexion.php');
include_once('EntidadAuxiliarDAO.php');

class BoletaDAO
{
    private $connection;
    private $objAuxiliar;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
        $this->objAuxiliar = new EntidadAuxiliarDAO();
    }

    /**
     * Obtiene todas las boletas de un paciente específico
     * @param int $idPaciente
     * @return array
     */
    public function obtenerBoletasPorPaciente($idPaciente)
    {
        $sql = "SELECT b.*, op.concepto, p.nombre, p.apellido_paterno, p.apellido_materno, p.dni
                FROM boletas b
                INNER JOIN orden_pago op ON b.id_orden = op.id_orden
                INNER JOIN pacientes p ON op.id_paciente = p.id_paciente
                WHERE op.id_paciente = ?
                ORDER BY b.fecha_emision DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $boletas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $boletas[] = $fila;
        }
        
        $stmt->close();
        return $boletas;
    }

    /**
     * Obtiene el resumen financiero del paciente
     * @param int $idPaciente
     * @return array
     */
    public function obtenerResumenFinancieroPaciente($idPaciente)
    {
        $sql = "SELECT 
                    COUNT(*) as total_boletas,
                    SUM(b.monto_total) as monto_total,
                    AVG(b.monto_total) as promedio_boleta,
                    MIN(b.fecha_emision) as primera_boleta,
                    MAX(b.fecha_emision) as ultima_boleta,
                    b.metodo_pago,
                    COUNT(*) as cantidad_por_metodo
                FROM boletas b
                INNER JOIN orden_pago op ON b.id_orden = op.id_orden
                WHERE op.id_paciente = ?
                GROUP BY b.metodo_pago";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $resumen = [];
        while ($fila = $resultado->fetch_assoc()) {
            $resumen[] = $fila;
        }
        
        $stmt->close();
        return $resumen;
    }

    /**
     * Obtiene boletas por rango de fechas
     * @param int $idPaciente
     * @param string $fechaInicio
     * @param string $fechaFin
     * @return array
     */
    public function obtenerBoletasPorRangoFechas($idPaciente, $fechaInicio, $fechaFin)
    {
        $sql = "SELECT b.*, op.concepto, p.nombre, p.apellido_paterno, p.apellido_materno, p.dni
                FROM boletas b
                INNER JOIN orden_pago op ON b.id_orden = op.id_orden
                INNER JOIN pacientes p ON op.id_paciente = p.id_paciente
                WHERE op.id_paciente = ? 
                AND DATE(b.fecha_emision) BETWEEN ? AND ?
                ORDER BY b.fecha_emision DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iss", $idPaciente, $fechaInicio, $fechaFin);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $boletas = [];
        while ($fila = $resultado->fetch_assoc()) {
            $boletas[] = $fila;
        }
        
        $stmt->close();
        return $boletas;
    }
}
?>