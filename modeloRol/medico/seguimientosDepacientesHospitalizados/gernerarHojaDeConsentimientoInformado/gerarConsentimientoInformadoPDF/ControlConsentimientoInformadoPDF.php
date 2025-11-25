<?php

include_once('../../../../modelo/ConsentimientoInformadoDAO.php');
include_once('../../../../shared/mensajeSistema.php');
include_once('./formConcentimientoInformadoPDF.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class ConsentimientoPDFDTO {
    // Atributo: `id` (Clave principal)
    public $id;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class GeneracionPDFFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): ConsentimientoPDFDTO {
        // Método: Crea y retorna el DTO
        return new ConsentimientoPDFDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, ConsentimientoPDFDTO $dto): Comando {
        switch ($action) {
            case 'generar':
                // Método: Crea y retorna el comando de generación de PDF
                return new GenerarPDFCommand($dto);
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
    abstract public function handle(ConsentimientoPDFDTO $dto): ?string;
    
    // Método: `passNext` (Pasa la validación al siguiente handler si existe)
    protected function passNext(ConsentimientoPDFDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de ID
class IdValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(ConsentimientoPDFDTO $dto): ?string
    {
        if ($dto->id <= 0) {
            return "ID de Consentimiento no proporcionado o no válido.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de Existencia de Datos
class DatosExistenciaValidator extends AbstractValidatorHandler {
    // Atributo: `$objDAO`
    private $objDAO;
    // Atributo: `$datos` (Resultado de la consulta para usar en el Command)
    public $datos = null; 
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new ConsentimientoInformadoDAO(); }

    // Método: `handle`
    public function handle(ConsentimientoPDFDTO $dto): ?string
    {
        // Método: `obtenerConsentimientoPorId` 
        $this->datos = $this->objDAO->obtenerConsentimientoPorId($dto->id);
        
        if (!$this->datos) {
            return "Consentimiento Informado no encontrado en la base de datos.";
        }
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Generar PDF 📦
class GenerarPDFCommand implements Comando
{
    // Atributos: DTO y Receptors
    private $dto;
    private $objFormPDF; // Receptor (Vista)
    private $datosConsentimiento = null;
    private $validationChain;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(ConsentimientoPDFDTO $dto)
    {
        $this->dto = $dto;
        $this->objFormPDF = new formConcentimientoInformadoPDF();
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new IdValidator();
        $datosValidator = new DatosExistenciaValidator();

        // Método: `setNext`
        $this->validationChain->setNext($datosValidator);
    }

    // Método: `execute` (Lógica central del Command)
    // El command se encarga de la generación completa
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Obtener los datos validados del último handler de la cadena
        // Nota: Esto asume que DatosExistenciaValidator es el último o el que contiene los datos.
        $currentHandler = $this->validationChain;
        while ($currentHandler !== null && !($currentHandler instanceof DatosExistenciaValidator)) {
             // Simulación de avanzar al siguiente hasta encontrar el validador de datos
             // Nota: En un sistema real, se podría acceder al último eslabón de la cadena
             $nextProperty = new ReflectionProperty($currentHandler, 'nextHandler');
             $nextProperty->setAccessible(true);
             $currentHandler = $nextProperty->getValue($currentHandler);
        }
        if ($currentHandler instanceof DatosExistenciaValidator) {
            $this->datosConsentimiento = $currentHandler->datos;
        }


        // 3. Ejecución del receptor (Vista/PDF Generator)
        // Método: `generarPDFShow`
        $this->objFormPDF->generarPDFShow($this->datosConsentimiento);
        
        // El stream de PDF generalmente detiene la ejecución, pero se retorna true si la orquestación fue exitosa
        return true; 
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
class ControlConsentimientoInformadoPDF
{
    // Atributos: Dependencias
    private $objMensaje;
    private $objFormPDF;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formConcentimientoInformadoPDF(); // Mantenido para evitar errores si se llama directamente
    }

    // Método: `generarPDF` (ACTÚA COMO MEDIATOR/INVOKER PARA EL COMMAND)
    // Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
    public function generarPDF()
    {
        // Atributo: `$rutaRetorno`
        $rutaRetorno = "../indexConsentimientoInformado.php";

        // Obtener datos de la solicitud (ID)
        $data = ['id' => $_GET['id'] ?? null];
        
        try {
            // Factory Method: Creación del DTO
            $dto = GeneracionPDFFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = GeneracionPDFFactory::crearComando('generar', $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación o datos no encontrados
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error al generar PDF: " . $mensajeError,
                    $rutaRetorno,
                    "systemOut",
                    false
                );
            } 
            // Si el resultado es true, el command ya ejecutó el stream/exit del PDF.
            
        } catch (Exception $e) {
            // Estado 2: Error interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema al generar PDF: ' . $e->getMessage(), 
                $rutaRetorno, 
                'error'
            );
        }
    }
}
?>