<?php

include_once('../../../../../modelo/OrdenExamenDAO.php');
include_once('../../../../../shared/mensajeSistema.php');
include_once('./formOrdenExamenClinicoPDF.php');

// =====================================================================
// PATRÓN STATE: Interfaz y Clases Concretas
// =====================================================================

interface EstadoOrdenPDF 
{
    // Método Abstracto: obtenerColor
    public function obtenerColor(): string; 
}

class PendientePDFState implements EstadoOrdenPDF 
{
    // Método: obtenerColor
    public function obtenerColor(): string { return 'red'; }
}

class RealizadoPDFState implements EstadoOrdenPDF 
{
    // Método: obtenerColor
    public function obtenerColor(): string { return 'orange'; }
}

class EntregadoPDFState implements EstadoOrdenPDF 
{
    // Método: obtenerColor
    public function obtenerColor(): string { return 'green'; }
}

class controlExamenClinicoPDF // (Controlador y Contexto del Patrón State)
{
    // Atributo: $objOrden (Modelo/DAO)
    private $objOrden;
    // Atributo: $objMensaje (Sistema de Mensajes)
    private $objMensaje;
    // Atributo: $objFormPDF (Vista/Generador PDF)
    private $objFormPDF;

    // Método: Constructor
    public function __construct()
    {
        $this->objOrden = new OrdenExamenDAO();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formOrdenExamenClinicoPDF();
    }

    // PATRÓN STATE: Método Contexto para obtener el estilo
    // Metodo: obtenerObjetoEstadoPDF
    public function obtenerObjetoEstadoPDF(string $estado): EstadoOrdenPDF
    {
        // En un escenario más complejo, este método podría usar un Factory.
        switch ($estado) {
            case 'Pendiente': return new PendientePDFState();
            case 'Realizado': return new RealizadoPDFState();
            case 'Entregado': return new EntregadoPDFState();
            default: return new PendientePDFState(); 
        }
    }

    // Metodo: generarPDF
    public function generarPDF()
    {
        $idOrden = $_GET['id'] ?? null;
        
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow("ID de Orden de Examen no proporcionado o no válido.", "../indexOrdenExamenClinico.php", "error");
            return;
        }

        // 1. Obtener datos del Modelo
        $orden = $this->objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            $this->objMensaje->mensajeSistemaShow("Orden de Examen no encontrada.", "../indexOrdenExamenClinico.php", "error");
            return;
        }

        // 2. Validación de datos mínimos
        if (empty($orden['nombre_paciente']) || empty($orden['nombre_medico']) || empty($orden['tipo_examen'])) {
            $this->objMensaje->mensajeSistemaShow("La orden de examen no tiene todos los datos necesarios para generar el PDF.", "../indexOrdenExamenClinico.php", "error");
            return;
        }

        // 3. Llamar a la Vista para generar y renderizar el PDF
        $this->objFormPDF->generarPDFShow($orden, $this); // Pasa el Contexto (Controlador) a la Vista
    }
}
?>