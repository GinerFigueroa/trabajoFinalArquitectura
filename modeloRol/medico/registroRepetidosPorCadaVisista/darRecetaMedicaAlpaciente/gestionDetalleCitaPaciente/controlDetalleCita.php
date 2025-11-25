<?php
// Directorio: /controlador/gestionDetalleCitaPaciente/controlDetalleCita.php

session_start();
include_once('../../../../../modelo/RecetaDetalleDAO.php');
include_once('../../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class DetalleCitaDTO {
    // Atributo: $idDetalle
    public $idDetalle;
    // Atributo: $idUsuario (Para validación de propiedad)
    public $idUsuario; 
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idDetalle = (int)($data['idDetalle'] ?? 0);
        $this->idUsuario = (int)($data['idUsuario'] ?? 0);
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class DetalleCitaFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): DetalleCitaDTO {
        // Crea y retorna el DTO
        return new DetalleCitaDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, DetalleCitaDTO $dto): Comando {
        switch ($action) {
            case 'eliminar':
                // Crea y retorna el comando de eliminación
                return new EliminarDetalleCitaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// COMMAND Concreto: Eliminar Detalle de Cita 📦
class EliminarDetalleCitaCommand implements Comando
{
    // Atributos: DTO y Receptor (DAO)
    private $objDAO; // Receptor (RecetaDetalleDAO)
    private $dto;
    // Atributo: $validationMessage (Estado de la validación)
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
        // Validación de ID simple
        if ($this->dto->idDetalle <= 0) {
            $this->validationMessage = "El ID del detalle no es válido.";
            return false;
        }

        // Validación de permisos de propiedad (Lógica de negocio en el Command)
        // Se asume que el método validarPropiedadDetalle existe en el DAO
        if (!$this->objDAO->validarPropiedadDetalle($this->dto->idDetalle, $this->dto->idUsuario)) {
            $this->validationMessage = "No tiene permisos para eliminar este detalle, o la receta no existe.";
            return false;
        }

        // Ejecución del receptor (DAO)
        // Método: `eliminarDetalle`
        return $this->objDAO->eliminarDetalle($this->dto->idDetalle);
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
 * Centraliza la coordinación (Factory, Command, State).
 */
class controlDetalleCita
{
    // Atributos: Dependencias
    private $objMensaje;
    private $objDetalle; // Se mantiene si se usa para otras funciones (ej. obtenerEstadisticas)

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objDetalle = new RecetaDetalleDAO(); // Mantenido para otros métodos de listado/stats
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (El estado del Command determina el flujo)
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRetorno = "./indexDetalleCita.php";

        try {
            // Factory Method: Creación del DTO
            $dto = DetalleCitaFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = DetalleCitaFactory::crearComando($action, $dto);

            // Command: Ejecución
            $resultado = $command->execute();

            // Atributo: $mensajeError
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado
            if ($mensajeError) {
                // Estado 1: Error de validación/permisos
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error de operación: " . $mensajeError,
                    $urlRetorno,
                    "error",
                    false
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    '✅ Detalle de receta eliminado correctamente.', 
                    $urlRetorno, 
                    'success'
                );
            } else {
                // Estado 3: Error de base de datos/registro no encontrado
                $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al eliminar el detalle. Verifique la existencia en la base de datos.', 
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
    
    // --- Métodos originales refactorizados/mantenidos ---

    // El método original 'eliminarDetalle' ahora llama a la nueva estructura.
    // Esto asegura compatibilidad con llamadas externas si existieran.
    public function eliminarDetalle($idDetalle)
    {
        // Asumiendo que esta llamada se hace después de la validación de rol
        if (!isset($_SESSION['id_usuario'])) {
             $this->objMensaje->mensajeSistemaShow('Sesión de usuario no encontrada.', './indexDetalleCita.php', 'error');
             return;
        }
        
        $this->ejecutarComando('eliminar', [
            'idDetalle' => $idDetalle, 
            'idUsuario' => $_SESSION['id_usuario']
        ]);
    }
    
    // Mantenemos otros métodos que usan directamente el DAO.
    public function obtenerEstadisticas()
    {
        // Lógica de obtención de estadísticas
        return [
             'total_detalles' => count($this->objDetalle->obtenerTodosDetalles()),
             'medicamentos_populares' => $this->objDetalle->obtenerMedicamentosMasRecetados(5)
        ];
    }
}


?>