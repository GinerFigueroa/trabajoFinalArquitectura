<?php

include_once('../../../../modelo/HistoriaClinicaDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, STATE
// ==========================================================

/**
 * HistoriaClinicaDTO (Data Transfer Object)
 * Atributos: historia_clinica_id, id_paciente, dr_tratante_id, fecha_creacion, id_usuario_editor.
 */
class HistoriaClinicaDTO {
    public $historia_clinica_id;
    public $id_paciente;
    public $dr_tratante_id;
    public $fecha_creacion;
    public $id_usuario_editor; // Para chequeo de permisos/auditoría
    
    public function __construct(array $data) {
        $this->historia_clinica_id = (int)($data['historia_clinica_id'] ?? 0);
        $this->id_paciente = (int)($data['id_paciente'] ?? 0);
        $this->dr_tratante_id = (int)($data['dr_tratante_id'] ?? 0);
        $this->fecha_creacion = trim($data['fecha_creacion'] ?? '');
        $this->id_usuario_editor = (int)($data['id_usuario_editor'] ?? 0);
    }
}

/**
 * Interfaz ComandoHistoria
 */
interface ComandoHistoria {
    /** Método: ejecuta la lógica de negocio. */
    public function execute(): bool;
    /** Método: obtiene el mensaje de estado (Patrón State). */
    public function getValidationMessage(): ?string;
}

/**
 * HistoriaClinicaFactory (Patrón Factory Method) 🏭
 * Métodos: crearDTO, crearComando.
 */
class HistoriaClinicaFactory {
    public static function crearDTO(array $data): HistoriaClinicaDTO {
        return new HistoriaClinicaDTO($data);
    }
    
    public static function crearComando(string $action, HistoriaClinicaDTO $dto): ComandoHistoria {
        switch ($action) {
            case 'editar':
                return new EditarHistoriaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Historial Clínico.");
        }
    }
}

/**
 * EditarHistoriaCommand (Command Concreto) 📦
 * Atributos: objDAO (Receptor), dto, validationMessage (State).
 * Métodos: __construct, execute, getValidationMessage.
 */
class EditarHistoriaCommand implements ComandoHistoria
{
    private $objDAO; // Receptor: HistoriaClinicaDAO
    private $dto;
    private $validationMessage = null; // Patrón State

    public function __construct(HistoriaClinicaDTO $dto)
    {
        $this->objDAO = new HistoriaClinicaDAO();
        $this->dto = $dto;
    }
    
    public function execute(): bool
    {
        // 1. Validaciones de Datos (Obligatoriedad e Integridad)
        if ($this->dto->historia_clinica_id <= 0 || $this->dto->id_paciente <= 0 || $this->dto->dr_tratante_id <= 0) {
            $this->validationMessage = "IDs de Historia Clínica, Paciente o Personal Tratante no válidos.";
            return false;
        }
        if (empty($this->dto->fecha_creacion) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $this->dto->fecha_creacion)) {
            $this->validationMessage = "El campo 'Fecha de Creación' es obligatorio y debe tener formato YYYY-MM-DD.";
            return false;
        }

        // 2. Validación de Negocio (Existencia y Permisos)
        
        // Verificar que la historia exista
        $historiaActual = $this->objDAO->obtenerHistoriaPorId($this->dto->historia_clinica_id);
        if (!$historiaActual) {
            $this->validationMessage = "La Historia Clínica a editar no existe.";
            return false;
        }

        // Si el usuario logueado no es el Dr. Tratante original ni Administrador (rol 1), denegar.
        if (($_SESSION['id_rol'] ?? 0) != 1 && $historiaActual['dr_tratante_id'] != $this->dto->id_usuario_editor) {
            $this->validationMessage = "Permiso denegado. Solo el Dr. Tratante original o un Administrador pueden editar esta historia.";
            return false;
        }

        // 3. Ejecución del Receptor (DAO)
        $resultado = $this->objDAO->editarHistoria(
            $this->dto->historia_clinica_id, 
            $this->dto->id_paciente, 
            $this->dto->dr_tratante_id, 
            $this->dto->fecha_creacion
        );
        
        if ($resultado) {
            return true;
        } else {
            $this->validationMessage = "Error en la base de datos al intentar actualizar la historia clínica.";
            return false;
        }
    }

    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * controlEditarHistorialPaciente (Patrón Mediator) 🤝
 * Atributos: objMensaje.
 * Métodos: __construct, ejecutarComando.
 */
class controlEditarHistorialPaciente
{
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Punto de coordinación central.
     * Patrón: STATE 🚦 (Manejo de estados basado en la salida del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        // La URL de retorno en caso de FALLO debe ser la página de edición actual.
        $urlRetornoFallo = './indexEditarHistorialPaciente.php?id=' . ($data['historia_clinica_id'] ?? 0);
        // La URL de retorno en caso de ÉXITO debe ser el listado principal.
        $urlRetornoExito = '../indexHistoriaClinica.php';

        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = HistoriaClinicaFactory::crearDTO($data);
            $command = HistoriaClinicaFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación o Permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de Validación: " . $mensajeError,
                    $urlRetornoFallo,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Historia Clínica actualizada correctamente.', 
                    $urlRetornoExito, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al actualizar la Historia Clínica. Fallo en la base de datos o sin cambios.', 
                    $urlRetornoFallo, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de sistema
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetornoFallo, 
                'error'
            );
        }
    }
    
  
}
?>