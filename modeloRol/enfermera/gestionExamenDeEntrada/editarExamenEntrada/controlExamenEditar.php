<?php

include_once("../../../../modelo/ExamenClinicoDAO.php"); // Receptor (DAO)
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, STATE
// ==========================================================

/**
 * ExamenEditarDTO (Data Transfer Object)
 */
class ExamenEditarDTO {
    // Atributos:
    public $examenId;
    public $historiaClinicaId;
    public $peso;
    public $talla;
    public $pulso;
    public $idEnfermero; // NULLable
    
    // Métodos:
    public function __construct(array $data) {
        $this->examenId = (int)($data['examen_id'] ?? 0);
        $this->historiaClinicaId = (int)($data['historia_clinica_id'] ?? 0);
        // Usar floatval() para asegurar el tipo, incluso si viene como string
        $this->peso = floatval($data['peso'] ?? 0.0);
        $this->talla = floatval($data['talla'] ?? 0.0);
        $this->pulso = trim($data['pulso'] ?? '');
        // Manejar ID de enfermero: si está vacío o es nulo, se establece a NULL (para la DB)
        $idEnfermeroTemp = $data['id_enfermero'] ?? null;
        $this->idEnfermero = (empty($idEnfermeroTemp)) ? NULL : (int)$idEnfermeroTemp;
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
 * ExamenEditarFactory (Patrón Factory Method) 🏭
 */
class ExamenEditarFactory {
    // Métodos:
    public static function crearDTO(array $data): ExamenEditarDTO {
        return new ExamenEditarDTO($data);
    }
    
    public static function crearComando(string $action, ExamenEditarDTO $dto): ComandoExamen {
        if ($action === 'editar') {
            return new EditarExamenCommand($dto);
        }
        throw new Exception("Acción de comando no soportada para Examen Clínico.");
    }
}

/**
 * EditarExamenCommand (Command Concreto) 📦
 * Contiene toda la lógica de validación y la invocación al DAO (Receptor).
 */
class EditarExamenCommand implements ComandoExamen
{
    // Atributos:
    private $objDAO; // Receptor: ExamenClinicoDAO
    private $dto;
    private $validationMessage = null; // Patrón State

    // Métodos:
    public function __construct(ExamenEditarDTO $dto)
    {
        $this->objDAO = new ExamenClinicoDAO();
        $this->dto = $dto;
    }
    
    /**
     * Ejecuta la lógica del comando, incluyendo validaciones.
     */
    public function execute(): bool
    {
        // --- 1. Validaciones de Campo Vacío (Obligatorias) ---
        if ($this->dto->examenId <= 0 || $this->dto->historiaClinicaId <= 0 || $this->dto->peso <= 0.0 || $this->dto->talla <= 0.0 || empty($this->dto->pulso)) {
            $this->validationMessage = "Faltan campos obligatorios (ID de Examen, Historia Clínica, Peso, Talla o Pulso).";
            return false;
        }

        // --- 2. Validaciones de Formato y Límite ---
        if ($this->dto->peso <= 0 || $this->dto->peso > 500) {
            $this->validationMessage = "El campo Peso debe ser un valor numérico positivo (máx 500).";
            return false;
        }
        if ($this->dto->talla <= 0 || $this->dto->talla > 3.0) {
            $this->validationMessage = "El campo Talla debe ser un valor numérico positivo (máx 3.0).";
            return false;
        }
        if (strlen($this->dto->pulso) > 20) {
            $this->validationMessage = "El campo Pulso no debe exceder los 20 caracteres.";
            return false;
        }

        // --- 3. Validaciones de Existencia de Entidades ---
        
        // a) Validar que el Examen exista
        if (!$this->objDAO->obtenerExamenPorId($this->dto->examenId)) {
            $this->validationMessage = "El Examen a editar no existe.";
            return false;
        }

        // b) Validar que el Paciente/Historia Clínica exista
        if (!$this->objDAO->obtenerNombrePacientePorHistoriaClinica($this->dto->historiaClinicaId)) {
            $this->validationMessage = "El ID de Historia Clínica seleccionado no es válido o no existe.";
            return false;
        }

        // c) Validar ID de Enfermera si no es NULL
        if ($this->dto->idEnfermero !== NULL && !$this->objDAO->obtenerNombrePersonalPorIdUsuario($this->dto->idEnfermero)) {
            $this->validationMessage = "El ID de Enfermera/o seleccionado no es válido o no existe.";
            return false;
        }

        // --- 4. Ejecución de la Acción (Receiver: DAO) ---
        try {
            $resultado = $this->objDAO->editarExamen(
                $this->dto->examenId,
                $this->dto->historiaClinicaId,
                $this->dto->peso,
                $this->dto->talla,
                $this->dto->pulso,
                $this->dto->idEnfermero
            );
            return $resultado;
        } catch (Exception $e) {
            // Manejo de errores de base de datos
            error_log("Error en EditarExamenCommand: " . $e->getMessage());
            $this->validationMessage = 'Error interno al actualizar el examen en la base de datos.';
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
 * controlExamenEditar (Patrón Mediator) 🤝
 * Coordina la creación del comando, su ejecución y el manejo de los mensajes de salida.
 */
class controlExamenEditar
{
    // Atributos:
    private $objMensaje;

    // Métodos:
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Punto de coordinación central.
     * Patrón: STATE 🚦 (Manejo de estados basado en la salida del Command)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $examenId = $data['examen_id'] ?? 0;
        // La ruta de retorno para errores debe incluir el ID del examen que se estaba editando
        $rutaRetorno = './indexExamenEditar.php?id=' . $examenId;

        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = ExamenEditarFactory::crearDTO($data);
            $command = ExamenEditarFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación, Existencia o DB
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error: " . $mensajeError,
                    $rutaRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Examen Clínico actualizado correctamente.',
                    '../indexExamenEntrada.php', // Redirigir a la lista principal después del éxito
                    'success'
                );
            } else {
                // Estado 3: Fallo genérico de la actualización (generalmente atrapado por mensajeError)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al actualizar el examen. La operación falló sin un mensaje de validación específico.',
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
    
}

    
