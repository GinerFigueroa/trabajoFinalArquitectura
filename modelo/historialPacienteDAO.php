<?php
// C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\HistorialDAO.php
include_once('Conexion.php'); 

class HistorialDAO
{
    private $connection;

    public function __construct() {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene los datos principales del historial clínico de un paciente.
     * El 'idPaciente' es el ID de la tabla 'pacientes' (pacientes.id_paciente).
     * @param int $idPaciente El ID del paciente.
     * @return array|null
     */
    public function obtenerResumenHistorialPorPaciente($idPaciente)
    {
        // Corregido para coincidir con la estructura de la base de datos 'opipitaltrabajo'
        $sql = "SELECT 
                    hc.historia_clinica_id, 
                    hc.fecha_creacion, 
                    a.alergias, 
                    -- Concatenamos las enfermedades más relevantes de la anamnesis
                    CONCAT_WS('; ', 
                        NULLIF(a.enfermedades_pulmonares, ''), 
                        NULLIF(a.enfermedades_cardiacas, ''), 
                        NULLIF(a.otras_enfermedades, '')
                    ) AS enfermedades_previas, 
                    -- Concatenamos los diagnósticos definitivos
                    GROUP_CONCAT(d.diagnostico_definitivo SEPARATOR ' | ') AS ultimos_diagnosticos
                FROM historia_clinica hc
                INNER JOIN pacientes p ON hc.id_paciente = p.id_paciente
                -- La anamnesis contiene las alergias y enfermedades previas
                LEFT JOIN anamnesis a ON hc.historia_clinica_id = a.historia_clinica_id
                -- El diagnóstico contiene los resultados finales
                LEFT JOIN diagnostico d ON hc.historia_clinica_id = d.historia_clinica_id
                WHERE p.id_usuario = ? -- Asumimos que $idPaciente es el id_usuario logueado
                GROUP BY hc.historia_clinica_id";

        $stmt = $this->connection->prepare($sql);
        // Verificar si la preparación fue exitosa
        if (!$stmt) {
            error_log("Error al preparar la consulta de Historial: " . $this->connection->error);
            return null;
        }
        
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        // Debe ser un fetch_assoc() único porque solo hay un historial por paciente
        $historial = $resultado->fetch_assoc();
        $stmt->close();

        return $historial;
    }
}
?>