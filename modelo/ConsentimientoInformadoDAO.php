<?php
include_once('conexion.php'); 
class ConsentimientoInformadoDAO
{
    private $connection; 

    public function __construct()
    {
        // Se asume el uso de una clase Conexion Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // --- CRUD Y OBTENCIÓN ---

public function obtenerTodosConsentimientos()
{
    $conn = $this->connection;
    
    $sql = "SELECT 
                ci.consentimiento_id,
                ci.historia_clinica_id,
                ci.diagnostico_descripcion,
                ci.fecha_firma,
                -- CORRECCIÓN: JOIN directo con usuarios
                CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_medico, 
                CONCAT(up.nombre, ' ', up.apellido_paterno, ' (HC:', ci.historia_clinica_id, ')') AS nombre_paciente_hc
            FROM consentimiento_informado ci
            -- CORRECCIÓN: JOIN directo con usuarios para el médico
            LEFT JOIN usuarios um ON ci.dr_tratante_id = um.id_usuario
            LEFT JOIN historia_clinica hc ON ci.historia_clinica_id = hc.historia_clinica_id 
            LEFT JOIN pacientes p ON hc.id_paciente = p.id_paciente
            LEFT JOIN usuarios up ON p.id_usuario = up.id_usuario
            ORDER BY ci.fecha_firma DESC";
    
    $resultado = $conn->query($sql);
    
    if ($conn->error) {
        die("❌ ERROR FATAL DE SQL: " . $conn->error); 
    }
    
    $consentimientos = [];
    if ($resultado && $resultado->num_rows > 0) {
        while ($fila = $resultado->fetch_assoc()) {
            $consentimientos[] = $fila;
        }
    }
    
    return $consentimientos;
}
    
   public function obtenerConsentimientoPorId($id)
{
    $conn = $this->connection;
    
    $sql = "SELECT 
                ci.*,
                CONCAT(up.nombre, ' ', up.apellido_paterno, ' ', up.apellido_materno) AS nombre_paciente_completo,
                CONCAT(um.nombre, ' ', um.apellido_paterno, ' ', up.apellido_materno) AS nombre_medico_completo,
                hc.historia_clinica_id,
                p.id_paciente
            FROM consentimiento_informado ci
            -- CORRECCIÓN: JOIN directo con usuarios para el médico
            JOIN usuarios um ON ci.dr_tratante_id = um.id_usuario
            JOIN historia_clinica hc ON ci.historia_clinica_id = hc.historia_clinica_id 
            JOIN pacientes p ON hc.id_paciente = p.id_paciente
            JOIN usuarios up ON p.id_usuario = up.id_usuario
            WHERE ci.consentimiento_id = ?";
    
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die('Error en la preparación: ' . $conn->error);
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $consentimiento = $resultado->fetch_assoc();
    
    $stmt->close();
    return $consentimiento;
}

    public function registrarConsentimiento($historia_clinica_id, $id_paciente, $dr_tratante_id, $diagnostico, $tratamiento)
    {
        $conn = $this->connection;
        $resultado = false;
        
        $sql = "INSERT INTO consentimiento_informado 
                (historia_clinica_id, id_paciente, dr_tratante_id, diagnostico_descripcion, tratamiento_descripcion) 
                VALUES (?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        // Tipos: i i i s s
        $stmt->bind_param("iiiss", $historia_clinica_id, $id_paciente, $dr_tratante_id, $diagnostico, $tratamiento);

        if ($stmt->execute()) {
            $resultado = true;
        }

        $stmt->close();
        return $resultado;
    }

    public function editarConsentimiento($id, $diagnostico, $tratamiento)
    {
        $conn = $this->connection;
        $resultado = false;

        // Nota: No permitimos editar HC o Dr. Tratante una vez creado.
        $sql = "UPDATE consentimiento_informado SET diagnostico_descripcion = ?, tratamiento_descripcion = ? WHERE consentimiento_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $diagnostico, $tratamiento, $id);
        
        if ($stmt->execute()) {
            $resultado = $stmt->affected_rows > 0;
        }

        $stmt->close();
        return $resultado;
    }

    public function eliminarConsentimiento($id)
    {
        $conn = $this->connection;
        $resultado = false;

        $sql = "DELETE FROM consentimiento_informado WHERE consentimiento_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $resultado = $stmt->affected_rows > 0;
        }

        $stmt->close();
        return $resultado;
    }
}

// =================================================================
// 2. CLASE AUXILIAR: EntidadHistoriaClinica (Para selectores)
// =================================================================

class EntidadHistoriaClinica 
{
    private $connection;
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

   public function obtenerHistoriasClinicasDisponibles()
    {
        $conn = $this->connection;
        // CORRECCIÓN: Cambiado 'historias_clinicas' a 'historia_clinica'
        $sql = "SELECT  hc.historia_clinica_id, p.id_paciente, CONCAT(u.nombre, 
        ' ', u.apellido_paterno) AS nombre_paciente 
               FROM historia_clinica hc 
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY hc.historia_clinica_id DESC";
        
        $resultado = $conn->query($sql);
        $historias = [];
        
        if ($resultado) {
            while ($fila = $resultado->fetch_assoc()) {
                $historias[] = $fila;
            }
        }
        
        return $historias;
    }
    
    public function obtenerInfoPorHistoriaClinica($historia_clinica_id)
    {
        $conn = $this->connection;
        // CORRECCIÓN: Cambiado 'historias_clinicas' a 'historia_clinica'
        $sql = "SELECT p.id_paciente, CONCAT(u.nombre, ' ', u.apellido_paterno, ' ', u.apellido_materno) AS nombre_completo
                FROM historia_clinica hc 
                JOIN pacientes p ON hc.id_paciente = p.id_paciente
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                WHERE hc.historia_clinica_id = ?";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $historia_clinica_id);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $info = $resultado->fetch_assoc();
        $stmt->close();
        return $info;
    }
}

// =================================================================
// 3. CLASE AUXILIAR: EntidadMedico (Para selectores)
// =================================================================

class EntidadMedico 
{
    private $connection;
    public function __construct()
    {
        $this->connection = Conexion::getInstancia()->getConnection();
    }

  // En la clase EntidadMedico
public function obtenerMedicosDisponibles()
{
    $conn = $this->connection;
    
    $sql = "SELECT 
                u.id_usuario AS id_dr_fk,  -- ⬅️ ¡CORRECCIÓN CLAVE! Usamos el ID de USUARIO
                CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_medico 
            FROM medicos m
            JOIN usuarios u ON m.id_usuario = u.id_usuario
            WHERE u.activo = 1
            ORDER BY u.apellido_paterno";
    
    $resultado = $conn->query($sql);
    $medicos = [];
    
    if ($resultado) {
        $medicos = $resultado->fetch_all(MYSQLI_ASSOC);
    }
    
    return $medicos;
}
}
?>