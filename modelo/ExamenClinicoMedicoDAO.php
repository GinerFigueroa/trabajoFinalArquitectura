<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\ExamenClinicoDAO.php
include_once('Conexion.php'); 
// Asumimos que EntidadAuxiliarDAO ya no existe como archivo separado, sino que
// sus métodos requeridos se integran aquí, o creamos un DAO auxiliar simple.

class ExamenClinicoDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =================================================================================
    // MÉTODOS AUXILIARES (SIMULANDO EntidadAuxiliarDAO para la solicitud)
    // Se requieren para obtener nombres y listas de HC.
    // =================================================================================
    
    /**
     * Auxiliar: Obtiene el nombre completo del paciente a partir del ID de Historia Clínica (HC).
     * @param int $idHistoriaClinica
     * @return string|null
     */
    public function obtenerNombrePacientePorIdHC($idHistoriaClinica)
    {
        $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE hc.historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idHistoriaClinica);
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
     * Auxiliar: Obtiene el nombre completo de cualquier usuario por su ID de usuario (Médico).
     * @param int $idUsuario
     * @return string|null
     */
    public function obtenerNombreCompletoUsuario($idUsuario)
    {
        $sql = "SELECT nombre, apellido_paterno, apellido_materno 
                FROM usuarios 
                WHERE id_usuario = ? AND activo = 1";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
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
     * Auxiliar: Obtiene solo las historias clínicas asignadas a un médico tratante.
     * Requerido para el <select> en el formulario de creación de órdenes.
     * @param int $idMedicoTratante
     * @return array
     */
    public function obtenerHistoriasPorMedico($idMedicoTratante)
    {
        $sql = "SELECT 
                    hc.historia_clinica_id, 
                    u.nombre,
                    u.apellido_paterno,
                    u.apellido_materno
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE hc.dr_tratante_id = ? 
                ORDER BY u.apellido_paterno";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedicoTratante);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $historias = [];

        while ($fila = $resultado->fetch_assoc()) {
            $fila['nombre_paciente'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
            $historias[] = $fila;
        }
        $stmt->close();
        return $historias;
    }

    // =================================================================================
    // MÉTODOS CRUD PRINCIPALES PARA ORDEN DE EXAMEN
    // =================================================================================
    
    /**
     * Obtiene solo las órdenes de examen creadas por un médico específico.
     * @param int $idMedico El id_usuario del médico logueado.
     * @return array
     */
    public function obtenerOrdenesPorMedico($idMedico)
    {
        $sql = "SELECT * FROM orden_examen
                WHERE id_medico = ? 
                ORDER BY fecha DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $ordenes = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $ordenes;
    }

    /**
     * Obtiene una orden de examen específica por ID.
     * @param int $idOrden
     * @return array|null
     */
    public function obtenerOrdenPorId($idOrden)
    {
        $sql = "SELECT * FROM orden_examen WHERE id_orden = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idOrden);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $orden = $resultado->fetch_assoc();
        $stmt->close();
        
        return $orden;
    }

    /**
     * Registra una nueva orden de examen.
     * @return bool
     */
    public function registrarOrden($historiaClinicaId, $idMedico, $fecha, $tipoExamen, $indicaciones) 
    {
        $estadoInicial = 'Pendiente';
        $sql = "INSERT INTO orden_examen 
                (historia_clinica_id, id_medico, fecha, tipo_examen, indicaciones, estado)
                VALUES (?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iissis", 
            $historiaClinicaId, 
            $idMedico, 
            $fecha, 
            $tipoExamen, 
            $indicaciones,
            $estadoInicial
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita los campos de una orden de examen existente.
     * Nota: Normalmente no se edita el id_medico o HC.
     * @return bool
     */
    public function editarOrden($idOrden, $fecha, $tipoExamen, $indicaciones, $estado, $resultados)
    {
        $sql = "UPDATE orden_examen SET 
                fecha = ?, 
                tipo_examen = ?, 
                indicaciones = ?, 
                estado = ?, 
                resultados = ?
                WHERE id_orden = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("sssssi", 
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
     * Elimina una orden de examen. 
     * @param int $idOrden
     * @return bool
     */
    public function eliminarOrden($idOrden)
    {
        $stmt = $this->connection->prepare("DELETE FROM orden_examen WHERE id_orden = ?");
        $stmt->bind_param("i", $idOrden);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }
}
?>