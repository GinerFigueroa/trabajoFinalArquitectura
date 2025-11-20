<?php
include_once('conexion.php');

class RecetaDetalleDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todos los detalles de recetas
     */
    public function obtenerTodosDetalles()
    {
        $sql = "SELECT 
                    rd.id_detalle,
                    rd.id_receta,
                    rd.medicamento,
                    rd.dosis,
                    rd.frecuencia,
                    rd.duracion,
                    rd.notas,
                    rm.id_receta,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico,
                    rm.fecha as fecha_receta
                FROM receta_detalle rd
                JOIN receta_medica rm ON rd.id_receta = rm.id_receta
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN medicos m ON rm.id_medico = m.id_medico 
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                ORDER BY rd.id_detalle DESC, rm.fecha DESC";

        $resultado = $this->connection->query($sql);
        $detalles = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $detalles[] = $fila;
            }
        }

        return $detalles;
    }

    /**
     * Obtiene detalles por ID de receta
     */
    public function obtenerDetallesPorReceta($idReceta)
    {
        $sql = "SELECT 
                    rd.*,
                    rm.fecha,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente
                FROM receta_detalle rd
                JOIN receta_medica rm ON rd.id_receta = rm.id_receta
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                WHERE rd.id_receta = ?
                ORDER BY rd.id_detalle ASC";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $detalles = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $detalles[] = $fila;
            }
        }
        $stmt->close();

        return $detalles;
    }

    /**
     * Obtiene un detalle específico por ID
     */
    public function obtenerDetallePorId($idDetalle)
    {
        $sql = "SELECT 
                    rd.*,
                    rm.id_receta,
                    rm.historia_clinica_id,
                    rm.id_medico,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) AS nombre_medico
                FROM receta_detalle rd
                JOIN receta_medica rm ON rd.id_receta = rm.id_receta
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN medicos m ON rm.id_medico = m.id_medico
                JOIN usuarios u_med ON m.id_usuario = u_med.id_usuario
                WHERE rd.id_detalle = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idDetalle);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $detalle = $resultado->fetch_assoc();
        $stmt->close();

        return $detalle;
    }

    /**
     * Registra un nuevo detalle de receta
     */
    public function registrarDetalle($idReceta, $medicamento, $dosis, $frecuencia, $duracion = null, $notas = null)
    {
        $sql = "INSERT INTO receta_detalle 
                (id_receta, medicamento, dosis, frecuencia, duracion, notas) 
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("isssss", 
            $idReceta, 
            $medicamento,
            $dosis, 
            $frecuencia,
            $duracion,
            $notas
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando consulta: " . $stmt->error);
        } else {
            $idDetalle = $this->connection->insert_id;
        }
        
        $stmt->close();
        
        return $resultado ? $idDetalle : false;
    }

    /**
     * Actualiza un detalle existente
     */
    public function actualizarDetalle($idDetalle, $medicamento, $dosis, $frecuencia, $duracion = null, $notas = null)
    {
        $sql = "UPDATE receta_detalle SET 
                medicamento = ?, 
                dosis = ?, 
                frecuencia = ?, 
                duracion = ?, 
                notas = ?
                WHERE id_detalle = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        $stmt->bind_param("sssssi", 
            $medicamento,
            $dosis, 
            $frecuencia,
            $duracion,
            $notas,
            $idDetalle
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Elimina un detalle
     */
    public function eliminarDetalle($idDetalle)
    {
        $sql = "DELETE FROM receta_detalle WHERE id_detalle = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idDetalle);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtiene recetas médicas disponibles para select
     */
    public function obtenerRecetasMedicas()
    {
        $sql = "SELECT 
                    rm.id_receta,
                    rm.fecha,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico
                FROM receta_medica rm
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                JOIN medicos m ON rm.id_medico = m.id_medico
                JOIN usuarios um ON m.id_usuario = um.id_usuario
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
     * Verifica si una receta existe
     */
    public function existeReceta($idReceta)
    {
        $sql = "SELECT COUNT(*) as count FROM receta_medica WHERE id_receta = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
     * Obtiene estadísticas de medicamentos más recetados
     */
    public function obtenerMedicamentosMasRecetados($limite = 10)
    {
        $sql = "SELECT 
                    medicamento,
                    COUNT(*) as total_veces,
                    AVG(LENGTH(dosis)) as avg_dosis_length
                FROM receta_detalle 
                GROUP BY medicamento 
                ORDER BY total_veces DESC 
                LIMIT ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $estadisticas = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $estadisticas[] = $fila;
            }
        }
        $stmt->close();

        return $estadisticas;
    }

    /**
     * Obtiene el id_usuario del médico dueño de la receta
     */
    public function obtenerIdUsuarioPorIdReceta($idReceta)
    {
        $sql = "SELECT m.id_usuario 
                FROM receta_medica rm
                JOIN medicos m ON rm.id_medico = m.id_medico
                WHERE rm.id_receta = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $idUsuario = $fila['id_usuario'];
            $stmt->close();
            return $idUsuario;
        }
        
        $stmt->close();
        return null;
    }

    /**
     * Valida si el usuario puede modificar el detalle (es el médico dueño)
     */
    public function validarPropiedadDetalle($idDetalle, $idUsuario)
    {
        $sql = "SELECT COUNT(*) as count 
                FROM receta_detalle rd
                JOIN receta_medica rm ON rd.id_receta = rm.id_receta
                JOIN medicos m ON rm.id_medico = m.id_medico
                WHERE rd.id_detalle = ? AND m.id_usuario = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $idDetalle, $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }
}
?>