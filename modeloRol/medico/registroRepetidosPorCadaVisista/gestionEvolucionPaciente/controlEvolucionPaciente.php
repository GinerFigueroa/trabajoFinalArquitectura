<?php
// Directorio: /controlador/evolucion/controlEvolucionPaciente.php

include_once('../../../../modelo/EvolucionPacienteDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EliminarEvolucionDTO {
    // Atributo: $id
    public $id;
    
    // Método: Constructor
    public function __construct(array $data) {
        // Asignación y limpieza de atributos
        $this->id = (int)($data['id_evolucion'] ?? 0);
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class EvolucionFactory {
    // Método: crearDTO
    public static function crearDTO(string $action, array $data) {
        switch ($action) {
            case 'eliminar':
                return new EliminarEvolucionDTO($data);
            default:
                throw new Exception("Acción de DTO no soportada.");
        }
    }
    
    // Método: crearComando (Factory Method)
    public static function crearComando(string $action, $dto): Comando {
        switch ($action) {
            case 'eliminar':
                // Método: Crea y retorna el comando de eliminación
                return new EliminarEvolucionCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// CHAIN OF RESPONSIBILITY (Validadores) 🔗
abstract class AbstractValidatorHandler {
    // Atributo: $nextHandler (Siguiente en la cadena)
    private $nextHandler = null;

    // Método: setNext
    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    // Método: handle (Abstracto)
    abstract public function handle($dto): ?string;
    
    // Método: passNext (Concreto)
    protected function passNext($dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de ID
class IdValidator extends AbstractValidatorHandler {
    // Método: handle
    public function handle($dto): ?string
    {
        // Validación para EliminarEvolucionDTO
        if ($dto instanceof EliminarEvolucionDTO && $dto->id <= 0) {
            return "El ID de Evolución es obligatorio y debe ser un número positivo.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de existencia
class ExistenciaValidator extends AbstractValidatorHandler {
    // Atributo: $objDAO
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { 
        $this->objDAO = new EvolucionPacienteDAO(); 
    }

    // Método: handle
    public function handle($dto): ?string
    {
        // Se asume un método en el DAO para verificar la existencia.
        // Si no existe, se usa obtenerEvolucionPorId y se verifica si devuelve datos.
        if ($dto instanceof EliminarEvolucionDTO) {
             // Método: obtenerEvolucionPorId
            if (!$this->objDAO->obtenerEvolucionPorId($dto->id)) {
                return "La Evolución con ID {$dto->id} no existe o ya fue eliminada.";
            }
        }
        return $this->passNext($dto);
    }
}


// COMMAND Concreto: Eliminar Evolución 📦
class EliminarEvolucionCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (EvolucionPacienteDAO)
    private $dto;
    private $validationChain;
    // Atributo: $validationMessage (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EliminarEvolucionDTO $dto)
    {
        $this->objDAO = new EvolucionPacienteDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new IdValidator();
        $existenciaValidator = new ExistenciaValidator();

        // Método: setNext
        $this->validationChain->setNext($existenciaValidator);
    }

    // Método: execute (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        // Método: handle
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Ejecución del receptor (DAO)
        // Método: eliminarEvolucion
        return $this->objDAO->eliminarEvolucion($this->dto->id);
    }

    // Método: getValidationMessage (Permite al Mediator leer el Estado de la validación)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlEvolucionPaciente
{
    // Atributos: Dependencias
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: ejecutarComando (Punto de coordinación central)
     * @param string $action La acción a ejecutar ('eliminar', 'registrar', etc.)
     * @param array $data Los datos de la petición (POST/GET)
     */
    public function ejecutarComando(string $action, array $data)
    {
        // Atributo: $urlRetorno
        $urlRetorno = './indexEvolucionPaciente.php';

        try {
            // Factory Method: Creación del DTO
            // Método: crearDTO
            $dto = EvolucionFactory::crearDTO($action, $data);
            
            // Factory Method: Creación del COMMAND
            // Método: crearComando
            $command = EvolucionFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Método: execute
            // Atributo: $resultado (Estado de la operación DAO)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            // Método: getValidationMessage
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Evolución médica eliminada correctamente.', 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar la evolución. Fallo en la DB o el registro no se encontró.', 
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