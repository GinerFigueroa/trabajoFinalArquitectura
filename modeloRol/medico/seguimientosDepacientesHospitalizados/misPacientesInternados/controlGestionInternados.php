<?php
// controlGestionInternados.php
include_once('../../../../modelo/InternadoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// --------------------------------------------------------------------------------
// PATRÓN: DTO (Data Transfer Object) y FACTORY METHOD 🏭
// --------------------------------------------------------------------------------
/**
 * Clase InternadoDTO: DTO para transferir datos entre capas.
 * Atributos: Los datos clave necesarios para el alta.
 */
class InternadoDTO {
    public $idInternado; 
    public $idHabitacion; 
    public $idMedico; 
    public $diagnostico; 
    public $observaciones; 
    public $estado; 

    public function __construct(array $data) {
        $this->idInternado = $data['idInternado'] ?? null;
        $this->idHabitacion = $data['idHabitacion'] ?? null;
        $this->idMedico = $data['idMedico'] ?? null;
        $this->diagnostico = $data['diagnostico'] ?? null;
        $this->observaciones = $data['observaciones'] ?? null;
        $this->estado = $data['estado'] ?? 'Activo'; 
    }
}

/**
 * Clase InternadoFactory: Crea el objeto DTO.
 * Métodos: crearInternado().
 */
class InternadoFactory {
    public static function crearInternado(array $data): InternadoDTO {
        return new InternadoDTO($data);
    }
}

// --------------------------------------------------------------------------------
// PATRÓN: CHAIN OF RESPONSIBILITY (Validadores) 🔗
// --------------------------------------------------------------------------------
/**
 * AbstractValidator: Manejador abstracto de la cadena de validación.
 * Atributos: nextHandler.
 * Métodos: setNext(), handle().
 */
abstract class AbstractValidator {
    private $nextHandler = null;
    public function setNext(AbstractValidator $handler): AbstractValidator {
        $this->nextHandler = $handler;
        return $handler;
    }
    public function handle(InternadoDTO $internado): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($internado);
        }
        return null;
    }
}

/**
 * InternadoActivoValidator: Valida que el paciente esté actualmente 'Activo' para poder dar de alta.
 * Atributos: objInternadoDAO.
 */
class InternadoActivoValidator extends AbstractValidator {
    private $objInternadoDAO;

    public function __construct(InternadoDAO $dao) { $this->objInternadoDAO = $dao; }

    public function handle(InternadoDTO $internado): ?string {
        $currentInternado = $this->objInternadoDAO->obtenerInternadoPorId($internado->idInternado);

        if (!$currentInternado) {
            return "Internado no encontrado.";
        }
        if ($currentInternado['estado'] != 'Activo') {
            return "Solo se puede dar de alta a pacientes con estado 'Activo'. Estado actual: " . $currentInternado['estado'];
        }
        
        // Actualiza el DTO con la info actual (se utiliza Factory/Builder de nuevo)
        // Esto es crucial para que el Command tenga todos los datos necesarios para la actualización.
        $internado->idHabitacion = $currentInternado['id_habitacion'];
        $internado->idMedico = $currentInternado['id_medico'];
        $internado->diagnostico = $currentInternado['diagnostico_ingreso'];
        $internado->observaciones = $currentInternado['observaciones'];

        return parent::handle($internado);
    }
}


// --------------------------------------------------------------------------------
// PATRÓN: COMMAND 📦
// --------------------------------------------------------------------------------
/**
 * Interfaz Command: Define el método de ejecución.
 * Métodos: execute(), getValidationMessage().
 */
interface Command {
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

/**
 * DarAltaInternadoCommand: Encapsula la lógica para dar de alta a un paciente.
 * Atributos: objInternadoDAO, internado, validationChain, validationMessage.
 * Métodos: __construct(), buildValidationChain(), execute(), getValidationMessage().
 */
class DarAltaInternadoCommand implements Command {
    private $objInternadoDAO;
    private $internado;
    private $validationChain;
    private $validationMessage = null;

    public function __construct(int $idInternado) {
        $this->objInternadoDAO = new InternadoDAO();
        // Creamos un DTO inicial, la validación lo completará con el resto de datos
        $this->internado = InternadoFactory::crearInternado(['idInternado' => $idInternado, 'estado' => 'Alta']);
        $this->buildValidationChain();
    }

    private function buildValidationChain() {
        // La cadena solo necesita validar que el estado permita el alta.
        $h1 = new InternadoActivoValidator($this->objInternadoDAO);
        // $h2, $h3... se añadirían aquí si se necesitaran más validaciones
        $this->validationChain = $h1;
    }

    public function execute(): bool {
        // Ejecución de la validación
        $this->validationMessage = $this->validationChain->handle($this->internado);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // Ejecución de la acción de negocio
        $fechaAlta = date('Y-m-d H:i:s');
        
        // El DTO ahora contiene todos los datos necesarios para la actualización, 
        // ya que el validador los cargó.
        return $this->objInternadoDAO->editarInternado(
            $this->internado->idInternado,
            $this->internado->idHabitacion, 
            $this->internado->idMedico,
            $fechaAlta, // Fecha de alta actual
            $this->internado->diagnostico,
            $this->internado->observaciones,
            'Alta', // Nuevo estado
            $this->internado->idHabitacion // Habitación anterior (se libera implícitamente en el DAO)
        );
    }

    public function getValidationMessage(): ?string {
        return $this->validationMessage;
    }
}

// --------------------------------------------------------------------------------
// PATRÓN: MEDIATOR / CONTROLADOR 🤝
// --------------------------------------------------------------------------------
/**
 * Clase controlGestionInternados: Actúa como el Mediator, coordinando el flujo.
 * Atributos: objMensaje. (No se necesita InternadoDAO directamente, lo maneja el Command).
 * Métodos: __construct(), darAltaInternado().
 */
class controlGestionInternados {
    private $objMensaje;

    public function __construct() {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Recibe la solicitud de la Vista/Get e invoca el Command.
     * @param int $idInternado ID del paciente a dar de alta.
     * Patrón: MEDIATOR (Coordina el Command y la respuesta).
     */
    public function darAltaInternado($idInternado) {
        
        if (empty($idInternado) || !is_numeric($idInternado)) {
            $this->objMensaje->mensajeSistemaShow("ID de internado no válido.", "./indexGestionInternados.php", "error");
            return;
        }

        // 1. Creación e Invocación del Command
        $command = new DarAltaInternadoCommand((int)$idInternado);
        $resultado = $command->execute();

        // 2. Manejo de la respuesta del Command (Lógica del Mediator)
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            $this->objMensaje->mensajeSistemaShow($mensajeError, "./indexGestionInternados.php", "error");
        } elseif ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Paciente dado de alta correctamente. La habitación ha sido liberada.", "./indexGestionInternados.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al dar de alta al paciente. Intente de nuevo.", "./indexGestionInternados.php", "error");
        }
    }
}
?>