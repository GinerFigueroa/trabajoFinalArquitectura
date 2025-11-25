<?php

include_once('../../../modelo/ExamenClinicoDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, STATE
// ==========================================================

/**
 * ExamenDTO (Data Transfer Object)
 * Utilizado para transferir y sanitizar los datos necesarios para una operación.
 */
class ExamenDTO {
    // Atributos:
    public $examenId;
    
    // Métodos:
    public function __construct(array $data) {
        $this->examenId = (int)($data['examenId'] ?? 0);
    }
}

/**
 * Interfaz ComandoExamen
 */
interface ComandoExamen {
    // Métodos:
    public function execute(): bool;
    public function getValidationMessage(): ?string;
}

/**
 * ExamenFactory (Patrón Factory Method) 🏭
 */
class ExamenFactory {
    // Atributos: Ninguno (Métodos estáticos).
    
    // Métodos:
    public static function crearDTO(array $data): ExamenDTO {
        return new ExamenDTO($data);
    }
    
    public static function crearComando(string $action, ExamenDTO $dto): ComandoExamen {
        switch ($action) {
            case 'eliminar':
                return new EliminarExamenCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Examen Clínico.");
        }
    }
}

/**
 * EliminarExamenCommand (Command Concreto) 📦
 */
class EliminarExamenCommand implements ComandoExamen
{
    // Atributos:
    private $objDAO; // Receptor: ExamenClinicoDAO
    private $dto;
    private $validationMessage = null; // Patrón State

    // Métodos:
    public function __construct(ExamenDTO $dto)
    {
        $this->objDAO = new ExamenClinicoDAO();
        $this->dto = $dto;
    }
    
    /**
     * Ejecuta la lógica del comando.
     */
    public function execute(): bool
    {
        // 1. Validaciones de Datos
        if ($this->dto->examenId <= 0) {
            $this->validationMessage = "ID de Examen Clínico no válido.";
            return false;
        }

        // 2. Validación de Negocio (Existencia)
        if (!$this->objDAO->obtenerExamenPorId($this->dto->examenId)) {
            $this->validationMessage = "Error: El Examen Clínico con ID **{$this->dto->examenId}** no existe o ya fue eliminado.";
            return false;
        }

        // 3. Ejecución del Receptor (DAO)
        $resultado = $this->objDAO->eliminarExamen($this->dto->examenId);

        if ($resultado) {
            return true;
        } else {
            // Este mensaje cubre fallos de DB o restricciones de integridad
            $this->validationMessage = "Error al eliminar el Examen Clínico. Fallo en la base de datos o existen registros dependientes.";
            return false;
        }
    }

    // Métodos para leer el Estado de la operación (Patrón State)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * controlExmenEntrada (Patrón Mediator) 🤝
 * Coordina la creación del comando, su ejecución y el manejo de los mensajes de salida.
 */
class controlExmenEntrada
{
    // Atributos:
    private $objMensaje;

    // Métodos:
    public function __construct()
    {
        // Se elimina la inicialización de $objExamenDAO ya que solo se usa dentro del Command
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Punto de coordinación central.
     * Patrón: STATE 🚦 (Manejo de estados basado en la salida del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $rutaRetorno = "./indexExamenEntrada.php";
        
        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = ExamenFactory::crearDTO($data);
            $command = ExamenFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación, Existencia o Permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error: " . $mensajeError,
                    $rutaRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Examen Clínico eliminado correctamente.', 
                    $rutaRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Fallo genérico (aunque el Command debería proveer un mensaje)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar el Examen Clínico. La operación falló.', 
                    $rutaRetorno, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de sistema
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $rutaRetorno, 
                'error'
            );
        }
    }
    
    // NOTA: Se elimina el método 'eliminarExamen' del código original, ya que su lógica 
    // ha sido migrada completamente al 'EliminarExamenCommand'.
}
?>