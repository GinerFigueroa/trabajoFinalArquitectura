<?php
// Archivo: controlAgregarEmisionBoleta.php

include_once('../../../../modelo/BoletaDAO.php'); 
include_once('../../../../shared/mensajeSistema.php');

class controlAgregarEmisionBoleta
{
    private $objBoletaDAO; 
    private $objMensaje;

    public function __construct()
    {
        $this->objBoletaDAO = new BoletaDAO(); 
        $this->objMensaje = new mensajeSistema();
    }
    
    public function emitirBoleta($idOrden, $numeroBoleta, $tipo, $montoTotal, $metodoPago)
    {
        $urlExito = "../indexEmisionBoletaFinal.php";
        // Si hay un error de validación, volvemos al formulario de agregar
        $urlError = "./indexAgregarEmisionBoleta.php"; 

        // Validación de monto
        if (!is_numeric($montoTotal) || $montoTotal <= 0) {
            $this->objMensaje->mensajeSistemaShow("El monto total debe ser un valor positivo.", $urlError, "error");
            return;
        }

        // Validación de ENUMs
        if (!in_array($tipo, BoletaAuxiliarDAO::obtenerTiposBoleta()) || !in_array($metodoPago, BoletaAuxiliarDAO::obtenerMetodosPago())) {
            $this->objMensaje->mensajeSistemaShow("Tipo de comprobante o método de pago no válido.", $urlError, "error");
            return;
        }

        $nuevoIdBoleta = $this->objBoletaDAO->registrarBoleta(
            $idOrden, $numeroBoleta, $tipo, $montoTotal, $metodoPago
        );

        if ($nuevoIdBoleta) {
            $this->objMensaje->mensajeSistemaShow(
                "Comprobante (ID: {$nuevoIdBoleta}) emitido y Orden Facturada. Puede generar el PDF desde el listado.", 
                $urlExito, 
                'success'
            );
        } else {
            // Este error puede ocurrir si la orden ya fue facturada justo antes de que el usuario haga clic.
            $this->objMensaje->mensajeSistemaShow(
                'Error al registrar la boleta. La orden ya podría estar facturada o hubo un error de BD.', 
                $urlError, 
                'error'
            );
        }
    }
}
?>