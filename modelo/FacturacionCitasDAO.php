<?php
// Archivo: modelo/FacturacionCitasDAO.php

include_once('conexion.php'); 

class FacturacionCitasDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // --- OPERACIONES DE LISTADO ---

    /**
     * Obtiene todas las facturas de citas, incluyendo la información de la cita y el paciente.
     * @return array
     */
    public function obtenerTodasLasFacturas()
    {
        $sql = "SELECT 
                    fc.id_factura, fc.id_cita, fc.fecha_emision, fc.monto_total, fc.estado,
                    c.fecha_hora AS fecha_cita,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
                FROM facturacion_citas fc
                JOIN citas c ON fc.id_cita = c.id_cita
                JOIN pacientes p ON c.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY fc.fecha_emision DESC";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * Obtiene los datos de una factura específica.
     * @param int $idFactura
     * @return array|null
     */
    public function obtenerFacturaPorId($idFactura)
    {
        $sql = "SELECT * FROM facturacion_citas WHERE id_factura = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idFactura);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $factura = $resultado->fetch_assoc();
        
        $stmt->close();
        return $factura;
    }

    // --------------------------------------------------------------------------------------
    // --- FUNCIÓN ADICIONAL PARA GENERACIÓN DE PDF ---
    // --------------------------------------------------------------------------------------

    /**
     * Obtiene los datos completos para generar la Factura PDF (Factura, Cita, Paciente, RUC/ID).
     * @param int $idFactura
     * @return array|null
     */
  // En FacturacionCitasDAO.php
public function obtenerFacturaCompletaParaPDF($idFactura)
{
    $sql = "SELECT 
                fc.id_factura, 
                fc.fecha_emision, 
                fc.monto_total, 
                fc.estado AS estado_factura,
                fc.id_cita,
                
                -- Campos de relleno para evitar errores en la vista PDF:
                NULL AS fecha_cita,
                'Servicio de Cita Registrado' AS concepto_servicio,
                'Paciente Desconocido' AS nombre_paciente,
                'N/A' AS id_paciente_doc 
            FROM facturacion_citas fc
            WHERE fc.id_factura = ?";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idFactura);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $data = $resultado->fetch_assoc();
    $stmt->close();

    return $data;
}

    // --- OPERACIONES CRUD ---

    /**
     * Registra una nueva factura.
     * @return int|bool ID del nuevo registro o false en caso de error.
     */
    public function registrarFactura($idCita, $fechaEmision, $montoTotal, $estado)
    {
        $sql = "INSERT INTO facturacion_citas (id_cita, fecha_emision, monto_total, estado)
                 VALUES (?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: i s d s
        $stmt->bind_param("isds", $idCita, $fechaEmision, $montoTotal, $estado);
        
        $resultado = $stmt->execute();
        $nuevoId = $resultado ? $this->connection->insert_id : false;
        
        $stmt->close();
        return $nuevoId;
    }
    
    /**
     * Edita una factura existente.
     * @return bool
     */
    public function editarFactura($idFactura, $idCita, $fechaEmision, $montoTotal, $estado)
    {
        $sql = "UPDATE facturacion_citas SET id_cita = ?, fecha_emision = ?, monto_total = ?, estado = ?
                 WHERE id_factura = ?";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: i s d s i
        $stmt->bind_param("isdsi", $idCita, $fechaEmision, $montoTotal, $estado, $idFactura);
        
        $resultado = $stmt->execute();
        
        $stmt->close();
        return $resultado;
    }
    
    /**
     * Elimina una factura.
     * @param int $idFactura
     * @return bool
     */
    public function eliminarFactura($idFactura)
    {
        $sql = "DELETE FROM facturacion_citas WHERE id_factura = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idFactura);
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}

// --------------------------------------------------------------------------------------
/**
 * Clase FacturacionCitasAuxiliarDAO
 * Se encarga de lookups de datos relacionados o utilidades.
 */
class FacturacionCitasAuxiliarDAO
{
    private $connection;
    // ... (El resto de la clase FacturacionCitasAuxiliarDAO es el mismo)
    // ...
    // ...
    
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    /**
     * Obtiene la lista de Citas (ID, Paciente, Fecha) que NO han sido facturadas.
     * Esto ayuda a evitar facturas duplicadas, aunque se puede forzar en la vista.
     * Se usa un LEFT JOIN para que puedas elegir entre todas.
     * @return array
     */
    public function obtenerCitasDisponiblesParaFacturar()
    {
        $sql = "SELECT 
                     c.id_cita, 
                     CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                     c.fecha_hora
                 FROM citas c
                 LEFT JOIN facturacion_citas fc ON c.id_cita = fc.id_cita
                 JOIN pacientes p ON c.id_paciente = p.id_paciente
                 JOIN usuarios u ON p.id_usuario = u.id_usuario
                 WHERE fc.id_factura IS NULL AND c.estado IN ('Completada', 'Confirmada')
                 ORDER BY c.fecha_hora DESC
                 LIMIT 50";
        
        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene una cita específica (ID, Paciente, Fecha)
     * @param int $idCita
     * @return array
     */
    public function obtenerInfoCita($idCita)
    {
        $sql = "SELECT 
                     c.id_cita, 
                     CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                     c.fecha_hora
                 FROM citas c
                 JOIN pacientes p ON c.id_paciente = p.id_paciente
                 JOIN usuarios u ON p.id_usuario = u.id_usuario
                 WHERE c.id_cita = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idCita);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $info = $resultado->fetch_assoc();
        $stmt->close();
        return $info;
    }


    /**
     * Retorna los valores del ENUM para Estado de Factura.
     * @return array
     */
    public static function obtenerEstadosFactura()
    {
        return ['Pendiente', 'Pagado', 'Anulado'];
    }
}
?>