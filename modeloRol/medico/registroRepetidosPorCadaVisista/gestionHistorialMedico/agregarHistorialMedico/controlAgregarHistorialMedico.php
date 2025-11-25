<?php
// Directorio: /controlador/historial/agregarHistorialMedico/controlAgregarHistorialMedico.php

// Nota: Se asume que estas clases existen en la ruta.
include_once('../../../../../modelo/RegistroMedicoDAO.php'); 
include_once('../../../../../shared/mensajeSistema.php'); 

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class AgregarHistorialDTO {
    public $historiaClinicaId;
    public $motivoConsulta;
    // ... otros atributos
    public $ultimaVisitaMedica;
    
    public function __construct(array $data) {
        // Asignación y limpieza de atributos
        $this->historiaClinicaId = (int)($data['historia_clinica_id'] ?? 0);
        $this->motivoConsulta = $this->limpiarTexto($data['motivo_consulta'] ?? '');
        $this->enfermedadActual = $this->limpiarTexto($data['enfermedad_actual'] ?? '');
        $this->tiempoEnfermedad = $this->limpiarTexto($data['tiempo_enfermedad'] ?? '');
        $this->signosSintomas = $this->limpiarTexto($data['signos_sintomas'] ?? '');
        $this->riesgos = $this->limpiarTexto($data['riesgos'] ?? '');
        $this->motivoUltimaVisita = $this->limpiarTexto($data['motivo_ultima_visita'] ?? '');
        
        // Se mantiene la fecha sin limpiar el texto, para la validación estricta posterior.
        $this->ultimaVisitaMedica = $data['ultima_visita_medica'] ?? null; 
    }
    
    private function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto ?? ''));
    }
}

// Patrón: FACTORY METHOD
interface Comando {} 

class AgregarHistorialFactory {
    public static function crearDTO(array $data): AgregarHistorialDTO {
        return new AgregarHistorialDTO($data);
    }
    
    // El Factory ahora recibe el DAO para pasarlo al Command (Inyección de dependencia)
    public static function crearComando(string $action, AgregarHistorialDTO $dto, RegistroMedicoDAO $dao): Comando {
        switch ($action) {
            case 'registrar':
                return new RegistrarHistorialCommand($dto, $dao);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// CHAIN OF RESPONSIBILITY (Validadores)
abstract class AbstractValidatorHandler {
    private $nextHandler = null;

    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    abstract public function handle(AgregarHistorialDTO $dto): ?string;
    
    protected function passNext(AgregarHistorialDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de campos obligatorios
class RequeridosValidator extends AbstractValidatorHandler {
    public function handle(AgregarHistorialDTO $dto): ?string
    {
        if ($dto->historiaClinicaId <= 0 || empty($dto->motivoConsulta)) {
            return "El ID de Historia Clínica o el Motivo de Consulta son obligatorios y válidos.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de existencia de Historia Clínica
class HistoriaExistenteValidator extends AbstractValidatorHandler {
    // El DAO se inyecta por constructor
    private $objDAO;
    
    public function __construct(RegistroMedicoDAO $dao) { 
        $this->objDAO = $dao; 
    }

    public function handle(AgregarHistorialDTO $dto): ?string
    {
        // NOTA: Usar obtenerHistoriasClinicas() para verificar existencia NO es escalable. 
        // Se recomienda implementar un método más eficiente en el DAO como `existeHistoriaClinicaPorId($id)`.
        
        // Manteniendo la lógica del código original:
        $historias = $this->objDAO->obtenerHistoriasClinicas();
        $historiaExiste = false;
        foreach ($historias as $historia) {
            if ($historia['historia_clinica_id'] == $dto->historiaClinicaId) {
                $historiaExiste = true;
                break;
            }
        }
        
        if (!$historiaExiste) {
            return "La Historia Clínica seleccionada con ID {$dto->historiaClinicaId} no existe.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 3: Validación de fecha (Formato y Lógica de futuro)
class FechaValidator extends AbstractValidatorHandler {
    
    private function validarFormatoFecha($fecha)
    {
        $patron = '/^\d{4}-\d{2}-\d{2}$/';
        if (!preg_match($patron, $fecha)) {
            return false;
        }
        
        list($año, $mes, $dia) = explode('-', $fecha);
        return checkdate((int)$mes, (int)$dia, (int)$año);
    }

    public function handle(AgregarHistorialDTO $dto): ?string
    {
        $fecha = $dto->ultimaVisitaMedica;
        if (!empty($fecha)) {
            // 1. Validar Formato y validez de calendario
            if (!$this->validarFormatoFecha($fecha)) {
                return "La fecha de última visita médica no tiene un formato válido (YYYY-MM-DD).";
            }
            
            // 2. Validar que no sea futura
            if (strtotime($fecha) > time()) {
                return "La fecha de última visita médica no puede ser futura.";
            }
        }
        return $this->passNext($dto);
    }
}


// COMMAND Concreto: Registrar Historial 📦
class RegistrarHistorialCommand implements Comando
{
    private $objDAO; // Receptor
    private $dto;
    private $validationChain;
    private $validationMessage = null;

    public function __construct(AgregarHistorialDTO $dto, RegistroMedicoDAO $dao) // Recibe el DAO
    {
        $this->objDAO = $dao;
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena con el DAO inyectado
        $this->validationChain = new RequeridosValidator();
        $existenciaValidator = new HistoriaExistenteValidator($this->objDAO); // Inyección
        $fechaValidator = new FechaValidator();

        $this->validationChain
             ->setNext($existenciaValidator)
             ->setNext($fechaValidator);
    }

    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Ejecución del receptor (DAO)
        return $this->objDAO->registrarRegistro(
            $this->dto->historiaClinicaId,
            $this->dto->riesgos,
            $this->dto->motivoConsulta,
            $this->dto->enfermedadActual,
            $this->dto->tiempoEnfermedad,
            $this->dto->signosSintomas,
            $this->dto->motivoUltimaVisita,
            $this->dto->ultimaVisitaMedica
        );
    }

    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlAgregarHistorialPaciente
{
    private $objMensaje;
    private $objDAO; // Nueva dependencia DAO

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objDAO = new RegistroMedicoDAO(); // Instanciamos la dependencia aquí
    }

    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = '../agregarHistorialMedico/indexAgregarHistorialMedico.php';
        $urlListado = '../indexHistorialMedico.php';

        try {
            // Factory Method: Creación del DTO
            $dto = AgregarHistorialFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND, pasándole el DAO
            $command = AgregarHistorialFactory::crearComando($action, $dto, $this->objDAO);

            // Command: Ejecución
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Registro Médico creado correctamente.', 
                    $urlListado, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al crear el registro médico. Fallo en la inserción en DB.', 
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