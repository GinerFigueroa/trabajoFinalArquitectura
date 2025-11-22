<?php
// Archivo: controlAgregarFacturaInternado.php

include_once('../../../../modelo/FacturacionInternadoDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlAgregarFacturaInternado
{
    private $objFacturaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objFacturaDAO = new FacturacionInternadoDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    public function registrarFacturaInternado(
        $idInternado, $fechaEmision, $diasInternado, $costoHabitacion, 
        $costoTratamientos, $costoMedicamentos, $costoOtros, $total, $estado)
    {
        $urlExito = "../indexFacturacionInternadoPDF.php";
        $urlError = "./indexAgregarFacturaInternado.php";

        // Validación de datos numéricos y estado
        if (!is_numeric($total) || $total <= 0) {
            $this->objMensaje->mensajeSistemaShow("El monto total debe ser un valor positivo.", $urlError, "error");
            return;
        }
        if (!in_array($estado, FacturacionInternadoAuxiliarDAO::obtenerEstadosFactura())) {
            $this->objMensaje->mensajeSistemaShow("Estado de factura no válido.", $urlError, "error");
            return;
        }

        $nuevoId = $this->objFacturaDAO->registrarFacturaInternado(
            $idInternado, $fechaEmision, $diasInternado, $costoHabitacion, 
            $costoTratamientos, $costoMedicamentos, $costoOtros, $total, $estado
        );

        if ($nuevoId) {
            $this->objMensaje->mensajeSistemaShow(
                "Factura de Internado generada correctamente. ID: {$nuevoId}", 
                $urlExito, 
                'success'
            );
        } else {
            $this->objMensaje->mensajeSistemaShow(
                'Error al registrar la factura en la base de datos. El internado podría ya estar facturado.', 
                $urlError, 
                'error'
            );
        }
    }
}
?>