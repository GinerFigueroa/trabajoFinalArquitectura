<?php
// FILE: modelo/OrdenPagoDAO.php
// Contiene todos los Data Access Objects (DAOs) necesarios para la gestión de Órdenes y entidades relacionadas.

// ⚠️ IMPORTANTE: Asegúrate de que la ruta a 'conexion.php' sea correcta desde donde se use este archivo.
include_once('conexion.php'); 

// =================================================================
// 1. CLASE PRINCIPAL: OrdenPagoDAO (Gestión de órdenes de pago)
// Se renombró de 'OrdenPago' a 'OrdenPagoDAO' para claridad.
// =================================================================

class OrdenPagoDAO
{
    private $connection; // Almacenará el objeto mysqli

    public function __construct()
    {
        // Obtener la única instancia de la conexión Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // --- CRUD Y OBTENCIÓN ---

    public function obtenerTodasOrdenes()
    {
        $conn = $this->connection;
        
        $sql = "SELECT 
                    op.*,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_paciente,
                    p.dni AS dni_paciente,
                    IF(op.id_cita IS NOT NULL, 'Cita', IF(op.id_internado IS NOT NULL, 'Internamiento', 'Otro')) AS tipo_servicio
                FROM orden_pago op
                JOIN pacientes p ON op.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY op.fecha_emision DESC";
        
        $resultado = $conn->query($sql);
        $ordenes = [];

        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $ordenes[] = $fila;
            }
        }
        
        return $ordenes;
    }
    
    public function obtenerOrdenPorId($idOrden)
    {
        $conn = $this->connection;
        
        $sql = "SELECT 
                    op.*,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente_completo,
                    p.dni AS dni_paciente
                FROM orden_pago op
                JOIN pacientes p ON op.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE op.id_orden = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $orden = $resultado->fetch_assoc();
        
        $stmt->close();
        return $orden;
    }

    public function registrarOrden($idPaciente, $idCita, $idInternado, $concepto, $monto)
    {
        $conn = $this->connection;
        $resultado = false;
        
        // Uso de NULL para la base de datos si los IDs no están presentes
        $idCita = $idCita ?: NULL;
        $idInternado = $idInternado ?: NULL;
        
        // Definición de tipos para bind_param (ajustada para manejar posibles NULLs o diferentes rutas de SQL)
        if ($idCita === NULL && $idInternado === NULL) {
            // Caso 1: Solo por concepto
            $sql = "INSERT INTO orden_pago (id_paciente, concepto, monto_estimado, estado) VALUES (?, ?, ?, 'Pendiente')";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isd", $idPaciente, $concepto, $monto);
        } else {
            // Caso 2: Con cita y/o internado
            $sql = "INSERT INTO orden_pago (id_paciente, id_cita, id_internado, concepto, monto_estimado, estado) VALUES (?, ?, ?, ?, ?, 'Pendiente')";
            $stmt = $conn->prepare($sql);
            // 'iiisd' asumiendo que mysqli maneja los NULLs para los IDs (ii) correctamente
            $stmt->bind_param("iiisd", $idPaciente, $idCita, $idInternado, $concepto, $monto);
        }

        if ($stmt->execute()) {
            $resultado = true;
        }

        $stmt->close();
        return $resultado;
    }

    public function editarOrden($idOrden, $concepto, $monto)
    {
        $conn = $this->connection;
        $resultado = false;

        // Solo se permite editar si el estado es 'Pendiente'
        $sql = "UPDATE orden_pago SET concepto = ?, monto_estimado = ? WHERE id_orden = ? AND estado = 'Pendiente'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sdi", $concepto, $monto, $idOrden);
        
        if ($stmt->execute()) {
            $resultado = $stmt->affected_rows > 0;
        }

        $stmt->close();
        return $resultado;
    }

    public function eliminarOrden($idOrden)
    {
        $conn = $this->connection;
        $resultado = false;

        // Solo se permite eliminar si el estado es 'Pendiente'
        $sql = "DELETE FROM orden_pago WHERE id_orden = ? AND estado = 'Pendiente'";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        
        if ($stmt->execute()) {
            $resultado = $stmt->affected_rows > 0;
        }

        $stmt->close();
        return $resultado;
    }
}

// =================================================================
// 2. CLASE PARA PACIENTE (PacienteDAO)
// Se renombró de 'Paciente' a 'PacienteDAO'.
// =================================================================

