<?php
// Directorio: /controlador/internado/controlEditarInternado.php

include_once('../../../../../modelo/InternadoDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class InternadoDTO {
    // Atributos: Almacena los datos del internado
    public $idInternado; public $idHabitacion; public $idHabitacionAnterior;
    public $idMedico; public $fechaAlta; public $diagnostico;
    public $observaciones; 
    // Atributo: Relacionado con el Patrón State
    public $estado; 
    public $modificadoPor;

    // Método: Constructor
    public function __construct(array $data) {
        $this->idInternado = $data['idInternado'] ?? null;
        $this->idHabitacion = $data['idHabitacion'] ?? null;
        $this->idHabitacionAnterior = $data['idHabitacionAnterior'] ?? null;
        $this->idMedico = $data['idMedico'] ?? null;
        $this->fechaAlta = $data['fechaAlta'] ?? null;
        $this->diagnostico = $data['diagnostico'] ?? '';
        $this->observaciones = $data['observaciones'] ?? '';
        $this->estado = $data['estado'] ?? '';
        $this->modificadoPor = $data['modificadoPor'] ?? null;
    }
}

// Patrón: FACTORY METHOD 🏭
class InternadoFactory {
    // Método: Crea una instancia de InternadoDTO
    public static function crearInternado(array $data): InternadoDTO {
        return new InternadoDTO($data);
    }
}

// CHAIN OF RESPONSIBILITY (Manejadores de Validación) 🔗
abstract class AbstractValidatorHandler {
    // Atributo: Almacena el siguiente manejador
    private $nextHandler = null;

    // Método: setNext
    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    // Método: handle (Abstracto en la interfaz, concreto aquí para encadenamiento)
    public function handle(InternadoDTO $internado): ?string
    {
        if ($this->nextHandler) {
            // Método: Llama al siguiente en la cadena
            return $this->nextHandler->handle($internado);
        }
        return null; 
    }
}

// Handler Concreto 1: Validaciones de Campos y Estado Base
class CamposBaseValidator extends AbstractValidatorHandler {
    public function handle(InternadoDTO $internado): ?string
    {
        // ... (Validaciones básicas del código original) ...
        if (empty($internado->idInternado) || empty($internado->idHabitacion) || 
            empty($internado->idMedico) || empty($internado->diagnostico) || 
            empty($internado->estado)) 
        {
            return "Todos los campos obligatorios deben estar completos.";
        }
        if (!is_numeric($internado->idInternado) || !is_numeric($internado->idHabitacion) || 
            !is_numeric($internado->idMedico) || !is_numeric($internado->idHabitacionAnterior)) {
            return "IDs de internado, habitación o médico no válidos.";
        }
        return parent::handle($internado);
    }
}

// Handler Concreto 2: Validaciones de Integridad y Pre-condición (Estado Activo)
class EntidadPrecondicionValidator extends AbstractValidatorHandler
{
    private $objInternado;
    private $objAuxiliar;

    // Método: Constructor
    public function __construct() { 
        $this->objInternado = new InternadoDAO(); 
        $this->objAuxiliar = new InternadoAuxiliarDAO(); 
    }

    public function handle(InternadoDTO $internado): ?string
    {
        // Método: `obtenerInternadoPorId` (Para verificar existencia)
        $internadoActual = $this->objInternado->obtenerInternadoPorId($internado->idInternado);
        if (!$internadoActual) { return "El internado no existe."; }

        // Patrón STATE: Pre-condición de edición
        if ($internadoActual['estado'] != 'Activo') { 
            return "Solo se pueden editar internados con estado 'Activo'.";
        }
        
        // Método: `medicoExiste`
        if (!$this->objAuxiliar->medicoExiste($internado->idMedico)) {
            return "El médico seleccionado no existe o no está activo.";
        }
        
        // Validación de cambio de habitación
        if ($internado->idHabitacion != $internado->idHabitacionAnterior) {
            // Método: `habitacionDisponible`
            if (!$this->objInternado->habitacionDisponible($internado->idHabitacion)) {
                return "La habitación seleccionada ya no está disponible.";
            }
        }
        
        // Atributo: `fecha_ingreso` (Se añade al DTO para la siguiente validación)
        $internado->fechaIngreso = $internadoActual['fecha_ingreso']; 

        return parent::handle($internado);
    }
}

