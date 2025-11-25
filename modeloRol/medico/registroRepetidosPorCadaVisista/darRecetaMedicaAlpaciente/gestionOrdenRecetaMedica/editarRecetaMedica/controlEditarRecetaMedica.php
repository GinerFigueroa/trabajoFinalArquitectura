<?php
// Directorio: /controlador/receta/editarRecetaMedica/controlEditarRecetaMedica.php

session_start();
include_once('../../../../../../modelo/RecetaMedicaDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, CHAIN, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class RecetaDTO {
    // Atributos: Los datos del formulario
    public $idReceta;
    public $historiaClinicaId;
    public $fecha;
    public $indicacionesGenerales;
    public $idUsuarioLogueado;
    public $idMedico; // ID de Médico real, se obtendrá en la cadena de validación
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idReceta = (int)($data['idReceta'] ?? 0);
        $this->historiaClinicaId = (int)($data['historiaClinicaId'] ?? 0);
        $this->fecha = trim($data['fecha'] ?? '');
        $this->indicacionesGenerales = trim($data['indicacionesGenerales'] ?? '');
        $this->idUsuarioLogueado = (int)($data['idUsuarioLogueado'] ?? 0);
        $this->idMedico = 0;
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Atributo: Interfaz base para el Command

class RecetaFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): RecetaDTO {
        // Método: Crea y retorna el DTO
        return new RecetaDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, RecetaDTO $dto): Comando {
        if ($action === 'editar') {
            // Método: Crea y retorna el comando de edición
            return new EditarRecetaCommand($dto);
        }
        throw new Exception("Acción de comando no soportada.");
    }
}

// CHAIN OF RESPONSIBILITY (Validadores) 🔗
abstract class AbstractValidatorHandler {
    // Atributo: $nextHandler
    private $nextHandler = null;

    // Método: `setNext`
    public function setNext(AbstractValidatorHandler $handler): AbstractValidatorHandler
    {
        $this->nextHandler = $handler;
        return $handler;
    }

    // Método: `handle` (Abstracto para la lógica, concreto para el encadenamiento)
    abstract public function handle(RecetaDTO $dto): ?string;
    
    // Método: `passNext`
    protected function passNext(RecetaDTO $dto): ?string
    {
        if ($this->nextHandler) {
            return $this->nextHandler->handle($dto);
        }
        return null;
    }
}

// Handler Concreto 1: Validación de campos obligatorios y formato
class RequeridosValidator extends AbstractValidatorHandler {
    // Método: `handle`
    public function handle(RecetaDTO $dto): ?string
    {
        if ($dto->idReceta <= 0 || $dto->historiaClinicaId <= 0 || empty($dto->fecha) || empty($dto->indicacionesGenerales)) {
            return "Todos los campos marcados con (*) son obligatorios.";
        }
        if (strlen($dto->indicacionesGenerales) < 10) {
            return "Las indicaciones generales deben tener al menos 10 caracteres.";
        }
        // Validación de fecha (no futura)
        $fechaActual = date('Y-m-d');
        if ($dto->fecha > $fechaActual) {
            return "La fecha de la receta no puede ser futura.";
        }
        return $this->passNext($dto);
    }
}

// Handler Concreto 2: Validación de permisos y propiedad (Receta y Médico)
class PropiedadValidator extends AbstractValidatorHandler {
    // Atributo: $objDAO
    private $objDAO;
    
    // Método: Constructor
    public function __construct() { $this->objDAO = new RecetaMedicaDAO(); }

