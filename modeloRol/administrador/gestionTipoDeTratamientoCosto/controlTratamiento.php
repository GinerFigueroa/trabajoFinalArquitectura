<?php

include_once('../../../shared/mensajeSistema.php');
include_once('../../../modelo/TratamientoDAO.php'); 

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, CoR
// ==========================================================

// DTO/ENTIDAD
class TratamientoDTO {
    // Atributo: $idTratamiento
    public $idTratamiento;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idTratamiento = (int)($data['idTratamiento'] ?? 0);
    }
}

// Patrón: COMMAND (Interfaz base)
interface Comando {
    // Atributo: Método abstracto `execute` (el corazón del Command)
    public function execute(): bool;
    // Atributo: Método abstracto `getValidationMessage` (Permite al Mediator leer el Estado)
    public function getValidationMessage(): ?string;
}

// Patrón: FACTORY METHOD 🏭
class TratamientoFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): TratamientoDTO {
        return new TratamientoDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, TratamientoDTO $dto): Comando {
        switch ($action) {
            case 'eliminar':
                // Atributo: Retorna la instancia del Command concreto
                return new EliminarTratamientoCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// Patrón: CHAIN OF RESPONSIBILITY (Abstract Handler) ⛓️
abstract class TratamientoValidationHandler {
    // Atributo: $successor (Siguiente eslabón de la cadena)
    protected $successor;

    // Método: `setNext`
    public function setNext(TratamientoValidationHandler $handler): TratamientoValidationHandler {
        $this->successor = $handler;
        return $handler;
    }

    // Atributo: Método abstracto `handle`
    abstract public function handle(TratamientoDTO $dto): ?string;
}

// Patrón: CHAIN OF RESPONSIBILITY (Concrete Handler: Validación de ID)
class IdValidationHandler extends TratamientoValidationHandler {
    // Método: `handle`
    public function handle(TratamientoDTO $dto): ?string {
        if ($dto->idTratamiento <= 0) {
            return "El ID del Tratamiento debe ser un número positivo.";
        }
        
        // Pasa la validación al siguiente eslabón (si existe)
        return $this->successor ? $this->successor->handle($dto) : null;
    }
}

// Patrón: COMMAND Concreto 📦
class EliminarTratamientoCommand implements Comando
{
    // Atributo: $objDAO (El Receptor: Sabe cómo realizar la operación)
    private $objDAO;
    // Atributo: $dto (Los datos de la solicitud)
    private $dto;
    // Atributo: $validationMessage (El Estado interno del Command, leído por el Mediator)
    private $validationMessage = null;

    // Método: Constructor (Inicia la Chain of Responsibility)
    public function __construct(TratamientoDTO $dto)
    {
        $this->objDAO = new TratamientoDAO(); 
        $this->dto = $dto;

        // Configuración de la CHAIN OF RESPONSIBILITY
        $idValidator = new IdValidationHandler();
        // Agregue más eslabones aquí si fuera necesario: $idValidator->setNext(new OtherHandler());
        
        // Ejecución de la cadena de validación
        $this->validationMessage = $idValidator->handle($this->dto);
    }
    
    // Método: `execute`
    public function execute(): bool
    {
        // Si la validación falló (CoR), no se ejecuta el DAO
        if ($this->validationMessage) {
            return false;
        }

        // Ejecución del receptor (DAO)
        return $this->objDAO->eliminarTratamiento($this->dto->idTratamiento);
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Orquesta la interacción entre el Factory, el Command, y el sistema de mensajes (State).
 */
class controlTratamiento
{
    // Atributo: $objMensaje (Dependencia del sistema de mensajes)
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Atributo: Método `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (Maneja el flujo de la respuesta basado en el estado del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "./indexTipoTratamiento.php";

        try {
            // 1. Factory Method: Creación del DTO
            $dto = TratamientoFactory::crearDTO($data);
            
            // 2. Factory Method: Creación del COMMAND
            $command = TratamientoFactory::crearComando($action, $dto);

            // 3. Command: Ejecución
            // Atributo: $resultado (Estado de la operación DAO: true/false)
            $resultado = $command->execute();

            // 4. State: Leer el estado de la validación del Command (CoR result)
            // Atributo: $mensajeError
            $mensajeError = $command->getValidationMessage();

            // 5. Mediator/STATE: Lógica de respuesta
            if ($mensajeError) {
                // Estado 1: Error de validación (Falló la CoR)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Tratamiento ID {$dto->idTratamiento} eliminado correctamente.", 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar el Tratamiento. Puede que ya no exista o haya un fallo en DB.', 
                    $urlRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error interno (Fallo en Factory o ejecución)
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetorno, 
                'error'
            );
        }
    }
    
    /**
     * Método de compatibilidad: Delega la llamada al nuevo método central.
     * Atributo: Método `eliminarTratamiento` (Función externa de compatibilidad)
     */
    public function eliminarTratamiento($idTratamiento)
    {
        // El viejo método ahora solo llama al Mediator
        $this->ejecutarComando('eliminar', ['idTratamiento' => $idTratamiento]);
    }
}
?>