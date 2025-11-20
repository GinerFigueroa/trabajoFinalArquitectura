<?php
// Archivo: modelo/InternadoDAO.php (Contiene la lógica transaccional de Internados)

include_once('conexion.php'); // Asume que 'conexion.php' contiene la clase Singleton Conexion

/**
 * Clase InternadoDAO (Data Access Object)
 * Responsable de las operaciones CRUD y transaccionales de la entidad 'internados'.
 */
class InternadoDAO
{
    private $connection;

    public function __construct()
    {
        // Se obtiene la ÚNICA instancia de la conexión a través del Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }

    // --- OPERACIONES CRUD PRINCIPALES ---
// Archivo: modelo/InternadoDAO.php

// ... (dentro de la clase InternadoDAO)

// En modelo/InternadoDAO.php (dentro de la clase InternadoDAO)

public function obtenerTodosInternados()
{
    $sql = "SELECT 
                i.*,
                -- Datos del Paciente
                CONCAT(up.nombre, ' ', up.apellido_paterno, ' ', up.apellido_materno) AS nombre_completo_paciente,
                p.dni AS dni_paciente,
                
                -- Datos de la Habitación (CORREGIDO)
                h.numero_puerta AS habitacion_numero, 
                
                -- Datos del Médico
                CONCAT(um.nombre, ' ', um.apellido_paterno) AS nombre_completo_medico,
                
                -- Especialidad del médico
                em.nombre AS especialidad_medico
            FROM internados i
            JOIN pacientes p ON i.id_paciente = p.id_paciente
            JOIN usuarios up ON p.id_usuario = up.id_usuario
            JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
            LEFT JOIN medicos m ON i.id_medico = m.id_medico
            LEFT JOIN usuarios um ON m.id_usuario = um.id_usuario
            LEFT JOIN especialidades_medicas em ON m.id_especialidad = em.id_especialidad
            ORDER BY i.fecha_ingreso DESC";
    
    $resultado = $this->connection->query($sql);
    
    if (!$resultado) {
        error_log("Error en obtenerTodosInternados: " . $this->connection->error);
        return [];
    }
    
    return $resultado->fetch_all(MYSQLI_ASSOC);
}
    
    public function obtenerInternadoPorId($idInternado)
    {
        $sql = "SELECT 
                    i.*, 
                    p.id_usuario, 
                    h.numero_puerta AS habitacion_numero,
                    CONCAT(up.nombre, ' ', up.apellido_paterno, ' ', up.apellido_materno) AS nombre_paciente
                FROM internados i
                JOIN pacientes p ON i.id_paciente = p.id_paciente
                JOIN usuarios up ON p.id_usuario = up.id_usuario
                JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
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
     * Registra un nuevo internamiento (Transaccional: Insertar internado + Ocupar habitación).
     * @return bool
     */
    public function registrarInternado($idPaciente, $idHabitacion, $idMedico, $fechaIngreso, $diagnostico, $observaciones)
{
        $conn = $this->connection;
        $conn->begin_transaction();
        $resultado = false;

        try {
            // 1. Insertar el registro de internamiento
           $sqlInternado = "INSERT INTO internados (id_paciente, id_habitacion, id_medico, fecha_ingreso, diagnostico_ingreso, observaciones, estado)
                 VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
            
            $stmtInternado = $conn->prepare($sqlInternado);
            $stmtInternado->bind_param("iiisss", $idPaciente, $idHabitacion, $idMedico, $fechaIngreso, $diagnostico, $observaciones);
            
            $stmtInternado->execute(); 
            $stmtInternado->close(); // Cerrar statement
            
            // 2. Actualizar el estado de la habitación a 'Ocupada'
            $sqlHabitacion = "UPDATE habitaciones SET estado = 'Ocupada' WHERE id_habitacion = ?";
            $stmtHabitacion = $conn->prepare($sqlHabitacion);
            $stmtHabitacion->bind_param("i", $idHabitacion);
            
            $stmtHabitacion->execute();
            $stmtHabitacion->close(); // Cerrar statement
            
            $conn->commit();
            $resultado = true;
            
        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction error in registrarInternado: " . $e->getMessage()); // Logueo de error
            $resultado = false;
        }

        return $resultado;
    }
// Dentro de la clase InternadoDAO

// Dentro de la clase InternadoDAO

/**
 * Edita un internado y gestiona el estado de las habitaciones de forma transaccional.
 *
 * @return bool True si la edición y gestión de habitaciones fue exitosa (COMMIT), False si falló (ROLLBACK).
 */
public function editarInternado(
    $idInternado, 
    $idHabitacionNueva, 
    $idMedico, 
    $fechaAlta, 
    $diagnosticoIngreso, 
    $observaciones, 
    $estado, 
    $idHabitacionAnterior
) {
    $conn = $this->connection;
    $conn->begin_transaction();

    try {
        // --- PARTE 1: Actualizar la tabla 'internados' (Corregida) ---
        // Se eliminan campos que no existen en la tabla (diagnostico_egreso, id_enfermero)
        $sqlInternado = "UPDATE internados SET 
            id_habitacion = ?, 
            id_medico = ?, 
            fecha_alta = ?, 
            diagnostico_ingreso = ?, 
            observaciones = ?, 
            estado = ?
        WHERE id_internado = ?";
        
        $stmtInternado = $conn->prepare($sqlInternado);
        
        // Cadena de tipos inicial: i=idHabitacion, i=idMedico, s=fechaAlta, s=diagnosticoIngreso, s=observaciones, s=estado, i=idInternado
        $tipos = "iissisi"; 
        
        // Array de variables (deben ser referencias para bind_param)
        $params = [
            &$idHabitacionNueva,
            &$idMedico,
            &$fechaAlta, // ⚠️ El problema de NULL ocurre aquí (índice 2)
            &$diagnosticoIngreso,
            &$observaciones,
            &$estado,
            &$idInternado
        ];
        
        // GESTIÓN DEL NULL PARA $fechaAlta (índice 2, tipo 's')
        // Si $fechaAlta es NULL, no es necesario cambiar el tipo 's' ya que mysqli lo maneja.
        // PERO, si $fechaAlta es un string vacío (''), y el campo es DATETIME, es mejor pasar NULL.
        // Vamos a asegurar que si es un string vacío, lo tratemos como NULL.
        if (empty($fechaAlta)) {
            $fechaAlta = null; // Aseguramos que el valor sea NULL para la DB
        }
        // Ya que el tipo es 's' (string), mysqli es más indulgente y acepta NULL.
        // Si tienes problemas de ejecución, usa call_user_func_array, que es más seguro.

        // Usamos la forma estándar (más limpia si no hay error de NULL en el 's')
        $stmtInternado->bind_param(
            $tipos, 
            $idHabitacionNueva,
            $idMedico,
            $fechaAlta, // Si es NULL, se espera que mysqli lo maneje ya que el tipo es 's'
            $diagnosticoIngreso,
            $observaciones,
            $estado,
            $idInternado
        );

        $executeSuccess = $stmtInternado->execute();
        $stmtInternado->close();

        if (!$executeSuccess) {
             throw new Exception("Fallo al actualizar internado: " . $stmtInternado->error);
        }
        
        // --- PARTE 2: Gestionar el estado de las habitaciones ---
        
        // Lógica de liberación: Si la habitación antigua es diferente de la nueva, o si el paciente fue dado de alta
        $debeLiberarAnterior = ($idHabitacionNueva != $idHabitacionAnterior) || ($estado != 'Activo');

        if ($debeLiberarAnterior) {
            $sqlLiberarAnterior = "UPDATE habitaciones SET estado = 'Disponible' WHERE id_habitacion = ?";
            $stmtLiberarAnterior = $conn->prepare($sqlLiberarAnterior);
            $stmtLiberarAnterior->bind_param("i", $idHabitacionAnterior);
            
            $executeLiberar = $stmtLiberarAnterior->execute();
            $stmtLiberarAnterior->close();
            
            if (!$executeLiberar) {
                throw new Exception("Fallo al liberar habitacion: " . $stmtLiberarAnterior->error);
            }
        }

        // Lógica de ocupación: Si el internado sigue ACTIVO Y se cambió a una nueva habitación.
        if ($estado == 'Activo' && $idHabitacionNueva != $idHabitacionAnterior) {
            $sqlOcuparNueva = "UPDATE habitaciones SET estado = 'Ocupada' WHERE id_habitacion = ?";
            $stmtOcuparNueva = $conn->prepare($sqlOcuparNueva);
            $stmtOcuparNueva->bind_param("i", $idHabitacionNueva);
            
            $executeOcupar = $stmtOcuparNueva->execute();
            $stmtOcuparNueva->close();
            
            if (!$executeOcupar) {
                throw new Exception("Fallo al ocupar nueva habitacion: " . $stmtOcuparNueva->error);
            }
        }
        
        // 3. Confirmar la transacción
        $conn->commit();
        return true;

    } catch (Exception $e) {
        // En caso de cualquier error (incluido el error de execute), deshacer todos los cambios
        $conn->rollback();
        // error_log("Error en editarInternado: " . $e->getMessage()); 
        return false;
    }
}

    /**
     * Elimina un internamiento y libera la habitación (Transaccional).
     * @return bool
     */
    public function eliminarInternado($idInternado)
    {
        $conn = $this->connection;
        $conn->begin_transaction();
        $resultado = false;
        $idHabitacion = null;

        try {
            // 1. Obtener datos para liberar la habitación SOLO si el estado es Activo
            $sqlGetHab = "SELECT id_habitacion, estado FROM internados WHERE id_internado = ?";
            $stmtGetHab = $conn->prepare($sqlGetHab);
            $stmtGetHab->bind_param("i", $idInternado);
            $stmtGetHab->execute();
            $resultGetHab = $stmtGetHab->get_result();
            $internado = $resultGetHab->fetch_assoc();
            
            if ($internado && $internado['estado'] == 'Activo') {
                $idHabitacion = $internado['id_habitacion'];
            }
            $stmtGetHab->close();

            // 2. Eliminar el registro
            $sqlDelete = "DELETE FROM internados WHERE id_internado = ?";
            $stmtDelete = $conn->prepare($sqlDelete);
            $stmtDelete->bind_param("i", $idInternado);
            $stmtDelete->execute();
            $stmtDelete->close();

            // 3. Liberar la habitación si era Activo
            if ($idHabitacion) {
                $sqlLiberar = "UPDATE habitaciones SET estado = 'Disponible' WHERE id_habitacion = ?";
                $stmtLiberar = $conn->prepare($sqlLiberar);
                $stmtLiberar->bind_param("i", $idHabitacion);
                $stmtLiberar->execute();
                $stmtLiberar->close();
            }
            
            $conn->commit();
            $resultado = true;

        } catch (Exception $e) {
            $conn->rollback();
            error_log("Transaction error in eliminarInternado: " . $e->getMessage()); // Logueo de error
            $resultado = false;
        }
        
        return $resultado;
    }

    // --- VALIDACIONES Y CONSULTAS SECUNDARIAS ---

    public function pacienteYaInternado($idPaciente)
    {
        $sql = "SELECT COUNT(*) FROM internados WHERE id_paciente = ? AND estado = 'Activo'";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function habitacionDisponible($idHabitacion)
    {
        $sql = "SELECT estado FROM habitaciones WHERE id_habitacion = ?";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idHabitacion);
        $stmt->execute();
        $stmt->bind_result($estado);
        $stmt->fetch();
        $stmt->close();
        return $estado === 'Disponible';
    }


    //--CONSULTA---PARA OBTENR--ORDEN-FACTURA
    public function obtenerInternamientosPorPaciente($idPaciente)
    {
        $sql = "SELECT 
                    i.id_internado, i.fecha_ingreso, i.estado, h.numero_puerta AS habitacion_numero
                FROM internados i
                JOIN habitaciones h ON i.id_habitacion = h.id_habitacion
                WHERE i.id_paciente = ? AND (i.estado = 'Activo' OR i.estado = 'Alta' OR i.estado = 'Derivado' OR i.estado = 'Fallecido')
                ORDER BY i.fecha_ingreso DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $internados = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();

        return $internados;
    }
    /**
     * Obtiene la fecha de ingreso de un internado específico.
     * @param int $idInternado El ID del internado.
     * @return string|null La fecha de ingreso en formato de base de datos o NULL.
     */
    public function obtenerFechaIngreso($idInternado)
    {
        $sql = "SELECT fecha_ingreso FROM internados WHERE id_internado = ?";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idInternado);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $fecha = null;
        if ($fila = $resultado->fetch_assoc()) {
            $fecha = $fila['fecha_ingreso'];
        }
        
        $stmt->close();
        return $fecha;
    }
    
//------------------para el tabla internado---
// En modelo/InternadoDAO.php (dentro de la clase InternadoDAO)

/**
 * Obtiene todos los usuarios con rol de paciente.
 * NOTA: Es más eficiente usar InternadoAuxiliarDAO si existe.
 * @return array
 */

 public function obtenerTodosUsuariosPacientes()
    {
        $sql = "SELECT p.*, u.nombre, u.apellido_paterno, u.apellido_materno, u.telefono, u.email, u.usuario_usuario, u.activo
                FROM pacientes p
                LEFT JOIN usuarios u ON p.id_usuario = u.id_usuario
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

// También necesitarás:
public function obtenerTodasHabitaciones()
{
    // Esta consulta es crucial. Solo debería listar 'Disponible' para un registro nuevo.
    $sql = "SELECT id_habitacion, numero_puerta as numero FROM habitaciones WHERE estado = 'Disponible'";
    
    $resultado = $this->connection->query($sql);
    
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}

public function obtenerTodosMedicos()
{
    $sql = "SELECT u.id_usuario, u.nombre, u.apellido_paterno
            FROM medicos m
            JOIN usuarios u ON m.id_usuario = u.id_usuario
            WHERE u.activo = 1
            ORDER BY u.apellido_paterno, u.nombre";

    $resultado = $this->connection->query($sql);
    
    return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
}
}




/**
 * Clase InternadoAuxiliarDAO (Data Access Object)
 * Responsable de obtener listas de datos auxiliares (lookups) para los formularios de internados.
 * Se renombró de EntidadInternadoDAO para reflejar mejor su propósito.
 */
class InternadoAuxiliarDAO
{
    private $connection;
    
    public function __construct()
    {
        // Se obtiene la ÚNICA instancia de la conexión a través del Singleton
        $this->connection = Conexion::getInstancia()->getConnection();
    }
    
    public function obtenerPacientesNoInternados()
    {
        $sql = "SELECT p.id_paciente, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo, p.dni
                FROM pacientes p
                JOIN usuarios u ON p.id_usuario = u.id_usuario
                LEFT JOIN internados i ON p.id_paciente = i.id_paciente AND i.estado = 'Activo'
                WHERE i.id_internado IS NULL AND u.activo = 1
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerMedicos()
    {
        $sql = "SELECT m.id_medico, CONCAT(u.nombre, ' ', u.apellido_paterno) AS nombre_completo
                FROM medicos m
                JOIN usuarios u ON m.id_usuario = u.id_usuario
                WHERE u.activo = 1
                ORDER BY u.apellido_paterno, u.nombre";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function obtenerHabitacionesDisponibles()
    {
        $sql = "SELECT id_habitacion, numero_puerta, piso, tipo 
                FROM habitaciones 
                WHERE estado = 'Disponible' 
                ORDER BY piso, numero_puerta"; 
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    /**
     * **MÉTODO FALTANTE (Agregado):** Obtiene habitaciones disponibles y asegura que la habitación actual
     * del internado se incluya, incluso si está 'Ocupada'.
     * @param int $idHabitacionActual El ID de la habitación que el paciente ocupa actualmente.
     * @return array Lista de habitaciones.
     */
    public function obtenerHabitacionesDisponiblesConActual($idHabitacionActual)
    {
        // Se obtiene la habitación actual (que estará Ocupada) y todas las Disponibles.
        $sql = "SELECT id_habitacion, numero_puerta, piso, tipo, estado 
                FROM habitaciones 
                WHERE estado = 'Disponible' OR id_habitacion = ?
                ORDER BY piso, numero_puerta"; 
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idHabitacionActual);
        $stmt->execute();
        $resultado = $stmt->get_result();
        
        $habitaciones = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        $stmt->close();
        
        return $habitaciones;
    }


    public function obtenerTodasHabitaciones()
    {
        $sql = "SELECT id_habitacion, numero_puerta, piso, tipo, estado 
                FROM habitaciones 
                ORDER BY piso, numero_puerta";
        
        $resultado = $this->connection->query($sql);
        
        return $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function pacienteExiste($idPaciente)
    {
        $sql = "SELECT COUNT(*) FROM pacientes p JOIN usuarios u ON p.id_usuario = u.id_usuario WHERE p.id_paciente = ? AND u.activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idPaciente);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }

    public function medicoExiste($idMedico)
    {
        $sql = "SELECT COUNT(*) FROM medicos m JOIN usuarios u ON m.id_usuario = u.id_usuario WHERE m.id_medico = ? AND u.activo = 1";
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0;
    }
    
    //-----------------------internado-por cada-medico--

    /**
     * Obtiene los internados activos (estado = 'Activo') asignados a un médico específico.
     * CORREGIDO: Join path (internados -> pacientes -> usuarios) y nombre de columna de habitación.
     * @param int $idMedico El ID del médico (de la tabla medicos).
     * @return array Lista de internados del médico.
     */
    public function obtenerMisInternadosActivos($idMedico)
    {
        $sql = "SELECT 
                    i.id_internado, 
                    i.fecha_ingreso, 
                    i.diagnostico_ingreso, 
                    i.observaciones,
                    h.numero_puerta AS habitacion_numero, 
                    CONCAT(up.nombre, ' ', up.apellido_paterno, ' ', up.apellido_materno) AS nombre_paciente
                FROM internados i
                INNER JOIN pacientes pa ON i.id_paciente = pa.id_paciente
                INNER JOIN usuarios up ON pa.id_usuario = up.id_usuario
                LEFT JOIN habitaciones h ON i.id_habitacion = h.id_habitacion 
                WHERE i.id_medico = ? AND i.estado = 'Activo'
                ORDER BY i.fecha_ingreso DESC";

        $stmt = $this->connection->prepare($sql);
        
        if (!$stmt) {
             error_log("Error al preparar la consulta (obtenerMisInternadosActivos): " . $this->connection->error);
             return [];
        }

        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $internados = $resultado ? $resultado->fetch_all(MYSQLI_ASSOC) : [];
        
        $stmt->close();
        return $internados;
    }


     // Auxiliar: Obtiene el nombre completo del paciente a partir del ID de Historia Clínica
    private function obtenerNombrePacientePorIdHC($idHistoriaClinica)
    {
        // Esta consulta asume que existe la tabla 'historia_clinica' -> 'pacientes' -> 'usuarios'
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
     * Obtiene todos los pacientes internados que están a cargo de un médico tratante específico.
     * Se mantiene tal cual estaba, asumiendo que la tabla 'internado' tiene 'dr_tratante_id' y 'id_historia_clinica'.
     * @param int $idMedico El ID del médico logueado.
     * @return array Lista de internados, enriquecida con el nombre del paciente.
     */
    public function obtenerInternadosPorMedicoTratante($idMedico)
    {
        $sql = "SELECT * FROM internado 
                WHERE dr_tratante_id = ? 
                ORDER BY fecha_ingreso DESC";
        
        $stmt = $this->connection->prepare($sql);
        $stmt->bind_param("i", $idMedico);
        $stmt->execute();
        $resultado = $stmt->get_result();
        $internados = $resultado->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
        
        // Adjuntar el nombre completo del paciente
        foreach ($internados as $key => $internado) {
            $internados[$key]['nombre_paciente'] = $this->obtenerNombrePacientePorIdHC($internado['id_historia_clinica']);
        }
        
        return $internados;
    }
    
  
}
?>