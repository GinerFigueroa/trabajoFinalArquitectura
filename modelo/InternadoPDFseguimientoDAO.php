<?php
include_once('conexion.php');

class InternadoPDFseguimientoDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene el internado completo con todos los datos relacionados
     */
    public function obtenerInternadoCompleto($idInternado)
    {
        $sql = "SELECT 
                    i.*,
                    -- Datos del Paciente
                    CONCAT(up.nombre, ' ', up.apellido_paterno, ' ', up.apellido_materno) AS nombre_completo_paciente,
                    p.dni AS dni_paciente,
                    p.fecha_nacimiento,
                    p.sexo,
                    p.domicilio,
                    up.telefono,
                    
                    -- Datos de la Habitación
                    h.numero_puerta AS habitacion_numero,
                    h.piso AS habitacion_piso,
                    h.tipo AS habitacion_tipo,
                    
                    -- Datos del Médico Tratante
                    CONCAT(um.nombre, ' ', um.apellido_paterno, ' ', um.apellido_materno) AS nombre_medico,
                    em.nombre AS especialidad_medico
                    
                FROM internados i
                JOIN pacientes p ON i.id_paciente = p.id_paciente
                JOIN usuarios up ON p.id_usuario = up.id_usuario
                JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
                LEFT JOIN medicos m ON i.id_medico = m.id_medico
                LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
                LEFT JOIN especialidades_medicas em ON m.id_especialidad = em.id_especialidad
                WHERE i.id_internado = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idInternado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $internado = $resultado->fetch_assoc();
        $stmt->close();
        
        return $internado;
    }

    /**
     * Obtiene todos los seguimientos del internado ordenados por fecha
     */
    public function obtenerSeguimientosPorInternado($idInternado)
    {
        $sql = "SELECT 
                    isg.*,
                    CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico,
                    CONCAT(ue.nombre, ' ', ue.apellido_paterno) AS nombre_enfermera,
                    em.nombre AS especialidad_medico
                FROM internados_seguimiento isg
                LEFT JOIN usuarios um ON isg.id_medico = um.id_usuario
                LEFT JOIN usuarios ue ON isg.id_enfermera = ue.id_usuario
                LEFT JOIN medicos m ON isg.id_medico = m.id_medico
                LEFT JOIN especialidades_medicas em ON m.id_especialidad = em.id_especialidad
                WHERE isg.id_internado = ?
                ORDER BY isg.fecha ASC, isg.id_seguimiento ASC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idInternado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $seguimientos = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        return $seguimientos;
    }

    /**
     * Obtiene información adicional del paciente
     */
    public function obtenerInfoAdicionalPaciente($idPaciente)
    {
        $sql = "SELECT 
                    p.*,
                    up.telefono,
                    up.email,
                    p.domicilio,
                    p.distrito,
                    p.ocupacion,
                    p.estado_civil
                FROM pacientes p
                JOIN usuarios up ON p.id_usuario = up.id_usuario
                WHERE p.id_paciente = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $info = $resultado->fetch_assoc();
        $stmt->close();
        
        return $info;
    }

    /**
     * Calcula los días de internamiento
     */
    public function calcularDiasInternado($idInternado)
    {
        $sql = "SELECT 
                    fecha_ingreso,
                    fecha_alta,
                    DATEDIFF(COALESCE(fecha_alta, NOW()), fecha_ingreso) AS dias_internado
                FROM internados 
                WHERE id_internado = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idInternado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $datos = $resultado->fetch_assoc();
        $stmt->close();
        
        return $datos ? $datos['dias_internado'] : 0;
    }

    /**
     * Obtiene datos combinados para reportes
     */
    public function obtenerDatosCombinados($idInternado)
    {
        $internado = $this->obtenerInternadoCompleto($idInternado);
        $seguimientos = $this->obtenerSeguimientosPorInternado($idInternado);
        
        if ($internado) {
            $infoPaciente = $this->obtenerInfoAdicionalPaciente($internado['id_paciente']);
            $diasInternado = $this->calcularDiasInternado($idInternado);
            
            return [
                'internado' => $internado,
                'seguimientos' => $seguimientos,
                'info_paciente' => $infoPaciente,
                'dias_internado' => $diasInternado,
                'total_seguimientos' => count($seguimientos)
            ];
        }
        
        return null;
    }
}
?>