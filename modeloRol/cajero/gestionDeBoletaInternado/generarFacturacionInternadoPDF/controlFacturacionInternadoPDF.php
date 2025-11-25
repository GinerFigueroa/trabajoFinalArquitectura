<?php

include_once('../../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php'); 
include_once('./formFacturacionInternadoPDF.php'); 

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO y COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class FacturaPDFRequestDTO {
    public $idFactura;
    
    public function __construct(array $data) {
        $this->idFactura = (int)($data['id_factura'] ?? 0);
    }
}

// Patrón: COMMAND 📦 - Interfaz base
interface ComandoFacturacionPDF {
    /**
     * @return array|null Retorna los datos de la factura si tiene éxito, null si falla.
     */
    public function execute(): ?array; 
    public function getValidationMessage(): ?string;
} 

// COMMAND Concreto: Obtener y Validar Datos para el PDF 📦
class GenerarFacturaPDFCommand implements ComandoFacturacionPDF
{
    private $objDAO; // Receptor
    private $dto;
    private $validationMessage = null; 

    public function __construct(FacturaPDFRequestDTO $dto)
    {
        $this->objDAO = new FacturacionInternadoDAO();
        $this->dto = $dto;
    }
    
    public function execute(): ?array
    {
        // 1. Validación DTO
        if (!$this->validate()) {
            return null;
        }

        // 2. Ejecución del Receptor (DAO)
        $factura = $this->objDAO->obtenerFacturaCompletaParaPDF($this->dto->idFactura);

        if (!$factura) {
             $this->validationMessage = "La Factura N° {$this->dto->idFactura} de Internado no fue encontrada.";
             return null;
        }

        return $factura;
    }
    
    private function validate(): bool
    {
        if ($this->dto->idFactura <= 0) {
            $this->validationMessage = "ID de Factura no proporcionado o no válido.";
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
 * Centraliza la lógica de flujo: Input -> DTO -> Command -> Form/View.
 */
class controlFacturacionInternadoPDF
{
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formFacturacionInternadoPDF();
    }

    /**
     * Método central que coordina la generación del PDF.
     */
    public function generarPDF()
    {
        $urlRedireccion = "../indexFacturacionInternado.php"; 
        
        // 1. Creación del DTO a partir del input (GET)
        $data = ['id_factura' => $_GET['id_factura'] ?? null];
        $dto = new FacturaPDFRequestDTO($data);

        // 2. Creación del COMMAND
        $command = new GenerarFacturaPDFCommand($dto);

        try {
            // 3. Ejecución del COMMAND y obtención de datos
            $factura = $command->execute();

            // 4. Manejo del ESTADO (Resultado del Command)
            if ($factura) {
                // Estado: Éxito. Llama al Presentador (Form/View) para renderizar el PDF.
                $this->objFormPDF->generarPDFShow($factura);
                // NOTA: La función generarPDFShow ejecuta dompdf::stream y termina el script.
            } else {
                // Estado: Fallo (Error de validación o no encontrado en el DAO)
                $mensajeError = $command->getValidationMessage() ?? "Error desconocido al obtener la factura.";
                
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error al generar PDF: " . $mensajeError, 
                    $urlRedireccion, 
                    "error"
                );
            }
        } catch (Exception $e) {
            // Manejo de errores fatales
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema al ejecutar el comando: ' . $e->getMessage(), 
                $urlRedireccion, 
                'error'
            );
        }
    }
}