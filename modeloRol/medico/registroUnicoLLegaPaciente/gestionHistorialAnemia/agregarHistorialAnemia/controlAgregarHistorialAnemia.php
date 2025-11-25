<?php
// Directorio: /controlador/historialAnemia/agregarHistorialAnemia/controlAgregarHistorialAnemia.php

include_once('../../../../../modelo/HistorialAnemiaPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class HistorialAnemiaDTO {
    // Atributos: Los datos del formulario
    public $historia_clinica_id;
    public $alergias;
    public $medicacion;
    public $enfermedades_pulmonares;
    public $enfermedades_cardiacas;
    public $enfermedades_neurologicas;
    public $enfermedades_hepaticas;
    public $enfermedades_renales;
    public $enfermedades_endocrinas;
    public $otras_enfermedades;
    public $ha_sido_operado;
    public $ha_tenido_tumor;
    public $ha_tenido_hemorragia;
    public $fuma;
    public $frecuencia_fuma;
    public $toma_anticonceptivos;
    public $esta_embarazada;
    public $semanas_embarazo;
    public $periodo_lactancia;
    
    // Método: Constructor (Inicializa los atributos)
    public function __construct(array $data) {
        $this->historia_clinica_id = (int)($data['historia_clinica_id'] ?? 0);
        $this->alergias = trim($data['alergias'] ?? '');
        $this->medicacion = trim($data['medicacion'] ?? '');
        $this->enfermedades_pulmonares = trim($data['enfermedades_pulmonares'] ?? '');
        $this->enfermedades_cardiacas = trim($data['enfermedades_cardiacas'] ?? '');
        $this->enfermedades_neurologicas = trim($data['enfermedades_neurologicas'] ?? '');
        $this->enfermedades_hepaticas = trim($data['enfermedades_hepaticas'] ?? '');
        $this->enfermedades_renales = trim($data['enfermedades_renales'] ?? '');
        $this->enfermedades_endocrinas = trim($data['enfermedades_endocrinas'] ?? '');
        $this->otras_enfermedades = trim($data['otras_enfermedades'] ?? '');
        $this->ha_sido_operado = trim($data['ha_sido_operado'] ?? '');
        $this->ha_tenido_tumor = isset($data['ha_tenido_tumor']) ? 1 : 0;
        $this->ha_tenido_hemorragia = isset($data['ha_tenido_hemorragia']) ? 1 : 0;
        $this->fuma = isset($data['fuma']) ? 1 : 0;
        $this->frecuencia_fuma = $this->fuma ? trim($data['frecuencia_fuma'] ?? '') : '';
        $this->toma_anticonceptivos = isset($data['toma_anticonceptivos']) ? 1 : 0;
        $this->esta_embarazada = isset($data['esta_embarazada']) ? 1 : 0;
        $this->semanas_embarazo = $this->esta_embarazada ? (int)($data['semanas_embarazo'] ?? 0) : null;
        $this->periodo_lactancia = isset($data['periodo_lactancia']) ? 1 : 0;

        // Limpieza: convertir strings vacíos a null para consistencia en la DB
        foreach ($this as $key => $value) {
            if ($value === '') {
                $this->$key = null;
            }
        }
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class HistorialAnemiaFactory {
    // Método: crearDTO (Crea y retorna el DTO)
    public static function crearDTO(array $data): HistorialAnemiaDTO {
        return new HistorialAnemiaDTO($data);
    }
    
    // Método: crearComando (Factory Method)
    public static function crearComando(string $action, HistorialAnemiaDTO $dto): Comando {
        switch ($action) {
            case 'agregar':
                // Método: Crea y retorna el comando de agregar
                return new AgregarHistorialAnemiaCommand($dto);
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

    // Método: handle (Abstracto para la lógica)
    // Atributo: $dto
    abstract public function handle(HistorialAnemiaDTO $dto): ?string;
    
    // Método: passNext (Pasa la validación al siguiente handler si existe)
    protected function passNext(HistorialAnemiaDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de Historia Clínica Requerida
class HistoriaClinicaValidator extends AbstractValidatorHandler {
    // Método: handle
    public function handle(HistorialAnemiaDTO $dto): ?string
    {
        if ($dto->historia_clinica_id <= 0) {
            return "Debe seleccionar una historia clínica válida para registrar el historial.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de Existencia de Historial (No duplicidad)
class DuplicidadValidator extends AbstractValidatorHandler {
    // Atributo: $objDAO
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new HistorialAnemiaPacienteDAO(); }

    // Método: handle
    public function handle(HistorialAnemiaDTO $dto): ?string
    {
        // Método: existeHistorialParaHistoriaClinica
        if ($this->objDAO->existeHistorialParaHistoriaClinica($dto->historia_clinica_id)) {
            return "Esta historia clínica ya tiene un historial de anemia registrado.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 3: Validación Condicional de Embarazo
class EmbarazoValidator extends AbstractValidatorHandler {
    // Método: handle
    public function handle(HistorialAnemiaDTO $dto): ?string
    {
        if ($dto->esta_embarazada === 1) {
            if ($dto->semanas_embarazo === null || $dto->semanas_embarazo < 1 || $dto->semanas_embarazo > 42) {
                return "Si la paciente está embarazada, debe especificar las semanas de gestación (1-42).";
            }
        }
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Agregar Historial de Anemia 📦
class AgregarHistorialAnemiaCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (HistorialAnemiaPacienteDAO)
    private $dto;
    private $validationChain;
    // Atributo: $validationMessage (Estado de la validación)
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
        $this->validationChain = new HistoriaClinicaValidator();
        $duplicidadValidator = new DuplicidadValidator();
        $embarazoValidator = new EmbarazoValidator();

        // Método: setNext
        $this->validationChain
             ->setNext($duplicidadValidator)
             ->setNext($embarazoValidator);
    }

    // Método: execute (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Ejecución del receptor (DAO)
        // Método: registrarHistorial
        return $this->objDAO->registrarHistorial($this->dto->historia_clinica_id, (array) $this->dto);
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
class controlAgregarHistorialAnemia
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
        // Atributo: $urlFormulario (Para retornos de error)
        $urlFormulario = './indexAgregarHistorialAnemia.php';
        // Atributo: $urlListado (Para éxito)
        $urlListado = '../indexHistorialAnemia.php';

        try {
            // Factory Method: Creación del DTO
            $dto = HistorialAnemiaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = HistorialAnemiaFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: $resultado (Estado de la operación DAO)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlFormulario,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Historial de anemia registrado correctamente.', 
                    $urlListado, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al registrar el historial de anemia. Por favor, intente nuevamente.', 
                    $urlFormulario, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlFormulario, 
                'error'
            );
        }
    }
}
?>