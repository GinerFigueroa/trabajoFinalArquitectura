<?php

include_once('../../../../modelo/OrdenPagoDAO.php');
include_once('../../../../shared/mensajeSistema.php');
include_once('./formOdenPDF.php'); // Vista que contiene la lógica de Dompdf

class controlOrdenPDF
{
    private $objOrden;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objOrden = new OrdenPago();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formOdenPDF();
    }

    public function generarPDF()
    {
        $idOrden = $_GET['id'] ?? null;
        
        if (empty($idOrden) || !is_numeric($idOrden)) {
            $this->objMensaje->mensajeSistemaShow("ID de Orden de Pago no proporcionado o no válido.", "../indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        $orden = $this->objOrden->obtenerOrdenPorId($idOrden);

        if (!$orden) {
            $this->objMensaje->mensajeSistemaShow("Orden de Pago no encontrada.", "../indexOdenPrefactura.php", "systemOut", false);
            return;
        }

        // Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($orden);
    }
}
?>