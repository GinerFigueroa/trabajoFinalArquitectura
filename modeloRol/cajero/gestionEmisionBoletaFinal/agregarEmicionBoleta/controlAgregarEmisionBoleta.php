<?php

include_once('../../../../modelo/BoletaDAO.php'); 
// Necesario para las constantes de validación
include_once('../../../../modelo/BoletaAuxiliarDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class EmisionBoletaDTO {
    public $idOrden;
    public $numeroBoleta;
    public $tipo;
    public $montoTotal;
    public $metodoPago;
    
    public function __construct(array $data) {
        $this->idOrden = (int)($data['id_orden'] ?? 0);
        $this->numeroBoleta = $data['numero_boleta'] ?? '';
        $this->tipo = $data['tipo'] ?? '';
        $this->montoTotal = (float)($data['monto_total'] ?? 0.0);
        $this->metodoPago = $data['metodo_pago'] ?? '';
    }
}

// Patrón: COMMAND 📦 - Interfaz base
interface Comando {
    public function execute(): ?int; // Retorna el ID de la boleta o null/false
    public function getValidationMessage(): ?string;
} 

// Patrón: FACTORY METHOD 🏭
class BoletaCommandFactory {
    public static function crearDTO(array $data): EmisionBoletaDTO {
        return new EmisionBoletaDTO($data);
    }
    
    public static function crearComando(string $action, EmisionBoletaDTO $dto): Comando {
        switch ($action) {
            case 'emitir':
                return new EmitirBoletaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada.");
        }
    }
}

// COMMAND Concreto: Emitir Boleta 📦
class EmitirBoletaCommand implements Comando
{
    private $objDAO; // Receptor (BoletaDAO)
    private $dto;
    private $validationMessage = null; // Estado de la validación

    public function __construct(EmisionBoletaDTO $dto)
    {
        $this->objDAO = new BoletaDAO();
        $this->dto = $dto;
    }
    
    /**
     * @return int|null Retorna el ID de la nueva boleta o null si falla
     */
    public function execute(): ?int
    {
        // Validación de datos antes de la ejecución (Patrón STATE implícito)
        if (!$this->validate()) {
            return null;
        }

        // Ejecución del receptor (DAO)
        // El DAO ahora se espera que retorne el nuevo ID o false/null
        $nuevoId = $this->objDAO->registrarBoleta(
            $this->dto->idOrden, 
            $this->dto->numeroBoleta, 
            $this->dto->tipo, 
            $this->dto->montoTotal, 
            $this->dto->metodoPago
        );

        // Si falla el registro por cualquier razón de BD/DAO
        if (!$nuevoId) {
             $this->validationMessage = 'Error al registrar la boleta. La orden ya podría estar facturada o hubo un error de BD.';
             return null;
        }

        return $nuevoId;
    }
    
    private function validate(): bool
    {
        if ($this->dto->idOrden <= 0 || empty($this->dto->numeroBoleta) || empty($this->dto->tipo) || empty($this->dto->metodoPago)) {
            $this->validationMessage = "Faltan campos obligatorios, incluyendo la selección de la Orden o N° de comprobante.";
            return false;
        }
        
        if ($this->dto->montoTotal <= 0) {
            $this->validationMessage = "El monto total debe ser un valor positivo.";
            return false;
        }

        // Validación de ENUMs
        if (!in_array($this->dto->tipo, BoletaAuxiliarDAO::obtenerTiposBoleta()) || !in_array($this->dto->metodoPago, BoletaAuxiliarDAO::obtenerMetodosPago())) {
            $this->validationMessage = "Tipo de comprobante o método de pago no válido.";
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
class controlAgregarEmisionBoleta
{
    private $objMensaje;

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
        $urlExito = "../indexEmisionBoletaFinal.php";
        $urlError = "./indexAgregarEmisionBoleta.php"; 

        try {
            // Factory Method: Creación del DTO
            $dto = BoletaCommandFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = BoletaCommandFactory::crearComando($action, $dto);

            // Command: Ejecución
            // $nuevoIdBoleta es el resultado (Estado de la operación)
            $nuevoIdBoleta = $command->execute();

            // Leer ESTADO/Error de validación del Command
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación o fallo interno del Command/DAO
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error en la emisión: " . $mensajeError,
                    $urlError,
                    "error",
                    false
                );
            } elseif ($nuevoIdBoleta) {
                // Estado 2: Éxito
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Comprobante (ID: {$nuevoIdBoleta}) emitido y Orden Facturada. Puede generar el PDF desde el listado.", 
                    $urlExito, 
                    'success'
                );
            } else {
                 // Estado 3: Fallo de ejecución no capturado por el Command (raro, pero como fallback)
                 $this->objMensaje->mensajeSistemaShow(
                    '⚠️ Error al registrar la boleta (resultado nulo).', 
                    $urlError, 
                    'error'
                );
            }
        } catch (Exception $e) {
            // Estado 4: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                $urlError, 
                'error'
            );
        }
    }
    
    // Método anterior (`emitirBoleta`) eliminado/obsoleto, ya que la lógica se movió a `ejecutarComando`.
}
?>