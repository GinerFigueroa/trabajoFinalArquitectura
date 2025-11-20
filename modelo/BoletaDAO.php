<?php
// Archivo: modelo/BoletaDAO.php

include_once('conexion.php'); 

class BoletaDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // --- OPERACIONES DE LISTADO ---

    /**
     * Obtiene todas las boletas/facturas, incluyendo la información de la orden y el paciente.
     * @return array
     */
    public function obtenerTodasLasBoletas()
    {
        $sql = "SELECT 
                    b.id_boleta, b.numero_boleta, b.tipo, b.monto_total, b.metodo_pago, b.fecha_emision,
                    op.id_orden, op.concepto,
                    u.nombre AS nombre_paciente, u.apellido_paterno AS apellido_paciente
                FROM boletas b
                JOIN orden_pago op ON b.id_orden = op.id_orden
                JOIN pacientes p ON op.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY b.fecha_emision DESC";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene los datos completos de una boleta/factura específica.
     * @param int $idBoleta
     * @return array|null
     */
    public function obtenerBoletaPorId($idBoleta)
    {
        $sql = "SELECT * FROM boletas WHERE id_boleta = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idBoleta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $boleta = $resultado->fetch_assoc();
        
        $stmt->close();
        return $boleta;
    }

    // --- OPERACIONES CRUD ---

    // La función registrarBoleta ya estaba definida en la respuesta anterior.
    // Se asume que se mantiene junto con su lógica transaccional.

    /**
     * Edita una boleta/factura existente.
     * @return bool
     */
    public function editarBoleta($idBoleta, $numeroBoleta, $tipo, $montoTotal, $metodoPago)
    {
        // Nota: id_orden no se edita ya que está vinculado al historial.
        $sql = "UPDATE boletas SET numero_boleta = ?, tipo = ?, monto_total = ?, metodo_pago = ?
                WHERE id_boleta = ?";
        
        $stmt = $this->connection->prepare($sql);
        // Tipos: s s d s i
        $stmt->bind_param("ssdsi", $numeroBoleta, $tipo, $montoTotal, $metodoPago, $idBoleta);
        
        $resultado = $stmt->execute();
        
        $stmt->close();
        return $resultado;
    }
    
    /**
     * Elimina una boleta/factura.
     * También debe actualizar el estado de la orden de pago asociada a 'Pendiente'.
     * @param int $idBoleta
     * @return bool
     */
    public function eliminarBoleta($idBoleta)
    {
        $this->connection->begin_transaction();
        $success = false;
        
        try {
            // 1. Obtener id_orden asociado antes de eliminar la boleta
            $sqlGetOrden = "SELECT id_orden FROM boletas WHERE id_boleta = ?";
            $stmtGetOrden = $this->connection->prepare($sqlGetOrden);
            $stmtGetOrden->bind_param("i", $idBoleta);
            $stmtGetOrden->execute();
            $resultado = $stmtGetOrden->get_result();
            $ordenData = $resultado->fetch_assoc();
            $idOrden = $ordenData['id_orden'] ?? null;
            $stmtGetOrden->close();

            if (!$idOrden) {
                throw new Exception("Orden de pago no encontrada para la boleta.");
            }

            // 2. Eliminar la Boleta
            $sqlBoleta = "DELETE FROM boletas WHERE id_boleta = ?";
            $stmtBoleta = $this->connection->prepare($sqlBoleta);
            $stmtBoleta->bind_param("i", $idBoleta);
            $stmtBoleta->execute();
            $stmtBoleta->close();

            // 3. Actualizar el estado de la Orden de Pago a 'Pendiente'
            $sqlOrden = "UPDATE orden_pago SET estado = 'Pendiente' WHERE id_orden = ?";
            $stmtOrden = $this->connection->prepare($sqlOrden);
            $stmtOrden->bind_param("i", $idOrden);
            $stmtOrden->execute();
            $stmtOrden->close();

            $this->connection->commit();
            $success = true;
            
        } catch (Exception $e) {
            $this->connection->rollback();
            error_log("Error al eliminar boleta y actualizar orden: " . $e->getMessage());
            $success = false;
        }

        return $success;
    }
    // En modelo/BoletaDAO.php, dentro de la clase BoletaAuxiliarDAO
public function obtenerOrdenesPendientes()
{
    $sql = "SELECT 
                op.id_orden, 
                op.concepto, 
                op.monto_estimado,
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
            FROM orden_pago op
            JOIN pacientes p ON op.id_paciente = p.id_paciente
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE op.estado = 'Pendiente'
            ORDER BY op.fecha_emision ASC";
    
    $resultado = $this->connection->query($sql);
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}

public function obtenerBoletaCompletaParaPDF($idBoleta)
    {
        // Consulta base que une boleta, orden y paciente
        $sql = "SELECT 
            b.id_boleta, b.numero_boleta, b.tipo, b.monto_total, b.metodo_pago, b.fecha_emision,
            op.id_orden, op.concepto, op.monto_estimado, op.id_cita, op.id_internado,
            u.nombre AS nombre_paciente, u.apellido_paterno AS apellido_paciente,
            -- u.identificacion AS id_paciente_doc, -- LÍNEA COMENTADA O ELIMINADA
            op.fecha_emision AS fecha_orden
        FROM boletas b
        JOIN orden_pago op ON b.id_orden = op.id_orden
        JOIN pacientes p ON op.id_paciente = p.id_paciente
        JOIN usuarios u ON p.id_usuario = u.id_usuario
        WHERE b.id_boleta = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idBoleta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $data = $resultado->fetch_assoc();
        $stmt->close();

        if ($data) {
            // Si la orden viene de una Cita
            if ($data['id_cita']) {
                $sqlCita = "SELECT fecha_hora FROM citas WHERE id_cita = ?";
                $stmtCita = $this->connection->prepare($sqlCita);
                $stmtCita->bind_param("i", $data['id_cita']);
                $stmtCita->execute();
                $data['detalle_origen'] = $stmtCita->get_result()->fetch_assoc();
                $data['origen_tipo'] = 'Cita Médica';
                $stmtCita->close();
            } 
            // Si la orden viene de un Internado
            else if ($data['id_internado']) {
                $sqlInternado = "SELECT fecha_ingreso, fecha_alta FROM internados WHERE id_internado = ?";
                $stmtInternado = $this->connection->prepare($sqlInternado);
                $stmtInternado->bind_param("i", $data['id_internado']);
                $stmtInternado->execute();
                $data['detalle_origen'] = $stmtInternado->get_result()->fetch_assoc();
                $data['origen_tipo'] = 'Internado';
                $stmtInternado->close();
            } else {
                $data['origen_tipo'] = 'Otro Concepto';
                $data['detalle_origen'] = null;
            }
        }

        return $data;
    }
    /**
 * Registra una boleta/factura y actualiza el estado de la orden de pago asociada.
 * Utiliza una transacción para garantizar atomicidad.
 * @return int|false El ID de la nueva boleta o false en caso de error.
 */
public function registrarBoleta($idOrden, $numeroBoleta, $tipo, $montoTotal, $metodoPago)
{
    $this->connection->begin_transaction();
    $nuevoIdBoleta = false;
    
    try {
        // 1. Verificar si la orden ya está pagada (medida de seguridad adicional)
        $sqlCheck = "SELECT estado FROM orden_pago WHERE id_orden = ?";
        $stmtCheck = $this->connection->prepare($sqlCheck);
        $stmtCheck->bind_param("i", $idOrden);
        $stmtCheck->execute();
        $resultadoCheck = $stmtCheck->get_result();
        $orden = $resultadoCheck->fetch_assoc();
        $stmtCheck->close();

        if ($orden['estado'] !== 'Pendiente') {
            throw new Exception("La orden de pago ya fue procesada.");
        }

        // 2. Insertar la Boleta
        $sqlBoleta = "INSERT INTO boletas (id_orden, numero_boleta, tipo, monto_total, metodo_pago, fecha_emision) 
                      VALUES (?, ?, ?, ?, ?, NOW())";
        $stmtBoleta = $this->connection->prepare($sqlBoleta);
        // Tipos: i s s d s 
        $stmtBoleta->bind_param("issds", $idOrden, $numeroBoleta, $tipo, $montoTotal, $metodoPago);
        
        if (!$stmtBoleta->execute()) {
            throw new Exception("Error al insertar la boleta: " . $stmtBoleta->error);
        }
        $nuevoIdBoleta = $this->connection->insert_id;
        $stmtBoleta->close();

        // 3. Actualizar el estado de la Orden de Pago a 'Facturada'
        $sqlOrden = "UPDATE orden_pago SET estado = 'Facturada' WHERE id_orden = ?";
        $stmtOrden = $this->connection->prepare($sqlOrden);
        $stmtOrden->bind_param("i", $idOrden);
        
        if (!$stmtOrden->execute()) {
            throw new Exception("Error al actualizar la orden de pago: " . $stmtOrden->error);
        }
        $stmtOrden->close();

        $this->connection->commit();
        
    } catch (Exception $e) {
        $this->connection->rollback();
        error_log("Error transaccional al registrar boleta: " . $e->getMessage());
        return false;
    }

    return $nuevoIdBoleta;
}
}

class BoletaAuxiliarDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene la información de una Orden de Pago para el formulario de emisión.
     * @param int $idOrden
     * @return array|null
     */
    public function obtenerOrdenParaEmitir($idOrden)
    {
        $sql = "SELECT 
                    op.id_orden, op.concepto, op.monto_estimado, op.estado,
                    u.nombre AS nombre_paciente, u.apellido_paterno AS apellido_paciente
                FROM orden_pago op
                JOIN pacientes p ON op.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE op.id_orden = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $orden = $resultado->fetch_assoc();
        
        $stmt->close();
        return $orden;
    }

    /**
     * Retorna los valores del ENUM para Tipo de Boleta.
     * @return array
     */
    public static function obtenerTiposBoleta()
    {
        return ['Boleta', 'Factura'];
    }

    /**
     * Retorna los valores del ENUM para Método de Pago.
     * @return array
     */
    public static function obtenerMetodosPago()
    {
        return ['Efectivo', 'Tarjeta', 'Transferencia'];
    }
    public function obtenerOrdenesPendientes()
{
    $sql = "SELECT 
                op.id_orden, 
                op.concepto, 
                op.monto_estimado,
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente
            FROM orden_pago op
            JOIN pacientes p ON op.id_paciente = p.id_paciente
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE op.estado = 'Pendiente'
            ORDER BY op.fecha_emision ASC";
    
    $resultado = $this->connection->query($sql);
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}
}
?>