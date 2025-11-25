<?php

include_once('../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class HistorialAnemiaDTO {
    // Atributos
    public $id;
    public $termino;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->id = (int)($data['id'] ?? 0);
        $this->termino = trim($data['termino'] ?? '');
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Atributo: `Comando` (Interfaz abstracta)

class HistorialAnemiaFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): HistorialAnemiaDTO {
        // Método: Crea y retorna el DTO
        return new HistorialAnemiaDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, HistorialAnemiaDTO $dto): Comando {
        switch ($action) {
            case 'eliminar':
                // Método: Crea y retorna el comando de eliminación
                return new EliminarHistorialCommand($dto);
            case 'buscar':
                // Método: Crea y retorna el comando de búsqueda
                return new BuscarHistorialCommand($dto);
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
    abstract public function handle(HistorialAnemiaDTO $dto): ?string;
    
    // Método: `passNext`
    protected function passNext(HistorialAnemiaDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de ID (para eliminar)
class IdHistorialValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(HistorialAnemiaDTO $dto): ?string
    {
        if ($dto->id <= 0) {
            return "ID de historial no válido.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de Existencia (para eliminar)
class HistorialExistenciaValidator extends AbstractValidatorHandler {
    // Atributo: `$objDAO`
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new HistorialAnemiaPacienteDAO(); }

    // Método: `handle`
    public function handle(HistorialAnemiaDTO $dto): ?string
    {
        // Método: `obtenerHistorialPorId`
        if (!$this->objDAO->obtenerHistorialPorId($dto->id)) {
            return "El historial no existe o ya fue eliminado.";
        }
        return $this->passNext($dto);
    }
}

// COMMAND Concreto 1: Eliminar Historial 📦
class EliminarHistorialCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (HistorialAnemiaPacienteDAO)
    private $dto;
    private $validationChain;
    // Atributo: `$validationMessage`
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(HistorialAnemiaDTO $dto)
    {
        $this->objDAO = new HistorialAnemiaPacienteDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new IdHistorialValidator();
        $existenciaValidator = new HistorialExistenciaValidator();

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
        // Método: `eliminarHistorial`
        return $this->objDAO->eliminarHistorial($this->dto->id);
    }

    // Método: `getValidationMessage`
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// COMMAND Concreto 2: Buscar Historial 🔎
class BuscarHistorialCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor
    private $dto;
    // Atributo: `$resultados`
    public $resultados = [];

    // Método: Constructor
    public function __construct(HistorialAnemiaDTO $dto)
    {
        $this->objDAO = new HistorialAnemiaPacienteDAO();
        $this->dto = $dto;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // Validación básica de término
        if (empty($this->dto->termino)) {
            return false; // Indicamos que no se ejecutó la búsqueda
        }

        // Ejecución del receptor (DAO)
        // Método: `buscarHistorialesPorPaciente`
        $this->resultados = $this->objDAO->buscarHistorialesPorPaciente($this->dto->termino);
        
        return true; 
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlHistorialAnemia
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
        // Atributo: `$urlRetorno`
        $urlRetorno = "./indexHistorialAnemia.php";

        try {
            // Factory Method: Creación del DTO
            $dto = HistorialAnemiaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = HistorialAnemiaFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($action === 'eliminar') {
                $mensajeError = $command->getValidationMessage();

                if ($mensajeError) {
                    // Estado 1: Error de validación o no existe
                    $this->objMensaje->mensajeSistemaShow(
                        "❌ Error de validación: " . $mensajeError,
                        $urlRetorno,
                        "error",
                        false
                    );
                } elseif ($resultado) {
                    // Estado 2: Éxito en eliminación
                    $this->objMensaje->mensajeSistemaShow(
                        "✅ Historial de anemia eliminado correctamente.", 
                        $urlRetorno, 
                        'success'
                    );
                } else {
                    // Estado 3: Error de base de datos
                    $this->objMensaje->mensajeSistemaShow(
                        '⚠️ Error al eliminar el historial de anemia.', 
                        $urlRetorno, 
                        'error'
                    );
                }
            } elseif ($action === 'buscar') {
                // Command de búsqueda
                $cantidad = count($command->resultados);
                // Estado 4: Resultados de búsqueda (la Vista de listado se encarga de mostrarlos)
                if ($cantidad > 0) {
                    $this->objMensaje->mensajeSistemaShow(
                        "🔍 Se encontraron $cantidad resultados para: " . htmlspecialchars($dto->termino), 
                        $urlRetorno, 
                        "info"
                    );
                } else {
                    $this->objMensaje->mensajeSistemaShow(
                        "⚠️ No se encontraron resultados para: " . htmlspecialchars($dto->termino), 
                        $urlRetorno, 
                        "warning"
                    );
                }
            }
        } catch (Exception $e) {
            // Estado 5: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetorno, 
                'error'
            );
        }
    }
}
?>