class PacienteDAO 
{
    private $connection;
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    public function obtenerPacientesPorRol($idRol)
    {
        $conn = $this->connection;
        
        $sql = "SELECT p.id_paciente, u.nombre, u.apellido_paterno, u.apellido_materno, p.dni 
                FROM pacientes p 
                JOIN usuarios u ON p.id_usuario = u.id_usuario 
                WHERE u.id_rol = ? 
                ORDER BY u.apellido_paterno";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idRol);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $pacientes = [];
        
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $pacientes[] = $fila;
            }
        }
        
        $stmt->close();
        return $pacientes;
    }
}

// =================================================================
// 3. CLASE PARA CONSULTAS DE CITAS (CitasDAO)
// Se renombró de 'EntidadCitas' a 'CitasDAO'.
// =================================================================

class CitasDAO 
{
    private $connection;
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    public function obtenerCitasPendientesPorPaciente($idPaciente)
    {
        $conn = $this->connection;
        
        $sql = "SELECT 
                    c.id_cita, 
                    c.fecha_hora, 
                    t.nombre AS nombre_tratamiento,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico
                FROM citas c
                LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                LEFT JOIN medicos m ON c.id_medico = m.id_medico
                LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
                WHERE 
                    c.id_paciente = ? AND 
                    c.estado = 'Completada' 
                ORDER BY c.fecha_hora DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $citas = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $citas[] = $fila;
            }
        }
        
        $stmt->close();
        return $citas;
    }
}

// =================================================================
// 4. CLASE PARA CONSULTAS DE INTERNADOS Y EXÁMENES (InternadosDAO)
// Se renombró de 'EntidadInternados' a 'InternadosDAO'.
// Esta clase también maneja la lógica de 'orden_examen'.
// =================================================================

class InternadosDAO
{
    private $connection;
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    public function obtenerInternamientosPorPaciente($idPaciente)
    {
        $conn = $this->connection;
        
        $sql = "SELECT 
                    i.id_internado, i.fecha_ingreso, i.estado, h.numero_puerta AS habitacion_numero
                FROM internados i
                JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
                WHERE i.id_paciente = ? AND (i.estado = 'Activo' OR i.estado = 'Alta' OR i.estado = 'Derivado')
                ORDER BY i.fecha_ingreso DESC";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $internados = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $internados[] = $fila;
            }
        }
        
        $stmt->close();
        return $internados;
    }

    /**
    * Registra una nueva orden de examen
    */
    public function registrarOrdenExamen($historiaClinicaId, $idUsuarioMedico, $fecha, $tipoExamen, $indicaciones, $estado = 'Pendiente', $resultados = null)
    {
        // Primero obtener el id_medico a partir del id_usuario
        $idMedico = $this->obtenerIdMedicoPorUsuario($idUsuarioMedico);
        
        if (!$idMedico) {
            error_log("Error: No se pudo encontrar id_medico para id_usuario: " . $idUsuarioMedico);
            return false;
        }

        $sql = "INSERT INTO orden_examen 
                (historia_clinica_id, id_medico, fecha, tipo_examen, indicaciones, estado, resultados) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("iisssss", 
            $historiaClinicaId, 
            $idMedico, // Usamos el id_medico correcto
            $fecha, 
            $tipoExamen, 
            $indicaciones, 
            $estado, 
            $resultados
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando consulta: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $resultado;
    }

    /**
    * Obtiene el id_medico a partir del id_usuario
    */
    public function obtenerIdMedicoPorUsuario($idUsuario)
    {
        $sql = "SELECT m.id_medico 
                FROM medicos m 
                WHERE m.id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return null;
        }
        
        $stmt->bind_param("i", $idUsuario);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $idMedico = $fila['id_medico'];
            $stmt->close();
            return $idMedico;
        }
        
        $stmt->close();
        return null;
    }

    /**
    * Verifica si un usuario es médico
    */
    public function esUsuarioMedico($idUsuario)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM usuarios u 
                WHERE u.id_usuario = ? AND u.id_rol = 2 AND u.activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
    * Obtiene información completa del médico por ID de usuario
    */
    public function obtenerMedicoPorIdUsuario($idUsuario)
    {
        $sql = "SELECT 
                    m.id_medico,
                    u.id_usuario,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
                    m.cedula_profesional,
                    e.nombre as especialidad
                FROM usuarios u
                JOIN medicos m ON u.id_usuario = m.id_usuario
                LEFT JOIN especialidades_medicas e ON m.id_especialidad = e.id_especialidad
                WHERE u.id_usuario = ? AND u.id_rol = 2 AND u.activo = 1";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $medico = $resultado->fetch_assoc();
        $stmt->close();

        return $medico;
    }
}
?>