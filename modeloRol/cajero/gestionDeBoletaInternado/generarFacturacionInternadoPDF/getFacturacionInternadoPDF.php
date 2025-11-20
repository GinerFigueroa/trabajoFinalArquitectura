<?php


include_once('../../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php'); 
include_once('./formFacturacionInternadoPDF.php'); 

class controlFacturacionInternadoPDF
{
    private $objFacturaDAO;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objFacturaDAO = new FacturacionInternadoDAO(); 
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formFacturacionInternadoPDF();
    }

    public function generarPDF()
    {
        // 1. Obtener y validar el ID de la factura
        $idFactura = $_GET['id_factura'] ?? null; 
        $urlRedireccion = "../indexListarFacturacionInternado.php"; 
        
        if (empty($idFactura) || !is_numeric($idFactura)) {
            $this->objMensaje->mensajeSistemaShow("ID de Factura no proporcionado o no válido.", $urlRedireccion, "error");
            return;
        }

        // 2. Obtener los datos completos para el PDF
        $factura = $this->objFacturaDAO->obtenerFacturaCompletaParaPDF($idFactura);

        if (!$factura) {
            $this->objMensaje->mensajeSistemaShow("La Factura N° {$idFactura} de Internado no fue encontrada.", $urlRedireccion, "error");
            return;
        }

        // 3. Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($factura);
    }
}
?>