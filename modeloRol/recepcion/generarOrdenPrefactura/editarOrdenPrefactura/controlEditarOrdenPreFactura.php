<?php
// FILE: controlEditarOrdenPreFactura.php
include_once('../../../../modelo/OrdenPagoDAO.php');
include_once('../../../../shared/mensajeSistema.php');

// ===============================================
// === PATRONES: FÁBRICA, CHAIN, COMMAND, DTO ===
// ===============================================

// PATRÓN: FACTORY METHOD (Entidad/DTO)
class OrdenEditDTO {
    // ATRIBUTOS
    public $idOrden; 
    public $concepto;
    public $monto; 

    // MÉTODO (Constructor)
    public function __construct($idOrden, $concepto, $monto) {
        $this->idOrden = (int)$idOrden;
        $this->concepto = $concepto;
        $this->monto = (float)$monto;
    }
}

class OrdenEditFactory {
    // MÉTODO (Creación de la entidad DTO)
    public static function createDTO(array $data): OrdenEditDTO {
        return new OrdenEditDTO(
            $data['idOrden'] ?? 0, 
            $data['concepto'] ?? '', 
            $data['monto'] ?? 0
        );
    }
}

// PATRÓN: CHAIN OF RESPONSIBILITY (Abstract Handler)
abstract class AbstractEditHandler {
    // ATRIBUTO
    private $nextHandler = null; 

    // MÉTODO (Encadenar el siguiente manejador)
    public function setNext(AbstractEditHandler $handler): AbstractEditHandler {
        $this->nextHandler = $handler;
        return $handler;
    }
    
    // MÉTODO ABSTRACTO (Ejecución de la lógica de la cadena)
    abstract public function handle(OrdenEditDTO $ordenDTO): ?string;

    // MÉTODO (Paso al siguiente en la cadena)
    protected function handleNext(OrdenEditDTO $ordenDTO): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($ordenDTO);
        }
        return null; // Cadena finalizada exitosamente
    }
}

// CHAIN OF RESPONSIBILITY (Handler Concreto 1: Validar datos de entrada)
class InputValidatorHandler extends AbstractEditHandler {
    // MÉTODO (Validación de campos obligatorios)
    public function handle(OrdenEditDTO $ordenDTO): ?string {
        if (empty($ordenDTO->concepto) || !is_numeric($ordenDTO->monto) || $ordenDTO->monto <= 0) {
            return 'Faltan campos obligatorios o el monto no es válido (debe ser mayor a cero).';
        }
        return $this->handleNext($ordenDTO);
    }
}

// CHAIN OF RESPONSIBILITY (Handler Concreto 2: Validar estado de la orden - PATRÓN STATE)
class StatusValidatorHandler extends AbstractEditHandler {
    // ATRIBUTO
    private $dao;

    // MÉTODO (Constructor)
    public function __construct() { $this->dao = new OrdenPago(); }
    
    // MÉTODO (Validación de Estado para la Edición)
    public function handle(OrdenEditDTO $ordenDTO): ?string
    {
        $ordenExistente = $this->dao->obtenerOrdenPorId($ordenDTO->idOrden);
        
        if (empty($ordenExistente)) { 
            return "La orden de prefactura ID {$ordenDTO->idOrden} no existe."; 
        }

        // Regla del PATRÓN STATE: solo se permite la edición si el estado es 'Pendiente'
        if ($ordenExistente['estado'] != 'Pendiente') {
            return "Solo se puede editar una orden con estado 'Pendiente'. El estado actual es '{$ordenExistente['estado']}'.";
        }
        
        return $this->handleNext($ordenDTO);
    }
}

// PATRÓN: COMMAND (Interfaz)
interface Command {
    // MÉTODO ABSTRACTO (Ejecución del comando)
    public function execute(): bool; 
    // MÉTODO ABSTRACTO (Mensaje de error de validación)
    public function getValidationMessage(): ?string;
}

// PATRÓN: COMMAND (Comando Concreto para Editar)
class EditarOrdenCommand implements Command {
    // ATRIBUTOS
    private $dao;
    private $ordenDTO; // Receptor (DTO de datos)
    private $validationChain;
    private $validationMessage = null;

    // MÉTODO (Constructor)
    public function __construct(array $data) {
        $this->dao = new OrdenPago(); // Receptor de la acción final
        // Uso del Factory Method para crear el DTO
        $this->ordenDTO = OrdenEditFactory::createDTO($data); 
        $this->buildValidationChain();
    }
    
    // MÉTODO (Construcción de la cadena)
    private function buildValidationChain() {
        $v1 = new InputValidatorHandler();
        $v2 = new StatusValidatorHandler();
        
        $v1->setNext($v2); // Encadenamiento
        $this->validationChain = $v1; 
    }

    // MÉTODO (Ejecuta la validación y luego la acción DAO)
    public function execute(): bool {
        // Ejecución de la Cadena de Responsabilidad (Chain of Responsibility)
        $this->validationMessage = $this->validationChain->handle($this->ordenDTO);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // Ejecuta la acción en el Receptor (DAO)
        // El DAO debe contener la lógica de actualizar la orden con los nuevos datos
        return $this->dao->editarOrden(
            $this->ordenDTO->idOrden, 
            $this->ordenDTO->concepto, 
            $this->ordenDTO->monto
        );
    }

    // MÉTODO (Retorna el resultado de la validación)
    public function getValidationMessage(): ?string {
        return $this->validationMessage;
    }
}


// =========================================
// === CONTROLADOR (PATRÓN: MEDIATOR) ===
// =========================================
class controlEditarOrdenPreFactura
{
    // ATRIBUTOS
    private $objMensaje;

    // MÉTODO (Constructor)
    public function __construct() {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * MÉTODO (El Mediator que orquesta la ejecución del Command).
     * Reemplaza el método 'editarOrden' original.
     */
    public function ejecutarAccion($data)
    {
        $idOrden = $data['idOrden'] ?? 0;
        $urlRetorno = './indexEditarOrdenPreFactura.php?id=' . $idOrden;

        // 1. Creación del COMMAND (Uso implícito de Factory Method)
        $command = new EditarOrdenCommand($data);
        
        // 2. Ejecución del Command
        $resultado = $command->execute();

        // 3. Lógica de respuesta del Mediator
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            $this->objMensaje->mensajeSistemaShow($mensajeError, $urlRetorno, 'systemOut', false);
        } elseif ($resultado) {
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura N° ' . $idOrden . ' actualizada correctamente.', '../indexOdenPrefactura.php', 'success');
        } else {
            $this->objMensaje->mensajeSistemaShow('Error al actualizar la orden en la base de datos o no se realizaron cambios.', $urlRetorno, 'error');
        }
    }
}
?>