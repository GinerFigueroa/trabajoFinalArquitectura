<?php

include_once('../../../../modelo/BoletaDAO.php'); 

include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class BoletaEdicionDTO {
    // Atributos: Los datos sanitizados que vienen del formulario
    public $idBoleta;
    public $numeroBoleta;
    public $tipo;
    public $montoTotal;
    public $metodoPago;

    // Método: Constructor
    public function __construct(array $data) {
        $this->idBoleta = (int)($data['id_boleta'] ?? 0);
        // Sanitización básica de strings
        $this->numeroBoleta = trim($data['numero_boleta'] ?? '');
        $this->tipo = $data['tipo'] ?? null;
        // Se asegura que el monto sea un flotante para validación posterior
        $this->montoTotal = filter_var($data['monto_total'] ?? 0, FILTER_VALIDATE_FLOAT); 
        $this->metodoPago = $data['metodo_pago'] ?? null;
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class BoletaEdicionFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): BoletaEdicionDTO {
        // Crea y retorna el DTO
        return new BoletaEdicionDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, BoletaEdicionDTO $dto): Comando {
        switch ($action) {
            case 'editarBoleta':
                // Crea y retorna el comando de edición
                return new EditarBoletaCommand($dto);
            default:
                throw new Exception("Acción de comando no soportada: " . $action);
        }
    }
}

// COMMAND Concreto: Editar Boleta 📦
class EditarBoletaCommand implements Comando
{
    // Atributos: DTO, Acciones (DAO) y Mensajes de estado
    private $objDAO; 
    private $dto;
    private $validationMessage = null;
    private $urlError = "";
    private $urlRedireccion = "../indexEmisionBoletaFinal.php";

    // Método: Constructor
    public function __construct(BoletaEdicionDTO $dto)
    {
        $this->objDAO = new BoletaDAO();
        $this->dto = $dto;
        // La URL de error depende del ID de boleta en el DTO
        $this->urlError = "./indexEditarEmisionBoleta.php?id={$this->dto->idBoleta}";
    }

    // Método de Validación
    private function validarDatos(): bool
    {
        // 1. Validación de campos obligatorios/ID
        if ($this->dto->idBoleta <= 0 || empty($this->dto->numeroBoleta) || empty($this->dto->tipo) || $this->dto->montoTotal === false || empty($this->dto->metodoPago)) {
            $this->validationMessage = "Faltan campos obligatorios o el ID de la boleta es inválido.";
            return false;
        }

        // 2. Validación de monto
        if (!is_numeric($this->dto->montoTotal) || $this->dto->montoTotal <= 0) {
            $this->validationMessage = "El monto total debe ser un valor numérico positivo.";
            return false;
        }
        
        // 3. Validación de ENUMs
        if (!in_array($this->dto->tipo, BoletaAuxiliarDAO::obtenerTiposBoleta()) || !in_array($this->dto->metodoPago, BoletaAuxiliarDAO::obtenerMetodosPago())) {
            $this->validationMessage = "Tipo de comprobante o método de pago no válido.";
            return false;
        }

        return true;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Validar datos
        if (!$this->validarDatos()) {
            return false; 
        }

        // 2. Ejecutar la acción de negocio (DAO)
        $resultado = $this->objDAO->editarBoleta(
            $this->dto->idBoleta, 
            $this->dto->numeroBoleta, 
            $this->dto->tipo, 
            $this->dto->montoTotal, 
            $this->dto->metodoPago
        );

        // 3. Manejar resultado del DAO
        if (!$resultado) {
            $this->validationMessage = 'Error al actualizar el comprobante. Es posible que no se haya modificado ningún dato o hubo un error de BD.';
            return false;
        }
        
        // Éxito
        return true; 
    }

    // Métodos de Estado (STATE)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    public function getRedirectionURL(bool $success): string
    {
        return $success ? $this->urlRedireccion : $this->urlError;
    }

    public function getSuccessMessage(): string
    {
        return 'Comprobante de pago actualizado correctamente.';
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación, maneja el flujo de ejecución y notifica el resultado.
 */
class controlEditarEmisionBoleta
{
    private $objMensaje;

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (Maneja el flujo de errores y éxito)
     */
    public function ejecutarComando(string $action, array $data)
    {
        try {
            // Factory Method: Creación del DTO
            $dto = BoletaEdicionFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = BoletaEdicionFactory::crearComando($action, $dto);

            // Command: Ejecución
            $resultado = $command->execute();

            $mensaje = $resultado ? $command->getSuccessMessage() : $command->getValidationMessage();
            $tipoMensaje = $resultado ? 'success' : 'error';
            $urlRetorno = $command->getRedirectionURL($resultado);

            // Mediator/STATE: Lógica para manejar el resultado del Command
            $this->objMensaje->mensajeSistemaShow(
                $mensaje,
                $urlRetorno,
                $tipoMensaje
            );

        } catch (Exception $e) {
            // Estado 2: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                "../indexEmisionBoletaFinal.php", 
                'error'
            );
        }
    }
}
?>