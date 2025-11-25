<?php
// Directorio: /controlador/gestionDetalleCitaPaciente/controlEditarDetalleCita.php

include_once('../../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class DetalleCitaDTO {
    // Atributos
    public $idDetalle;
    public $medicamento;
    public $dosis;
    public $frecuencia;
    public $duracion;
    public $notas;
    public $idUsuarioMedico;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idDetalle = (int)($data['idDetalle'] ?? 0);
        $this->medicamento = trim(htmlspecialchars($data['medicamento'] ?? ''));
        $this->dosis = trim(htmlspecialchars($data['dosis'] ?? ''));
        $this->frecuencia = trim(htmlspecialchars($data['frecuencia'] ?? ''));
        $this->duracion = $data['duracion'] ? trim(htmlspecialchars($data['duracion'])) : null;
        $this->notas = $data['notas'] ? trim(htmlspecialchars($data['notas'])) : null;
        $this->idUsuarioMedico = (int)($data['idUsuarioMedico'] ?? 0);
    }
}

// Patrón: FACTORY METHOD 🏭
interface ComandoDetalle {} // Interfaz base para el Command

class DetalleCitaFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): DetalleCitaDTO {
        return new DetalleCitaDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, DetalleCitaDTO $dto): ComandoDetalle {
        switch ($action) {
            case 'editar':
                // Método: Crea y retorna el comando de edición
                return new EditarDetalleCitaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada para Detalle de Receta.");
        }
    }
}

// COMMAND Concreto: Editar Detalle de Receta 📦
class EditarDetalleCitaCommand implements ComandoDetalle
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Atributo: Receptor (RecetaDetalleDAO)
    private $dto;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(DetalleCitaDTO $dto)
    {
        $this->objDAO = new RecetaDetalleDAO();
        $this->dto = $dto;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Validaciones de Datos (Se movieron del Controlador aquí)
        if ($this->dto->idDetalle <= 0) {
            $this->validationMessage = "El ID del detalle no es válido.";
            return false;
        }
        if (empty($this->dto->medicamento) || strlen($this->dto->medicamento) < 2) {
            $this->validationMessage = "El medicamento es obligatorio y debe tener al menos 2 caracteres.";
            return false;
        }
        if (empty($this->dto->dosis) || strlen($this->dto->dosis) < 1) {
            $this->validationMessage = "La dosis es obligatoria.";
            return false;
        }
        if (empty($this->dto->frecuencia)) {
            $this->validationMessage = "La frecuencia es obligatoria.";
            return false;
        }
        
        // 2. Validación de Propiedad/Permisos (Lógica de Negocio)
        if (!$this->objDAO->validarPropiedadDetalle($this->dto->idDetalle, $this->dto->idUsuarioMedico)) {
            $this->validationMessage = "No tiene permisos para editar este detalle. Solo el médico que creó la receta puede modificarla.";
            return false;
        }
        
        // 3. Ejecución del Receptor (DAO)
        // Se ejecuta la actualización con los datos del DTO
        return $this->objDAO->actualizarDetalle(
            $this->dto->idDetalle, 
            $this->dto->medicamento, 
            $this->dto->dosis, 
            $this->dto->frecuencia, 
            $this->dto->duracion, 
            $this->dto->notas
        );
    }

    // Método: `getValidationMessage` (Permite al Mediator leer el Estado)
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
 * Centraliza la coordinación entre la creación del Command/DTO (Factory), 
 * la ejecución del Command y el manejo de los resultados (State).
 */
class controlEditarDetalleCita
{
    // Atributos: Dependencias
    private $objMensaje;
    private $objDetalleDAO; // Necesario para métodos de soporte como obtenerDetalle

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objDetalleDAO = new RecetaDetalleDAO();
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (El estado de la operación determina el flujo de mensajes)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "../indexDetalleCita.php";
        // En caso de fallo en validación, redirigir al formulario de edición
        $urlRetornoFallo = "./indexEditarDetalleCita.php?id=" . ($data['idDetalle'] ?? 0);

        try {
            // Factory Method: Creación del DTO
            $dto = DetalleCitaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = DetalleCitaFactory::crearComando($action, $dto);

            // Command: Ejecución
            $resultado = $command->execute();

            // Atributo: `$mensajeError`
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación o Permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de validación/permisos: " . $mensajeError,
                    // Si el error es de ID/datos, volvemos al formulario, si es de permisos/propiedad volvemos al listado.
                    // Para simplificar, usamos una sola URL de retorno de fallo.
                    $urlRetornoFallo,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Detalle de receta actualizado correctamente. ID: ' . $dto->idDetalle, 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: Fallo en DB)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al actualizar el detalle de receta. Puede que haya un fallo en DB.', 
                    $urlRetornoFallo, 
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
    
    // El método auxiliar `obtenerDetalle` se mantiene para ser llamado desde getEditarDetalleCita.php 
    // y para que la Vista pueda pre-cargar los datos (Aunque es mejor que la vista llame directamente al DAO)
    public function obtenerDetalle($idDetalle)
    {
        return $this->objDetalleDAO->obtenerDetallePorId($idDetalle);
    }
    
    // Los métodos originales de lógica de negocio (`editarDetalle`, `validarUsuarioMedico`) se eliminan 
    // ya que su lógica fue movida al Command y al punto de entrada.
}
?>