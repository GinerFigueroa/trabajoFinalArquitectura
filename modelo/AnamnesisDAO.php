<?php

include_once('Conexion.php'); 

class AnamnesisDAO
{
    private $connection;
    private $objAuxiliar;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
        $this->objAuxiliar = new EntidadAuxiliarDAO();
    }

    /**
     * Obtiene todos los registros de Anamnesis, enriquecidos con el nombre del paciente.
     * @return array
     */
    public function obtenerTodasAnamnesis()
    {
        $sql = "SELECT anamnesis_id, historia_clinica_id, alergias, enfermedades_pulmonares, enfermedades_cardiacas, medicacion
                FROM anamnesis
                ORDER BY anamnesis_id DESC";
        
        $resultado = $this->connection->query($sql);
        $anamnesisLista = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $fila['nombre_paciente'] = $this->objAuxiliar->obtenerNombrePacientePorHistoriaClinica($fila['historia_clinica_id']);
                $anamnesisLista[] = $fila;
            }
        }
        return $anamnesisLista;
    }

    /**
     * Obtiene un registro de Anamnesis específico por ID.
     * @param int $anamnesisId
     * @return array|null
     */
    public function obtenerAnamnesisPorId($anamnesisId)
    {
        $sql = "SELECT * FROM anamnesis WHERE anamnesis_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $anamnesisId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $registro = $resultado->fetch_assoc();
        $stmt->close();
        
        return $registro;
    }
    
    /**
     * Registra una nueva anamnesis.
     */
    public function registrarAnamnesis(
        $hcId, $alergias, $pulm, $card, $neuro, $hepat, $renal, $endo, $otras, $med, $operado, $tumor, $hemorragia, $fuma, $frecFuma, $anticon, $embar, $semEmbar, $lact
    ) {
        $sql = "INSERT INTO anamnesis (historia_clinica_id, alergias, enfermedades_pulmonares, enfermedades_cardiacas, enfermedades_neurologicas, enfermedades_hepaticas, enfermedades_renales, enfermedades_endocrinas, otras_enfermedades, medicacion, ha_sido_operado, ha_tenido_tumor, ha_tenido_hemorragia, fuma, frecuencia_fuma, toma_anticonceptivos, esta_embarazada, semanas_embarazo, periodo_lactancia)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->connection->prepare($sql);
        
        // Tipos: i=integer, s=string. (1 int, 10 strings, 4 tinyint (i), 1 string, 3 tinyint (i), 1 int, 1 tinyint (i))
        $stmt->bind_param("issssssssssiiiisi", 
            $hcId, $alergias, $pulm, $card, $neuro, $hepat, $renal, $endo, $otras, $med, $operado, 
            $tumor, $hemorragia, $fuma, $frecFuma, $anticon, $embar, $semEmbar, $lact
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita un registro de anamnesis existente.
     */
    public function editarAnamnesis(
        $anamnesisId, $hcId, $alergias, $pulm, $card, $neuro, $hepat, $renal, $endo, $otras, $med, $operado, $tumor, $hemorragia, $fuma, $frecFuma, $anticon, $embar, $semEmbar, $lact
    ) {
        $sql = "UPDATE anamnesis SET 
                historia_clinica_id = ?, alergias = ?, enfermedades_pulmonares = ?, enfermedades_cardiacas = ?, 
                enfermedades_neurologicas = ?, enfermedades_hepaticas = ?, enfermedades_renales = ?, 
                enfermedades_endocrinas = ?, otras_enfermedades = ?, medicacion = ?, ha_sido_operado = ?, 
                ha_tenido_tumor = ?, ha_tenido_hemorragia = ?, fuma = ?, frecuencia_fuma = ?, 
                toma_anticonceptivos = ?, esta_embarazada = ?, semanas_embarazo = ?, periodo_lactancia = ?
                WHERE anamnesis_id = ?";

        $stmt = $this->connection->prepare($sql);
        
        $stmt->bind_param("issssssssssiiiisii", 
            $hcId, $alergias, $pulm, $card, $neuro, $hepat, $renal, $endo, $otras, $med, $operado, 
            $tumor, $hemorragia, $fuma, $frecFuma, $anticon, $embar, $semEmbar, $lact, $anamnesisId
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina un registro de anamnesis.
     */
    public function eliminarAnamnesis($anamnesisId)
    {
        $stmt = $this->connection->prepare("DELETE FROM anamnesis WHERE anamnesis_id = ?");
        $stmt->bind_param("i", $anamnesisId);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }


    // Método a asegurar/añadir en EntidadAuxiliarDAO.php (si no lo tienes ya):

/**
 * Obtiene una lista de Historias Clínicas activas con el nombre del paciente.
 * Utilizado para poblar el <select> de Historia Clínica.
 * @return array
 */
public function obtenerHistoriasClinicasActivasConNombrePaciente()
{
    $sql = "SELECT hc.historia_clinica_id, u.nombre, u.apellido_paterno, u.apellido_materno
            FROM historia_clinica hc
            INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            -- Consideramos la historia clínica como 'activa' si existe y el paciente está activo (u.activo=1)
            ORDER BY hc.historia_clinica_id DESC";
    
    $resultado = $this->connection->query($sql);
    $historias = [];
    while ($fila = $resultado->fetch_assoc()) {
        $fila['nombre_completo'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
        $historias[] = $fila;
    }
    return $historias;
}

/**
 * Obtiene el nombre completo del paciente a partir del ID de Historia Clínica.
 * @param int $historiaClinicaId
 * @return string|null
 */
public function obtenerNombrePacientePorHistoriaClinica($historiaClinicaId)
{
    $sql = "SELECT u.nombre, u.apellido_paterno, u.apellido_materno
            FROM historia_clinica hc
            INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            WHERE hc.historia_clinica_id = ?";
    
    $stmt = $this->connection->prepare($sql);
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


// rol--medico--
public function obtenerAnamnesisConAnemiaPorMedico($idMedico)
    {
        // **ASUMIMOS LA ESTRUCTURA DE FILTRADO MÁS COMPLEJA Y SEGURA:**
        // Filtra Anamnesis donde la Historia Clínica esté relacionada con un Internado
        // cuyo médico tratante sea el médico logueado. Y que el campo 'antecedentes_patologicos'
        // contenga la palabra 'anemia'.
        
        $sql = "SELECT 
                    a.anamnesis_id, a.fecha_registro, a.antecedentes_patologicos, 
                    hc.historia_clinica_id, 
                    u.nombre, u.apellido_paterno, u.apellido_materno
                FROM anamnesis a
                INNER JOIN historia_clinica hc ON a.historia_clinica_id = hc.historia_clinica_id
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
                INNER JOIN internado i ON hc.historia_clinica_id = i.id_historia_clinica 
                    AND i.dr_tratante_id = ? -- FILTRO 1: Pacientes asignados a este médico
                WHERE a.antecedentes_patologicos LIKE '%anemia%' -- FILTRO 2: Condición de Anemia
                ORDER BY a.fecha_registro DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $anamnesis = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Formatear el nombre del paciente
        foreach ($anamnesis as $key => $a) {
            $anamnesis[$key]['nombre_paciente'] = trim("{$a['nombre']} {$a['apellido_paterno']} {$a['apellido_materno']}");
            unset($anamnesis[$key]['nombre'], $anamnesis[$key]['apellido_paterno'], $anamnesis[$key]['apellido_materno']);
        }
        
        return $anamnesis;
    }
}
?>
