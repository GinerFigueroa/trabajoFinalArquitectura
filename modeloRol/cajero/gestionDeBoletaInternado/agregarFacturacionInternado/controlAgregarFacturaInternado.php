<?php

include_once('../../../../modelo/FacturacionInternadoDAO.php'); 

include_once('../../../../shared/mensajeSistema.php');

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class FacturaInternadoDTO {
    public $idInternado;
    public $fechaEmision;
    public $diasInternado;
    public $costoHabitacion;
    public $costoTratamientos;
    public $costoMedicamentos;
    public $costoOtros;
    public $total;
    public $estado;
    
    // El ID de factura (nuevoId) se manejará como retorno del Command.

    public function __construct(array $data) {
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
    /**
     * @return int|bool Retorna el nuevo ID de la factura si tiene éxito, false si falla.
     */
    public function execute(); 
    public function getValidationMessage(): ?string;
} 

// Patrón: FACTORY METHOD 🏭
class FacturaInternadoCommandFactory {
    
    public static function crearDTO(array $data): FacturaInternadoDTO {
        return new FacturaInternadoDTO($data);
    }
    
    public static function crearComando(string $action, FacturaInternadoDTO $dto): ComandoFacturacion {
        if ($action === 'agregar') {
            return new RegistrarFacturaInternadoCommand($dto);
        } else {
            throw new Exception("Acción de comando ({$action}) no soportada.");
        }
    }
}

// COMMAND Concreto: Registrar Nueva Factura de Internado 📦
class RegistrarFacturaInternadoCommand implements ComandoFacturacion
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
        if ($this->dto->idInternado <= 0) {
            $this->validationMessage = "ID de Internado inválido o no seleccionado.";
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

    public function execute()
    {
        if (!$this->validate()) {
            return false;
        }

        // Ejecución del receptor (DAO). Retorna el nuevo ID o false.
        $nuevoId = $this->objDAO->registrarFacturaInternado(
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

        if (!$nuevoId) {
             $this->validationMessage = 'Error al registrar la factura en la base de datos. El internado podría ya tener una factura.';
             return false;
        }

        return $nuevoId;
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
class controlAgregarFacturaInternado
{
    private $objMensaje;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
    }
    
    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * @param string $action La acción a ejecutar (ej. 'agregar').
     * @param array $data Los datos POST del formulario.
     */
    public function ejecutarComando(string $action, array $data)
    {
        // Define la URL de éxito y la URL de error (volver al formulario de agregar)
        $urlExito = "../indexFacturacionInternadoPDF.php";
        $urlError = "./indexAgregarFacturaInternado.php";

        try {
            // Factory Method: Creación del DTO
            $dto = FacturaInternadoCommandFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND
            $command = FacturaInternadoCommandFactory::crearComando($action, $dto);

            // Command: Ejecución. $nuevoId es el ID o false si falla.
            $nuevoId = $command->execute();

            // Leer ESTADO/Error de validación del Command
            $mensajeError = $command->getValidationMessage();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if ($mensajeError) {
                // Estado 1: Error de validación o fallo interno del Command/DAO
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error al agregar la factura: " . $mensajeError,
                    $urlError, // Vuelve al formulario
                    "error"
                );
            } elseif ($nuevoId) {
                // Estado 2: Éxito (ID generado)
                $this->objMensaje->mensajeSistemaShow(
                    "✅ Factura de Internado generada correctamente. ID: {$nuevoId}", 
                    $urlExito, 
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
                $urlError, 
                'error'
            );
        }
    }
}