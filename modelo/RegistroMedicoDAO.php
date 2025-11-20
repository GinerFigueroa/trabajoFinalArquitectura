<?php
// RegistroMedicoDAO.php

include_once('conexion.php');

class RegistroMedicoDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene TODOS los registros médicos con información del paciente y médico tratante
     * @return array
     */
    public function obtenerTodosRegistros()
    {
        $sql = "SELECT 
                    rm.registro_medico_id,
                    rm.historia_clinica_id,
                    rm.fecha_registro,
                    rm.riesgos,
                    rm.motivo_consulta,
                    rm.enfermedad_actual,
                    rm.tiempo_enfermedad,
                    rm.signos_sintomas,
                    rm.motivo_ultima_visita,
                    rm.ultima_visita_medica,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante,
                    hc.fecha_creacion
                FROM registro_medico rm
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario
                ORDER BY rm.fecha_registro DESC";

        $resultado = $this->connection->query($sql);
        $registros = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $registros[] = $fila;
            }
            if (is_object($resultado)) {
                $resultado->free();
            }
        }

        return $registros;
    }
    /**
 * Obtiene una lista de pacientes activos (rol 4) que SÍ tienen registro en historia_clinica.
 * Útil para consultar y gestionar historias clínicas existentes.
 * @return array
 */
public function obtenerPacientesConHistoriaAsignada()
{
    $sql = "SELECT 
                p.id_paciente, 
                u.nombre, 
                u.apellido_paterno, 
                u.apellido_materno,
                hc.historia_clinica_id,
                hc.fecha_creacion,
                u2.nombre as dr_nombre,
                u2.apellido_paterno as dr_apellido
            FROM pacientes p
            INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
            INNER JOIN historia_clinica hc ON p.id_paciente = hc.id_paciente
            LEFT JOIN usuarios u2 ON hc.dr_tratante_id = u2.id_usuario
            WHERE u.id_rol = 4 -- Asume que 4 es el rol de Paciente
            AND u.activo = 1 
            ORDER BY u.apellido_paterno, u.apellido_materno";

    // Nota: Se asume que $this->connection es la conexión mysqli activa.
    $resultado = $this->connection->query($sql);
    $pacientes = [];
    
    if ($resultado === FALSE) {
        // Manejo de error de consulta
        error_log("Error en obtenerPacientesConHistoriaAsignada: " . $this->connection->error);
        return [];
    }
    
    if ($resultado) {
        while ($fila = $resultado->fetch_assoc()) {
            // Concatenar el nombre completo para facilitar el uso en la vista
            $fila['nombre_completo'] = trim($fila['nombre'] . ' ' . $fila['apellido_paterno'] . ' ' . $fila['apellido_materno']);
            $fila['dr_completo'] = $fila['dr_nombre'] ? trim($fila['dr_nombre'] . ' ' . $fila['dr_apellido']) : 'No asignado';
            $pacientes[] = $fila;
        }
    }
    return $pacientes;
}

    /**
     * Obtiene un registro médico específico por ID
     * @param int $idRegistro
     * @return array|null
     */
    public function obtenerRegistroPorId($idRegistro)
    {
        $sql = "SELECT 
                    rm.registro_medico_id,
                    rm.historia_clinica_id,
                    rm.fecha_registro,
                    rm.riesgos,
                    rm.motivo_consulta,
                    rm.enfermedad_actual,
                    rm.tiempo_enfermedad,
                    rm.signos_sintomas,
                    rm.motivo_ultima_visita,
                    rm.ultima_visita_medica,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
                FROM registro_medico rm
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario
                WHERE rm.registro_medico_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idRegistro);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $registro = $resultado->fetch_assoc();
        $stmt->close();

        return $registro;
    }

    /**
     * Elimina un registro médico por su ID
     * @param int $idRegistro
     * @return bool
     */
    public function eliminarRegistro($idRegistro)
    {
        $sql = "DELETE FROM registro_medico WHERE registro_medico_id = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idRegistro);
        $resultado = $stmt->execute();
        $stmt->close();

        return $resultado;
    }

 // En RegistroMedicoDAO.php - Método CORRECTO
