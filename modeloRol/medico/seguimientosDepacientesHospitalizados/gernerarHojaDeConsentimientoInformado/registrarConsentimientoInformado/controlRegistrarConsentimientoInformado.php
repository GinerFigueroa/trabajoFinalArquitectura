<?php

include_once('../../../../modelo/ConsentimientoInformadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class RegistroConsentimientoDTO {
    // Atributos: Los datos del formulario
    public $historiaClinicaId;
    public $idPaciente;
    public $drTratanteId;
    public $diagnostico;
    public $tratamiento;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->historiaClinicaId = (int)($data['historiaClinicaId'] ?? 0);
        $this->idPaciente = (int)($data['idPaciente'] ?? 0);
        $this->drTratanteId = (int)($data['drTratanteId'] ?? 0);
        $this->diagnostico = trim($data['diagnostico'] ?? '');
        $this->tratamiento = trim($data['tratamiento'] ?? '');
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class ConsentimientoRegistroFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): RegistroConsentimientoDTO {
        // Método: Crea y retorna el DTO
        return new RegistroConsentimientoDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, RegistroConsentimientoDTO $dto): Comando {
        switch ($action) {
            case 'registrar':
                // Método: Crea y retorna el comando de registro
                return new RegistrarConsentimientoCommand($dto);
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
    abstract public function handle(RegistroConsentimientoDTO $dto): ?string;
    
    // Método: `passNext` (Pasa la validación al siguiente handler si existe)
    protected function passNext(RegistroConsentimientoDTO $dto): ?string
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
    public function handle(RegistroConsentimientoDTO $dto): ?string
    {
        if ($dto->historiaClinicaId <= 0 || $dto->idPaciente <= 0 || $dto->drTratanteId <= 0 || empty($dto->diagnostico) || empty($dto->tratamiento)) {
            return "Faltan campos obligatorios o no son válidos (HC, Paciente, Doctor, Diagnóstico, Tratamiento).";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de existencia de HC y Médico (simulado con Entidades)
class EntidadesValidator extends AbstractValidatorHandler {
    // Atributo: `$objHC`
    private $objHC;
    // Atributo: `$objMedico`
    private $objMedico;
    
    // Método: Constructor
    public function __construct() { 
        $this->objHC = new EntidadHistoriaClinica();
        $this->objMedico = new EntidadMedico();
    }

    // Método: `handle`
    public function handle(RegistroConsentimientoDTO $dto): ?string
    {
        // Validación de HC
        // Método: `obtenerInfoPorHistoriaClinica`
        if (!$this->objHC->obtenerInfoPorHistoriaClinica($dto->historiaClinicaId)) {
            return "La Historia Clínica seleccionada no es válida o no existe.";
        }
        
        // Validación de Médico (se asume que existe un método similar en EntidadMedico)
        // Método: `validarExistenciaMedico`
        // if (!$this->objMedico->validarExistenciaMedico($dto->drTratanteId)) {
        //     return "El Doctor Tratante seleccionado no es válido o no existe.";
        // }
        
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Registrar Consentimiento 📦
class RegistrarConsentimientoCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (ConsentimientoInformadoDAO)
    private $dto;
    private $validationChain;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(RegistroConsentimientoDTO $dto)
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
        $entidadesValidator = new EntidadesValidator();

        $this->validationChain
             ->setNext($entidadesValidator);
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
        // Método: `registrarConsentimiento`
        return $this->objDAO->registrarConsentimiento(
            $this->dto->historiaClinicaId, 
            $this->dto->idPaciente, 
            $this->dto->drTratanteId, 
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
class controlRegistrarConsentimientoInformado
{
    // Atributos: Dependencias
    private $objDAO;
    private $objHC;
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        // Se mantienen las instancias para la función auxiliar (AJAX)
        $this->objDAO = new ConsentimientoInformadoDAO();
        $this->objHC = new EntidadHistoriaClinica();
        $this->objMensaje = new mensajeSistema();
    }

    // Método: `obtenerInfoPacientePorHC` (Maneja la solicitud AJAX / Lógica auxiliar)
    public function obtenerInfoPacientePorHC($idHC)
    {
        // Método: `obtenerInfoPorHistoriaClinica`
        return $this->objHC->obtenerInfoPorHistoriaClinica($idHC);
    }

    // Método: `ejecutarComando` (Punto de coordinación central para el registro)
    // Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
    public function ejecutarComando(string $action, array $data)
    {
        // Atributo: `$urlRetorno` (Para errores, se retorna al formulario)
        $urlRetorno = './indexRegistrarConsetimientoInformado.php';

        try {
            // Factory Method: Creación del DTO
            $dto = ConsentimientoRegistroFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = ConsentimientoRegistroFactory::crearComando($action, $dto);

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
                    '✅ Consentimiento Informado registrado correctamente.', 
                    '../indexConsentimientoInformado.php', 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '❌ Error al registrar el Consentimiento Informado. Fallo en la base de datos.', 
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