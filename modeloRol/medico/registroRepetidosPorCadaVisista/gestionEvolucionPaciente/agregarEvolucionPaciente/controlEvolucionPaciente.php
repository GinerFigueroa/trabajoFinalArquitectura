<?php
// Directorio: /controlador/evolucion/agregarEvolucionPaciente/controlEvolucionPaciente.php

include_once('../../../../../modelo/EvolucionPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EvolucionDTO {
    // Atributos: Los datos del formulario
    public $historiaClinicaId;
    public $idUsuarioLogueado; // ID del usuario, no del médico
    public $idMedico;          // ID real del médico (se obtiene del DAO)
    public $notaSubjetiva;
    public $notaObjetiva;
    public $analisis;
    public $planDeAccion;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->historiaClinicaId = (int)($data['historia_clinica_id'] ?? 0);
        $this->idUsuarioLogueado = (int)($data['id_usuario_logueado'] ?? 0);
        $this->notaSubjetiva = trim($data['nota_subjetiva'] ?? '');
        $this->notaObjetiva = trim($data['nota_objetiva'] ?? '');
        $this->analisis = trim($data['analisis'] ?? '');
        $this->planDeAccion = trim($data['plan_de_accion'] ?? '');
        $this->idMedico = 0; // Se llenará posteriormente
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Atributo: Interfaz base para el Command

class EvolucionFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): EvolucionDTO {
        // Método: Crea y retorna el DTO
        return new EvolucionDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, EvolucionDTO $dto): Comando {
        switch ($action) {
            case 'registrar':
                // Método: Crea y retorna el comando de registro
                return new RegistrarEvolucionCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// CHAIN OF RESPONSIBILITY (Validadores) 🔗
abstract class AbstractValidatorHandler {
    // Atributo: `$nextHandler` (Siguiente en la cadena)
    private $nextHandler = null;

    // Método: `setNext`
    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    // Método: `handle` (Abstracto para la lógica, concreto para el encadenamiento)
    // Atributo: `$dto` (El objeto a validar)
    abstract public function handle(EvolucionDTO $dto): ?string;
    
    // Método: `passNext` (Pasa la validación al siguiente handler si existe)
    protected function passNext(EvolucionDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de campos obligatorios (SOAP)
class RequeridosValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(EvolucionDTO $dto): ?string
    {
        if ($dto->historiaClinicaId <= 0 || empty($dto->notaSubjetiva) || empty($dto->notaObjetiva) || empty($dto->planDeAccion)) {
            return "Faltan campos obligatorios (Paciente, Subjetiva, Objetiva o Plan de Acción).";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de la existencia del Médico asociado al Usuario
class MedicoValidator extends AbstractValidatorHandler {
    // Atributo: `$objDAO`
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new EvolucionPacienteDAO(); }

    // Método: `handle`
    public function handle(EvolucionDTO $dto): ?string
    {
        // Método: `obtenerIdMedicoPorUsuario`
        $idMedico = $this->objDAO->obtenerIdMedicoPorUsuario($dto->idUsuarioLogueado);
        
        if (!$idMedico) {
            return "No se encontró un médico asociado al usuario logueado (ID: {$dto->idUsuarioLogueado}).";
        }
        
        // Actualizar el DTO con el ID de médico real antes de pasar al Command
        $dto->idMedico = $idMedico;
        
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Registrar Evolución 📦
class RegistrarEvolucionCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Atributo: Receptor (EvolucionPacienteDAO)
    private $dto;
    // Atributo: `$validationChain`
    private $validationChain;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EvolucionDTO $dto)
    {
        $this->objDAO = new EvolucionPacienteDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new RequeridosValidator();
        $medicoValidator = new MedicoValidator();

        // Método: `setNext`
        $this->validationChain
             ->setNext($medicoValidator);
    }

    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        // Se ejecuta primero, y si falla, retorna el mensaje de error.
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Ejecución del receptor (DAO) con el DTO actualizado (incluye $idMedico)
        // Método: `registrarEvolucion`
        return $this->objDAO->registrarEvolucion(
            $this->dto->historiaClinicaId,
            $this->dto->idMedico,
            $this->dto->notaSubjetiva,
            $this->dto->notaObjetiva,
            $this->dto->analisis,
            $this->dto->planDeAccion
        );
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado de la validación)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    // Método: `getHistoriaClinicaId` (Para la redirección)
    public function getHistoriaClinicaId(): int
    {
        return $this->dto->historiaClinicaId;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación entre la creación del Command/DTO (Factory), 
 * la ejecución del Command y el manejo de los resultados (State).
 */
class controlEvolucionPaciente
{
    // Atributos: Dependencias
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    // Método: `ejecutarComando` (Punto de coordinación central)
    // Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "./formEvolucionPaciente.php";
        
        try {
            // Factory Method: Creación del DTO
            $dto = EvolucionFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            // Atributo: `$command`
            $command = EvolucionFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Atributo: `$mensajeError`
            $mensajeError = $command->getValidationMessage();
            // Atributo: `$hcId`
            $hcId = $command->getHistoriaClinicaId();
            $urlListado = "../indexEvolucionPaciente.php?hc_id=" . $hcId;

            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno . "?error=" . urlencode($mensajeError),
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Nota de Evolución registrada correctamente (HC N° {$hcId}).", 
                    $urlListado, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al registrar la evolución. Fallo en la Base de Datos.', 
                    $urlRetorno . "?error=" . urlencode("Error de base de datos"), 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
            $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetorno, 
                'error'
            );
        }
    }
}
?>