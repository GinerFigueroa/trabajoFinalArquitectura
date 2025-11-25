<?php
include_once('conexion.php');

class OrdenExamenDAO
{
    private $connection;

    public function __construct() {
        // Asegúrate de que la clase 'Conexion' esté definida y funcionando correctamente
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    // ========================================================
    // MÉTODOS DE VALIDACIÓN REQUERIDOS POR EL COMMAND (SOLUCIÓN)
    // ========================================================

    /**
     * Verifica si una Historia Clínica existe en la base de datos.
     * ESTE MÉTODO FALTABA Y CAUSABA EL ERROR FATAL.
     * * @param int $idHistoriaClinica ID de la Historia Clínica (historia_clinica_id).
     * @return bool True si existe, False si no.
     */
    public function existeHistoriaClinica(int $idHistoriaClinica): bool {
        $sql = "SELECT COUNT(*) AS count 
                FROM historia_clinica 
                WHERE historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta (existeHistoriaClinica): " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("i", $idHistoriaClinica);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
     * Verifica si un usuario es médico. (Ya estaba en su código, se mantiene).
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

    // ========================================================
    // MÉTODOS DE LECTURA DE DATOS
    // ========================================================

    /**
     * Obtiene todas las órdenes de examen.
     */
    public function obtenerTodasOrdenes()
    {
        $sql = "SELECT 
                    oe.id_orden,
                    oe.historia_clinica_id,
                    oe.id_medico,
                    oe.fecha,
                    oe.tipo_examen,
                    oe.indicaciones,
                    oe.estado,
                    oe.resultados,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico
                FROM orden_examen oe
                JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN medicos m ON oe.id_medico = m.id_medico 
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                
                ORDER BY oe.fecha DESC, oe.id_orden DESC";

        $resultado = $this->connection->query($sql);
        $ordenes = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $ordenes[] = $fila;
            }
        }

        return $ordenes;
    }

    /**
    * Obtiene información del médico por ID de usuario.
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

    /**
    * Obtiene el id_medico a partir del id_usuario.
    */
    public function obtenerIdMedicoPorUsuario($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta (obtenerIdMedicoPorUsuario): " . $this->connection->error);
            return null;
        }
        
