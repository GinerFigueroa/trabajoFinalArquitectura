<?php
// Archivo: ../modelo/DashboardBoletaDAO.php

include_once('conexion.php'); 

class DashboardBoletaDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene estadísticas generales de boletas
     */
    public function obtenerEstadisticasGenerales($fechaInicio = null, $fechaFin = null)
    {
        $whereClause = "";
        $params = [];
        $types = "";

        if ($fechaInicio && $fechaFin) {
            $whereClause = " WHERE DATE(b.fecha_emision) BETWEEN ? AND ?";
            $params = [$fechaInicio, $fechaFin];
            $types = "ss";
        }

        $sql = "SELECT 
                    COUNT(*) as total_boletas,
                    SUM(b.monto_total) as ingreso_total,
                    AVG(b.monto_total) as promedio_boleta,
                    MIN(b.monto_total) as boleta_minima,
                    MAX(b.monto_total) as boleta_maxima,
                    COUNT(DISTINCT op.id_paciente) as pacientes_unicos
                FROM boletas b
                JOIN orden_pago op ON b.id_orden = op.id_orden
                $whereClause";

        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estadisticas = $resultado->fetch_assoc();
        $stmt->close();

        return $estadisticas;
    }

    /**
     * Obtiene ingresos por mes para gráfico de líneas
     */
    public function obtenerIngresosPorMes($anio = null)
    {
        $anio = $anio ?: date('Y');
        
        $sql = "SELECT 
                    MONTH(b.fecha_emision) as mes,
                    YEAR(b.fecha_emision) as anio,
                    SUM(b.monto_total) as total_ingresos,
                    COUNT(*) as cantidad_boletas
                FROM boletas b
                WHERE YEAR(b.fecha_emision) = ?
                GROUP BY YEAR(b.fecha_emision), MONTH(b.fecha_emision)
                ORDER BY anio, mes";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $anio);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $datos = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        // Rellenar meses faltantes con cero
        $mesesCompletos = [];
        for ($mes = 1; $mes <= 12; $mes++) {
            $encontrado = false;
            foreach ($datos as $dato) {
                if ($dato['mes'] == $mes) {
                    $mesesCompletos[] = $dato;
                    $encontrado = true;
                    break;
                }
            }
            if (!$encontrado) {
                $mesesCompletos[] = [
                    'mes' => $mes,
                    'anio' => $anio,
                    'total_ingresos' => 0,
                    'cantidad_boletas' => 0
                ];
            }
        }

        return $mesesCompletos;
    }

    /**
     * Obtiene distribución por tipo de boleta para gráfico de torta
     */
    public function obtenerDistribucionPorTipo($fechaInicio = null, $fechaFin = null)
    {
        $whereClause = "";
        $params = [];
        $types = "";

        if ($fechaInicio && $fechaFin) {
            $whereClause = " WHERE DATE(b.fecha_emision) BETWEEN ? AND ?";
            $params = [$fechaInicio, $fechaFin];
            $types = "ss";
        }

        $sql = "SELECT 
                    b.tipo,
                    COUNT(*) as cantidad,
                    SUM(b.monto_total) as monto_total,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM boletas $whereClause)), 2) as porcentaje
                FROM boletas b
                $whereClause
                GROUP BY b.tipo
                ORDER BY monto_total DESC";

        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        $distribucion = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $distribucion;
    }

    /**
     * Obtiene distribución por método de pago
     */
    public function obtenerDistribucionPorMetodoPago($fechaInicio = null, $fechaFin = null)
    {
        $whereClause = "";
        $params = [];
        $types = "";

        if ($fechaInicio && $fechaFin) {
            $whereClause = " WHERE DATE(b.fecha_emision) BETWEEN ? AND ?";
            $params = [$fechaInicio, $fechaFin];
            $types = "ss";
        }

        $sql = "SELECT 
                    b.metodo_pago,
                    COUNT(*) as cantidad,
                    SUM(b.monto_total) as monto_total,
                    ROUND((COUNT(*) * 100.0 / (SELECT COUNT(*) FROM boletas $whereClause)), 2) as porcentaje
                FROM boletas b
                $whereClause
                GROUP BY b.metodo_pago
                ORDER BY monto_total DESC";

        $stmt = $this->connection->prepare($sql);
        
        if (!empty($params)) {
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $resultado = $stmt->get_result();
        $distribucion = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $distribucion;
    }

    /**
     * Obtiene boletas más recientes para la tabla
     */
    public function obtenerBoletasRecientes($limite = 10)
    {
        $sql = "SELECT 
                    b.id_boleta,
                    b.numero_boleta,
                    b.tipo,
                    b.monto_total,
                    b.metodo_pago,
                    b.fecha_emision,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) as paciente,
                    op.concepto
                FROM boletas b
                JOIN orden_pago op ON b.id_orden = op.id_orden
                JOIN pacientes p ON op.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY b.fecha_emision DESC
                LIMIT ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $boletas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        return $boletas;
    }

    /**
     * Obtiene años disponibles para filtros
     */
    public function obtenerAniosDisponibles()
    {
        $sql = "SELECT DISTINCT YEAR(fecha_emision) as anio 
                FROM boletas 
                ORDER BY anio DESC";

        $resultado = $this->connection->query($sql);
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    /**
     * Obtiene tendencia de ingresos (comparativa mes actual vs anterior)
     */
    public function obtenerTendenciaIngresos()
    {
        $sql = "SELECT 
                    YEAR(fecha_emision) as anio,
                    MONTH(fecha_emision) as mes,
                    SUM(monto_total) as ingresos
                FROM boletas 
                WHERE fecha_emision >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)
                GROUP BY YEAR(fecha_emision), MONTH(fecha_emision)
                ORDER BY anio DESC, mes DESC
                LIMIT 2";

        $resultado = $this->connection->query($sql);
        $datos = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        
        $tendencia = [
            'mes_actual' => $datos[0]['ingresos'] ?? 0,
            'mes_anterior' => $datos[1]['ingresos'] ?? 0
        ];
        
        if ($tendencia['mes_anterior'] > 0) {
            $tendencia['variacion'] = (($tendencia['mes_actual'] - $tendencia['mes_anterior']) / $tendencia['mes_anterior']) * 100;
        } else {
            $tendencia['variacion'] = $tendencia['mes_actual'] > 0 ? 100 : 0;
        }
        
        return $tendencia;
    }
}
?>