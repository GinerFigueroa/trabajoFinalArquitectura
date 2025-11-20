<?php


include_once('conexion.php'); 

class EmicionDeBoletaDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    
    
    /**
     * Obtiene los datos de una factura de internado específica.
     * @param int $idFactura
     * @return array|null
     */
    public function obtenerBoletaInternadoPorId($idBoleta)
    {
        $sql = "SELECT * FROM facturacion_internado WHERE id_factura = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idFactura);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $factura = $resultado->fetch_assoc();
        
        $stmt->close();
        return $factura;
    }
    

    
  
  /**
 * Obtiene los datos completos de una factura de internado para el PDF.
 * Incluye datos del paciente e internado.
 * @param int $idFactura
 * @return array|null
 */
public function obtenerBoletaCompletaParaPDF($idBoleta)
{
    $sql = "SELECT 
                fi.*, 
                i.fecha_ingreso,
                i.fecha_alta,
                -- Nombre se obtiene de usuarios (u)
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                -- DNI se obtiene de pacientes (p)
                p.dni AS id_paciente_doc 
            FROM facturacion_internado fi
            JOIN internados i ON fi.id_internado = i.id_internado
            -- Unimos a pacientes (p) para obtener el DNI
            JOIN pacientes p ON i.id_paciente = p.id_paciente
            -- Unimos a usuarios (u) para obtener el nombre
            JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE fi.id_factura = ?";
    
    $stmt = $this->connection->prepare($sql);
    $stmt->bind_param("i", $idFactura);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $data = $resultado->fetch_assoc();
    $stmt->close();

    return $data;
}
}

// --------------------------------------------------------------------------------------
/**
 * Clase FacturacionInternadoAuxiliarDAO
 * Se encarga de lookups de datos relacionados o utilidades.
 */
class FacturacionInternadoAuxiliarDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene la lista de Internados (ID y Paciente) para facturar.
     * Asume la tabla 'internados' y que tiene campos de fecha de ingreso/alta para calcular días.
     * @return array
     */
    public function obtenerInternadosParaFacturar()
    {
        $sql = "SELECT 
                    i.id_internado, 
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                    i.fecha_ingreso,
                    i.fecha_alta, /* <<-- CORRECCIÓN APLICADA */
                    DATEDIFF(COALESCE(i.fecha_alta, NOW()), i.fecha_ingreso) AS dias_estadia /* <<-- CORRECCIÓN APLICADA */
                FROM internados i
                JOIN pacientes p ON i.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                -- Podrías filtrar aquí por los que no tienen factura aún
                LEFT JOIN facturacion_internado fi ON i.id_internado = fi.id_internado
                WHERE fi.id_factura IS NULL 
                ORDER BY i.fecha_ingreso DESC
                LIMIT 50";
        
        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene información de un Internado específico.
     * @param int $idInternado
     * @return array
     */
    public function obtenerInfoInternado($idInternado)
    {
        $sql = "SELECT 
                    i.id_internado, 
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                    i.fecha_ingreso,
                    i.fecha_alta, /* <<-- CORRECCIÓN APLICADA */
                    DATEDIFF(COALESCE(i.fecha_alta, NOW()), i.fecha_ingreso) AS dias_estadia /* <<-- CORRECCIÓN APLICADA */
                FROM internados i
                JOIN pacientes p ON i.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE i.id_internado = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idInternado);
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