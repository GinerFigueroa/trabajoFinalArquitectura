<?php

include_once('../../../../modelo/HistoriaClinicaDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, STATE
// ==========================================================

/**
 * HistoriaClinicaDTO (Data Transfer Object)
 */
class HistoriaClinicaDTO {
    // Atributos:
    public $id_paciente;
    public $dr_tratante_id;
    public $fecha_creacion;
    
    // Métodos:
    public function __construct(array $data) {
        $this->id_paciente = (int)($data['id_paciente'] ?? 0);
        $this->dr_tratante_id = (int)($data['dr_tratante_id'] ?? 0);
        $this->fecha_creacion = trim($data['fecha_creacion'] ?? date("Y-m-d"));
    }
}

/**
 * Interfaz ComandoHistoria
 */
interface ComandoHistoria {
    // Métodos:
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

/**
 * HistoriaClinicaFactory (Patrón Factory Method) 🏭
 */
class HistoriaClinicaFactory {
    // Atributos: Ninguno (Métodos estáticos).
    
    // Métodos:
    public static function crearDTO(array $data): HistoriaClinicaDTO {
        return new HistoriaClinicaDTO($data);
    }
    
    public static function crearComando(string $action, HistoriaClinicaDTO $dto): ComandoHistoria {
        switch ($action) {
            case 'agregar':
                return new AgregarHistoriaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Historial Clínico.");
        }
    }
}

/**
 * AgregarHistoriaCommand (Command Concreto) 📦
 */
class AgregarHistoriaCommand implements ComandoHistoria
{
    // Atributos:
    private $objDAO; // Receptor: HistoriaClinicaDAO
    private $dto;
    private $validationMessage = null; // Patrón State
    private $newId = 0; // Almacena el ID generado si tiene éxito
    
    // Métodos:
    public function __construct(HistoriaClinicaDTO $dto)
    {
        $this->objDAO = new HistoriaClinicaDAO();
        $this->dto = $dto;
    }
    
    /**
     * Ejecuta la lógica del comando.
     */
    public function execute(): bool
    {
        // 1. Validaciones de Datos
        if ($this->dto->id_paciente <= 0) {
            $this->validationMessage = "Debe seleccionar un paciente válido.";
            return false;
        }
        if ($this->dto->dr_tratante_id <= 0) {
            $this->validationMessage = "Error de sesión: ID del personal tratante no válido.";
            return false;
        }

        // 2. Ejecución del Receptor (DAO)
        $historiaClinicaId = $this->objDAO->registrarHistoria(
            $this->dto->id_paciente, 
            $this->dto->dr_tratante_id, 
            $this->dto->fecha_creacion
        );
        
        // 3. Manejo del Resultado
        if ($historiaClinicaId > 0) {
            $this->newId = $historiaClinicaId;
            return true;
        } else {
            // Asume que 0 o FALSE significa error de inserción o paciente duplicado
            $this->validationMessage = "Error al crear la Historia Clínica. El paciente seleccionado ya tiene una HC o hubo un fallo en la base de datos.";
            return false;
        }
    }

    // Métodos para leer el Estado (Patrón State)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    public function getNewId(): int
    {
        return $this->newId;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * controlAgregarHistorialPaciente (Patrón Mediator) 🤝
 */
class controlAgregarHistorialPaciente
{
    // Atributos:
    private $objMensaje;

    // Métodos:
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
        $rutaRetornoFallo = './indexAgregarHistorialPaciente.php';
        
        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = HistoriaClinicaFactory::crearDTO($data);
            $command = HistoriaClinicaFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación o DB
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error: " . $mensajeError,
                    $rutaRetornoFallo,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito (Obtenemos el ID de la HC recién creada)
                $historiaClinicaId = $command->getNewId();
                
                // Redirigir a la vista de historias clínicas con el mensaje de éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Historia Clínica base creada y asignada correctamente. Proceda a completar la Anamnesis.', 
                    // Ruta para continuar con la captura de información detallada
                    '../indexHistoriaClinica.php', 
                    'success'
                );
            } else {
                // Esto debería ser cubierto por el mensajeError, pero es un fallback
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error inesperado al intentar registrar la Historia Clínica.', 
                    $rutaRetornoFallo, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de sistema
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $rutaRetornoFallo, 
                'error'
            );
        }
    }
    
    // NOTA: Se elimina el método 'agregarHistoria' del código original, ya que su lógica 
    // ha sido migrada completamente al 'AgregarHistoriaCommand'.
}
?>