    // Método: `handle`
    public function handle(RecetaDTO $dto): ?string
    {
        // 1. Validar que el usuario logueado sea médico (Rol 2)
        if (!isset($_SESSION['rol_id']) || $_SESSION['rol_id'] != 2 || $dto->idUsuarioLogueado <= 0) {
            return "Solo el personal médico puede editar recetas.";
        }

        // 2. Obtener el ID del médico asociado al usuario logueado
        // Atributo: $idMedico
        $idMedico = $this->objDAO->obtenerIdMedicoPorUsuario($dto->idUsuarioLogueado);
        
        if (!$idMedico) {
            return "No se pudo identificar al médico logueado.";
        }
        $dto->idMedico = $idMedico; // Se actualiza el DTO para el Command

        // 3. Validar la propiedad de la receta
        // Atributo: $recetaOriginal
        $recetaOriginal = $this->objDAO->obtenerRecetaPorId($dto->idReceta);
        
        if (!$recetaOriginal) {
            return "La receta médica a editar no existe.";
        }
        
        // Obtener el id_usuario del médico original de la receta
        // Atributo: $idUsuarioRecetaOriginal
        $idUsuarioRecetaOriginal = $this->objDAO->obtenerIdUsuarioPorIdMedico($recetaOriginal['id_medico']);
        
        if ($idUsuarioRecetaOriginal != $dto->idUsuarioLogueado) {
            return "No tiene permisos para editar esta receta. Solo el médico que la creó puede modificarla.";
        }
        
        return $this->passNext($dto);
    }
}

// COMMAND Concreto: Editar Receta 📦
class EditarRecetaCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Atributo: Receptor (RecetaMedicaDAO)
    private $dto;
    // Atributo: $validationChain
    private $validationChain;
    // Atributo: $validationMessage
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(RecetaDTO $dto)
    {
        $this->objDAO = new RecetaMedicaDAO();
        $this->dto = $dto;
        $this->buildValidationChain();
    }
    
    // Método: Configura la Cadena de Responsabilidad
    private function buildValidationChain()
    {
        // CHAIN OF RESPONSIBILITY: Configuración de la cadena
        $this->validationChain = new RequeridosValidator();
        $propiedadValidator = new PropiedadValidator();

        // Método: `setNext`
        $this->validationChain
             ->setNext($propiedadValidator);
    }

    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Chain of Responsibility: Ejecución de la cadena de validación
        $this->validationMessage = $this->validationChain->handle($this->dto);
        
        if ($this->validationMessage !== null) {
            return false;
        }

        // 2. Ejecución del receptor (DAO) con el DTO validado y actualizado ($idMedico)
        // Método: `actualizarReceta`
        return $this->objDAO->actualizarReceta(
            $this->dto->idReceta,
            $this->dto->historiaClinicaId,
            $this->dto->idMedico, // ID de médico obtenido por el validador
            $this->dto->fecha,
            $this->dto->indicacionesGenerales
        );
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado de la validación)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    // Método: `getIdReceta`
    public function getIdReceta(): int
    {
        return $this->dto->idReceta;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación.
 */
class controlEditarRecetaMedica
{
    // Atributos: Dependencias
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "../indexRecetaMedica.php";
        $idReceta = $data['idReceta'] ?? 0;
        
        try {
            // Factory Method: Creación del DTO
            $dto = RecetaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = RecetaFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: $resultado (Estado de la operación DAO)
            $resultado = $command->execute();

            // Atributo: $mensajeError
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación (Chain of Responsibility)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    "./indexEditarRecetaMedica.php?id=" . $idReceta,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Receta médica actualizada correctamente. ID: {$idReceta}", 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: No se afectaron filas)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al actualizar la receta médica. No se realizaron cambios en la base de datos.', 
                    "./indexEditarRecetaMedica.php?id=" . $idReceta, 
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
    
    // Métodos originales que quedan obsoletos o se simplifican/eliminan para la refactorización:
    public function editarReceta($idReceta, $historiaClinicaId, $fecha, $indicacionesGenerales) 
    {
        // Este método debe ser reemplazado por la ejecución del Comando.
        $data = [
            'idReceta' => $idReceta, 
            'historiaClinicaId' => $historiaClinicaId, 
            'fecha' => $fecha, 
            'indicacionesGenerales' => $indicacionesGenerales, 
            'idUsuarioLogueado' => $_SESSION['id_usuario'] ?? null
        ];
        $this->ejecutarComando('editar', $data);
    }
    
    
}
?>