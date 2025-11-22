<?php
include_once('conexion.php'); 

class ExamenClinicoDAO
{
    private $connection;

    public function __construct() {
        // Se conecta a la base de datos
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // =================================================================
    // MÉTODOS AUXILIARES (Ahora PUBLIC para ser usados por el controlador)
    // =================================================================

    /**
     * Obtiene el nombre completo del paciente a partir del historia_clinica_id.
     * Utilizado para validación en el controlador y para enriquecer la lista de exámenes.
     * @param int $historiaClinicaId
     * @return string|null El nombre completo o null si no existe.
     */
    public function obtenerNombrePacientePorHistoriaClinica($historiaClinicaId) // <-- CORREGIDO: De private a PUBLIC
    {
        $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE hc.historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
             return null;
        }

        $stmt->bind_param("i", $historiaClinicaId);
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
     * Obtiene el nombre completo del personal por su ID de usuario (Enfermera).
     * Utilizado para validación en el controlador y para enriquecer la lista de exámenes.
     * @param int $idUsuario
     * @return string|null El nombre completo o null si no existe.
     */
    public function obtenerNombrePersonalPorIdUsuario($idUsuario) // <-- CORREGIDO: De private a PUBLIC
    {
        // Se asegura que si el ID es nulo (no asignado), retorna null inmediatamente
        if (empty($idUsuario)) {
            return null;
        }
        
        $sql = "SELECT nombre, apellido_paterno, apellido_materno 
                FROM usuarios 
                WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
             return null;
        }
        
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


    // =================================================================
    // MÉTODOS CRUD PRINCIPALES
    // =================================================================

    /**
     * Obtiene todos los registros de examen clínico, enriquecidos con 
     * los nombres del paciente y enfermero.
     * @return array
     */
    public function obtenerTodosExamenes()
    {
        $sql = "SELECT examen_id, historia_clinica_id, peso, talla, pulso, id_enfermero
                FROM examen_clinico
                ORDER BY examen_id DESC";
        
        $resultado = $this->connection->query($sql);
        $examenes = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // Obtener nombre del Paciente a través de Historia Clínica
                $fila['nombre_paciente'] = $this->obtenerNombrePacientePorHistoriaClinica($fila['historia_clinica_id']);
                
                // Obtener nombre del Enfermero (usando el método interno)
                $fila['nombre_enfermero'] = $this->obtenerNombrePersonalPorIdUsuario($fila['id_enfermero']);
                $examenes[] = $fila;
            }
        }
        return $examenes;
    }
    
    /**
     * Obtiene un examen específico por ID, enriquecido con los nombres.
     * @param int $examenId
     * @return array|null
     */
    public function obtenerExamenPorId($examenId)
    {
        $sql = "SELECT * FROM examen_clinico WHERE examen_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $examenId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $examen = $resultado->fetch_assoc();
        $stmt->close();
        
        if ($examen) {
             $examen['nombre_paciente'] = $this->obtenerNombrePacientePorHistoriaClinica($examen['historia_clinica_id']);
             $examen['nombre_enfermero'] = $this->obtenerNombrePersonalPorIdUsuario($examen['id_enfermero']);
        }
        return $examen;
    }

    /**
     * Registra un nuevo examen.
     *
     * @param int $historiaClinicaId
     * @param float $peso
     * @param float $talla
     * @param string $pulso
     * @param int|null $idEnfermero
     * @return bool
     */
    public function registrarExamen($historiaClinicaId, $peso, $talla, $pulso, $idEnfermero)
    {
        // Se prepara la sentencia SQL sin valor para id_enfermero si es NULL
        if (is_null($idEnfermero)) {
            $sql = "INSERT INTO examen_clinico (historia_clinica_id, peso, talla, pulso, id_enfermero)
                    VALUES (?, ?, ?, ?, NULL)";
            $stmt = $this->connection->prepare($sql);
            // Tipos: 'i' (int), 'd' (decimal/float), 's' (string), 's' (string)
            $stmt->bind_param("idss", $historiaClinicaId, $peso, $talla, $pulso); 
        } else {
            $sql = "INSERT INTO examen_clinico (historia_clinica_id, peso, talla, pulso, id_enfermero)
                    VALUES (?, ?, ?, ?, ?)";
            $stmt = $this->connection->prepare($sql);
            // Tipos: 'i' (int), 'd' (decimal/float), 's' (string), 's' (string), 'i' (int)
            $stmt->bind_param("idssi", $historiaClinicaId, $peso, $talla, $pulso, $idEnfermero);
        }

        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita un examen existente.
     *
     * @param int $examenId
     * @param int $historiaClinicaId
     * @param float $peso
     * @param float $talla
     * @param string $pulso
     * @param int|null $idEnfermero
     * @return bool
     */
    public function editarExamen($examenId, $historiaClinicaId, $peso, $talla, $pulso, $idEnfermero)
    {
        // El controlador debe enviar NULL para un campo vacío, pero se asegura la conversión aquí
        $idEnfermero = empty($idEnfermero) ? NULL : (int)$idEnfermero;

        if (is_null($idEnfermero)) {
            $sql = "UPDATE examen_clinico 
                    SET historia_clinica_id = ?, peso = ?, talla = ?, pulso = ?, id_enfermero = NULL
                    WHERE examen_id = ?";
            $stmt = $this->connection->prepare($sql);
            // Tipos: 'i' (historia_clinica_id), 'd' (peso), 's' (talla), 's' (pulso), 'i' (examenId)
            $stmt->bind_param("idssi", $historiaClinicaId, $peso, $talla, $pulso, $examenId);
        } else {
            $sql = "UPDATE examen_clinico 
                    SET historia_clinica_id = ?, peso = ?, talla = ?, pulso = ?, id_enfermero = ?
                    WHERE examen_id = ?";
            $stmt = $this->connection->prepare($sql);
            // Tipos: 'i' (historia_clinica_id), 'd' (peso), 's' (talla), 's' (pulso), 'i' (id_enfermero), 'i' (examenId)
            $stmt->bind_param("idssii", $historiaClinicaId, $peso, $talla, $pulso, $idEnfermero, $examenId);
        }
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina un examen de la base de datos.
     * @param int $examenId
     * @return bool
     */
    public function eliminarExamen($examenId)
    {
        $stmt = $this->connection->prepare("DELETE FROM examen_clinico WHERE examen_id = ?");
        $stmt->bind_param("i", $examenId);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    // =================================================================
    // MÉTODOS PARA SELECCIÓN EN VISTAS/FORMULARIOS (Combos/Selects)
    // =================================================================

    /**
     * Obtiene una lista de Historias Clínicas activas con el nombre completo del paciente.
     * @return array
     */
    public function obtenerHistoriasClinicasConNombrePaciente()
    {
        $sql = "SELECT hc.historia_clinica_id, u.nombre, u.apellido_paterno, u.apellido_materno
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY u.apellido_paterno";
        
        $resultado = $this->connection->query($sql);
        $historias = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $fila['nombre_completo'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
                $historias[] = $fila;
            }
        }
        return $historias;
    }
    
    /**
     * Obtiene una lista de Enfermeros activos (asumiendo id_rol = 6) para el select de la vista.
     * @return array
     */
    public function obtenerEnfermerosActivos()
    {
        $sql = "SELECT id_usuario, nombre, apellido_paterno, apellido_materno 
                FROM usuarios 
                WHERE id_rol = 6 AND activo = 1 
                ORDER BY apellido_paterno";
        $resultado = $this->connection->query($sql);
        $enfermeros = [];
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $enfermeros[] = $fila;
            }
        }
        return $enfermeros;
    }
    
}
?>