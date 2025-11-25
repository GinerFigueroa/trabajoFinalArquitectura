<?php

include_once("../../../../../modelo/InternadoSeguimientoDAO.php"); 
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EvolucionSeguimientoDTO {
    // Atributos: Los datos del formulario
    public $idInternado;
    public $idMedicoUsuario; // id_usuario del médico
    public $idEnfermeraUsuario; // id_usuario de la enfermera
    public $evolucion;
    public $tratamiento;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idInternado = $data['idInternado'] ?? null;
        $this->idMedicoUsuario = $data['idMedico'] ?? null;
        $this->idEnfermeraUsuario = $data['idEnfermera'] ?? null;
        $this->evolucion = $data['evolucion'] ?? '';
        $this->tratamiento = $data['tratamiento'] ?? '';
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class EvolucionFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): EvolucionSeguimientoDTO {
        // Método: Crea y retorna el DTO
        return new EvolucionSeguimientoDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, EvolucionSeguimientoDTO $dto): Comando {
        switch ($action) {
            case 'agregar':
                // Método: Crea y retorna el comando de registro
                return new AgregarSeguimientoCommand($dto);
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
    abstract public function handle(EvolucionSeguimientoDTO $dto): ?string;
    
    // Método: `passNext` (Pasa la validación al siguiente handler si existe)
    protected function passNext(EvolucionSeguimientoDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de campos obligatorios
class RequeridosValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(EvolucionSeguimientoDTO $dto): ?string
    {
        if (empty($dto->idInternado) || empty($dto->idMedicoUsuario) || empty($dto->evolucion)) {
            return "Los campos Paciente Hospitalizado, Médico Tratante y Evolución Clínica son obligatorios.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de Internado
class InternadoValidator extends AbstractValidatorHandler {
    // Atributo: `$objAuxiliarDAO`
    private $objAuxiliarDAO;
    
    // Método: Constructor
    public function __construct() { $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); }

    // Método: `handle`
    public function handle(EvolucionSeguimientoDTO $dto): ?string
    {
        // Método: `obtenerNombrePacientePorInternado`
        if (!$this->objAuxiliarDAO->obtenerNombrePacientePorInternado($dto->idInternado)) {
            return "El ID de Internado seleccionado no es válido o no existe.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 3: Validación de Médico
class MedicoValidator extends AbstractValidatorHandler {
    // Atributo: `$objAuxiliarDAO`
    private $objAuxiliarDAO;
    
    // Método: Constructor
    public function __construct() { $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); }

    // Método: `handle`
    public function handle(EvolucionSeguimientoDTO $dto): ?string
    {
        // Método: `validarUsuarioEsMedico`
        if (!$this->objAuxiliarDAO->validarUsuarioEsMedico($dto->idMedicoUsuario)) {
            return "El ID de usuario seleccionado como médico no es válido.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 4: Validación de Enfermera (si aplica)
class EnfermeraValidator extends AbstractValidatorHandler {
    // Atributo: `$objAuxiliarDAO`
    private $objAuxiliarDAO;

    // Método: Constructor
    public function __construct() { $this->objAuxiliarDAO = new EntidadAuxiliarDAO(); }

    // Método: `handle`
    public function handle(EvolucionSeguimientoDTO $dto): ?string
    {
        if (!empty($dto->idEnfermeraUsuario)) {
            // Método: `validarUsuarioEsEnfermera`
            if (!$this->objAuxiliarDAO->validarUsuarioEsEnfermera($dto->idEnfermeraUsuario)) {
                return "El usuario seleccionado como enfermera no es una enfermera válida.";
            }
        }
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Agregar Seguimiento 📦
class AgregarSeguimientoCommand implements Comando
{
    // Atributos: DTO y Receptors (DAOs)
    private $objSeguimientoDAO; // Receptor 1
    private $objAuxiliarDAO; // Receptor 2
    private $dto;
    private $validationChain;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EvolucionSeguimientoDTO $dto)
    {
        $this->objSeguimientoDAO = new InternadoSeguimientoDAO();
        $this->objAuxiliarDAO = new EntidadAuxiliarDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new RequeridosValidator();
        $internadoValidator = new InternadoValidator();
        $medicoValidator = new MedicoValidator();
        $enfermeraValidator = new EnfermeraValidator();

        $this->validationChain
             ->setNext($internadoValidator)
             ->setNext($medicoValidator)
             ->setNext($enfermeraValidator);
    }

    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Lógica de negocio/conversión: Convertir id_usuario_medico a id_medico
        // Método: `obtenerIdMedicoPorIdUsuario`
        $idMedicoTabla = $this->objAuxiliarDAO->obtenerIdMedicoPorIdUsuario($this->dto->idMedicoUsuario);
        if (!$idMedicoTabla) {
            $this->validationMessage = "Error al obtener el ID de médico real a partir del ID de usuario.";
            return false;
        }
        
        // 3. Ejecución del receptor (DAO)
        // Método: `registrarSeguimiento`
        return $this->objSeguimientoDAO->registrarSeguimiento(
            $this->dto->idInternado, 
            $idMedicoTabla,      // id_medico de la tabla medicos
            $this->dto->idEnfermeraUsuario, // id_usuario de la tabla usuarios
            $this->dto->evolucion, 
            $this->dto->tratamiento
        );
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado de la validación)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

// Patrón: MEDIATOR 🤝
class controlAgregarEvolucionPaciente
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
        // Atributo: `$rutaRetorno` (Para errores, se retorna al formulario)
        $rutaRetorno = './indexaAgregarEvolucionPaciente.php';

        try {
            // Factory Method: Creación del DTO
            $dto = EvolucionFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = EvolucionFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $rutaRetorno,
                    "error"
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Evolución clínica registrada correctamente.', 
                    '../indexEvolucionClinicaPacienteHospitalizado.php', 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '❌ Error al registrar la evolución. Fallo en la inserción en la base de datos.', 
                    $rutaRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $rutaRetorno, 
                'error'
            );
        }
    }
}
?>