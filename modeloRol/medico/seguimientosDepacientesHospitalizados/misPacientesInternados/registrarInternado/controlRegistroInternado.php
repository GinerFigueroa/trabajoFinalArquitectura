<?php
include_once('../../../../../modelo/InternadoDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. DTO/ENTITY, FACTORY METHOD, y PATRÓN STATE (Inicial) 🏥
// ==========================================================

/**
 * Clase Internado (DTO/Entity)
 * ATRIBUTOS: idPaciente, idHabitacion, idMedico, fechaIngreso, diagnostico, observaciones, estado, creadoPor
 * MÉTODOS: __construct
 */
class Internado {
    // ATRIBUTOS
    public $idPaciente; public $idHabitacion; public $idMedico;
    public $fechaIngreso; public $diagnostico; public $observaciones;
    public $estado; // PATRÓN STATE (Estado inicial "Activo")
    public $creadoPor;

    public function __construct(array $data) {
        $this->idPaciente = $data['idPaciente'] ?? null;
        $this->idHabitacion = $data['idHabitacion'] ?? null;
        $this->idMedico = $data['idMedico'] ?? null;
        $this->fechaIngreso = $data['fechaIngreso'] ?? null;
        $this->diagnostico = $data['diagnostico'] ?? '';
        $this->observaciones = $data['observaciones'] ?? '';
        $this->estado = 'Activo'; // Estado inicial del internado
        $this->creadoPor = $data['creadoPor'] ?? null;
    }
}

/**
 * Clase InternadoFactory (PATRÓN: FACTORY METHOD)
 * ATRIBUTOS: (Ninguno)
 * MÉTODOS: crearInternado
 */
class InternadoFactory {
    public static function crearInternado(array $data): Internado {
        return new Internado($data);
    }
}

// ==========================================================
// 2. PATRÓN CHAIN OF RESPONSIBILITY (Validación) 🔗
// ==========================================================

/**
 * AbstractValidatorHandler (CoR Base)
 * ATRIBUTOS: $nextHandler (AbstractValidatorHandler | null)
 * MÉTODOS: setNext, handle
 */
abstract class AbstractValidatorHandler {
    private $nextHandler = null;

    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler {
        $this->nextHandler = $handler;
        return $handler;
    }

    public function handle(Internado $internado): ?string {
        // Ejecuta el siguiente en la cadena si existe
        if ($this->nextHandler) {
            return $this->nextHandler->handle($internado);
        }
        return null;
    }
}

/**
 * CamposObligatoriosHandler (CoR Handler)
 * ATRIBUTOS: (Ninguno)
 * MÉTODOS: handle
 */
class CamposObligatoriosHandler extends AbstractValidatorHandler {
    public function handle(Internado $internado): ?string {
        if (empty($internado->idPaciente) || empty($internado->idHabitacion) || 
            empty($internado->idMedico) || empty($internado->fechaIngreso) || empty($internado->diagnostico)) {
            return "Todos los campos marcados con (*) son obligatorios.";
        }
        return parent::handle($internado);
    }
}

/**
 * FechaIngresoHandler (CoR Handler)
 * ATRIBUTOS: (Ninguno)
 * MÉTODOS: handle
 */
class FechaIngresoHandler extends AbstractValidatorHandler {
    public function handle(Internado $internado): ?string {
        try {
            $fechaIngresoDateTime = new DateTime($internado->fechaIngreso);
            $fechaActual = new DateTime();
            
            if ($fechaIngresoDateTime > $fechaActual) {
                return "La fecha de ingreso no puede ser futura.";
            }
            // Formatea la fecha para la persistencia
            $internado->fechaIngreso = $fechaIngresoDateTime->format('Y-m-d H:i:s');
        } catch (\Exception $e) {
            return "Formato de fecha de ingreso inválido.";
        }
        return parent::handle($internado);
    }
}

/**
 * DisponibilidadHandler (CoR Handler)
 * ATRIBUTOS: $objInternado (InternadoDAO), $objAuxiliar (InternadoAuxiliarDAO)
 * MÉTODOS: __construct, handle
 */
class DisponibilidadHandler extends AbstractValidatorHandler {
    private $objInternado;
    private $objAuxiliar;

    public function __construct() { 
        $this->objInternado = new InternadoDAO(); 
        $this->objAuxiliar = new InternadoAuxiliarDAO(); 
    }

    public function handle(Internado $internado): ?string {
        // Validar existencia de entidades y disponibilidad
        if (!$this->objAuxiliar->pacienteExiste($internado->idPaciente) || !$this->objAuxiliar->medicoExiste($internado->idMedico)) {
            return "El paciente o médico seleccionado no existe o no está activo.";
        }
        if ($this->objInternado->pacienteYaInternado($internado->idPaciente)) {
            return "El paciente ya se encuentra internado actualmente.";
        }
        if (!$this->objInternado->habitacionDisponible($internado->idHabitacion)) {
            return "La habitación seleccionada no está disponible.";
        }

        return parent::handle($internado);
    }
}


// ==========================================================
// 3. PATRÓN COMMAND (Registro) 📦
// ==========================================================

/**
 * Interfaz Command
 * ATRIBUTOS: (Ninguno)
 * MÉTODOS: execute, getValidationMessage
 */
interface Command {
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

/**
 * RegistroInternadoCommand (Clase Command)
 * ATRIBUTOS: $objInternadoDAO, $internado (Internado DTO), $validationChain, $validationMessage
 * MÉTODOS: __construct, buildValidationChain, execute, getValidationMessage
 */
class RegistroInternadoCommand implements Command {
    private $objInternadoDAO;
    private $internado;
    private $validationChain;
    private $validationMessage = null;

    public function __construct(array $internadoData) {
        $this->objInternadoDAO = new InternadoDAO();
        // PATRÓN FACTORY METHOD
        $this->internado = InternadoFactory::crearInternado($internadoData);
        $this->buildValidationChain();
    }

    private function buildValidationChain() {
        // Configuración de la CADENA DE RESPONSABILIDAD (CoR)
        $h1 = new CamposObligatoriosHandler();
        $h2 = new FechaIngresoHandler();
        $h3 = new DisponibilidadHandler();
        
        $h1->setNext($h2)->setNext($h3);
        $this->validationChain = $h1;
    }

    public function execute(): bool {
        // Ejecuta la CADENA DE RESPONSABILIDAD
        $this->validationMessage = $this->validationChain->handle($this->internado);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // Lógica de Persistencia (llama al DAO con los datos validados)
        return $this->objInternadoDAO->registrarInternado(
            $this->internado->idPaciente,
            $this->internado->idHabitacion,
            $this->internado->idMedico,
            $this->internado->fechaIngreso, // Ya formateada por FechaIngresoHandler
            trim($this->internado->diagnostico),
            trim($this->internado->observaciones)
        );
    }

    public function getValidationMessage(): ?string {
        return $this->validationMessage;
    }
}

// ==========================================================
// 4. PATRÓN MEDIATOR (Controlador) 🧘
// ==========================================================

/**
 * Clase controlRegistroInternado (PATRÓN: MEDIATOR)
 * ATRIBUTOS: $objMensaje (mensajeSistema)
 * MÉTODOS: __construct, registrarInternado (método del Mediator)
 */
class controlRegistroInternado {
    private $objMensaje;

    public function __construct() {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método principal que orquesta la ejecución del Command y maneja la respuesta.
     * @param array $internadoData Datos recibidos del GET.
     */
    public function registrarInternado(array $internadoData) {
        $urlRetorno = './indexRegistroInternado.php';

        // Crea el COMMAND con los datos
        $command = new RegistroInternadoCommand($internadoData);
        
        // Ejecuta el COMMAND
        $resultado = $command->execute();

        // Lógica de manejo de respuesta (Mediator)
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            // Error de validación (desde la Chain of Responsibility)
            $this->objMensaje->mensajeSistemaShow($mensajeError, $urlRetorno, "error");
        } elseif ($resultado) {
            // Éxito
            $this->objMensaje->mensajeSistemaShow(
                "Internado registrado correctamente. El paciente ha sido asignado a la habitación.",
                "../indexGestionInternados.php",
                "success"
            );
        } else {
            // Error de persistencia (DAO)
            $this->objMensaje->mensajeSistemaShow(
                "Error al registrar el internado. Por favor, intente nuevamente.",
                $urlRetorno,
                "error"
            );
        }
    }
}
?>