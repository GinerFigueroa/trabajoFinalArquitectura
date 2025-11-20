<?php
// Path: C:\xampp\htdocs\TRABAJOFINALARQUITECTURA\modelo\CitasPacienteDAO.php

// Asegúrate de que esta ruta sea correcta
include_once('Conexion.php'); 

class CitasPacienteDAO
{
    private $connection;

    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    /**
     * Obtiene todas las citas de un paciente específico, incluyendo detalles del médico y tratamiento.
     * @param int $idUsuario El ID del usuario (paciente) logueado.
     * @return array
     */
    public function obtenerCitasPorPaciente($idUsuario)
    {
        // CONSULTA FINAL Y FUNCIONAL: Se usa la tabla 'medicos' y se unen las 3 tablas.
        $sql = "SELECT 
                    c.id_cita, 
                    c.fecha_hora, 
                    c.estado, 
                    t.nombre AS nombre_tratamiento, 
                    u_medico.nombre AS nombre_medico, 
                    u_medico.apellido_paterno AS apellido_paterno
                FROM citas c
                LEFT JOIN tratamientos t ON c.id_tratamiento = t.id_tratamiento
                INNER JOIN pacientes p ON c.id_paciente = p.id_paciente 
                
                -- UNIÓN CLAVE 1: De citas (id_medico) a la tabla 'medicos' (id_medico)
                INNER JOIN medicos m ON c.id_medico = m.id_medico
                -- UNIÓN CLAVE 2: De 'medicos' (id_usuario) a la tabla 'usuarios' (id_usuario)
                INNER JOIN usuarios u_medico ON m.id_usuario = u_medico.id_usuario
                
                WHERE p.id_usuario = ? 
                ORDER BY c.fecha_hora DESC";

        if (!$this->connection) {
            return [];
        }

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
            return [];
        }

        $stmt->bind_param("i", $idUsuario); 
        
        if (!$stmt->execute()) {
            $stmt->close();
            return [];
        }

        $resultado = $stmt->get_result();
        $citas = $resultado->fetch_all(MYSQLI_ASSOC); 
        $stmt->close();

        return $citas;
    }
    
    /**
     * Obtiene la lista de pacientes activos para módulos de agendamiento/búsqueda.
     */
    public function obtenerPacientesDisponibles()
    {
        $sql = "SELECT p.id_paciente, p.dni, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
}
?>