public function registrarRegistro($historiaClinicaId, $riesgos, $motivoConsulta, $enfermedadActual, $tiempoEnfermedad, $signosSintomas, $motivoUltimaVisita, $ultimaVisitaMedica)
{
    $sql = "INSERT INTO registro_medico 
            (historia_clinica_id, riesgos, motivo_consulta, enfermedad_actual, tiempo_enfermedad, signos_sintomas, motivo_ultima_visita, ultima_visita_medica) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $this->connection->prepare($sql);
    
    if (!$stmt) {
        error_log("Error preparando consulta: " . $this->connection->error);
        return false;
    }

    // Convertir fecha vacía a NULL
    if (empty($ultimaVisitaMedica)) {
        $ultimaVisitaMedica = null;
    }
    
    $stmt->bind_param("isssssss", 
        $historiaClinicaId, 
        $riesgos, 
        $motivoConsulta, 
        $enfermedadActual, 
        $tiempoEnfermedad, 
        $signosSintomas, 
        $motivoUltimaVisita, 
        $ultimaVisitaMedica
    );
    
    $resultado = $stmt->execute();
    
    if (!$resultado) {
        error_log("Error ejecutando consulta: " . $stmt->error);
    }
    
    $stmt->close();
    return $resultado;
}




    /**
     * Edita un registro médico existente
     * @param int $idRegistro
     * @param string $riesgos
     * @param string $motivoConsulta
     * @param string $enfermedadActual
     * @param string $tiempoEnfermedad
     * @param string $signosSintomas
     * @param string $motivoUltimaVisita
     * @param string|null $ultimaVisitaMedica (formato YYYY-MM-DD o NULL)
     * @return bool
     */
    public function editarRegistro($idRegistro, $riesgos, $motivoConsulta, $enfermedadActual, $tiempoEnfermedad, $signosSintomas, $motivoUltimaVisita, $ultimaVisitaMedica)
    {
        $sql = "UPDATE registro_medico SET 
                riesgos = ?, 
                motivo_consulta = ?, 
                enfermedad_actual = ?, 
                tiempo_enfermedad = ?, 
                signos_sintomas = ?, 
                motivo_ultima_visita = ?, 
                ultima_visita_medica = ?
                WHERE registro_medico_id = ?";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            error_log("Error preparando consulta de edición: " . $this->connection->error);
            return false;
        }

        // Convertir fecha vacía a NULL
        if (empty($ultimaVisitaMedica)) {
            $ultimaVisitaMedica = null;
        }
        
        $stmt->bind_param("sssssssi", 
            $riesgos, 
            $motivoConsulta, 
            $enfermedadActual, 
            $tiempoEnfermedad, 
            $signosSintomas, 
            $motivoUltimaVisita, 
            $ultimaVisitaMedica,
            $idRegistro
        );
        
        $resultado = $stmt->execute();
        
        if (!$resultado) {
            error_log("Error ejecutando edición: " . $stmt->error);
        }
        
        $stmt->close();
        return $resultado;
    }

    /**
     * Obtiene las historias clínicas disponibles para seleccionar
     * @return array
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
     * Obtiene registros médicos por historia clínica específica
     * @param int $historiaClinicaId
     * @return array
     */
    public function obtenerRegistrosPorHistoria($historiaClinicaId)
    {
        $sql = "SELECT 
                    rm.registro_medico_id,
                    rm.fecha_registro,
                    rm.riesgos,
                    rm.motivo_consulta,
                    rm.enfermedad_actual,
                    rm.tiempo_enfermedad,
                    rm.signos_sintomas,
                    rm.motivo_ultima_visita,
                    rm.ultima_visita_medica,
                    CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
                FROM registro_medico rm
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario
                WHERE rm.historia_clinica_id = ?
                ORDER BY rm.fecha_registro DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $registros = [];
        
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $registros[] = $fila;
            }
        }
        $stmt->close();
        return $registros;
    }

    /**
     * Verifica si existe una historia clínica
     * @param int $historiaClinicaId
     * @return bool
     */
    public function existeHistoriaClinica($historiaClinicaId)
    {
        $sql = "SELECT COUNT(*) as count FROM historia_clinica WHERE historia_clinica_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $historiaClinicaId);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
     * Obtiene estadísticas de registros médicos
     * @return array
     */
    public function obtenerEstadisticas()
    {
        $sql = "SELECT 
                    COUNT(*) as total_registros,
                    COUNT(DISTINCT historia_clinica_id) as total_pacientes,
                    DATE_FORMAT(MIN(fecha_registro), '%d/%m/%Y') as primera_fecha,
                    DATE_FORMAT(MAX(fecha_registro), '%d/%m/%Y') as ultima_fecha
                FROM registro_medico";

        $resultado = $this->connection->query($sql);
        $estadisticas = $resultado->fetch_assoc();
        
        if (is_object($resultado)) {
            $resultado->free();
        }
        
        return $estadisticas;
    }

    /**
     * Busca registros médicos por término de búsqueda
     * @param string $termino
     * @return array
     */
    public function buscarRegistros($termino)
    {
        $sql = "SELECT 
                    rm.registro_medico_id,
                    rm.historia_clinica_id,
                    rm.fecha_registro,
                    rm.motivo_consulta,
                    rm.enfermedad_actual,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) AS nombre_paciente,
                    CONCAT(u_trat.nombre, ' ', u_trat.apellido_paterno) AS nombre_tratante
                FROM registro_medico rm
                JOIN historia_clinica hc ON rm.historia_clinica_id = hc.historia_clinica_id
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                JOIN usuarios u_trat ON hc.dr_tratante_id = u_trat.id_usuario
                WHERE rm.motivo_consulta LIKE ? 
                   OR rm.enfermedad_actual LIKE ? 
                   OR rm.signos_sintomas LIKE ?
                   OR u_pac.nombre LIKE ? 
                   OR u_pac.apellido_paterno LIKE ?
                ORDER BY rm.fecha_registro DESC";

        $stmt = $this->connection->prepare($sql);
        $terminoLike = "%" . $termino . "%";
        $stmt->bind_param("sssss", 
            $terminoLike, 
            $terminoLike, 
            $terminoLike, 
            $terminoLike, 
            $terminoLike
        );
        $stmt->execute();
        $resultado = $stmt->get_result();
        $registros = [];
        
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $registros[] = $fila;
            }
        }
        $stmt->close();
        return $registros;
    }

    /**
     * Obtiene el último ID insertado (para confirmación después de inserción)
     * @return int
     */
    public function obtenerUltimoIdInsertado()
    {
        return $this->connection->insert_id;
    }

    /**
     * Verifica si un registro médico existe
     * @param int $idRegistro
     * @return bool
     */
    public function existeRegistro($idRegistro)
    {
        $sql = "SELECT COUNT(*) as count FROM registro_medico WHERE registro_medico_id = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idRegistro);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['count'] > 0;
    }

    /**
     * Obtiene el conteo de registros por mes para gráficos
     * @param int $mes
     * @param int $año
     * @return int
     */
    public function obtenerConteoPorMes($mes, $año)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM registro_medico 
                WHERE MONTH(fecha_registro) = ? AND YEAR(fecha_registro) = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("ii", $mes, $año);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $fila = $resultado->fetch_assoc();
        $stmt->close();
        
        return $fila['total'];
    }

    /**
     * Obtiene el próximo ID disponible para registro médico
     */
    private function obtenerProximoIdRegistro()
    {
        $sql = "SELECT COALESCE(MAX(registro_medico_id), 0) + 1 as next_id FROM registro_medico";
        $resultado = $this->connection->query($sql);
        
        if ($resultado && $fila = $resultado->fetch_assoc()) {
            return $fila['next_id'];
        }
        
        return 1;
    }
}
?>