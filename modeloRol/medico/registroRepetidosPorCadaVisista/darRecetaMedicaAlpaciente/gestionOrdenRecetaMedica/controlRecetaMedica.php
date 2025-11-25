<?php
// Directorio: /controlador/receta/controlRecetaMedica.php

include_once('../../../../../modelo/RecetaMedicaDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class RecetaDTO {
    // Atributo: $idReceta
    public $idReceta;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idReceta = (int)($data['idReceta'] ?? 0);
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
        switch ($action) {
            case 'eliminar':
                // Método: Crea y retorna el comando de eliminación
                return new EliminarRecetaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// COMMAND Concreto: Eliminar Receta 📦
class EliminarRecetaCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Atributo: Receptor (RecetaMedicaDAO)
    private $dto;
    // Atributo: `$validationMessage` (Estado de la validación)
    private $validationMessage = null;

    // Método: Constructor
    public function __construct(RecetaDTO $dto)
    {
        $this->objDAO = new RecetaMedicaDAO();
        $this->dto = $dto;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // Validación de ID simple antes de ejecutar (Se puede expandir a Chain of Responsibility)
        if ($this->dto->idReceta <= 0) {
            $this->validationMessage = "El ID de la receta no es válido o está ausente.";
            return false;
        }

        // Ejecución del receptor (DAO)
        // Método: `eliminarReceta`
        return $this->objDAO->eliminarReceta($this->dto->idReceta);
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

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación entre la creación del Command/DTO (Factory), 
 * la ejecución del Command y el manejo de los resultados (State).
 */
class controlRecetaMedica
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
        $urlRetorno = "./indexRecetaMedica.php";

        try {
            // Factory Method: Creación del DTO
            $dto = RecetaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            // Atributo: `$command`
            $command = RecetaFactory::crearComando($action, $dto);

            // Command: Ejecución
            // Atributo: `$resultado` (Estado de la operación DAO)
            $resultado = $command->execute();

            // Atributo: `$mensajeError`
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
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
                    '✅ Receta médica eliminada correctamente.', 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos (Ej: Receta no encontrada o fallo en DB)
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar la receta médica. Puede que ya no exista o haya un fallo en DB.', 
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
    
    // El método original `eliminarReceta` se elimina o se redirige al nuevo `ejecutarComando`
    // Para no romper la funcionalidad externa (si existe), se podría mantener y hacer que llame al nuevo método:
    public function eliminarReceta($idReceta)
    {
        $this->ejecutarComando('eliminar', ['idReceta' => $idReceta]);
    }
}
?>