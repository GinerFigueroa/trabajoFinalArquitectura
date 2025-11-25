<?php

include_once('../../../../shared/mensajeSistema.php');
include_once('../../../../modelo/TratamientoDAO.php'); 

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, CoR
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object para Registro)
class TratamientoRegistroDTO {
    // Atributos: Los datos del tratamiento
    public $nombre; public $idEspecialidad; public $descripcion;
    public $duracion; public $costo; public $requisitos; public $activo;

    // Método: Constructor (Para inicializar atributos)
    public function __construct(array $data) {
        $this->nombre = $data['nombre'] ?? '';
        $this->idEspecialidad = (int)($data['idEspecialidad'] ?? 0);
        $this->descripcion = $data['descripcion'] ?? '';
        $this->duracion = (int)($data['duracion'] ?? 0);
        $this->costo = (float)($data['costo'] ?? 0.0);
        $this->requisitos = $data['requisitos'] ?? '';
        // 'activo' viene por defecto del Builder, generalmente a 1
        $this->activo = (int)($data['activo'] ?? 1); 
    }
}

// Patrón: COMMAND (Interfaz base)
interface Comando {
    // Atributo: Método abstracto `execute`
    public function execute(): bool;
    // Atributo: Método abstracto `getValidationMessage`
    public function getValidationMessage(): ?string;
}

// Patrón: FACTORY METHOD 🏭
class TratamientoRegistroFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): TratamientoRegistroDTO {
        return new TratamientoRegistroDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, TratamientoRegistroDTO $dto): Comando {
        if ($action === 'registrar') {
            // Atributo: Retorna la instancia del Command concreto
            return new RegistrarTratamientoCommand($dto);
        }
        throw new Exception("Acción de comando no soportada.");
    }
}

// Patrón: CHAIN OF RESPONSIBILITY (Abstract Handler) ⛓️
abstract class TratamientoRegisterValidationHandler {
    // Atributo: $successor
    protected $successor;

    // Método: `setNext`
    public function setNext(TratamientoRegisterValidationHandler $handler): TratamientoRegisterValidationHandler {
        $this->successor = $handler;
        return $handler;
    }

    // Atributo: Método abstracto `handle`
    abstract public function handle(TratamientoRegistroDTO $dto): ?string;
}

// Patrón: CHAIN OF RESPONSIBILITY (Concrete Handler 1: Validación de Duración/Costo)
class DuracionCostoRegisterValidationHandler extends TratamientoRegisterValidationHandler {
    // Método: `handle`
    public function handle(TratamientoRegistroDTO $dto): ?string {
        if (!is_numeric($dto->duracion) || $dto->duracion <= 0) {
            return "La duración debe ser un número entero positivo (mayor que cero).";
        }
        if (!is_numeric($dto->costo) || $dto->costo < 0) {
            return "El costo debe ser un número positivo (o cero).";
        }
        return $this->successor ? $this->successor->handle($dto) : null;
    }
}

// Patrón: CHAIN OF RESPONSIBILITY (Concrete Handler 2: Validación de Especialidad y Nombre Único)
class EspecialidadNombreRegisterValidationHandler extends TratamientoRegisterValidationHandler {
    // Atributo: $objDAO (Dependencia del DAO para las validaciones)
    private $objDAO;

    // Método: Constructor (Inicializa el DAO)
    public function __construct() {
        $this->objDAO = new TratamientoDAO();
    }
    
    // Método: `handle`
    public function handle(TratamientoRegistroDTO $dto): ?string {
        // Validación 1: Especialidad existe
        if (!$this->objDAO->especialidadExiste($dto->idEspecialidad)) {
            return "La especialidad seleccionada no es válida.";
        }
        
        // Validación 2: Nombre único
        // En registro, el tercer parámetro (ID a excluir) no se pasa o se pasa como null/0
        if ($this->objDAO->validarNombreUnico($dto->nombre, $dto->idEspecialidad)) { 
            return "Ya existe un tratamiento con el nombre '{$dto->nombre}' en esa especialidad.";
        }

        return $this->successor ? $this->successor->handle($dto) : null;
    }
}

