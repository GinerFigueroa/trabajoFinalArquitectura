<?php
// Archivo: modelo/SeguimientoDAO.php

// Asume que la conexión a la base de datos ya está incluida o la obtienes de una clase base.
// Ejemplo: include_once('Conexion.php'); 

class SeguimientoDAO
{
    private $connection;

    // Métodos estáticos para ENUMs (medios de pago)
    public static function obtenerMediosPago()
    {
        // Esto debería coincidir con el ENUM de tu tabla 'seguimiento_tratamiento'
        return ['Efectivo', 'Tarjeta', 'Transferencia', 'Cheque'];
    }
    
    // Simulación de conexión a BD (Reemplazar con tu lógica real)
    public function __construct()
    {
        // $this->connection = (new Conexion())->getConnection();
        // Por ahora, solo simulación para evitar errores de conexión
        $this->connection = $this->simularConexion(); 
    }

    private function simularConexion()
    {
        // En un entorno real, esto devolvería el objeto mysqli o PDO
        return true; 
    }
    
    // ====================================================================
    // MÉTODOS AUXILIARES PARA FORMULARIOS (Doctores, Presupuestos)
    // ====================================================================

    /**
     * Obtiene una lista simplificada de todos los doctores.
     */
    public function obtenerDoctores()
    {
        // SELECT u.id_usuario, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
        // FROM usuarios u JOIN personal p ON u.id_usuario = p.id_usuario WHERE p.rol = 'Doctor'
        // Simulación:
        return [
            ['id_doctor' => 101, 'nombre_completo' => 'Dr. Juan Pérez'],
            ['id_doctor' => 102, 'nombre_completo' => 'Dra. María Gómez'],
        ];
    }

    /**
     * Obtiene presupuestos con un saldo pendiente para que puedan ser seleccionados en el registro.
     * (El saldo se calcula con una vista o lógica de la BD)
     */
    public function obtenerPresupuestosPendientes()
    {
        // SELECT p.presupuesto_id, u.nombre AS nombre_paciente, (p.costo_total - COALESCE(SUM(st.a_cuenta), 0)) AS saldo_pendiente
        // FROM presupuesto p JOIN planes pl ON p.plan_id = pl.plan_id ... (unir hasta paciente)
        // LEFT JOIN seguimiento_tratamiento st ON p.presupuesto_id = st.presupuesto_id
        // GROUP BY p.presupuesto_id HAVING saldo_pendiente > 0
        // Simulación:
        return [
            ['presupuesto_id' => 1, 'nombre_paciente' => 'Max', 'saldo_pendiente' => 500.00],
            ['presupuesto_id' => 2, 'nombre_paciente' => 'Luna', 'saldo_pendiente' => 120.50],
        ];
    }
    
    /**
     * Obtiene el presupuesto y el saldo actual antes de registrar un nuevo pago.
     */
    public function obtenerPresupuestoConSaldo($presupuestoId)
    {
        // Lógica real: Obtener el costo total del presupuesto y la suma de todos los 'a_cuenta'
        // SELECT p.costo_total, (p.costo_total - COALESCE(SUM(st.a_cuenta), 0)) AS saldo_pendiente
        // FROM presupuesto p LEFT JOIN seguimiento_tratamiento st ON p.presupuesto_id = st.presupuesto_id
        // WHERE p.presupuesto_id = ? GROUP BY p.presupuesto_id
        
        // Simulación:
        if ($presupuestoId == 1) return ['costo_total' => 1000.00, 'saldo_pendiente' => 500.00];
        if ($presupuestoId == 2) return ['costo_total' => 300.00, 'saldo_pendiente' => 120.50];
        return null;
    }


    // ====================================================================
    // MÉTODOS DE CONSULTA Y LISTADO (Raíz del módulo)
    // ====================================================================

    /**
     * Lista todos los registros de seguimiento con información del presupuesto y el doctor.
     */
    public function listarTodosLosSeguimientos()
    {
        // SELECT st.*, p.costo_total, CONCAT(d.nombre, ' ', d.apellido) AS nombre_doctor
        // FROM seguimiento_tratamiento st 
        // JOIN presupuesto p ON st.presupuesto_id = p.presupuesto_id
        // JOIN usuarios d ON st.doctor_id = d.id_usuario ...
        
        // Simulación:
        return [
            [
                'seguimiento_id' => 10,
                'presupuesto_id' => 1,
                'fecha_procedimiento' => '2025-10-01',
                'doctor_id' => 101,
                'nombre_doctor' => 'Dr. Juan Pérez',
                'a_cuenta' => 500.00,
                'saldo' => 500.00,
                'medio_pago' => 'Tarjeta'
            ],
            [
                'seguimiento_id' => 11,
                'presupuesto_id' => 2,
                'fecha_procedimiento' => '2025-10-05',
                'doctor_id' => 102,
                'nombre_doctor' => 'Dra. María Gómez',
                'a_cuenta' => 179.50,
                'saldo' => 120.50,
                'medio_pago' => 'Efectivo'
            ],
        ];
    }

    /**
     * Obtiene un registro de seguimiento específico para su edición.
     */
    public function obtenerSeguimientoPorId($seguimientoId)
    {
        // SELECT st.*, p.costo_total, (p.costo_total - st.a_cuenta - st.saldo_anterior) AS saldo_calculado...
        // WHERE seguimiento_id = ?
        
        // Simulación:
        if ($seguimientoId == 10) {
            return [
                'seguimiento_id' => 10,
                'presupuesto_id' => 1,
                'fecha_procedimiento' => '2025-10-01',
                'doctor_id' => 101,
                'a_cuenta' => 500.00,
                'saldo' => 500.00, // Saldo después de esta transacción
                'medio_pago' => 'Tarjeta'
            ];
        }
        return null;
    }


    // ====================================================================
    // MÉTODOS DE ACCIÓN (Registro y Edición)
    // ====================================================================
    
    /**
     * Registra un nuevo seguimiento de tratamiento/pago.
     * @return bool|int Retorna el ID del nuevo registro o false.
     */
    public function registrarNuevoSeguimiento($presupuestoId, $fechaProcedimiento, $doctorId, $aCuenta, $nuevoSaldo, $medioPago)
    {
        // $stmt = $this->connection->prepare("INSERT INTO seguimiento_tratamiento (...) VALUES (?, ?, ?, ?, ?, ?)");
        // $stmt->bind_param("iisdds", $presupuestoId, $fechaProcedimiento, $doctorId, $aCuenta, $nuevoSaldo, $medioPago);
        // $resultado = $stmt->execute();
        
        // Simulación:
        echo "";
        return (mt_rand(0, 1) == 1) ? mt_rand(12, 99) : true; // Retorna true/ID si tiene éxito
    }

    /**
     * Actualiza un registro de seguimiento existente.
     * @return bool
     */
    public function actualizarSeguimiento($seguimientoId, $fechaProcedimiento, $doctorId, $aCuenta, $saldo, $medioPago)
    {
        // $stmt = $this->connection->prepare("UPDATE seguimiento_tratamiento SET fecha_procedimiento=?, doctor_id=?, a_cuenta=?, saldo=?, medio_pago=? WHERE seguimiento_id=?");
        // $stmt->bind_param("siddds", $fechaProcedimiento, $doctorId, $aCuenta, $saldo, $medioPago, $seguimientoId);
        // $resultado = $stmt->execute();
        
        // Simulación:
        echo "";
        return true; // Retorna true si tiene éxito
    }
}