        $stmt->bind_param("i", $idUsuario);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta (obtenerIdMedicoPorUsuario): " . $stmt->error);
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
        error_log("No se encontró médico para id_usuario: " . $idUsuario);
        return null;
    }

    /**
    * Obtiene o crea el id_medico a partir del id_usuario (método robusto).
    * (Se mantiene para la lógica de registro, aunque el command solo usa obtenerIdMedicoPorUsuario).
    */
    public function obtenerOcrearIdMedicoPorUsuario($idUsuario)
    {
        $idMedico = $this->obtenerIdMedicoPorUsuario($idUsuario);
        
        if ($idMedico) {
            return $idMedico;
        }
        
        if ($this->esUsuarioMedico($idUsuario)) {
            // Crear registro en médicos automáticamente
            $sql = "INSERT INTO medicos (id_usuario, cedula_profesional) VALUES (?, ?)";
            $stmt = $this->connection->prepare($sql);
            $cedula = "MED" . str_pad($idUsuario, 5, '0', STR_PAD_LEFT);
            $stmt->bind_param("is", $idUsuario, $cedula);
            
            if ($stmt->execute()) {
                $idMedico = $this->connection->insert_id;
                $stmt->close();
                return $idMedico;
            }
            $stmt->close();
        }
        
        return null;
    }

    /**
    * Obtiene una orden específica por ID.
    * Se eliminó la función duplicada 'obtdddenerOrdenPorId'.
    */
    public function obtenerOrdenPorId($idOrden)
    {
        $sql = "SELECT 
                    oe.*,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    u_med.id_usuario,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico,
                    hc.id_paciente
                FROM orden_examen oe
                JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN medicos m ON oe.id_medico = m.id_medico 
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario 
                WHERE oe.id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta (obtenerOrdenPorId): " . $this->connection->error);
            return null;
        }
        
        $stmt->bind_param("i", $idOrden);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta (obtenerOrdenPorId): " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $resultado = $stmt->get_result();
        $orden = $resultado->fetch_assoc();
        $stmt->close();

        return $orden;
    }

    /**
    * Obtiene el ID del médico asociado a una orden.
    */
    public function obtenerIdMedicoPorOrden($idOrden)
    {
        $sql = "SELECT id_medico FROM orden_examen WHERE id_orden = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            return $fila['id_medico'];
        }
        
        return null;
    }

    // ========================================================
    // MÉTODOS DE ESCRITURA DE DATOS (CRUD)
    // ========================================================
        
    /**
    * Registra una nueva orden de examen.
    */
    public function registrarOrden($historiaClinicaId, $idUsuarioMedico, $fecha, $tipoExamen, $indicaciones, $estado = 'Pendiente', $resultados = null)
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
            error_log("Error preparando consulta (registrarOrden): " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("iisssss", 
            $historiaClinicaId, 
            $idMedico, // Usamos el id_medico convertido
            $fecha, 
            $tipoExamen, 
            $indicaciones, 
            $estado, 
            $resultados
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando consulta (registrarOrden): " . $stmt->error);
        }
        
        $stmt->close();
        
        return $resultado;
    }

    /**
    * Actualiza una orden existente.
    */
    public function actualizarOrden($idOrden, $historiaClinicaId, $idMedico, $fecha, $tipoExamen, $indicaciones, $estado, $resultados = null)
    {
        $sql = "UPDATE orden_examen SET 
                historia_clinica_id = ?, 
                id_medico = ?, 
                fecha = ?, 
                tipo_examen = ?, 
                indicaciones = ?, 
                estado = ?, 
                resultados = ?
                WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta (actualizarOrden): " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("iisssssi", 
            $historiaClinicaId, 
            $idMedico, 
            $fecha, 
            $tipoExamen, 
            $indicaciones, 
            $estado, 
            $resultados,
            $idOrden
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
    * Actualiza solo el estado y resultados de una orden.
    */
    public function actualizarResultados($idOrden, $estado, $resultados)
    {
        $sql = "UPDATE orden_examen SET 
                estado = ?, 
                resultados = ?
                WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta (actualizarResultados): " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("ssi", $estado, $resultados, $idOrden);
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
    * Elimina una orden.
    */
    public function eliminarOrden($idOrden)
    {
        $sql = "DELETE FROM orden_examen WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    // ========================================================
    // MÉTODOS PARA INTERFACES DE USUARIO (SELECTS/FILTROS)
    // ========================================================

    /**
    * Obtiene historias clínicas para select.
    */
    public function obtenerHistoriasClinicas()
    {
        $sql = "SELECT 
                    hc.historia_clinica_id,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni
                FROM historia_clinica hc
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY u.nombre, u.apellido_paterno";

        $resultado = $this->connection->query($sql);
        $historias = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $historias[] = $fila;
            }
        }

        return $historias;
    }

    /**
    * Obtiene médicos activos para select.
    */
    public function obtenerMedicosActivos()
    {
        $sql = "SELECT 
                    u.id_usuario,
                    CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo,
                    m.cedula_profesional
                FROM usuarios u
                JOIN medicos m ON u.id_usuario = m.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno";

        $resultado = $this->connection->query($sql);
        $medicos = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $medicos[] = $fila;
            }
        }

        return $medicos;
    }

    /**
    * Obtiene órdenes por estado.
    */
    public function obtenerOrdenesPorEstado($estado)
    {
        $sql = "SELECT 
                    oe.*,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente
                FROM orden_examen oe
                JOIN historia_clinica hc ON oe.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                WHERE oe.estado = ?
                ORDER BY oe.fecha DESC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("s", $estado);
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
}
?>