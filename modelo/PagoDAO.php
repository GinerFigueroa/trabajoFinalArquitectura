<?php
// Archivo: modelo/PagoDAO.php

include_once('conexion.php'); 

class PagoDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // --- OPERACIONES DE LISTADO ---

    /**
     * Obtiene todos los pagos realizados, incluyendo la información de la cita y el paciente.
     * @return array
     */
    public function obtenerTodosLosPagos()
    {
        $sql = "SELECT 
                    p.id_pago, p.monto, p.metodo_pago, p.estado, p.fecha_pago, p.referencia, p.notas,
                    c.id_cita,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
                FROM pagos p
                LEFT JOIN citas c ON p.id_cita = c.id_cita
                LEFT JOIN pacientes pa ON c.id_paciente = pa.id_paciente
                LEFT JOIN usuarios u ON pa.id_usuario = u.id_usuario
                ORDER BY p.fecha_pago DESC";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtiene los datos de un pago específico.
     * @param int $idPago
     * @return array|null
     */
    public function obtenerPagoPorId($idPago)
    {
        $sql = "SELECT * FROM pagos WHERE id_pago = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPago);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $pago = $resultado->fetch_assoc();
        
        $stmt->close();
        return $pago;
    }

    // --- OPERACIONES CRUD ---

    /**
     * Registra un nuevo pago.
     * @return int|bool ID del nuevo registro o false en caso de error.
     */
    public function registrarPago($idCita, $monto, $metodoPago, $estado, $referencia, $fechaPago, $notas)
    {
       $sql = "INSERT INTO pagos (id_cita, monto, metodo_pago, estado, referencia, fecha_pago, notas)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: i d s s s s s
        $stmt->bind_param("idsssss", $idCita, $monto, $metodoPago, $estado, $referencia, $fechaPago, $notas);
        
        $resultado = $stmt->execute();
        $nuevoId = $resultado ? $this->connection->insert_id : false;
        
        $stmt->close();
        return $nuevoId;
    }
    
    /**
     * Edita un pago existente.
     * @return bool
     */
    public function editarPago($idPago, $idCita, $monto, $metodoPago, $estado, $referencia, $fechaPago, $notas)
    {
        $sql = "UPDATE pagos SET id_cita = ?, monto = ?, metodo_pago = ?, estado = ?, referencia = ?, fecha_pago = ?, notas = ?
                WHERE id_pago = ?";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: i d s s s s s i
        $stmt->bind_param("idsssssi", $idCita, $monto, $metodoPago, $estado, $referencia, $fechaPago, $notas, $idPago);
        
        $resultado = $stmt->execute();
        
        $stmt->close();
        return $resultado;
    }
    
    /**
     * Elimina un pago.
     * @param int $idPago
     * @return bool
     */
    public function eliminarPago($idPago)
    {
        $sql = "DELETE FROM pagos WHERE id_pago = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPago);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}

// --------------------------------------------------------------------------------------
/**
 * Clase PagoAuxiliarDAO
 * Se encarga de lookups de datos relacionados o utilidades.
 */
class PagoAuxiliarDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene la lista de Citas (ID y Paciente) para un select box.
     * Esto ayuda a asociar un pago a una cita.
     * @return array
     */
    public function obtenerListaCitas()
    {
        $sql = "SELECT 
                    c.id_cita, 
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                    c.fecha_hora
                FROM citas c
                JOIN pacientes pa ON c.id_paciente = pa.id_paciente
                JOIN usuarios u ON pa.id_usuario = u.id_usuario
                ORDER BY c.fecha_hora DESC
                LIMIT 50"; // Limitar a las últimas 50 citas para eficiencia
        
        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Retorna los valores del ENUM para Método de Pago.
     * @return array
     */
    public static function obtenerMetodosPago()
    {
        return ['Efectivo', 'Tarjeta crédito', 'Tarjeta débito', 'Transferencia'];
    }

    /**
     * Retorna los valores del ENUM para Estado de Pago.
     * @return array
     */
    public static function obtenerEstadosPago()
    {
        return ['Pendiente', 'Completado', 'Reembolsado', 'Cancelado'];
    }

    //---MODELO-PACIENTE

     
    /**
     * Obtiene el listado de pagos/facturas para un paciente.
     * Se asume una tabla 'pagos' enlazada a 'citas', que a su vez está enlazada al 'tratamiento' y al 'paciente'.
     * @param int $idPaciente El ID del usuario que es paciente.
     * @return array
     */
    public function obtenerPagosPorPaciente($idPaciente)
    {
        $sql = "SELECT p.id_pago, p.fecha_pago, p.monto, p.estado, 
                       c.id_cita, t.nombre AS nombre_tratamiento
                FROM pagos p
                -- Unir a citas para filtrar por el paciente
                INNER JOIN citas c ON p.id_cita = c.id_cita 
                INNER JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                WHERE c.id_paciente = ?
                ORDER BY p.fecha_pago DESC";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error al preparar la consulta de Pagos: " . $this->connection->error);
            return [];
        }
        
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $pagos = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $pagos;
    }
    
    /**
     * Obtiene el detalle de un pago específico para la descarga de la factura.
     * @param int $idPago ID del pago.
     * @return array|null
     */
    public function obtenerDetallePago($idPago)
    {
        // Esta consulta sería más compleja en la vida real, incluyendo datos de la clínica/factura.
        $sql = "SELECT p.id_pago, p.fecha_pago, p.monto, t.nombre AS concepto, u.nombre AS paciente_nombre
                FROM pagos p
                INNER JOIN citas c ON p.id_cita = c.id_cita
                INNER JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                INNER JOIN usuarios u ON c.id_paciente = u.id_usuario
                WHERE p.id_pago = ?";
                
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPago);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $detalle = $resultado->fetch_assoc();
        $stmt->close();
        
        return $detalle;
    }

}
?>
