<?php
// Directorio: /controlador/gestionHistoriaClinica/controlHistorialClinico.php

include_once('../../../modelo/HistoriaClinicaDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND, STATE
// ==========================================================

/**
 * HistoriaClinicaDTO (Data Transfer Object)
 * Atributos: Almacena los datos necesarios para la operación.
 */
class HistoriaClinicaDTO {
    public $idHistoria;
    public $idMedico;
    
    public function __construct(array $data) {
        $this->idHistoria = (int)($data['idHistoria'] ?? 0);
        $this->idMedico = (int)($data['idMedico'] ?? 0);
    }
}

/**
 * Interfaz ComandoHistoria
 */
interface ComandoHistoria {
    /** Método: ejecuta la lógica de negocio. */
    public function execute(): bool;
    /** Método: obtiene el mensaje de estado (Patrón State). */
    public function getValidationMessage(): ?string;
}

/**
 * HistoriaClinicaFactory (Patrón Factory Method) 🏭
 * Atributos: No tiene atributos.
 * Métodos: crearDTO, crearComando.
 */
class HistoriaClinicaFactory {
    public static function crearDTO(array $data): HistoriaClinicaDTO {
        return new HistoriaClinicaDTO($data);
    }
    
    public static function crearComando(string $action, HistoriaClinicaDTO $dto): ComandoHistoria {
        switch ($action) {
            case 'eliminar':
                return new EliminarHistoriaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Historial Clínico.");
        }
    }
}

/**
 * EliminarHistoriaCommand (Command Concreto) 📦
 * Atributos: objDAO (Receptor), dto, validationMessage (State).
 * Métodos: __construct, execute, getValidationMessage.
 */
class EliminarHistoriaCommand implements ComandoHistoria
{
    private $objDAO; // Receptor: HistoriaClinicaDAO
    private $dto;
    private $validationMessage = null; // Patrón State

    public function __construct(HistoriaClinicaDTO $dto)
    {
        $this->objDAO = new HistoriaClinicaDAO();
        $this->dto = $dto;
    }
    
    public function execute(): bool
    {
        // 1. Validaciones de Datos
        if ($this->dto->idHistoria <= 0 || $this->dto->idMedico <= 0) {
            $this->validationMessage = "IDs de Historia Clínica o Médico no válidos.";
            return false;
        }

        // 2. Validación de Negocio (Permisos)
        $historia = $this->objDAO->obtenerHistoriaPorId($this->dto->idHistoria);

        if (!$historia) {
            $this->validationMessage = "La Historia Clínica con ID **{$this->dto->idHistoria}** no existe.";
            return false;
        }
        
        // El médico solo puede eliminar sus propias historias (o un Admin, si se implementa rol 1)
        if ($historia['dr_tratante_id'] != $this->dto->idMedico) {
            $this->validationMessage = "Acceso Denegado: No tiene permisos para eliminar la historia clínica de otro médico.";
            return false;
        }

        // 3. Ejecución del Receptor (DAO)
        $resultado = $this->objDAO->eliminarHistoria($this->dto->idHistoria);
        
        if ($resultado) {
            return true;
        } else {
            $this->validationMessage = "Error en la base de datos al intentar eliminar la historia clínica.";
            return false;
        }
    }

    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * controlHistorialClinico (Patrón Mediator) 🤝
 * Atributos: objMensaje.
 * Métodos: __construct, ejecutarComando.
 */
class controlHistorialClinico
{
    private $objMensaje;

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
        $rutaRetorno = "./indexHistoriaClinica.php";

        try {
            // 1. Crear DTO y COMMAND (Factory)
            $dto = HistoriaClinicaFactory::crearDTO($data);
            $command = HistoriaClinicaFactory::crearComando($action, $dto);

            // 2. Ejecutar COMMAND
            $resultado = $command->execute();

            // 3. Manejo del Estado (Mediator)
            $mensajeError = $command->getValidationMessage();

            if ($mensajeError) {
                // Estado 1: Error de validación o Permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de Operación: " . $mensajeError,
                    $rutaRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Historia Clínica (ID: {$dto->idHistoria}) eliminada correctamente, junto con sus registros asociados.", 
                    $rutaRetorno, 
                    "success"
                );
            } else {
                // Estado 3: Error de base de datos
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar la historia clínica. Fallo en la base de datos.', 
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