// Handler Concreto 3: Validaciones de Fechas (Post-condición/Transición)
class FechasTransicionValidator extends AbstractValidatorHandler
{
    public function handle(InternadoDTO $internado): ?string
    {
        $fechaAltaFormateada = null;
        
        // Patrón STATE: Validaciones al cambiar a un estado final (no Activo)
        if ($internado->estado != 'Activo' && !empty($internado->fechaAlta)) {
            try {
                // Atributos: fechas para comparación
                $fechaAltaDateTime = new DateTime($internado->fechaAlta);
                $fechaIngresoDateTime = new DateTime($internado->fechaIngreso);
                $fechaActual = new DateTime();

                if ($fechaAltaDateTime > $fechaActual) { return "La fecha de alta no puede ser futura."; }
                if ($fechaAltaDateTime < $fechaIngresoDateTime) { return "La fecha de alta no puede ser anterior a la fecha de ingreso."; }

                $fechaAltaFormateada = $fechaAltaDateTime->format('Y-m-d H:i:s');
            } catch (Exception $e) {
                return "Formato de fecha de alta no válido.";
            }
        }

        // Si el estado cambia a no Activo y no hay fecha, usar fecha actual.
        if ($internado->estado != 'Activo' && empty($internado->fechaAlta)) {
            $fechaAltaFormateada = date('Y-m-d H:i:s');
        }

        // Atributo: Se actualiza el DTO con la fecha formateada para el Command
        $internado->fechaAlta = $fechaAltaFormateada; 
        
        return parent::handle($internado);
    }
}

// COMMAND (Lógica de Ejecución) 📦
class EditarInternadoCommand implements Command
{
    // Atributos: El DTO y el Receptor
    private $objInternadoDAO;
    private $internado;
    private $validationChain;
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(array $internadoData)
    {
        $this->objInternadoDAO = new InternadoDAO(); // Receptor
        // Factory Method: Creación del DTO
        $this->internado = InternadoFactory::crearInternado($internadoData);
        $this->buildValidationChain();
    }
    
    // Método: Configura el orden de la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        $h1 = new CamposBaseValidator();
        $h2 = new EntidadPrecondicionValidator();
        $h3 = new FechasTransicionValidator();
        
        // Método: Encadenamiento
        $h1->setNext($h2)->setNext($h3);
        $this->validationChain = $h1;
    }

    // Método: Ejecuta la lógica central
    public function execute(): bool
    {
        // Chain of Responsibility: Ejecución de la cadena de validación
        // Método: handle (devuelve null si es exitoso, string si hay error)
        $this->validationMessage = $this->validationChain->handle($this->internado);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // Ejecución del receptor (DAO)
        // Patrón STATE: La lógica de la transición (liberar habitación, actualizar fechaAlta)
        // se maneja dentro del método del DAO para garantizar la atomicidad (transacción).
        return $this->objInternadoDAO->editarInternado(
            $this->internado->idInternado,
            $this->internado->idHabitacion,
            $this->internado->idMedico,
            $this->internado->fechaAlta, // Ya formateada o nula
            $this->internado->diagnostico,
            $this->internado->observaciones,
            $this->internado->estado,
            $this->internado->idHabitacionAnterior
        );
    }

    // Método: getValidationMessage
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    // Método: Obtiene el estado final para el Mediator
    public function getEstadoFinal(): string
    {
        return $this->internado->estado;
    }
    
    // Método: Obtiene el ID de la habitación nueva
    public function getIdHabitacion(): int
    {
        return $this->internado->idHabitacion;
    }
    
    // Método: Obtiene el ID de la habitación anterior
    public function getIdHabitacionAnterior(): int
    {
        return $this->internado->idHabitacionAnterior;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlEditarInternado
{
    // Atributos: Dependencias de comunicación
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    // Método: El 'Invoker' y coordinador del flujo (Método Central)
    public function ejecutarComandoEditarInternado(array $internadoData)
    {
        // Command: Se crea y ejecuta el comando
        $command = new EditarInternadoCommand($internadoData);
        $resultado = $command->execute();

        // Atributo: URL de retorno en caso de error
        $urlRetorno = './indexEditarInternado.php?id=' . $internadoData['idInternado'];
        
        // Mediator: Lógica para manejar la respuesta del Command
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            // Manejo de error de validación (Chain of Responsibility)
            $this->objMensaje->mensajeSistemaShow(
                "❌ " . $mensajeError,
                $urlRetorno,
                "error"
            );
        } elseif ($resultado) {
            // Manejo de éxito
            $mensaje = "✅ Internado actualizado correctamente.";
            
            // Mensajes adicionales basados en el Patrón STATE y cambios de habitación
            if ($command->getIdHabitacion() != $command->getIdHabitacionAnterior()) {
                $mensaje .= " La habitación ha sido cambiada y la anterior liberada.";
            }
            if ($command->getEstadoFinal() != 'Activo') {
                $mensaje .= " El paciente ha sido dado de **" . $command->getEstadoFinal() . "** y la habitación liberada.";
            }

            $this->objMensaje->mensajeSistemaShow(
                $mensaje,
                "../indexGestionInternados.php",
                "success"
            );
        } else {
            // Manejo de error de base de datos
            $this->objMensaje->mensajeSistemaShow(
                "❌ Error al actualizar el internado en la base de datos.",
                $urlRetorno,
                "error"
            );
        }
    }
}
?>