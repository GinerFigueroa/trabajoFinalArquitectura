<?php

include_once('../../../../modelo/BoletaDAO.php'); // Necesario para obtener datos de la boleta
include_once('../../../../shared/mensajeSistema.php');
include_once('./formEmitirPrefactura.php'); // La vista que genera el HTML y el PDF

class controlEmicionBoletaPDF
{
    private $objBoletaDAO;
    private $objMensaje;
    private $objFormPDF;

    public function __construct()
    {
        $this->objBoletaDAO = new BoletaDAO();
        $this->objMensaje = new mensajeSistema();
        $this->objFormPDF = new formEmicionBoletaPDF();
    }

    public function generarPDF()
    {
        // 1. Obtener y validar el ID de la boleta desde GET
        $idBoleta = $_GET['id_boleta'] ?? null;
        $urlRedireccion = "../indexEmisionBoletaFinal.php";
        
        if (empty($idBoleta) || !is_numeric($idBoleta)) {
            $this->objMensaje->mensajeSistemaShow("ID de Boleta/Factura no proporcionado o no válido.", $urlRedireccion, "error");
            return;
        }

        // 2. Obtener los datos completos para el PDF
        // Se usa la función que junta Boleta, Orden, Paciente y Detalle (Cita/Internado)
        $boleta = $this->objBoletaDAO->obtenerBoletaCompletaParaPDF($idBoleta);

        if (!$boleta) {
            $this->objMensaje->mensajeSistemaShow("El comprobante de pago N° {$idBoleta} no fue encontrado.", $urlRedireccion, "error");
            return;
        }

        // 3. Llamar a la vista que genera el HTML y renderiza el PDF
        $this->objFormPDF->generarPDFShow($boleta);
    }
}
?>