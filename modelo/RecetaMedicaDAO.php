<?php
include_once('conexion.php');

class RecetaMedicaDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todas las recetas médicas
     */
    public function obtenerTodasRecetas()
    {
        $sql = "SELECT 
                    rm.id_receta,
                    rm.historia_clinica_id,
                    rm.id_medico,
                    rm.fecha,
                    rm.indicaciones_generales,
                    rm.creado_en,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico
            FROM receta_medica rm
            JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
            JOIN medicos m ON rm.id_medico = m.id_medico 
            JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
            ORDER BY rm.fecha DESC, rm.id_receta DESC";

        $resultado = $this->connection->query($sql);
        $recetas = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $recetas[] = $fila;
            }
        }

        return $recetas;
    }

    /**
     * Obtiene una receta específica por ID
     */
    public function obtenerRecetaPorId($idReceta)
    {
        $sql = "SELECT 
                    rm.*,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    u_med.id_usuario,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico,
                    hc.id_paciente
            FROM receta_medica rm
            JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
            JOIN medicos m ON rm.id_medico = m.id_medico
            JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
            WHERE rm.id_receta = ?";

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return null;
        }
        
        $stmt->bind_param("i", $idReceta);
        
        if (!$stmt->execute()) {
            error_log("Error ejecutando consulta: " . $stmt->error);
            $stmt->close();
            return null;
        }
        
        $resultado = $stmt->get_result();
        $receta = $resultado->fetch_assoc();
        $stmt->close();

        return $receta;
    }

    /**
     * Registra una nueva receta médica
     */
    public function registrarReceta($historiaClinicaId, $idUsuarioMedico, $fecha, $indicacionesGenerales)
    {
        // Obtener el id_medico a partir del id_usuario
        $idMedico = $this->obtenerIdMedicoPorUsuario($idUsuarioMedico);
        
        if (!$idMedico) {
            error_log("Error: No se pudo encontrar id_medico para id_usuario: " . $idUsuarioMedico);
            return false;
        }

        $sql = "INSERT INTO receta_medica 
                (historia_clinica_id, id_medico, fecha, indicaciones_generales) 
                VALUES (?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("iiss", 
            $historiaClinicaId, 
            $idMedico,
            $fecha, 
            $indicacionesGenerales
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando consulta: " . $stmt->error);
        } else {
            $idReceta = $this->connection->insert_id;
        }
        
        $stmt->close();
        
        return $resultado ? $idReceta : false;
    }

    /**
     * Actualiza una receta existente
     */
    public function actualizarReceta($idReceta, $historiaClinicaId, $idMedico, $fecha, $indicacionesGenerales)
    {
        $sql = "UPDATE receta_medica SET 
                historia_clinica_id = ?, 
                id_medico = ?, 
                fecha = ?, 
                indicaciones_generales = ?
                WHERE id_receta = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("iissi", 
            $historiaClinicaId, 
            $idMedico, 
            $fecha, 
            $indicacionesGenerales,
            $idReceta
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Elimina una receta
     */
    public function eliminarReceta($idReceta)
    {
        $sql = "DELETE FROM receta_medica WHERE id_receta = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtiene el id_medico a partir del id_usuario
     */
    public function obtenerIdMedicoPorUsuario($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
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
        error_log("No se encontró médico para id_usuario: " . $idUsuario);
        return null;
    }

    /**
     * Obtiene historias clínicas para select
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
     * Obtiene médicos activos para select
     */
    public function obtenerMedicosActivos()
    {
        $sql = "SELECT 
                    u.id_usuario,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo,
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
 * Obtiene el id_usuario a partir del id_medico
 */
public function obtenerIdUsuarioPorIdMedico($idMedico)
{
    $sql = "SELECT id_usuario FROM medicos WHERE id_medico = ?";
    
    $stmt = $this->connection->prepare($sql);
    if (!$stmt) {
        error_log("Error preparando consulta: " . $this->connection->error);
        return null;
    }
    
    $stmt->bind_param("i", $idMedico);
    
    if (!$stmt->execute()) {
        error_log("Error ejecutando consulta: " . $stmt->error);
        $stmt->close();
        return null;
    }
    
    $resultado = $stmt->get_result();
    
    if ($fila = $resultado->fetch_assoc()) {
        $idUsuario = $fila['id_usuario'];
        $stmt->close();
        return $idUsuario;
    }
    
    $stmt->close();
    return null;
}
}
?>