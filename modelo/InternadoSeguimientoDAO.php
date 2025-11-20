<?php
include_once('conexion.php'); 
include_once('EntidadAuxiliarDAO.php');

class InternadoSeguimientoDAO
{
    private $connection;
    private $objAuxiliar;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
        $this->objAuxiliar = new EntidadAuxiliarDAO();
    }

    /**
     * Obtiene todos los seguimientos, con los nombres completos de Paciente, Médico y Enfermera.
     * @return array
     */
    public function obtenerTodosSeguimientos()
    {
        $sql = "SELECT id_seguimiento, id_internado, fecha, id_medico, id_enfermera, evolucion, tratamiento
                FROM internados_seguimiento
                ORDER BY fecha DESC";
        
        $resultado = $this->connection->query($sql);
        $seguimientos = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                // Obtener nombres auxiliares
                $fila['nombre_paciente'] = $this->objAuxiliar->obtenerNombrePacientePorInternado($fila['id_internado']);
                $fila['nombre_medico'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($fila['id_medico']);
                $fila['nombre_enfermera'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($fila['id_enfermera']);
                $seguimientos[] = $fila;
            }
        }
        return $seguimientos;
    }

    /**
     * Obtiene un seguimiento específico por ID.
     * @param int $idSeguimiento
     * @return array|null
     */
    public function obtenerSeguimientoPorId($idSeguimiento)
    {
        $sql = "SELECT * FROM internados_seguimiento WHERE id_seguimiento = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idSeguimiento);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $seguimiento = $resultado->fetch_assoc();
        $stmt->close();

        if ($seguimiento) {
             // Opcional: enriquecer con nombres para la vista de edición
             $seguimiento['nombre_medico'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($seguimiento['id_medico']);
             $seguimiento['nombre_enfermera'] = $this->objAuxiliar->obtenerNombrePersonalPorIdUsuario($seguimiento['id_enfermera']);
             $seguimiento['nombre_paciente'] = $this->objAuxiliar->obtenerNombrePacientePorInternado($seguimiento['id_internado']);
        }
        return $seguimiento;
    }

    /**
     * Registra un nuevo seguimiento.
     * @return bool
     */
    public function registrarSeguimiento($idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento)
    {
        $sql = "INSERT INTO internados_seguimiento (id_internado, id_medico, id_enfermera, evolucion, tratamiento)
                VALUES (?, ?, ?, ?, ?)";

        $stmt = $this->connection->prepare($sql);
        // Tipos: i = integer, s = string. Asumimos que evolucion y tratamiento son strings largos.
        $stmt->bind_param("iiiss", $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Edita un seguimiento existente.
     * @return bool
     */
    public function editarSeguimiento($idSeguimiento, $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento)
    {
        $sql = "UPDATE internados_seguimiento 
                SET id_internado = ?, id_medico = ?, id_enfermera = ?, evolucion = ?, tratamiento = ?
                WHERE id_seguimiento = ?";

        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("iiissi", $idInternado, $idMedico, $idEnfermera, $evolucion, $tratamiento, $idSeguimiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    /**
     * Elimina un seguimiento de la base de datos.
     * @return bool
     */
    public function eliminarSeguimiento($idSeguimiento)
    {
        $stmt = $this->connection->prepare("DELETE FROM internados_seguimiento WHERE id_seguimiento = ?");
        $stmt->bind_param("i", $idSeguimiento);
        
        $resultado = $stmt->execute();
        $stmt->close();
        return $resultado;
    }

    public function obtenerSeguimientosPorMedicoTratante($idMedico)
    {
        $sql = "SELECT 
                    isg.id_seguimiento,
                    isg.id_internado,
                    isg.fecha,
                    isg.id_medico,
                    isg.id_enfermera,
                    isg.evolucion,
                    isg.tratamiento,
                    i.id_paciente,
                    i.diagnostico_ingreso,
                    i.estado as estado_internado,
                    h.numero_puerta,
                    h.tipo as tipo_habitacion,
                    CONCAT(u_pac.nombre, ' ', u_pac.apellido_paterno, ' ', u_pac.apellido_materno) as nombre_paciente,
                    CONCAT(u_med.nombre, ' ', u_med.apellido_paterno) as nombre_medico,
                    CONCAT(u_enf.nombre, ' ', u_enf.apellido_paterno) as nombre_enfermera
                FROM internados_seguimiento isg
                INNER JOIN internados i ON isg.id_internado = i.id_internado
                INNER JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
                INNER JOIN pacientes p ON i.id_paciente = p.id_paciente
                INNER JOIN usuarios u_pac ON p.id_usuario = u_pac.id_usuario
                LEFT JOIN usuarios u_med ON isg.id_medico = u_med.id_usuario
                LEFT JOIN usuarios u_enf ON isg.id_enfermera = u_enf.id_usuario
                WHERE i.id_medico = ?
                ORDER BY isg.fecha DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $seguimientos = [];

        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $seguimientos[] = $fila;
            }
        }
        $stmt->close();
        
        return $seguimientos;
    }

    /**
     * Obtiene el ID del médico a partir del ID de usuario
     */
    public function obtenerIdMedicoPorIdUsuario($idUsuario)
    {
        $sql = "SELECT id_medico FROM medicos WHERE id_usuario = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $medico = $resultado->fetch_assoc();
        $stmt->close();

        return $medico ? $medico['id_medico'] : null;
    }
}
?>
}