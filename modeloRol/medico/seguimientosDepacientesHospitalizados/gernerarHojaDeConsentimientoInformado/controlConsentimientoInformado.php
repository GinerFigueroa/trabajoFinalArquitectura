<?php

include_once('../../../modelo/ConsentimientoInformadoDAO.php');
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class ConsentimientoDTO {
    // Atributo: `id` (Clave principal)
    public $id;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->id = $data['id'] ?? null;
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class ConsentimientoFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): ConsentimientoDTO {
        // Método: Crea y retorna el DTO
        return new ConsentimientoDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, ConsentimientoDTO $dto): Comando {
        switch ($action) {
            case 'eliminar':
                // Método: Crea y retorna el comando de eliminación
                return new EliminarConsentimientoCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// CHAIN OF RESPONSIBILITY (Validadores) 🔗
abstract class AbstractValidatorHandler {
    // Atributo: `$nextHandler` (Siguiente en la cadena, abstracto)
    private $nextHandler = null;

    // Método: `setNext`
    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    // Método: `handle` (Abstracto para la lógica, concreto para el encadenamiento)
    // Atributo: `$dto` (El objeto a validar)
    abstract public function handle(ConsentimientoDTO $dto): ?string;
    
    // Método: `passNext` (Pasa la validación al siguiente handler si existe)
    protected function passNext(ConsentimientoDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de ID y Existencia
class IdExistenciaValidator extends AbstractValidatorHandler {
    // Atributo: `$objDAO`
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new ConsentimientoInformadoDAO(); }

    // Método: `handle`
    public function handle(ConsentimientoDTO $dto): ?string
    {
        if (empty($dto->id) || !is_numeric($dto->id)) {
            return "ID de consentimiento no válido.";
        }
        // Método: `obtenerConsentimientoPorId`
        if (!$this->objDAO->obtenerConsentimientoPorId($dto->id)) {
            return "El consentimiento informado con el ID proporcionado no existe.";
        }
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Eliminar Consentimiento 📦
class EliminarConsentimientoCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (ConsentimientoInformadoDAO)
    private $dto;
    private $validationChain;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(ConsentimientoDTO $dto)
    {
        $this->objDAO = new ConsentimientoInformadoDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        // Solo necesitamos un validador para esta operación simple
        $this->validationChain = new IdExistenciaValidator();
    }

    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Ejecución del receptor (DAO)
        // Método: `eliminarConsentimiento`
        return $this->objDAO->eliminarConsentimiento($this->dto->id);
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado de la validación)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlConsentimientoInformado
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
        // Atributo: `$rutaRetorno` (Para errores, se retorna a la lista principal)
        $rutaRetorno = "./indexConsentimientoInformado.php";

        try {
            // Factory Method: Creación del DTO
            $dto = ConsentimientoFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = ConsentimientoFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $rutaRetorno,
                    "systemOut", // Usamos systemOut para evitar redirección instantánea si hay un error
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Consentimiento Informado eliminado correctamente.', 
                    $rutaRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '❌ Error al eliminar el consentimiento informado. Fallo en la base de datos.', 
                    $rutaRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $rutaRetorno, 
                'error'
            );
        }
    }
}
?>