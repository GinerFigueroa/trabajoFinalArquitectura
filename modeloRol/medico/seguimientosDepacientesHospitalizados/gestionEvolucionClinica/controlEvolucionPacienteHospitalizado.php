<?php

include_once('../../../../modelo/InternadoSeguimientoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EvolucionSeguimientoDTO {
    // Atributos: Los datos mínimos necesarios para la operación
    public $idSeguimiento;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idSeguimiento = $data['id'] ?? null;
    }
}

// Patrón: FACTORY METHOD 🏭
interface Command {} // Interfaz base para el Command

class EvolucionFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): EvolucionSeguimientoDTO {
        // Método: Crea y retorna el DTO
        return new EvolucionSeguimientoDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, EvolucionSeguimientoDTO $dto): Command {
        switch ($action) {
            case 'eliminar':
                // Método: Crea y retorna un comando de eliminación
                return new EliminarSeguimientoCommand($dto);
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
    public function handle(EvolucionSeguimientoDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null; 
    }
}

// Handler Concreto: Validación de ID (Única validación para la eliminación)
class IdSeguimientoValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(EvolucionSeguimientoDTO $dto): ?string
    {
        if (empty($dto->idSeguimiento) || !is_numeric($dto->idSeguimiento) || $dto->idSeguimiento <= 0) {
            return "ID de seguimiento para la eliminación no es válido.";
        }
        
        // Se podría añadir una validación DAO para verificar existencia
        // $objDAO = new InternadoSeguimientoDAO();
        // if (!$objDAO->existeSeguimiento($dto->idSeguimiento)) {
        //     return "El registro de seguimiento no existe.";
        // }
        
        return parent::handle($dto);
    }
}

// COMMAND Concreto: Eliminar Seguimiento 📦
class EliminarSeguimientoCommand implements Command
{
    // Atributos: DTO y Receptor (DAO)
    private $objSeguimientoDAO; // Receptor
    private $dto;
    private $validationChain;
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EvolucionSeguimientoDTO $dto)
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // Atributo: `validationChain`
        $this->validationChain = new IdSeguimientoValidator();
    }

    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // Chain of Responsibility: Ejecución de la cadena
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // Ejecución del receptor (DAO)
        // Método: `eliminarSeguimiento`
        return $this->objSeguimientoDAO->eliminarSeguimiento($this->dto->idSeguimiento);
    }

    // Método: `getValidationMessage` (Para que el Mediator lo use)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlEvolucionPacienteHospitalizado
{
    // Atributos: Dependencias
    private $objMensaje;
    // Atributo: URL de retorno
    private $urlRetorno = './indexEvolucionClinicaPacienteHospitalizado.php';

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    // Método: `ejecutarComando` (Punto de coordinación central)
    // Atributos: `action` (tipo de comando), `data` (datos para el DTO)
    public function ejecutarComando(string $action, array $data)
    {
        try {
            // Factory Method: Creación del DTO
            $dto = EvolucionFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = EvolucionFactory::crearComando($action, $dto);

            // Command: Ejecución
            $resultado = $command->execute();

            // Mediator: Lógica para manejar la respuesta del Command
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Manejo de error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $this->urlRetorno,
                    "error"
                );
            } elseif ($resultado) {
                // Manejo de éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Registro de evolución eliminado correctamente.', 
                    $this->urlRetorno, 
                    'success'
                );
            } else {
                // Manejo de error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '❌ Error al eliminar el registro de evolución en la base de datos.', 
                    $this->urlRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Manejo de error de fábrica (acción no soportada)
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno: ' . $e->getMessage(), 
                $this->urlRetorno, 
                'error'
            );
        }
    }
}
?>