<?php
// Directorio: /controlador/historial/editarHistorialMedico/controlEditarHistorialMedico.php

include_once('../../../../../modelo/RegistroMedicoDAO.php'); 
include_once('../../../../../shared/mensajeSistema.php'); 

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EditarHistorialDTO {
    // Atributos: Los datos del formulario
    public $idRegistro;
    public $motivoConsulta;
    public $enfermedadActual;
    public $tiempoEnfermedad;
    public $signosSintomas;
    public $riesgos;
    public $motivoUltimaVisita;
    public $ultimaVisitaMedica;
    
    // Método: Constructor
    public function __construct(array $data) {
        // Asignación y limpieza de atributos
        $this->idRegistro = (int)($data['registro_medico_id'] ?? 0);
        $this->motivoConsulta = $this->limpiarTexto($data['motivo_consulta'] ?? '');
        $this->enfermedadActual = $this->limpiarTexto($data['enfermedad_actual'] ?? '');
        $this->tiempoEnfermedad = $this->limpiarTexto($data['tiempo_enfermedad'] ?? '');
        $this->signosSintomas = $this->limpiarTexto($data['signos_sintomas'] ?? '');
        $this->riesgos = $this->limpiarTexto($data['riesgos'] ?? '');
        $this->motivoUltimaVisita = $this->limpiarTexto($data['motivo_ultima_visita'] ?? '');
        $this->ultimaVisitaMedica = $data['ultima_visita_medica'] ?? null;
    }
    
    // Método: Auxiliar para limpieza 
    private function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto ?? ''));
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class EditarHistorialFactory {
    // Método: crearDTO
    public static function crearDTO(array $data): EditarHistorialDTO {
        // Método: Crea y retorna el DTO
        return new EditarHistorialDTO($data);
    }
    
    // Método: crearComando (Factory Method)
    public static function crearComando(string $action, EditarHistorialDTO $dto): Comando {
        switch ($action) {
            case 'actualizar':
                // Método: Crea y retorna el comando de edición
                return new ActualizarHistorialCommand($dto);
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
    abstract public function handle(EditarHistorialDTO $dto): ?string;
    
    // Método: passNext (Concreto)
    protected function passNext(EditarHistorialDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de campos obligatorios
class RequeridosValidator extends AbstractValidatorHandler {
    // Método: handle
    public function handle(EditarHistorialDTO $dto): ?string
    {
        // Atributos obligatorios: ID de registro y motivo de consulta
        if ($dto->idRegistro <= 0 || empty($dto->motivoConsulta)) {
            return "El ID del registro o el Motivo de Consulta son obligatorios y válidos.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de existencia del registro
class ExistenciaValidator extends AbstractValidatorHandler {
    // Atributo: $objDAO
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { 
        $this->objDAO = new RegistroMedicoDAO(); 
    }

    // Método: handle
    public function handle(EditarHistorialDTO $dto): ?string
    {
        // Método: obtenerRegistroPorId
        if (!$this->objDAO->obtenerRegistroPorId($dto->idRegistro)) {
            return "El Registro Médico con ID {$dto->idRegistro} no existe.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 3: Validación de fecha 
class FechaValidator extends AbstractValidatorHandler {
    // Método: handle
    public function handle(EditarHistorialDTO $dto): ?string
    {
        $fecha = $dto->ultimaVisitaMedica;
        if ($fecha) {
            // Validación de formato
            $patron = '/^\d{4}-\d{2}-\d{2}$/';
            if (!preg_match($patron, $fecha)) {
                return "La fecha de última visita médica no tiene un formato válido (YYYY-MM-DD).";
            }
            
            // Validación de fecha futura
            if (strtotime($fecha) > time()) {
                return "La fecha de última visita médica no puede ser futura.";
            }
        }
        return $this->passNext($dto);
    }
}


// COMMAND Concreto: Actualizar Historial 📦
class ActualizarHistorialCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (RegistroMedicoDAO)
    private $dto;
    private $validationChain;
    // Atributo: $validationMessage (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EditarHistorialDTO $dto)
    {
        $this->objDAO = new RegistroMedicoDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new RequeridosValidator();
        $existenciaValidator = new ExistenciaValidator();
        $fechaValidator = new FechaValidator();

        // Método: setNext
        $this->validationChain
             ->setNext($existenciaValidator)
             ->setNext($fechaValidator);
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
        // Método: editarRegistro
        return $this->objDAO->editarRegistro(
            $this->dto->idRegistro,
            $this->dto->riesgos,
            $this->dto->motivoConsulta,
            $this->dto->enfermedadActual,
            $this->dto->tiempoEnfermedad,
            $this->dto->signosSintomas,
            $this->dto->motivoUltimaVisita,
            $this->dto->ultimaVisitaMedica
        );
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
class controlEditarHistorialPaciente
{
    // Atributos: Dependencias
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    // Método: ejecutarComando (Punto de coordinación central)
    // Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
    public function ejecutarComando(string $action, array $data)
    {
        // Atributo: $id
        $id = (int)($data['registro_medico_id'] ?? 0);
        // Atributo: $urlRetorno
        $urlRetorno = './indexEditarHistorialMedico.php?reg_id=' . $id;
        $urlListado = '../indexHistorialMedico.php';

        try {
            // Factory Method: Creación del DTO
            // Método: crearDTO
            $dto = EditarHistorialFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            // Método: crearComando
            $command = EditarHistorialFactory::crearComando($action, $dto);

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
                    '✅ Registro Médico actualizado correctamente.', 
                    $urlListado, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al actualizar el registro. Verifique que se hayan realizado cambios o fallo en DB.', 
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