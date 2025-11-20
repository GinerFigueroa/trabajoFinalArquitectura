<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\DetalleRecetaDAO.php
include_once('Conexion.php'); 

class DetalleRecetaDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =================================================================================
    // MÉTODOS AUXILIARES (SIMULANDO EntidadAuxiliarDAO)
    // =================================================================================
    
    /**
     * Auxiliar: Obtiene el nombre completo del paciente a partir del ID de Receta.
     * @param int $idReceta
     * @return string|null
     */
    public function obtenerNombrePacientePorIdReceta($idReceta)
    {
        $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
                FROM receta_medica rm
                INNER JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE rm.id_receta = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $stmt->close();
            return trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
        }
        $stmt->close();
        return null;
    }

    /**
     * Auxiliar: Obtiene el ID de Historia Clínica a partir del ID de Receta.
     * @param int $idReceta
     * @return int|null
     */
    public function obtenerIdHCPorIdReceta($idReceta)
    {
        $sql = "SELECT historia_clinica_id FROM receta_medica WHERE id_receta = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        if ($fila = $resultado->fetch_assoc()) {
            $stmt->close();
            return (int)$fila['historia_clinica_id'];
        }
        $stmt->close();
        return null;
    }

    /**
     * Auxiliar: Obtiene una lista de recetas (ID, Fecha) creadas por un médico.
     * Requerido para el listado principal.
     * @param int $idMedicoTratante
     * @return array
     */
    public function obtenerRecetasPorMedico($idMedicoTratante)
    {
        $sql = "SELECT id_receta, fecha, historia_clinica_id
                FROM receta_medica
                WHERE id_medico = ? 
                ORDER BY fecha DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedicoTratante);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $recetas = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $recetas;
    }

    // =================================================================================
    // MÉTODOS CRUD PRINCIPALES PARA DETALLE DE RECETA
    // =================================================================================
    
    /**
     * Obtiene todos los detalles (medicamentos) para una receta específica.
     * @param int $idReceta
     * @return array
     */
    public function obtenerDetallesPorIdReceta($idReceta)
    {
        $sql = "SELECT * FROM receta_detalle
                WHERE id_receta = ? 
                ORDER BY id_detalle ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idReceta);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $detalles = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $detalles;
    }

    /**
     * Obtiene un detalle (medicamento) específico por su ID.
     * @param int $idDetalle
     * @return array|null
     */
    public function obtenerDetallePorId($idDetalle)
    {
        $sql = "SELECT * FROM receta_detalle WHERE id_detalle = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idDetalle);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $detalle = $resultado->fetch_assoc();
        $stmt->close();
        
        return $detalle;
    }

    /**
     * Registra un nuevo medicamento en el detalle de una receta.
     * @return bool
     */
    public function registrarDetalle($idReceta, $medicamento, $dosis, $frecuencia, $duracion, $notas) 
    {
        $sql = "INSERT INTO receta_detalle 
                (id_receta, medicamento, dosis, frecuencia, duracion, notas)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("isssss", 
            $idReceta, 
            $medicamento, 
            $dosis, 
            $frecuencia,
            $duracion,
            $notas
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita los campos de un detalle de receta existente.
     * @return bool
     */
    public function editarDetalle($idDetalle, $medicamento, $dosis, $frecuencia, $duracion, $notas)
    {
        $sql = "UPDATE receta_detalle SET 
                medicamento = ?, 
                dosis = ?, 
                frecuencia = ?, 
                duracion = ?, 
                notas = ?
                WHERE id_detalle = ?";

        $stmt = $this->connection->prepare($sql);
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
     * Elimina un detalle (medicamento) de la receta.
     * @param int $idDetalle
     * @return bool
     */
    public function eliminarDetalle($idDetalle)
    {
        $stmt = $this->connection->prepare("DELETE FROM receta_detalle WHERE id_detalle = ?");
        $stmt->bind_param("i", $idDetalle);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>