// Patrón: COMMAND Concreto 📦
class RegistrarTratamientoCommand implements Comando
{
    // Atributo: $objDAO (El Receptor)
    private $objDAO;
    // Atributo: $dto (Los datos de la solicitud)
    private $dto;
    // Atributo: $validationMessage (El Estado del Command)
    private $validationMessage = null;

    // Método: Constructor (Inicia la Chain of Responsibility)
    public function __construct(TratamientoRegistroDTO $dto)
    {
        $this->objDAO = new TratamientoDAO(); 
        $this->dto = $dto;

        // Configuración de la CHAIN OF RESPONSIBILITY
        $handler1 = new DuracionCostoRegisterValidationHandler();
        $handler2 = new EspecialidadNombreRegisterValidationHandler();
        
        // Cadena: Duración/Costo -> Especialidad/Nombre Único
        $handler1->setNext($handler2);
        
        // Ejecución de la cadena de validación
        $this->validationMessage = $handler1->handle($this->dto);
    }
    
    // Atributo: Método `execute`
    public function execute(): bool
    {
        // Si la validación falló (CoR), no se ejecuta el DAO
        if ($this->validationMessage) {
            return false;
        }

        // Conversión del DTO a array (como lo espera el DAO original)
        $dataArray = [
            'nombre' => $this->dto->nombre,
            'idEspecialidad' => $this->dto->idEspecialidad,
            'descripcion' => $this->dto->descripcion,
            'duracion' => $this->dto->duracion,
            'costo' => $this->dto->costo,
            'requisitos' => $this->dto->requisitos,
            'activo' => $this->dto->activo
        ];

        // Ejecución del receptor (DAO)
        return $this->objDAO->registrarTratamiento($dataArray);
    }

    // Atributo: Método `getValidationMessage`
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Orquesta la interacción entre el Factory, el Command y el manejo de mensajes.
 */
class controlAgregarTratamiento
{
    // Atributo: $objMensaje 
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Atributo: Método `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (Maneja el flujo de la respuesta basado en el estado del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetornoError = "./indexAgregarTratamiento.php";
        $urlRetornoSuccess = "../indexTipoTratamiento.php";

        try {
            // 1. Factory Method: Creación del DTO
            $dto = TratamientoRegistroFactory::crearDTO($data);
            
            // 2. Factory Method: Creación del COMMAND
            $command = TratamientoRegistroFactory::crearComando($action, $dto);

            // 3. Command: Ejecución
            // Atributo: $resultado (Estado de la operación DAO: true/false)
            $resultado = $command->execute();

            // 4. State: Leer el estado de la validación del Command (CoR result)
            // Atributo: $mensajeError
            $mensajeError = $command->getValidationMessage();

            // 5. Mediator/STATE: Lógica de respuesta
            if ($mensajeError) {
                // Estado 1: Error de validación (Falló la CoR)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación: " . $mensajeError,
                    $urlRetornoError,
                    "systemOut", // Usamos systemOut para evitar que el mensaje se cierre automáticamente
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Tratamiento '{$dto->nombre}' registrado correctamente.", 
                    $urlRetornoSuccess, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al registrar el tratamiento. No se realizó el registro en la base de datos.', 
                    $urlRetornoError, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error interno (Fallo en Factory o ejecución)
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRetornoError, 
                'error'
            );
        }
    }
    
    /**
     * Método de compatibilidad: Permite que el código original siga llamando a este método.
     * Atributo: Método `registrarTratamiento` (Función externa de compatibilidad)
     */
    public function registrarTratamiento(array $data)
    {
        // El viejo método ahora solo llama al Mediator
        $this->ejecutarComando('registrar', $data);
    }
}
?>