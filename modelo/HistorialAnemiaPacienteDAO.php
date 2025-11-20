<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\HistorialAnemiaPacienteDAO.php

include_once('conexion.php');

class HistorialAnemiaPacienteDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todos los registros de anamnesis/historial anemia
     */
    public function obtenerTodosHistoriales()
    {
        $sql = "SELECT 
                    a.anamnesis_id,
                    a.historia_clinica_id,
                    a.alergias,
                    a.enfermedades_pulmonares,
                    a.enfermedades_cardiacas,
                    a.enfermedades_neurologicas,
                    a.enfermedades_hepaticas,
                    a.enfermedades_renales,
                    a.enfermedades_endocrinas,
                    a.otras_enfermedades,
                    a.medicacion,
                    a.ha_sido_operado,
                    a.ha_tenido_tumor,
                    a.ha_tenido_hemorragia,
                    a.fuma,
                    a.frecuencia_fuma,
                    a.toma_anticonceptivos,
                    a.esta_embarazada,
                    a.semanas_embarazo,
                    a.periodo_lactancia,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni,
                    hc.fecha_creacion
                FROM anamnesis a
                JOIN historia_clinica hc ON a.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY hc.fecha_creacion DESC, a.anamnesis_id DESC";

        $resultado = $this->connection->query($sql);
        $historiales = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $historiales[] = $fila;
            }
        }

        return $historiales;
    }

    /**
     * Obtiene un historial específico por ID
     */
    public function obtenerHistorialPorId($idAnamnesis)
    {
        $sql = "SELECT 
                    a.*,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni,
                    p.id_paciente,
                    hc.fecha_creacion,
                    hc.dr_tratante_id
                FROM anamnesis a
                JOIN historia_clinica hc ON a.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE a.anamnesis_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idAnamnesis);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $historial = $resultado->fetch_assoc();
        $stmt->close();

        return $historial;
    }

    /**
     * Obtiene el historial por historia clínica ID
     */
    public function obtenerHistorialPorHistoriaClinica($historiaClinicaId)
    {
        $sql = "SELECT 
                    a.*,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni
                FROM anamnesis a
                JOIN historia_clinica hc ON a.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE a.historia_clinica_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $historial = $resultado->fetch_assoc();
        $stmt->close();

        return $historial;
    }

    /**
     * Verifica si existe historial para una historia clínica
     */
    public function existeHistorialParaHistoriaClinica($historiaClinicaId)
    {
        $sql = "SELECT COUNT(*) as count FROM anamnesis WHERE historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
     * Registra un nuevo historial de anemia/anamnesis - VERSIÓN CORREGIDA
     */
    public function registrarHistorial($historiaClinicaId, $datos)
    {
        $sql = "INSERT INTO anamnesis (
                    historia_clinica_id,
                    alergias,
                    enfermedades_pulmonares,
                    enfermedades_cardiacas,
                    enfermedades_neurologicas,
                    enfermedades_hepaticas,
                    enfermedades_renales,
                    enfermedades_endocrinas,
                    otras_enfermedades,
                    medicacion,
                    ha_sido_operado,
                    ha_tenido_tumor,
                    ha_tenido_hemorragia,
                    fuma,
                    frecuencia_fuma,
                    toma_anticonceptivos,
                    esta_embarazada,
                    semanas_embarazo,
                    periodo_lactancia
                ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        // Extraer y preparar valores individualmente
        $alergias = $datos['alergias'] ?? null;
        $enfermedadesPulmonares = $datos['enfermedades_pulmonares'] ?? null;
        $enfermedadesCardiacas = $datos['enfermedades_cardiacas'] ?? null;
        $enfermedadesNeurologicas = $datos['enfermedades_neurologicas'] ?? null;
        $enfermedadesHepaticas = $datos['enfermedades_hepaticas'] ?? null;
        $enfermedadesRenales = $datos['enfermedades_renales'] ?? null;
        $enfermedadesEndocrinas = $datos['enfermedades_endocrinas'] ?? null;
        $otrasEnfermedades = $datos['otras_enfermedades'] ?? null;
        $medicacion = $datos['medicacion'] ?? null;
        $haSidoOperado = $datos['ha_sido_operado'] ?? null;
        $haTenidoTumor = isset($datos['ha_tenido_tumor']) ? (int)$datos['ha_tenido_tumor'] : 0;
        $haTenidoHemorragia = isset($datos['ha_tenido_hemorragia']) ? (int)$datos['ha_tenido_hemorragia'] : 0;
        $fuma = isset($datos['fuma']) ? (int)$datos['fuma'] : 0;
        $frecuenciaFuma = $datos['frecuencia_fuma'] ?? null;
        $tomaAnticonceptivos = isset($datos['toma_anticonceptivos']) ? (int)$datos['toma_anticonceptivos'] : 0;
        $estaEmbarazada = isset($datos['esta_embarazada']) ? (int)$datos['esta_embarazada'] : 0;
        $semanasEmbarazo = isset($datos['semanas_embarazo']) ? (int)$datos['semanas_embarazo'] : null;
        $periodoLactancia = isset($datos['periodo_lactancia']) ? (int)$datos['periodo_lactancia'] : 0;

        // Convertir cadenas vacías a NULL
        $alergias = ($alergias === '') ? null : $alergias;
        $enfermedadesPulmonares = ($enfermedadesPulmonares === '') ? null : $enfermedadesPulmonares;
        $enfermedadesCardiacas = ($enfermedadesCardiacas === '') ? null : $enfermedadesCardiacas;
        $enfermedadesNeurologicas = ($enfermedadesNeurologicas === '') ? null : $enfermedadesNeurologicas;
        $enfermedadesHepaticas = ($enfermedadesHepaticas === '') ? null : $enfermedadesHepaticas;
        $enfermedadesRenales = ($enfermedadesRenales === '') ? null : $enfermedadesRenales;
        $enfermedadesEndocrinas = ($enfermedadesEndocrinas === '') ? null : $enfermedadesEndocrinas;
        $otrasEnfermedades = ($otrasEnfermedades === '') ? null : $otrasEnfermedades;
        $medicacion = ($medicacion === '') ? null : $medicacion;
        $haSidoOperado = ($haSidoOperado === '') ? null : $haSidoOperado;
        $frecuenciaFuma = ($frecuenciaFuma === '') ? null : $frecuenciaFuma;

        $stmt->bind_param("isssssssssiiiiisiii",
            $historiaClinicaId,
            $alergias,
            $enfermedadesPulmonares,
            $enfermedadesCardiacas,
            $enfermedadesNeurologicas,
            $enfermedadesHepaticas,
            $enfermedadesRenales,
            $enfermedadesEndocrinas,
            $otrasEnfermedades,
            $medicacion,
            $haSidoOperado,
            $haTenidoTumor,
            $haTenidoHemorragia,
            $fuma,
            $frecuenciaFuma,
            $tomaAnticonceptivos,
            $estaEmbarazada,
            $semanasEmbarazo,
            $periodoLactancia
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando consulta: " . $stmt->error);
        }
        
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Actualiza un historial existente - VERSIÓN CORREGIDA
     */
    public function actualizarHistorial($idAnamnesis, $datos)
    {
        $sql = "UPDATE anamnesis SET 
                    alergias = ?,
                    enfermedades_pulmonares = ?,
                    enfermedades_cardiacas = ?,
                    enfermedades_neurologicas = ?,
                    enfermedades_hepaticas = ?,
                    enfermedades_renales = ?,
                    enfermedades_endocrinas = ?,
                    otras_enfermedades = ?,
                    medicacion = ?,
                    ha_sido_operado = ?,
                    ha_tenido_tumor = ?,
                    ha_tenido_hemorragia = ?,
                    fuma = ?,
                    frecuencia_fuma = ?,
                    toma_anticonceptivos = ?,
                    esta_embarazada = ?,
                    semanas_embarazo = ?,
                    periodo_lactancia = ?
                WHERE anamnesis_id = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta: " . $this->connection->error);
            return false;
        }

        // Extraer y preparar valores individualmente
        $alergias = $datos['alergias'] ?? null;
        $enfermedadesPulmonares = $datos['enfermedades_pulmonares'] ?? null;
        $enfermedadesCardiacas = $datos['enfermedades_cardiacas'] ?? null;
        $enfermedadesNeurologicas = $datos['enfermedades_neurologicas'] ?? null;
        $enfermedadesHepaticas = $datos['enfermedades_hepaticas'] ?? null;
        $enfermedadesRenales = $datos['enfermedades_renales'] ?? null;
        $enfermedadesEndocrinas = $datos['enfermedades_endocrinas'] ?? null;
        $otrasEnfermedades = $datos['otras_enfermedades'] ?? null;
        $medicacion = $datos['medicacion'] ?? null;
        $haSidoOperado = $datos['ha_sido_operado'] ?? null;
        $haTenidoTumor = isset($datos['ha_tenido_tumor']) ? (int)$datos['ha_tenido_tumor'] : 0;
        $haTenidoHemorragia = isset($datos['ha_tenido_hemorragia']) ? (int)$datos['ha_tenido_hemorragia'] : 0;
        $fuma = isset($datos['fuma']) ? (int)$datos['fuma'] : 0;
        $frecuenciaFuma = $datos['frecuencia_fuma'] ?? null;
        $tomaAnticonceptivos = isset($datos['toma_anticonceptivos']) ? (int)$datos['toma_anticonceptivos'] : 0;
        $estaEmbarazada = isset($datos['esta_embarazada']) ? (int)$datos['esta_embarazada'] : 0;
        $semanasEmbarazo = isset($datos['semanas_embarazo']) ? (int)$datos['semanas_embarazo'] : null;
        $periodoLactancia = isset($datos['periodo_lactancia']) ? (int)$datos['periodo_lactancia'] : 0;

        // Convertir cadenas vacías a NULL
        $alergias = ($alergias === '') ? null : $alergias;
        $enfermedadesPulmonares = ($enfermedadesPulmonares === '') ? null : $enfermedadesPulmonares;
        $enfermedadesCardiacas = ($enfermedadesCardiacas === '') ? null : $enfermedadesCardiacas;
        $enfermedadesNeurologicas = ($enfermedadesNeurologicas === '') ? null : $enfermedadesNeurologicas;
        $enfermedadesHepaticas = ($enfermedadesHepaticas === '') ? null : $enfermedadesHepaticas;
        $enfermedadesRenales = ($enfermedadesRenales === '') ? null : $enfermedadesRenales;
        $enfermedadesEndocrinas = ($enfermedadesEndocrinas === '') ? null : $enfermedadesEndocrinas;
        $otrasEnfermedades = ($otrasEnfermedades === '') ? null : $otrasEnfermedades;
        $medicacion = ($medicacion === '') ? null : $medicacion;
        $haSidoOperado = ($haSidoOperado === '') ? null : $haSidoOperado;
        $frecuenciaFuma = ($frecuenciaFuma === '') ? null : $frecuenciaFuma;

        $stmt->bind_param("ssssssssssiiiiisiii",
            $alergias,
            $enfermedadesPulmonares,
            $enfermedadesCardiacas,
            $enfermedadesNeurologicas,
            $enfermedadesHepaticas,
            $enfermedadesRenales,
            $enfermedadesEndocrinas,
            $otrasEnfermedades,
            $medicacion,
            $haSidoOperado,
            $haTenidoTumor,
            $haTenidoHemorragia,
            $fuma,
            $frecuenciaFuma,
            $tomaAnticonceptivos,
            $estaEmbarazada,
            $semanasEmbarazo,
            $periodoLactancia,
            $idAnamnesis
        );
        
        $resultado = $stmt->execute();
        $stmt->close();
        
        return $resultado;
    }

    /**
     * Elimina un historial
     */
    public function eliminarHistorial($idAnamnesis)
    {
        $sql = "DELETE FROM anamnesis WHERE anamnesis_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idAnamnesis);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

    /**
     * Obtiene historias clínicas disponibles para select
     */
    public function obtenerHistoriasClinicasDisponibles()
    {
        $sql = "SELECT 
                    hc.historia_clinica_id,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni,
                    hc.fecha_creacion
                FROM historia_clinica hc
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE NOT EXISTS (
                    SELECT 1 FROM anamnesis a 
                    WHERE a.historia_clinica_id = hc.historia_clinica_id
                )
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
     * Obtiene todas las historias clínicas (para edición)
     */
    public function obtenerTodasHistoriasClinicas()
    {
        $sql = "SELECT 
                    hc.historia_clinica_id,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni,
                    hc.fecha_creacion
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
     * Obtiene estadísticas de factores de riesgo
     */
    public function obtenerEstadisticasFactoresRiesgo()
    {
        $sql = "SELECT 
                    COUNT(*) as total_pacientes,
                    SUM(ha_tenido_tumor) as con_tumor,
                    SUM(ha_tenido_hemorragia) as con_hemorragia,
                    SUM(fuma) as fumadores,
                    SUM(esta_embarazada) as embarazadas,
                    SUM(periodo_lactancia) as en_lactancia
                FROM anamnesis";

        $resultado = $this->connection->query($sql);
        return $resultado->fetch_assoc();
    }

    /**
     * Busca historiales por paciente
     */
    public function buscarHistorialesPorPaciente($terminoBusqueda)
    {
        $sql = "SELECT 
                    a.*,
                    CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_paciente,
                    p.dni
                FROM anamnesis a
                JOIN historia_clinica hc ON a.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.nombre LIKE ? OR u.apellido_paterno LIKE ? OR u.apellido_materno LIKE ? OR p.dni LIKE ?
                ORDER BY u.nombre, u.apellido_paterno";

        $stmt = $this->connection->prepare($sql);
        $termino = "%" . $terminoBusqueda . "%";
        $stmt->bind_param("ssss", $termino, $termino, $termino, $termino);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $historiales = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $historiales[] = $fila;
            }
        }
        $stmt->close();

        return $historiales;
    }
}
?>