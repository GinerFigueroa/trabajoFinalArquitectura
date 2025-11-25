<?php

include_once('../../../../modelo/BoletaDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');
// Receptor: La vista que tiene la lógica de generar el HTML y el PDF
include_once('./formEmicionBoletaPDF.php'); 

// ==========================================================
// 1. ESTRUCTURAS DE PATRONES: DTO, FACTORY, COMMAND
// ==========================================================

// DTO/ENTIDAD (Data Transfer Object)
class BoletaPDFDTO {
    // Atributo: $idBoleta
    public $idBoleta;
    
    // Método: Constructor
    public function __construct(array $data) {
        $this->idBoleta = (int)($data['id_boleta'] ?? 0);
    }
}

// Patrón: FACTORY METHOD 🏭
interface Comando {} // Interfaz base para el Command

class BoletaPDFFactory {
    // Método: `crearDTO`
    public static function crearDTO(array $data): BoletaPDFDTO {
        // Crea y retorna el DTO
        return new BoletaPDFDTO($data);
    }
    
    // Método: `crearComando` (Factory Method)
    public static function crearComando(string $action, BoletaPDFDTO $dto, formEmicionBoletaPDF $receptor): Comando {
        switch ($action) {
            case 'generarPDF':
                // Crea y retorna el comando de generación de PDF
                return new GenerarBoletaPDFCommand($dto, $receptor);
            default:
                throw new Exception("Acción de comando no soportada: " . $action);
        }
    }
}

// COMMAND Concreto: Generar Boleta PDF 📦
class GenerarBoletaPDFCommand implements Comando
{
    // Atributos: DTO, Receptor (Vista) y Acciones (DAO)
    private $objDAO; // DAO para obtener los datos
    private $dto;
    private $objReceptor; // formEmicionBoletaPDF (la vista)
    private $validationMessage = null;
    private $urlRedireccion = "../indexEmisionBoletaFinal.php";

    // Método: Constructor
    public function __construct(BoletaPDFDTO $dto, formEmicionBoletaPDF $receptor)
    {
        $this->objDAO = new BoletaDAO();
        $this->dto = $dto;
        $this->objReceptor = $receptor;
    }

    // Método de Validación y Obtención de Datos
    private function validarYObtenerDatos(): ?array
    {
        // 1. Validación de ID
        if ($this->dto->idBoleta <= 0) {
            $this->validationMessage = "ID de Boleta/Factura no proporcionado o no válido.";
            return null;
        }

        // 2. Obtener los datos completos para el PDF
        $boletaData = $this->objDAO->obtenerBoletaCompletaParaPDF($this->dto->idBoleta);

        if (!$boletaData) {
            $this->validationMessage = "El comprobante de pago N° {$this->dto->idBoleta} no fue encontrado.";
            return null;
        }
        
        return $boletaData;
    }
    
    // Método: `execute` (Lógica central del Command)
    public function execute(): bool
    {
        // 1. Validar y obtener datos
        // Atributo: `$boleta`
        $boleta = $this->validarYObtenerDatos();

        if (!$boleta) {
            return false; // Falla la ejecución si la validación/obtención falla
        }

        // 2. Ejecución del Receptor (Vista que genera el PDF)
        // El Command llama al método del Receptor (formEmicionBoletaPDF)
        $this->objReceptor->generarPDFShow($boleta);
        
        // La ejecución exitosa aquí implica que la vista tomó el control para enviar el PDF.
        return true; 
    }

    // Métodos de Estado (STATE)
    public function getValidationMessage(): ?string
    {
        return $this->validationMessage;
    }
    
    public function getRedirectionURL(): string
    {
        return $this->urlRedireccion;
    }
}

// ==========================================================
// 2. CONTROLADOR (MEDIATOR)
// ==========================================================

/**
 * Patrón: MEDIATOR 🤝
 * Centraliza la coordinación entre los demás patrones.
 */
class controlEmicionBoletaPDF
{
    private $objMensaje;
    private $objFormPDF; // El Receptor que se inyectará al Command

    // Método: Constructor
    public function __construct()
    {
        $this->objMensaje = new mensajeSistema();
        // El receptor se inicializa aquí
        $this->objFormPDF = new formEmicionBoletaPDF();
    }

    /**
     * Método: `ejecutarComando` (Punto de coordinación central)
     * Patrón: STATE 🚦 (Maneja el flujo de errores)
     */
    public function ejecutarComando(string $action, array $data)
    {
        try {
            // Factory Method: Creación del DTO
            $dto = BoletaPDFFactory::crearDTO($data);
            
            // Factory Method: Creación del COMMAND, inyectando el Receptor
            $command = BoletaPDFFactory::crearComando($action, $dto, $this->objFormPDF);

            // Command: Ejecución
            $resultado = $command->execute();

            $mensajeError = $command->getValidationMessage();
            $urlRetorno = $command->getRedirectionURL();

            // Mediator/STATE: Lógica para manejar el resultado del Command
            if (!$resultado) {
                // Estado 1: Fallo (Validación fallida o datos no encontrados)
                $this->objMensaje->mensajeSistemaShow(
                    "❌ Error al generar el PDF: " . $mensajeError,
                    $urlRetorno,
                    "error"
                );
            }
            // Si $resultado es true, la vista ya ha enviado el PDF y el script muere naturalmente.

        } catch (Exception $e) {
            // Estado 2: Error de fábrica o interno
             $this->objMensaje->mensajeSistemaShow(
                '❌ Error interno del sistema: ' . $e->getMessage(), 
                "../indexEmisionBoletaFinal.php", 
                'error'
            );
        }
    }
    
    /**
     * @deprecated Este método mantiene la compatibilidad con el index original.
     */
    public function generarPDF()
    {
        $idBoleta = $_GET['id_boleta'] ?? null;
        $this->ejecutarComando('generarPDF', ['id_boleta' => $idBoleta]);
    }
}
?>