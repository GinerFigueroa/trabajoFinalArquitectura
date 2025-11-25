<?php

include_once('../../../../../modelo/EvolucionPacienteDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EditarEvolucionDTO {
    // Atributos: Los datos del formulario
    public $idEvolucion;
    public $notaSubjetiva;
    public $notaObjetiva;
    public $analisis;
    public $planDeAccion;
    
    // Método: Constructor
    public function __construct(array $data) {
        // Asignación y limpieza de atributos
        $this->idEvolucion = (int)($data['id_evolucion'] ?? 0);
        $this->notaSubjetiva = $this->limpiarTexto($data['nota_subjetiva'] ?? '');
        $this->notaObjetiva = $this->limpiarTexto($data['nota_objetiva'] ?? '');
        $this->analisis = $this->limpiarTexto($data['analisis'] ?? '');
        $this->planDeAccion = $this->limpiarTexto($data['plan_de_accion'] ?? '');
    }
    
    // Método: Auxiliar para limpieza 
    private function limpiarTexto($texto) {
        return trim(htmlspecialchars($texto ?? ''));
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class EvolucionFactory {
    // Método: crearDTO
    public static function crearDTO(string $action, array $data): EditarEvolucionDTO {
        switch ($action) {
            case 'editar':
                // Método: Crea y retorna el DTO de edición
                return new EditarEvolucionDTO($data);
            default:
                throw new Exception("Acción de DTO no soportada.");
        }
    }
    
    // Método: crearComando (Factory Method)
    public static function crearComando(string $action, $dto): Comando {
        if ($dto instanceof EditarEvolucionDTO) {
            switch ($action) {
                case 'editar':
                    // Método: Crea y retorna el comando de edición
                    return new EditarEvolucionCommand($dto);
                default:
                    throw new Exception("Acción de comando no soportada.");
            }
        }
        throw new Exception("DTO incompatible para el comando.");
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
    abstract public function handle($dto): ?string;
    
    // Método: passNext (Concreto)
    protected function passNext($dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de ID y campos requeridos
class RequeridosValidator extends AbstractValidatorHandler {
    // Método: handle
    public function handle($dto): ?string
    {
        if ($dto instanceof EditarEvolucionDTO) {
            // Atributos obligatorios: ID de evolución y nota subjetiva
            if ($dto->idEvolucion <= 0) {
                return "El ID de Evolución no es válido.";
            }
            if (empty($dto->notaSubjetiva)) {
                return "La nota subjetiva (S) es obligatoria.";
            }
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de existencia de Evolución
class EvolucionExistenteValidator extends AbstractValidatorHandler {
    // Atributo: $objDAO
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { 
        $this->objDAO = new EvolucionPacienteDAO(); 
    }

    // Método: handle
    public function handle($dto): ?string
    {
        if ($dto instanceof EditarEvolucionDTO) {
            // Se asume un método en el DAO para verificar la existencia.
            // Método: obtenerEvolucionPorId
            if (!$this->objDAO->obtenerEvolucionPorId($dto->idEvolucion)) {
                return "La Evolución con ID {$dto->idEvolucion} no existe o no se puede encontrar.";
            }
        }
        return $this->passNext($dto);
    }
}


// COMMAND Concreto: Editar Evolución 📦
class EditarEvolucionCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (EvolucionPacienteDAO)
    private $dto;
    private $validationChain;
    // Atributo: $validationMessage (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(EditarEvolucionDTO $dto)
    {
        $this->objDAO = new EvolucionPacienteDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new RequeridosValidator();
        $existenciaValidator = new EvolucionExistenteValidator();

        // Método: setNext
        $this->validationChain->setNext($existenciaValidator);
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
        // Método: editarEvolucion
        return $this->objDAO->editarEvolucion(
            $this->dto->idEvolucion,
            $this->dto->notaSubjetiva,
            $this->dto->notaObjetiva,
            $this->dto->analisis,
            $this->dto->planDeAccion
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
class controlEditarEvolucionPaciente
{
    // Atributos: Dependencias
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: ejecutarComando (Punto de coordinación central)
     * @param string $action La acción a ejecutar ('editar')
     * @param array $data Los datos de la petición (POST)
     */
    // Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
    public function ejecutarComando(string $action, array $data)
    {
        // Atributo: $urlRetorno
        $urlRetorno = "../editarEvolucionPaciente/indexEvolucionPaciente.php?evo_id=" . ($data['id_evolucion'] ?? 0);
        $urlListado = "../indexEvolucionPaciente.php";

        try {
            // Factory Method: Creación del DTO
            // Método: crearDTO
            $dto = EvolucionFactory::crearDTO($action, $data);
            
            // Factory Method: Creación del COMMAND
            // Método: crearComando
            $command = EvolucionFactory::crearComando($action, $dto);

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
                    '✅ Evolución médica actualizada correctamente.', 
                    $urlListado, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al actualizar la evolución médica. Fallo en la DB o no hubo cambios.', 
                    $urlRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlListado, 
                'error'
            );
        }
    }
}
?>