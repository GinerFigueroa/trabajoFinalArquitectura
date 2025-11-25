<?php
// FILE: controlAgregarOdenPreFactura.php

include_once('../../../../modelo/OrdenPagoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

// ===============================================
// === PATRONES: FÁBRICA, CHAIN, COMMAND, DTO ===
// ===============================================

// PATRÓN: FACTORY METHOD (Entidad/DTO)
class OrdenRegistroDTO {
    // ATRIBUTOS
    public $idPaciente; 
    public $idCita;
    public $idInternado;
    public $concepto;
    public $monto; 

    // MÉTODO (Constructor)
    public function __construct($idPaciente, $idCita, $idInternado, $concepto, $monto) {
        // Conversión a tipos de datos seguros y manejo de NULL/empty
        $this->idPaciente = (int)$idPaciente;
        $this->concepto = $concepto;
        $this->monto = (float)$monto;
        $this->idCita = empty($idCita) ? null : (int)$idCita;
        $this->idInternado = empty($idInternado) ? null : (int)$idInternado;
    }
}

class OrdenRegistroFactory {
    // MÉTODO (Creación de la entidad DTO a partir de datos POST)
    public static function createDTO(array $data): OrdenRegistroDTO {
        return new OrdenRegistroDTO(
            $data['idPaciente'] ?? 0, 
            $data['idCita'] ?? null,
            $data['idInternado'] ?? null,
            $data['concepto'] ?? '', 
            $data['monto'] ?? 0
        );
    }
}

// PATRÓN: CHAIN OF RESPONSIBILITY (Abstract Handler)
abstract class AbstractRegistroHandler {
    // ATRIBUTO
    private $nextHandler = null; 

    // MÉTODO (Encadenar el siguiente manejador)
    public function setNext(AbstractRegistroHandler $handler): AbstractRegistroHandler {
        $this->nextHandler = $handler;
        return $handler;
    }
    
    // MÉTODO ABSTRACTO (Ejecución de la lógica de la cadena)
    abstract public function handle(OrdenRegistroDTO $ordenDTO): ?string;

    // MÉTODO (Paso al siguiente en la cadena)
    protected function handleNext(OrdenRegistroDTO $ordenDTO): ?string {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($ordenDTO);
        }
        return null; // Cadena finalizada exitosamente
    }
}

// CHAIN OF RESPONSIBILITY (Handler Concreto 1: Validar datos de entrada)
class InputRegistroValidatorHandler extends AbstractRegistroHandler {
    // MÉTODO (Validación de campos obligatorios)
    public function handle(OrdenRegistroDTO $ordenDTO): ?string {
        // ATRIBUTO implícito: Reglas de validación
        if ($ordenDTO->idPaciente <= 0 || empty($ordenDTO->concepto) || $ordenDTO->monto <= 0) {
            return 'Faltan campos obligatorios o los valores son inválidos (Paciente, Concepto, Monto > 0).';
        }
        return $this->handleNext($ordenDTO);
    }
}

// CHAIN OF RESPONSIBILITY (Handler Concreto 2: Validar Asociación de Servicio)
class ServiceAssociationHandler extends AbstractRegistroHandler {
    // MÉTODO (Validación de Asociación de Servicio)
    public function handle(OrdenRegistroDTO $ordenDTO): ?string {
        // La orden DEBE estar asociada a Cita O Internamiento.
        if ($ordenDTO->idCita === null && $ordenDTO->idInternado === null) {
            return "La orden debe estar asociada obligatoriamente a un ID de Cita o un ID de Internamiento.";
        }
        return $this->handleNext($ordenDTO);
    }
}

// PATRÓN: COMMAND (Interfaz)
interface Command {
    // MÉTODO ABSTRACTO
    public function execute(): bool; 
    // MÉTODO ABSTRACTO
    public function getValidationMessage(): ?string;
}

// PATRÓN: COMMAND (Comando Concreto para Registrar)
class RegistrarOrdenCommand implements Command {
    // ATRIBUTOS
    private $dao;
    private $ordenDTO; 
    private $validationChain;
    private $validationMessage = null;

    // MÉTODO (Constructor)
    public function __construct(array $data) {
        $this->dao = new OrdenPago(); 
        // Uso del Factory Method para crear el DTO (Receptor)
        $this->ordenDTO = OrdenRegistroFactory::createDTO($data); 
        $this->buildValidationChain();
    }
    
    // MÉTODO (Construcción de la cadena)
    private function buildValidationChain() {
        $v1 = new InputRegistroValidatorHandler();
        $v2 = new ServiceAssociationHandler();
        
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
        return $this->dao->registrarOrden(
            $this->ordenDTO->idPaciente, 
            $this->ordenDTO->idCita, 
            $this->ordenDTO->idInternado, 
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
class controlAgregarOdenPreFactura
{
    // ATRIBUTOS
    private $objMensaje;
    private $objCita; // EntidadCitas (DAO auxiliar)
    private $objInternado; // EntidadInternados (DAO auxiliar)

    // MÉTODO (Constructor)
    public function __construct() {
        $this->objMensaje = new mensajeSistema();
        // Se asume que EntidadCitas y EntidadInternados están en OrdenPagoDAO.php
        $this->objCita = new EntidadCitas(); 
        $this->objInternado = new EntidadInternados(); 
    }

    // --- Métodos para AJAX (no requieren reestructuración de patrón) ---
    public function obtenerCitasPorPaciente($idPaciente) {
        return $this->objCita->obtenerCitasPendientesPorPaciente($idPaciente); 
    }
    
    public function obtenerInternadosPorPaciente($idPaciente) {
        return $this->objInternado->obtenerInternamientosPorPaciente($idPaciente);
    }
    // -----------------------------------------------------------------


    /**
     * MÉTODO (El Mediator que orquesta la ejecución del Command).
     * Reemplaza el método 'agregarOrden' original.
     */
    public function ejecutarRegistroAccion($data)
    {
        $urlRetorno = '../indexOdenPrefactura.php';

        // 1. Creación del COMMAND
        $command = new RegistrarOrdenCommand($data);
        
        // 2. Ejecución del Command
        $resultado = $command->execute();

        // 3. Lógica de respuesta del Mediator
        $mensajeError = $command->getValidationMessage();

        if ($mensajeError) {
            // Error de Chain of Responsibility
            $this->objMensaje->mensajeSistemaShow($mensajeError, $urlRetorno, 'systemOut', false);
        } elseif ($resultado) {
            // Éxito del Command
            $this->objMensaje->mensajeSistemaShow('Orden de Prefactura registrada correctamente con estado "Pendiente".', $urlRetorno, 'success');
        } else {
            // Error en el DAO (fallo de inserción)
            $this->objMensaje->mensajeSistemaShow('Error al registrar la Orden de Prefactura. Verifique la integridad de los datos.', $urlRetorno, 'error');
        }
    }
}
?>