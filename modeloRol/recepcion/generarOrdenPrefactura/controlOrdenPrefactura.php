<?php
// FILE: controlOrdenPrefactura.php
include_once('../../../modelo/ordenPagoDAO.php');
include_once('../../../shared/mensajeSistema.php');

// ===============================================
// === PATRONES: FÁBRICA, CHAIN, COMMAND, STATE ===
// ===============================================

// PATRÓN: FACTORY METHOD (Entidad/DTO)
class OrdenPrefactura {
    // ATRIBUTOS
    public $idOrden; 
    public $estado; // ATRIBUTO que define el estado

    // MÉTODO (Constructor)
    public function __construct($idOrden, $estado = 'Pendiente') {
        $this->idOrden = (int)$idOrden;
        $this->estado = $estado;
    }
}

class OrdenFactory {
    // MÉTODO (Creación de la entidad)
    public static function crearOrden(array $data): OrdenPrefactura {
        return new OrdenPrefactura(
            $data['idOrden'] ?? 0, 
            $data['estado'] ?? 'Pendiente'
        );
    }
}

// PATRÓN: CHAIN OF RESPONSIBILITY (Abstract Handler)
abstract class AbstractOrdenHandler {
    // ATRIBUTO
    private $nextHandler = null; 

    // MÉTODO (Encadenar el siguiente manejador)
    public function setNext(AbstractOrdenHandler $handler): AbstractOrdenHandler {
        $this->nextHandler = $handler;
        return $handler;
    }
    
    // MÉTODO ABSTRACTO (Ejecución de la lógica de la cadena)
    abstract public function handle(OrdenPrefactura $orden): ?string;

    // MÉTODO (Paso al siguiente en la cadena)
    protected function handleNext(OrdenPrefactura $orden): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($orden);
        }
        return null;
    }
}

// CHAIN OF RESPONSIBILITY (Handler Concreto + PATRÓN: STATE para la validación de transición)
class EliminarOrdenValidator extends AbstractOrdenHandler {
    // ATRIBUTO
    private $dao;

    // MÉTODO (Constructor)
    public function __construct() { $this->dao = new OrdenPagoDAO(); }
    
    // MÉTODO (Lógica de validación)
    public function handle(OrdenPrefactura $orden): ?string
    {
        $ordenExistente = $this->dao->obtenerOrdenPorId($orden->idOrden);
        
        if (empty($ordenExistente)) { 
            return "La orden de prefactura ID {$orden->idOrden} no existe."; 
        }

        // Validación de Estado (Regla del PATRÓN STATE: solo se permite eliminar si el estado es 'Pendiente')
        if ($ordenExistente['estado'] != 'Pendiente') {
            return "Solo se puede eliminar una orden con estado 'Pendiente'. El estado actual es '{$ordenExistente['estado']}'.";
        }
        
        // Pasa al siguiente si la validación es exitosa
        return $this->handleNext($orden);
    }
}

// PATRÓN: COMMAND (Interfaz)
interface Command {
    // MÉTODO ABSTRACTO (Ejecución del comando)
    public function execute(): bool; 
    // MÉTODO ABSTRACTO (Mensaje de error de validación)
    public function getValidationMessage(): ?string;
}

// PATRÓN: COMMAND (Comando Concreto para Eliminar)
class EliminarOrdenCommand implements Command {
    // ATRIBUTOS
    private $dao;
    private $orden; // Receptor del comando (la entidad)
    private $validationChain;
    private $validationMessage = null;

    // MÉTODO (Constructor)
    public function __construct(array $data) {
        $this->dao = new OrdenPagoDAO();
        // Uso del Factory Method
        $this->orden = OrdenFactory::crearOrden($data); 
        $this->buildValidationChain();
    }
    
    // MÉTODO (Construcción de la cadena)
    private function buildValidationChain() {
        $v1 = new EliminarOrdenValidator();
        $this->validationChain = $v1; 
    }

    // MÉTODO (Ejecuta la validación y luego la acción)
    public function execute(): bool {
        // Ejecución de la Cadena de Responsabilidad (Chain of Responsibility)
        $this->validationMessage = $this->validationChain->handle($this->orden);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // Ejecuta la acción en el Receptor (DAO)
        return $this->dao->eliminarOrden($this->orden->idOrden);
    }

    // MÉTODO (Retorna el resultado de la validación)
    public function getValidationMessage(): ?string {
        return $this->validationMessage;
    }
}


// =========================================
// === CONTROLADOR (PATRÓN: MEDIATOR) ===
// =========================================
class controlOrdenPrefactura
{
    // ATRIBUTOS
    private $objMensaje;

    // MÉTODO (Constructor)
    public function __construct() {
        // Se asume que OrdenPago existe en el DAO
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * MÉTODO (El Mediator que orquesta la acción de negocio).
     * Reemplaza la lógica original de 'eliminarOrden' por la ejecución del Command.
     */
    public function ejecutarAccion($accion, $data)
    {
        $command = null;

        if ($accion === 'eliminar') {
            // PATRÓN: FACTORY METHOD (Implícito) & COMMAND (Creación)
            $command = new EliminarOrdenCommand($data);
        } else {
            $this->objMensaje->mensajeSistemaShow("Acción no soportada.", "./indexOdenPrefactura.php", "systemOut", false);
            return;
        }
        
        // Ejecución del Command
        $resultado = $command->execute();

        // Lógica de respuesta del Mediator
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            $this->objMensaje->mensajeSistemaShow($mensajeError, "./indexOdenPrefactura.php", "systemOut", false);
        } elseif ($resultado) {
            $this->objMensaje->mensajeSistemaShow("Orden de prefactura eliminada correctamente.", "./indexOdenPrefactura.php", "success");
        } else {
            $this->objMensaje->mensajeSistemaShow("Error al ejecutar la acción en la base de datos.", "./indexOdenPrefactura.php", "error");
        }
    }
    
    // El método 'eliminarOrden' original del controlador se elimina/reemplaza.
}
?>