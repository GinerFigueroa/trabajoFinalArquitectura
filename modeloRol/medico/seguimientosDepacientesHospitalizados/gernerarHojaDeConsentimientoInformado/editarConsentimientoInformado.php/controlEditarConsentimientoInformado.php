<?php

include_once('../../../../modelo/ConsentimientoInformadoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EditarConsentimientoDTO {
    // Atributos: Los datos del formulario
    public $idConsentimiento;
    public $diagnostico;
    public $tratamiento;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idConsentimiento = (int)($data['idConsentimiento'] ?? 0);
        $this->diagnostico = trim($data['diagnostico'] ?? '');
        $this->tratamiento = trim($data['tratamiento'] ?? '');
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class EditarConsentimientoFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): EditarConsentimientoDTO {
        // Método: Crea y retorna el DTO
        return new EditarConsentimientoDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, EditarConsentimientoDTO $dto): Comando {
        switch ($action) {
            case 'editar':
                // Método: Crea y retorna el comando de edición
                return new EditarConsentimientoCommand($dto);
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
    abstract public function handle(EditarConsentimientoDTO $dto): ?string;
    
    // Método: `passNext` (Pasa la validación al siguiente handler si existe)
    protected function passNext(EditarConsentimientoDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de campos obligatorios
class RequeridosValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(EditarConsentimientoDTO $dto): ?string
    {
        if ($dto->idConsentimiento <= 0 || empty($dto->diagnostico) || empty($dto->tratamiento)) {
            return "Faltan campos obligatorios (ID, Diagnóstico o Tratamiento) o no son válidos.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de existencia del registro
class ExistenciaValidator extends AbstractValidatorHandler {
    // Atributo: `$objDAO`
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new ConsentimientoInformadoDAO(); }

    // Método: `handle`
    public function handle(EditarConsentimientoDTO $dto): ?string
    {
        // Método: `obtenerConsentimientoPorId`
        if (!$this->objDAO->obtenerConsentimientoPorId($dto->idConsentimiento)) {
            return "El Consentimiento Informado con ID {$dto->idConsentimiento} no existe.";
        }
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Editar Consentimiento 📦
class EditarConsentimientoCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (ConsentimientoInformadoDAO)
    private $dto;
    private $validationChain;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EditarConsentimientoDTO $dto)
    {
        $this->objDAO = new ConsentimientoInformadoDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new RequeridosValidator();
        $existenciaValidator = new ExistenciaValidator();

        // Método: `setNext`
        $this->validationChain
             ->setNext($existenciaValidator);
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
        // Método: `editarConsentimiento`
        return $this->objDAO->editarConsentimiento(
            $this->dto->idConsentimiento, 
            $this->dto->diagnostico, 
            $this->dto->tratamiento
        );
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
class controlEditarConsentimientoInformado
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
        // Atributo: `$id`
        $id = (int)($data['idConsentimiento'] ?? 0);
        // Atributo: `$urlRetorno`
        $urlRetorno = './indexEditarConsentimientoInformado.php?id=' . $id;
        $urlListado = '../indexConsentimientoInformado.php';

        try {
            // Factory Method: Creación del DTO
            $dto = EditarConsentimientoFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = EditarConsentimientoFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno,
                    "systemOut",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Consentimiento N° ' . $id . ' actualizado correctamente.', 
                    $urlListado, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al editar el consentimiento. Verifique que se hayan realizado cambios o fallo en DB.', 
                    $urlRetorno, 
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