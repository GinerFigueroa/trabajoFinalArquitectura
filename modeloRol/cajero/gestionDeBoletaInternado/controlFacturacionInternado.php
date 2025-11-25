<?php

include_once('../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class FacturaInternadoDTO {
    public $idFactura;
    
    public function __construct(array $data) {
        // Para la eliminación, solo necesitamos el ID.
        $this->idFactura = (int)($data['id'] ?? 0);
    }
}

// Patrón: COMMAND 📦 - Interfaz base
interface Comando {
    public function execute(): bool; // Retorna true si tiene éxito, false si falla.
    public function getValidationMessage(): ?string;
} 

// Patrón: FACTORY METHOD 🏭
class FacturaInternadoCommandFactory {
    
    public static function crearDTO(array $data): FacturaInternadoDTO {
        return new FacturaInternadoDTO($data);
    }
    
    public static function crearComando(string $action, FacturaInternadoDTO $dto): Comando {
        switch ($action) {
            case 'eliminar':
                return new EliminarFacturaInternadoCommand($dto);
            // case 'editar': // Comandos futuros
            //     return new EditarFacturaInternadoCommand($dto);
            default:
                throw new Exception("Acción de comando ({$action}) no soportada.");
        }
    }
}

// COMMAND Concreto: Eliminar Factura 📦
class EliminarFacturaInternadoCommand implements Comando
{
    private $objDAO; // Receptor (FacturacionInternadoDAO)
    private $dto;
    private $validationMessage = null; 

    public function __construct(FacturaInternadoDTO $dto)
    {
        $this->objDAO = new FacturacionInternadoDAO();
        $this->dto = $dto;
    }
    
    /**
     * @return bool Retorna true si la eliminación es exitosa, false en caso contrario.
     */
    public function execute(): bool
    {
        // Validación de datos antes de la ejecución
        if (!$this->validate()) {
            return false;
        }

        // Ejecución del receptor (DAO)
        $resultado = $this->objDAO->eliminarFacturaInternado($this->dto->idFactura);

        if (!$resultado) {
             $this->validationMessage = 'Error al eliminar la factura de Internado de la base de datos.';
             return false;
        }

        return true;
    }
    
    private function validate(): bool
    {
        if ($this->dto->idFactura <= 0) {
            $this->validationMessage = "ID de factura no válido o faltante.";
            return false;
        }
        
        return true;
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
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación entre la creación del Command/DTO (Factory), 
 * la ejecución del Command y el manejo de los resultados (State).
 */
class controlFacturacionInternado
{
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Utiliza el Factory para determinar y ejecutar la acción.
     */
    public function ejecutarComando(string $action, array $data)
    {
        $urlRedireccion = "./indexFacturacionInternadoPDF.php";

        try {
            // Factory Method: Creación del DTO
            $dto = FacturaInternadoCommandFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = FacturaInternadoCommandFactory::crearComando($action, $dto);

            // Command: Ejecución. $resultado es true/false (Estado de la operación)
            $resultado = $command->execute();

            // Leer ESTADO/Error de validación del Command
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación o fallo interno del Command/DAO
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error al procesar la factura: " . $mensajeError,
                    $urlRedireccion,
                    "error"
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                
                // Mensaje de éxito específico para la acción de eliminación
                if ($action === 'eliminar') {
                    $this->objMensaje->mensajeSistemaShow(
                        "✅ Factura de Internado eliminada correctamente (ID: {$dto->idFactura}).", 
                        $urlRedireccion, 
                        'success'
                    );
                } else {
                     // Mensaje genérico para otras acciones que puedan ser implementadas
                    $this->objMensaje->mensajeSistemaShow(
                        "✅ Acción '{$action}' ejecutada correctamente.", 
                        $urlRedireccion, 
                        'success'
                    );
                }

            } else {
                 // Estado 3: Fallo de ejecución del DAO no capturado por el mensaje de validación
                 $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al ejecutar el comando. Por favor, reintente.', 
                    $urlRedireccion, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno inesperado
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRedireccion, 
                'error'
            );
        }
    }
    
    // El método anterior `eliminarFacturaInternado` ha sido reemplazado por `ejecutarComando`.
}