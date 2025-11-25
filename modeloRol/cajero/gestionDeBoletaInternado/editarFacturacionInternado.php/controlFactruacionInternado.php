<?php

include_once('../../../../modelo/FacturacionInternadoDAO.php'); 

include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class FacturaInternadoDTO {
    public $idFactura;
    public $idInternado;
    public $fechaEmision;
    public $diasInternado;
    public $costoHabitacion;
    public $costoTratamientos;
    public $costoMedicamentos;
    public $costoOtros;
    public $total;
    public $estado;

    public function __construct(array $data) {
        $this->idFactura = (int)($data['id_factura'] ?? 0);
        $this->idInternado = (int)($data['id_internado'] ?? 0);
        $this->fechaEmision = $data['fecha_emision'] ?? null;
        $this->diasInternado = (int)($data['dias_internado'] ?? 0);
        $this->costoHabitacion = (float)($data['costo_habitacion'] ?? 0.00);
        $this->costoTratamientos = (float)($data['costo_tratamientos'] ?? 0.00);
        $this->costoMedicamentos = (float)($data['costo_medicamentos'] ?? 0.00);
        $this->costoOtros = (float)($data['costo_otros'] ?? 0.00);
        $this->total = (float)($data['total'] ?? 0.00);
        $this->estado = $data['estado'] ?? null;
    }
}

// Patrón: COMMAND 📦 - Interfaz base
interface ComandoFacturacion {
    public function execute(): bool; // Retorna true si tiene éxito, false si falla.
    public function getValidationMessage(): ?string;
} 

// Patrón: FACTORY METHOD 🏭
class FacturaInternadoCommandFactory {
    
    public static function crearDTO(array $data): FacturaInternadoDTO {
        return new FacturaInternadoDTO($data);
    }
    
    public static function crearComando(string $action, FacturaInternadoDTO $dto): ComandoFacturacion {
        if ($action === 'editar') {
            return new EditarFacturaInternadoCommand($dto);
        } else {
            throw new Exception("Acción de comando ({$action}) no soportada.");
        }
    }
}

// COMMAND Concreto: Editar Factura de Internado 📦
class EditarFacturaInternadoCommand implements ComandoFacturacion
{
    private $objDAO; // Receptor (FacturacionInternadoDAO)
    private $dto;
    private $validationMessage = null; 

    public function __construct(FacturaInternadoDTO $dto)
    {
        $this->objDAO = new FacturacionInternadoDAO();
        $this->dto = $dto;
    }
    
    private function validate(): bool
    {
        // 1. Validación de IDs y campos obligatorios
        if ($this->dto->idFactura <= 0 || $this->dto->idInternado <= 0) {
            $this->validationMessage = "IDs de Factura o Internado inválidos.";
            return false;
        }
        if (empty($this->dto->fechaEmision) || $this->dto->diasInternado <= 0 || empty($this->dto->estado)) {
            $this->validationMessage = "Faltan campos obligatorios (Fecha de Emisión, Días Internado, Estado).";
            return false;
        }
        
        // 2. Validación de Montos
        if ($this->dto->total <= 0) {
            $this->validationMessage = "El monto total debe ser un valor positivo.";
            return false;
        }

        // 3. Validación de Estado (se requiere FacturacionInternadoAuxiliarDAO)
        if (!in_array($this->dto->estado, FacturacionInternadoAuxiliarDAO::obtenerEstadosFactura())) {
            $this->validationMessage = "Estado de factura no válido: " . htmlspecialchars($this->dto->estado);
            return false;
        }
        
        return true;
    }

    public function execute(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        // Ejecución del receptor (DAO)
        $resultado = $this->objDAO->editarFacturaInternado(
            $this->dto->idFactura, 
            $this->dto->idInternado, 
            $this->dto->fechaEmision, 
            $this->dto->diasInternado, 
            $this->dto->costoHabitacion, 
            $this->dto->costoTratamientos, 
            $this->dto->costoMedicamentos, 
            $this->dto->costoOtros, 
            $this->dto->total, 
            $this->dto->estado
        );

        if (!$resultado) {
             $this->validationMessage = 'Error al actualizar la factura. Es posible que no se haya modificado ningún dato o hubo un error de BD.';
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
 * la ejecución del Command y el manejo de los mensajes de sistema.
 */
class controlEditarFacturacionInternado
{
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * @param string $action La acción a ejecutar (ej. 'editar').
     * @param array $data Los datos POST del formulario.
     */
    public function ejecutarComando(string $action, array $data)
    {
        // Define la URL de redirección principal y la URL de error (volver al formulario de edición)
        $urlRedireccion = "../indexFacturacionInternado.php";
        $urlError = "./indexEditarFacturacionInternado.php?id=" . ($data['id_factura'] ?? 0);

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
                    "❌ Error al actualizar la factura: " . $mensajeError,
                    $urlError, // Vuelve al formulario para corregir
                    "error"
                );
            } elseif ($resultado) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Factura de Internado #{$dto->idFactura} actualizada correctamente.", 
                    $urlRedireccion, 
                    'success'
                );
            } else {
                 // Estado 3: Fallo de ejecución del DAO no capturado por el mensaje de validación
                 $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al ejecutar el comando. Por favor, reintente.', 
                    $urlError, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno inesperado (ej. acción no soportada)
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlRedireccion, 
                'error'
            );
        }
    }
    
    // El método original `editarFacturaInternado` ha sido reemplazado por `ejecutarComando`.
}
?>